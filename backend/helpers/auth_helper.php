<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — AUTHENTICATION & SECURITY HELPER
 * Session management, password verification, CSRF, rate-limiting, and audit logging.
 */

// TRUSTED PROXY VALIDATOR (Hanya percaya X-Forwarded-For/X-Real-IP jika request berasal dari Nginx/Docker)
function isTrustedProxy(string $ip): bool {
    if ($ip === '127.0.0.1' || $ip === '::1' || $ip === 'localhost') {
        return true;
    }
    $long = ip2long($ip);
    if ($long === false) {
        return false;
    }
    // 127.0.0.0/8 (Loopback)
    if (($long & 0xFF000000) === 0x7F000000) return true;
    // 10.0.0.0/8 (Private Network / Docker)
    if (($long & 0xFF000000) === 0x0A000000) return true;
    // 172.16.0.0/12 (Docker Default Bridge Subnet: 172.16.0.0 s/d 172.31.255.255)
    if (($long & 0xFFF00000) === 0xAC100000) return true;
    // 192.168.0.0/16 (Private LAN)
    if (($long & 0xFFFF0000) === 0xC0A80000) return true;

    return false;
}

if (session_status() === PHP_SESSION_NONE) {
    // Sinkronkan batas garbage collection dengan lifetime cookie
    ini_set('session.gc_maxlifetime', 86400);

    // Deteksi HTTPS melalui proxy tepercaya (Docker / Nginx)
    $remote = $_SERVER['REMOTE_ADDR'] ?? '';
    $isProxy = isTrustedProxy($remote);
    $proto = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '';
    
    $isSecure = (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443)
        || ($isProxy && strtolower($proto) === 'https');
    
    // Hardened Session Cookie Configuration
    session_set_cookie_params([
        'lifetime' => 86400, // 24 jam
        'path' => '/',
        'domain' => '',
        'secure' => $isSecure,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    
    session_start();
}

require_once __DIR__ . '/../config/db.php';

function hashPassword(string $password): string {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
}

function verifyPassword(string $password, string $hash): bool {
    return password_verify($password, $hash);
}

// CSRF TOKEN HELPERS
function getCsrfToken(): string {
    if (empty($_SESSION['mgi_csrf_token'])) {
        $_SESSION['mgi_csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['mgi_csrf_token'];
}

function validateCsrfToken(?string $token = null): bool {
    // Permintaan mobile/API yang diautentikasi dengan Bearer token valid tidak rentan CSRF berbasis cookie
    $bearer = getBearerToken();
    if ($bearer && validateInvestorAuthToken($bearer)) {
        return true;
    }

    $sessionToken = $_SESSION['mgi_csrf_token'] ?? '';
    if (empty($sessionToken)) {
        return false;
    }

    if ($token === null) {
        $headers = function_exists('getallheaders') ? getallheaders() : [];
        $headerToken = $headers['X-CSRF-Token'] ?? ($headers['x-csrf-token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null));
        if ($headerToken) {
            $token = $headerToken;
        } else {
            $raw = file_get_contents('php://input');
            $decoded = json_decode($raw, true);
            $token = $decoded['csrf_token'] ?? ($_POST['csrf_token'] ?? null);
        }
    }

    return !empty($token) && hash_equals($sessionToken, (string)$token);
}

function getClientIp(): string {
    $remoteAddr = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

    // Header X-Real-IP / X-Forwarded-For HANYA dipercaya jika request dikirim oleh reverse proxy terpercaya (Nginx/Docker)
    if (isTrustedProxy($remoteAddr)) {
        // 1. Utamakan X-Real-IP jika di-set oleh Nginx (paling bersih dan terisolasi)
        if (!empty($_SERVER['HTTP_X_REAL_IP']) && filter_var($_SERVER['HTTP_X_REAL_IP'], FILTER_VALIDATE_IP)) {
            return $_SERVER['HTTP_X_REAL_IP'];
        }

        // 2. Jika X-Forwarded-For ada, telusuri dari KANAN ke KIRI (ambil IP pertama yang bukan proxy tepercaya)
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = array_map('trim', explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']));
            for ($i = count($ips) - 1; $i >= 0; $i--) {
                $candidate = $ips[$i];
                if (filter_var($candidate, FILTER_VALIDATE_IP)) {
                    if (!isTrustedProxy($candidate)) {
                        return $candidate; // IP klien pertama dari kanan di luar proxy
                    }
                }
            }
        }
    }

    return filter_var($remoteAddr, FILTER_VALIDATE_IP) ? $remoteAddr : '127.0.0.1';
}

function normalizeIpSubnet(string $ip): string {
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
        $packed = inet_pton($ip);
        if ($packed !== false && strlen($packed) === 16) {
            // Masking 64-bit terakhir untuk subnet /64 IPv6
            return inet_ntop(substr($packed, 0, 8) . str_repeat("\0", 8)) . '/64';
        }
    }
    return $ip;
}

function getRateLimitSecret(): string {
    static $secret = null;
    if ($secret === null) {
        $secret = $_ENV['RATE_LIMIT_SECRET'] ?? ($_ENV['DB_PASS'] ?? 'mgi_sec_rate_limit_salt_2026');
    }
    return $secret;
}

function getRateLimitKey(string $actionKey, string $scope, string $identifier): string {
    return hash_hmac('sha256', $actionKey . '|' . $scope . '|' . strtolower(trim($identifier)), getRateLimitSecret());
}

/**
 * Mengonsumsi kuota percobaan secara ATOMIK SEBELUM verifikasi password (Menutup Celah TOCTOU).
 * Mengembalikan array ['allowed' => bool, 'attempts' => int, 'locked_until' => int]
 */
function consumeRateLimitSlot(string $actionKey, int $maxAttempts = 5, int $decaySeconds = 900, ?string $identifier = null): array {
    $rawIp = getClientIp();
    $normIp = normalizeIpSubnet($rawIp);
    $isAdminAction = str_starts_with($actionKey, 'admin_');
    $ipKey = getRateLimitKey($actionKey, 'ip', $normIp);

    try {
        $db = getDB();

        // 1. Eksekusi Reservasi Slot per-IP secara Atomik di MySQL
        $delay30 = 30;
        $delay60 = 60;
        $delay300 = 300;
        $delay900 = min($decaySeconds, 900);

        $sql = "
            INSERT INTO security_rate_limits (
                rate_key, action_type, identifier, ip_address, attempts, first_attempt_at, last_attempt_at, locked_until
            ) VALUES (
                :rate_key, :action_type, :norm_ip, :ip_address, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 0
            ) ON DUPLICATE KEY UPDATE
                locked_until = IF(
                    (UNIX_TIMESTAMP() - last_attempt_at) > :decay_seconds,
                    0,
                    IF(
                        locked_until > UNIX_TIMESTAMP(),
                        locked_until,
                        CASE
                            WHEN (attempts + 1) >= 9 THEN UNIX_TIMESTAMP() + :delay_900
                            WHEN (attempts + 1) = 8 THEN UNIX_TIMESTAMP() + :delay_300
                            WHEN (attempts + 1) = 7 THEN UNIX_TIMESTAMP() + :delay_60
                            WHEN (attempts + 1) = 6 THEN UNIX_TIMESTAMP() + :delay_30
                            ELSE 0
                        END
                    )
                ),
                attempts = IF((UNIX_TIMESTAMP() - last_attempt_at) > :decay_seconds_2, 1, attempts + 1),
                last_attempt_at = UNIX_TIMESTAMP();
        ";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':rate_key' => $ipKey,
            ':action_type' => substr($actionKey, 0, 32),
            ':norm_ip' => substr($normIp, 0, 128),
            ':ip_address' => substr($rawIp, 0, 45),
            ':decay_seconds' => $decaySeconds,
            ':decay_seconds_2' => $decaySeconds,
            ':delay_30' => $delay30,
            ':delay_60' => $delay60,
            ':delay_300' => $delay300,
            ':delay_900' => $delay900
        ]);

        // Ambil status baris setelah reservasi atomik
        $stmtStatus = $db->prepare("SELECT attempts, locked_until, UNIX_TIMESTAMP() AS server_now FROM security_rate_limits WHERE rate_key = ?");
        $stmtStatus->execute([$ipKey]);
        $row = $stmtStatus->fetch(PDO::FETCH_ASSOC);

        $attempts = (int)($row['attempts'] ?? 1);
        $lockedUntil = (int)($row['locked_until'] ?? 0);
        $now = (int)($row['server_now'] ?? time());

        // Jika locked_until aktif atau attempts melampaui batas -> DITOLAK
        if ($lockedUntil > $now || $attempts > $maxAttempts) {
            return ['allowed' => false, 'attempts' => $attempts, 'locked_until' => $lockedUntil];
        }

        // 2. Jika ada identifier (username), cek juga batas per-username lintas IP
        if (!empty($identifier)) {
            $userKey = getRateLimitKey($actionKey, 'user', $identifier);
            $userMax = $maxAttempts * 2;
            $stmtUser = $db->prepare("SELECT attempts, locked_until, last_attempt_at FROM security_rate_limits WHERE rate_key = ? LIMIT 1");
            $stmtUser->execute([$userKey]);
            $rowUser = $stmtUser->fetch(PDO::FETCH_ASSOC);

            if ($rowUser) {
                if ($now < (int)$rowUser['locked_until']) {
                    return ['allowed' => false, 'attempts' => $attempts, 'locked_until' => (int)$rowUser['locked_until']];
                }
                if ((int)$rowUser['attempts'] >= $userMax && ($now - (int)$rowUser['last_attempt_at']) <= $decaySeconds) {
                    return ['allowed' => false, 'attempts' => $attempts, 'locked_until' => $now + $delay900];
                }
            }
        }

        // Purge probabilistik
        if (random_int(1, 100) === 1) {
            $db->exec("DELETE FROM security_rate_limits WHERE last_attempt_at < (UNIX_TIMESTAMP() - 86400) AND locked_until < UNIX_TIMESTAMP()");
        }

        return ['allowed' => true, 'attempts' => $attempts, 'locked_until' => 0];

    } catch (Throwable $t) {
        if ($isAdminAction) {
            error_log("[SECURITY CRITICAL] consumeRateLimitSlot DB error for admin action '{$actionKey}': " . $t->getMessage());
            return ['allowed' => false, 'attempts' => 999, 'locked_until' => time() + 300];
        } else {
            error_log("[WARNING] consumeRateLimitSlot DB error for public action '{$actionKey}': " . $t->getMessage());
            return ['allowed' => true, 'attempts' => 1, 'locked_until' => 0];
        }
    }
}

/**
 * Wrapper pengecekan read-only untuk form yang tidak melakukan reservasi slot
 */
function checkRateLimit(string $actionKey, int $maxAttempts = 5, int $decaySeconds = 900, ?string $identifier = null): bool {
    $rawIp = getClientIp();
    $normIp = normalizeIpSubnet($rawIp);
    $isAdminAction = str_starts_with($actionKey, 'admin_');
    $now = time();

    try {
        $db = getDB();
        $ipKey = getRateLimitKey($actionKey, 'ip', $normIp);
        $stmt = $db->prepare("SELECT attempts, locked_until, last_attempt_at FROM security_rate_limits WHERE rate_key = ? LIMIT 1");
        $stmt->execute([$ipKey]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            if ($now < (int)$row['locked_until']) return false;
            if ((int)$row['attempts'] >= $maxAttempts && ($now - (int)$row['last_attempt_at']) <= $decaySeconds) return false;
        }

        if (!empty($identifier)) {
            $userKey = getRateLimitKey($actionKey, 'user', $identifier);
            $stmtUser = $db->prepare("SELECT attempts, locked_until, last_attempt_at FROM security_rate_limits WHERE rate_key = ? LIMIT 1");
            $stmtUser->execute([$userKey]);
            $rowUser = $stmtUser->fetch(PDO::FETCH_ASSOC);
            if ($rowUser) {
                if ($now < (int)$rowUser['locked_until']) return false;
                if ((int)$rowUser['attempts'] >= ($maxAttempts * 2) && ($now - (int)$rowUser['last_attempt_at']) <= $decaySeconds) return false;
            }
        }
        return true;
    } catch (Throwable $t) {
        return !$isAdminAction;
    }
}

/**
 * Mencatat percobaan gagal secara ATOMIK menggunakan INSERT ... ON DUPLICATE KEY UPDATE
 */
function recordFailedAttempt(string $actionKey, int $decaySeconds = 900, ?string $identifier = null, int $maxAttempts = 5): int {
    $rawIp = getClientIp();
    $normIp = normalizeIpSubnet($rawIp);
    $now = time();

    try {
        $db = getDB();

        // 1. Pencatatan Atomik per-IP
        $ipKey = getRateLimitKey($actionKey, 'ip', $normIp);
        $delay30 = 30;
        $delay60 = 60;
        $delay300 = 300;
        $delay900 = min($decaySeconds, 900);

        $sql = "
            INSERT INTO security_rate_limits (
                rate_key, action_type, identifier, ip_address, attempts, first_attempt_at, last_attempt_at, locked_until
            ) VALUES (
                :rate_key, :action_type, :norm_ip, :ip_address, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 0
            ) ON DUPLICATE KEY UPDATE
                locked_until = IF(
                    (UNIX_TIMESTAMP() - last_attempt_at) > :decay_seconds,
                    0,
                    CASE
                        WHEN (attempts + 1) >= 9 THEN UNIX_TIMESTAMP() + :delay_900
                        WHEN (attempts + 1) = 8 THEN UNIX_TIMESTAMP() + :delay_300
                        WHEN (attempts + 1) = 7 THEN UNIX_TIMESTAMP() + :delay_60
                        WHEN (attempts + 1) = 6 THEN UNIX_TIMESTAMP() + :delay_30
                        ELSE 0
                    END
                ),
                attempts = IF((UNIX_TIMESTAMP() - last_attempt_at) > :decay_seconds_2, 1, attempts + 1),
                last_attempt_at = UNIX_TIMESTAMP();
        ";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':rate_key' => $ipKey,
            ':action_type' => substr($actionKey, 0, 32),
            ':norm_ip' => substr($normIp, 0, 128),
            ':ip_address' => substr($rawIp, 0, 45),
            ':decay_seconds' => $decaySeconds,
            ':decay_seconds_2' => $decaySeconds,
            ':delay_30' => $delay30,
            ':delay_60' => $delay60,
            ':delay_300' => $delay300,
            ':delay_900' => $delay900
        ]);

        // 2. Pencatatan Atomik per-Username (Lintas IP) jika ada identifier
        if (!empty($identifier)) {
            $userKey = getRateLimitKey($actionKey, 'user', $identifier);
            $userMax = $maxAttempts * 2;
            $stmtUser = $db->prepare("
                INSERT INTO security_rate_limits (
                    rate_key, action_type, identifier, ip_address, attempts, first_attempt_at, last_attempt_at, locked_until
                ) VALUES (
                    :rate_key, :action_type, :identifier, 'CROSS_IP', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 0
                ) ON DUPLICATE KEY UPDATE
                    locked_until = IF(
                        (UNIX_TIMESTAMP() - last_attempt_at) > :decay_seconds,
                        0,
                        CASE
                            WHEN (attempts + 1) >= :user_max THEN UNIX_TIMESTAMP() + :delay_900
                            ELSE 0
                        END
                    ),
                    attempts = IF((UNIX_TIMESTAMP() - last_attempt_at) > :decay_seconds_2, 1, attempts + 1),
                    last_attempt_at = UNIX_TIMESTAMP();
            ");
            $stmtUser->execute([
                ':rate_key' => $userKey,
                ':action_type' => substr($actionKey, 0, 32),
                ':identifier' => 'sha256:' . substr(hash('sha256', strtolower(trim($identifier))), 0, 32),
                ':decay_seconds' => $decaySeconds,
                ':decay_seconds_2' => $decaySeconds,
                ':user_max' => $userMax,
                ':delay_900' => $delay900
            ]);
        }

        // Ambil hitungan terkini untuk respons
        $stmtGet = $db->prepare("SELECT attempts FROM security_rate_limits WHERE rate_key = ?");
        $stmtGet->execute([$ipKey]);
        return (int)$stmtGet->fetchColumn() ?: 1;

    } catch (Throwable $t) {
        error_log("[WARNING] recordFailedAttempt DB error: " . $t->getMessage());
        return 1;
    }
}

function clearRateLimit(string $actionKey, ?string $identifier = null, ?string $specificIp = null): void {
    try {
        $db = getDB();

        // 1. Bersihkan rate limit per-IP (bisa IP spesifik via CLI atau IP klien aktif)
        $targetIp = !empty($specificIp) ? $specificIp : getClientIp();
        $normIp = normalizeIpSubnet($targetIp);
        $ipKey = getRateLimitKey($actionKey, 'ip', $normIp);

        $stmt = $db->prepare("DELETE FROM security_rate_limits WHERE rate_key = ?");
        $stmt->execute([$ipKey]);

        // 2. Bersihkan rate limit per-Username jika identifier diberikan
        if (!empty($identifier)) {
            $userKey = getRateLimitKey($actionKey, 'user', $identifier);
            $stmtUser = $db->prepare("DELETE FROM security_rate_limits WHERE rate_key = ?");
            $stmtUser->execute([$userKey]);
        }
    } catch (Throwable $t) {
        error_log("[MGI clearRateLimit Error] " . $t->getMessage());
    }
}

function checkSessionTimeout(int $maxIdleSeconds = 3600): void {
    if (session_status() === PHP_SESSION_ACTIVE && !empty($_SESSION['mgi_last_activity'])) {
        if ((time() - (int)$_SESSION['mgi_last_activity']) > $maxIdleSeconds) {
            clearAdminSession();
            clearInvestorSession();
            if (str_contains($_SERVER['REQUEST_URI'] ?? '', '/api/')) {
                require_once __DIR__ . '/response.php';
                sendJsonError('Sesi telah kedaluwarsa karena tidak ada aktivitas (idle timeout). Silakan login kembali.', 401);
            } else {
                header('Location: login.php?error=idle_timeout');
                exit;
            }
        }
    }
    $_SESSION['mgi_last_activity'] = time();
}



// ADMIN SESSION FUNCTIONS
function setAdminSession(array $admin): void {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true); // Prevent session fixation
    }
    $_SESSION['mgi_admin_logged_in'] = true;
    $_SESSION['mgi_admin_id'] = $admin['id'];
    $_SESSION['mgi_admin_username'] = $admin['username'];
    $_SESSION['mgi_admin_role'] = $admin['role'];
    $_SESSION['mgi_admin_name'] = $admin['full_name'] ?? $admin['username'];
}

