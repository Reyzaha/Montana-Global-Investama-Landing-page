<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — API: ADMIN SYSTEM SETTINGS
 * Methods: GET, POST
 */

require_once __DIR__ . '/../../backend/config/db.php';
require_once __DIR__ . '/../../backend/helpers/response.php';
require_once __DIR__ . '/../../backend/helpers/auth_helper.php';

$admin = requireAdminAuth();
$method = $_SERVER['REQUEST_METHOD'];

try {
    $db = getDB();

    if ($method === 'GET') {
        $stmt = $db->query("SELECT setting_key, setting_value, description FROM system_settings");
        $rows = $stmt->fetchAll();
        $settings = [];
        foreach ($rows as $r) {
            $settings[$r['setting_key']] = [
                'value' => $r['setting_value'],
                'description' => $r['description']
            ];
        }
        sendJsonResponse($settings);
    }

    if ($method === 'POST' && ($_GET['action'] ?? '') === 'test_email') {
        require_once __DIR__ . '/../../backend/helpers/mail_helper.php';
        $input = getJsonInput();
        $targetEmail = trim($input['target_email'] ?? 'contact@montanainvestama.com');
        if (empty($targetEmail) || !filter_var($targetEmail, FILTER_VALIDATE_EMAIL)) {
            sendJsonError('Alamat email tujuan tidak valid.');
        }

        $cfg = getMailSettings($db);
        if (!empty($input['smtp_pass'])) {
            $cfg['smtp_pass'] = (string)$input['smtp_pass'];
        }
        if (!empty($input['smtp_host'])) {
            $cfg['smtp_host'] = (string)$input['smtp_host'];
        }
        if (!empty($input['smtp_port'])) {
            $cfg['smtp_port'] = (int)$input['smtp_port'];
        }
        if (!empty($input['smtp_user'])) {
            $cfg['smtp_user'] = (string)$input['smtp_user'];
        }

        $subject = '[Uji Coba Sistem] Tes Integrasi Email SMTP PT Montana Global Investama';
        $bodyHtml = '
            <div style="font-family: Arial, sans-serif; padding: 20px; border: 1px solid #e2e8f0; border-radius: 8px;">
                <h3 style="color: #0b1d3a; margin-top:0;">Tes Konfigurasi Email Berhasil!</h3>
                <p>Email ini dikirimkan untuk memverifikasi bahwa integrasi notifikasi lead dan minat investor PT Montana Global Investama berjalan lancar.</p>
                <p><strong>Waktu Pengujian:</strong> ' . date('d F Y, H:i:s T') . '</p>
                <p><strong>Server SMTP:</strong> ' . htmlspecialchars($cfg['smtp_host'] . ':' . $cfg['smtp_port']) . '</p>
                <p><strong>Akun Pengirim:</strong> ' . htmlspecialchars($cfg['smtp_user']) . '</p>
            </div>
        ';

        if (!empty($cfg['smtp_pass'])) {
            $res = sendSmtpMail($cfg, $targetEmail, $subject, $bodyHtml, strip_tags($bodyHtml));
            if ($res['success']) {
                sendJsonResponse($res, 200, 'Email uji coba berhasil dikirim via Hostinger SMTP ke ' . $targetEmail);
            } else {
                sendJsonError('Pengiriman email SMTP gagal: ' . $res['message'], 400);
            }
        } else {
            $sent = sendMgiEmail($targetEmail, $subject, $bodyHtml, strip_tags($bodyHtml));
            if ($sent) {
                sendJsonResponse(null, 200, 'Email uji coba terkirim ke ' . $targetEmail);
            } else {
                sendJsonError('Password SMTP belum diatur dan mail server lokal gagal mengirim. Silakan masukkan password SMTP Hostinger.', 400);
            }
        }
    }

    if ($method === 'POST' || $method === 'PUT') {
        $input = getJsonInput();
        
        $stmtUp = $db->prepare("
            INSERT INTO system_settings (setting_key, setting_value)
            VALUES (?, ?)
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
        ");

        foreach ($input as $key => $val) {
            $valStr = is_bool($val) ? ($val ? '1' : '0') : (string)$val;
            $stmtUp->execute([$key, $valStr]);
        }

        // Auto-sync Google Search Console verification token into index.html
        if (isset($input['gsc_verification_token'])) {
            $token = trim((string)$input['gsc_verification_token']);
            $indexFile = __DIR__ . '/../../index.html';
            if (file_exists($indexFile) && !empty($token)) {
                $html = file_get_contents($indexFile);
                $html = preg_replace(
                    '/<meta name="google-site-verification" content="[^"]*">/',
                    '<meta name="google-site-verification" content="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">',
                    $html
                );
                file_put_contents($indexFile, $html);
            }
        }

        logAdminActivity('update_settings', 'system_settings', null, 'Admin updated system settings');

        sendJsonResponse(null, 200, 'Pengaturan sistem berhasil diperbarui.');
    }

    sendJsonError('Method tidak didukung.', 405);

} catch (Exception $e) {
    sendJsonError('Terjadi kesalahan pengaturan sistem: ' . $e->getMessage(), 500);
}
