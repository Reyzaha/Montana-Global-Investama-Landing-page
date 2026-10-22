<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — API: INVESTOR DEVICE TOKEN (FCM)
 * Handles:
 * - POST /api/investor/device_token.php -> register/update FCM device token
 * - POST /api/investor/device_token.php?action=delete -> remove FCM device token on sign out
 */

require_once __DIR__ . '/../../backend/config/db.php';
require_once __DIR__ . '/../../backend/helpers/response.php';
require_once __DIR__ . '/../../backend/helpers/auth_helper.php';

$session = getInvestorSession();
if (!$session) {
    sendJsonError('Sesi investor belum aktif atau telah kedaluwarsa.', 401);
}
$investorId = (int)$session['id'];
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? 'save';

if ($method !== 'POST') {
    sendJsonError('Method harus POST.', 405);
}

try {
    $db = getDB();
    $input = getJsonInput();
    $fcmToken = trim($input['fcm_token'] ?? ($input['token'] ?? ''));

    if (empty($fcmToken)) {
        sendJsonError('Token perangkat (FCM) wajib disertakan.', 400);
    }

    if ($action === 'delete') {
        $stmt = $db->prepare("DELETE FROM investor_device_tokens WHERE investor_id = ? AND fcm_token = ?");
        $stmt->execute([$investorId, $fcmToken]);
        sendJsonResponse(null, 200, 'Token perangkat berhasil dihapus.');
    }

    $deviceType = trim(strtolower($input['device_type'] ?? 'android'));
    if (!in_array($deviceType, ['android', 'ios', 'web'])) {
        $deviceType = 'android';
    }

    $stmt = $db->prepare("
        INSERT INTO investor_device_tokens (investor_id, fcm_token, device_type, updated_at)
        VALUES (?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE
            investor_id = VALUES(investor_id),
            device_type = VALUES(device_type),
            updated_at = NOW()
    ");
    $stmt->execute([$investorId, $fcmToken, $deviceType]);

    sendJsonResponse([
        'investor_id' => $investorId,
        'device_type' => $deviceType,
        'registered' => true
    ], 200, 'Token perangkat investor berhasil didaftarkan untuk notifikasi.');

} catch (Throwable $e) {
    sendJsonError('Gagal memproses token perangkat: ' . $e->getMessage(), 500);
}