function getAdminSession(): ?array {
    if (!empty($_SESSION['mgi_admin_logged_in']) && !empty($_SESSION['mgi_admin_id'])) {
        return [
            'id' => $_SESSION['mgi_admin_id'],
            'username' => $_SESSION['mgi_admin_username'],
            'role' => $_SESSION['mgi_admin_role'],
            'name' => $_SESSION['mgi_admin_name']
        ];
    }
    return null;
}

function requireAdminAuth(): array {
    $admin = getAdminSession();
    if (!$admin) {
        if (str_contains($_SERVER['REQUEST_URI'] ?? '', '/api/')) {
            require_once __DIR__ . '/response.php';
            sendJsonError('Akses ditolak. Sesi administrator belum aktif atau telah kedaluwarsa.', 401);
        } else {
            header('Location: login.php?error=auth_required');
            exit;
        }
    }
    return $admin;
}

function clearAdminSession(): void {
    unset($_SESSION['mgi_admin_logged_in']);
    unset($_SESSION['mgi_admin_id']);
    unset($_SESSION['mgi_admin_username']);
    unset($_SESSION['mgi_admin_role']);
    unset($_SESSION['mgi_admin_name']);
}

// INVESTOR SESSION FUNCTIONS
function setInvestorSession(array $investor): void {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true); // Prevent session fixation
    }
    $_SESSION['mgi_investor_logged_in'] = true;
    $_SESSION['mgi_investor_id'] = $investor['id'];
    $_SESSION['mgi_investor_type'] = $investor['account_type'];
    $_SESSION['mgi_investor_email'] = $investor['email'];
    $_SESSION['mgi_investor_name'] = $investor['full_name'] ?? ($investor['business_name'] ?? '');
    $_SESSION['mgi_investor_login_time'] = date('c');
    $_SESSION['mgi_investor_ip'] = getClientIp();
}

