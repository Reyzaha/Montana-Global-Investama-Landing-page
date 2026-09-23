<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — DATABASE SYNC SCRIPT
 * Usage:
 *   Docker: docker exec -it mgi_web_app php sync_db.php
 *   Host:   php sync_db.php
 */

require_once __DIR__ . '/backend/config/db.php';

echo "=== MGI DATABASE SYNC START ===\n";

try {
    $db = getDB();
    $jsonFile = __DIR__ . '/data/projects.json';
    if (!file_exists($jsonFile)) {
        die("Error: File data/projects.json tidak ditemukan!\n");
    }

    $raw = json_decode(file_get_contents($jsonFile), true);
    $projects = $raw['projects'] ?? [];

    echo "Memuat " . count($projects) . " proyek dari data/projects.json...\n";

    // Disable foreign keys temporarily for clean reload
    $db->exec("SET FOREIGN_KEY_CHECKS = 0;");
    $db->exec("TRUNCATE TABLE funding_items;");
    $db->exec("TRUNCATE TABLE project_simulation;");
    $db->exec("TRUNCATE TABLE project_details;");
    $db->exec("TRUNCATE TABLE projects;");
    $db->exec("SET FOREIGN_KEY_CHECKS = 1;");

    $insP = $db->prepare("
        INSERT INTO projects (
            id, city, title, category, image, funding_collected, funding_target,
            currency, status, featured, lokasi, target_display, tenor,
            return_rate, risk_level, min_investment, payout, remaining_days, asset_backed, sort_order
        ) VALUES (?, ?, ?, ?, ?, ?, ?, 'IDR', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $insD = $db->prepare("
        INSERT INTO project_details (
            project_id, tagline, what_will_provide_title, what_will_provide_content,
            sinergi_title, sinergi_content, summary_title, summary_content
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $insF = $db->prepare("
        INSERT INTO funding_items (project_id, no_urut, item_name, quantity, unit_price, total)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    $insS = $db->prepare("
        INSERT INTO project_simulation (
            project_id, tenor_bulan, estimasi_return_persen, modal_kerja_bulanan_persen,
            minimum_investasi, maximum_investasi, default_investasi, notes
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");

    foreach ($projects as $idx => $p) {
        $pid = $p['id'];
        $info = $p['info'] ?? [];
        $fnd = $p['funding'] ?? [];
        $insP->execute([
            $pid,
            $p['city'] ?? '',
            $p['title'],
            $p['category'] ?? 'Alat Berat & Infrastruktur',
            $p['image'] ?? 'assets/img/komatsu.jpg',
            (int)($fnd['collected'] ?? 0),
            (int)($fnd['target'] ?? 0),
            $p['status'] ?? 'Open',
            !empty($p['featured']) ? 1 : 0,
            $info['lokasi'] ?? '',
            $info['target'] ?? '',
            $info['tenor'] ?? '36 Bulan',
            $info['return'] ?? '≥30% (p.a.)',
            'Menengah - Terukur',
            $info['min_investment'] ?? 'Rp 500.000.000',
            $info['payout'] ?? 'Bagi Hasil Kuartalan',
            $info['remaining_days'] ?? '18 Hari Tersisa',
            $info['asset_backed'] ?? 'Unit CBU Grade A & BPKB',
            $idx + 1
        ]);

        $det = $p['detail'] ?? [];
        $wp = $det['what_will_provide'] ?? [];
        $sf = $det['about_sinergi_foundation'] ?? [];
        $sm = $det['summary'] ?? [];

        $insD->execute([
            $pid,
            $det['tagline'] ?? '',
            $wp['title'] ?? 'Alokasi Penggunaan Modal',
            $wp['content'] ?? '',
            $sf['title'] ?? 'Kerangka Kontrak Penyerapan Pasar (Offtake Framework)',
            $sf['content'] ?? '',
            $sm['title'] ?? 'Ringkasan Kelayakan Investasi & Profil Risiko',
            $sm['content'] ?? ''
        ]);

        $fTarget = $det['funding_target'] ?? [];
        $rows = $fTarget['rows'] ?? [];
        foreach ($rows as $r) {
            $insF->execute([
                $pid,
                $r['no'] ?? 1,
                $r['item'] ?? '',
                $r['quantity'] ?? 1,
                $r['unit_price'] ?? 0,
                $r['total'] ?? 0
            ]);
        }

        $sim = $det['simulation'] ?? [];
        $insS->execute([
            $pid,
            $sim['tenor_bulan'] ?? 36,
            $sim['estimasi_return_persen'] ?? 30,
            $sim['modal_kerja_bulanan_persen'] ?? 2.2,
            $sim['minimum_investasi'] ?? 500000000,
            $sim['maximum_investasi'] ?? 500000000000,
            $sim['default_investasi'] ?? 500000000,
            'Simulasi ilustratif'
        ]);

        $targetFormatted = number_format((float)($fnd['target'] ?? 0), 0, ',', '.');
        echo "  [OK] {$pid} -> Rp {$targetFormatted} ({$p['title']})\n";
    }

    echo "=== SINKRONISASI DATABASE BERHASIL PENUH! ===\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
