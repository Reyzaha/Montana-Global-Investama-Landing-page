<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — API: COMPANY PROFILE
 * Method: GET
 */

require_once __DIR__ . '/../backend/config/db.php';
require_once __DIR__ . '/../backend/helpers/response.php';

try {
    $staticFile = __DIR__ . '/../data/company-profile.json';
    
    // Check if database has updated record and sync if needed
    $db = null;
    try {
        $db = getDB();
    } catch (Exception $dbEx) {}

    // Priority to latest static file data if exists
    if (file_exists($staticFile)) {
        $jsonContent = file_get_contents($staticFile);
        
        // Sync to database if connected
        if ($db) {
            try {
                $syncStmt = $db->prepare("INSERT INTO company_profile (setting_key, setting_value) VALUES ('main_profile', :val) ON DUPLICATE KEY UPDATE setting_value = :val2");
                $syncStmt->execute([':val' => $jsonContent, ':val2' => $jsonContent]);
            } catch (Exception $e) {}
        }
        
        header('Content-Type: application/json; charset=utf-8');
        echo $jsonContent;
        exit;
    }

    if ($db) {
        $stmt = $db->prepare("SELECT setting_value FROM company_profile WHERE setting_key = 'main_profile' LIMIT 1");
        $stmt->execute();
        $row = $stmt->fetch();

        if ($row && !empty($row['setting_value'])) {
            header('Content-Type: application/json; charset=utf-8');
            echo $row['setting_value'];
            exit;
        }
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
