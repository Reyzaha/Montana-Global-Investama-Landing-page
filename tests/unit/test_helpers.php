<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — UNIT TESTS
 * Tests helper functions, hashing, rate limiting, and event dispatchers.
 */

require_once __DIR__ . '/../../backend/config/db.php';
require_once __DIR__ . '/../../backend/helpers/auth_helper.php';
require_once __DIR__ . '/../../backend/helpers/realtime_helper.php';
require_once __DIR__ . '/../../backend/helpers/mail_helper.php';

$testsPassed = 0;
$testsFailed = 0;

function assertTest(bool $condition, string $testName): void {
    global $testsPassed, $testsFailed;
    if ($condition) {
        echo "  [PASS] {$testName}\n";
        $testsPassed++;
    } else {
        echo "  [FAIL] {$testName}\n";
        $testsFailed++;
    }
}

echo "=== [1/7] MENJALANKAN UNIT TEST: HELPERS & LOGIC ===\n";

// 1. Password Hashing (Bcrypt)
$rawPass = 'MgiSecure2026!#';
$hash = hashPassword($rawPass);
assertTest(str_starts_with($hash, '$2y$'), "Bcrypt hash prefix is valid ($2y$)");
assertTest(verifyPassword($rawPass, $hash), "Bcrypt verification succeeds with correct password");
assertTest(!verifyPassword('WrongPassword', $hash), "Bcrypt verification fails with wrong password");

// 2. CSRF Token Generation & Validation
$token = getCsrfToken();
assertTest(!empty($token) && strlen($token) === 64, "CSRF token generated (64-char hex string)");
assertTest(validateCsrfToken($token), "CSRF token validates successfully");
assertTest(!validateCsrfToken('invalid_token_12345'), "Invalid CSRF token rejected");

// 3. Rate Limiting Logic
$actionKey = 'test_unit_' . uniqid();
clearRateLimit($actionKey);
assertTest(checkRateLimit($actionKey, 3, 60), "Initial rate limit check allowed");
recordFailedAttempt($actionKey, 60);
recordFailedAttempt($actionKey, 60);
recordFailedAttempt($actionKey, 60);
assertTest(!checkRateLimit($actionKey, 3, 60), "Rate limit exceeded (3 attempts) blocks 4th request");
clearRateLimit($actionKey);
assertTest(checkRateLimit($actionKey, 3, 60), "Rate limit cleared successfully");

// 4. Realtime Event Trigger & Invalidation
$evId = triggerRealtimeEvent('content_updated', 'hero', 'update', ['unit_test' => true]);
assertTest($evId > 0, "triggerRealtimeEvent writes to database and returns event ID > 0");

$state = getLatestRealtimeState();
assertTest((int)$state['last_event_id'] >= $evId, "getLatestRealtimeState reflects latest event ID");

$recentEvents = getEventsSince($evId - 1, 5);
$found = false;
foreach ($recentEvents as $e) {
    if ($e['id'] === $evId && $e['section'] === 'hero') {
        $found = true;
        break;
    }
}
assertTest($found, "getEventsSince retrieves event payload correctly");

// 5. Mail Settings Configuration
$mailCfg = getMailSettings();
assertTest(!empty($mailCfg['official_email']), "Mail settings provides official_email");
assertTest(!empty($mailCfg['smtp_host']), "Mail settings provides smtp_host");

echo "\nUnit Tests Selesai: {$testsPassed} Lulus, {$testsFailed} Gagal.\n";
if ($testsFailed > 0) exit(1);
