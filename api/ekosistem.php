<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — API: EKOSISTEM STRUCTURE
 * Method: GET
 */

require_once __DIR__ . '/../backend/config/db.php';
require_once __DIR__ . '/../backend/helpers/response.php';

try {
    $staticFile = __DIR__ . '/../data/ekosistem.json';
    $baseData = file_exists($staticFile) ? json_decode(file_get_contents($staticFile), true) : [];

    // Attempt DB fetch to dynamically merge nodes if available
    try {
        $db = getDB();
        $stmt = $db->query("SELECT * FROM ekosistem_nodes ORDER BY level ASC, sort_order ASC");
        $rawNodes = $stmt->fetchAll();

        if (!empty($rawNodes)) {
            // Build map of static nodes by id for fallback properties
            $staticMap = [];
            if (!empty($baseData['nodes'])) {
                foreach ($baseData['nodes'] as $sn) {
                    $staticMap[$sn['id']] = $sn;
                }
            }

            $nodes = [];
            foreach ($rawNodes as $n) {
                $id = $n['id'];
                $fallback = $staticMap[$id] ?? [];
                
                $nodes[] = [
                    'id' => $id,
                    'label' => $n['label'] ?: ($fallback['label'] ?? ''),
                    'short_label' => $n['short_label'] ?? ($fallback['short_label'] ?? $n['label']),
                    'subtitle' => $n['subtitle'] ?? ($fallback['subtitle'] ?? ''),
                    'parent' => $n['parent_id'] ?? ($fallback['parent'] ?? null),
                    'level' => isset($n['level']) ? (int)$n['level'] : ($fallback['level'] ?? 0),
                    'badge' => $n['badge'] ?: ($fallback['badge'] ?? ''),
                    'type' => $n['type'] ?? ($fallback['type'] ?? (($n['badge'] === 'Mitra Strategis' || $id === 'sinergi-foundation') ? 'partner' : ($n['level'] == 0 ? 'holding' : ($n['level'] == 2 ? 'facility' : 'subsidiary')))),
                    'category' => $n['category'] ?? ($fallback['category'] ?? ''),
                    'icon' => $n['icon'] ?? ($fallback['icon'] ?? 'building'),
                    'description' => $n['role_desc'] ?: ($fallback['description'] ?? '')
                ];
            }
            $baseData['nodes'] = $nodes;
        }
    } catch (Exception $dbErr) {
        // Fallback silently to baseline static JSON data if DB is temporarily unreachable
    }

    if (empty($baseData)) {
        throw new Exception('Data ekosistem tidak tersedia.');
    }

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($baseData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
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
