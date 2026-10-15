<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — API: ADMIN ONBOARDING WIZARD
 * Methods: GET, POST
 * Guides site owner through initial configuration and marks onboarding complete.
 */

require_once __DIR__ . '/../../backend/config/db.php';
require_once __DIR__ . '/../../backend/helpers/response.php';
require_once __DIR__ . '/../../backend/helpers/auth_helper.php';

$admin = requireAdminAuth();
$method = $_SERVER['REQUEST_METHOD'];

try {
    $db = getDB();

    // 1. GET: Ambil status onboarding & pengaturan dasar saat ini
    if ($method === 'GET') {
        $stmt = $db->query("SELECT setting_key, setting_value FROM system_settings");
        $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        $heroStmt = $db->query("SELECT * FROM hero_sections WHERE is_active = 1 LIMIT 1");
        $hero = $heroStmt->fetch() ?: null;

        $featuresCount = (int)$db->query("SELECT COUNT(*) FROM features WHERE is_active = 1")->fetchColumn();

        sendJsonResponse([
            'onboarding_completed' => ($settings['onboarding_completed'] ?? '0') === '1',
            'settings' => $settings,
            'hero' => $hero,
            'active_features_count' => $featuresCount
        ], 200, 'Status onboarding berhasil diambil.');
    }

    // 2. POST: Simpan seluruh tahapan onboarding
    if ($method === 'POST') {
        $input = getJsonInput();

        // 2.1 Simpan Pengaturan Identitas & Kontak
        $settingKeys = [
            'site_title', 'site_tagline', 'site_logo', 'contact_email', 'official_email',
            'contact_phone', 'whatsapp_number', 'operating_hours', 'contact_address',
            'workshop_address', 'disclaimer_text', 'require_auth_for_details'
        ];

        $stmtUp = $db->prepare("
            INSERT INTO system_settings (setting_key, setting_value)
            VALUES (?, ?)
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
        ");

        foreach ($settingKeys as $k) {
            if (isset($input[$k])) {
                $stmtUp->execute([$k, (string)$input[$k]]);
            }
        }

        // Tandai onboarding selesai
        $stmtUp->execute(['onboarding_completed', '1']);

        // 2.2 Simpan / Perbarui Hero Section jika diisi
        if (!empty($input['hero_headline'])) {
            $badge = trim($input['hero_badge'] ?? 'Manajer Investasi Sektor Riil Berbasis Aset Produktif');
            $headline = trim($input['hero_headline']);
            $subheadline = trim($input['hero_subheadline'] ?? '');
            $ctaText = trim($input['hero_cta_text'] ?? 'Pelajari Proyek Berjalan');
            $ctaLink = trim($input['hero_cta_link'] ?? 'invest');
            $secCtaText = trim($input['hero_secondary_cta_text'] ?? 'Konsultasi Tim');
            $secCtaLink = trim($input['hero_secondary_cta_link'] ?? 'contact');
            $mediaUrl = trim($input['hero_media_url'] ?? 'assets/img/hero-alat-berat.png');

            // Cek apakah sudah ada hero section
            $chkHero = $db->query("SELECT id FROM hero_sections LIMIT 1")->fetchColumn();
            if ($chkHero) {
                $updateHero = $db->prepare("
                    UPDATE hero_sections SET
                        badge_text = ?, headline = ?, subheadline = ?,
                        cta_text = ?, cta_link = ?,
                        secondary_cta_text = ?, secondary_cta_link = ?,
                        media_url = ?, is_active = 1
                    WHERE id = ?
                ");
                $updateHero->execute([
                    $badge, $headline, $subheadline, $ctaText, $ctaLink,
                    $secCtaText, $secCtaLink, $mediaUrl, $chkHero
                ]);
            } else {
                $insertHero = $db->prepare("
                    INSERT INTO hero_sections (badge_text, headline, subheadline, cta_text, cta_link, secondary_cta_text, secondary_cta_link, media_url, is_active)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)
                ");
                $insertHero->execute([
                    $badge, $headline, $subheadline, $ctaText, $ctaLink,
                    $secCtaText, $secCtaLink, $mediaUrl
                ]);
            }
        }

        logAdminActivity('onboarding', 'system_settings', null, 'Admin menyelesaikan Wizard Onboarding Setup Konten Awal');

        sendJsonResponse(null, 200, 'Konfigurasi onboarding awal berhasil disimpan! Situs siap digunakan.');
    }

    sendJsonError('Metode HTTP tidak didukung.', 405);

} catch (Throwable $e) {
    sendJsonError('Gagal menyimpan onboarding: ' . $e->getMessage(), 500);
}
