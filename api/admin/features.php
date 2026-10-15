<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — API: ADMIN FEATURES & VALUE PROPOSITIONS CRUD
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

    // 1. GET: Ambil daftar features atau single feature
    if ($method === 'GET') {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
        if ($id) {
            $stmt = $db->prepare("SELECT * FROM features WHERE id = ?");
            $stmt->execute([$id]);
            $feat = $stmt->fetch();
            if (!$feat) {
                sendJsonError('Feature tidak ditemukan.', 404);
            }
            sendJsonResponse($feat);
        }

        $category = isset($_GET['category']) ? trim($_GET['category']) : '';
        if ($category !== '') {
            $stmt = $db->prepare("SELECT * FROM features WHERE category = ? ORDER BY sort_order ASC, id ASC");
            $stmt->execute([$category]);
        } else {
            $stmt = $db->query("SELECT * FROM features ORDER BY category ASC, sort_order ASC, id ASC");
        }
        $features = $stmt->fetchAll();
        sendJsonResponse($features);
    }

    // 2. POST: Tambah feature baru
    if ($method === 'POST') {
        $category = trim($input['category'] ?? 'value_prop');
        $validCategories = ['value_prop', 'pilar', 'layanan', 'keunggulan'];
        if (!in_array($category, $validCategories)) {
            $category = 'value_prop';
        }

        $itemNumber = trim($input['item_number'] ?? '');
        $title = trim($input['title'] ?? '');
        $subtitle = trim($input['subtitle'] ?? '');
        $description = trim($input['description'] ?? '');
        $icon = trim($input['icon'] ?? '');
        $imageUrl = trim($input['image_url'] ?? '');
        $badgeText = trim($input['badge_text'] ?? '');
        $accentColor = trim($input['accent_color'] ?? '#1D3589');
        $sortOrder = isset($input['sort_order']) ? (int)$input['sort_order'] : 0;
        $isActive = isset($input['is_active']) ? (int)$input['is_active'] : 1;

        if (empty($title) || empty($description)) {
            sendJsonError('Judul fitur dan deskripsi wajib diisi.');
        }

        $stmt = $db->prepare("
            INSERT INTO features (
                category, item_number, title, subtitle, description,
                icon, image_url, badge_text, accent_color, sort_order, is_active
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $category, $itemNumber, $title, $subtitle, $description,
            $icon, $imageUrl, $badgeText, $accentColor, $sortOrder, $isActive
        ]);
        $id = (int)$db->lastInsertId();

        logAdminActivity('create', 'features', (string)$id, "Admin menambah fitur ({$category}): {$title}");
        sendJsonResponse(['id' => $id], 201, 'Fitur berhasil ditambahkan.');
    }

    // 3. PUT: Update feature atau toggle status
    if ($method === 'PUT') {
        $id = (int)($input['id'] ?? ($_GET['id'] ?? 0));
        if (!$id) {
            sendJsonError('ID fitur tidak valid.');
        }

        // Action khusus: toggle active
        if (isset($input['action']) && $input['action'] === 'toggle_status') {
            $stmt = $db->prepare("UPDATE features SET is_active = NOT is_active WHERE id = ?");
            $stmt->execute([$id]);
            logAdminActivity('update', 'features', (string)$id, "Admin mengubah status aktif fitur #{$id}");
            sendJsonResponse(null, 200, 'Status publikasi fitur berhasil diperbarui.');
        }

        $category = trim($input['category'] ?? 'value_prop');
        $itemNumber = trim($input['item_number'] ?? '');
        $title = trim($input['title'] ?? '');
        $subtitle = trim($input['subtitle'] ?? '');
        $description = trim($input['description'] ?? '');
        $icon = trim($input['icon'] ?? '');
        $imageUrl = trim($input['image_url'] ?? '');
        $badgeText = trim($input['badge_text'] ?? '');
        $accentColor = trim($input['accent_color'] ?? '#1D3589');
        $sortOrder = isset($input['sort_order']) ? (int)$input['sort_order'] : 0;
        $isActive = isset($input['is_active']) ? (int)$input['is_active'] : 1;

        if (empty($title) || empty($description)) {
            sendJsonError('Judul fitur dan deskripsi wajib diisi.');
        }

        $stmt = $db->prepare("
            UPDATE features SET
                category = ?, item_number = ?, title = ?, subtitle = ?, description = ?,
                icon = ?, image_url = ?, badge_text = ?, accent_color = ?,
                sort_order = ?, is_active = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $category, $itemNumber, $title, $subtitle, $description,
            $icon, $imageUrl, $badgeText, $accentColor,
            $sortOrder, $isActive, $id
        ]);

        logAdminActivity('update', 'features', (string)$id, "Admin memperbarui fitur: {$title}");
        sendJsonResponse(['id' => $id], 200, 'Fitur berhasil diperbarui.');
    }

    // 4. DELETE: Hapus feature
    if ($method === 'DELETE') {
        $id = (int)($input['id'] ?? ($_GET['id'] ?? 0));
        if (!$id) {
            sendJsonError('ID fitur tidak valid.');
        }

        $stmt = $db->prepare("DELETE FROM features WHERE id = ?");
        $stmt->execute([$id]);

        logAdminActivity('delete', 'features', (string)$id, "Admin menghapus fitur #{$id}");
        sendJsonResponse(null, 200, 'Fitur berhasil dihapus.');
    }

    sendJsonError('Metode HTTP tidak didukung.', 405);

} catch (Throwable $e) {
    sendJsonError('Operasi gagal: ' . $e->getMessage(), 500);
}
