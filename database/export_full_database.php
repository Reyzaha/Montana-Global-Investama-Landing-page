<?php
/**
 * PT MONTANA GLOBAL INVESTAMA
 * Export Full Database (Schema + Data) from Localhost to clean UTF-8 SQL
 */

require_once __DIR__ . '/../backend/config/db.php';

try {
    $db = getDB();
    $port = Database::getConnectedPort();
    echo "Connected to Localhost Database on port {$port}\n";

    $outputFile = __DIR__ . '/mgi_landing_production_complete.sql';
    $handle = fopen($outputFile, 'wb');
    if (!$handle) {
        throw new Exception("Cannot open file {$outputFile} for writing");
    }

    // Write header
    $header = "-- =====================================================================\n"
            . "-- DATABASE DUMP: PT MONTANA GLOBAL INVESTAMA (mgi_landing)\n"
            . "-- Exported from Localhost: " . date('Y-m-d H:i:s') . "\n"
            . "-- Encoding: UTF-8 without BOM\n"
            . "-- =====================================================================\n\n"
            . "SET NAMES utf8mb4;\n"
            . "SET CHARACTER SET utf8mb4;\n"
            . "SET FOREIGN_KEY_CHECKS = 0;\n"
            . "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n"
            . "SET time_zone = '+07:00';\n\n"
            . "CREATE DATABASE IF NOT EXISTS `mgi_landing` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;\n"
            . "USE `mgi_landing`;\n\n";

    fwrite($handle, $header);

    // Get all tables
    $tables = $db->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'")->fetchAll(PDO::FETCH_NUM);
    $tableNames = array_map(fn($t) => $t[0], $tables);

    echo "Found " . count($tableNames) . " tables to export.\n";

    foreach ($tableNames as $table) {
        echo " - Exporting table `{$table}`...";

        // Table Structure
        fwrite($handle, "-- -----------------------------------------------------\n");
        fwrite($handle, "-- Table structure for `{$table}`\n");
        fwrite($handle, "-- -----------------------------------------------------\n");
        fwrite($handle, "DROP TABLE IF EXISTS `{$table}`;\n");

        $createRow = $db->query("SHOW CREATE TABLE `{$table}`")->fetch(PDO::FETCH_NUM);
        $createSql = $createRow[1];
        fwrite($handle, $createSql . ";\n\n");

        // Table Data
        $stmt = $db->query("SELECT * FROM `{$table}`");
        $rowCount = 0;

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (!empty($rows)) {
            fwrite($handle, "-- Dumping data for table `{$table}`\n");
            
            // Get column names
            $columns = array_keys($rows[0]);
            $colList = implode('`, `', $columns);

            $chunks = array_chunk($rows, 50);
            foreach ($chunks as $chunk) {
                $valSqls = [];
                foreach ($chunk as $row) {
                    $vals = [];
                    foreach ($columns as $c) {
                        $v = $row[$c];
                        if ($v === null) {
                            $vals[] = 'NULL';
                        } else {
                            $vals[] = $db->quote($v);
                        }
                    }
                    $valSqls[] = '(' . implode(', ', $vals) . ')';
                    $rowCount++;
                }
                fwrite($handle, "INSERT INTO `{$table}` (`{$colList}`) VALUES\n" . implode(",\n", $valSqls) . ";\n");
            }
            fwrite($handle, "\n");
        }

        echo " ({$rowCount} rows)\n";
    }

    fwrite($handle, "SET FOREIGN_KEY_CHECKS = 1;\n");
    fclose($handle);

    echo "\n[SUCCESS] Successfully exported to: {$outputFile}\n";
    echo "File size: " . number_format(filesize($outputFile) / 1024, 2) . " KB\n";

    // Also update root mgi_landing_backup.sql in clean UTF-8
    copy($outputFile, __DIR__ . '/../mgi_landing_backup.sql');
    echo "[SUCCESS] Copied to root mgi_landing_backup.sql\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
