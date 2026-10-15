<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — API: ADMIN TESTIMONIALS CRUD
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

    // 1. GET: Ambil semua testimoni atau single testimoni by ID
    if ($method === 'GET') {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
        if ($id) {
            $stmt = $db->prepare("SELECT * FROM testimonials WHERE id = ?");
            $stmt->execute([$id]);
            $item = $stmt->fetch();
            if (!$item) {
                sendJsonError('Testimoni tidak ditemukan.', 404);
            }
            sendJsonResponse($item);
        }

        $category = isset($_GET['category']) ? trim($_GET['category']) : '';
        if ($category !== '') {
            $stmt = $db->prepare("SELECT * FROM testimonials WHERE category = ? ORDER BY sort_order ASC, id DESC");
            $stmt->execute([$category]);
        } else {
            $stmt = $db->query("SELECT * FROM testimonials ORDER BY sort_order ASC, id DESC");
        }
        $testimonials = $stmt->fetchAll();
        sendJsonResponse($testimonials);
    }

    // 2. POST: Tambah testimoni baru
    if ($method === 'POST') {
        $name = trim($input['name'] ?? '');
        $roleTitle = trim($input['role_title'] ?? '');
        $companyName = trim($input['company_name'] ?? '');
        $avatarUrl = trim($input['avatar_url'] ?? '');
        $rating = isset($input['rating']) ? max(1, min(5, (int)$input['rating'])) : 5;
        $content = trim($input['content'] ?? '');
        $projectName = trim($input['project_name'] ?? '');
        $category = in_array($input['category'] ?? '', ['investor', 'kontraktor', 'partner']) ? $input['category'] : 'investor';
        $isFeatured = isset($input['is_featured']) ? (int)$input['is_featured'] : 1;
        $isActive = isset($input['is_active']) ? (int)$input['is_active'] : 1;
        $sortOrder = isset($input['sort_order']) ? (int)$input['sort_order'] : 0;

        if (empty($name) || empty($roleTitle) || empty($content)) {
            sendJsonError('Nama, jabatan/peran, dan isi ulasan testimoni wajib diisi.');
        }

        $stmt = $db->prepare("
            INSERT INTO testimonials (
                name, role_title, company_name, avatar_url, rating,
                content, project_name, category, is_featured, is_active, sort_order
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $name, $roleTitle, $companyName, $avatarUrl, $rating,
            $content, $projectName, $category, $isFeatured, $isActive, $sortOrder
        ]);
        $id = (int)$db->lastInsertId();

        logAdminActivity('create', 'testimonials', (string)$id, "Admin menambah testimoni dari: {$name}");
        sendJsonResponse(['id' => $id], 201, 'Testimoni berhasil ditambahkan.');
    }

    // 3. PUT: Update testimoni atau toggle status
    if ($method === 'PUT') {
        $id = (int)($input['id'] ?? ($_GET['id'] ?? 0));
        if (!$id) {
            sendJsonError('ID testimoni tidak valid.');
        }

        // Action khusus: toggle active
        if (isset($input['action']) && $input['action'] === 'toggle_status') {
            $stmt = $db->prepare("UPDATE testimonials SET is_active = NOT is_active WHERE id = ?");
            $stmt->execute([$id]);
            logAdminActivity('update', 'testimonials', (string)$id, "Admin toggle status testimoni #{$id}");
            sendJsonResponse(null, 200, 'Status testimoni berhasil diubah.');
        }

        // Action khusus: toggle featured
        if (isset($input['action']) && $input['action'] === 'toggle_featured') {
            $stmt = $db->prepare("UPDATE testimonials SET is_featured = NOT is_featured WHERE id = ?");
            $stmt->execute([$id]);
            logAdminActivity('update', 'testimonials', (string)$id, "Admin toggle featured testimoni #{$id}");
            sendJsonResponse(null, 200, 'Status unggulan testimoni berhasil diubah.');
        }

        $name = trim($input['name'] ?? '');
        $roleTitle = trim($input['role_title'] ?? '');
        $companyName = trim($input['company_name'] ?? '');
        $avatarUrl = trim($input['avatar_url'] ?? '');
        $rating = isset($input['rating']) ? max(1, min(5, (int)$input['rating'])) : 5;
        $content = trim($input['content'] ?? '');
        $projectName = trim($input['project_name'] ?? '');
        $category = in_array($input['category'] ?? '', ['investor', 'kontraktor', 'partner']) ? $input['category'] : 'investor';
        $isFeatured = isset($input['is_featured']) ? (int)$input['is_featured'] : 1;
        $isActive = isset($input['is_active']) ? (int)$input['is_active'] : 1;
        $sortOrder = isset($input['sort_order']) ? (int)$input['sort_order'] : 0;

        if (empty($name) || empty($roleTitle) || empty($content)) {
            sendJsonError('Nama, jabatan/peran, dan isi ulasan testimoni wajib diisi.');
        }

        $stmt = $db->prepare("
            UPDATE testimonials SET
                name = ?, role_title = ?, company_name = ?, avatar_url = ?,
                rating = ?, content = ?, project_name = ?, category = ?,
                is_featured = ?, is_active = ?, sort_order = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $name, $roleTitle, $companyName, $avatarUrl,
            $rating, $content, $projectName, $category,
            $isFeatured, $isActive, $sortOrder, $id
        ]);

        logAdminActivity('update', 'testimonials', (string)$id, "Admin memperbarui testimoni: {$name}");
        sendJsonResponse(['id' => $id], 200, 'Testimoni berhasil diperbarui.');
    }

    // 4. DELETE: Hapus testimoni
    if ($method === 'DELETE') {
        $id = (int)($input['id'] ?? ($_GET['id'] ?? 0));
        if (!$id) {
            sendJsonError('ID testimoni tidak valid.');
        }

        $stmt = $db->prepare("DELETE FROM testimonials WHERE id = ?");
        $stmt->execute([$id]);

        logAdminActivity('delete', 'testimonials', (string)$id, "Admin menghapus testimoni #{$id}");
        sendJsonResponse(null, 200, 'Testimoni berhasil dihapus.');
    }

    sendJsonError('Metode HTTP tidak didukung.', 405);

} catch (Throwable $e) {
    sendJsonError('Operasi gagal: ' . $e->getMessage(), 500);
}
