<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — REALTIME SSE STREAM (SERVER-SENT EVENTS)
 * High-performance, native HTTP streaming endpoint.
 * Pushes live database changes to frontend landing page and admin dashboard.
 */

require_once __DIR__ . '/../../backend/config/db.php';
require_once __DIR__ . '/../../backend/helpers/realtime_helper.php';

// Disable compression and buffering for real-time streaming
if (function_exists('apache_setenv')) {
    @apache_setenv('no-gzip', '1');
}
@ini_set('zlib.output_compression', '0');
@ini_set('implicit_flush', '1');
while (ob_get_level() > 0) {
    @ob_end_clean();
}

// Set SSE Headers
header('Content-Type: text/event-stream; charset=utf-8');
header('Cache-Control: no-cache, no-transform');
header('Connection: keep-alive');
header('X-Accel-Buffering: no'); // Prevents Nginx from buffering SSE events
header('Access-Control-Allow-Origin: *');

// Determine client's last known event ID
$lastEventId = 0;
if (isset($_SERVER['HTTP_LAST_EVENT_ID'])) {
    $lastEventId = (int)$_SERVER['HTTP_LAST_EVENT_ID'];
} elseif (isset($_GET['last_id'])) {
    $lastEventId = (int)$_GET['last_id'];
}

// Send initial connection handshake & retry hint (reconnect in 2 seconds on network drop)
echo "retry: 2000\n\n";
echo "event: connected\n";
echo "data: " . json_encode(['status' => 'online', 'server_time' => date('c'), 'last_event_id' => $lastEventId]) . "\n\n";
@ob_flush();
@flush();

$startTime = time();
$maxExecutionSeconds = 15; // 15s loop ensures PHP workers cycle quickly and don't block web concurrency
$lastPing = time();
$pingInterval = (int)(getenv('REALTIME_PING_INTERVAL') ?: 8);

// If client was disconnected and missed recent events, send backlog first
if ($lastEventId > 0) {
    $backlog = getEventsSince($lastEventId, 20);
    foreach ($backlog as $ev) {
        echo "id: {$ev['id']}\n";
        echo "event: {$ev['event_type']}\n";
        echo "data: " . json_encode($ev, JSON_UNESCAPED_UNICODE) . "\n\n";
        $lastEventId = $ev['id'];
    }
    @ob_flush();
    @flush();
} else {
    // Fresh connection: get current latest ID so client only receives future events
    $currentState = getLatestRealtimeState();
    $lastEventId = (int)$currentState['last_event_id'];
}

// Main streaming loop
while ((time() - $startTime) < $maxExecutionSeconds) {
    if (connection_aborted()) {
        break;
    }

    // Check for new events
    $newEvents = getEventsSince($lastEventId, 15);
    if (!empty($newEvents)) {
        foreach ($newEvents as $ev) {
            echo "id: {$ev['id']}\n";
            echo "event: {$ev['event_type']}\n";
            echo "data: " . json_encode($ev, JSON_UNESCAPED_UNICODE) . "\n\n";
            $lastEventId = $ev['id'];
        }
        @ob_flush();
        @flush();
    }

    // Periodic heartbeat comment to keep NAT and proxies alive
    if ((time() - $lastPing) >= $pingInterval) {
        echo ": ping " . time() . "\n\n";
        @ob_flush();
        @flush();
        $lastPing = time();
    }

    // Sleep 1 second between checks to keep CPU and MySQL load minimal
    usleep(1000000);
}

// Clean termination: tell browser to reconnect immediately
echo "event: heartbeat_reconnect\n";
echo "data: {\"last_event_id\": {$lastEventId}}\n\n";
@ob_flush();
@flush();
exit;
