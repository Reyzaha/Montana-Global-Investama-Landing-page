<?php
$pageTitle = 'Kelola Segmen Kota';
require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
  <div>
    <h5 class="fw-bold text-dark mb-1">Manajemen Segmen Kota Investasi</h5>
    <p class="text-muted small mb-0">Kelola master data kota proyek dan tentukan kota mana saja yang aktif ditampilkan sebagai segmen/kategori di halaman publik investasi.</p>
  </div>
  <button type="button" class="btn btn-mgi-gold d-flex align-items-center gap-2 shadow-sm" onclick="openCreateCityModal()">
    <i class="bi bi-plus-circle-fill"></i>
    <span>Tambah Kota Baru</span>
  </button>
</div>

<!-- Stats Counter Summary Row -->
<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="admin-card p-3 d-flex align-items-center gap-3">
      <div class="rounded-3 p-2 bg-primary bg-opacity-10 text-primary fs-3">
        <i class="bi bi-geo-alt-fill"></i>
      </div>
      <div>
        <div class="fs-4 fw-bold text-dark" id="statTotalCities">0</div>
        <div class="text-muted small">Total Kota Terdaftar</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="admin-card p-3 d-flex align-items-center gap-3">
      <div class="rounded-3 p-2 bg-success bg-opacity-10 text-success fs-3">
        <i class="bi bi-check-circle-fill"></i>
      </div>
      <div>
        <div class="fs-4 fw-bold text-success" id="statActiveSegments">0</div>
        <div class="text-muted small">Segmen Aktif Publik</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="admin-card p-3 d-flex align-items-center gap-3">
      <div class="rounded-3 p-2 bg-warning bg-opacity-10 text-warning fs-3">
        <i class="bi bi-pause-circle-fill"></i>
      </div>
      <div>
        <div class="fs-4 fw-bold text-warning" id="statInactiveSegments">0</div>
        <div class="text-muted small">Segmen Nonaktif</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="admin-card p-3 d-flex align-items-center gap-3">
      <div class="rounded-3 p-2 bg-info bg-opacity-10 text-info fs-3">
        <i class="bi bi-briefcase-fill"></i>
      </div>
      <div>
        <div class="fs-4 fw-bold text-primary" id="statLinkedProjects">0</div>
        <div class="text-muted small">Total Proyek Terkait</div>
      </div>
    </div>
  </div>
</div>

<!-- Table Card -->
<div class="admin-card">
  <div class="admin-card-header bg-white d-flex flex-wrap align-items-center justify-content-between gap-3">
    <div class="d-flex align-items-center gap-2">
      <i class="bi bi-pin-map-fill text-primary" style="color: #142563 !important;"></i>
      <span class="fw-bold text-dark">Daftar Kota &amp; Pengaturan Segmen</span>
    </div>
    <div class="d-flex align-items-center gap-2">
      <input type="text" id="citySearch" class="form-control form-control-sm" placeholder="Cari nama kota..." style="width: 220px;" oninput="renderCitiesTable()">
      <select id="cityFilterStatus" class="form-select form-select-sm" style="width: 170px;" onchange="renderCitiesTable()">
        <option value="">Semua Status Segmen</option>
        <option value="1">Aktif di Segmen</option>
        <option value="0">Nonaktif</option>
      </select>
    </div>
  </div>

  <div class="table-responsive">
    <table class="admin-table">
      <thead>
        <tr>
          <th style="width: 60px;">Icon</th>
          <th>Nama Kota</th>
          <th>Slug ID</th>
          <th>Deskripsi Wilayah</th>
          <th class="text-center">Tampil di Segmen Publik</th>
          <th class="text-center">Proyek Terkait</th>
          <th class="text-center" style="width: 80px;">Urutan</th>
          <th class="text-end">Aksi</th>
        </tr>
      </thead>
      <tbody id="citiesTableBody">
        <tr>
          <td colspan="8" class="text-center py-5 text-muted">
            <span class="spinner-border spinner-border-sm me-2"></span> Memuat data kota &amp; segmen...
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</div>

