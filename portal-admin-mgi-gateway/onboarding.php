<?php
$pageTitle = 'Setup Onboarding Konten';
require_once __DIR__ . '/includes/header.php';
?>

<div class="mb-4">
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
    <div>
      <h4 class="fw-bold text-dark mb-1">Wizard Onboarding Konten Awal</h4>
      <p class="text-muted small mb-0">Panduan langkah-demi-langkah bagi pemilik situs untuk mengisi identitas resmi, hero banner, kontak, dan kebijakan tata kelola secara manual tanpa data dummy.</p>
    </div>
    <span class="badge px-3 py-2 rounded-pill small fw-bold" id="onboardingStatusBadge" style="background-color: #142563; color: #fff;">
      <i class="bi bi-magic me-1"></i> Mode Konfigurasi
    </span>
  </div>
</div>

<!-- Alert Box -->
<div id="onboardingAlertContainer"></div>

<!-- Step Navigation Pills -->
<div class="bg-white p-3 rounded-3 border shadow-sm mb-4">
  <div class="row g-2 text-center" id="onboardingStepsNav">
    <div class="col-4 col-md-3">
      <button type="button" class="btn btn-sm w-100 fw-bold py-2 btn-mgi-primary step-nav-btn active" id="stepBtn1" onclick="goToStep(1)">
        <span class="badge bg-white text-dark rounded-circle me-1">1</span> Identitas &amp; Kontak
      </button>
    </div>
    <div class="col-4 col-md-3">
      <button type="button" class="btn btn-sm w-100 fw-bold py-2 btn-outline-secondary step-nav-btn" id="stepBtn2" onclick="goToStep(2)">
        <span class="badge bg-secondary text-white rounded-circle me-1">2</span> Hero Banner
      </button>
    </div>
    <div class="col-4 col-md-3">
      <button type="button" class="btn btn-sm w-100 fw-bold py-2 btn-outline-secondary step-nav-btn" id="stepBtn3" onclick="goToStep(3)">
        <span class="badge bg-secondary text-white rounded-circle me-1">3</span> Tata Kelola
      </button>
    </div>
    <div class="col-12 col-md-3">
      <button type="button" class="btn btn-sm w-100 fw-bold py-2 btn-outline-secondary step-nav-btn" id="stepBtn4" onclick="goToStep(4)">
        <span class="badge bg-secondary text-white rounded-circle me-1">4</span> Konfirmasi
      </button>
    </div>
  </div>
</div>

