<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — REALTIME FALLBACK CHECK / ETAG ENDPOINT
 * Lightweight polling fallback when Server-Sent Events (SSE) cannot connect.
 * Supports HTTP 304 Not Modified for maximum bandwidth efficiency.
 */

require_once __DIR__ . '/../../backend/config/db.php';
require_once __DIR__ . '/../../backend/helpers/realtime_helper.php';
require_once __DIR__ . '/../../backend/helpers/response.php';

$sinceId = isset($_GET['since_id']) ? (int)$_GET['since_id'] : 0;
$state = getLatestRealtimeState();
$currentLastId = (int)$state['last_event_id'];

// Generate dynamic ETag
$etag = '"mgi-rev-' . $currentLastId . '"';
header('ETag: ' . $etag);
header('Cache-Control: public, no-cache');

if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && trim($_SERVER['HTTP_IF_NONE_MATCH']) === $etag && $sinceId >= $currentLastId) {
    http_response_code(304);
    exit;
}

$events = [];
if ($sinceId < $currentLastId) {
    $events = getEventsSince($sinceId, 25);
}

sendJsonResponse([
    'has_changes'   => !empty($events),
    'current_rev'   => $currentLastId,
    'last_event_id' => $currentLastId,
    'events'        => $events,
    'timestamp'     => date('c')
], 200, 'Status revisi realtime berhasil diperiksa.');
