<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — API: ADMIN FAQS CRUD
 * Methods: GET, POST, PUT, DELETE
 */

require_once __DIR__ . '/../../backend/config/db.php';
require_once __DIR__ . '/../../backend/helpers/response.php';
require_once __DIR__ . '/../../backend/helpers/auth_helper.php';

$admin = requireAdminAuth();
$method = $_SERVER['REQUEST_METHOD'];
$input = getJsonInput();

if (isset($input['_method'])) {
    $method = strtoupper($input['_method']);
} elseif (isset($_GET['_method'])) {
    $method = strtoupper($_GET['_method']);
}

try {
    $db = getDB();

    // 1. GET: Ambil semua FAQs atau single FAQ by ID
    if ($method === 'GET') {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
        if ($id) {
            $stmt = $db->prepare("SELECT * FROM faqs WHERE id = ?");
            $stmt->execute([$id]);
            $faq = $stmt->fetch();
            if (!$faq) {
                sendJsonError('FAQ tidak ditemukan.', 404);
            }
            sendJsonResponse($faq);
        }

        $category = isset($_GET['category']) ? trim($_GET['category']) : '';
        if ($category !== '') {
            $stmt = $db->prepare("SELECT * FROM faqs WHERE category = ? ORDER BY sort_order ASC, id ASC");
            $stmt->execute([$category]);
        } else {
            $stmt = $db->query("SELECT * FROM faqs ORDER BY sort_order ASC, id ASC");
        }
        $faqs = $stmt->fetchAll();
        sendJsonResponse($faqs);
    }

    // 2. POST: Tambah FAQ baru
    if ($method === 'POST') {
        $category = trim($input['category'] ?? 'Umum');
        $question = trim($input['question'] ?? '');
        $answer = trim($input['answer'] ?? '');
        $sortOrder = isset($input['sort_order']) ? (int)$input['sort_order'] : 0;
        $isActive = isset($input['is_active']) ? (int)$input['is_active'] : 1;

        if (empty($question) || empty($answer)) {
            sendJsonError('Pertanyaan dan jawaban FAQ wajib diisi.');
        }

        $stmt = $db->prepare("
            INSERT INTO faqs (category, question, answer, sort_order, is_active)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$category, $question, $answer, $sortOrder, $isActive]);
        $id = (int)$db->lastInsertId();

        logAdminActivity('create', 'faqs', (string)$id, "Admin menambah FAQ: {$question}");
        sendJsonResponse(['id' => $id], 201, 'FAQ berhasil ditambahkan.');
    }

    // 3. PUT: Update FAQ atau toggle status
    if ($method === 'PUT') {
        $id = (int)($input['id'] ?? ($_GET['id'] ?? 0));
        if (!$id) {
            sendJsonError('ID FAQ tidak valid.');
        }

        // Action khusus: toggle active
        if (isset($input['action']) && $input['action'] === 'toggle_status') {
            $stmt = $db->prepare("UPDATE faqs SET is_active = NOT is_active WHERE id = ?");
            $stmt->execute([$id]);
            logAdminActivity('update', 'faqs', (string)$id, "Admin toggle status FAQ #{$id}");
            sendJsonResponse(null, 200, 'Status FAQ berhasil diubah.');
        }

        $category = trim($input['category'] ?? 'Umum');
        $question = trim($input['question'] ?? '');
        $answer = trim($input['answer'] ?? '');
        $sortOrder = isset($input['sort_order']) ? (int)$input['sort_order'] : 0;
        $isActive = isset($input['is_active']) ? (int)$input['is_active'] : 1;

        if (empty($question) || empty($answer)) {
            sendJsonError('Pertanyaan dan jawaban FAQ wajib diisi.');
        }

        $stmt = $db->prepare("
            UPDATE faqs SET
                category = ?, question = ?, answer = ?,
                sort_order = ?, is_active = ?
            WHERE id = ?
        ");
        $stmt->execute([$category, $question, $answer, $sortOrder, $isActive, $id]);

        logAdminActivity('update', 'faqs', (string)$id, "Admin memperbarui FAQ: {$question}");
        sendJsonResponse(['id' => $id], 200, 'FAQ berhasil diperbarui.');
    }

    // 4. DELETE: Hapus FAQ
    if ($method === 'DELETE') {
        $id = (int)($input['id'] ?? ($_GET['id'] ?? 0));
        if (!$id) {
            sendJsonError('ID FAQ tidak valid.');
        }

        $stmt = $db->prepare("DELETE FROM faqs WHERE id = ?");
        $stmt->execute([$id]);

        logAdminActivity('delete', 'faqs', (string)$id, "Admin menghapus FAQ #{$id}");
        sendJsonResponse(null, 200, 'FAQ berhasil dihapus.');
    }

    sendJsonError('Metode HTTP tidak didukung.', 405);

} catch (Throwable $e) {
    sendJsonError('Operasi gagal: ' . $e->getMessage(), 500);
}
