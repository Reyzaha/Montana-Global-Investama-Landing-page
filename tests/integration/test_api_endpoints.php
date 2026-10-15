<?php
/**
 * Automated API Integration Test Suite
 * PT Montana Global Investama
 * 
 * Verifies all public GET & POST endpoints, response schemas, HTTP codes,
 * and form validation (valid, empty, malformed input).
 */

require_once __DIR__ . '/../../backend/config/db.php';

$baseUrl = 'http://127.0.0.1:8000';
$passed = 0;
$failed = 0;

function assertInt($condition, $description) {
    global $passed, $failed;
    if ($condition) {
        echo "  [PASS] {$description}\n";
        $passed++;
    } else {
        echo "  [FAIL] {$description}\n";
        $failed++;
    }
}

echo "=== [3/7] MENJALANKAN API INTEGRATION TESTS ===\n";

// --- 1. Test Public GET Endpoints ---
$publicEndpoints = [
    '/api/hero.php' => function($data) {
        return isset($data['title']) || isset($data['subtitle']) || isset($data['hero']);
    },
    '/api/features.php' => function($data) {
        return isset($data['value_propositions']) || isset($data['pilars']) || isset($data['all']);
    },
    '/api/pricing.php' => function($data) {
        return is_array($data) && count($data) > 0;
    },
    '/api/projects.php' => function($data) {
        return isset($data['projects']) && is_array($data['projects']);
    },
    '/api/testimonials.php' => function($data) {
        return is_array($data) && count($data) > 0;
    },
    '/api/faqs.php' => function($data) {
        return is_array($data) && count($data) > 0;
    },
    '/api/team.php' => function($data) {
        return is_array($data) && count($data) > 0;
    },
    '/api/articles.php' => function($data) {
        return is_array($data);
    },
    '/api/settings.php' => function($data) {
        return isset($data['site_title']) || isset($data['official_email']);
    },
    '/api/health.php' => function($data) {
        return ($data['status'] ?? '') === 'healthy' && isset($data['checks']['database']);
    },
    '/api/realtime/check.php' => function($data) {
        return isset($data['current_rev']) && isset($data['last_event_id']);
    }
];

foreach ($publicEndpoints as $ep => $validator) {
    $ch = curl_init("{$baseUrl}{$ep}");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 4
    ]);
    $res = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $json = json_decode($res, true);
    $data = isset($json['data']) ? $json['data'] : $json;
    $valid = is_array($json) && $validator($data);

    assertInt($code === 200 && $valid, "GET {$ep} returns HTTP 200 and matches expected schema");
}

// --- 2. Test Contact Form Validation (POST /api/contact.php) ---

// Case A: Valid Input
$validContact = [
    'full_name' => 'Dr. Budi Pratama, M.Sc',
    'email' => 'budi.pratama@enterprise-corp.id',
    'phone' => '+6281299887766',
    'subject' => 'Permohonan Kerjasama Sindikasi 2026',
    'message' => 'Halo Manajemen Montana Global Investama, kami bermaksud mendiskusikan sindikasi pendanaan armada.',
    'website' => '' // honeypot empty
];
$testIp = '198.51.100.' . rand(10, 250);
$ch = curl_init("{$baseUrl}/api/contact.php");
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($validContact),
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        "X-Forwarded-For: {$testIp}"
    ],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 4
]);
$res = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
if ($code !== 201) {
    echo "  [DEBUG] Contact code: {$code}, Body: {$res}\n";
}
assertInt($code === 201 && ($json['success'] ?? false) === true, "POST /api/contact.php with valid data returns HTTP 201 Created");

// Case B: Completely Empty Input
$ch = curl_init("{$baseUrl}/api/contact.php");
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode([]),
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 4
]);
$res = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
$json = json_decode($res, true);
assertInt($code === 422 && ($json['success'] ?? true) === false, "POST /api/contact.php with empty payload returns HTTP 422 Unprocessable Entity");

// Case C: Invalid Email Format
$invalidEmailContact = [
    'full_name' => 'Investor Anonymous',
    'email' => 'bukan_email_valid@',
    'phone' => '08123456789',
    'subject' => 'Tanya Investasi',
    'message' => 'Test invalid email validation format pesan cukup panjang'
];
$ch = curl_init("{$baseUrl}/api/contact.php");
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($invalidEmailContact),
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 4
]);
$res = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
$json = json_decode($res, true);
assertInt($code === 422 && ($json['success'] ?? true) === false, "POST /api/contact.php with malformed email returns HTTP 422 Unprocessable Entity");

// --- 3. Test Newsletter Form Validation (POST /api/newsletter.php) ---

// Case A: Valid Email
$validSub = [
    'email' => 'subscriber_' . time() . '_' . rand(100, 999) . '@investor-weekly.com'
];
$ch = curl_init("{$baseUrl}/api/newsletter.php");
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($validSub),
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 4
]);
$res = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
$json = json_decode($res, true);
assertInt(in_array($code, [200, 201]) && ($json['success'] ?? false) === true, "POST /api/newsletter.php with valid email succeeds (HTTP {$code})");

// Case B: Empty Email
$ch = curl_init("{$baseUrl}/api/newsletter.php");
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode(['email' => '']),
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 4
]);
$res = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
$json = json_decode($res, true);
assertInt($code === 422 && ($json['success'] ?? true) === false, "POST /api/newsletter.php with empty email returns HTTP 422");

// Case C: Invalid Email
$ch = curl_init("{$baseUrl}/api/newsletter.php");
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode(['email' => 'not-an-email']),
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 4
]);
$res = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
$json = json_decode($res, true);
assertInt($code === 422 && ($json['success'] ?? true) === false, "POST /api/newsletter.php with invalid email returns HTTP 422");

echo "\nIntegration Tests Selesai: {$passed} Lulus, {$failed} Gagal.\n";
exit($failed > 0 ? 1 : 0);
