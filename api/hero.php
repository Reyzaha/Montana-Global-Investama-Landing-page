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
        // Auto-seed default corporate hero record so API is self-healing
        $db->exec("
            INSERT INTO hero_sections (id, kicker, title, title_highlight, subtitle, cta_primary_text, cta_primary_url, cta_secondary_text, cta_secondary_url, is_active, sort_order)
            VALUES (1, 'Grup & Manajer Investasi Proyek Sektor Riil', 'Tata Kelola Modal Strategis &', 'Investasi Proyek Sektor Riil Terpercaya', 'PT Montana Global Investama mengalokasikan permodalan strategis berbasis aset fisik produktif unit alat berat dan ekosistem industri terpadu, berlandaskan kepatuhan tata kelola TARIF transparan dan akuntabel.', 'Katalog Proyek Terbuka', 'invest', 'Profil & Tata Kelola', 'about', 1, 1)
            ON DUPLICATE KEY UPDATE is_active = 1
        ");
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
    }

    sendJsonResponse($hero);

} catch (Throwable $e) {
    sendJsonError('Gagal memuat hero section: ' . $e->getMessage(), 500);
}
