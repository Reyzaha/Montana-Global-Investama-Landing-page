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
