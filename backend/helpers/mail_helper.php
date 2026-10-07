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
        'lead_notify_email' => 'contact@montanainvestama.com',
        'official_email'    => 'contact@montanainvestama.com',
        'smtp_host'         => 'smtp.hostinger.com',
        'smtp_port'         => '465',
        'smtp_secure'       => 'ssl',
        'smtp_user'         => 'contact@montanainvestama.com',
        'smtp_pass'         => '',
        'smtp_from_name'    => 'PT Montana Global Investama'
    ];

    try {
        $stmt = $db->query("
            SELECT setting_key, setting_value 
            FROM system_settings 
            WHERE setting_key IN ('lead_notify_email', 'official_email', 'smtp_host', 'smtp_port', 'smtp_secure', 'smtp_user', 'smtp_pass', 'smtp_from_name')
        ");
        while ($row = $stmt->fetch()) {
            if ($row['setting_value'] !== null && $row['setting_value'] !== '') {
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

    $sendUser = $sendCommand(base64_encode($user));
    if (!str_starts_with($sendUser, '334')) {
        fclose($socket);
        return ['success' => false, 'message' => "Username SMTP ditolak: {$sendUser}"];
    }

    $sendPass = $sendCommand(base64_encode($pass));
    if (!str_starts_with($sendPass, '235')) {
        fclose($socket);
        return ['success' => false, 'message' => "Autentikasi SMTP gagal: Password email Hostinger tidak valid."];
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

    // 1. Coba via SMTP jika password terkonfigurasi
    if (!empty($cfg['smtp_pass'])) {
        $res = sendSmtpMail($cfg, $to, $subject, $htmlBody, $textBody, $replyTo);
        if ($res['success']) {
            return true;
        }
        error_log('[MGI Mail] SMTP gagal (' . $res['message'] . '), menggunakan fallback mail().');
    }

    // 2. Fallback mail()
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
