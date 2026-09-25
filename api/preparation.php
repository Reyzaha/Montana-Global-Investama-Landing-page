<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — API: PREPARATION WORKFLOW
 * Method: GET
 */

require_once __DIR__ . '/../backend/config/db.php';
require_once __DIR__ . '/../backend/helpers/response.php';

try {
    $staticFile = __DIR__ . '/../data/preparation.json';
    $db = null;
    try {
        $db = getDB();
    } catch (Exception $dbEx) {}

    // Priority to latest static JSON data if exists
    if (file_exists($staticFile)) {
        $jsonContent = file_get_contents($staticFile);
        $prepData = json_decode($jsonContent, true);

        // Sync to database if connected
        if ($db && !empty($prepData)) {
            try {
                // Sync entities
                $entities = $prepData['entities_summary'] ?? ($prepData['entities'] ?? []);
                if (!empty($entities)) {
                    $db->exec("TRUNCATE TABLE preparation_entities");
                    $stmtEnt = $db->prepare("
                        INSERT INTO preparation_entities (code, name, slogan, role, level, badge, color, description, focus, output, location, sort_order)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    foreach ($entities as $idx => $e) {
                        $stmtEnt->execute([
                            $e['code'],
                            $e['name'],
                            $e['slogan'] ?? '',
                            $e['role'] ?? '',
                            $e['level'] ?? 'Level 1',
                            $e['badge'] ?? ($e['role'] ?? ''),
                            $e['color'] ?? 'gold',
                            $e['description'] ?? ($e['role'] ?? ''),
                            $e['fokus_kerja'] ?? ($e['focus'] ?? ''),
                            $e['output_utama'] ?? ($e['output'] ?? ''),
                            $e['location'] ?? null,
                            $idx + 1
                        ]);
                    }
                }

                // Sync workflow
                $wf = $prepData['workflow'] ?? [];
                if (!empty($wf)) {
                    $db->exec("TRUNCATE TABLE preparation_workflow");
                    $stmtWf = $db->prepare("
                        INSERT INTO preparation_workflow (step_number, title, actor, description, badge)
                        VALUES (?, ?, ?, ?, ?)
                    ");
                    foreach ($wf as $w) {
                        $stmtWf->execute([
                            $w['step'] ?? 1,
                            $w['title'] ?? '',
                            $w['actor'] ?? '',
                            $w['desc'] ?? ($w['description'] ?? ''),
                            $w['badge'] ?? ''
                        ]);
                    }
                }
            } catch (Exception $syncErr) {}
        }

        header('Content-Type: application/json; charset=utf-8');
        echo $jsonContent;
        exit;
    }

    // Fallback to DB fetch if static file is absent
    if ($db) {
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
    }

    sendJsonError('Data alur kerja preparation tidak ditemukan.', 404);

} catch (Exception $e) {
    $staticFile = __DIR__ . '/../data/preparation.json';
    if (file_exists($staticFile)) {
        header('Content-Type: application/json; charset=utf-8');
        readfile($staticFile);
        exit;
    }
    sendJsonError('Gagal memuat alur kerja preparation: ' . $e->getMessage(), 500);
}

