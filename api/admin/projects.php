<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — API: ADMIN PROJECTS CRUD
 * Methods: GET, POST, PUT, DELETE
 */

require_once __DIR__ . '/../../backend/config/db.php';
require_once __DIR__ . '/../../backend/helpers/response.php';
require_once __DIR__ . '/../../backend/helpers/auth_helper.php';

$admin = requireAdminAuth();
$method = $_SERVER['REQUEST_METHOD'];
$input = getJsonInput();

// Handle method spoofing from client to avoid Nginx PUT/DELETE restrictions
if (isset($input['_method'])) {
    $method = strtoupper($input['_method']);
} elseif (isset($_GET['_method'])) {
    $method = strtoupper($_GET['_method']);
} elseif (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $method = 'DELETE';
}

// Helper to sanitize BIGINT money string
function cleanMoney(mixed $val): string {
    if (is_null($val)) return '0';
    $str = preg_replace('/[^0-9]/', '', (string)$val);
    return empty($str) ? '0' : $str;
}

try {
    $db = getDB();
    ensureProjectsSchema($db);

    // 1. GET: Ambil daftar proyek atau single proyek
    if ($method === 'GET') {
        $id = $_GET['id'] ?? null;

        if ($id) {
            $stmt = $db->prepare("SELECT * FROM projects WHERE id = ?");
            $stmt->execute([$id]);
            $project = $stmt->fetch();

            if (!$project) {
                sendJsonError('Proyek tidak ditemukan.', 404);
            }

            // Detail
            $stmtD = $db->prepare("SELECT * FROM project_details WHERE project_id = ?");
            $stmtD->execute([$id]);
            $detail = $stmtD->fetch();

            // Items (RAB)
            $stmtI = $db->prepare("SELECT * FROM funding_items WHERE project_id = ? ORDER BY no_urut ASC");
            $stmtI->execute([$id]);
            $items = $stmtI->fetchAll();

            // Simulation
            $stmtS = $db->prepare("SELECT * FROM project_simulation WHERE project_id = ?");
            $stmtS->execute([$id]);
            $sim = $stmtS->fetch();

            sendJsonResponse([
                'project' => $project,
                'detail' => $detail,
                'funding_items' => $items,
                'simulation' => $sim
            ]);
        }

        // List all projects (compatible with MySQL ONLY_FULL_GROUP_BY)
        $stmt = $db->query("
            SELECT p.*, 
                   c.name as city_name,
                   c.slug as city_slug,
                   c.icon as city_icon,
                   COALESCE(sub.item_count, 0) as item_count,
                   CASE 
                       WHEN p.funding_target > 0 THEN ROUND((p.funding_collected / p.funding_target * 100), 2)
                       ELSE 0 
                   END as funding_percent
            FROM projects p
            LEFT JOIN cities c ON p.city_id = c.id
            LEFT JOIN (
                SELECT project_id, COUNT(id) as item_count
                FROM funding_items
                GROUP BY project_id
            ) sub ON p.id = sub.project_id
            ORDER BY p.sort_order ASC, p.created_at DESC
        ");
        $projects = $stmt->fetchAll();

        sendJsonResponse($projects);
    }

    // 2. POST: Tambah Proyek Baru
    if ($method === 'POST') {
        $input = getJsonInput();
        $id = trim($input['id'] ?? '');
        $title = trim($input['title'] ?? '');

        if (empty($id) || empty($title)) {
            sendJsonError('ID Proyek dan Judul Proyek wajib diisi.');
        }

        // Cek ID unik
        $stmtCheck = $db->prepare("SELECT id FROM projects WHERE id = ?");
        $stmtCheck->execute([$id]);
        if ($stmtCheck->fetch()) {
            sendJsonError("ID Proyek '{$id}' sudah digunakan. Silakan buat ID yang berbeda.");
        }

        $fundingTarget = cleanMoney($input['funding_target'] ?? '0');
        $fundingCollected = cleanMoney($input['funding_collected'] ?? '0');

        $cityId = !empty($input['city_id']) ? (int)$input['city_id'] : null;
        $cityName = trim($input['city'] ?? '');
        if ($cityId && empty($cityName)) {
            $stmtC = $db->prepare("SELECT name FROM cities WHERE id = ?");
            $stmtC->execute([$cityId]);
            $cityName = $stmtC->fetchColumn() ?: '';
        }

        $db->beginTransaction();

        $stmtIns = $db->prepare("
            INSERT INTO projects (
                id, city_id, city, title, category, image, funding_collected, funding_target,
                currency, status, featured, lokasi, target_display, tenor,
                return_rate, risk_level, min_investment, payout, remaining_days, asset_backed
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'IDR', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmtIns->execute([
            $id,
            $cityId,
            $cityName,
            $title,
            $input['category'] ?? 'Alat Berat & Infrastruktur',
            $input['image'] ?? 'assets/img/komatsu.jpg',
            $fundingCollected,
            $fundingTarget,
            $input['status'] ?? 'Open',
            !empty($input['featured']) ? 1 : 0,
            $input['lokasi'] ?? '',
            !empty($input['target_display']) ? trim($input['target_display']) : ('Rp ' . number_format((float)$fundingTarget, 0, ',', '.')),
            $input['tenor'] ?? '36 Bulan',
            $input['return_rate'] ?? '≥30% (p.a.)',
            $input['risk_level'] ?? 'Menengah - Terukur',
            $input['min_investment'] ?? 'Rp 500.000.000',
            $input['payout'] ?? 'Bagi Hasil Kompetitif',
            $input['remaining_days'] ?? '30 Hari Tersisa',
            $input['asset_backed'] ?? 'Unit CBU Grade A & BPKB'
        ]);

        // Insert Detail
        $stmtDet = $db->prepare("
            INSERT INTO project_details (
                project_id, tagline, what_will_provide_title, what_will_provide_content,
                sinergi_title, sinergi_content, summary_title, summary_content
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmtDet->execute([
            $id,
            $input['tagline'] ?? '',
            $input['what_will_provide_title'] ?? 'Alokasi Penggunaan Modal (Use of Funds)',
            $input['what_will_provide_content'] ?? '',
            $input['sinergi_title'] ?? 'Struktur Kemitraan Strategis',
            $input['sinergi_content'] ?? '',
            $input['summary_title'] ?? 'Ringkasan Kelayakan Investasi',
            $input['summary_content'] ?? ''
        ]);

        // Insert RAB (funding items)
        if (!empty($input['funding_items']) && is_array($input['funding_items'])) {
            $stmtItem = $db->prepare("
                INSERT INTO funding_items (project_id, no_urut, item_name, quantity, unit_price, total)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            foreach ($input['funding_items'] as $idx => $item) {
                if (empty($item['item_name'])) continue;
                $qty = max(1, (int)($item['quantity'] ?? 1));
                $price = cleanMoney($item['unit_price'] ?? 0);
                $total = cleanMoney($item['total'] ?? ($qty * (float)$price));
                $stmtItem->execute([$id, $idx + 1, $item['item_name'], $qty, $price, $total]);
            }
        }

        // Insert Simulation
        $sim = $input['simulation'] ?? [];
        $stmtSim = $db->prepare("
            INSERT INTO project_simulation (
                project_id, tenor_bulan, estimasi_return_persen, modal_kerja_bulanan_persen,
                minimum_investasi, maximum_investasi, default_investasi, notes
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmtSim->execute([
            $id,
            (int)($sim['tenor_bulan'] ?? 36),
            (float)($sim['estimasi_return_persen'] ?? 30.0),
            (float)($sim['modal_kerja_bulanan_persen'] ?? 2.2),
            cleanMoney($sim['minimum_investasi'] ?? 500000000),
            cleanMoney($sim['maximum_investasi'] ?? 500000000000),
            cleanMoney($sim['default_investasi'] ?? 500000000),
            $sim['notes'] ?? 'Simulasi bersifat ilustratif, bukan jaminan.'
        ]);

        $db->commit();

        logAdminActivity('create', 'projects', $id, "Admin created project '{$title}'");

        sendJsonResponse(['id' => $id], 201, "Proyek '{$title}' berhasil ditambahkan.");
    }

    // 3. PUT: Update Proyek Eksisting
    if ($method === 'PUT') {
        $input = getJsonInput();
        $id = trim($input['id'] ?? ($_GET['id'] ?? ''));

        if (empty($id)) {
            sendJsonError('ID Proyek wajib disertakan untuk update.');
        }

        $stmtCheck = $db->prepare("SELECT * FROM projects WHERE id = ?");
        $stmtCheck->execute([$id]);
        $existing = $stmtCheck->fetch();
        if (!$existing) {
            sendJsonError("Proyek '{$id}' tidak ditemukan.", 404);
        }

        $fundingTarget = cleanMoney($input['funding_target'] ?? ($existing['funding_target'] ?? '0'));
        $fundingCollected = cleanMoney($input['funding_collected'] ?? ($existing['funding_collected'] ?? '0'));

        $cityId = isset($input['city_id']) ? (!empty($input['city_id']) ? (int)$input['city_id'] : null) : ($existing['city_id'] ?? null);
        $cityName = trim($input['city'] ?? ($existing['city'] ?? ''));
        if ($cityId && empty($cityName)) {
            $stmtC = $db->prepare("SELECT name FROM cities WHERE id = ?");
            $stmtC->execute([$cityId]);
            $cityName = $stmtC->fetchColumn() ?: '';
        } elseif (!$cityId) {
            $cityName = null;
        }

        $targetDisplay = !empty($input['target_display']) 
            ? trim($input['target_display']) 
            : (!empty($existing['target_display']) ? $existing['target_display'] : ('Rp ' . number_format((float)$fundingTarget, 0, ',', '.')));

        $featured = isset($input['featured']) ? (!empty($input['featured']) ? 1 : 0) : (int)($existing['featured'] ?? 0);

        $db->beginTransaction();

        $stmtUp = $db->prepare("
            UPDATE projects SET
                city_id = ?, city = ?, title = ?, category = ?, image = ?, funding_collected = ?, funding_target = ?,
                status = ?, featured = ?, lokasi = ?, target_display = ?, tenor = ?,
                return_rate = ?, risk_level = ?, min_investment = ?, payout = ?,
                remaining_days = ?, asset_backed = ?
            WHERE id = ?
        ");
        $stmtUp->execute([
            $cityId,
            $cityName,
            $input['title'] ?? $existing['title'],
            $input['category'] ?? ($existing['category'] ?? 'Alat Berat & Infrastruktur'),
            $input['image'] ?? ($existing['image'] ?? 'assets/img/komatsu.jpg'),
            $fundingCollected,
            $fundingTarget,
            $input['status'] ?? ($existing['status'] ?? 'Open'),
            $featured,
            $input['lokasi'] ?? ($existing['lokasi'] ?? ''),
            $targetDisplay,
            $input['tenor'] ?? ($existing['tenor'] ?? '36 Bulan'),
            $input['return_rate'] ?? ($existing['return_rate'] ?? '≥30% (p.a.)'),
            $input['risk_level'] ?? ($existing['risk_level'] ?? 'Menengah - Terukur'),
            $input['min_investment'] ?? ($existing['min_investment'] ?? 'Rp 500.000.000'),
            $input['payout'] ?? ($existing['payout'] ?? 'Bagi Hasil Kompetitif'),
            $input['remaining_days'] ?? ($existing['remaining_days'] ?? '18 Hari Tersisa'),
            $input['asset_backed'] ?? ($existing['asset_backed'] ?? 'Unit CBU Grade A & BPKB'),
            $id
        ]);

        // Update Detail
        $stmtDetCheck = $db->prepare("SELECT * FROM project_details WHERE project_id = ?");
        $stmtDetCheck->execute([$id]);
        $existingDet = $stmtDetCheck->fetch();
        if ($existingDet) {
            $stmtDetUp = $db->prepare("
                UPDATE project_details SET
                    tagline = ?, what_will_provide_title = ?, what_will_provide_content = ?,
                    sinergi_title = ?, sinergi_content = ?, summary_title = ?, summary_content = ?
                WHERE project_id = ?
            ");
            $stmtDetUp->execute([
                $input['tagline'] ?? ($existingDet['tagline'] ?? ''),
                $input['what_will_provide_title'] ?? ($existingDet['what_will_provide_title'] ?? 'Alokasi Penggunaan Modal (Use of Funds & Asset Acquisition)'),
                $input['what_will_provide_content'] ?? ($existingDet['what_will_provide_content'] ?? ''),
                $input['sinergi_title'] ?? ($existingDet['sinergi_title'] ?? 'Struktur Kemitraan Strategis & Jaminan Penyerapan Pasar (Offtake Framework)'),
                $input['sinergi_content'] ?? ($existingDet['sinergi_content'] ?? ''),
                $input['summary_title'] ?? ($existingDet['summary_title'] ?? 'Ringkasan Kelayakan Investasi & Profil Risiko (Feasibility Summary)'),
                $input['summary_content'] ?? ($existingDet['summary_content'] ?? ''),
                $id
            ]);
        } else {
            $stmtDetIns = $db->prepare("
                INSERT INTO project_details (
                    project_id, tagline, what_will_provide_title, what_will_provide_content,
                    sinergi_title, sinergi_content, summary_title, summary_content
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmtDetIns->execute([
                $id,
                $input['tagline'] ?? '',
                $input['what_will_provide_title'] ?? 'Alokasi Penggunaan Modal (Use of Funds & Asset Acquisition)',
                $input['what_will_provide_content'] ?? '',
                $input['sinergi_title'] ?? 'Struktur Kemitraan Strategis & Jaminan Penyerapan Pasar (Offtake Framework)',
                $input['sinergi_content'] ?? '',
                $input['summary_title'] ?? 'Ringkasan Kelayakan Investasi & Profil Risiko (Feasibility Summary)',
                $input['summary_content'] ?? ''
            ]);
        }

        // Replace RAB (Funding items)
        if (isset($input['funding_items']) && is_array($input['funding_items'])) {
            $db->prepare("DELETE FROM funding_items WHERE project_id = ?")->execute([$id]);
            $stmtItem = $db->prepare("
                INSERT INTO funding_items (project_id, no_urut, item_name, quantity, unit_price, total)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            foreach ($input['funding_items'] as $idx => $item) {
                if (empty($item['item_name'])) continue;
                $qty = max(1, (int)($item['quantity'] ?? 1));
                $price = cleanMoney($item['unit_price'] ?? 0);
                $total = cleanMoney($item['total'] ?? ($qty * (float)$price));
                $stmtItem->execute([$id, $idx + 1, $item['item_name'], $qty, $price, $total]);
            }
        }

        // Update simulation
        if (isset($input['simulation']) && is_array($input['simulation'])) {
            $sim = $input['simulation'];
            $stmtSimUp = $db->prepare("
                INSERT INTO project_simulation (
                    project_id, tenor_bulan, estimasi_return_persen, modal_kerja_bulanan_persen,
                    minimum_investasi, maximum_investasi, default_investasi, notes
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    tenor_bulan = VALUES(tenor_bulan),
                    estimasi_return_persen = VALUES(estimasi_return_persen),
                    modal_kerja_bulanan_persen = VALUES(modal_kerja_bulanan_persen),
                    minimum_investasi = VALUES(minimum_investasi),
                    maximum_investasi = VALUES(maximum_investasi),
                    default_investasi = VALUES(default_investasi),
                    notes = VALUES(notes)
            ");
            $stmtSimUp->execute([
                $id,
                (int)($sim['tenor_bulan'] ?? 36),
                (float)($sim['estimasi_return_persen'] ?? 30.0),
                (float)($sim['modal_kerja_bulanan_persen'] ?? 2.2),
                cleanMoney($sim['minimum_investasi'] ?? 500000000),
                cleanMoney($sim['maximum_investasi'] ?? 500000000000),
                cleanMoney($sim['default_investasi'] ?? 500000000),
                $sim['notes'] ?? ''
            ]);
        }

        $db->commit();

        logAdminActivity('update', 'projects', $id, "Admin updated project '{$id}'");

        sendJsonResponse(['id' => $id], 200, "Proyek '{$id}' berhasil diperbarui.");
    }

    // 4. DELETE: Hapus Proyek
    if ($method === 'DELETE') {
        $id = trim($_GET['id'] ?? ($input['id'] ?? ''));
        if (empty($id)) {
            sendJsonError('ID Proyek wajib disertakan untuk penghapusan.');
        }

        $stmt = $db->prepare("SELECT title FROM projects WHERE id = ?");
        $stmt->execute([$id]);
        $proj = $stmt->fetch();
        if (!$proj) {
            sendJsonError("Proyek '{$id}' tidak ditemukan.", 404);
        }

        $stmtDel = $db->prepare("DELETE FROM projects WHERE id = ?");
        $stmtDel->execute([$id]);

        logAdminActivity('delete', 'projects', $id, "Admin deleted project '{$proj['title']}' ({$id})");

        sendJsonResponse(['id' => $id], 200, "Proyek '{$proj['title']}' berhasil dihapus.");
    }

    sendJsonError('Method tidak didukung.', 405);

} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    sendJsonError('Terjadi kesalahan operasi proyek: ' . $e->getMessage(), 500);
}

/**
 * Auto-migration & seed for projects tables
 */
function ensureProjectsSchema(PDO $db): void {
    try {
        $db->exec("
            CREATE TABLE IF NOT EXISTS `projects` (
              `id` VARCHAR(50) PRIMARY KEY,
              `title` VARCHAR(255) NOT NULL,
              `category` VARCHAR(100) NOT NULL,
              `image` VARCHAR(255) NOT NULL DEFAULT 'assets/img/komatsu.jpg',
              `funding_collected` BIGINT NOT NULL DEFAULT 0,
              `funding_target` BIGINT NOT NULL DEFAULT 0,
              `currency` VARCHAR(10) NOT NULL DEFAULT 'IDR',
              `status` VARCHAR(50) NOT NULL DEFAULT 'Open',
              `featured` TINYINT(1) NOT NULL DEFAULT 0,
              `lokasi` VARCHAR(255) NOT NULL,
              `target_display` VARCHAR(100) NULL,
              `tenor` VARCHAR(50) NOT NULL DEFAULT '36 Bulan',
              `return_rate` VARCHAR(50) NOT NULL DEFAULT '≥30% (p.a.)',
              `risk_level` VARCHAR(100) NOT NULL,
              `min_investment` VARCHAR(100) NOT NULL DEFAULT 'Rp 500.000.000',
              `payout` VARCHAR(100) NOT NULL DEFAULT 'Bagi Hasil Kompetitif',
              `remaining_days` VARCHAR(100) NOT NULL DEFAULT '18 Hari Tersisa',
              `asset_backed` VARCHAR(255) NOT NULL DEFAULT 'Unit CBU Grade A & BPKB',
              `sort_order` INT NOT NULL DEFAULT 0,
              `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
              `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        $db->exec("
            CREATE TABLE IF NOT EXISTS `project_details` (
              `id` INT AUTO_INCREMENT PRIMARY KEY,
              `project_id` VARCHAR(50) NOT NULL,
              `tagline` VARCHAR(255) NULL,
              `what_will_provide_title` VARCHAR(255) NOT NULL DEFAULT 'Alokasi Penggunaan Modal (Use of Funds & Asset Acquisition)',
              `what_will_provide_content` TEXT NOT NULL,
              `sinergi_title` VARCHAR(255) NOT NULL DEFAULT 'Struktur Kemitraan Strategis & Jaminan Penyerapan Pasar (Offtake Framework)',
              `sinergi_content` TEXT NOT NULL,
              `summary_title` VARCHAR(255) NOT NULL DEFAULT 'Ringkasan Kelayakan Investasi & Profil Risiko (Feasibility Summary)',
              `summary_content` TEXT NOT NULL,
              `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
              `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              CONSTRAINT `fk_project_detail` FOREIGN KEY (`project_id`) 
                REFERENCES `projects`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        $db->exec("
            CREATE TABLE IF NOT EXISTS `funding_items` (
              `id` INT AUTO_INCREMENT PRIMARY KEY,
              `project_id` VARCHAR(50) NOT NULL,
              `no_urut` INT NOT NULL DEFAULT 1,
              `item_name` VARCHAR(255) NOT NULL,
              `quantity` INT NOT NULL DEFAULT 1,
              `unit_price` BIGINT NOT NULL DEFAULT 0,
              `total` BIGINT NOT NULL DEFAULT 0,
              `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
              CONSTRAINT `fk_funding_item_project` FOREIGN KEY (`project_id`) 
                REFERENCES `projects`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        $db->exec("
            CREATE TABLE IF NOT EXISTS `project_simulation` (
              `project_id` VARCHAR(50) PRIMARY KEY,
              `tenor_bulan` INT NOT NULL DEFAULT 36,
              `estimasi_return_persen` DECIMAL(5,2) NOT NULL DEFAULT 30.00,
              `modal_kerja_bulanan_persen` DECIMAL(5,2) NOT NULL DEFAULT 2.20,
              `minimum_investasi` BIGINT NOT NULL DEFAULT 500000000,
              `maximum_investasi` BIGINT NOT NULL DEFAULT 500000000000,
              `default_investasi` BIGINT NOT NULL DEFAULT 500000000,
              `notes` TEXT NULL,
              `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              CONSTRAINT `fk_simulation_project` FOREIGN KEY (`project_id`) 
                REFERENCES `projects`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // If table projects is completely empty, populate from data/projects.json
        $count = $db->query("SELECT COUNT(*) FROM projects")->fetchColumn();
        if ((int)$count === 0) {
            $jsonFile = __DIR__ . '/../../data/projects.json';
            if (file_exists($jsonFile)) {
                $raw = json_decode(file_get_contents($jsonFile), true);
                $projectsList = $raw['projects'] ?? [];
                $insP = $db->prepare("
                    INSERT INTO projects (
                        id, title, category, image, funding_collected, funding_target,
                        currency, status, featured, lokasi, target_display, tenor,
                        return_rate, risk_level, min_investment, payout, remaining_days, asset_backed, sort_order
                    ) VALUES (?, ?, ?, ?, ?, ?, 'IDR', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $insD = $db->prepare("
                    INSERT INTO project_details (
                        project_id, tagline, what_will_provide_title, what_will_provide_content,
                        sinergi_title, sinergi_content, summary_title, summary_content
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $insF = $db->prepare("
                    INSERT INTO funding_items (project_id, no_urut, item_name, quantity, unit_price, total)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $insS = $db->prepare("
                    INSERT INTO project_simulation (
                        project_id, tenor_bulan, estimasi_return_persen, modal_kerja_bulanan_persen,
                        minimum_investasi, maximum_investasi, default_investasi, notes
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");

                foreach ($projectsList as $idx => $p) {
                    $pid = $p['id'];
                    $info = $p['info'] ?? [];
                    $fnd = $p['funding'] ?? [];
                    $insP->execute([
                        $pid,
                        $p['title'],
                        $p['category'] ?? 'Alat Berat & Infrastruktur',
                        $p['image'] ?? 'assets/img/komatsu.jpg',
                        (int)($fnd['collected'] ?? 0),
                        (int)($fnd['target'] ?? 0),
                        $p['status'] ?? 'Open',
                        !empty($p['featured']) ? 1 : 0,
                        $info['lokasi'] ?? '',
                        $info['target'] ?? '',
                        $info['tenor'] ?? '36 Bulan',
                        $info['return'] ?? '≥30% (p.a.)',
                        'Menengah - Terukur',
                        $info['min_investment'] ?? 'Rp 500.000.000',
                        $info['payout'] ?? 'Bagi Hasil Kompetitif',
                        $info['remaining_days'] ?? '30 Hari Tersisa',
                        $info['asset_backed'] ?? 'Unit CBU Grade A & BPKB',
                        $idx + 1
                    ]);

                    $det = $p['detail'] ?? [];
                    $wwp = $det['what_will_provide'] ?? [];
                    $sin = $det['sinergi_mitra'] ?? ($det['sinergi'] ?? []);
                    $sum = $det['summary'] ?? [];
                    $insD->execute([
                        $pid,
                        $det['tagline'] ?? '',
                        $wwp['title'] ?? 'Alokasi Penggunaan Modal',
                        $wwp['content'] ?? '',
                        $sin['title'] ?? 'Struktur Kemitraan',
                        $sin['content'] ?? '',
                        $sum['title'] ?? 'Ringkasan Kelayakan',
                        $sum['content'] ?? ''
                    ]);

                    $rows = $det['funding_target']['rows'] ?? [];
                    foreach ($rows as $rIdx => $r) {
                        $insF->execute([
                            $pid,
                            $rIdx + 1,
                            $r['item'] ?? 'Unit Item',
                            (int)($r['quantity'] ?? 1),
                            (int)($r['unit_price'] ?? 0),
                            (int)($r['total'] ?? 0)
                        ]);
                    }

                    $sim = $det['simulation'] ?? [];
                    $insS->execute([
                        $pid,
                        (int)($sim['tenor_bulan'] ?? 36),
                        (float)($sim['estimasi_return_persen'] ?? 30.00),
                        (float)($sim['modal_kerja_bulanan_persen'] ?? 2.20),
                        (int)($sim['minimum_investasi'] ?? 500000000),
                        (int)($sim['maximum_investasi'] ?? 500000000000),
                        (int)($sim['default_investasi'] ?? 500000000),
                        $sim['notes'] ?? 'Simulasi bersifat indikatif.'
                    ]);
                }
            }
        }
    } catch (Exception $e) {
        error_log("[CMS Ensure Projects Schema Error] " . $e->getMessage());
    }
}
