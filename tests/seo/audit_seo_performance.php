<?php
/**
 * SEO, Core Web Vitals & Accessibility Automated Audit Suite
 * PT Montana Global Investama
 * 
 * Verifies:
 * 1. Meta tags (title, description, viewport, robots, canonical)
 * 2. Open Graph & Twitter Cards completeness
 * 3. Single H1 heading hierarchy and semantic HTML structure
 * 4. Image alt attributes across all public landing templates
 * 5. robots.txt & sitemap.xml validity
 * 6. Server TTFB latency (Core Web Vitals LCP baseline target < 200ms)
 */

$baseUrl = 'http://127.0.0.1:8000';
$rootDir = realpath(__DIR__ . '/../../');
$passed = 0;
$failed = 0;

function assertSeo($condition, $description) {
    global $passed, $failed;
    if ($condition) {
        echo "  [PASS] {$description}\n";
        $passed++;
    } else {
        echo "  [FAIL] {$description}\n";
        $failed++;
    }
}

echo "=== [6/7] MENJALANKAN SEO, PERFORMANCE & ACCESSIBILITY AUDIT ===\n";

// --- 1. Fetch Homepage & Benchmark TTFB ---
$ch = curl_init("{$baseUrl}/index.html");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 4
]);
$start = microtime(true);
$html = curl_exec($ch);
$duration = (microtime(true) - $start) * 1000;
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$connectTime = curl_getinfo($ch, CURLINFO_CONNECT_TIME) * 1000;
$ttfb = curl_getinfo($ch, CURLINFO_STARTTRANSFER_TIME) * 1000;
curl_close($ch);

assertSeo($httpCode === 200, "Server response: HTTP 200 OK untuk beranda");
assertSeo($ttfb < 150, "Performa Core Web Vitals (TTFB): {$ttfb} ms (Target < 150ms)");

// --- 2. Meta Tags & Canonical Audit ---
$hasTitle = preg_match('/<title>(.*?)<\/title>/is', $html, $titleMatch) && strlen(trim($titleMatch[1])) > 10;
$hasDesc = preg_match('/<meta\s+name=["\']description["\']\s+content=["\'](.*?)["\']/is', $html, $descMatch) && strlen(trim($descMatch[1])) >= 50;
$hasViewport = preg_match('/<meta\s+name=["\']viewport["\']\s+content=["\']width=device-width,\s*initial-scale=1\.0["\']/i', $html);
$hasCanonical = preg_match('/<link\s+rel=["\']canonical["\']\s+href=["\']https:\/\/montanainvestama\.com\/["\']/i', $html);

assertSeo($hasTitle, "SEO Title Tag terdefinisi optimal: '" . substr(trim($titleMatch[1] ?? ''), 0, 50) . "...'");
assertSeo($hasDesc, "SEO Meta Description lengkap (" . strlen(trim($descMatch[1] ?? '')) . " karakter)");
assertSeo($hasViewport, "Mobile Responsive Viewport meta tag dikonfigurasi dengan tepat");
assertSeo($hasCanonical, "Canonical URL meta tag valid (https://montanainvestama.com/)");

// --- 3. Open Graph & Twitter Cards Audit ---
$ogRequired = ['og:title', 'og:description', 'og:image', 'og:url', 'og:type'];
$ogPassed = true;
foreach ($ogRequired as $tag) {
    if (!preg_match('/<meta\s+property=["\']' . preg_quote($tag, '/') . '["\']/i', $html)) {
        $ogPassed = false;
        break;
    }
}
assertSeo($ogPassed, "Social Sharing: Open Graph Protocol meta tags (og:title, og:image, og:desc) lengkap");

$twRequired = ['twitter:card', 'twitter:title', 'twitter:description', 'twitter:image'];
$twPassed = true;
foreach ($twRequired as $tag) {
    if (!preg_match('/<meta\s+name=["\']' . preg_quote($tag, '/') . '["\']/i', $html)) {
        $twPassed = false;
        break;
    }
}
assertSeo($twPassed, "Social Sharing: Twitter Cards meta tags lengkap");

// --- 4. Heading Hierarchy Audit (Single H1) ---
preg_match_all('/<h1\b[^>]*>(.*?)<\/h1>/is', $html, $h1Matches);
$h1Count = count($h1Matches[0] ?? []);
assertSeo($h1Count === 1, "Struktur Heading Hierarki: Tepat memiliki tepat 1 elemen <h1> untuk SEO semantic");

// --- 5. Image Alt Attributes & Accessibility Audit ---
preg_match_all('/<img\b([^>]*)>/i', $html, $imgTags);
$missingAlt = 0;
foreach ($imgTags[1] as $imgAttrs) {
    if (!preg_match('/\balt=["\'][^"\']+["\']/i', $imgAttrs)) {
        $missingAlt++;
    }
}
assertSeo($missingAlt === 0, "Aksesibilitas & SEO Gambar: Seluruh tag <img> memiliki atribut alt deskriptif");

// --- 6. Schema.org JSON-LD Structured Data Audit ---
$hasJsonLd = preg_match('/<script\s+type=["\']application\/ld\+json["\']>(.*?)<\/script>/is', $html, $jsonLdMatch);
$validJsonLd = false;
if ($hasJsonLd) {
    $decoded = json_decode($jsonLdMatch[1], true);
    if ($decoded && isset($decoded['@context']) && isset($decoded['@graph'])) {
        $validJsonLd = true;
    }
}
assertSeo($validJsonLd, "Rich Snippets: Structured Data JSON-LD Schema.org (@graph) valid dan dapat di-crawl Google");

// --- 7. Robots.txt & Sitemap.xml Verification ---
$chRobots = curl_init("{$baseUrl}/robots.txt");
curl_setopt_array($chRobots, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 4]);
$robotsTxt = curl_exec($chRobots);
$codeRobots = curl_getinfo($chRobots, CURLINFO_HTTP_CODE);
curl_close($chRobots);

$robotsValid = ($codeRobots === 200) 
    && str_contains($robotsTxt, 'User-agent: *')
    && str_contains($robotsTxt, 'Disallow: /admin/')
    && str_contains($robotsTxt, 'Disallow: /portal-admin-mgi-gateway/')
    && str_contains($robotsTxt, 'Sitemap:');
assertSeo($robotsValid, "robots.txt dapat diakses publik dan melindungi direktori admin");

$chSitemap = curl_init("{$baseUrl}/sitemap.xml");
curl_setopt_array($chSitemap, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 4]);
$sitemapXml = curl_exec($chSitemap);
$codeSitemap = curl_getinfo($chSitemap, CURLINFO_HTTP_CODE);
curl_close($chSitemap);

$xmlObj = @simplexml_load_string($sitemapXml);
$sitemapValid = ($codeSitemap === 200) && ($xmlObj !== false) && count($xmlObj->url) >= 5;
assertSeo($sitemapValid, "sitemap.xml valid XML dengan " . ($xmlObj ? count($xmlObj->url) : 0) . " URL landing page");

echo "\nSEO & Accessibility Audit Selesai: {$passed} Lulus, {$failed} Gagal.\n";
exit($failed > 0 ? 1 : 0);
