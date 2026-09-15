<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — API: EKOSISTEM STRUCTURE
 * Method: GET
 */

require_once __DIR__ . '/../backend/config/db.php';
require_once __DIR__ . '/../backend/helpers/response.php';

try {
    $db = getDB();
    $stmt = $db->query("SELECT * FROM ekosistem_nodes ORDER BY level ASC, sort_order ASC");
    $rawNodes = $stmt->fetchAll();

    $nodes = [];
    foreach ($rawNodes as $n) {
        $nodes[] = [
            'id' => $n['id'],
            'label' => $n['label'],
            'subtitle' => $n['subtitle'],
            'parent' => $n['parent_id'],
            'level' => (int)$n['level'],
            'badge' => $n['badge'],
            'role_desc' => $n['role_desc']
        ];
    }

    $response = [
        'title' => 'Bagan Ekosistem Korporat Montana Group',
        'subtitle' => 'Struktur Hubungan Hierarki, Sinergi Entitas, dan Infrastruktur Fisik',
        'intro' => 'Visualisasi arsitektur tata kelola holding dan entitas operasional di bawah naungan PT Montana Global Investama.',
        'nodes' => $nodes
    ];

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;

} catch (Exception $e) {
    $staticFile = __DIR__ . '/../data/ekosistem.json';
    if (file_exists($staticFile)) {
        header('Content-Type: application/json; charset=utf-8');
        readfile($staticFile);
        exit;
    }
    sendJsonError('Gagal memuat struktur ekosistem: ' . $e->getMessage(), 500);
}
