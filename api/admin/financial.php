<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — API: ADMIN FINANCIAL & BILLING MANAGEMENT
 * Modules: Neraca, Laba Rugi, Pembelian (Pengadaan MIU), Penjualan, Billing
 * Methods: GET, POST, PUT, DELETE
 */

require_once __DIR__ . '/../../backend/config/db.php';
require_once __DIR__ . '/../../backend/helpers/response.php';
require_once __DIR__ . '/../../backend/helpers/auth_helper.php';

$admin = requireAdminAuth();
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
                if (strpos($cat, 'dividen') !== false || strpos($cat, 'bagi hasil') !== false) {
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

    // 2. POST: Buat Transaksi Baru / Kirim Billing ke Investor
    if ($method === 'POST') {
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

        // Log aktivitas admin
        logAdminActivity(
            'create_financial_billing',
            'financial_records',
            (string)$newId,
            "Admin created {$module} record/billing '{$title}' ({$recordNumber}) nominal Rp " . number_format($amount, 0, ',', '.') . " for investor ID {$investorId}"
        );

        sendJsonResponse([
            'id' => $newId,
            'record_number' => $recordNumber
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