<form id="onboardingForm" onsubmit="submitOnboarding(event)">
  <!-- STEP 1: IDENTITAS & KONTAK -->
  <div class="admin-card step-panel" id="panelStep1">
    <div class="admin-card-header bg-white p-3 border-bottom">
      <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
        <i class="bi bi-building-fill text-warning" style="color: #C5A059 !important;"></i>
        <span>Langkah 1: Identitas Resmi Perusahaan &amp; Kontak Layanan</span>
      </h6>
    </div>
    <div class="admin-card-body p-4">
      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label small fw-bold text-dark">Nama Resmi Perusahaan <span class="text-danger">*</span></label>
          <input type="text" class="form-control" id="obSiteTitle" value="PT Montana Global Investama" required>
        </div>
        <div class="col-md-6">
          <label class="form-label small fw-bold text-dark">Tagline Korporat</label>
          <input type="text" class="form-control" id="obSiteTagline" value="Tumbuh Bersama Melalui Investasi Terpercaya">
        </div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label small fw-bold text-dark">Email Resmi Korespondensi <span class="text-danger">*</span></label>
          <input type="email" class="form-control" id="obEmail" value="contact@montanainvestama.com" required>
        </div>
        <div class="col-md-6">
          <label class="form-label small fw-bold text-dark">Nomor Telepon Kantor / WA</label>
          <input type="text" class="form-control" id="obPhone" value="+62 21 5315 8899">
        </div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label small fw-bold text-dark">Alamat Kantor Pusat</label>
          <textarea class="form-control" id="obAddress" rows="2">Roseville Soho &amp; Suite, Sunburst CBD Lot I.8, Serpong, Tangerang Selatan</textarea>
        </div>
        <div class="col-md-6">
          <label class="form-label small fw-bold text-dark">Alamat Pool &amp; Workshop Sentral</label>
          <textarea class="form-control" id="obWorkshop" rows="2">Jl. Nasional III No.88, Pejuritan, Kec. Karanganyar, Kab. Kebumen, Jawa Tengah</textarea>
        </div>
      </div>

      <div class="text-end mt-4 pt-3 border-top">
        <button type="button" class="btn btn-mgi-primary px-4 fw-bold" onclick="goToStep(2)">
          <span>Lanjut ke Hero Banner</span> <i class="bi bi-arrow-right ms-1"></i>
        </button>
      </div>
    </div>
  </div>

  <!-- STEP 2: HERO BANNER -->
  <div class="admin-card step-panel d-none" id="panelStep2">
    <div class="admin-card-header bg-white p-3 border-bottom">
      <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
        <i class="bi bi-badge-ad-fill text-warning" style="color: #C5A059 !important;"></i>
        <span>Langkah 2: Pesan Utama di Halaman Depan (Hero Section)</span>
      </h6>
    </div>
    <div class="admin-card-body p-4">
      <div class="mb-3">
        <label class="form-label small fw-bold text-dark">Badge / Kicker Header</label>
        <input type="text" class="form-control" id="obHeroBadge" value="Manajer Investasi Sektor Riil Berbasis Aset Produktif">
      </div>

      <div class="mb-3">
        <label class="form-label small fw-bold text-dark">Judul Utama (Headline) <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="obHeroHeadline" value="Investasi Terpercaya Pada Sektor Riil Berbasis Aset Produktif" required>
      </div>

      <div class="mb-3">
        <label class="form-label small fw-bold text-dark">Subjudul Penjelas (Subheadline)</label>
        <textarea class="form-control" id="obHeroSubheadline" rows="2">Akses pendanaan langsung pada proyek pengadaan unit alat berat prima dengan tata kelola profesional dan bagi hasil transparan.</textarea>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label small fw-bold text-dark">Teks Tombol CTA Utama</label>
          <input type="text" class="form-control" id="obHeroCtaText" value="Pelajari Proyek Berjalan">
        </div>
        <div class="col-md-6">
          <label class="form-label small fw-bold text-dark">Target Link CTA Utama</label>
          <input type="text" class="form-control" id="obHeroCtaLink" value="invest">
        </div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label small fw-bold text-dark">Teks Tombol CTA Sekunder</label>
          <input type="text" class="form-control" id="obHeroSecCtaText" value="Konsultasi Tim">
        </div>
        <div class="col-md-6">
          <label class="form-label small fw-bold text-dark">Target Link CTA Sekunder</label>
          <input type="text" class="form-control" id="obHeroSecCtaLink" value="contact">
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label small fw-bold text-dark">URL Gambar / Video Hero</label>
        <input type="text" class="form-control" id="obHeroMedia" value="assets/img/hero-alat-berat.png">
      </div>

      <div class="d-flex justify-content-between mt-4 pt-3 border-top">
        <button type="button" class="btn btn-outline-secondary px-4" onclick="goToStep(1)">
          <i class="bi bi-arrow-left me-1"></i> Kembali
        </button>
        <button type="button" class="btn btn-mgi-primary px-4 fw-bold" onclick="goToStep(3)">
          <span>Lanjut ke Tata Kelola</span> <i class="bi bi-arrow-right ms-1"></i>
        </button>
      </div>
    </div>
  </div>

  <!-- STEP 3: TATA KELOLA & REGULASI -->
  <div class="admin-card step-panel d-none" id="panelStep3">
    <div class="admin-card-header bg-white p-3 border-bottom">
      <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
        <i class="bi bi-shield-check text-warning" style="color: #C5A059 !important;"></i>
        <span>Langkah 3: Tata Kelola (GCG), Proteksi Dokumen &amp; Risiko</span>
      </h6>
    </div>
    <div class="admin-card-body p-4">
      <div class="p-3 bg-light rounded-3 border mb-4">
        <div class="form-check form-switch d-flex align-items-center justify-content-between ps-0">
          <div>
            <label class="form-check-label fw-bold text-dark d-block mb-1" for="obGatedToggle">
              Proteksi Akses Detail Proyek (Wajib Login Pemodal)
            </label>
            <small class="text-muted d-block">
              Wajibkan pengunjung mendaftar/login sebelum melihat rincian belanja modal (RAB), spesifikasi nomor seri mesin alat berat, dan simulasi BEP/ROI.
            </small>
          </div>
          <input class="form-check-input ms-3 fs-3" type="checkbox" role="switch" id="obGatedToggle" checked>
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label small fw-bold text-dark">Pernyataan Keterbukaan Risiko (Risk Disclosure Statement)</label>
        <textarea class="form-control" id="obDisclaimer" rows="3">Target ilustratif berdasarkan proyeksi kinerja, bukan jaminan — hasil aktual mengikuti kinerja riil usaha dan dapat lebih rendah dari target. Investasi pada instrumen sektor riil mengandung risiko fluktuasi pasar dan operasional. Harap membaca seluruh dokumen penawaran dan Risk Disclosure Statement secara cermat.</textarea>
      </div>

      <div class="d-flex justify-content-between mt-4 pt-3 border-top">
        <button type="button" class="btn btn-outline-secondary px-4" onclick="goToStep(2)">
          <i class="bi bi-arrow-left me-1"></i> Kembali
        </button>
        <button type="button" class="btn btn-mgi-primary px-4 fw-bold" onclick="goToStep(4)">
          <span>Tinjau &amp; Konfirmasi</span> <i class="bi bi-arrow-right ms-1"></i>
        </button>
      </div>
    </div>
  </div>

  <!-- STEP 4: KONFIRMASI & LUNCURKAN -->
  <div class="admin-card step-panel d-none" id="panelStep4">
    <div class="admin-card-header bg-white p-3 border-bottom">
      <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
        <i class="bi bi-check-circle-fill text-success"></i>
        <span>Langkah 4: Tinjauan Akhir &amp; Selesaikan Onboarding</span>
      </h6>
    </div>
    <div class="admin-card-body p-4">
      <div class="alert alert-success d-flex align-items-center gap-3 p-3 rounded-3 mb-4">
        <i class="bi bi-patch-check-fill fs-2 text-success"></i>
        <div>
          <strong class="d-block text-dark fs-6">Seluruh data awal siap diterapkan ke database produksi!</strong>
          <small class="text-secondary">Sistem memastikan tidak ada data mock atau lorem ipsum yang tayang. Seluruh section frontend akan langsung menyinkronkan data yang Anda tentukan di wizard ini.</small>
        </div>
      </div>

      <div class="p-3 bg-light rounded-3 border mb-4">
        <h6 class="fw-bold text-dark mb-2">Ringkasan Setup:</h6>
        <ul class="list-unstyled mb-0 small space-y-2">
          <li class="d-flex justify-content-between py-1 border-bottom">
            <span class="text-muted">Perusahaan:</span>
            <strong class="text-dark" id="revTitle">-</strong>
          </li>
          <li class="d-flex justify-content-between py-1 border-bottom">
            <span class="text-muted">Email Korespondensi:</span>
            <span class="text-primary" id="revEmail">-</span>
          </li>
          <li class="d-flex justify-content-between py-1 border-bottom">
            <span class="text-muted">Headline Hero:</span>
            <span class="text-dark text-truncate" style="max-width: 320px;" id="revHeadline">-</span>
          </li>
          <li class="d-flex justify-content-between py-1">
            <span class="text-muted">Proteksi Dokumen:</span>
            <span class="badge bg-success" id="revGated">Aktif (Wajib Login)</span>
          </li>
        </ul>
      </div>

      <div class="d-flex justify-content-between mt-4 pt-3 border-top">
        <button type="button" class="btn btn-outline-secondary px-4" onclick="goToStep(3)">
          <i class="bi bi-arrow-left me-1"></i> Kembali
        </button>
        <button type="submit" class="btn btn-mgi-gold px-5 py-2.5 fw-bold shadow-sm d-flex align-items-center gap-2" id="btnFinishOnboarding">
          <i class="bi bi-rocket-takeoff-fill"></i>
          <span>Simpan &amp; Luncurkan Situs</span>
        </button>
      </div>
    </div>
  </div>
