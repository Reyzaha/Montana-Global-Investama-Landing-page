<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — API: PUBLIC FEATURES / VALUE PROPOSITIONS / PILARS
 * Method: GET
 * Returns active features ordered by sort_order ASC, id ASC.
 */

require_once __DIR__ . '/../backend/config/db.php';
require_once __DIR__ . '/../backend/helpers/response.php';

try {
    $db = getDB();

    $category = isset($_GET['category']) ? trim($_GET['category']) : '';

    if ($category !== '') {
        $stmt = $db->prepare("
            SELECT id, category, item_number, title, subtitle, description, 
                   icon, image_url, badge_text, accent_color, sort_order
            FROM features
            WHERE is_active = 1 AND category = ?
            ORDER BY sort_order ASC, id ASC
        ");
        $stmt->execute([$category]);
        $items = $stmt->fetchAll();

        sendJsonResponse($items, 200, 'Data features berhasil diambil.');
    } else {
        $stmt = $db->query("
            SELECT id, category, item_number, title, subtitle, description, 
                   icon, image_url, badge_text, accent_color, sort_order
            FROM features
            WHERE is_active = 1
            ORDER BY sort_order ASC, id ASC
        ");
        $items = $stmt->fetchAll();
        if (empty($items)) {
            // Auto-seed default corporate features
            $featStmt = $db->prepare("
                INSERT INTO features (id, category, item_number, title, subtitle, description, icon, image_url, badge_text, accent_color, sort_order, is_active)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
            ");
            $defaultFeatures = [
                [1, 'value_prop', '01', 'Alokasi Modal Berbasis Aset Fisik', 'Proteksi Nilai Riil', 'Modal dialokasikan langsung pada aset fisik produktif bernilai tinggi (unit alat berat) dengan dukungan legalitas lengkap serta proteksi asuransi komprehensif.', 'assets/img/icon-alokasi-modal.png', NULL, 'Keamanan Modal', '#1D3589', 1],
                [2, 'value_prop', '02', 'Kemitraan Strategis & Kontrak Eksklusif', 'Kepastian Utilisasi', 'Kerangka kemitraan eksklusif melalui kontrak jangka menengah hingga panjang bersama kontraktor rekanan daerah dirancang guna memastikan penyerapan unit dan kepastian utilisasi sejak awal periode proyek.', 'assets/img/icon-kemitraan.png', NULL, 'Utilisasi Terjamin', '#1D3589', 2],
                [3, 'value_prop', '03', 'Proyeksi Imbal Hasil Terukur', 'Underlying Riil Non-Spekulatif', 'Setiap proyek dilengkapi pemodelan CAPEX, simulasi BEP, dan proyeksi arus kas operasional sebagai dasar evaluasi uji kelayakan (due diligence) sebelum partisipasi modal disetujui.', 'assets/img/icon-imbal-hasil.png', NULL, 'Akuntabilitas Finansial', '#1D3589', 3],
                [4, 'value_prop', '04', 'Sinergi Terintegrasi Lintas Ekosistem', 'Efisiensi Rantai Pasok', 'Integrasi vertikal antara MGI (Grup & Alokasi Modal), MIU (Workshop & Overhaul Sentral), MSI (Impor CBU Door-to-Door), dan Mypurcase (Purchasing B2B) menghasilkan efisiensi biaya belanja modal dan perlindungan nilai aset.', 'assets/img/icon-tata-kelola.png', NULL, 'Efisiensi Nilai', '#1D3589', 4],
                [5, 'value_prop', '05', 'Transparansi, Audit & Tata Kelola TARIF', 'Good Corporate Governance', 'Investor memperoleh akses visibilitas terhadap belanja modal (CAPEX), peruntukan dana, status unit lapangan, serta pelaporan keuangan berkala sesuai standar Tata Kelola Perusahaan yang Baik (Good Corporate Governance).', 'assets/img/icon-transparansi.png', NULL, 'GCG TARIF', '#1D3589', 5],
                [6, 'pilar', 'MIU', 'Montana Indo Utama (MIU)', 'Integrated Heavy Equipment Solutions', 'Mendukung kebutuhan alat berat melalui supply, deployment, workshop sentral Kebumen 4.500 m², dan operational support untuk proyek sektor riil.', NULL, 'assets/img/miu-building.jpg', 'Alat Berat & Workshop Sentral', '#1D3589', 1],
                [7, 'pilar', 'MSI', 'Montana Sentra Industri (MSI)', 'Import & Logistics Partner', 'Menyediakan solusi impor unit CBU Jepang & Cina, pengurusan customs clearance pelabuhan terpadu, dan distribusi door-to-door lokasi proyek.', NULL, 'assets/img/icon-msi-ship.png', 'Logistik & Impor CBU', '#C5A059', 2],
                [8, 'pilar', 'MPC', 'Mypurcase', 'Platform B2B Procurement Digital', 'Menghubungkan kebutuhan pembelian dengan supplier terverifikasi, otomatisasi purchasing, dan transparansi rantai ketersediaan secara efisien.', NULL, 'assets/img/mypurcase-app.png', 'Segera Diluncurkan 2027', '#059669', 3]
            ];
            foreach ($defaultFeatures as $f) {
                $featStmt->execute($f);
            }
            $stmt = $db->query("
                SELECT id, category, item_number, title, subtitle, description, 
                       icon, image_url, badge_text, accent_color, sort_order
                FROM features
                WHERE is_active = 1
                ORDER BY sort_order ASC, id ASC
            ");
            $items = $stmt->fetchAll();
        }

        $grouped = [
            'value_props' => [],
            'pilars' => [],
            'layanan' => [],
            'keunggulan' => [],
            'all' => $items
        ];

        foreach ($items as $row) {
            $cat = $row['category'];
            if ($cat === 'value_prop') {
                $grouped['value_props'][] = $row;
            } elseif ($cat === 'pilar') {
                $grouped['pilars'][] = $row;
            } elseif ($cat === 'layanan') {
                $grouped['layanan'][] = $row;
            } elseif ($cat === 'keunggulan') {
                $grouped['keunggulan'][] = $row;
            }
        }

        sendJsonResponse($grouped, 200, 'Semua data features berhasil diambil.');
    }

} catch (Throwable $e) {
    sendJsonError('Gagal memuat features: ' . $e->getMessage(), 500);
}
