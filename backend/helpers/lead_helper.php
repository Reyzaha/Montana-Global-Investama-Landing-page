<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — LEAD CAPTURE HELPER
 * Shared constants & utilities for the Google Ads lead funnel
 * (public api/leads.php and admin api/admin/leads.php).
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/mail_helper.php';

// Pilihan rentang rencana nominal investasi (ubah di sini bila perlu).
// Disesuaikan dengan minimum paket proyek saat ini (Rp 5 Miliar).
const LEAD_INVESTMENT_RANGES = [
    '< Rp 5 Miliar',
    'Rp 5 – 10 Miliar',
    'Rp 10 – 25 Miliar',
    '> Rp 25 Miliar',
    'Belum tahu, ingin diskusi dulu',
];

const LEAD_STATUSES = [
    'new'        => 'Baru',
    'contacted'  => 'Dihubungi',
    'meeting'    => 'Meeting',
    'site_visit' => 'Site Visit',
    'deal'       => 'Deal',
    'lost'       => 'Tidak Lanjut',
];

const LEAD_ACCOUNT_TYPES = [
    'perorangan' => 'Perorangan',
    'perusahaan' => 'Perusahaan',
];

// Selaras dengan pilihan badan hukum di register.html
const LEAD_LEGAL_ENTITIES = ['Perseroan Terbatas (PT)', 'Persekutuan Komanditer (CV)', 'PT Perorangan', 'Usaha Dagang (UD)', 'Tidak ada'];

// Setting keys yang aman diekspos ke publik (JANGAN masukkan token rahasia di sini).
const LEAD_PUBLIC_SETTING_KEYS = [
    'google_ads_id',
    'google_ads_conversion_label',
];

/**
 * Ambil beberapa nilai dari tabel system_settings sekaligus.
 */
function getLeadSettings(PDO $db, array $keys): array {
    $result = array_fill_keys($keys, '');
    if (empty($keys)) return $result;

    try {
        $placeholders = implode(',', array_fill(0, count($keys), '?'));
        $stmt = $db->prepare("SELECT setting_key, setting_value FROM system_settings WHERE setting_key IN ($placeholders)");
        $stmt->execute($keys);
        foreach ($stmt->fetchAll() as $row) {
            $result[$row['setting_key']] = (string)($row['setting_value'] ?? '');
        }
    } catch (Throwable $e) {
        error_log('[MGI Lead] Gagal membaca system_settings: ' . $e->getMessage());
    }
    return $result;
}

/**
 * Normalisasi nomor WhatsApp Indonesia ke format internasional 62xxxxxxxxxx.
 * Mengembalikan null jika format tidak valid.
 */
function normalizeIndonesianPhone(string $raw): ?string {
    $digits = preg_replace('/\D+/', '', $raw);
    if ($digits === '' || $digits === null) return null;

    if (str_starts_with($digits, '0')) {
        $digits = '62' . substr($digits, 1);
    } elseif (str_starts_with($digits, '8')) {
        $digits = '62' . $digits;
    }

    // 62 + 8..13 digit, nomor seluler Indonesia diawali 8
    if (!preg_match('/^628\d{7,12}$/', $digits)) {
        return null;
    }
    return $digits;
}

function cleanLeadText(?string $value, int $maxLen): ?string {
    if ($value === null) return null;
    $value = trim(strip_tags($value));
    $value = preg_replace('/\s+/u', ' ', $value);
    if ($value === '') return null;
    return mb_substr($value, 0, $maxLen);
}

/**
 * Kirim notifikasi lead baru ke email dan/atau Telegram (jika dikonfigurasi).
 * Tidak pernah melempar exception agar proses simpan lead tidak terganggu.
 */
