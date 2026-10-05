<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — LEAD CAPTURE HELPER
 * Shared constants & utilities for the Google Ads lead funnel
 * (public api/leads.php and admin api/admin/leads.php).
 */

require_once __DIR__ . '/../config/db.php';

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
    'lead_whatsapp_number',
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
    $s = getLeadSettings($db, ['lead_notify_email', 'telegram_bot_token', 'telegram_chat_id']);

    $isCorp = ($lead['account_type'] ?? 'perorangan') === 'perusahaan';
    $lines = [
        'Lead baru dari Landing Page Konsultasi',
        '--------------------------------------',
        'Kategori : ' . ($isCorp ? 'PERUSAHAAN' : 'Perorangan'),
    ];
    if ($isCorp) {
        $lines[] = 'Perusahaan: ' . trim(($lead['legal_entity'] ?? '') . ' ' . ($lead['business_name'] ?? ''));
        $lines[] = 'PIC      : ' . $lead['full_name'] . (!empty($lead['pic_position']) ? ' (' . $lead['pic_position'] . ')' : '');
    } else {
        $lines[] = 'Nama     : ' . $lead['full_name'];
    }
    array_push($lines,
        'WhatsApp : +' . $lead['phone'],
        'Email    : ' . ($lead['email'] ?? '-'),
        'Kota     : ' . ($lead['city'] ?? '-'),
        'Nominal  : ' . ($lead['investment_range'] ?? '-'),
        'Sumber   : ' . ($lead['utm_source'] ?? ($lead['gclid'] ? 'google_ads' : 'langsung')),
        'Kampanye : ' . ($lead['utm_campaign'] ?? '-'),
        'Keyword  : ' . ($lead['utm_term'] ?? '-'),
        '',
        'Chat langsung: https://wa.me/' . $lead['phone']
    );
    $text = implode("\n", $lines);

    // 1. Email
    $to = trim($s['lead_notify_email']);
    if ($to !== '' && filter_var($to, FILTER_VALIDATE_EMAIL)) {
        try {
            $subject = '=?UTF-8?B?' . base64_encode('[Lead Baru] ' . $lead['full_name'] . ' — ' . ($lead['investment_range'] ?? '')) . '?=';
            $headers = "Content-Type: text/plain; charset=UTF-8\r\n";
            @mail($to, $subject, $text, $headers);
        } catch (Throwable $e) {
            error_log('[MGI Lead] Email notifikasi gagal: ' . $e->getMessage());
        }
    }

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
