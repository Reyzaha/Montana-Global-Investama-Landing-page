<?php
$pageTitle = 'Pesan Masuk (Formulir Kontak)';
require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
  <div>
    <h4 class="fw-bold text-dark mb-1">Pesan Masuk &amp; Formulir Kontak</h4>
    <p class="text-muted small mb-0">Kelola pesan korespondensi, pertanyaan umum calon investor, dan permohonan konsultasi dari halaman kontak resmi.</p>
  </div>
  <div class="d-flex align-items-center gap-2">
    <a href="../api/admin/inquiries.php?export=csv" class="btn btn-outline-success shadow-sm d-flex align-items-center gap-2">
      <i class="bi bi-file-earmark-excel-fill"></i>
      <span>Export ke CSV / Excel</span>
    </a>
  </div>
</div>

<!-- Alert Box -->
<div id="inquiryAlertContainer"></div>

<!-- Metric Stat Cards -->
<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="p-3 bg-white rounded-3 border shadow-xs h-100 border-start border-4 border-primary">
      <div class="text-muted small fw-semibold">Total Pesan</div>
      <div class="fs-4 fw-bold text-dark mt-1" id="statTotal">0</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="p-3 bg-white rounded-3 border shadow-xs h-100 border-start border-4 border-warning">
      <div class="text-muted small fw-semibold">Belum Dibaca (Unread)</div>
      <div class="fs-4 fw-bold text-warning mt-1" id="statUnread">0</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="p-3 bg-white rounded-3 border shadow-xs h-100 border-start border-4 border-info">
      <div class="text-muted small fw-semibold">Telah Dihubungi</div>
      <div class="fs-4 fw-bold text-info mt-1" id="statContacted">0</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="p-3 bg-white rounded-3 border shadow-xs h-100 border-start border-4 border-secondary">
      <div class="text-muted small fw-semibold">Diarsipkan</div>
      <div class="fs-4 fw-bold text-secondary mt-1" id="statArchived">0</div>
    </div>
  </div>
</div>

<!-- Filter Tabs & Search -->
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3 bg-white p-2 rounded-3 border shadow-sm">
  <ul class="nav nav-pills gap-1" id="inquiryStatusTabs">
    <li class="nav-item">
      <button class="nav-link active fw-bold px-3 py-1.5" onclick="filterInquiryStatus('')">Semua Pesan</button>
    </li>
    <li class="nav-item">
      <button class="nav-link fw-bold px-3 py-1.5" onclick="filterInquiryStatus('unread')">
        <i class="bi bi-envelope-fill me-1 text-warning"></i> Belum Dibaca
      </button>
    </li>
    <li class="nav-item">
      <button class="nav-link fw-bold px-3 py-1.5" onclick="filterInquiryStatus('contacted')">
        <i class="bi bi-check2-all me-1 text-info"></i> Telah Dihubungi
      </button>
    </li>
    <li class="nav-item">
      <button class="nav-link fw-bold px-3 py-1.5" onclick="filterInquiryStatus('archived')">
        <i class="bi bi-archive-fill me-1 text-secondary"></i> Arsip
      </button>
    </li>
  </ul>
  <div class="d-flex align-items-center gap-2">
    <input type="text" id="inquirySearch" class="form-control form-control-sm" placeholder="Cari nama, email, subjek..." style="width: 240px;" oninput="debouncedSearch()">
    <button class="btn btn-sm btn-outline-secondary" onclick="loadInquiriesList()">
      <i class="bi bi-arrow-clockwise"></i>
    </button>
  </div>
</div>

