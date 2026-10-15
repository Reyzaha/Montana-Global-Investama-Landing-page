<?php
$pageTitle = 'Subscriber Newsletter';
require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
  <div>
    <h4 class="fw-bold text-dark mb-1">Subscriber Newsletter &amp; Buletin</h4>
    <p class="text-muted small mb-0">Kelola daftar email peminat buletin wawasan pasar alat berat dan rilis proyek investasi berkala.</p>
  </div>
  <div class="d-flex align-items-center gap-2">
    <a href="../api/admin/newsletter.php?export=csv" class="btn btn-outline-success shadow-sm d-flex align-items-center gap-2">
      <i class="bi bi-file-earmark-excel-fill"></i>
      <span>Export ke CSV / Excel</span>
    </a>
  </div>
</div>

<!-- Alert Box -->
<div id="newsletterAlertContainer"></div>

<!-- Metric Stat Cards -->
<div class="row g-3 mb-4">
  <div class="col-12 col-md-4">
    <div class="p-3 bg-white rounded-3 border shadow-xs h-100 border-start border-4 border-primary">
      <div class="text-muted small fw-semibold">Total Pelanggan</div>
      <div class="fs-4 fw-bold text-dark mt-1" id="statTotalSub">0</div>
    </div>
  </div>
  <div class="col-12 col-md-4">
    <div class="p-3 bg-white rounded-3 border shadow-xs h-100 border-start border-4 border-success">
      <div class="text-muted small fw-semibold">Langganan Aktif</div>
      <div class="fs-4 fw-bold text-success mt-1" id="statActiveSub">0</div>
    </div>
  </div>
  <div class="col-12 col-md-4">
    <div class="p-3 bg-white rounded-3 border shadow-xs h-100 border-start border-4 border-secondary">
      <div class="text-muted small fw-semibold">Berhenti Berlangganan (Unsubscribed)</div>
      <div class="fs-4 fw-bold text-secondary mt-1" id="statUnsub">0</div>
    </div>
  </div>
</div>

<!-- Filter Tabs & Search -->
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3 bg-white p-2 rounded-3 border shadow-sm">
  <ul class="nav nav-pills gap-1" id="newsletterStatusTabs">
    <li class="nav-item">
      <button class="nav-link active fw-bold px-3 py-1.5" onclick="filterSubStatus('')">Semua Status</button>
    </li>
    <li class="nav-item">
      <button class="nav-link fw-bold px-3 py-1.5" onclick="filterSubStatus('active')">
        <i class="bi bi-check-circle-fill me-1 text-success"></i> Aktif
      </button>
    </li>
    <li class="nav-item">
      <button class="nav-link fw-bold px-3 py-1.5" onclick="filterSubStatus('unsubscribed')">
        <i class="bi bi-dash-circle-fill me-1 text-secondary"></i> Unsubscribed
      </button>
    </li>
  </ul>
  <div class="d-flex align-items-center gap-2">
    <input type="text" id="newsletterSearch" class="form-control form-control-sm" placeholder="Cari email..." style="width: 240px;" oninput="debouncedSearch()">
    <button class="btn btn-sm btn-outline-secondary" onclick="loadSubscribersList()">
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
          <th>Alamat Email</th>
          <th>Status</th>
          <th>Halaman Sumber</th>
          <th>Tanggal Mendaftar</th>
          <th class="text-end" style="width: 140px;">Aksi</th>
        </tr>
      </thead>
      <tbody id="subscriberTableBody">
        <tr>
          <td colspan="5" class="text-center py-4 text-muted">
            <span class="spinner-border spinner-border-sm me-2"></span> Memuat subscriber...
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</div>

<script>
let currentSubStatusFilter = '';
let searchSubTimeout = null;
let allSubscribers = [];

document.addEventListener('DOMContentLoaded', () => {
  loadSubscribersList();
});

function showAlert(message, type = 'success') {
  const container = document.getElementById('newsletterAlertContainer');
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
  clearTimeout(searchSubTimeout);
  searchSubTimeout = setTimeout(loadSubscribersList, 300);
}

function filterSubStatus(status) {
  currentSubStatusFilter = status;
  const buttons = document.querySelectorAll('#newsletterStatusTabs button');
  buttons.forEach(btn => {
    btn.classList.remove('active');
    if ((status === '' && btn.innerText.includes('Semua')) || (status !== '' && btn.getAttribute('onclick').includes(status))) {
      btn.classList.add('active');
    }
  });
  loadSubscribersList();
}

