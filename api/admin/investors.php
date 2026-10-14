<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — API: ADMIN INVESTORS MANAGEMENT
 * Methods: GET, PUT
 */

require_once __DIR__ . '/../../backend/config/db.php';
require_once __DIR__ . '/../../backend/helpers/response.php';
require_once __DIR__ . '/../../backend/helpers/auth_helper.php';
require_once __DIR__ . '/../../backend/helpers/notification_helper.php';

$admin = requireAdminAuth();
$method = $_SERVER['REQUEST_METHOD'];
$input = getJsonInput();

// Handle method spoofing if needed
if (isset($input['_method'])) {
    $method = strtoupper($input['_method']);
} elseif (isset($_GET['_method'])) {
    $method = strtoupper($_GET['_method']);
}

try {
    $db = getDB();

    // 1. GET: Ambil daftar investor dengan relasi perusahaan & portofolio proyek eksklusif
    if ($method === 'GET') {
        $type = $_GET['type'] ?? null;
        $status = $_GET['status'] ?? null;
        $search = trim($_GET['search'] ?? '');

        $sql = "
            SELECT i.id, i.account_type, i.email, i.full_name, i.citizenship, i.phone, i.business_activity,
                   i.status, i.notes, i.created_at, i.updated_at,
                   c.business_name, c.legal_entity, c.company_address, c.pic_name,
                   c.pic_position, c.company_phone, c.annual_turnover,
                   p.id AS portfolio_id, p.project_id, p.contract_number, p.amount AS portfolio_amount,
                   p.return_rate AS portfolio_return_rate, p.tenor AS portfolio_tenor,
                   p.status AS portfolio_status, p.start_date, p.end_date,
                   pr.title AS project_title, pr.status AS project_status
            FROM investors i
            LEFT JOIN investor_companies c ON i.id = c.investor_id
            LEFT JOIN investor_portfolios p ON i.id = p.investor_id AND p.status IN ('active', 'pending_allocation')
            LEFT JOIN projects pr ON p.project_id = pr.id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($type) && in_array($type, ['perorangan', 'perusahaan'])) {
            $sql .= " AND i.account_type = ?";
            $params[] = $type;
        }

        if (!empty($status) && in_array($status, ['active', 'pending_verification', 'suspended'])) {
            $sql .= " AND i.status = ?";
            $params[] = $status;
        }

        if (!empty($search)) {
            $sql .= " AND (i.email LIKE ? OR i.full_name LIKE ? OR c.business_name LIKE ? OR c.pic_name LIKE ? OR pr.title LIKE ?)";
            $term = "%{$search}%";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $sql .= " ORDER BY i.created_at DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $investors = $stmt->fetchAll();

        // Ambil juga daftar proyek yang tersedia (Open atau belum ada investor)
        $projectsStmt = $db->query("
            SELECT pr.id, pr.title, pr.funding_target, pr.funding_collected, pr.status, pr.tenor, pr.return_rate,
                   (SELECT COUNT(*) FROM investor_portfolios ip WHERE ip.project_id = pr.id AND ip.status = 'active') as active_investor_count
            FROM projects pr
            ORDER BY pr.sort_order ASC, pr.title ASC
        ");
        $availableProjects = $projectsStmt ? $projectsStmt->fetchAll() : [];

        sendJsonResponse([
            'investors' => $investors,
            'available_projects' => $availableProjects
        ]);
    }

    // 2. POST: Assign Investor ke Proyek Eksklusif (1 Investor = 1 Proyek)
    if ($method === 'POST') {
        $action = trim($_GET['action'] ?? ($input['action'] ?? 'assign_project'));

        if ($action === 'assign_project') {
            $investorId = (int)($input['investor_id'] ?? 0);
            $projectId = trim($input['project_id'] ?? '');
            $amount = (float)preg_replace('/[^0-9.]/', '', (string)($input['amount'] ?? 500000000));
            $contractNumber = trim($input['contract_number'] ?? '');
            $returnRate = trim($input['return_rate'] ?? '≥30% (p.a.)');
            $tenor = trim($input['tenor'] ?? '36 Bulan');
            $startDate = trim($input['start_date'] ?? date('Y-m-d'));
            $endDate = trim($input['end_date'] ?? date('Y-m-d', strtotime('+3 years')));
            $createBilling = !empty($input['create_billing']);

            if (!$investorId) {
                sendJsonError('Investor wajib dipilih.');
            }
            if (empty($projectId)) {
                sendJsonError('Proyek kemitraan wajib dipilih.');
            }
            if ($amount <= 0) {
                sendJsonError('Nominal penempatan modal harus lebih dari 0.');
            }

            // Validasi Investor
            $stmtInv = $db->prepare("SELECT id, email, full_name, status FROM investors WHERE id = ?");
            $stmtInv->execute([$investorId]);
            $inv = $stmtInv->fetch();
            if (!$inv) {
                sendJsonError('Investor tidak ditemukan.', 404);
            }

            // Validasi Proyek
            $stmtProj = $db->prepare("SELECT id, title, funding_target, funding_collected, status FROM projects WHERE id = ?");
            $stmtProj->execute([$projectId]);
            $proj = $stmtProj->fetch();
            if (!$proj) {
                sendJsonError('Proyek tidak ditemukan.', 404);
            }

            // Aturan 1 Investor = 1 Proyek: Cek apakah proyek ini sudah ditautkan ke investor lain
            $stmtCheckOther = $db->prepare("SELECT id, investor_id FROM investor_portfolios WHERE project_id = ? AND investor_id != ? AND status = 'active' LIMIT 1");
            $stmtCheckOther->execute([$projectId, $investorId]);
            $otherPort = $stmtCheckOther->fetch();
            if ($otherPort) {
                sendJsonError("Proyek '{$proj['title']}' sudah dipegang secara eksklusif oleh Investor ID #{$otherPort['investor_id']}. Sistem 1-on-1 melarang lebih dari 1 investor pada proyek yang sama.", 400);
            }

            // Auto-generate contract number jika kosong
            if (empty($contractNumber)) {
                $contractNumber = 'MGI-SPK/' . date('Y') . '/' . strtoupper(substr($projectId, 0, 8)) . '-' . str_pad($investorId, 4, '0', STR_PAD_LEFT);
            }

            // Cek apakah investor sudah memiliki portfolio di proyek ini
            $stmtCheckOwn = $db->prepare("SELECT id FROM investor_portfolios WHERE investor_id = ? AND project_id = ?");
            $stmtCheckOwn->execute([$investorId, $projectId]);
            $existingPort = $stmtCheckOwn->fetch();

            if ($existingPort) {
                $stmtUpPort = $db->prepare("
                    UPDATE investor_portfolios 
                    SET contract_number = ?, amount = ?, return_rate = ?, tenor = ?, start_date = ?, end_date = ?, status = 'active', updated_at = NOW()
                    WHERE id = ?
                ");
                $stmtUpPort->execute([$contractNumber, $amount, $returnRate, $tenor, $startDate, $endDate, $existingPort['id']]);
                $portfolioId = $existingPort['id'];
            } else {
                $stmtInsPort = $db->prepare("
                    INSERT INTO investor_portfolios 
                    (investor_id, project_id, contract_number, amount, return_rate, tenor, start_date, end_date, next_payout_date, payout_received, allocated_units, status)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0, 'Alokasi Unit Bertahap', 'active')
                ");
                $nextPayout = date('Y-m-d', strtotime('+3 months'));
                $stmtInsPort->execute([$investorId, $projectId, $contractNumber, $amount, $returnRate, $tenor, $startDate, $endDate, $nextPayout]);
                $portfolioId = $db->lastInsertId();
            }

            // Update status proyek menjadi Fully Funded untuk mengunci eksklusivitas
            $stmtUpProj = $db->prepare("UPDATE projects SET status = 'Fully Funded', funding_collected = ? WHERE id = ?");
            $stmtUpProj->execute([$amount, $projectId]);

            // Jika admin meminta pembuatan billing deposit awal otomatis
            $billingId = null;
            if ($createBilling) {
                $billingRecordNo = 'BILL-' . date('Ymd') . '-' . rand(100, 999);
                $dueDate = date('Y-m-d', strtotime('+7 days'));
                
                $stmtBill = $db->prepare("
                    INSERT INTO financial_records 
                    (record_number, investor_id, project_id, module, category, title, description, amount, flow_type, status, is_billing, billing_status, transaction_date, due_date)
                    VALUES (?, ?, ?, 'neraca', 'Penyetoran Modal', 'Penempatan Modal Awal Investasi', 'Tagihan penempatan modal eksklusif sesuai Surat Perjanjian Kerjasama (SPK)', ?, 'in', 'sent', 1, 'sent', ?, ?)
                ");
                $stmtBill->execute([$billingRecordNo, $investorId, $projectId, $amount, $startDate, $dueDate]);
                $billingId = $db->lastInsertId();

                // Notifikasi Billing ke Investor
                createNotification(
                    $investorId,
                    $projectId,
                    'billing',
                    'Tagihan Penempatan Modal Baru Diterbitkan',
                    "Admin telah menerbitkan tagihan penempatan modal awal sebesar Rp " . number_format($amount, 0, ',', '.') . " untuk {$proj['title']}. Nomor Billing: {$billingRecordNo}.",
                    "investor-dashboard.html?tab=billing"
                );
            }

            // Notifikasi Kemitraan Proyek Aktif ke Investor
            createNotification(
                $investorId,
                $projectId,
                'portfolio',
                'Kemitraan Proyek Resmi Aktif',
                "Selamat! Anda telah resmi ditautkan sebagai investor tunggal untuk proyek '{$proj['title']}'. Modul transparansi operasional & pembukuan kini telah aktif.",
                "investor-dashboard.html?project_id={$projectId}"
            );

            logAdminActivity('assign_project', 'investor_portfolios', (string)$portfolioId, "Admin assigned investor #{$investorId} ({$inv['email']}) to project {$projectId} with amount Rp " . number_format($amount, 0, ',', '.'));

            sendJsonResponse([
                'portfolio_id' => $portfolioId,
                'billing_id' => $billingId,
                'project_id' => $projectId,
                'contract_number' => $contractNumber,
                'amount' => $amount
            ], 201, "Investor berhasil ditautkan secara eksklusif ke proyek {$proj['title']}.");
        }
    }

    // 3. PUT: Update Status Investor atau Catatan
    if ($method === 'PUT') {
        $id = (int)($input['id'] ?? ($_GET['id'] ?? 0));
        $status = $input['status'] ?? null;
        $notes = $input['notes'] ?? null;

        if (!$id) {
            sendJsonError('ID Investor wajib disertakan.');
        }

        $stmtCheck = $db->prepare("SELECT id, email, full_name, status FROM investors WHERE id = ?");
        $stmtCheck->execute([$id]);
        $inv = $stmtCheck->fetch();
        if (!$inv) {
            sendJsonError('Investor tidak ditemukan.', 404);
        }

        $allowedStatuses = ['active', 'pending_verification', 'suspended'];
        $newStatus = ($status && in_array($status, $allowedStatuses)) ? $status : $inv['status'];

        $stmtUp = $db->prepare("UPDATE investors SET status = ?, notes = ? WHERE id = ?");
        $stmtUp->execute([$newStatus, $notes, $id]);

        // Kirim notifikasi jika status diubah menjadi active
        if ($newStatus === 'active' && $inv['status'] !== 'active') {
            createNotification(
                $id,
                null,
                'status_update',
                'Akun Anda Telah Diverifikasi & Aktif',
                'Selamat! Akun investor Anda telah diverifikasi oleh tim PT Montana Global Investama. Anda sekarang dapat memilih dan mendanai proyek kemitraan.',
                'investor-dashboard.html'
            );
        }

        logAdminActivity('update_status', 'investors', (string)$id, "Admin changed investor {$inv['email']} status to {$newStatus}");

        sendJsonResponse([
            'id' => $id,
            'status' => $newStatus
        ], 200, "Status investor {$inv['email']} berhasil diperbarui menjadi '{$newStatus}'.");
    }

    sendJsonError('Method tidak didukung.', 405);

} catch (Exception $e) {
    sendJsonError('Terjadi kesalahan operasi investor: ' . $e->getMessage(), 500);
}
