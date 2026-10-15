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
    sendJsonResponse($faqs, 200, 'Data FAQ berhasil diambil.');

} catch (Throwable $e) {
    sendJsonError('Gagal memuat FAQ: ' . $e->getMessage(), 500);
}
