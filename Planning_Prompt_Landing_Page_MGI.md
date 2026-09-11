# PLANNING PROMPT — LANDING PAGE PT MONTANA GLOBAL INVESTAMA

> Dokumen ini adalah spesifikasi teknis & konten untuk pengembangan landing page + (nantinya) dashboard admin PT Montana Global Investama (MGI). Gunakan dokumen ini sebagai prompt/brief ke developer, tim desain, atau AI coding assistant (Claude Code, dsb).

---

## 1. RINGKASAN PROJECT

**Nama Project:** Landing Page & Dashboard PT Montana Global Investama (MGI)

**Tujuan:**
Membangun landing page investasi yang menampilkan company profile, daftar produk investasi (project-based), detail tiap project investasi lengkap dengan simulasi BEP/ROI, halaman transformasi (journey), halaman preparation (struktur kerja MIU–MGI–Mypurcase), dan halaman ekosistem (struktur bagan detail).

**Prinsip arsitektur:**
- Konten halaman **tidak di-hardcode di HTML**, melainkan disimpan sebagai **data JSON**, lalu di-render menjadi HTML oleh JavaScript (client-side rendering / templating).
- Ke depan, JSON ini akan **berasal dari database MySQL** melalui backend API (bukan file statis lagi), agar admin bisa update dari **dashboard admin** tanpa menyentuh kode.
- Roadmap: landing page (fase 1) → dashboard admin CRUD (fase 2) → aplikasi penuh (fase 3, mis. portal investor, KYC digital, dsb — merujuk ke dokumen Investor Package yang sudah ada).

**Hosting & Infrastruktur target:**
- Development lokal: **XAMPP** (Apache + MySQL/MariaDB + PHP, jika backend pakai PHP) atau Node.js + MySQL.
- Containerization: **Docker** (agar environment dev = environment production, mudah dipindah).
- Production: **Hostinger** (VPS atau hosting yang mendukung Docker/Node — perlu dicek paket Hostinger yang dipakai: Shared Hosting biasa TIDAK mendukung Docker, jadi kemungkinan perlu **Hostinger VPS**).

---

## 2. STACK TEKNOLOGI YANG DISARANKAN

