-- =============================================================================
-- DATABASE SCHEMA: PT MONTANA GLOBAL INVESTAMA (MGI)
-- Versi: Fase 2 (Backend API & Dashboard Admin CRUD)
-- Catatan Kritis: Seluruh kolom nominal dana WAJIB menggunakan BIGINT
-- agar mendukung rentang simulasi Rp 500 Juta s/d Rp 500 Miliar (500M)
-- =============================================================================

CREATE DATABASE IF NOT EXISTS `mgi_landing` 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE `mgi_landing`;

-- 1. Tabel Administrator
CREATE TABLE IF NOT EXISTS `admin_users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(100) NOT NULL UNIQUE,
  `email` VARCHAR(191) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `full_name` VARCHAR(150) NOT NULL DEFAULT 'Super Administrator',
  `role` ENUM('superadmin', 'admin', 'editor') NOT NULL DEFAULT 'admin',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `mfa_secret` VARCHAR(64) NULL,
  `mfa_enabled` TINYINT(1) NOT NULL DEFAULT 0,
  `last_login` DATETIME NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Tabel Akun Investor (Perorangan & Perusahaan)
CREATE TABLE IF NOT EXISTS `investors` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `account_type` ENUM('perorangan', 'perusahaan') NOT NULL DEFAULT 'perorangan',
  `email` VARCHAR(191) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `full_name` VARCHAR(255) NULL,
  `citizenship` ENUM('Indonesia (WNI)', 'Warga Negara Asing (WNA)') DEFAULT 'Indonesia (WNI)',
  `phone` VARCHAR(50) NULL,
  `status` ENUM('active', 'pending_verification', 'suspended') NOT NULL DEFAULT 'active',
  `notes` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Tabel Detail Profil Investor Korporasi / Perusahaan
CREATE TABLE IF NOT EXISTS `investor_companies` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `investor_id` INT NOT NULL,
  `business_name` VARCHAR(255) NOT NULL,
  `legal_entity` ENUM(
    'Perseroan Terbatas (PT)',
    'Persekutuan Komanditer (CV)',
    'PT Perorangan',
    'Usaha Dagang (UD)',
    'Tidak ada'
  ) NOT NULL,
  `company_address` TEXT NOT NULL,
  `pic_name` VARCHAR(255) NOT NULL,
  `pic_position` VARCHAR(150) NOT NULL,
  `company_phone` VARCHAR(50) NOT NULL,
  `annual_turnover` ENUM(
    'Di bawah Rp500 Juta',
    'Rp500 Juta – Rp2,5 Miliar',
    'Rp2,5 Miliar – Rp10 Miliar',
    'Rp10 Miliar – Rp50 Miliar',
    'Rp50 Miliar – Rp250 Miliar',
    'Di atas Rp250 Miliar'
  ) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_investor_company` FOREIGN KEY (`investor_id`) 
    REFERENCES `investors`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Tabel Proyek Investasi Utama
