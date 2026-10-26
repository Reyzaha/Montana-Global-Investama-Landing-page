<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — API: INVESTOR DASHBOARD
 * Actions: GET dashboard data, POST save_bank, POST update_profile, POST change_password
 */

require_once __DIR__ . '/../../backend/config/db.php';
require_once __DIR__ . '/../../backend/helpers/response.php';
require_once __DIR__ . '/../../backend/helpers/auth_helper.php';
require_once __DIR__ . '/../../backend/helpers/mail_helper.php';
require_once __DIR__ . '/../../backend/helpers/lead_helper.php';

$action = $_GET['action'] ?? 'get';
$method = $_SERVER['REQUEST_METHOD'];

const CURRENT_PRIVACY_POLICY_VERSION = '1.0';

function isInvestorBiodataComplete(?array $user): bool {
    if (!$user) return false;
    $email = trim($user['email'] ?? '');
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    $type = $user['account_type'] ?? 'perorangan';
    if ($type === 'perusahaan') {
        $bName = trim($user['business_name'] ?? '');
        $picName = trim($user['pic_name'] ?? '');
        $phone = trim($user['company_phone'] ?? ($user['phone'] ?? ''));
        $phoneDigits = preg_replace('/\D/', '', $phone);
        $address = trim($user['company_address'] ?? '');
        $bAct = trim($user['business_activity'] ?? '');
        return !empty($bName) && !empty($picName) && !empty($phone) && $phone !== '-' && strlen($phoneDigits) >= 8
            && !empty($address) && $address !== 'Alamat operasional belum dilengkapi'
            && !empty($bAct);
    } else {
        $fullName = trim($user['full_name'] ?? '');
        $phone = trim($user['phone'] ?? '');
        $phoneDigits = preg_replace('/\D/', '', $phone);
        $bAct = trim($user['business_activity'] ?? '');
        $citizenship = trim($user['citizenship'] ?? '');
        return !empty($fullName) && !empty($phone) && $phone !== '-' && strlen($phoneDigits) >= 8
            && !empty($bAct) && !empty($citizenship);
    }
}

function isInvestorPrivacyAccepted(?array $user): bool {
    if (!$user) return false;
    $accepted = !empty($user['privacy_policy_accepted']);
    $version = trim((string)($user['privacy_policy_version'] ?? ''));
    return $accepted && ($version === CURRENT_PRIVACY_POLICY_VERSION);
}

$session = getInvestorSession();
if (!$session) {
    sendJsonError('Sesi investor belum aktif atau telah kedaluwarsa. Silakan masuk terlebih dahulu.', 401);
}
$investorId = (int)$session['id'];

// Validasi keamanan untuk mutasi state (POST)
if ($method === 'POST') {
    if (!validateCsrfToken() && !$session) {
        sendJsonError('Validasi token keamanan (CSRF) gagal. Silakan muat ulang halaman.', 403);
    }
}

