<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — AUTOMATED DATABASE & MEDIA BACKUP
 * Dumps MySQL schema/data and archives uploaded media into timestamped ZIP files.
 *
 * Usage via CLI / Cron:
 * php scripts/backup.php
 */

require_once __DIR__ . '/../backend/config/db.php';

// Strict Execution Guard: CLI Only
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    die(json_encode(['error' => 'Akses ditolak: Skrip backup hanya dapat dijalankan melalui CLI atau cron job.']));
}

$backupDir = dirname(__DIR__) . '/backups';
if (!is_dir($backupDir)) {
    @mkdir($backupDir, 0755, true);
}

$timestamp = date('Ymd_His');
$sqlFile = "{$backupDir}/dump_{$timestamp}.sql";
$zipFile = "{$backupDir}/backup_{$timestamp}.zip";

echo "=== [MGI BACKUP] Memulai proses backup otomatis ({$timestamp}) ===\n";

try {
    $db = getDB();
    $dbname = getenv('DB_NAME') ?: 'mgi_landing';

    // 1. Generate SQL Dump
    $fp = fopen($sqlFile, 'w');
    if (!$fp) {
        throw new Exception("Tidak dapat membuat file dump SQL: {$sqlFile}");
    }

    fwrite($fp, "-- PT MONTANA GLOBAL INVESTAMA DATABASE BACKUP\n");
    fwrite($fp, "-- Database: {$dbname}\n");
    fwrite($fp, "-- Tanggal: " . date('Y-m-d H:i:s T') . "\n");
    fwrite($fp, "SET FOREIGN_KEY_CHECKS=0;\n\n");

    // Get all tables
    $tables = $db->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'")->fetchAll(PDO::FETCH_COLUMN);

    foreach ($tables as $table) {
        fwrite($fp, "-- --------------------------------------------------------\n");
        fwrite($fp, "-- Struktur tabel: `{$table}`\n");
        fwrite($fp, "DROP TABLE IF EXISTS `{$table}`;\n");

        $createRow = $db->query("SHOW CREATE TABLE `{$table}`")->fetch();
        fwrite($fp, $createRow['Create Table'] . ";\n\n");

        // Dump data in chunks of 500 rows
        $count = $db->query("SELECT COUNT(*) FROM `{$table}`")->fetchColumn();
        if ($count > 0) {
            fwrite($fp, "-- Data untuk tabel: `{$table}` ({$count} baris)\n");
            $offset = 0;
            $chunkSize = 500;

            while ($offset < $count) {
                $rows = $db->query("SELECT * FROM `{$table}` LIMIT {$chunkSize} OFFSET {$offset}")->fetchAll(PDO::FETCH_ASSOC);
                if (empty($rows)) break;

                $cols = array_keys($rows[0]);
                $colNames = implode('`, `', $cols);

                foreach ($rows as $row) {
                    $values = array_map(function ($val) use ($db) {
                        return is_null($val) ? 'NULL' : $db->quote($val);
                    }, array_values($row));
                    fwrite($fp, "INSERT INTO `{$table}` (`{$colNames}`) VALUES (" . implode(', ', $values) . ");\n");
                }
                $offset += $chunkSize;
            }
            fwrite($fp, "\n");
        }
    }

    fwrite($fp, "SET FOREIGN_KEY_CHECKS=1;\n");
    fclose($fp);

    echo "[✓] Dump SQL selesai: " . basename($sqlFile) . " (" . round(filesize($sqlFile) / 1024, 2) . " KB)\n";

    // 2. Compress to ZIP (SQL dump + uploads directory)
    if (class_exists('ZipArchive')) {
        $zip = new ZipArchive();
        if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            $zip->addFile($sqlFile, basename($sqlFile));

            $uploadsDir = dirname(__DIR__) . '/uploads';
            if (is_dir($uploadsDir)) {
                $files = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($uploadsDir, RecursiveDirectoryIterator::SKIP_DOTS),
                    RecursiveIteratorIterator::LEAVES_ONLY
                );

                foreach ($files as $name => $file) {
                    if (!$file->isDir()) {
                        $filePath = $file->getRealPath();
                        $relativePath = 'uploads/' . substr($filePath, strlen($uploadsDir) + 1);
                        $zip->addFile($filePath, $relativePath);
                    }
                }
            }

            $zip->close();
            // Delete raw sql after successful zip
            @unlink($sqlFile);
            echo "[✓] Arsip ZIP selesai: " . basename($zipFile) . " (" . round(filesize($zipFile) / 1024 / 1024, 2) . " MB)\n";
        }
    }

    // 3. Retention policy: Hapus backup yang lebih lama dari 14 hari
    $retentionDays = 14;
    $now = time();
    $deletedCount = 0;

    foreach (glob("{$backupDir}/*.{zip,sql}", GLOB_BRACE) as $oldBackup) {
        if (($now - filemtime($oldBackup)) > ($retentionDays * 86400)) {
            @unlink($oldBackup);
            $deletedCount++;
        }
    }
    if ($deletedCount > 0) {
        echo "[✓] Kebijakan retensi: {$deletedCount} file backup lama (> {$retentionDays} hari) dibersihkan.\n";
    }

    echo "=== [MGI BACKUP] Sukses 100% ===\n";

} catch (Throwable $e) {
    echo "[!] Kesalahan proses backup: " . $e->getMessage() . "\n";
    exit(1);
}
