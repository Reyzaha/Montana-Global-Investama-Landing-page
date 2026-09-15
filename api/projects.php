<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — API: PROJECTS CATALOG & DETAIL
 * Method: GET
 * Supports: /api/projects.php and /api/projects.php?id=proj-001
 */

require_once __DIR__ . '/../backend/config/db.php';
require_once __DIR__ . '/../backend/helpers/response.php';
require_once __DIR__ . '/../backend/helpers/auth_helper.php';

try {
    $db = getDB();
    $projectId = $_GET['id'] ?? null;
    $investorSession = getInvestorSession();
    $isAuthenticated = ($investorSession !== null);

    // Ambil setting require_auth_for_details dan disclaimer dari settings
    $requireAuth = true;
    $disclaimer = "Target ilustratif berdasarkan proyeksi kinerja, bukan jaminan — hasil aktual mengikuti kinerja riil usaha dan dapat lebih rendah dari target. Investasi pada instrumen sektor riil mengandung risiko fluktuasi pasar dan operasional. Harap membaca seluruh dokumen penawaran dan Risk Disclosure Statement secara cermat.";
    
    try {
        $stmtSet = $db->prepare("SELECT setting_key, setting_value FROM system_settings WHERE setting_key IN ('disclaimer_text', 'require_auth_for_details')");
        $stmtSet->execute();
        $settingsMap = $stmtSet->fetchAll(PDO::FETCH_KEY_PAIR);
        if (!empty($settingsMap['disclaimer_text'])) {
            $disclaimer = $settingsMap['disclaimer_text'];
        }
        if (isset($settingsMap['require_auth_for_details'])) {
            $requireAuth = ($settingsMap['require_auth_for_details'] === '1');
        }
    } catch (Exception $e) {}

    // Gated condition: Jika gated aktif dan pengunjung belum login
    $isGated = $requireAuth && !$isAuthenticated;

    // Query projects
    $query = "SELECT * FROM projects ORDER BY sort_order ASC, created_at DESC";
    $stmt = $db->query($query);
    $rawProjects = $stmt->fetchAll();

    $projects = [];

    foreach ($rawProjects as $p) {
        $pId = $p['id'];

        // Detail
        $stmtD = $db->prepare("SELECT * FROM project_details WHERE project_id = ? LIMIT 1");
        $stmtD->execute([$pId]);
        $detailRow = $stmtD->fetch();

        // Funding rows (RAB)
        $stmtItems = $db->prepare("SELECT * FROM funding_items WHERE project_id = ? ORDER BY no_urut ASC");
        $stmtItems->execute([$pId]);
        $itemRows = $stmtItems->fetchAll();

        $fundingRows = [];
        $grandTotal = 0;
        foreach ($itemRows as $item) {
            $rowTotal = (float)$item['total'];
            $grandTotal += $rowTotal;
            $fundingRows[] = [
                'no' => (int)$item['no_urut'],
                'item' => $item['item_name'],
                'quantity' => (int)$item['quantity'],
                'unit_price' => (float)$item['unit_price'],
                'total' => $rowTotal
            ];
        }

        // Simulation
        $stmtSim = $db->prepare("SELECT * FROM project_simulation WHERE project_id = ? LIMIT 1");
        $stmtSim->execute([$pId]);
        $simRow = $stmtSim->fetch();

        $simulation = [
            'tenor_bulan' => $simRow ? (int)$simRow['tenor_bulan'] : 36,
            'estimasi_return_persen' => $simRow ? (float)$simRow['estimasi_return_persen'] : 30.0,
            'modal_kerja_bulanan_persen' => $simRow ? (float)$simRow['modal_kerja_bulanan_persen'] : 2.2,
            'minimum_investasi' => $simRow ? (float)$simRow['minimum_investasi'] : 500000000,
            'maximum_investasi' => $simRow ? (float)$simRow['maximum_investasi'] : 500000000000,
            'default_investasi' => $simRow ? (float)$simRow['default_investasi'] : 500000000,
            'notes' => $simRow['notes'] ?? 'Simulasi bersifat ilustratif, bukan jaminan — mengacu pada Risk Disclosure Statement.'
        ];

        // Persiapkan watermarking & server-side gating data
        $viewerWatermark = null;
        if ($isAuthenticated && $investorSession) {
            $viewerWatermark = 'DOKUMEN RAHASIA PT MONTANA GLOBAL INVESTAMA — DIAKSES OLEH: ' . strtoupper($investorSession['name']) . ' (' . $investorSession['email'] . ') — ' . date('d/m/Y H:i');
        }

        if ($isGated) {
            // Sembunyikan data sensitif di level server
            $detail = [
                'tagline' => $detailRow['tagline'] ?? '',
                'is_gated' => true,
                'gated_notice' => 'Sesuai prinsip keterbukaan informasi terbatas dan kepatuhan APU-PPT/GCG, rincian belanja modal (CAPEX Breakdown) serta simulator BEP/ROI hanya dapat diakses oleh investor terdaftar.',
                'what_will_provide' => [
                    'type' => 'text',
                    'title' => $detailRow['what_will_provide_title'] ?? 'Alokasi Penggunaan Modal',
                    'content' => $detailRow['what_will_provide_content'] ?? ''
                ],
                'funding_target' => [
                    'type' => 'table',
                    'title' => 'Target Pendanaan & Rencana Anggaran Biaya (CAPEX Breakdown)',
                    'columns' => ['No', 'Item Pengadaan / Spesifikasi', 'Qty', 'Harga Satuan (IDR)', 'Total Alokasi (IDR)'],
                    'rows' => [], // DIKOSONGKAN SISI SERVER UNTUK PENGUNJUNG BELUM LOGIN
                    'grand_total' => (float)$p['funding_target'],
                    'is_gated' => true
                ],
                'about_sinergi_foundation' => [
                    'type' => 'text',
                    'title' => $detailRow['sinergi_title'] ?? 'Struktur Kemitraan Strategis',
                    'content' => $detailRow['sinergi_content'] ?? ''
                ],
                'summary' => [
                    'type' => 'text',
                    'title' => $detailRow['summary_title'] ?? 'Ringkasan Kelayakan Investasi',
                    'content' => $detailRow['summary_content'] ?? ''
                ],
                'simulation' => [
                    'is_gated' => true,
                    'notes' => 'Kalkulator BEP dan proyeksi ROI terkunci. Silakan masuk sebagai investor terdaftar untuk mengaktifkan simulasi interaktif.'
                ]
            ];
        } else {
            // Data lengkap untuk investor yang telah terautentikasi
            $detail = [
                'tagline' => $detailRow['tagline'] ?? '',
                'is_gated' => false,
                'viewer_watermark' => $viewerWatermark,
                'what_will_provide' => [
                    'type' => 'text',
                    'title' => $detailRow['what_will_provide_title'] ?? 'Alokasi Penggunaan Modal',
                    'content' => $detailRow['what_will_provide_content'] ?? ''
                ],
                'funding_target' => [
                    'type' => 'table',
                    'title' => 'Target Pendanaan & Rencana Anggaran Biaya (CAPEX Breakdown)',
                    'columns' => ['No', 'Item Pengadaan / Spesifikasi', 'Qty', 'Harga Satuan (IDR)', 'Total Alokasi (IDR)'],
                    'rows' => $fundingRows,
                    'grand_total' => $grandTotal > 0 ? $grandTotal : (float)$p['funding_target'],
                    'is_gated' => false
                ],
                'about_sinergi_foundation' => [
                    'type' => 'text',
                    'title' => $detailRow['sinergi_title'] ?? 'Struktur Kemitraan Strategis',
                    'content' => $detailRow['sinergi_content'] ?? ''
                ],
                'summary' => [
                    'type' => 'text',
                    'title' => $detailRow['summary_title'] ?? 'Ringkasan Kelayakan Investasi',
                    'content' => $detailRow['summary_content'] ?? ''
                ],
                'simulation' => $simulation
            ];
        }

        $projects[] = [
            'id' => $p['id'],
            'title' => $p['title'],
            'category' => $p['category'],
            'image' => $p['image'],
            'funding' => [
                'collected' => (float)$p['funding_collected'],
                'target' => (float)$p['funding_target'],
                'currency' => $p['currency'] ?? 'IDR'
            ],
            'status' => $p['status'],
            'featured' => (bool)$p['featured'],
            'info' => [
                'lokasi' => $p['lokasi'],
                'target' => $p['target_display'] ?: ('Rp ' . number_format((float)$p['funding_target'], 0, ',', '.')),
                'tenor' => $p['tenor'],
                'return' => $p['return_rate'],
                'risk' => $p['risk_level'],
                'min_investment' => $p['min_investment'],
                'payout' => $p['payout'],
                'remaining_days' => $p['remaining_days'],
                'asset_backed' => $p['asset_backed']
            ],
            'detail' => $detail
        ];
    }

    $response = [
        'currency' => 'IDR',
        'is_authenticated' => $isAuthenticated,
        'require_auth_for_details' => $requireAuth,
        'disclaimer_text' => $disclaimer,
        'projects' => $projects
    ];

    if ($projectId) {
        $single = null;
        foreach ($projects as $proj) {
            if ($proj['id'] === $projectId) {
                $single = $proj;
                break;
            }
        }
        $response['project'] = $single;
    }

    sendJsonResponse($response, 200);

} catch (Exception $e) {
    // Fallback if DB error — tetapkan aturan server-side gating juga pada file statis
    $staticFile = __DIR__ . '/../data/projects.json';
    if (file_exists($staticFile)) {
        $rawJson = file_get_contents($staticFile);
        $staticData = json_decode($rawJson, true);
        
        $investorSession = getInvestorSession();
        $isGated = ($investorSession === null);

        if ($isGated && isset($staticData['projects']) && is_array($staticData['projects'])) {
            foreach ($staticData['projects'] as &$sp) {
                if (isset($sp['detail'])) {
                    $sp['detail']['is_gated'] = true;
                    if (isset($sp['detail']['funding_target'])) {
                        $sp['detail']['funding_target']['rows'] = [];
                        $sp['detail']['funding_target']['is_gated'] = true;
                    }
                    if (isset($sp['detail']['simulation'])) {
                        $sp['detail']['simulation'] = [
                            'is_gated' => true,
                            'notes' => 'Kalkulator BEP dan proyeksi ROI terkunci untuk non-investor.'
                        ];
                    }
                }
            }
            unset($sp);
        }

        sendJsonResponse($staticData, 200);
    }
    
    sendJsonException($e, 'Gagal memuat katalog proyek investasi.');
}
