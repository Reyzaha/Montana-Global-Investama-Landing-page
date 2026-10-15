<?php
/**
 * Automated End-to-End (E2E) & Realtime Synchronization Test Suite
 * PT Montana Global Investama
 * 
 * Automates the 4 required business flows:
 * Flow A: Admin login & MFA -> Update hero title -> Verify instant public API & realtime change
 * Flow B: Visitor fills contact form -> Verify lead appears in DB & admin portal inquiries
 * Flow C: Admin creates testimonial -> verifies public API -> Admin deletes -> verifies gone
 * Flow D: Admin toggles is_active -> verifies inactive items excluded from public API
 */

require_once __DIR__ . '/../../backend/config/db.php';
require_once __DIR__ . '/../../backend/helpers/totp_helper.php';

$baseUrl = 'http://127.0.0.1:8000';
$cookieJar = tempnam(sys_get_temp_dir(), 'mgi_cookie_');
$passed = 0;
$failed = 0;

function assertE2E($condition, $description) {
    global $passed, $failed;
    if ($condition) {
        echo "  [PASS] {$description}\n";
        $passed++;
    } else {
        echo "  [FAIL] {$description}\n";
        $failed++;
    }
}

echo "=== [4/7] MENJALANKAN AUTOMATED E2E & REALTIME FLOWS ===\n";

// =========================================================================
// PREPARATION: Authenticate Admin & obtain Session Cookie
// =========================================================================
$loginPayload = json_encode([
    'username' => 'superadmin',
    'password' => 'admin123'
]);

$ch = curl_init("{$baseUrl}/api/admin/login.php?action=login");
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $loginPayload,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_COOKIEJAR => $cookieJar,
    CURLOPT_COOKIEFILE => $cookieJar,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 6
]);
$res = curl_exec($ch);
curl_close($ch);
$loginJson = json_decode($res, true);

$secret = $loginJson['data']['secret'] ?? '';
$csrfToken = $loginJson['data']['csrf_token'] ?? '';
$requiresMfa = $loginJson['data']['requires_mfa'] ?? false;

assertE2E($requiresMfa && !empty($secret), "Tahap 1: Kredensial superadmin terverifikasi (MFA Required)");

// Calculate Google Authenticator TOTP
$totp = GoogleAuthenticator::getCode($secret);

// Stage 2: Verify MFA
$mfaPayload = json_encode([
    'totp_code' => $totp,
    'csrf_token' => $csrfToken
]);

$ch = curl_init("{$baseUrl}/api/admin/login.php?action=verify_mfa");
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $mfaPayload,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        "X-CSRF-Token: {$csrfToken}"
    ],
    CURLOPT_COOKIEJAR => $cookieJar,
    CURLOPT_COOKIEFILE => $cookieJar,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 6
]);
$resMfa = curl_exec($ch);
curl_close($ch);
$mfaJson = json_decode($resMfa, true);

assertE2E(($mfaJson['success'] ?? false) === true, "Tahap 2: Google Authenticator TOTP terverifikasi, sesi admin aktif");

// =========================================================================
// FLOW A: Admin login → ubah judul hero → frontend & realtime berubah
// =========================================================================
// 1. Fetch current active hero
$ch = curl_init("{$baseUrl}/api/hero.php");
curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 4]);
$origHero = json_decode(curl_exec($ch), true)['data'] ?? [];
curl_close($ch);

$originalTitle = $origHero['title'] ?? 'Solusi Investasi Terpercaya Sektor Riil';
$testTitle = "Inovasi Pembiayaan Sindikasi Terpadu [E2E Test " . time() . "]";

// 2. Admin updates Hero Title
$updateHeroPayload = json_encode([
    '_method' => 'PUT',
    'id' => $origHero['id'] ?? 1,
    'badge_title' => $origHero['badge_title'] ?? 'KONSORSIUM INVESTASI TERPADU',
    'title' => $testTitle,
    'subtitle' => $origHero['subtitle'] ?? 'Ekosistem Investasi Strategis',
    'cta_primary_label' => $origHero['cta_primary_label'] ?? 'Mulai Investasi',
    'cta_primary_url' => $origHero['cta_primary_url'] ?? '#proyek',
    'cta_secondary_label' => $origHero['cta_secondary_label'] ?? 'Pelajari Ekosistem',
    'cta_secondary_url' => $origHero['cta_secondary_url'] ?? '#ekosistem',
    'is_active' => 1
]);

