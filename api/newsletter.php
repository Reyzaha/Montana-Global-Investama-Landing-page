<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — API: PUBLIC NEWSLETTER SUBSCRIPTION
 * Method: POST
 * Accepts newsletter subscription email, validates, and stores in database.
 */

require_once __DIR__ . '/../backend/config/db.php';
require_once __DIR__ . '/../backend/helpers/response.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonError('Metode HTTP tidak didukung. Gunakan POST.', 405);
}

$input = getJsonInput();

// Anti-spam honeypot
if (!empty($input['website']) || !empty($input['honeypot'])) {
    sendJsonResponse(null, 200, 'Email Anda berhasil didaftarkan ke buletin.');
}

$email = trim($input['email'] ?? '');
$sourcePage = trim($input['source_page'] ?? 'landing_footer');

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendJsonError('Format alamat email tidak valid.', 422);
}

try {
    $db = getDB();

    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ipParts = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $ipAddress = trim($ipParts[0]);
    }

    // Check if email already registered
    $checkStmt = $db->prepare("SELECT id, status FROM newsletter_subscribers WHERE email = ? LIMIT 1");
    $checkStmt->execute([$email]);
    $existing = $checkStmt->fetch();

    if ($existing) {
        if ($existing['status'] === 'unsubscribed') {
            // Re-activate
            $db->prepare("UPDATE newsletter_subscribers SET status = 'active', updated_at = NOW() WHERE id = ?")
               ->execute([$existing['id']]);
            sendJsonResponse(null, 200, 'Selamat datang kembali! Langganan buletin wawasan berkala Anda telah diaktifkan kembali.');
        } else {
            sendJsonResponse(null, 200, 'Alamat email Anda sudah terdaftar aktif dalam buletin wawasan berkala MGI.');
        }
    }

    $stmt = $db->prepare("
        INSERT INTO newsletter_subscribers (email, status, source_page, ip_address, subscribed_at)
        VALUES (?, 'active', ?, ?, NOW())
    ");
    $stmt->execute([$email, $sourcePage, $ipAddress]);
    $id = (int)$db->lastInsertId();

    sendJsonResponse(['id' => $id], 201, 'Terima kasih! Email Anda berhasil didaftarkan ke buletin wawasan berkala PT Montana Global Investama.');

} catch (Throwable $e) {
    sendJsonError('Gagal memproses langganan: ' . $e->getMessage(), 500);
}
