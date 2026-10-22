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

    // 5. Action Items (Perlu Tindakan Operasional Segera)
    $stmtActLeads = $db->query("
        SELECT id, full_name, email, account_type, created_at,
               (created_at < (NOW() - INTERVAL 24 HOUR)) as is_over_24h
        FROM leads 
        WHERE status = 'new'
        ORDER BY created_at ASC
        LIMIT 5
    ");
    $actionLeads = $stmtActLeads ? $stmtActLeads->fetchAll() : [];

    $leadsOver24h = (int)$db->query("
        SELECT COUNT(*) FROM leads WHERE status = 'new' AND created_at < (NOW() - INTERVAL 24 HOUR)
    ")->fetchColumn();
    $leadsNewTotal = (int)$db->query("
        SELECT COUNT(*) FROM leads WHERE status = 'new'
    ")->fetchColumn();

    $stmtActInv = $db->query("
        SELECT id, full_name, email, account_type, created_at
        FROM investors
        WHERE status = 'pending_verification'
        ORDER BY created_at ASC
        LIMIT 5
    ");
    $actionInvestors = $stmtActInv ? $stmtActInv->fetchAll() : [];

    $stmtActPay = $db->query("
        SELECT f.id, f.record_number, f.title, f.amount, f.paid_at, f.payment_proof_url,
               i.full_name as investor_name, p.title as project_title
        FROM financial_records f
        JOIN investors i ON f.investor_id = i.id
        JOIN projects p ON f.project_id = p.id
        WHERE f.is_billing = 1 AND f.billing_status = 'paid'
        ORDER BY f.paid_at ASC
        LIMIT 5
    ");
    $actionPayments = $stmtActPay ? $stmtActPay->fetchAll() : [];

    $stmtActOverdue = $db->query("
        SELECT f.id, f.record_number, f.title, f.amount, f.due_date,
               DATEDIFF(CURDATE(), f.due_date) as days_overdue,
               i.full_name as investor_name, p.title as project_title
        FROM financial_records f
        JOIN investors i ON f.investor_id = i.id
        JOIN projects p ON f.project_id = p.id
        WHERE f.is_billing = 1 AND f.billing_status = 'unpaid' AND f.due_date IS NOT NULL AND f.due_date < CURDATE()
        ORDER BY f.due_date ASC
        LIMIT 5
    ");
    $actionOverdue = $stmtActOverdue ? $stmtActOverdue->fetchAll() : [];

    $totalActionRequired = count($actionPayments) + count($actionOverdue) + $leadsOver24h + (int)$invStats['pending_investors'];

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
        'recent_logs' => $recentLogs,
        'action_center' => [
            'total_actions_count' => $totalActionRequired,
            'leads_new_count' => $leadsNewTotal,
            'leads_over_24h_count' => $leadsOver24h,
            'pending_investors_count' => (int)$invStats['pending_investors'],
            'unverified_payments_count' => count($actionPayments),
            'overdue_billings_count' => count($actionOverdue),
            'action_leads' => $actionLeads,
            'action_investors' => $actionInvestors,
            'action_payments' => $actionPayments,
            'action_overdue' => $actionOverdue
        ]
    ]);

} catch (Exception $e) {
    sendJsonError('Gagal memuat statistik dashboard: ' . $e->getMessage(), 500);
}
