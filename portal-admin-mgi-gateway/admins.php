<?php
$pageTitle = 'Manajemen Administrator';
require_once __DIR__ . '/includes/header.php';

// Pastikan hanya Super Administrator yang dapat membuka halaman ini
if (($adminUser['role'] ?? '') !== 'superadmin') {
    echo '<div class="alert alert-danger p-4 rounded-3"><i class="bi bi-shield-x me-2"></i> Akses ditolak. Halaman ini hanya untuk Super Administrator.</div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}
?>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
  <div>
    <h5 class="fw-bold text-dark mb-1">Manajemen Administrator &amp; Akses Tim</h5>
    <p class="text-muted small mb-0">Kelola akun administrator, tentukan hak akses peran (Role), reset MFA, atau buat admin baru.</p>
  </div>
  <button type="button" class="btn btn-mgi-primary d-flex align-items-center gap-2 shadow-sm" onclick="openCreateModal()">
    <i class="bi bi-person-plus-fill"></i>
    <span>Tambah Admin Baru</span>
  </button>
</div>

<!-- Admin Users Table Card -->
<div class="admin-card">
  <div class="admin-card-header bg-white d-flex flex-wrap align-items-center justify-content-between gap-3">
    <div class="d-flex align-items-center gap-2">
      <i class="bi bi-people-fill text-primary" style="color: #142563 !important;"></i>
      <span class="fw-bold text-dark">Daftar Akun Administrator</span>
      <span class="badge bg-light text-dark border ms-2" id="adminCountBadge">0 Admin</span>
    </div>

    <div class="d-flex align-items-center gap-2">
      <input type="text" id="searchAdmin" class="form-control form-control-sm" placeholder="Cari nama, username, email..." style="width: 260px;" oninput="filterAdmins()">
    </div>
  </div>

  <div class="table-responsive">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Administrator</th>
          <th>Username</th>
          <th>Peran (Role)</th>
          <th>Status Akun</th>
          <th>Status MFA</th>
          <th>Login Terakhir</th>
          <th class="text-end">Aksi</th>
        </tr>
      </thead>
      <tbody id="adminsTableBody">
        <tr>
          <td colspan="7" class="text-center py-5 text-muted">
            <span class="spinner-border spinner-border-sm me-2"></span> Memuat daftar administrator...
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</div>

<!-- MODAL TAMBAH ADMIN BARU -->
<div class="modal fade" id="createAdminModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      <div class="modal-header text-white" style="background: #142563;">
        <h5 class="modal-title fw-bold">
          <i class="bi bi-person-plus-fill me-2 text-warning" style="color: #C5A059 !important;"></i>
          <span>Tambah Administrator Baru</span>
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form id="createAdminForm" onsubmit="submitCreateAdmin(event)">
        <div class="modal-body p-4">
          <div id="createAdminAlert" class="alert alert-danger d-none py-2 small mb-3"></div>

          <div class="mb-3">
            <label class="form-label fw-bold small">Nama Lengkap <span class="text-danger">*</span></label>
            <input type="text" id="createFullName" class="form-control" placeholder="Contoh: Ahmad Fauzi, S.Kom." required>
          </div>

          <div class="row g-2 mb-3">
            <div class="col-md-6">
              <label class="form-label fw-bold small">Username <span class="text-danger">*</span></label>
              <input type="text" id="createUsername" class="form-control" placeholder="admin_fauzi" required autocomplete="off">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-bold small">Peran (Role) <span class="text-danger">*</span></label>
              <select id="createRole" class="form-select" required>
                <option value="admin" selected>Admin (Kelola Konten &amp; Investor)</option>
                <option value="superadmin">Superadmin (Akses Penuh + Kelola Admin)</option>
                <option value="editor">Editor (Hanya CMS Konten)</option>
              </select>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold small">Alamat Email Resmi <span class="text-danger">*</span></label>
            <input type="email" id="createEmail" class="form-control" placeholder="fauzi@montanaglobalinvestama.com" required>
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold small">Kata Sandi Awal <span class="text-danger">*</span></label>
            <div class="input-group">
              <input type="password" id="createPassword" class="form-control" placeholder="Minimal 8 karakter" required minlength="8">
              <button class="btn btn-outline-secondary" type="button" onclick="togglePassVisibility('createPassword', this)">
                <i class="bi bi-eye"></i>
              </button>
            </div>
            <small class="text-muted">Kombinasikan huruf besar, kecil, angka, dan simbol.</small>
          </div>

          <div class="p-3 bg-light rounded-3 border">
            <div class="form-check form-switch mb-0">
              <input class="form-check-input" type="checkbox" id="createIsActive" checked>
              <label class="form-check-label fw-semibold small" for="createIsActive">
                Status Akun Aktif (Langsung dapat digunakan untuk login)
              </label>
            </div>
          </div>
        </div>

        <div class="modal-footer bg-light px-4 py-3">
          <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
          <button type="submit" id="btnSubmitCreate" class="btn btn-mgi-primary btn-sm px-4">
            <i class="bi bi-check2-circle me-1"></i> Buat Akun Admin
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- MODAL EDIT ADMIN -->
<div class="modal fade" id="editAdminModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      <div class="modal-header text-white" style="background: #142563;">
        <h5 class="modal-title fw-bold">
          <i class="bi bi-person-gear me-2 text-warning" style="color: #C5A059 !important;"></i>
          <span>Edit Administrator: <span id="editAdminTitleUsername">-</span></span>
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form id="editAdminForm" onsubmit="submitEditAdmin(event)">
        <input type="hidden" id="editAdminId">
        <div class="modal-body p-4">
          <div id="editAdminAlert" class="alert alert-danger d-none py-2 small mb-3"></div>

          <div class="mb-3">
            <label class="form-label fw-bold small">Nama Lengkap</label>
            <input type="text" id="editFullName" class="form-control" required>
          </div>

          <div class="row g-2 mb-3">
            <div class="col-md-6">
              <label class="form-label fw-bold small">Alamat Email</label>
              <input type="email" id="editEmail" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-bold small">Peran (Role)</label>
              <select id="editRole" class="form-select" required>
                <option value="admin">Admin</option>
                <option value="superadmin">Superadmin</option>
                <option value="editor">Editor</option>
              </select>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold small">Ganti Kata Sandi (Kosongkan jika tidak ingin diubah)</label>
            <div class="input-group">
              <input type="password" id="editPassword" class="form-control" placeholder="••••••••" minlength="8">
              <button class="btn btn-outline-secondary" type="button" onclick="togglePassVisibility('editPassword', this)">
                <i class="bi bi-eye"></i>
              </button>
            </div>
            <small class="text-muted">Isi hanya jika admin meminta reset password baru.</small>
          </div>

          <div class="p-3 bg-light rounded-3 border mb-3">
            <div class="form-check form-switch mb-0">
              <input class="form-check-input" type="checkbox" id="editIsActive">
              <label class="form-check-label fw-semibold small" for="editIsActive">
                Status Akun Aktif
              </label>
            </div>
          </div>

          <div class="border-top pt-3">
            <label class="form-label fw-bold small d-block mb-1 text-secondary">Pengaturan Keamanan MFA</label>
            <div class="d-flex align-items-center justify-content-between p-2 rounded bg-light border">
              <div>
                <small class="d-block text-dark fw-semibold" id="editMfaStatusText">Status: Aktif</small>
                <small class="text-muted">Reset jika admin kehilangan akses Google Authenticator.</small>
              </div>
              <button type="button" class="btn btn-sm btn-outline-warning text-dark fw-semibold" onclick="resetAdminMfa()">
                <i class="bi bi-arrow-counterclockwise me-1"></i> Reset MFA
              </button>
            </div>
          </div>
        </div>

        <div class="modal-footer bg-light px-4 py-3">
          <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
          <button type="submit" id="btnSubmitEdit" class="btn btn-mgi-primary btn-sm px-4">
            <i class="bi bi-check2-circle me-1"></i> Simpan Perubahan
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  let allAdmins = [];
  const currentLoggedId = <?= (int)($adminUser['id'] ?? 0) ?>;

  document.addEventListener('DOMContentLoaded', loadAdmins);

  async function loadAdmins() {
    try {
      const res = await fetch('../api/admin/admins.php');
      const json = await res.json();

      if (json.success && Array.isArray(json.data)) {
        allAdmins = json.data;
        renderAdminsTable(allAdmins);
      } else {
        document.getElementById('adminsTableBody').innerHTML = `
          <tr><td colspan="7" class="text-center text-danger py-4">${json.message || 'Gagal memuat data.'}</td></tr>
        `;
      }
    } catch (e) {
      document.getElementById('adminsTableBody').innerHTML = `
        <tr><td colspan="7" class="text-center text-danger py-4">Koneksi ke backend gagal.</td></tr>
      `;
    }
  }

  function renderAdminsTable(admins) {
    const tbody = document.getElementById('adminsTableBody');
    document.getElementById('adminCountBadge').textContent = `${admins.length} Admin`;

    if (!admins.length) {
      tbody.innerHTML = `<tr><td colspan="7" class="text-center text-muted py-4">Tidak ada data administrator.</td></tr>`;
      return;
    }

    tbody.innerHTML = admins.map(a => {
      const isMe = Number(a.id) === currentLoggedId;
      const isSuper = a.role === 'superadmin';
      const roleBadge = isSuper 
        ? '<span class="badge bg-royal text-white px-2 py-1"><i class="bi bi-star-fill text-warning me-1"></i> Superadmin</span>'
        : (a.role === 'admin' 
            ? '<span class="badge bg-primary text-white px-2 py-1" style="background: #1D3589 !important;">Admin</span>' 
            : '<span class="badge bg-secondary text-white px-2 py-1">Editor</span>');

      const statusBadge = a.is_active == 1
        ? '<span class="badge bg-success text-white px-2 py-1"><i class="bi bi-check-circle me-1"></i> Aktif</span>'
        : '<span class="badge bg-danger text-white px-2 py-1"><i class="bi bi-x-circle me-1"></i> Nonaktif</span>';

      const mfaBadge = a.mfa_enabled == 1
        ? '<span class="badge bg-info text-dark px-2 py-1"><i class="bi bi-shield-check me-1"></i> Terkunci (MFA Aktif)</span>'
        : '<span class="badge bg-warning text-dark px-2 py-1"><i class="bi bi-qr-code me-1"></i> Perlu Scan QR</span>';

      const lastLogin = a.last_login ? new Date(a.last_login).toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' }) : '<span class="text-muted fst-italic">Belum pernah login</span>';

      return `
        <tr>
          <td>
            <div class="d-flex align-items-center gap-2">
              <div class="rounded-circle bg-light border d-flex align-items-center justify-content-center fw-bold text-dark" style="width: 38px; height: 38px;">
                ${a.username.charAt(0).toUpperCase()}
              </div>
              <div>
                <div class="fw-bold text-dark mb-0">${escapeHtml(a.full_name || a.username)} ${isMe ? '<span class="badge bg-warning text-dark ms-1" style="font-size: 0.65rem;">Akun Anda</span>' : ''}</div>
                <small class="text-muted">${escapeHtml(a.email)}</small>
              </div>
            </div>
          </td>
          <td><code class="fw-bold text-primary">${escapeHtml(a.username)}</code></td>
          <td>${roleBadge}</td>
          <td>${statusBadge}</td>
          <td>${mfaBadge}</td>
          <td class="small text-muted">${lastLogin}</td>
          <td class="text-end">
            <div class="btn-group btn-group-sm">
              <button class="btn btn-outline-secondary" title="Edit Admin" onclick="openEditModal(${a.id})">
                <i class="bi bi-pencil-fill"></i>
              </button>
              ${!isMe ? `
                <button class="btn btn-outline-danger" title="Hapus Admin" onclick="deleteAdmin(${a.id}, '${escapeHtml(a.username)}')">
                  <i class="bi bi-trash-fill"></i>
                </button>
              ` : ''}
            </div>
          </td>
        </tr>
      `;
    }).join('');
  }

  function filterAdmins() {
    const q = document.getElementById('searchAdmin').value.toLowerCase().trim();
    if (!q) {
      renderAdminsTable(allAdmins);
      return;
    }
    const filtered = allAdmins.filter(a => 
      (a.username && a.username.toLowerCase().includes(q)) ||
      (a.full_name && a.full_name.toLowerCase().includes(q)) ||
      (a.email && a.email.toLowerCase().includes(q)) ||
      (a.role && a.role.toLowerCase().includes(q))
    );
    renderAdminsTable(filtered);
  }

  function openCreateModal() {
    document.getElementById('createAdminForm').reset();
    document.getElementById('createIsActive').checked = true;
    document.getElementById('createAdminAlert').classList.add('d-none');
    new bootstrap.Modal(document.getElementById('createAdminModal')).show();
  }

  async function submitCreateAdmin(e) {
    e.preventDefault();
    const alertBox = document.getElementById('createAdminAlert');
    const btn = document.getElementById('btnSubmitCreate');
    alertBox.classList.add('d-none');

    const payload = {
      username: document.getElementById('createUsername').value.trim(),
      full_name: document.getElementById('createFullName').value.trim(),
      email: document.getElementById('createEmail').value.trim(),
      role: document.getElementById('createRole').value,
      password: document.getElementById('createPassword').value,
      is_active: document.getElementById('createIsActive').checked ? 1 : 0
    };

    btn.disabled = true;
    btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...`;

    try {
      const res = await fetch('../api/admin/admins.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      const json = await res.json();

      if (json.success) {
        bootstrap.Modal.getInstance(document.getElementById('createAdminModal')).hide();
        AdminApp.showToast(json.message || 'Administrator baru berhasil dibuat!', 'success');
        loadAdmins();
      } else {
        alertBox.textContent = json.message || 'Gagal membuat admin.';
        alertBox.classList.remove('d-none');
      }
    } catch (err) {
      alertBox.textContent = 'Terjadi kesalahan sistem saat menghubungi server.';
      alertBox.classList.remove('d-none');
    } finally {
      btn.disabled = false;
      btn.innerHTML = `<i class="bi bi-check2-circle me-1"></i> Buat Akun Admin`;
    }
  }

  function openEditModal(id) {
    const admin = allAdmins.find(a => Number(a.id) === Number(id));
    if (!admin) return;

    document.getElementById('editAdminId').value = admin.id;
    document.getElementById('editAdminTitleUsername').textContent = admin.username;
    document.getElementById('editFullName').value = admin.full_name || '';
    document.getElementById('editEmail').value = admin.email || '';
    document.getElementById('editRole').value = admin.role || 'admin';
    document.getElementById('editPassword').value = '';
    document.getElementById('editIsActive').checked = Number(admin.is_active) === 1;
    document.getElementById('editAdminAlert').classList.add('d-none');

    // Jika akun sendiri, disable field role dan status aktif
    const isMe = Number(admin.id) === currentLoggedId;
    document.getElementById('editRole').disabled = isMe;
    document.getElementById('editIsActive').disabled = isMe;

    const mfaStatusText = Number(admin.mfa_enabled) === 1 ? 'Status: Terkunci (Google Authenticator Aktif)' : 'Status: Menunggu Scan QR Code Pertama';
    document.getElementById('editMfaStatusText').textContent = mfaStatusText;

    new bootstrap.Modal(document.getElementById('editAdminModal')).show();
  }

  async function submitEditAdmin(e) {
    e.preventDefault();
    const alertBox = document.getElementById('editAdminAlert');
    const btn = document.getElementById('btnSubmitEdit');
    alertBox.classList.add('d-none');

    const id = document.getElementById('editAdminId').value;
    const payload = {
      id: id,
      full_name: document.getElementById('editFullName').value.trim(),
      email: document.getElementById('editEmail').value.trim(),
      role: document.getElementById('editRole').value,
      is_active: document.getElementById('editIsActive').checked ? 1 : 0
    };

    const pass = document.getElementById('editPassword').value;
    if (pass) {
      payload.password = pass;
    }

    btn.disabled = true;
    btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...`;

    try {
      const res = await fetch('../api/admin/admins.php', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      const json = await res.json();

      if (json.success) {
        bootstrap.Modal.getInstance(document.getElementById('editAdminModal')).hide();
        AdminApp.showToast(json.message || 'Perubahan administrator berhasil disimpan!', 'success');
        loadAdmins();
      } else {
        alertBox.textContent = json.message || 'Gagal menyimpan perubahan.';
        alertBox.classList.remove('d-none');
      }
    } catch (err) {
      alertBox.textContent = 'Terjadi kesalahan sistem saat menghubungi server.';
      alertBox.classList.remove('d-none');
    } finally {
      btn.disabled = false;
      btn.innerHTML = `<i class="bi bi-check2-circle me-1"></i> Simpan Perubahan`;
    }
  }

  async function resetAdminMfa() {
    const id = document.getElementById('editAdminId').value;
    const username = document.getElementById('editAdminTitleUsername').textContent;
    if (!confirm(`Reset Google Authenticator untuk admin '${username}'?\n\nAdmin akan diwajibkan melakukan scan QR Code baru pada login berikutnya.`)) {
      return;
    }

    try {
      const res = await fetch(`../api/admin/admins.php?action=reset_mfa`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: id })
      });
      const json = await res.json();

      if (json.success) {
        AdminApp.showToast(json.message || 'MFA berhasil direset!', 'success');
        bootstrap.Modal.getInstance(document.getElementById('editAdminModal')).hide();
        loadAdmins();
      } else {
        alert(json.message || 'Gagal mereset MFA.');
      }
    } catch (e) {
      alert('Terjadi kesalahan sistem.');
    }
  }

  async function deleteAdmin(id, username) {
    if (!confirm(`Apakah Anda yakin ingin MENGHAPUS permanen akun admin '${username}'?\nTindakan ini tidak dapat dibatalkan.`)) {
      return;
    }

    try {
      const res = await fetch(`../api/admin/admins.php?id=${id}`, {
        method: 'DELETE'
      });
      const json = await res.json();

      if (json.success) {
        AdminApp.showToast(json.message || 'Admin berhasil dihapus!', 'success');
        loadAdmins();
      } else {
        AdminApp.showToast(json.message || 'Gagal menghapus admin.', 'danger');
      }
    } catch (e) {
      AdminApp.showToast('Koneksi ke backend gagal.', 'danger');
    }
  }

  function togglePassVisibility(inputId, btn) {
    const input = document.getElementById(inputId);
    const icon = btn.querySelector('i');
    if (input.type === 'password') {
      input.type = 'text';
      icon.className = 'bi bi-eye-slash';
    } else {
      input.type = 'password';
      icon.className = 'bi bi-eye';
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
