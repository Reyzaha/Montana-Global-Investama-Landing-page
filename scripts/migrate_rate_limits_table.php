<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — MIGRATION: PERSISTENT RATE LIMITS TABLE
 * Menambahkan tabel security_rate_limits untuk perlindungan brute-force persisten
 * di luar sesi PHP (tahan request tanpa cookie / multi-client).
 *
 * Jalankan via CLI:
 * C:\xampp\php\php.exe scripts/migrate_rate_limits_table.php
 */

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    die(json_encode(['error' => 'Hanya dapat dijalankan via CLI.']));
}

require_once __DIR__ . '/../backend/config/env.php';
require_once __DIR__ . '/../backend/config/db.php';

echo "=================================================================\n";
echo "   MGI MIGRATION: TABEL SECURITY_RATE_LIMITS (PERSISTEN)       \n";
echo "=================================================================\n\n";

try {
    $db = getDB();
    echo "[✓] Berhasil terhubung ke database MySQL.\n";

    $sql = "
    CREATE TABLE IF NOT EXISTS `security_rate_limits` (
        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `rate_key` VARCHAR(64) NOT NULL COMMENT 'SHA-256 hash dari action+identifier+IP',
        `action_type` VARCHAR(32) NOT NULL,
        `identifier` VARCHAR(128) NOT NULL DEFAULT '',
        `ip_address` VARCHAR(45) NOT NULL,
        `attempts` INT UNSIGNED NOT NULL DEFAULT 1,
        `first_attempt_at` INT UNSIGNED NOT NULL,
        `last_attempt_at` INT UNSIGNED NOT NULL,
        `locked_until` INT UNSIGNED NOT NULL DEFAULT 0,
        `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uq_rate_key` (`rate_key`),
        INDEX `idx_action_id` (`action_type`, `identifier`),
        INDEX `idx_ip` (`ip_address`),
        INDEX `idx_lock` (`locked_until`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";

    $db->exec($sql);
    echo "[✓] Tabel `security_rate_limits` berhasil dibuat / diverifikasi.\n";

    // Verifikasi skema tabel
    $stmt = $db->query("DESCRIBE `security_rate_limits`");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "[✓] Struktur kolom terverifikasi: " . implode(', ', $columns) . "\n\n";
    echo "[SUCCESS] Migrasi rate limiting persisten selesai tanpa merusak data yang ada.\n";
    exit(0);

} catch (Exception $e) {
    echo "[ERROR] Migrasi gagal: " . $e->getMessage() . "\n";
    exit(1);
}