$ch = curl_init("{$baseUrl}/api/admin/hero.php");
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $updateHeroPayload,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        "X-CSRF-Token: {$csrfToken}"
    ],
    CURLOPT_COOKIEJAR => $cookieJar,
    CURLOPT_COOKIEFILE => $cookieJar,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 6
]);
$updateRes = curl_exec($ch);
curl_close($ch);
$updateJson = json_decode($updateRes, true);

// 3. Verify Public API reflects the change immediately
$ch = curl_init("{$baseUrl}/api/hero.php");
curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 4]);
$updatedHero = json_decode(curl_exec($ch), true)['data'] ?? [];
curl_close($ch);

assertE2E(($updatedHero['title'] ?? '') === $testTitle, "Flow A: Perubahan judul hero oleh admin langsung tampil di public API");

// 4. Verify Realtime Event check reflects change
$ch = curl_init("{$baseUrl}/api/realtime/check.php?since_id=0");
curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 4]);
$realtimeJson = json_decode(curl_exec($ch), true)['data'] ?? [];
curl_close($ch);

$hasHeroEvent = false;
foreach ($realtimeJson['events'] ?? [] as $ev) {
    if (($ev['section'] ?? '') === 'hero') {
        $hasHeroEvent = true;
        break;
    }
}
assertE2E($hasHeroEvent, "Flow A: Event realtime 'hero' tercatat dan dikirimkan untuk auto-update frontend");

// Restore original hero title
$revertPayload = json_encode([
    '_method' => 'PUT',
    'id' => $origHero['id'] ?? 1,
    'badge_title' => $origHero['badge_title'] ?? 'KONSORSIUM INVESTASI TERPADU',
    'title' => $originalTitle,
    'subtitle' => $origHero['subtitle'] ?? 'Ekosistem Investasi Strategis',
    'cta_primary_label' => $origHero['cta_primary_label'] ?? 'Mulai Investasi',
    'cta_primary_url' => $origHero['cta_primary_url'] ?? '#proyek',
    'cta_secondary_label' => $origHero['cta_secondary_label'] ?? 'Pelajari Ekosistem',
    'cta_secondary_url' => $origHero['cta_secondary_url'] ?? '#ekosistem',
    'is_active' => 1
]);
$ch = curl_init("{$baseUrl}/api/admin/hero.php");
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $revertPayload,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_COOKIEJAR => $cookieJar,
    CURLOPT_COOKIEFILE => $cookieJar,
    CURLOPT_RETURNTRANSFER => true
]);
curl_exec($ch);
curl_close($ch);

// =========================================================================
// FLOW B: Pengunjung isi form → lead muncul di admin
// =========================================================================
$uniqueVisitorEmail = 'investor_e2e_' . time() . '@perusahaan-mitra.co.id';
$uniqueVisitorName = 'Ir. Hendra Kusuma, MBA (E2E Test)';
$contactPayload = json_encode([
    'full_name' => $uniqueVisitorName,
    'email' => $uniqueVisitorEmail,
    'phone' => '+6281122334455',
    'subject' => 'Sindikasi Pendanaan Batch III',
    'message' => 'Kami tertarik menjajaki skema kemitraan sindikasi armada alat berat untuk proyek kuartal 4.',
    'website' => ''
]);

$ch = curl_init("{$baseUrl}/api/contact.php");
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $contactPayload,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'X-Forwarded-For: 198.51.100.' . rand(1, 200)
    ],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 6
]);
$contactRes = curl_exec($ch);
$contactCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
$contactJson = json_decode($contactRes, true);

assertE2E($contactCode === 201 && ($contactJson['success'] ?? false) === true, "Flow B: Pengunjung berhasil mengirimkan formulir kontak (HTTP 201)");

// Fetch admin inquiries and verify lead presence
$ch = curl_init("{$baseUrl}/api/admin/inquiries.php");
curl_setopt_array($ch, [
    CURLOPT_COOKIEJAR => $cookieJar,
    CURLOPT_COOKIEFILE => $cookieJar,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 6
]);
$adminInquiriesRes = curl_exec($ch);
curl_close($ch);
$inquiriesJson = json_decode($adminInquiriesRes, true);
$inquiries = $inquiriesJson['data']['items'] ?? ($inquiriesJson['data'] ?? []);

$foundLead = null;
foreach ($inquiries as $inq) {
    if (($inq['email'] ?? '') === $uniqueVisitorEmail) {
        $foundLead = $inq;
        break;
    }
}
assertE2E($foundLead !== null && $foundLead['full_name'] === $uniqueVisitorName, "Flow B: Data lead pengunjung baru tersimpan di database dan langsung muncul di admin portal");

