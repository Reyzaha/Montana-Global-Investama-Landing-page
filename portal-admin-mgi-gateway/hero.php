<?php
$pageTitle = 'Manajemen Hero Section';
require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
  <div>
    <h4 class="fw-bold text-dark mb-1">Manajemen Hero Section</h4>
    <p class="text-muted small mb-0">Kelola judul utama, teks penekanan, pengantar, tombol Call-to-Action, dan media visual di beranda landing page secara real-time.</p>
  </div>
  <button type="button" class="btn btn-mgi-gold shadow-sm d-flex align-items-center gap-2" onclick="openHeroModal()">
    <i class="bi bi-plus-circle-fill"></i>
    <span>Tambah Hero Section Baru</span>
  </button>
</div>

<!-- Alert Box -->
<div id="heroAlertContainer"></div>

<!-- Live Preview Card -->
<div class="admin-card mb-4 bg-white border">
  <div class="admin-card-header bg-light border-bottom d-flex align-items-center justify-content-between py-2.5 px-3">
    <div class="d-flex align-items-center gap-2">
      <i class="bi bi-eye-fill text-primary" style="color: #142563 !important;"></i>
      <span class="fw-bold text-dark small">Pratinjau Langsung (Live Preview di Beranda)</span>
    </div>
    <span class="badge bg-success small" id="previewStatusBadge">Status: Aktif</span>
  </div>
  <div class="p-4 p-md-5 text-center bg-white rounded-bottom position-relative overflow-hidden" id="heroLivePreview" style="min-height: 240px;">
    <div class="corporate-kicker mb-3" style="display: inline-block; padding: 4px 14px; background: rgba(197, 160, 89, 0.12); color: #C5A059; border: 1px solid rgba(197, 160, 89, 0.3); border-radius: 999px; font-size: 0.78rem; font-weight: 700; text-transform: uppercase;">
      <span id="prevKicker">Grup &amp; Manajer Investasi Proyek Sektor Riil</span>
    </div>
    <h2 class="display-6 fw-bold text-dark mb-3 lh-tight" id="prevTitleWrap">
      <span id="prevTitle">Tata Kelola Modal Strategis &amp;</span> <br>
      <span id="prevTitleHighlight" class="text-primary" style="color: #142563 !important;">Investasi Proyek Sektor Riil Terpercaya</span>
    </h2>
    <p class="text-muted mx-auto mb-4" id="prevSubtitle" style="max-width: 780px; font-size: 1.05rem; line-height: 1.7;">
      Memuat teks deskripsi hero...
    </p>
    <div class="d-flex flex-wrap justify-content-center gap-3">
      <a href="javascript:void(0)" class="btn btn-mgi-gold px-4 py-2.5 fw-bold rounded-2 shadow-sm" id="prevCtaPrimary">Katalog Proyek Terbuka &rarr;</a>
      <a href="javascript:void(0)" class="btn btn-outline-dark px-4 py-2.5 fw-bold rounded-2" id="prevCtaSecondary">Profil &amp; Tata Kelola</a>
    </div>
  </div>
</div>

