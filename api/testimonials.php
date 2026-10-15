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

    sendJsonResponse($testimonials, 200, 'Data testimoni berhasil diambil.');

} catch (Throwable $e) {
    sendJsonError('Gagal memuat testimoni: ' . $e->getMessage(), 500);
}
