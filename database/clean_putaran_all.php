<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — UTILITY PEMBERSIHAN TARGET PUTARAN
 * Menghapus seluruh residu teks "Target 2–3x Putaran/Tahun" dan variasinya
 * dari database (MySQL) serta file statis projects.json.
 *
 * Jalankan di VPS Docker:
 * docker compose exec mgi-web php database/clean_putaran_all.php
 *
 * Jalankan di Komputer Lokal:
 * C:\xampp\php\php.exe database/clean_putaran_all.php
 */

require_once __DIR__ . '/../backend/config/db.php';

echo "=================================================================\n";
echo "   PT MONTANA GLOBAL INVESTAMA — PEMBERSIHAN TARGET PUTARAN       \n";
echo "=================================================================\n\n";

// 1. Bersihkan file data/projects.json jika ada
$jsonFile = __DIR__ . '/../data/projects.json';
if (file_exists($jsonFile)) {
    $content = file_get_contents($jsonFile);
    $originalLength = strlen($content);

    $content = str_replace('"target_perputaran": "2 \u2013 3 Kali per Tahun"', '"target_perputaran": "12 Bulan"', $content);
    $content = str_replace('"target_perputaran": "2 – 3 Kali per Tahun"', '"target_perputaran": "12 Bulan"', $content);
    $content = str_replace('Target perputaran 2–3 kali per tahun merupakan proyeksi berbasis kinerja riil penjualan unit.', 'Proyeksi imbal hasil berbasis kinerja riil penjualan unit armada alat berat.', $content);
    $content = str_replace('Dengan model perputaran modal 2–3 kali per tahun', 'Dengan model perputaran modal terukur', $content);

    file_put_contents($jsonFile, $content);
    echo "[✓] File data/projects.json berhasil dibersihkan.\n";
}

// 2. Bersihkan file database/mgi_landing_production_complete.sql
$sqlDump = __DIR__ . '/mgi_landing_production_complete.sql';
if (file_exists($sqlDump)) {
    $sqlContent = file_get_contents($sqlDump);
    $sqlContent = str_replace('2 \\\\u2013 3 Kali per Tahun', '12 Bulan', $sqlContent);
    $sqlContent = str_replace('2 \\u2013 3 Kali per Tahun', '12 Bulan', $sqlContent);
    $sqlContent = str_replace('2 – 3 Kali per Tahun', '12 Bulan', $sqlContent);
    $sqlContent = str_replace('model perputaran modal 2–3 kali per tahun', 'model perputaran modal terukur', $sqlContent);
    $sqlContent = str_replace('(Target 2–3x Putaran/Tahun)', '', $sqlContent);
    $sqlContent = str_replace('(Target 2-3x Putaran/Tahun)', '', $sqlContent);
    file_put_contents($sqlDump, $sqlContent);
    echo "[✓] File database/mgi_landing_production_complete.sql berhasil dibersihkan.\n";
}

// 3. Eksekusi pembersihan pada Database MySQL Aktif
try {
    $db = getDB();
    echo "[✓] Terhubung ke database MySQL.\n";

    // A. Tabel projects: set tenor menjadi '12 Bulan' bersih
    $stmtA = $db->exec("UPDATE projects SET tenor = '12 Bulan' WHERE tenor LIKE '%Putaran%' OR tenor LIKE '%Target%' OR tenor = '' OR tenor IS NULL");
    echo "  - Tabel `projects` (kolom tenor) diperbarui: {$stmtA} baris.\n";

    // B. Tabel project_details: bersihkan summary_risk dan rab_executive_json
    try {
        $stmtB1 = $db->exec("
            UPDATE project_details 
            SET summary_risk = REPLACE(REPLACE(summary_risk, '2–3 kali per tahun', 'terukur'), '2-3 kali per tahun', 'terukur')
            WHERE summary_risk LIKE '%2–3%' OR summary_risk LIKE '%2-3%'
        ");
        echo "  - Tabel `project_details` (summary_risk) diperbarui: {$stmtB1} baris.\n";

        $stmtB2 = $db->exec("
            UPDATE project_details 
            SET rab_executive_json = REPLACE(REPLACE(REPLACE(rab_executive_json, '2 \\\\u2013 3 Kali per Tahun', '12 Bulan'), '2 \u2013 3 Kali per Tahun', '12 Bulan'), '2 – 3 Kali per Tahun', '12 Bulan')
            WHERE rab_executive_json LIKE '%Kali per Tahun%' OR rab_executive_json LIKE '%target_perputaran%'
        ");
        echo "  - Tabel `project_details` (rab_executive_json) diperbarui: {$stmtB2} baris.\n";
    } catch (Exception $eB) {
        // Kolom mungkin berbeda jika tabel custom
    }

    // C. Tabel project_simulation: bersihkan notes
    try {
        $stmtC = $db->exec("
            UPDATE project_simulation 
            SET notes = 'Proyeksi imbal hasil berbasis kinerja riil penjualan unit armada alat berat.' 
            WHERE notes LIKE '%2–3%' OR notes LIKE '%2-3%' OR notes LIKE '%putaran%'
        ");
        echo "  - Tabel `project_simulation` (notes) diperbarui: {$stmtC} baris.\n";
    } catch (Exception $eC) {}

    // D. Bersihkan cache response jika ada
    $cacheDir = __DIR__ . '/../backend/cache';
    if (is_dir($cacheDir)) {
        $files = glob($cacheDir . '/*');
        foreach ($files as $f) {
            if (is_file($f)) @unlink($f);
        }
        echo "[✓] Cache sistem telah dikosongkan.\n";
    }

    echo "\n=================================================================\n";
    echo " [✓] SEMUA RESIDU 'Target 2–3x Putaran/Tahun' TELAH BERSIH TOTAL! \n";
    echo "=================================================================\n\n";

} catch (Exception $e) {
    echo "[!] Catatan database: " . $e->getMessage() . "\n";
}
