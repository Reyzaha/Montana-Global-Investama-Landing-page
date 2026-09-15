<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — API: ADMIN DASHBOARD STATS
 * Method: GET
 */

require_once __DIR__ . '/../../backend/config/db.php';
require_once __DIR__ . '/../../backend/helpers/response.php';
require_once __DIR__ . '/../../backend/helpers/auth_helper.php';

requireAdminAuth();

try {
    $db = getDB();

    // 1. Projects stats
    $stmtP = $db->query("
        SELECT 
            COUNT(*) as total_projects,
            SUM(CASE WHEN status = 'Open' THEN 1 ELSE 0 END) as open_projects,
            SUM(CASE WHEN status = 'Fully Funded' THEN 1 ELSE 0 END) as funded_projects,
            COALESCE(SUM(funding_target), 0) as total_target,
            COALESCE(SUM(funding_collected), 0) as total_collected
        FROM projects
    ");
    $projStats = $stmtP->fetch();

    // 2. Investors stats
    $stmtI = $db->query("
        SELECT 
            COUNT(*) as total_investors,
            SUM(CASE WHEN account_type = 'perorangan' THEN 1 ELSE 0 END) as total_individual,
            SUM(CASE WHEN account_type = 'perusahaan' THEN 1 ELSE 0 END) as total_corporate,
            SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_investors,
            SUM(CASE WHEN status = 'pending_verification' THEN 1 ELSE 0 END) as pending_investors
        FROM investors
    ");
    $invStats = $stmtI->fetch();

    // 3. Recent projects
    $stmtRP = $db->query("
        SELECT id, title, category, status, funding_collected, funding_target, created_at
        FROM projects
        ORDER BY created_at DESC
        LIMIT 5
    ");
    $recentProjects = $stmtRP->fetchAll();

    // 4. Recent audit logs
    $stmtLogs = $db->query("
        SELECT l.*, u.username as admin_username
        FROM admin_logs l
        LEFT JOIN admin_users u ON l.admin_id = u.id
        ORDER BY l.created_at DESC
        LIMIT 10
    ");
    $recentLogs = $stmtLogs->fetchAll();

    sendJsonResponse([
        'metrics' => [
            'total_projects' => (int)$projStats['total_projects'],
            'open_projects' => (int)$projStats['open_projects'],
            'funded_projects' => (int)$projStats['funded_projects'],
            'total_target' => (float)$projStats['total_target'],
            'total_collected' => (float)$projStats['total_collected'],
            'funding_percentage' => $projStats['total_target'] > 0 
                ? round(($projStats['total_collected'] / $projStats['total_target']) * 100, 1) 
                : 0,
            'total_investors' => (int)$invStats['total_investors'],
            'total_individual' => (int)$invStats['total_individual'],
            'total_corporate' => (int)$invStats['total_corporate'],
            'active_investors' => (int)$invStats['active_investors'],
            'pending_investors' => (int)$invStats['pending_investors']
        ],
        'recent_projects' => $recentProjects,
        'recent_logs' => $recentLogs
    ]);

} catch (Exception $e) {
    sendJsonError('Gagal memuat statistik dashboard: ' . $e->getMessage(), 500);
}
