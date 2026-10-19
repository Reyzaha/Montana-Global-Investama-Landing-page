<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — API: PROJECTS ROUTER
 * Handles:
 * - GET  /api/projects/:id/dashboard
 * - POST /api/projects/:id/billing
 * - POST /api/projects/:id/purchases
 * - POST /api/projects/:id/sales
 * - POST /api/projects/:id/costs
 * - POST /api/projects/:id/inventory
 * - GET  /api/projects/:id/documents
 * - POST /api/projects/:id/documents
 * - GET  /api/projects/:id/stream (SSE)
 */

require_once __DIR__ . '/../../backend/config/db.php';
require_once __DIR__ . '/../../backend/helpers/response.php';
require_once __DIR__ . '/../../backend/helpers/auth_helper.php';

$projectId = trim($_GET['project_id'] ?? ($_GET['id'] ?? ''));
$action = trim($_GET['action'] ?? '');
$method = $_SERVER['REQUEST_METHOD'];

if (empty($projectId)) {
    sendJsonError('Project ID wajib diisi.', 400);
}

// Helper to clean numeric amounts
function cleanAmount(mixed $val): float {
    if (is_null($val)) return 0.0;
    if (is_numeric($val)) return (float)$val;
    $clean = preg_replace('/[^0-9.]/', '', (string)$val);
    return (float)$clean;
}

// Touch last updated timestamp file for real-time SSE stream detection
function touchProjectUpdate(string $pId): void {
    $dir = __DIR__ . '/../../backend/cache';
    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }
    @file_put_contents($dir . "/project_{$pId}_stream.txt", time() . '|' . microtime(true));
}

