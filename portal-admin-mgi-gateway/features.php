<?php
$pageTitle = 'Manajemen Fitur & Value Proposition';
require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
  <div>
    <h4 class="fw-bold text-dark mb-1">Manajemen Fitur, Layanan &amp; Value Proposition</h4>
    <p class="text-muted small mb-0">Kelola 5 pilar keunggulan nilai investasi (Value Proposition), pilar ekosistem bisnis (MIU, MSI, Mypurcase), dan fitur layanan secara dinamis.</p>
  </div>
  <button type="button" class="btn btn-mgi-gold shadow-sm d-flex align-items-center gap-2" onclick="openFeatureModal()">
    <i class="bi bi-plus-circle-fill"></i>
    <span>Tambah Fitur Baru</span>
  </button>
</div>

<!-- Alert Box -->
<div id="featureAlertContainer"></div>

<!-- Filter Tabs -->
<ul class="nav nav-pills mb-3 gap-2 bg-white p-2 rounded-3 border border-subtle shadow-sm flex-wrap" id="featureCategoryFilter">
  <li class="nav-item">
    <button class="nav-link active fw-bold px-3 py-1.5" onclick="filterCategory('')">Semua Kategori</button>
  </li>
  <li class="nav-item">
    <button class="nav-link fw-bold px-3 py-1.5" onclick="filterCategory('value_prop')">
      <i class="bi bi-shield-check me-1 text-primary" style="color: #142563 !important;"></i> Value Proposition (5 Pilar)
    </button>
  </li>
  <li class="nav-item">
    <button class="nav-link fw-bold px-3 py-1.5" onclick="filterCategory('pilar')">
      <i class="bi bi-diagram-3-fill me-1 text-warning" style="color: #C5A059 !important;"></i> Pilar Ekosistem Usaha
    </button>
  </li>
  <li class="nav-item">
    <button class="nav-link fw-bold px-3 py-1.5" onclick="filterCategory('layanan')">
      <i class="bi bi-gear-wide-connected me-1 text-success"></i> Layanan Proyek
    </button>
  </li>
</ul>

