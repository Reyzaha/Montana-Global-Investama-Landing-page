<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — API: PUBLIC SITE SETTINGS
 * Method: GET
 * Returns public site settings, branding, contacts, and meta from MySQL database.
 */

require_once __DIR__ . '/../backend/config/db.php';
require_once __DIR__ . '/../backend/helpers/response.php';

try {
    $db = getDB();

    $allowedKeys = [
        'site_title',
        'site_tagline',
        'site_logo',
        'site_favicon',
        'contact_address',
        'workshop_address',
        'contact_email',
        'contact_phone',
        'official_email',
        'whatsapp_number',
        'operating_hours',
        'social_instagram',
        'social_linkedin',
        'social_youtube',
        'meta_description',
        'meta_keywords',
        'disclaimer_text',
        'require_auth_for_details',
        'gsc_verification_token',
        // Footer specific settings & toggles
        'footer_logo',
        'footer_tagline',
        'footer_badge_text',
        'footer_badge_show',
        'contact_phone_show',
        'whatsapp_show',
        'official_email_show',
        'contact_address_show',
        'operating_hours_show',
        'social_instagram_show',
        'social_linkedin_show',
        'social_youtube_show',
        'footer_col2_title',
        'footer_col2_links',
        'footer_col3_title',
        'footer_col3_links',
        'footer_newsletter_show',
        'footer_newsletter_title',
        'footer_newsletter_desc',
        'footer_copyright',
        'footer_status_text'
    ];

    $inPlaceholders = implode(',', array_fill(0, count($allowedKeys), '?'));
    $stmt = $db->prepare("SELECT setting_key, setting_value FROM system_settings WHERE setting_key IN ($inPlaceholders)");
    $stmt->execute($allowedKeys);
    $rows = $stmt->fetchAll();

    $settings = [];
    foreach ($rows as $r) {
        $settings[$r['setting_key']] = $r['setting_value'];
    }

    sendJsonResponse($settings, 200, 'Pengaturan publik berhasil dimuat.');

} catch (Throwable $e) {
    sendJsonError('Gagal memuat pengaturan: ' . $e->getMessage(), 500);
}
