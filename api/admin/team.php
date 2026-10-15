<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — API: ADMIN TEAM MEMBERS CRUD
 * Methods: GET, POST, PUT, DELETE
 */

require_once __DIR__ . '/../../backend/config/db.php';
require_once __DIR__ . '/../../backend/helpers/response.php';
require_once __DIR__ . '/../../backend/helpers/auth_helper.php';
require_once __DIR__ . '/../../backend/helpers/realtime_helper.php';

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

    // 1. GET: Ambil semua anggota tim atau single by ID
    if ($method === 'GET') {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
        if ($id) {
            $stmt = $db->prepare("SELECT * FROM team_members WHERE id = ?");
            $stmt->execute([$id]);
            $member = $stmt->fetch();
            if (!$member) {
                sendJsonError('Anggota tim tidak ditemukan.', 404);
            }
            sendJsonResponse($member);
        }

        $dept = isset($_GET['department']) ? trim($_GET['department']) : '';
        if ($dept !== '') {
            $stmt = $db->prepare("SELECT * FROM team_members WHERE department = ? ORDER BY sort_order ASC, id ASC");
            $stmt->execute([$dept]);
        } else {
            $stmt = $db->query("SELECT * FROM team_members ORDER BY sort_order ASC, id ASC");
        }
        $members = $stmt->fetchAll();
        sendJsonResponse($members);
    }

    // 2. POST: Tambah anggota tim baru
    if ($method === 'POST') {
        $name = trim($input['name'] ?? '');
        $roleTitle = trim($input['role_title'] ?? '');
        $department = trim($input['department'] ?? 'Direksi & Manajemen');
        $photoUrl = trim($input['photo_url'] ?? '');
        $bio = trim($input['bio'] ?? '');
        $linkedinUrl = trim($input['linkedin_url'] ?? '');
        $sortOrder = isset($input['sort_order']) ? (int)$input['sort_order'] : 0;
        $isActive = isset($input['is_active']) ? (int)$input['is_active'] : 1;

        if (empty($name) || empty($roleTitle)) {
            sendJsonError('Nama dan jabatan anggota tim wajib diisi.');
        }

        $stmt = $db->prepare("
            INSERT INTO team_members (name, role_title, department, photo_url, bio, linkedin_url, sort_order, is_active)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$name, $roleTitle, $department, $photoUrl, $bio, $linkedinUrl, $sortOrder, $isActive]);
        $id = (int)$db->lastInsertId();

        logAdminActivity('create', 'team_members', (string)$id, "Admin menambah anggota tim: {$name}");
        triggerRealtimeEvent('content_updated', 'team', 'create', ['id' => $id]);
        sendJsonResponse(['id' => $id], 201, 'Anggota tim berhasil ditambahkan.');
    }

    // 3. PUT: Update anggota tim atau toggle status
    if ($method === 'PUT') {
        $id = (int)($input['id'] ?? ($_GET['id'] ?? 0));
        if (!$id) {
            sendJsonError('ID anggota tim tidak valid.');
        }

        // Action khusus: toggle active
        if (isset($input['action']) && $input['action'] === 'toggle_status') {
            $stmt = $db->prepare("UPDATE team_members SET is_active = NOT is_active WHERE id = ?");
            $stmt->execute([$id]);
            logAdminActivity('update', 'team_members', (string)$id, "Admin toggle status anggota tim #{$id}");
            triggerRealtimeEvent('content_updated', 'team', 'update', ['id' => $id]);
            sendJsonResponse(null, 200, 'Status anggota tim berhasil diubah.');
        }

        $name = trim($input['name'] ?? '');
        $roleTitle = trim($input['role_title'] ?? '');
        $department = trim($input['department'] ?? 'Direksi & Manajemen');
        $photoUrl = trim($input['photo_url'] ?? '');
        $bio = trim($input['bio'] ?? '');
        $linkedinUrl = trim($input['linkedin_url'] ?? '');
        $sortOrder = isset($input['sort_order']) ? (int)$input['sort_order'] : 0;
        $isActive = isset($input['is_active']) ? (int)$input['is_active'] : 1;

        if (empty($name) || empty($roleTitle)) {
            sendJsonError('Nama dan jabatan anggota tim wajib diisi.');
        }

        $stmt = $db->prepare("
            UPDATE team_members SET
                name = ?, role_title = ?, department = ?, photo_url = ?,
                bio = ?, linkedin_url = ?, sort_order = ?, is_active = ?
            WHERE id = ?
        ");
        $stmt->execute([$name, $roleTitle, $department, $photoUrl, $bio, $linkedinUrl, $sortOrder, $isActive, $id]);

        logAdminActivity('update', 'team_members', (string)$id, "Admin memperbarui anggota tim: {$name}");
        triggerRealtimeEvent('content_updated', 'team', 'update', ['id' => $id]);
        sendJsonResponse(['id' => $id], 200, 'Data anggota tim berhasil diperbarui.');
    }

    // 4. DELETE: Hapus anggota tim
    if ($method === 'DELETE') {
        $id = (int)($input['id'] ?? ($_GET['id'] ?? 0));
        if (!$id) {
            sendJsonError('ID anggota tim tidak valid.');
        }

        $stmt = $db->prepare("DELETE FROM team_members WHERE id = ?");
        $stmt->execute([$id]);

        logAdminActivity('delete', 'team_members', (string)$id, "Admin menghapus anggota tim #{$id}");
        triggerRealtimeEvent('content_updated', 'team', 'delete', ['id' => $id]);
        sendJsonResponse(null, 200, 'Anggota tim berhasil dihapus.');
    }

    sendJsonError('Metode HTTP tidak didukung.', 405);

} catch (Throwable $e) {
    sendJsonError('Operasi gagal: ' . $e->getMessage(), 500);
}
