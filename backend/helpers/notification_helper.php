<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — NOTIFICATION HELPER
 * Utility for real-time notifications between Admin and Investor
 */

function createNotification(
    PDO $db,
    int $investorId,
    ?string $projectId,
    string $type,
    string $title,
    string $message,
    ?string $linkUrl = null
): bool {
    try {
        $stmt = $db->prepare("
            INSERT INTO notifications (investor_id, project_id, type, title, message, link_url, is_read, created_at)
            VALUES (?, ?, ?, ?, ?, ?, 0, NOW())
        ");
        $res = $stmt->execute([$investorId, $projectId, $type, $title, $message, $linkUrl]);

        // Touch project cache stream if project_id is provided for instant SSE detection
        if (!empty($projectId)) {
            $cacheDir = __DIR__ . '/../cache';
            if (!is_dir($cacheDir)) {
                @mkdir($cacheDir, 0777, true);
            }
            @file_put_contents($cacheDir . "/project_{$projectId}_stream.txt", time() . '|' . microtime(true));
        }

        // Dispatch FCM Push Notification to registered investor devices
        dispatchPushNotificationToInvestor($db, $investorId, $title, $message, $linkUrl, [
            'type' => $type,
            'project_id' => $projectId ?? '',
            'notification_id' => (string)$db->lastInsertId()
        ]);

        return $res;
    } catch (Throwable $e) {
        error_log("Failed to create notification: " . $e->getMessage());
        return false;
    }
}

function getUnreadNotificationsCount(PDO $db, int $investorId): int {
    try {
        $stmt = $db->prepare("SELECT COUNT(*) FROM notifications WHERE investor_id = ? AND is_read = 0");
        $stmt->execute([$investorId]);
        return (int)$stmt->fetchColumn();
    } catch (Throwable $e) {
        return 0;
    }
}

function getInvestorNotifications(PDO $db, int $investorId, int $limit = 30): array {
    try {
        $stmt = $db->prepare("
            SELECT n.*, p.title as project_title
            FROM notifications n
            LEFT JOIN projects p ON n.project_id = p.id
            WHERE n.investor_id = ?
            ORDER BY n.created_at DESC, n.id DESC
            LIMIT ?
        ");
        $stmt->bindValue(1, $investorId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        return [];
    }
}

function markNotificationAsRead(PDO $db, int $notificationId, int $investorId): bool {
    try {
        $stmt = $db->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND investor_id = ?");
        return $stmt->execute([$notificationId, $investorId]);
    } catch (Throwable $e) {
        return false;
    }
}

function markAllNotificationsAsRead(PDO $db, int $investorId): bool {
    try {
        $stmt = $db->prepare("UPDATE notifications SET is_read = 1 WHERE investor_id = ?");
        return $stmt->execute([$investorId]);
    } catch (Throwable $e) {
        return false;
    }
}

/**
 * Dispatches Push Notification to registered FCM device tokens of the investor.
 * Compatible with Flutter FirebaseCloudMessagings handler.
 */
function dispatchPushNotificationToInvestor(
    PDO $db,
    int $investorId,
    string $title,
    string $body,
    ?string $linkUrl = null,
    array $extraData = []
): bool {
    try {
        $stmt = $db->prepare("SELECT id, fcm_token, device_type FROM investor_device_tokens WHERE investor_id = ?");
        $stmt->execute([$investorId]);
        $tokens = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($tokens)) {
            return false;
        }

        $fcmServerKey = $_ENV['FCM_SERVER_KEY'] ?? getenv('FCM_SERVER_KEY') ?: '';

        // If FCM Server Key is not configured in environment, record log and exit safely
        if (empty($fcmServerKey)) {
            error_log("[MGI FCM Dispatch] Notice: FCM_SERVER_KEY is not configured yet in .env. Skipping external push API.");
            return false;
        }

        $url = 'https://fcm.googleapis.com/fcm/send';

        foreach ($tokens as $tRecord) {
            $deviceToken = $tRecord['fcm_token'];

            $payload = [
                'to' => $deviceToken,
                'priority' => 'high',
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                    'sound' => 'default',
                    'badge' => 1
                ],
                'data' => array_merge([
                    'notification.title' => $title,
                    'notification.body' => $body,
                    'notification.payload' => $linkUrl ?? '',
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                    'link_url' => $linkUrl ?? '',
                    'investor_id' => (string)$investorId,
                    'timestamp' => (string)time()
                ], $extraData)
            ];

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => [
                    'Authorization: key=' . $fcmServerKey,
                    'Content-Type: application/json'
                ],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_POSTFIELDS => json_encode($payload),
                CURLOPT_TIMEOUT => 5
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            // Clean up invalid or unregistered tokens
            if ($response && ($httpCode === 200 || $httpCode === 400 || $httpCode === 404)) {
                $resData = json_decode($response, true);
                if (!empty($resData['results'][0]['error'])) {
                    $err = $resData['results'][0]['error'];
                    if (in_array($err, ['NotRegistered', 'InvalidRegistration', 'MismatchSenderId'])) {
                        $del = $db->prepare("DELETE FROM investor_device_tokens WHERE id = ?");
                        $del->execute([$tRecord['id']]);
                    }
                }
            }
        }

        return true;
    } catch (Throwable $e) {
        error_log("[MGI FCM Dispatch] Error: " . $e->getMessage());
        return false;
    }
}

