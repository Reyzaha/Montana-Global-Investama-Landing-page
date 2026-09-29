-- =====================================================================
-- DATABASE DUMP: PT MONTANA GLOBAL INVESTAMA (mgi_landing)
-- Exported from Localhost: 2026-09-28 10:03:18
-- Encoding: UTF-8 without BOM
-- =====================================================================

SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';
SET time_zone = '+07:00';

CREATE DATABASE IF NOT EXISTS `mgi_landing` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `mgi_landing`;

-- -----------------------------------------------------
-- Table structure for `admin_logs`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `admin_logs`;
CREATE TABLE `admin_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `target_type` varchar(50) NOT NULL,
  `target_id` varchar(100) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_admin_log_user` (`admin_id`),
  CONSTRAINT `fk_admin_log_user` FOREIGN KEY (`admin_id`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `admin_logs`
INSERT INTO `admin_logs` (`id`, `admin_id`, `action`, `target_type`, `target_id`, `details`, `ip_address`, `created_at`) VALUES
('1', '2', 'login', 'admin_users', '2', 'Admin successfully logged in with MFA from IP ::1', '::1', '2026-09-15 15:25:25'),
('2', '2', 'upload_file', 'assets', NULL, 'Admin uploaded file \'test_komatsu_1789637059_db2cf7e3.png\' into \'projects\'', '127.0.0.1', '2026-09-17 16:24:19'),
('4', NULL, 'mfa_failed', 'admin_users', '2', 'Invalid MFA OTP attempt from IP ::1', '::1', '2026-09-23 14:45:32'),
('5', NULL, 'mfa_failed', 'admin_users', '2', 'Invalid MFA OTP attempt from IP ::1', '::1', '2026-09-23 14:45:39'),
('6', NULL, 'login_failed', 'admin_users', NULL, 'Failed login attempt for \'admin\' from IP ::1', '::1', '2026-09-23 14:47:51'),
('7', '1', 'create_financial_billing', 'financial_records', '10', 'Admin created pembelian record/billing \'Pengadaan Tambahan Unit PC138US via MIU\' (BILL-MIU-2026-TEST) nominal Rp 1.400.000.000 for investor ID 1', '127.0.0.1', '2026-09-28 10:56:09');

-- -----------------------------------------------------
-- Table structure for `admin_users`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `admin_users`;
CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `email` varchar(191) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(150) NOT NULL DEFAULT 'Super Administrator',
  `role` enum('superadmin','admin','editor') NOT NULL DEFAULT 'admin',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `mfa_secret` varchar(64) DEFAULT NULL,
  `mfa_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `admin_users`
INSERT INTO `admin_users` (`id`, `username`, `email`, `password_hash`, `full_name`, `role`, `is_active`, `mfa_secret`, `mfa_enabled`, `last_login`, `created_at`, `updated_at`) VALUES
('1', 'superadmin', 'superadmin@montanaglobalinvestama.com', '$2y$10$vy69at/M6/o2DNTYCW7AfOg4g10LuOefJYnFJHC7xyTB5qPCJ5SRK', 'Super Administrator MGI', 'superadmin', '1', NULL, '0', NULL, '2026-09-24 16:53:48', '2026-09-28 10:53:40'),
('2', 'admin', 'admin@montanaglobalinvestama.com', '$2y$10$vy69at/M6/o2DNTYCW7AfOg4g10LuOefJYnFJHC7xyTB5qPCJ5SRK', 'Administrator Operasional MGI', 'admin', '1', NULL, '0', NULL, '2026-09-24 16:53:48', '2026-09-28 10:53:40');

-- -----------------------------------------------------
-- Table structure for `campaign_updates`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `campaign_updates`;
CREATE TABLE `campaign_updates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` varchar(50) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `category` varchar(100) NOT NULL DEFAULT 'Operasional Lapangan',
  `update_date` date NOT NULL,
  `content` text NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_campaign_project` (`project_id`),
  CONSTRAINT `fk_campaign_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `campaign_updates`
INSERT INTO `campaign_updates` (`id`, `project_id`, `title`, `category`, `update_date`, `content`, `image_url`, `sort_order`, `created_at`) VALUES
('1', 'proj-jkt-jabar', 'Kedatangan Unit Excavator Komatsu CBU Jepang di Pool Jabodetabek & Jawa Barat', 'Logistik & Impor CBU', '2024-08-20', 'Alhamdulillah, unit Excavator Hydraulic Komatsu PC138US-8 Grade A asal Jepang telah tiba dengan selamat di Pusat Workshop & Pool Jabodetabek & Jawa Barat. Tim mekanik telah menyelesaikan tahap PDI (Pre-Delivery Inspection), pengujian hidrolik, pemasangan sistem GPS tracking terpadu, dan penerbitan sertifikat kelaikan fungsi operasional.', 'assets/img/project-jabodetabek.jpg', '1', '2026-09-24 16:53:48'),
('2', 'proj-jkt-jabar', 'Mobilisasi Unit ke Proyek Strategis Daerah & Kesiapan Montana Towing', 'Operasional Lapangan', '2024-09-02', 'Unit telah diberangkatkan menuju lokasi proyek kawasan industri dengan pengawalan armada Montana Towing. Seluruh unit mencatatkan utilitas kerja rata-rata 8,5 jam per hari tanpa kendala mekanis.', 'assets/img/project-jabodetabek.jpg', '2', '2026-09-24 16:53:48'),
('3', 'proj-jkt-jabar', 'Distribusi Imbal Hasil Kuartal II Berjalan Lancar ke Rekening Investor', 'Laporan Keuangan & Dividen', '2024-07-15', 'Manajemen PT Montana Global Investama telah menuntaskan distribusi bagi hasil kompetitif periode Q2 kepada seluruh investor mitra perorangan dan korporasi terdaftar secara tepat waktu. Laporan keuangan proyek telah diverifikasi oleh tim kepatuhan internal.', 'assets/img/project-jabodetabek.jpg', '3', '2026-09-24 16:53:48');

-- -----------------------------------------------------
-- Table structure for `cities`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `cities`;
CREATE TABLE `cities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `icon` varchar(50) NOT NULL DEFAULT 'bi-geo-alt-fill',
  `description` varchar(255) DEFAULT NULL,
  `is_active_segment` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `cities`
INSERT INTO `cities` (`id`, `name`, `slug`, `icon`, `description`, `is_active_segment`, `sort_order`, `created_at`, `updated_at`) VALUES
('1', 'Kebumen', 'kebumen', 'bi-building-gear', 'Sentral Pool & Workshop Alat Berat 4.500 m² Jawa Tengah', '1', '1', '2026-09-23 11:56:42', '2026-09-23 11:56:42'),
('2', 'Cilacap', 'cilacap', 'bi-water', 'Kawasan Industri Semen & Logistik Pelabuhan Tanjung Intan', '1', '2', '2026-09-23 11:56:42', '2026-09-23 11:56:42'),
('3', 'Semarang', 'semarang', 'bi-pin-map-fill', 'Koridor Logistik, Kuari Agregat & Infrastruktur Tol Trans Jawa', '1', '3', '2026-09-23 11:56:42', '2026-09-23 11:56:42'),
('4', 'Bali', 'bali', 'bi-sun-fill', 'Ekspansi Unit Compact Resor, Hotel Premium & Pariwisata', '1', '4', '2026-09-23 11:56:42', '2026-09-23 11:56:42'),
('5', 'NTB', 'ntb', 'bi-geo-alt-fill', 'Proyek Pertambangan Mineral & Infrastruktur Jalan Lingkar Lombok-Sumbawa', '1', '5', '2026-09-23 11:56:42', '2026-09-23 11:56:42');

-- -----------------------------------------------------
-- Table structure for `company_profile`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `company_profile`;
CREATE TABLE `company_profile` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` longtext NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=75 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `company_profile`
INSERT INTO `company_profile` (`id`, `setting_key`, `setting_value`, `updated_at`) VALUES
('1', 'main_profile', '{\n  \"company_name\": \"PT Montana Global Investama\",\n  \"short_name\": \"MGI\",\n  \"group_name\": \"Montana Group\",\n  \"tagline_id\": \"Tumbuh Bersama Melalui Investasi Terpercaya\",\n  \"tagline_en\": \"Growing Together Through Trusted Investments\",\n  \"founded_year\": \"2022\",\n  \"kantor_operasional\": \"Roseville Soho & Suite, Sunburst CBD Lot I.8, Jl. Kapten Soebijanto Djojohadikusumo, Lengkong Gudang, Serpong, Tangerang Selatan\",\n  \"headquarters\": \"Roseville Soho & Suite, Sunburst CBD Lot I.8, Jl. Kapten Soebijanto Djojohadikusumo, Lengkong Gudang, Serpong, Tangerang Selatan\",\n  \"stats\": [\n    {\n      \"label\": \"Pendanaan Armada\",\n      \"value\": \"Rp 100M+\",\n      \"desc\": \"Aset Fisik Produktif\"\n    },\n    {\n      \"label\": \"Armada Komatsu\",\n      \"value\": \"125+ Unit\",\n      \"desc\": \"CBU Siap Operasi\"\n    },\n    {\n      \"label\": \"Fasilitas Sentral\",\n      \"value\": \"4.500 m²\",\n      \"desc\": \"Pool & Workshop Kebumen\"\n    },\n    {\n      \"label\": \"Ekosistem Usaha\",\n      \"value\": \"3 Pilar\",\n      \"desc\": \"MIU, MSI & Mypurcase\"\n    }\n  ],\n  \"about_group\": {\n    \"title\": \"Tentang Montana Group\",\n    \"subtitle\": \"Kelompok Usaha Sektor Riil Berdaya Saing Tinggi & Berkelanjutan\",\n    \"description\": \"Montana Group adalah kelompok usaha yang dipimpin oleh PT Montana Global Investama (MGI) selaku grup dan manajer investasi project, yang menaungi tiga pilar usaha operasional teruji di lapangan. Model ini memungkinkan MGI berfokus pada tata kelola, alokasi modal per project, dan manajemen risiko tingkat grup, sementara setiap unit usaha menjalankan eksekusi operasional sesuai bidang keahliannya masing-masing.\",\n    \"positioning\": \"Dengan struktur grup ini, PT Montana Global Investama memposisikan diri sebagai wadah investasi per project terpercaya bagi pemodal yang ingin berpartisipasi pada pertumbuhan sektor riil (khususnya alat berat, industri logistik, dan rantai ketersediaan pengadaan) melalui satu pintu masuk project investasi yang dikelola secara profesional, transparan, dan berlandaskan kerangka tata kelola TARIF.\"\n  },\n  \"entities\": [\n    {\n      \"code\": \"MGI\",\n      \"name\": \"PT Montana Global Investama\",\n      \"slogan\": \"Tumbuh Bersama Melalui Investasi Terpercaya\",\n      \"role\": \"Manajer Project Investasi & Grup (Tata Kelola, Alokasi Modal Per Project, Manajemen Risiko)\",\n      \"level\": \"Level 0 (Grup)\",\n      \"badge\": \"Grup & Manajer Proyek Investasi\",\n      \"color\": \"gold\",\n      \"description\": \"PT Montana Global Investama berperan sebagai grup dan manajer project investasi (tata kelola, alokasi modal per project, manajemen risiko), menghadirkan instrumen investasi per project sektor riil berbasis aset fisik bernilai tinggi dengan proyeksi pengembalian modal 2-3 tahun.\",\n      \"focus\": \"Pengelolaan portofolio investasi per project, tata kelola TARIF, mitigasi risiko, hubungan investor\",\n      \"output\": \"Alokasi modal per project terukur, kepatuhan laporan berkala, monitoring kinerja proyek\"\n    },\n    {\n      \"code\": \"MIU\",\n      \"name\": \"PT Montana Indo Utama (MIU)\",\n      \"slogan\": \"Solusi Alat Berat Terintegrasi\",\n      \"role\": \"Solusi Alat Berat Terintegrasi — Penjualan, Ketersediaan Armada, Penempatan & Dukungan Operasional\",\n      \"level\": \"Level 1 (Unit Usaha)\",\n      \"badge\": \"Alat Berat & Dukungan Operasional\",\n      \"color\": \"blue\",\n      \"description\": \"Solusi Alat Berat Terintegrasi. Mendukung kebutuhan armada alat berat melalui ketersediaan, penempatan, dan dukungan teknis operasional untuk proyek sektor riil.\",\n      \"focus\": \"Penjualan & suplai unit alat berat, penempatan ke lokasi proyek, perawatan berkala, workshop sentral Kebumen, dan dukungan operasional sektor riil\",\n      \"output\": \"Ketersediaan armada unit alat berat siap kerja, keandalan operasional lapangan, servis darurat siaga, dan kepastian produktivitas proyek\",\n      \"location\": \"Jl. Nasional III No.88, Pejuritan, Kec. Karanganyar, Kab. Kebumen, Jawa Tengah\"\n    },\n    {\n      \"code\": \"MSI\",\n      \"name\": \"PT Montana Sentra Industri (MSI)\",\n      \"slogan\": \"Mitra Impor & Logistik Terpadu\",\n      \"role\": \"Mitra Impor & Logistik — Solusi Impor Alat Berat, Pengurusan Bea Cukai & Distribusi Langsung\",\n      \"level\": \"Level 1 (Unit Usaha)\",\n      \"badge\": \"Mitra Impor & Logistik\",\n      \"color\": \"navy\",\n      \"description\": \"Mitra Impor & Logistik. Menyediakan solusi impor alat berat, pengurusan kepabeanan, dan distribusi langsung ke lokasi proyek untuk kebutuhan industri.\",\n      \"focus\": \"Solusi pengadaan impor alat berat, pengurusan kepabeanan pelabuhan terpadu, dan manajemen pengiriman logistik langsung ke lokasi proyek dan industri\",\n      \"output\": \"Kelancaran izin impor kepabeanan, kepastian ketersediaan armada internasional, efisiensi rantai distribusi, dan pengiriman aman tepat waktu\",\n      \"location\": \"Jl. Revolusi No. 89, Desa/Kelurahan Karanganyar, Kec. Karanganyar, Kab. Kebumen, Provinsi Jawa Tengah\"\n    },\n    {\n      \"code\": \"Mypurcase\",\n      \"name\": \"Mypurcase (Segera Diluncurkan 2027)\",\n      \"slogan\": \"Platform Pengadaan Digital B2B\",\n      \"role\": \"Platform Pengadaan Digital — Penghubung Pembelian, Pemasok, Pengadaan & Rantai Ketersediaan\",\n      \"level\": \"Level 1 (Platform Pengadaan)\",\n      \"badge\": \"Platform Pengadaan B2B\",\n      \"color\": \"gold\",\n      \"description\": \"Platform Pengadaan B2B yang menghubungkan kebutuhan pembelian dengan pemasok terverifikasi, proses pengadaan, dan rantai ketersediaan secara digital, efisien, dan transparan.\",\n      \"focus\": \"Digitalisasi pengadaan B2B, integrasi kebutuhan pembelian dengan pemasok terverifikasi, otomatisasi alur pembelian, dan transparansi rantai ketersediaan\",\n      \"output\": \"Proses pengadaan digital cepat dan transparan, efisiensi belanja modal, pelacakan transaksi langsung, dan tata kelola akuntabel\"\n    }\n  ],\n  \"sections\": [\n    {\n      \"type\": \"text\",\n      \"id\": \"tentang-kami\",\n      \"title\": \"Tentang Kami\",\n      \"subtitle\": \"Membangun Masa Depan Melalui Sinergi Investasi Produktif\",\n      \"content\": \"PT Montana Global Investama (MGI) berperan sebagai grup & manajer investasi (tata kelola, alokasi modal, manajemen risiko), sementara eksekusi operasional dijalankan oleh tiga unit usaha teruji di lapangan: PT Montana Indo Utama, PT Montana Sentra Industri, dan Mypurcase.\",\n      \"quote\": \"Di Montana Global Investama, setiap keputusan investasi berlandaskan pada tiga prinsip utama: aset yang nyata dan terukur, tata kelola yang transparan dan akuntabel, serta kemitraan strategis yang menciptakan nilai berkelanjutan.\"\n    },\n    {\n      \"type\": \"vision_mission\",\n      \"id\": \"visi-misi\",\n      \"title\": \"Visi & Misi Perusahaan\",\n      \"subtitle\": \"Arah Strategis Menuju Keunggulan Pengelolaan Modal Terpercaya\",\n      \"vision\": \"Menjadi perusahaan pengelolaan dana investasi terpercaya di Indonesia yang menghubungkan modal strategis dengan potensi sektor riil bernilai tinggi secara berkesinambungan.\",\n      \"missions\": [\n        \"Mengalokasikan permodalan secara tepat guna pada aset fisik produktif bernilai tinggi dengan perlindungan legal dan asuransi.\",\n        \"Membangun kemitraan strategis offtake jangka menengah hingga panjang untuk menjamin kepastian penyerapan unit proyek.\",\n        \"Menerapkan prinsip tata kelola yang baik secara konsisten melalui kerangka TARIF dalam setiap keputusan dan alokasi modal.\",\n        \"Mengoptimalkan sinergi ekosistem (MGI, MIU, MSI, Mypurcase) untuk menciptakan efisiensi biaya dan perlindungan nilai terintegrasi.\",\n        \"Memberikan transparansi dan pelaporan kinerja berkala demi menjaga akuntabilitas dan kepercayaan mitra pemodal.\"\n      ]\n    },\n    {\n      \"type\": \"cards\",\n      \"id\": \"core-values\",\n      \"title\": \"Nilai-Nilai Perusahaan\",\n      \"subtitle\": \"Fondasi Moral & Profesional Dalam Setiap Pengambilan Keputusan\",\n      \"items\": [\n        {\n          \"icon\": \"shield-check\",\n          \"img\": \"assets/img/icon-tata-kelola.png\",\n          \"name\": \"Integritas\",\n          \"desc\": \"Menjunjung tinggi kejujuran, etika bisnis luhur, dan keterbukaan dalam setiap transaksi dan kemitraan.\"\n        },\n        {\n          \"icon\": \"award\",\n          \"img\": \"assets/img/icon-imbal-hasil.png\",\n          \"name\": \"Profesionalisme\",\n          \"desc\": \"Didukung tim ahli berpengalaman dengan dedikasi tinggi terhadap standar eksekusi terbaik di industri.\"\n        },\n        {\n          \"icon\": \"eye\",\n          \"img\": \"assets/img/icon-transparansi.png\",\n          \"name\": \"Transparansi\",\n          \"desc\": \"Penyajian laporan kinerja berkala, audit independen, dan visibilitas alokasi modal secara menyeluruh kepada investor.\"\n        },\n        {\n          \"icon\": \"gear-wide-connected\",\n          \"img\": \"assets/img/icon-kemitraan.png\",\n          \"name\": \"Kemitraan Strategis\",\n          \"desc\": \"Membangun ekosistem terintegrasi melalui kepastian kerja sama dan kontrak jangka menengah hingga panjang.\"\n        },\n        {\n          \"icon\": \"people\",\n          \"img\": \"assets/img/icon-alokasi-modal.png\",\n          \"name\": \"Sinergi Ekosistem\",\n          \"desc\": \"Menciptakan sinergi erat lintas entitas (MIU, MSI, Mypurcase, MGI) guna memaksimalkan penciptaan nilai kolektif.\"\n        }\n      ]\n    },\n    {\n      \"type\": \"gcg\",\n      \"id\": \"tata-kelola\",\n      \"title\": \"Tata Kelola sebagai Fondasi Kepercayaan\",\n      \"subtitle\": \"Prinsip tata kelola yang baik, transparan, akuntabel, dan terukur diterapkan secara konsisten melalui kerangka TARIF dalam setiap proses pengambilan keputusan dan alokasi modal.\",\n      \"items\": [\n        {\n          \"code\": \"T\",\n          \"name\": \"Transparency\",\n          \"desc\": \"Keterbukaan informasi material dan relevan kepada seluruh pemangku kepentingan.\",\n          \"icon\": \"eye\",\n          \"img\": \"assets/img/icon-transparansi.png\"\n        },\n        {\n          \"code\": \"A\",\n          \"name\": \"Accountability\",\n          \"desc\": \"Kejelasan fungsi, struktur, sistem, dan pertanggungjawaban pengelolaan investasi.\",\n          \"icon\": \"clipboard-check\",\n          \"img\": \"assets/img/icon-tata-kelola.png\"\n        },\n        {\n          \"code\": \"R\",\n          \"name\": \"Responsibility\",\n          \"desc\": \"Kepatuhan penuh terhadap hukum, regulasi, dan tanggung jawab sosial-lingkungan.\",\n          \"icon\": \"people\",\n          \"img\": \"assets/img/icon-kemitraan.png\"\n        },\n        {\n          \"code\": \"I\",\n          \"name\": \"Independency\",\n          \"desc\": \"Pengambilan keputusan profesional objektif bebas dari benturan kepentingan.\",\n          \"icon\": \"balance\",\n          \"img\": \"assets/img/icon-alokasi-modal.png\"\n        },\n        {\n          \"code\": \"F\",\n          \"name\": \"Fairness\",\n          \"desc\": \"Perlakuan adil dan setara bagi seluruh investor dalam pemenuhan hak-hak pemodal.\",\n          \"icon\": \"equal\",\n          \"img\": \"assets/img/icon-imbal-hasil.png\"\n        }\n      ]\n    }\n  ],\n  \"contact\": {\n    \"corporate_office\": \"Roseville Soho & Suite, Sunburst CBD Lot I.8, Jl. Kapten Soebijanto Djojohadikusumo, Lengkong Gudang, Serpong, Tangerang Selatan\",\n    \"operational_office\": \"Jl. Nasional III No.88, Pejuritan, Kec. Karanganyar, Kab. Kebumen, Jawa Tengah\",\n    \"email\": \"kontak@montanaglobalinvestama.com\",\n    \"work_hours\": \"Senin – Jumat: 08.00 – 17.00 WIB\",\n    \"google_maps_url\": \"https://maps.google.com/?q=Roseville+Soho+Sunburst+CBD+Serpong+Tangerang+Selatan\"\n  }\n}', '2026-09-28 11:12:04');

-- -----------------------------------------------------
-- Table structure for `ekosistem_nodes`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `ekosistem_nodes`;
CREATE TABLE `ekosistem_nodes` (
  `id` varchar(50) NOT NULL,
  `label` varchar(255) NOT NULL,
  `short_label` varchar(150) DEFAULT NULL,
  `subtitle` varchar(255) DEFAULT NULL,
  `parent_id` varchar(50) DEFAULT NULL,
  `level` int(11) NOT NULL DEFAULT 0,
  `badge` varchar(100) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `category` varchar(150) DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `role_desc` text DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_ekosistem_parent` (`parent_id`),
  CONSTRAINT `fk_ekosistem_parent` FOREIGN KEY (`parent_id`) REFERENCES `ekosistem_nodes` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `ekosistem_nodes`
INSERT INTO `ekosistem_nodes` (`id`, `label`, `short_label`, `subtitle`, `parent_id`, `level`, `badge`, `type`, `category`, `icon`, `role_desc`, `sort_order`, `created_at`) VALUES
('mgi-holding', 'PT Montana Global Investama', NULL, NULL, NULL, '0', 'Grup & Pengelola Modal Project', NULL, NULL, NULL, 'Induk usaha (grup), manajer investasi, dan pengelola modal project grup. Menghadirkan instrumen project investasi riil dengan prinsip agresif dan menguntungkan berimbal hasil menarik serta tata kelola perusahaan yang baik (TARIF).', '1', '2026-09-24 16:53:48'),
('miu', 'PT Montana Indo Utama (MIU)', NULL, NULL, 'mgi-holding', '1', 'Kantor & Workshop Sentral', NULL, NULL, NULL, 'Entitas operasional terpadu yang berevolusi dari CV Montana Machine. Fokus pada armada alat berat khusus merek Komatsu CBU Jepang Grade A, fasilitas kantor & workshop sentral pool 4.500 m² di Kebumen, armada Montana Towing siaga 24 jam, serta utilisasi operasional di Jawa Tengah, Bali, dan NTB.', '2', '2026-09-24 16:53:48'),
('montana-sentra-industri', 'Montana Sentra Industri (MSI)', NULL, NULL, 'mgi-holding', '1', 'Supply Import Laut & Ekspedisi', NULL, NULL, NULL, 'Fokus pada supply import unit mesin dan alat berat langsung dari Jepang atau Cina ke Indonesia melalui jalur laut hingga sampai ke alamat customer secara efisien dan aman.', '3', '2026-09-24 16:53:48'),
('mypurcase', 'Mypurcase (Coming Soon 2027)', NULL, NULL, 'mgi-holding', '1', 'Coming Soon 2027', NULL, NULL, NULL, 'System dan platform pengadaan terpadu untuk purchasing unit dan mesin secara efisien, transparan, dan terukur lintas entitas dalam ekosistem Montana Group.', '4', '2026-09-24 16:53:48'),
('workshop-kebumen', 'Kantor, Workshop & Pool Sentral Kebumen', NULL, NULL, 'miu', '2', 'Fasilitas Kantor & Workshop', NULL, NULL, NULL, 'Pangkalan workshop seluas 4.500 m² di Kebumen dengan teknisi berpengalaman, stok unit Komatsu tersedia, dan armada Montana Towing.', '5', '2026-09-24 16:53:48');

-- -----------------------------------------------------
-- Table structure for `financial_records`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `financial_records`;
CREATE TABLE `financial_records` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `investor_id` int(11) NOT NULL,
  `project_id` varchar(50) NOT NULL,
  `module` enum('neraca','labarugi','pembelian','penjualan') NOT NULL,
  `category` varchar(100) NOT NULL,
  `record_number` varchar(100) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `amount` decimal(18,2) NOT NULL,
  `flow_type` enum('in','out','balance_asset') DEFAULT 'out',
  `vendor_client` varchar(255) DEFAULT NULL,
  `unit_detail` varchar(255) DEFAULT NULL,
  `status` enum('draft','sent','paid','verified') DEFAULT 'sent',
  `is_billing` tinyint(1) DEFAULT 1,
  `billing_status` enum('unpaid','paid','settled','reported') DEFAULT 'reported',
  `transaction_date` date NOT NULL,
  `due_date` date DEFAULT NULL,
  `attachment_url` varchar(255) DEFAULT NULL,
  `created_by` varchar(100) DEFAULT 'Admin MGI',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_fin_investor` (`investor_id`),
  KEY `idx_fin_project` (`project_id`),
  KEY `idx_fin_module` (`module`),
  KEY `idx_fin_billing` (`is_billing`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `financial_records`
INSERT INTO `financial_records` (`id`, `investor_id`, `project_id`, `module`, `category`, `record_number`, `title`, `description`, `amount`, `flow_type`, `vendor_client`, `unit_detail`, `status`, `is_billing`, `billing_status`, `transaction_date`, `due_date`, `attachment_url`, `created_by`, `created_at`, `updated_at`) VALUES
('1', '1', 'proj-jkt-jabar', 'neraca', 'Modal Disetor & Kas Awal', 'CAP-MGI-2026-001', 'Penempatan Modal Investasi Proyek', 'Setoran penempatan modal kerja proyek regional Jabodetabek senilai Rp 5.000.000.000 ditempatkan di rekening escrow proyek Bank Mandiri.', '5000000000.00', 'in', 'PT Montana Global Investama', 'Akad Kontrak Proyek Komatsu', 'verified', '0', 'settled', '2026-01-05', '2026-01-05', NULL, 'Admin MGI', '2026-09-28 10:36:32', '2026-09-28 10:36:32'),
('2', '1', 'proj-jkt-jabar', 'pembelian', 'Pengadaan Unit Alat Berat (MIU)', 'BILL-MIU-2026-001', 'Pembelian 2x Unit Excavator Komatsu PC138US via MIU', 'Alokasi penyerapan modal 5 Miliar: pengurangan kas sebesar Rp 2.800.000.000 untuk realisasi pembelian 2 unit Excavator Komatsu PC138US Grade A CBU Jepang melalui vendor internal grup PT Montana Industri Utama (MIU).', '2800000000.00', 'out', 'PT Montana Industri Utama (MIU)', '2x Komatsu PC138US-8 (SN: KM-9481 & KM-9482)', 'verified', '1', 'settled', '2026-01-18', '2026-01-25', NULL, 'Admin MGI', '2026-09-28 10:36:32', '2026-09-28 10:36:32'),
('3', '1', 'proj-jkt-jabar', 'pembelian', 'Attachment & Logistik CBU (MIU)', 'BILL-MIU-2026-002', 'Pengadaan Breaker & Logistik Impor CBU via MIU', 'Pengadaan hydraulic breaker kit & biaya customs clearance logistik impor unit CBU Jepang via workshop PT Montana Industri Utama (MIU).', '350000000.00', 'out', 'PT Montana Industri Utama (MIU)', '2 Set Hydraulic Breaker & Sparepart Kit', 'verified', '1', 'settled', '2026-02-10', '2026-02-15', NULL, 'Admin MGI', '2026-09-28 10:36:32', '2026-09-28 10:36:32'),
('4', '1', 'proj-jkt-jabar', 'neraca', 'Aset Tetap Unit Fisik (MIU)', 'AST-MIU-2026-001', 'Kapitalisasi Aset Unit Alat Berat MIU', 'Pencatatan aktiva tetap unit fisik 2x Komatsu PC138US di pool workshop Jabodetabek bernilai perolehan Rp 3.150.000.000.', '3150000000.00', 'balance_asset', 'PT Montana Industri Utama (MIU)', '2x Komatsu PC138US-8 CBU Jepang', 'verified', '0', 'settled', '2026-02-15', '2026-02-15', NULL, 'Admin MGI', '2026-09-28 10:36:32', '2026-09-28 10:36:32'),
('5', '1', 'proj-jkt-jabar', 'penjualan', 'Kontrak Sewa Unit Mining & Earthmoving', 'INV-SLS-2026-001', 'Kontrak Sewa Utilisasi Armada Komatsu — Tahap 1', 'Pendapatan sewa operasional 2 unit Komatsu PC138US untuk proyek Cut & Fill kawasan industri Jabodetabek selama 2 bulan operasional.', '450000000.00', 'in', 'PT Surya Semesta Mandiri', 'Kontrak Sewa Unit KM-9481 & KM-9482', 'verified', '0', 'paid', '2026-03-25', '2026-03-30', NULL, 'Admin MGI', '2026-09-28 10:36:32', '2026-09-28 10:36:32'),
('6', '1', 'proj-jkt-jabar', 'labarugi', 'Beban Operasional & Pemeliharaan', 'EXP-OPS-2026-001', 'Beban Pemeliharaan Rutin Workshop & Fuel', 'Biaya suku cadang habis pakai (fast moving parts), oli hidrolik, dan pemeliharaan rutin di pool workshop Kebumen & Jabodetabek.', '85000000.00', 'out', 'Workshop Pool MGI Regional', 'Service Rutin 250 Jam', 'verified', '0', 'settled', '2026-03-28', '2026-03-28', NULL, 'Admin MGI', '2026-09-28 10:36:32', '2026-09-28 10:36:32'),
('7', '1', 'proj-jkt-jabar', 'labarugi', 'Distribusi Dividen Bagi Hasil', 'BILL-DIV-2026-Q1', 'Billing Bagi Hasil Dividen Kuartal I - 2026 (Nett)', 'Faktur distribusi laba bersih operasional proyek periode Kuartal I 2026 yang ditransfer ke rekening bank terdaftar investor.', '125000000.00', 'out', 'Investor PT Montana Global Investama', 'Bagi Hasil Kuartal I', 'verified', '1', 'paid', '2026-04-15', '2026-04-15', NULL, 'Admin MGI', '2026-09-28 10:36:32', '2026-09-28 10:36:32'),
('8', '1', 'proj-jkt-jabar', 'penjualan', 'Kontrak Sewa Unit Infrastruktur', 'INV-SLS-2026-002', 'Kontrak Sewa Utilisasi Armada Komatsu — Tahap 2', 'Pendapatan sewa utilisasi lanjutan untuk proyek tol dan logistik kawasan barat.', '520000000.00', 'in', 'PT Citra Konstruksi Nusantara', 'Kontrak Sewa Unit KM-9481 & KM-9482', 'verified', '0', 'paid', '2026-06-28', '2026-06-30', NULL, 'Admin MGI', '2026-09-28 10:36:32', '2026-09-28 10:36:32'),
('9', '1', 'proj-jkt-jabar', 'labarugi', 'Distribusi Dividen Bagi Hasil', 'BILL-DIV-2026-Q2', 'Billing Bagi Hasil Dividen Kuartal II - 2026 (Nett)', 'Faktur dividen bagi hasil kompetitif periode Kuartal II 2026 yang telah berhasil disalurkan ke rekening bank investor.', '145000000.00', 'out', 'Investor PT Montana Global Investama', 'Bagi Hasil Kuartal II', 'verified', '1', 'paid', '2026-07-15', '2026-07-15', NULL, 'Admin MGI', '2026-09-28 10:36:32', '2026-09-28 10:36:32');

-- -----------------------------------------------------
-- Table structure for `funding_items`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `funding_items`;
CREATE TABLE `funding_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` varchar(50) NOT NULL,
  `no_urut` int(11) NOT NULL DEFAULT 1,
  `item_name` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `unit_price` bigint(20) NOT NULL DEFAULT 0,
  `total` bigint(20) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_funding_item_project` (`project_id`),
  CONSTRAINT `fk_funding_item_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `funding_items`
INSERT INTO `funding_items` (`id`, `project_id`, `no_urut`, `item_name`, `quantity`, `unit_price`, `total`, `created_at`) VALUES
('1', 'proj-jkt-jabar', '1', 'Excavator Komatsu PC200-8 CBU Jepang Grade A', '3', '1650000000', '4950000000', '2026-09-28 10:34:14'),
('2', 'proj-jkt-jabar', '2', 'Excavator Komatsu PC138US-8 Compact Tail', '3', '950000000', '2850000000', '2026-09-28 10:34:14'),
('3', 'proj-jkt-jabar', '3', 'Dukungan Armada Montana Towing & Workshop Overhaul', '1', '1200000000', '1200000000', '2026-09-28 10:34:14'),
('4', 'proj-jkt-jabar', '4', 'Logistik Impor CBU & Cadangan Operasional Kas Project', '1', '1000000000', '1000000000', '2026-09-28 10:34:14'),
('5', 'proj-surabaya', '1', 'Excavator Komatsu PC200-8 CBU Jepang Grade A', '2', '1650000000', '3300000000', '2026-09-28 10:34:14'),
('6', 'proj-surabaya', '2', 'Excavator Komatsu PC138US-8 Compact Tail', '1', '950000000', '950000000', '2026-09-28 10:34:14'),
('7', 'proj-surabaya', '3', 'Logistik, Sertifikasi & Cadangan Operasional Kas Project', '1', '750000000', '750000000', '2026-09-28 10:34:14'),
('8', 'proj-denpasar', '1', 'Excavator Komatsu PC200-8 CBU Jepang Grade A', '2', '1650000000', '3300000000', '2026-09-28 10:34:14'),
('9', 'proj-denpasar', '2', 'Mini Excavator Komatsu PC78US-8 (Kompak Resor)', '3', '800000000', '2400000000', '2026-09-28 10:34:14'),
('10', 'proj-denpasar', '3', 'Mobilisasi Lintas Pulau & Asuransi All Risk', '1', '650000000', '650000000', '2026-09-28 10:34:14'),
('11', 'proj-denpasar', '4', 'Cadangan Pemeliharaan & Workshop Support Regional Bali', '1', '650000000', '650000000', '2026-09-28 10:34:14'),
('12', 'proj-makassar', '1', 'Excavator Komatsu PC200-8 CBU Jepang Grade A', '2', '1650000000', '3300000000', '2026-09-28 10:34:14'),
('13', 'proj-makassar', '2', 'Excavator Komatsu PC138US-8 Compact Tail', '1', '950000000', '950000000', '2026-09-28 10:34:14'),
('14', 'proj-makassar', '3', 'Dukungan Armada Towing, Mobilisasi & Casis Khusus', '1', '450000000', '450000000', '2026-09-28 10:34:14'),
('15', 'proj-makassar', '4', 'Biaya Asuransi Alat Berat & Cadangan Operasional Kas', '1', '300000000', '300000000', '2026-09-28 10:34:14'),
('16', 'proj-kebumen', '1', 'Excavator Komatsu PC200-8 CBU Jepang Grade A', '2', '1650000000', '3300000000', '2026-09-28 10:34:14'),
('17', 'proj-kebumen', '2', 'Excavator Komatsu PC138US-8 Compact Tail', '2', '950000000', '1900000000', '2026-09-28 10:34:14'),
('18', 'proj-kebumen', '3', 'Upgrade Fasilitas Pool & Workshop Maintenance 4.500 m²', '1', '500000000', '500000000', '2026-09-28 10:34:14'),
('19', 'proj-kebumen', '4', 'Asuransi Alat Berat & Cadangan Kas Operasional', '1', '300000000', '300000000', '2026-09-28 10:34:14');

-- -----------------------------------------------------
-- Table structure for `investor_bank_accounts`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `investor_bank_accounts`;
CREATE TABLE `investor_bank_accounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `investor_id` int(11) NOT NULL,
  `bank_name` varchar(100) NOT NULL,
  `account_number` varchar(50) NOT NULL,
  `account_holder` varchar(255) NOT NULL,
  `branch` varchar(150) DEFAULT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_bank_investor` (`investor_id`),
  CONSTRAINT `fk_bank_investor` FOREIGN KEY (`investor_id`) REFERENCES `investors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `investor_bank_accounts`
INSERT INTO `investor_bank_accounts` (`id`, `investor_id`, `bank_name`, `account_number`, `account_holder`, `branch`, `is_primary`, `created_at`, `updated_at`) VALUES
('1', '1', 'Bank Central Asia (BCA)', '8820394821', 'Budi Pratama', 'KCP Sudirman Jakarta', '1', '2026-09-24 16:53:48', '2026-09-24 16:53:48'),
('2', '2', 'Bank Mandiri', '1270009847281', 'PT Nusantara Capital Group', 'KC SCBD Equity Tower', '1', '2026-09-24 16:53:48', '2026-09-24 16:53:48');

-- -----------------------------------------------------
-- Table structure for `investor_companies`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `investor_companies`;
CREATE TABLE `investor_companies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `investor_id` int(11) NOT NULL,
  `business_name` varchar(255) NOT NULL,
  `legal_entity` enum('Perseroan Terbatas (PT)','Persekutuan Komanditer (CV)','PT Perorangan','Usaha Dagang (UD)','Tidak ada') NOT NULL,
  `company_address` text NOT NULL,
  `pic_name` varchar(255) NOT NULL,
  `pic_position` varchar(150) NOT NULL,
  `company_phone` varchar(50) NOT NULL,
  `annual_turnover` enum('Di bawah Rp500 Juta','Rp500 Juta – Rp2,5 Miliar','Rp2,5 Miliar – Rp10 Miliar','Rp10 Miliar – Rp50 Miliar','Rp50 Miliar – Rp250 Miliar','Di atas Rp250 Miliar') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_investor_company` (`investor_id`),
  CONSTRAINT `fk_investor_company` FOREIGN KEY (`investor_id`) REFERENCES `investors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `investor_companies`
INSERT INTO `investor_companies` (`id`, `investor_id`, `business_name`, `legal_entity`, `company_address`, `pic_name`, `pic_position`, `company_phone`, `annual_turnover`, `created_at`, `updated_at`) VALUES
('1', '2', 'PT Nusantara Capital Group', 'Perseroan Terbatas (PT)', 'Equity Tower Lt. 28, SCBD, Jakarta Selatan', 'Hendra Wijaya, S.E., M.B.A.', 'Managing Director', '081198765432', 'Rp10 Miliar – Rp50 Miliar', '2026-09-16 14:48:46', '2026-09-16 14:48:46');

-- -----------------------------------------------------
-- Table structure for `investor_portfolios`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `investor_portfolios`;
CREATE TABLE `investor_portfolios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `investor_id` int(11) NOT NULL,
  `project_id` varchar(50) NOT NULL,
  `contract_number` varchar(100) NOT NULL,
  `amount` bigint(20) NOT NULL DEFAULT 500000000,
  `return_rate` varchar(50) NOT NULL DEFAULT '≥30% (p.a.)',
  `tenor` varchar(50) NOT NULL DEFAULT '36 Bulan',
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `next_payout_date` date DEFAULT NULL,
  `payout_received` bigint(20) NOT NULL DEFAULT 0,
  `allocated_units` varchar(255) DEFAULT NULL,
  `status` enum('active','completed','pending_allocation') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `contract_number` (`contract_number`),
  KEY `fk_portfolio_investor` (`investor_id`),
  KEY `fk_portfolio_project` (`project_id`),
  CONSTRAINT `fk_portfolio_investor` FOREIGN KEY (`investor_id`) REFERENCES `investors` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_portfolio_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `investor_portfolios`
INSERT INTO `investor_portfolios` (`id`, `investor_id`, `project_id`, `contract_number`, `amount`, `return_rate`, `tenor`, `start_date`, `end_date`, `next_payout_date`, `payout_received`, `allocated_units`, `status`, `created_at`, `updated_at`) VALUES
('1', '1', 'proj-jkt-jabar', 'MGI/INV/2024/001-BP', '5000000000', '≥32% (p.a.)', '36 Bulan', '2024-01-15', '2027-01-15', '2024-10-15', '37500000', '2x Komatsu PC138US-8 CBU Jepang', 'active', '2026-09-24 16:53:48', '2026-09-28 10:36:32'),
('2', '2', 'proj-jkt-jabar', 'MGI/CORP/2024/008-NCG', '2500000000', '≥32% (p.a.)', '36 Bulan', '2024-02-01', '2027-02-01', '2024-11-01', '187500000', '2x Komatsu PC200-8 & 1x D6R Caterpillar', 'active', '2026-09-24 16:53:48', '2026-09-24 16:53:48');

-- -----------------------------------------------------
-- Table structure for `investors`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `investors`;
CREATE TABLE `investors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_type` enum('perorangan','perusahaan') NOT NULL DEFAULT 'perorangan',
  `email` varchar(191) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `citizenship` enum('Indonesia (WNI)','Warga Negara Asing (WNA)') DEFAULT 'Indonesia (WNI)',
  `phone` varchar(50) DEFAULT NULL,
  `business_activity` varchar(255) DEFAULT NULL,
  `status` enum('active','pending_verification','suspended') NOT NULL DEFAULT 'active',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `investors`
INSERT INTO `investors` (`id`, `account_type`, `email`, `password_hash`, `full_name`, `citizenship`, `phone`, `business_activity`, `status`, `notes`, `created_at`, `updated_at`) VALUES
('1', 'perorangan', 'investor@gmail.com', '$2y$10$Lelh6pP2AnzfLxLpVwTv.eA4aPbkITwzvEzhIIlOgYcmwBSSd6Ihq', 'Budi Pratama', 'Indonesia (WNI)', '081234567890', NULL, 'active', NULL, '2026-09-16 14:48:46', '2026-09-16 14:48:46'),
('2', 'perusahaan', 'corporate@holding.com', '$2y$10$L1db4kaUCkNu5P0a4YcQx.W4d27KcehnDjVESPnvdwaXXrP5yYep6', 'Hendra Wijaya, S.E., M.B.A.', 'Indonesia (WNI)', '081198765432', NULL, 'active', NULL, '2026-09-16 14:48:46', '2026-09-16 14:48:46'),
('5', 'perorangan', 'unit_test_1789634960@test.com', '$2y$10$o6HKiHURfTsZ3gNIdPXpOeQAVtrq8HqCcLXHequD7tJX9HVOnzM82', 'Investor Unit Test', 'Indonesia (WNI)', '081298765432', NULL, 'active', NULL, '2026-09-17 15:49:20', '2026-09-17 15:49:20');

-- -----------------------------------------------------
-- Table structure for `preparation_entities`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `preparation_entities`;
CREATE TABLE `preparation_entities` (
  `code` varchar(50) NOT NULL,
  `name` varchar(255) NOT NULL,
  `slogan` varchar(255) DEFAULT NULL,
  `role` varchar(255) NOT NULL,
  `level` varchar(100) NOT NULL,
  `badge` varchar(100) NOT NULL,
  `color` varchar(50) NOT NULL DEFAULT 'gold',
  `description` text NOT NULL,
  `focus` text NOT NULL,
  `output` text NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `preparation_entities`
INSERT INTO `preparation_entities` (`code`, `name`, `slogan`, `role`, `level`, `badge`, `color`, `description`, `focus`, `output`, `location`, `sort_order`, `updated_at`) VALUES
('MGI', 'PT Montana Global Investama', '', 'Pengelola Proyek & Permodalan Grup', 'Level 1', 'Pengelola Proyek & Permodalan Grup', 'gold', 'Pengelola Proyek & Permodalan Grup', 'Pengelolaan proyek investasi, kepatuhan, relasi investor', 'Alokasi permodalan proyek, transparansi imbal hasil terukur', NULL, '1', '2026-09-28 15:00:09'),
('MIU', 'PT Montana Indo Utama (MIU)', '', 'Kantor, Workshop & Dukungan Armada', 'Level 1', 'Kantor, Workshop & Dukungan Armada', 'blue', 'Kantor, Workshop & Dukungan Armada', 'Workshop Kebumen, armada khusus Komatsu CBU Jepang, armada Montana Towing, operasional Jawa Tengah, Bali, dan NTB', '125 unit Komatsu Grade A siap pakai, kantor & pool Kebumen, armada towing siaga', NULL, '2', '2026-09-28 15:00:09'),
('MSI', 'PT Montana Sentra Industri (MSI)', '', 'Suplai Impor dari Jepang atau Cina ke Indonesia', 'Level 1', 'Suplai Impor dari Jepang atau Cina ke Indonesia', 'navy', 'Suplai Impor dari Jepang atau Cina ke Indonesia', 'Fokus pada impor dari Jepang atau Cina ke Indonesia sampai ke alamat pelanggan', 'Pengiriman door-to-door unit mesin dan alat berat impor langsung sampai lokasi', NULL, '3', '2026-09-28 15:00:09'),
('Mypurcase', 'Mypurcase (Segera Diluncurkan 2027)', '', 'Sistem dan Pengadaan Terpadu Ekosistem', 'Level 1', 'Sistem dan Pengadaan Terpadu Ekosistem', 'gold', 'Sistem dan Pengadaan Terpadu Ekosistem', 'Sistem dan pengadaan terpadu serta pembelian unit dan mesin secara efisien', 'Pengadaan terpadu hemat biaya, transparansi alokasi belanja modal', NULL, '4', '2026-09-28 15:00:09');

-- -----------------------------------------------------
-- Table structure for `preparation_workflow`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `preparation_workflow`;
CREATE TABLE `preparation_workflow` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `step_number` int(11) NOT NULL DEFAULT 1,
  `title` varchar(255) NOT NULL,
  `actor` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `badge` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `preparation_workflow`
INSERT INTO `preparation_workflow` (`id`, `step_number`, `title`, `actor`, `description`, `badge`, `created_at`) VALUES
('1', '1', '1. Penempatan Modal Investasi Proyek', 'Investor → PT Montana Global Investama (MGI)', 'Investor menempatkan dana pada investasi proyek riil pilihan dengan proyeksi imbal hasil menarik dan terukur.', '', '2026-09-28 15:00:09'),
('2', '2', '2. Alokasi Modal & Pemilihan Unit', 'MGI → Unit Usaha (MIU & MSI)', 'MGI mengalokasikan permodalan proyek untuk akuisisi unit alat berat Komatsu CBU Jepang dan suplai impor.', '', '2026-09-28 15:00:09'),
('3', '3', '3. Akuisisi Unit Alat Berat', 'MIU → Unit Alat Berat', 'MIU akuisisi unit alat berat Komatsu CBU Jepang.', '', '2026-09-28 15:00:09'),
('4', '4', '4. Suplai Impor Door-to-Door', 'MSI → Alamat Pelanggan / Pool', 'MSI mengawal impor langsung dari Jepang atau Cina hingga unit tiba sempurna di alamat pelanggan atau pool workshop.', '', '2026-09-28 15:00:09'),
('5', '5', '5. Sistem dan Pengadaan Terpadu', 'Mypurcase', 'Pembelian unit dan mesin dikoordinasikan melalui sistem terpadu Mypurcase secara efisien dan transparan.', '', '2026-09-28 15:00:09'),
('6', '6', '6. Utilisasi Proyek & Distribusi Hasil', 'MIU → MSI → Mypurcase → MGI & Investor', 'Unit Komatsu dioperasikan pada kontrak kerja riil di kota-kota strategis, menghasilkan dividen kompetitif yang ditransfer ke rekening investor.', '', '2026-09-28 15:00:09');

-- -----------------------------------------------------
-- Table structure for `project_details`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `project_details`;
CREATE TABLE `project_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` varchar(50) NOT NULL,
  `tagline` varchar(255) DEFAULT NULL,
  `what_will_provide_title` varchar(255) NOT NULL DEFAULT 'Alokasi Penggunaan Modal (Use of Funds & Asset Acquisition)',
  `what_will_provide_content` text NOT NULL,
  `sinergi_title` varchar(255) NOT NULL DEFAULT 'Struktur Kemitraan Strategis & Jaminan Penyerapan Pasar (Offtake Framework)',
  `sinergi_content` text NOT NULL,
  `summary_title` varchar(255) NOT NULL DEFAULT 'Ringkasan Kelayakan Investasi & Profil Risiko (Feasibility Summary)',
  `summary_content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_project_detail` (`project_id`),
  CONSTRAINT `fk_project_detail` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `project_details`
INSERT INTO `project_details` (`id`, `project_id`, `tagline`, `what_will_provide_title`, `what_will_provide_content`, `sinergi_title`, `sinergi_content`, `summary_title`, `summary_content`, `created_at`, `updated_at`) VALUES
('1', 'proj-jkt-jabar', 'Sentral Pool & Workshop Terpadu Distribusi Unit Komatsu Regional Jabodetabek dan Jawa Barat', 'Alokasi Penggunaan Modal Proyek', 'Permodalan dialokasikan untuk pengadaan unit alat berat Komatsu CBU Jepang Grade A (Excavator Komatsu PC200-8 & PC138US-8) serta fasilitas workshop terpadu regional Jabodetabek & Jawa Barat. Penempatan strategis untuk mendukung proyek infrastruktur jalan, kawasan industri, dan logistik nasional.', 'Kerangka Kontrak Penyerapan Pasar', 'Unit Komatsu langsung diutilisasi untuk proyek galian kuari, normalisasi infrastruktur sipil, dan pembangunan kawasan industri berjangka panjang di wilayah Jabodetabek serta Jawa Barat dengan kepastian cashflow pembayaran kontrak.', 'Ringkasan Kelayakan Proyek Investasi', 'Didukung aset fisik nyata Komatsu berlikuiditas pasar sekunder tertinggi di Indonesia, proyeksi imbal hasil terukur ≥32% (p.a.) dengan jadwal bagi hasil setiap kuartal.', '2026-09-28 10:34:14', '2026-09-28 10:34:14'),
('2', 'proj-surabaya', 'Investasi Alat Berat untuk Mendukung Ekspansi Industri, Infrastruktur & Logistik Regional Surabaya & Jawa Timur', 'Alokasi Penggunaan Modal Proyek Surabaya', 'Permodalan dialokasikan untuk penambahan unit alat berat Komatsu CBU Jepang Grade A guna memperkuat penetrasi operasional pada sentra industri manufaktur, koridor logistik pelabuhan Tanjung Perak, dan ekspansi infrastruktur regional Jawa Timur.', 'Kerangka Kontrak Penyerapan Pasar Surabaya', 'Kebutuhan alat berat di koridor industri Surabaya, Gresik, hingga Sidoarjo memiliki tingkat utilisasi tinggi dengan kontrak penyewaan jangka menengah dan panjang dari mitra kontraktor terverifikasi.', 'Ringkasan Kelayakan Proyek Investasi', 'Investasi berbasis unit fisik riil dengan likuiditas tinggi, proyeksi imbal hasil terukur ≥30% per tahun, didukung proteksi asuransi alat berat komprehensif dan monitoring sistem terpadu.', '2026-09-28 10:34:14', '2026-09-28 10:34:14'),
('3', 'proj-denpasar', 'Investasi Alat Berat untuk Mendukung Pertumbuhan Pariwisata, Properti & Infrastruktur Bali', 'Alokasi Penggunaan Modal Proyek Bali', 'Dana dialokasikan untuk penempatan armada alat berat kompak dan heavy-duty (Komatsu PC78US-8 dan PC200-8) dalam rangka konstruksi resor mewah pariwisata, penataan lansekap perhotelan, dan infrastruktur strategis di wilayah Bali.', 'Kerangka Kontrak Penyerapan Pasar Bali', 'Sektor hospitality dan real estate premium di Bali memerlukan armada alat berat dengan standar emisi dan kebisingan rendah untuk proyek pembangunan resor dan perataan lahan bertaraf internasional.', 'Ringkasan Kelayakan Proyek Investasi', 'Pendanaan proyek telah ditutup penuh (Closed) dan seluruh unit telah beroperasi aktif dengan jadwal distribusi dividen berjalan tepat waktu kepada para mitra investor.', '2026-09-28 10:34:14', '2026-09-28 10:34:14'),
('4', 'proj-makassar', 'Investasi Alat Berat untuk Mendukung Infrastruktur, Logistik & Aktivitas Sektor Sumber Daya Regional Sulawesi', 'Alokasi Penggunaan Modal Proyek Makassar', 'Pendanaan dialokasikan untuk pengadaan armada Excavator Komatsu PC200-8 dan penunjang operasional guna memenuhi tingginya permintaan galian material, infrastruktur pelabuhan New Port Makassar, serta koridor logistik industri nikel & mineral di Sulawesi.', 'Kerangka Kontrak Penyerapan Pasar Sulawesi', 'Sebagai gerbang Indonesia Timur, ekspansi infrastruktur dan hilirisasi tambang di Sulawesi memacu kenaikan permintaan rental unit alat berat dengan kepastian pembayaran kontrak kerja berdurasi multi-tahun.', 'Ringkasan Kelayakan Proyek Investasi', 'Unit riil Grade A dengan jaminan fisik BPKB/faktur kepemilikan sah, imbal hasil terukur ≥32% (p.a.) dengan pelaporan performa berkala dan transparansi pembagian dividen kuartalan.', '2026-09-28 10:34:14', '2026-09-28 10:34:14'),
('5', 'proj-kebumen', 'Investasi Alat Berat untuk Mendukung Konstruksi, Infrastruktur & Pengembangan Agribisnis Jawa Tengah Bagian Selatan', 'Alokasi Penggunaan Modal Proyek Kebumen', 'Permodalan dialokasikan untuk pengadaan armada alat berat Excavator Komatsu PC200-8 dan PC138US-8 serta optimalisasi Sentral Pool & Workshop Montana seluas 4.500 m² di Kebumen guna melayani proyek irigasi modern, bendungan, infrastruktur jalan, dan agribisnis koridor selatan Jawa Tengah.', 'Kerangka Kontrak Penyerapan Pasar Kebumen', 'Didukung langsung oleh keberadaan sentral workshop pool Montana di Kebumen, unit beroperasi melayani proyek konstruksi jalur lintas selatan (JJLS), perbaikan irigasi pertanian agribisnis, serta pekerjaan sipil regional.', 'Ringkasan Kelayakan Proyek Investasi', 'Pendanaan telah tercapai 100% (Didanai Penuh) berkat partisipasi aktif para mitra pemodal. Aset fisik telah terdistribusi pada kontrak riil dengan tingkat bagi hasil kuartalan yang terjamin.', '2026-09-28 10:34:14', '2026-09-28 10:34:14');

-- -----------------------------------------------------
-- Table structure for `project_simulation`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `project_simulation`;
CREATE TABLE `project_simulation` (
  `project_id` varchar(50) NOT NULL,
  `tenor_bulan` int(11) NOT NULL DEFAULT 36,
  `estimasi_return_persen` decimal(5,2) NOT NULL DEFAULT 30.00,
  `modal_kerja_bulanan_persen` decimal(5,2) NOT NULL DEFAULT 2.20,
  `minimum_investasi` bigint(20) NOT NULL DEFAULT 500000000,
  `maximum_investasi` bigint(20) NOT NULL DEFAULT 500000000000,
  `default_investasi` bigint(20) NOT NULL DEFAULT 500000000,
  `notes` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`project_id`),
  CONSTRAINT `fk_simulation_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `project_simulation`
INSERT INTO `project_simulation` (`project_id`, `tenor_bulan`, `estimasi_return_persen`, `modal_kerja_bulanan_persen`, `minimum_investasi`, `maximum_investasi`, `default_investasi`, `notes`, `updated_at`) VALUES
('proj-denpasar', '36', '32.00', '2.40', '500000000', '500000000000', '500000000', 'Simulasi ilustratif', '2026-09-28 10:34:14'),
('proj-jkt-jabar', '36', '32.00', '2.40', '500000000', '500000000000', '500000000', 'Simulasi ilustratif', '2026-09-28 10:34:14'),
('proj-kebumen', '36', '31.00', '2.30', '500000000', '500000000000', '500000000', 'Simulasi ilustratif', '2026-09-28 10:34:14'),
('proj-makassar', '36', '32.00', '2.40', '500000000', '500000000000', '500000000', 'Simulasi ilustratif', '2026-09-28 10:34:14'),
('proj-surabaya', '36', '30.00', '2.20', '500000000', '500000000000', '500000000', 'Simulasi ilustratif', '2026-09-28 10:34:14');

-- -----------------------------------------------------
-- Table structure for `projects`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `projects`;
CREATE TABLE `projects` (
  `id` varchar(50) NOT NULL,
  `city_id` int(11) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `category` varchar(150) NOT NULL DEFAULT 'Alat Berat & Infrastruktur',
  `image` varchar(255) NOT NULL DEFAULT 'assets/img/komatsu.jpg',
  `funding_collected` bigint(20) NOT NULL DEFAULT 0,
  `funding_target` bigint(20) NOT NULL,
  `currency` varchar(10) NOT NULL DEFAULT 'IDR',
  `status` enum('Open','Fully Funded','Closed','Coming Soon') NOT NULL DEFAULT 'Open',
  `featured` tinyint(1) NOT NULL DEFAULT 0,
  `lokasi` varchar(255) NOT NULL,
  `target_display` varchar(255) DEFAULT NULL,
  `tenor` varchar(100) NOT NULL,
  `return_rate` varchar(100) NOT NULL,
  `risk_level` varchar(100) NOT NULL,
  `min_investment` varchar(100) NOT NULL DEFAULT 'Rp 500.000.000',
  `payout` varchar(100) NOT NULL DEFAULT 'Bagi Hasil Kompetitif',
  `remaining_days` varchar(100) NOT NULL DEFAULT '18 Hari Tersisa',
  `asset_backed` varchar(255) NOT NULL DEFAULT 'Unit CBU Grade A & BPKB',
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `projects`
INSERT INTO `projects` (`id`, `city_id`, `city`, `title`, `category`, `image`, `funding_collected`, `funding_target`, `currency`, `status`, `featured`, `lokasi`, `target_display`, `tenor`, `return_rate`, `risk_level`, `min_investment`, `payout`, `remaining_days`, `asset_backed`, `sort_order`, `created_at`, `updated_at`) VALUES
('proj-denpasar', NULL, 'Denpasar', '02 — Bali Tourism & Infrastructure Growth', 'Investasi Alat Berat untuk Mendukung Pertumbuhan Pariwisata, Properti & Infrastruktur Bali', 'assets/img/project-denpasar.jpg', '7000000000', '7000000000', 'IDR', 'Closed', '0', 'Denpasar & Bali', 'Rp 7.000.000.000', '36 Bulan', '≥32% (p.a.)', 'Menengah - Terukur', 'Rp 500.000.000', 'Bagi Hasil Kuartalan Berjalan', 'Pendanaan Selesai', 'Aset Fisik Komatsu CBU Grade A', '3', '2026-09-28 10:34:14', '2026-09-28 10:34:14'),
('proj-jkt-jabar', NULL, 'Jabodetabek & Jawa Barat', 'Ekspansi Pool & Alat Berat Regional Jabodetabek dan Jawa Barat', 'Proyek Ekspansi Pool & Alat Berat Komatsu', 'assets/img/project-jabodetabek.jpg', '7500000000', '10000000000', 'IDR', 'Open', '0', 'DKI Jakarta & Jawa Barat', 'Rp 10.000.000.000', '36 Bulan', '≥32% (p.a.)', 'Menengah - Terukur', 'Rp 500.000.000', 'Bagi Hasil Kompetitif', '18 Hari Tersisa', 'Aset Fisik Komatsu CBU Grade A', '1', '2026-09-28 10:34:14', '2026-09-28 10:34:14'),
('proj-kebumen', NULL, 'Kebumen', 'Sentral Pool & Workshop Alat Berat Kebumen', 'Sentral Pool & Workshop Alat Berat Komatsu', 'assets/img/project-kebumen.jpg', '4800000000', '6000000000', 'IDR', 'Open', '1', 'Kebumen & Jawa Tengah', 'Rp 6.000.000.000', '36 Bulan', '≥31% (p.a.)', 'Menengah - Terukur', 'Rp 500.000.000', 'Bagi Hasil Kuartalan', '14 Hari Tersisa', 'Aset Fisik Komatsu CBU Grade A', '5', '2026-09-28 10:34:14', '2026-09-28 10:34:14'),
('proj-makassar', NULL, 'Makassar', '03 — Makassar Infrastructure & Resource Growth', 'Investasi Alat Berat untuk Mendukung Infrastruktur, Logistik & Aktivitas Sektor Sumber Daya', 'assets/img/project-makasar.jpg', '3200000000', '5000000000', 'IDR', 'Open', '0', 'Makassar & Sulawesi Selatan', 'Rp 5.000.000.000', '36 Bulan', '≥32% (p.a.)', 'Menengah - Terukur', 'Rp 500.000.000', 'Bagi Hasil Kuartalan', '24 Hari Tersisa', 'Aset Fisik Komatsu CBU Grade A', '4', '2026-09-28 10:34:14', '2026-09-28 10:34:14'),
('proj-surabaya', NULL, 'Surabaya', '01 — Surabaya Industrial Growth', 'Investasi Alat Berat untuk Mendukung Ekspansi Industri, Infrastruktur & Logistik', 'assets/img/project-surabaya.jpg', '0', '5000000000', 'IDR', 'Coming Soon', '0', 'Surabaya & Jawa Timur', 'Rp 5.000.000.000', '36 Bulan', '≥30% (p.a.)', 'Menengah - Terukur', 'Rp 500.000.000', 'Bagi Hasil Kuartalan', 'Segera Hadir', 'Aset Fisik Komatsu CBU Grade A', '2', '2026-09-28 10:34:14', '2026-09-28 10:34:14');

-- -----------------------------------------------------
-- Table structure for `system_settings`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `system_settings`;
CREATE TABLE `system_settings` (
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `system_settings`
INSERT INTO `system_settings` (`setting_key`, `setting_value`, `description`, `updated_at`) VALUES
('disclaimer_text', 'Target ilustratif berdasarkan proyeksi kinerja, bukan jaminan — hasil aktual mengikuti kinerja riil usaha dan dapat lebih rendah dari target. Investasi pada instrumen sektor riil mengandung risiko fluktuasi pasar dan operasional. Harap membaca seluruh dokumen penawaran dan Risk Disclosure Statement secara cermat.', 'Teks Pernyataan Keterbukaan Risiko (Risk Disclosure Statement)', '2026-09-11 15:42:48'),
('gsc_verification_token', 'tS5QjXbDMMHvS9GPmI2IBWQoPTWxC9tf2BHYZx-ouNI', NULL, '2026-09-21 15:19:03'),
('official_email', 'Montanaglobalinvestamaom@gmail.com', 'Alamat Email Resmi Korespondensi', '2026-09-11 15:42:48'),
('require_auth_for_details', '1', 'Proteksi Gated Content: 1 = Wajib Login untuk melihat RAB & Simulasi, 0 = Terbuka Publik', '2026-09-11 15:42:48'),
('site_title', 'PT Montana Global Investama', 'Nama Resmi Perusahaan', '2026-09-11 15:42:48');

-- -----------------------------------------------------
-- Table structure for `transformasi_steps`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `transformasi_steps`;
CREATE TABLE `transformasi_steps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `step_order` int(11) NOT NULL DEFAULT 1,
  `year_or_phase` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `subtitle` varchar(255) DEFAULT NULL,
  `description` text NOT NULL,
  `highlights` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`highlights`)),
  `status` varchar(50) NOT NULL DEFAULT 'completed',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `transformasi_steps`
