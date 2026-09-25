<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — API: EKOSISTEM STRUCTURE
 * Method: GET
 */

require_once __DIR__ . '/../backend/config/db.php';
require_once __DIR__ . '/../backend/helpers/response.php';

try {
    $staticFile = __DIR__ . '/../data/ekosistem.json';
    $db = null;
    try {
        $db = getDB();
    } catch (Exception $dbEx) {}

    // Priority to latest static file data if exists
    if (file_exists($staticFile)) {
        $jsonContent = file_get_contents($staticFile);
        $baseData = json_decode($jsonContent, true);

        // Sync nodes to DB if connected
        if ($db && !empty($baseData['nodes'])) {
            try {
                $db->exec("SET FOREIGN_KEY_CHECKS = 0; TRUNCATE TABLE ekosistem_nodes; SET FOREIGN_KEY_CHECKS = 1;");
                $stmtNode = $db->prepare("
                    INSERT INTO ekosistem_nodes (id, label, short_label, subtitle, parent_id, level, badge, type, category, icon, role_desc, sort_order)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                foreach ($baseData['nodes'] as $idx => $n) {
                    $stmtNode->execute([
                        $n['id'],
                        $n['label'],
                        $n['short_label'] ?? $n['label'],
                        $n['subtitle'] ?? null,
                        $n['parent'] ?? null,
                        $n['level'] ?? 0,
                        $n['badge'] ?? null,
                        $n['type'] ?? 'subsidiary',
                        $n['category'] ?? '',
                        $n['icon'] ?? 'building',
                        $n['description'] ?? null,
                        $idx + 1
                    ]);
                }
            } catch (Exception $syncErr) {}
        }

        header('Content-Type: application/json; charset=utf-8');
        echo $jsonContent;
        exit;
    }

    // Fallback to DB fetch if static file is absent
    if ($db) {
        $stmt = $db->query("SELECT * FROM ekosistem_nodes ORDER BY level ASC, sort_order ASC");
        $rawNodes = $stmt->fetchAll();

        if (!empty($rawNodes)) {
            $nodes = [];
            foreach ($rawNodes as $n) {
                $nodes[] = [
                    'id' => $n['id'],
                    'label' => $n['label'] ?? '',
                    'short_label' => $n['short_label'] ?? $n['label'],
                    'subtitle' => $n['subtitle'] ?? '',
                    'parent' => $n['parent_id'] ?? null,
                    'level' => isset($n['level']) ? (int)$n['level'] : 0,
                    'badge' => $n['badge'] ?? '',
                    'type' => $n['type'] ?? (($n['badge'] === 'Mitra Strategis') ? 'partner' : ($n['level'] == 0 ? 'holding' : ($n['level'] == 2 ? 'facility' : 'subsidiary'))),
                    'category' => $n['category'] ?? '',
                    'icon' => $n['icon'] ?? 'building',
                    'description' => $n['role_desc'] ?? ''
                ];
            }
            $baseData = [
                'title' => 'Bagan Ekosistem Korporat Montana Group',
                'subtitle' => 'Ekosistem Investasi Proyek',
                'intro' => 'Struktur grup PT Montana Global Investama menaungi entitas operasional terpadu: MIU, MSI, dan Mypurcase.',
                'nodes' => $nodes
            ];

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($baseData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }
    }

    sendJsonError('Data ekosistem tidak tersedia.', 404);

} catch (Exception $e) {
    $staticFile = __DIR__ . '/../data/ekosistem.json';
    if (file_exists($staticFile)) {
        header('Content-Type: application/json; charset=utf-8');
        readfile($staticFile);
        exit;
    }
    sendJsonError('Gagal memuat struktur ekosistem: ' . $e->getMessage(), 500);
}

