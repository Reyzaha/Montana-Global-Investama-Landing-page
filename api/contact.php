<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — API: PUBLIC CONTACT FORM SUBMISSION
 * Method: POST
 * Accepts contact inquiries, validates input, prevents spam, saves to database,
 * and notifies admin.
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

// 1. Honeypot Anti-Spam Check
if (!empty($input['website']) || !empty($input['honeypot'])) {
    // Bot detected: respond with fake success
    sendJsonResponse(null, 200, 'Pesan Anda berhasil terkirim.');
}

// 2. Input Extraction & Sanitization
$fullName = trim($input['full_name'] ?? '');
$email    = trim($input['email'] ?? '');
$phone    = trim($input['phone'] ?? '');
$subject  = trim($input['subject'] ?? 'Pertanyaan Umum / Konsultasi Investasi');
$message  = trim($input['message'] ?? '');

// 3. Validation
$errors = [];
if (mb_strlen($fullName) < 2) {
    $errors[] = 'Nama lengkap minimal 2 karakter.';
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Format alamat email tidak valid.';
}
if (mb_strlen($message) < 10) {
    $errors[] = 'Pesan terlalu pendek, minimal 10 karakter.';
}

if (!empty($errors)) {
    sendJsonError(implode(' ', $errors), 422, ['validation_errors' => $errors]);
}

try {
    $db = getDB();

    $ipAddress = getClientIp();

    // Rate Limiting: Max 5 submissions per hour from same IP
    $rateStmt = $db->prepare("
        SELECT COUNT(*) FROM contact_inquiries
        WHERE ip_address = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
    ");
    $rateStmt->execute([$ipAddress]);
    if ($rateStmt->fetchColumn() >= 5) {
        sendJsonError('Terlalu banyak permintaan dari koneksi Anda. Silakan coba lagi nanti.', 429);
    }

    // Insert into database
    $stmt = $db->prepare("
        INSERT INTO contact_inquiries (full_name, email, phone, subject, message, status, ip_address, created_at)
        VALUES (?, ?, ?, ?, ?, 'new', ?, NOW())
    ");
    $stmt->execute([$fullName, $email, $phone, $subject, $message, $ipAddress]);
    $inquiryId = (int)$db->lastInsertId();

    // Trigger Realtime Event for Admin Dashboard immediately!
    triggerRealtimeEvent('new_inquiry', 'inquiries', 'create', [
        'id'        => $inquiryId,
        'full_name' => $fullName,
        'email'     => $email,
        'subject'   => $subject
    ]);

    // Notify Admin & User via Email
    try {
        $settingStmt = $db->query("SELECT setting_value FROM system_settings WHERE setting_key = 'lead_notify_email' LIMIT 1");
        $notifyEmail = $settingStmt->fetchColumn() ?: 'contact@montanainvestama.com';

        $mailSubject = "[Pesan Masuk Baru] {$subject} — dari {$fullName}";
        $mailHtml = "
            <div style='font-family: Arial, sans-serif; padding: 20px; border: 1px solid #E2E8F0; border-radius: 8px;'>
                <h3 style='color: #142563; margin-top: 0;'>Pesan Masuk Baru dari Formulir Kontak</h3>
                <p><strong>Nama Pengirim:</strong> " . htmlspecialchars($fullName) . "</p>
                <p><strong>Email:</strong> <a href='mailto:" . htmlspecialchars($email) . "'>" . htmlspecialchars($email) . "</a></p>
                <p><strong>Nomor Telepon:</strong> " . htmlspecialchars($phone ?: '-') . "</p>
                <p><strong>Perihal:</strong> " . htmlspecialchars($subject) . "</p>
                <p><strong>Pesan:</strong></p>
                <blockquote style='background: #F8FAFC; border-left: 4px solid #C5A059; padding: 12px; margin: 10px 0;'>" . nl2br(htmlspecialchars($message)) . "</blockquote>
                <p style='color: #64748B; font-size: 0.85rem;'>ID Pesan: #{$inquiryId} | IP: {$ipAddress} | Waktu: " . date('d F Y H:i:s') . "</p>
            </div>
        ";

        sendMgiEmail($notifyEmail, $mailSubject, $mailHtml, strip_tags($mailHtml));

        // Send Corporate Auto-reply Confirmation to User
        $userSubject = "Konfirmasi Penerimaan Pesan — PT Montana Global Investama";
        $userHtml = "
            <div style='font-family: Arial, sans-serif; padding: 24px; border: 1px solid #E2E8F0; border-radius: 8px; max-width: 600px; margin: auto;'>
                <div style='border-bottom: 2px solid #C5A059; padding-bottom: 12px; margin-bottom: 16px;'>
                    <h2 style='color: #142563; margin: 0; font-size: 1.25rem;'>PT Montana Global Investama</h2>
                    <p style='color: #C5A059; margin: 4px 0 0 0; font-size: 0.85rem; font-weight: bold;'>Manajer Investasi Proyek Sektor Riil</p>
                </div>
                <p>Kepada Yth. <strong>" . htmlspecialchars($fullName) . "</strong>,</p>
                <p>Terima kasih telah menghubungi PT Montana Global Investama. Pesan dan permohonan informasi Anda telah kami terima dengan rincian sebagai berikut:</p>
                <div style='background: #F8FAFC; padding: 14px; border-left: 4px solid #142563; margin: 16px 0; font-size: 0.9rem;'>
                    <p style='margin: 4px 0;'><strong>Perihal:</strong> " . htmlspecialchars($subject) . "</p>
                    <p style='margin: 4px 0;'><strong>Waktu Pengiriman:</strong> " . date('d F Y H:i:s T') . "</p>
                </div>
                <p>Tim Investor Relations dan Kemitraan kami sedang meninjau pertanyaan Anda dan akan menghubungi Anda sesegera mungkin.</p>
                <br>
                <p style='margin: 0; color: #142563; font-weight: bold;'>Divisi Komunikasi Korporat & Investor Relations</p>
                <p style='margin: 4px 0; color: #64748B; font-size: 0.85rem;'>PT Montana Global Investama</p>
            </div>
        ";
        sendMgiEmail($email, $userSubject, $userHtml, strip_tags($userHtml));

    } catch (Throwable $mailEx) {
        error_log('[MGI Mail] Failed to send contact emails: ' . $mailEx->getMessage());
    }

    sendJsonResponse([
        'inquiry_id' => $inquiryId
    ], 201, 'Terima kasih, pesan Anda telah berhasil dikirimkan. Tim Investor Relations kami akan segera menghubungi Anda.');

} catch (Throwable $e) {
    sendJsonError('Gagal menyimpan pesan: ' . $e->getMessage(), 500);
}
