<?php
/**
 * Master Enterprise Test Runner
 * PT Montana Global Investama
 * 
 * Executes all automated test suites and compiles a comprehensive tally.
 */

$suites = [
    '1. Unit Tests (Helpers, Bcrypt, CSRF, Realtime, Mail)' => 'tests/unit/test_helpers.php',
    '2. Security Tests (Guards, SQLi, XSS, Honeypot, Obscurity)' => 'tests/unit/test_security.php',
    '3. Integration Tests (Endpoints, Schemas, Form Validation)' => 'tests/integration/test_api_endpoints.php',
    '4. E2E & Realtime Flows (Admin, Lead, Testimonial, Section)' => 'tests/e2e/test_e2e_realtime.php',
    '5. Data Integrity Scanner (No Lorem Ipsum, 100% Component Mapping)' => 'tests/integrity/scan_hardcoded_content.php',
    '6. SEO, Core Web Vitals & Accessibility Audit' => 'tests/seo/audit_seo_performance.php',
    '7. High-Concurrency Load Test (150 Virtual Users)' => 'tests/load/run_load_test.php'
];

$phpBinary = 'C:\\xampp\\php\\php.exe';
$totalSuites = count($suites);
$passedSuites = 0;
$failedSuites = 0;
$startTime = microtime(true);

echo "====================================================================\n";
echo "  PT MONTANA GLOBAL INVESTAMA — ENTERPRISE TESTING STRATEGY RUNNER\n";
echo "  Executed at: " . date('Y-m-d H:i:s T') . "\n";
echo "====================================================================\n\n";

foreach ($suites as $name => $file) {
    echo ">> Menjalankan: {$name}...\n";
    $cmd = "\"{$phpBinary}\" \"" . __DIR__ . '/../' . $file . "\"";
    $output = [];
    $returnCode = 0;
    exec($cmd, $output, $returnCode);

    foreach ($output as $line) {
        echo "   {$line}\n";
    }

    if ($returnCode === 0) {
        echo "   ==> HASIL SUITE: [LULUS / PASSED]\n\n";
        $passedSuites++;
    } else {
        echo "   ==> HASIL SUITE: [GAGAL / FAILED]\n\n";
        $failedSuites++;
    }
}

$elapsed = round(microtime(true) - $startTime, 2);

echo "====================================================================\n";
echo "  RINGKASAN AKHIR EKSEKUSI STRATEGI TESTING MGI\n";
echo "====================================================================\n";
echo "  Total Test Suite:  {$totalSuites}\n";
echo "  Suite Lulus:       {$passedSuites}\n";
echo "  Suite Gagal:       {$failedSuites}\n";
echo "  Total Waktu Uji:   {$elapsed} detik\n";
echo "  Status Kesiapan:   " . ($failedSuites === 0 ? "SIAP PRODUKSI (PRODUCTION READY)" : "MEMERLUKAN PERBAIKAN") . "\n";
echo "====================================================================\n";

exit($failedSuites > 0 ? 1 : 0);
