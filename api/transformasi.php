<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — API: TRANSFORMASI JOURNEY
 * Method: GET
 */

require_once __DIR__ . '/../backend/config/db.php';
require_once __DIR__ . '/../backend/helpers/response.php';

try {
    $db = getDB();
    $stmt = $db->query("SELECT * FROM transformasi_steps ORDER BY step_order ASC");
    $steps = $stmt->fetchAll();

    $journey = [];
    foreach ($steps as $st) {
        $achievements = !empty($st['highlights']) ? json_decode($st['highlights'], true) : [];
        $journey[] = [
            'step' => (int)$st['step_order'],
            'year_or_phase' => $st['year_or_phase'],
            'badge' => "Milestone " . $st['step_order'],
            'title' => $st['title'],
            'tagline' => $st['subtitle'],
            'description' => $st['description'],
            'achievements' => $achievements,
            'status' => $st['status']
        ];
    }

    $response = [
        'title' => 'Perjalanan Transformasi Montana Group',
        'subtitle' => 'Rekam Jejak Evolusi Dari Spesialis Alat Berat Menuju Ekosistem Holding Investasi Terpadu',
        'intro' => 'Transformasi Montana Group dibangun di atas fondasi rekam jejak riil di sektor alat berat, inovasi berkelanjutan, dan dedikasi menciptakan nilai ekonomi optimal melalui tata kelola yang amanah.',
        'journey' => $journey
    ];

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
} catch (Exception $e) {
    $staticFile = __DIR__ . '/../data/transformasi.json';
    if (file_exists($staticFile)) {
        header('Content-Type: application/json; charset=utf-8');
        readfile($staticFile);
        exit;
    }
    sendJsonError('Gagal memuat timeline transformasi: ' . $e->getMessage(), 500);
}