<!-- Main Table Card -->
<div class="admin-card">
  <div class="table-responsive">
    <table class="admin-table table-hover mb-0">
      <thead>
        <tr>
          <th>Waktu Masuk</th>
          <th>Pengirim &amp; Kontak</th>
          <th>Subjek</th>
          <th>Cuplikan Pesan</th>
          <th>Status</th>
          <th class="text-end" style="width: 140px;">Aksi</th>
        </tr>
      </thead>
      <tbody id="inquiryTableBody">
        <tr>
          <td colspan="6" class="text-center py-4 text-muted">
            <span class="spinner-border spinner-border-sm me-2"></span> Memuat pesan masuk...
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal Detail Pesan -->
<div class="modal fade" id="inquiryDetailModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-3">
      <div class="modal-header bg-light border-bottom py-3 px-4">
        <h5 class="modal-title fw-bold text-dark mb-0">Detail Pesan Masuk #<span id="detailId"></span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4 space-y-3">
        <div class="row g-3 mb-3 p-3 bg-light rounded-3 border">
          <div class="col-md-6">
            <small class="text-muted d-block">Nama Pengirim:</small>
            <strong class="text-dark fs-6" id="detailName">-</strong>
          </div>
          <div class="col-md-6">
            <small class="text-muted d-block">Waktu Masuk:</small>
            <span class="text-dark" id="detailDate">-</span>
          </div>
          <div class="col-md-6">
            <small class="text-muted d-block">Alamat Email:</small>
            <a href="#" id="detailEmail" class="fw-semibold text-primary">-</a>
          </div>
          <div class="col-md-6">
            <small class="text-muted d-block">Nomor Telepon / WhatsApp:</small>
            <a href="#" id="detailPhone" class="fw-semibold text-success" target="_blank">-</a>
          </div>
        </div>

        <div class="mb-3">
          <small class="text-muted d-block">Perihal / Subjek:</small>
          <strong class="text-dark fs-6" id="detailSubject">-</strong>
        </div>

        <div class="mb-3">
          <small class="text-muted d-block mb-1">Isi Pesan:</small>
          <div class="p-3 bg-white border rounded-3 text-secondary lh-lg" id="detailMessage" style="min-height: 120px; white-space: pre-line;"></div>
        </div>

        <div class="d-flex align-items-center gap-3 pt-3 border-top">
          <label class="form-label small fw-bold text-dark mb-0">Ubah Status:</label>
          <select id="detailStatusSelect" class="form-select form-select-sm" style="width: 200px;">
            <option value="unread">Belum Dibaca (Unread)</option>
            <option value="read">Telah Dibaca (Read)</option>
            <option value="contacted">Telah Dihubungi (Contacted)</option>
            <option value="archived">Arsipkan (Archived)</option>
          </select>
          <button type="button" class="btn btn-sm btn-primary fw-bold" onclick="updateInquiryStatus()">
            Simpan Perubahan
          </button>
        </div>
      </div>
      <div class="modal-footer bg-light border-top px-4 py-2">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

<script>
let currentStatusFilter = '';
let searchTimeout = null;
let allInquiries = [];
let inquiryModalInstance = null;
let activeInquiryId = null;

document.addEventListener('DOMContentLoaded', () => {
  const modalEl = document.getElementById('inquiryDetailModal');
  if (modalEl) {
    inquiryModalInstance = new bootstrap.Modal(modalEl);
  }
  loadInquiriesList();
});

function showAlert(message, type = 'success') {
  const container = document.getElementById('inquiryAlertContainer');
  if (!container) return;
  container.innerHTML = `
    <div class="alert alert-${type} alert-dismissible fade show shadow-sm d-flex align-items-center" role="alert">
      <i class="bi bi-${type === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill'} me-2 fs-5"></i>
      <div>${message}</div>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  `;
  setTimeout(() => { container.innerHTML = ''; }, 4000);
}

function debouncedSearch() {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(loadInquiriesList, 300);
}

function filterInquiryStatus(status) {
  currentStatusFilter = status;
  const buttons = document.querySelectorAll('#inquiryStatusTabs button');
  buttons.forEach(btn => {
    btn.classList.remove('active');
    if ((status === '' && btn.innerText.includes('Semua')) || (status !== '' && btn.getAttribute('onclick').includes(status))) {
      btn.classList.add('active');
    }
  });
  loadInquiriesList();
}

async function loadInquiriesList() {
  const tbody = document.getElementById('inquiryTableBody');
  tbody.innerHTML = `
    <tr>
      <td colspan="6" class="text-center py-4 text-muted">
        <span class="spinner-border spinner-border-sm me-2"></span> Memuat pesan masuk...
      </td>
    </tr>
  `;

  const search = document.getElementById('inquirySearch').value.trim();
  let url = `../api/admin/inquiries.php?status=${encodeURIComponent(currentStatusFilter)}&search=${encodeURIComponent(search)}`;

  try {
    const res = await fetch(url);
    const result = await res.json();
    if (!res.ok || !result.success) {
      throw new Error(result.message || 'Gagal memuat pesan');
    }

    const counts = result.data.counts || {};
    document.getElementById('statTotal').textContent = counts.total || 0;
    document.getElementById('statUnread').textContent = counts.unread || 0;
    document.getElementById('statContacted').textContent = counts.contacted || 0;
    document.getElementById('statArchived').textContent = counts.archived || 0;

    allInquiries = result.data.items || [];
    renderInquiriesTable();
  } catch (err) {
    tbody.innerHTML = `
      <tr>
        <td colspan="6" class="text-center py-4 text-danger">
          <i class="bi bi-exclamation-triangle-fill me-1"></i> ${err.message}
          <div class="mt-2"><button class="btn btn-sm btn-outline-primary" onclick="loadInquiriesList()">Coba Lagi</button></div>
        </td>
      </tr>
    `;
  }
}

