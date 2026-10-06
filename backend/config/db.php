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
            // (Removed to prevent dummy data from being automatically populated in production)
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
                    `phone` VARCHAR(20) NULL DEFAULT NULL,
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
                    INDEX `idx_leads_email` (`email`),
                    INDEX `idx_leads_created` (`created_at`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");

            // Pastikan phone nullable jika tabel lama dibuat NOT NULL
            try {
                $db->exec("ALTER TABLE `leads` MODIFY COLUMN `phone` VARCHAR(20) NULL DEFAULT NULL");
            } catch (Throwable $_) {}

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
