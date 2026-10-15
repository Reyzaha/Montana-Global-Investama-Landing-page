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

    sendJsonResponse($articles, 200, 'Daftar wawasan & artikel berhasil dimuat.');

} catch (Throwable $e) {
    sendJsonError('Gagal memuat artikel: ' . $e->getMessage(), 500);
}
