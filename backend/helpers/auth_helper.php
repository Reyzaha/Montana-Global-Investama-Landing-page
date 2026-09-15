<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — AUTHENTICATION & SECURITY HELPER
 * Session management, password verification, CSRF, rate-limiting, and audit logging.
 */

if (session_status() === PHP_SESSION_NONE) {
    $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
    
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
    $sessionToken = $_SESSION['mgi_csrf_token'] ?? '';
    if (empty($sessionToken)) {
        return false;
    }

    if ($token === null) {
        // Read from header X-CSRF-Token or request body
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

// RATE LIMITING HELPERS (Brute Force Protection)
function getClientIp(): string {
    if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) return $_SERVER['HTTP_CF_CONNECTING_IP'];
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        return trim($ips[0]);
    }
    return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
}

function checkRateLimit(string $actionKey, int $maxAttempts = 5, int $decaySeconds = 900): bool {
    $ip = getClientIp();
    $storageKey = 'rl_' . md5($actionKey . '_' . $ip);
    
    if (isset($_SESSION[$storageKey])) {
        $record = $_SESSION[$storageKey];
        if (time() < $record['reset_at']) {
            if ($record['attempts'] >= $maxAttempts) {
                return false; // Rate limit terlampaui
            }
        } else {
            // Waktu blokir telah habis, reset counter
            unset($_SESSION[$storageKey]);
        }
    }
    return true;
}

function recordFailedAttempt(string $actionKey, int $decaySeconds = 900): int {
    $ip = getClientIp();
    $storageKey = 'rl_' . md5($actionKey . '_' . $ip);
    
    if (!isset($_SESSION[$storageKey]) || time() >= $_SESSION[$storageKey]['reset_at']) {
        $_SESSION[$storageKey] = [
            'attempts' => 1,
            'reset_at' => time() + $decaySeconds
        ];
    } else {
        $_SESSION[$storageKey]['attempts']++;
    }
    
    return $_SESSION[$storageKey]['attempts'];
}

function clearRateLimit(string $actionKey): void {
    $ip = getClientIp();
    $storageKey = 'rl_' . md5($actionKey . '_' . $ip);
    unset($_SESSION[$storageKey]);
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

