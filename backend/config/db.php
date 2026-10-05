<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — DATABASE CONNECTION CONFIG
 * Supports adaptive port detection (XAMPP 3307 vs Standard 3306)
 * and PDO singleton pattern.
 */

class Database {
    private static ?PDO $instance = null;
    private static ?int $connectedPort = null;

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            $host = getenv('DB_HOST') ?: '127.0.0.1';
            $dbname = getenv('DB_NAME') ?: 'mgi_landing';
            $user = getenv('DB_USER') ?: 'root';
            $pass = getenv('DB_PASS') ?: '';
            
            // Allow override via environment, otherwise test 3307 then 3306
            $envPort = getenv('DB_PORT');
            $portsToTry = $envPort ? [(int)$envPort] : [3307, 3306];
            
            $connected = false;
            $lastException = null;

            foreach ($portsToTry as $port) {
                // Quick socket handshake check (max 250ms) to detect dead/hung daemons on Windows
                $fp = @fsockopen($host, $port, $errno, $errstr, 0.2);
                if (!$fp) {
                    continue;
                }
                stream_set_timeout($fp, 0, 200000); // 200ms
                $char = fgetc($fp);
                $meta = stream_get_meta_data($fp);
                fclose($fp);
                if ($meta['timed_out'] || $char === false) {
                    // Daemon is dead/hung, skip immediately to avoid 60s freeze!
                    continue;
                }

                try {
                    $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
                    $options = [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                        PDO::ATTR_TIMEOUT => 2,
                        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
                    ];
                    
                    self::$instance = new PDO($dsn, $user, $pass, $options);
                    self::$connectedPort = $port;
                    $connected = true;
                    break;
                } catch (PDOException $e) {
                    $lastException = $e;
                    // If the error is that the database doesn't exist yet, connect to server without dbname
                    if ($e->getCode() == 1049) {
                        try {
                            $serverDsn = "mysql:host={$host};port={$port};charset=utf8mb4";
                            $tmpPdo = new PDO($serverDsn, $user, $pass);
                            $tmpPdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbname}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                            
                            self::$instance = new PDO($dsn, $user, $pass, $options);
                            self::$connectedPort = $port;
                            $connected = true;
                            break;
                        } catch (Exception $inner) {
                            $lastException = $inner;
                        }
                    }
                }
            }

            if (!$connected) {
                throw new Exception("Koneksi database gagal: " . ($lastException ? $lastException->getMessage() : 'Port tidak merespons'));
            }

            self::ensureSchemaUpdates(self::$instance);
        }

        return self::$instance;
    }

    private static function ensureSchemaUpdates(PDO $db): void {
        try {
            $cols = $db->query("SHOW COLUMNS FROM `investors` LIKE 'business_activity'")->fetchAll();
            if (empty($cols)) {
                $db->exec("ALTER TABLE `investors` ADD COLUMN `business_activity` VARCHAR(255) NULL AFTER `phone`");
            }

            $colsComp = $db->query("SHOW COLUMNS FROM `investor_companies` LIKE 'business_activity'")->fetchAll();
            if (empty($colsComp)) {
                $db->exec("ALTER TABLE `investor_companies` ADD COLUMN `business_activity` VARCHAR(255) NULL AFTER `business_name`");
            }

            // Ensure rab_executive_json column exists in project_details
            $colsRab = $db->query("SHOW COLUMNS FROM `project_details` LIKE 'rab_executive_json'")->fetchAll();
            if (empty($colsRab)) {
                $db->exec("ALTER TABLE `project_details` ADD COLUMN `rab_executive_json` LONGTEXT NULL AFTER `summary_content`");
            }

            // Ensure financial_records table exists for Odoo/Kledo style financial modules & billing
            $db->exec("
                CREATE TABLE IF NOT EXISTS `financial_records` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `investor_id` INT NOT NULL,
                    `project_id` VARCHAR(50) NOT NULL,
                    `module` ENUM('neraca', 'labarugi', 'pembelian', 'penjualan') NOT NULL,
                    `category` VARCHAR(100) NOT NULL,
                    `record_number` VARCHAR(100) NOT NULL,
                    `title` VARCHAR(255) NOT NULL,
                    `description` TEXT NULL,
                    `amount` DECIMAL(18, 2) NOT NULL,
                    `flow_type` ENUM('in', 'out', 'balance_asset') DEFAULT 'out',
                    `vendor_client` VARCHAR(255) NULL,
                    `unit_detail` VARCHAR(255) NULL,
                    `status` ENUM('draft', 'sent', 'paid', 'verified') DEFAULT 'sent',
                    `is_billing` TINYINT(1) DEFAULT 1,
                    `billing_status` ENUM('unpaid', 'paid', 'settled', 'reported') DEFAULT 'reported',
                    `transaction_date` DATE NOT NULL,
                    `due_date` DATE NULL,
                    `attachment_url` VARCHAR(255) NULL,
                    `created_by` VARCHAR(100) DEFAULT 'Admin MGI',
                    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX `idx_fin_investor` (`investor_id`),
                    INDEX `idx_fin_project` (`project_id`),
                    INDEX `idx_fin_module` (`module`),
                    INDEX `idx_fin_billing` (`is_billing`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");

            // Seed initial 5M allocation and MIU unit purchase records if empty
            $cnt = (int)$db->query("SELECT COUNT(*) FROM `financial_records`")->fetchColumn();
            if ($cnt === 0) {
                // Ensure investor 1 has 5 Billion portfolio if needed
                $db->exec("UPDATE `investor_portfolios` SET `amount` = 5000000000, `allocated_units` = '2x Unit Alat Berat CBU Jepang' WHERE `investor_id` = 1 AND `project_id` = 'proj-jkt-jabar' LIMIT 1");

                $seedSql = "
                    INSERT INTO `financial_records` 
                    (`investor_id`, `project_id`, `module`, `category`, `record_number`, `title`, `description`, `amount`, `flow_type`, `vendor_client`, `unit_detail`, `status`, `is_billing`, `billing_status`, `transaction_date`, `due_date`, `created_by`)
                    VALUES
                    (1, 'proj-jkt-jabar', 'neraca', 'Modal Disetor & Kas Awal', 'CAP-MGI-2026-001', 'Penempatan Modal Investasi Proyek', 'Setoran penempatan modal kerja proyek regional Jabodetabek senilai Rp 5.000.000.000 ditempatkan di rekening escrow proyek Bank Mandiri.', 5000000000.00, 'in', 'PT Montana Global Investama', 'Akad Kontrak Proyek Alat Berat', 'verified', 0, 'settled', '2026-01-05', '2026-01-05', 'Admin MGI'),

                    (1, 'proj-jkt-jabar', 'pembelian', 'Pengadaan Unit Alat Berat (MIU)', 'BILL-MIU-2026-001', 'Pembelian 2x Unit Alat Berat via MIU', 'Alokasi penyerapan modal 5 Miliar: pengurangan kas sebesar Rp 2.800.000.000 untuk realisasi pembelian 2 unit Alat Berat Grade A CBU Jepang melalui vendor internal grup PT Montana Industri Utama (MIU).', 2800000000.00, 'out', 'PT Montana Industri Utama (MIU)', '2x Unit Alat Berat (SN: AB-9481 & AB-9482)', 'verified', 1, 'settled', '2026-01-18', '2026-01-25', 'Admin MGI'),

                    (1, 'proj-jkt-jabar', 'pembelian', 'Attachment & Logistik CBU (MIU)', 'BILL-MIU-2026-002', 'Pengadaan Breaker & Logistik Impor CBU via MIU', 'Pengadaan hydraulic breaker kit & biaya customs clearance logistik impor unit CBU Jepang via workshop PT Montana Industri Utama (MIU).', 350000000.00, 'out', 'PT Montana Industri Utama (MIU)', '2 Set Hydraulic Breaker & Sparepart Kit', 'verified', 1, 'settled', '2026-02-10', '2026-02-15', 'Admin MGI'),

                    (1, 'proj-jkt-jabar', 'neraca', 'Aset Tetap Unit Fisik (MIU)', 'AST-MIU-2026-001', 'Kapitalisasi Aset Unit Alat Berat MIU', 'Pencatatan aktiva tetap unit fisik 2 unit alat berat di pool workshop Jabodetabek bernilai perolehan Rp 3.150.000.000.', 3150000000.00, 'balance_asset', 'PT Montana Industri Utama (MIU)', '2x Unit Alat Berat CBU Jepang', 'verified', 0, 'settled', '2026-02-15', '2026-02-15', 'Admin MGI'),

                    (1, 'proj-jkt-jabar', 'penjualan', 'Kontrak Sewa Unit Mining & Earthmoving', 'INV-SLS-2026-001', 'Kontrak Sewa Utilisasi Armada Alat Berat — Tahap 1', 'Pendapatan sewa operasional 2 unit alat berat untuk proyek Cut & Fill kawasan industri Jabodetabek selama 2 bulan operasional.', 450000000.00, 'in', 'PT Surya Semesta Mandiri', 'Kontrak Sewa Unit AB-9481 & AB-9482', 'verified', 0, 'paid', '2026-03-25', '2026-03-30', 'Admin MGI'),

                    (1, 'proj-jkt-jabar', 'labarugi', 'Beban Operasional & Pemeliharaan', 'EXP-OPS-2026-001', 'Beban Pemeliharaan Rutin Workshop & Fuel', 'Biaya suku cadang habis pakai (fast moving parts), oli hidrolik, dan pemeliharaan rutin di pool workshop Kebumen & Jabodetabek.', 85000000.00, 'out', 'Workshop Pool MGI Regional', 'Service Rutin 250 Jam', 'verified', 0, 'settled', '2026-03-28', '2026-03-28', 'Admin MGI'),

                    (1, 'proj-jkt-jabar', 'labarugi', 'Distribusi Dividen Bagi Hasil', 'BILL-DIV-2026-Q1', 'Billing Bagi Hasil Dividen Kuartal I - 2026 (Nett)', 'Faktur distribusi laba bersih operasional proyek periode Kuartal I 2026 yang ditransfer ke rekening bank terdaftar investor.', 125000000.00, 'out', 'Investor PT Montana Global Investama', 'Bagi Hasil Kuartal I', 'verified', 1, 'paid', '2026-04-15', '2026-04-15', 'Admin MGI'),

                    (1, 'proj-jkt-jabar', 'penjualan', 'Kontrak Sewa Unit Infrastruktur', 'INV-SLS-2026-002', 'Kontrak Sewa Utilisasi Armada Alat Berat — Tahap 2', 'Pendapatan sewa utilisasi lanjutan untuk proyek tol dan logistik kawasan barat.', 520000000.00, 'in', 'PT Citra Konstruksi Nusantara', 'Kontrak Sewa Unit AB-9481 & AB-9482', 'verified', 0, 'paid', '2026-06-28', '2026-06-30', 'Admin MGI'),

                    (1, 'proj-jkt-jabar', 'labarugi', 'Distribusi Dividen Bagi Hasil', 'BILL-DIV-2026-Q2', 'Billing Bagi Hasil Dividen Kuartal II - 2026 (Nett)', 'Faktur dividen bagi hasil kompetitif periode Kuartal II 2026 yang telah berhasil disalurkan ke rekening bank investor.', 145000000.00, 'out', 'Investor PT Montana Global Investama', 'Bagi Hasil Kuartal II', 'verified', 1, 'paid', '2026-07-15', '2026-07-15', 'Admin MGI')
                ";
                $db->exec($seedSql);
            }
        } catch (Throwable $e) {
            // Silently ignore or log during initial installation
            error_log("Schema update warning: " . $e->getMessage());
        }

        // Lead capture table (Landing Page Konsultasi / Google Ads).
        // Dipisah dari blok di atas agar tetap dibuat walau migrasi lama gagal.
        try {
            $db->exec("
                CREATE TABLE IF NOT EXISTS `leads` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `account_type` ENUM('perorangan','perusahaan') NOT NULL DEFAULT 'perorangan',
                    `full_name` VARCHAR(120) NOT NULL,
                    `phone` VARCHAR(20) NOT NULL,
                    `email` VARCHAR(150) NULL,
                    `business_name` VARCHAR(150) NULL,
                    `legal_entity` VARCHAR(50) NULL,
                    `pic_position` VARCHAR(100) NULL,
                    `city` VARCHAR(100) NULL,
                    `investment_range` VARCHAR(60) NULL,
                    `status` ENUM('new','contacted','meeting','site_visit','deal','lost') NOT NULL DEFAULT 'new',
                    `admin_notes` TEXT NULL,
                    `assigned_to` VARCHAR(100) NULL,
                    `contacted_at` DATETIME NULL,
                    `utm_source` VARCHAR(100) NULL,
                    `utm_medium` VARCHAR(100) NULL,
                    `utm_campaign` VARCHAR(150) NULL,
                    `utm_term` VARCHAR(150) NULL,
                    `utm_content` VARCHAR(150) NULL,
                    `gclid` VARCHAR(255) NULL,
                    `gbraid` VARCHAR(255) NULL,
                    `wbraid` VARCHAR(255) NULL,
                    `landing_page` VARCHAR(255) NULL,
                    `referrer` VARCHAR(255) NULL,
                    `ip_address` VARCHAR(45) NULL,
                    `user_agent` VARCHAR(255) NULL,
                    `consent_at` DATETIME NULL,
                    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX `idx_leads_status` (`status`),
                    INDEX `idx_leads_phone` (`phone`),
                    INDEX `idx_leads_created` (`created_at`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");

            // Tambah kolom Perorangan/Perusahaan bila tabel sudah terlanjur dibuat versi lama
            $leadCols = [
                'account_type'  => "ENUM('perorangan','perusahaan') NOT NULL DEFAULT 'perorangan' AFTER `id`",
                'email'         => "VARCHAR(150) NULL AFTER `phone`",
                'business_name' => "VARCHAR(150) NULL AFTER `email`",
                'legal_entity'  => "VARCHAR(50) NULL AFTER `business_name`",
                'pic_position'  => "VARCHAR(100) NULL AFTER `legal_entity`",
            ];
            foreach ($leadCols as $col => $def) {
                $exists = $db->query("SHOW COLUMNS FROM `leads` LIKE '{$col}'")->fetchAll();
                if (empty($exists)) {
                    $db->exec("ALTER TABLE `leads` ADD COLUMN `{$col}` {$def}");
                }
            }
        } catch (Throwable $e) {
            error_log("Schema update warning (leads): " . $e->getMessage());
        }
    }

    public static function getConnectedPort(): ?int {
        return self::$connectedPort;
    }
}

function getDB(): PDO {
    return Database::getConnection();
}
