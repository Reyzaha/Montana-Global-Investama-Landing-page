<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — API: ADMIN MEDIA LIBRARY & UPLOADS
 * Methods: GET, POST, DELETE
 */

require_once __DIR__ . '/../../backend/config/db.php';
require_once __DIR__ . '/../../backend/helpers/response.php';
require_once __DIR__ . '/../../backend/helpers/auth_helper.php';

$admin = requireAdminAuth();
$method = $_SERVER['REQUEST_METHOD'];

$uploadDir = __DIR__ . '/../../uploads';
if (!is_dir($uploadDir)) {
    @mkdir($uploadDir, 0755, true);
}

try {
    $db = getDB();

    // 1. GET: Ambil daftar file media
    if ($method === 'GET') {
        $search = trim($_GET['search'] ?? '');
        $type = trim($_GET['type'] ?? '');

        $sql = "SELECT * FROM media_files WHERE 1=1";
        $params = [];

        if ($type !== '') {
            $sql .= " AND file_type LIKE ?";
            $params[] = '%' . $type . '%';
        }

        if ($search !== '') {
            $sql .= " AND (original_name LIKE ? OR caption LIKE ?)";
            $params[] = '%' . $search . '%';
            $params[] = '%' . $search . '%';
        }

        $sql .= " ORDER BY uploaded_at DESC LIMIT 500";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $files = $stmt->fetchAll();

        sendJsonResponse($files, 200, 'Daftar media berhasil dimuat.');
    }

    // 2. POST: Upload file baru
    if ($method === 'POST') {
        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            sendJsonError('File tidak ditemukan atau terjadi kesalahan saat upload.', 400);
        }

        $file = $_FILES['file'];
        $caption = trim($_POST['caption'] ?? '');
        $origName = basename($file['name']);
        $fileSize = (int)$file['size'];

        // Maximum size: 5MB
        if ($fileSize > 5 * 1024 * 1024) {
            sendJsonError('Ukuran file melebihi batas maksimum 5MB.', 422);
        }

        $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
        $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'svg', 'gif', 'pdf'];

        if (!in_array($ext, $allowedExts)) {
            sendJsonError('Format file tidak didukung. Format yang diizinkan: JPG, PNG, WEBP, SVG, GIF, PDF.', 422);
        }

        // Generate safe unique filename
        $safeBase = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($origName, PATHINFO_FILENAME));
        $filename = 'mgi_' . time() . '_' . substr(md5(uniqid()), 0, 8) . '.' . $ext;
        $targetPath = $uploadDir . '/' . $filename;
        $relPath = 'uploads/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            sendJsonError('Gagal memindahkan file ke direktori tujuan server.', 500);
        }

        $mimeType = mime_content_type($targetPath) ?: 'application/octet-stream';

        $adminId = isset($admin['id']) ? (int)$admin['id'] : null;

        $stmt = $db->prepare("
            INSERT INTO media_files (filename, original_name, file_path, file_type, file_size, caption, uploaded_by, uploaded_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$filename, $origName, $relPath, $mimeType, $fileSize, $caption, $adminId]);
        $mediaId = (int)$db->lastInsertId();

        logAdminActivity('upload', 'media_files', (string)$mediaId, "Admin mengunggah media: {$origName}");

        sendJsonResponse([
            'id' => $mediaId,
            'filename' => $filename,
            'original_name' => $origName,
            'file_path' => $relPath,
            'file_size' => $fileSize,
            'file_type' => $mimeType
        ], 201, 'File media berhasil diunggah.');
    }

    // 3. DELETE: Hapus media
    if ($method === 'DELETE') {
        $input = getJsonInput();
        $id = (int)($input['id'] ?? ($_GET['id'] ?? 0));

        if (!$id) {
            sendJsonError('ID media tidak valid.');
        }

        $stmt = $db->prepare("SELECT file_path, filename FROM media_files WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        if ($row) {
            $filePath = __DIR__ . '/../../' . $row['file_path'];
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
            $delStmt = $db->prepare("DELETE FROM media_files WHERE id = ?");
            $delStmt->execute([$id]);

            logAdminActivity('delete', 'media_files', (string)$id, "Admin menghapus media: {$row['filename']}");
        }

        sendJsonResponse(null, 200, 'File media berhasil dihapus.');
    }

    sendJsonError('Metode HTTP tidak didukung.', 405);

} catch (Throwable $e) {
    sendJsonError('Operasi gagal: ' . $e->getMessage(), 500);
}