function generateInvestorAuthToken(array $investor): string {
    $payload = [
        'sub' => (int)$investor['id'],
        'email' => strtolower(trim($investor['email'] ?? '')),
        'type' => $investor['account_type'] ?? 'perorangan',
        'name' => $investor['full_name'] ?? ($investor['business_name'] ?? ''),
        'iat' => time(),
        'exp' => time() + (30 * 86400) // 30 hari untuk mobile app
    ];
    $json = json_encode($payload, JSON_UNESCAPED_SLASHES);
    $encoded = rtrim(strtr(base64_encode($json), '+/', '-_'), '=');
    $sig = hash_hmac('sha256', $encoded, getRateLimitSecret());
    return $encoded . '.' . $sig;
}

function getBearerToken(): ?string {
    $headers = function_exists('getallheaders') ? getallheaders() : [];

    // Check Authorization header in all variations
    $authHeader = $headers['Authorization'] 
        ?? ($headers['authorization'] 
        ?? ($_SERVER['HTTP_AUTHORIZATION'] 
        ?? ($_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? null)));

    if ($authHeader && preg_match('/Bearer\s+(\S+)/i', $authHeader, $matches)) {
        return $matches[1];
    }

    // Fallback: X-Auth-Token, X-Session-ID, atau query parameter token
    return $headers['X-Auth-Token'] 
        ?? ($headers['x-auth-token'] 
        ?? ($headers['X-Session-ID']
        ?? ($headers['x-session-id']
        ?? ($_SERVER['HTTP_X_AUTH_TOKEN'] 
        ?? ($_SERVER['HTTP_X_SESSION_ID'] 
        ?? ($_GET['auth_token'] ?? null))))));
}