function notifyNewLead(PDO $db, array $lead): void {
    $mailCfg = getMailSettings($db);
    $s = getLeadSettings($db, ['telegram_bot_token', 'telegram_chat_id']);

    $isCorp = ($lead['account_type'] ?? 'perorangan') === 'perusahaan';
    $source = $lead['utm_source'] ?? ($lead['gclid'] ? 'Google Ads' : 'Website Langsung');
    $headline = !empty($lead['admin_notes']) && str_contains($lead['admin_notes'], 'Portal Investor') 
        ? 'Minat Investasi Baru (Portal Investor)' 
        : 'Lead Baru (Konsultasi Calon Investor)';

    $lines = [
        $headline . ' — PT Montana Global Investama',
        '------------------------------------------------------------',
        'Kategori : ' . ($isCorp ? 'PERUSAHAAN (Institusi)' : 'Perorangan (Individu)'),
    ];
    if ($isCorp) {
        $lines[] = 'Perusahaan: ' . trim(($lead['legal_entity'] ?? '') . ' ' . ($lead['business_name'] ?? ''));
        $lines[] = 'PIC       : ' . $lead['full_name'] . (!empty($lead['pic_position']) ? ' (' . $lead['pic_position'] . ')' : '');
    } else {
        $lines[] = 'Nama      : ' . $lead['full_name'];
    }
    array_push($lines,
        'Email     : ' . ($lead['email'] ?? '-'),
        'Telepon   : ' . ($lead['phone'] ?? '-'),
        'Kota      : ' . ($lead['city'] ?? '-'),
        'Rencana   : ' . ($lead['investment_range'] ?? '-'),
        'Sumber    : ' . $source,
        'Catatan   : ' . ($lead['admin_notes'] ?? '-'),
        'Waktu     : ' . date('d F Y, H:i:s T')
    );
    $text = implode("\n", $lines);

    $htmlBody = '
    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; background: #ffffff;">
      <div style="background-color: #0b1d3a; padding: 24px; text-align: center; color: #ffffff;">
        <h2 style="margin: 0; font-size: 20px; font-weight: bold; color: #ffffff;">PT MONTANA GLOBAL INVESTAMA</h2>
        <p style="margin: 6px 0 0; font-size: 13px; color: #c29b38; font-weight: 600;">' . htmlspecialchars($headline) . '</p>
      </div>
      <div style="padding: 24px; color: #334155; line-height: 1.6; font-size: 14px;">
        <p style="margin-top: 0;">Halo Tim MGI,</p>
        <p>Telah masuk data calon investor baru yang memerlukan tindak lanjut dari Relationship Manager (RM):</p>
        
        <table style="width: 100%; border-collapse: collapse; margin: 20px 0; background: #f8fafc; border-radius: 6px;">
          <tr>
            <td style="padding: 10px 14px; border-bottom: 1px solid #e2e8f0; font-weight: bold; width: 35%;">Kategori Akun</td>
            <td style="padding: 10px 14px; border-bottom: 1px solid #e2e8f0;">' . ($isCorp ? '<span style="color:#0071E3; font-weight:bold;">Perusahaan (Institusi)</span>' : '<span style="color:#059669; font-weight:bold;">Perorangan</span>') . '</td>
          </tr>';
    if ($isCorp) {
        $htmlBody .= '
          <tr>
            <td style="padding: 10px 14px; border-bottom: 1px solid #e2e8f0; font-weight: bold;">Badan Usaha / PT</td>
            <td style="padding: 10px 14px; border-bottom: 1px solid #e2e8f0; font-weight: bold;">' . htmlspecialchars(trim(($lead['legal_entity'] ?? '') . ' ' . ($lead['business_name'] ?? ''))) . '</td>
          </tr>
          <tr>
            <td style="padding: 10px 14px; border-bottom: 1px solid #e2e8f0; font-weight: bold;">Kontak PIC</td>
            <td style="padding: 10px 14px; border-bottom: 1px solid #e2e8f0;">' . htmlspecialchars($lead['full_name']) . (!empty($lead['pic_position']) ? ' (' . htmlspecialchars($lead['pic_position']) . ')' : '') . '</td>
          </tr>';
    } else {
        $htmlBody .= '
          <tr>
            <td style="padding: 10px 14px; border-bottom: 1px solid #e2e8f0; font-weight: bold;">Nama Investor</td>
            <td style="padding: 10px 14px; border-bottom: 1px solid #e2e8f0; font-weight: bold;">' . htmlspecialchars($lead['full_name']) . '</td>
          </tr>';
    }
    $htmlBody .= '
          <tr>
            <td style="padding: 10px 14px; border-bottom: 1px solid #e2e8f0; font-weight: bold;">Alamat Email</td>
            <td style="padding: 10px 14px; border-bottom: 1px solid #e2e8f0;"><a href="mailto:' . htmlspecialchars($lead['email'] ?? '') . '" style="color: #2563eb; font-weight: 600;">' . htmlspecialchars($lead['email'] ?? '-') . '</a></td>
          </tr>
          <tr>
            <td style="padding: 10px 14px; border-bottom: 1px solid #e2e8f0; font-weight: bold;">Nomor Telepon</td>
            <td style="padding: 10px 14px; border-bottom: 1px solid #e2e8f0; font-weight: 600;">' . htmlspecialchars($lead['phone'] ?? '-') . '</td>
          </tr>
          <tr>
            <td style="padding: 10px 14px; border-bottom: 1px solid #e2e8f0; font-weight: bold;">Rencana Nominal</td>
            <td style="padding: 10px 14px; border-bottom: 1px solid #e2e8f0; color: #b45309; font-weight: bold; font-size: 15px;">' . htmlspecialchars($lead['investment_range'] ?? '-') . '</td>
          </tr>
          <tr>
            <td style="padding: 10px 14px; border-bottom: 1px solid #e2e8f0; font-weight: bold;">Sumber / Channel</td>
            <td style="padding: 10px 14px; border-bottom: 1px solid #e2e8f0;">' . htmlspecialchars($source) . '</td>
          </tr>';
    if (!empty($lead['admin_notes'])) {
        $htmlBody .= '
          <tr>
            <td style="padding: 10px 14px; font-weight: bold; vertical-align: top;">Rincian Pengajuan</td>
            <td style="padding: 10px 14px; white-space: pre-line;">' . htmlspecialchars($lead['admin_notes']) . '</td>
          </tr>';
    }
    $htmlBody .= '
        </table>

        <div style="text-align: center; margin: 28px 0;">
          <a href="https://montanainvestama.com/portal-admin-mgi-gateway/leads.php" style="background-color: #c29b38; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 14px; display: inline-block;">
            Buka Portal Admin &amp; Kelola Lead &rarr;
          </a>
        </div>
      </div>
      <div style="background: #f1f5f9; padding: 16px; text-align: center; color: #64748b; font-size: 12px; border-top: 1px solid #e2e8f0;">
        Notifikasi otomatis sistem terintegrasi PT Montana Global Investama.<br>
        Email resmi korespondensi: <strong>' . htmlspecialchars($mailCfg['official_email']) . '</strong>
      </div>
    </div>';

    // 1. Kirim Email (prioritas ke lead_notify_email atau official_email)
    $to = trim($mailCfg['lead_notify_email'] ?: $mailCfg['official_email']);
    if (empty($to)) {
        $to = 'contact@montanainvestama.com';
    }
    $subject = '[' . $headline . '] ' . ($lead['business_name'] ?: $lead['full_name']) . ' — ' . ($lead['investment_range'] ?? '');
    sendMgiEmail($to, $subject, $htmlBody, $text, $lead['email'] ?? null);

    // 2. Telegram
    $token = trim($s['telegram_bot_token']);
    $chatId = trim($s['telegram_chat_id']);
    if ($token !== '' && $chatId !== '') {
        try {
            $url = 'https://api.telegram.org/bot' . rawurlencode($token) . '/sendMessage';
            $payload = http_build_query(['chat_id' => $chatId, 'text' => $text, 'disable_web_page_preview' => 'true']);
            $ctx = stream_context_create([
                'http' => [
                    'method'  => 'POST',
                    'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
                    'content' => $payload,
                    'timeout' => 4,
                    'ignore_errors' => true,
                ],
            ]);
            @file_get_contents($url, false, $ctx);
        } catch (Throwable $e) {
            error_log('[MGI Lead] Telegram notifikasi gagal: ' . $e->getMessage());
        }
    }
}
