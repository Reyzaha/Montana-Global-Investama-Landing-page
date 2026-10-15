<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — API: PUBLIC ANALYTICS BEACON TRACKER
 * Method: POST
 * Lightweight telemetry tracking for page views, CTA clicks, and conversion events.
 */

require_once __DIR__ . '/../backend/config/db.php';
require_once __DIR__ . '/../backend/helpers/response.php';
require_once __DIR__ . '/../backend/helpers/realtime_helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonError('Metode HTTP tidak didukung. Gunakan POST.', 405);
}

$input = getJsonInput();

$eventType = trim($input['event_type'] ?? 'page_view');
if (!in_array($eventType, ['page_view', 'cta_click', 'conversion'])) {
    $eventType = 'page_view';
}

$pageUrl = trim($input['page_url'] ?? ($_SERVER['HTTP_REFERER'] ?? '/'));
$elementId = trim($input['element_id'] ?? '');
$metadata = isset($input['metadata']) && is_array($input['metadata']) ? json_encode($input['metadata']) : null;

$ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $ipParts = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
    $ipAddress = trim($ipParts[0]);
}
$userAgent = mb_substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255);

try {
    $db = getDB();

    $cols = [];
    try {
        $cols = $db->query("SHOW COLUMNS FROM analytics_events")->fetchAll(PDO::FETCH_COLUMN);
        if (!in_array('page_url', $cols)) {
            $db->exec("ALTER TABLE analytics_events ADD COLUMN page_url VARCHAR(255) NULL AFTER event_type");
            if (in_array('page_path', $cols)) {
                $db->exec("UPDATE analytics_events SET page_url = page_path WHERE page_url IS NULL");
            }
            $cols[] = 'page_url';
        }
        if (!in_array('metadata_json', $cols)) {
            $db->exec("ALTER TABLE analytics_events ADD COLUMN metadata_json TEXT NULL AFTER user_agent");
            $cols[] = 'metadata_json';
        }
    } catch (Throwable $ignore) {}

    $fields = ['event_type'];
    $placeholders = ['?'];
    $params = [$eventType];

    if (in_array('event_name', $cols)) {
        $fields[] = 'event_name';
        $placeholders[] = '?';
        $params[] = $eventType;
    }
    if (in_array('page_path', $cols)) {
        $fields[] = 'page_path';
        $placeholders[] = '?';
        $params[] = $pageUrl;
    }
    if (in_array('page_url', $cols)) {
        $fields[] = 'page_url';
        $placeholders[] = '?';
        $params[] = $pageUrl;
    }
    if (in_array('element_id', $cols)) {
        $fields[] = 'element_id';
        $placeholders[] = '?';
        $params[] = $elementId;
    }
    if (in_array('ip_address', $cols)) {
        $fields[] = 'ip_address';
        $placeholders[] = '?';
        $params[] = $ipAddress;
    }
    if (in_array('user_agent', $cols)) {
        $fields[] = 'user_agent';
        $placeholders[] = '?';
        $params[] = $userAgent;
    }
    if (in_array('metadata_json', $cols)) {
        $fields[] = 'metadata_json';
        $placeholders[] = '?';
        $params[] = $metadata;
    }

    $fieldsStr = implode(', ', $fields) . ', created_at';
    $placeholdersStr = implode(', ', $placeholders) . ', NOW()';

    $stmt = $db->prepare("INSERT INTO analytics_events ($fieldsStr) VALUES ($placeholdersStr)");
    $stmt->execute($params);

    if ($eventType === 'cta_click' || $eventType === 'conversion') {
        triggerRealtimeEvent('analytics_ping', 'analytics', 'record', [
            'event_type' => $eventType,
            'page_url'   => $pageUrl,
            'element_id' => $elementId
        ]);
    }

    sendJsonResponse(['recorded' => true], 200, 'Event tercatat.');

} catch (Throwable $e) {
    // Analytics errors should fail silently to avoid impacting user experience
    sendJsonResponse(['recorded' => false, 'error' => $e->getMessage()], 200);
}
