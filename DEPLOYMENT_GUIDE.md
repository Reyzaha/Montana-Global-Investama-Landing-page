# Panduan Deployment Produksi & Arsitektur Real-Time
## PT Montana Global Investama

Dokumen ini memuat panduan lengkap konfigurasi dan deployment sistem landing page & portal PT Montana Global Investama ke server produksi (VPS Ubuntu/Debian, Docker, atau Cloud Server).

---

### 1. Pilihan Strategi Real-Time & Rasionalisasi

Sistem mengadopsi arsitektur **Hybrid Enterprise Real-Time**:
- **Primer (Opsi A)**: **Server-Sent Events (SSE)** via endpoint [`api/realtime/stream.php`](api/realtime/stream.php) dan native browser API `EventSource`.
- **Fallback (Opsi C)**: **Intelligent Conditional Polling** via endpoint [`api/realtime/check.php`](api/realtime/check.php) dengan header ETag / HTTP 304 Not Modified.

#### Mengapa Bukan WebSocket Murni atau Eksternal SaaS?
1. **Zero External Dependency / 100% On-Premise**: Tidak bergantung pada pihak ketiga (Pusher, Firebase, Supabase) yang mengenakan biaya per kuota/koneksi atau berisiko vendor lock-in.
2. **Firewall & Corporate Proxy Friendly**: SSE berjalan di atas standar HTTP/1.1 dan HTTP/2 (`text/event-stream`), bebas hambatan pada jaringan korporat dan SSL load balancer tanpa perlu konfigurasi upgrade socket yang rumit.
3. **Resilient Auto-Reconnect**: Browser secara native menangani rekoneksi otomatis dengan `retry: 2000`. Jika jaringan terputus permanen, engine otomatis beralih ke interval polling ringan (25 detik) tanpa error visual bagi pengguna.

---

### 2. Kebutuhan Sistem Produksi (Prerequisites)

- **Sistem Operasi**: Ubuntu 22.04 LTS / 24.04 LTS atau Debian 12
- **Web Server**: Nginx 1.20+ atau Apache 2.4+ (dengan `mod_rewrite` & `mod_headers`)
- **PHP**: PHP 8.2 atau 8.3 (dengan ekstensi `pdo_mysql`, `curl`, `mbstring`, `openssl`, `zip`, `zlib`)
- **Database**: MySQL 8.0+ atau MariaDB 10.6+
- **SSL**: Let's Encrypt / Certbot (HTTPS Wajib untuk Web Crypto & Beacon Telemetry)

---

### 3. Langkah-Langkah Deployment Produksi

#### Langkah 1: Clone Repositori ke Server
```bash
cd /var/www
git clone https://github.com/Reyzaha/Montana-Global-Investama-Landing-page.git montana-global-investama
cd montana-global-investama
```

#### Langkah 2: Konfigurasi Environment (`.env`)
Salin file template [`.env.example`](.env.example) menjadi `.env`:
```bash
cp .env.example .env
nano .env
```
Sesuaikan parameter kredensial:
```dotenv
APP_NAME="PT Montana Global Investama"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://montanainvestama.com

# Database MySQL Produksi
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=mgi_landing
DB_USER=mgi_dbuser
DB_PASS=PasswordDbKuatAnda

# Realtime Driver
REALTIME_DRIVER=sse
REALTIME_PING_INTERVAL=15
REALTIME_POLL_INTERVAL=25

# Email SMTP / Resend API
MAIL_DRIVER=smtp
LEAD_NOTIFY_EMAIL=contact@montanainvestama.com
OFFICIAL_EMAIL=contact@montanainvestama.com
SMTP_HOST=smtp.hostinger.com
SMTP_PORT=465
SMTP_SECURE=ssl
SMTP_USER=contact@montanainvestama.com
SMTP_PASS=PasswordEmailHostingerAnda
SMTP_FROM_NAME="PT Montana Global Investama"
RESEND_API_KEY=

# Error Monitoring (Opsional)
SENTRY_DSN=
```

#### Langkah 3: Eksekusi Migrasi Database
Jalankan skrip migrasi database:
```bash
php database/seed.php
```
*Catatan: Database akan diisi skema tabel, akun Super Administrator awal (`superadmin` / `SuperAdminMGI2026!`), dan pengaturan default tanpa data dummy investor palsu.*

#### Langkah 4: Set Izin Akses Folder (Permissions)
Pastikan direktori cache, upload, log, dan backup dapat ditulisi oleh web server:
```bash
chown -R www-data:www-data /var/www/montana-global-investama
chmod -R 755 /var/www/montana-global-investama
chmod -R 775 /var/www/montana-global-investama/backend/cache
chmod -R 775 /var/www/montana-global-investama/uploads
chmod -R 775 /var/www/montana-global-investama/backups
```

#### Langkah 5: Konfigurasi Nginx Web Server
Gunakan template konfigurasi yang disediakan di [`deploy/nginx.conf`](deploy/nginx.conf):
```bash
sudo cp deploy/nginx.conf /etc/nginx/sites-available/montanainvestama.com
sudo ln -s /etc/nginx/sites-available/montanainvestama.com /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

#### Langkah 6: Pasang Sertifikat SSL Gratis (Certbot)
```bash
sudo certbot --nginx -d montanainvestama.com -d www.montanainvestama.com
```

#### Langkah 7: Pasang Cron Job Backup Otomatis
Buka crontab server:
```bash
crontab -e
```
Tambahkan baris berikut untuk menjalankan backup database & media setiap hari pukul 02:00 dini hari (dengan kebijakan retensi 14 hari):
```cron
0 2 * * * /usr/bin/php /var/www/montana-global-investama/scripts/backup.php >> /var/log/mgi_backup.log 2>&1
```

---

### 4. Health-Check Endpoint & Monitoring

Endpoint kesehatan sistem dapat diakses secara publik / oleh monitoring agent di:
```http
GET https://montanainvestama.com/api/health.php
```

#### Format Respons JSON (HTTP 200 OK):
```json
{
  "status": "healthy",
  "timestamp": "2026-10-15T09:45:00+07:00",
  "uptime_check": "PT Montana Global Investama Production Portal",
  "latency_ms": 12.4,
  "checks": {
    "database": {
      "status": "healthy",
      "port": 3306,
      "latency_ms": 1.8,
      "core_tables_found": 4
    },
    "storage": {
      "status": "healthy",
      "cache_writable": true,
      "uploads_writable": true
    },
    "realtime": {
      "status": "healthy",
      "driver": "sse",
      "last_event_id": 42,
      "last_sync": "2026-10-15T09:45:00+07:00"
    },
    "system": {
      "php_version": "8.2.12",
      "environment": "production",
      "memory_usage": "2.05 MB"
    }
  }
}
```

Endpoint ini siap diintegrasikan langsung dengan **UptimeRobot**, **BetterStack**, **Datadog**, atau **Kubernetes Liveness Probe**.
