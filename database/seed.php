<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — DATABASE MIGRATOR & SEEDER
 * Populates MySQL database from existing Fase 1 JSON files & default superadmin.
 * Run via CLI: php database/seed.php
 */

require_once __DIR__ . '/../backend/config/db.php';
require_once __DIR__ . '/../backend/helpers/auth_helper.php';

echo "=== [MGI] MEMULAI MIGRASI & SEEDING DATABASE FASE 2 ===\n";

try {
    $db = getDB();
    echo "[✓] Berhasil terhubung ke MySQL pada port: " . Database::getConnectedPort() . "\n";

    // 1. Eksekusi Skema SQL
    $schemaFile = __DIR__ . '/schema.sql';
    if (!file_exists($schemaFile)) {
        throw new Exception("File skema tidak ditemukan: {$schemaFile}");
    }

    $sql = file_get_contents($schemaFile);
    // Split queries by semicolon outside quotes
    $db->exec($sql);
    echo "[✓] Skema database berhasil dieksekusi (14 tabel siap).\n";

    // 2. Seed Super Administrator
    $adminUser = 'admin';
    $adminEmail = 'admin@montanaglobalinvestama.com';
    $adminPass = 'admin123';
    $adminHash = hashPassword($adminPass);

    $stmt = $db->prepare("SELECT id FROM admin_users WHERE username = ?");
    $stmt->execute([$adminUser]);
    if (!$stmt->fetch()) {
        $stmtInsert = $db->prepare("
            INSERT INTO admin_users (username, email, password_hash, full_name, role, is_active)
            VALUES (?, ?, ?, 'Super Administrator MGI', 'superadmin', 1)
        ");
        $stmtInsert->execute([$adminUser, $adminEmail, $adminHash]);
        echo "[✓] User superadmin default dibuat: username '{$adminUser}', password '{$adminPass}'\n";
    } else {
        echo "[i] User superadmin '{$adminUser}' sudah ada.\n";
    }

    // 3. Seed Demo Investor Accounts
    $demoInvestors = [
        [
            'account_type' => 'perorangan',
            'email' => 'investor@gmail.com',
            'password' => 'password123',
            'full_name' => 'Budi Pratama',
            'citizenship' => 'Indonesia (WNI)',
            'phone' => '081234567890'
        ],
        [
            'account_type' => 'perusahaan',
            'email' => 'corporate@holding.com',
            'password' => 'password123',
            'full_name' => 'Hendra Wijaya, S.E., M.B.A.',
            'citizenship' => 'Indonesia (WNI)',
            'phone' => '081198765432',
            'company' => [
                'business_name' => 'PT Nusantara Capital Group',
                'legal_entity' => 'Perseroan Terbatas (PT)',
                'company_address' => 'Equity Tower Lt. 28, SCBD, Jakarta Selatan',
                'pic_name' => 'Hendra Wijaya, S.E., M.B.A.',
                'pic_position' => 'Managing Director',
                'company_phone' => '081198765432',
                'annual_turnover' => 'Rp10 Miliar – Rp50 Miliar'
            ]
        ]
    ];

    foreach ($demoInvestors as $inv) {
        $stmt = $db->prepare("SELECT id FROM investors WHERE email = ?");
        $stmt->execute([$inv['email']]);
        $existing = $stmt->fetch();
        
        if (!$existing) {
            $stmtInsert = $db->prepare("
                INSERT INTO investors (account_type, email, password_hash, full_name, citizenship, phone, status)
                VALUES (?, ?, ?, ?, ?, ?, 'active')
            ");
            $stmtInsert->execute([
                $inv['account_type'],
                $inv['email'],
                hashPassword($inv['password']),
                $inv['full_name'],
                $inv['citizenship'],
                $inv['phone']
            ]);
            $investorId = $db->lastInsertId();

            if (!empty($inv['company'])) {
                $c = $inv['company'];
                $stmtComp = $db->prepare("
                    INSERT INTO investor_companies (investor_id, business_name, legal_entity, company_address, pic_name, pic_position, company_phone, annual_turnover)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmtComp->execute([
                    $investorId,
                    $c['business_name'],
                    $c['legal_entity'],
                    $c['company_address'],
                    $c['pic_name'],
                    $c['pic_position'],
                    $c['company_phone'],
                    $c['annual_turnover']
                ]);
            }
            echo "[✓] Demo investor ditambahkan: {$inv['email']} ({$inv['account_type']})\n";
        }
    }

    // 4. Seed Projects Data (from data/projects.json)
    $projectsJsonFile = __DIR__ . '/../data/projects.json';
    if (file_exists($projectsJsonFile)) {
        $projData = json_decode(file_get_contents($projectsJsonFile), true);
        $projectsList = $projData['projects'] ?? [];

        foreach ($projectsList as $idx => $p) {
            $pId = $p['id'];
            $fundingCollected = (string)($p['funding']['collected'] ?? 0);
            $fundingTarget = (string)($p['funding']['target'] ?? 0);

            // Upsert project
            $stmt = $db->prepare("
                INSERT INTO projects (
                    id, title, category, image, funding_collected, funding_target,
                    currency, status, featured, lokasi, target_display, tenor,
                    return_rate, risk_level, min_investment, payout, remaining_days,
                    asset_backed, sort_order
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    title = VALUES(title),
                    category = VALUES(category),
                    image = VALUES(image),
                    funding_collected = VALUES(funding_collected),
                    funding_target = VALUES(funding_target),
                    status = VALUES(status),
                    featured = VALUES(featured),
                    lokasi = VALUES(lokasi),
                    target_display = VALUES(target_display),
                    tenor = VALUES(tenor),
                    return_rate = VALUES(return_rate),
                    risk_level = VALUES(risk_level),
                    min_investment = VALUES(min_investment),
                    payout = VALUES(payout),
                    remaining_days = VALUES(remaining_days),
                    asset_backed = VALUES(asset_backed),
                    sort_order = VALUES(sort_order)
            ");
            $stmt->execute([
                $pId,
                $p['title'] ?? 'Proyek Investasi',
                $p['category'] ?? 'Alat Berat & Infrastruktur',
                $p['image'] ?? 'assets/img/komatsu.jpg',
                $fundingCollected,
                $fundingTarget,
                $p['funding']['currency'] ?? 'IDR',
                $p['status'] ?? 'Open',
                !empty($p['featured']) ? 1 : 0,
                $p['info']['lokasi'] ?? '',
                $p['info']['target'] ?? '',
                $p['info']['tenor'] ?? '',
                $p['info']['return'] ?? '',
                $p['info']['risk'] ?? '',
                $p['info']['min_investment'] ?? 'Rp 500.000.000',
                $p['info']['payout'] ?? 'Bagi Hasil Kuartalan',
                $p['info']['remaining_days'] ?? '18 Hari Tersisa',
                $p['info']['asset_backed'] ?? 'Unit CBU Grade A & BPKB',
                $idx + 1
            ]);

            // Upsert project_details
            if (!empty($p['detail'])) {
                $d = $p['detail'];
                $db->prepare("DELETE FROM project_details WHERE project_id = ?")->execute([$pId]);
                
                $stmtDetail = $db->prepare("
                    INSERT INTO project_details (
                        project_id, tagline, what_will_provide_title, what_will_provide_content,
                        sinergi_title, sinergi_content, summary_title, summary_content
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmtDetail->execute([
                    $pId,
                    $d['tagline'] ?? '',
                    $d['what_will_provide']['title'] ?? 'Alokasi Penggunaan Modal',
                    $d['what_will_provide']['content'] ?? '',
                    $d['about_sinergi_foundation']['title'] ?? 'Struktur Kemitraan Strategis',
                    $d['about_sinergi_foundation']['content'] ?? '',
                    $d['summary']['title'] ?? 'Ringkasan Kelayakan Investasi',
                    $d['summary']['content'] ?? ''
                ]);

                // Insert funding items (RAB)
                if (!empty($d['funding_target']['rows'])) {
                    $db->prepare("DELETE FROM funding_items WHERE project_id = ?")->execute([$pId]);
                    $stmtItem = $db->prepare("
                        INSERT INTO funding_items (project_id, no_urut, item_name, quantity, unit_price, total)
                        VALUES (?, ?, ?, ?, ?, ?)
                    ");
                    foreach ($d['funding_target']['rows'] as $r) {
                        $stmtItem->execute([
                            $pId,
                            $r['no'] ?? 1,
                            $r['item'] ?? '',
                            $r['quantity'] ?? 1,
                            (string)($r['unit_price'] ?? 0),
                            (string)($r['total'] ?? 0)
                        ]);
                    }
                }

                // Insert simulation
                if (!empty($d['simulation'])) {
                    $s = $d['simulation'];
                    $db->prepare("DELETE FROM project_simulation WHERE project_id = ?")->execute([$pId]);
                    $stmtSim = $db->prepare("
                        INSERT INTO project_simulation (
                            project_id, tenor_bulan, estimasi_return_persen, modal_kerja_bulanan_persen,
                            minimum_investasi, maximum_investasi, default_investasi, notes
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    $stmtSim->execute([
                        $pId,
                        $s['tenor_bulan'] ?? 36,
                        $s['estimasi_return_persen'] ?? 30,
                        $s['modal_kerja_bulanan_persen'] ?? 2.2,
                        (string)($s['minimum_investasi'] ?? 500000000),
                        (string)($s['maximum_investasi'] ?? 500000000000),
                        (string)($s['default_investasi'] ?? 500000000),
                        $s['notes'] ?? ''
                    ]);
                }
            }

            echo "[✓] Proyek '{$pId}' ({$p['title']}) berhasil disinkronkan ke DB.\n";
        }
    }

    // 5. Seed Company Profile
    $cpFile = __DIR__ . '/../data/company-profile.json';
    if (file_exists($cpFile)) {
        $cpData = file_get_contents($cpFile);
        $stmt = $db->prepare("
            INSERT INTO company_profile (setting_key, setting_value)
            VALUES ('main_profile', ?)
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
        ");
        $stmt->execute([$cpData]);
        echo "[✓] Data Company Profile berhasil disinkronkan ke DB.\n";
    }

    // 6. Seed Transformasi Steps
    $transFile = __DIR__ . '/../data/transformasi.json';
    if (file_exists($transFile)) {
        $transData = json_decode(file_get_contents($transFile), true);
        $steps = $transData['journey'] ?? ($transData['steps'] ?? []);
        $db->exec("TRUNCATE TABLE transformasi_steps");
        $stmtStep = $db->prepare("
            INSERT INTO transformasi_steps (step_order, year_or_phase, title, subtitle, description, highlights, status)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        foreach ($steps as $st) {
            $achievements = $st['achievements'] ?? ($st['highlights'] ?? []);
            $stmtStep->execute([
                $st['step'] ?? 1,
                $st['year_or_phase'] ?? '',
                $st['title'] ?? '',
                $st['tagline'] ?? ($st['subtitle'] ?? ''),
                $st['description'] ?? '',
                json_encode($achievements, JSON_UNESCAPED_UNICODE),
                $st['status'] ?? 'completed'
            ]);
        }
        echo "[✓] " . count($steps) . " Langkah Transformasi berhasil disinkronkan ke DB.\n";
    }

    // 7. Seed Preparation Entities & Workflow
    $prepFile = __DIR__ . '/../data/preparation.json';
    if (file_exists($prepFile)) {
        $prepData = json_decode(file_get_contents($prepFile), true);
        
        // Entities
        $entities = $prepData['entities_summary'] ?? ($prepData['entities'] ?? []);
        $db->exec("TRUNCATE TABLE preparation_entities");
        $stmtEnt = $db->prepare("
            INSERT INTO preparation_entities (code, name, slogan, role, level, badge, color, description, focus, output, location, sort_order)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        foreach ($entities as $idx => $e) {
            $stmtEnt->execute([
                $e['code'],
                $e['name'],
                $e['slogan'] ?? '',
                $e['role'] ?? '',
                $e['level'] ?? 'Level 1',
                $e['badge'] ?? ($e['role'] ?? ''),
                $e['color'] ?? 'gold',
                $e['description'] ?? ($e['role'] ?? ''),
                $e['fokus_kerja'] ?? ($e['focus'] ?? ''),
                $e['output_utama'] ?? ($e['output'] ?? ''),
                $e['location'] ?? null,
                $idx + 1
            ]);
        }
        echo "[✓] " . count($entities) . " Entitas Preparation berhasil disinkronkan ke DB.\n";

        // Workflow
        $wf = $prepData['workflow'] ?? [];
        $db->exec("TRUNCATE TABLE preparation_workflow");
        $stmtWf = $db->prepare("
            INSERT INTO preparation_workflow (step_number, title, actor, description, badge)
            VALUES (?, ?, ?, ?, ?)
        ");
        foreach ($wf as $w) {
            $stmtWf->execute([
                $w['step'] ?? 1,
                $w['title'] ?? '',
                $w['actor'] ?? '',
                $w['desc'] ?? ($w['description'] ?? ''),
                $w['badge'] ?? ''
            ]);
        }
        echo "[✓] " . count($wf) . " Alur Workflow Sinergi berhasil disinkronkan ke DB.\n";
    }

    // 8. Seed Ekosistem Nodes
    $ekoFile = __DIR__ . '/../data/ekosistem.json';
    if (file_exists($ekoFile)) {
        $ekoData = json_decode(file_get_contents($ekoFile), true);
        $nodes = $ekoData['nodes'] ?? [];
        
        // Disable foreign key checks for clean node insertion
        $db->exec("SET FOREIGN_KEY_CHECKS = 0; TRUNCATE TABLE ekosistem_nodes; SET FOREIGN_KEY_CHECKS = 1;");
        $stmtNode = $db->prepare("
            INSERT INTO ekosistem_nodes (id, label, subtitle, parent_id, level, badge, role_desc, sort_order)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        foreach ($nodes as $idx => $n) {
            $stmtNode->execute([
                $n['id'],
                $n['label'],
                $n['subtitle'] ?? null,
                $n['parent'] ?? null,
                $n['level'] ?? 0,
                $n['badge'] ?? null,
                $n['role_desc'] ?? null,
                $idx + 1
            ]);
        }
        echo "[✓] " . count($nodes) . " Node Ekosistem berhasil disinkronkan ke DB.\n";
    }

    // 9. Seed System Settings
    $settings = [
        'require_auth_for_details' => ['1', 'Proteksi Gated Content: 1 = Wajib Login untuk melihat RAB & Simulasi, 0 = Terbuka Publik'],
        'disclaimer_text' => [
            'Target ilustratif berdasarkan proyeksi kinerja, bukan jaminan — hasil aktual mengikuti kinerja riil usaha dan dapat lebih rendah dari target. Investasi pada instrumen sektor riil mengandung risiko fluktuasi pasar dan operasional. Harap membaca seluruh dokumen penawaran dan Risk Disclosure Statement secara cermat.',
            'Teks Pernyataan Keterbukaan Risiko (Risk Disclosure Statement)'
        ],
        'site_title' => ['PT Montana Global Investama', 'Nama Resmi Perusahaan'],
        'official_email' => ['Montanaglobalinvestamaom@gmail.com', 'Alamat Email Resmi Korespondensi']
    ];

    $stmtSet = $db->prepare("
        INSERT INTO system_settings (setting_key, setting_value, description)
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), description = VALUES(description)
    ");
    foreach ($settings as $k => $v) {
        $stmtSet->execute([$k, $v[0], $v[1]]);
    }
    echo "[✓] Pengaturan sistem (system_settings) berhasil di-seed.\n";

    // 10. Seed Demo Bank Accounts
    $stmtInv1 = $db->prepare("SELECT id FROM investors WHERE email = 'investor@gmail.com'");
    $stmtInv1->execute();
    $inv1Id = $stmtInv1->fetchColumn();

    $stmtInv2 = $db->prepare("SELECT id FROM investors WHERE email = 'corporate@holding.com'");
    $stmtInv2->execute();
    $inv2Id = $stmtInv2->fetchColumn();

    $db->exec("TRUNCATE TABLE investor_bank_accounts");
    $stmtBank = $db->prepare("
        INSERT INTO investor_bank_accounts (investor_id, bank_name, account_number, account_holder, branch, is_primary)
        VALUES (?, ?, ?, ?, ?, 1)
    ");

    if ($inv1Id) {
        $stmtBank->execute([$inv1Id, 'Bank Central Asia (BCA)', '8820394821', 'Budi Pratama', 'KCP Sudirman Jakarta']);
        echo "[✓] Rekening bank demo investor perorangan ditambahkan.\n";
    }
    if ($inv2Id) {
        $stmtBank->execute([$inv2Id, 'Bank Mandiri', '1270009847281', 'PT Nusantara Capital Group', 'KC SCBD Equity Tower']);
        echo "[✓] Rekening bank demo investor korporasi ditambahkan.\n";
    }

    // 11. Seed Demo Portfolios
    $db->exec("TRUNCATE TABLE investor_portfolios");
    $stmtPort = $db->prepare("
        INSERT INTO investor_portfolios (
            investor_id, project_id, contract_number, amount, return_rate, tenor,
            start_date, end_date, next_payout_date, payout_received, allocated_units, status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')
    ");

    if ($inv1Id) {
        $stmtPort->execute([
            $inv1Id, 'proj-001', 'MGI/INV/2024/001-BP', 500000000, '≥30% (p.a.)', '36 Bulan',
            '2024-01-15', '2027-01-15', '2024-10-15', 37500000, '1x Komatsu PC138US (Grade A)'
        ]);
        echo "[✓] Portofolio demo investor perorangan ditambahkan (Rp 500 Juta di proj-001).\n";
    }

    if ($inv2Id) {
        $stmtPort->execute([
            $inv2Id, 'proj-001', 'MGI/CORP/2024/008-NCG', 2500000000, '≥30% (p.a.)', '36 Bulan',
            '2024-02-01', '2027-02-01', '2024-11-01', 187500000, '2x Komatsu PC200-8 & 1x D6R Caterpillar'
        ]);
        $stmtPort->execute([
            $inv2Id, 'proj-002', 'MGI/CORP/2024/014-NCG', 1000000000, '≥28% (p.a.)', '24 Bulan',
            '2024-05-10', '2026-05-10', '2024-11-10', 70000000, '4x Hino 500 Dump Truck Sindikasi'
        ]);
        echo "[✓] Portofolio demo investor korporasi ditambahkan (Total Rp 3,5 Miliar di 2 proyek).\n";
    }

    // 12. Seed Campaign Updates
    $db->exec("TRUNCATE TABLE campaign_updates");
    $stmtCamp = $db->prepare("
        INSERT INTO campaign_updates (project_id, title, category, update_date, content, image_url, sort_order)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    $updates = [
        [
            'proj-001',
            'Kedatangan 6 Unit Excavator Komatsu PC138US-8 CBU Jepang di Pool Kebumen',
            'Logistik & Impor CBU',
            '2024-08-20',
            'Alhamdulillah, seluruh 6 unit Excavator Hydraulic Komatsu PC138US-8 Grade A asal Jepang telah tiba dengan selamat di Pusat Workshop & Pool Kebumen seluas 4.500 m². Tim mekanik telah menyelesaikan tahap PDI (Pre-Delivery Inspection), pengujian hidrolik, pemasangan sistem GPS tracking terpadu, dan penerbitan sertifikat kelaikan fungsi operasional.',
            'assets/img/komatsu.jpg',
            1
        ],
        [
            'proj-001',
            'Mobilisasi Unit ke Proyek Strategis Daerah Jawa Tengah & Kesiapan Montana Towing',
            'Operasional Lapangan',
            '2024-09-02',
            'Sebanyak 4 unit telah diberangkatkan menuju lokasi proyek sipil normalisasi sungai dan pematangan lahan tol Solo-Yogyakarta dengan pengawalan armada Montana Towing. Seluruh unit mencatatkan utilitas kerja rata-rata 8,5 jam per hari tanpa kendala mekanis.',
            'assets/img/komatsu.jpg',
            2
        ],
        [
            'proj-001',
            'Distribusi Imbal Hasil Kuartal II Berjalan Lancar ke Rekening Investor',
            'Laporan Keuangan & Dividen',
            '2024-07-15',
            'Manajemen PT Montana Global Investama telah menuntaskan penyaluran bagi hasil kuartalan periode Q2 kepada seluruh investor sindikasi perorangan dan korporasi terdaftar secara tepat waktu. Laporan keuangan proyek telah diverifikasi oleh tim kepatuhan internal.',
            'assets/img/komatsu.jpg',
            3
        ]
    ];

    foreach ($updates as $u) {
        $stmtCamp->execute($u);
    }
    echo "[✓] 3 Laporan Campaign Update operasional lapangan berhasil disinkronkan ke DB.\n";

    echo "\n=== [MGI] SEEDING DATABASE SELESAI DENGAN SUKSES! ===\n";

} catch (Exception $e) {
    echo "\n[✕] ERROR SAAT SEEDING: " . $e->getMessage() . "\n";
    exit(1);
}
