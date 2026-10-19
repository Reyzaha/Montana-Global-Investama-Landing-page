<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — API: PUBLIC TEAM MEMBERS
 * Method: GET
 * Returns active management & board members from MySQL database.
 */

require_once __DIR__ . '/../backend/config/db.php';
require_once __DIR__ . '/../backend/helpers/response.php';

try {
    $db = getDB();

    $department = isset($_GET['department']) ? trim($_GET['department']) : '';

    if ($department !== '') {
        $stmt = $db->prepare("
            SELECT id, name, role_title, department, photo_url, bio, linkedin_url, sort_order
            FROM team_members
            WHERE is_active = 1 AND department = ?
            ORDER BY sort_order ASC, id ASC
        ");
        $stmt->execute([$department]);
    } else {
        $stmt = $db->query("
            SELECT id, name, role_title, department, photo_url, bio, linkedin_url, sort_order
            FROM team_members
            WHERE is_active = 1
            ORDER BY sort_order ASC, id ASC
        ");
    }

    $members = $stmt->fetchAll();

    sendJsonResponse($members, 200, 'Data tim manajemen berhasil diambil.');

} catch (Throwable $e) {
    sendJsonError('Gagal memuat tim manajemen: ' . $e->getMessage(), 500);
}
