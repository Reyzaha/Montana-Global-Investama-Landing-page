<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — API: ADMIN ARTICLES CRUD
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

function generateSlug($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    return empty($text) ? 'article-' . time() : $text;
}

try {
    $db = getDB();

    // 1. GET: Ambil daftar artikel atau single artikel by ID
    if ($method === 'GET') {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
        if ($id) {
            $stmt = $db->prepare("SELECT * FROM articles WHERE id = ?");
            $stmt->execute([$id]);
            $article = $stmt->fetch();
            if (!$article) {
                sendJsonError('Artikel tidak ditemukan.', 404);
            }
            sendJsonResponse($article);
        }

        $category = isset($_GET['category']) ? trim($_GET['category']) : '';
        if ($category !== '') {
            $stmt = $db->prepare("SELECT * FROM articles WHERE category = ? ORDER BY published_at DESC, id DESC");
            $stmt->execute([$category]);
        } else {
            $stmt = $db->query("SELECT * FROM articles ORDER BY published_at DESC, id DESC");
        }
        $articles = $stmt->fetchAll();
        sendJsonResponse($articles);
    }

    // 2. POST: Buat artikel baru
    if ($method === 'POST') {
        $title = trim($input['title'] ?? '');
        $category = trim($input['category'] ?? 'Wawasan Industri');
        $excerpt = trim($input['excerpt'] ?? '');
        $content = trim($input['content'] ?? '');
        $coverImage = trim($input['cover_image'] ?? '');
        $authorName = trim($input['author_name'] ?? 'Tim Riset & Analis MGI');
        $status = in_array($input['status'] ?? '', ['published', 'draft']) ? $input['status'] : 'published';
        
        $slug = trim($input['slug'] ?? '');
        if (empty($slug)) {
            $slug = generateSlug($title);
        } else {
            $slug = generateSlug($slug);
        }

        if (empty($title) || empty($content)) {
            sendJsonError('Judul dan isi konten artikel wajib diisi.');
        }

        // Cek duplikasi slug
        $stmt = $db->prepare("SELECT COUNT(*) FROM articles WHERE slug = ?");
        $stmt->execute([$slug]);
        if ($stmt->fetchColumn() > 0) {
            $slug .= '-' . time();
        }

        if (empty($excerpt)) {
            $excerpt = mb_substr(strip_tags($content), 0, 160) . '...';
        }

        $stmt = $db->prepare("
            INSERT INTO articles (title, slug, category, excerpt, content, cover_image, author_name, status, published_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$title, $slug, $category, $excerpt, $content, $coverImage, $authorName, $status]);
        $id = (int)$db->lastInsertId();

        logAdminActivity('create', 'articles', (string)$id, "Admin membuat artikel baru: {$title}");
        sendJsonResponse(['id' => $id, 'slug' => $slug], 201, 'Artikel berhasil dipublikasikan.');
    }

    // 3. PUT: Update artikel atau toggle status publish/draft
    if ($method === 'PUT') {
        $id = (int)($input['id'] ?? ($_GET['id'] ?? 0));
        if (!$id) {
            sendJsonError('ID artikel tidak valid.');
        }

        // Action toggle status
        if (isset($input['action']) && $input['action'] === 'toggle_status') {
            $stmt = $db->prepare("SELECT status FROM articles WHERE id = ?");
            $stmt->execute([$id]);
            $currentStatus = $stmt->fetchColumn();
            $newStatus = ($currentStatus === 'published') ? 'draft' : 'published';

            $stmt = $db->prepare("UPDATE articles SET status = ? WHERE id = ?");
            $stmt->execute([$newStatus, $id]);
            logAdminActivity('update', 'articles', (string)$id, "Admin mengubah status artikel #{$id} menjadi {$newStatus}");
            sendJsonResponse(['status' => $newStatus], 200, "Status artikel berhasil diubah menjadi {$newStatus}.");
        }

        $title = trim($input['title'] ?? '');
        $category = trim($input['category'] ?? 'Wawasan Industri');
        $excerpt = trim($input['excerpt'] ?? '');
        $content = trim($input['content'] ?? '');
        $coverImage = trim($input['cover_image'] ?? '');
        $authorName = trim($input['author_name'] ?? 'Tim Riset & Analis MGI');
        $status = in_array($input['status'] ?? '', ['published', 'draft']) ? $input['status'] : 'published';
        
        $slug = trim($input['slug'] ?? '');
        if (empty($slug)) {
            $slug = generateSlug($title);
        } else {
            $slug = generateSlug($slug);
        }

        if (empty($title) || empty($content)) {
            sendJsonError('Judul dan isi konten artikel wajib diisi.');
        }

        // Cek duplikasi slug kecuali milik sendiri
        $stmt = $db->prepare("SELECT COUNT(*) FROM articles WHERE slug = ? AND id != ?");
        $stmt->execute([$slug, $id]);
        if ($stmt->fetchColumn() > 0) {
            $slug .= '-' . time();
        }

        if (empty($excerpt)) {
            $excerpt = mb_substr(strip_tags($content), 0, 160) . '...';
        }

        $stmt = $db->prepare("
            UPDATE articles SET
                title = ?, slug = ?, category = ?, excerpt = ?,
                content = ?, cover_image = ?, author_name = ?, status = ?
            WHERE id = ?
        ");
        $stmt->execute([$title, $slug, $category, $excerpt, $content, $coverImage, $authorName, $status, $id]);

        logAdminActivity('update', 'articles', (string)$id, "Admin memperbarui artikel #{$id}: {$title}");
        sendJsonResponse(['id' => $id, 'slug' => $slug], 200, 'Artikel berhasil diperbarui.');
    }

    // 4. DELETE: Hapus artikel
    if ($method === 'DELETE') {
        $id = (int)($input['id'] ?? ($_GET['id'] ?? 0));
        if (!$id) {
            sendJsonError('ID artikel tidak valid.');
        }

        $stmt = $db->prepare("DELETE FROM articles WHERE id = ?");
        $stmt->execute([$id]);

        logAdminActivity('delete', 'articles', (string)$id, "Admin menghapus artikel #{$id}");
        sendJsonResponse(null, 200, 'Artikel berhasil dihapus.');
    }

    sendJsonError('Metode HTTP tidak didukung.', 405);

} catch (Throwable $e) {
    sendJsonError('Operasi gagal: ' . $e->getMessage(), 500);
}
