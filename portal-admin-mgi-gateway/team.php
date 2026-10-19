<?php
$pageTitle = 'Manajemen Dewan & Tim';
require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
  <div>
    <h4 class="fw-bold text-dark mb-1">Manajemen Dewan Direksi &amp; Tim</h4>
    <p class="text-muted small mb-0">Kelola jajaran pimpinan strategis, dewan pengawas, serta tim manajemen operasional ekosistem Montana Group.</p>
  </div>
  <button type="button" class="btn btn-mgi-gold shadow-sm d-flex align-items-center gap-2" onclick="openTeamModal()">
    <i class="bi bi-person-plus-fill"></i>
    <span>Tambah Anggota Tim</span>
  </button>
</div>

<!-- Alert Box -->
<div id="teamAlertContainer"></div>

<!-- Filter Tabs -->
<ul class="nav nav-pills mb-3 gap-2 bg-white p-2 rounded-3 border border-subtle shadow-sm flex-wrap" id="teamDeptFilter">
  <li class="nav-item">
    <button class="nav-link active fw-bold px-3 py-1.5" onclick="filterTeamDept('')">Semua Departemen</button>
  </li>
  <li class="nav-item">
    <button class="nav-link fw-bold px-3 py-1.5" onclick="filterTeamDept('Dewan Komisaris')">Dewan Komisaris</button>
  </li>
  <li class="nav-item">
    <button class="nav-link fw-bold px-3 py-1.5" onclick="filterTeamDept('Dewan Direksi')">Dewan Direksi</button>
  </li>
  <li class="nav-item">
    <button class="nav-link fw-bold px-3 py-1.5" onclick="filterTeamDept('Manajemen Operasional')">Manajemen Operasional</button>
  </li>
</ul>

<!-- Table Card -->
<div class="admin-card">
  <div class="admin-card-header bg-white d-flex align-items-center justify-content-between p-3 border-bottom">
    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
      <i class="bi bi-people-fill text-warning" style="color: #C5A059 !important;"></i>
      <span>Daftar Anggota Tim &amp; Pimpinan</span>
    </h6>
    <button class="btn btn-sm btn-outline-secondary" onclick="loadTeamList()">
      <i class="bi bi-arrow-clockwise me-1"></i> Refresh
    </button>
  </div>
  <div class="table-responsive">
    <table class="admin-table table-hover mb-0">
      <thead>
        <tr>
          <th>Pimpinan / Anggota</th>
          <th>Jabatan &amp; Departemen</th>
          <th>Bio Ringkas</th>
          <th>LinkedIn</th>
          <th class="text-center" style="width: 80px;">Urutan</th>
          <th>Status</th>
          <th class="text-end" style="width: 140px;">Aksi</th>
        </tr>
      </thead>
      <tbody id="teamTableBody">
        <tr>
          <td colspan="7" class="text-center py-4 text-muted">
            <span class="spinner-border spinner-border-sm me-2"></span> Memuat data tim...
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal Form Tambah / Edit Anggota Tim -->
<div class="modal fade" id="teamModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-3">
      <div class="modal-header bg-light border-bottom py-3 px-4">
        <h5 class="modal-title fw-bold text-dark mb-0" id="teamModalTitle">Tambah Anggota Tim</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="teamForm" onsubmit="saveTeamMember(event)">
        <input type="hidden" id="teamId" name="id" value="">
        <div class="modal-body p-4 space-y-3">
          
          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label small fw-bold text-dark">Nama Lengkap &amp; Gelar <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="formTeamName" name="name" placeholder="Contoh: Reyhan Pratama, S.E., M.M." required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-bold text-dark">Jabatan / Role Title <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="formTeamRole" name="role_title" placeholder="Contoh: Direktur Utama & Founder" required>
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label small fw-bold text-dark">Departemen / Divisi <span class="text-danger">*</span></label>
              <select class="form-select" id="formTeamDept" name="department" required>
                <option value="Dewan Direksi">Dewan Direksi</option>
                <option value="Dewan Komisaris">Dewan Komisaris</option>
                <option value="Manajemen Operasional">Manajemen Operasional</option>
                <option value="Investor Relations">Investor Relations</option>
                <option value="Keuangan & Kepatuhan">Keuangan &amp; Kepatuhan</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-bold text-dark">Urutan Tampil (Sort Order)</label>
              <input type="number" class="form-control" id="formTeamOrder" name="sort_order" value="1" min="0">
              <small class="text-muted">Angka lebih kecil tampil lebih dulu di depan.</small>
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label small fw-bold text-dark">URL Foto Profil</label>
              <input type="text" class="form-control" id="formTeamPhoto" name="photo_url" placeholder="Contoh: assets/img/team-leader-1.jpg">
              <small class="text-muted">Bisa URL internal atau eksternal gambar.</small>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-bold text-dark">Tautan Profil LinkedIn</label>
              <input type="url" class="form-control" id="formTeamLinkedin" name="linkedin_url" placeholder="https://linkedin.com/in/username">
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label small fw-bold text-dark">Biografi Ringkas &amp; Pengalaman</label>
            <textarea class="form-control" id="formTeamBio" name="bio" rows="3" placeholder="Tuliskan ringkasan latar belakang profesional dan keahlian..."></textarea>
          </div>

          <div class="form-check form-switch mt-3">
            <input class="form-check-input" type="checkbox" id="formTeamActive" name="is_active" checked>
            <label class="form-check-label small fw-bold text-dark" for="formTeamActive">Publikasikan di Website (Aktif)</label>
          </div>

        </div>
        <div class="modal-footer bg-light border-top px-4 py-3">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-mgi-primary px-4 fw-bold" id="btnSaveTeam">
            <i class="bi bi-check-circle-fill me-1"></i> Simpan Anggota Tim
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
let allTeamMembers = [];
let currentDeptFilter = '';
let teamModalInstance = null;

