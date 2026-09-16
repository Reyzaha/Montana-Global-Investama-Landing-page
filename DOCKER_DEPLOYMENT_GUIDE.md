# Panduan Deployment Hostinger VPS Menggunakan Docker
PT Montana Global Investama (MGI)

Panduan praktis untuk menjalankan sistem website MGI di Hostinger VPS berbasis Docker & Docker Compose.

---

## 1. Persiapan VPS di Hostinger

1. Buka Terminal SSH ke VPS Anda:
   ```bash
   ssh root@<IP_VPS_ANDA>
   ```
2. Pastikan Docker & Docker Compose sudah terinstal di VPS:
   ```bash
   docker --version
   docker compose version
   ```
   *(Jika belum, jalankan `curl -fsSL https://get.docker.com | sh`)*

---

## 2. Unggah Source Code ke VPS

Anda dapat melakukan `git clone` repository ini atau mengunggah folder project melalui SFTP / SCP:
```bash
cd /var/www
git clone https://github.com/Reyzaha/Montana-Global-Investama-Landing-page.git mgi
cd mgi
```

---

## 3. Menjalankan Sistem dengan Docker Compose

Jalankan perintah berikut di direktori project:
```bash
docker compose up -d --build
```

Docker akan otomatis:
1. Membangun image Apache + PHP 8.2 (`mgi_web_app`).
2. Menjalankan database MySQL 8.0 (`mgi_database`).
3. Mengimpor skema awal `database/schema.sql` secara otomatis.

Periksa status container:
```bash
docker compose ps
```

---

## 4. Inisialisasi Akun Superadmin Baru (Siap MFA)

Jalankan perintah berikut di dalam container untuk membuat akun Superadmin fresh yang siap di-scan Google Authenticator:
```bash
docker compose exec mgi-web php database/clean_dummy.php
```

Catat output kredensial yang dihasilkan di layar terminal.

---

## 5. Akses & Login Administrator dengan MFA Google Authenticator

1. Buka browser: `http://<IP_VPS_ANDA>/portal-admin-mgi-gateway/login.php` atau `https://domain-anda.com/portal-admin-mgi-gateway/login.php`
2. Masukkan kredensial:
   - **Username**: `admin`
   - **Password**: `Montana@2026!Secure`
3. Klik **Lanjutkan Autentikasi**.
4. **Scan QR Code**:
   - Buka aplikasi **Google Authenticator** di smartphone (Android / iOS).
   - Tekan tanda `+` lalu pilih **Scan a QR code**.
   - Arahkan kamera ke QR Code yang tampil di layar login.
5. Masukkan **6 Digit Kode OTP** yang muncul di Google Authenticator.
6. Klik **Verifikasi & Masuk** — Anda akan diarahkan langsung ke Dashboard Admin MGI.

---

## 6. Perintah Operasional Penting

* **Melihat Log Aplikasi**:
  ```bash
  docker compose logs -f mgi-web
  ```
* **Melihat Log Database**:
  ```bash
  docker compose logs -f mgi-db
  ```
* **Menghentikan Container**:
  ```bash
  docker compose down
  ```
* **Restart Container**:
  ```bash
  docker compose restart
  ```
