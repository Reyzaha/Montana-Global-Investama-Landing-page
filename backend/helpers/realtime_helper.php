<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — REALTIME SYNCHRONIZATION HELPER
 * Central event publisher & revision tracker for Server-Sent Events (SSE) & polling fallback.
 */

require_once __DIR__ . '/../config/db.php';

function getRealtimeCacheFile(): string {
    $dir = __DIR__ . '/../cache';
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    return $dir . '/realtime_state.json';
}

/**
 * Trigger a real-time event across the system.
 * Broadcasts to all connected SSE clients (landing page tabs and admin portals).
 */
function triggerRealtimeEvent(string $eventType, string $section, string $action = 'update', array $payload = []): int {
    try {
        $db = getDB();

        // 1. Insert into realtime_events table
        $payloadJson = !empty($payload) ? json_encode($payload, JSON_UNESCAPED_UNICODE) : null;
        $stmt = $db->prepare("
            INSERT INTO realtime_events (event_type, section, action, payload, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$eventType, $section, $action, $payloadJson]);
        $eventId = (int)$db->lastInsertId();

        // 2. Bump revision counters for the specific section and global
        $stmtRev = $db->prepare("
            INSERT INTO content_revisions (section, revision_id, updated_at)
            VALUES (?, 1, NOW())
            ON DUPLICATE KEY UPDATE revision_id = revision_id + 1, updated_at = NOW()
        ");
        $stmtRev->execute([$section]);
        if ($section !== 'global') {
            $stmtRev->execute(['global']);
        }

        // 3. Write lightweight cache file for ultra-fast polling & SSE checks
        $cacheFile = getRealtimeCacheFile();
        $state = [
            'last_event_id' => $eventId,
            'timestamp'     => microtime(true),
            'section'       => $section,
            'action'        => $action,
            'event'         => $eventType,
            'iso_time'      => date('c')
        ];
        @file_put_contents($cacheFile, json_encode($state), LOCK_EX);

        // 4. Occasional housekeeping (1 in 50 chance): purge events older than 3 days
        if (mt_rand(1, 50) === 1) {
            $db->exec("DELETE FROM realtime_events WHERE created_at < DATE_SUB(NOW(), INTERVAL 3 DAY)");
        }

        return $eventId;
    } catch (Throwable $e) {
        error_log('[MGI Realtime] Error triggering event: ' . $e->getMessage());
        return 0;
    }
}

/**
 * Get current revision state (for lightweight polling and ETag generation)
 */
function getLatestRealtimeState(?PDO $db = null): array {
    $cacheFile = getRealtimeCacheFile();
    if (file_exists($cacheFile)) {
        $content = @file_get_contents($cacheFile);
        if ($content) {
            $decoded = json_decode($content, true);
            if (is_array($decoded) && isset($decoded['last_event_id'])) {
                return $decoded;
            }
        }
    }

    if (!$db) {
        $db = getDB();
    }

    try {
        $row = $db->query("SELECT MAX(id) as max_id FROM realtime_events")->fetch();
        $maxId = (int)($row['max_id'] ?? 0);
        return [
            'last_event_id' => $maxId,
            'timestamp'     => microtime(true),
            'section'       => 'global',
            'action'        => 'init',
            'event'         => 'init',
            'iso_time'      => date('c')
        ];
    } catch (Throwable $e) {
        return [
            'last_event_id' => 0,
            'timestamp'     => microtime(true),
            'section'       => 'global',
            'action'        => 'error',
            'event'         => 'error',
            'iso_time'      => date('c')
        ];
    }
}

/**
 * Fetch events newer than $lastEventId
 */
function getEventsSince(int $lastEventId, int $limit = 25): array {
    try {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT id, event_type, section, action, payload, created_at
            FROM realtime_events
            WHERE id > ?
            ORDER BY id ASC
            LIMIT ?
        ");
        $stmt->bindValue(1, $lastEventId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll();
        foreach ($rows as &$r) {
            $r['id'] = (int)$r['id'];
            if (!empty($r['payload'])) {
                $r['payload'] = json_decode($r['payload'], true);
            }
        }
        return $rows;
    } catch (Throwable $e) {
        error_log('[MGI Realtime] Error fetching events: ' . $e->getMessage());
        return [];
    }
}
