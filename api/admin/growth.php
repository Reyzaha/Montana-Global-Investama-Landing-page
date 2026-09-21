<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — API: ADMIN GROWTH & PERFORMANCE MANAGEMENT
 * Method: GET, POST
 */

require_once __DIR__ . '/../../backend/config/db.php';
require_once __DIR__ . '/../../backend/helpers/response.php';
require_once __DIR__ . '/../../backend/helpers/auth_helper.php';

$admin = requireAdminAuth();
$dataFile = __DIR__ . '/../../data/growth.json';
$method = $_SERVER['REQUEST_METHOD'];

try {
    $db = getDB();

    if ($method === 'GET') {
        // Cek database MySQL terlebih dahulu
        try {
            $stmt = $db->prepare("SELECT setting_value FROM system_settings WHERE setting_key = 'growth_json_data' LIMIT 1");
            $stmt->execute();
            $val = $stmt->fetchColumn();
            if (!empty($val)) {
                $json = json_decode($val, true);
                if ($json && isset($json['years'])) {
                    sendJsonResponse($json);
                }
            }
        } catch (Exception $e) {
            // Abaikan jika tabel belum ada, lanjut ke file
        }

        if (file_exists($dataFile)) {
            $json = json_decode(file_get_contents($dataFile), true);
            sendJsonResponse($json);
        } else {
            sendJsonError('File data pertumbuhan tidak ditemukan.', 404);
        }
    }

    if ($method === 'POST') {
        $input = getJsonInput();
        if (!$input || !isset($input['years'])) {
            sendJsonError('Format payload data tidak valid.', 400);
        }

        $encoded = json_encode($input, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $dbSaved = false;

        // 1. Simpan ke database MySQL agar tidak terpengaruh batasan permission file Linux
        try {
            $db->exec("
                CREATE TABLE IF NOT EXISTS `system_settings` (
                  `setting_key` VARCHAR(100) PRIMARY KEY,
                  `setting_value` LONGTEXT NOT NULL,
                  `description` VARCHAR(255) NULL,
                  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
            $stmtSet = $db->prepare("
                INSERT INTO system_settings (setting_key, setting_value, description)
                VALUES ('growth_json_data', ?, 'Data Grafik Pertumbuhan & Ringkasan KPI')
                ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = CURRENT_TIMESTAMP
            ");
            $stmtSet->execute([$encoded]);
            $dbSaved = true;
        } catch (Exception $e) {
            error_log("[MGI Growth DB Save Error] " . $e->getMessage());
        }

        // 2. Simpan juga ke file data/growth.json jika writable
        $dataDir = dirname($dataFile);
        if (!is_dir($dataDir)) {
            @mkdir($dataDir, 0777, true);
        }
        $fileSaved = @file_put_contents($dataFile, $encoded);

        // Jika salah satu (Database atau File) berhasil, anggap sukses!
        if (!$dbSaved && $fileSaved === false) {
            sendJsonError('Gagal menyimpan file data pertumbuhan. Periksa koneksi database atau permission file.', 500);
        }

        // Catat aktivitas admin
        logAdminActivity('update', 'mgi_growth_data', 'all', "Admin {$admin['username']} memperbarui data grafik pertumbuhan & KPI performa");

        sendJsonResponse($input, 200, 'Data pertumbuhan & KPI persentase berhasil disimpan dan diperbarui!');
    }

    sendJsonError('Metode HTTP tidak didukung.', 405);

} catch (Exception $e) {
    sendJsonError('Terjadi kesalahan server: ' . $e->getMessage(), 500);
}