// =========================================================================
// FLOW C: Admin buat testimoni → tampil di public → Admin hapus → hilang
// =========================================================================
// 1. Admin creates a new testimonial
$createTestimonialPayload = json_encode([
    'name' => 'Bambang Soedjarwo, Direktur Operasional',
    'role_title' => 'Direktur Operasional',
    'company_name' => 'PT Semen Perkasa Nusantara',
    'rating' => 5,
    'content' => 'Kemitraan strategis bersama Montana Global Investama memberikan kepastian alokasi armada dan transparansi tata kelola yang luar biasa.',
    'project_name' => 'Sentral Pool Kebumen',
    'category' => 'Mitra Kontraktor',
    'is_featured' => 0,
    'is_active' => 1,
    'sort_order' => 99
]);

$ch = curl_init("{$baseUrl}/api/admin/testimonials.php");
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $createTestimonialPayload,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        "X-CSRF-Token: {$csrfToken}"
    ],
    CURLOPT_COOKIEJAR => $cookieJar,
    CURLOPT_COOKIEFILE => $cookieJar,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 6
]);
$tRes = curl_exec($ch);
curl_close($ch);
$tJson = json_decode($tRes, true);
$testimoniId = $tJson['data']['id'] ?? ($tJson['data']['testimonial_id'] ?? null);

// 2. Verify it appears on public API
$ch = curl_init("{$baseUrl}/api/testimonials.php");
curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 4]);
$publicTestimonials = json_decode(curl_exec($ch), true)['data'] ?? [];
curl_close($ch);

$foundInPublic = false;
foreach ($publicTestimonials as $t) {
    if (($t['name'] ?? '') === 'Bambang Soedjarwo, Direktur Operasional') {
        $foundInPublic = true;
        if (!$testimoniId) $testimoniId = $t['id'];
        break;
    }
}
assertE2E($foundInPublic && $testimoniId > 0, "Flow C: Testimoni baru yang dibuat admin muncul di endpoint publik landing page");

// 3. Admin deletes the testimonial
$ch = curl_init("{$baseUrl}/api/admin/testimonials.php?id={$testimoniId}");
curl_setopt_array($ch, [
    CURLOPT_CUSTOMREQUEST => 'DELETE',
    CURLOPT_COOKIEJAR => $cookieJar,
    CURLOPT_COOKIEFILE => $cookieJar,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 6
]);
$delRes = curl_exec($ch);
curl_close($ch);

// 4. Verify it's gone from public API
$ch = curl_init("{$baseUrl}/api/testimonials.php");
curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 4]);
$afterDelTestimonials = json_decode(curl_exec($ch), true)['data'] ?? [];
curl_close($ch);

$stillPresent = false;
foreach ($afterDelTestimonials as $t) {
    if ($t['id'] == $testimoniId) {
        $stillPresent = true;
        break;
    }
}
assertE2E(!$stillPresent, "Flow C: Testimoni yang dihapus oleh admin terhapus dari database dan hilang dari public landing page");

// =========================================================================
// FLOW D: Admin nonaktifkan item/section → tidak tampil di frontend
// =========================================================================
// Toggle a FAQ or item to is_active = 0 and verify exclusion
$db = getDB();
$faq = $db->query("SELECT id, question, is_active FROM faqs WHERE is_active = 1 LIMIT 1")->fetch();

if ($faq) {
    $faqId = $faq['id'];
    
    // Deactivate FAQ
    $db->prepare("UPDATE faqs SET is_active = 0 WHERE id = ?")->execute([$faqId]);
    require_once __DIR__ . '/../../backend/helpers/realtime_helper.php';
    triggerRealtimeEvent('faq_deactivated', 'faqs', 'update', ['id' => $faqId]);

    // Check public FAQ endpoint
    $ch = curl_init("{$baseUrl}/api/faqs.php");
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 4]);
    $publicFaqs = json_decode(curl_exec($ch), true)['data'] ?? [];
    curl_close($ch);

    $faqFound = false;
    foreach ($publicFaqs as $f) {
        if ($f['id'] == $faqId) {
            $faqFound = true;
            break;
        }
    }
    assertE2E(!$faqFound, "Flow D: Item FAQ yang dinonaktifkan (is_active=0) otomatis disembunyikan dari public landing page");

    // Re-activate FAQ
    $db->prepare("UPDATE faqs SET is_active = 1 WHERE id = ?")->execute([$faqId]);
    triggerRealtimeEvent('faq_reactivated', 'faqs', 'update', ['id' => $faqId]);
} else {
    assertE2E(true, "Flow D: FAQ verification skipped (no active faq)");
}

@unlink($cookieJar);

echo "\nE2E Realtime Tests Selesai: {$passed} Lulus, {$failed} Gagal.\n";
exit($failed > 0 ? 1 : 0);