// Fallback to static JSON if database is offline or project is not yet in DB
function serveStaticDashboardFallback(string $projectId, ?array $investor = null): void {
    $staticFile = __DIR__ . '/../../data/projects.json';
    if (!file_exists($staticFile)) {
        sendJsonError('Project data tidak ditemukan.', 404);
    }
    $raw = json_decode(file_get_contents($staticFile), true);
    $found = null;
    foreach ($raw['projects'] ?? [] as $pj) {
        if ($pj['id'] === $projectId) {
            $found = $pj;
            break;
        }
    }
    if (!$found && !empty($raw['projects'])) {
        $found = $raw['projects'][0];
    }
    if (!$found) {
        sendJsonError("Proyek {$projectId} tidak ditemukan.", 404);
    }

    $fnd = $found['funding'] ?? [];
    $target = (float)($fnd['target'] ?? 45000000000);
    $collected = (float)($fnd['collected'] ?? 0);
    $rabExecutive = $found['detail']['rab_executive'] ?? null;
    $fundingItems = [];
    foreach ($found['detail']['funding_target']['rows'] ?? [] as $idx => $r) {
        $fundingItems[] = [
            'no_urut' => $idx + 1,
            'item_name' => $r['item'] ?? ('Alokasi Anggaran ' . ($idx + 1)),
            'quantity' => $r['volume'] ?? '1 Paket',
            'unit_price' => (float)($r['harga_satuan'] ?? 0),
            'total' => (float)($r['total'] ?? 0)
        ];
    }

    $documents = [
        [
            'id' => 1,
            'doc_number' => 'DOC-RAB-' . strtoupper($found['id']),
            'doc_title' => 'Rencana Anggaran Biaya (RAB) Eksekutif: ' . $found['title'],
            'doc_type' => 'rab',
            'file_path' => 'prospektus.html',
            'file_size' => '1.8 MB',
            'published_by' => 'Komite Investasi MGI',
            'status' => 'published',
            'published_at' => '2026-09-15'
        ],
        [
            'id' => 2,
            'doc_number' => 'DOC-PROSP-' . strtoupper($found['id']),
            'doc_title' => 'Memorandum Informasi & Prospektus Penawaran Investasi',
            'doc_type' => 'prospectus',
            'file_path' => 'prospektus.html',
            'file_size' => '3.4 MB',
            'published_by' => 'Legal & Compliance MGI',
            'status' => 'published',
            'published_at' => '2026-09-15'
        ]
    ];

    sendJsonResponse([
        'is_preview' => true,
        'preview_notice' => 'Transparansi Format Pembukuan Riil Proyek (Hadir Otomatis Saat Penempatan Dana)',
        'project' => [
            'id' => $found['id'],
            'title' => $found['title'],
            'category' => $found['category'] ?? 'Pengadaan & Perputaran Unit Alat Berat',
            'lokasi' => $found['info']['lokasi'] ?? 'DKI Jakarta & Jawa Barat',
            'image' => $found['image'] ?? 'assets/img/1.png',
            'status' => $found['status'] ?? 'Open',
            'is_preview' => true,
            'contract_number' => 'MGI/PRJ/' . date('Y') . '/' . strtoupper(substr(md5($found['id']), 0, 4)),
            'tenor' => $found['info']['tenor'] ?? '12 Bulan',
            'return_rate' => $found['info']['return'] ?? '≥30% (p.a.)',
            'funding_target' => $target,
            'funding_collected' => $collected
        ],
        'rab_executive' => $rabExecutive,
        'funding_items' => $fundingItems,
        'financial_summary' => [
            'total_dana_investor' => 0,
            'total_pembelian' => 0,
            'total_biaya' => 0,
            'total_terpakai' => 0,
            'sisa_dana' => 0,
            'total_penjualan' => 0,
            'laba_bersih' => 0,
            'profit_diterima' => 0,
            'dividen_diterima' => 0,
            'efisiensi_opex' => '0%'
        ],
        'inventory' => [
            'total_units' => 0,
            'active_units' => 0,
            'standby_units' => 0,
            'total_asset_value' => 0,
            'items' => []
        ],
        'purchases' => [],
        'sales' => [],
        'costs' => [],
        'billing' => [
            'total_allocated' => 0,
            'remaining_cash' => 0,
            'records' => []
        ],
        'neraca' => [
            'aktiva' => [
                'kas_escrow' => 0,
                'piutang_usaha' => 0,
                'total_aset_lancar' => 0,
                'aset_tetap_bruto' => 0,
                'akumulasi_penyusutan' => 0,
                'nilai_buku_aset_tetap' => 0,
                'total_aktiva' => 0
            ],
            'pasiva' => [
                'kewajiban_lancar' => 0,
                'modal_investor' => 0,
                'saldo_laba_ditahan' => 0,
                'laba_berjalan' => 0,
                'total_pasiva' => 0,
                'is_balanced' => true
            ]
        ],
        'laba_rugi' => [
            'pendapatan' => [
                'penjualan_alat_berat' => 0,
                'pendapatan_lain' => 0,
                'total_pendapatan' => 0
            ],
            'beban' => [
                'impor_customs_miu' => 0,
                'workshop_perawatan' => 0,
                'total_beban' => 0
            ],
            'laba_bersih_operasional' => 0,
            'margin_persen' => 0,
            'profit' => [
                'realisasi_diterima' => 0,
                'estimasi_kuartal_berikutnya' => 0,
                'jadwal_berikutnya' => '-',
                'target_roi_tahunan' => $found['info']['return'] ?? '≥30% (p.a.)'
            ],
            'dividen' => [
                'realisasi_diterima' => 0,
                'estimasi_kuartal_berikutnya' => 0,
                'jadwal_berikutnya' => '-',
                'target_roi_tahunan' => $found['info']['return'] ?? '≥30% (p.a.)'
            ]
        ],
        'documents' => $documents,
        'has_invested' => false,
        'charts' => [
            'labels' => ['Jan 2026', 'Feb 2026', 'Mar 2026', 'Apr 2026', 'Mei 2026', 'Jun 2026', 'Jul 2026', 'Agu 2026', 'Sep 2026'],
            'pembelian' => [0, 0, 0, 0, 0, 0, 0, 0, 0],
            'penjualan' => [0, 0, 0, 0, 0, 0, 0, 0, 0],
            'biaya' => [0, 0, 0, 0, 0, 0, 0, 0, 0],
            'sisa_dana' => [0, 0, 0, 0, 0, 0, 0, 0, 0]
        ]
    ]);
}

function serveStaticDocumentsFallback(string $projectId): void {
    $docs = [
        [
            'id' => 1,
            'doc_number' => 'DOC-RAB-' . strtoupper($projectId),
            'doc_title' => 'Rencana Anggaran Biaya (RAB) Eksekutif Proyek',
            'doc_type' => 'rab',
            'file_path' => 'prospektus.html',
            'file_size' => '1.8 MB',
            'published_by' => 'Komite Investasi MGI',
            'status' => 'published',
            'published_at' => '2026-09-15'
        ],
        [
            'id' => 2,
            'doc_number' => 'DOC-PROSP-' . strtoupper($projectId),
            'doc_title' => 'Memorandum Informasi & Prospektus Penawaran Investasi',
            'doc_type' => 'prospectus',
            'file_path' => 'prospektus.html',
            'file_size' => '3.4 MB',
            'published_by' => 'Legal & Compliance MGI',
            'status' => 'published',
            'published_at' => '2026-09-15'
        ]
    ];
    sendJsonResponse([
        'project_id' => $projectId,
        'documents' => $docs
    ]);
}

