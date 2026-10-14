<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — API: INVESTOR NOTIFICATIONS
 * Handles:
 * - GET  /api/investor/notifications.php -> returns unread count and list of notifications
 * - POST /api/investor/notifications.php?action=mark_read&id=123
 * - POST /api/investor/notifications.php?action=mark_all_read
 */

require_once __DIR__ . '/../../backend/config/db.php';
require_once __DIR__ . '/../../backend/helpers/response.php';
require_once __DIR__ . '/../../backend/helpers/auth_helper.php';
require_once __DIR__ . '/../../backend/helpers/notification_helper.php';

$session = getInvestorSession();
if (!$session) {
    sendJsonError('Sesi investor belum aktif atau telah kedaluwarsa.', 401);
}
$investorId = (int)$session['id'];
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? 'list';

try {
    $db = getDB();

    if ($method === 'GET') {
        $limit = isset($_GET['limit']) ? max(1, min(100, (int)$_GET['limit'])) : 30;
        $notifications = getInvestorNotifications($db, $investorId, $limit);
        $unreadCount = getUnreadNotificationsCount($db, $investorId);

        sendJsonResponse([
            'unread_count' => $unreadCount,
            'total' => count($notifications),
            'notifications' => $notifications
        ]);
    }

    if ($method === 'POST') {
        $input = getJsonInput();

        if ($action === 'mark_read') {
            $notifId = (int)($input['id'] ?? ($_GET['id'] ?? 0));
            if (!$notifId) {
                sendJsonError('ID notifikasi tidak valid.');
            }
            markNotificationAsRead($db, $notifId, $investorId);
            $unreadCount = getUnreadNotificationsCount($db, $investorId);
            sendJsonResponse(['unread_count' => $unreadCount], 200, 'Notifikasi berhasil ditandai sebagai dibaca.');
        }

        if ($action === 'mark_all_read') {
            markAllNotificationsAsRead($db, $investorId);
            sendJsonResponse(['unread_count' => 0], 200, 'Seluruh notifikasi telah ditandai sebagai dibaca.');
        }

        sendJsonError('Aksi tidak didukung.', 400);
    }

    sendJsonError('Method tidak didukung.', 405);

} catch (Throwable $e) {
    sendJsonError('Gagal memproses notifikasi: ' . $e->getMessage(), 500);
}