function validateInvestorAuthToken(string $token): ?array {
    $token = trim($token);
    $parts = explode('.', $token);
    if (count($parts) !== 2) {
        return null;
    }
    [$encoded, $sig] = $parts;
    $expectedSig = hash_hmac('sha256', $encoded, getRateLimitSecret());
    if (!hash_equals($expectedSig, $sig)) {
        return null;
    }

    $json = base64_decode(strtr($encoded, '-_', '+/'));
    if (!$json) {
        return null;
    }
    $payload = json_decode($json, true);
    if (!is_array($payload) || empty($payload['sub']) || empty($payload['exp'])) {
        return null;
    }

    if ($payload['exp'] < time()) {
        return null;
    }

    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT id, account_type, email, full_name, status FROM investors WHERE id = ? LIMIT 1");
        $stmt->execute([(int)$payload['sub']]);
        $inv = $stmt->fetch();
        if (!$inv || ($inv['status'] ?? '') === 'suspended') {
            return null;
        }

        return [
            'id' => (int)$inv['id'],
            'type' => $inv['account_type'],
            'email' => $inv['email'],
            'name' => $inv['full_name'] ?? '',
            'login_time' => date('c', $payload['iat'] ?? time()),
            'ip' => getClientIp()
        ];
    } catch (Throwable $e) {
        return [
            'id' => (int)$payload['sub'],
            'type' => $payload['type'] ?? 'perorangan',
            'email' => $payload['email'] ?? '',
            'name' => $payload['name'] ?? '',
            'login_time' => date('c', $payload['iat'] ?? time()),
            'ip' => getClientIp()
        ];
    }
}

