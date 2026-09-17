<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — API: ADMIN USERS MANAGEMENT
 * Strictly restricted to role 'superadmin'
 * Methods: GET, POST, PUT, DELETE
 */

require_once __DIR__ . '/../../backend/config/db.php';
require_once __DIR__ . '/../../backend/helpers/response.php';
require_once __DIR__ . '/../../backend/helpers/auth_helper.php';
require_once __DIR__ . '/../../backend/helpers/totp_helper.php';

$currentAdmin = requireAdminAuth();

// Superadmin privilege check
if (($currentAdmin['role'] ?? '') !== 'superadmin') {
    sendJsonError('Akses ditolak. Fitur ini hanya dapat diakses oleh Super Administrator.', 403);
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    $db = getDB();

    // 1. GET: Ambil daftar seluruh admin
    if ($method === 'GET') {
        $stmt = $db->query("
            SELECT id, username, email, full_name, role, is_active, mfa_enabled, last_login, created_at, updated_at
            FROM admin_users
            ORDER BY id ASC
        ");
        $admins = $stmt->fetchAll();
        sendJsonResponse($admins);
    }

    // 2. POST: Tambah admin baru
    if ($method === 'POST') {
        $input = getJsonInput();
        $username = trim($input['username'] ?? '');
        $email = trim(strtolower($input['email'] ?? ''));
        $fullName = trim($input['full_name'] ?? '');
        $password = (string)($input['password'] ?? '');
        $role = $input['role'] ?? 'admin';
        $isActive = isset($input['is_active']) ? (int)$input['is_active'] : 1;

        if (empty($username) || empty($email) || empty($password)) {
            sendJsonError('Username, email, dan kata sandi wajib diisi.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            sendJsonError('Format email tidak valid.');
        }

        if (strlen($password) < 8) {
            sendJsonError('Kata sandi admin minimal 8 karakter demi standar keamanan sistem.');
        }

        if (!in_array($role, ['superadmin', 'admin', 'editor'])) {
            $role = 'admin';
        }

        // Cek duplikat username / email
        $stmtCheck = $db->prepare("SELECT id FROM admin_users WHERE username = ? OR email = ?");
        $stmtCheck->execute([$username, $email]);
        if ($stmtCheck->fetch()) {
            sendJsonError('Username atau email sudah digunakan oleh administrator lain.');
        }

        $hash = hashPassword($password);
        $mfaSecret = GoogleAuthenticator::generateSecret(16);

        $stmtIns = $db->prepare("
            INSERT INTO admin_users (username, email, password_hash, full_name, role, is_active, mfa_secret, mfa_enabled)
            VALUES (?, ?, ?, ?, ?, ?, ?, 0)
        ");
        $stmtIns->execute([
            $username,
            $email,
            $hash,
            $fullName ?: $username,
            $role,
            $isActive,
            $mfaSecret
        ]);
        $newId = (int)$db->lastInsertId();

        logAdminActivity('create_admin', 'admin_users', (string)$newId, "Superadmin created admin user: '{$username}' with role: '{$role}'");

        sendJsonResponse([
            'id' => $newId,
            'username' => $username,
            'email' => $email,
            'full_name' => $fullName,
            'role' => $role
        ], 201, "Akun administrator '{$username}' berhasil dibuat.");
    }

    // 3. PUT: Update data admin (edit profile, role, status, atau reset password / reset MFA)
    if ($method === 'PUT') {
        $input = getJsonInput();
        $id = (int)($input['id'] ?? ($_GET['id'] ?? 0));

        if (!$id) {
            sendJsonError('ID Administrator wajib disertakan.');
        }

        // Ambil data target
        $stmtTarget = $db->prepare("SELECT * FROM admin_users WHERE id = ?");
        $stmtTarget->execute([$id]);
        $target = $stmtTarget->fetch();

        if (!$target) {
            sendJsonError('Akun administrator tidak ditemukan.', 404);
        }

        // Khusus Reset MFA
        if ($action === 'reset_mfa') {
            $newSecret = GoogleAuthenticator::generateSecret(16);
            $stmtReset = $db->prepare("UPDATE admin_users SET mfa_enabled = 0, mfa_secret = ? WHERE id = ?");
            $stmtReset->execute([$newSecret, $id]);

            logAdminActivity('reset_mfa', 'admin_users', (string)$id, "MFA reset for admin '{$target['username']}'");
            sendJsonResponse(null, 200, "MFA untuk admin '{$target['username']}' berhasil direset. Admin wajib melakukan scan QR Code baru saat login berikutnya.");
        }

        // Update Umum (Nama, Email, Role, Status, Password opsional)
        $fullName = trim($input['full_name'] ?? $target['full_name']);
        $email = trim(strtolower($input['email'] ?? $target['email']));
        $role = $input['role'] ?? $target['role'];
        $isActive = isset($input['is_active']) ? (int)$input['is_active'] : $target['is_active'];
        $newPassword = (string)($input['password'] ?? '');

        // Proteksi: Superadmin tidak boleh menonaktifkan dirinya sendiri atau mencabut role superadmin dari dirinya sendiri
        if ((int)$currentAdmin['id'] === $id) {
            if ($isActive === 0) {
                sendJsonError('Anda tidak dapat menonaktifkan akun Anda sendiri.');
            }
            if ($role !== 'superadmin') {
                sendJsonError('Anda tidak dapat menurunkan role akun Anda sendiri.');
            }
        }

        if (!in_array($role, ['superadmin', 'admin', 'editor'])) {
            $role = $target['role'];
        }

        // Cek duplikat email dengan akun lain
        if ($email !== $target['email']) {
            $stmtEmail = $db->prepare("SELECT id FROM admin_users WHERE email = ? AND id != ?");
            $stmtEmail->execute([$email, $id]);
            if ($stmtEmail->fetch()) {
                sendJsonError('Alamat email sudah digunakan oleh administrator lain.');
            }
        }

        if (!empty($newPassword)) {
            if (strlen($newPassword) < 8) {
                sendJsonError('Kata sandi baru minimal 8 karakter.');
            }
            $hash = hashPassword($newPassword);
            $stmtUp = $db->prepare("
                UPDATE admin_users
                SET full_name = ?, email = ?, role = ?, is_active = ?, password_hash = ?
                WHERE id = ?
            ");
            $stmtUp->execute([$fullName, $email, $role, $isActive, $hash, $id]);
        } else {
            $stmtUp = $db->prepare("
                UPDATE admin_users
                SET full_name = ?, email = ?, role = ?, is_active = ?
                WHERE id = ?
            ");
            $stmtUp->execute([$fullName, $email, $role, $isActive, $id]);
        }

        logAdminActivity('update_admin', 'admin_users', (string)$id, "Superadmin updated admin: '{$target['username']}'");
        sendJsonResponse(null, 200, "Data administrator '{$target['username']}' berhasil diperbarui.");
    }

    // 4. DELETE: Hapus akun admin
    if ($method === 'DELETE') {
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) {
            $input = getJsonInput();
            $id = (int)($input['id'] ?? 0);
        }

        if (!$id) {
            sendJsonError('ID Administrator wajib disertakan.');
        }

        // Proteksi: Tidak boleh menghapus akun sendiri
        if ((int)$currentAdmin['id'] === $id) {
            sendJsonError('Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $stmtTarget = $db->prepare("SELECT username FROM admin_users WHERE id = ?");
        $stmtTarget->execute([$id]);
        $target = $stmtTarget->fetch();

        if (!$target) {
            sendJsonError('Akun administrator tidak ditemukan.', 404);
        }

        $stmtDel = $db->prepare("DELETE FROM admin_users WHERE id = ?");
        $stmtDel->execute([$id]);

        logAdminActivity('delete_admin', 'admin_users', (string)$id, "Superadmin deleted admin: '{$target['username']}'");
        sendJsonResponse(null, 200, "Akun administrator '{$target['username']}' berhasil dihapus.");
    }

    sendJsonError('Metode HTTP tidak didukung.', 405);

} catch (Exception $e) {
    sendJsonException($e, 'Terjadi kesalahan sistem saat memproses manajemen administrator.');
}
