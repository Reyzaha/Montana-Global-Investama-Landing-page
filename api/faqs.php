<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — API: PUBLIC FAQS (TANYA JAWAB)
 * Method: GET
 * Returns active FAQs ordered by sort_order ASC, id ASC.
 */

require_once __DIR__ . '/../backend/config/db.php';
require_once __DIR__ . '/../backend/helpers/response.php';

try {
    $db = getDB();

    $category = isset($_GET['category']) ? trim($_GET['category']) : '';

    if ($category !== '') {
        $stmt = $db->prepare("
            SELECT id, category, question, answer, sort_order
            FROM faqs
            WHERE is_active = 1 AND category = ?
            ORDER BY sort_order ASC, id ASC
        ");
        $stmt->execute([$category]);
    } else {
        $stmt = $db->query("
            SELECT id, category, question, answer, sort_order
            FROM faqs
            WHERE is_active = 1
            ORDER BY sort_order ASC, id ASC
        ");
    }

    $faqs = $stmt->fetchAll();
    if (empty($faqs) && empty($category)) {
        $faqStmt = $db->prepare("
            INSERT INTO faqs (id, category, question, answer, sort_order, is_active)
            VALUES (?, ?, ?, ?, ?, 1)
        ");
        $defaultFaqs = [
            [1, 'Tata Kelola & Monitoring', 'Bagaimana mekanisme kepatuhan tata kelola TARIF diimplementasikan dalam pengelolaan dana?', 'MGI menerapkan prinsip TARIF secara ketat: Transparansi alokasi belanja modal, Akuntabilitas laporan keuangan berkala, Responsibilitas kepatuhan legal dan asuransi, Independensi due diligence, serta Kesetaraan hak mitra pemodal.', 1],
            [2, 'Underlying & Keamanan Modal', 'Apakah investasi proyek di Montana Global Investama memiliki underlying aset fisik riil?', 'Ya. Setiap alokasi modal langsung terikat pada aset fisik produktif berupa armada unit alat berat CBU yang terlindungi polis asuransi alat berat komprehensif serta didukung fasilitas workshop sentral.', 2],
            [3, 'Imbal Hasil & Pembagian Hasil', 'Kapan dan bagaimana pembagian imbal hasil dibayarkan kepada mitra pemodal?', 'Imbal hasil dibagikan secara berkala (triwulanan atau per siklus proyek sesuai kesepakatan akad kemitraan) langsung melalui transfer rekening escrow terverifikasi, disertai lampiran laporan operasional.', 3],
            [4, 'Ekosistem Bisnis', 'Apa peran tiga pilar usaha (MIU, MSI, Mypurcase) dalam ekosistem Montana Group?', 'MIU bertindak sebagai penyedia teknis dan workshop alat berat, MSI menangani logistik serta impor bea cukai, dan Mypurcase menghubungkan pengadaan digital B2B. Sinergi ini memastikan unit siap kerja dengan downtime minimal.', 4]
        ];
        foreach ($defaultFaqs as $f) {
            $faqStmt->execute($f);
        }
        $stmt = $db->query("SELECT id, category, question, answer, sort_order FROM faqs WHERE is_active = 1 ORDER BY sort_order ASC, id ASC");
        $faqs = $stmt->fetchAll();
    }

    sendJsonResponse($faqs, 200, 'Data FAQ berhasil diambil.');

} catch (Throwable $e) {
    sendJsonError('Gagal memuat FAQ: ' . $e->getMessage(), 500);
}
