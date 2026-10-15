<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — API: ADMIN PRICING TIERS CRUD
 * Methods: GET, POST, PUT, DELETE
 */

require_once __DIR__ . '/../../backend/config/db.php';
require_once __DIR__ . '/../../backend/helpers/response.php';
require_once __DIR__ . '/../../backend/helpers/auth_helper.php';

$admin = requireAdminAuth();
$method = $_SERVER['REQUEST_METHOD'];
$input = getJsonInput();

if (isset($input['_method'])) {
    $method = strtoupper($input['_method']);
} elseif (isset($_GET['_method'])) {
    $method = strtoupper($_GET['_method']);
}

try {
    $db = getDB();

    // 1. GET: Ambil semua tiers atau single tier by ID
    if ($method === 'GET') {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
        if ($id) {
            $stmt = $db->prepare("SELECT * FROM pricing_tiers WHERE id = ?");
            $stmt->execute([$id]);
            $tier = $stmt->fetch();
            if (!$tier) {
                sendJsonError('Paket investasi tidak ditemukan.', 404);
            }
            sendJsonResponse($tier);
        }

        $projectId = isset($_GET['project_id']) ? trim($_GET['project_id']) : '';
        if ($projectId !== '') {
            $stmt = $db->prepare("SELECT * FROM pricing_tiers WHERE project_id = ? OR project_id IS NULL ORDER BY sort_order ASC, id ASC");
            $stmt->execute([$projectId]);
        } else {
            $stmt = $db->query("SELECT * FROM pricing_tiers ORDER BY sort_order ASC, id ASC");
        }
        $tiers = $stmt->fetchAll();
        sendJsonResponse($tiers);
    }

    // 2. POST: Tambah paket baru
    if ($method === 'POST') {
        $projectId = !empty($input['project_id']) ? trim($input['project_id']) : null;
        $name = trim($input['name'] ?? '');
        $badge = trim($input['badge'] ?? '');
        $nominal = isset($input['nominal']) ? (int)$input['nominal'] : 0;
        $nominalDisplay = trim($input['nominal_display'] ?? '');
        if (empty($nominalDisplay) && $nominal > 0) {
            $nominalDisplay = 'Rp ' . number_format($nominal, 0, ',', '.');
        }
        $unitQty = isset($input['unit_qty']) ? (int)$input['unit_qty'] : 1;
        $containerQty = trim($input['container_qty'] ?? '');
        $porsiProyek = trim($input['porsi_proyek'] ?? '');
        $jaminanAset = trim($input['jaminan_aset'] ?? '');
        $operatorPerk = trim($input['operator_perk'] ?? '');
        $deskripsi = trim($input['deskripsi'] ?? '');
        $profitPerSiklus = isset($input['profit_per_siklus']) ? (int)$input['profit_per_siklus'] : 0;
        $profit2xTahunan = isset($input['profit_2x_tahunan']) ? (int)$input['profit_2x_tahunan'] : 0;
        $profit3xTahunan = isset($input['profit_3x_tahunan']) ? (int)$input['profit_3x_tahunan'] : 0;
        $isFeatured = isset($input['is_featured']) ? (int)$input['is_featured'] : 0;
        $isActive = isset($input['is_active']) ? (int)$input['is_active'] : 1;
        $sortOrder = isset($input['sort_order']) ? (int)$input['sort_order'] : 0;

        if (empty($name) || empty($deskripsi) || $nominal <= 0) {
            sendJsonError('Nama paket, nominal investasi, dan deskripsi wajib diisi.');
        }

        $stmt = $db->prepare("
            INSERT INTO pricing_tiers (
                project_id, name, badge, nominal, nominal_display,
                unit_qty, container_qty, porsi_proyek, jaminan_aset, operator_perk,
                deskripsi, profit_per_siklus, profit_2x_tahunan, profit_3x_tahunan,
                is_featured, is_active, sort_order
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $projectId, $name, $badge, $nominal, $nominalDisplay,
            $unitQty, $containerQty, $porsiProyek, $jaminanAset, $operatorPerk,
            $deskripsi, $profitPerSiklus, $profit2xTahunan, $profit3xTahunan,
            $isFeatured, $isActive, $sortOrder
        ]);
        $id = (int)$db->lastInsertId();

        logAdminActivity('create', 'pricing_tiers', (string)$id, "Admin menambah paket investasi: {$name}");
        sendJsonResponse(['id' => $id], 201, 'Paket investasi berhasil ditambahkan.');
    }

    // 3. PUT: Update paket atau toggle status
    if ($method === 'PUT') {
        $id = (int)($input['id'] ?? ($_GET['id'] ?? 0));
        if (!$id) {
            sendJsonError('ID paket tidak valid.');
        }

        // Action khusus: toggle active
        if (isset($input['action']) && $input['action'] === 'toggle_status') {
            $stmt = $db->prepare("UPDATE pricing_tiers SET is_active = NOT is_active WHERE id = ?");
            $stmt->execute([$id]);
            logAdminActivity('update', 'pricing_tiers', (string)$id, "Admin toggle status paket #{$id}");
            sendJsonResponse(null, 200, 'Status paket berhasil diubah.');
        }

        // Action khusus: toggle featured
        if (isset($input['action']) && $input['action'] === 'toggle_featured') {
            $stmt = $db->prepare("UPDATE pricing_tiers SET is_featured = NOT is_featured WHERE id = ?");
            $stmt->execute([$id]);
            logAdminActivity('update', 'pricing_tiers', (string)$id, "Admin toggle featured paket #{$id}");
            sendJsonResponse(null, 200, 'Status unggulan berhasil diubah.');
        }

        $projectId = !empty($input['project_id']) ? trim($input['project_id']) : null;
        $name = trim($input['name'] ?? '');
        $badge = trim($input['badge'] ?? '');
        $nominal = isset($input['nominal']) ? (int)$input['nominal'] : 0;
        $nominalDisplay = trim($input['nominal_display'] ?? '');
        if (empty($nominalDisplay) && $nominal > 0) {
            $nominalDisplay = 'Rp ' . number_format($nominal, 0, ',', '.');
        }
        $unitQty = isset($input['unit_qty']) ? (int)$input['unit_qty'] : 1;
        $containerQty = trim($input['container_qty'] ?? '');
        $porsiProyek = trim($input['porsi_proyek'] ?? '');
        $jaminanAset = trim($input['jaminan_aset'] ?? '');
        $operatorPerk = trim($input['operator_perk'] ?? '');
        $deskripsi = trim($input['deskripsi'] ?? '');
        $profitPerSiklus = isset($input['profit_per_siklus']) ? (int)$input['profit_per_siklus'] : 0;
        $profit2xTahunan = isset($input['profit_2x_tahunan']) ? (int)$input['profit_2x_tahunan'] : 0;
        $profit3xTahunan = isset($input['profit_3x_tahunan']) ? (int)$input['profit_3x_tahunan'] : 0;
        $isFeatured = isset($input['is_featured']) ? (int)$input['is_featured'] : 0;
        $isActive = isset($input['is_active']) ? (int)$input['is_active'] : 1;
        $sortOrder = isset($input['sort_order']) ? (int)$input['sort_order'] : 0;

        if (empty($name) || empty($deskripsi) || $nominal <= 0) {
            sendJsonError('Nama paket, nominal investasi, dan deskripsi wajib diisi.');
        }

        $stmt = $db->prepare("
            UPDATE pricing_tiers SET
                project_id = ?, name = ?, badge = ?, nominal = ?, nominal_display = ?,
                unit_qty = ?, container_qty = ?, porsi_proyek = ?, jaminan_aset = ?,
                operator_perk = ?, deskripsi = ?, profit_per_siklus = ?,
                profit_2x_tahunan = ?, profit_3x_tahunan = ?,
                is_featured = ?, is_active = ?, sort_order = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $projectId, $name, $badge, $nominal, $nominalDisplay,
            $unitQty, $containerQty, $porsiProyek, $jaminanAset,
            $operatorPerk, $deskripsi, $profitPerSiklus,
            $profit2xTahunan, $profit3xTahunan,
            $isFeatured, $isActive, $sortOrder, $id
        ]);

        logAdminActivity('update', 'pricing_tiers', (string)$id, "Admin memperbarui paket investasi: {$name}");
        sendJsonResponse(['id' => $id], 200, 'Paket investasi berhasil diperbarui.');
    }

    // 4. DELETE: Hapus paket
    if ($method === 'DELETE') {
        $id = (int)($input['id'] ?? ($_GET['id'] ?? 0));
        if (!$id) {
            sendJsonError('ID paket tidak valid.');
        }

        $stmt = $db->prepare("DELETE FROM pricing_tiers WHERE id = ?");
        $stmt->execute([$id]);

        logAdminActivity('delete', 'pricing_tiers', (string)$id, "Admin menghapus paket investasi #{$id}");
        sendJsonResponse(null, 200, 'Paket investasi berhasil dihapus.');
    }

    sendJsonError('Metode HTTP tidak didukung.', 405);

} catch (Throwable $e) {
    sendJsonError('Operasi gagal: ' . $e->getMessage(), 500);
}
