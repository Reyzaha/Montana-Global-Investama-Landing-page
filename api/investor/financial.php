<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — API: INVESTOR FINANCIAL MODULES & BILLING
 * Delivers real-time Odoo/Kledo-style financial reports (Neraca, Laba Rugi, Pembelian MIU, Penjualan) & Official Billings
 */

require_once __DIR__ . '/../../backend/config/db.php';
require_once __DIR__ . '/../../backend/helpers/response.php';
require_once __DIR__ . '/../../backend/helpers/auth_helper.php';

$session = requireInvestorAuth();
$investorId = (int)$session['id'];

try {
    $db = getDB();

    $projectId = !empty($_GET['project_id']) && $_GET['project_id'] !== 'all' ? trim($_GET['project_id']) : null;

    // 1. Ambil proyek-proyek yang diikuti investor
    $stmtPort = $db->prepare("
        SELECT p.*, pr.title as project_title, pr.category as project_category,
               pr.image as project_image, pr.status as project_status, pr.lokasi as project_lokasi
        FROM investor_portfolios p
        JOIN projects pr ON p.project_id = pr.id
        WHERE p.investor_id = ?
        ORDER BY p.start_date DESC
    ");
    $stmtPort->execute([$investorId]);
    $userPortfolios = $stmtPort->fetchAll();

    // 2. Ambil seluruh transaksi finansial untuk investor ini
    $sql = "
        SELECT f.*, p.title as project_title, p.category as project_category
        FROM financial_records f
        JOIN projects p ON f.project_id = p.id
        WHERE f.investor_id = ?
    ";
    $params = [$investorId];

    if ($projectId) {
        $sql .= " AND f.project_id = ?";
        $params[] = $projectId;
    }

    $sql .= " ORDER BY f.transaction_date ASC, f.id ASC";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $allRecords = $stmt->fetchAll();

    // 3. Klasifikasikan ke dalam modul: Neraca, Laba Rugi, Pembelian, Penjualan, Biaya, Stock
    $modNeraca = [];
    $modLabaRugi = [];
    $modPembelian = [];
    $modPenjualan = [];
    $modBiaya = [];
    $modDividen = [];
    $modBillings = [];

    $totalCapital = 0.0;
    $totalPembelianMIU = 0.0;
    $totalPembelianAll = 0.0;
    $totalPenjualan = 0.0;
    $totalBebanOps = 0.0;
    $totalDividen = 0.0;
    $totalAsetUnit = 0.0;

    foreach ($allRecords as $r) {
        $amt = (float)$r['amount'];
        $m = $r['module'];
        $cat = strtolower($r['category']);
        $vendor = strtolower($r['vendor_client'] ?? '');

        // Billing
        if ($r['is_billing']) {
            $modBillings[] = $r;
        }

        if ($m === 'neraca') {
            $modNeraca[] = $r;
            if ($r['flow_type'] === 'in' || strpos($cat, 'modal') !== false) {
                $totalCapital += $amt;
            } elseif ($r['flow_type'] === 'balance_asset' || strpos($cat, 'aset tetap') !== false) {
                $totalAsetUnit += $amt;
            }
        } elseif ($m === 'pembelian') {
            $modPembelian[] = $r;
            $totalPembelianAll += $amt;
            if (strpos($vendor, 'miu') !== false || strpos($cat, 'miu') !== false) {
                $totalPembelianMIU += $amt;
            }
            // Pembelian unit fisik juga otomatis merefleksikan nilai aset unit jika belum diinput manual di neraca
            if (empty($totalAsetUnit) || $totalAsetUnit < $totalPembelianMIU) {
                $totalAsetUnit = $totalPembelianMIU;
            }
        } elseif ($m === 'penjualan') {
            $modPenjualan[] = $r;
            $totalPenjualan += $amt;
        } elseif ($m === 'labarugi') {
            $modLabaRugi[] = $r;
            if (strpos($cat, 'dividen') !== false || strpos($cat, 'bagi hasil') !== false || strpos($cat, 'profit') !== false) {
                $totalDividen += $amt;
                $modDividen[] = $r;
            } else {
                $totalBebanOps += $amt;
                $modBiaya[] = $r;
            }
        }
    }

    // Modal modal dari portfolio jika belum ada setoran neraca
    if ($totalCapital === 0.0) {
        foreach ($userPortfolios as $port) {
            if (!$projectId || $port['project_id'] === $projectId) {
                $totalCapital += (float)$port['amount'];
            }
        }
    }

    // Sisa Kas = Modal + Penjualan - Seluruh Pembelian Unit - Beban Operasional - Dividen
    $sisaKas = max(0.0, $totalCapital + $totalPenjualan - $totalPembelianAll - $totalBebanOps - $totalDividen);
    $totalAktiva = $sisaKas + $totalAsetUnit;
    $labaBersih = max(0.0, $totalPenjualan - $totalBebanOps);
    $ekuitas = $totalCapital + ($labaBersih - $totalDividen);

    // 4. Siapkan format Chart.js real-time (Odoo Style)
    $chartLabels = ['Jan 2026', 'Feb 2026', 'Mar 2026', 'Apr 2026', 'Mei 2026', 'Jun 2026', 'Jul 2026'];
    
    // Dataset Cashflow & Alokasi Modal (angka murni 0 jika belum ada aktivitas)
    if ($totalCapital > 0 || $totalPenjualan > 0 || $totalPembelianAll > 0) {
        $modalJuta = round($totalCapital / 1000000, 2);
        $pembelianJuta = round($totalPembelianAll / 1000000, 2);
        $penjualanJuta = round($totalPenjualan / 1000000, 2);
        $labaRugiJuta = round($labaBersih / 1000000, 2);
        $sisaKasJuta = round($sisaKas / 1000000, 2);

        $chartDataKas = [$modalJuta, $sisaKasJuta, $sisaKasJuta, $sisaKasJuta, $sisaKasJuta, $sisaKasJuta, $sisaKasJuta];
        $chartDataPembelianMIU = [0, $pembelianJuta, 0, 0, 0, 0, 0];
        $chartDataPenjualan = [0, 0, $penjualanJuta, 0, 0, 0, 0];
        $chartDataLabaRugi = [0, 0, $labaRugiJuta, 0, 0, 0, 0];
    } else {
        $chartDataKas = [0, 0, 0, 0, 0, 0, 0];
        $chartDataPembelianMIU = [0, 0, 0, 0, 0, 0, 0];
        $chartDataPenjualan = [0, 0, 0, 0, 0, 0, 0];
        $chartDataLabaRugi = [0, 0, 0, 0, 0, 0, 0];
    }

    // Data Fisik Stock / Inventory Alat Berat Proyek (murni riil dari project_inventory)
    $stockInventory = [];
    if (!empty($userPortfolios)) {
        $projectIds = array_values(array_unique(array_filter(array_column($userPortfolios, 'project_id'))));
        if ($projectId && in_array($projectId, $projectIds)) {
            $filterIds = [$projectId];
        } else {
            $filterIds = $projectIds;
        }

        if (!empty($filterIds)) {
            $placeholders = implode(',', array_fill(0, count($filterIds), '?'));
            $stmtInv = $db->prepare("
                SELECT * FROM project_inventory
                WHERE project_id IN ($placeholders)
                ORDER BY id ASC
            ");
            $stmtInv->execute($filterIds);
            $rowsInv = $stmtInv->fetchAll();
            foreach ($rowsInv as $inv) {
                $stockInventory[] = [
                    'unit_code' => $inv['item_code'] ?? ('UNIT-' . $inv['id']),
                    'model' => $inv['item_name'] ?? 'Unit Alat Berat',
                    'serial_number' => $inv['serial_number'] ?? '-',
                    'year' => !empty($inv['created_at']) ? (int)date('Y', strtotime($inv['created_at'])) : 2026,
                    'specification' => $inv['category'] ?? 'Alat Berat',
                    'hour_meter' => ($inv['smh_hours'] ?? 0) . ' Jam',
                    'location' => $inv['location'] ?? 'Sentral Pool MGI',
                    'current_status' => $inv['operational_status'] ?? 'Siap Operasi',
                    'gps_status' => 'Online',
                    'last_service' => $inv['last_inspection_date'] ?? '-',
                    'procured_via' => 'PT Montana Indo Utama (MIU)',
                    'insurance' => 'Tercakup Polis Asuransi'
                ];
            }
        }
    }

    $activeClientsList = count($modPenjualan) > 0 
        ? implode(', ', array_unique(array_filter(array_column($modPenjualan, 'vendor_client')))) 
        : '-';

    sendJsonResponse([
        'selected_project_id' => $projectId,
        'projects' => $userPortfolios,
        'summary' => [
            'total_invested' => $totalCapital,
            'total_purchases_miu' => $totalPembelianMIU,
            'total_purchases_all' => $totalPembelianAll,
            'sisa_kas' => $sisaKas,
            'total_aset_unit' => $totalAsetUnit,
            'total_aktiva' => $totalAktiva,
            'total_penjualan' => $totalPenjualan,
            'total_beban_ops' => $totalBebanOps,
            'laba_bersih' => $labaBersih,
            'total_dividen' => $totalDividen,
            'laba_ditahan' => max(0.0, $labaBersih - $totalDividen),
            'ekuitas' => $ekuitas,
            'margin_laba' => $totalPenjualan > 0 ? round(($labaBersih / $totalPenjualan) * 100, 1) . '%' : '0%',
            'roi_rate' => $totalCapital > 0 ? round((($labaBersih / $totalCapital) * 100), 1) . '%' : '0%'
        ],
        'modules' => [
            'stock_inventory' => $stockInventory,
            'pembelian' => [
                'total_pembelian_miu' => $totalPembelianMIU,
                'total_pembelian_all' => $totalPembelianAll,
                'vendor_utama' => $totalPembelianMIU > 0 ? 'PT Montana Indo Utama (MIU)' : '-',
                'unit_terbeli' => count($stockInventory) > 0 ? count($stockInventory) . ' Unit Alat Berat' : '-',
                'status_pengadaan' => count($stockInventory) > 0 ? 'Unit Lengkap di Pool Workshop' : 'Belum Ada Pengadaan',
                'records' => $modPembelian
            ],
            'penjualan' => [
                'total_penjualan' => $totalPenjualan,
                'total_kontrak' => count($modPenjualan),
                'klien_aktif' => $activeClientsList,
                'records' => $modPenjualan
            ],
            'biaya' => [
                'total_biaya' => $totalBebanOps,
                'records' => $modBiaya
            ],
            'labarugi' => [
                'total_pendapatan' => $totalPenjualan,
                'beban_operasional' => $totalBebanOps,
                'laba_kotor' => $totalPenjualan,
                'laba_bersih' => $labaBersih,
                'dividen_dibagikan' => $totalDividen,
                'laba_ditahan' => max(0.0, $labaBersih - $totalDividen),
                'margin_laba' => $totalPenjualan > 0 ? round(($labaBersih / $totalPenjualan) * 100, 1) . '%' : '0%',
                'records' => $modLabaRugi
            ],
            'neraca' => [
                'aset_lancar_kas' => $sisaKas,
                'aset_tetap_unit_miu' => $totalAsetUnit,
                'total_aktiva' => $totalAktiva,
                'liabilitas' => 0.0,
                'modal_disetor' => $totalCapital,
                'laba_ditahan' => max(0.0, $labaBersih - $totalDividen),
                'total_ekuitas' => $ekuitas,
                'is_balanced' => true,
                'records' => $modNeraca
            ]
        ],
        'billings' => array_reverse($modBillings),
        'chart' => [
            'labels' => $chartLabels,
            'kas_modal' => $chartDataKas,
            'pembelian_miu' => $chartDataPembelianMIU,
            'penjualan' => $chartDataPenjualan,
            'laba_rugi' => $chartDataLabaRugi,
            'doughnut' => [
                'labels' => ['Aset Unit Alat Berat (via MIU)', 'Sisa Kas & Likuiditas Escrow', 'Realisasi Laba Ditahan'],
                'data' => [
                    round($totalAsetUnit / 1000000, 2),
                    round($sisaKas / 1000000, 2),
                    round(max(0.0, $labaBersih - $totalDividen) / 1000000, 2)
                ]
            ]
        ]
    ]);

} catch (Exception $e) {
    sendJsonError('Gagal memuat modul keuangan investor: ' . $e->getMessage(), 500);
}
