<?php
$pageTitle = 'Manajemen Testimoni Mitra';
require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
  <div>
    <h4 class="fw-bold text-dark mb-1">Manajemen Testimoni &amp; Ulasan Mitra</h4>
    <p class="text-muted small mb-0">Kelola ulasan autentik dari mitra pemodal, kontraktor rekanan daerah, dan partner ekosistem untuk memperkuat kredibilitas tata kelola investasi.</p>
  </div>
  <button type="button" class="btn btn-mgi-gold shadow-sm d-flex align-items-center gap-2" onclick="openTestimonialModal()">
    <i class="bi bi-plus-circle-fill"></i>
    <span>Tambah Testimoni Baru</span>
  </button>
</div>

<!-- Alert Box -->
<div id="testiAlertContainer"></div>

<!-- Filter Tabs -->
<ul class="nav nav-pills mb-3 gap-2 bg-white p-2 rounded-3 border border-subtle shadow-sm flex-wrap" id="testiCategoryFilter">
  <li class="nav-item">
    <button class="nav-link active fw-bold px-3 py-1.5" onclick="filterTestiCategory('')">Semua Kategori</button>
  </li>
  <li class="nav-item">
    <button class="nav-link fw-bold px-3 py-1.5" onclick="filterTestiCategory('investor')">
      <i class="bi bi-person-check-fill me-1 text-primary" style="color: #142563 !important;"></i> Mitra Pemodal (Investor)
    </button>
  </li>
  <li class="nav-item">
    <button class="nav-link fw-bold px-3 py-1.5" onclick="filterTestiCategory('kontraktor')">
      <i class="bi bi-truck me-1 text-warning" style="color: #C5A059 !important;"></i> Kontraktor Rekanan
    </button>
  </li>
  <li class="nav-item">
    <button class="nav-link fw-bold px-3 py-1.5" onclick="filterTestiCategory('partner')">
      <i class="bi bi-handshake-fill me-1 text-success"></i> Partner Strategis
    </button>
  </li>
</ul>

