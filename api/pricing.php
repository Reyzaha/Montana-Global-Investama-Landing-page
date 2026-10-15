<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — API: PUBLIC PRICING & INVESTMENT TIERS
 * Method: GET
 * Returns active pricing tiers from MySQL database.
 */

require_once __DIR__ . '/../backend/config/db.php';
require_once __DIR__ . '/../backend/helpers/response.php';

try {
    $db = getDB();

    $projectId = isset($_GET['project_id']) ? trim($_GET['project_id']) : null;

    if ($projectId) {
        $stmt = $db->prepare("
            SELECT id, project_id, name, badge, nominal, nominal_display,
                   unit_qty, container_qty, porsi_proyek, jaminan_aset, operator_perk,
                   deskripsi, profit_per_siklus, profit_2x_tahunan, profit_3x_tahunan,
                   is_featured, sort_order
            FROM pricing_tiers
            WHERE is_active = 1 AND (project_id = ? OR project_id IS NULL)
            ORDER BY sort_order ASC, id ASC
        ");
        $stmt->execute([$projectId]);
    } else {
        $stmt = $db->query("
            SELECT id, project_id, name, badge, nominal, nominal_display,
                   unit_qty, container_qty, porsi_proyek, jaminan_aset, operator_perk,
                   deskripsi, profit_per_siklus, profit_2x_tahunan, profit_3x_tahunan,
                   is_featured, sort_order
            FROM pricing_tiers
            WHERE is_active = 1
            ORDER BY sort_order ASC, id ASC
        ");
    }

    $tiers = $stmt->fetchAll();
    if (empty($tiers) && empty($projectId)) {
        $pStmt = $db->prepare("
            INSERT INTO pricing_tiers (id, project_id, name, badge, nominal, nominal_display, unit_qty, container_qty, porsi_proyek, jaminan_aset, operator_perk, deskripsi, profit_per_siklus, profit_2x_tahunan, profit_3x_tahunan, is_featured, sort_order, is_active)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
        ");
        $defaultTiers = [
            [1, 'proj-kebumen', 'Paket Partisipasi Perunggu', 'TIER BRONZE', 50000000.00, 'Rp 50.000.000', 1, 0, '0.33% Nilai Sindikasi', 'Aset Fisik Bersama', 'Laporan Bulanan Digital', 'Paket awal kemitraan modal riil pada sentral pool Kebumen dengan agunan aset fisik terdaftar dan bagi hasil berkala.', 'Rp 3.500.000 (7,0%)', 'Rp 7.000.000 (14,0%)', 'Rp 10.500.000 (21,0%)', 0, 1],
            [2, 'proj-kebumen', 'Paket Partisipasi Perak', 'PILIHAN POPULER', 100000000.00, 'Rp 100.000.000', 2, 0, '0.67% Nilai Sindikasi', 'Aset Fisik Terikat Unit', 'Akses Monitoring GPS Khusus', 'Paket menengah dengan alokasi armada spesifik, proteksi asuransi komprehensif, dan pembagian dividen langsung ke rekening escrow.', 'Rp 7.200.000 (7,2%)', 'Rp 14.400.000 (14,4%)', 'Rp 21.600.000 (21,6%)', 1, 2],
            [3, 'proj-kebumen', 'Paket Partisipasi Emas', 'TIER GOLD', 250000000.00, 'Rp 250.000.000', 5, 0, '1.67% Nilai Sindikasi', 'Hak Preferen Aset Fisik', 'Kunjungan Lapangan & VIP Meet', 'Kemitraan institusional / perorangan utama dengan hak preferen perlindungan modal, bagi hasil maksimal, serta pelaporan keuangan triwulanan audited.', 'Rp 18.500.000 (7,4%)', 'Rp 37.000.000 (14,8%)', 'Rp 55.500.000 (22,2%)', 0, 3],
            [4, 'proj-denpasar', 'Paket Eksekutif Sindikasi Denpasar', 'BALI EXPANSION', 500000000.00, 'Rp 500.000.000', 10, 0, '3.33% Nilai Sindikasi', 'Aset Penuh 1 Unit Excavator', 'VIP Briefing & Audit Access', 'Partisipasi skala penuh untuk ekspansi armada pendukung pariwisata dan infrastruktur Denpasar, Bali.', 'Rp 40.000.000 (8,0%)', 'Rp 80.000.000 (16,0%)', 'Rp 120.000.000 (24,0%)', 0, 4]
        ];
        foreach ($defaultTiers as $t) {
            $pStmt->execute($t);
        }
        $stmt = $db->query("SELECT id, project_id, name, badge, nominal, nominal_display, unit_qty, container_qty, porsi_proyek, jaminan_aset, operator_perk, deskripsi, profit_per_siklus, profit_2x_tahunan, profit_3x_tahunan, is_featured, sort_order FROM pricing_tiers WHERE is_active = 1 ORDER BY sort_order ASC, id ASC");
        $tiers = $stmt->fetchAll();
    }

    sendJsonResponse($tiers, 200, 'Data paket investasi berhasil diambil.');

} catch (Throwable $e) {
    sendJsonError('Gagal memuat paket investasi: ' . $e->getMessage(), 500);
}