<!-- MODAL TAMBAH KOTA BARU -->
<div class="modal fade" id="addCityModal" tabindex="-1" aria-labelledby="addCityModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-3">
      <div class="modal-header text-white" style="background: #142563;">
        <h6 class="modal-title fw-bold" id="addCityModalLabel">
          <i class="bi bi-plus-circle me-2 text-warning" style="color: #C5A059 !important;"></i> Tambah Kota Baru
        </h6>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="addCityForm" onsubmit="saveNewCity(event)">
        <div class="modal-body p-4">
          <div class="mb-3">
            <label class="form-label fw-semibold">Nama Kota <span class="text-danger">*</span></label>
            <input type="text" id="cityName" class="form-control" placeholder="Contoh: Surabaya, Balikpapan" required oninput="autoGenerateSlug(this.value, 'citySlug')">
          </div>
          <div class="row g-2 mb-3">
            <div class="col-md-7">
              <label class="form-label fw-semibold">Slug (ID URL)</label>
              <input type="text" id="citySlug" class="form-control" placeholder="surabaya">
              <small class="text-muted" style="font-size: 0.72rem;">Otomatis jika dibiarkan kosong.</small>
            </div>
            <div class="col-md-5">
              <label class="form-label fw-semibold">Icon</label>
              <select id="cityIcon" class="form-select">
                <option value="bi-geo-alt-fill">📍 Geo Alt</option>
                <option value="bi-building-gear">🏭 Building Gear</option>
                <option value="bi-water">🌊 Water / Port</option>
                <option value="bi-pin-map-fill">📌 Pin Map</option>
                <option value="bi-sun-fill">☀️ Sun / Tourism</option>
                <option value="bi-truck">🚚 Truck / Cargo</option>
                <option value="bi-compass">🧭 Compass</option>
              </select>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Deskripsi Singkat Wilayah / Portofolio</label>
            <textarea id="cityDesc" class="form-control" rows="2" placeholder="Fokus proyek, zona industri, atau jangkauan armada..."></textarea>
          </div>
          <div class="row g-2 mb-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Urutan Tampil (Sort Order)</label>
              <input type="number" id="citySort" class="form-control" value="0" min="0">
            </div>
            <div class="col-md-6 d-flex align-items-end">
              <div class="form-check form-switch mb-2">
                <input class="form-check-input" type="checkbox" id="cityActive" checked style="cursor: pointer;">
                <label class="form-check-label fw-bold text-dark" for="cityActive" style="cursor: pointer;">
                  Tampilkan di Segmen Publik
                </label>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary btn-sm px-4 fw-bold" style="background: #142563; border-color: #142563;" id="btnSubmitNewCity">
            Simpan Kota
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- MODAL EDIT KOTA -->
<div class="modal fade" id="editCityModal" tabindex="-1" aria-labelledby="editCityModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-3">
      <div class="modal-header text-white" style="background: #142563;">
        <h6 class="modal-title fw-bold" id="editCityModalLabel">
          <i class="bi bi-pencil-square me-2 text-warning" style="color: #C5A059 !important;"></i> Edit Data Kota
        </h6>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="editCityForm" onsubmit="saveEditCity(event)">
        <input type="hidden" id="editCityId">
        <div class="modal-body p-4">
          <div class="mb-3">
            <label class="form-label fw-semibold">Nama Kota <span class="text-danger">*</span></label>
            <input type="text" id="editCityName" class="form-control" required>
          </div>
          <div class="row g-2 mb-3">
            <div class="col-md-7">
              <label class="form-label fw-semibold">Slug (ID URL)</label>
              <input type="text" id="editCitySlug" class="form-control" required>
            </div>
            <div class="col-md-5">
              <label class="form-label fw-semibold">Icon</label>
              <select id="editCityIcon" class="form-select">
                <option value="bi-geo-alt-fill">📍 Geo Alt</option>
                <option value="bi-building-gear">🏭 Building Gear</option>
                <option value="bi-water">🌊 Water / Port</option>
                <option value="bi-pin-map-fill">📌 Pin Map</option>
                <option value="bi-sun-fill">☀️ Sun / Tourism</option>
                <option value="bi-truck">🚚 Truck / Cargo</option>
                <option value="bi-compass">🧭 Compass</option>
              </select>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Deskripsi Singkat Wilayah / Portofolio</label>
            <textarea id="editCityDesc" class="form-control" rows="2"></textarea>
          </div>
          <div class="row g-2 mb-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Urutan Tampil (Sort Order)</label>
              <input type="number" id="editCitySort" class="form-control" value="0" min="0">
            </div>
            <div class="col-md-6 d-flex align-items-end">
              <div class="form-check form-switch mb-2">
                <input class="form-check-input" type="checkbox" id="editCityActive" style="cursor: pointer;">
                <label class="form-check-label fw-bold text-dark" for="editCityActive" style="cursor: pointer;">
                  Tampilkan di Segmen Publik
                </label>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary btn-sm px-4 fw-bold" style="background: #142563; border-color: #142563;" id="btnSubmitEditCity">
            Simpan Perubahan
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
let allCities = [];

function autoGenerateSlug(name, targetId) {
  const slugInput = document.getElementById(targetId);
  if (!slugInput) return;
  slugInput.value = name.toLowerCase()
    .replace(/[^\w\s-]/g, '')
    .trim()
    .replace(/\s+/g, '-');
}

