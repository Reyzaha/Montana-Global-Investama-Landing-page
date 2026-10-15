<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — API: ADMIN ANALYTICS REPORTING
 * Method: GET
 * Aggregates visit counts, CTA clicks, conversion rates, and 7-day performance.
 */

require_once __DIR__ . '/../../backend/config/db.php';
require_once __DIR__ . '/../../backend/helpers/response.php';
require_once __DIR__ . '/../../backend/helpers/auth_helper.php';

$admin = requireAdminAuth();

try {
    $db = getDB();

    // Self-healing schema migration for analytics_events
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
        if (in_array('event_name', $cols)) {
            $db->exec("ALTER TABLE analytics_events MODIFY COLUMN event_name VARCHAR(100) NULL DEFAULT 'event'");
        }
        if (in_array('page_path', $cols)) {
            $db->exec("ALTER TABLE analytics_events MODIFY COLUMN page_path VARCHAR(255) NULL");
        }
    } catch (Throwable $ignore) {
        // Fallback gracefully if ALTER TABLE is restricted
    }

    $colUrlSelect = in_array('page_url', $cols) 
        ? "COALESCE(NULLIF(page_url, ''), page_path, '/') AS page_url"
        : (in_array('page_path', $cols) ? "COALESCE(page_path, '/') AS page_url" : "'/' AS page_url");
    $colUrlGroup = in_array('page_url', $cols)
        ? "COALESCE(NULLIF(page_url, ''), page_path, '/')"
        : (in_array('page_path', $cols) ? "COALESCE(page_path, '/')" : "'/'");

    // 1. Overall KPI Totals & Today
    $kpiStmt = $db->query("
        SELECT
            COUNT(*) AS total_events,
            SUM(CASE WHEN event_type = 'page_view' THEN 1 ELSE 0 END) AS total_views,
            SUM(CASE WHEN event_type = 'cta_click' THEN 1 ELSE 0 END) AS total_cta_clicks,
            SUM(CASE WHEN event_type = 'conversion' THEN 1 ELSE 0 END) AS total_conversions,
            
            SUM(CASE WHEN event_type = 'page_view' AND DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) AS views_today,
            SUM(CASE WHEN event_type = 'cta_click' AND DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) AS cta_clicks_today,
            SUM(CASE WHEN event_type = 'conversion' AND DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) AS conversions_today,

            SUM(CASE WHEN event_type = 'page_view' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) AS views_7d,
            SUM(CASE WHEN event_type = 'cta_click' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) AS cta_clicks_7d,
            SUM(CASE WHEN event_type = 'conversion' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) AS conversions_7d
        FROM analytics_events
    ");
    $kpi = $kpiStmt->fetch();

    $totalViews = (int)($kpi['total_views'] ?? 0);
    $totalConversions = (int)($kpi['total_conversions'] ?? 0);
    $conversionRate = ($totalViews > 0) ? round(($totalConversions / $totalViews) * 100, 2) : 0;

    // 2. 7-Day Performance Trend
    $trendStmt = $db->query("
        SELECT
            DATE(created_at) AS event_date,
            SUM(CASE WHEN event_type = 'page_view' THEN 1 ELSE 0 END) AS views,
            SUM(CASE WHEN event_type = 'cta_click' THEN 1 ELSE 0 END) AS cta_clicks,
            SUM(CASE WHEN event_type = 'conversion' THEN 1 ELSE 0 END) AS conversions
        FROM analytics_events
        WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
        GROUP BY DATE(created_at)
        ORDER BY event_date ASC
    ");
    $dailyTrend = $trendStmt->fetchAll();

    // 3. Top Visited Pages
    $pagesStmt = $db->query("
        SELECT {$colUrlSelect}, COUNT(*) AS view_count
        FROM analytics_events
        WHERE event_type = 'page_view'
        GROUP BY {$colUrlGroup}
        ORDER BY view_count DESC
        LIMIT 6
    ");
    $topPages = $pagesStmt->fetchAll();

    // 4. Top Clicked CTA Elements
    $ctaStmt = $db->query("
        SELECT element_id, COUNT(*) AS click_count
        FROM analytics_events
        WHERE event_type = 'cta_click' AND element_id IS NOT NULL AND element_id != ''
        GROUP BY element_id
        ORDER BY click_count DESC
        LIMIT 6
    ");
    $topCtas = $ctaStmt->fetchAll();

    // 5. Recent 15 Live Telemetry Events
    $recentStmt = $db->query("
        SELECT id, event_type, {$colUrlSelect}, element_id, ip_address, created_at
        FROM analytics_events
        ORDER BY created_at DESC
        LIMIT 15
    ");
    $recentEvents = $recentStmt->fetchAll();

    sendJsonResponse([
        'kpi' => [
            'views_today' => (int)($kpi['views_today'] ?? 0),
            'views_7d' => (int)($kpi['views_7d'] ?? 0),
            'views_total' => $totalViews,

            'cta_clicks_today' => (int)($kpi['cta_clicks_today'] ?? 0),
            'cta_clicks_7d' => (int)($kpi['cta_clicks_7d'] ?? 0),
            'cta_clicks_total' => (int)($kpi['total_cta_clicks'] ?? 0),

            'conversions_today' => (int)($kpi['conversions_today'] ?? 0),
            'conversions_7d' => (int)($kpi['conversions_7d'] ?? 0),
            'conversions_total' => $totalConversions,

            'conversion_rate' => $conversionRate
        ],
        'daily_trend' => $dailyTrend,
        'top_pages' => $topPages,
        'top_ctas' => $topCtas,
        'recent_events' => $recentEvents
    ], 200, 'Data analitik ringkas berhasil dimuat.');

} catch (Throwable $e) {
    sendJsonError('Gagal memuat analitik: ' . $e->getMessage(), 500);
}
