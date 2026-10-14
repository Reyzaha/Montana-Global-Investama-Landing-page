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
