<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — ENTERPRISE MAIL & NOTIFICATION HELPER
 * Native SSL/TLS SMTP client with fallback to mail()
 */

require_once __DIR__ . '/../config/db.php';

function getMailSettings(?PDO $db = null): array {
    if (!$db) {
        $db = getDB();
    }
    $defaults = [
        'lead_notify_email' => getenv('LEAD_NOTIFY_EMAIL') ?: 'contact@montanainvestama.com',
        'official_email'    => getenv('OFFICIAL_EMAIL') ?: 'contact@montanainvestama.com',
        'smtp_host'         => getenv('SMTP_HOST') ?: 'smtp.hostinger.com',
        'smtp_port'         => getenv('SMTP_PORT') ?: '465',
        'smtp_secure'       => getenv('SMTP_SECURE') ?: 'ssl',
        'smtp_user'         => getenv('SMTP_USER') ?: 'contact@montanainvestama.com',
        'smtp_pass'         => getenv('SMTP_PASS') ?: '',
        'smtp_from_name'    => getenv('SMTP_FROM_NAME') ?: 'PT Montana Global Investama',
        'resend_api_key'    => getenv('RESEND_API_KEY') ?: '',
        'mail_driver'       => getenv('MAIL_DRIVER') ?: 'smtp'
    ];

    try {
        $stmt = $db->query("
            SELECT setting_key, setting_value 
            FROM system_settings 
            WHERE setting_key IN ('lead_notify_email', 'official_email', 'smtp_host', 'smtp_port', 'smtp_secure', 'smtp_user', 'smtp_pass', 'smtp_from_name', 'resend_api_key', 'mail_driver')
        ");
        while ($row = $stmt->fetch()) {
            if ($row['setting_value'] !== null && $row['setting_value'] !== '') {
                // Only override if env was empty or defaults
                $defaults[$row['setting_key']] = (string)$row['setting_value'];
            }
        }
    } catch (Throwable $e) {
        error_log('[MGI Mail] Gagal membaca konfigurasi mail: ' . $e->getMessage());
    }

    if (empty($defaults['lead_notify_email'])) {
        $defaults['lead_notify_email'] = $defaults['official_email'] ?: 'contact@montanainvestama.com';
    }
    return $defaults;
}

/**
 * Send email via Resend API (HTTP POST /emails)
 */
function sendResendEmail(string $apiKey, string $from, string $to, string $subject, string $htmlBody): array {
    if (empty($apiKey)) {
        return ['success' => false, 'message' => 'Resend API key belum diset.'];
    }

    $ch = curl_init('https://api.resend.com/emails');
    $payload = json_encode([
        'from'    => $from,
        'to'      => [$to],
        'subject' => $subject,
        'html'    => $htmlBody
    ]);

    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json'
        ]
    ]);

    $resp = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);

    if ($err) {
        return ['success' => false, 'message' => 'Resend cURL error: ' . $err];
    }

    if ($httpCode >= 200 && $httpCode < 300) {
        return ['success' => true, 'message' => 'Email berhasil dikirim via Resend API.'];
    }

    return ['success' => false, 'message' => "Resend API error (HTTP {$httpCode}): {$resp}"];
}

/**
 * Native Socket-based SMTP Client (Hostinger, Google Workspace, Custom VPS)
 */
