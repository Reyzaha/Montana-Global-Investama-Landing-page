<?php
require_once __DIR__ . '/../backend/config/db.php';

try {
    $db = getDB();
    echo "Connected to database.\n";

    // Add nik_paspor column if not exists
    $cols = $db->query("SHOW COLUMNS FROM `investors` LIKE 'nik_paspor'")->fetchAll();
    if (empty($cols)) {
        $db->exec("ALTER TABLE `investors` ADD COLUMN `nik_paspor` VARCHAR(50) NULL AFTER `citizenship`");
        echo "1. Added nik_paspor column.\n";
    } else {
        echo "1. nik_paspor column already exists.\n";
    }

    // Add npwp column if not exists
    $cols = $db->query("SHOW COLUMNS FROM `investors` LIKE 'npwp'")->fetchAll();
    if (empty($cols)) {
        $db->exec("ALTER TABLE `investors` ADD COLUMN `npwp` VARCHAR(50) NULL AFTER `nik_paspor`");
        echo "2. Added npwp column.\n";
    } else {
        echo "2. npwp column already exists.\n";
    }

    echo "Migration completed successfully!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