try {
    $db = getDB();

    // Verify project exists
    $stmtP = $db->prepare("SELECT * FROM projects WHERE id = ?");
    $stmtP->execute([$projectId]);
    $project = $stmtP->fetch();

    if (!$project) {
        if ($action === 'dashboard') {
            serveStaticDashboardFallback($projectId);
            exit;
        }
        if ($action === 'documents' && $method === 'GET') {
            serveStaticDocumentsFallback($projectId);
            exit;
        }
        sendJsonError("Proyek dengan ID '{$projectId}' tidak ditemukan.", 404);
    }

    // Determine current user session (Investor or Admin/Operator)
    $investor = getInvestorSession();
    $admin = getAdminSession();

    // -------------------------------------------------------------
    // ACTION 1: SSE REAL-TIME STREAM (GET /api/projects/:id/stream)
    // -------------------------------------------------------------
    if ($action === 'stream') {
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no');

        $cacheFile = __DIR__ . "/../../backend/cache/project_{$projectId}_stream.txt";
        $lastChecked = isset($_GET['last_time']) ? (float)$_GET['last_time'] : microtime(true);

        // Send initial heartbeat
        echo "event: ping\n";
        echo "data: " . json_encode(['time' => time()]) . "\n\n";
        @ob_flush();
        @flush();

        $startLoop = time();
        while (time() - $startLoop < 25) { // Keep connection open for max 25s, browser will auto-reconnect
            if (file_exists($cacheFile)) {
                $content = trim(file_get_contents($cacheFile));
                $parts = explode('|', $content);
                $fileTime = isset($parts[1]) ? (float)$parts[1] : (float)$parts[0];
                if ($fileTime > $lastChecked) {
                    echo "event: update\n";
                    echo "data: " . json_encode(['type' => 'project_updated', 'project_id' => $projectId, 'timestamp' => $fileTime]) . "\n\n";
                    @ob_flush();
                    @flush();
                    exit;
                }
            }
            usleep(500000); // 0.5s pause
        }

        echo "event: ping\n";
        echo "data: " . json_encode(['time' => time()]) . "\n\n";
        @ob_flush();
        @flush();
        exit;
    }

    // -------------------------------------------------------------
    // ACTION 2: DASHBOARD DATA (GET /api/projects/:id/dashboard)
    // -------------------------------------------------------------
    if ($action === 'dashboard') {
        $investorId = $investor ? (int)$investor['id'] : (!empty($_GET['investor_id']) ? (int)$_GET['investor_id'] : null);

        // Portfolio info for this investor
        $portfolio = null;
        if ($investorId) {
            $stmtPort = $db->prepare("SELECT * FROM investor_portfolios WHERE project_id = ? AND investor_id = ? LIMIT 1");
            $stmtPort->execute([$projectId, $investorId]);
            $portfolio = $stmtPort->fetch();
        }

        $hasInvested = ($portfolio !== false && $portfolio !== null && (float)($portfolio['amount'] ?? 0) > 0);
        $isPreview = !$hasInvested;

        if ($hasInvested) {
            $totalCapital = (float)$portfolio['amount'];
            $contractNumber = !empty($portfolio['contract_number']) ? $portfolio['contract_number'] : ('MGI/INV/' . date('Y') . '/' . str_pad($investorId, 3, '0', STR_PAD_LEFT) . '-BP');
            $payoutReceived = isset($portfolio['payout_received']) ? (float)$portfolio['payout_received'] : 0.0;

            // Inventory items allocated for this project
            $stmtInv = $db->prepare("SELECT * FROM project_inventory WHERE project_id = ? ORDER BY id ASC");
            $stmtInv->execute([$projectId]);
            $inventoryList = $stmtInv->fetchAll();

            // Financial records for this investor on this project
            $stmtFin = $db->prepare("SELECT * FROM financial_records WHERE project_id = ? AND investor_id = ? ORDER BY transaction_date ASC, id ASC");
            $stmtFin->execute([$projectId, $investorId]);
            $allFin = $stmtFin->fetchAll();

            $purchases = [];
            $sales = [];
            $costs = [];
            $billings = [];

            $totalPembelian = 0.0;
            $totalPenjualan = 0.0;
            $totalBiaya = 0.0;

            foreach ($allFin as $f) {
                $amt = (float)$f['amount'];
                $m = strtolower($f['module']);
                $cat = strtolower($f['category']);
                $flow = strtolower($f['flow_type']);

                if ($f['is_billing']) {
                    $billings[] = $f;
                }

                if ($m === 'pembelian') {
                    $purchases[] = $f;
                    $totalPembelian += $amt;
                } elseif ($m === 'penjualan') {
                    $sales[] = $f;
                    $totalPenjualan += $amt;
                } elseif ($m === 'biaya' || ($m === 'labarugi' && $flow === 'out' && strpos($cat, 'dividen') === false && strpos($cat, 'profit') === false && strpos($cat, 'bagi hasil') === false)) {
                    $costs[] = $f;
                    $totalBiaya += $amt;
                }
            }

            $totalTerpakai = $totalPembelian + $totalBiaya;
            $sisaDana = max(0, $totalCapital - $totalTerpakai);
            $labaBersih = $totalPenjualan - $totalBiaya;

            // Documents for this investor & project
            $stmtDocs = $db->prepare("SELECT * FROM project_documents WHERE project_id = ? AND (investor_id = ? OR investor_id IS NULL) AND status = 'published' ORDER BY published_at DESC, id DESC");
            $stmtDocs->execute([$projectId, $investorId]);
            $documents = $stmtDocs->fetchAll();

            // Inventory summary stats
            $totalUnits = count($inventoryList);
            $activeUnits = 0;
            $standbyUnits = 0;
            $totalAssetValue = 0.0;
            foreach ($inventoryList as $inv) {
                if ($inv['operational_status'] === 'Aktif Beroperasi') $activeUnits++;
                else $standbyUnits++;
                $totalAssetValue += (float)$inv['total_value'];
            }
        } else {
            // Investor baru yang belum menempatkan modal: seluruh modul pembukuan kosong / 0
            $totalCapital = 0.0;
            $contractNumber = '-';
            $payoutReceived = 0.0;
            $inventoryList = [];
            $allFin = [];
            $purchases = [];
            $sales = [];
            $costs = [];
            $billings = [];
            $totalPembelian = 0.0;
            $totalPenjualan = 0.0;
            $totalBiaya = 0.0;
            $totalTerpakai = 0.0;
            $sisaDana = 0.0;
            $labaBersih = 0.0;
            $totalUnits = 0;
            $activeUnits = 0;
            $standbyUnits = 0;
            $totalAssetValue = 0.0;

            // Dokumen proposal/RAB acuan saja (bukan faktur transaksi privat)
            $stmtDocs = $db->prepare("SELECT * FROM project_documents WHERE project_id = ? AND investor_id IS NULL AND status = 'published' ORDER BY published_at DESC, id DESC");
            $stmtDocs->execute([$projectId]);
            $documents = $stmtDocs->fetchAll();
        }

        // Structured Balance Sheet (Neraca)
        $aktivaLancar = $sisaDana;
        $asetTetapBruto = $totalAssetValue;
        $akumulasiPenyusutan = $asetTetapBruto > 0 ? round($asetTetapBruto * 0.05) : 0;
        $nilaiBukuAset = max(0, $asetTetapBruto - $akumulasiPenyusutan);
        $totalAktiva = $aktivaLancar + $nilaiBukuAset;

        $kewajiban = 0;
        $ekuitasModal = $totalCapital;
        $saldoLaba = max(0, $totalAktiva - $ekuitasModal);
        $totalPasiva = $kewajiban + $ekuitasModal + $saldoLaba;

        $neracaData = [
            'aktiva' => [
                'kas_escrow' => $aktivaLancar,
                'piutang_usaha' => 0,
                'total_aset_lancar' => $aktivaLancar,
                'aset_tetap_bruto' => $asetTetapBruto,
                'akumulasi_penyusutan' => $akumulasiPenyusutan,
                'nilai_buku_aset_tetap' => $nilaiBukuAset,
                'total_aktiva' => $totalAktiva
            ],
            'pasiva' => [
                'kewajiban_lancar' => $kewajiban,
                'modal_investor' => $ekuitasModal,
                'saldo_laba_ditahan' => $saldoLaba,
                'laba_berjalan' => $labaBersih,
                'total_pasiva' => $totalPasiva,
                'is_balanced' => true
            ]
        ];

        // Structured Income Statement (Laba Rugi)
        $labaRugiData = [
            'pendapatan' => [
                'penjualan_alat_berat' => $totalPenjualan,
                'pendapatan_lain' => 0,
                'total_pendapatan' => $totalPenjualan
            ],
            'beban' => [
                'impor_customs_miu' => round($totalBiaya * 0.45),
                'workshop_perawatan' => round($totalBiaya * 0.55),
                'total_beban' => $totalBiaya
            ],
            'laba_bersih_operasional' => $labaBersih,
            'margin_persen' => $totalPenjualan > 0 ? round(($labaBersih / $totalPenjualan) * 100, 1) : 0,
            'profit' => [
                'realisasi_diterima' => $payoutReceived,
                'estimasi_kuartal_berikutnya' => $totalCapital > 0 ? round($totalCapital * 0.08) : 0,
                'jadwal_berikutnya' => $totalCapital > 0 ? '15 Oktober 2026' : '-',
                'target_roi_tahunan' => $project['return_rate'] ?: '≥32% (p.a.)'
            ],
            'dividen' => [
                'realisasi_diterima' => $payoutReceived,
                'estimasi_kuartal_berikutnya' => $totalCapital > 0 ? round($totalCapital * 0.08) : 0,
                'jadwal_berikutnya' => $totalCapital > 0 ? '15 Oktober 2026' : '-',
                'target_roi_tahunan' => $project['return_rate'] ?: '≥32% (p.a.)'
            ]
        ];

        // Fetch RAB Executive & Funding Items
        $stmtDet = $db->prepare("SELECT rab_executive_json FROM project_details WHERE project_id = ? LIMIT 1");
        $stmtDet->execute([$projectId]);
        $detRow = $stmtDet->fetch();
        $rabExecutive = null;
        if (!empty($detRow['rab_executive_json'])) {
            $rabExecutive = json_decode($detRow['rab_executive_json'], true);
        }
        if (!$rabExecutive) {
            $jsonFile = __DIR__ . '/../../data/projects.json';
            if (file_exists($jsonFile)) {
                $rawP = json_decode(file_get_contents($jsonFile), true);
                foreach ($rawP['projects'] ?? [] as $pj) {
                    if ($pj['id'] === $projectId && !empty($pj['detail']['rab_executive'])) {
                        $rabExecutive = $pj['detail']['rab_executive'];
                        break;
                    }
                }
            }
        }

        $stmtItems = $db->prepare("SELECT no_urut, item_name, quantity, unit_price, total FROM funding_items WHERE project_id = ? ORDER BY no_urut ASC");
        $stmtItems->execute([$projectId]);
        $fundingItems = $stmtItems->fetchAll();

        // Return comprehensive dashboard package
        sendJsonResponse([
            'is_preview' => $isPreview,
            'preview_notice' => $isPreview ? 'Transparansi Format Pembukuan Riil Proyek (Hadir Otomatis Saat Penempatan Dana)' : null,
            'project' => [
                'id' => $project['id'],
                'title' => $project['title'],
                'category' => $project['category'],
                'lokasi' => $project['lokasi'],
                'image' => $project['image'],
                'status' => $project['status'],
                'is_preview' => $isPreview,
                'contract_number' => $contractNumber,
                'tenor' => $project['tenor'] ?: '36 Bulan',
                'return_rate' => $project['return_rate'] ?: '≥32% (p.a.)',
                'funding_target' => (float)$project['funding_target'],
                'funding_collected' => (float)$project['funding_collected']
            ],
            'rab_executive' => $rabExecutive,
            'funding_items' => $fundingItems,
            'financial_summary' => [
                'total_dana_investor' => $totalCapital,
                'total_pembelian' => $totalPembelian,
                'total_biaya' => $totalBiaya,
                'total_terpakai' => $totalTerpakai,
                'sisa_dana' => $sisaDana,
                'total_penjualan' => $totalPenjualan,
                'laba_bersih' => $labaBersih,
                'profit_diterima' => $payoutReceived,
                'dividen_diterima' => $payoutReceived,
                'efisiensi_opex' => $totalPenjualan > 0 ? round(($totalBiaya / $totalPenjualan) * 100, 1) . '%' : '0%'
            ],
            'inventory' => [
                'total_units' => $totalUnits,
                'active_units' => $activeUnits,
                'standby_units' => $standbyUnits,
                'total_asset_value' => $totalAssetValue,
                'items' => $inventoryList
            ],
            'purchases' => $purchases,
            'sales' => $sales,
            'costs' => $costs,
            'billing' => [
                'total_allocated' => $totalTerpakai,
                'remaining_cash' => $sisaDana,
                'records' => $billings
            ],
            'neraca' => $neracaData,
            'laba_rugi' => $labaRugiData,
            'documents' => $documents,
            'has_invested' => $hasInvested,
            'charts' => [
                'labels' => ['Jan 2026', 'Feb 2026', 'Mar 2026', 'Apr 2026', 'Mei 2026', 'Jun 2026', 'Jul 2026', 'Agu 2026', 'Sep 2026'],
                'pembelian' => $hasInvested ? [$totalPembelian > 0 ? round($totalPembelian / 1000000) : 0, 0, 0, 0, 0, 0, 0, 0, 0] : [0, 0, 0, 0, 0, 0, 0, 0, 0],
                'penjualan' => $hasInvested ? [0, 0, $totalPenjualan > 0 ? round($totalPenjualan / 1000000) : 0, 0, 0, 0, 0, 0, 0] : [0, 0, 0, 0, 0, 0, 0, 0, 0],
                'biaya' => $hasInvested ? [0, 0, $totalBiaya > 0 ? round($totalBiaya / 1000000) : 0, 0, 0, 0, 0, 0, 0] : [0, 0, 0, 0, 0, 0, 0, 0, 0],
                'sisa_dana' => $hasInvested ? [round($totalCapital / 1000000), round($sisaDana / 1000000), round($sisaDana / 1000000), round($sisaDana / 1000000), round($sisaDana / 1000000), round($sisaDana / 1000000), round($sisaDana / 1000000), round($sisaDana / 1000000), round($sisaDana / 1000000)] : [0, 0, 0, 0, 0, 0, 0, 0, 0]
            ]
        ]);
    }

    // -------------------------------------------------------------
    // ACTION 3: DOCUMENTS (GET /api/projects/:id/documents)
    // -------------------------------------------------------------
    if ($action === 'documents' && $method === 'GET') {
        $stmtDocs = $db->prepare("SELECT * FROM project_documents WHERE project_id = ? ORDER BY published_at DESC, id DESC");
        $stmtDocs->execute([$projectId]);
        sendJsonResponse([
            'project_id' => $projectId,
            'documents' => $stmtDocs->fetchAll()
        ]);
    }

    // -------------------------------------------------------------
    // OPERATOR / ADMIN ONLY ACTIONS BELOW (POST)
    // -------------------------------------------------------------
    if ($method === 'POST') {
        $input = getJsonInput();

        // 1. BILLING / ALOKASI DANA (POST /api/projects/:id/billing)
        if ($action === 'billing') {
            $invId = !empty($input['investor_id']) ? (int)$input['investor_id'] : 1;
            $title = trim($input['title'] ?? 'Alokasi Penggunaan Dana Proyek');
            $category = trim($input['category'] ?? 'Alokasi Modal Proyek');
            $vendor = trim($input['vendor_client'] ?? 'PT Montana Indo Utama (MIU)');
            $unitDetail = trim($input['unit_detail'] ?? '');
            $amount = cleanAmount($input['amount'] ?? 0);
            $desc = trim($input['description'] ?? '');
            $date = !empty($input['transaction_date']) ? $input['transaction_date'] : date('Y-m-d');
            $recNum = 'BILL-MGI-' . date('Ymd') . '-' . rand(100, 999);

            if ($amount <= 0) {
                sendJsonError('Nominal alokasi/billing harus lebih besar dari 0.', 422);
            }

            $stmt = $db->prepare("
                INSERT INTO financial_records 
                (investor_id, project_id, module, category, record_number, title, description, amount, flow_type, vendor_client, unit_detail, is_billing, billing_status, transaction_date)
                VALUES (?, ?, 'neraca', ?, ?, ?, ?, ?, 'out', ?, ?, 1, 'settled', ?)
            ");
            $stmt->execute([$invId, $projectId, $category, $recNum, $title, $desc, $amount, $vendor, $unitDetail, $date]);
            $insertedId = $db->lastInsertId();

            // Auto-generate published document record
            $docNum = 'DOC-BILL-' . date('Ymd') . '-' . $insertedId;
            $stmtDoc = $db->prepare("
                INSERT INTO project_documents 
                (project_id, investor_id, doc_number, doc_title, doc_type, file_path, file_size, published_by, status, published_at)
                VALUES (?, ?, ?, ?, 'billing', ?, '1.5 MB', 'Operator MGI', 'published', NOW())
            ");
            $docTitle = "Faktur Billing Alokasi Dana: {$title} (" . number_format($amount, 0, ',', '.') . ")";
            $stmtDoc->execute([$projectId, $invId, $docNum, $docTitle, "documents/{$projectId}/{$docNum}.pdf"]);

            touchProjectUpdate($projectId);
            sendJsonResponse([
                'id' => $insertedId,
                'record_number' => $recNum,
                'message' => 'Billing alokasi dana berhasil dibuat dan diterbitkan ke investor.'
            ], 201);
        }

        // 2. PEMBELIAN (POST /api/projects/:id/purchases)
        if ($action === 'purchases') {
            $invId = !empty($input['investor_id']) ? (int)$input['investor_id'] : 1;
            $title = trim($input['title'] ?? 'Pembelian Unit Alat Berat');
            $category = trim($input['category'] ?? 'Pengadaan Unit (MSI/MIU)');
            $vendor = trim($input['vendor_client'] ?? 'PT Montana Sentra Industri (MSI)');
            $unitDetail = trim($input['unit_detail'] ?? '');
            $amount = cleanAmount($input['amount'] ?? 0);
            $desc = trim($input['description'] ?? '');
            $date = !empty($input['transaction_date']) ? $input['transaction_date'] : date('Y-m-d');
            $recNum = 'BUY-MGI-' . date('Ymd') . '-' . rand(100, 999);

            if ($amount <= 0) {
                sendJsonError('Nominal pembelian harus lebih besar dari 0.', 422);
            }

            $stmt = $db->prepare("
                INSERT INTO financial_records 
                (investor_id, project_id, module, category, record_number, title, description, amount, flow_type, vendor_client, unit_detail, is_billing, transaction_date)
                VALUES (?, ?, 'pembelian', ?, ?, ?, ?, ?, 'out', ?, ?, 1, ?)
            ");
            $stmt->execute([$invId, $projectId, $category, $recNum, $title, $desc, $amount, $vendor, $unitDetail, $date]);
            $insertedId = $db->lastInsertId();

            // Auto-generate published document record
            $docNum = 'DOC-BUY-' . date('Ymd') . '-' . $insertedId;
            $stmtDoc = $db->prepare("
                INSERT INTO project_documents 
                (project_id, investor_id, doc_number, doc_title, doc_type, file_path, file_size, published_by, status, published_at)
                VALUES (?, ?, ?, ?, 'pembelian', ?, '1.8 MB', 'Operator MGI', 'published', NOW())
            ");
            $docTitle = "Faktur Pembelian: {$title} via {$vendor}";
            $stmtDoc->execute([$projectId, $invId, $docNum, $docTitle, "documents/{$projectId}/{$docNum}.pdf"]);

            touchProjectUpdate($projectId);
            sendJsonResponse([
                'id' => $insertedId,
                'record_number' => $recNum,
                'message' => 'Transaksi pembelian berhasil dicatat dan faktur diterbitkan ke dashboard investor.'
            ], 201);
        }

        // 3. PENJUALAN (POST /api/projects/:id/sales)
        if ($action === 'sales') {
            $invId = !empty($input['investor_id']) ? (int)$input['investor_id'] : 1;
            $title = trim($input['title'] ?? 'Kontrak Penjualan Unit Alat Berat');
            $category = trim($input['category'] ?? 'Penjualan Unit Infrastruktur');
            $client = trim($input['vendor_client'] ?? 'Klien Mitra Konstruksi');
            $unitDetail = trim($input['unit_detail'] ?? '');
            $amount = cleanAmount($input['amount'] ?? 0);
            $desc = trim($input['description'] ?? '');
            $date = !empty($input['transaction_date']) ? $input['transaction_date'] : date('Y-m-d');
            $recNum = 'INV-SLS-' . date('Ymd') . '-' . rand(100, 999);

            if ($amount <= 0) {
                sendJsonError('Nominal penjualan harus lebih besar dari 0.', 422);
            }

            $stmt = $db->prepare("
                INSERT INTO financial_records 
                (investor_id, project_id, module, category, record_number, title, description, amount, flow_type, vendor_client, unit_detail, is_billing, transaction_date)
                VALUES (?, ?, 'penjualan', ?, ?, ?, ?, ?, 'in', ?, ?, 0, ?)
            ");
            $stmt->execute([$invId, $projectId, $category, $recNum, $title, $desc, $amount, $client, $unitDetail, $date]);
            $insertedId = $db->lastInsertId();

            // Auto-generate document record
            $docNum = 'DOC-SLS-' . date('Ymd') . '-' . $insertedId;
            $stmtDoc = $db->prepare("
                INSERT INTO project_documents 
                (project_id, investor_id, doc_number, doc_title, doc_type, file_path, file_size, published_by, status, published_at)
                VALUES (?, ?, ?, ?, 'penjualan', ?, '1.3 MB', 'Operator MGI', 'published', NOW())
            ");
            $docTitle = "Invoice Penjualan: {$title} ({$client})";
            $stmtDoc->execute([$projectId, $invId, $docNum, $docTitle, "documents/{$projectId}/{$docNum}.pdf"]);

            touchProjectUpdate($projectId);
            sendJsonResponse([
                'id' => $insertedId,
                'record_number' => $recNum,
                'message' => 'Transaksi penjualan berhasil dicatat dan laporan terbit.'
            ], 201);
        }

        // 4. BIAYA (POST /api/projects/:id/costs)
        if ($action === 'costs') {
            $invId = !empty($input['investor_id']) ? (int)$input['investor_id'] : 1;
            $title = trim($input['title'] ?? 'Biaya Impor & Customs MIU');
            $category = trim($input['category'] ?? 'Biaya Impor / Workshop');
            $vendor = trim($input['vendor_client'] ?? 'PT Montana Indo Utama (MIU)');
            $amount = cleanAmount($input['amount'] ?? 0);
            $desc = trim($input['description'] ?? '');
            $date = !empty($input['transaction_date']) ? $input['transaction_date'] : date('Y-m-d');
            $recNum = 'EXP-' . date('Ymd') . '-' . rand(100, 999);

            if ($amount <= 0) {
                sendJsonError('Nominal biaya harus lebih besar dari 0.', 422);
            }

            $stmt = $db->prepare("
                INSERT INTO financial_records 
                (investor_id, project_id, module, category, record_number, title, description, amount, flow_type, vendor_client, is_billing, transaction_date)
                VALUES (?, ?, 'biaya', ?, ?, ?, ?, ?, 'out', ?, 0, ?)
            ");
            $stmt->execute([$invId, $projectId, $category, $recNum, $title, $desc, $amount, $vendor, $date]);
            $insertedId = $db->lastInsertId();

            // Auto-generate document record
            $docNum = 'DOC-EXP-' . date('Ymd') . '-' . $insertedId;
            $stmtDoc = $db->prepare("
                INSERT INTO project_documents 
                (project_id, investor_id, doc_number, doc_title, doc_type, file_path, file_size, published_by, status, published_at)
                VALUES (?, ?, ?, ?, 'biaya', ?, '1.1 MB', 'Operator MGI', 'published', NOW())
            ");
            $docTitle = "Bukti Pengeluaran Biaya: {$title} via {$vendor}";
            $stmtDoc->execute([$projectId, $invId, $docNum, $docTitle, "documents/{$projectId}/{$docNum}.pdf"]);

            touchProjectUpdate($projectId);
            sendJsonResponse([
                'id' => $insertedId,
                'record_number' => $recNum,
                'message' => 'Biaya berhasil dicatat dan dokumen bukti diterbitkan.'
            ], 201);
        }

        // 5. INVENTORY / STOK (POST /api/projects/:id/inventory)
        if ($action === 'inventory') {
            $itemCode = trim($input['item_code'] ?? ('EXC-' . rand(100, 999)));
            $itemName = trim($input['item_name'] ?? 'Unit Alat Berat Siap Operasi');
            $category = trim($input['category'] ?? 'Alat Berat');
            $sn = trim($input['serial_number'] ?? '');
            $qty = !empty($input['quantity']) ? (int)$input['quantity'] : 1;
            $unitCost = cleanAmount($input['unit_cost'] ?? 0);
            $totalVal = $unitCost * $qty;
            $cond = trim($input['condition_status'] ?? 'Grade A (Prima)');
            $opStatus = trim($input['operational_status'] ?? 'Aktif Beroperasi');
            $loc = trim($input['location'] ?? 'Pool Narogong & Cikarang');
            $smh = !empty($input['smh_hours']) ? (int)$input['smh_hours'] : 0;
            $inspectDate = !empty($input['last_inspection_date']) ? $input['last_inspection_date'] : date('Y-m-d');

            $stmt = $db->prepare("
                INSERT INTO project_inventory 
                (project_id, item_code, item_name, category, serial_number, quantity, unit_cost, total_value, condition_status, operational_status, location, smh_hours, last_inspection_date)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$projectId, $itemCode, $itemName, $category, $sn, $qty, $unitCost, $totalVal, $cond, $opStatus, $loc, $smh, $inspectDate]);
            $insertedId = $db->lastInsertId();

            // Auto-generate document record
            $docNum = 'DOC-INV-' . date('Ymd') . '-' . $insertedId;
            $stmtDoc = $db->prepare("
                INSERT INTO project_documents 
                (project_id, investor_id, doc_number, doc_title, doc_type, file_path, file_size, published_by, status, published_at)
                VALUES (?, NULL, ?, ?, 'inventory', ?, '2.1 MB', 'Operator MGI', 'published', NOW())
            ");
            $docTitle = "Berita Acara Pendaftaran Unit Armada: {$itemName} ({$itemCode})";
            $stmtDoc->execute([$projectId, $docNum, $docTitle, "documents/{$projectId}/{$docNum}.pdf"]);

            touchProjectUpdate($projectId);
            sendJsonResponse([
                'id' => $insertedId,
                'item_code' => $itemCode,
                'message' => 'Data inventaris alat berat berhasil dicatat dan sertifikat terbit.'
            ], 201);
        }

        // 6. GENERATE / TERBITKAN DOKUMEN (POST /api/projects/:id/documents)
        if ($action === 'documents') {
            $invId = !empty($input['investor_id']) ? (int)$input['investor_id'] : 1;
            $docTitle = trim($input['doc_title'] ?? 'Laporan Operasional Resmi MGI');
            $docType = trim($input['doc_type'] ?? 'laporan');
            $docNum = 'DOC-PUB-' . date('Ymd') . '-' . rand(100, 999);
            $filePath = "documents/{$projectId}/{$docNum}.pdf";

            $stmtDoc = $db->prepare("
                INSERT INTO project_documents 
                (project_id, investor_id, doc_number, doc_title, doc_type, file_path, file_size, published_by, status, published_at)
                VALUES (?, ?, ?, ?, ?, ?, '2.4 MB', 'Operator MGI', 'published', NOW())
            ");
            $stmtDoc->execute([$projectId, $invId, $docNum, $docTitle, $docType, $filePath]);
            $insertedId = $db->lastInsertId();

            touchProjectUpdate($projectId);
            sendJsonResponse([
                'id' => $insertedId,
                'doc_number' => $docNum,
                'message' => 'Dokumen PDF resmi berhasil diterbitkan ke investor.'
            ], 201);
        }
    }

    sendJsonError('Endpoint atau method tidak valid.', 404);

} catch (Exception $e) {
    if ($action === 'dashboard') {
        serveStaticDashboardFallback($projectId);
        exit;
    }
    if ($action === 'documents' && $method === 'GET') {
        serveStaticDocumentsFallback($projectId);
        exit;
    }
    sendJsonError('Server Error: ' . $e->getMessage(), 500);
}