async function loadSubscribersList() {
  const tbody = document.getElementById('subscriberTableBody');
  tbody.innerHTML = `
    <tr>
      <td colspan="5" class="text-center py-4 text-muted">
        <span class="spinner-border spinner-border-sm me-2"></span> Memuat subscriber...
      </td>
    </tr>
  `;

  const search = document.getElementById('newsletterSearch').value.trim();
  let url = `../api/admin/newsletter.php?status=${encodeURIComponent(currentSubStatusFilter)}&search=${encodeURIComponent(search)}`;

  try {
    const res = await fetch(url);
    const result = await res.json();
    if (!res.ok || !result.success) {
      throw new Error(result.message || 'Gagal memuat data subscriber');
    }

    const counts = result.data.counts || {};
    document.getElementById('statTotalSub').textContent = counts.total || 0;
    document.getElementById('statActiveSub').textContent = counts.active || 0;
    document.getElementById('statUnsub').textContent = counts.unsubscribed || 0;

    allSubscribers = result.data.items || [];
    renderSubscribersTable();
  } catch (err) {
    tbody.innerHTML = `
      <tr>
        <td colspan="5" class="text-center py-4 text-danger">
          <i class="bi bi-exclamation-triangle-fill me-1"></i> ${err.message}
          <div class="mt-2"><button class="btn btn-sm btn-outline-primary" onclick="loadSubscribersList()">Coba Lagi</button></div>
        </td>
      </tr>
    `;
  }
}

function renderSubscribersTable() {
  const tbody = document.getElementById('subscriberTableBody');
  if (allSubscribers.length === 0) {
    tbody.innerHTML = `
      <tr>
        <td colspan="5" class="text-center py-5 text-muted">
          <i class="bi bi-envelope-x fs-1 d-block mb-2 text-secondary"></i>
          Belum ada subscriber terdaftar${currentSubStatusFilter ? ` dengan status "${currentSubStatusFilter}"` : ''}.
        </td>
      </tr>
    `;
    return;
  }

  tbody.innerHTML = allSubscribers.map(item => {
    const isActive = item.status === 'active';
    const statusBadge = isActive
      ? '<span class="badge bg-success">Aktif</span>'
      : '<span class="badge bg-secondary">Unsubscribed</span>';

    return `
      <tr>
        <td>
          <div class="d-flex align-items-center gap-2">
            <i class="bi bi-envelope-at text-gold fs-5"></i>
            <div>
              <strong class="text-dark">${item.email}</strong>
              <div class="small text-muted">ID: #${item.id}</div>
            </div>
          </div>
        </td>
        <td>
          <button class="badge border-0 ${isActive ? 'bg-success' : 'bg-secondary'} px-2 py-1 cursor-pointer" onclick="toggleSubStatus(${item.id})">
            ${isActive ? 'Aktif' : 'Unsubscribed'}
          </button>
        </td>
        <td><span class="badge bg-light text-dark border small">${item.source_page || 'landing_footer'}</span></td>
        <td class="small text-muted">${(item.subscribed_at || '').substring(0, 16)}</td>
        <td class="text-end">
          <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteSubscriber(${item.id}, '${item.email}')" title="Hapus">
            <i class="bi bi-trash3-fill"></i>
          </button>
        </td>
      </tr>
    `;
  }).join('');
}

async function toggleSubStatus(id) {
  try {
    const res = await fetch('../api/admin/newsletter.php', {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: id })
    });
    const json = await res.json();
    if (!res.ok || !json.success) throw new Error(json.message || 'Gagal mengubah status');

    showAlert(json.message || 'Status langganan diperbarui.', 'success');
    loadSubscribersList();
  } catch (err) {
    showAlert(err.message, 'danger');
  }
}

async function deleteSubscriber(id, email) {
  if (!confirm(`Apakah Anda yakin ingin menghapus email "${email}" dari daftar buletin?`)) return;

  try {
    const res = await fetch('../api/admin/newsletter.php', {
      method: 'DELETE',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: id })
    });
    const json = await res.json();
    if (!res.ok || !json.success) throw new Error(json.message || 'Gagal menghapus');

    showAlert('Subscriber berhasil dihapus.', 'success');
    loadSubscribersList();
  } catch (err) {
    showAlert(err.message, 'danger');
  }
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
