<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — API: PUBLIC GROWTH & METRICS DATA
 * Method: GET
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

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
