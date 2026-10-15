<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — API: ADMIN HERO SECTION CRUD
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

    // 1. GET: Ambil semua hero sections atau single hero by ID
    if ($method === 'GET') {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
        if ($id) {
            $stmt = $db->prepare("SELECT * FROM hero_sections WHERE id = ?");
            $stmt->execute([$id]);
            $hero = $stmt->fetch();
            if (!$hero) {
                sendJsonError('Hero section tidak ditemukan.', 404);
            }
            sendJsonResponse($hero);
        }

        $stmt = $db->query("SELECT * FROM hero_sections ORDER BY sort_order ASC, id DESC");
        $heroes = $stmt->fetchAll();
        sendJsonResponse($heroes);
    }

    // 2. POST: Tambah hero section baru
    if ($method === 'POST') {
        $kicker = trim($input['kicker'] ?? 'Grup & Manajer Investasi Proyek Sektor Riil');
        $title = trim($input['title'] ?? '');
        $titleHighlight = trim($input['title_highlight'] ?? '');
        $subtitle = trim($input['subtitle'] ?? '');
        $ctaPrimaryText = trim($input['cta_primary_text'] ?? 'Katalog Proyek Terbuka');
        $ctaPrimaryUrl = trim($input['cta_primary_url'] ?? 'invest');
        $ctaSecondaryText = trim($input['cta_secondary_text'] ?? 'Profil & Tata Kelola');
        $ctaSecondaryUrl = trim($input['cta_secondary_url'] ?? 'about');
        $mediaType = in_array($input['media_type'] ?? '', ['image', 'video']) ? $input['media_type'] : 'image';
        $mediaUrl = trim($input['media_url'] ?? '');
        $badgeText = trim($input['badge_text'] ?? '');
        $isActive = isset($input['is_active']) ? (int)$input['is_active'] : 1;
        $sortOrder = isset($input['sort_order']) ? (int)$input['sort_order'] : 0;

        if (empty($title) || empty($subtitle)) {
            sendJsonError('Judul utama dan deskripsi subjudul wajib diisi.');
        }

        // Jika diset active, nonaktifkan hero lain jika ingin single active mode
        if ($isActive) {
            $db->exec("UPDATE hero_sections SET is_active = 0");
        }

        $stmt = $db->prepare("
            INSERT INTO hero_sections (
                kicker, title, title_highlight, subtitle,
                cta_primary_text, cta_primary_url, cta_secondary_text, cta_secondary_url,
                media_type, media_url, badge_text, is_active, sort_order
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $kicker, $title, $titleHighlight, $subtitle,
            $ctaPrimaryText, $ctaPrimaryUrl, $ctaSecondaryText, $ctaSecondaryUrl,
            $mediaType, $mediaUrl, $badgeText, $isActive, $sortOrder
        ]);
        $id = (int)$db->lastInsertId();

        logAdminActivity('create', 'hero_sections', (string)$id, "Admin membuat hero section: {$title}");
        sendJsonResponse(['id' => $id], 201, 'Hero section berhasil dibuat.');
    }

    // 3. PUT: Update hero section
    if ($method === 'PUT') {
        $id = (int)($input['id'] ?? ($_GET['id'] ?? 0));
        if (!$id) {
            sendJsonError('ID Hero section tidak valid.');
        }

        $kicker = trim($input['kicker'] ?? '');
        $title = trim($input['title'] ?? '');
        $titleHighlight = trim($input['title_highlight'] ?? '');
        $subtitle = trim($input['subtitle'] ?? '');
        $ctaPrimaryText = trim($input['cta_primary_text'] ?? '');
        $ctaPrimaryUrl = trim($input['cta_primary_url'] ?? '');
        $ctaSecondaryText = trim($input['cta_secondary_text'] ?? '');
        $ctaSecondaryUrl = trim($input['cta_secondary_url'] ?? '');
        $mediaType = in_array($input['media_type'] ?? '', ['image', 'video']) ? $input['media_type'] : 'image';
        $mediaUrl = trim($input['media_url'] ?? '');
        $badgeText = trim($input['badge_text'] ?? '');
        $isActive = isset($input['is_active']) ? (int)$input['is_active'] : 1;
        $sortOrder = isset($input['sort_order']) ? (int)$input['sort_order'] : 0;

        if (empty($title) || empty($subtitle)) {
            sendJsonError('Judul utama dan deskripsi subjudul wajib diisi.');
        }

        if ($isActive) {
            $db->prepare("UPDATE hero_sections SET is_active = 0 WHERE id != ?")->execute([$id]);
        }

        $stmt = $db->prepare("
            UPDATE hero_sections SET
                kicker = ?, title = ?, title_highlight = ?, subtitle = ?,
                cta_primary_text = ?, cta_primary_url = ?, cta_secondary_text = ?, cta_secondary_url = ?,
                media_type = ?, media_url = ?, badge_text = ?, is_active = ?, sort_order = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $kicker, $title, $titleHighlight, $subtitle,
            $ctaPrimaryText, $ctaPrimaryUrl, $ctaSecondaryText, $ctaSecondaryUrl,
            $mediaType, $mediaUrl, $badgeText, $isActive, $sortOrder, $id
        ]);

        logAdminActivity('update', 'hero_sections', (string)$id, "Admin memperbarui hero section ID {$id}");
        sendJsonResponse(['id' => $id], 200, 'Hero section berhasil diperbarui.');
    }

    // 4. DELETE: Hapus hero section
    if ($method === 'DELETE') {
        $id = (int)($_GET['id'] ?? ($input['id'] ?? 0));
        if (!$id) {
            sendJsonError('ID Hero section tidak valid.');
        }

        $stmt = $db->prepare("DELETE FROM hero_sections WHERE id = ?");
        $stmt->execute([$id]);

        logAdminActivity('delete', 'hero_sections', (string)$id, "Admin menghapus hero section ID {$id}");
        sendJsonResponse(['id' => $id], 200, 'Hero section berhasil dihapus.');
    }

} catch (Throwable $e) {
    sendJsonError('Gagal memproses hero section: ' . $e->getMessage(), 500);
}
