<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — API: ADMIN FOOTER MANAGEMENT
 * Methods: GET, POST
 */

require_once __DIR__ . '/../../backend/config/db.php';
require_once __DIR__ . '/../../backend/helpers/response.php';
require_once __DIR__ . '/../../backend/helpers/auth_helper.php';
require_once __DIR__ . '/../../backend/helpers/realtime_helper.php';

$admin = requireAdminAuth();
$method = $_SERVER['REQUEST_METHOD'];

// Default configuration for footer
$defaultFooterConfig = [
    'footer_logo' => 'assets/img/mgi-official-logo.png',
    'footer_tagline' => 'PT Montana Global Investama adalah entitas manajer investasi project dan pemegang kendali strategis (grup) sektor riil berbasis aset fisik produktif, menghadirkan pertumbuhan nilai per project terukur melalui kepatuhan tata kelola terpercaya dan integrasi ekosistem terpadu.',
    'footer_badge_text' => 'Standar Tata Kelola Perusahaan & Kepatuhan Regulasi',
    'footer_badge_show' => '1',
    
    // Contact Info
    'contact_phone' => '+62 21 5315 8899',
    'contact_phone_show' => '1',
    'whatsapp_number' => '6281198765432',
    'whatsapp_show' => '1',
    'official_email' => 'contact@montanainvestama.com',
    'official_email_show' => '1',
    'contact_address' => 'Roseville Soho & Suite, Sunburst CBD Lot I.8, Serpong, Tangerang Selatan',
    'contact_address_show' => '1',
    'operating_hours' => 'Senin – Jumat (08.00 – 17.00 WIB)',
    'operating_hours_show' => '1',

    // Social Media
    'social_instagram' => 'https://instagram.com/montanainvestama',
    'social_instagram_show' => '1',
    'social_linkedin' => 'https://linkedin.com/company/montana-global-investama',
    'social_linkedin_show' => '1',
    'social_youtube' => 'https://youtube.com/@montanainvestama',
    'social_youtube_show' => '1',

    // Column 2 Navigation Links
    'footer_col2_title' => 'Navigasi Utama',
    'footer_col2_links' => json_encode([
        ['label' => 'Beranda', 'url' => '/'],
        ['label' => 'Profil Perusahaan', 'url' => 'about'],
        ['label' => 'Portofolio Proyek Investasi', 'url' => 'invest'],
        ['label' => 'Transformasi Perusahaan', 'url' => 'transformasi'],
        ['label' => 'Kontak Kami', 'url' => 'contact']
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),

    // Column 3 Navigation Links
    'footer_col3_title' => 'Struktur & Sinergi',
    'footer_col3_links' => json_encode([
        ['label' => 'Struktur Alur Kerja Entitas', 'url' => 'preparation'],
        ['label' => 'Ekosistem Terpadu', 'url' => 'ekosistem'],
        ['label' => 'Simulasi Titik Impas & Bagi Hasil', 'url' => "javascript:void(0)\" onclick=\"MGIAuth.handleProtectedDetail('proj-jkt-jabar')"],
        ['label' => 'Tata Kelola Perusahaan (TARIF)', 'url' => 'about.html#tata-kelola']
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),

    // Newsletter Section
    'footer_newsletter_show' => '1',
    'footer_newsletter_title' => 'Langganan Buletin & Wawasan Pasar MGI',
    'footer_newsletter_desc' => 'Dapatkan rilis portofolio proyek baru, analisis sektor riil, dan kabar dividen langsung di email Anda.',

    // Bottom Bar
    'footer_copyright' => 'PT Montana Global Investama. Seluruh Hak Cipta Dilindungi Undang-Undang.',
    'footer_status_text' => 'Rahasia & Terbatas'
];

try {
    $db = getDB();

    if ($method === 'GET') {
        $keys = array_keys($defaultFooterConfig);
        $placeholders = implode(',', array_fill(0, count($keys), '?'));
        
        $stmt = $db->prepare("SELECT setting_key, setting_value FROM system_settings WHERE setting_key IN ($placeholders)");
        $stmt->execute($keys);
        $rows = $stmt->fetchAll();

        $saved = [];
        foreach ($rows as $r) {
            $saved[$r['setting_key']] = $r['setting_value'];
        }

        $result = [];
        foreach ($defaultFooterConfig as $k => $defaultVal) {
            $result[$k] = isset($saved[$k]) ? $saved[$k] : $defaultVal;
        }

        // Parse JSON links for convenience
        $result['col2_links_parsed'] = json_decode($result['footer_col2_links'] ?? '[]', true) ?: [];
        $result['col3_links_parsed'] = json_decode($result['footer_col3_links'] ?? '[]', true) ?: [];

        sendJsonResponse($result, 200, 'Pengaturan footer berhasil dimuat.');
    }

    if ($method === 'POST' || $method === 'PUT') {
        $input = getJsonInput();
        if (empty($input)) {
            sendJsonError('Data pengaturan tidak boleh kosong.', 400);
        }

        $stmtUp = $db->prepare("
            INSERT INTO system_settings (setting_key, setting_value)
            VALUES (?, ?)
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
        ");

        // Allowed footer keys
        $allowed = array_keys($defaultFooterConfig);
        // Also allow updating contact_email if passed
        $allowed[] = 'contact_email';

        foreach ($input as $key => $val) {
            if (in_array($key, $allowed, true)) {
                if (is_array($val)) {
                    $valStr = json_encode($val, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                } elseif (is_bool($val)) {
                    $valStr = $val ? '1' : '0';
                } else {
                    $valStr = (string)$val;
                }
                $stmtUp->execute([$key, $valStr]);

                // Keep contact_email synced with official_email
                if ($key === 'official_email') {
                    $stmtUp->execute(['contact_email', $valStr]);
                }
            }
        }

        logAdminActivity('update_footer', 'system_settings', null, 'Admin memperbarui konfigurasi footer website');
        triggerRealtimeEvent('content_updated', 'settings', 'update');

        sendJsonResponse(null, 200, 'Konfigurasi footer berhasil disimpan dan disinkronkan ke seluruh halaman.');
    }

    sendJsonError('Metode tidak didukung.', 405);

} catch (Throwable $e) {
    sendJsonError('Terjadi kesalahan pengaturan footer: ' . $e->getMessage(), 500);
}