function sendStaticInvestorDashboardFallback(?array $session): void {
    $invId = $session ? (int)$session['id'] : 1;
    $email = $session ? ($session['email'] ?? 'investor@mgi.co.id') : 'investor@mgi.co.id';
    $name = $session ? ($session['name'] ?? 'Investor MGI') : 'Investor MGI';
    $type = $session ? ($session['type'] ?? 'individual') : 'individual';

    $fallbackTotal = 0.0;
    $fallbackCount = 0;
    $fallbackNextPayout = null;
    $fallbackPortfolios = [];

    try {
        $db = getDB();
        $stmtF = $db->prepare("
            SELECT p.*, pr.title as project_title, pr.category as project_category,
                   pr.image as project_image, pr.status as project_status, pr.lokasi as project_lokasi
            FROM investor_portfolios p
            LEFT JOIN projects pr ON p.project_id = pr.id
            WHERE p.investor_id = ?
            ORDER BY p.start_date DESC
        ");
        $stmtF->execute([$invId]);
        $rows = $stmtF->fetchAll();
        foreach ($rows as $r) {
            $amt = (float)($r['amount'] ?? 0);
            $fallbackTotal += $amt;
            if (($r['status'] ?? 'active') === 'active') {
                $fallbackCount++;
            }
            if (!empty($r['next_payout_date']) && !$fallbackNextPayout) {
                $fallbackNextPayout = $r['next_payout_date'];
            }
            $fallbackPortfolios[] = [
                'id' => (int)$r['id'],
                'project_id' => $r['project_id'],
                'project_title' => $r['project_title'] ?: 'Proyek Penempatan Modal',
                'project_category' => $r['project_category'] ?: 'Alat Berat',
                'project_image' => $r['project_image'] ?: 'assets/img/1.png',
                'contract_number' => $r['contract_number'],
                'amount' => $amt,
                'return_rate' => $r['return_rate'],
                'tenor' => $r['tenor'],
                'start_date' => $r['start_date'],
                'end_date' => $r['end_date'],
                'next_payout_date' => $r['next_payout_date'],
                'payout_received' => (float)($r['payout_received'] ?? 0),
                'allocated_units' => $r['allocated_units'],
                'status' => $r['status']
            ];
        }
    } catch (Throwable $e) {
        error_log("Static dashboard fallback DB warning: " . $e->getMessage());
    }

    sendJsonResponse([
        'profile' => [
            'id' => $invId,
            'type' => $type,
            'account_type' => $type,
            'email' => $email,
            'fullName' => $name,
            'full_name' => $name,
            'citizenship' => 'Indonesia (WNI)',
            'phone' => '',
            'business_activity' => '',
            'businessActivity' => '',
            'nik_paspor' => '',
            'npwp' => '',
            'has_password' => true,
            'auth_provider' => 'email',
            'created_at' => date('Y-m-d')
        ],
        'bankAccount' => null,
        'summary' => [
            'totalInvested' => $fallbackTotal,
            'total_investasi' => $fallbackTotal,
            'totalPayoutReceived' => 0,
            'totalNetProfit' => 0,
            'activeProjectsCount' => $fallbackCount,
            'nextPayoutDate' => $fallbackNextPayout,
            'totalUnitsAllocated' => 0,
            'totalPurchasesMIU' => 0,
            'cashEscrowBalance' => 0
        ],
        'metrics' => [
            'total_invested' => $fallbackTotal,
            'total_payout_received' => 0,
            'total_purchases_miu' => 0,
            'active_projects_count' => $fallbackCount,
            'next_payout_date' => $fallbackNextPayout,
            'est_roi_rate' => $fallbackCount > 0 ? '≥32% (p.a.)' : '0%'
        ],
        'portfolios' => $fallbackPortfolios,
        'billings' => [],
        'notifications' => [
            [
                'id' => 1,
                'title' => 'Selamat Datang di Portal Investor MGI',
                'message' => 'Akun investor Anda telah aktif. Silakan jelajahi katalog proyek atau tinjau acuan RAB.',
                'type' => 'portfolio',
                'is_read' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ]
        ],
        'unread_notifications_count' => 0,
        'csrfToken' => getCsrfToken()
    ]);
}

try {
    $db = getDB();

    // 1. GET: Ambil seluruh data untuk dashboard investor
    if ($action === 'get') {
        // A. Profile
        $stmtP = $db->prepare("
            SELECT i.id, i.account_type, i.email, i.password_hash, i.google_id, i.avatar_url, i.full_name, i.citizenship, i.phone, i.business_activity, i.nik_paspor, i.npwp, i.status, i.created_at,
                   i.privacy_policy_accepted, i.privacy_policy_accepted_at, i.privacy_policy_version,
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
            LEFT JOIN projects pr ON p.project_id = pr.id
            WHERE p.investor_id = ?
            ORDER BY p.start_date DESC
        ");
        $stmtPort->execute([$investorId]);
        $rawPortfolios = $stmtPort->fetchAll() ?: [];

        $portfolios = [];
        $totalInvested = 0;
        $totalPayoutReceived = 0;
        $nextPayoutDates = [];

        foreach ($rawPortfolios as $port) {
            $amt = (float)($port['amount'] ?? 0);
            $payout = (float)($port['payout_received'] ?? 0);
            $totalInvested += $amt;
            $totalPayoutReceived += $payout;

            if (!empty($port['next_payout_date']) && ($port['status'] ?? 'active') === 'active') {
                $nextPayoutDates[] = $port['next_payout_date'];
            }

            $portfolios[] = [
                'id' => (int)$port['id'],
                'project_id' => $port['project_id'],
                'project_title' => $port['project_title'] ?: 'Proyek Penempatan Modal',
                'project_category' => $port['project_category'] ?: 'Alat Berat',
                'project_image' => $port['project_image'] ?: 'assets/img/1.png',
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
        $campaignUpdates = [];
        try {
            $stmtCamp = $db->query("
                SELECT cu.*, pr.title as project_title
                FROM campaign_updates cu
                LEFT JOIN projects pr ON cu.project_id = pr.id
                ORDER BY cu.update_date DESC, cu.sort_order ASC
                LIMIT 10
            ");
            $campaignUpdates = $stmtCamp ? $stmtCamp->fetchAll() : [];
        } catch (Throwable $e) {
            error_log("Dashboard warning (campaign_updates): " . $e->getMessage());
        }

        // E. Catalog Projects (All projects for exploration, search & filter: Open, Coming Soon, Closed, Full)
        $catalogProjects = [];
        $openProjects = [];
        try {
            $stmtCatalog = $db->query("
                SELECT id, title, category, image, status, funding_collected, funding_target,
                       lokasi, tenor, return_rate, min_investment, remaining_days, asset_backed
                FROM projects
                ORDER BY sort_order ASC
            ");
            $catalogProjects = $stmtCatalog ? $stmtCatalog->fetchAll() : [];
            $openProjects = array_values(array_filter($catalogProjects, function ($p) {
                return ($p['status'] ?? '') === 'Open';
            }));
        } catch (Throwable $e) {
            error_log("Dashboard warning (catalog_projects): " . $e->getMessage());
        }

        // F. Financial Records & Billings for Odoo/Kledo style reporting
        $allFinRecords = [];
        $billingsList = [];
        $totalPurchasesMIU = 0.0;
        try {
            $stmtFin = $db->prepare("
                SELECT f.*, pr.title as project_title, pr.category as project_category
                FROM financial_records f
                LEFT JOIN projects pr ON f.project_id = pr.id
                WHERE f.investor_id = ?
                ORDER BY f.transaction_date DESC, f.id DESC
            ");
            $stmtFin->execute([$investorId]);
            $allFinRecords = $stmtFin->fetchAll() ?: [];

            foreach ($allFinRecords as $fr) {
                if (!empty($fr['is_billing'])) {
                    $billingsList[] = $fr;
                }
                if ($fr['module'] === 'pembelian' && (strpos(strtolower($fr['vendor_client'] ?? ''), 'miu') !== false || strpos(strtolower($fr['category'] ?? ''), 'miu') !== false)) {
                    $totalPurchasesMIU += (float)$fr['amount'];
                }
            }
        } catch (Throwable $e) {
            error_log("Dashboard warning (financial_records): " . $e->getMessage());
        }

        // G. Real-time Notifications for Investor Notification Box
        $notificationsList = [];
        $unreadNotifCount = 0;
        try {
            $stmtNotif = $db->prepare("
                SELECT n.*, p.title as project_title
                FROM notifications n
                LEFT JOIN projects p ON n.project_id = p.id
                WHERE n.investor_id = ?
                ORDER BY n.created_at DESC, n.id DESC
                LIMIT 30
            ");
            $stmtNotif->execute([$investorId]);
            $notificationsList = $stmtNotif->fetchAll() ?: [];
            foreach ($notificationsList as $n) {
                if (empty($n['is_read'])) $unreadNotifCount++;
            }
        } catch (Throwable $e) {
            error_log("Dashboard warning (notifications): " . $e->getMessage());
        }

        // H. Project Documents for Confidential Center
        $documentsList = [];
        try {
            $stmtDocs = $db->prepare("
                SELECT id, project_id, doc_number, doc_title, doc_type, file_path, file_size, published_at, created_at
                FROM project_documents
                WHERE investor_id = ? OR investor_id IS NULL
                ORDER BY created_at DESC
                LIMIT 10
            ");
            $stmtDocs->execute([$investorId]);
            $documentsList = $stmtDocs->fetchAll() ?: [];
        } catch (Throwable $e) {
            error_log("Dashboard warning (project_documents): " . $e->getMessage());
        }

        $needsBio = !isInvestorBiodataComplete($profile);
        $needsPriv = !isInvestorPrivacyAccepted($profile);

        sendJsonResponse([
            'needs_biodata' => $needsBio,
            'needs_privacy_consent' => $needsPriv,
            'privacy_policy_accepted' => (bool)($profile['privacy_policy_accepted'] ?? false),
            'privacy_policy_version' => $profile['privacy_policy_version'] ?? null,
            'profile' => [
                'id' => (int)$profile['id'],
                'type' => $profile['account_type'],
                'account_type' => $profile['account_type'],
                'email' => $profile['email'],
                'fullName' => $profile['account_type'] === 'perusahaan' ? $profile['business_name'] : ($profile['full_name'] ?: explode('@', $profile['email'])[0]),
                'full_name' => $profile['account_type'] === 'perusahaan' ? $profile['business_name'] : ($profile['full_name'] ?: explode('@', $profile['email'])[0]),
                'citizenship' => $profile['citizenship'] ?? 'Indonesia (WNI)',
                'phone' => $profile['phone'] ?? '',
                'business_activity' => $profile['business_activity'] ?? '',
                'businessActivity' => $profile['business_activity'] ?? '',
                'nik_paspor' => $profile['nik_paspor'] ?? '',
                'npwp' => $profile['npwp'] ?? '',
                'avatar_url' => $profile['avatar_url'] ?? '',
                'auth_provider' => !empty($profile['google_id']) ? 'google' : 'email',
                'has_password' => !empty($profile['password_hash']),
                'status' => $profile['status'],
                'registered_at' => $profile['created_at'],
                'privacy_policy_accepted' => (bool)($profile['privacy_policy_accepted'] ?? false),
                'privacy_policy_accepted_at' => $profile['privacy_policy_accepted_at'] ?? null,
                'privacy_policy_version' => $profile['privacy_policy_version'] ?? null,
                'needs_biodata' => $needsBio,
                'needs_privacy_consent' => $needsPriv,
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
                'total_purchases_miu' => $totalPurchasesMIU,
                'active_projects_count' => count($portfolios),
                'next_payout_date' => $earliestNextPayout,
                'est_roi_rate' => count($portfolios) > 0 ? '≥32% (p.a.)' : '0%'
            ],
            'summary' => [
                'totalInvested' => $totalInvested,
                'totalPayoutReceived' => $totalPayoutReceived,
                'totalPurchasesMiu' => $totalPurchasesMIU,
                'activeProjectsCount' => count($portfolios),
                'nextPayoutDate' => $earliestNextPayout,
                'estRoiRate' => count($portfolios) > 0 ? '≥32% (p.a.)' : '0%'
            ],
            'portfolios' => $portfolios,
            'billings' => $billingsList,
            'financial_records' => $allFinRecords,
            'bank_account' => $bankAccount ? [
                'id' => (int)$bankAccount['id'],
                'bank_name' => $bankAccount['bank_name'],
                'account_number' => $bankAccount['account_number'],
                'account_holder' => $bankAccount['account_holder'],
                'branch' => $bankAccount['branch'] ?? ''
            ] : null,
            'bankAccount' => $bankAccount ? [
                'id' => (int)$bankAccount['id'],
                'bank_name' => $bankAccount['bank_name'],
                'account_number' => $bankAccount['account_number'],
                'account_holder' => $bankAccount['account_holder'],
                'branch' => $bankAccount['branch'] ?? ''
            ] : null,
            'campaign_updates' => $campaignUpdates,
            'documents' => $documentsList,
            'notifications' => $notificationsList,
            'unread_notifications_count' => $unreadNotifCount,
            'open_projects' => $openProjects,
            'catalog_projects' => $catalogProjects,
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
        $nikPaspor = trim($input['nik_paspor'] ?? ($input['nik'] ?? ''));
        $npwp = trim($input['npwp'] ?? '');
        $businessName = trim($input['business_name'] ?? '') ?: $fullName;

        if (empty($fullName)) {
            sendJsonError('Nama lengkap atau nama perusahaan wajib diisi.');
        }

        $stmtUp = $db->prepare("UPDATE investors SET full_name = ?, phone = ?, business_activity = ?, nik_paspor = ?, npwp = ? WHERE id = ?");
        $stmtUp->execute([$fullName, $phone, $businessActivity, $nikPaspor, $npwp, $investorId]);

        // If corporate, sync company profile details
        $stmtCompCheck = $db->prepare("SELECT id FROM investor_companies WHERE investor_id = ?");
        $stmtCompCheck->execute([$investorId]);
        if ($stmtCompCheck->fetch()) {
            $stmtComp = $db->prepare("
                UPDATE investor_companies SET
                    business_name = ?, company_address = ?, pic_name = ?, pic_position = ?, company_phone = ?
                WHERE investor_id = ?
            ");
            $stmtComp->execute([
                $businessName,
                trim($input['company_address'] ?? ''),
                trim($input['pic_name'] ?? ''),
                trim($input['pic_position'] ?? ''),
                trim($input['company_phone'] ?? $phone),
                $investorId
            ]);
        }

        sendJsonResponse(null, 200, 'Profil investor berhasil diperbarui.');
    }

    // 4. POST change_password: Ganti / buat kata sandi
    if ($action === 'change_password') {
        if ($method !== 'POST') sendJsonError('Method harus POST.', 405);
        $input = getJsonInput();
        $oldPass = (string)($input['old_password'] ?? '');
        $newPass = (string)($input['new_password'] ?? '');

        if (empty($newPass)) {
            sendJsonError('Kata sandi baru wajib diisi.');
        }

        if (strlen($newPass) < 8) {
            sendJsonError('Kata sandi baru minimal 8 karakter demi keamanan finansial akun Anda.');
        }

        $stmt = $db->prepare("SELECT password_hash FROM investors WHERE id = ?");
        $stmt->execute([$investorId]);
        $currentHash = $stmt->fetchColumn();

        // Jika akun memiliki kata sandi lama (bukan pendaftaran murni Google), validasi sandi lama
        if (!empty($currentHash)) {
            if (empty($oldPass)) {
                sendJsonError('Kata sandi lama wajib diisi.');
            }
            if (!verifyPassword($oldPass, $currentHash)) {
                sendJsonError('Kata sandi lama tidak sesuai.');
            }
        }

        $newHash = hashPassword($newPass);
        $stmtUp = $db->prepare("UPDATE investors SET password_hash = ? WHERE id = ?");
        $stmtUp->execute([$newHash, $investorId]);

        sendJsonResponse(null, 200, !empty($currentHash) ? 'Kata sandi berhasil diperbarui.' : 'Kata sandi akun berhasil dibuat.');
    }

    // 5. POST submit_interest: Kirim pengajuan minat penempatan modal dari portal investor
    if ($action === 'submit_interest') {
        if ($method !== 'POST') sendJsonError('Method harus POST.', 405);
        $input = getJsonInput();

        $projectId = trim($input['project_id'] ?? '');
        $projectTitle = trim($input['project_title'] ?? 'Proyek Investasi MGI');
        $packageLabel = trim($input['package_label'] ?? 'Paket Kemitraan');
        $nominal = (float)($input['nominal'] ?? 0);
        $notes = trim($input['notes'] ?? '');

        if ($nominal <= 0) {
            sendJsonError('Nominal alokasi modal tidak valid.');
        }

        // Ambil data profil investor terbaru
        $stmtInv = $db->prepare("
            SELECT i.*, c.business_name, c.legal_entity, c.company_address, c.pic_name, c.pic_position, c.company_phone, c.annual_turnover
            FROM investors i
            LEFT JOIN investor_companies c ON i.id = c.investor_id
            WHERE i.id = ?
        ");
        $stmtInv->execute([$investorId]);
        $inv = $stmtInv->fetch();

        if (!$inv) {
            sendJsonError('Data akun investor tidak ditemukan.', 404);
        }

        $isCorp = ($inv['account_type'] === 'perusahaan');
        $formattedNominal = number_format($nominal, 0, ',', '.');
        $rangeStr = 'Rp ' . $formattedNominal . ' (' . $packageLabel . ')';

        $fullName = $isCorp ? ($inv['pic_name'] ?: $inv['full_name']) : ($inv['full_name'] ?: explode('@', $inv['email'])[0]);
        $phone = $inv['phone'] ?: ($inv['company_phone'] ?? null);
        $email = $inv['email'];
        $businessName = $isCorp ? ($inv['business_name'] ?: $inv['full_name']) : null;
        $legalEntity = $isCorp ? ($inv['legal_entity'] ?? 'Perseroan Terbatas (PT)') : null;
        $picPosition = $isCorp ? ($inv['pic_position'] ?? 'Perwakilan Resmi') : null;
        $city = $inv['company_address'] ? mb_substr($inv['company_address'], 0, 100) : 'Indonesia';

        $adminNotes = sprintf(
            "PENGAJUAN MINAT DARI PORTAL INVESTOR (ID #MGI-%04d)\nProyek: %s\nPaket: %s\nNominal: Rp %s\nAkun: %s\nCatatan Pemodal: %s",
            $investorId,
            $projectTitle,
            $packageLabel,
            $formattedNominal,
            $isCorp ? ($inv['business_name'] ?: $inv['full_name']) : $inv['full_name'],
            !empty($notes) ? $notes : '-'
        );

        // 1. Simpan ke tabel leads agar langsung masuk ke CRM Admin (leads.php)
        $stmtLead = $db->prepare("
            INSERT INTO leads (
                investor_id, account_type, full_name, phone, email, business_name, legal_entity, pic_position,
                city, investment_range, project_id, status, admin_notes, utm_source, utm_campaign, landing_page, consent_at
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?,
                ?, ?, ?, 'new', ?, 'Portal Investor', ?, '/investor-dashboard.html', NOW()
            )
        ");
        $stmtLead->execute([
            $investorId,
            $inv['account_type'],
            $fullName,
            $phone,
            $email,
            $businessName,
            $legalEntity,
            $picPosition,
            $city,
            $rangeStr,
            $projectId ?: null,
            $adminNotes,
            $projectTitle
        ]);
        $leadId = (int)$db->lastInsertId();

        // 2. Kirim notifikasi email resmi ke contact@montanainvestama.com & tim MGI
        $leadData = [
            'account_type'     => $inv['account_type'],
            'full_name'        => $fullName,
            'phone'            => $phone,
            'email'            => $email,
            'business_name'    => $businessName,
            'legal_entity'     => $legalEntity,
            'pic_position'     => $picPosition,
            'city'             => $city,
            'investment_range' => $rangeStr,
            'utm_source'       => 'Portal Investor (ID #MGI-' . str_pad((string)$investorId, 4, '0', STR_PAD_LEFT) . ')',
            'admin_notes'      => $adminNotes
        ];
        notifyNewLead($db, $leadData);

        sendJsonResponse([
            'lead_id' => $leadId,
            'project_title' => $projectTitle,
            'package_label' => $packageLabel,
            'nominal' => $nominal
        ], 201, 'Minat penempatan modal berhasil dicatat dan diteruskan ke Relationship Manager (RM) Prioritas MGI.');
    }

    // 6. POST upload_payment_proof: Unggah bukti pembayaran / transfer pelunasan billing
    if ($action === 'upload_payment_proof') {
        if ($method !== 'POST') sendJsonError('Method harus POST.', 405);

        $billingId = (int)($_POST['billing_id'] ?? 0);
        $notes = trim($_POST['notes'] ?? '');
        $proofUrl = null;

        // Jika request berformat JSON
        if (!$billingId) {
            $input = getJsonInput();
            $billingId = (int)($input['billing_id'] ?? 0);
            $notes = trim($input['notes'] ?? '');
            $proofUrl = trim($input['payment_proof_url'] ?? '');
        }

        if (!$billingId) {
            sendJsonError('ID billing tagihan wajib disertakan.');
        }

        // Pastikan billing ada dan benar-benar milik investor ini
        $stmtCheck = $db->prepare("SELECT * FROM financial_records WHERE id = ? AND investor_id = ?");
        $stmtCheck->execute([$billingId, $investorId]);
        $bill = $stmtCheck->fetch();

        if (!$bill) {
            sendJsonError('Tagihan / billing tidak ditemukan atau bukan milik akun Anda.', 404);
        }

        // Tangani upload file jika ada di $_FILES
        if (!empty($_FILES['proof_file']) && $_FILES['proof_file']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['proof_file'];
            if ($file['size'] > 15 * 1024 * 1024) {
                sendJsonError('Ukuran file bukti bayar maksimal 15 MB.');
            }

            $allowedMimes = [
                'image/jpeg' => 'jpg',
                'image/pjpeg' => 'jpg',
                'image/png'  => 'png',
                'image/webp' => 'webp',
                'application/pdf' => 'pdf'
            ];

            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($file['tmp_name']);

            if (!array_key_exists($mime, $allowedMimes)) {
                sendJsonError('Format bukti bayar tidak didukung. Harap unggah gambar JPG, PNG, WebP, atau dokumen PDF.');
            }

            $ext = $allowedMimes[$mime];
            $baseDir = __DIR__ . '/../../assets/uploads/payments';
            if (!is_dir($baseDir)) {
                @mkdir($baseDir, 0777, true);
            }

            $uniqueName = 'PAY_PROOF_' . $billingId . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $destination = $baseDir . '/' . $uniqueName;

            if (!@move_uploaded_file($file['tmp_name'], $destination)) {
                sendJsonError('Gagal memindahkan file bukti bayar ke server penyimpanan.');
            }

            $proofUrl = 'assets/uploads/payments/' . $uniqueName;
        }

        if (empty($proofUrl)) {
            sendJsonError('Bukti transfer / dokumen pembayaran wajib diunggah.');
        }

        // Update status di financial_records menjadi 'paid' (menunggu verifikasi admin)
        $stmtUp = $db->prepare("
            UPDATE financial_records SET
                payment_proof_url = ?,
                paid_at = NOW(),
                payment_notes = ?,
                billing_status = 'paid',
                status = 'sent',
                rejection_reason = NULL,
                updated_at = NOW()
            WHERE id = ? AND investor_id = ?
        ");
        $stmtUp->execute([$proofUrl, $notes, $billingId, $investorId]);

        // Touch SSE stream file agar real-time sync berjalan
        $cacheDir = __DIR__ . '/../../backend/cache';
        if (!is_dir($cacheDir)) {
            @mkdir($cacheDir, 0777, true);
        }
        @file_put_contents($cacheDir . "/project_{$bill['project_id']}_stream.txt", time() . '|' . microtime(true));

        // Buat notifikasi internal akun investor
        createNotification(
            $db,
            $investorId,
            $bill['project_id'],
            'billing',
            'Bukti Pembayaran Terkirim',
            "Bukti transfer untuk {$bill['title']} ({$bill['record_number']}) telah berhasil dikirim dan sedang dalam antrean verifikasi Finance MGI.",
            "investor-dashboard.html?tab=pembukuan"
        );

        sendJsonResponse([
            'billing_id' => $billingId,
            'payment_proof_url' => $proofUrl,
            'paid_at' => date('Y-m-d H:i:s'),
            'billing_status' => 'paid'
        ], 200, 'Bukti pembayaran berhasil diunggah. Tim Finance MGI akan segera memverifikasi transaksi Anda.');
    }

    sendJsonError('Aksi tidak dikenali.', 400);

} catch (Throwable $e) {
    error_log("API Investor Dashboard Exception: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
    if ($action === 'get') {
        sendStaticInvestorDashboardFallback($session);
        exit;
    }
    sendJsonException($e, 'Terjadi kesalahan sistem saat memproses data investor.');
}
