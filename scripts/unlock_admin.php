<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — CLI UNLOCK ADMINISTRATOR RATE LIMIT
 * 
 * Penggunaan CLI:
 * php scripts/unlock_admin.php --username=admin
 * php scripts/unlock_admin.php --ip=203.0.113.50
 * php scripts/unlock_admin.php --username=admin --ip=203.0.113.50
 * php scripts/unlock_admin.php --all
 */

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    die(json_encode(['error' => 'Hanya dapat dijalankan via CLI.']));
}

require_once __DIR__ . '/../backend/helpers/auth_helper.php';

$options = getopt('', ['username::', 'ip::', 'action::', 'all']);

$username = $options['username'] ?? null;
$ip = $options['ip'] ?? null;
$action = $options['action'] ?? 'admin_login';
$isAll = isset($options['all']);

echo "=================================================================\n";
echo "   MGI OPERATIONAL CLI: UNLOCK ADMINISTRATOR RATE LIMIT         \n";
echo "=================================================================\n\n";

if (empty($username) && empty($ip) && !$isAll) {
    echo "Petunjuk Penggunaan:\n";
    echo "  php scripts/unlock_admin.php --username=<admin_username>\n";
    echo "  php scripts/unlock_admin.php --ip=<client_ip_address>\n";
    echo "  php scripts/unlock_admin.php --username=<admin_username> --ip=<client_ip>\n";
    echo "  php scripts/unlock_admin.php --all\n\n";
    exit(1);
}

try {
    $db = getDB();

    if ($isAll) {
        $stmt = $db->prepare("DELETE FROM security_rate_limits WHERE action_type IN ('admin_login', 'admin_mfa')");
        $stmt->execute();
        $affected = $stmt->rowCount();
        echo "[✓] Berhasil mereset seluruh rate limit administrator ({$affected} baris dihapus).\n";
        exit(0);
    }

    // Buka kunci admin_login
    clearRateLimit('admin_login', $username, $ip);

    // Buka kunci admin_mfa jika ada username / ID
    if (!empty($username)) {
        // Cek ID admin jika username diberikan
        $stmtAdmin = $db->prepare("SELECT id FROM admin_users WHERE username = ? OR email = ? LIMIT 1");
        $stmtAdmin->execute([$username, $username]);
        $adminId = $stmtAdmin->fetchColumn();
        if ($adminId) {
            clearRateLimit('admin_mfa', 'admin_acc_' . (int)$adminId, $ip);
        }
    } else {
        clearRateLimit('admin_mfa', null, $ip);
    }

    echo "[✓] Rate limit untuk admin berhasil dibuka:\n";
    if ($username) {
        echo "    - Target Username/Akun : {$username}\n";
    }
    if ($ip) {
        echo "    - Target IP Address    : {$ip}\n";
    }
    echo "    - Status               : AKTIF KEMBALI\n\n";
    exit(0);

} catch (Exception $e) {
    echo "[ERROR] Gagal membuka kunci: " . $e->getMessage() . "\n";
    exit(1);
}
