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
    if (empty($members) && empty($department)) {
        $tmStmt = $db->prepare("
            INSERT INTO team_members (id, name, role_title, department, photo_url, bio, linkedin_url, sort_order, is_active)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)
        ");
        $defaultMembers = [
            [1, 'Reyhan Siregar, B.Eng., MBA', 'Chief Executive Officer (CEO)', 'Direksi', 'assets/img/team-ceo.jpg', 'Berpengalaman lebih dari 15 tahun dalam alokasi investasi modal, sindikasi permodalan infrastruktur, dan tata kelola korporasi.', 'https://linkedin.com', 1],
            [2, 'Faris Santoso, S.T., M.M.', 'Chief Operating Officer (COO)', 'Operasional', 'assets/img/team-coo.jpg', 'Spesialis manajemen alat berat dan efisiensi operasional lapangan dengan rekam jejak memimpin pemeliharaan lebih dari 100+ armada proyek riil.', 'https://linkedin.com', 2],
            [3, 'Maya Kartika, S.E., Ak., CA', 'Chief Financial Officer (CFO)', 'Keuangan', 'assets/img/team-cfo.jpg', 'Pakar akuntansi keuangan, kepatuhan audit TARIF, serta pemodelan dividen berkala bagi pemodal institusi dan perorangan.', 'https://linkedin.com', 3]
        ];
        foreach ($defaultMembers as $m) {
            $tmStmt->execute($m);
        }
        $stmt = $db->query("SELECT id, name, role_title, department, photo_url, bio, linkedin_url, sort_order FROM team_members WHERE is_active = 1 ORDER BY sort_order ASC, id ASC");
        $members = $stmt->fetchAll();
    }

    sendJsonResponse($members, 200, 'Data tim manajemen berhasil diambil.');

} catch (Throwable $e) {
    sendJsonError('Gagal memuat tim manajemen: ' . $e->getMessage(), 500);
}