</form>

<script>
let currentStep = 1;

document.addEventListener('DOMContentLoaded', async () => {
  try {
    const res = await fetch('../api/admin/onboarding.php');
    const result = await res.json();
    if (res.ok && result.success && result.data) {
      const d = result.data;
      if (d.onboarding_completed) {
        document.getElementById('onboardingStatusBadge').innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> Onboarding Selesai';
        document.getElementById('onboardingStatusBadge').className = 'badge bg-success text-white px-3 py-2 rounded-pill small fw-bold';
      }

      const s = d.settings || {};
      if (s.site_title) document.getElementById('obSiteTitle').value = s.site_title;
      if (s.site_tagline) document.getElementById('obSiteTagline').value = s.site_tagline;
      if (s.contact_email) document.getElementById('obEmail').value = s.contact_email;
      if (s.contact_phone) document.getElementById('obPhone').value = s.contact_phone;
      if (s.contact_address) document.getElementById('obAddress').value = s.contact_address;
      if (s.workshop_address) document.getElementById('obWorkshop').value = s.workshop_address;
      if (s.disclaimer_text) document.getElementById('obDisclaimer').value = s.disclaimer_text;
      document.getElementById('obGatedToggle').checked = (s.require_auth_for_details === '1');

      const h = d.hero || {};
      if (h.badge_text) document.getElementById('obHeroBadge').value = h.badge_text;
      if (h.headline) document.getElementById('obHeroHeadline').value = h.headline;
      if (h.subheadline) document.getElementById('obHeroSubheadline').value = h.subheadline;
      if (h.cta_text) document.getElementById('obHeroCtaText').value = h.cta_text;
      if (h.cta_link) document.getElementById('obHeroCtaLink').value = h.cta_link;
      if (h.secondary_cta_text) document.getElementById('obHeroSecCtaText').value = h.secondary_cta_text;
      if (h.secondary_cta_link) document.getElementById('obHeroSecCtaLink').value = h.secondary_cta_link;
      if (h.media_url) document.getElementById('obHeroMedia').value = h.media_url;
    }
  } catch (e) {
    console.error('Failed to pre-fill onboarding:', e);
  }
});

