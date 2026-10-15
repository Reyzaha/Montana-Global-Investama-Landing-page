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
    sendJsonResponse($tiers, 200, 'Data paket investasi berhasil diambil.');

} catch (Throwable $e) {
    sendJsonError('Gagal memuat paket investasi: ' . $e->getMessage(), 500);
}
