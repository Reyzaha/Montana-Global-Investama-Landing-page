<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — API: PUBLIC HERO SECTION
 * Method: GET
 * Returns active hero section configuration from MySQL database.
 */

require_once __DIR__ . '/../backend/config/db.php';
require_once __DIR__ . '/../backend/helpers/response.php';

try {
    $db = getDB();

    $stmt = $db->query("
        SELECT id, kicker, title, title_highlight, subtitle, 
               cta_primary_text, cta_primary_url, cta_secondary_text, cta_secondary_url, 
               media_type, media_url, badge_text, is_active, sort_order
        FROM hero_sections 
        WHERE is_active = 1 
        ORDER BY sort_order ASC, id DESC 
        LIMIT 1
    ");
    $hero = $stmt->fetch();

    if (!$hero) {
        // Return 200 with null data so frontend can cleanly show empty state
        sendJsonResponse(null, 200, 'Belum ada konten hero section aktif.');
    }

    sendJsonResponse($hero);

} catch (Throwable $e) {
    sendJsonError('Gagal memuat hero section: ' . $e->getMessage(), 500);
}
