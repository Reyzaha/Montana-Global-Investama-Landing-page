<?php
require_once __DIR__ . '/../backend/config/db.php';

try {
    $db = getDB();
    echo "Connected to database.\n";

    // 1. Make password_hash nullable
    $db->exec("ALTER TABLE `investors` MODIFY COLUMN `password_hash` VARCHAR(255) NULL");
    echo "1. Modified password_hash to be nullable.\n";

    // 2. Check and add google_id column
    $cols = $db->query("SHOW COLUMNS FROM `investors` LIKE 'google_id'")->fetchAll();
    if (empty($cols)) {
        $db->exec("ALTER TABLE `investors` ADD COLUMN `google_id` VARCHAR(100) NULL AFTER `password_hash`");
        $db->exec("ALTER TABLE `investors` ADD INDEX `idx_investors_google_id` (`google_id`)");
        echo "2. Added google_id column and index.\n";
    } else {
        echo "2. google_id column already exists.\n";
    }

    // 3. Check and add avatar_url column
    $cols = $db->query("SHOW COLUMNS FROM `investors` LIKE 'avatar_url'")->fetchAll();
    if (empty($cols)) {
        $db->exec("ALTER TABLE `investors` ADD COLUMN `avatar_url` VARCHAR(255) NULL AFTER `phone`");
        echo "3. Added avatar_url column.\n";
    } else {
        echo "3. avatar_url column already exists.\n";
    }

    echo "Migration completed successfully!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
