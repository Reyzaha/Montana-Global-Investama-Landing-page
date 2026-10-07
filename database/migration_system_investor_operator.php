<?php
/**
 * Migration: Setup tables for Project Inventory and Project Documents
 * Also update financial_records module enum to include 'biaya'
 */
require_once __DIR__ . '/../backend/config/db.php';

try {
    $db = getDB();
    echo "Connected to database.\n";

    // 1. Update financial_records module enum if needed
    $db->exec("
        ALTER TABLE `financial_records` 
        MODIFY COLUMN `module` ENUM('neraca', 'labarugi', 'pembelian', 'penjualan', 'biaya') NOT NULL
    ");
    echo "1. Updated financial_records module enum.\n";

    // 2. Create project_inventory table
    $db->exec("
        CREATE TABLE IF NOT EXISTS `project_inventory` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `project_id` VARCHAR(50) NOT NULL,
            `item_code` VARCHAR(100) NOT NULL,
            `item_name` VARCHAR(255) NOT NULL,
            `category` VARCHAR(100) NOT NULL DEFAULT 'Alat Berat',
            `serial_number` VARCHAR(100) NULL,
            `quantity` INT NOT NULL DEFAULT 1,
            `unit_cost` DECIMAL(18,2) NOT NULL DEFAULT 0,
            `total_value` DECIMAL(18,2) NOT NULL DEFAULT 0,
            `condition_status` VARCHAR(50) NOT NULL DEFAULT 'Grade A (Prima)',
            `operational_status` VARCHAR(50) NOT NULL DEFAULT 'Aktif Beroperasi',
            `location` VARCHAR(255) NOT NULL DEFAULT 'Pool Narogong & Workshop Kebumen',
            `smh_hours` INT NOT NULL DEFAULT 0,
            `last_inspection_date` DATE NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX `idx_proj_inv_project` (`project_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "2. Created/verified project_inventory table.\n";

    // Seed sample inventory for proj-jkt-jabar if empty
    $countInv = $db->query("SELECT COUNT(*) FROM `project_inventory` WHERE `project_id` = 'proj-jkt-jabar'")->fetchColumn();
    if ($countInv == 0) {
        $stmtIns = $db->prepare("
            INSERT INTO `project_inventory` 
            (`project_id`, `item_code`, `item_name`, `category`, `serial_number`, `quantity`, `unit_cost`, `total_value`, `condition_status`, `operational_status`, `location`, `smh_hours`, `last_inspection_date`)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmtIns->execute([
            'proj-jkt-jabar', 'EXC-AB-138-01', 'Unit Alat Berat CBU Japan Grade A', 'Alat Berat', 'AB-882910-JP', 1, 1400000000, 1400000000, 'Grade A (Prima)', 'Aktif Beroperasi', 'Sub-Seksi 4 Toll Road Bekasi Timur', 1250, '2026-09-20'
        ]);
        $stmtIns->execute([
            'proj-jkt-jabar', 'EXC-AB-138-02', 'Unit Alat Berat CBU Japan Grade A', 'Alat Berat', 'AB-882911-JP', 1, 1400000000, 1400000000, 'Grade A (Prima)', 'Aktif Beroperasi', 'Narogong Limestone Zone Jabar', 1168, '2026-09-22'
        ]);
        $stmtIns->execute([
            'proj-jkt-jabar', 'ATT-BRK-001', 'Hydraulic Breaker Kit Alat Berat Grade A', 'Attachment', 'BRK-9901-JP', 2, 175000000, 350000000, 'Grade A (Prima)', 'Standby Workshop', 'Sentral Workshop Pool Kebumen', 0, '2026-09-18'
        ]);
        echo "   - Seeded initial inventory for proj-jkt-jabar.\n";
    }

    // 3. Create project_documents table
    $db->exec("
        CREATE TABLE IF NOT EXISTS `project_documents` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `project_id` VARCHAR(50) NOT NULL,
            `investor_id` INT NULL,
            `doc_number` VARCHAR(100) NOT NULL,
            `doc_title` VARCHAR(255) NOT NULL,
            `doc_type` VARCHAR(50) NOT NULL DEFAULT 'laporan',
            `file_path` VARCHAR(255) NULL,
            `file_size` VARCHAR(50) NOT NULL DEFAULT '1.4 MB',
            `published_by` VARCHAR(100) NOT NULL DEFAULT 'Operator MGI',
            `status` ENUM('draft', 'published') NOT NULL DEFAULT 'published',
            `published_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX `idx_proj_docs_project` (`project_id`),
            INDEX `idx_proj_docs_investor` (`investor_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "3. Created/verified project_documents table.\n";

    // Seed sample documents for proj-jkt-jabar if empty
    $countDocs = $db->query("SELECT COUNT(*) FROM `project_documents` WHERE `project_id` = 'proj-jkt-jabar'")->fetchColumn();
    if ($countDocs == 0) {
        $stmtDoc = $db->prepare("
            INSERT INTO `project_documents` 
            (`project_id`, `investor_id`, `doc_number`, `doc_title`, `doc_type`, `file_path`, `file_size`, `published_by`, `status`, `published_at`)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmtDoc->execute([
            'proj-jkt-jabar', 1, 'DOC-RAB-2026-001', 'Proposal Proyek & Rencana Anggaran Biaya (RAB) Unit Alat Berat', 'pembelian', 'documents/proj-jkt-jabar/proposal proyek-rab-alat-berat.pdf', '2.8 MB', 'Operator MGI', 'published', '2026-01-20 10:00:00'
        ]);
        $stmtDoc->execute([
            'proj-jkt-jabar', 1, 'DOC-CBU-2026-002', 'Sertifikat Kepemilikan & Dokumen Bea Cukai CBU Jepang (Form CBU)', 'inventory', 'documents/proj-jkt-jabar/dokumen-cbu-bea-cukai.pdf', '3.4 MB', 'Operator MGI', 'published', '2026-02-12 14:30:00'
        ]);
        $stmtDoc->execute([
            'proj-jkt-jabar', 1, 'DOC-BILL-2026-001', 'Faktur Alokasi Dana Pembelian 2 Unit Alat Berat via MSI & Biaya Impor MIU', 'billing', 'documents/proj-jkt-jabar/faktur-alokasi-dana-msi-miu.pdf', '1.2 MB', 'Operator MGI', 'published', '2026-02-15 09:15:00'
        ]);
        $stmtDoc->execute([
            'proj-jkt-jabar', 1, 'DOC-REP-2026-Q2', 'Buku Laporan Operasional Kuartal II 2026 (SMH, Penjualan, Laba Rugi)', 'laporan', 'documents/proj-jkt-jabar/laporan-operasional-q2-2026.pdf', '4.1 MB', 'Operator MGI', 'published', '2026-07-20 16:00:00'
        ]);
        echo "   - Seeded initial documents for proj-jkt-jabar.\n";
    }

    echo "Migration completed successfully!\n";
} catch (Exception $e) {
    echo "Migration Error: " . $e->getMessage() . "\n";
    exit(1);
}
