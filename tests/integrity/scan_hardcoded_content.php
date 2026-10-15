<?php
/**
 * Data Integrity & Codebase Scanner
 * PT Montana Global Investama
 * 
 * 1. Scans landing page templates & frontend JS for hardcoded dummy/lorem ipsum text.
 * 2. Validates 100% mapping: Frontend Component <-> API Endpoint <-> Admin Module.
 */

$rootDir = realpath(__DIR__ . '/../../');
$passed = 0;
$failed = 0;

function assertInteg($condition, $description) {
    global $passed, $failed;
    if ($condition) {
        echo "  [PASS] {$description}\n";
        $passed++;
    } else {
        echo "  [FAIL] {$description}\n";
        $failed++;
    }
}

echo "=== [5/7] MENJALANKAN DATA INTEGRITY & CODEBASE SCANNER ===\n";

// --- 1. Scan for Lorem Ipsum / Placeholder Text in Public HTML & JS ---
$targetFiles = [
    'index.html',
    'about.html',
    'contact.html',
    'invest.html',
    'invest-detail.html',
    'ekosistem.html',
    'flow-kerja.html',
    'transformasi.html',
    'konsultasi.html',
    'assets/js/main.js',
    'assets/js/components.js',
    'assets/js/realtime.js'
];

$dummyPatterns = [
    'lorem\s+ipsum' => 'Lorem ipsum dummy text',
    'dolor\s+sit\s+amet' => 'Dolor sit amet placeholder',
    'consectetur\s+adipiscing' => 'Consectetur adipiscing dummy copy',
    'dummy\s+text' => 'Explicit dummy text marker',
    'faker' => 'Faker mock reference',
    'john\s+doe' => 'John Doe generic placeholder'
];

$foundViolations = [];

foreach ($targetFiles as $relPath) {
    $fullPath = $rootDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relPath);
    if (!file_exists($fullPath)) {
        continue;
    }

    $content = file_get_contents($fullPath);

    foreach ($dummyPatterns as $pattern => $label) {
        if (preg_match('/' . $pattern . '/i', $content, $matches)) {
            $foundViolations[] = "{$relPath}: Mengandung '{$label}'";
        }
    }
}

if (empty($foundViolations)) {
    assertInteg(true, "Pemindaian template landing page: Nol (0) teks lorem ipsum / dummy placeholder tersisa");
} else {
    foreach ($foundViolations as $v) {
        echo "  [VIOLATION] {$v}\n";
    }
    assertInteg(false, "Ditemukan " . count($foundViolations) . " temuan dummy/lorem ipsum di komponen landing page");
}

// --- 2. Check 100% Component <-> API Endpoint <-> Admin Module Coverage ---
$componentMatrix = [
    'Hero Section' => [
        'api' => 'api/hero.php',
        'admin_api' => 'api/admin/hero.php',
        'admin_view' => 'portal-admin-mgi-gateway/hero.php'
    ],
    'Fitur & Pilar Bisnis' => [
        'api' => 'api/features.php',
        'admin_api' => 'api/admin/features.php',
        'admin_view' => 'portal-admin-mgi-gateway/features.php'
    ],
    'Paket Investasi & Pricing' => [
        'api' => 'api/pricing.php',
        'admin_api' => 'api/admin/pricing.php',
        'admin_view' => 'portal-admin-mgi-gateway/pricing.php'
    ],
    'Proyek & Portofolio' => [
        'api' => 'api/projects.php',
        'admin_api' => 'api/admin/projects.php',
        'admin_view' => 'portal-admin-mgi-gateway/projects.php'
    ],
    'Testimoni & Pengalaman Mitra' => [
        'api' => 'api/testimonials.php',
        'admin_api' => 'api/admin/testimonials.php',
        'admin_view' => 'portal-admin-mgi-gateway/testimonials.php'
    ],
    'FAQ (Pertanyaan Umum)' => [
        'api' => 'api/faqs.php',
        'admin_api' => 'api/admin/faqs.php',
        'admin_view' => 'portal-admin-mgi-gateway/faqs.php'
    ],
    'Tim & Manajemen' => [
        'api' => 'api/team.php',
        'admin_api' => 'api/admin/team.php',
        'admin_view' => 'portal-admin-mgi-gateway/team.php'
    ],
    'Artikel & Wawasan' => [
        'api' => 'api/articles.php',
        'admin_api' => 'api/admin/articles.php',
        'admin_view' => 'portal-admin-mgi-gateway/articles.php'
    ],
    'Pengaturan Situs & SEO' => [
        'api' => 'api/settings.php',
        'admin_api' => 'api/admin/settings.php',
        'admin_view' => 'portal-admin-mgi-gateway/settings.php'
    ],
    'Leads & Formulir Kontak' => [
        'api' => 'api/contact.php',
        'admin_api' => 'api/admin/inquiries.php',
        'admin_view' => 'portal-admin-mgi-gateway/inquiries.php'
    ],
    'Newsletter Subscribers' => [
        'api' => 'api/newsletter.php',
        'admin_api' => 'api/admin/newsletter.php',
        'admin_view' => 'portal-admin-mgi-gateway/newsletter.php'
    ],
    'Manajemen Media' => [
        'api' => 'api/admin/media.php',
        'admin_api' => 'api/admin/media.php',
        'admin_view' => 'portal-admin-mgi-gateway/media.php'
    ],
    'Analytics & Konversi' => [
        'api' => 'api/admin/analytics.php',
        'admin_api' => 'api/admin/analytics.php',
        'admin_view' => 'portal-admin-mgi-gateway/analytics.php'
    ],
    'Manajemen Akun Admin & MFA' => [
        'api' => 'api/admin/admins.php',
        'admin_api' => 'api/admin/admins.php',
        'admin_view' => 'portal-admin-mgi-gateway/admins.php'
    ]
];

foreach ($componentMatrix as $compName => $paths) {
    $apiExists = file_exists($rootDir . '/' . $paths['api']);
    $adminApiExists = file_exists($rootDir . '/' . $paths['admin_api']);
    $adminViewExists = file_exists($rootDir . '/' . $paths['admin_view']);

    $allExist = $apiExists && $adminApiExists && $adminViewExists;
    assertInteg($allExist, "Komponen '{$compName}': Endpoint API & Modul Admin lengkap");
}

echo "\nData Integrity Scan Selesai: {$passed} Lulus, {$failed} Gagal.\n";
exit($failed > 0 ? 1 : 0);