<!-- Features Table Card -->
<div class="admin-card">
  <div class="admin-card-header bg-white d-flex align-items-center justify-content-between p-3 border-bottom">
    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
      <i class="bi bi-list-check text-warning" style="color: #C5A059 !important;"></i>
      <span>Daftar Fitur &amp; Keunggulan Aktif</span>
    </h6>
    <button class="btn btn-sm btn-outline-secondary" onclick="loadFeaturesList()">
      <i class="bi bi-arrow-clockwise me-1"></i> Refresh
    </button>
  </div>
  <div class="table-responsive">
    <table class="admin-table table-hover mb-0">
      <thead>
        <tr>
          <th style="width: 70px;">No / ID</th>
          <th>Kategori</th>
          <th>Judul &amp; Subjudul</th>
          <th>Ikon / Media</th>
          <th>Urutan</th>
          <th>Status</th>
          <th class="text-end" style="width: 150px;">Aksi</th>
        </tr>
      </thead>
      <tbody id="featuresTableBody">
        <tr>
          <td colspan="7" class="text-center py-4 text-muted">
            <span class="spinner-border spinner-border-sm me-2"></span> Memuat data fitur...
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal Form Tambah / Edit Feature -->
<div class="modal fade" id="featureModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-3">
      <div class="modal-header bg-light border-bottom py-3 px-4">
        <h5 class="modal-title fw-bold text-dark mb-0" id="featureModalTitle">Tambah Fitur Baru</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="featureForm" onsubmit="saveFeature(event)">
        <input type="hidden" id="featId" name="id" value="">
        <div class="modal-body p-4 space-y-3">
          
          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label small fw-bold text-dark">Kategori Fitur <span class="text-danger">*</span></label>
              <select class="form-select" id="formCategory" name="category" required>
                <option value="value_prop">Value Proposition (Pilar Keunggulan Investasi)</option>
                <option value="pilar">Pilar Usaha (Ekosistem MIU, MSI, Mypurcase)</option>
                <option value="layanan">Layanan Proyek</option>
                <option value="keunggulan">Keunggulan Tambahan</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-bold text-dark">Nomor / Kode Editorial</label>
              <input type="text" class="form-control" id="formItemNumber" name="item_number" placeholder="Misal: 01, 02, MIU, MSI">
              <small class="text-muted">Ditampilkan besar pada sudut kartu editorial.</small>
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-8">
              <label class="form-label small fw-bold text-dark">Judul Fitur / Pilar <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="formTitle" name="title" placeholder="Contoh: Alokasi Modal Berbasis Aset Fisik" required>
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-bold text-dark">Badge / Label Singkat</label>
              <input type="text" class="form-control" id="formBadgeText" name="badge_text" placeholder="Contoh: Keamanan Modal">
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label small fw-bold text-dark">Subjudul / Tagline Ringkas</label>
            <input type="text" class="form-control" id="formSubtitle" name="subtitle" placeholder="Contoh: Proteksi Nilai Riil Berkelanjutan">
          </div>

          <div class="mb-3">
            <label class="form-label small fw-bold text-dark">Deskripsi Lengkap <span class="text-danger">*</span></label>
            <textarea class="form-control" id="formDescription" name="description" rows="3" placeholder="Jelaskan secara mendalam tentang fitur/keunggulan ini..." required></textarea>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label small fw-bold text-dark">Path Ikon</label>
              <input type="text" class="form-control" id="formIcon" name="icon" placeholder="Contoh: assets/img/icon-alokasi-modal.png atau bi-shield-check">
              <small class="text-muted">Bisa berupa path gambar PNG/SVG atau Bootstrap Icon class.</small>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-bold text-dark">URL / Path Foto (Untuk Kartu Pilar)</label>
              <input type="text" class="form-control" id="formImageUrl" name="image_url" placeholder="Contoh: assets/img/miu-building.jpg">
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-4">
              <label class="form-label small fw-bold text-dark">Warna Aksen Border / Aset</label>
              <input type="color" class="form-control form-control-color w-100" id="formAccentColor" name="accent_color" value="#1D3589">
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-bold text-dark">Urutan Tampil (Sort)</label>
              <input type="number" class="form-control" id="formSortOrder" name="sort_order" value="1" min="0">
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-bold text-dark">Status Publikasi</label>
              <select class="form-select" id="formIsActive" name="is_active">
                <option value="1">Aktif / Publikasi</option>
                <option value="0">Draft / Nonaktif</option>
              </select>
            </div>
          </div>

        </div>
        <div class="modal-footer bg-light border-top py-3 px-4">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-mgi-gold px-4 fw-bold shadow-sm" id="btnSaveFeature">
            <span class="spinner-border spinner-border-sm me-1 d-none" id="saveSpinner"></span>
            Simpan Perubahan
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
let currentCategory = '';
let featureModalInstance = null;
let allFeatures = [];

document.addEventListener('DOMContentLoaded', () => {
  featureModalInstance = new bootstrap.Modal(document.getElementById('featureModal'));
  loadFeaturesList();
});

function showAlert(message, type = 'success') {
  const container = document.getElementById('featureAlertContainer');
  container.innerHTML = `
    <div class="alert alert-${type} alert-dismissible fade show shadow-sm" role="alert">
      <div class="d-flex align-items-center gap-2">
        <i class="bi ${type === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill'} fs-5"></i>
        <div>${escapeHtml(message)}</div>
      </div>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  `;
}

function filterCategory(cat) {
  currentCategory = cat;
  const buttons = document.querySelectorAll('#featureCategoryFilter button');
  buttons.forEach(btn => btn.classList.remove('active'));
  event.target.classList.add('active');
  renderTable();
}

