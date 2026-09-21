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

// Helper to sanitize BIGINT money string
function cleanMoney(mixed $val): string {
    if (is_null($val)) return '0';
    $str = preg_replace('/[^0-9]/', '', (string)$val);
    return empty($str) ? '0' : $str;
}

try {
    $db = getDB();

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
                   COALESCE(sub.item_count, 0) as item_count,
                   CASE 
                       WHEN p.funding_target > 0 THEN ROUND((p.funding_collected / p.funding_target * 100), 2)
                       ELSE 0 
                   END as funding_percent
            FROM projects p
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

        $db->beginTransaction();

        $stmtIns = $db->prepare("
            INSERT INTO projects (
                id, title, category, image, funding_collected, funding_target,
                currency, status, featured, lokasi, target_display, tenor,
                return_rate, risk_level, min_investment, payout, remaining_days, asset_backed
            ) VALUES (?, ?, ?, ?, ?, ?, 'IDR', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmtIns->execute([
            $id,
            $title,
            $input['category'] ?? 'Alat Berat & Infrastruktur',
            $input['image'] ?? 'assets/img/komatsu.jpg',
            $fundingCollected,
            $fundingTarget,
            $input['status'] ?? 'Open',
            !empty($input['featured']) ? 1 : 0,
            $input['lokasi'] ?? '',
            $input['target_display'] ?? '',
            $input['tenor'] ?? '36 Bulan',
            $input['return_rate'] ?? '≥30% (p.a.)',
            $input['risk_level'] ?? 'Menengah - Terukur',
            $input['min_investment'] ?? 'Rp 500.000.000',
            $input['payout'] ?? 'Bagi Hasil Kuartalan',
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

        $stmtCheck = $db->prepare("SELECT id, title FROM projects WHERE id = ?");
        $stmtCheck->execute([$id]);
        $existing = $stmtCheck->fetch();
        if (!$existing) {
            sendJsonError("Proyek '{$id}' tidak ditemukan.", 404);
        }

        $fundingTarget = cleanMoney($input['funding_target'] ?? '0');
        $fundingCollected = cleanMoney($input['funding_collected'] ?? '0');

        $db->beginTransaction();

        $stmtUp = $db->prepare("
            UPDATE projects SET
                title = ?, category = ?, image = ?, funding_collected = ?, funding_target = ?,
                status = ?, featured = ?, lokasi = ?, target_display = ?, tenor = ?,
                return_rate = ?, risk_level = ?, min_investment = ?, payout = ?,
                remaining_days = ?, asset_backed = ?
            WHERE id = ?
        ");
        $stmtUp->execute([
            $input['title'] ?? $existing['title'],
            $input['category'] ?? 'Alat Berat & Infrastruktur',
            $input['image'] ?? 'assets/img/komatsu.jpg',
            $fundingCollected,
            $fundingTarget,
            $input['status'] ?? 'Open',
            !empty($input['featured']) ? 1 : 0,
            $input['lokasi'] ?? '',
            $input['target_display'] ?? '',
            $input['tenor'] ?? '36 Bulan',
            $input['return_rate'] ?? '≥30% (p.a.)',
            $input['risk_level'] ?? 'Menengah - Terukur',
            $input['min_investment'] ?? 'Rp 500.000.000',
            $input['payout'] ?? 'Bagi Hasil Kuartalan',
            $input['remaining_days'] ?? '18 Hari Tersisa',
            $input['asset_backed'] ?? 'Unit CBU Grade A & BPKB',
            $id
        ]);

        // Update Detail
        $stmtDetCheck = $db->prepare("SELECT id FROM project_details WHERE project_id = ?");
        $stmtDetCheck->execute([$id]);
        if ($stmtDetCheck->fetch()) {
            $stmtDetUp = $db->prepare("
                UPDATE project_details SET
                    tagline = ?, what_will_provide_title = ?, what_will_provide_content = ?,
                    sinergi_title = ?, sinergi_content = ?, summary_title = ?, summary_content = ?
                WHERE project_id = ?
            ");
            $stmtDetUp->execute([
                $input['tagline'] ?? '',
                $input['what_will_provide_title'] ?? 'Alokasi Penggunaan Modal',
                $input['what_will_provide_content'] ?? '',
                $input['sinergi_title'] ?? 'Struktur Kemitraan Strategis',
                $input['sinergi_content'] ?? '',
                $input['summary_title'] ?? 'Ringkasan Kelayakan Investasi',
                $input['summary_content'] ?? '',
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
                $input['what_will_provide_title'] ?? 'Alokasi Penggunaan Modal',
                $input['what_will_provide_content'] ?? '',
                $input['sinergi_title'] ?? 'Struktur Kemitraan Strategis',
                $input['sinergi_content'] ?? '',
                $input['summary_title'] ?? 'Ringkasan Kelayakan Investasi',
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
        $id = trim($_GET['id'] ?? '');
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
