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
    if ($method === 'GET') {
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

        // Simpan data baru ke data/growth.json
        $dataDir = dirname($dataFile);
        if (!is_dir($dataDir)) {
            @mkdir($dataDir, 0775, true);
        }
        if (file_exists($dataFile) && !is_writable($dataFile)) {
            @chmod($dataFile, 0664);
        }

        $encoded = json_encode($input, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $saved = @file_put_contents($dataFile, $encoded);
        if ($saved === false) {
            sendJsonError('Gagal menyimpan file data pertumbuhan. Periksa permission folder data/ di server hosting (jalankan: chmod 775 data && chmod 664 data/growth.json).', 500);
        }

        // Catat aktivitas admin
        logAdminActivity('update', 'mgi_growth_data', 'all', "Admin {$admin['username']} memperbarui data grafik pertumbuhan & KPI performa");

        sendJsonResponse($input, 200, 'Data pertumbuhan & KPI persentase berhasil disimpan dan diperbarui!');
    }

    sendJsonError('Metode HTTP tidak didukung.', 405);

} catch (Exception $e) {
    sendJsonError('Terjadi kesalahan server: ' . $e->getMessage(), 500);
}