async function loadFeaturesList() {
  const tbody = document.getElementById('featuresTableBody');
  tbody.innerHTML = `
    <tr>
      <td colspan="7" class="text-center py-4 text-muted">
        <span class="spinner-border spinner-border-sm me-2"></span> Memuat data fitur...
      </td>
    </tr>
  `;

  try {
    const res = await fetch('../api/admin/features.php', { cache: 'no-store' });
    const json = await res.json();
    if (!json.success) throw new Error(json.message || 'Gagal memuat data.');

    allFeatures = json.data || [];
    renderTable();
  } catch (err) {
    tbody.innerHTML = `
      <tr>
        <td colspan="7" class="text-center py-4 text-danger">
          <i class="bi bi-exclamation-circle me-1"></i> ${escapeHtml(err.message)}
          <br><button class="btn btn-sm btn-outline-dark mt-2" onclick="loadFeaturesList()">Coba Lagi</button>
        </td>
      </tr>
    `;
  }
}

function renderTable() {
  const tbody = document.getElementById('featuresTableBody');
  const filtered = currentCategory ? allFeatures.filter(f => f.category === currentCategory) : allFeatures;

  if (filtered.length === 0) {
    tbody.innerHTML = `
      <tr>
        <td colspan="7" class="text-center py-5 text-muted">
          <i class="bi bi-inbox fs-3 d-block mb-2"></i>
          Belum ada fitur untuk kategori ini. Klik "Tambah Fitur Baru" untuk memulai.
        </td>
      </tr>
    `;
    return;
  }

  tbody.innerHTML = filtered.map(item => {
    const badgeCategory = item.category === 'value_prop' 
      ? '<span class="badge bg-primary text-white" style="background-color: #142563 !important;">Value Prop</span>'
      : (item.category === 'pilar' ? '<span class="badge bg-warning text-dark" style="background-color: #C5A059 !important; color: #fff !important;">Pilar Ekosistem</span>' : '<span class="badge bg-secondary">Layanan</span>');

    const statusBadge = item.is_active == 1
      ? '<span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 py-1 px-2">Aktif</span>'
      : '<span class="badge bg-secondary bg-opacity-10 text-muted border border-secondary border-opacity-25 py-1 px-2">Draft</span>';

    const mediaPreview = item.image_url 
      ? `<img src="../${escapeHtml(item.image_url)}" class="rounded border" style="width: 38px; height: 38px; object-fit: cover;" onerror="this.src='../assets/img/mgi-official-logo.png'">`
      : (item.icon ? `<img src="../${escapeHtml(item.icon)}" style="width: 28px; height: 28px; object-fit: contain;" onerror="this.replaceWith(document.createTextNode('📦'))">` : '-');

    return `
      <tr>
        <td class="fw-bold text-dark">
          <span class="badge bg-light text-dark border px-2 py-1">${escapeHtml(item.item_number || '#' + item.id)}</span>
        </td>
        <td>${badgeCategory}</td>
        <td>
          <div class="fw-bold text-dark">${escapeHtml(item.title)}</div>
          <div class="text-muted small text-truncate" style="max-width: 280px;">${escapeHtml(item.subtitle || item.description)}</div>
        </td>
        <td>${mediaPreview}</td>
        <td><span class="badge bg-light text-secondary border">${item.sort_order}</span></td>
        <td>
          <button type="button" class="btn btn-sm p-0 border-0" onclick="toggleFeatureStatus(${item.id})">
            ${statusBadge}
          </button>
        </td>
        <td class="text-end">
          <button type="button" class="btn btn-sm btn-outline-primary me-1 p-1 px-2" onclick="editFeature(${item.id})" title="Edit">
            <i class="bi bi-pencil-square"></i>
          </button>
          <button type="button" class="btn btn-sm btn-outline-danger p-1 px-2" onclick="deleteFeature(${item.id}, '${escapeHtml(item.title)}')" title="Hapus">
            <i class="bi bi-trash"></i>
          </button>
        </td>
      </tr>
    `;
  }).join('');
}

