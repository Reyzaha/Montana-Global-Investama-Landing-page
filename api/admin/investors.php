<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — API: ADMIN INVESTORS MANAGEMENT
 * Methods: GET, PUT
 */

require_once __DIR__ . '/../../backend/config/db.php';
require_once __DIR__ . '/../../backend/helpers/response.php';
require_once __DIR__ . '/../../backend/helpers/auth_helper.php';
require_once __DIR__ . '/../../backend/helpers/notification_helper.php';
require_once __DIR__ . '/../../backend/helpers/mail_helper.php';

$admin = requireAdminMutationAuth(['superadmin', 'admin']);
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
                   i.nik_paspor, i.npwp, i.kyc_checklist, i.kyc_verified_at,
                   i.status, i.notes, i.created_at, i.updated_at,
                   c.business_name, c.legal_entity, c.company_address, c.pic_name,
                   c.pic_position, c.company_phone, c.annual_turnover, c.nib, c.npwp_perusahaan,
                   p.id AS portfolio_id, p.project_id, p.contract_number, p.amount AS portfolio_amount,
                   p.return_rate AS portfolio_return_rate, p.tenor AS portfolio_tenor,
                   p.status AS portfolio_status, p.start_date, p.end_date,
                   pr.title AS project_title, pr.status AS project_status
            FROM investors i
            LEFT JOIN investor_companies c ON i.id = c.investor_id
            LEFT JOIN investor_portfolios p ON p.id = (
                SELECT MAX(ip.id) FROM investor_portfolios ip
                WHERE ip.investor_id = i.id AND ip.status IN ('active', 'pending_allocation')
            )
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
            if ($inv['status'] !== 'active') {
                sendJsonError('Investor belum berstatus Active. Verifikasi akun investor terlebih dahulu sebelum menautkan proyek.', 400);
            }

            $db->beginTransaction();
            try {
            // Validasi Proyek (row lock agar dua admin tidak bisa menautkan proyek yang sama bersamaan)
            $stmtProj = $db->prepare("SELECT id, title, funding_target, funding_collected, status FROM projects WHERE id = ? FOR UPDATE");
            $stmtProj->execute([$projectId]);
            $proj = $stmtProj->fetch();
            if (!$proj) {
                $db->rollBack();
                sendJsonError('Proyek tidak ditemukan.', 404);
            }

            // Aturan 1 Investor = 1 Proyek: Cek apakah proyek ini sudah ditautkan ke investor lain
            $stmtCheckOther = $db->prepare("SELECT id, investor_id FROM investor_portfolios WHERE project_id = ? AND investor_id != ? AND status = 'active' LIMIT 1");
            $stmtCheckOther->execute([$projectId, $investorId]);
            $otherPort = $stmtCheckOther->fetch();
            if ($otherPort) {
                $db->rollBack();
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
                    VALUES (?, ?, ?, 'neraca', 'Penyetoran Modal', 'Penempatan Modal Awal Investasi', 'Tagihan penempatan modal eksklusif sesuai Surat Perjanjian Kerjasama (SPK)', ?, 'in', 'sent', 1, 'unpaid', ?, ?)
                ");
                $stmtBill->execute([$billingRecordNo, $investorId, $projectId, $amount, $startDate, $dueDate]);
                $billingId = $db->lastInsertId();
            }

            $db->commit();
            } catch (Throwable $e) {
                if ($db->inTransaction()) {
                    $db->rollBack();
                }
                throw $e;
            }

            // Notifikasi dikirim setelah commit agar kegagalannya tidak membatalkan data
            if ($billingId !== null) {
                createNotification(
                    $db,
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
                $db,
                $investorId,
                $projectId,
                'portfolio',
                'Kemitraan Proyek Resmi Aktif',
                "Selamat! Anda telah resmi ditautkan sebagai investor tunggal untuk proyek '{$proj['title']}'. Modul transparansi operasional & pembukuan kini telah aktif.",
                "investor-dashboard.html?project_id={$projectId}"
            );

            // Kirim email resmi notifikasi penautan kemitraan & penerbitan billing ke email investor
            if (!empty($inv['email'])) {
                $fmtNominal = 'Rp ' . number_format($amount, 0, ',', '.');
                $invName = $inv['full_name'] ?: 'Investor Pemodal';
                $emailSubject = "Selamat Datang: Penautan Kemitraan Eksklusif Proyek {$proj['title']} - PT Montana Global Investama";
                $emailHtml = "
                    <div style='font-family: Arial, sans-serif; color: #1e293b; max-width: 600px; margin: 0 auto; padding: 24px; border: 1px solid #e2e8f0; border-radius: 12px;'>
                        <div style='text-align: center; margin-bottom: 24px;'>
                            <h2 style='color: #1e3a8a; margin: 0;'>PT Montana Global Investama</h2>
                            <p style='color: #64748b; font-size: 13px; margin: 4px 0;'>Pemberitahuan Penautan Portofolio Proyek Resmi</p>
                        </div>
                        <p>Yth. Bapak/Ibu <strong>" . htmlspecialchars($invName) . "</strong>,</p>
                        <p>Selamat! Akun Anda telah resmi ditautkan secara eksklusif sebagai pemodal tunggal untuk proyek:</p>
                        <div style='background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; margin: 16px 0;'>
                            <div style='font-size: 16px; font-weight: bold; color: #1e3a8a;'>" . htmlspecialchars($proj['title']) . "</div>
                            <div style='font-size: 13px; color: #64748b; margin-top: 4px;'>Kategori: " . htmlspecialchars($proj['category'] ?? 'Pengadaan Alat Berat') . "</div>
                            <div style='font-size: 13px; color: #64748b;'>No. Kontrak SPK: <strong>" . htmlspecialchars($contractNumber) . "</strong></div>
                            <div style='font-size: 14px; font-weight: bold; color: #047857; margin-top: 8px;'>Alokasi Modal: {$fmtNominal}</div>
                            <div style='font-size: 13px; color: #475569;'>Estimasi Bagi Hasil: {$returnRate}% per tahun | Tenor: {$tenor} bulan</div>
                        </div>
                        " . ($billingId ? "
                        <p>Faktur tagihan penempatan modal awal (No: <strong>" . htmlspecialchars($billingRecordNo) . "</strong>) telah diterbitkan. Anda dapat mengunggah bukti transfer pelunasan langsung melalui Portal Investor.</p>
                        " : "") . "
                        <div style='text-align: center; margin: 24px 0;'>
                            <a href='https://montanainvestama.com/investor-dashboard.html?project_id={$projectId}' style='background-color: #1e3a8a; color: #ffffff; padding: 12px 24px; text-decoration: none; font-weight: bold; border-radius: 8px; display: inline-block;'>Buka Dashboard Investor &amp; Unggah Bukti Bayar</a>
                        </div>
                        <hr style='border: 0; border-top: 1px solid #e2e8f0; margin: 24px 0;'/>
                        <p style='font-size: 11px; color: #94a3b8; text-align: center;'>Email ini diterbitkan otomatis oleh Sistem PT Montana Global Investama.</p>
                    </div>
                ";
                @sendMgiEmail($inv['email'], $emailSubject, $emailHtml);
            }

            logAdminActivity('assign_project', 'investor_portfolios', (string)$portfolioId, "Admin assigned investor #{$investorId} ({$inv['email']}) to project {$projectId} with amount Rp " . number_format($amount, 0, ',', '.'));

            sendJsonResponse([
                'portfolio_id' => $portfolioId,
                'billing_id' => $billingId,
                'project_id' => $projectId,
                'contract_number' => $contractNumber,
                'amount' => $amount
            ], 201, "Investor berhasil ditautkan secara eksklusif ke proyek {$proj['title']}.");
        }

        if ($action === 'unassign_project') {
            $investorId = (int)($input['investor_id'] ?? 0);
            $projectId = trim($input['project_id'] ?? '');
            $reason = trim($input['reason'] ?? 'Dilepaskan / dibatalkan oleh admin');

            if (!$investorId || empty($projectId)) {
                sendJsonError('Investor ID dan Project ID wajib disertakan.');
            }

            $db->beginTransaction();
            try {
                // Lock and fetch active portfolio
                $stmtPort = $db->prepare("
                    SELECT id, contract_number, amount 
                    FROM investor_portfolios 
                    WHERE investor_id = ? AND project_id = ? AND status IN ('active', 'pending_allocation') 
                    FOR UPDATE
                ");
                $stmtPort->execute([$investorId, $projectId]);
                $port = $stmtPort->fetch();

                if (!$port) {
                    $db->rollBack();
                    sendJsonError('Tidak ditemukan portofolio kemitraan aktif untuk investor dan proyek tersebut.', 404);
                }

                // Update status portofolio menjadi cancelled
                $stmtCancel = $db->prepare("UPDATE investor_portfolios SET status = 'cancelled', updated_at = NOW() WHERE id = ?");
                $stmtCancel->execute([$port['id']]);

                // Hitung sisa portofolio aktif pada proyek ini
                $stmtRem = $db->prepare("
                    SELECT COALESCE(SUM(amount), 0) AS total_collected, COUNT(*) AS active_count 
                    FROM investor_portfolios 
                    WHERE project_id = ? AND status = 'active'
                ");
                $stmtRem->execute([$projectId]);
                $rem = $stmtRem->fetch();
                $activeCount = (int)($rem['active_count'] ?? 0);
                $totalCollected = (float)($rem['total_collected'] ?? 0);

                // Jika tidak ada portofolio aktif lagi, kembalikan status proyek menjadi Open
                if ($activeCount === 0) {
                    $stmtReopen = $db->prepare("UPDATE projects SET status = 'Open', funding_collected = 0 WHERE id = ?");
                    $stmtReopen->execute([$projectId]);
                } else {
                    $stmtReopen = $db->prepare("UPDATE projects SET funding_collected = ? WHERE id = ?");
                    $stmtReopen->execute([$totalCollected, $projectId]);
                }

                $stmtProjName = $db->prepare("SELECT title FROM projects WHERE id = ?");
                $stmtProjName->execute([$projectId]);
                $projTitle = $stmtProjName->fetchColumn() ?: $projectId;

                $db->commit();
            } catch (Throwable $e) {
                if ($db->inTransaction()) {
                    $db->rollBack();
                }
                throw $e;
            }

            // Notifikasi ke investor
            createNotification(
                $db,
                $investorId,
                $projectId,
                'portfolio',
                'Kemitraan Proyek Telah Dinonaktifkan',
                "Kemitraan Anda pada proyek '{$projTitle}' telah dilepaskan oleh administrator. Alasan/Catatan: {$reason}",
                "investor-dashboard.html"
            );

            logAdminActivity(
                'unassign_project',
                'investor_portfolios',
                (string)$port['id'],
                "Admin unassigned project {$projectId} from investor #{$investorId}. Reason: {$reason}"
            );

            sendJsonResponse([
                'portfolio_id' => $port['id'],
                'project_id'   => $projectId,
                'investor_id'  => $investorId,
                'project_title'=> $projTitle
            ], 200, "Kemitraan pada proyek '{$projTitle}' berhasil dilepaskan. Proyek kini kembali terbuka (Open).");
        }
    }

    // 3. PUT: Update Status Investor atau Catatan
    if ($method === 'PUT') {
        $id = (int)($input['id'] ?? ($_GET['id'] ?? 0));
        $status = $input['status'] ?? null;
        $notes = $input['notes'] ?? null;
        $kycChecklist = isset($input['kyc_checklist']) 
            ? (is_array($input['kyc_checklist']) ? json_encode($input['kyc_checklist']) : trim($input['kyc_checklist']))
            : null;
        $nikPaspor = !empty($input['nik_paspor']) ? trim($input['nik_paspor']) : null;
        $npwp = !empty($input['npwp']) ? trim($input['npwp']) : null;
        $nib = !empty($input['nib']) ? trim($input['nib']) : null;
        $npwpPerusahaan = !empty($input['npwp_perusahaan']) ? trim($input['npwp_perusahaan']) : null;

        if (!$id) {
            sendJsonError('ID Investor wajib disertakan.');
        }

        $stmtCheck = $db->prepare("SELECT id, email, full_name, account_type, status FROM investors WHERE id = ?");
        $stmtCheck->execute([$id]);
        $inv = $stmtCheck->fetch();
        if (!$inv) {
            sendJsonError('Investor tidak ditemukan.', 404);
        }

        $allowedStatuses = ['active', 'pending_verification', 'suspended'];
        $newStatus = ($status && in_array($status, $allowedStatuses)) ? $status : $inv['status'];

        // Update investor table
        $updateSql = "
            UPDATE investors SET 
                status = ?, 
                notes = ?,
                nik_paspor = COALESCE(?, nik_paspor),
                npwp = COALESCE(?, npwp),
                kyc_checklist = COALESCE(?, kyc_checklist),
                kyc_verified_at = " . ($newStatus === 'active' ? "COALESCE(kyc_verified_at, NOW())" : "kyc_verified_at") . ",
                updated_at = NOW()
            WHERE id = ?
        ";
        $stmtUp = $db->prepare($updateSql);
        $stmtUp->execute([$newStatus, $notes, $nikPaspor, $npwp, $kycChecklist, $id]);

        // Update corporate legal documents if applicable
        if ($inv['account_type'] === 'perusahaan' && ($nib || $npwpPerusahaan)) {
            $stmtUpComp = $db->prepare("
                UPDATE investor_companies SET
                    nib = COALESCE(?, nib),
                    npwp_perusahaan = COALESCE(?, npwp_perusahaan),
                    updated_at = NOW()
                WHERE investor_id = ?
            ");
            $stmtUpComp->execute([$nib, $npwpPerusahaan, $id]);
        }

        // Kirim notifikasi jika status diubah menjadi active
        if ($newStatus === 'active' && $inv['status'] !== 'active') {
            createNotification(
                $db,
                $id,
                null,
                'status_update',
                'Akun Anda Telah Diverifikasi & Aktif',
                'Selamat! Akun investor Anda telah diverifikasi oleh tim PT Montana Global Investama. Anda sekarang dapat memilih dan mendanai proyek kemitraan.',
                'investor-dashboard.html'
            );

            // Kirim email konfirmasi verifikasi KYC resmi ke email investor
            if (!empty($inv['email'])) {
                $invDisplayName = $inv['full_name'] ?: 'Investor Pemodal';
                $emailSubject = "Akun Terverifikasi Resmi: Akses Penuh Kemitraan Proyek - PT Montana Global Investama";
                $emailHtml = "
                    <div style='font-family: Arial, sans-serif; color: #1e293b; max-width: 600px; margin: 0 auto; padding: 24px; border: 1px solid #e2e8f0; border-radius: 12px;'>
                        <div style='text-align: center; margin-bottom: 24px;'>
                            <h2 style='color: #142563; margin: 0;'>PT Montana Global Investama</h2>
                            <p style='color: #64748b; font-size: 13px; margin: 4px 0;'>Pemberitahuan Verifikasi Kelayakan Pemodal (KYC / Due Diligence)</p>
                        </div>
                        <p>Yth. Bapak/Ibu <strong>" . htmlspecialchars($invDisplayName) . "</strong>,</p>
                        <p>Selamat! Dokumen identitas dan kelayakan pemodal Anda telah berhasil diverifikasi oleh tim Kepatuhan &amp; Legal PT Montana Global Investama.</p>
                        <div style='background-color: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 16px; margin: 16px 0; color: #166534;'>
                            <strong>Status Akun Anda Sekarang: AKTIF (VERIFIED INVESTOR)</strong><br>
                            <span style='font-size: 13px; color: #15803d;'>Anda kini memiliki akses eksklusif untuk memilih proyek investasi sektor riil, meninjau prospektus, dan menempatkan alokasi modal.</span>
                        </div>
                        <p style='font-size: 13px; color: #475569;'>Silakan login ke portal investor untuk melihat katalog proyek aktif yang siap didanai:</p>
                        <div style='text-align: center; margin: 24px 0;'>
                            <a href='https://montanainvestama.com/investor-dashboard.html' style='background-color: #142563; color: #ffffff; padding: 12px 24px; text-decoration: none; font-weight: bold; border-radius: 8px; display: inline-block;'>Masuk ke Portal Investor</a>
                        </div>
                        <hr style='border: 0; border-top: 1px solid #e2e8f0; margin: 24px 0;'/>
                        <p style='font-size: 11px; color: #94a3b8; text-align: center;'>Email ini diterbitkan otomatis oleh Sistem PT Montana Global Investama.</p>
                    </div>
                ";
                @sendMgiEmail($inv['email'], $emailSubject, $emailHtml);
            }
        }

        logAdminActivity('update_status', 'investors', (string)$id, "Admin changed investor {$inv['email']} status to {$newStatus} (KYC updated)");

        sendJsonResponse([
            'id' => $id,
            'status' => $newStatus
        ], 200, "Status investor {$inv['email']} berhasil diperbarui menjadi '{$newStatus}'.");
    }

    sendJsonError('Method tidak didukung.', 405);

} catch (Throwable $e) {
    sendJsonException($e, 'Terjadi kesalahan operasi investor. Silakan coba lagi.');
}
