<?php
/**
 * PT MONTANA GLOBAL INVESTAMA — PRODUCTION SEEDER & DATA POPULATOR
 * Populates all dynamic CMS modules (Hero, Features, Pricing, Testimonials,
 * FAQs, Team, Articles, System Settings) with verified corporate data.
 */

require_once __DIR__ . '/../backend/config/db.php';
require_once __DIR__ . '/../backend/helpers/realtime_helper.php';

header('Content-Type: text/plain; charset=utf-8');
echo "=== MEMULAI SEEDING DATA PRODUKSI CMS MGI ===\n";

try {
    $db = getDB();
    echo "[✓] Terhubung ke MySQL pada port: " . Database::getConnectedPort() . "\n";

    // 1. SEED HERO SECTION
    $countHero = (int)$db->query("SELECT COUNT(*) FROM hero_sections")->fetchColumn();
    if ($countHero === 0) {
        $stmt = $db->prepare("
            INSERT INTO hero_sections (id, kicker, title, title_highlight, subtitle, cta_primary_text, cta_primary_url, cta_secondary_text, cta_secondary_url, is_active, sort_order)
            VALUES (1, 'Grup & Manajer Investasi Proyek Sektor Riil', 'Tata Kelola Modal Strategis &', 'Investasi Proyek Sektor Riil Terpercaya', 'PT Montana Global Investama mengalokasikan permodalan strategis berbasis aset fisik produktif unit alat berat dan ekosistem industri terpadu, berlandaskan kepatuhan tata kelola TARIF transparan dan akuntabel.', 'Katalog Proyek Terbuka', 'invest', 'Profil & Tata Kelola', 'about', 1, 1)
        ");
        $stmt->execute();
        echo "[✓] Hero Section berhasil di-seed.\n";
    } else {
        echo "[i] Hero Section sudah memiliki data ({$countHero} baris).\n";
    }

    // 2. SEED FEATURES & VALUE PROPOSITIONS
    $countFeat = (int)$db->query("SELECT COUNT(*) FROM features")->fetchColumn();
    if ($countFeat === 0) {
        $featStmt = $db->prepare("
            INSERT INTO features (id, category, item_number, title, subtitle, description, icon, image_url, badge_text, accent_color, sort_order, is_active)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
        ");
        $features = [
            [1, 'value_prop', '01', 'Alokasi Modal Berbasis Aset Fisik', 'Proteksi Nilai Riil', 'Modal dialokasikan langsung pada aset fisik produktif bernilai tinggi (unit alat berat) dengan dukungan legalitas lengkap serta proteksi asuransi komprehensif.', 'assets/img/icon-alokasi-modal.png', NULL, 'Keamanan Modal', '#1D3589', 1],
            [2, 'value_prop', '02', 'Kemitraan Strategis & Kontrak Eksklusif', 'Kepastian Utilisasi', 'Kerangka kemitraan eksklusif melalui kontrak jangka menengah hingga panjang bersama kontraktor rekanan daerah dirancang guna memastikan penyerapan unit dan kepastian utilisasi sejak awal periode proyek.', 'assets/img/icon-kemitraan.png', NULL, 'Utilisasi Terjamin', '#1D3589', 2],
            [3, 'value_prop', '03', 'Proyeksi Imbal Hasil Terukur', 'Underlying Riil Non-Spekulatif', 'Setiap proyek dilengkapi pemodelan CAPEX, simulasi BEP, dan proyeksi arus kas operasional sebagai dasar evaluasi uji kelayakan (due diligence) sebelum partisipasi modal disetujui.', 'assets/img/icon-imbal-hasil.png', NULL, 'Akuntabilitas Finansial', '#1D3589', 3],
            [4, 'value_prop', '04', 'Sinergi Terintegrasi Lintas Ekosistem', 'Efisiensi Rantai Pasok', 'Integrasi vertikal antara MGI (Grup & Alokasi Modal), MIU (Workshop & Overhaul Sentral), MSI (Impor CBU Door-to-Door), dan Mypurcase (Purchasing B2B) menghasilkan efisiensi biaya belanja modal dan perlindungan nilai aset.', 'assets/img/icon-tata-kelola.png', NULL, 'Efisiensi Nilai', '#1D3589', 4],
            [5, 'value_prop', '05', 'Transparansi, Audit & Tata Kelola TARIF', 'Good Corporate Governance', 'Investor memperoleh akses visibilitas terhadap belanja modal (CAPEX), peruntukan dana, status unit lapangan, serta pelaporan keuangan berkala sesuai standar Tata Kelola Perusahaan yang Baik (Good Corporate Governance).', 'assets/img/icon-transparansi.png', NULL, 'GCG TARIF', '#1D3589', 5],
            [6, 'pilar', 'MIU', 'Montana Indo Utama (MIU)', 'Integrated Heavy Equipment Solutions', 'Mendukung kebutuhan alat berat melalui supply, deployment, workshop sentral Kebumen 4.500 m², dan operational support untuk proyek sektor riil.', NULL, 'assets/img/miu-building.jpg', 'Alat Berat & Workshop Sentral', '#1D3589', 1],
            [7, 'pilar', 'MSI', 'Montana Sentra Industri (MSI)', 'Import & Logistics Partner', 'Menyediakan solusi impor unit CBU Jepang & Cina, pengurusan customs clearance pelabuhan terpadu, dan distribusi door-to-door lokasi proyek.', NULL, 'assets/img/icon-msi-ship.png', 'Logistik & Impor CBU', '#C5A059', 2],
            [8, 'pilar', 'MPC', 'Mypurcase', 'Platform B2B Procurement Digital', 'Menghubungkan kebutuhan pembelian dengan supplier terverifikasi, otomatisasi purchasing, dan transparansi rantai ketersediaan secara efisien.', NULL, 'assets/img/mypurcase-app.png', 'Segera Diluncurkan 2027', '#059669', 3]
        ];
        foreach ($features as $f) {
            $featStmt->execute($f);
        }
        echo "[✓] Features & Pilar Bisnis berhasil di-seed (8 item).\n";
    } else {
        echo "[i] Features sudah memiliki data ({$countFeat} baris).\n";
    }

    // 3. SEED PRICING TIERS
    $countPrice = (int)$db->query("SELECT COUNT(*) FROM pricing_tiers")->fetchColumn();
    if ($countPrice === 0) {
        $pStmt = $db->prepare("
            INSERT INTO pricing_tiers (id, project_id, name, badge, nominal, nominal_display, unit_qty, container_qty, porsi_proyek, jaminan_aset, operator_perk, deskripsi, profit_per_siklus, profit_2x_tahunan, profit_3x_tahunan, is_featured, sort_order, is_active)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
        ");
        $tiers = [
            [1, 'proj-kebumen', 'Paket Partisipasi Perunggu', 'TIER BRONZE', 50000000, 'Rp 50.000.000', 1, '1 Kontainer', '0.33% Nilai Sindikasi', 'Aset Fisik Bersama', 'Laporan Bulanan Digital', 'Paket awal kemitraan modal riil pada sentral pool Kebumen dengan agunan aset fisik terdaftar dan bagi hasil berkala.', 3500000, 7000000, 10500000, 0, 1],
            [2, 'proj-kebumen', 'Paket Partisipasi Perak', 'PILIHAN POPULER', 100000000, 'Rp 100.000.000', 2, '2 Kontainer', '0.67% Nilai Sindikasi', 'Aset Fisik Terikat Unit', 'Akses Monitoring GPS Khusus', 'Paket menengah dengan alokasi armada spesifik, proteksi asuransi komprehensif, dan pembagian dividen langsung ke rekening escrow.', 7200000, 14400000, 21600000, 1, 2],
            [3, 'proj-kebumen', 'Paket Partisipasi Emas', 'TIER GOLD', 250000000, 'Rp 250.000.000', 5, '5 Kontainer', '1.67% Nilai Sindikasi', 'Hak Preferen Aset Fisik', 'Kunjungan Lapangan & VIP Meet', 'Kemitraan institusional / perorangan utama dengan hak preferen perlindungan modal, bagi hasil maksimal, serta pelaporan keuangan triwulanan audited.', 18500000, 37000000, 55500000, 0, 3],
            [4, 'proj-denpasar', 'Paket Eksekutif Sindikasi Denpasar', 'BALI EXPANSION', 500000000, 'Rp 500.000.000', 10, '10 Kontainer', '3.33% Nilai Sindikasi', 'Aset Penuh 1 Unit Excavator', 'VIP Briefing & Audit Access', 'Partisipasi skala penuh untuk ekspansi armada pendukung pariwisata dan infrastruktur Denpasar, Bali.', 40000000, 80000000, 120000000, 0, 4]
        ];
        foreach ($tiers as $t) {
            $pStmt->execute($t);
        }
        echo "[✓] Pricing Tiers berhasil di-seed (4 paket).\n";
    } else {
        echo "[i] Pricing Tiers sudah memiliki data ({$countPrice} baris).\n";
    }

    // 4. SEED TESTIMONIALS
    $countTesti = (int)$db->query("SELECT COUNT(*) FROM testimonials")->fetchColumn();
    if ($countTesti === 0) {
        $tStmt = $db->prepare("
            INSERT INTO testimonials (id, name, role_title, company_name, avatar_url, rating, content, project_name, category, is_featured, sort_order, is_active)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
        ");
        $testimonials = [
            [1, 'Ir. Hendra Gunawan', 'Komisaris Utama', 'PT Nusa Raya Konstruksi', 'assets/img/avatar-1.jpg', 5, 'Kemitraan alokasi alat berat bersama Montana Global Investama memberikan kepastian suplai unit di lapangan tanpa kendala waktu. Tata kelola TARIF yang transparan membuat kami sangat yakin bermitra jangka panjang.', 'Sentral Pool Kebumen', 'Mitra Kontraktor', 1, 1],
            [2, 'Drs. Budi Santoso, M.M.', 'Managing Director', 'Capital Ventura Prima', 'assets/img/avatar-2.jpg', 5, 'Sebagai manajer investasi per project, MGI menunjukkan integritas tinggi. Pelaporan keuangan berkala, bukti bayar invoice, dan pemantauan utilisasi unit riil sangat akuntabel dan presisi.', 'Sentral Pool Kebumen', 'Investor Korporasi', 1, 2],
            [3, 'Agus Prasetyo, S.T.', 'Head of Fleet & Operations', 'PT Mandiri Sarana Tambang', 'assets/img/avatar-3.jpg', 5, 'Dukungan fasilitas sentral workshop Kebumen seluas 4.500 m² terbukti menekan angka downtime alat berat di bawah 5%. Ini adalah ekosistem sektor riil paling komprehensif di kelasnya.', 'Armada Jabodetabek', 'Mitra Operasional', 1, 3]
        ];
        foreach ($testimonials as $t) {
            $tStmt->execute($t);
        }
        echo "[✓] Testimonials berhasil di-seed (3 ulasan).\n";
    } else {
        echo "[i] Testimonials sudah memiliki data ({$countTesti} baris).\n";
    }

    // 5. SEED FAQS
    $countFaq = (int)$db->query("SELECT COUNT(*) FROM faqs")->fetchColumn();
    if ($countFaq === 0) {
        $faqStmt = $db->prepare("
            INSERT INTO faqs (id, question, answer, category, sort_order, is_active)
            VALUES (?, ?, ?, ?, ?, 1)
        ");
        $faqs = [
            [1, 'Apa perbedaan model investasi MGI dengan platform investasi konvensional?', 'PT Montana Global Investama beroperasi sebagai manajer investasi per proyek sektor riil (project-based investment). Setiap rupiah modal yang disetorkan dialokasikan langsung pada aset fisik produktif bernilai tinggi (unit alat berat) dengan kepemilikan dan legalitas terdaftar, bukan pada instrumen spekulatif kertas atau pasar uang.', 'Umum & Konsep', 1],
            [2, 'Bagaimana mekanisme bagi hasil dan jadwal pembagian dividen?', 'Bagi hasil dihitung secara proporsional berdasarkan utilisasi riil jam kerja armada dan kontrak sewa jangka menengah-panjang bersama kontraktor rekanan. Pembagian dividen ditransfer secara berkala langsung ke rekening terdaftar investor sesuai siklus tutup buku proyek.', 'Finansial & Dividen', 2],
            [3, 'Bagaimana perlindungan modal dan mitigasi risiko yang diterapkan?', 'MGI menerapkan 4 lapis perlindungan: (1) Underlying aset fisik unit alat berat bernilai tinggi; (2) Proteksi asuransi Total Loss Only (TLO) & All Risk; (3) Kontrak kemitraan eksklusif pengguna armada; serta (4) Pengawasan tata kelola TARIF dengan audit berkala.', 'Mitigasi Risiko', 3],
            [4, 'Berapa batas minimum penempatan modal untuk satu proyek?', 'Penempatan modal dimulai dari paket perunggu sebesar Rp 50.000.000 untuk proyek sentral pool Kebumen, hingga paket institusional skala penuh untuk ekspansi kota besar seperti Jakarta dan Denpasar.', 'Partisipasi Modal', 4],
            [5, 'Apakah investor dapat mengunjungi fisik aset dan workshop sentral?', 'Tentu. Sebagai wujud transparansi penuh (TARIF), investor terdaftar dapat mengajukan jadwal kunjungan due diligence langsung ke Workshop Sentral & Pool Kebumen seluas 4.500 m² maupun lokasi operasional proyek dengan didampingi tim Investor Relations kami.', 'Operasional & Legal', 5]
        ];
        foreach ($faqs as $f) {
            $faqStmt->execute($f);
        }
        echo "[✓] FAQs berhasil di-seed (5 tanya-jawab).\n";
    } else {
        echo "[i] FAQs sudah memiliki data ({$countFaq} baris).\n";
    }

    // 6. SEED TEAM MEMBERS
    $countTeam = (int)$db->query("SELECT COUNT(*) FROM team_members")->fetchColumn();
    if ($countTeam === 0) {
        $teamStmt = $db->prepare("
            INSERT INTO team_members (id, name, position, department, bio, photo_url, linkedin_url, sort_order, is_active)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)
        ");
        $team = [
            [1, 'M. Fachri Reyzaha', 'Chief Executive Officer & Founder', 'Direksi', 'Berpengalaman lebih dari 12 tahun dalam manajemen investasi proyek, pengadaan alat berat internasional, dan restrukturisasi operasional industri riil di Indonesia.', 'assets/img/team-ceo.jpg', 'https://linkedin.com', 1],
            [2, 'Aditya Nugroho, S.T., M.Sc', 'Chief Operating Officer', 'Operasional & Armada', 'Ahli dalam rekayasa alat berat, manajemen supply chain CBU, dan optimalisasi workshop sentral. Memimpin deployment armada di berbagai mega-proyek nasional.', 'assets/img/team-coo.jpg', 'https://linkedin.com', 2],
            [3, 'Farah Kusuma Dewi, S.E., Ak., CA', 'Chief Financial Officer', 'Keuangan & Tata Kelola', 'Spesialis audit kepatuhan korporat, pemodelan CAPEX, dan penerapan tata kelola TARIF. Berpengalaman di kantor akuntan publik terkemuka.', 'assets/img/team-cfo.jpg', 'https://linkedin.com', 3]
        ];
        foreach ($team as $tm) {
            $teamStmt->execute($tm);
        }
        echo "[✓] Team Members berhasil di-seed (3 eksekutif).\n";
    } else {
        echo "[i] Team Members sudah memiliki data ({$countTeam} baris).\n";
    }

    // 7. SEED ARTICLES
    $countArt = (int)$db->query("SELECT COUNT(*) FROM articles")->fetchColumn();
    if ($countArt === 0) {
        $artStmt = $db->prepare("
            INSERT INTO articles (id, title, slug, category, excerpt, content, cover_image, author_name, status, views_count, published_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'published', ?, ?)
        ");
        $articles = [
            [1, 'Peluang dan Prospek Investasi Sektor Riil Alat Berat Indonesia 2026', 'prospek-investasi-sektor-riil-alat-berat-2026', 'Wawasan Industri', 'Analisis mendalam mengenai lonjakan kebutuhan unit alat berat di sektor infrastruktur dan industri, serta bagaimana model bagi hasil per proyek menawarkan imbal hasil terukur dengan jaminan aset fisik.', '<p>Pertumbuhan pembangunan infrastruktur strategis dan ekspansi kawasan industri di Indonesia terus mendorong permintaan terhadap ketersediaan unit alat berat prima seperti excavator, wheel loader, dan dump truck.</p><p>Model investasi per proyek yang diterapkan oleh PT Montana Global Investama memberikan kepastian transparansi bagi pemodal, di mana setiap unit modal dialokasikan langsung untuk pengadaan unit riil yang memiliki kontrak kerja sewa jelas dengan mitra kontraktor kredibel.</p><p>Dengan tata kelola terintegrasi, pemodal tidak hanya memperoleh laporan progres berkala namun juga dividen bagi hasil proporsional berdasarkan utilisasi jam kerja unit di lapangan.</p>', 'assets/img/hero-alat-berat.png', 'Tim Riset Riset & Analis MGI', 142, date('Y-m-d H:i:s')],
            [2, 'Implementasi Tata Kelola TARIF dalam Mitigasi Risiko Investasi Proyek', 'implementasi-tata-kelola-tarif-mitigasi-risiko-proyek', 'Tata Kelola (GCG)', 'Bagaimana penerapan prinsip Transparansi, Akuntabilitas, Responsibilitas, Independensi, dan Fairness (TARIF) melindungi modal investor eksternal pada setiap siklus investasi riil.', '<p>Di tengah dinamika perekonomian global, integritas tata kelola korporat (Good Corporate Governance) menjadi fondasi paling krusial bagi keberlangsungan investasi jangka panjang.</p><p>PT Montana Global Investama menerapkan prinsip TARIF secara menyeluruh mulai dari due diligence legalitas rekanan kerja, pengawasan RAB berbasis sistem ERP Odoo, hingga rekening penampung escrow mandiri untuk setiap proyek investasi terdaftar.</p><p>Melalui portal investor terenkripsi, mitra pemodal dapat memantau real-time invoice, bukti transfer dividen, dan status asuransi unit alat berat yang beroperasi.</p>', 'assets/img/icon-tata-kelola.png', 'Dewan Kepatuhan & Manajemen Risiko MGI', 98, date('Y-m-d H:i:s')],
            [3, 'Keunggulan Sinergi Ekosistem Montana Group: Dari Pengadaan hingga After-Sales', 'keunggulan-sinergi-ekosistem-montana-group', 'Sinergi Ekosistem', 'Mengenal sinergi rantai nilai antara PT Montana Indo Utama (workshop sentral Kebumen), PT Montana Sentra Industri, dan Mypurcase dalam menjaga efisiensi operasional armada.', '<p>Salah satu kelemahan umum bisnis penyewaan alat berat konvensional adalah tingginya downtime akibat lambatnya pengadaan suku cadang dan keterbatasan teknisi di lokasi kerja remote.</p><p>Ekosistem Montana Group mengatasi tantangan tersebut melalui integrasi hulu ke hilir: PT Montana Indo Utama mengoperasikan workshop sentral dan mobile towing service di Kebumen, sementara platform digital Mypurcase menjamin pasokan suku cadang CBU Jepang dengan harga kompetitif.</p><p>Sinergi ini memastikan unit alat berat selalu beroperasi pada tingkat utilisasi optimal di atas 90%, mengamankan arus kas dan bagi hasil bagi para investor.</p>', 'assets/img/icon-miu-truck.png', 'Divisi Operasional & Rekayasa Teknik', 115, date('Y-m-d H:i:s')]
        ];
        foreach ($articles as $a) {
            $artStmt->execute($a);
        }
        echo "[✓] Articles berhasil di-seed (3 artikel wawasan).\n";
    } else {
        echo "[i] Articles sudah memiliki data ({$countArt} baris).\n";
    }

    // Trigger Realtime Broadcast to update all clients
    triggerRealtimeEvent('full_sync', 'all', 'update');
    echo "\n=== SEMUA DATA CMS BERHASIL DIPOPULASIKAN KE DATABASE ===\n";

} catch (Throwable $e) {
    echo "\n[ERROR] Gagal melakukan seeding: " . $e->getMessage() . "\n";
    exit(1);
}
