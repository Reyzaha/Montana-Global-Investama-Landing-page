<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — API: PREPARATION WORKFLOW
 * Method: GET
 */

require_once __DIR__ . '/../backend/config/db.php';
require_once __DIR__ . '/../backend/helpers/response.php';

try {
    $db = getDB();

    // Entities
    $stmtEnt = $db->query("SELECT * FROM preparation_entities ORDER BY sort_order ASC");
    $rawEntities = $stmtEnt->fetchAll();

    $entitiesSummary = [];
    foreach ($rawEntities as $e) {
        $icon = match($e['code']) {
            'MGI' => 'building',
            'MIU' => 'truck',
            'MSI' => 'gear-wide-connected',
            'Mypurcase' => 'cart-check',
            default => 'briefcase'
        };

        $entitiesSummary[] = [
            'code' => $e['code'],
            'name' => $e['name'],
            'slogan' => $e['slogan'],
            'role' => $e['role'],
            'level' => $e['level'],
            'badge' => $e['badge'],
            'color' => $e['color'],
            'description' => $e['description'],
            'fokus_kerja' => $e['focus'],
            'output_utama' => $e['output'],
            'location' => $e['location'],
            'icon' => $icon
        ];
    }

    // Workflow
    $stmtWf = $db->query("SELECT * FROM preparation_workflow ORDER BY step_number ASC");
    $rawWf = $stmtWf->fetchAll();

    $workflow = [];
    foreach ($rawWf as $w) {
        $workflow[] = [
            'step' => (int)$w['step_number'],
            'title' => $w['title'],
            'actor' => $w['actor'],
            'desc' => $w['description'],
            'badge' => $w['badge']
        ];
    }

    $response = [
        'title' => 'Struktur Kerja & Sinergi Operasional Antar Entitas',
        'subtitle' => 'Alur Koordinasi Terpadu: Pengelolaan Modal, Pengadaan Rantai Ketersediaan, dan Eksekusi Lapangan',
        'intro' => 'Menjelaskan bagaimana dana investor mengalir dan bagaimana entitas-entitas dalam Montana Group saling berkoordinasi dalam satu alur kerja operasional yang transparan, akuntabel, dan terproteksi aset riil.',
        'entities_summary' => $entitiesSummary,
        'workflow' => $workflow
    ];

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;

} catch (Exception $e) {
    $staticFile = __DIR__ . '/../data/preparation.json';
    if (file_exists($staticFile)) {
        header('Content-Type: application/json; charset=utf-8');
        readfile($staticFile);
        exit;
    }
    sendJsonError('Gagal memuat alur kerja preparation: ' . $e->getMessage(), 500);
}
