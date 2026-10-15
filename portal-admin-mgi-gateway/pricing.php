<?php
$pageTitle = 'Manajemen Paket & Tier Investasi';
require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
  <div>
    <h4 class="fw-bold text-dark mb-1">Manajemen Paket &amp; Tier Investasi</h4>
    <p class="text-muted small mb-0">Kelola skema tiket investasi, nominal penempatan modal, kuota armada alat berat, fasilitas operator profesional, serta simulasi proyeksi bagi hasil pemodal.</p>
  </div>
  <button type="button" class="btn btn-mgi-gold shadow-sm d-flex align-items-center gap-2" onclick="openTierModal()">
    <i class="bi bi-plus-circle-fill"></i>
    <span>Tambah Paket Investasi</span>
  </button>
</div>

<!-- Alert Box -->
<div id="pricingAlertContainer"></div>

<!-- Summary Cards -->
<div class="row g-3 mb-4" id="pricingSummaryCards">
  <div class="col-sm-6 col-xl-3">
    <div class="admin-card p-3 bg-white border">
      <div class="text-muted small fw-bold text-uppercase">Total Paket Aktif</div>
      <div class="fs-3 fw-bold text-dark mt-1" id="statTotalTiers">0</div>
      <div class="text-success small"><i class="bi bi-check-circle me-1"></i>Siap Ditampilkan di Web</div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="admin-card p-3 bg-white border">
      <div class="text-muted small fw-bold text-uppercase">Tiket Terendah</div>
      <div class="fs-3 fw-bold text-primary mt-1" style="color: #142563 !important;" id="statMinTicket">-</div>
      <div class="text-muted small">Partisipasi Minimum</div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="admin-card p-3 bg-white border">
      <div class="text-muted small fw-bold text-uppercase">Tiket Terbesar</div>
      <div class="fs-3 fw-bold text-warning mt-1" style="color: #C5A059 !important;" id="statMaxTicket">-</div>
      <div class="text-muted small">Kapasitas Penuh Regional</div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="admin-card p-3 bg-white border">
      <div class="text-muted small fw-bold text-uppercase">Operator Didukung</div>
      <div class="fs-3 fw-bold text-success mt-1" id="statOperators">100%</div>
      <div class="text-muted small">Kelolaan Workshop MIU</div>
    </div>
  </div>
</div>