document.addEventListener('DOMContentLoaded', () => {
  const modalEl = document.getElementById('teamModal');
  if (modalEl) {
    teamModalInstance = new bootstrap.Modal(modalEl);
  }
  loadTeamList();
});

function showAlert(message, type = 'success') {
  const container = document.getElementById('teamAlertContainer');
  if (!container) return;
  const alertHtml = `
    <div class="alert alert-${type} alert-dismissible fade show shadow-sm d-flex align-items-center" role="alert">
      <i class="bi bi-${type === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill'} me-2 fs-5"></i>
      <div>${message}</div>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  `;
  container.innerHTML = alertHtml;
  setTimeout(() => {
    container.innerHTML = '';
  }, 4000);
}

async function loadTeamList() {
  const tbody = document.getElementById('teamTableBody');
  tbody.innerHTML = `
    <tr>
      <td colspan="7" class="text-center py-4 text-muted">
        <span class="spinner-border spinner-border-sm me-2"></span> Memuat data tim...
      </td>
    </tr>
  `;

  try {
    const res = await fetch('../api/admin/team.php');
    const result = await res.json();

    if (!res.ok || !result.success) {
      throw new Error(result.message || 'Gagal memuat data dari server');
    }

    allTeamMembers = result.data || [];
    renderTeamTable();
  } catch (err) {
    tbody.innerHTML = `
      <tr>
        <td colspan="7" class="text-center py-4 text-danger">
          <i class="bi bi-exclamation-triangle-fill me-1"></i> ${err.message}
          <div class="mt-2">
            <button class="btn btn-sm btn-outline-primary" onclick="loadTeamList()">Coba Lagi</button>
          </div>
        </td>
      </tr>
    `;
  }
}

function filterTeamDept(dept) {
  currentDeptFilter = dept;
  const buttons = document.querySelectorAll('#teamDeptFilter button');
  buttons.forEach(btn => {
    btn.classList.remove('active');
    if ((dept === '' && btn.innerText.includes('Semua')) || (dept !== '' && btn.innerText.includes(dept))) {
      btn.classList.add('active');
    }
  });
  renderTeamTable();
}

function renderTeamTable() {
  const tbody = document.getElementById('teamTableBody');
  let filtered = allTeamMembers;

  if (currentDeptFilter !== '') {
    filtered = allTeamMembers.filter(m => (m.department || '').toLowerCase() === currentDeptFilter.toLowerCase());
  }

  if (filtered.length === 0) {
    tbody.innerHTML = `
      <tr>
        <td colspan="7" class="text-center py-5 text-muted">
          <i class="bi bi-person-x fs-1 d-block mb-2 text-secondary"></i>
          Belum ada data anggota tim${currentDeptFilter ? ` untuk departemen "${currentDeptFilter}"` : ''}.
        </td>
      </tr>
    `;
    return;
  }

  tbody.innerHTML = filtered.map(m => {
    const isActive = parseInt(m.is_active) === 1;
    const photoImg = m.photo_url 
      ? `<img src="../${m.photo_url}" alt="${m.name}" class="rounded-circle object-fit-cover shadow-sm" style="width: 44px; height: 44px;" onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name=${encodeURIComponent(m.name)}&background=142563&color=fff';">`
      : `<div class="rounded-circle bg-royal text-white d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 44px; height: 44px; font-size: 1rem; background-color: #142563;">${(m.name || 'M').charAt(0)}</div>`;

    const linkedinBadge = m.linkedin_url 
      ? `<a href="${m.linkedin_url}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary py-0 px-2 rounded-pill small" title="Buka LinkedIn"><i class="bi bi-linkedin"></i> Profil</a>`
      : `<span class="text-muted small">-</span>`;

    return `
      <tr>
        <td>
          <div class="d-flex align-items-center gap-3">
            ${photoImg}
            <div>
              <div class="fw-bold text-dark">${m.name}</div>
              <div class="text-muted small">ID: #${m.id}</div>
            </div>
          </div>
        </td>
        <td>
          <div class="fw-semibold text-primary" style="color: #142563 !important;">${m.role_title}</div>
          <span class="badge bg-light text-dark border small mt-1">${m.department || 'Direksi'}</span>
        </td>
        <td style="max-width: 250px;">
          <div class="text-truncate text-secondary small" title="${m.bio || ''}">${m.bio || '-'}</div>
        </td>
        <td>${linkedinBadge}</td>
        <td class="text-center fw-bold text-secondary">${m.sort_order}</td>
        <td>
          <button class="badge border-0 ${isActive ? 'bg-success' : 'bg-secondary'} px-2 py-1 cursor-pointer" onclick="toggleTeamStatus(${m.id})">
            ${isActive ? 'Aktif' : 'Draft'}
          </button>
        </td>
        <td class="text-end">
          <div class="btn-group btn-group-sm">
            <button type="button" class="btn btn-outline-primary" onclick="openTeamModal(${m.id})" title="Edit">
              <i class="bi bi-pencil-fill"></i>
            </button>
            <button type="button" class="btn btn-outline-danger" onclick="deleteTeamMember(${m.id}, '${m.name.replace(/'/g, "\\'")}')" title="Hapus">
              <i class="bi bi-trash3-fill"></i>
            </button>
          </div>
        </td>
      </tr>
    `;
  }).join('');
}