function sendSmtpMail(array $cfg, string $to, string $subject, string $htmlBody, string $textBody = '', ?string $replyTo = null): array {
    $host = trim($cfg['smtp_host'] ?: 'smtp.hostinger.com');
    $port = (int)($cfg['smtp_port'] ?: 465);
    $secure = strtolower($cfg['smtp_secure'] ?: 'ssl');
    $user = trim($cfg['smtp_user'] ?: 'contact@montanainvestama.com');
    $pass = (string)($cfg['smtp_pass'] ?? '');
    $fromName = $cfg['smtp_from_name'] ?: 'PT Montana Global Investama';

    if (empty($pass)) {
        return ['success' => false, 'message' => 'Password SMTP belum diatur di Pengaturan Sistem.'];
    }

    $socketPrefix = ($secure === 'ssl' || $port === 465) ? 'ssl://' : '';
    $timeout = 10;
    $errno = 0;
    $errstr = '';

    $socket = @stream_socket_client($socketPrefix . $host . ':' . $port, $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT);
    if (!$socket) {
        return ['success' => false, 'message' => "Gagal terhubung ke {$host}:{$port} ({$errstr})"];
    }

    stream_set_timeout($socket, $timeout);

    $getResponse = function() use ($socket) {
        $data = '';
        while ($str = fgets($socket, 515)) {
            $data .= $str;
            if (substr($str, 3, 1) === ' ') break;
        }
        return $data;
    };

    $sendCommand = function(string $cmd) use ($socket, $getResponse) {
        fputs($socket, $cmd . "\r\n");
        return $getResponse();
    };

    $init = $getResponse();
    if (!str_starts_with($init, '220')) {
        fclose($socket);
        return ['success' => false, 'message' => "Handshake server SMTP gagal: {$init}"];
    }

    $hostname = gethostname() ?: 'montanainvestama.com';
    $ehlo = $sendCommand('EHLO ' . $hostname);
    if (!str_starts_with($ehlo, '250')) {
        fclose($socket);
        return ['success' => false, 'message' => "EHLO ditolak: {$ehlo}"];
    }

    if ($secure === 'tls' && $port === 587) {
        $starttls = $sendCommand('STARTTLS');
        if (!str_starts_with($starttls, '220')) {
            fclose($socket);
            return ['success' => false, 'message' => "STARTTLS gagal: {$starttls}"];
        }
        stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
        $sendCommand('EHLO ' . $hostname);
    }

    $auth = $sendCommand('AUTH LOGIN');
    if (!str_starts_with($auth, '334')) {
        fclose($socket);
        return ['success' => false, 'message' => "AUTH LOGIN tidak didukung: {$auth}"];
    }

    $sendUser = $sendCommand(base64_encode(trim($user)));
    if (!str_starts_with($sendUser, '334')) {
        fclose($socket);
        return ['success' => false, 'message' => "Username SMTP ditolak: {$sendUser}"];
    }

    $passAttempt = $pass;
    $sendPass = $sendCommand(base64_encode($passAttempt));
    if (!str_starts_with($sendPass, '235') && $passAttempt !== trim($passAttempt)) {
        // Coba ulang dengan password yang di-trim spasi jika ada spasi ekstra
        $sendPass = $sendCommand(base64_encode(trim($passAttempt)));
    }

    if (!str_starts_with($sendPass, '235')) {
        fclose($socket);
        $respStr = trim($sendPass);
        return [
            'success' => false, 
            'message' => "Autentikasi SMTP gagal ({$respStr}): Password untuk akun {$user} tidak valid. Pastikan Anda memasukkan Password Mailbox Email Hostinger (bukan password login akun hPanel). Coba tes login terlebih dahulu di https://mail.hostinger.com."
        ];
    }

    $mailFrom = $sendCommand("MAIL FROM:<{$user}>");
    if (!str_starts_with($mailFrom, '250')) {
        fclose($socket);
        return ['success' => false, 'message' => "MAIL FROM ditolak: {$mailFrom}"];
    }

    $rcptTo = $sendCommand("RCPT TO:<{$to}>");
    if (!str_starts_with($rcptTo, '250')) {
        fclose($socket);
        return ['success' => false, 'message' => "RCPT TO ditolak: {$rcptTo}"];
    }

    $dataCmd = $sendCommand("DATA");
    if (!str_starts_with($dataCmd, '354')) {
        fclose($socket);
        return ['success' => false, 'message' => "DATA command ditolak: {$dataCmd}"];
    }

    $boundary = "b_" . md5(uniqid((string)time(), true));
    $headers = [
        "MIME-Version: 1.0",
        "From: =?UTF-8?B?" . base64_encode($fromName) . "?= <{$user}>",
        "To: <{$to}>",
        "Date: " . date('r'),
        "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=",
        "Content-Type: multipart/alternative; boundary=\"{$boundary}\""
    ];
    if ($replyTo) {
        $headers[] = "Reply-To: <{$replyTo}>";
    }

    $message = implode("\r\n", $headers) . "\r\n\r\n";
    $message .= "--{$boundary}\r\n";
    $message .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $message .= "Content-Transfer-Encoding: base64\r\n\r\n";
    $message .= chunk_split(base64_encode($textBody ?: strip_tags($htmlBody))) . "\r\n";
    $message .= "--{$boundary}\r\n";
    $message .= "Content-Type: text/html; charset=UTF-8\r\n";
    $message .= "Content-Transfer-Encoding: base64\r\n\r\n";
    $message .= chunk_split(base64_encode($htmlBody)) . "\r\n";
    $message .= "--{$boundary}--\r\n";
    $message .= ".";

    $sendData = $sendCommand($message);
    $sendCommand("QUIT");
    fclose($socket);

    if (str_starts_with($sendData, '250')) {
        return ['success' => true, 'message' => 'Email berhasil terkirim via Hostinger SMTP.'];
    }

    return ['success' => false, 'message' => "Pengiriman email ditolak: {$sendData}"];
}

