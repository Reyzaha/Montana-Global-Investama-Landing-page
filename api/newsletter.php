<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — API: PUBLIC NEWSLETTER SUBSCRIPTION
 * Method: POST
 * Accepts newsletter subscription email, validates, and stores in database.
 */

require_once __DIR__ . '/../backend/config/db.php';
require_once __DIR__ . '/../backend/helpers/response.php';
require_once __DIR__ . '/../backend/helpers/mail_helper.php';
require_once __DIR__ . '/../backend/helpers/realtime_helper.php';
require_once __DIR__ . '/../backend/helpers/auth_helper.php';

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

    $ipAddress = getClientIp();

    // Rate Limiting: Maksimal 5 permintaan langganan per jam per IP
    if (!checkRateLimit('newsletter_sub', 5, 3600)) {
        sendJsonError('Terlalu banyak permintaan langganan buletin dari jaringan Anda. Silakan coba lagi 1 jam kemudian.', 429);
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

    // Trigger Realtime Event for Admin Dashboard
    triggerRealtimeEvent('new_subscriber', 'newsletter', 'create', [
        'id'    => $id,
        'email' => $email
    ]);

    // Send Welcome Email to Subscriber
    try {
        $welcomeSubject = "Selamat Datang di Buletin Wawasan Berkala — PT Montana Global Investama";
        $welcomeHtml = "
            <div style='font-family: Arial, sans-serif; padding: 24px; border: 1px solid #E2E8F0; border-radius: 8px; max-width: 600px; margin: auto;'>
                <div style='border-bottom: 2px solid #C5A059; padding-bottom: 12px; margin-bottom: 16px;'>
                    <h2 style='color: #142563; margin: 0;'>PT Montana Global Investama</h2>
                    <p style='color: #C5A059; margin: 4px 0 0 0; font-size: 0.85rem; font-weight: bold;'>Komoditas, Hilirisasi Mineral, &amp; Energi Hijau</p>
                </div>
                <p>Halo,</p>
                <p>Terima kasih telah bergabung dalam buletin wawasan berkala <strong>PT Montana Global Investama</strong>.</p>
                <p>Melalui buletin ini, Anda akan menerima analisis eksklusif terkait perkembangan hilirisasi nikel, batu bara, serta peluang pembiayaan sindikasi proyek sektor riil langsung dari meja analis kami.</p>
                <div style='background: #F8FAFC; border-left: 4px solid #C5A059; padding: 12px; margin: 16px 0;'>
                    <p style='margin: 0; font-size: 0.9rem;'>Email Terdaftar: <strong>" . htmlspecialchars($email) . "</strong></p>
                </div>
                <p style='font-size: 0.85rem; color: #64748B;'>Jika Anda merasa tidak mendaftar pada situs kami, silakan abaikan email ini.</p>
                <br>
                <p style='margin: 0; color: #142563; font-weight: bold;'>Tim Riset &amp; Publikasi Korporat</p>
                <p style='margin: 2px 0; color: #64748B; font-size: 0.85rem;'>PT Montana Global Investama</p>
            </div>
        ";
        sendMgiEmail($email, $welcomeSubject, $welcomeHtml, strip_tags($welcomeHtml));
    } catch (Throwable $mailEx) {
        error_log('[MGI Mail] Failed to send newsletter welcome email: ' . $mailEx->getMessage());
    }

    sendJsonResponse(['id' => $id], 201, 'Terima kasih! Email Anda berhasil didaftarkan ke buletin wawasan berkala PT Montana Global Investama.');

} catch (Throwable $e) {
    sendJsonError('Gagal memproses langganan: ' . $e->getMessage(), 500);
}