function showAlert(message, type = 'success') {
  const container = document.getElementById('onboardingAlertContainer');
  if (!container) return;
  container.innerHTML = `
    <div class="alert alert-${type} alert-dismissible fade show shadow-sm d-flex align-items-center" role="alert">
      <i class="bi bi-${type === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill'} me-2 fs-5"></i>
      <div>${message}</div>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  `;
}

function goToStep(step) {
  currentStep = step;

  // Update navigation buttons
  for (let i = 1; i <= 4; i++) {
    const btn = document.getElementById(`stepBtn${i}`);
    const panel = document.getElementById(`panelStep${i}`);
    if (btn) {
      if (i === step) {
        btn.className = 'btn btn-sm w-100 fw-bold py-2 btn-mgi-primary step-nav-btn active';
      } else {
        btn.className = 'btn btn-sm w-100 fw-bold py-2 btn-outline-secondary step-nav-btn';
      }
    }
    if (panel) {
      if (i === step) panel.classList.remove('d-none');
      else panel.classList.add('d-none');
    }
  }

  // If entering step 4, update summary preview
  if (step === 4) {
    document.getElementById('revTitle').textContent = document.getElementById('obSiteTitle').value;
    document.getElementById('revEmail').textContent = document.getElementById('obEmail').value;
    document.getElementById('revHeadline').textContent = document.getElementById('obHeroHeadline').value;
    const isGated = document.getElementById('obGatedToggle').checked;
    document.getElementById('revGated').textContent = isGated ? 'Aktif (Wajib Login)' : 'Terbuka Publik';
    document.getElementById('revGated').className = isGated ? 'badge bg-success' : 'badge bg-secondary';
  }

  window.scrollTo({ top: 0, behavior: 'smooth' });
}

async function submitOnboarding(e) {
  e.preventDefault();
  const btn = document.getElementById('btnFinishOnboarding');
  btn.disabled = true;
  btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span> Menyimpan Pengaturan...`;

  const payload = {
    site_title: document.getElementById('obSiteTitle').value.trim(),
    site_tagline: document.getElementById('obSiteTagline').value.trim(),
    contact_email: document.getElementById('obEmail').value.trim(),
    official_email: document.getElementById('obEmail').value.trim(),
    contact_phone: document.getElementById('obPhone').value.trim(),
    contact_address: document.getElementById('obAddress').value.trim(),
    workshop_address: document.getElementById('obWorkshop').value.trim(),

    hero_badge: document.getElementById('obHeroBadge').value.trim(),
    hero_headline: document.getElementById('obHeroHeadline').value.trim(),
    hero_subheadline: document.getElementById('obHeroSubheadline').value.trim(),
    hero_cta_text: document.getElementById('obHeroCtaText').value.trim(),
    hero_cta_link: document.getElementById('obHeroCtaLink').value.trim(),
    hero_secondary_cta_text: document.getElementById('obHeroSecCtaText').value.trim(),
    hero_secondary_cta_link: document.getElementById('obHeroSecCtaLink').value.trim(),
    hero_media_url: document.getElementById('obHeroMedia').value.trim(),

    require_auth_for_details: document.getElementById('obGatedToggle').checked ? '1' : '0',
    disclaimer_text: document.getElementById('obDisclaimer').value.trim()
  };

  try {
    const res = await fetch('../api/admin/onboarding.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const result = await res.json();

    if (!res.ok || !result.success) {
      throw new Error(result.message || 'Gagal menyimpan onboarding');
    }

    showAlert('Selamat! Setup awal situs berhasil disimpan ke database. Anda dapat kembali memperbarui setiap section dari menu modul yang relevan.', 'success');
    document.getElementById('onboardingStatusBadge').innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> Onboarding Selesai';
    document.getElementById('onboardingStatusBadge').className = 'badge bg-success text-white px-3 py-2 rounded-pill small fw-bold';
  } catch (err) {
    showAlert(err.message, 'danger');
  } finally {
    btn.disabled = false;
    btn.innerHTML = `<i class="bi bi-rocket-takeoff-fill me-1"></i> <span>Simpan &amp; Luncurkan Situs</span>`;
  }
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