function openTeamModal(id = null) {
  const form = document.getElementById('teamForm');
  form.reset();
  document.getElementById('teamId').value = '';
  document.getElementById('teamModalTitle').textContent = 'Tambah Anggota Tim';
  document.getElementById('formTeamOrder').value = '1';
  document.getElementById('formTeamActive').checked = true;

  if (id) {
    const member = allTeamMembers.find(m => parseInt(m.id) === parseInt(id));
    if (member) {
      document.getElementById('teamId').value = member.id;
      document.getElementById('teamModalTitle').textContent = 'Edit Anggota Tim #' + member.id;
      document.getElementById('formTeamName').value = member.name;
      document.getElementById('formTeamRole').value = member.role_title;
      document.getElementById('formTeamDept').value = member.department || 'Dewan Direksi';
      document.getElementById('formTeamOrder').value = member.sort_order;
      document.getElementById('formTeamPhoto').value = member.photo_url || '';
      document.getElementById('formTeamLinkedin').value = member.linkedin_url || '';
      document.getElementById('formTeamBio').value = member.bio || '';
      document.getElementById('formTeamActive').checked = parseInt(member.is_active) === 1;
    }
  }

  if (teamModalInstance) {
    teamModalInstance.show();
  }
}

async function saveTeamMember(e) {
  e.preventDefault();
  const btn = document.getElementById('btnSaveTeam');
  btn.disabled = true;
  btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...`;

  const id = document.getElementById('teamId').value;
  const isEdit = !!id;

  const payload = {
    id: id ? parseInt(id) : undefined,
    name: document.getElementById('formTeamName').value.trim(),
    role_title: document.getElementById('formTeamRole').value.trim(),
    department: document.getElementById('formTeamDept').value,
    sort_order: parseInt(document.getElementById('formTeamOrder').value) || 0,
    photo_url: document.getElementById('formTeamPhoto').value.trim(),
    linkedin_url: document.getElementById('formTeamLinkedin').value.trim(),
    bio: document.getElementById('formTeamBio').value.trim(),
    is_active: document.getElementById('formTeamActive').checked ? 1 : 0
  };

  try {
    const url = '../api/admin/team.php';
    const method = isEdit ? 'PUT' : 'POST';

    const res = await fetch(url, {
      method: method,
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });

    const result = await res.json();
    if (!res.ok || !result.success) {
      throw new Error(result.message || 'Gagal menyimpan data');
    }

    if (teamModalInstance) {
      teamModalInstance.hide();
    }
    showAlert(result.message || 'Data anggota tim berhasil disimpan.', 'success');
    loadTeamList();
  } catch (err) {
    showAlert(err.message, 'danger');
  } finally {
    btn.disabled = false;
    btn.innerHTML = `<i class="bi bi-check-circle-fill me-1"></i> Simpan Anggota Tim`;
  }
}

async function toggleTeamStatus(id) {
  try {
    const res = await fetch('../api/admin/team.php', {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: id, action: 'toggle_status' })
    });
    const result = await res.json();
    if (!res.ok || !result.success) {
      throw new Error(result.message || 'Gagal mengubah status');
    }
    showAlert('Status anggota tim diperbarui.', 'success');
    loadTeamList();
  } catch (err) {
    showAlert(err.message, 'danger');
  }
}

async function deleteTeamMember(id, name) {
  if (!confirm(`Apakah Anda yakin ingin menghapus anggota tim "${name}"?`)) {
    return;
  }

  try {
    const res = await fetch('../api/admin/team.php', {
      method: 'DELETE',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: id })
    });
    const result = await res.json();
    if (!res.ok || !result.success) {
      throw new Error(result.message || 'Gagal menghapus');
    }
    showAlert('Anggota tim berhasil dihapus.', 'success');
    loadTeamList();
  } catch (err) {
    showAlert(err.message, 'danger');
  }
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