async function loadCities() {
  const tbody = document.getElementById('citiesTableBody');
  try {
    const res = await fetch('../api/admin/cities.php');
    const data = await res.json();

    if (!data.success && !Array.isArray(data)) {
      tbody.innerHTML = `<tr><td colspan="8" class="text-center py-4 text-danger">${data.message || 'Gagal memuat data kota.'}</td></tr>`;
      return;
    }

    allCities = Array.isArray(data) ? data : (data.data || []);
    updateSummaryStats();
    renderCitiesTable();
  } catch (err) {
    tbody.innerHTML = `<tr><td colspan="8" class="text-center py-4 text-danger">Gagal menghubungi server API: ${err.message}</td></tr>`;
  }
}

function updateSummaryStats() {
  const total = allCities.length;
  const active = allCities.filter(c => c.is_active_segment == 1).length;
  const inactive = total - active;
  const linked = allCities.reduce((acc, c) => acc + (parseInt(c.project_count) || 0), 0);

  document.getElementById('statTotalCities').textContent = total;
  document.getElementById('statActiveSegments').textContent = active;
  document.getElementById('statInactiveSegments').textContent = inactive;
  document.getElementById('statLinkedProjects').textContent = linked;
}

function renderCitiesTable() {
  const tbody = document.getElementById('citiesTableBody');
  const search = (document.getElementById('citySearch').value || '').toLowerCase().trim();
  const filterStatus = document.getElementById('cityFilterStatus').value;

  let filtered = allCities.filter(c => {
    const matchSearch = c.name.toLowerCase().includes(search) || (c.slug && c.slug.toLowerCase().includes(search));
    const matchStatus = (filterStatus === '') ? true : (String(c.is_active_segment) === filterStatus);
    return matchSearch && matchStatus;
  });

  if (filtered.length === 0) {
    tbody.innerHTML = `<tr><td colspan="8" class="text-center py-4 text-muted">Tidak ada data kota yang sesuai kriteria pencarian.</td></tr>`;
    return;
  }

  tbody.innerHTML = filtered.map(c => `
    <tr>
      <td class="text-center">
        <div class="d-inline-flex align-items-center justify-content-center bg-light border rounded p-2 text-primary" style="width: 38px; height: 38px; color: #142563 !important;">
          <i class="bi ${c.icon || 'bi-geo-alt-fill'} fs-5"></i>
        </div>
      </td>
      <td>
        <div class="fw-bold text-dark">${escapeHtml(c.name)}</div>
        <div class="small text-muted" style="font-size: 0.75rem;">Ditambahkan: ${c.created_at ? c.created_at.substring(0, 10) : '-'}</div>
      </td>
      <td>
        <span class="badge bg-light text-dark border font-monospace px-2 py-1">${escapeHtml(c.slug)}</span>
      </td>
      <td>
        <div class="text-secondary small" style="max-width: 280px; font-size: 0.78rem;">
          ${escapeHtml(c.description || '—')}
        </div>
      </td>
      <td class="text-center">
        <div class="form-check form-switch d-inline-block">
          <input class="form-check-input" type="checkbox" role="switch" id="toggle_${c.id}"
            ${c.is_active_segment == 1 ? 'checked' : ''}
            onchange="toggleCitySegment(${c.id})"
            style="cursor: pointer;">
        </div>
        <div class="small fw-semibold mt-1" style="font-size: 0.72rem;">
          ${c.is_active_segment == 1 
            ? '<span class="text-success"><i class="bi bi-check-circle me-1"></i>Tampil di Segmen</span>' 
            : '<span class="text-muted"><i class="bi bi-eye-slash me-1"></i>Disembunyikan</span>'}
        </div>
      </td>
      <td class="text-center">
        <a href="projects.php?filter_city=${encodeURIComponent(c.name)}" class="badge ${parseInt(c.project_count) > 0 ? 'bg-royal text-white' : 'bg-light text-muted border'} px-2.5 py-1 text-decoration-none" title="Lihat proyek kota ini">
          <i class="bi bi-briefcase me-1"></i> ${c.project_count || 0} Proyek
        </a>
      </td>
      <td class="text-center fw-bold text-muted">${c.sort_order || 0}</td>
      <td class="text-end">
        <div class="btn-group btn-group-sm">
          <button type="button" class="btn btn-outline-primary" onclick="openEditCityModal(${c.id})" title="Edit Kota">
            <i class="bi bi-pencil-fill"></i>
          </button>
          <button type="button" class="btn btn-outline-danger" onclick="deleteCity(${c.id}, '${escapeJs(c.name)}')" title="Hapus Kota">
            <i class="bi bi-trash-fill"></i>
          </button>
        </div>
      </td>
    </tr>
  `).join('');
}

