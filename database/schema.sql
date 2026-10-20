
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `admin_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `target_type` varchar(50) NOT NULL,
  `target_id` varchar(100) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_admin_log_user` (`admin_id`),
  CONSTRAINT `fk_admin_log_user` FOREIGN KEY (`admin_id`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `admin_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `email` varchar(191) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(150) NOT NULL DEFAULT 'Super Administrator',
  `role` enum('superadmin','admin','editor') NOT NULL DEFAULT 'admin',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `mfa_secret` varchar(64) DEFAULT NULL,
  `mfa_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `analytics_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `analytics_events` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `event_type` enum('page_view','cta_click','form_submit','conversion') NOT NULL DEFAULT 'page_view',
  `page_url` varchar(255) DEFAULT NULL,
  `event_name` varchar(100) DEFAULT 'event',
  `page_path` varchar(255) DEFAULT NULL,
  `element_id` varchar(100) DEFAULT NULL,
  `session_id` varchar(100) DEFAULT NULL,
  `ip_address` varchar(50) DEFAULT NULL,
  `referrer` varchar(255) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `metadata_json` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_analytics_event` (`event_type`,`event_name`,`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `articles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `articles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `category` varchar(100) NOT NULL DEFAULT 'Wawasan Pasar & Industri',
  `excerpt` text NOT NULL,
  `content` longtext NOT NULL,
  `cover_image` varchar(255) DEFAULT NULL,
  `author_name` varchar(100) NOT NULL DEFAULT 'Tim Riset & Analis MGI',
  `status` enum('published','draft') NOT NULL DEFAULT 'published',
  `views_count` int(11) NOT NULL DEFAULT 0,
  `published_at` datetime NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_articles_status` (`status`,`published_at`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `campaign_updates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `campaign_updates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` varchar(50) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `category` varchar(100) NOT NULL DEFAULT 'Operasional Lapangan',
  `update_date` date NOT NULL,
  `content` text NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_campaign_project` (`project_id`),
  CONSTRAINT `fk_campaign_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `icon` varchar(50) NOT NULL DEFAULT 'bi-geo-alt-fill',
  `description` varchar(255) DEFAULT NULL,
  `is_active_segment` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `company_profile`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `company_profile` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` longtext NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=132 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `contact_inquiries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contact_inquiries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(191) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `company_name` varchar(200) DEFAULT NULL,
  `subject` varchar(255) NOT NULL DEFAULT 'Pertanyaan Umum Proyek Investasi',
  `message` text NOT NULL,
  `status` enum('new','unread','read','replied','archived') NOT NULL DEFAULT 'new',
  `admin_notes` text DEFAULT NULL,
  `ip_address` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_contact_status` (`status`),
  KEY `idx_contact_email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `content_revisions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `content_revisions` (
  `section` varchar(64) NOT NULL,
  `revision_id` bigint(20) unsigned NOT NULL DEFAULT 1,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`section`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ekosistem_nodes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ekosistem_nodes` (
  `id` varchar(50) NOT NULL,
  `label` varchar(255) NOT NULL,
  `short_label` varchar(150) DEFAULT NULL,
  `subtitle` varchar(255) DEFAULT NULL,
  `parent_id` varchar(50) DEFAULT NULL,
  `level` int(11) NOT NULL DEFAULT 0,
  `badge` varchar(100) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `category` varchar(150) DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `role_desc` text DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_ekosistem_parent` (`parent_id`),
  CONSTRAINT `fk_ekosistem_parent` FOREIGN KEY (`parent_id`) REFERENCES `ekosistem_nodes` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `faqs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `faqs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category` varchar(100) NOT NULL DEFAULT 'Umum',
  `question` varchar(255) NOT NULL,
  `answer` text NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_faqs_active` (`is_active`,`sort_order`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `features`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `features` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category` enum('value_prop','layanan','keunggulan','pilar') NOT NULL DEFAULT 'value_prop',
  `item_number` varchar(10) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `subtitle` varchar(255) DEFAULT NULL,
  `description` text NOT NULL,
  `icon` varchar(100) DEFAULT 'bi-shield-check',
  `image_url` varchar(255) DEFAULT NULL,
  `badge_text` varchar(100) DEFAULT NULL,
  `accent_color` varchar(50) NOT NULL DEFAULT '#1D3589',
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `financial_records`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `financial_records` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `investor_id` int(11) NOT NULL,
  `project_id` varchar(50) NOT NULL,
  `module` enum('neraca','labarugi','pembelian','penjualan','biaya') NOT NULL,
  `category` varchar(100) NOT NULL,
  `record_number` varchar(100) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `amount` decimal(18,2) NOT NULL,
  `flow_type` enum('in','out','balance_asset') DEFAULT 'out',
  `vendor_client` varchar(255) DEFAULT NULL,
  `unit_detail` varchar(255) DEFAULT NULL,
  `status` enum('draft','sent','paid','verified') DEFAULT 'sent',
  `is_billing` tinyint(1) DEFAULT 1,
  `billing_status` enum('unpaid','paid','settled','reported') DEFAULT 'reported',
  `transaction_date` date NOT NULL,
  `due_date` date DEFAULT NULL,
  `attachment_url` varchar(255) DEFAULT NULL,
  `created_by` varchar(100) DEFAULT 'Admin MGI',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_fin_investor` (`investor_id`),
  KEY `idx_fin_project` (`project_id`),
  KEY `idx_fin_module` (`module`),
  KEY `idx_fin_billing` (`is_billing`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `funding_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `funding_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` varchar(50) NOT NULL,
  `no_urut` int(11) NOT NULL DEFAULT 1,
  `item_name` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `unit_price` bigint(20) NOT NULL DEFAULT 0,
  `total` bigint(20) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_funding_item_project` (`project_id`),
  CONSTRAINT `fk_funding_item_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `hero_sections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hero_sections` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kicker` varchar(255) NOT NULL DEFAULT 'Grup & Manajer Investasi Proyek Sektor Riil',
  `title` varchar(255) NOT NULL,
  `title_highlight` varchar(255) DEFAULT NULL,
  `subtitle` text NOT NULL,
  `cta_primary_text` varchar(100) NOT NULL DEFAULT 'Katalog Proyek Terbuka',
  `cta_primary_url` varchar(255) NOT NULL DEFAULT 'invest',
  `cta_secondary_text` varchar(100) NOT NULL DEFAULT 'Profil & Tata Kelola',
  `cta_secondary_url` varchar(255) NOT NULL DEFAULT 'about',
  `media_type` enum('image','video') NOT NULL DEFAULT 'image',
  `media_url` varchar(255) DEFAULT NULL,
  `badge_text` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `investor_bank_accounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `investor_bank_accounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `investor_id` int(11) NOT NULL,
  `bank_name` varchar(100) NOT NULL,
  `account_number` varchar(50) NOT NULL,
  `account_holder` varchar(255) NOT NULL,
  `branch` varchar(150) DEFAULT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_bank_investor` (`investor_id`),
  CONSTRAINT `fk_bank_investor` FOREIGN KEY (`investor_id`) REFERENCES `investors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `investor_companies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `investor_companies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `investor_id` int(11) NOT NULL,
  `business_name` varchar(255) NOT NULL,
  `business_activity` varchar(255) DEFAULT NULL,
  `legal_entity` enum('Perseroan Terbatas (PT)','Persekutuan Komanditer (CV)','PT Perorangan','Usaha Dagang (UD)','Tidak ada') NOT NULL,
  `company_address` text NOT NULL,
  `pic_name` varchar(255) NOT NULL,
  `pic_position` varchar(150) NOT NULL,
  `company_phone` varchar(50) NOT NULL,
  `annual_turnover` enum('Di bawah Rp500 Juta','Rp500 Juta ÔÇô Rp2,5 Miliar','Rp2,5 Miliar ÔÇô Rp10 Miliar','Rp10 Miliar ÔÇô Rp50 Miliar','Rp50 Miliar ÔÇô Rp250 Miliar','Di atas Rp250 Miliar') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_investor_company` (`investor_id`),
  CONSTRAINT `fk_investor_company` FOREIGN KEY (`investor_id`) REFERENCES `investors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `investor_portfolios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `investor_portfolios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `investor_id` int(11) NOT NULL,
  `project_id` varchar(50) NOT NULL,
  `contract_number` varchar(100) NOT NULL,
  `amount` bigint(20) NOT NULL DEFAULT 500000000,
  `return_rate` varchar(50) NOT NULL DEFAULT 'ÔëÑ30% (p.a.)',
  `tenor` varchar(50) NOT NULL DEFAULT '36 Bulan',
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `next_payout_date` date DEFAULT NULL,
  `payout_received` bigint(20) NOT NULL DEFAULT 0,
  `allocated_units` varchar(255) DEFAULT NULL,
  `status` enum('active','completed','pending_allocation') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `contract_number` (`contract_number`),
  KEY `fk_portfolio_investor` (`investor_id`),
  KEY `fk_portfolio_project` (`project_id`),
  CONSTRAINT `fk_portfolio_investor` FOREIGN KEY (`investor_id`) REFERENCES `investors` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_portfolio_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `investors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `investors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_type` enum('perorangan','perusahaan') NOT NULL DEFAULT 'perorangan',
  `email` varchar(191) NOT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `google_id` varchar(100) DEFAULT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `citizenship` enum('Indonesia (WNI)','Warga Negara Asing (WNA)') DEFAULT 'Indonesia (WNI)',
  `nik_paspor` varchar(50) DEFAULT NULL,
  `npwp` varchar(50) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `avatar_url` varchar(255) DEFAULT NULL,
  `business_activity` varchar(255) DEFAULT NULL,
  `status` enum('active','pending_verification','suspended') NOT NULL DEFAULT 'active',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_investors_google_id` (`google_id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `leads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `leads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_type` enum('perorangan','perusahaan') NOT NULL DEFAULT 'perorangan',
  `full_name` varchar(120) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `business_name` varchar(150) DEFAULT NULL,
  `legal_entity` varchar(50) DEFAULT NULL,
  `pic_position` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `investment_range` varchar(60) DEFAULT NULL,
  `status` enum('new','contacted','meeting','site_visit','deal','lost') NOT NULL DEFAULT 'new',
  `admin_notes` text DEFAULT NULL,
  `assigned_to` varchar(100) DEFAULT NULL,
  `contacted_at` datetime DEFAULT NULL,
  `utm_source` varchar(100) DEFAULT NULL,
  `utm_medium` varchar(100) DEFAULT NULL,
  `utm_campaign` varchar(150) DEFAULT NULL,
  `utm_term` varchar(150) DEFAULT NULL,
  `utm_content` varchar(150) DEFAULT NULL,
  `gclid` varchar(255) DEFAULT NULL,
  `gbraid` varchar(255) DEFAULT NULL,
  `wbraid` varchar(255) DEFAULT NULL,
  `landing_page` varchar(255) DEFAULT NULL,
  `referrer` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `consent_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_leads_status` (`status`),
  KEY `idx_leads_phone` (`phone`),
  KEY `idx_leads_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `media_files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `media_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `file_name` varchar(255) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_type` varchar(50) NOT NULL DEFAULT 'image',
  `mime_type` varchar(100) NOT NULL,
  `file_size` bigint(20) NOT NULL DEFAULT 0,
  `alt_text` varchar(255) DEFAULT NULL,
  `category` varchar(100) NOT NULL DEFAULT 'general',
  `uploaded_by` varchar(100) NOT NULL DEFAULT 'Admin',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_media_type` (`file_type`,`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `newsletter_subscribers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `newsletter_subscribers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(191) NOT NULL,
  `status` enum('active','unsubscribed') NOT NULL DEFAULT 'active',
  `ip_address` varchar(50) DEFAULT NULL,
  `source_page` varchar(100) DEFAULT 'landing_page',
  `subscribed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_newsletter_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `investor_id` int(11) NOT NULL,
  `project_id` varchar(50) DEFAULT NULL,
  `type` varchar(50) NOT NULL DEFAULT 'system',
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `link_url` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_notif_investor` (`investor_id`),
  KEY `idx_notif_read` (`is_read`),
  KEY `idx_notif_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `preparation_entities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `preparation_entities` (
  `code` varchar(50) NOT NULL,
  `name` varchar(255) NOT NULL,
  `slogan` varchar(255) DEFAULT NULL,
  `role` varchar(255) NOT NULL,
  `level` varchar(100) NOT NULL,
  `badge` varchar(100) NOT NULL,
  `color` varchar(50) NOT NULL DEFAULT 'gold',
  `description` text NOT NULL,
  `focus` text NOT NULL,
  `output` text NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `preparation_workflow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `preparation_workflow` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `step_number` int(11) NOT NULL DEFAULT 1,
  `title` varchar(255) NOT NULL,
  `actor` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `badge` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pricing_tiers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pricing_tiers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` varchar(50) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `badge` varchar(100) DEFAULT NULL,
  `nominal` bigint(20) NOT NULL DEFAULT 0,
  `nominal_display` varchar(100) NOT NULL,
  `unit_qty` int(11) NOT NULL DEFAULT 1,
  `container_qty` varchar(150) DEFAULT NULL,
  `porsi_proyek` varchar(255) DEFAULT NULL,
  `jaminan_aset` varchar(255) DEFAULT NULL,
  `operator_perk` text DEFAULT NULL,
  `deskripsi` text NOT NULL,
  `profit_per_siklus` bigint(20) NOT NULL DEFAULT 0,
  `profit_2x_tahunan` bigint(20) NOT NULL DEFAULT 0,
  `profit_3x_tahunan` bigint(20) NOT NULL DEFAULT 0,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_pricing_project` (`project_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `project_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` varchar(50) NOT NULL,
  `tagline` varchar(255) DEFAULT NULL,
  `what_will_provide_title` varchar(255) NOT NULL DEFAULT 'Alokasi Penggunaan Modal (Use of Funds & Asset Acquisition)',
  `what_will_provide_content` text NOT NULL,
  `sinergi_title` varchar(255) NOT NULL DEFAULT 'Struktur Kemitraan Strategis & Jaminan Penyerapan Pasar (Offtake Framework)',
  `sinergi_content` text NOT NULL,
  `summary_title` varchar(255) NOT NULL DEFAULT 'Ringkasan Kelayakan Investasi & Profil Risiko (Feasibility Summary)',
  `summary_content` text NOT NULL,
  `rab_executive_json` longtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_project_detail` (`project_id`),
  CONSTRAINT `fk_project_detail` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `project_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project_documents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` varchar(50) NOT NULL,
  `investor_id` int(11) DEFAULT NULL,
  `doc_number` varchar(100) NOT NULL,
  `doc_title` varchar(255) NOT NULL,
  `doc_type` varchar(50) NOT NULL DEFAULT 'laporan',
  `file_path` varchar(255) DEFAULT NULL,
  `file_size` varchar(50) NOT NULL DEFAULT '1.4 MB',
  `published_by` varchar(100) NOT NULL DEFAULT 'Operator MGI',
  `status` enum('draft','published') NOT NULL DEFAULT 'published',
  `published_at` datetime NOT NULL DEFAULT current_timestamp(),
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_proj_docs_project` (`project_id`),
  KEY `idx_proj_docs_investor` (`investor_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `project_inventory`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project_inventory` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` varchar(50) NOT NULL,
  `item_code` varchar(100) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `category` varchar(100) NOT NULL DEFAULT 'Alat Berat',
  `serial_number` varchar(100) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `unit_cost` decimal(18,2) NOT NULL DEFAULT 0.00,
  `total_value` decimal(18,2) NOT NULL DEFAULT 0.00,
  `condition_status` varchar(50) NOT NULL DEFAULT 'Grade A (Prima)',
  `operational_status` varchar(50) NOT NULL DEFAULT 'Aktif Beroperasi',
  `location` varchar(255) NOT NULL DEFAULT 'Pool Narogong & Workshop Kebumen',
  `smh_hours` int(11) NOT NULL DEFAULT 0,
  `last_inspection_date` date DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_proj_inv_project` (`project_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `project_simulation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project_simulation` (
  `project_id` varchar(50) NOT NULL,
  `tenor_bulan` int(11) NOT NULL DEFAULT 36,
  `estimasi_return_persen` decimal(5,2) NOT NULL DEFAULT 30.00,
  `modal_kerja_bulanan_persen` decimal(5,2) NOT NULL DEFAULT 2.20,
  `minimum_investasi` bigint(20) NOT NULL DEFAULT 500000000,
  `maximum_investasi` bigint(20) NOT NULL DEFAULT 500000000000,
  `default_investasi` bigint(20) NOT NULL DEFAULT 500000000,
  `notes` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`project_id`),
  CONSTRAINT `fk_simulation_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `projects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `projects` (
  `id` varchar(50) NOT NULL,
  `city_id` int(11) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `category` varchar(150) NOT NULL DEFAULT 'Alat Berat & Infrastruktur',
  `image` varchar(255) NOT NULL DEFAULT 'assets/img/komatsu.jpg',
  `funding_collected` bigint(20) NOT NULL DEFAULT 0,
  `funding_target` bigint(20) NOT NULL,
  `currency` varchar(10) NOT NULL DEFAULT 'IDR',
  `status` enum('Open','Fully Funded','Closed','Coming Soon') NOT NULL DEFAULT 'Open',
  `featured` tinyint(1) NOT NULL DEFAULT 0,
  `lokasi` varchar(255) NOT NULL,
  `target_display` varchar(255) DEFAULT NULL,
  `tenor` varchar(100) NOT NULL,
  `return_rate` varchar(100) NOT NULL,
  `risk_level` varchar(100) NOT NULL,
  `min_investment` varchar(100) NOT NULL DEFAULT 'Rp 500.000.000',
  `payout` varchar(100) NOT NULL DEFAULT 'Bagi Hasil Kompetitif',
  `remaining_days` varchar(100) NOT NULL DEFAULT '18 Hari Tersisa',
  `asset_backed` varchar(255) NOT NULL DEFAULT 'Unit CBU Grade A & BPKB',
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `realtime_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `realtime_events` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `event_type` varchar(64) NOT NULL,
  `section` varchar(64) NOT NULL,
  `action` varchar(32) NOT NULL DEFAULT 'update',
  `payload` longtext DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_created_id` (`created_at`,`id`),
  KEY `idx_section` (`section`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `system_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `system_settings` (
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `team_members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `team_members` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `role_title` varchar(150) NOT NULL,
  `department` varchar(100) NOT NULL DEFAULT 'Direksi & Manajemen',
  `photo_url` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `linkedin_url` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `testimonials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `testimonials` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `role_title` varchar(150) NOT NULL,
  `company_name` varchar(200) DEFAULT NULL,
  `avatar_url` varchar(255) DEFAULT NULL,
  `rating` tinyint(4) NOT NULL DEFAULT 5,
  `content` text NOT NULL,
  `project_name` varchar(255) DEFAULT NULL,
  `category` enum('investor','kontraktor','partner') NOT NULL DEFAULT 'investor',
  `is_featured` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `transformasi_steps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `transformasi_steps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `step_order` int(11) NOT NULL DEFAULT 1,
  `year_or_phase` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `subtitle` varchar(255) DEFAULT NULL,
  `description` text NOT NULL,
  `highlights` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`highlights`)),
  `status` varchar(50) NOT NULL DEFAULT 'completed',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

DROP TABLE IF EXISTS `security_rate_limits`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `security_rate_limits` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rate_key` varchar(64) NOT NULL COMMENT 'SHA-256 hash dari action+identifier+IP',
  `action_type` varchar(32) NOT NULL,
  `identifier` varchar(128) NOT NULL DEFAULT '',
  `ip_address` varchar(45) NOT NULL,
  `attempts` int(10) unsigned NOT NULL DEFAULT 1,
  `first_attempt_at` int(10) unsigned NOT NULL,
  `last_attempt_at` int(10) unsigned NOT NULL,
  `locked_until` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_rate_key` (`rate_key`),
  KEY `idx_action_id` (`action_type`,`identifier`),
  KEY `idx_ip` (`ip_address`),
  KEY `idx_lock` (`locked_until`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;


/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