<!-- Table of Hero Sections -->
<div class="admin-card">
  <div class="admin-card-header bg-white d-flex align-items-center justify-content-between p-3 border-bottom">
    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
      <i class="bi bi-collection-play-fill text-warning" style="color: #C5A059 !important;"></i>
      <span>Daftar Konfigurasi Hero Section</span>
    </h6>
    <button class="btn btn-sm btn-outline-secondary" onclick="loadHeroList()">
      <i class="bi bi-arrow-clockwise me-1"></i> Refresh
    </button>
  </div>
  <div class="table-responsive">
    <table class="admin-table table-hover mb-0">
      <thead>
        <tr>
          <th>Kicker</th>
          <th>Judul Utama &amp; Penekanan</th>
          <th>Tombol CTA</th>
          <th>Status</th>
          <th>Urutan</th>
          <th class="text-end">Aksi</th>
        </tr>
      </thead>
      <tbody id="heroTableBody">
        <tr>
          <td colspan="6" class="text-center py-4 text-muted">
            <span class="spinner-border spinner-border-sm me-2"></span> Memuat data hero section...
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal Form Tambah / Edit Hero -->
<div class="modal fade" id="heroModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-3">
      <div class="modal-header bg-light border-bottom py-3 px-4">
        <h5 class="modal-title fw-bold text-dark mb-0" id="heroModalTitle">Edit Hero Section</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="heroForm" onsubmit="saveHero(event)">
        <input type="hidden" id="heroId" name="id" value="">
        <div class="modal-body p-4 space-y-3">
          
          <div class="mb-3">
            <label class="form-label small fw-bold text-dark">Kicker / Kategori Atas</label>
            <input type="text" class="form-control" id="formKicker" name="kicker" placeholder="Contoh: Grup & Manajer Investasi Proyek Sektor Riil" required>
            <small class="text-muted">Teks lencana kecil di atas judul utama.</small>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-7">
              <label class="form-label small fw-bold text-dark">Judul Utama (Baris 1) <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="formTitle" name="title" placeholder="Contoh: Tata Kelola Modal Strategis &" required>
            </div>
            <div class="col-md-5">
              <label class="form-label small fw-bold text-dark">Teks Penekanan (Warna Khusus)</label>
              <input type="text" class="form-control" id="formTitleHighlight" name="title_highlight" placeholder="Contoh: Investasi Proyek Sektor Riil Terpercaya">
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label small fw-bold text-dark">Deskripsi Subjudul <span class="text-danger">*</span></label>
            <textarea class="form-control" id="formSubtitle" name="subtitle" rows="3" placeholder="Tuliskan ringkasan peran strategis MGI, alokasi permodalan alat berat, dan kepatuhan TARIF..." required></textarea>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label small fw-bold text-dark">Label Tombol CTA Utama</label>
              <input type="text" class="form-control" id="formCtaPrimaryText" name="cta_primary_text" placeholder="Katalog Proyek Terbuka" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-bold text-dark">URL Tujuan Tombol Utama</label>
              <input type="text" class="form-control" id="formCtaPrimaryUrl" name="cta_primary_url" placeholder="invest" required>
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label small fw-bold text-dark">Label Tombol Sekunder</label>
              <input type="text" class="form-control" id="formCtaSecondaryText" name="cta_secondary_text" placeholder="Profil & Tata Kelola">
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-bold text-dark">URL Tujuan Tombol Sekunder</label>
              <input type="text" class="form-control" id="formCtaSecondaryUrl" name="cta_secondary_url" placeholder="about">
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-4">
              <label class="form-label small fw-bold text-dark">Tipe Media</label>
              <select class="form-select" id="formMediaType" name="media_type">
                <option value="image">Gambar (Image)</option>
                <option value="video">Video</option>
              </select>
            </div>
            <div class="col-md-8">
              <label class="form-label small fw-bold text-dark">URL / Path Media (Opsional)</label>
              <input type="text" class="form-control" id="formMediaUrl" name="media_url" placeholder="assets/img/hero-banner.jpg">
            </div>
          </div>

          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label small fw-bold text-dark">Urutan Tampilan (Sort Order)</label>
              <input type="number" class="form-control" id="formSortOrder" name="sort_order" value="0">
            </div>
            <div class="col-md-6 d-flex align-items-center pt-3">
              <div class="form-check form-switch mt-2">
                <input class="form-check-input" type="checkbox" id="formIsActive" name="is_active" checked>
                <label class="form-check-label fw-bold text-dark" for="formIsActive">Jadikan Hero Section Aktif</label>
              </div>
            </div>
          </div>

        </div>
        <div class="modal-footer bg-light border-top py-2.5 px-4">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-mgi-primary px-4 fw-bold shadow-sm" id="btnSubmitHero">
            <i class="bi bi-save me-1"></i> Simpan Perubahan
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
let heroesList = [];
let heroModalInstance = null;

document.addEventListener('DOMContentLoaded', () => {
  heroModalInstance = new bootstrap.Modal(document.getElementById('heroModal'));
  loadHeroList();
});

