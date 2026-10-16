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

    // 2. Data Kredensial Baru Superadmin & Admin
    $password = 'Montana@2026!Secure';
    $hash     = hashPassword($password);

    // 3. Upsert kedua akun (superadmin & admin): password di-update ke Montana@2026!Secure, MFA dimatikan (mfa_enabled = 0 & mfa_secret = NULL)
    // Dengan mfa_enabled = 0, sistem otomatis menampilkan QR Code baru saat login berikutnya.
    $accounts = [
        ['username' => 'superadmin', 'email' => 'superadmin@montanaglobalinvestama.com'],
        ['username' => 'admin',      'email' => 'admin@montanaglobalinvestama.com']
    ];

    $stmt = $db->prepare("
        INSERT INTO admin_users (username, email, password_hash, full_name, role, is_active, mfa_secret, mfa_enabled)
        VALUES (?, ?, ?, 'Super Administrator MGI', 'superadmin', 1, NULL, 0)
        ON DUPLICATE KEY UPDATE 
            password_hash = VALUES(password_hash),
            email = VALUES(email),
            role = 'superadmin',
            is_active = 1,
            mfa_secret = NULL,
            mfa_enabled = 0
    ");

    foreach ($accounts as $acc) {
        $stmt->execute([$acc['username'], $acc['email'], $hash]);
    }

    echo "[✓] Akun 'superadmin' dan 'admin' berhasil diperbarui & MFA berhasil di-reset!\n\n";
    echo "=================================================================\n";
    echo " KREDENSIAL LOGIN ADMINISTRATOR MGI:\n";
    echo "  - URL Login  : http://<IP_ATAU_DOMAIN>/portal-admin-mgi-gateway/login.php\n";
    echo "  - Username   : superadmin (atau admin)\n";
    echo "  - Password   : {$password}\n";
    echo "  - Status MFA : RESET (Siap scan QR Code Google Authenticator baru)\n";
    echo "  - Rate Limit : BERSIH (Blokir 15 menit telah dibuka langsung)\n";
    echo "=================================================================\n\n";
    echo "Langkah selanjutnya:\n";
    echo "1. Buka halaman login di browser Anda.\n";
    echo "2. Masukkan Username dan Password di atas, lalu klik 'Lanjutkan Autentikasi'.\n";
    echo "3. Barcode QR Code baru akan otomatis muncul di layar.\n";
    echo "4. Buka aplikasi Google Authenticator di HP, scan QR Code tersebut.\n";
    echo "5. Masukkan 6 digit kode yang muncul di HP untuk verifikasi dan masuk.\n\n";

} catch (Exception $e) {
    echo "\n[✕] GAGAL MERESET SUPERADMIN: " . $e->getMessage() . "\n";
    exit(1);
}