CREATE TABLE IF NOT EXISTS `projects` (
  `id` VARCHAR(50) PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `category` VARCHAR(150) NOT NULL DEFAULT 'Alat Berat & Infrastruktur',
  `image` VARCHAR(255) NOT NULL DEFAULT 'assets/img/komatsu.jpg',
  `funding_collected` BIGINT NOT NULL DEFAULT 0,
  `funding_target` BIGINT NOT NULL,
  `currency` VARCHAR(10) NOT NULL DEFAULT 'IDR',
  `status` ENUM('Open', 'Fully Funded', 'Closed', 'Coming Soon') NOT NULL DEFAULT 'Open',
  `featured` TINYINT(1) NOT NULL DEFAULT 0,
  `lokasi` VARCHAR(255) NOT NULL,
  `target_display` VARCHAR(255) NULL,
  `tenor` VARCHAR(100) NOT NULL,
  `return_rate` VARCHAR(100) NOT NULL,
  `risk_level` VARCHAR(100) NOT NULL,
  `min_investment` VARCHAR(100) NOT NULL DEFAULT 'Rp 500.000.000',
  `payout` VARCHAR(100) NOT NULL DEFAULT 'Bagi Hasil Kuartalan',
  `remaining_days` VARCHAR(100) NOT NULL DEFAULT '18 Hari Tersisa',
  `asset_backed` VARCHAR(255) NOT NULL DEFAULT 'Unit CBU Grade A & BPKB',
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Tabel Detail Deskripsi Proyek
CREATE TABLE IF NOT EXISTS `project_details` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `project_id` VARCHAR(50) NOT NULL,
  `tagline` VARCHAR(255) NULL,
  `what_will_provide_title` VARCHAR(255) NOT NULL DEFAULT 'Alokasi Penggunaan Modal (Use of Funds & Asset Acquisition)',
  `what_will_provide_content` TEXT NOT NULL,
  `sinergi_title` VARCHAR(255) NOT NULL DEFAULT 'Struktur Kemitraan Strategis & Jaminan Penyerapan Pasar (Offtake Framework)',
  `sinergi_content` TEXT NOT NULL,
  `summary_title` VARCHAR(255) NOT NULL DEFAULT 'Ringkasan Kelayakan Investasi & Profil Risiko (Feasibility Summary)',
  `summary_content` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_project_detail` FOREIGN KEY (`project_id`) 
    REFERENCES `projects`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Tabel Rencana Anggaran Biaya (RAB / Funding Items per Proyek)
CREATE TABLE IF NOT EXISTS `funding_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `project_id` VARCHAR(50) NOT NULL,
  `no_urut` INT NOT NULL DEFAULT 1,
  `item_name` VARCHAR(255) NOT NULL,
  `quantity` INT NOT NULL DEFAULT 1,
  `unit_price` BIGINT NOT NULL DEFAULT 0,
  `total` BIGINT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_funding_item_project` FOREIGN KEY (`project_id`) 
    REFERENCES `projects`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Tabel Parameter Simulasi BEP & ROI Proyek
CREATE TABLE IF NOT EXISTS `project_simulation` (
  `project_id` VARCHAR(50) PRIMARY KEY,
  `tenor_bulan` INT NOT NULL DEFAULT 36,
  `estimasi_return_persen` DECIMAL(5,2) NOT NULL DEFAULT 30.00,
  `modal_kerja_bulanan_persen` DECIMAL(5,2) NOT NULL DEFAULT 2.20,
  `minimum_investasi` BIGINT NOT NULL DEFAULT 500000000,
  `maximum_investasi` BIGINT NOT NULL DEFAULT 500000000000,
  `default_investasi` BIGINT NOT NULL DEFAULT 500000000,
  `notes` TEXT NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_simulation_project` FOREIGN KEY (`project_id`) 
    REFERENCES `projects`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Tabel Company Profile & Tata Kelola
CREATE TABLE IF NOT EXISTS `company_profile` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `setting_key` VARCHAR(100) NOT NULL UNIQUE,
  `setting_value` LONGTEXT NOT NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Tabel Milestone Transformasi (Journey)
CREATE TABLE IF NOT EXISTS `transformasi_steps` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `step_order` INT NOT NULL DEFAULT 1,
  `year_or_phase` VARCHAR(50) NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `subtitle` VARCHAR(255) NULL,
  `description` TEXT NOT NULL,
  `highlights` JSON NULL,
  `status` VARCHAR(50) NOT NULL DEFAULT 'completed',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Tabel Entitas Preparation (MIU, MGI, MSI, Mypurcase)
CREATE TABLE IF NOT EXISTS `preparation_entities` (
  `code` VARCHAR(50) PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `slogan` VARCHAR(255) NULL,
  `role` VARCHAR(255) NOT NULL,
  `level` VARCHAR(100) NOT NULL,
  `badge` VARCHAR(100) NOT NULL,
  `color` VARCHAR(50) NOT NULL DEFAULT 'gold',
  `description` TEXT NOT NULL,
  `focus` TEXT NOT NULL,
  `output` TEXT NOT NULL,
  `location` VARCHAR(255) NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. Tabel Alur Kerja Sinergi (Preparation Workflow)
CREATE TABLE IF NOT EXISTS `preparation_workflow` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `step_number` INT NOT NULL DEFAULT 1,
  `title` VARCHAR(255) NOT NULL,
  `actor` VARCHAR(255) NOT NULL,
  `description` TEXT NOT NULL,
  `badge` VARCHAR(100) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. Tabel Node Ekosistem (Hierarki Organisasi)
CREATE TABLE IF NOT EXISTS `ekosistem_nodes` (
  `id` VARCHAR(50) PRIMARY KEY,
  `label` VARCHAR(255) NOT NULL,
  `subtitle` VARCHAR(255) NULL,
  `parent_id` VARCHAR(50) NULL,
  `level` INT NOT NULL DEFAULT 0,
  `badge` VARCHAR(100) NULL,
  `role_desc` TEXT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_ekosistem_parent` FOREIGN KEY (`parent_id`) 
    REFERENCES `ekosistem_nodes`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13. Tabel Pengaturan Sistem (Settings)
CREATE TABLE IF NOT EXISTS `system_settings` (
  `setting_key` VARCHAR(100) PRIMARY KEY,
  `setting_value` TEXT NOT NULL,
  `description` VARCHAR(255) NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 14. Tabel Audit Log Aktivitas Admin (Compliance & GCG)
CREATE TABLE IF NOT EXISTS `admin_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `admin_id` INT NULL,
  `action` VARCHAR(100) NOT NULL,
  `target_type` VARCHAR(50) NOT NULL,
  `target_id` VARCHAR(100) NULL,
  `details` TEXT NULL,
  `ip_address` VARCHAR(50) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_admin_log_user` FOREIGN KEY (`admin_id`) 
    REFERENCES `admin_users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 15. Tabel Rekening Bank Investor (Penerimaan Hasil Investasi & Dividen)
CREATE TABLE IF NOT EXISTS `investor_bank_accounts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `investor_id` INT NOT NULL,
  `bank_name` VARCHAR(100) NOT NULL,
  `account_number` VARCHAR(50) NOT NULL,
  `account_holder` VARCHAR(255) NOT NULL,
  `branch` VARCHAR(150) NULL,
  `is_primary` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_bank_investor` FOREIGN KEY (`investor_id`) 
    REFERENCES `investors`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 16. Tabel Portofolio Penempatan Investasi Riil Investor
CREATE TABLE IF NOT EXISTS `investor_portfolios` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `investor_id` INT NOT NULL,
  `project_id` VARCHAR(50) NOT NULL,
  `contract_number` VARCHAR(100) NOT NULL UNIQUE,
  `amount` BIGINT NOT NULL DEFAULT 500000000,
  `return_rate` VARCHAR(50) NOT NULL DEFAULT '≥30% (p.a.)',
  `tenor` VARCHAR(50) NOT NULL DEFAULT '36 Bulan',
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `next_payout_date` DATE NULL,
  `payout_received` BIGINT NOT NULL DEFAULT 0,
  `allocated_units` VARCHAR(255) NULL,
  `status` ENUM('active', 'completed', 'pending_allocation') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_portfolio_investor` FOREIGN KEY (`investor_id`) 
    REFERENCES `investors`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_portfolio_project` FOREIGN KEY (`project_id`) 
    REFERENCES `projects`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 17. Tabel Update Kampanye & Progres Lapangan (Campaign Update)
CREATE TABLE IF NOT EXISTS `campaign_updates` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `project_id` VARCHAR(50) NULL,
  `title` VARCHAR(255) NOT NULL,
  `category` VARCHAR(100) NOT NULL DEFAULT 'Operasional Lapangan',
  `update_date` DATE NOT NULL,
  `content` TEXT NOT NULL,
  `image_url` VARCHAR(255) NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_campaign_project` FOREIGN KEY (`project_id`) 
    REFERENCES `projects`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

