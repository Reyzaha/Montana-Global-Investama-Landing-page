<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — API: INVESTOR AUTHENTICATION
 * Actions: login, register, me, logout, settings
 */

require_once __DIR__ . '/../backend/config/db.php';
require_once __DIR__ . '/../backend/helpers/response.php';
require_once __DIR__ . '/../backend/helpers/auth_helper.php';

$action = $_GET['action'] ?? ($_POST['action'] ?? 'me');
$method = $_SERVER['REQUEST_METHOD'];

try {
    // 1. GET CSRF TOKEN (No DB required)
    if ($action === 'csrf') {
        sendJsonResponse(['csrf_token' => getCsrfToken()]);
    }

    // 2. LOGOUT (No DB required)
    if ($action === 'logout') {
        logoutInvestor();
        sendJsonResponse(['logged_out' => true, 'csrf_token' => getCsrfToken()], 200, 'Berhasil keluar dari sesi.');
    }

    // 3. GET CURRENT INVESTOR (ME) - unauthenticated check doesn't need DB
    if ($action === 'me') {
        $session = getInvestorSession();
        if (!$session) {
            sendJsonResponse(['logged_in' => false, 'user' => null, 'csrf_token' => getCsrfToken()]);
        }
        $db = getDB();
        $stmt = $db->prepare("
            SELECT i.id, i.account_type, i.email, i.full_name, i.citizenship, i.phone, i.status,
                   c.business_name, c.legal_entity, c.company_address, c.pic_name, c.pic_position, c.annual_turnover
            FROM investors i
            LEFT JOIN investor_companies c ON i.id = c.investor_id
            WHERE i.id = ?
        ");
        $stmt->execute([$session['id']]);
        $user = $stmt->fetch();

        if ($user) {
            sendJsonResponse([
                'logged_in' => true,
                'csrf_token' => getCsrfToken(),
                'user' => [
                    'id' => (int)$user['id'],
                    'type' => $user['account_type'],
                    'email' => $user['email'],
                    'fullName' => $user['account_type'] === 'perusahaan' ? $user['business_name'] : ($user['full_name'] ?: explode('@', $user['email'])[0]),
                    'picName' => $user['pic_name'] ?? '',
                    'phone' => $user['phone'] ?? '',
                    'legalEntity' => $user['legal_entity'] ?? '',
                    'status' => $user['status']
                ]
            ]);
        } else {
            logoutInvestor();
            sendJsonResponse(['logged_in' => false, 'user' => null, 'csrf_token' => getCsrfToken()]);
        }
    }

    // 4. GET SETTINGS (Untuk status Gated Content)
    if ($action === 'settings') {
        try {
            $db = getDB();
            $stmt = $db->query("SELECT setting_key, setting_value FROM system_settings");
            $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            sendJsonResponse([
                'require_auth_for_details' => ($settings['require_auth_for_details'] ?? '1') === '1',
                'disclaimer_text' => $settings['disclaimer_text'] ?? '',
                'csrf_token' => getCsrfToken()
            ]);
        } catch (Throwable $e) {
            // Graceful fallback if database is unavailable
            sendJsonResponse([
                'require_auth_for_details' => true,
                'disclaimer_text' => 'Akses dokumen finansial dan spesifikasi teknis alat berat dilindungi.',
                'csrf_token' => getCsrfToken()
            ]);
        }
    }

    // Database is strictly required for registration and login
    $db = getDB();

    // 4. INVESTOR LOGIN (Dengan Rate Limiting & Anti-Brute Force)
    if ($action === 'login') {
        if ($method !== 'POST') {
            sendJsonError('Method harus POST.', 405);
        }

        // Cek Rate Limit (Maksimal 5x gagal per 15 menit)
        if (!checkRateLimit('investor_login', 5, 900)) {
            sendJsonError('Terlalu banyak percobaan masuk yang gagal. Akses diblokir sementara selama 15 menit demi keamanan akun.', 429);
        }

        $input = getJsonInput();
        $email = trim(strtolower($input['email'] ?? ''));
        $password = (string)($input['password'] ?? '');
        $type = $input['type'] ?? null;

        if (empty($email) || empty($password)) {
            sendJsonError('Email dan kata sandi wajib diisi.');
        }

        $stmt = $db->prepare("
            SELECT i.*, c.business_name, c.legal_entity, c.pic_name, c.pic_position
            FROM investors i
            LEFT JOIN investor_companies c ON i.id = c.investor_id
            WHERE LOWER(i.email) = ?
        ");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !verifyPassword($password, $user['password_hash'])) {
            $attempts = recordFailedAttempt('investor_login', 900);
            $remaining = max(0, 5 - $attempts);
            sendJsonError("Email atau kata sandi tidak valid. Sisa percobaan: {$remaining}.", 401);
        }

        if ($user['status'] === 'suspended') {
            sendJsonError('Akun Anda sedang ditangguhkan. Silakan hubungi relationship manager MGI.', 403);
        }

        if ($type && $user['account_type'] !== $type) {
            $typeLabel = $user['account_type'] === 'perusahaan' ? 'Perusahaan' : 'Perorangan';
            sendJsonError("Akun ini terdaftar sebagai tipe \"{$typeLabel}\". Silakan pilih tab \"{$typeLabel}\" untuk masuk.");
        }

        // Reset rate limit counter on success
        clearRateLimit('investor_login');

        // Set session with session regeneration
        setInvestorSession($user);

        $userData = [
            'id' => (int)$user['id'],
            'type' => $user['account_type'],
            'email' => $user['email'],
            'fullName' => $user['account_type'] === 'perusahaan' ? $user['business_name'] : ($user['full_name'] ?: explode('@', $user['email'])[0]),
            'picName' => $user['pic_name'] ?? '',
            'phone' => $user['phone'] ?? '',
            'legalEntity' => $user['legal_entity'] ?? '',
            'status' => $user['status']
        ];

        sendJsonResponse([
            'user' => $userData,
            'csrf_token' => getCsrfToken(),
            'message' => 'Autentikasi berhasil! Mengalihkan ke portal investasi...'
        ], 200, 'Login berhasil.');
    }

    // 4. INVESTOR REGISTER
    if ($action === 'register') {
        if ($method !== 'POST') {
            sendJsonError('Method harus POST.', 405);
        }

        $input = getJsonInput();
        $accountType = $input['type'] ?? ($input['account_type'] ?? 'perorangan');
        $email = trim(strtolower($input['email'] ?? ''));
        $password = (string)($input['password'] ?? '');

        if (empty($email) || empty($password)) {
            sendJsonError('Email dan kata sandi wajib diisi.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            sendJsonError('Format alamat email tidak valid.');
        }

        if (strlen($password) < 8) {
            sendJsonError('Kata sandi minimal 8 karakter demi standar keamanan finansial akun Anda.');
        }

        // Cek email duplikat
        $stmt = $db->prepare("SELECT id FROM investors WHERE LOWER(email) = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            sendJsonError('Alamat email ini sudah terdaftar. Silakan gunakan email lain atau masuk ke akun Anda.');
        }

        $db->beginTransaction();

        $hash = hashPassword($password);

        if ($accountType === 'perusahaan') {
            $businessName = trim($input['business_name'] ?? ($input['businessName'] ?? ''));
            $legalEntity = $input['legal_entity'] ?? ($input['legalEntity'] ?? 'Perseroan Terbatas (PT)');
            $companyAddress = trim($input['company_address'] ?? ($input['companyAddress'] ?? ''));
            $picName = trim($input['pic_name'] ?? ($input['picName'] ?? ''));
            $picPosition = trim($input['pic_position'] ?? ($input['picPosition'] ?? ''));
            $companyPhone = trim($input['company_phone'] ?? ($input['phone'] ?? ''));
            $annualTurnover = $input['annual_turnover'] ?? ($input['annualTurnover'] ?? 'Rp10 Miliar – Rp50 Miliar');

            if (empty($businessName) || empty($picName)) {
                sendJsonError('Nama perusahaan dan nama PIC wajib diisi.');
            }

            $stmtIns = $db->prepare("
                INSERT INTO investors (account_type, email, password_hash, full_name, phone, status)
                VALUES ('perusahaan', ?, ?, ?, ?, 'active')
            ");
            $stmtIns->execute([$email, $hash, $businessName, $companyPhone]);
            $investorId = (int)$db->lastInsertId();

            $stmtComp = $db->prepare("
                INSERT INTO investor_companies (
                    investor_id, business_name, legal_entity, company_address,
                    pic_name, pic_position, company_phone, annual_turnover
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmtComp->execute([
                $investorId, $businessName, $legalEntity, $companyAddress,
                $picName, $picPosition, $companyPhone, $annualTurnover
            ]);

            $fullName = $businessName;

        } else {
            // Perorangan
            $fullName = trim($input['full_name'] ?? ($input['fullName'] ?? ''));
            $citizenship = $input['citizenship'] ?? 'Indonesia (WNI)';
            $phone = trim($input['phone'] ?? '');

            if (empty($fullName)) {
                sendJsonError('Nama lengkap sesuai identitas wajib diisi.');
            }

            $stmtIns = $db->prepare("
                INSERT INTO investors (account_type, email, password_hash, full_name, citizenship, phone, status)
                VALUES ('perorangan', ?, ?, ?, ?, ?, 'active')
            ");
            $stmtIns->execute([$email, $hash, $fullName, $citizenship, $phone]);
            $investorId = (int)$db->lastInsertId();
        }

        $db->commit();

        $newUser = [
            'id' => $investorId,
            'account_type' => $accountType,
            'email' => $email,
            'full_name' => $fullName,
            'status' => 'active'
        ];

        setInvestorSession($newUser);

        sendJsonResponse([
            'user' => [
                'id' => $investorId,
                'type' => $accountType,
                'email' => $email,
                'fullName' => $fullName,
                'phone' => $input['phone'] ?? '',
                'status' => 'active'
            ],
            'csrf_token' => getCsrfToken(),
            'message' => 'Registrasi berhasil! Selamat datang di Portal Investor PT Montana Global Investama.'
        ], 201, 'Registrasi berhasil.');
    }

    // 5. LOGOUT
    if ($action === 'logout') {
        clearInvestorSession();
        sendJsonResponse(null, 200, 'Berhasil keluar dari sesi investor.');
    }

    sendJsonError('Aksi tidak dikenali.', 400);

} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    sendJsonException($e, 'Terjadi kesalahan sistem saat memproses autentikasi.');
}