<!-- Table Card -->
<div class="admin-card">
  <div class="admin-card-header bg-white d-flex align-items-center justify-content-between p-3 border-bottom">
    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
      <i class="bi bi-tags-fill text-warning" style="color: #C5A059 !important;"></i>
      <span>Daftar Paket Kemitraan Modal (Tiers)</span>
    </h6>
    <button class="btn btn-sm btn-outline-secondary" onclick="loadTiersList()">
      <i class="bi bi-arrow-clockwise me-1"></i> Refresh
    </button>
  </div>
  <div class="table-responsive">
    <table class="admin-table table-hover mb-0">
      <thead>
        <tr>
          <th>Nama Paket &amp; Badge</th>
          <th>Nominal Tiket</th>
          <th>Alokasi Armada</th>
          <th>Fasilitas Operator</th>
          <th>Bagi Hasil Siklus</th>
          <th>Status</th>
          <th class="text-end" style="width: 140px;">Aksi</th>
        </tr>
      </thead>
      <tbody id="tiersTableBody">
        <tr>
          <td colspan="7" class="text-center py-4 text-muted">
            <span class="spinner-border spinner-border-sm me-2"></span> Memuat data paket...
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal Form Tambah / Edit Tier -->
<div class="modal fade" id="tierModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-3">
      <div class="modal-header bg-light border-bottom py-3 px-4">
        <h5 class="modal-title fw-bold text-dark mb-0" id="tierModalTitle">Tambah Paket Investasi</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="tierForm" onsubmit="saveTier(event)">
        <input type="hidden" id="tierId" name="id" value="">
        <div class="modal-body p-4 space-y-3">
          
          <div class="row g-3 mb-3">
            <div class="col-md-7">
              <label class="form-label small fw-bold text-dark">Nama Paket <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="formTierName" name="name" placeholder="Contoh: Paket Kemitraan Tunggal (15 Miliar)" required>
            </div>
            <div class="col-md-5">
              <label class="form-label small fw-bold text-dark">Lencana / Badge</label>
              <input type="text" class="form-control" id="formTierBadge" name="badge" placeholder="Contoh: Kapasitas Penuh & Bonus 2 Operator">
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label small fw-bold text-dark">Nominal Investasi (Rupiah Angka) <span class="text-danger">*</span></label>
              <input type="number" class="form-control" id="formTierNominal" name="nominal" placeholder="Contoh: 15000000000" min="1000000" required oninput="syncNominalDisplay()">
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-bold text-dark">Tampilan Nominal (Label)</label>
              <input type="text" class="form-control" id="formTierNominalDisplay" name="nominal_display" placeholder="Contoh: Rp 15.000.000.000">
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-4">
              <label class="form-label small fw-bold text-dark">Jumlah Unit Armada</label>
              <input type="number" class="form-control" id="formTierUnitQty" name="unit_qty" value="1" min="1">
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-bold text-dark">Alokasi Kontainer</label>
              <input type="text" class="form-control" id="formTierContainerQty" name="container_qty" placeholder="Contoh: 6.25 Kontainer 40FT HC">
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-bold text-dark">Jaminan Fisik Aset</label>
              <input type="text" class="form-control" id="formTierJaminanAset" name="jaminan_aset" placeholder="Contoh: Seluruh Unit Fisik Siap Operasi">
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label small fw-bold text-dark">Fasilitas Operator Profesional</label>
            <input type="text" class="form-control" id="formTierOperatorPerk" name="operator_perk" placeholder="Contoh: 2 Operator Profesional Alat Berat Bersertifikat (Rotasi Shift & Pengawasan)">
            <small class="text-muted">Operator bersertifikat yang dikelola langsung oleh tim operasional PT MIU.</small>
          </div>

          <div class="mb-3">
            <label class="form-label small fw-bold text-dark">Deskripsi Skema Paket <span class="text-danger">*</span></label>
            <textarea class="form-control" id="formTierDeskripsi" name="deskripsi" rows="3" placeholder="Jelaskan secara mendalam tentang struktur paket kemitraan ini..." required></textarea>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-4">
              <label class="form-label small fw-bold text-dark">Estimasi Profit per Siklus (Rp)</label>
              <input type="number" class="form-control" id="formTierProfitSiklus" name="profit_per_siklus" placeholder="2146200000">
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-bold text-dark">Profit 2x Setahun (Rp)</label>
              <input type="number" class="form-control" id="formTierProfit2x" name="profit_2x_tahunan" placeholder="4292400000">
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-bold text-dark">Profit 3x Setahun (Rp)</label>
              <input type="number" class="form-control" id="formTierProfit3x" name="profit_3x_tahunan" placeholder="6438600000">
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-4">
              <label class="form-label small fw-bold text-dark">Urutan Tampil (Sort Order)</label>
              <input type="number" class="form-control" id="formTierSortOrder" name="sort_order" value="1" min="0">
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-bold text-dark">Status Unggulan</label>
              <select class="form-select" id="formTierIsFeatured" name="is_featured">
                <option value="0">Reguler</option>
                <option value="1">Unggulan (Highlighted)</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-bold text-dark">Status Publikasi</label>
              <select class="form-select" id="formTierIsActive" name="is_active">
                <option value="1">Aktif / Tampilkan</option>
                <option value="0">Draft / Sembunyikan</option>
              </select>
            </div>
          </div>

        </div>
        <div class="modal-footer bg-light border-top py-3 px-4">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-mgi-gold px-4 fw-bold shadow-sm" id="btnSaveTier">
            <span class="spinner-border spinner-border-sm me-1 d-none" id="saveTierSpinner"></span>
            Simpan Paket
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
let tierModalInstance = null;
let allTiers = [];

document.addEventListener('DOMContentLoaded', () => {
  tierModalInstance = new bootstrap.Modal(document.getElementById('tierModal'));
  loadTiersList();
});