function openFeatureModal(feature = null) {
  const form = document.getElementById('featureForm');
  form.reset();

  if (feature) {
    document.getElementById('featureModalTitle').innerText = 'Edit Fitur / Pilar';
    document.getElementById('featId').value = feature.id;
    document.getElementById('formCategory').value = feature.category;
    document.getElementById('formItemNumber').value = feature.item_number || '';
    document.getElementById('formTitle').value = feature.title;
    document.getElementById('formSubtitle').value = feature.subtitle || '';
    document.getElementById('formDescription').value = feature.description;
    document.getElementById('formIcon').value = feature.icon || '';
    document.getElementById('formImageUrl').value = feature.image_url || '';
    document.getElementById('formBadgeText').value = feature.badge_text || '';
    document.getElementById('formAccentColor').value = feature.accent_color || '#1D3589';
    document.getElementById('formSortOrder').value = feature.sort_order;
    document.getElementById('formIsActive').value = feature.is_active;
  } else {
    document.getElementById('featureModalTitle').innerText = 'Tambah Fitur Baru';
    document.getElementById('featId').value = '';
    document.getElementById('formCategory').value = currentCategory || 'value_prop';
    document.getElementById('formAccentColor').value = '#1D3589';
    document.getElementById('formSortOrder').value = allFeatures.length + 1;
    document.getElementById('formIsActive').value = '1';
  }

  featureModalInstance.show();
}

function editFeature(id) {
  const feature = allFeatures.find(f => f.id == id);
  if (feature) openFeatureModal(feature);
}

async function saveFeature(e) {
  e.preventDefault();
  const form = document.getElementById('featureForm');
  const id = document.getElementById('featId').value;
  const isEdit = !!id;

  const payload = {
    id: id ? parseInt(id) : undefined,
    category: document.getElementById('formCategory').value,
    item_number: document.getElementById('formItemNumber').value,
    title: document.getElementById('formTitle').value,
    subtitle: document.getElementById('formSubtitle').value,
    description: document.getElementById('formDescription').value,
    icon: document.getElementById('formIcon').value,
    image_url: document.getElementById('formImageUrl').value,
    badge_text: document.getElementById('formBadgeText').value,
    accent_color: document.getElementById('formAccentColor').value,
    sort_order: parseInt(document.getElementById('formSortOrder').value) || 0,
    is_active: parseInt(document.getElementById('formIsActive').value) || 0
  };

  const btn = document.getElementById('btnSaveFeature');
  const spinner = document.getElementById('saveSpinner');
  btn.disabled = true;
  spinner.classList.remove('d-none');

  try {
    const res = await fetch('../api/admin/features.php', {
      method: isEdit ? 'PUT' : 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const json = await res.json();
    if (!json.success) throw new Error(json.message || 'Gagal menyimpan.');

    showAlert(isEdit ? 'Fitur berhasil diperbarui.' : 'Fitur berhasil ditambahkan.');
    featureModalInstance.hide();
    loadFeaturesList();
  } catch (err) {
    showAlert(err.message, 'danger');
  } finally {
    btn.disabled = false;
    spinner.classList.add('d-none');
  }
}

async function toggleFeatureStatus(id) {
  try {
    const res = await fetch('../api/admin/features.php', {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id, action: 'toggle_status' })
    });
    const json = await res.json();
    if (!json.success) throw new Error(json.message);
    showAlert('Status fitur berhasil diubah.');
    loadFeaturesList();
  } catch (err) {
    showAlert(err.message, 'danger');
  }
}

async function deleteFeature(id, title) {
  if (!confirm(`Hapus fitur "${title}" secara permanen?`)) return;

  try {
    const res = await fetch('../api/admin/features.php', {
      method: 'DELETE',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id })
    });
    const json = await res.json();
    if (!json.success) throw new Error(json.message);
    showAlert('Fitur berhasil dihapus.');
    loadFeaturesList();
  } catch (err) {
    showAlert(err.message, 'danger');
  }
}

function escapeHtml(str) {
  if (!str) return '';
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
