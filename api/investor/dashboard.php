<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — API: INVESTOR DASHBOARD
 * Actions: GET dashboard data, POST save_bank, POST update_profile, POST change_password
 */

require_once __DIR__ . '/../../backend/config/db.php';
require_once __DIR__ . '/../../backend/helpers/response.php';
require_once __DIR__ . '/../../backend/helpers/auth_helper.php';

$session = requireInvestorAuth();
$investorId = (int)$session['id'];

$action = $_GET['action'] ?? 'get';
$method = $_SERVER['REQUEST_METHOD'];

// Validasi CSRF untuk semua mutasi state (POST)
if ($method === 'POST') {
    if (!validateCsrfToken()) {
        sendJsonError('Validasi token keamanan (CSRF) gagal. Silakan muat ulang halaman.', 403);
    }
}

try {
    $db = getDB();

    // 1. GET: Ambil seluruh data untuk dashboard investor
    if ($action === 'get') {
        // A. Profile
        $stmtP = $db->prepare("
            SELECT i.id, i.account_type, i.email, i.full_name, i.citizenship, i.phone, i.business_activity, i.status, i.created_at,
                   c.business_name, c.legal_entity, c.company_address, c.pic_name, c.pic_position, c.company_phone, c.annual_turnover
            FROM investors i
            LEFT JOIN investor_companies c ON i.id = c.investor_id
            WHERE i.id = ?
        ");
        $stmtP->execute([$investorId]);
        $profile = $stmtP->fetch();

        if (!$profile) {
            sendJsonError('Data investor tidak ditemukan.', 404);
        }

        // B. Bank Account
        $stmtB = $db->prepare("
            SELECT * FROM investor_bank_accounts
            WHERE investor_id = ?
            ORDER BY is_primary DESC, id DESC
            LIMIT 1
        ");
        $stmtB->execute([$investorId]);
        $bankAccount = $stmtB->fetch();

        // C. Portfolios
        $stmtPort = $db->prepare("
            SELECT p.*, pr.title as project_title, pr.category as project_category,
                   pr.image as project_image, pr.status as project_status, pr.lokasi as project_lokasi
            FROM investor_portfolios p
            JOIN projects pr ON p.project_id = pr.id
            WHERE p.investor_id = ?
            ORDER BY p.start_date DESC
        ");
        $stmtPort->execute([$investorId]);
        $rawPortfolios = $stmtPort->fetchAll();

        $portfolios = [];
        $totalInvested = 0;
        $totalPayoutReceived = 0;
        $nextPayoutDates = [];

        foreach ($rawPortfolios as $port) {
            $amt = (float)$port['amount'];
            $payout = (float)$port['payout_received'];
            $totalInvested += $amt;
            $totalPayoutReceived += $payout;

            if (!empty($port['next_payout_date']) && $port['status'] === 'active') {
                $nextPayoutDates[] = $port['next_payout_date'];
            }

            $portfolios[] = [
                'id' => (int)$port['id'],
                'project_id' => $port['project_id'],
                'project_title' => $port['project_title'],
                'project_category' => $port['project_category'],
                'project_image' => $port['project_image'],
                'contract_number' => $port['contract_number'],
                'amount' => $amt,
                'return_rate' => $port['return_rate'],
                'tenor' => $port['tenor'],
                'start_date' => $port['start_date'],
                'end_date' => $port['end_date'],
                'next_payout_date' => $port['next_payout_date'],
                'payout_received' => $payout,
                'allocated_units' => $port['allocated_units'],
                'status' => $port['status']
            ];
        }

        // Sort next payout dates
        sort($nextPayoutDates);
        $earliestNextPayout = !empty($nextPayoutDates) ? $nextPayoutDates[0] : null;

        // D. Campaign Updates
        $stmtCamp = $db->query("
            SELECT cu.*, pr.title as project_title
            FROM campaign_updates cu
            LEFT JOIN projects pr ON cu.project_id = pr.id
            ORDER BY cu.update_date DESC, cu.sort_order ASC
            LIMIT 10
        ");
        $campaignUpdates = $stmtCamp->fetchAll();

        // E. Available Open Projects
        $stmtOpen = $db->query("
            SELECT id, title, category, image, status, funding_collected, funding_target,
                   lokasi, tenor, return_rate, min_investment, remaining_days, asset_backed
            FROM projects
            WHERE status = 'Open'
            ORDER BY sort_order ASC
            LIMIT 4
        ");
        $openProjects = $stmtOpen->fetchAll();

        sendJsonResponse([
            'profile' => [
                'id' => (int)$profile['id'],
                'type' => $profile['account_type'],
                'email' => $profile['email'],
                'fullName' => $profile['account_type'] === 'perusahaan' ? $profile['business_name'] : ($profile['full_name'] ?: explode('@', $profile['email'])[0]),
                'citizenship' => $profile['citizenship'] ?? 'Indonesia (WNI)',
                'phone' => $profile['phone'] ?? '',
                'business_activity' => $profile['business_activity'] ?? '',
                'status' => $profile['status'],
                'registered_at' => $profile['created_at'],
                // Corporate fields
                'business_name' => $profile['business_name'] ?? '',
                'legal_entity' => $profile['legal_entity'] ?? '',
                'company_address' => $profile['company_address'] ?? '',
                'pic_name' => $profile['pic_name'] ?? '',
                'pic_position' => $profile['pic_position'] ?? '',
                'company_phone' => $profile['company_phone'] ?? '',
                'annual_turnover' => $profile['annual_turnover'] ?? ''
            ],
            'metrics' => [
                'total_invested' => $totalInvested,
                'total_payout_received' => $totalPayoutReceived,
                'active_projects_count' => count($portfolios),
                'next_payout_date' => $earliestNextPayout,
                'est_roi_rate' => count($portfolios) > 0 ? '≥30% (p.a.)' : '0%'
            ],
            'portfolios' => $portfolios,
            'bank_account' => $bankAccount ? [
                'id' => (int)$bankAccount['id'],
                'bank_name' => $bankAccount['bank_name'],
                'account_number' => $bankAccount['account_number'],
                'account_holder' => $bankAccount['account_holder'],
                'branch' => $bankAccount['branch'] ?? ''
            ] : null,
            'campaign_updates' => $campaignUpdates,
            'open_projects' => $openProjects,
            'csrf_token' => getCsrfToken(),
            'session_security' => [
                'ip' => $session['ip'] ?? getClientIp(),
                'login_time' => $session['login_time'] ?? date('c'),
                'is_secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            ]
        ]);
    }

    // 2. POST save_bank: Simpan / update rekening bank
    if ($action === 'save_bank') {
        if ($method !== 'POST') sendJsonError('Method harus POST.', 405);
        $input = getJsonInput();
        $bankName = trim($input['bank_name'] ?? '');
        $accNumber = trim($input['account_number'] ?? '');
        $accHolder = trim($input['account_holder'] ?? '');
        $branch = trim($input['branch'] ?? '');

        if (empty($bankName) || empty($accNumber) || empty($accHolder)) {
            sendJsonError('Nama bank, nomor rekening, dan nama pemilik rekening wajib diisi.');
        }

        $stmtCheck = $db->prepare("SELECT id FROM investor_bank_accounts WHERE investor_id = ? LIMIT 1");
        $stmtCheck->execute([$investorId]);
        $existing = $stmtCheck->fetch();

        if ($existing) {
            $stmtUp = $db->prepare("
                UPDATE investor_bank_accounts SET
                    bank_name = ?, account_number = ?, account_holder = ?, branch = ?, updated_at = NOW()
                WHERE id = ? AND investor_id = ?
            ");
            $stmtUp->execute([$bankName, $accNumber, $accHolder, $branch, $existing['id'], $investorId]);
        } else {
            $stmtIns = $db->prepare("
                INSERT INTO investor_bank_accounts (investor_id, bank_name, account_number, account_holder, branch, is_primary)
                VALUES (?, ?, ?, ?, ?, 1)
            ");
            $stmtIns->execute([$investorId, $bankName, $accNumber, $accHolder, $branch]);
        }

        sendJsonResponse(null, 200, 'Data rekening bank pencairan bagi hasil berhasil disimpan.');
    }

    // 3. POST update_profile: Update data profil kontak
    if ($action === 'update_profile') {
        if ($method !== 'POST') sendJsonError('Method harus POST.', 405);
        $input = getJsonInput();

        $fullName = trim($input['full_name'] ?? '');
        $phone = trim($input['phone'] ?? '');
        $businessActivity = trim($input['business_activity'] ?? '');

        if (empty($fullName)) {
            sendJsonError('Nama lengkap wajib diisi.');
        }

        $stmtUp = $db->prepare("UPDATE investors SET full_name = ?, phone = ?, business_activity = ? WHERE id = ?");
        $stmtUp->execute([$fullName, $phone, $businessActivity, $investorId]);

        // If corporate
        if (!empty($input['business_name'])) {
            $stmtComp = $db->prepare("
                UPDATE investor_companies SET
                    business_name = ?, company_address = ?, pic_name = ?, pic_position = ?, company_phone = ?
                WHERE investor_id = ?
            ");
            $stmtComp->execute([
                trim($input['business_name']),
                trim($input['company_address'] ?? ''),
                trim($input['pic_name'] ?? ''),
                trim($input['pic_position'] ?? ''),
                trim($input['company_phone'] ?? $phone),
                $investorId
            ]);
        }

        sendJsonResponse(null, 200, 'Profil investor berhasil diperbarui.');
    }

    // 4. POST change_password: Ganti kata sandi
    if ($action === 'change_password') {
        if ($method !== 'POST') sendJsonError('Method harus POST.', 405);
        $input = getJsonInput();
        $oldPass = (string)($input['old_password'] ?? '');
        $newPass = (string)($input['new_password'] ?? '');

        if (empty($oldPass) || empty($newPass)) {
            sendJsonError('Kata sandi lama dan baru wajib diisi.');
        }

        if (strlen($newPass) < 8) {
            sendJsonError('Kata sandi baru minimal 8 karakter demi keamanan finansial akun Anda.');
        }

        $stmt = $db->prepare("SELECT password_hash FROM investors WHERE id = ?");
        $stmt->execute([$investorId]);
        $currentHash = $stmt->fetchColumn();

        if (!verifyPassword($oldPass, $currentHash)) {
            sendJsonError('Kata sandi lama tidak sesuai.');
        }

        $newHash = hashPassword($newPass);
        $stmtUp = $db->prepare("UPDATE investors SET password_hash = ? WHERE id = ?");
        $stmtUp->execute([$newHash, $investorId]);

        sendJsonResponse(null, 200, 'Kata sandi berhasil diperbarui.');
    }

    sendJsonError('Aksi tidak dikenali.', 400);

} catch (Exception $e) {
    sendJsonException($e, 'Terjadi kesalahan sistem saat memproses data investor.');
}
