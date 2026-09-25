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
        } catch (Throwable $e) {
            // Silently ignore if investors table does not exist yet during initial installation
        }
    }

    public static function getConnectedPort(): ?int {
        return self::$connectedPort;
    }
}

function getDB(): PDO {
    return Database::getConnection();
}
