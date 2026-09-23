<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — PUBLIC API: ACTIVE CITIES FOR SEGMENTATION
 * Method: GET
 */

require_once __DIR__ . '/../backend/config/db.php';
require_once __DIR__ . '/../backend/helpers/response.php';

try {
    $db = getDB();

    $stmt = $db->query("
        SELECT c.id, c.name, c.slug, c.icon, c.description, c.sort_order,
               COUNT(p.id) as project_count,
               SUM(CASE WHEN p.status = 'Open' THEN 1 ELSE 0 END) as open_project_count
        FROM cities c
        LEFT JOIN projects p ON (p.city_id = c.id OR LOWER(p.city) = LOWER(c.name))
        WHERE c.is_active_segment = 1
        GROUP BY c.id
        ORDER BY c.sort_order ASC, c.name ASC
    ");
    $cities = $stmt->fetchAll();

    sendJsonResponse(['cities' => $cities]);

} catch (Exception $e) {
    // Fallback to data/cities.json
    $jsonFile = __DIR__ . '/../data/cities.json';
    if (file_exists($jsonFile)) {
        $raw = json_decode(file_get_contents($jsonFile), true);
        $cities = array_filter($raw['cities'] ?? [], function($c) {
            return !isset($c['is_active_segment']) || (int)$c['is_active_segment'] === 1;
        });
        sendJsonResponse(['cities' => array_values($cities)]);
    }

    sendJsonError('Gagal memuat data kota: ' . $e->getMessage(), 500);
}
