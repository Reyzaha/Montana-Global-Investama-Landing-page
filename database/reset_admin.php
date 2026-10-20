<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — UTILITY RESET SUPERADMIN & MFA
 * Mengatur ulang akun Superadmin dan mereset Google Authenticator (MFA)
 * tanpa menghapus data investor, proyek, atau portofolio.
 *
 * Cara menjalankan di VPS (Docker):
 * docker exec -it mgi_web_app php database/reset_admin.php
 *
 * Cara menjalankan di Komputer Lokal (XAMPP):
 * C:\xampp\php\php.exe database/reset_admin.php
 */

require_once __DIR__ . '/../backend/config/env.php';

// Strict Execution Guard: CLI Only
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    header('Content-Type: application/json');
    die(json_encode(['status' => 'error', 'message' => 'Akses ditolak: Skrip reset admin hanya dapat dijalankan melalui terminal CLI.']));
}

require_once __DIR__ . '/../backend/config/db.php';
require_once __DIR__ . '/../backend/helpers/auth_helper.php';

echo "=================================================================\n";
echo "   PT MONTANA GLOBAL INVESTAMA — RESET SUPERADMIN & MFA (TOTP)   \n";
echo "=================================================================\n\n";

try {
    $db = getDB();
    echo "[✓] Terhubung ke database MySQL.\n";

    // 1. Reset Rate Limits (jika terkena blokir karena salah password berulang)
    try {
        $db->exec("TRUNCATE TABLE `rate_limits`");
        echo "[✓] Blokir percobaan login (rate_limits) telah dibersihkan.\n";
    } catch (Exception $e) {
        // Abaikan jika tabel belum dibuat
    }

    echo "[!] PEMBERITAHUAN KEAMANAN:\n";
    echo "    Untuk mematuhi standar keamanan, kata sandi tidak lagi di-hardcode di skrip ini.\n";
    echo "    Silakan jalankan skrip rotasi kredensial interaktif yang aman:\n";
    echo "    -> php scripts/rotate_admin_interactive.php\n\n";
    exit(0);
} catch (Exception $e) {
    echo "\n[✕] GAGAL: " . $e->getMessage() . "\n";
    exit(1);
}
