<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — API: COMPANY PROFILE
 * Method: GET
 */

require_once __DIR__ . '/../backend/config/db.php';
require_once __DIR__ . '/../backend/helpers/response.php';

try {
    $db = getDB();
    $stmt = $db->prepare("SELECT setting_value FROM company_profile WHERE setting_key = 'main_profile' LIMIT 1");
    $stmt->execute();
    $row = $stmt->fetch();

    if ($row && !empty($row['setting_value'])) {
        $data = json_decode($row['setting_value'], true);
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    // Fallback to static JSON if database empty
    $staticFile = __DIR__ . '/../data/company-profile.json';
    if (file_exists($staticFile)) {
        header('Content-Type: application/json; charset=utf-8');
        readfile($staticFile);
        exit;
    }

    sendJsonError('Data profil perusahaan tidak ditemukan.', 404);
} catch (Exception $e) {
    // Fallback if DB error
    $staticFile = __DIR__ . '/../data/company-profile.json';
    if (file_exists($staticFile)) {
        header('Content-Type: application/json; charset=utf-8');
        readfile($staticFile);
        exit;
    }
    sendJsonError('Gagal memuat profil perusahaan: ' . $e->getMessage(), 500);
}
