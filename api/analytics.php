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

    $stmt = $db->prepare("
        INSERT INTO analytics_events (event_type, page_url, element_id, ip_address, user_agent, metadata_json, created_at)
        VALUES (?, ?, ?, ?, ?, ?, NOW())
    ");
    $stmt->execute([$eventType, $pageUrl, $elementId, $ipAddress, $userAgent, $metadata]);

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