<!-- Table Card -->
<div class="admin-card">
  <div class="admin-card-header bg-white d-flex align-items-center justify-content-between p-3 border-bottom">
    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
      <i class="bi bi-chat-quote-fill text-warning" style="color: #C5A059 !important;"></i>
      <span>Daftar Testimoni Terpublikasi</span>
    </h6>
    <button class="btn btn-sm btn-outline-secondary" onclick="loadTestimonialsList()">
      <i class="bi bi-arrow-clockwise me-1"></i> Refresh
    </button>
  </div>
  <div class="table-responsive">
    <table class="admin-table table-hover mb-0">
      <thead>
        <tr>
          <th>Mitra / Narasumber</th>
          <th>Kategori</th>
          <th>Rating</th>
          <th>Proyek Terkait</th>
          <th>Isi Ulasan Testimoni</th>
          <th>Status</th>
          <th class="text-end" style="width: 130px;">Aksi</th>
        </tr>
      </thead>
      <tbody id="testiTableBody">
        <tr>
          <td colspan="7" class="text-center py-4 text-muted">
            <span class="spinner-border spinner-border-sm me-2"></span> Memuat data testimoni...
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal Form Tambah / Edit Testimoni -->
<div class="modal fade" id="testiModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-3">
      <div class="modal-header bg-light border-bottom py-3 px-4">
        <h5 class="modal-title fw-bold text-dark mb-0" id="testiModalTitle">Tambah Testimoni Baru</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="testiForm" onsubmit="saveTestimonial(event)">
        <input type="hidden" id="testiId" name="id" value="">
        <div class="modal-body p-4 space-y-3">
          
          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label small fw-bold text-dark">Nama Lengkap <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="formTestiName" name="name" placeholder="Contoh: Bambang Hartono" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-bold text-dark">Kategori Mitra <span class="text-danger">*</span></label>
              <select class="form-select" id="formTestiCategory" name="category" required>
                <option value="investor">Mitra Pemodal (Investor)</option>
                <option value="kontraktor">Kontraktor Rekanan Lapangan</option>
                <option value="partner">Partner Ekosistem &amp; Logistik</option>
              </select>
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label small fw-bold text-dark">Jabatan / Peran <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="formTestiRole" name="role_title" placeholder="Contoh: Mitra Pemodal Sektor Riil" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-bold text-dark">Perusahaan / Afiliasi</label>
              <input type="text" class="form-control" id="formTestiCompany" name="company_name" placeholder="Contoh: Investor Individu Jabodetabek">
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label small fw-bold text-dark">Path Foto / Avatar</label>
              <input type="text" class="form-control" id="formTestiAvatar" name="avatar_url" placeholder="Contoh: assets/img/avatar-investor-1.png">
              <small class="text-muted">Kosongkan jika ingin memakai inisial nama.</small>
            </div>
            <div class="col-md-3">
              <label class="form-label small fw-bold text-dark">Penilaian (Bintang 1-5)</label>
              <select class="form-select" id="formTestiRating" name="rating">
                <option value="5">⭐⭐⭐⭐⭐ (5 Bintang)</option>
                <option value="4">⭐⭐⭐⭐ (4 Bintang)</option>
                <option value="3">⭐⭐⭐ (3 Bintang)</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label small fw-bold text-dark">Urutan Tampil</label>
              <input type="number" class="form-control" id="formTestiSort" name="sort_order" value="1" min="0">
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label small fw-bold text-dark">Nama Proyek Terkait</label>
            <input type="text" class="form-control" id="formTestiProject" name="project_name" placeholder="Contoh: Pengadaan Armada CBU Jabodetabek">
          </div>

          <div class="mb-3">
            <label class="form-label small fw-bold text-dark">Ulasan / Testimoni Lengkap <span class="text-danger">*</span></label>
            <textarea class="form-control" id="formTestiContent" name="content" rows="4" placeholder="Tuliskan ulasan nyata dari mitra..." required></textarea>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label small fw-bold text-dark">Status Unggulan (Featured)</label>
              <select class="form-select" id="formTestiIsFeatured" name="is_featured">
                <option value="1">Tampilkan di Beranda (Featured)</option>
                <option value="0">Tampilkan Biasa</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-bold text-dark">Status Publikasi</label>
              <select class="form-select" id="formTestiIsActive" name="is_active">
                <option value="1">Aktif / Publikasi</option>
                <option value="0">Draft / Sembunyikan</option>
              </select>
            </div>
          </div>

        </div>
        <div class="modal-footer bg-light border-top py-3 px-4">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-mgi-gold px-4 fw-bold shadow-sm" id="btnSaveTesti">
            <span class="spinner-border spinner-border-sm me-1 d-none" id="saveTestiSpinner"></span>
            Simpan Testimoni
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
let testiModalInstance = null;
let allTestimonials = [];
let currentTestiCategory = '';

document.addEventListener('DOMContentLoaded', () => {
  testiModalInstance = new bootstrap.Modal(document.getElementById('testiModal'));
  loadTestimonialsList();
});