function openCreateCityModal() {
  document.getElementById('addCityForm').reset();
  document.getElementById('cityActive').checked = true;
  document.getElementById('citySort').value = allCities.length + 1;
  const modal = new bootstrap.Modal(document.getElementById('addCityModal'));
  modal.show();
}

async function saveNewCity(e) {
  e.preventDefault();
  const btn = document.getElementById('btnSubmitNewCity');
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';

  const payload = {
    name: document.getElementById('cityName').value.trim(),
    slug: document.getElementById('citySlug').value.trim(),
    icon: document.getElementById('cityIcon').value,
    description: document.getElementById('cityDesc').value.trim(),
    sort_order: parseInt(document.getElementById('citySort').value) || 0,
    is_active_segment: document.getElementById('cityActive').checked ? 1 : 0
  };

  try {
    const res = await fetch('../api/admin/cities.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const result = await res.json();

    if (result.success) {
      bootstrap.Modal.getInstance(document.getElementById('addCityModal')).hide();
      alert('Berhasil: ' + result.message);
      loadCities();
    } else {
      alert('Gagal: ' + result.message);
    }
  } catch (err) {
    alert('Terjadi kesalahan koneksi: ' + err.message);
  } finally {
    btn.disabled = false;
    btn.innerHTML = 'Simpan Kota';
  }
}

function openEditCityModal(id) {
  const city = allCities.find(c => c.id == id);
  if (!city) return;

  document.getElementById('editCityId').value = city.id;
  document.getElementById('editCityName').value = city.name;
  document.getElementById('editCitySlug').value = city.slug;
  document.getElementById('editCityIcon').value = city.icon || 'bi-geo-alt-fill';
  document.getElementById('editCityDesc').value = city.description || '';
  document.getElementById('editCitySort').value = city.sort_order || 0;
  document.getElementById('editCityActive').checked = (city.is_active_segment == 1);

  const modal = new bootstrap.Modal(document.getElementById('editCityModal'));
  modal.show();
}

async function saveEditCity(e) {
  e.preventDefault();
  const btn = document.getElementById('btnSubmitEditCity');
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Memperbarui...';

  const id = document.getElementById('editCityId').value;
  const payload = {
    id: id,
    _method: 'PUT',
    name: document.getElementById('editCityName').value.trim(),
    slug: document.getElementById('editCitySlug').value.trim(),
    icon: document.getElementById('editCityIcon').value,
    description: document.getElementById('editCityDesc').value.trim(),
    sort_order: parseInt(document.getElementById('editCitySort').value) || 0,
    is_active_segment: document.getElementById('editCityActive').checked ? 1 : 0
  };

  try {
    const res = await fetch('../api/admin/cities.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const result = await res.json();

    if (result.success) {
      bootstrap.Modal.getInstance(document.getElementById('editCityModal')).hide();
      alert('Berhasil: ' + result.message);
      loadCities();
    } else {
      alert('Gagal: ' + result.message);
    }
  } catch (err) {
    alert('Terjadi kesalahan koneksi: ' + err.message);
  } finally {
    btn.disabled = false;
    btn.innerHTML = 'Simpan Perubahan';
  }
}

async function toggleCitySegment(id) {
  try {
    const res = await fetch('../api/admin/cities.php?action=toggle_segment', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: id })
    });
    const result = await res.json();
    if (result.success) {
      const city = allCities.find(c => c.id == id);
      if (city) {
        city.is_active_segment = result.is_active_segment;
      }
      updateSummaryStats();
      renderCitiesTable();
    } else {
      alert('Gagal mengubah status segmen: ' + result.message);
      loadCities();
    }
  } catch (err) {
    alert('Koneksi terputus: ' + err.message);
    loadCities();
  }
}

async function deleteCity(id, name) {
  if (!confirm(`Apakah Anda yakin ingin menghapus kota "${name}"?\n\nPerhatian: Proyek yang terkait dengan kota ini akan dilepas asosiasi kotanya.`)) {
    return;
  }

  try {
    const res = await fetch(`../api/admin/cities.php?action=delete&id=${id}`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' }
    });
    const result = await res.json();
    if (result.success) {
      alert(result.message);
      loadCities();
    } else {
      alert('Gagal: ' + result.message);
    }
  } catch (err) {
    alert('Terjadi kesalahan: ' + err.message);
  }
}

function escapeHtml(text) {
  if (!text) return '';
  return String(text).replace(/[&<>"']/g, function(m) {
    return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m];
  });
}

function escapeJs(text) {
  if (!text) return '';
  return String(text).replace(/['"\\]/g, '\\$&');
}

document.addEventListener('DOMContentLoaded', () => {
  loadCities();
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
