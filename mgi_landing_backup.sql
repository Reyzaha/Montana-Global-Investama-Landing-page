-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: mgi_landing
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `admin_logs`
--

DROP TABLE IF EXISTS `admin_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin_logs`
--

LOCK TABLES `admin_logs` WRITE;
/*!40000 ALTER TABLE `admin_logs` DISABLE KEYS */;
INSERT INTO `admin_logs` VALUES (1,2,'login','admin_users','2','Admin successfully logged in with MFA from IP ::1','::1','2026-09-15 08:25:25'),(2,2,'upload_file','assets',NULL,'Admin uploaded file \'test_komatsu_1789637059_db2cf7e3.png\' into \'projects\'','127.0.0.1','2026-09-17 09:24:19'),(4,NULL,'mfa_failed','admin_users','2','Invalid MFA OTP attempt from IP ::1','::1','2026-09-23 07:45:32'),(5,NULL,'mfa_failed','admin_users','2','Invalid MFA OTP attempt from IP ::1','::1','2026-09-23 07:45:39'),(6,NULL,'login_failed','admin_users',NULL,'Failed login attempt for \'admin\' from IP ::1','::1','2026-09-23 07:47:51');
/*!40000 ALTER TABLE `admin_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `admin_users`
--

DROP TABLE IF EXISTS `admin_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin_users`
--

LOCK TABLES `admin_users` WRITE;
/*!40000 ALTER TABLE `admin_users` DISABLE KEYS */;
INSERT INTO `admin_users` VALUES (1,'superadmin','superadmin@montanaglobalinvestama.com','$2y$10$3IlH14aDQZr7wXmEAjcpSuDLGHliz7OFqoOnbPlqhkeBpb1RoBaBO','Super Administrator MGI','superadmin',1,NULL,0,NULL,'2026-09-24 07:58:19','2026-09-24 07:58:19'),(2,'admin','admin@montanaglobalinvestama.com','$2y$10$235JM.pMxXxTEPsdJ2hOHe4Nm1/I7U2kOMCmI52toYlkHTBDsCjxa','Administrator Operasional MGI','admin',1,NULL,0,NULL,'2026-09-24 07:58:19','2026-09-24 07:58:19');
/*!40000 ALTER TABLE `admin_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `campaign_updates`
--

DROP TABLE IF EXISTS `campaign_updates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `campaign_updates`
--

LOCK TABLES `campaign_updates` WRITE;
/*!40000 ALTER TABLE `campaign_updates` DISABLE KEYS */;
INSERT INTO `campaign_updates` VALUES (1,'proj-jkt-jabar','Kedatangan Unit Excavator Komatsu CBU Jepang di Pool Jabodetabek & Jawa Barat','Logistik & Impor CBU','2024-08-20','Alhamdulillah, unit Excavator Hydraulic Komatsu PC138US-8 Grade A asal Jepang telah tiba dengan selamat di Pusat Workshop & Pool Jabodetabek & Jawa Barat. Tim mekanik telah menyelesaikan tahap PDI (Pre-Delivery Inspection), pengujian hidrolik, pemasangan sistem GPS tracking terpadu, dan penerbitan sertifikat kelaikan fungsi operasional.','assets/img/project-jabodetabek.jpg',1,'2026-09-24 07:58:19'),(2,'proj-jkt-jabar','Mobilisasi Unit ke Proyek Strategis Daerah & Kesiapan Montana Towing','Operasional Lapangan','2024-09-02','Unit telah diberangkatkan menuju lokasi proyek kawasan industri dengan pengawalan armada Montana Towing. Seluruh unit mencatatkan utilitas kerja rata-rata 8,5 jam per hari tanpa kendala mekanis.','assets/img/project-jabodetabek.jpg',2,'2026-09-24 07:58:19'),(3,'proj-jkt-jabar','Distribusi Imbal Hasil Kuartal II Berjalan Lancar ke Rekening Investor','Laporan Keuangan & Dividen','2024-07-15','Manajemen PT Montana Global Investama telah menuntaskan penyaluran bagi hasil kuartalan periode Q2 kepada seluruh investor sindikasi perorangan dan korporasi terdaftar secara tepat waktu. Laporan keuangan proyek telah diverifikasi oleh tim kepatuhan internal.','assets/img/project-jabodetabek.jpg',3,'2026-09-24 07:58:19');
/*!40000 ALTER TABLE `campaign_updates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cities`
--

DROP TABLE IF EXISTS `cities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cities`
--

LOCK TABLES `cities` WRITE;
/*!40000 ALTER TABLE `cities` DISABLE KEYS */;
INSERT INTO `cities` VALUES (1,'Kebumen','kebumen','bi-building-gear','Sentral Pool & Workshop Alat Berat 4.500 m² Jawa Tengah',1,1,'2026-09-23 04:56:42','2026-09-23 04:56:42'),(2,'Cilacap','cilacap','bi-water','Kawasan Industri Semen & Logistik Pelabuhan Tanjung Intan',1,2,'2026-09-23 04:56:42','2026-09-23 04:56:42'),(3,'Semarang','semarang','bi-pin-map-fill','Koridor Logistik, Kuari Agregat & Infrastruktur Tol Trans Jawa',1,3,'2026-09-23 04:56:42','2026-09-23 04:56:42'),(4,'Bali','bali','bi-sun-fill','Ekspansi Unit Compact Resor, Hotel Premium & Pariwisata',1,4,'2026-09-23 04:56:42','2026-09-23 04:56:42'),(5,'NTB','ntb','bi-geo-alt-fill','Proyek Pertambangan Mineral & Infrastruktur Jalan Lingkar Lombok-Sumbawa',1,5,'2026-09-23 04:56:42','2026-09-23 04:56:42');
/*!40000 ALTER TABLE `cities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `company_profile`
--

DROP TABLE IF EXISTS `company_profile`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `company_profile` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` longtext NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=62 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `company_profile`
--

LOCK TABLES `company_profile` WRITE;
/*!40000 ALTER TABLE `company_profile` DISABLE KEYS */;
INSERT INTO `company_profile` VALUES (1,'main_profile','{\n  \"company_name\": \"PT Montana Global Investama\",\n  \"short_name\": \"MGI\",\n  \"group_name\": \"Montana Group\",\n  \"tagline_id\": \"Tumbuh Bersama Melalui Investasi Terpercaya\",\n  \"tagline_en\": \"Growing Together Through Trusted Investments\",\n  \"founded_year\": \"2022\",\n  \"headquarters\": \"Roseville Soho & Suite, Sunburst CBD Lot I.8, Jl. Kapten Soebijanto Djojohadikusumo, Lengkong Gudang, Serpong, Tangerang Selatan\",\n  \"stats\": [\n    {\n      \"label\": \"Kapitalisasi Armada\",\n      \"value\": \"Rp 100M+\",\n      \"desc\": \"Aset Fisik Produktif\"\n    },\n    {\n      \"label\": \"Armada Komatsu\",\n      \"value\": \"125+ Unit\",\n      \"desc\": \"CBU Siap Operasi\"\n    },\n    {\n      \"label\": \"Fasilitas Sentral\",\n      \"value\": \"4.500 m²\",\n      \"desc\": \"Pool & Workshop Kebumen\"\n    },\n    {\n      \"label\": \"Ekosistem Usaha\",\n      \"value\": \"3 Pilar\",\n      \"desc\": \"MIU, MSI & Mypurcase\"\n    }\n  ],\n  \"about_group\": {\n    \"title\": \"Tentang Montana Group\",\n    \"subtitle\": \"Kelompok Usaha Sektor Riil Berdaya Saing Tinggi & Berkelanjutan\",\n    \"description\": \"Montana Group adalah kelompok usaha yang dipimpin oleh PT Montana Global Investama (MGI) selaku holding dan manajer investasi project, yang menaungi tiga pilar usaha operasional teruji di lapangan. Model ini memungkinkan MGI berfokus pada tata kelola, alokasi modal per project, dan manajemen risiko tingkat grup, sementara setiap unit usaha menjalankan eksekusi operasional sesuai bidang keahliannya masing-masing.\",\n    \"positioning\": \"Dengan struktur holding ini, PT Montana Global Investama memposisikan diri sebagai wadah project investasi per project terpercaya bagi pemodal yang ingin berpartisipasi pada pertumbuhan sektor riil (khususnya alat berat, industri logistik, dan rantai pasok pengadaan) melalui satu pintu masuk project investasi yang dikelola secara profesional, transparan, dan berlandaskan kerangka tata kelola TARIF.\"\n  },\n  \"entities\": [\n    {\n      \"code\": \"MGI\",\n      \"name\": \"PT Montana Global Investama\",\n      \"slogan\": \"Tumbuh Bersama Melalui Investasi Terpercaya\",\n      \"role\": \"Manajer Project Investasi & Holding (Tata Kelola, Alokasi Modal Per Project, Manajemen Risiko)\",\n      \"level\": \"Level 0 (Holding)\",\n      \"badge\": \"Holding & Project Investment Manager\",\n      \"color\": \"gold\",\n      \"description\": \"PT Montana Global Investama berperan sebagai holding dan manajer project investasi (tata kelola, alokasi modal per project, manajemen risiko), menghadirkan instrumen project investasi per project sektor riil berbasis aset fisik bernilai tinggi dengan proyeksi pengembalian modal 2-3 tahun.\",\n      \"focus\": \"Pengelolaan portofolio project investasi per project, tata kelola TARIF, mitigasi risiko, hubungan investor\",\n      \"output\": \"Alokasi modal per project terukur, kepatuhan laporan berkala, monitoring kinerja proyek\"\n    },\n    {\n      \"code\": \"MIU\",\n      \"name\": \"PT Montana Indo Utama (MIU)\",\n      \"slogan\": \"Integrated Heavy Equipment Solutions\",\n      \"role\": \"Solusi Alat Berat Terintegrasi — Penjualan, Supply, Deployment & Operational Support\",\n      \"level\": \"Level 1 (Unit Usaha)\",\n      \"badge\": \"Alat Berat & Operational Support\",\n      \"color\": \"blue\",\n      \"description\": \"Integrated Heavy Equipment Solutions. Mendukung kebutuhan alat berat melalui supply, deployment, dan operational support untuk proyek sektor riil.\",\n      \"focus\": \"Penjualan & supply unit alat berat, deployment ke lokasi proyek, perawatan berkala, workshop sentral Kebumen, dan operational support sektor riil\",\n      \"output\": \"Ketersediaan armada unit alat berat siap kerja, keandalan operasional lapangan, servis darurat siaga, dan kepastian produktivitas proyek\",\n      \"location\": \"Jl. Nasional III No.88, Pejuritan, Kec. Karanganyar, Kab. Kebumen, Jawa Tengah\"\n    },\n    {\n      \"code\": \"MSI\",\n      \"name\": \"PT Montana Sentra Industri (MSI)\",\n      \"slogan\": \"Import & Logistics Partner\",\n      \"role\": \"Mitra Impor & Logistik — Solusi Impor Alat Berat, Customs Clearance & Distribusi Door-to-Door\",\n      \"level\": \"Level 1 (Unit Usaha)\",\n      \"badge\": \"Import & Logistics Partner\",\n      \"color\": \"navy\",\n      \"description\": \"Import & Logistics Partner. Menyediakan solusi impor alat berat, customs clearance, dan distribusi door-to-door untuk kebutuhan proyek dan industri.\",\n      \"focus\": \"Solusi pengadaan impor alat berat, pengurusan customs clearance pelabuhan terpadu, dan manajemen pengiriman logistik door-to-door ke lokasi proyek dan industri\",\n      \"output\": \"Kelancaran izin impor kepabeanan, kepastian pasokan armada internasional, efisiensi rantai distribusi, dan pengiriman aman tepat waktu\",\n      \"location\": \"Jl. Nasional III No.88, Pejuritan, Kec. Karanganyar, Kab. Kebumen, Jawa Tengah\"\n    },\n    {\n      \"code\": \"Mypurcase\",\n      \"name\": \"Mypurcase (Coming soon 2027)\",\n      \"slogan\": \"Platform B2B Procurement Digital\",\n      \"role\": \"Platform Pengadaan Digital — Penghubung Pembelian, Supplier, Pengadaan & Rantai Pasok\",\n      \"level\": \"Level 1 (Platform Pengadaan)\",\n      \"badge\": \"Platform B2B Procurement\",\n      \"color\": \"gold\",\n      \"description\": \"Platform B2B Procurement yang menghubungkan kebutuhan pembelian dengan supplier, proses pengadaan, dan rantai pasok secara digital, efisien, dan transparan.\",\n      \"focus\": \"Digitalisasi pengadaan B2B, integrasi kebutuhan pembelian dengan supplier terverifikasi, otomatisasi alur purchasing, dan transparansi rantai pasok\",\n      \"output\": \"Proses pengadaan digital cepat dan transparan, efisiensi belanja modal, pelacakan transaksi real-time, dan tata kelola akuntabel\"\n    }\n  ],\n  \"sections\": [\n    {\n      \"type\": \"text\",\n      \"id\": \"tentang-kami\",\n      \"title\": \"Tentang Kami\",\n      \"subtitle\": \"Membangun Masa Depan Melalui Sinergi Investasi Produktif\",\n      \"content\": \"PT Montana Global Investama (MGI) berperan sebagai holding & manajer investasi (tata kelola, alokasi modal, manajemen risiko), sementara eksekusi operasional dijalankan oleh tiga unit usaha teruji di lapangan: PT Montana Indo Utama, PT Montana Sentra Industri, dan Mypurcase.\",\n      \"quote\": \"Di Montana Global Investama, setiap keputusan investasi berlandaskan pada tiga prinsip utama: aset yang nyata dan terukur, tata kelola yang transparan dan akuntabel, serta kemitraan strategis yang menciptakan nilai berkelanjutan.\"\n    },\n    {\n      \"type\": \"vision_mission\",\n      \"id\": \"visi-misi\",\n      \"title\": \"Visi & Misi Perusahaan\",\n      \"subtitle\": \"Arah Strategis Menuju Keunggulan Pengelolaan Modal Terpercaya\",\n      \"vision\": \"Menjadi perusahaan pengelolaan dana investasi terpercaya di Indonesia yang menghubungkan modal strategis dengan potensi sektor riil bernilai tinggi secara berkesinambungan.\",\n      \"missions\": [\n        \"Mengalokasikan permodalan secara tepat guna pada aset fisik produktif bernilai tinggi dengan perlindungan legal dan asuransi.\",\n        \"Membangun kemitraan strategis offtake jangka menengah hingga panjang untuk menjamin kepastian penyerapan unit proyek.\",\n        \"Menerapkan prinsip tata kelola yang baik secara konsisten melalui kerangka TARIF dalam setiap keputusan dan alokasi modal.\",\n        \"Mengoptimalkan sinergi ekosistem (MGI, MIU, MSI, Mypurcase) untuk menciptakan efisiensi biaya dan perlindungan nilai terintegrasi.\",\n        \"Memberikan transparansi dan pelaporan kinerja berkala demi menjaga akuntabilitas dan kepercayaan mitra pemodal.\"\n      ]\n    },\n    {\n      \"type\": \"cards\",\n      \"id\": \"core-values\",\n      \"title\": \"Nilai-Nilai Perusahaan\",\n      \"subtitle\": \"Fondasi Moral & Profesional Dalam Setiap Pengambilan Keputusan\",\n      \"items\": [\n        {\n          \"icon\": \"shield-check\",\n          \"img\": \"assets/img/icon-tata-kelola.png\",\n          \"name\": \"Integritas\",\n          \"desc\": \"Menjunjung tinggi kejujuran, etika bisnis luhur, dan keterbukaan dalam setiap transaksi dan kemitraan.\"\n        },\n        {\n          \"icon\": \"award\",\n          \"img\": \"assets/img/icon-imbal-hasil.png\",\n          \"name\": \"Profesionalisme\",\n          \"desc\": \"Didukung tim ahli berpengalaman dengan dedikasi tinggi terhadap standar eksekusi terbaik di industri.\"\n        },\n        {\n          \"icon\": \"eye\",\n          \"img\": \"assets/img/icon-transparansi.png\",\n          \"name\": \"Transparansi\",\n          \"desc\": \"Penyajian laporan kinerja berkala, audit independen, dan visibilitas alokasi modal secara menyeluruh kepada investor.\"\n        },\n        {\n          \"icon\": \"gear-wide-connected\",\n          \"img\": \"assets/img/icon-kemitraan.png\",\n          \"name\": \"Kemitraan Strategis\",\n          \"desc\": \"Membangun ekosistem terintegrasi melalui kepastian kerja sama dan kontrak jangka menengah hingga panjang.\"\n        },\n        {\n          \"icon\": \"people\",\n          \"img\": \"assets/img/icon-alokasi-modal.png\",\n          \"name\": \"Sinergi Ekosistem\",\n          \"desc\": \"Menciptakan sinergi erat lintas entitas (MIU, MSI, Mypurcase, MGI) guna memaksimalkan penciptaan nilai kolektif.\"\n        }\n      ]\n    },\n    {\n      \"type\": \"gcg\",\n      \"id\": \"tata-kelola\",\n      \"title\": \"Tata Kelola sebagai Fondasi Kepercayaan\",\n      \"subtitle\": \"Prinsip tata kelola yang baik, transparan, akuntabel, dan terukur diterapkan secara konsisten melalui kerangka TARIF dalam setiap proses pengambilan keputusan dan alokasi modal.\",\n      \"items\": [\n        {\n          \"code\": \"T\",\n          \"name\": \"Transparency\",\n          \"desc\": \"Keterbukaan informasi material dan relevan kepada seluruh pemangku kepentingan.\",\n          \"icon\": \"eye\",\n          \"img\": \"assets/img/icon-transparansi.png\"\n        },\n        {\n          \"code\": \"A\",\n          \"name\": \"Accountability\",\n          \"desc\": \"Kejelasan fungsi, struktur, sistem, dan pertanggungjawaban pengelolaan investasi.\",\n          \"icon\": \"clipboard-check\",\n          \"img\": \"assets/img/icon-tata-kelola.png\"\n        },\n        {\n          \"code\": \"R\",\n          \"name\": \"Responsibility\",\n          \"desc\": \"Kepatuhan penuh terhadap hukum, regulasi, dan tanggung jawab sosial-lingkungan.\",\n          \"icon\": \"people\",\n          \"img\": \"assets/img/icon-kemitraan.png\"\n        },\n        {\n          \"code\": \"I\",\n          \"name\": \"Independency\",\n          \"desc\": \"Pengambilan keputusan profesional objektif bebas dari benturan kepentingan.\",\n          \"icon\": \"balance\",\n          \"img\": \"assets/img/icon-alokasi-modal.png\"\n        },\n        {\n          \"code\": \"F\",\n          \"name\": \"Fairness\",\n          \"desc\": \"Perlakuan adil dan setara bagi seluruh investor dalam pemenuhan hak-hak pemodal.\",\n          \"icon\": \"equal\",\n          \"img\": \"assets/img/icon-imbal-hasil.png\"\n        }\n      ]\n    }\n  ],\n  \"contact\": {\n    \"corporate_office\": \"Roseville Soho & Suite, Sunburst CBD Lot I.8, Jl. Kapten Soebijanto Djojohadikusumo, Lengkong Gudang, Serpong, Tangerang Selatan\",\n    \"operational_office\": \"Jl. Nasional III No.88, Pejuritan, Kec. Karanganyar, Kab. Kebumen, Jawa Tengah\",\n    \"email\": \"kontak@montanaglobalinvestama.com\",\n    \"work_hours\": \"Senin – Jumat: 08.00 – 17.00 WIB\",\n    \"google_maps_url\": \"https://maps.google.com/?q=Roseville+Soho+Sunburst+CBD+Serpong+Tangerang+Selatan\"\n  }\n}','2026-09-24 07:22:16');
/*!40000 ALTER TABLE `company_profile` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ekosistem_nodes`
--

DROP TABLE IF EXISTS `ekosistem_nodes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ekosistem_nodes`
--

LOCK TABLES `ekosistem_nodes` WRITE;
/*!40000 ALTER TABLE `ekosistem_nodes` DISABLE KEYS */;
INSERT INTO `ekosistem_nodes` VALUES ('mgi-holding','PT Montana Global Investama',NULL,NULL,NULL,0,'Holding & Pengelola Modal Project',NULL,NULL,NULL,'Induk usaha (holding), manajer investasi, dan pengelola modal project grup. Menghadirkan instrumen project investasi riil dengan prinsip agresif dan menguntungkan berimbal hasil menarik serta tata kelola perusahaan yang baik (TARIF).',1,'2026-09-24 07:58:19'),('miu','PT Montana Indo Utama (MIU)',NULL,NULL,'mgi-holding',1,'Kantor & Workshop Sentral',NULL,NULL,NULL,'Entitas operasional terpadu yang berevolusi dari CV Montana Machine. Fokus pada armada alat berat khusus merek Komatsu CBU Jepang Grade A, fasilitas kantor & workshop sentral pool 4.500 m² di Kebumen, armada Montana Towing siaga 24 jam, serta utilisasi operasional di Jawa Tengah, Bali, dan NTB.',2,'2026-09-24 07:58:19'),('montana-sentra-industri','Montana Sentra Industri (MSI)',NULL,NULL,'mgi-holding',1,'Supply Import Laut & Ekspedisi',NULL,NULL,NULL,'Fokus pada supply import unit mesin dan alat berat langsung dari Jepang atau Cina ke Indonesia melalui jalur laut hingga sampai ke alamat customer secara efisien dan aman.',3,'2026-09-24 07:58:19'),('mypurcase','Mypurcase (Coming Soon 2027)',NULL,NULL,'mgi-holding',1,'Coming Soon 2027',NULL,NULL,NULL,'System dan platform pengadaan terpadu untuk purchasing unit dan mesin secara efisien, transparan, dan terukur lintas entitas dalam ekosistem Montana Group.',4,'2026-09-24 07:58:19'),('workshop-kebumen','Kantor, Workshop & Pool Sentral Kebumen',NULL,NULL,'miu',2,'Fasilitas Kantor & Workshop',NULL,NULL,NULL,'Pangkalan workshop seluas 4.500 m² di Kebumen dengan teknisi berpengalaman, stok unit Komatsu tersedia, dan armada Montana Towing.',5,'2026-09-24 07:58:19');
/*!40000 ALTER TABLE `ekosistem_nodes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `funding_items`
--

DROP TABLE IF EXISTS `funding_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `funding_items`
--

LOCK TABLES `funding_items` WRITE;
/*!40000 ALTER TABLE `funding_items` DISABLE KEYS */;
INSERT INTO `funding_items` VALUES (22,'proj-jkt-jabar',1,'Excavator Komatsu PC200-8 CBU Jepang Grade A',3,1650000000,4950000000,'2026-09-24 07:58:19'),(23,'proj-jkt-jabar',2,'Excavator Komatsu PC138US-8 Compact Tail',3,950000000,2850000000,'2026-09-24 07:58:19'),(24,'proj-jkt-jabar',3,'Dukungan Armada Montana Towing & Workshop Overhaul',1,1200000000,1200000000,'2026-09-24 07:58:19'),(25,'proj-jkt-jabar',4,'Logistik Impor CBU & Cadangan Operasional Kas Project',1,1000000000,1000000000,'2026-09-24 07:58:19');
/*!40000 ALTER TABLE `funding_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `investor_bank_accounts`
--

DROP TABLE IF EXISTS `investor_bank_accounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `investor_bank_accounts`
--

LOCK TABLES `investor_bank_accounts` WRITE;
/*!40000 ALTER TABLE `investor_bank_accounts` DISABLE KEYS */;
INSERT INTO `investor_bank_accounts` VALUES (1,1,'Bank Central Asia (BCA)','8820394821','Budi Pratama','KCP Sudirman Jakarta',1,'2026-09-24 07:58:19','2026-09-24 07:58:19'),(2,2,'Bank Mandiri','1270009847281','PT Nusantara Capital Group','KC SCBD Equity Tower',1,'2026-09-24 07:58:19','2026-09-24 07:58:19');
/*!40000 ALTER TABLE `investor_bank_accounts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `investor_companies`
--

DROP TABLE IF EXISTS `investor_companies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `investor_companies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `investor_id` int(11) NOT NULL,
  `business_name` varchar(255) NOT NULL,
  `legal_entity` enum('Perseroan Terbatas (PT)','Persekutuan Komanditer (CV)','PT Perorangan','Usaha Dagang (UD)','Tidak ada') NOT NULL,
  `company_address` text NOT NULL,
  `pic_name` varchar(255) NOT NULL,
  `pic_position` varchar(150) NOT NULL,
  `company_phone` varchar(50) NOT NULL,
  `annual_turnover` varchar(100) NOT NULL DEFAULT 'Rp10 Miliar – Rp50 Miliar',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_investor_company` (`investor_id`),
  CONSTRAINT `fk_investor_company` FOREIGN KEY (`investor_id`) REFERENCES `investors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `investor_companies`
--

LOCK TABLES `investor_companies` WRITE;
/*!40000 ALTER TABLE `investor_companies` DISABLE KEYS */;
INSERT INTO `investor_companies` VALUES (1,2,'PT Nusantara Capital Group','Perseroan Terbatas (PT)','Equity Tower Lt. 28, SCBD, Jakarta Selatan','Hendra Wijaya, S.E., M.B.A.','Managing Director','081198765432','Rp10 Miliar – Rp50 Miliar','2026-09-16 07:48:46','2026-09-16 07:48:46');
/*!40000 ALTER TABLE `investor_companies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `investor_portfolios`
--

DROP TABLE IF EXISTS `investor_portfolios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `investor_portfolios`
--

LOCK TABLES `investor_portfolios` WRITE;
/*!40000 ALTER TABLE `investor_portfolios` DISABLE KEYS */;
INSERT INTO `investor_portfolios` VALUES (1,1,'proj-jkt-jabar','MGI/INV/2024/001-BP',500000000,'≥32% (p.a.)','36 Bulan','2024-01-15','2027-01-15','2024-10-15',37500000,'1x Komatsu PC138US (Grade A)','active','2026-09-24 07:58:19','2026-09-24 07:58:19'),(2,2,'proj-jkt-jabar','MGI/CORP/2024/008-NCG',2500000000,'≥32% (p.a.)','36 Bulan','2024-02-01','2027-02-01','2024-11-01',187500000,'2x Komatsu PC200-8 & 1x D6R Caterpillar','active','2026-09-24 07:58:19','2026-09-24 07:58:19');
/*!40000 ALTER TABLE `investor_portfolios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `investors`
--

DROP TABLE IF EXISTS `investors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `investors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_type` enum('perorangan','perusahaan') NOT NULL DEFAULT 'perorangan',
  `email` varchar(191) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `citizenship` enum('Indonesia (WNI)','Warga Negara Asing (WNA)') DEFAULT 'Indonesia (WNI)',
  `phone` varchar(50) DEFAULT NULL,
  `status` enum('active','pending_verification','suspended') NOT NULL DEFAULT 'active',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `investors`
--

LOCK TABLES `investors` WRITE;
/*!40000 ALTER TABLE `investors` DISABLE KEYS */;
INSERT INTO `investors` VALUES (1,'perorangan','investor@gmail.com','$2y$10$Lelh6pP2AnzfLxLpVwTv.eA4aPbkITwzvEzhIIlOgYcmwBSSd6Ihq','Budi Pratama','Indonesia (WNI)','081234567890','active',NULL,'2026-09-16 07:48:46','2026-09-16 07:48:46'),(2,'perusahaan','corporate@holding.com','$2y$10$L1db4kaUCkNu5P0a4YcQx.W4d27KcehnDjVESPnvdwaXXrP5yYep6','Hendra Wijaya, S.E., M.B.A.','Indonesia (WNI)','081198765432','active',NULL,'2026-09-16 07:48:46','2026-09-16 07:48:46'),(5,'perorangan','unit_test_1789634960@test.com','$2y$10$o6HKiHURfTsZ3gNIdPXpOeQAVtrq8HqCcLXHequD7tJX9HVOnzM82','Investor Unit Test','Indonesia (WNI)','081298765432','active',NULL,'2026-09-17 08:49:20','2026-09-17 08:49:20');
/*!40000 ALTER TABLE `investors` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `preparation_entities`
--

DROP TABLE IF EXISTS `preparation_entities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `preparation_entities`
--

LOCK TABLES `preparation_entities` WRITE;
/*!40000 ALTER TABLE `preparation_entities` DISABLE KEYS */;
INSERT INTO `preparation_entities` VALUES ('MGI','PT Montana Global Investama','','Pengelola Project & Permodalan Grup','Level 1','Pengelola Project & Permodalan Grup','gold','Pengelola Project & Permodalan Grup','Pengelolaan project investasi, kepatuhan, relasi investor','Alokasi permodalan project, transparansi imbal hasil terukur',NULL,1,'2026-09-24 07:58:19'),('MIU','PT Montana Indo Utama (MIU)','','Kantor, Workshop Sentral & Armada Montana Towing','Level 1','Kantor, Workshop Sentral & Armada Montana Towing','blue','Kantor, Workshop Sentral & Armada Montana Towing','Workshop Kebumen, armada khusus Komatsu CBU Jepang, armada Montana Towing, operasional Jawa Tengah, Bali, dan NTB','125 unit Komatsu Grade A siap pakai, kantor & pool Kebumen, armada towing siaga',NULL,2,'2026-09-24 07:58:19'),('MSI','Montana Sentra Industri (MSI)','','Supply Import dari Jepang atau Cina ke Indonesia','Level 1','Supply Import dari Jepang atau Cina ke Indonesia','navy','Supply Import dari Jepang atau Cina ke Indonesia','Fokus kepada import dari Jepang atau Cina ke Indonesia sampai pada alamat customer','Pengiriman door-to-door unit mesin dan alat berat impor langsung sampai lokasi',NULL,3,'2026-09-24 07:58:19'),('Mypurcase','Mypurcase (Coming soon 2027)','','System dan Pengadaan Terpadu Ekosistem','Level 1','System dan Pengadaan Terpadu Ekosistem','gold','System dan Pengadaan Terpadu Ekosistem','System dan pengadaan terpadu dan purchasing unit dan mesin secara efisien','Purchasing terpadu hemat biaya, transparansi alokasi belanja modal',NULL,4,'2026-09-24 07:58:19');
/*!40000 ALTER TABLE `preparation_entities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `preparation_workflow`
--

DROP TABLE IF EXISTS `preparation_workflow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `preparation_workflow` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `step_number` int(11) NOT NULL DEFAULT 1,
  `title` varchar(255) NOT NULL,
  `actor` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `badge` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `preparation_workflow`
--

LOCK TABLES `preparation_workflow` WRITE;
/*!40000 ALTER TABLE `preparation_workflow` DISABLE KEYS */;
INSERT INTO `preparation_workflow` VALUES (1,1,'1. Penempatan Modal Project Investasi','Investor → PT Montana Global Investama (MGI)','Investor melakukan penempatan dana pada project investasi riil pilihan dengan proyeksi imbal hasil agresif dan menguntungkan.','','2026-09-24 07:58:19'),(2,2,'2. Alokasi Modal & Pemilihan Unit','MGI → Unit Usaha (MIU & MSI)','MGI mengalokasikan permodalan project untuk akuisisi unit alat berat Komatsu CBU Jepang dan supply import material industri.','','2026-09-24 07:58:19'),(3,3,'3. System dan Pengadaan Terpadu','Unit Usaha → Mypurcase','Purchasing unit dan mesin dikoordinasikan melalui sistem terpadu Mypurcase secara efisien dan transparan.','','2026-09-24 07:58:19'),(4,4,'4. Supply Import Door-to-Door','MSI → Alamat Customer / Pool','MSI mengawal import langsung dari Jepang atau Cina hingga unit tiba sempurna di alamat customer atau pool workshop.','','2026-09-24 07:58:19'),(5,5,'5. Utilisasi Project & Distribusi Hasil','MIU → MGI & Investor','Unit Komatsu dioperasikan pada kontrak kerja riil di kota-kota strategis, menghasilkan dividen kuartalan yang ditransfer ke rekening investor.','','2026-09-24 07:58:19');
/*!40000 ALTER TABLE `preparation_workflow` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `project_details`
--

DROP TABLE IF EXISTS `project_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_details`
--

LOCK TABLES `project_details` WRITE;
/*!40000 ALTER TABLE `project_details` DISABLE KEYS */;
INSERT INTO `project_details` VALUES (7,'proj-jkt-jabar','Sentral Pool & Workshop Terpadu Distribusi Unit Komatsu Regional Jabodetabek dan Jawa Barat','Alokasi Penggunaan Modal Project','Permodalan dialokasikan untuk pengadaan unit alat berat Komatsu CBU Jepang Grade A (Excavator Komatsu PC200-8 & PC138US-8) serta fasilitas workshop terpadu regional Jabodetabek & Jawa Barat. Penempatan strategis untuk mendukung proyek infrastruktur jalan, kawasan industri, dan logistik nasional.','Kerangka Kontrak Penyerapan Pasar (Offtake Framework)','Unit Komatsu langsung diutilisasi untuk proyek galian kuari, normalisasi infrastruktur sipil, dan pembangunan kawasan industri berjangka panjang di wilayah Jabodetabek serta Jawa Barat dengan kepastian cashflow pembayaran kontrak.','Ringkasan Kelayakan Project Investasi','Didukung underlying asset Komatsu berlikuiditas pasar sekunder tertinggi di Indonesia, proyeksi imbal hasil terukur ≥32% (p.a.) dengan jadwal bagi hasil setiap kuartal.','2026-09-24 07:58:19','2026-09-24 07:58:19');
/*!40000 ALTER TABLE `project_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `project_simulation`
--

DROP TABLE IF EXISTS `project_simulation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_simulation`
--

LOCK TABLES `project_simulation` WRITE;
/*!40000 ALTER TABLE `project_simulation` DISABLE KEYS */;
INSERT INTO `project_simulation` VALUES ('proj-jkt-jabar',36,32.00,2.40,500000000,500000000000,500000000,'','2026-09-24 07:58:19');
/*!40000 ALTER TABLE `project_simulation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `projects`
--

DROP TABLE IF EXISTS `projects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
  `payout` varchar(100) NOT NULL DEFAULT 'Bagi Hasil Kuartalan',
  `remaining_days` varchar(100) NOT NULL DEFAULT '18 Hari Tersisa',
  `asset_backed` varchar(255) NOT NULL DEFAULT 'Unit CBU Grade A & BPKB',
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `projects`
--

LOCK TABLES `projects` WRITE;
/*!40000 ALTER TABLE `projects` DISABLE KEYS */;
INSERT INTO `projects` VALUES ('proj-jkt-jabar',NULL,'Jabodetabek & Jawa Barat','Expands Pool and Heavy Duty Regional Jabodetabek dan Jawa Barat','Project Ekspansi Pool & Heavy Duty Komatsu','assets/img/project-jabodetabek.jpg',7500000000,10000000000,'IDR','Open',1,'DKI Jakarta & Jawa Barat','Rp 10.000.000.000','36 Bulan','≥32% (p.a.)','','Rp 500.000.000','Bagi Hasil Kuartalan','18 Hari Tersisa','Underlying Aset Komatsu CBU Grade A',1,'2026-09-23 10:01:02','2026-09-24 07:08:07');
/*!40000 ALTER TABLE `projects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system_settings`
--

DROP TABLE IF EXISTS `system_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `system_settings` (
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_settings`
--

LOCK TABLES `system_settings` WRITE;
/*!40000 ALTER TABLE `system_settings` DISABLE KEYS */;
INSERT INTO `system_settings` VALUES ('disclaimer_text','Target ilustratif berdasarkan proyeksi kinerja, bukan jaminan — hasil aktual mengikuti kinerja riil usaha dan dapat lebih rendah dari target. Investasi pada instrumen sektor riil mengandung risiko fluktuasi pasar dan operasional. Harap membaca seluruh dokumen penawaran dan Risk Disclosure Statement secara cermat.','Teks Pernyataan Keterbukaan Risiko (Risk Disclosure Statement)','2026-09-11 08:42:48'),('gsc_verification_token','tS5QjXbDMMHvS9GPmI2IBWQoPTWxC9tf2BHYZx-ouNI',NULL,'2026-09-21 08:19:03'),('official_email','Montanaglobalinvestamaom@gmail.com','Alamat Email Resmi Korespondensi','2026-09-11 08:42:48'),('require_auth_for_details','1','Proteksi Gated Content: 1 = Wajib Login untuk melihat RAB & Simulasi, 0 = Terbuka Publik','2026-09-11 08:42:48'),('site_title','PT Montana Global Investama','Nama Resmi Perusahaan','2026-09-11 08:42:48');
/*!40000 ALTER TABLE `system_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transformasi_steps`
--

DROP TABLE IF EXISTS `transformasi_steps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transformasi_steps`
--

LOCK TABLES `transformasi_steps` WRITE;
/*!40000 ALTER TABLE `transformasi_steps` DISABLE KEYS */;
INSERT INTO `transformasi_steps` VALUES (1,1,'2022','CV Montana Machine','Distribusi Alat Berat CBU Jepang Berkualitas Tinggi di Kebumen','Bermula dari satu unit alat berat di Kebumen, meletakkan fondasi teknis, kualitas, dan distribusi alat berat CBU Jepang berkualitas tinggi.','[\"Memulai distribusi alat berat CBU Jepang berkualitas tinggi pertama di Kebumen\",\"Menjalin jaringan kerja sama penyediaan unit dan rekondisi mesin\",\"Membangun standar kualitas dan inspeksi mekanikal ketat\"]','completed','2026-09-24 07:58:19','2026-09-24 07:58:19'),(2,2,'Ekspansi','PT Montana Indo Utama (MIU)','Integrated Heavy Equipment Solutions','Menyediakan solusi terintegrasi mulai dari pengadaan, deployment, hingga dukungan operasional alat berat untuk memenuhi kebutuhan proyek sektor riil.','[\"Pengembangan workshop sentral & pool seluas 4.500 m² di Kebumen\",\"Peluncuran armada Montana Towing untuk penanganan operasional darurat dan mobilisasi cepat\",\"Penyediaan solusi deployment dan kontrak kerja alat berat berkesinambungan\"]','completed','2026-09-24 07:58:19','2026-09-24 07:58:19'),(3,3,'Impor & Logistik','PT Montana Sentra Industri (MSI)','Import & Logistics Partner','Mendukung proses impor, customs clearance, manajemen logistik, dan distribusi door-to-door melalui jaringan mitra yang terintegrasi.','[\"Solusi impor langsung mesin dan alat berat dari Jepang dan Cina\",\"Pengurusan customs clearance terpadu di pelabuhan utama\",\"Pengiriman logistik aman door-to-door langsung ke lokasi proyek\"]','completed','2026-09-24 07:58:19','2026-09-24 07:58:19'),(4,4,'Struktural','PT Montana Global Investama Holding','Holding & Manajer Investasi Sektor Riil','Mengembangkan kapabilitas dari distribusi lokal menuju pengelolaan portofolio proyek dan investasi lintas sektor secara terstruktur dengan tata kelola TARIF.','[\"MGI memegang peran holding pengelola modal, tata kelola, dan mitigasi risiko\",\"Penerapan kerangka tata kelola TARIF secara konsisten\",\"Membuka pintu kemitraan investasi proyek berbasis aset fisik riil bagi pemodal\"]','completed','2026-09-24 07:58:19','2026-09-24 07:58:19'),(5,5,'Digitalisasi','Mypurcase Diluncurkan 2027','B2B Procurement & Purchasing Platform','Mendigitalisasi proses pengadaan dan rantai pasok B2B untuk meningkatkan efisiensi, transparansi, dan keterlacakan transaksi antar entitas dan supplier.','[\"Digitalisasi transaksi pengadaan suku cadang, mesin, dan unit\",\"Efisiensi serapan belanja modal dan transparansi harga supplier\",\"Sistem pelacakan pengadaan secara digital dan real-time\"]','completed','2026-09-24 07:58:19','2026-09-24 07:58:19');
/*!40000 ALTER TABLE `transformasi_steps` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-09-24 14:59:05