function showAlert(message, type = 'success') {
  const container = document.getElementById('testiAlertContainer');
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

function filterTestiCategory(cat) {
  currentTestiCategory = cat;
  const buttons = document.querySelectorAll('#testiCategoryFilter button');
  buttons.forEach(btn => btn.classList.remove('active'));
  event.target.classList.add('active');
  renderTestiTable();
}

async function loadTestimonialsList() {
  const tbody = document.getElementById('testiTableBody');
  tbody.innerHTML = `
    <tr>
      <td colspan="7" class="text-center py-4 text-muted">
        <span class="spinner-border spinner-border-sm me-2"></span> Memuat data testimoni...
      </td>
    </tr>
  `;

  try {
    const res = await fetch('../api/admin/testimonials.php', { cache: 'no-store' });
    const json = await res.json();
    if (!json.success) throw new Error(json.message || 'Gagal memuat testimoni.');

    allTestimonials = json.data || [];
    renderTestiTable();
  } catch (err) {
    tbody.innerHTML = `
      <tr>
        <td colspan="7" class="text-center py-4 text-danger">
          <i class="bi bi-exclamation-circle me-1"></i> ${escapeHtml(err.message)}
          <br><button class="btn btn-sm btn-outline-dark mt-2" onclick="loadTestimonialsList()">Coba Lagi</button>
        </td>
      </tr>
    `;
  }
}

function renderTestiTable() {
  const tbody = document.getElementById('testiTableBody');
  const filtered = currentTestiCategory ? allTestimonials.filter(t => t.category === currentTestiCategory) : allTestimonials;

  if (filtered.length === 0) {
    tbody.innerHTML = `
      <tr>
        <td colspan="7" class="text-center py-5 text-muted">
          <i class="bi bi-inbox fs-3 d-block mb-2"></i>
          Belum ada testimoni dalam kategori ini. Klik "Tambah Testimoni Baru" untuk menambahkan.
        </td>
      </tr>
    `;
    return;
  }

  tbody.innerHTML = filtered.map(item => {
    const stars = '★'.repeat(item.rating) + '☆'.repeat(5 - item.rating);
    const catBadge = item.category === 'investor'
      ? '<span class="badge bg-primary text-white" style="background-color: #142563 !important;">Mitra Pemodal</span>'
      : (item.category === 'kontraktor' ? '<span class="badge bg-warning text-dark" style="background-color: #C5A059 !important; color: #fff !important;">Kontraktor</span>' : '<span class="badge bg-secondary">Partner</span>');

    const statusBadge = item.is_active == 1
      ? '<span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 py-1 px-2">Aktif</span>'
      : '<span class="badge bg-secondary bg-opacity-10 text-muted border border-secondary border-opacity-25 py-1 px-2">Draft</span>';

    const avatarHtml = item.avatar_url
      ? `<img src="../${escapeHtml(item.avatar_url)}" class="rounded-circle border" style="width: 38px; height: 38px; object-fit: cover;" onerror="this.onerror=null;this.replaceWith(document.createTextNode('👤'))">`
      : `<div class="rounded-circle bg-light border d-inline-flex align-items-center justify-content-center fw-bold text-primary" style="width: 38px; height: 38px; font-size: 0.9rem;">${escapeHtml(item.name.charAt(0))}</div>`;

    return `
      <tr>
        <td>
          <div class="d-flex align-items-center gap-2.5">
            ${avatarHtml}
            <div>
              <div class="fw-bold text-dark">${escapeHtml(item.name)}</div>
              <div class="text-muted small">${escapeHtml(item.role_title)} ${item.company_name ? '• ' + escapeHtml(item.company_name) : ''}</div>
            </div>
          </div>
        </td>
        <td>${catBadge}</td>
        <td><span class="text-warning fw-bold small" style="color: #C5A059 !important;">${stars}</span></td>
        <td><span class="badge bg-light text-dark border">${escapeHtml(item.project_name || 'Umum')}</span></td>
        <td>
          <div class="small text-muted text-truncate" style="max-width: 280px;" title="${escapeHtml(item.content)}">
            "${escapeHtml(item.content)}"
          </div>
        </td>
        <td>
          <button type="button" class="btn btn-sm p-0 border-0" onclick="toggleTestiStatus(${item.id})" title="Klik untuk ubah status">
            ${statusBadge}
          </button>
        </td>
        <td class="text-end">
          <button type="button" class="btn btn-sm btn-outline-primary me-1 p-1 px-2" onclick="editTestimonial(${item.id})" title="Edit">
            <i class="bi bi-pencil-square"></i>
          </button>
          <button type="button" class="btn btn-sm btn-outline-danger p-1 px-2" onclick="deleteTestimonial(${item.id}, '${escapeHtml(item.name)}')" title="Hapus">
            <i class="bi bi-trash"></i>
          </button>
        </td>
      </tr>
    `;
  }).join('');
}

function openTestimonialModal(testi = null) {
  const form = document.getElementById('testiForm');
  form.reset();

  if (testi) {
    document.getElementById('testiModalTitle').innerText = 'Edit Testimoni';
    document.getElementById('testiId').value = testi.id;
    document.getElementById('formTestiName').value = testi.name;
    document.getElementById('formTestiCategory').value = testi.category;
    document.getElementById('formTestiRole').value = testi.role_title;
    document.getElementById('formTestiCompany').value = testi.company_name || '';
    document.getElementById('formTestiAvatar').value = testi.avatar_url || '';
    document.getElementById('formTestiRating').value = testi.rating;
    document.getElementById('formTestiProject').value = testi.project_name || '';
    document.getElementById('formTestiContent').value = testi.content;
    document.getElementById('formTestiIsFeatured').value = testi.is_featured;
    document.getElementById('formTestiIsActive').value = testi.is_active;
    document.getElementById('formTestiSort').value = testi.sort_order;
  } else {
    document.getElementById('testiModalTitle').innerText = 'Tambah Testimoni Baru';
    document.getElementById('testiId').value = '';
    document.getElementById('formTestiCategory').value = currentTestiCategory || 'investor';
    document.getElementById('formTestiRating').value = '5';
    document.getElementById('formTestiSort').value = allTestimonials.length + 1;
    document.getElementById('formTestiIsFeatured').value = '1';
    document.getElementById('formTestiIsActive').value = '1';
  }

  testiModalInstance.show();
}

function editTestimonial(id) {
  const item = allTestimonials.find(t => t.id == id);
  if (item) openTestimonialModal(item);
}

async function saveTestimonial(e) {
  e.preventDefault();
  const id = document.getElementById('testiId').value;
  const isEdit = !!id;

  const payload = {
    id: id ? parseInt(id) : undefined,
    name: document.getElementById('formTestiName').value,
    category: document.getElementById('formTestiCategory').value,
    role_title: document.getElementById('formTestiRole').value,
    company_name: document.getElementById('formTestiCompany').value,
    avatar_url: document.getElementById('formTestiAvatar').value,
    rating: parseInt(document.getElementById('formTestiRating').value) || 5,
    project_name: document.getElementById('formTestiProject').value,
    content: document.getElementById('formTestiContent').value,
    is_featured: parseInt(document.getElementById('formTestiIsFeatured').value) || 0,
    is_active: parseInt(document.getElementById('formTestiIsActive').value) || 0,
    sort_order: parseInt(document.getElementById('formTestiSort').value) || 0
  };

  const btn = document.getElementById('btnSaveTesti');
  const spinner = document.getElementById('saveTestiSpinner');
  btn.disabled = true;
  spinner.classList.remove('d-none');

  try {
    const res = await fetch('../api/admin/testimonials.php', {
      method: isEdit ? 'PUT' : 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const json = await res.json();
    if (!json.success) throw new Error(json.message || 'Gagal menyimpan.');

    showAlert(isEdit ? 'Testimoni berhasil diperbarui.' : 'Testimoni berhasil ditambahkan.');
    testiModalInstance.hide();
    loadTestimonialsList();
  } catch (err) {
    showAlert(err.message, 'danger');
  } finally {
    btn.disabled = false;
    spinner.classList.add('d-none');
  }
}

async function toggleTestiStatus(id) {
  try {
    const res = await fetch('../api/admin/testimonials.php', {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id, action: 'toggle_status' })
    });
    const json = await res.json();
    if (!json.success) throw new Error(json.message);
    showAlert('Status publikasi testimoni berhasil diubah.');
    loadTestimonialsList();
  } catch (err) {
    showAlert(err.message, 'danger');
  }
}

async function deleteTestimonial(id, name) {
  if (!confirm(`Hapus testimoni dari "${name}" secara permanen?`)) return;

  try {
    const res = await fetch('../api/admin/testimonials.php', {
      method: 'DELETE',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id })
    });
    const json = await res.json();
    if (!json.success) throw new Error(json.message);
    showAlert('Testimoni berhasil dihapus.');
    loadTestimonialsList();
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
