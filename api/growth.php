<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — API: PUBLIC GROWTH & METRICS DATA
 * Method: GET
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// 1. Cek database MySQL terlebih dahulu
try {
    require_once __DIR__ . '/../backend/config/db.php';
    $db = getDB();
    $stmt = $db->prepare("SELECT setting_value FROM system_settings WHERE setting_key = 'growth_json_data' LIMIT 1");
    $stmt->execute();
    $val = $stmt->fetchColumn();
    if (!empty($val)) {
        echo $val;
        exit;
    }
} catch (Exception $e) {
    // Lanjut fallback ke file statis
}

$dataFile = __DIR__ . '/../data/growth.json';

if (file_exists($dataFile)) {
    echo file_get_contents($dataFile);
    exit;
}

// Fallback JSON jika file belum ada
echo json_encode([
    'status' => 'error',
    'message' => 'Data pertumbuhan belum diinisialisasi.'
], JSON_UNESCAPED_UNICODE);
exit;