INSERT INTO `transformasi_steps` (`id`, `step_order`, `year_or_phase`, `title`, `subtitle`, `description`, `highlights`, `status`, `created_at`, `updated_at`) VALUES
('1', '1', '2022', 'CV Montana Machine', 'Distribusi Alat Berat CBU Jepang Berkualitas Tinggi di Kebumen', 'Bermula dari satu unit alat berat di Kebumen, meletakkan fondasi teknis, kualitas, dan distribusi alat berat CBU Jepang berkualitas tinggi.', '[\"Memulai distribusi alat berat CBU Jepang berkualitas tinggi pertama di Kebumen\",\"Menjalin jaringan kerja sama penyediaan unit dan rekondisi mesin\",\"Membangun standar kualitas dan inspeksi mekanikal ketat\"]', 'completed', '2026-09-24 16:53:48', '2026-09-24 16:53:48'),
('2', '2', 'Ekspansi', 'PT Montana Indo Utama (MIU)', 'Integrated Heavy Equipment Solutions', 'Menyediakan solusi terintegrasi mulai dari pengadaan, deployment, hingga dukungan operasional alat berat untuk memenuhi kebutuhan proyek sektor riil.', '[\"Pengembangan workshop sentral & pool seluas 4.500 m² di Kebumen\",\"Peluncuran armada Montana Towing untuk penanganan operasional darurat dan mobilisasi cepat\",\"Penyediaan solusi deployment dan kontrak kerja alat berat berkesinambungan\"]', 'completed', '2026-09-24 16:53:48', '2026-09-24 16:53:48'),
('3', '3', 'Impor & Logistik', 'PT Montana Sentra Industri (MSI)', 'Import & Logistics Partner', 'Mendukung proses impor, customs clearance, manajemen logistik, dan distribusi door-to-door melalui jaringan mitra yang terintegrasi.', '[\"Solusi impor langsung mesin dan alat berat dari Jepang dan Cina\",\"Pengurusan customs clearance terpadu di pelabuhan utama\",\"Pengiriman logistik aman door-to-door langsung ke lokasi proyek\"]', 'completed', '2026-09-24 16:53:48', '2026-09-24 16:53:48'),
('4', '4', 'Struktural', 'PT Montana Global Investama Grup', 'Grup & Manajer Investasi Sektor Riil', 'Mengembangkan kapabilitas dari distribusi lokal menuju pengelolaan portofolio proyek dan investasi lintas sektor secara terstruktur dengan tata kelola TARIF.', '[\"MGI memegang peran grup pengelola modal, tata kelola, dan mitigasi risiko\",\"Penerapan kerangka tata kelola TARIF secara konsisten\",\"Membuka pintu kemitraan investasi proyek berbasis aset fisik riil bagi pemodal\"]', 'completed', '2026-09-24 16:53:48', '2026-09-24 16:53:48'),
('5', '5', 'Digitalisasi', 'Mypurcase Diluncurkan 2027', 'B2B Procurement & Purchasing Platform', 'Mendigitalisasi proses pengadaan dan rantai ketersediaan B2B untuk meningkatkan efisiensi, transparansi, dan keterlacakan transaksi antar entitas dan supplier.', '[\"Digitalisasi transaksi pengadaan suku cadang, mesin, dan unit\",\"Efisiensi serapan belanja modal dan transparansi harga supplier\",\"Sistem pelacakan pengadaan secara digital dan real-time\"]', 'completed', '2026-09-24 16:53:48', '2026-09-24 16:53:48');

SET FOREIGN_KEY_CHECKS = 1;
