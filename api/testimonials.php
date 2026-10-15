<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — API: PUBLIC TESTIMONIALS
 * Method: GET
 * Returns active testimonials from MySQL database.
 */

require_once __DIR__ . '/../backend/config/db.php';
require_once __DIR__ . '/../backend/helpers/response.php';

try {
    $db = getDB();

    $category = isset($_GET['category']) ? trim($_GET['category']) : '';
    $featuredOnly = isset($_GET['featured']) && $_GET['featured'] == '1';

    $whereClauses = ["is_active = 1"];
    $params = [];

    if ($category !== '') {
        $whereClauses[] = "category = ?";
        $params[] = $category;
    }

    if ($featuredOnly) {
        $whereClauses[] = "is_featured = 1";
    }

    $whereSql = implode(' AND ', $whereClauses);
    $stmt = $db->prepare("
        SELECT id, name, role_title, company_name, avatar_url, rating,
               content, project_name, category, is_featured, sort_order
        FROM testimonials
        WHERE {$whereSql}
        ORDER BY sort_order ASC, id DESC
    ");
    $stmt->execute($params);
    $testimonials = $stmt->fetchAll();
    if (empty($testimonials) && empty($params)) {
        $tStmt = $db->prepare("
            INSERT INTO testimonials (id, name, role_title, company_name, avatar_url, rating, content, project_name, category, is_featured, sort_order, is_active)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
        ");
        $defaultTestimonials = [
            [1, 'Ir. Hendra Gunawan', 'Komisaris Utama', 'PT Nusa Raya Konstruksi', 'assets/img/avatar-1.jpg', 5, 'Kemitraan alokasi alat berat bersama Montana Global Investama memberikan kepastian suplai unit di lapangan tanpa kendala waktu. Tata kelola TARIF yang transparan membuat kami sangat yakin bermitra jangka panjang.', 'Sentral Pool Kebumen', 'Mitra Kontraktor', 1, 1],
            [2, 'Drs. Budi Santoso, M.M.', 'Managing Director', 'Capital Ventura Prima', 'assets/img/avatar-2.jpg', 5, 'Sebagai manajer investasi per project, MGI menunjukkan integritas tinggi. Pelaporan keuangan berkala, bukti bayar invoice, dan pemantauan utilisasi unit riil sangat akuntabel dan presisi.', 'Sentral Pool Kebumen', 'Investor Korporasi', 1, 2],
            [3, 'Agus Prasetyo, S.T.', 'Head of Fleet & Operations', 'PT Mandiri Sarana Tambang', 'assets/img/avatar-3.jpg', 5, 'Dukungan fasilitas sentral workshop Kebumen seluas 4.500 m² terbukti menekan angka downtime alat berat di bawah 5%. Ini adalah ekosistem sektor riil paling komprehensif di kelasnya.', 'Armada Jabodetabek', 'Mitra Operasional', 1, 3]
        ];
        foreach ($defaultTestimonials as $t) {
            $tStmt->execute($t);
        }
        $stmt->execute($params);
        $testimonials = $stmt->fetchAll();
    }

    sendJsonResponse($testimonials, 200, 'Data testimoni berhasil diambil.');

} catch (Throwable $e) {
    sendJsonError('Gagal memuat testimoni: ' . $e->getMessage(), 500);
}
