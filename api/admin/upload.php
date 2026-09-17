<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — API: ADMIN FILE UPLOAD
 * Securely handles image and attachment uploads for Projects and CMS
 * Method: POST (multipart/form-data)
 */

require_once __DIR__ . '/../../backend/config/db.php';
require_once __DIR__ . '/../../backend/helpers/response.php';
require_once __DIR__ . '/../../backend/helpers/auth_helper.php';

$admin = requireAdminAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonError('Method harus POST.', 405);
}

try {
    if (empty($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        $errorCode = $_FILES['file']['error'] ?? UPLOAD_ERR_NO_FILE;
        $errorMessages = [
            UPLOAD_ERR_INI_SIZE   => 'Ukuran file melebihi batas upload server (upload_max_filesize).',
            UPLOAD_ERR_FORM_SIZE  => 'Ukuran file melebihi batas form.',
            UPLOAD_ERR_PARTIAL    => 'File hanya terunggah sebagian.',
            UPLOAD_ERR_NO_FILE    => 'Tidak ada file yang diunggah.',
            UPLOAD_ERR_NO_TMP_DIR => 'Folder temporary server tidak ditemukan.',
            UPLOAD_ERR_CANT_WRITE => 'Gagal menulis file ke disk server.',
            UPLOAD_ERR_EXTENSION  => 'Ekstensi file diblokir oleh ekstensi PHP.'
        ];
        sendJsonError($errorMessages[$errorCode] ?? 'Terjadi kesalahan saat mengunggah file.');
    }

    $file = $_FILES['file'];
    $folder = preg_replace('/[^a-zA-Z0-9_\-]/', '', $_POST['folder'] ?? 'projects');
    if (empty($folder)) {
        $folder = 'projects';
    }

    // Maksimal ukuran file: 10 MB
    $maxBytes = 10 * 1024 * 1024;
    if ($file['size'] > $maxBytes) {
        sendJsonError('Ukuran file terlalu besar. Maksimal 10 MB.');
    }

    // Validasi tipe ekstensi yang diizinkan
    $allowedMimes = [
        'image/jpeg' => 'jpg',
        'image/pjpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
        'image/svg+xml' => 'svg'
    ];

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);

    if (!array_key_exists($mime, $allowedMimes)) {
        sendJsonError("Format file tidak didukung ({$mime}). Harap unggah gambar JPG, PNG, WebP, GIF, atau SVG.");
    }

    $extension = $allowedMimes[$mime];
    $origName = pathinfo($file['name'], PATHINFO_FILENAME);
    $cleanOrigName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', substr($origName, 0, 30));

    // Direktori target di bawah assets/uploads/<folder>/
    $targetDir = __DIR__ . '/../../assets/uploads/' . $folder;
    if (!is_dir($targetDir)) {
        if (!mkdir($targetDir, 0755, true)) {
            sendJsonError('Gagal membuat direktori upload di server.', 500);
        }
    }

    // Nama file unik
    $uniqueName = $cleanOrigName . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
    $destination = $targetDir . '/' . $uniqueName;

    $moved = is_uploaded_file($file['tmp_name']) 
        ? move_uploaded_file($file['tmp_name'], $destination) 
        : (copy($file['tmp_name'], $destination) && @unlink($file['tmp_name']));

    if (!$moved) {
        sendJsonError('Gagal memindahkan file yang diunggah ke folder penyimpanan.', 500);
    }

    // Path relatif dari root aplikasi untuk disimpan di DB
    $relativePath = 'assets/uploads/' . $folder . '/' . $uniqueName;

    logAdminActivity('upload_file', 'assets', null, "Admin uploaded file '{$uniqueName}' into '{$folder}'");

    sendJsonResponse([
        'file_path' => $relativePath,
        'filename'  => $uniqueName,
        'size'      => $file['size'],
        'mime'      => $mime,
        'url'       => $relativePath
    ], 201, 'File attachment berhasil diunggah.');

} catch (Exception $e) {
    sendJsonException($e, 'Terjadi kesalahan sistem saat memproses upload file.');
}
