<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — API: PUBLIC ARTICLES & INSIGHTS
 * Method: GET
 * Returns published articles & market insights from MySQL database.
 */

require_once __DIR__ . '/../backend/config/db.php';
require_once __DIR__ . '/../backend/helpers/response.php';

try {
    $db = getDB();

    $slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';
    $category = isset($_GET['category']) ? trim($_GET['category']) : '';
    $limit = isset($_GET['limit']) ? max(1, min(50, (int)$_GET['limit'])) : 10;

    // Single article lookup by slug
    if ($slug !== '') {
        $stmt = $db->prepare("
            SELECT id, title, slug, category, excerpt, content, cover_image, author_name, views_count, published_at
            FROM articles
            WHERE slug = ? AND status = 'published'
            LIMIT 1
        ");
        $stmt->execute([$slug]);
        $article = $stmt->fetch();

        if (!$article) {
            sendJsonError('Artikel tidak ditemukan.', 404);
        }

        // Increment view count
        $db->prepare("UPDATE articles SET views_count = views_count + 1 WHERE id = ?")->execute([$article['id']]);
        $article['views_count']++;

        sendJsonResponse($article, 200, 'Artikel berhasil dimuat.');
    }

    // List articles
    $sql = "SELECT id, title, slug, category, excerpt, cover_image, author_name, views_count, published_at FROM articles WHERE status = 'published'";
    $params = [];

    if ($category !== '') {
        $sql .= " AND category = ?";
        $params[] = $category;
    }

    $sql .= " ORDER BY published_at DESC LIMIT " . (int)$limit;

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $articles = $stmt->fetchAll();
    if (empty($articles) && empty($category) && empty($slug)) {
        $artStmt = $db->prepare("
            INSERT INTO articles (id, title, slug, category, excerpt, content, cover_image, author_name, views_count, status, published_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'published', NOW())
        ");
        $defaultArticles = [
            [1, 'Strategi Alokasi Modal Berbasis Aset Fisik di Tengah Ketidakpastian Global', 'strategi-alokasi-modal-berbasis-aset-fisik', 'Wawasan Pasar', 'Mengapa investasi berbasis alat berat riil menjadi instrumen lindung nilai (hedging) yang tangguh bagi portofolio pemodal korporasi dan institusional.', '<p>Di tengah dinamika pasar keuangan makro, alokasi modal pada aset riil produktif seperti alat berat CBU terbukti memberikan ketahanan arus kas operasional yang stabil...</p>', 'assets/img/hero-alat-berat.png', 'Tim Riset Investasi MGI', 428],
            [2, 'Implementasi Kerangka TARIF: Fondasi Akuntabilitas Investasi Per Proyek', 'implementasi-kerangka-tarif-tata-kelola-mgi', 'Tata Kelola (GCG)', 'Kajian komprehensif mengenai transparansi belanja modal (CAPEX) dan pengawasan unit lapangan melalui 5 pilar TARIF di Montana Global Investama.', '<p>Good Corporate Governance bukan sekadar formalitas kepatuhan, melainkan mekanisme perlindungan modal investor yang diterapkan dari due diligence hingga distribusi dividen...</p>', 'assets/img/icon-tata-kelola.png', 'Komite Tata Kelola MGI', 612],
            [3, 'Optimalisasi Fasilitas Sentral Kebumen: Menekan Downtime Menuju Standar 95% Utilisasi', 'optimalisasi-fasilitas-sentral-workshop-kebumen', 'Kabar Korporasi', 'Bagaimana workshop sentral seluas 4.500 m² di Kebumen menjadi tulang punggung pemeliharaan armada dan proteksi nilai aset sindikasi.', '<p>Fasilitas sentral MGI di Kebumen Jawa Tengah dilengkapi bay overhaul modern, teknisi bersertifikasi, dan manajemen rantai pasok suku cadang door-to-door...</p>', 'assets/img/miu-building.jpg', 'Manajemen Operasional MGI', 349]
        ];
        foreach ($defaultArticles as $a) {
            $artStmt->execute($a);
        }
        $stmt->execute($params);
        $articles = $stmt->fetchAll();
    }

    sendJsonResponse($articles, 200, 'Daftar wawasan & artikel berhasil dimuat.');

} catch (Throwable $e) {
    sendJsonError('Gagal memuat artikel: ' . $e->getMessage(), 500);
}
