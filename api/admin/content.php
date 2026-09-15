<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — API: ADMIN CONTENT MANAGEMENT
 * Method: GET, POST, PUT, DELETE
 */

require_once __DIR__ . '/../../backend/config/db.php';
require_once __DIR__ . '/../../backend/helpers/response.php';
require_once __DIR__ . '/../../backend/helpers/auth_helper.php';

$admin = requireAdminAuth();
$section = $_GET['section'] ?? 'transformasi';
$method = $_SERVER['REQUEST_METHOD'];

try {
    $db = getDB();

    // 1. TRANSFORMASI STEPS
    if ($section === 'transformasi') {
        if ($method === 'GET') {
            $stmt = $db->query("SELECT * FROM transformasi_steps ORDER BY step_order ASC");
            sendJsonResponse($stmt->fetchAll());
        }

        if ($method === 'POST' || $method === 'PUT') {
            $input = getJsonInput();
            $id = $input['id'] ?? null;
            $stepOrder = (int)($input['step_order'] ?? 1);
            $yearPhase = trim($input['year_or_phase'] ?? '');
            $title = trim($input['title'] ?? '');
            $subtitle = trim($input['subtitle'] ?? '');
            $description = trim($input['description'] ?? '');
            $highlights = is_array($input['highlights'] ?? null) 
                ? json_encode($input['highlights'], JSON_UNESCAPED_UNICODE) 
                : ($input['highlights'] ?? '[]');

            if (empty($title)) {
                sendJsonError('Judul milestone transformasi wajib diisi.');
            }

            if ($id) {
                $stmt = $db->prepare("
                    UPDATE transformasi_steps SET
                        step_order = ?, year_or_phase = ?, title = ?, subtitle = ?,
                        description = ?, highlights = ?
                    WHERE id = ?
                ");
                $stmt->execute([$stepOrder, $yearPhase, $title, $subtitle, $description, $highlights, $id]);
                logAdminActivity('update', 'transformasi_steps', (string)$id, "Admin updated transformasi step '{$title}'");
                sendJsonResponse(['id' => $id], 200, "Milestone transformasi berhasil diperbarui.");
            } else {
                $stmt = $db->prepare("
                    INSERT INTO transformasi_steps (step_order, year_or_phase, title, subtitle, description, highlights)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$stepOrder, $yearPhase, $title, $subtitle, $description, $highlights]);
                $newId = $db->lastInsertId();
                logAdminActivity('create', 'transformasi_steps', (string)$newId, "Admin created transformasi step '{$title}'");
                sendJsonResponse(['id' => $newId], 201, "Milestone transformasi berhasil ditambahkan.");
            }
        }

        if ($method === 'DELETE') {
            $id = (int)($_GET['id'] ?? 0);
            if ($id) {
                $db->prepare("DELETE FROM transformasi_steps WHERE id = ?")->execute([$id]);
                logAdminActivity('delete', 'transformasi_steps', (string)$id, "Admin deleted transformasi step ID {$id}");
                sendJsonResponse(['id' => $id], 200, "Milestone transformasi berhasil dihapus.");
            }
            sendJsonError('ID milestone tidak valid.');
        }
    }

    sendJsonError("Section '{$section}' tidak valid.", 400);

} catch (Exception $e) {
    sendJsonError('Terjadi kesalahan CMS konten: ' . $e->getMessage(), 500);
}