/**
 * Universal Sender: tries SMTP first, falls back to mail()
 */
function sendMgiEmail(string $to, string $subject, string $htmlBody, string $textBody = '', ?string $replyTo = null): bool {
    $cfg = getMailSettings();

    // 1. Coba via Resend API jika driver di-set ke resend atau resend_api_key terisi dan smtp_pass kosong
    if (($cfg['mail_driver'] === 'resend' || (!empty($cfg['resend_api_key']) && empty($cfg['smtp_pass']))) && !empty($cfg['resend_api_key'])) {
        $from = "{$cfg['smtp_from_name']} <{$cfg['smtp_user']}>";
        $res = sendResendEmail($cfg['resend_api_key'], $from, $to, $subject, $htmlBody);
        if ($res['success']) {
            return true;
        }
        error_log('[MGI Mail] Resend gagal (' . $res['message'] . '), mencoba driver alternatif.');
    }

    // 2. Coba via SMTP jika password terkonfigurasi
    if (!empty($cfg['smtp_pass'])) {
        $res = sendSmtpMail($cfg, $to, $subject, $htmlBody, $textBody, $replyTo);
        if ($res['success']) {
            return true;
        }
        error_log('[MGI Mail] SMTP gagal (' . $res['message'] . '), menggunakan fallback mail().');
    }

    // 3. Fallback: If mail_driver is log or credentials are unconfigured, log safely without blocking
    if ($cfg['mail_driver'] === 'log' || (empty($cfg['smtp_pass']) && empty($cfg['resend_api_key']))) {
        error_log("[MGI Mail Sim] Sent to: {$to} | Subject: {$subject} | Snippet: " . substr(strip_tags($htmlBody), 0, 100));
        return true;
    }

    // 4. Fallback native mail()
    $fromName = $cfg['smtp_from_name'] ?: 'PT Montana Global Investama';
    $fromEmail = $cfg['smtp_user'] ?: 'contact@montanainvestama.com';
    $headers = [
        'MIME-Version: 1.0',
        'Content-Type: text/html; charset=UTF-8',
        'From: =?UTF-8?B?' . base64_encode($fromName) . '?= <' . $fromEmail . '>',
        'Reply-To: ' . ($replyTo ?: $fromEmail)
    ];
    $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
    return @mail($to, $encodedSubject, $htmlBody, implode("\r\n", $headers));
}
