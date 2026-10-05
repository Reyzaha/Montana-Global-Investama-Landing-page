<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — API: PUBLIC LEAD CAPTURE
 * GET  ?action=config  → CSRF token + konfigurasi publik (WhatsApp, Google Ads)
 * POST                 → Simpan lead dari form konsultasi
 */

require_once __DIR__ . '/../backend/helpers/response.php';
require_once __DIR__ . '/../backend/helpers/auth_helper.php';
require_once __DIR__ . '/../backend/helpers/lead_helper.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// ---------------------------------------------------------------
// GET: konfigurasi publik
// ---------------------------------------------------------------
if ($method === 'GET') {
    $config = array_fill_keys(LEAD_PUBLIC_SETTING_KEYS, '');
    try {
        $config = getLeadSettings(getDB(), LEAD_PUBLIC_SETTING_KEYS);
    } catch (Throwable $e) {
        error_log('[MGI Lead] Config DB unavailable: ' . $e->getMessage());
    }

    sendJsonResponse([
        'csrf_token'                  => getCsrfToken(),
        'whatsapp_number'             => normalizeIndonesianPhone($config['lead_whatsapp_number']) ?? '',
        'google_ads_id'               => preg_match('/^AW-\d+$/', $config['google_ads_id']) ? $config['google_ads_id'] : '',
        'google_ads_conversion_label' => preg_replace('/[^A-Za-z0-9_\-]/', '', $config['google_ads_conversion_label']),
        'investment_ranges'           => LEAD_INVESTMENT_RANGES,
    ]);
}

// ---------------------------------------------------------------
// POST: simpan lead
// ---------------------------------------------------------------
if ($method === 'POST') {
    $input = getJsonInput();

    // 1. Honeypot anti-bot: field tersembunyi harus kosong. Bot dibalas "sukses" palsu.
    if (!empty($input['website'])) {
        sendJsonResponse(['lead_id' => 0], 201, 'Terima kasih.');
    }

    // 2. CSRF
    if (!validateCsrfToken($input['csrf_token'] ?? null)) {
        sendJsonError('Sesi halaman telah kedaluwarsa. Silakan muat ulang halaman lalu kirim kembali.', 403);
    }

    // 3. Rate limit: maksimal 5 pengiriman per jam per sesi/IP
    if (!checkRateLimit('lead_submit', 5, 3600)) {
        sendJsonError('Terlalu banyak pengiriman. Silakan hubungi kami langsung via WhatsApp.', 429);
    }

    // 4. Validasi
    $errors = [];
    $fullName = cleanLeadText($input['full_name'] ?? null, 120);
    $phone    = normalizeIndonesianPhone((string)($input['phone'] ?? ''));
    $city     = cleanLeadText($input['city'] ?? null, 100);
    $range    = (string)($input['investment_range'] ?? '');
    $consent  = filter_var($input['consent'] ?? false, FILTER_VALIDATE_BOOLEAN);

    if (!$fullName || mb_strlen($fullName) < 3) $errors['full_name'] = 'Nama minimal 3 karakter.';
    if (!$phone) $errors['phone'] = 'Nomor WhatsApp tidak valid. Contoh: 0812 3456 7890';
    if (!$city || mb_strlen($city) < 2) $errors['city'] = 'Kota domisili wajib diisi.';
    if (!in_array($range, LEAD_INVESTMENT_RANGES, true)) $errors['investment_range'] = 'Pilih rencana nominal investasi.';
    if (!$consent) $errors['consent'] = 'Persetujuan dihubungi wajib dicentang.';

    if (!empty($errors)) {
        sendJsonError('Mohon periksa kembali data Anda.', 422, $errors);
    }

    $attr = is_array($input['attribution'] ?? null) ? $input['attribution'] : [];

    try {
        $db = getDB();

        // 5. Cegah duplikat: nomor sama dalam 24 jam terakhir → kembalikan lead lama
        $stmtDup = $db->prepare("SELECT id FROM leads WHERE phone = ? AND created_at >= (NOW() - INTERVAL 1 DAY) ORDER BY id DESC LIMIT 1");
        $stmtDup->execute([$phone]);
        $existingId = $stmtDup->fetchColumn();
        if ($existingId) {
            sendJsonResponse(['lead_id' => (int)$existingId, 'duplicate' => true], 200, 'Data Anda sudah kami terima sebelumnya. Tim kami akan segera menghubungi Anda.');
        }

        $lead = [
            'full_name'        => $fullName,
            'phone'            => $phone,
            'city'             => $city,
            'investment_range' => $range,
            'utm_source'       => cleanLeadText($attr['utm_source'] ?? null, 100),
            'utm_medium'       => cleanLeadText($attr['utm_medium'] ?? null, 100),
            'utm_campaign'     => cleanLeadText($attr['utm_campaign'] ?? null, 150),
            'utm_term'         => cleanLeadText($attr['utm_term'] ?? null, 150),
            'utm_content'      => cleanLeadText($attr['utm_content'] ?? null, 150),
            'gclid'            => cleanLeadText($attr['gclid'] ?? null, 255),
            'gbraid'           => cleanLeadText($attr['gbraid'] ?? null, 255),
            'wbraid'           => cleanLeadText($attr['wbraid'] ?? null, 255),
            'landing_page'     => cleanLeadText($attr['landing_page'] ?? null, 255),
            'referrer'         => cleanLeadText($attr['referrer'] ?? null, 255),
            'ip_address'       => mb_substr(getClientIp(), 0, 45),
            'user_agent'       => mb_substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255),
        ];

        $cols = array_keys($lead);
        $sql = 'INSERT INTO leads (' . implode(',', $cols) . ', consent_at) VALUES (' . implode(',', array_fill(0, count($cols), '?')) . ', NOW())';
        $db->prepare($sql)->execute(array_values($lead));
        $leadId = (int)$db->lastInsertId();

        recordFailedAttempt('lead_submit', 3600); // hitung kuota pengiriman
        notifyNewLead($db, $lead);

        sendJsonResponse(['lead_id' => $leadId], 201, 'Terima kasih! Tim kami akan menghubungi Anda via WhatsApp.');

    } catch (Throwable $e) {
        sendJsonException($e, 'Data belum berhasil terkirim. Silakan coba lagi atau hubungi kami via WhatsApp.');
    }
}

sendJsonError('Method tidak didukung.', 405);