| Layer | Pilihan | Catatan |
|---|---|---|
| Frontend rendering | HTML + CSS + Vanilla JS (fetch JSON → render DOM) atau template engine ringan (mis. Handlebars/EJS) | Hindari framework berat dulu (React/Vue) kecuali tim sudah familiar — supaya loading landing page cepat (SEO & performa penting untuk halaman investasi publik) |
| Data source (fase 1) | File `.json` statis per halaman | Mudah untuk mulai dev tanpa backend dulu |
| Data source (fase 2+) | REST API (PHP native/Laravel, atau Node.js/Express) yang query ke MySQL, return JSON | JS frontend tetap fetch JSON — cukup ganti sumbernya dari file statis ke endpoint API |
| Database | MySQL / MariaDB (via XAMPP saat dev) | Skema di Bagian 6 |
| Admin dashboard | Panel terpisah (mis. `/admin`), form CRUD untuk tiap jenis konten | Autentikasi wajib (login admin), lihat Bagian 7 |
| Container | Docker + docker-compose (services: web/app, mysql, phpmyadmin opsional) | Agar bisa langsung deploy image yang sama ke VPS Hostinger |
| Deployment | Docker image → Hostinger VPS (via SSH/CI-CD sederhana) | Perlu domain + SSL (Let's Encrypt) |

**Struktur folder yang disarankan:**
```
project-root/
├── docker-compose.yml
├── frontend/
│   ├── public/
│   │   ├── index.html          (landing/home)
│   │   ├── about.html
│   │   ├── invest.html
│   │   ├── invest-detail.html
│   │   ├── transformasi.html
│   │   ├── preparation.html
│   │   ├── ekosistem.html
│   │   ├── contact.html        (lokasi kantor, workshop, due diligence)
│   │   ├── login.html          (portal masuk investor perorangan/perusahaan)
│   │   ├── register.html       (registrasi investor perorangan/perusahaan)
│   │   └── assets/ (css, img, fonts)
│   ├── data/                    (fase 1: JSON statis)
│   │   ├── company-profile.json
│   │   ├── projects.json
│   │   ├── transformasi.json
│   │   ├── preparation.json
│   │   └── ekosistem.json
│   └── js/
│       ├── renderer.js          (core: fetch JSON -> render HTML)
│       ├── auth.js              (client-side session, role investor, gated modal)
│       ├── components.js        (card project, navbar auth, modal, progress bar)
│       ├── diagram-renderer.js  (timeline, workflow, org tree)
│       └── bep-roi-simulator.js
├── backend/                      (fase 2)
│   ├── api/ (endpoints: /projects, /projects/:id, /company, dll)
│   ├── admin/ (dashboard admin: login, CRUD form)
│   └── config/db.js atau db.php
├── database/
│   └── schema.sql
└── docker/
    ├── Dockerfile.web
    └── Dockerfile.db (jika custom)
```

---

## 3. KONSEP RENDERING: JSON → JS → HTML

Contoh alur teknis (harus diimplementasikan konsisten di semua halaman):

1. Browser load `invest.html` (kerangka HTML kosong / template placeholder, mis. `<div id="project-list"></div>`).
2. `renderer.js` melakukan `fetch('data/projects.json')` (fase 1) atau `fetch('/api/projects')` (fase 2).
3. Data JSON di-loop, tiap item di-render menjadi HTML card menggunakan template function (mis. `createProjectCard(data)` yang return string HTML atau DOM element).
4. HTML card di-`append` ke container.
5. Untuk halaman detail, ambil `?id=` dari URL query string, fetch data spesifik project tsb, lalu render title-title (What your invest will provide, Funding Target table, About Sinergi Foundation, Summary) dan jalankan simulasi BEP/ROI.

**Kenapa pendekatan ini bagus untuk kasus MGI:**
- Admin nanti tinggal edit data di dashboard → JSON/DB berubah → tampilan otomatis update tanpa ubah kode HTML/CSS.
- Struktur konten (title, table, deskripsi) fleksibel karena disimpan sebagai array/object generik, bukan fixed HTML.

---

## 4. SPESIFIKASI HALAMAN

### 4.1 Halaman "About Us" (Company Profile)

Menampilkan company profile PT Montana Global Investama, merujuk pada dokumen **Investor Package** yang sudah ada. Konten yang perlu ditampilkan (ambil dari isi company profile MGI):

- Tagline: *"Growing Together Through Trusted Investments" / "Tumbuh Bersama Melalui Investasi Terpercaya"*
- **Tentang Kami** (deskripsi umum perusahaan)
- **Visi**
- **Misi** (list, poin 1–5)
- **Nilai-Nilai Perusahaan / Core Values** (Integritas, Profesionalisme, Transparansi, Inovasi, Kolaborasi)
- **Bidang Usaha & Layanan Strategis** (5 pilar bisnis)
- **Keunggulan Perusahaan (Value Proposition)**
- **Tata Kelola Perusahaan (GCG)** — 5 prinsip
- **Kerangka Manajemen Risiko**
- **Komitmen Kepatuhan** (APU-PPT)

**Struktur JSON contoh (`company-profile.json`):**
```json
{
  "tagline_id": "Tumbuh Bersama Melalui Investasi Terpercaya",
  "tagline_en": "Growing Together Through Trusted Investments",
  "sections": [
    {
      "type": "text",
      "title": "Tentang Kami",
      "content": "PT Montana Global Investama adalah perusahaan manajer investasi..."
    },
    {
      "type": "text",
      "title": "Visi",
      "content": "Menjadi perusahaan pengelolaan investasi terdepan dan terpercaya..."
    },
    {
      "type": "list",
      "title": "Misi",
      "items": [
        "Eksekusi Investment Excellence...",
        "Kemitraan Berorientasi Nilai...",
        "..."
      ]
    },
    {
      "type": "cards",
      "title": "Nilai-Nilai Perusahaan",
      "items": [
        { "name": "Integritas", "desc": "..." },
        { "name": "Profesionalisme", "desc": "..." }
      ]
    }
  ]
}
```
> Catatan desain: gunakan komponen generik `renderSection(section)` yang bisa handle tipe `text`, `list`, `cards`, `table` — supaya about-us dan halaman lain bisa pakai renderer yang sama.

---

### 4.2 Halaman "Invest" (List Produk Investasi / Project)

Menampilkan **card per project** investasi milik MGI. Tiap card berisi:

**Layout Card (urutan dari atas ke bawah):**
1. **Gambar** project (thumbnail/cover)
2. **Progress bar** pengadaan dana
   - Pojok kiri progress bar: **Total pengadaan** (mis. "Rp 12.500.000.000 / Rp 20.000.000.000")
   - Pojok kanan: **Status project** (badge, mis. `Open`, `Fully Funded`, `Closed`, `Coming Soon`)
3. **Garis pemisah (`<hr>`)**
4. **2 baris info, masing-masing 2 kolom (kiri-kanan):**
   - Baris 1: **Lokasi** (kiri) | **Target** (kanan)
   - Baris 2: **Tenor** (kiri) | **Return** (kanan) — atau sesuaikan grouping, minimal cover: Lokasi, Target, Tenor, Return, Risk (5 data poin, bisa disusun 2 baris x label+value, dengan salah satu baris punya 1 kolom penuh jika ganjil, misal Risk full-width di baris ke-3)
5. **Button** → menuju halaman detail project (`invest-detail.html?id=xxx`)

**Struktur JSON contoh (`projects.json`):**
```json
{
  "projects": [
    {
      "id": "proj-001",
      "title": "Nama Project / Tier Investasi",
      "image": "assets/img/project-001-cover.jpg",
      "funding": {
        "collected": 12500000000,
        "target": 20000000000,
        "currency": "IDR"
      },
      "status": "Open",
      "info": {
        "lokasi": "Kebumen, Jawa Tengah",
        "target": "Rp 20.000.000.000",
        "tenor": "3 tahun",
        "return": "≥30% (ilustratif)",
        "risk": "Menengah - Tinggi"
      },
      "detail_url": "invest-detail.html?id=proj-001"
    }
  ]
}
```

**Alur Akses & Proteksi Prospektus (Gated Access Flow):**
- Publik/investor bebas menjelajahi katalog proyek investasi tanpa wajib login terlebih dahulu.
- Tombol action *"Lihat Detail & Simulasi BEP"* pada card memicu pengecekan autentikasi:
  - **Jika sudah login**: Langsung diarahkan ke halaman detail proyek (`invest-detail.html?id=xxx`).
  - **Jika belum login**: Sistem menampilkan **Pop-up Modal Interaktif** yang menginfokan bahwa detail anggaran belanja modal (*RAB*), spesifikasi unit fisik, dan simulasi BEP/ROI hanya dapat diakses oleh investor terdaftar (kepatuhan APU-PPT & GCG). Modal menyediakan 2 tombol: *"Masuk ke Akun"* (login) dan *"Daftar Akun Baru"* (register) dengan parameter pengalihan otomatis (*callback redirect*) kembali ke proyek yang ingin dilihat.

**Komponen JS yang perlu dibuat (Disesuaikan Kebiasaan Investor & Benchmark Ethis / Kapital Boost):**
- `renderProgressBar(collected, target)` → hitung persentase, render progress bar emas solid + label progres dana dan target nominal.
- `renderStatusBadge(status)` → warna badge solid per status (Open = emerald solid, Fully Funded = navy solid, Coming Soon = amber solid).
- `renderKeyMetrics(info)` → panel metrik cepat 3 kolom yang menjadi penentu keputusan investor: **Proyeksi ROI** (teks hijau tebal), **Durasi Tenor**, dan **Tiket Investasi Minimum** (tiket minimum perorangan).
- `renderMetaRow(info)` → baris informasi sekunder (lokasi proyek, jadwal pembagian hasil kuartalan/bulanan, dan profil risiko terukur).
- `renderProjectCard(project)` → kartu proyek komprehensif berestetika Clean White Luxury, dilengkapi badge urgensi hitung mundur sisa hari (*countdown remaining days*), badge proteksi aset riil (*Asset-Backed*), dan tombol aksi terintegrasi dengan proteksi autentikasi `MGIAuth.handleProtectedDetail(project.id)`.

---

### 4.3 Halaman "Invest Detail" (Detail per Project)

Diakses via `invest-detail.html?id=proj-001`. JS akan fetch data spesifik project berdasarkan `id` dari query string.

> **Proteksi Halaman Ketat (Strict Access Guard):** Jika halaman detail diakses langsung via URL browser (atau tautan eksternal/footer) oleh pengguna yang belum login:
> 1. Sistem **tidak merender** data sensitif (RAB, unit fisik, offtake, prospektus, dan simulasi BEP).
> 2. Tampilan utama digantikan oleh **Layar Akses Terkunci (*Locked Access Screen*)** yang menjelaskan batasan regulasi APU-PPT & GCG dengan tombol login/registrasi.
> 3. Pop-up Modal Autentikasi otomatis muncul di depan layar.
> 4. **Penanganan Penutupan Modal (Dismiss Guard):** Jika pengunjung menutup modal (menekan tombol silang `X` atau tombol kembali), sistem otomatis mengarahkan kembali (*redirect*) pengunjung ke halaman portofolio publik (`invest.html`) sehingga data detail proyek tidak dapat diakses tanpa login.

**Section/Title yang wajib ada (urut):**

1. **"What Your Invest Will Provide"**
   - Tipe konten: **deskripsi/paragraf** (bukan table)
   - Menjelaskan manfaat/output dari investasi ini (mis. unit alat berat yang akan dibeli, dampak bisnis, dsb)

2. **"Funding Target"**
   - Tipe konten: **table** (bukan deskripsi)
   - Kolom: **No | Item | Quantity | Unit Price | Total**
   - Jumlah baris **fleksibel/dinamis** sesuai kebutuhan tiap project (mis. project alat berat butuh baris: Excavator PC138US, Bulldozer, dsb — sedangkan project lain bisa beda item)
   - Tambahkan baris **Total keseluruhan** di akhir table (footer/summary row)

3. **"About Sinergi Foundation"**
   - Tipe konten: **deskripsi/paragraf**
   - Menjelaskan latar belakang/tujuan pengadaan (badan/yayasan/skema kemitraan terkait project ini)

4. **"Summary"**
   - Tipe konten: **deskripsi/paragraf**
   - Ringkasan keseluruhan project (kesimpulan, mengapa layak diinvestasikan, dsb)

5. **Simulasi BEP & ROI** (di bagian bawah, setelah semua section di atas)
   - Interaktif (idealnya ada input yang bisa diubah user, mis. nominal investasi) yang menghitung otomatis:
     - **BEP (Break Even Point)** → dalam satuan waktu (bulan/tahun) berdasarkan biaya vs pendapatan/return
     - **ROI (Return on Investment)** → persentase, berdasarkan data tenor & return dari project
   - Bisa ditampilkan sebagai kalkulator kecil (input: jumlah dana investasi → output: estimasi BEP & ROI) atau chart sederhana

**Struktur JSON contoh (`projects.json`, ditambahkan per project untuk detail):**
```json
{
  "id": "proj-001",
  "detail": {
    "what_will_provide": {
      "type": "text",
      "content": "Dana investasi ini akan digunakan untuk pengadaan unit-unit alat berat CBU Jepang..."
    },
    "funding_target": {
      "type": "table",
      "columns": ["No", "Item", "Quantity", "Unit Price", "Total"],
      "rows": [
        { "no": 1, "item": "Excavator Komatsu PC138US", "quantity": 2, "unit_price": 450000000, "total": 900000000 },
        { "no": 2, "item": "Bulldozer", "quantity": 1, "unit_price": 600000000, "total": 600000000 }
      ],
      "grand_total": 20000000000
    },
    "about_sinergi_foundation": {
      "type": "text",
      "content": "Pengadaan ini dilakukan melalui kemitraan strategis dengan Sinergi Foundation..."
    },
    "summary": {
      "type": "text",
      "content": "Project ini menawarkan potensi pertumbuhan nilai yang stabil dengan risiko terukur..."
    },
    "simulation": {
      "tenor_bulan": 36,
      "estimasi_return_persen": 30,
      "modal_kerja_bulanan_persen": 2,
      "notes": "Simulasi bersifat ilustratif, bukan jaminan — mengacu pada Risk Disclosure Statement."
    }
  }
}
```

**PENTING (compliance):** Karena ini terkait produk investasi riil (merujuk ke dokumen Investor Package: Proposal Investasi, Risk Disclosure Statement), pastikan setiap tampilan simulasi BEP/ROI dan target ilustratif **selalu disertai disclaimer** seperti pada dokumen asli, misalnya:
> *"Target ilustratif berdasarkan proyeksi kinerja, bukan jaminan — hasil aktual mengikuti kinerja riil usaha dan dapat lebih rendah dari target."*

Tampilkan disclaimer ini secara jelas di setiap halaman invest & invest-detail (jangan disembunyikan di footer kecil saja) — ini konsisten dengan Risk Disclosure Statement di Investor Package.

---

### 4.4 Halaman "Transformasi"

Menampilkan **journey/timeline** berupa poin-poin (step-by-step), format seperti roadmap/perjalanan transformasi bisnis MGI (bisa historis — dari awal berdiri — atau ke depan — rencana pengembangan).

**Struktur JSON contoh (`transformasi.json`):**
```json
{
  "title": "Perjalanan Transformasi Montana Global Investama",
  "journey": [
    {
      "step": 1,
      "year_or_phase": "2022",
      "title": "Awal Berdiri & Akar Operasional",
      "description": "Montana Indo Utama (MIU) dirintis sebagai penyedia alat berat impor Jepang..."
    },
    {
      "step": 2,
      "year_or_phase": "2024",
      "title": "Ekspansi ke Pengelolaan Investasi",
      "description": "Transformasi menjadi PT Montana Global Investama untuk mengelola modal investor..."
    },
    {
      "step": 3,
      "year_or_phase": "2026+",
      "title": "Ekosistem MIU - MSI - MGI - Mypurcase",
      "description": "Integrasi ekosistem bisnis untuk mendukung pertumbuhan berkelanjutan..."
    }
  ]
}
```

**Desain UI yang disarankan:** vertical timeline dengan garis penghubung antar step, tiap step ada nomor/icon, judul, dan deskripsi singkat (bisa expand untuk detail lebih lanjut).

> **Catatan:** Isi konten detail tiap step transformasi didukung data riil di `data/transformasi.json`.

---

### 4.5 Halaman "Preparation"

Menjelaskan **struktur kerja** antara **MIU, MSI, MGI, dan Mypurcase** (entitas yang berkaitan dalam ekosistem bisnis Montana Group).

Konten yang dijelaskan:
- **PT Montana Global Investama (MGI)**: Induk Usaha (Holding), Manajer Investasi, dan Pengelola Modal Grup.
- **PT Montana Indo Utama (MIU)**: Unit Usaha Operasional — Impor & Distribusi CBU Jepang, Workshop Sentral Kebumen (4.500 m²), Armada Montana Towing, serta Utilisasi Lapangan Proyek Riil. *(CV Montana Machine telah dikonsolidasikan penuh ke dalam MIU)*.
- **Montana Sentra Industri (MSI)**: Unit Usaha Berbasis Industri / Manufaktur dan Rekayasa Teknik Permesinan.
- **Mypurcase**: Sistem / Platform Pengadaan (Procurement) Internal Ekosistem.
- Ditampilkan dalam bentuk **kartu entitas terstruktur** + **5 tahap alur siklus permodalan dan eksekusi terpadu**.

**Struktur JSON (`preparation.json`):**
```json
{
  "title": "Struktur Kerja & Sinergi Operasional Antar Entitas",
  "entities_summary": [
    { "code": "MGI", "name": "PT Montana Global Investama", "role": "Induk Usaha (Holding), Manajer Investasi" },
    { "code": "MIU", "name": "PT Montana Indo Utama (MIU)", "role": "Impor, Distribusi CBU, Workshop & Utilisasi Alat Berat" },
    { "code": "MSI", "name": "Montana Sentra Industri", "role": "Industri & Manufaktur Presisi" },
    { "code": "Mypurcase", "name": "Mypurcase", "role": "Platform Pengadaan (Procurement)" }
  ],
  "workflow": [
    { "step": 1, "actor": "Investor → MGI", "desc": "Penempatan Modal Investasi" },
    { "step": 2, "actor": "MGI → Unit Usaha (MIU / MSI)", "desc": "Alokasi Modal Sesuai Rencana" },
    { "step": 3, "actor": "Unit Usaha → Mypurcase", "desc": "Proses Pengadaan Barang & Jasa" },
    { "step": 4, "actor": "Mypurcase → Vendor/Pemasok", "desc": "Eksekusi Pembelian ke Pemasok" },
    { "step": 5, "actor": "Unit Usaha → MGI & Investor", "desc": "Pelaporan Kinerja & Distribusi Hasil" }
  ]
}
```

---

### 4.6 Halaman "Ekosistem"

Versi **lebih komprehensif** dari halaman Preparation — menampilkan **bagan struktur organisasi lengkap** dari ekosistem Montana Group (MGI, MIU, MSI, Mypurcase, serta Workshop Sentral Kebumen dan Mitra Strategis).

> **Konsolidasi Entitas:** CV Montana Machine dikonsolidasikan sepenuhnya ke dalam **PT Montana Indo Utama (MIU)** sebagai pilar operasional alat berat, workshop, armada towing, dan utilisasi lapangan.

**Rekomendasi implementasi visual:**
- Bagan hierarki level 0 (Holding), level 1 (Subsidiaries & Strategic Partners), dan level 2 (Fasilitas Teknis / Workshop Kebumen di bawah MIU).
- Render dinamis dari JSON menggunakan Bootstrap 5 Card & Grid System berestetika Clean White Luxury & Solid Gold/Royal Navy.

**Struktur JSON (`ekosistem.json`):**
```json
{
  "title": "Bagan Ekosistem Korporat Montana Group",
  "nodes": [
    { "id": "mgi-holding", "label": "PT Montana Global Investama", "parent": null, "level": 0 },
    { "id": "miu", "label": "PT Montana Indo Utama (MIU)", "parent": "mgi-holding", "level": 1 },
    { "id": "montana-sentra-industri", "label": "Montana Sentra Industri", "parent": "mgi-holding", "level": 1 },
    { "id": "mypurcase", "label": "Mypurcase", "parent": "mgi-holding", "level": 1 },
    { "id": "workshop-kebumen", "label": "Workshop Sentral Kebumen", "parent": "miu", "level": 2 }
  ]
}
```

---

### 4.7 Halaman "Contact Us" (Lokasi Kantor, Pusat Workshop & Kehadiran Operasional)

Halaman ini difokuskan untuk menampilkan kehadiran fisik riil perusahaan, memandu kunjungan calon pemodal, dan menyediakan jalur komunikasi resmi terverifikasi. **Formulir kirim pesan dan captcha telah ditiadakan** karena proses akuisisi data calon pemodal dialihkan secara aman dan terstruktur ke **Portal Registrasi Investor**.

#### 1. Kolom Kiri: Kantor Pusat Holding & Akses Peta Digital
- **Judul:** "Kantor Pusat PT Montana Global Investama"
- **Detail Domisili Resmi:**
  - **Alamat:** Jl. Sunburst CBD Jl. Kapten Soebijanto Djojohadikusumo No.8 Lot I, Lengkong Gudang, Kec. Serpong, Kota Tangerang Selatan, Banten 15321.
  - **Landmark:** Kawasan Sunburst CBD BSD City (dekat BFI Tower, TerasKota BSD, akses langsung JORR 2).
  - **Jam Operasional & Korespondensi:** Senin – Jumat: 08.00 – 17.00 WIB.
- **Saluran Resmi Korespondensi:**
  - **Email Resmi Tunggal:** `Montanaglobalinvestamaom@gmail.com`
- **Integrasi Peta Digital (Google Maps):** Tampilan peta interaktif Sunburst CBD dengan tombol *"Buka di Google Maps"*.

#### 2. Kolom Kanan: Pusat Workshop Kebumen, Panduan Kunjungan & Portal Investor
- **Pusat Workshop & Pool Alat Berat Kebumen:**
  - **Alamat:** Jl. Nasional III No.88, Pejuritan, Kec. Karanganyar, Kab. Kebumen, Jawa Tengah.
  - **Kapasitas:** Pool armada hingga 50+ unit mesin CBU Jepang, workshop overhaul & uji fungsi, serta pangkalan armada Montana Towing.
- **Panduan Kunjungan Fisik (Site Visit Due Diligence Procedure):**
  1. *Registrasi Akun Investor*: Pemohon wajib terdaftar di portal investor resmi MGI.
  2. *Reservasi Jadwal*: Konfirmasi kunjungan minimal 3 hari kerja sebelumnya kepada Relationship Manager.
  3. *Inspeksi & NDA di Lokasi*: Uji coba unit mesin, telaah dokumen kelaikan teknis, dan verifikasi fisik langsung.
- **Banner Callout Portal Investor:** Informasi bahwa konsultasi personal, telaah RAB, dan peminatan proyek kini dikelola terpadu via Portal Investor MGI, dilengkapi tombol CTA menuju `register.html` dan `login.html`.

#### 3. Elemen Penguat Kepercayaan Investor (Investor Trust & Confidence Boosters)
- **Jaminan Kerahasiaan (NDA Guarantee):** Seluruh data, portofolio, dan rencana investasi calon pemodal dilindungi oleh klausul kerahasiaan (*Non-Disclosure Agreement*) berstandar perbankan.
- **SLA Respons Cepat (< 24 Jam Kerja):** Komitmen respon personal dari tim *Senior Investment Specialist / Relationship Manager* dalam 1x24 jam kerja.
- **Fasilitas Kunjungan Fisik (Site Visit Due Diligence):** Calon investor terdaftar berhak mendapatkan jadwal inspeksi langsung ke pool & workshop permesinan CBU Jepang di Kebumen, Jawa Tengah.
- **Kepatuhan Regulasi & Anti-Pencucian Uang (APU-PPT):** Menjamin bahwa setiap instrumen sindikasi dan transaksi mematuhi hukum pasar keuangan Indonesia.

---

### 4.8 Halaman "Login Investor" (Masuk ke Portal)

Halaman autentikasi resmi (`login.html`) bagi pemodal terdaftar untuk mengakses rincian prospektus proyek dan simulasi BEP/ROI.

**Fitur & Komponen Halaman:**
1. **Pilihan Kategori Akun (Tab/Toggle):**
   - `Perorangan` (Individu)
   - `Perusahaan` (Korporasi)
2. **Field Input:**
   - Alamat Email Terdaftar
   - Kata Sandi (dengan toggle tampilkan/sembunyikan sandi)
3. **Fitur Pendukung:**
   - Checkbox *"Ingat saya di perangkat ini"*
   - Tautan *"Lupa Sandi?"* dengan modal instruksi pemulihan via korespondensi resmi
   - Tombol pengisian cepat Akun Demo (*Demo Perorangan* dan *Demo Korporasi*) untuk kemudahan evaluasi & QA
   - Tautan menuju halaman registrasi (`register.html`)
4. **Alur Callback Redirect:**
   - Menyimpan query string parameter `?redirect=...`. Jika investor sebelumnya membuka detail proyek, setelah berhasil login langsung dialihkan kembali ke proyek tersebut.

---

### 4.9 Halaman "Register Investor" (Pendaftaran Akun Baru)

Halaman registrasi resmi (`register.html`) yang memfasilitasi onboarding pemodal baru dengan form dinamis berbasis kategori investor.

**1. Pilihan Kategori Akun:**
- `Perorangan (Individu)`: Ditujukan untuk pemodal mandiri, profesional, dan private investor.
- `Perusahaan (Institusi)`: Ditujukan untuk badan usaha PT, CV, holding, dan unit kemitraan korporasi.

**2. Field Registrasi Perorangan:**
- Nama Lengkap Sesuai Identitas
- Alamat Email Aktif
- Kewarganegaraan (Pilihan: `Indonesia (WNI)`, `Warga Negara Asing (WNA)`)
- Nomor Telepon (WhatsApp)
- Kata Sandi
- Konfirmasi Kata Sandi

**3. Field Registrasi Perusahaan:**
- Nama Bisnis / Perusahaan
- Badan Hukum Usaha (Pilihan dropdown: `Perseroan Terbatas (PT)`, `Persekutuan Komanditer (CV)`, `PT Perorangan`, `Usaha Dagang (UD)`, `Tidak ada`)
  - *Dukungan Catatan Dokumen Screening Dinamis*: Ketika entitas dipilih, muncul box notifikasi dokumen yang wajib disiapkan pemohon (SK Kemenkumham untuk PT, NIB untuk CV/PT Perorangan/UD, Profil perusahaan/pitch deck, Laporan keuangan laba rugi lengkap, dan Laporan neraca lengkap).
- Alamat Perusahaan (Domisili hukum / kantor operasional)
- Nama Kontak Person (PIC)
- Jabatan Kontak Person (mis. Direktur Utama, Finance Director, Legal Manager)
- Alamat Email Perusahaan
- Nomor Telepon Perusahaan / PIC
- Omzet Tahunan Usaha (Pilihan dropdown):
  - `Di bawah Rp500 Juta`
  - `Rp500 Juta – Rp2,5 Miliar`
  - `Rp2,5 Miliar – Rp10 Miliar`
  - `Rp10 Miliar – Rp50 Miliar`
  - `Rp50 Miliar – Rp250 Miliar`
  - `Di atas Rp250 Miliar`
- Kata Sandi
- Konfirmasi Kata Sandi

**4. Validasi & Kepatuhan:**
- Validasi kecocokan kata sandi secara *real-time*.
- Checkbox persetujuan kepatuhan Good Corporate Governance (GCG), kerangka APU-PPT, dan Non-Disclosure Agreement (NDA).
- Otomatis login dan redirect kembali ke proyek yang diinginkan setelah pendaftaran berhasil.

---

## 5. KOMPONEN JS BERSAMA (REUSABLE) YANG PERLU DIBUAT

| Komponen | Fungsi | Dipakai di halaman |
|---|---|---|
| `renderer.js` (core) | fetch JSON, loop, render generic section (text/list/table/cards) | Semua halaman |
| `project-card.js` / `components.js` | render card investasi (gambar, progress bar, status, info grid, button proteksi auth) | Invest, Home |
| `progress-bar.js` | hitung % dan render bar | Invest, Detail, Home |
| `funding-table.js` | render table dinamis (kolom & baris fleksibel) | Invest Detail |
| `bep-roi-simulator.js` | kalkulasi BEP & ROI, render hasil (angka/chart) | Invest Detail |
| `timeline.js` / `diagram-renderer.js` | render journey/step transformasi | Transformasi |
| `org-chart.js` / `diagram-renderer.js` | render bagan struktur dari JSON nodes/parent & alur workflow | Preparation, Ekosistem |
| `navbar.js` / `footer.js` | render navigasi (dengan state auth investor) & footer konsisten | Semua halaman |
| `auth.js` | manajemen sesi investor (Perorangan vs Perusahaan), login, register, dan redirect handler | Semua halaman |
| `auth-modal` (di `components.js`) | pop-up modal interaktif saat user non-login mengklik detail proyek | Invest, Home, Detail |

---

## 6. SKEMA DATABASE MYSQL (Fase 2 — saat sudah pakai backend + admin dashboard)

```sql
-- Tabel company profile (bisa 1 row saja atau section-based)
CREATE TABLE company_profile_sections (
  id INT AUTO_INCREMENT PRIMARY KEY,
  section_type ENUM('text','list','cards','table') NOT NULL,
  title VARCHAR(255),
  content JSON, -- fleksibel untuk isi paragraf/list/table
  sort_order INT DEFAULT 0,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel project investasi
CREATE TABLE projects (
  id VARCHAR(50) PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  image VARCHAR(255),
  funding_collected BIGINT DEFAULT 0,
  funding_target BIGINT NOT NULL,
  status ENUM('Open','Fully Funded','Closed','Coming Soon') DEFAULT 'Open',
  lokasi VARCHAR(255),
  target VARCHAR(255),
  tenor VARCHAR(100),
  return_rate VARCHAR(100),
  risk_level VARCHAR(100),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Detail project (section-based, biar fleksibel)
CREATE TABLE project_details (
  id INT AUTO_INCREMENT PRIMARY KEY,
  project_id VARCHAR(50) NOT NULL,
  section_key ENUM('what_will_provide','about_sinergi_foundation','summary') NOT NULL,
  content TEXT,
  FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
);

-- Funding target table (item pengadaan per project)
CREATE TABLE funding_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  project_id VARCHAR(50) NOT NULL,
  no_urut INT,
  item_name VARCHAR(255),
  quantity INT,
  unit_price BIGINT,
  total BIGINT,
  FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
);

-- Simulasi BEP/ROI per project
CREATE TABLE project_simulation (
  project_id VARCHAR(50) PRIMARY KEY,
  tenor_bulan INT,
  estimasi_return_persen DECIMAL(5,2),
  performance_share_persen DECIMAL(5,2),
  notes TEXT,
  FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
);

-- Transformasi (journey)
CREATE TABLE transformasi_steps (
  id INT AUTO_INCREMENT PRIMARY KEY,
  step_order INT,
  phase_label VARCHAR(100),
  title VARCHAR(255),
  description TEXT
);

-- Preparation entities (MIU, MGI, Mypurcase)
CREATE TABLE preparation_entities (
  id VARCHAR(50) PRIMARY KEY,
  name VARCHAR(100),
  role VARCHAR(255),
  description TEXT
);

CREATE TABLE preparation_workflow (
  id INT AUTO_INCREMENT PRIMARY KEY,
  from_entity VARCHAR(50),
  to_entity VARCHAR(50),
  action VARCHAR(255)
);

-- Ekosistem (org structure, self-referencing untuk parent-child)
CREATE TABLE ekosistem_nodes (
  id VARCHAR(50) PRIMARY KEY,
  label VARCHAR(255),
  parent_id VARCHAR(50) NULL,
  level INT DEFAULT 0,
  description TEXT,
  FOREIGN KEY (parent_id) REFERENCES ekosistem_nodes(id) ON DELETE SET NULL
);

-- Admin user (untuk login dashboard)
CREATE TABLE admin_users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) UNIQUE NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('superadmin','editor') DEFAULT 'editor',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Akun Investor (Perorangan & Perusahaan)
CREATE TABLE investors (
  id INT AUTO_INCREMENT PRIMARY KEY,
  account_type ENUM('perorangan','perusahaan') NOT NULL DEFAULT 'perorangan',
  email VARCHAR(191) UNIQUE NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  full_name VARCHAR(255), -- Nama lengkap untuk perorangan
  citizenship ENUM('Indonesia (WNI)', 'Warga Negara Asing (WNA)') DEFAULT 'Indonesia (WNI)',
  phone VARCHAR(50),
  status ENUM('active','pending_verification','suspended') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Profil Tambahan Investor Korporasi / Perusahaan
CREATE TABLE investor_companies (
  id INT AUTO_INCREMENT PRIMARY KEY,
  investor_id INT NOT NULL,
  business_name VARCHAR(255) NOT NULL,
  legal_entity ENUM('Perseroan Terbatas (PT)','Persekutuan Komanditer (CV)','PT Perorangan','Usaha Dagang (UD)','Tidak ada') NOT NULL,
  company_address TEXT NOT NULL,
  pic_name VARCHAR(255) NOT NULL,
  pic_position VARCHAR(150) NOT NULL,
  company_phone VARCHAR(50) NOT NULL,
  annual_turnover ENUM(
    'Di bawah Rp500 Juta',
    'Rp500 Juta – Rp2,5 Miliar',
    'Rp2,5 Miliar – Rp10 Miliar',
    'Rp10 Miliar – Rp50 Miliar',
    'Rp50 Miliar – Rp250 Miliar',
    'Di atas Rp250 Miliar'
  ) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (investor_id) REFERENCES investors(id) ON DELETE CASCADE
);
```

---

## 7. DASHBOARD ADMIN (Fase 2 — Requirement Awal)

**Fitur wajib:**
1. **Login admin** (session/JWT-based, password di-hash — jangan plaintext).
2. **CRUD Project Investasi**: tambah/edit/hapus project, upload gambar, atur status, atur progress funding, atur funding target table (bisa tambah/hapus baris dinamis), isi 4 section deskripsi (what provide, sinergi foundation, summary), atur parameter simulasi BEP/ROI.
3. **CRUD Company Profile Section**: edit tiap section about-us (text/list/cards).
4. **CRUD Transformasi**: tambah/edit/hapus step journey.
5. **CRUD Preparation & Ekosistem**: edit entities, workflow, dan struktur node (parent-child).
6. **Preview** sebelum publish (opsional tapi disarankan) — supaya admin bisa cek tampilan sebelum konten live.
7. **Log aktivitas** sederhana (siapa mengubah apa, kapan) — untuk audit, penting karena ini konten terkait produk investasi (compliance).

**Rekomendasi teknis dashboard:**
- Bisa pakai framework admin sederhana (mis. PHP native dengan Bootstrap, atau kalau mau lebih modern: Node.js + Express + EJS, atau bahkan headless CMS ringan seperti Strapi kalau tidak mau bangun dari nol — perlu didiskusikan sesuai kapasitas tim dev).

---

## 8. DOCKER & DEPLOYMENT KE HOSTINGER

**docker-compose.yml (contoh kerangka):**
```yaml
version: '3.8'
services:
  web:
    build: ./docker
    ports:
      - "8080:80"
    volumes:
      - ./frontend:/var/www/html/frontend
      - ./backend:/var/www/html/backend
    depends_on:
      - db
  db:
    image: mysql:8.0
    environment:
      MYSQL_ROOT_PASSWORD: ${DB_ROOT_PASSWORD}
      MYSQL_DATABASE: mgi_landing
      MYSQL_USER: ${DB_USER}
      MYSQL_PASSWORD: ${DB_PASSWORD}
    volumes:
      - db_data:/var/lib/mysql
    ports:
      - "3306:3306"
  phpmyadmin:
    image: phpmyadmin
    environment:
      PMA_HOST: db
    ports:
      - "8081:80"
volumes:
  db_data:
```

**Hal yang perlu dicek sebelum deploy ke Hostinger:**
1. **Cek paket Hostinger yang dipakai** — Shared Hosting biasa **tidak support Docker**. Untuk pakai Docker, kemungkinan besar perlu **Hostinger VPS**.
2. Kalau ternyata pakai Shared Hosting (bukan VPS), rencana Docker perlu disesuaikan: backend jalan langsung sebagai PHP di server Hostinger (tanpa container), database pakai MySQL yang disediakan Hostinger (bukan container MySQL sendiri).
3. Siapkan **domain + SSL (HTTPS)** wajib, karena ini web terkait investasi/finansial (kepercayaan & keamanan data investor penting).
4. Backup database rutin (terutama data investor/KYC jika nanti masuk ke fase aplikasi).

---

## 9. HAL YANG PERLU DIKONFIRMASI / BELUM LENGKAP

Sebelum development dimulai penuh, mohon dikonfirmasi ke tim MGI:

1. **Isi konten halaman Transformasi** — poin-poin journey/milestone apa saja yang mau ditampilkan (belum ada di dokumen yang di-upload).
2. **Definisi & penjelasan MIU dan Mypurcase** — perannya apa persis, hubungan struktural dengan MGI (untuk halaman Preparation & Ekosistem).
3. **Data project investasi riil** — nama-nama project, gambar, angka funding, status, item funding target per project (untuk isi halaman Invest & Invest Detail dengan data asli, bukan placeholder).
4. **Paket Hostinger** yang akan dipakai — Shared / VPS / Cloud (menentukan apakah Docker bisa langsung dipakai di production atau perlu penyesuaian).
5. **Branding/desain visual** — warna, font, logo assets (sudah ada logo MIU & Montana Global Investama dari company profile, tema solid luxury Navy #1D3589 & Gold #C5A059 tanpa gradasi).
6. **Bahasa** — apakah landing page full Bahasa Indonesia, atau perlu dwibahasa (ID/EN), mengingat tagline company profile ada versi EN juga.

---

## 10. URUTAN PENGERJAAN YANG DISARANKAN (ROADMAP)

### Fase 1: Landing Page Statis & Investor Portal (Current — In Progress / Finalizing)
1. Setup struktur folder project + XAMPP lokal + template Bootstrap 5 Clean Corporate Luxury.
2. Buat `renderer.js` core + styling tema solid (`assets/css/style.css` tanpa gradasi).
3. Bangun halaman **About Us** (`about.html`, company profile MGI & tata kelola GCG).
4. Bangun halaman **Invest** (`invest.html`) + **Invest Detail** (`invest-detail.html`) + simulator BEP/ROI (`bep-roi-simulator.js`).
5. Integrasikan **Modul Autentikasi Investor & Gated Detail** (`auth.js`):
   - Publik bebas browsing halaman landing page & katalog portofolio.
   - Pop-up modal interaktif saat user non-login mengklik detail proyek untuk verifikasi.
   - Halaman **Login Investor** (`login.html`) untuk Perorangan & Perusahaan.
   - Halaman **Register Investor** (`register.html`) dengan form dinamis (WNI/WNA, omzet tahunan, badan hukum usaha).
6. Bangun halaman **Transformasi** (`transformasi.html`, vertical journey timeline).
7. Bangun halaman **Preparation** (`preparation.html`) & **Ekosistem** (`ekosistem.html`, bagan interaktif & alur kerja grup).
8. Restrukturisasi halaman **Contact Us** (`contact.html`): peniadaan form kirim pesan/captcha, penguatan profil kantor pusat BSD City, workshop Kebumen, panduan due diligence, dan navigasi portal registrasi.
9. Testing menyeluruh (responsive/mobile, cross-browser, pengujian form validasi, disclaimer compliance).

### Fase 2: Backend API & Dashboard Admin CRUD
1. Skema database MySQL (`database/schema.sql`) untuk tabel projects, company profile, transformasi, entities, investors, dan admin users.
2. REST API endpoint (PHP / Node.js Express) query MySQL return JSON.
3. Dashboard Admin CRUD (`/admin`): login superadmin, manajemen proyek, upload foto, kelola item anggaran biaya (RAB), input milestone transformasi, dan review akun investor terdaftar.
4. Migrasi frontend: ubah target fetch data dari file `.json` lokal ke endpoint API backend.

### Fase 3: Deployment & Portal Investor Penuh
1. Containerization Docker (`Dockerfile.web`, `docker-compose.yml`).
2. Deploy ke Hostinger VPS (domain resmi + SSL Let's Encrypt).
3. Pengembangan fitur lanjutan (KYC digital investor terverifikasi, e-signature NDA, distribusi dokumen prospektus terenkripsi, reporting imbal hasil berkala).

---

*Dokumen ini bisa langsung dipakai sebagai prompt ke developer atau AI coding assistant (mis. Claude Code) untuk mulai membangun project step-by-step sesuai roadmap di atas.*
