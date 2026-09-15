<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — DATABASE PURGE & CLEANUP UTILITY
 * Membersihkan seluruh data dummy, akun investor demo, portofolio, dan rekening bank.
 * Menyiapkan sistem siap produksi (clean production state) untuk Docker / VPS Hostinger.
 * Menjaga akun Administrator agar siap di-setup MFA Google Authenticator.
 *
 * Jalankan via CLI:
 * C:\xampp\php\php.exe database/clean_dummy.php
 */

require_once __DIR__ . '/../backend/config/db.php';
require_once __DIR__ . '/../backend/helpers/auth_helper.php';
require_once __DIR__ . '/../backend/helpers/totp_helper.php';

echo "=================================================================\n";
echo "   PT MONTANA GLOBAL INVESTAMA — PEMBERSIHAN TOTAL DATA DUMMY   \n";
echo "=================================================================\n\n";

try {
    $db = getDB();
    echo "[✓] Terhubung ke database MySQL (Port: " . Database::getConnectedPort() . ")\n";

    // 1. Pastikan Skema dan Kolom MFA pada admin_users sudah ada
    $schemaFile = __DIR__ . '/schema.sql';
    if (file_exists($schemaFile)) {
        $sql = file_get_contents($schemaFile);
        $db->exec($sql);
        echo "[✓] Skema database diverifikasi & disinkronkan.\n";
    }

    // Periksa kolom mfa_secret & mfa_enabled jika tabel sudah ada dari versi sebelumnya
    try {
        $db->exec("ALTER TABLE admin_users ADD COLUMN IF NOT EXISTS `mfa_secret` VARCHAR(64) NULL AFTER `is_active`");
        $db->exec("ALTER TABLE admin_users ADD COLUMN IF NOT EXISTS `mfa_enabled` TINYINT(1) NOT NULL DEFAULT 0 AFTER `mfa_secret`");
    } catch (Exception $colErr) {
        // Abaikan jika sudah ada
    }

    // 2. Nonaktifkan FOREIGN_KEY_CHECKS sementara untuk truncate bersih
    $db->exec("SET FOREIGN_KEY_CHECKS = 0;");

    // 3. Bersihkan seluruh Data Investor & Akun Dummy
    $tablesToClean = [
        'investor_portfolios',
        'investor_bank_accounts',
        'investor_companies',
        'investors',
        'campaign_updates',
        'admin_logs'
    ];

    foreach ($tablesToClean as $table) {
        $db->exec("TRUNCATE TABLE `{$table}`");
        echo "[✓] Berhasil membersihkan (TRUNCATE) tabel: {$table}\n";
    }

    // Bersihkan juga rate_limits jika ada
    try {
        $db->exec("TRUNCATE TABLE `rate_limits`");
        echo "[✓] Berhasil membersihkan riwayat rate_limits\n";
    } catch (Exception $e) {}

    // 4. Bersihkan Proyek Dummy jika diminta kosong
    // Opsi: kita truncate atau kosongkan projects, project_details, funding_items, project_simulation
    $cleanProjects = true; // Bersihkan data dummy proyek
    if ($cleanProjects) {
        $projectTables = [
            'project_simulation',
            'funding_items',
            'project_details',
            'projects'
        ];
        foreach ($projectTables as $pTable) {
            $db->exec("TRUNCATE TABLE `{$pTable}`");
            echo "[✓] Berhasil membersihkan tabel proyek dummy: {$pTable}\n";
        }
    }

    // Aktifkan kembali FOREIGN_KEY_CHECKS
    $db->exec("SET FOREIGN_KEY_CHECKS = 1;");

    // 5. Setup / Reset Bersih Akun Super Administrator Siap MFA
    // Admin default disiapkan tanpa demo password bawaan lama atau reset mfa_enabled = 0
    // sehingga saat login pertama kali, sistem langsung mewajibkan SCAN QR CODE GOOGLE AUTHENTICATOR.
    $adminUser = 'admin';
    $adminEmail = 'admin@montanaglobalinvestama.com';
    $adminPass = 'Montana@2026!Secure'; // Kata sandi kuat production
    $adminHash = hashPassword($adminPass);

    // Hapus akun admin lama dan buat akun fresh
    $db->exec("DELETE FROM admin_users");
    
    // Generate fresh MFA Secret untuk admin
    $adminMfaSecret = GoogleAuthenticator::generateSecret(16);

    $stmtAdmin = $db->prepare("
        INSERT INTO admin_users (username, email, password_hash, full_name, role, is_active, mfa_secret, mfa_enabled)
        VALUES (?, ?, ?, 'Super Administrator MGI', 'superadmin', 1, ?, 0)
    ");
    $stmtAdmin->execute([$adminUser, $adminEmail, $adminHash, $adminMfaSecret]);

    echo "\n=================================================================\n";
    echo " [✓] SELURUH DATA DUMMY & AKUN DEMO TELAH BERHASIL DIBERSIHKAN!  \n";
    echo "=================================================================\n";
    echo "Kredensial Superadmin Fresh Siap MFA:\n";
    echo "  - Username     : {$adminUser}\n";
    echo "  - Email        : {$adminEmail}\n";
    echo "  - Password     : {$adminPass}\n";
    echo "  - MFA Secret   : {$adminMfaSecret}\n";
    echo "  - Status MFA   : WAJIB SCAN QR CODE (Google Authenticator) pada Login Pertama\n";
    echo "=================================================================\n\n";

} catch (Exception $e) {
    echo "\n[✕] GAGAL MEMBERSIHKAN DATABASE: " . $e->getMessage() . "\n";
    exit(1);
}
