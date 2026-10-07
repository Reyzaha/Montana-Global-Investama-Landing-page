<?php
$pageTitle = 'Pengaturan Sistem';
require_once __DIR__ . '/includes/header.php';
?>

<div class="mb-4">
  <h5 class="fw-bold text-dark mb-1">Konfigurasi Sistem &amp; Kepatuhan</h5>
  <p class="text-muted small mb-0">Atur kebijakan akses dokumen penawaran (Gated Content), disclaimer kepatuhan, dan identitas resmi.</p>
</div>

<div class="row g-4">
  <div class="col-12 col-lg-8">
    <div class="admin-card">
      <div class="admin-card-header bg-white">
        <div class="d-flex align-items-center gap-2">
          <i class="bi bi-shield-lock text-primary" style="color: #142563 !important;"></i>
          <span class="fw-bold text-dark">Kebijakan Akses Dokumen (Gated Content)</span>
        </div>
      </div>
      <div class="admin-card-body">
        <div class="p-3 bg-light rounded-3 border mb-4">
          <div class="form-check form-switch d-flex align-items-center justify-content-between ps-0">
            <div>
              <label class="form-check-label fw-bold text-dark d-block mb-1" for="toggleGatedContent">
                Proteksi Akses Detail Proyek (Wajib Login)
              </label>
              <small class="text-muted d-block" style="max-width: 520px;">
                Jika diaktifkan, rincian belanja modal (RAB), spesifikasi unit fisik, dan kalkulator simulasi BEP/ROI hanya dapat diakses oleh investor yang telah login. Pengunjung umum akan diarahkan ke modal autentikasi sesuai kepatuhan regulasi dan tata kelola perusahaan yang baik.
              </small>
            </div>
            <input class="form-check-input ms-3 fs-3" type="checkbox" role="switch" id="toggleGatedContent" onchange="saveGatedContentToggle()">
          </div>
        </div>

        <form id="settingsForm" onsubmit="saveGeneralSettings(event)">
          <div class="mb-3">
            <label class="form-label">Teks Pernyataan Keterbukaan Risiko (Risk Disclosure Statement)</label>
            <textarea id="settingDisclaimer" class="form-control" rows="3" required></textarea>
            <small class="text-muted">Ditampilkan di seluruh footer katalog proyek, modal simulasi, dan dokumen penawaran.</small>
          </div>

          <div class="row g-3 mb-4">
            <div class="col-md-6">
              <label class="form-label">Nama Resmi Perusahaan</label>
              <input type="text" id="settingSiteTitle" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Email Resmi Korespondensi</label>
              <input type="email" id="settingEmail" class="form-control" required>
            </div>
            <div class="col-12">
              <label class="form-label">Email Penerima Notifikasi Lead &amp; Minat Investor</label>
              <input type="email" id="settingNotifyEmail" class="form-control" placeholder="contact@montanainvestama.com" required>
              <small class="text-muted">Setiap lead konsultasi baru dan pengajuan minat proyek dari Portal Investor otomatis dikirimkan ke alamat email ini.</small>
            </div>
          </div>

          <hr class="my-4">

          <h6 class="fw-bold text-dark mb-3 d-flex align-items-center justify-content-between">
            <span class="d-flex align-items-center gap-2">
              <i class="bi bi-envelope-at-fill text-primary" style="color: #142563 !important;"></i>
              <span>Konfigurasi Email Hostinger (SMTP)</span>
            </span>
            <span class="badge bg-light text-dark border small fw-normal">Rekomendasi Hostinger SSL</span>
          </h6>
          <p class="text-muted small mb-3">
            Gunakan kredensial email corporate Hostinger Anda agar notifikasi terkirim dengan reputasi pengiriman tinggi (anti-spam).
          </p>

          <div class="row g-3 mb-3 p-3 bg-light rounded-3 border">
            <div class="col-md-6">
              <label class="form-label fw-semibold small">Host Server SMTP</label>
              <input type="text" id="settingSmtpHost" class="form-control font-monospace" placeholder="smtp.hostinger.com" value="smtp.hostinger.com">
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold small">Port SMTP</label>
              <input type="number" id="settingSmtpPort" class="form-control font-monospace" placeholder="465" value="465">
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold small">Protokol Keamanan</label>
              <select id="settingSmtpSecure" class="form-select">
                <option value="ssl" selected>SSL (Port 465)</option>
                <option value="tls">TLS (Port 587)</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold small">Username / Akun Email</label>
              <input type="email" id="settingSmtpUser" class="form-control" placeholder="contact@montanainvestama.com" value="contact@montanainvestama.com">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold small">Password Akun Email Hostinger</label>
              <div class="input-group">
                <input type="password" id="settingSmtpPass" class="form-control font-monospace" placeholder="Masukkan password email Anda">
                <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordInput('settingSmtpPass', this)">
                  <i class="bi bi-eye"></i>
                </button>
              </div>
            </div>
            <div class="col-12 mt-2 pt-2 border-top d-flex flex-wrap align-items-center justify-content-between gap-2">
              <small class="text-muted">
                <i class="bi bi-info-circle text-primary me-1"></i> Jika password dikosongkan, sistem mencoba pengiriman via MTA server lokal.
              </small>
              <button type="button" class="btn btn-sm btn-outline-primary d-flex align-items-center gap-1" id="btnTestEmail" onclick="sendTestEmail()">
                <i class="bi bi-send-fill"></i>
                <span>Kirim Uji Coba Email</span>
              </button>
            </div>
          </div>

          <hr class="my-4">

          <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
            <i class="bi bi-google text-danger"></i>
            <span>Integrasi SEO &amp; Google Search Console</span>
          </h6>

          <div class="mb-3">
            <label class="form-label fw-semibold small">Kode Verifikasi Google Search Console (Meta Tag Token)</label>
            <div class="input-group">
              <span class="input-group-text bg-light text-muted small">&lt;meta name="google-site-verification" content="</span>
              <input type="text" id="settingGscToken" class="form-control font-monospace" placeholder="contoh: abcd1234efgh5678...">
              <span class="input-group-text bg-light text-muted small">"&gt;</span>
            </div>
            <small class="text-muted">Salin token dari Google Search Console metode <em>Tag HTML</em>. Token ini otomatis disinkronkan ke halaman utama.</small>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold small">URL Peta Situs (Sitemap XML)</label>
            <div class="input-group">
              <input type="text" class="form-control bg-light" value="https://montanainvestama.com/sitemap.xml" readonly>
              <a href="../sitemap.xml" target="_blank" class="btn btn-outline-secondary">
                <i class="bi bi-box-arrow-up-right me-1"></i> Buka XML
              </a>
            </div>
            <small class="text-muted">Submit URL ini pada menu <strong>Peta Situs (Sitemaps)</strong> Google Search Console Anda.</small>
          </div>

          <div class="mb-4">
            <label class="form-label fw-semibold small">Kata Kunci Utama Website (SEO Meta Keywords)</label>
            <textarea id="settingKeywords" class="form-control small" rows="2" placeholder="investasi per project, project based investment, investasi sektor riil, investasi alat berat..."></textarea>
            <small class="text-muted">Pisahkan setiap kata kunci dengan tanda koma (,).</small>
          </div>

          <div class="text-end">
            <button type="submit" id="btnSaveSettings" class="btn btn-mgi-primary px-4">
              <i class="bi bi-check2-circle me-1"></i> Simpan Pengaturan
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="col-12 col-lg-4">
    <!-- Panduan Google Search Console -->
    <div class="admin-card mb-4 border border-warning-subtle shadow-sm">
      <div class="admin-card-header bg-white border-bottom border-warning-subtle">
        <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
          <i class="bi bi-lightbulb-fill text-warning"></i>
          <span>Panduan Google Search Console</span>
        </h6>
      </div>
      <div class="admin-card-body p-3 small text-secondary">
        <ol class="ps-3 mb-0 lh-base">
          <li class="mb-2">Buka <a href="https://search.google.com/search-console" target="_blank" class="fw-bold text-royal text-decoration-none">Google Search Console <i class="bi bi-box-arrow-up-right"></i></a> dan masuk dengan akun Google Anda.</li>
          <li class="mb-2">Klik <strong>Tambahkan Properti</strong>, pilih tipe <strong>Awalan URL</strong>, lalu masukkan <code>https://montanainvestama.com</code>.</li>
          <li class="mb-2">Pilih metode verifikasi <strong>Tag HTML</strong>. Salin string kode pada bagian <code>content="..."</code>, lalu tempelkan ke kolom form di samping dan klik <strong>Simpan Pengaturan</strong>.</li>
          <li class="mb-2">Kembali ke tab Google Search Console lalu klik tombol <strong>Verifikasi</strong>.</li>
          <li>Masuk ke menu <strong>Peta Situs (Sitemaps)</strong> di panel kiri GSC, ketik <code>sitemap.xml</code> lalu klik <strong>Kirim</strong>.</li>
        </ol>
      </div>
    </div>
    <div class="admin-card">
      <div class="admin-card-header bg-white">
        <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
          <i class="bi bi-info-circle text-warning" style="color: #C5A059 !important;"></i>
          <span>Informasi Lingkungan Server</span>
        </h6>
      </div>
      <div class="admin-card-body p-0">
        <ul class="list-group list-group-flush small">
          <li class="list-group-item d-flex justify-content-between align-items-center py-3">
            <span class="text-muted">Versi PHP</span>
            <strong><?= phpversion() ?></strong>
          </li>
          <li class="list-group-item d-flex justify-content-between align-items-center py-3">
            <span class="text-muted">Engine Database</span>
            <strong>MySQL (Port <?= Database::getConnectedPort() ?: 3307 ?>)</strong>
          </li>
          <li class="list-group-item d-flex justify-content-between align-items-center py-3">
            <span class="text-muted">Dukungan Nilai Finansial</span>
            <span class="badge bg-success text-white">BIGINT (s/d Rp 500M)</span>
          </li>
          <li class="list-group-item d-flex justify-content-between align-items-center py-3">
            <span class="text-muted">Mode Frontend</span>
            <span class="badge bg-primary text-white" style="background: #142563 !important;">API-First + Static Fallback</span>
          </li>
        </ul>
      </div>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', loadSettings);

  async function loadSettings() {
    try {
      const res = await fetch('../api/admin/settings.php');
      const json = await res.json();
      if (!json.success) {
        if (json.status === 401) window.location.href = 'login.php';
        return;
      }

      const s = json.data || {};
      document.getElementById('toggleGatedContent').checked = (s.require_auth_for_details && s.require_auth_for_details.value === '1');
      document.getElementById('settingDisclaimer').value = s.disclaimer_text ? s.disclaimer_text.value : '';
      document.getElementById('settingSiteTitle').value = s.site_title ? s.site_title.value : 'PT Montana Global Investama';
      document.getElementById('settingEmail').value = s.official_email ? s.official_email.value : 'contact@montanainvestama.com';
      document.getElementById('settingNotifyEmail').value = s.lead_notify_email ? s.lead_notify_email.value : (s.official_email ? s.official_email.value : 'contact@montanainvestama.com');
      document.getElementById('settingGscToken').value = s.gsc_verification_token ? s.gsc_verification_token.value : '';
      document.getElementById('settingKeywords').value = s.meta_keywords ? s.meta_keywords.value : 'investasi per project, website investment per project, project based investment, investasi sektor riil, investasi alat berat, aset fisik produktif, montana global investama, manajer investasi proyek';

      if (s.smtp_host && s.smtp_host.value) document.getElementById('settingSmtpHost').value = s.smtp_host.value;
      if (s.smtp_port && s.smtp_port.value) document.getElementById('settingSmtpPort').value = s.smtp_port.value;
      if (s.smtp_secure && s.smtp_secure.value) document.getElementById('settingSmtpSecure').value = s.smtp_secure.value;
      if (s.smtp_user && s.smtp_user.value) document.getElementById('settingSmtpUser').value = s.smtp_user.value;
      if (s.smtp_pass && s.smtp_pass.value) document.getElementById('settingSmtpPass').value = s.smtp_pass.value;
    } catch (e) {
      console.error(e);
      AdminApp.showToast('Gagal memuat pengaturan sistem.', 'danger');
    }
  }

  function togglePasswordInput(id, btn) {
    const input = document.getElementById(id);
    if (!input) return;
    const isPass = input.type === 'password';
    input.type = isPass ? 'text' : 'password';
    const icon = btn.querySelector('i');
    if (icon) {
      icon.className = isPass ? 'bi bi-eye-slash' : 'bi bi-eye';
    }
  }

  async function sendTestEmail() {
    const btn = document.getElementById('btnTestEmail');
    const targetEmail = document.getElementById('settingNotifyEmail').value.trim() || 'contact@montanainvestama.com';
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Mengirim...';

    try {
      const payload = {
        target_email: targetEmail,
        smtp_host: document.getElementById('settingSmtpHost').value.trim(),
        smtp_port: document.getElementById('settingSmtpPort').value.trim(),
        smtp_secure: document.getElementById('settingSmtpSecure').value,
        smtp_user: document.getElementById('settingSmtpUser').value.trim(),
        smtp_pass: document.getElementById('settingSmtpPass').value
      };

      const res = await fetch('../api/admin/settings.php?action=test_email', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      const json = await res.json();
      if (json && json.success) {
        AdminApp.showToast(`Berhasil! ${json.message}`, 'success');
      } else {
        AdminApp.showToast((json && json.message) ? json.message : 'Uji coba pengiriman email gagal.', 'danger');
      }
    } catch (e) {
      console.error(e);
      AdminApp.showToast('Gangguan koneksi saat mengirim email uji coba.', 'danger');
    } finally {
      btn.disabled = false;
      btn.innerHTML = originalText;
    }
  }

  async function saveGatedContentToggle() {
    const isChecked = document.getElementById('toggleGatedContent').checked;
    try {
      const res = await fetch('../api/admin/settings.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          require_auth_for_details: isChecked ? '1' : '0'
        })
      });
      const json = await res.json();
      if (json.success) {
        AdminApp.showToast(`Mode Gated Content diubah: ${isChecked ? 'Wajib Login' : 'Terbuka untuk Publik'}.`, 'success');
      } else {
        AdminApp.showToast(json.message, 'danger');
      }
    } catch (e) {
      AdminApp.showToast('Gagal memperbarui toggle gated content.', 'danger');
    }
  }

  async function saveGeneralSettings(e) {
    e.preventDefault();
    const payload = {
      disclaimer_text: document.getElementById('settingDisclaimer').value.trim(),
      site_title: document.getElementById('settingSiteTitle').value.trim(),
      official_email: document.getElementById('settingEmail').value.trim(),
      lead_notify_email: document.getElementById('settingNotifyEmail').value.trim(),
      gsc_verification_token: document.getElementById('settingGscToken').value.trim(),
      meta_keywords: document.getElementById('settingKeywords').value.trim(),
      smtp_host: document.getElementById('settingSmtpHost').value.trim(),
      smtp_port: document.getElementById('settingSmtpPort').value.trim(),
      smtp_secure: document.getElementById('settingSmtpSecure').value,
      smtp_user: document.getElementById('settingSmtpUser').value.trim(),
      smtp_pass: document.getElementById('settingSmtpPass').value
    };

    const btn = document.getElementById('btnSaveSettings');
    btn.disabled = true;

    try {
      const res = await fetch('../api/admin/settings.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      const json = await res.json();
      if (json.success) {
        AdminApp.showToast('Pengaturan sistem & konfigurasi email berhasil disimpan.', 'success');
      } else {
        AdminApp.showToast(json.message, 'danger');
      }
    } catch (e) {
      AdminApp.showToast('Gagal menyimpan pengaturan.', 'danger');
    } finally {
      btn.disabled = false;
    }
  }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
