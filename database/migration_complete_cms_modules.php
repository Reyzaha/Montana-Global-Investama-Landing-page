<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — MIGRATION: COMPLETE DYNAMIC CMS & SYSTEM MODULES
 * Creates all missing tables for Hero, Features, Pricing Tiers, Testimonials,
 * FAQs, Team/About, Blog/Articles, Newsletter, Contact Messages, Media, and Analytics.
 */

require_once __DIR__ . '/../backend/config/db.php';

echo "=== MEMULAI MIGRASI MODUL LENGKAP CMS MGI ===\n";

try {
    $db = getDB();
    echo "[✓] Terhubung ke MySQL pada port: " . Database::getConnectedPort() . "\n";

    // 1. Tabel Hero Section
    $db->exec("
        CREATE TABLE IF NOT EXISTS `hero_sections` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `kicker` VARCHAR(255) NOT NULL DEFAULT 'Grup & Manajer Investasi Proyek Sektor Riil',
            `title` VARCHAR(255) NOT NULL,
            `title_highlight` VARCHAR(255) NULL,
            `subtitle` TEXT NOT NULL,
            `cta_primary_text` VARCHAR(100) NOT NULL DEFAULT 'Katalog Proyek Terbuka',
            `cta_primary_url` VARCHAR(255) NOT NULL DEFAULT 'invest',
            `cta_secondary_text` VARCHAR(100) NOT NULL DEFAULT 'Profil & Tata Kelola',
            `cta_secondary_url` VARCHAR(255) NOT NULL DEFAULT 'about',
            `media_type` ENUM('image', 'video') NOT NULL DEFAULT 'image',
            `media_url` VARCHAR(255) NULL,
            `badge_text` VARCHAR(100) NULL,
            `is_active` TINYINT(1) NOT NULL DEFAULT 1,
            `sort_order` INT NOT NULL DEFAULT 0,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "[✓] Tabel 'hero_sections' siap.\n";

    // 2. Tabel Fitur & Layanan / Value Proposition
    $db->exec("
        CREATE TABLE IF NOT EXISTS `features` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `category` ENUM('value_prop', 'layanan', 'keunggulan', 'pilar') NOT NULL DEFAULT 'value_prop',
            `item_number` VARCHAR(10) NULL,
            `title` VARCHAR(255) NOT NULL,
            `subtitle` VARCHAR(255) NULL,
            `description` TEXT NOT NULL,
            `icon` VARCHAR(100) NULL DEFAULT 'bi-shield-check',
            `image_url` VARCHAR(255) NULL,
            `badge_text` VARCHAR(100) NULL,
            `accent_color` VARCHAR(50) NOT NULL DEFAULT '#1D3589',
            `sort_order` INT NOT NULL DEFAULT 0,
            `is_active` TINYINT(1) NOT NULL DEFAULT 1,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "[✓] Tabel 'features' siap.\n";

    // 3. Tabel Harga / Paket Kemitraan (Pricing Tiers)
    $db->exec("
        CREATE TABLE IF NOT EXISTS `pricing_tiers` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `project_id` VARCHAR(50) NULL,
            `name` VARCHAR(255) NOT NULL,
            `badge` VARCHAR(100) NULL,
            `nominal` BIGINT NOT NULL DEFAULT 0,
            `nominal_display` VARCHAR(100) NOT NULL,
            `unit_qty` INT NOT NULL DEFAULT 1,
            `container_qty` VARCHAR(150) NULL,
            `porsi_proyek` VARCHAR(255) NULL,
            `jaminan_aset` VARCHAR(255) NULL,
            `operator_perk` TEXT NULL,
            `deskripsi` TEXT NOT NULL,
            `profit_per_siklus` BIGINT NOT NULL DEFAULT 0,
            `profit_2x_tahunan` BIGINT NOT NULL DEFAULT 0,
            `profit_3x_tahunan` BIGINT NOT NULL DEFAULT 0,
            `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
            `is_active` TINYINT(1) NOT NULL DEFAULT 1,
            `sort_order` INT NOT NULL DEFAULT 0,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX `idx_pricing_project` (`project_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "[✓] Tabel 'pricing_tiers' siap.\n";

    // 4. Tabel Testimoni Mitra & Rekanan
    $db->exec("
        CREATE TABLE IF NOT EXISTS `testimonials` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(150) NOT NULL,
            `role_title` VARCHAR(150) NOT NULL,
            `company_name` VARCHAR(200) NULL,
            `avatar_url` VARCHAR(255) NULL,
            `rating` TINYINT NOT NULL DEFAULT 5,
            `content` TEXT NOT NULL,
            `project_name` VARCHAR(255) NULL,
            `category` ENUM('investor', 'kontraktor', 'partner') NOT NULL DEFAULT 'investor',
            `is_featured` TINYINT(1) NOT NULL DEFAULT 1,
            `sort_order` INT NOT NULL DEFAULT 0,
            `is_active` TINYINT(1) NOT NULL DEFAULT 1,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "[✓] Tabel 'testimonials' siap.\n";

    // 5. Tabel FAQ (Tanya Jawab Terverifikasi)
    $db->exec("
        CREATE TABLE IF NOT EXISTS `faqs` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `category` VARCHAR(100) NOT NULL DEFAULT 'Umum',
            `question` VARCHAR(255) NOT NULL,
            `answer` TEXT NOT NULL,
            `sort_order` INT NOT NULL DEFAULT 0,
            `is_active` TINYINT(1) NOT NULL DEFAULT 1,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX `idx_faqs_active` (`is_active`, `sort_order`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "[✓] Tabel 'faqs' siap.\n";

    // 6. Tabel Tim Manajemen & Direksi (Team / About)
    $db->exec("
        CREATE TABLE IF NOT EXISTS `team_members` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(150) NOT NULL,
            `role_title` VARCHAR(150) NOT NULL,
            `department` VARCHAR(100) NOT NULL DEFAULT 'Direksi & Manajemen',
            `photo_url` VARCHAR(255) NULL,
            `bio` TEXT NULL,
            `linkedin_url` VARCHAR(255) NULL,
            `sort_order` INT NOT NULL DEFAULT 0,
            `is_active` TINYINT(1) NOT NULL DEFAULT 1,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "[✓] Tabel 'team_members' siap.\n";

    // 7. Tabel Artikel & Wawasan Pasar (Blog / News)
    $db->exec("
        CREATE TABLE IF NOT EXISTS `articles` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `title` VARCHAR(255) NOT NULL,
            `slug` VARCHAR(255) NOT NULL UNIQUE,
            `category` VARCHAR(100) NOT NULL DEFAULT 'Wawasan Pasar & Industri',
            `excerpt` TEXT NOT NULL,
            `content` LONGTEXT NOT NULL,
            `cover_image` VARCHAR(255) NULL,
            `author_name` VARCHAR(100) NOT NULL DEFAULT 'Tim Riset & Analis MGI',
            `status` ENUM('published', 'draft') NOT NULL DEFAULT 'published',
            `views_count` INT NOT NULL DEFAULT 0,
            `published_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX `idx_articles_status` (`status`, `published_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "[✓] Tabel 'articles' siap.\n";

    // 8. Tabel Pesan Masuk / Contact Inquiries (dari contact.html)
    $db->exec("
        CREATE TABLE IF NOT EXISTS `contact_inquiries` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `full_name` VARCHAR(150) NOT NULL,
            `email` VARCHAR(191) NOT NULL,
            `phone` VARCHAR(50) NULL,
            `company_name` VARCHAR(200) NULL,
            `subject` VARCHAR(255) NOT NULL DEFAULT 'Pertanyaan Umum Proyek Investasi',
            `message` TEXT NOT NULL,
            `status` ENUM('new', 'read', 'replied', 'archived') NOT NULL DEFAULT 'new',
            `admin_notes` TEXT NULL,
            `ip_address` VARCHAR(50) NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX `idx_contact_status` (`status`),
            INDEX `idx_contact_email` (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "[✓] Tabel 'contact_inquiries' siap.\n";

    // 9. Tabel Subscriber Newsletter
    $db->exec("
        CREATE TABLE IF NOT EXISTS `newsletter_subscribers` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `email` VARCHAR(191) NOT NULL UNIQUE,
            `status` ENUM('active', 'unsubscribed') NOT NULL DEFAULT 'active',
            `ip_address` VARCHAR(50) NULL,
            `subscribed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX `idx_newsletter_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "[✓] Tabel 'newsletter_subscribers' siap.\n";

    // 10. Tabel Media Library Terpusat
    $db->exec("
        CREATE TABLE IF NOT EXISTS `media_files` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `file_name` VARCHAR(255) NOT NULL,
            `original_name` VARCHAR(255) NOT NULL,
            `file_path` VARCHAR(255) NOT NULL,
            `file_type` VARCHAR(50) NOT NULL DEFAULT 'image',
            `mime_type` VARCHAR(100) NOT NULL,
            `file_size` BIGINT NOT NULL DEFAULT 0,
            `alt_text` VARCHAR(255) NULL,
            `category` VARCHAR(100) NOT NULL DEFAULT 'general',
            `uploaded_by` VARCHAR(100) NOT NULL DEFAULT 'Admin',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX `idx_media_type` (`file_type`, `category`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "[✓] Tabel 'media_files' siap.\n";

    // 11. Tabel Analytics Ringkas (Kunjungan, Klik CTA, Konversi)
    $db->exec("
        CREATE TABLE IF NOT EXISTS `analytics_events` (
            `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
            `event_type` ENUM('page_view', 'cta_click', 'form_submit', 'conversion') NOT NULL DEFAULT 'page_view',
            `event_name` VARCHAR(100) NULL DEFAULT 'event',
            `page_path` VARCHAR(255) NULL,
            `page_url` VARCHAR(255) NULL,
            `element_id` VARCHAR(100) NULL,
            `session_id` VARCHAR(100) NULL,
            `ip_address` VARCHAR(50) NULL,
            `referrer` VARCHAR(255) NULL,
            `user_agent` TEXT NULL,
            `metadata_json` TEXT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX `idx_analytics_event` (`event_type`, `created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // Pastikan kolom page_url dan metadata_json tersedia pada instalasi lama
    try {
        $cols = $db->query("SHOW COLUMNS FROM analytics_events")->fetchAll(PDO::FETCH_COLUMN);
        if (!in_array('page_url', $cols)) {
            $db->exec("ALTER TABLE analytics_events ADD COLUMN page_url VARCHAR(255) NULL AFTER event_type");
            if (in_array('page_path', $cols)) {
                $db->exec("UPDATE analytics_events SET page_url = page_path WHERE page_url IS NULL");
            }
        }
        if (!in_array('metadata_json', $cols)) {
            $db->exec("ALTER TABLE analytics_events ADD COLUMN metadata_json TEXT NULL AFTER user_agent");
        }
        if (in_array('event_name', $cols)) {
            $db->exec("ALTER TABLE analytics_events MODIFY COLUMN event_name VARCHAR(100) NULL DEFAULT 'event'");
        }
        if (in_array('page_path', $cols)) {
            $db->exec("ALTER TABLE analytics_events MODIFY COLUMN page_path VARCHAR(255) NULL");
        }
    } catch (Throwable $e) {}
    echo "[✓] Tabel 'analytics_events' siap.\n";

    // 12. Ensure default site settings in system_settings
    $defaultSettings = [
        'site_title' => 'PT Montana Global Investama | Platform Pendanaan & Investasi Proyek Sektor Riil',
        'site_tagline' => 'Tumbuh Bersama Melalui Investasi Terpercaya',
        'site_logo' => 'assets/img/mgi-official-logo.png',
        'site_favicon' => 'favicon.ico',
        'contact_email' => 'contact@montanainvestama.com',
        'contact_phone' => '+62 21 5315 8899',
        'whatsapp_number' => '6281198765432',
        'contact_address' => 'Roseville Soho & Suite, Sunburst CBD Lot I.8, Serpong, Tangerang Selatan',
        'workshop_address' => 'Jl. Nasional III No.88, Pejuritan, Kec. Karanganyar, Kab. Kebumen, Jawa Tengah',
        'operating_hours' => 'Senin – Jumat (08.00 – 17.00 WIB)',
        'meta_description' => 'Platform pendanaan dan manajer investasi per proyek sektor riil terpercaya di Indonesia berbasis aset fisik alat berat.',
        'meta_keywords' => 'platform pendanaan, investasi alat berat, montana global investama, bagi hasil proyek riil',
        'social_linkedin' => 'https://linkedin.com/company/montana-global-investama',
        'social_instagram' => 'https://instagram.com/montanainvestama',
        'social_youtube' => 'https://youtube.com/@montanainvestama',
        'onboarding_completed' => '0'
    ];

    $checkSet = $db->prepare("SELECT COUNT(*) FROM system_settings WHERE setting_key = ?");
    $insSet = $db->prepare("INSERT INTO system_settings (setting_key, setting_value, description) VALUES (?, ?, ?)");

    foreach ($defaultSettings as $k => $v) {
        $checkSet->execute([$k]);
        if ($checkSet->fetchColumn() == 0) {
            $insSet->execute([$k, $v, 'Default site configuration']);
        }
    }
    echo "[✓] Pengaturan situs 'system_settings' diperbarui.\n";
    echo "=== SEMUA TABEL MODUL CMS BERHASIL DIMIGRASIKAN ===\n\n";

    // Auto-seed default production content
    if (file_exists(__DIR__ . '/seed_cms_production.php')) {
        require_once __DIR__ . '/seed_cms_production.php';
    }

} catch (Throwable $e) {
    echo "ERROR MIGRASI: " . $e->getMessage() . "\n";
    exit(1);
}