async function loadHeroList() {
  const tbody = document.getElementById('heroTableBody');
  tbody.innerHTML = `<tr><td colspan="6" class="text-center py-4 text-muted"><span class="spinner-border spinner-border-sm me-2"></span> Memuat data...</td></tr>`;

  try {
    const res = await fetch('../api/admin/hero.php', { cache: 'no-store' });
    const json = await res.json();
    heroesList = (json && json.data) ? json.data : [];

    if (!heroesList || heroesList.length === 0) {
      tbody.innerHTML = `<tr><td colspan="6" class="text-center py-4 text-muted">Belum ada konfigurasi hero section di database. Klik tombol <strong>+ Tambah Hero Section</strong> untuk membuatnya.</td></tr>`;
      renderEmptyPreview();
      return;
    }

    // Render table
    tbody.innerHTML = heroesList.map(h => `
      <tr class="${h.is_active ? 'table-light' : ''}">
        <td><span class="badge bg-light text-dark border">${escapeHtml(h.kicker || '-')}</span></td>
        <td>
          <div class="fw-bold text-dark">${escapeHtml(h.title)}</div>
          <small class="text-primary">${escapeHtml(h.title_highlight || '')}</small>
        </td>
        <td>
          <div class="small fw-semibold text-dark">${escapeHtml(h.cta_primary_text || '-')} &rarr; <span class="text-muted font-monospace">${escapeHtml(h.cta_primary_url || '-')}</span></div>
        </td>
        <td>
          ${h.is_active ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-secondary">Non-Aktif</span>'}
        </td>
        <td>${h.sort_order}</td>
        <td class="text-end">
          <button class="btn btn-sm btn-outline-primary me-1" onclick="editHero(${h.id})" title="Edit">
            <i class="bi bi-pencil-square"></i>
          </button>
          <button class="btn btn-sm btn-outline-danger" onclick="deleteHero(${h.id})" title="Hapus">
            <i class="bi bi-trash"></i>
          </button>
        </td>
      </tr>
    `).join('');

    // Update live preview with active hero
    const active = heroesList.find(h => h.is_active == 1) || heroesList[0];
    if (active) {
      renderActivePreview(active);
    } else {
      renderEmptyPreview();
    }

  } catch (err) {
    tbody.innerHTML = `<tr><td colspan="6" class="text-center py-4 text-danger"><i class="bi bi-exclamation-triangle me-1"></i> Gagal memuat data hero section.</td></tr>`;
  }
}

function renderActivePreview(h) {
  document.getElementById('prevKicker').textContent = h.kicker || 'Grup & Manajer Investasi Proyek Sektor Riil';
  document.getElementById('prevTitle').textContent = h.title || '';
  document.getElementById('prevTitleHighlight').textContent = h.title_highlight || '';
  document.getElementById('prevSubtitle').textContent = h.subtitle || '';
  document.getElementById('prevCtaPrimary').textContent = (h.cta_primary_text || 'Katalog Proyek Terbuka') + ' →';
  document.getElementById('prevCtaPrimary').href = '../' + (h.cta_primary_url || 'invest');
  document.getElementById('prevCtaSecondary').textContent = h.cta_secondary_text || 'Profil & Tata Kelola';
  document.getElementById('prevCtaSecondary').href = '../' + (h.cta_secondary_url || 'about');
  document.getElementById('previewStatusBadge').className = h.is_active ? 'badge bg-success small' : 'badge bg-secondary small';
  document.getElementById('previewStatusBadge').textContent = h.is_active ? 'Status: Aktif di Beranda' : 'Status: Non-Aktif (Draft)';
}

function renderEmptyPreview() {
  document.getElementById('prevKicker').textContent = 'Pemberitahuan';
  document.getElementById('prevTitle').textContent = 'Konten Hero Belum Dibuat';
  document.getElementById('prevTitleHighlight').textContent = '';
  document.getElementById('prevSubtitle').textContent = 'Belum ada data hero aktif di database. Halaman depan akan menampilkan placeholder panduan.';
  document.getElementById('previewStatusBadge').className = 'badge bg-warning text-dark small';
  document.getElementById('previewStatusBadge').textContent = 'Belum Ada Konten';
}

