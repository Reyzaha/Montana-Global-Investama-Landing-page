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
                Jika diaktifkan, rincian belanja modal (RAB), spesifikasi unit fisik, dan kalkulator simulasi BEP/ROI hanya dapat diakses oleh investor yang telah login. Pengunjung umum akan diarahkan ke modal autentikasi sesuai kepatuhan APU-PPT &amp; GCG.
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
      document.getElementById('settingEmail').value = s.official_email ? s.official_email.value : 'Montanaglobalinvestamaom@gmail.com';
    } catch (e) {
      console.error(e);
      AdminApp.showToast('Gagal memuat pengaturan sistem.', 'danger');
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
      official_email: document.getElementById('settingEmail').value.trim()
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
        AdminApp.showToast('Pengaturan sistem berhasil disimpan.', 'success');
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
