<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — API: ADMIN SYSTEM SETTINGS
 * Methods: GET, POST
 */

require_once __DIR__ . '/../../backend/config/db.php';
require_once __DIR__ . '/../../backend/helpers/response.php';
require_once __DIR__ . '/../../backend/helpers/auth_helper.php';

$admin = requireAdminAuth();
$method = $_SERVER['REQUEST_METHOD'];

try {
    $db = getDB();

    if ($method === 'GET') {
        $stmt = $db->query("SELECT setting_key, setting_value, description FROM system_settings");
        $rows = $stmt->fetchAll();
        $settings = [];
        foreach ($rows as $r) {
            $settings[$r['setting_key']] = [
                'value' => $r['setting_value'],
                'description' => $r['description']
            ];
        }
        sendJsonResponse($settings);
    }

    if ($method === 'POST' || $method === 'PUT') {
        $input = getJsonInput();
        
        $stmtUp = $db->prepare("
            INSERT INTO system_settings (setting_key, setting_value)
            VALUES (?, ?)
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
        ");

        foreach ($input as $key => $val) {
            $valStr = is_bool($val) ? ($val ? '1' : '0') : (string)$val;
            $stmtUp->execute([$key, $valStr]);
        }

        logAdminActivity('update_settings', 'system_settings', null, 'Admin updated system settings');

        sendJsonResponse(null, 200, 'Pengaturan sistem berhasil diperbarui.');
    }

    sendJsonError('Method tidak didukung.', 405);

} catch (Exception $e) {
    sendJsonError('Terjadi kesalahan pengaturan sistem: ' . $e->getMessage(), 500);
}