function openHeroModal() {
  document.getElementById('heroModalTitle').textContent = 'Tambah Hero Section Baru';
  document.getElementById('heroForm').reset();
  document.getElementById('heroId').value = '';
  document.getElementById('formIsActive').checked = true;
  document.getElementById('formSortOrder').value = '0';
  heroModalInstance.show();
}

function editHero(id) {
  const h = heroesList.find(x => x.id == id);
  if (!h) return;

  document.getElementById('heroModalTitle').textContent = 'Edit Hero Section (ID: ' + h.id + ')';
  document.getElementById('heroId').value = h.id;
  document.getElementById('formKicker').value = h.kicker || '';
  document.getElementById('formTitle').value = h.title || '';
  document.getElementById('formTitleHighlight').value = h.title_highlight || '';
  document.getElementById('formSubtitle').value = h.subtitle || '';
  document.getElementById('formCtaPrimaryText').value = h.cta_primary_text || '';
  document.getElementById('formCtaPrimaryUrl').value = h.cta_primary_url || '';
  document.getElementById('formCtaSecondaryText').value = h.cta_secondary_text || '';
  document.getElementById('formCtaSecondaryUrl').value = h.cta_secondary_url || '';
  document.getElementById('formMediaType').value = h.media_type || 'image';
  document.getElementById('formMediaUrl').value = h.media_url || '';
  document.getElementById('formSortOrder').value = h.sort_order || 0;
  document.getElementById('formIsActive').checked = !!parseInt(h.is_active);

  heroModalInstance.show();
}

async function saveHero(e) {
  e.preventDefault();
  const btn = document.getElementById('btnSubmitHero');
  const originalHtml = btn.innerHTML;
  btn.disabled = true;
  btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...`;

  const id = document.getElementById('heroId').value;
  const payload = {
    id: id ? parseInt(id) : null,
    kicker: document.getElementById('formKicker').value.trim(),
    title: document.getElementById('formTitle').value.trim(),
    title_highlight: document.getElementById('formTitleHighlight').value.trim(),
    subtitle: document.getElementById('formSubtitle').value.trim(),
    cta_primary_text: document.getElementById('formCtaPrimaryText').value.trim(),
    cta_primary_url: document.getElementById('formCtaPrimaryUrl').value.trim(),
    cta_secondary_text: document.getElementById('formCtaSecondaryText').value.trim(),
    cta_secondary_url: document.getElementById('formCtaSecondaryUrl').value.trim(),
    media_type: document.getElementById('formMediaType').value,
    media_url: document.getElementById('formMediaUrl').value.trim(),
    sort_order: parseInt(document.getElementById('formSortOrder').value) || 0,
    is_active: document.getElementById('formIsActive').checked ? 1 : 0
  };

  try {
    const url = '../api/admin/hero.php';
    const method = id ? 'PUT' : 'POST';
    const res = await fetch(url, {
      method: method,
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const json = await res.json();

    if (json && json.success) {
      heroModalInstance.hide();
      showAlert('success', json.message || 'Hero section berhasil disimpan.');
      loadHeroList();
    } else {
      showAlert('danger', json.message || 'Gagal menyimpan hero section.');
    }
  } catch (err) {
    showAlert('danger', 'Terjadi kesalahan jaringan.');
  } finally {
    btn.disabled = false;
    btn.innerHTML = originalHtml;
  }
}

async function deleteHero(id) {
  if (!confirm('Apakah Anda yakin ingin menghapus konfigurasi hero section ini?')) return;

  try {
    const res = await fetch('../api/admin/hero.php', {
      method: 'DELETE',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: id })
    });
    const json = await res.json();
    if (json && json.success) {
      showAlert('success', 'Hero section berhasil dihapus.');
      loadHeroList();
    } else {
      showAlert('danger', json.message || 'Gagal menghapus hero section.');
    }
  } catch (err) {
    showAlert('danger', 'Terjadi kesalahan jaringan.');
  }
}

function showAlert(type, msg) {
  const container = document.getElementById('heroAlertContainer');
  container.innerHTML = `
    <div class="alert alert-${type} alert-dismissible fade show" role="alert">
      ${msg}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  `;
}

function escapeHtml(str) {
  if (!str) return '';
  return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
