<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — API: TRANSFORMASI JOURNEY
 * Method: GET
 */

require_once __DIR__ . '/../backend/config/db.php';
require_once __DIR__ . '/../backend/helpers/response.php';

try {
    $staticFile = __DIR__ . '/../data/transformasi.json';
    
    // Priority to latest static file data if exists
    if (file_exists($staticFile)) {
        header('Content-Type: application/json; charset=utf-8');
        readfile($staticFile);
        exit;
    }

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
        'subtitle' => 'Dari Kebumen Menuju Ekosistem Investasi Nasional',
        'intro' => 'Bermula dari satu unit alat berat di Kebumen (2022), Montana Group tumbuh menjadi grup investasi multi-sektor yang modern.',
        'quote' => 'Kami mengintegrasikan keandalan operasional dan transformasi digital untuk membangun ekosistem sektor riil yang berkelanjutan, efisien, dan transparan.',
        'journey' => $journey
    ];

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
} catch (Exception $e) {
    sendJsonError('Gagal memuat timeline transformasi: ' . $e->getMessage(), 500);
}
