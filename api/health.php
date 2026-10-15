<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — ENTERPRISE HEALTH-CHECK ENDPOINT
 * High-reliability endpoint for uptime monitors, container liveness/readiness probes,
 * and DevOps monitoring.
 *
 * GET /api/health.php
 */

require_once __DIR__ . '/../backend/config/db.php';
require_once __DIR__ . '/../backend/helpers/realtime_helper.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

$startTime = microtime(true);
$checks = [
    'database'  => ['status' => 'pending'],
    'storage'   => ['status' => 'pending'],
    'realtime'  => ['status' => 'pending'],
    'system'    => ['status' => 'pending']
];

$isHealthy = true;

// 1. Check Database Connectivity & Query Latency
try {
    $dbStart = microtime(true);
    $db = getDB();
    $stmt = $db->query("SELECT 1");
    $stmt->fetch();
    $dbLatencyMs = round((microtime(true) - $dbStart) * 1000, 2);

    // Verify key tables
    $tableCount = $db->query("
        SELECT COUNT(*) FROM information_schema.tables 
        WHERE table_schema = DATABASE() AND table_name IN ('hero_sections', 'projects', 'system_settings', 'realtime_events')
    ")->fetchColumn();

    $checks['database'] = [
        'status'     => 'healthy',
        'port'       => Database::getConnectedPort(),
        'latency_ms' => $dbLatencyMs,
        'core_tables_found' => (int)$tableCount
    ];
} catch (Throwable $e) {
    $isHealthy = false;
    $checks['database'] = [
        'status'  => 'unhealthy',
        'error'   => $e->getMessage()
    ];
}

// 2. Check Storage & Directory Writable Permissions
$cacheDir = __DIR__ . '/../backend/cache';
$uploadsDir = __DIR__ . '/../uploads';
$isCacheWritable = is_dir($cacheDir) && is_writable($cacheDir);
$isUploadsWritable = is_dir($uploadsDir) && is_writable($uploadsDir);

if (!$isCacheWritable && !is_dir($cacheDir)) {
    @mkdir($cacheDir, 0755, true);
    $isCacheWritable = is_writable($cacheDir);
}
if (!$isUploadsWritable && !is_dir($uploadsDir)) {
    @mkdir($uploadsDir, 0755, true);
    $isUploadsWritable = is_writable($uploadsDir);
}

$checks['storage'] = [
    'status'           => ($isCacheWritable && $isUploadsWritable) ? 'healthy' : 'warning',
    'cache_writable'   => $isCacheWritable,
    'uploads_writable' => $isUploadsWritable
];

// 3. Check Realtime Engine Status
try {
    $rtState = getLatestRealtimeState();
    $checks['realtime'] = [
        'status'        => 'healthy',
        'driver'        => getenv('REALTIME_DRIVER') ?: 'sse',
        'last_event_id' => (int)$rtState['last_event_id'],
        'last_sync'     => $rtState['iso_time'] ?? date('c')
    ];
} catch (Throwable $e) {
    $checks['realtime'] = [
        'status' => 'degraded',
        'error'  => $e->getMessage()
    ];
}

// 4. System & PHP Environment
$checks['system'] = [
    'php_version'    => PHP_VERSION,
    'environment'    => getenv('APP_ENV') ?: 'production',
    'memory_usage'   => round(memory_get_usage(true) / 1024 / 1024, 2) . ' MB',
    'peak_memory'    => round(memory_get_peak_usage(true) / 1024 / 1024, 2) . ' MB',
    'timezone'       => date_default_timezone_get()
];

$totalLatencyMs = round((microtime(true) - $startTime) * 1000, 2);

$httpStatus = $isHealthy ? 200 : 503;
http_response_code($httpStatus);

echo json_encode([
    'status'       => $isHealthy ? 'healthy' : 'unhealthy',
    'timestamp'    => date('c'),
    'uptime_check' => 'PT Montana Global Investama Production Portal',
    'latency_ms'   => $totalLatencyMs,
    'checks'       => $checks
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
exit;
