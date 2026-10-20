<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — API: ADMIN LOGIN & MFA VERIFICATION
 * Supports Google Authenticator TOTP (QR Setup & Code Verification)
 */

require_once __DIR__ . '/../../backend/config/db.php';
require_once __DIR__ . '/../../backend/helpers/response.php';
require_once __DIR__ . '/../../backend/helpers/auth_helper.php';
require_once __DIR__ . '/../../backend/helpers/totp_helper.php';

$action = $_GET['action'] ?? 'login';

// LOGOUT
if ($action === 'logout') {
    $admin = getAdminSession();
    if ($admin) {
        logAdminActivity('logout', 'admin_users', (string)$admin['id'], 'Admin logged out');
    }
    clearAdminSession();
    unset($_SESSION['mgi_admin_mfa_pending']);
    sendJsonResponse(null, 200, 'Admin berhasil logout.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonError('Method harus POST.', 405);
}

// 1. TAHAP 1: LOGIN KREDENSIAL (USERNAME / EMAIL & PASSWORD)
if ($action === 'login') {
    $input = getJsonInput();
    $username = trim($input['username'] ?? '');
    $password = (string)($input['password'] ?? '');

    // Rate Limiting Atomik Admin Login (Pre-Reservation Slot untuk Menutup Celah TOCTOU)
    $slot = consumeRateLimitSlot('admin_login', 5, 900, $username);
    if (!$slot['allowed']) {
        sendJsonError('Terlalu banyak percobaan masuk administrator yang gagal. Akses dibatasi sementara demi keamanan sistem. Silakan coba lagi nanti.', 429);
    }

    if (empty($username) || empty($password)) {
        sendJsonError('Username dan password wajib diisi.');
    }

    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM admin_users WHERE username = ? OR email = ? LIMIT 1");
        $stmt->execute([$username, $username]);
        $admin = $stmt->fetch();

        if (!$admin || !verifyPassword($password, $admin['password_hash'])) {
            $remaining = max(0, 5 - $slot['attempts']);
            logAdminActivity('login_failed', 'admin_users', null, "Failed login attempt for '{$username}' from IP " . getClientIp());
            sendJsonError("Kredensial administrator tidak valid. Sisa percobaan: {$remaining}.", 401);
        }

        if (empty($admin['is_active'])) {
            sendJsonError('Akun administrator ini dinonaktifkan. Silakan hubungi superadmin.', 403);
        }

        // Reset rate limit counter on success
        clearRateLimit('admin_login', $username);

        // Cek status MFA Google Authenticator
        $mfaSecret = $admin['mfa_secret'] ?? null;
        $mfaEnabled = !empty($admin['mfa_enabled']);

        // Jika belum ada secret, generate baru untuk setup
        if (empty($mfaSecret)) {
            $mfaSecret = GoogleAuthenticator::generateSecret(16);
            $stmtSec = $db->prepare("UPDATE admin_users SET mfa_secret = ? WHERE id = ?");
            $stmtSec->execute([$mfaSecret, $admin['id']]);
            $admin['mfa_secret'] = $mfaSecret;
            $mfaEnabled = false;
        }

        // Simpan sesi sementara untuk verifikasi MFA
        $_SESSION['mgi_admin_mfa_pending'] = [
            'id' => (int)$admin['id'],
            'username' => $admin['username'],
            'email' => $admin['email'],
            'full_name' => $admin['full_name'],
            'role' => $admin['role'],
            'is_active' => $admin['is_active'],
            'mfa_secret' => $mfaSecret,
            'mfa_enabled' => $mfaEnabled
        ];

        $qrUri = GoogleAuthenticator::getQrUri('MGI Admin Portal', $admin['username'], $mfaSecret);

        sendJsonResponse([
            'requires_mfa' => true,
            'mfa_setup' => !$mfaEnabled,
            'secret' => $mfaSecret,
            'qr_uri' => $qrUri,
            'csrf_token' => getCsrfToken()
        ], 200, $mfaEnabled ? 'Masukkan 6 digit kode dari Google Authenticator.' : 'Scan QR code dengan Google Authenticator lalu masukkan 6 digit kode.');

    } catch (Exception $e) {
        sendJsonException($e, 'Terjadi kesalahan sistem saat memproses login administrator.');
    }
}

// 2. TAHAP 2: VERIFIKASI KODE 2FA / MFA (GOOGLE AUTHENTICATOR)
if ($action === 'verify_mfa') {
    $pendingAdmin = $_SESSION['mgi_admin_mfa_pending'] ?? null;
    if (!$pendingAdmin) {
        sendJsonError('Sesi verifikasi telah kedaluwarsa. Silakan login kembali.', 401);
    }

    $adminAccountId = 'admin_acc_' . (int)$pendingAdmin['id'];

    // Rate limit percobaan OTP MFA (Pre-Reservation Slot untuk Menutup Celah TOCTOU)
    $slot = consumeRateLimitSlot('admin_mfa', 5, 600, $adminAccountId);
    if (!$slot['allowed']) {
        sendJsonError('Terlalu banyak percobaan kode MFA yang salah untuk akun ini. Verifikasi ditangguhkan sementara demi keamanan.', 429);
    }

    $input = getJsonInput();
    $totpCode = trim($input['totp_code'] ?? '');

    if (empty($totpCode) || strlen($totpCode) !== 6 || !ctype_digit($totpCode)) {
        sendJsonError('Kode autentikasi harus terdiri dari 6 digit angka.');
    }

    $secret = $pendingAdmin['mfa_secret'];
    if (!GoogleAuthenticator::verifyCode($secret, $totpCode, 1)) {
        $remaining = max(0, 5 - $slot['attempts']);
        logAdminActivity('mfa_failed', 'admin_users', (string)$pendingAdmin['id'], 'Invalid MFA OTP attempt from IP ' . getClientIp());
        sendJsonError("Kode autentikasi salah atau sudah kedaluwarsa. Sisa percobaan: {$remaining}.", 401);
    }

    try {
        $db = getDB();

        // Aktifkan MFA secara permanen di database jika sebelumnya belum
        if (empty($pendingAdmin['mfa_enabled'])) {
            $stmtUp = $db->prepare("UPDATE admin_users SET mfa_enabled = 1, last_login = NOW() WHERE id = ?");
            $stmtUp->execute([$pendingAdmin['id']]);
        } else {
            $stmtUp = $db->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?");
            $stmtUp->execute([$pendingAdmin['id']]);
        }

        // Hapus rate limit MFA
        clearRateLimit('admin_mfa', $adminAccountId);


        // Bangun sesi admin resmi
        $adminData = [
            'id' => $pendingAdmin['id'],
            'username' => $pendingAdmin['username'],
            'email' => $pendingAdmin['email'],
            'full_name' => $pendingAdmin['full_name'],
            'role' => $pendingAdmin['role'],
            'is_active' => $pendingAdmin['is_active']
        ];
        setAdminSession($adminData);
        unset($_SESSION['mgi_admin_mfa_pending']);

        // Audit log berhasil
        logAdminActivity('login', 'admin_users', (string)$pendingAdmin['id'], 'Admin successfully logged in with MFA from IP ' . getClientIp());

        sendJsonResponse([
            'admin' => $adminData,
            'csrf_token' => getCsrfToken(),
            'redirect' => 'index.php'
        ], 200, 'Verifikasi MFA berhasil! Mengalihkan ke dashboard...');

    } catch (Exception $e) {
        sendJsonException($e, 'Gagal menyelesaikan verifikasi MFA.');
    }
}

sendJsonError('Action tidak valid.', 400);
