<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — API: ADMIN FINANCIAL & BILLING MANAGEMENT
 * Modules: Neraca, Laba Rugi, Pembelian (Pengadaan MIU), Penjualan, Billing
 * Methods: GET, POST, PUT, DELETE
 */

require_once __DIR__ . '/../../backend/config/db.php';
require_once __DIR__ . '/../../backend/helpers/response.php';
require_once __DIR__ . '/../../backend/helpers/auth_helper.php';
require_once __DIR__ . '/../../backend/helpers/notification_helper.php';
require_once __DIR__ . '/../../backend/helpers/mail_helper.php';

$admin = requireAdminMutationAuth(['superadmin', 'admin']);
$method = $_SERVER['REQUEST_METHOD'];
$input = getJsonInput();

// Handle method spoofing from client
if (isset($input['_method'])) {
    $method = strtoupper($input['_method']);
} elseif (isset($_GET['_method'])) {
    $method = strtoupper($_GET['_method']);
} elseif (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $method = 'DELETE';
}

function cleanAmount(mixed $val): float {
    if (is_null($val)) return 0.0;
    if (is_numeric($val)) return (float)$val;
    $clean = preg_replace('/[^0-9.]/', '', (string)$val);
    return (float)$clean;
}

try {
    $db = getDB();

    // 1. GET: Ambil daftar transaksi, ringkasan kalkulasi, serta opsi investor & project
    if ($method === 'GET') {
        $investorId = !empty($_GET['investor_id']) ? (int)$_GET['investor_id'] : null;
        $projectId = !empty($_GET['project_id']) ? trim($_GET['project_id']) : null;
        $module = !empty($_GET['module']) ? trim($_GET['module']) : null;
        $isBillingOnly = isset($_GET['billing_only']) && ($_GET['billing_only'] === '1' || $_GET['billing_only'] === 'true');
        $search = trim($_GET['search'] ?? '');

        $sql = "
            SELECT f.*, 
                   i.full_name as investor_name, i.email as investor_email, i.account_type,
                   c.business_name as investor_company,
                   p.title as project_title, p.category as project_category
            FROM financial_records f
            JOIN investors i ON f.investor_id = i.id
            LEFT JOIN investor_companies c ON i.id = c.investor_id
            JOIN projects p ON f.project_id = p.id
            WHERE 1=1
        ";
        $params = [];

        if ($investorId) {
            $sql .= " AND f.investor_id = ?";
            $params[] = $investorId;
        }

        if ($projectId) {
            $sql .= " AND f.project_id = ?";
            $params[] = $projectId;
        }

        if ($module && in_array($module, ['neraca', 'labarugi', 'pembelian', 'penjualan'])) {
            $sql .= " AND f.module = ?";
            $params[] = $module;
        }

        if ($isBillingOnly) {
            $sql .= " AND f.is_billing = 1";
        }

        if (!empty($search)) {
            $sql .= " AND (f.title LIKE ? OR f.record_number LIKE ? OR f.vendor_client LIKE ? OR f.unit_detail LIKE ? OR f.description LIKE ?)";
            $term = "%{$search}%";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $sql .= " ORDER BY f.transaction_date DESC, f.id DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $records = $stmt->fetchAll();

        // Hitung akumulasi ringkasan finansial (Filter aware or global)
        $summarySql = "SELECT * FROM financial_records WHERE 1=1";
        $summaryParams = [];
        if ($investorId) {
            $summarySql .= " AND investor_id = ?";
            $summaryParams[] = $investorId;
        }
        if ($projectId) {
            $summarySql .= " AND project_id = ?";
            $summaryParams[] = $projectId;
        }

        $stmtSum = $db->prepare($summarySql);
        $stmtSum->execute($summaryParams);
        $allRecords = $stmtSum->fetchAll();

        $totalCapital = 0.0;
        $totalPurchasesMIU = 0.0;
        $totalPurchasesAll = 0.0;
        $totalPenjualan = 0.0;
        $totalBebanOps = 0.0;
        $totalDividen = 0.0;
        $totalAsetUnit = 0.0;
        $totalBillings = 0;

        foreach ($allRecords as $r) {
            $amt = (float)$r['amount'];
            $mod = $r['module'];
            $cat = strtolower($r['category']);
            $vendor = strtolower($r['vendor_client'] ?? '');

            if ($r['is_billing']) {
                $totalBillings++;
            }

            if ($mod === 'neraca') {
                if ($r['flow_type'] === 'in' || strpos($cat, 'modal') !== false) {
                    $totalCapital += $amt;
                } elseif ($r['flow_type'] === 'balance_asset' || strpos($cat, 'aset tetap') !== false) {
                    $totalAsetUnit += $amt;
                }
            } elseif ($mod === 'pembelian') {
                $totalPurchasesAll += $amt;
                if (strpos($vendor, 'miu') !== false || strpos($cat, 'miu') !== false) {
                    $totalPurchasesMIU += $amt;
                }
            } elseif ($mod === 'penjualan') {
                $totalPenjualan += $amt;
            } elseif ($mod === 'labarugi') {
                if (strpos($cat, 'dividen') !== false || strpos($cat, 'bagi hasil') !== false || strpos($cat, 'profit') !== false) {
                    $totalDividen += $amt;
                } else {
                    $totalBebanOps += $amt;
                }
            }
        }

        // Default modal ke portofolio investor jika ada
        if ($totalCapital === 0.0 && $investorId) {
            $stmtPort = $db->prepare("SELECT SUM(amount) FROM investor_portfolios WHERE investor_id = ?");
            $stmtPort->execute([$investorId]);
            $totalCapital = (float)$stmtPort->fetchColumn();
        }

        // Sisa Kas Proyek = Modal Disetor + Penjualan - Seluruh Pembelian Unit - Beban Operasional - Dividen
        $sisaKas = max(0.0, $totalCapital + $totalPenjualan - $totalPurchasesAll - $totalBebanOps - $totalDividen);
        $totalAktiva = $sisaKas + $totalAsetUnit;
        $labaBersih = max(0.0, $totalPenjualan - $totalBebanOps);

        // Ambil daftar investor untuk dropdown
        $stmtInvList = $db->query("
            SELECT i.id, i.full_name, i.email, i.account_type, c.business_name
            FROM investors i
            LEFT JOIN investor_companies c ON i.id = c.investor_id
            WHERE i.status = 'active'
            ORDER BY i.full_name ASC
        ");
        $investorOptions = $stmtInvList ? $stmtInvList->fetchAll() : [];

        // Ambil daftar proyek untuk dropdown
        $stmtProjList = $db->query("SELECT id, title, category, funding_target, funding_collected FROM projects ORDER BY sort_order ASC");
        $projectOptions = $stmtProjList ? $stmtProjList->fetchAll() : [];

        sendJsonResponse([
            'records' => $records,
            'summary' => [
                'total_capital' => $totalCapital,
                'total_purchases_miu' => $totalPurchasesMIU,
                'total_purchases_all' => $totalPurchasesAll,
                'total_penjualan' => $totalPenjualan,
                'total_beban_ops' => $totalBebanOps,
                'total_dividen' => $totalDividen,
                'sisa_kas' => $sisaKas,
                'total_aset_unit' => $totalAsetUnit,
                'total_aktiva' => $totalAktiva,
                'laba_bersih' => $labaBersih,
                'total_billings_count' => $totalBillings
            ],
            'investors' => $investorOptions,
            'projects' => $projectOptions
        ]);
    }

    // 2. POST: Aksi Verifikasi Pembayaran ATAU Buat Transaksi / Billing Baru
    if ($method === 'POST') {
        $action = trim($_GET['action'] ?? ($input['action'] ?? ''));

        // 2A. Aksi Verifikasi Pembayaran oleh Admin
        if ($action === 'verify_payment') {
            $id = (int)($input['id'] ?? ($_GET['id'] ?? 0));
            $verStatus = trim($input['verification_status'] ?? 'approve'); // 'approve' | 'reject'
            $rejectionReason = trim($input['rejection_reason'] ?? '');

            if (!$id) {
                sendJsonError('ID catatan billing wajib disertakan.');
            }

            $stmtCheck = $db->prepare("
                SELECT f.*, i.full_name as investor_name, i.email as investor_email, p.title as project_title
                FROM financial_records f
                JOIN investors i ON f.investor_id = i.id
                JOIN projects p ON f.project_id = p.id
                WHERE f.id = ?
            ");
            $stmtCheck->execute([$id]);
            $existing = $stmtCheck->fetch();

            if (!$existing) {
                sendJsonError('Catatan billing tidak ditemukan.', 404);
            }

            $adminName = $admin['name'] ?? 'Admin Finance MGI';

            if ($verStatus === 'approve') {
                $stmtUp = $db->prepare("
                    UPDATE financial_records SET
                        billing_status = 'settled',
                        status = 'verified',
                        verified_at = NOW(),
                        verified_by = ?,
                        rejection_reason = NULL,
                        updated_at = NOW()
                    WHERE id = ?
                ");
                $stmtUp->execute([$adminName, $id]);

                // Touch SSE cache file
                $cacheDir = __DIR__ . '/../../backend/cache';
                if (!is_dir($cacheDir)) {
                    @mkdir($cacheDir, 0777, true);
                }
                @file_put_contents($cacheDir . "/project_{$existing['project_id']}_stream.txt", time() . '|' . microtime(true));

                // Notifikasi ke portal investor
                $notifTitle = "Pembayaran Diverifikasi Lunas: {$existing['title']}";
                $notifMsg = "Pembayaran untuk faktur {$existing['record_number']} senilai Rp " . number_format($existing['amount'], 0, ',', '.') . " telah diverifikasi lunas oleh Finance MGI.";
                createNotification($db, (int)$existing['investor_id'], $existing['project_id'], 'billing', $notifTitle, $notifMsg, "investor-dashboard.html?tab=pembukuan");

                // Kirim email konfirmasi resmi lunas
                if (!empty($existing['investor_email'])) {
                    $fmtNominal = 'Rp ' . number_format($existing['amount'], 0, ',', '.');
                    $invName = $existing['investor_name'] ?: 'Investor Pemodal';
                    $emailSubject = "Konfirmasi Pelunasan Pembayaran: {$existing['title']} ({$existing['record_number']}) - PT Montana Global Investama";
                    $emailHtml = "
                        <div style='font-family: Arial, sans-serif; color: #1e293b; max-width: 600px; margin: 0 auto; padding: 24px; border: 1px solid #e2e8f0; border-radius: 12px;'>
                            <div style='text-align: center; margin-bottom: 24px;'>
                                <h2 style='color: #047857; margin: 0;'>PT Montana Global Investama</h2>
                                <p style='color: #64748b; font-size: 13px; margin: 4px 0;'>Tanda Terima Digital &amp; Konfirmasi Pelunasan</p>
                            </div>
                            <p>Yth. Bapak/Ibu <strong>" . htmlspecialchars($invName) . "</strong>,</p>
                            <p>Kabar baik! Tim Finance PT Montana Global Investama telah memverifikasi dan mengonfirmasi bukti transfer pembayaran tagihan Anda:</p>
                            <div style='background-color: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 16px; margin: 16px 0;'>
                                <table style='width: 100%; border-collapse: collapse; font-size: 14px;'>
                                    <tr style='border-bottom: 1px solid #dcfce7;'><td style='padding: 6px 0; color: #166534;'>No. Faktur / Ref:</td><td style='padding: 6px 0; font-weight: bold;'>" . htmlspecialchars($existing['record_number']) . "</td></tr>
                                    <tr style='border-bottom: 1px solid #dcfce7;'><td style='padding: 6px 0; color: #166534;'>Perihal Tagihan:</td><td style='padding: 6px 0; font-weight: bold;'>" . htmlspecialchars($existing['title']) . "</td></tr>
                                    <tr style='border-bottom: 1px solid #dcfce7;'><td style='padding: 6px 0; color: #166534;'>Proyek:</td><td style='padding: 6px 0; font-weight: bold;'>" . htmlspecialchars($existing['project_title']) . "</td></tr>
                                    <tr style='border-bottom: 1px solid #dcfce7;'><td style='padding: 6px 0; color: #166534;'>Nominal Diverifikasi:</td><td style='padding: 6px 0; font-weight: bold; color: #047857; font-size: 16px;'>" . $fmtNominal . "</td></tr>
                                    <tr style='border-bottom: 1px solid #dcfce7;'><td style='padding: 6px 0; color: #166534;'>Status:</td><td style='padding: 6px 0; font-weight: bold; color: #047857;'>LUNAS (SETTLED)</td></tr>
                                    <tr><td style='padding: 6px 0; color: #166534;'>Diverifikasi Oleh:</td><td style='padding: 6px 0;'>" . htmlspecialchars($adminName) . " pada " . date('d M Y H:i') . " WIB</td></tr>
                                </table>
                            </div>
                            <p style='font-size: 13px; color: #475569;'>Dana telah resmi dibukukan ke dalam neraca proyek dan dapat dipantau perkembangannya pada grafik transparansi finansial di portal Anda.</p>
                            <div style='text-align: center; margin: 24px 0;'>
                                <a href='https://montanainvestama.com/investor-dashboard.html?tab=billing' style='background-color: #047857; color: #ffffff; padding: 12px 24px; text-decoration: none; font-weight: bold; border-radius: 8px; display: inline-block;'>Lihat Status Lunas di Portal Investor</a>
                            </div>
                            <hr style='border: 0; border-top: 1px solid #e2e8f0; margin: 24px 0;'/>
                            <p style='font-size: 11px; color: #94a3b8; text-align: center;'>Email ini diterbitkan otomatis oleh Sistem PT Montana Global Investama.</p>
                        </div>
                    ";
                    @sendMgiEmail($existing['investor_email'], $emailSubject, $emailHtml);
                }

                logAdminActivity(
                    'verify_payment_settled',
                    'financial_records',
                    (string)$id,
                    "Admin verified payment settled for {$existing['record_number']} (Rp " . number_format($existing['amount'], 0, ',', '.') . ")"
                );

                sendJsonResponse([
                    'id' => $id,
                    'billing_status' => 'settled',
                    'status' => 'verified',
                    'verified_at' => date('Y-m-d H:i:s'),
                    'verified_by' => $adminName
                ], 200, "Bukti pembayaran {$existing['record_number']} berhasil diverifikasi Lunas.");

            } else {
                // Reject verification
                if (empty($rejectionReason)) {
                    sendJsonError('Alasan penolakan bukti bayar wajib diisi agar investor mengetahui perbaikan yang diperlukan.');
                }

                $stmtUp = $db->prepare("
                    UPDATE financial_records SET
                        billing_status = 'unpaid',
                        status = 'sent',
                        rejection_reason = ?,
                        verified_at = NOW(),
                        verified_by = ?,
                        updated_at = NOW()
                    WHERE id = ?
                ");
                $stmtUp->execute([$rejectionReason, $adminName, $id]);

                // Touch SSE cache file
                $cacheDir = __DIR__ . '/../../backend/cache';
                if (!is_dir($cacheDir)) {
                    @mkdir($cacheDir, 0777, true);
                }
                @file_put_contents($cacheDir . "/project_{$existing['project_id']}_stream.txt", time() . '|' . microtime(true));

                // Notifikasi ke portal investor
                $notifTitle = "Bukti Bayar Perlu Revisi: {$existing['title']}";
                $notifMsg = "Bukti transfer untuk faktur {$existing['record_number']} ditolak. Alasan: {$rejectionReason}. Silakan unggah bukti yang benar.";
                createNotification($db, (int)$existing['investor_id'], $existing['project_id'], 'billing', $notifTitle, $notifMsg, "investor-dashboard.html?tab=pembukuan");

                // Kirim email notifikasi penolakan
                if (!empty($existing['investor_email'])) {
                    $invName = $existing['investor_name'] ?: 'Investor Pemodal';
                    $emailSubject = "Pemberitahuan Revisi Bukti Pembayaran: {$existing['title']} ({$existing['record_number']}) - PT Montana Global Investama";
                    $emailHtml = "
                        <div style='font-family: Arial, sans-serif; color: #1e293b; max-width: 600px; margin: 0 auto; padding: 24px; border: 1px solid #e2e8f0; border-radius: 12px;'>
                            <div style='text-align: center; margin-bottom: 24px;'>
                                <h2 style='color: #b91c1c; margin: 0;'>PT Montana Global Investama</h2>
                                <p style='color: #64748b; font-size: 13px; margin: 4px 0;'>Pemberitahuan Revisi Verifikasi Pembayaran</p>
                            </div>
                            <p>Yth. Bapak/Ibu <strong>" . htmlspecialchars($invName) . "</strong>,</p>
                            <p>Setelah ditinjau oleh Finance MGI, bukti transfer yang Anda unggah untuk faktur <strong>" . htmlspecialchars($existing['record_number']) . "</strong> (" . htmlspecialchars($existing['title']) . ") memerlukan revisi:</p>
                            <div style='background-color: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 16px; margin: 16px 0; color: #991b1b;'>
                                <strong>Catatan / Alasan Penolakan:</strong><br>
                                " . nl2br(htmlspecialchars($rejectionReason)) . "
                            </div>
                            <p style='font-size: 13px; color: #475569;'>Harap unggah kembali bukti transfer pembayaran yang valid (jelas terbaca nominal, rekening tujuan, dan tanggal transfer) melalui Portal Investor.</p>
                            <div style='text-align: center; margin: 24px 0;'>
                                <a href='https://montanainvestama.com/investor-dashboard.html?tab=billing' style='background-color: #b91c1c; color: #ffffff; padding: 12px 24px; text-decoration: none; font-weight: bold; border-radius: 8px; display: inline-block;'>Unggah Ulang Bukti Transfer</a>
                            </div>
                            <hr style='border: 0; border-top: 1px solid #e2e8f0; margin: 24px 0;'/>
                            <p style='font-size: 11px; color: #94a3b8; text-align: center;'>Email ini diterbitkan otomatis oleh Sistem PT Montana Global Investama.</p>
                        </div>
                    ";
                    @sendMgiEmail($existing['investor_email'], $emailSubject, $emailHtml);
                }

                logAdminActivity(
                    'verify_payment_rejected',
                    'financial_records',
                    (string)$id,
                    "Admin rejected payment proof for {$existing['record_number']}. Reason: {$rejectionReason}"
                );

                sendJsonResponse([
                    'id' => $id,
                    'billing_status' => 'unpaid',
                    'status' => 'sent',
                    'rejection_reason' => $rejectionReason
                ], 200, "Bukti pembayaran ditolak dan notifikasi revisi telah dikirimkan ke investor.");
            }
        }

        // 2B. Aksi Kirim Pengingat Tagihan Jatuh Tempo ke Investor
        if ($action === 'send_due_reminder') {
            $id = (int)($input['id'] ?? ($_GET['id'] ?? 0));
            if (!$id) {
                sendJsonError('ID catatan billing wajib disertakan.');
            }

            $stmtCheck = $db->prepare("
                SELECT f.*, i.full_name as investor_name, i.email as investor_email, p.title as project_title
                FROM financial_records f
                JOIN investors i ON f.investor_id = i.id
                JOIN projects p ON f.project_id = p.id
                WHERE f.id = ?
            ");
            $stmtCheck->execute([$id]);
            $existing = $stmtCheck->fetch();

            if (!$existing) {
                sendJsonError('Catatan billing tidak ditemukan.', 404);
            }
            if (!$existing['is_billing']) {
                sendJsonError('Catatan ini bukan merupakan faktur tagihan.');
            }
            if ($existing['billing_status'] === 'settled') {
                sendJsonError('Faktur ini telah diverifikasi lunas sebelumnya.');
            }

            $fmtNominal = 'Rp ' . number_format($existing['amount'], 0, ',', '.');
            $invName = $existing['investor_name'] ?: 'Investor Pemodal';
            $dueDateStr = $existing['due_date'] ? date('d M Y', strtotime($existing['due_date'])) : 'Segera';

            $today = new DateTime();
            $dueStatusText = "Jatuh Tempo: " . $dueDateStr;
            if ($existing['due_date']) {
                $dueDateObj = new DateTime($existing['due_date']);
                $diffDays = (int)$today->diff($dueDateObj)->format("%r%a");
                if ($diffDays < 0) {
                    $dueStatusText = "LEWAT JATUH TEMPO (" . abs($diffDays) . " hari yang lalu)";
                } elseif ($diffDays === 0) {
                    $dueStatusText = "JATUH TEMPO HARI INI";
                } else {
                    $dueStatusText = "Jatuh tempo dalam {$diffDays} hari ({$dueDateStr})";
                }
            }

            // In-app notification
            $notifTitle = "Pengingat Tagihan: {$existing['title']}";
            $notifMsg = "Faktur {$existing['record_number']} senilai {$fmtNominal} memiliki tanggal jatuh tempo {$dueDateStr}. Harap selesaikan transfer & unggah bukti bayar.";
            createNotification($db, (int)$existing['investor_id'], $existing['project_id'], 'billing', $notifTitle, $notifMsg, "investor-dashboard.html?tab=pembukuan");

            // Email pengingat
            if (!empty($existing['investor_email'])) {
                $emailSubject = "Pengingat Tagihan Pembayaran Alokasi Modal: {$existing['title']} ({$existing['record_number']}) - PT Montana Global Investama";
                $emailHtml = "
                    <div style='font-family: Arial, sans-serif; color: #1e293b; max-width: 600px; margin: 0 auto; padding: 24px; border: 1px solid #e2e8f0; border-radius: 12px;'>
                        <div style='text-align: center; margin-bottom: 24px;'>
                            <h2 style='color: #142563; margin: 0;'>PT Montana Global Investama</h2>
                            <p style='color: #64748b; font-size: 13px; margin: 4px 0;'>Pemberitahuan &amp; Pengingat Jatuh Tempo Tagihan Modal</p>
                        </div>
                        <p>Yth. Bapak/Ibu <strong>" . htmlspecialchars($invName) . "</strong>,</p>
                        <p>Kami menginformasikan bahwa tagihan alokasi modal kemitraan proyek Anda saat ini berstatus <strong>Menunggu Pembayaran</strong>:</p>
                        <div style='background-color: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 16px; margin: 16px 0;'>
                            <table style='width: 100%; border-collapse: collapse; font-size: 14px;'>
                                <tr style='border-bottom: 1px solid #fef3c7;'><td style='padding: 6px 0; color: #92400e;'>No. Faktur:</td><td style='padding: 6px 0; font-weight: bold;'>" . htmlspecialchars($existing['record_number']) . "</td></tr>
                                <tr style='border-bottom: 1px solid #fef3c7;'><td style='padding: 6px 0; color: #92400e;'>Perihal Tagihan:</td><td style='padding: 6px 0; font-weight: bold;'>" . htmlspecialchars($existing['title']) . "</td></tr>
                                <tr style='border-bottom: 1px solid #fef3c7;'><td style='padding: 6px 0; color: #92400e;'>Proyek Penempatan:</td><td style='padding: 6px 0; font-weight: bold;'>" . htmlspecialchars($existing['project_title']) . "</td></tr>
                                <tr style='border-bottom: 1px solid #fef3c7;'><td style='padding: 6px 0; color: #92400e;'>Total Tagihan:</td><td style='padding: 6px 0; font-weight: bold; color: #b45309; font-size: 16px;'>" . $fmtNominal . "</td></tr>
                                <tr><td style='padding: 6px 0; color: #92400e;'>Status Waktu:</td><td style='padding: 6px 0; font-weight: bold; color: #dc2626;'>" . htmlspecialchars($dueStatusText) . "</td></tr>
                            </table>
                        </div>
                        <div style='background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px; margin: 16px 0; font-size: 13px;'>
                            <strong>Instruksi Transfer Rekening Resmi Escrow:</strong><br>
                            Bank: <strong>Bank Mandiri (Persero) Tbk</strong><br>
                            Nomor Rekening: <strong>123-00-9876543-2</strong><br>
                            Atas Nama: <strong>PT Montana Global Investama</strong><br>
                            Berita Acara: <code>" . htmlspecialchars($existing['record_number']) . "</code>
                        </div>
                        <p style='font-size: 13px; color: #475569;'>Setelah melakukan pembayaran, mohon unggah struk/bukti transfer melalui menu Billing pada Portal Investor agar tim Finance MGI dapat segera memvalidasi pencatatan pembukuan.</p>
                        <div style='text-align: center; margin: 24px 0;'>
                            <a href='https://montanainvestama.com/investor-dashboard.html?tab=billing' style='background-color: #142563; color: #ffffff; padding: 12px 24px; text-decoration: none; font-weight: bold; border-radius: 8px; display: inline-block;'>Buka Portal &amp; Unggah Bukti Bayar</a>
                        </div>
                        <hr style='border: 0; border-top: 1px solid #e2e8f0; margin: 24px 0;'/>
                        <p style='font-size: 11px; color: #94a3b8; text-align: center;'>Email ini diterbitkan otomatis oleh Sistem PT Montana Global Investama.</p>
                    </div>
                ";
                @sendMgiEmail($existing['investor_email'], $emailSubject, $emailHtml);
            }

            logAdminActivity(
                'send_due_reminder',
                'financial_records',
                (string)$id,
                "Admin sent due reminder email to {$existing['investor_name']} for {$existing['record_number']}"
            );

            sendJsonResponse([
                'id' => $id,
                'sent_to' => $existing['investor_email'],
                'due_date' => $existing['due_date']
            ], 200, "Pengingat tagihan {$existing['record_number']} berhasil dikirimkan ke investor.");
        }

        // 2C. Buat Transaksi Baru / Kirim Billing ke Investor
        $investorId = (int)($input['investor_id'] ?? 0);
        $projectId = trim($input['project_id'] ?? '');
        $module = trim($input['module'] ?? 'pembelian');
        $category = trim($input['category'] ?? '');
        $title = trim($input['title'] ?? '');
        $description = trim($input['description'] ?? '');
        $amount = cleanAmount($input['amount'] ?? 0);
        $flowType = trim($input['flow_type'] ?? '');
        $vendorClient = trim($input['vendor_client'] ?? '');
        $unitDetail = trim($input['unit_detail'] ?? '');
        $status = trim($input['status'] ?? 'sent');
        $isBilling = !empty($input['is_billing']) ? 1 : 0;
        $billingStatus = trim($input['billing_status'] ?? ($isBilling ? 'settled' : 'reported'));
        $transactionDate = trim($input['transaction_date'] ?? date('Y-m-d'));
        $dueDate = !empty($input['due_date']) ? trim($input['due_date']) : null;
        $recordNumber = trim($input['record_number'] ?? '');

        if (!$investorId) {
            sendJsonError('Investor wajib dipilih.');
        }
        if (empty($projectId)) {
            sendJsonError('Proyek investasi wajib dipilih.');
        }
        if (!in_array($module, ['neraca', 'labarugi', 'pembelian', 'penjualan'])) {
            sendJsonError('Modul keuangan tidak valid (harus Neraca, Laba Rugi, Pembelian, atau Penjualan).');
        }
        if (empty($title)) {
            sendJsonError('Judul transaksi / nama billing wajib diisi.');
        }
        if ($amount <= 0) {
            sendJsonError('Nominal transaksi harus lebih dari 0.');
        }

        // Tentukan default flow_type jika kosong
        if (empty($flowType)) {
            if ($module === 'penjualan') {
                $flowType = 'in';
            } elseif ($module === 'pembelian' || $module === 'labarugi') {
                $flowType = 'out';
            } else {
                $flowType = 'balance_asset';
            }
        }

        // Auto-generate record number jika kosong
        if (empty($recordNumber)) {
            $prefix = 'FIN';
            if ($isBilling) $prefix = 'BILL';
            elseif ($module === 'pembelian') $prefix = 'PO-MIU';
            elseif ($module === 'penjualan') $prefix = 'INV-SLS';
            elseif ($module === 'labarugi') $prefix = 'EXP';
            elseif ($module === 'neraca') $prefix = 'AST';

            $recordNumber = $prefix . '-' . date('Ymd') . '-' . rand(100, 999);
        }

        $stmtIns = $db->prepare("
            INSERT INTO financial_records (
                investor_id, project_id, module, category, record_number, title, description,
                amount, flow_type, vendor_client, unit_detail, status, is_billing, billing_status,
                transaction_date, due_date, created_by
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmtIns->execute([
            $investorId,
            $projectId,
            $module,
            $category ?: ucfirst($module),
            $recordNumber,
            $title,
            $description,
            $amount,
            $flowType,
            $vendorClient,
            $unitDetail,
            $status,
            $isBilling,
            $billingStatus,
            $transactionDate,
            $dueDate,
            $admin['name'] ?? 'Admin MGI'
        ]);

        $newId = (int)$db->lastInsertId();

        // Auto-generate official published PDF document for investor dashboard
        $docType = $module;
        if ($isBilling) $docType = 'billing';
        elseif ($module === 'pembelian') $docType = 'pembelian';
        elseif ($module === 'penjualan') $docType = 'penjualan';
        elseif ($module === 'biaya' || $module === 'labarugi') $docType = 'biaya';

        $docNum = 'DOC-' . strtoupper(substr($docType, 0, 4)) . '-' . date('Ymd') . '-' . $newId;
        $docTitle = ($isBilling ? 'Faktur Alokasi Dana: ' : 'Dokumen Resmi: ') . $title;
        $stmtDoc = $db->prepare("
            INSERT INTO project_documents 
            (project_id, investor_id, doc_number, doc_title, doc_type, file_path, file_size, published_by, status, published_at)
            VALUES (?, ?, ?, ?, ?, ?, '1.5 MB', ?, 'published', NOW())
        ");
        $stmtDoc->execute([$projectId, $investorId, $docNum, $docTitle, $docType, "documents/{$projectId}/{$docNum}.pdf", $admin['name'] ?? 'Operator MGI']);

        // Touch SSE stream file for instant real-time synchronization
        $cacheDir = __DIR__ . '/../../backend/cache';
        if (!is_dir($cacheDir)) {
            @mkdir($cacheDir, 0777, true);
        }
        @file_put_contents($cacheDir . "/project_{$projectId}_stream.txt", time() . '|' . microtime(true));

        // Kirim notifikasi real-time ke dashboard investor
        $notifType = $isBilling ? 'billing' : 'financial';
        $notifTitle = $isBilling ? "Billing Diterbitkan: {$title}" : "Mutasi Pembukuan: {$title}";
        $notifMsg = "Admin telah mencatat " . ($isBilling ? "billing alokasi" : "transaksi") . " senilai Rp " . number_format($amount, 0, ',', '.') . " pada modul " . ucfirst($module) . " (No: {$recordNumber}).";
        createNotification($db, $investorId, $projectId, $notifType, $notifTitle, $notifMsg, "investor-dashboard.html?tab=pembukuan");

        // Log aktivitas admin
        logAdminActivity(
            'create_financial_billing',
            'financial_records',
            (string)$newId,
            "Admin created {$module} record/billing '{$title}' ({$recordNumber}) nominal Rp " . number_format($amount, 0, ',', '.') . " for investor ID {$investorId}"
        );

        // Kirim email notifikasi & dokumen PDF tagihan jika dipilih oleh admin
        $emailSent = false;
        if ($isBilling && !empty($input['send_email_pdf'])) {
            $stmtInv = $db->prepare("SELECT full_name, email FROM investors WHERE id = ?");
            $stmtInv->execute([$investorId]);
            $invData = $stmtInv->fetch();
            if ($invData && !empty($invData['email'])) {
                $invEmail = $invData['email'];
                $invName = $invData['full_name'] ?: 'Investor MGI';
                $fmtNominal = 'Rp ' . number_format($amount, 0, ',', '.');
                $subject = "Pemberitahuan Tagihan Resmi: {$title} ({$recordNumber})";
                $emailHtml = "
                    <div style='font-family: Arial, sans-serif; color: #1e293b; max-width: 600px; margin: 0 auto; padding: 24px; border: 1px solid #e2e8f0; border-radius: 12px;'>
                        <div style='text-align: center; margin-bottom: 24px;'>
                            <h2 style='color: #1e3a8a; margin: 0;'>PT Montana Global Investama</h2>
                            <p style='color: #64748b; font-size: 13px; margin: 4px 0;'>Pemberitahuan Tagihan Digital Resmi &amp; Dokumen Proyek</p>
                        </div>
                        <p>Yth. Bapak/Ibu <strong>" . htmlspecialchars($invName) . "</strong>,</p>
                        <p>Berikut rincian tagihan digital resmi / dokumen alokasi dana proyek yang telah diterbitkan:</p>
                        <table style='width: 100%; border-collapse: collapse; margin: 16px 0; font-size: 14px;'>
                            <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 8px 0; color: #64748b;'>Nomor Tagihan:</td><td style='padding: 8px 0; font-weight: bold;'>" . htmlspecialchars($recordNumber) . "</td></tr>
                            <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 8px 0; color: #64748b;'>Perihal / Judul:</td><td style='padding: 8px 0; font-weight: bold;'>" . htmlspecialchars($title) . "</td></tr>
                            <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 8px 0; color: #64748b;'>Nominal:</td><td style='padding: 8px 0; font-weight: bold; color: #047857; font-size: 16px;'>" . $fmtNominal . "</td></tr>
                            <tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 8px 0; color: #64748b;'>Tanggal Terbit:</td><td style='padding: 8px 0;'>" . htmlspecialchars($transactionDate) . "</td></tr>
                            " . ($dueDate ? "<tr style='border-bottom: 1px solid #e2e8f0;'><td style='padding: 8px 0; color: #64748b;'>Jatuh Tempo:</td><td style='padding: 8px 0; color: #b91c1c;'>" . htmlspecialchars($dueDate) . "</td></tr>" : "") . "
                        </table>
                        <p style='font-size: 13px; color: #475569;'>Dokumen resmi tagihan berformat PDF dapat Anda pratinjau (preview) atau unduh langsung di Portal Investor MGI.</p>
                        <div style='text-align: center; margin: 24px 0;'>
                            <a href='https://montanainvestama.com/investor-dashboard.html?tab=billing' style='background-color: #1e3a8a; color: #ffffff; padding: 12px 24px; text-decoration: none; font-weight: bold; border-radius: 8px; display: inline-block;'>Buka Portal Investor &amp; Unduh Tagihan</a>
                        </div>
                        <hr style='border: 0; border-top: 1px solid #e2e8f0; margin: 24px 0;'/>
                        <p style='font-size: 11px; color: #94a3b8; text-align: center;'>Email ini diterbitkan otomatis oleh Sistem PT Montana Global Investama.</p>
                    </div>
                ";
                $emailSent = @sendMgiEmail($invEmail, $subject, $emailHtml);
            }
        }

        sendJsonResponse([
            'id' => $newId,
            'record_number' => $recordNumber,
            'email_sent' => $emailSent
        ], 201, "Data transaksi & billing '{$title}' berhasil dicatat dan dikirimkan ke investor.");
    }

    // 3. PUT: Update status atau detail transaksi
    if ($method === 'PUT') {
        $id = (int)($input['id'] ?? ($_GET['id'] ?? 0));
        if (!$id) {
            sendJsonError('ID record keuangan wajib disertakan.');
        }

        $stmtCheck = $db->prepare("SELECT * FROM financial_records WHERE id = ?");
        $stmtCheck->execute([$id]);
        $existing = $stmtCheck->fetch();
        if (!$existing) {
            sendJsonError('Data transaksi tidak ditemukan.', 404);
        }

        $title = trim($input['title'] ?? $existing['title']);
        $description = trim($input['description'] ?? $existing['description']);
        $amount = isset($input['amount']) ? cleanAmount($input['amount']) : (float)$existing['amount'];
        $status = trim($input['status'] ?? $existing['status']);
        $billingStatus = trim($input['billing_status'] ?? $existing['billing_status']);
        $vendorClient = trim($input['vendor_client'] ?? $existing['vendor_client']);
        $unitDetail = trim($input['unit_detail'] ?? $existing['unit_detail']);
        $isBilling = isset($input['is_billing']) ? ((int)$input['is_billing'] ? 1 : 0) : (int)$existing['is_billing'];

        $stmtUp = $db->prepare("
            UPDATE financial_records SET
                title = ?, description = ?, amount = ?, status = ?,
                billing_status = ?, vendor_client = ?, unit_detail = ?, is_billing = ?,
                updated_at = NOW()
            WHERE id = ?
        ");
        $stmtUp->execute([
            $title, $description, $amount, $status,
            $billingStatus, $vendorClient, $unitDetail, $isBilling,
            $id
        ]);

        $cacheDir = __DIR__ . '/../../backend/cache';
        if (!is_dir($cacheDir)) {
            @mkdir($cacheDir, 0777, true);
        }
        @file_put_contents($cacheDir . "/project_{$existing['project_id']}_stream.txt", time() . '|' . microtime(true));

        if ($billingStatus !== $existing['billing_status']) {
            $notifTitle = "Status Billing Diperbarui: {$title}";
            $notifMsg = "Status billing {$existing['record_number']} kini telah diubah menjadi " . strtoupper($billingStatus) . ".";
            createNotification($db, (int)$existing['investor_id'], $existing['project_id'], 'billing', $notifTitle, $notifMsg, "investor-dashboard.html?tab=pembukuan");
        }

        logAdminActivity(
            'update_financial_billing',
            'financial_records',
            (string)$id,
            "Admin updated financial record ID {$id}"
        );

        sendJsonResponse([
            'id' => $id,
            'title' => $title,
            'status' => $status
        ], 200, "Transaksi '{$title}' berhasil diperbarui.");
    }

    // 4. DELETE: Hapus transaksi
    if ($method === 'DELETE') {
        $id = (int)($input['id'] ?? ($_GET['id'] ?? 0));
        if (!$id) {
            sendJsonError('ID transaksi wajib disertakan.');
        }

        $stmtCheck = $db->prepare("SELECT title, record_number, project_id FROM financial_records WHERE id = ?");
        $stmtCheck->execute([$id]);
        $existing = $stmtCheck->fetch();
        if (!$existing) {
            sendJsonError('Transaksi tidak ditemukan.', 404);
        }

        $stmtDel = $db->prepare("DELETE FROM financial_records WHERE id = ?");
        $stmtDel->execute([$id]);

        $cacheDir = __DIR__ . '/../../backend/cache';
        if (!is_dir($cacheDir)) {
            @mkdir($cacheDir, 0777, true);
        }
        @file_put_contents($cacheDir . "/project_{$existing['project_id']}_stream.txt", time() . '|' . microtime(true));

        logAdminActivity(
            'delete_financial_billing',
            'financial_records',
            (string)$id,
            "Admin deleted financial record '{$existing['title']}' ({$existing['record_number']})"
        );

        sendJsonResponse(null, 200, "Transaksi '{$existing['title']}' berhasil dihapus.");
    }

    sendJsonError('Method tidak didukung.', 405);

} catch (Exception $e) {
    sendJsonError('Terjadi kesalahan operasi modul keuangan & billing: ' . $e->getMessage(), 500);
}