function renderInquiriesTable() {
  const tbody = document.getElementById('inquiryTableBody');
  if (allInquiries.length === 0) {
    tbody.innerHTML = `
      <tr>
        <td colspan="6" class="text-center py-5 text-muted">
          <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
          Belum ada pesan masuk${currentStatusFilter ? ` dengan status "${currentStatusFilter}"` : ''}.
        </td>
      </tr>
    `;
    return;
  }

  tbody.innerHTML = allInquiries.map(item => {
    let statusBadge = '<span class="badge bg-secondary">Unread</span>';
    if (item.status === 'unread') statusBadge = '<span class="badge bg-warning text-dark">Belum Dibaca</span>';
    else if (item.status === 'read') statusBadge = '<span class="badge bg-light text-dark border">Dibaca</span>';
    else if (item.status === 'contacted') statusBadge = '<span class="badge bg-info text-dark">Dihubungi</span>';
    else if (item.status === 'archived') statusBadge = '<span class="badge bg-secondary">Arsip</span>';

    const isUnread = item.status === 'unread';
    const rowClass = isUnread ? 'table-warning bg-opacity-10 fw-semibold' : '';

    return `
      <tr class="${rowClass}">
        <td class="small text-muted">${(item.created_at || '').substring(0, 16)}</td>
        <td>
          <div class="fw-bold text-dark">${item.full_name}</div>
          <div class="small text-muted">${item.email}</div>
          ${item.phone ? `<div class="small text-secondary"><i class="bi bi-telephone me-1"></i>${item.phone}</div>` : ''}
        </td>
        <td>
          <div class="text-truncate text-dark fw-bold" style="max-width: 200px;" title="${item.subject || ''}">
            ${item.subject || 'Konsultasi'}
          </div>
        </td>
        <td>
          <div class="text-truncate text-secondary small" style="max-width: 280px;" title="${item.message}">
            ${item.message}
          </div>
        </td>
        <td>${statusBadge}</td>
        <td class="text-end">
          <div class="btn-group btn-group-sm">
            <button type="button" class="btn btn-outline-primary" onclick="openInquiryDetail(${item.id})" title="Lihat Detail Pesan">
              <i class="bi bi-eye-fill"></i>
            </button>
            <button type="button" class="btn btn-outline-danger" onclick="deleteInquiry(${item.id}, '${item.full_name.replace(/'/g, "\\'")}')" title="Hapus">
              <i class="bi bi-trash3-fill"></i>
            </button>
          </div>
        </td>
      </tr>
    `;
  }).join('');
}

function openInquiryDetail(id) {
  const item = allInquiries.find(i => parseInt(i.id) === parseInt(id));
  if (!item) return;

  activeInquiryId = item.id;
  document.getElementById('detailId').textContent = item.id;
  document.getElementById('detailName').textContent = item.full_name;
  document.getElementById('detailDate').textContent = item.created_at;
  
  const emailEl = document.getElementById('detailEmail');
  emailEl.textContent = item.email;
  emailEl.href = 'mailto:' + item.email;

  const phoneEl = document.getElementById('detailPhone');
  if (item.phone) {
    const cleanPhone = item.phone.replace(/[^\d]/g, '');
    phoneEl.textContent = item.phone;
    phoneEl.href = `https://wa.me/${cleanPhone.startsWith('0') ? '62' + cleanPhone.slice(1) : cleanPhone}`;
  } else {
    phoneEl.textContent = '-';
    phoneEl.removeAttribute('href');
  }

  document.getElementById('detailSubject').textContent = item.subject || 'Konsultasi Investasi Proyek';
  document.getElementById('detailMessage').textContent = item.message;
  document.getElementById('detailStatusSelect').value = item.status;

  // Auto-mark as read if was unread
  if (item.status === 'unread') {
    fetch('../api/admin/inquiries.php', {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: item.id, status: 'read' })
    }).then(() => {
      item.status = 'read';
    });
  }

  if (inquiryModalInstance) {
    inquiryModalInstance.show();
  }
}

async function updateInquiryStatus() {
  if (!activeInquiryId) return;
  const newStatus = document.getElementById('detailStatusSelect').value;

  try {
    const res = await fetch('../api/admin/inquiries.php', {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: activeInquiryId, status: newStatus })
    });
    const json = await res.json();
    if (!res.ok || !json.success) throw new Error(json.message || 'Gagal mengubah status');

    if (inquiryModalInstance) inquiryModalInstance.hide();
    showAlert('Status pesan berhasil diperbarui.', 'success');
    loadInquiriesList();
  } catch (err) {
    alert('Gagal: ' + err.message);
  }
}

async function deleteInquiry(id, name) {
  if (!confirm(`Apakah Anda yakin ingin menghapus pesan dari "${name}"?`)) return;

  try {
    const res = await fetch('../api/admin/inquiries.php', {
      method: 'DELETE',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: id })
    });
    const json = await res.json();
    if (!res.ok || !json.success) throw new Error(json.message || 'Gagal menghapus');

    showAlert('Pesan berhasil dihapus.', 'success');
    loadInquiriesList();
  } catch (err) {
    showAlert(err.message, 'danger');
  }
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