function showAlert(message, type = 'success') {
  const container = document.getElementById('pricingAlertContainer');
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

function syncNominalDisplay() {
  const nom = parseInt(document.getElementById('formTierNominal').value) || 0;
  if (nom > 0) {
    document.getElementById('formTierNominalDisplay').value = 'Rp ' + nom.toLocaleString('id-ID');
  }
}

async function loadTiersList() {
  const tbody = document.getElementById('tiersTableBody');
  tbody.innerHTML = `
    <tr>
      <td colspan="7" class="text-center py-4 text-muted">
        <span class="spinner-border spinner-border-sm me-2"></span> Memuat data paket...
      </td>
    </tr>
  `;

  try {
    const res = await fetch('../api/admin/pricing.php', { cache: 'no-store' });
    const json = await res.json();
    if (!json.success) throw new Error(json.message || 'Gagal memuat paket.');

    allTiers = json.data || [];
    renderTiersTable();
    updateSummaryStats();
  } catch (err) {
    tbody.innerHTML = `
      <tr>
        <td colspan="7" class="text-center py-4 text-danger">
          <i class="bi bi-exclamation-circle me-1"></i> ${escapeHtml(err.message)}
          <br><button class="btn btn-sm btn-outline-dark mt-2" onclick="loadTiersList()">Coba Lagi</button>
        </td>
      </tr>
    `;
  }
}

function updateSummaryStats() {
  const activeTiers = allTiers.filter(t => t.is_active == 1);
  document.getElementById('statTotalTiers').innerText = activeTiers.length;

  if (activeTiers.length > 0) {
    const sorted = [...activeTiers].sort((a, b) => a.nominal - b.nominal);
    document.getElementById('statMinTicket').innerText = sorted[0].nominal_display || ('Rp ' + sorted[0].nominal.toLocaleString('id-ID'));
    document.getElementById('statMaxTicket').innerText = sorted[sorted.length - 1].nominal_display || ('Rp ' + sorted[sorted.length - 1].nominal.toLocaleString('id-ID'));
  }
}

function renderTiersTable() {
  const tbody = document.getElementById('tiersTableBody');

  if (allTiers.length === 0) {
    tbody.innerHTML = `
      <tr>
        <td colspan="7" class="text-center py-5 text-muted">
          <i class="bi bi-inbox fs-3 d-block mb-2"></i>
          Belum ada paket investasi. Klik "Tambah Paket Investasi" untuk membuat baru.
        </td>
      </tr>
    `;
    return;
  }

  tbody.innerHTML = allTiers.map(tier => {
    const featuredStar = tier.is_featured == 1
      ? '<span class="badge bg-warning text-dark me-1" style="background-color: #C5A059 !important; color: #fff !important;"><i class="bi bi-star-fill me-1"></i>Unggulan</span>'
      : '';

    const badgeLabel = tier.badge
      ? `<span class="badge bg-light text-primary border">${escapeHtml(tier.badge)}</span>`
      : '';

    const statusBadge = tier.is_active == 1
      ? '<span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 py-1 px-2">Aktif</span>'
      : '<span class="badge bg-secondary bg-opacity-10 text-muted border border-secondary border-opacity-25 py-1 px-2">Draft</span>';

    const profitPerSiklusDisplay = tier.profit_per_siklus > 0
      ? 'Rp ' + Number(tier.profit_per_siklus).toLocaleString('id-ID')
      : '-';

    return `
      <tr>
        <td>
          <div class="fw-bold text-dark">${featuredStar}${escapeHtml(tier.name)}</div>
          <div class="mt-1">${badgeLabel}</div>
        </td>
        <td class="fw-bold text-royal" style="color: #142563 !important;">
          ${escapeHtml(tier.nominal_display || ('Rp ' + Number(tier.nominal).toLocaleString('id-ID')))}
        </td>
        <td>
          <span class="badge bg-light text-dark border px-2 py-1">${tier.unit_qty} Unit</span>
          <div class="text-muted small mt-0.5">${escapeHtml(tier.container_qty || '-')}</div>
        </td>
        <td>
          <div class="small text-truncate" style="max-width: 220px;" title="${escapeHtml(tier.operator_perk || '-')}">
            <i class="bi bi-person-check text-success me-1"></i>${escapeHtml(tier.operator_perk || 'Standar MGI')}
          </div>
        </td>
        <td>
          <div class="fw-semibold text-success small">${profitPerSiklusDisplay}</div>
          <div class="text-muted" style="font-size: 0.72rem;">per siklus kerja</div>
        </td>
        <td>
          <button type="button" class="btn btn-sm p-0 border-0" onclick="toggleTierStatus(${tier.id})" title="Klik untuk ubah status">
            ${statusBadge}
          </button>
        </td>
        <td class="text-end">
          <button type="button" class="btn btn-sm btn-outline-primary me-1 p-1 px-2" onclick="editTier(${tier.id})" title="Edit">
            <i class="bi bi-pencil-square"></i>
          </button>
          <button type="button" class="btn btn-sm btn-outline-danger p-1 px-2" onclick="deleteTier(${tier.id}, '${escapeHtml(tier.name)}')" title="Hapus">
            <i class="bi bi-trash"></i>
          </button>
        </td>
      </tr>
    `;
  }).join('');
}

function openTierModal(tier = null) {
  const form = document.getElementById('tierForm');
  form.reset();

  if (tier) {
    document.getElementById('tierModalTitle').innerText = 'Edit Paket Investasi';
    document.getElementById('tierId').value = tier.id;
    document.getElementById('formTierName').value = tier.name;
    document.getElementById('formTierBadge').value = tier.badge || '';
    document.getElementById('formTierNominal').value = tier.nominal;
    document.getElementById('formTierNominalDisplay').value = tier.nominal_display || '';
    document.getElementById('formTierUnitQty').value = tier.unit_qty;
    document.getElementById('formTierContainerQty').value = tier.container_qty || '';
    document.getElementById('formTierJaminanAset').value = tier.jaminan_aset || '';
    document.getElementById('formTierOperatorPerk').value = tier.operator_perk || '';
    document.getElementById('formTierDeskripsi').value = tier.deskripsi;
    document.getElementById('formTierProfitSiklus').value = tier.profit_per_siklus || '';
    document.getElementById('formTierProfit2x').value = tier.profit_2x_tahunan || '';
    document.getElementById('formTierProfit3x').value = tier.profit_3x_tahunan || '';
    document.getElementById('formTierSortOrder').value = tier.sort_order;
    document.getElementById('formTierIsFeatured').value = tier.is_featured;
    document.getElementById('formTierIsActive').value = tier.is_active;
  } else {
    document.getElementById('tierModalTitle').innerText = 'Tambah Paket Investasi';
    document.getElementById('tierId').value = '';
    document.getElementById('formTierSortOrder').value = allTiers.length + 1;
    document.getElementById('formTierUnitQty').value = 1;
    document.getElementById('formTierIsFeatured').value = '0';
    document.getElementById('formTierIsActive').value = '1';
  }

  tierModalInstance.show();
}

function editTier(id) {
  const tier = allTiers.find(t => t.id == id);
  if (tier) openTierModal(tier);
}

async function saveTier(e) {
  e.preventDefault();
  const id = document.getElementById('tierId').value;
  const isEdit = !!id;

  const payload = {
    id: id ? parseInt(id) : undefined,
    name: document.getElementById('formTierName').value,
    badge: document.getElementById('formTierBadge').value,
    nominal: parseInt(document.getElementById('formTierNominal').value) || 0,
    nominal_display: document.getElementById('formTierNominalDisplay').value,
    unit_qty: parseInt(document.getElementById('formTierUnitQty').value) || 1,
    container_qty: document.getElementById('formTierContainerQty').value,
    jaminan_aset: document.getElementById('formTierJaminanAset').value,
    operator_perk: document.getElementById('formTierOperatorPerk').value,
    deskripsi: document.getElementById('formTierDeskripsi').value,
    profit_per_siklus: parseInt(document.getElementById('formTierProfitSiklus').value) || 0,
    profit_2x_tahunan: parseInt(document.getElementById('formTierProfit2x').value) || 0,
    profit_3x_tahunan: parseInt(document.getElementById('formTierProfit3x').value) || 0,
    sort_order: parseInt(document.getElementById('formTierSortOrder').value) || 0,
    is_featured: parseInt(document.getElementById('formTierIsFeatured').value) || 0,
    is_active: parseInt(document.getElementById('formTierIsActive').value) || 0
  };

  const btn = document.getElementById('btnSaveTier');
  const spinner = document.getElementById('saveTierSpinner');
  btn.disabled = true;
  spinner.classList.remove('d-none');

  try {
    const res = await fetch('../api/admin/pricing.php', {
      method: isEdit ? 'PUT' : 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const json = await res.json();
    if (!json.success) throw new Error(json.message || 'Gagal menyimpan.');

    showAlert(isEdit ? 'Paket investasi berhasil diperbarui.' : 'Paket investasi baru berhasil ditambahkan.');
    tierModalInstance.hide();
    loadTiersList();
  } catch (err) {
    showAlert(err.message, 'danger');
  } finally {
    btn.disabled = false;
    spinner.classList.add('d-none');
  }
}

async function toggleTierStatus(id) {
  try {
    const res = await fetch('../api/admin/pricing.php', {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id, action: 'toggle_status' })
    });
    const json = await res.json();
    if (!json.success) throw new Error(json.message);
    showAlert('Status paket berhasil diperbarui.');
    loadTiersList();
  } catch (err) {
    showAlert(err.message, 'danger');
  }
}

async function deleteTier(id, name) {
  if (!confirm(`Hapus paket investasi "${name}" secara permanen?`)) return;

  try {
    const res = await fetch('../api/admin/pricing.php', {
      method: 'DELETE',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id })
    });
    const json = await res.json();
    if (!json.success) throw new Error(json.message);
    showAlert('Paket investasi berhasil dihapus.');
    loadTiersList();
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
