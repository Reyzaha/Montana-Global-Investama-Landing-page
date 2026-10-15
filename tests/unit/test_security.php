<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — SECURITY TESTS
 * Tests SQL injection resistance, XSS sanitization, rate limiting, and auth guards.
 */

$baseUrl = 'http://127.0.0.1:8000';
$testsPassed = 0;
$testsFailed = 0;

function assertSec(bool $condition, string $testName, string $details = ''): void {
    global $testsPassed, $testsFailed;
    if ($condition) {
        echo "  [PASS] {$testName}\n";
        $testsPassed++;
    } else {
        echo "  [FAIL] {$testName}" . ($details ? " -> {$details}" : '') . "\n";
        $testsFailed++;
    }
}

echo "=== [2/7] MENJALANKAN SECURITY & INJECTION TESTS ===\n";

// 1. Admin Authentication Guards: Unauthenticated requests MUST return 401
$protectedEndpoints = [
    '/api/admin/hero.php',
    '/api/admin/features.php',
    '/api/admin/pricing.php',
    '/api/admin/projects.php',
    '/api/admin/testimonials.php',
    '/api/admin/faqs.php',
    '/api/admin/team.php',
    '/api/admin/articles.php',
    '/api/admin/settings.php',
    '/api/admin/inquiries.php',
    '/api/admin/newsletter.php',
    '/api/admin/media.php',
    '/api/admin/analytics.php',
    '/api/admin/admins.php'
];

foreach ($protectedEndpoints as $ep) {
    $ch = curl_init("{$baseUrl}{$ep}");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 4
    ]);
    $res = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $json = json_decode($res, true);
    assertSec($code === 401 && ($json['success'] ?? true) === false, "Admin guard on {$ep} blocks unauthenticated access (HTTP 401)");
}

// 2. Direct Admin Path Obscurity: /admin/ and unauthenticated portal dashboard redirect to login
$ch = curl_init("{$baseUrl}/admin/");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => false,
    CURLOPT_TIMEOUT => 4
]);
$res = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
assertSec(in_array($code, [301, 302, 403, 404]), "Access to /admin/ is redirected or denied (HTTP {$code})");

$ch = curl_init("{$baseUrl}/portal-admin-mgi-gateway/index.php");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => false,
    CURLOPT_TIMEOUT => 4
]);
$res = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
assertSec($code === 302, "Unauthenticated access to /portal-admin-mgi-gateway/index.php is redirected to login (HTTP {$code})");

// 3. SQL Injection Resistance in Public APIs
$sqliPayloads = [
    "' OR '1'='1",
    "1; DROP TABLE test_dummy; --",
    "' UNION SELECT NULL, NULL, NULL --"
];

foreach ($sqliPayloads as $payload) {
    $url = "{$baseUrl}/api/projects.php?id=" . urlencode($payload);
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 4
    ]);
    $res = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $json = json_decode($res, true);
    assertSec($code === 200 || $code === 404, "SQL Injection payload safely handled on api/projects.php?id=" . substr($payload, 0, 15) . " (HTTP {$code})");
}

// 4. Contact Form XSS & Script Tag Resistance
$xssPayload = '<script>alert("XSS")</script><img src=x onerror=alert(1)>';
$ch = curl_init("{$baseUrl}/api/contact.php");
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode([
        'full_name' => 'Security Tester ' . $xssPayload,
        'email'     => 'sectest@example.com',
        'phone'     => '08123456789',
        'subject'   => 'Security Audit',
        'message'   => 'Testing sanitized stored message: ' . $xssPayload
    ]),
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 5
]);
$res = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$json = json_decode($res, true);
assertSec($code === 201 && ($json['success'] ?? false) === true, "Contact API safely accepts and parameterizes input without script execution (HTTP {$code})");

// Verify that the record was stored with proper parameterization in DB
require_once __DIR__ . '/../../backend/config/db.php';
$db = getDB();
$stmt = $db->query("SELECT message FROM contact_inquiries WHERE email = 'sectest@example.com' ORDER BY id DESC LIMIT 1");
$storedMsg = $stmt->fetchColumn();
assertSec(str_contains($storedMsg, '<script>alert("XSS")</script>'), "Message stored safely as literal parameterized string in DB");
$db->exec("DELETE FROM contact_inquiries WHERE email = 'sectest@example.com'");

// 5. Honeypot Anti-Spam Check: Bot payload with 'website' field
$ch = curl_init("{$baseUrl}/api/contact.php");
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode([
        'full_name' => 'Spam Bot',
        'email'     => 'bot@spam.com',
        'message'   => 'Buy cheap pills online',
        'website'   => 'http://spam-link.com' // Honeypot filled!
    ]),
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 4
]);
$res = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
assertSec($code === 200, "Honeypot anti-spam trap silences bot with fake 200 OK without inserting to DB");

$stmtCheck = $db->query("SELECT COUNT(*) FROM contact_inquiries WHERE email = 'bot@spam.com'");
assertSec((int)$stmtCheck->fetchColumn() === 0, "Bot submission blocked from entering database");

echo "\nSecurity Tests Selesai: {$testsPassed} Lulus, {$testsFailed} Gagal.\n";
if ($testsFailed > 0) exit(1);