function getInvestorSession(): ?array {
    if (!empty($_SESSION['mgi_investor_logged_in']) && !empty($_SESSION['mgi_investor_id'])) {
        return [
            'id' => $_SESSION['mgi_investor_id'],
            'type' => $_SESSION['mgi_investor_type'],
            'email' => $_SESSION['mgi_investor_email'],
            'name' => $_SESSION['mgi_investor_name'],
            'login_time' => $_SESSION['mgi_investor_login_time'] ?? null,
            'ip' => $_SESSION['mgi_investor_ip'] ?? null
        ];
    }

    // Support token-based auth for mobile apps and API clients
    $token = getBearerToken();
    if ($token) {
        $investor = validateInvestorAuthToken($token);
        if ($investor) {
            return $investor;
        }
    }

    return null;
}

function requireInvestorAuth(): array {
    $investor = getInvestorSession();
    if (!$investor) {
        require_once __DIR__ . '/response.php';
        sendJsonError('Sesi investor belum aktif atau telah kedaluwarsa. Silakan masuk terlebih dahulu.', 401);
    }
    return $investor;
}

function clearInvestorSession(): void {
    unset($_SESSION['mgi_investor_logged_in']);
    unset($_SESSION['mgi_investor_id']);
    unset($_SESSION['mgi_investor_type']);
    unset($_SESSION['mgi_investor_email']);
    unset($_SESSION['mgi_investor_name']);
    unset($_SESSION['mgi_investor_login_time']);
    unset($_SESSION['mgi_investor_ip']);
}

function logoutInvestor(): void {
    clearInvestorSession();
}

// AUDIT LOG HELPER
function logAdminActivity(string $action, string $targetType, ?string $targetId = null, ?string $details = null): void {
    try {
        $db = getDB();
        $admin = getAdminSession();
        $adminId = $admin ? $admin['id'] : null;
        $ip = getClientIp();

        $stmt = $db->prepare("
            INSERT INTO admin_logs (admin_id, action, target_type, target_id, details, ip_address)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$adminId, $action, $targetType, $targetId, $details, $ip]);
    } catch (Exception $e) {
        // Silently continue if log fails so primary transaction isn't blocked
        error_log("[MGI Admin Log Error] " . $e->getMessage());
    }
}

