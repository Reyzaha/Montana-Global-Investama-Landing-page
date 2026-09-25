<?php
$pageTitle = 'Manajemen Investor';
require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
  <div>
    <h5 class="fw-bold text-dark mb-1">Daftar Investor Terdaftar</h5>
    <p class="text-muted small mb-0">Tinjau profil pemodal perorangan dan korporasi, verifikasi kelayakan dokumen, serta kelola status akun.</p>
  </div>
</div>

<!-- Tabs & Search Card -->
<div class="admin-card">
  <div class="admin-card-header bg-white d-flex flex-wrap align-items-center justify-content-between gap-3">
    <ul class="nav nav-pills" id="investorTypeTabs">
      <li class="nav-item">
        <button class="nav-link active py-1 px-3 small fw-bold" onclick="filterType('')">Semua Pemodal</button>
      </li>
      <li class="nav-item">
        <button class="nav-link py-1 px-3 small fw-bold" onclick="filterType('perorangan')">Perorangan (Individu)</button>
      </li>
      <li class="nav-item">
        <button class="nav-link py-1 px-3 small fw-bold" onclick="filterType('perusahaan')">Perusahaan (Korporasi)</button>
      </li>
    </ul>

    <div class="d-flex align-items-center gap-2">
      <input type="text" id="investorSearch" class="form-control form-control-sm" placeholder="Cari nama, email, perusahaan..." style="width: 250px;" oninput="loadInvestors()">
      <select id="investorStatusFilter" class="form-select form-select-sm" style="width: 160px;" onchange="loadInvestors()">
        <option value="">Semua Status</option>
        <option value="active">Active (Aktif)</option>
        <option value="pending_verification">Pending Verification</option>
        <option value="suspended">Suspended (Ditangguhkan)</option>
      </select>
    </div>
  </div>

  <div class="table-responsive">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Nama Lengkap / Perusahaan</th>
          <th>Tipe Akun</th>
          <th>Kontak (Email / Telepon)</th>
          <th>Identitas / Legalitas</th>
          <th>Status Akun</th>
          <th>Tgl Daftar</th>
          <th class="text-end">Aksi</th>
        </tr>
      </thead>
      <tbody id="investorsTableBody">
        <tr>
          <td colspan="7" class="text-center py-5 text-muted">
            <span class="spinner-border spinner-border-sm me-2"></span> Memuat data investor...
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</div>

<!-- MODAL DETAIL INVESTOR -->
<div class="modal fade" id="investorDetailModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      <div class="modal-header text-white" style="background: #142563;">
        <h5 class="modal-title fw-bold">
          <i class="bi bi-person-badge-fill me-2 text-warning" style="color: #C5A059 !important;"></i>
          <span>Detail Profil &amp; Verifikasi Investor</span>
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-4 bg-light">
        <div class="card p-4 border-0 rounded-3 shadow-sm bg-white mb-3">
          <div class="d-flex align-items-center justify-content-between border-bottom pb-3 mb-3">
            <div>
              <h5 class="fw-bold text-dark mb-1" id="modalInvName">-</h5>
              <div class="text-muted small" id="modalInvEmail">-</div>
            </div>
            <span id="modalInvTypeBadge" class="badge bg-royal text-white px-3 py-2 rounded-pill small">Tipe</span>
          </div>

          <div class="row g-3" id="modalInvDetailsGrid">
            <!-- Dynamic profile details populated here -->
          </div>
        </div>

        <div class="card p-4 border-0 rounded-3 shadow-sm bg-white">
          <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">Tindakan Verifikasi &amp; Pengelolaan Status</h6>
          <form id="investorStatusForm" onsubmit="updateInvestorStatus(event)">
            <input type="hidden" id="modalInvId">
            <div class="row g-3">
              <div class="col-md-5">
                <label class="form-label">Status Verifikasi</label>
                <select id="modalInvStatusSelect" class="form-select">
                  <option value="active">Active (Disetujui / Akses Penuh)</option>
                  <option value="pending_verification">Pending Verification (Menunggu Review)</option>
                  <option value="suspended">Suspended (Ditangguhkan)</option>
                </select>
              </div>
              <div class="col-md-7">
                <label class="form-label">Catatan Admin / Due Diligence</label>
                <input type="text" id="modalInvNotes" class="form-control" placeholder="Contoh: Dokumen NIB telah diverifikasi">
              </div>
              <div class="col-12 text-end">
                <button type="submit" id="btnUpdateInvStatus" class="btn btn-mgi-primary px-4">
                  <i class="bi bi-check2-circle me-1"></i> Simpan Status
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>

    </div>
  </div>
</div>

<script>
  let currentTypeFilter = '';
  let investorsData = [];
  let detailModalInstance = null;

  document.addEventListener('DOMContentLoaded', () => {
    detailModalInstance = new bootstrap.Modal(document.getElementById('investorDetailModal'));
    loadInvestors();
  });

  function filterType(type) {
    currentTypeFilter = type;
    document.querySelectorAll('#investorTypeTabs .nav-link').forEach(btn => btn.classList.remove('active'));
    event.target.classList.add('active');
    loadInvestors();
  }

  async function loadInvestors() {
    const search = document.getElementById('investorSearch').value.trim();
    const status = document.getElementById('investorStatusFilter').value;
    const tb = document.getElementById('investorsTableBody');

    const params = new URLSearchParams();
    if (currentTypeFilter) params.set('type', currentTypeFilter);
    if (status) params.set('status', status);
    if (search) params.set('search', search);

    try {
      const res = await fetch(`../api/admin/investors.php?${params.toString()}`);
      const json = await res.json();
      if (!json.success) {
        if (json.status === 401) window.location.href = 'login.php';
        return;
      }

      investorsData = json.data || [];

      if (investorsData.length === 0) {
        tb.innerHTML = `<tr><td colspan="7" class="text-center py-5 text-muted">Tidak ada data investor yang cocok dengan filter.</td></tr>`;
        return;
      }

      tb.innerHTML = investorsData.map(inv => {
        const isCorp = inv.account_type === 'perusahaan';
        const displayName = isCorp ? inv.business_name : (inv.full_name || inv.email.split('@')[0]);
        const subName = isCorp ? `PIC: ${inv.pic_name || '-'} (${inv.pic_position || '-'})` : (inv.citizenship || 'WNI');
        
        let statusBadge = `<span class="badge bg-success text-white">Active</span>`;
        if (inv.status === 'pending_verification') statusBadge = `<span class="badge bg-warning text-dark">Pending</span>`;
        if (inv.status === 'suspended') statusBadge = `<span class="badge bg-danger text-white">Suspended</span>`;

        const dateStr = new Date(inv.created_at).toLocaleDateString('id-ID', {
          day: 'numeric', month: 'short', year: 'numeric'
        });

        return `
          <tr>
            <td>
              <div class="fw-bold text-dark">${displayName}</div>
              <small class="text-muted">${subName}</small>
            </td>
            <td>
              <span class="badge ${isCorp ? 'bg-primary' : 'bg-secondary'} text-white" style="${isCorp ? 'background: #142563 !important;' : ''}">
                ${isCorp ? 'Perusahaan' : 'Perorangan'}
              </span>
            </td>
            <td>
              <div class="text-dark small">${inv.email}</div>
              <small class="text-muted">${inv.phone || inv.company_phone || '-'}</small>
            </td>
            <td>
              <div class="small fw-semibold">${isCorp ? (inv.legal_entity || 'Badan Usaha') : (inv.citizenship || 'WNI')}</div>
              ${isCorp && inv.annual_turnover ? `<small class="text-muted">Omzet: ${inv.annual_turnover}</small>` : (inv.business_activity ? `<small class="text-primary">${inv.business_activity}</small>` : '')}
            </td>
            <td>${statusBadge}</td>
            <td class="small text-muted">${dateStr}</td>
            <td class="text-end">
              <button type="button" class="btn btn-sm btn-outline-primary py-1 px-3 d-inline-flex align-items-center gap-1" onclick="viewInvestorDetail(${inv.id})">
                <i class="bi bi-eye-fill"></i>
                <span>Tinjau</span>
              </button>
            </td>
          </tr>
        `;
      }).join('');

    } catch (e) {
      console.error(e);
      AdminApp.showToast('Gagal memuat data investor.', 'danger');
    }
  }

  function viewInvestorDetail(id) {
    const inv = investorsData.find(i => Number(i.id) === Number(id));
    if (!inv) return;

    const isCorp = inv.account_type === 'perusahaan';
    document.getElementById('modalInvId').value = inv.id;
    document.getElementById('modalInvName').textContent = isCorp ? inv.business_name : (inv.full_name || inv.email);
    document.getElementById('modalInvEmail').textContent = inv.email;
    document.getElementById('modalInvTypeBadge').textContent = isCorp ? 'Investor Korporasi / Perusahaan' : 'Investor Perorangan (Individu)';
    document.getElementById('modalInvStatusSelect').value = inv.status;
    document.getElementById('modalInvNotes').value = inv.notes || '';

    const grid = document.getElementById('modalInvDetailsGrid');
    if (isCorp) {
      grid.innerHTML = `
        <div class="col-md-6">
          <small class="text-muted d-block">Nama Perusahaan</small>
          <strong class="text-primary">${inv.business_name || inv.full_name || '-'}</strong>
        </div>
        <div class="col-md-6">
          <small class="text-muted d-block">Badan Hukum Usaha</small>
          <strong>${inv.legal_entity || '-'}</strong>
        </div>
        <div class="col-md-6">
          <small class="text-muted d-block">Rentang Omzet Tahunan</small>
          <strong>${inv.annual_turnover || '-'}</strong>
        </div>
        <div class="col-md-6">
          <small class="text-muted d-block">Nama Person In Charge (PIC)</small>
          <strong>${inv.pic_name || '-'}</strong>
        </div>
        <div class="col-md-6">
          <small class="text-muted d-block">Jabatan PIC</small>
          <strong>${inv.pic_position || '-'}</strong>
        </div>
        <div class="col-md-6">
          <small class="text-muted d-block">Telepon Perusahaan / WhatsApp PIC</small>
          <strong>${inv.company_phone || inv.phone || '-'}</strong>
        </div>
        <div class="col-md-6">
          <small class="text-muted d-block">Tanggal Registrasi</small>
          <strong>${new Date(inv.created_at).toLocaleString('id-ID')}</strong>
        </div>
        <div class="col-12">
          <small class="text-muted d-block">Alamat Lengkap Perusahaan / Domisili Hukum</small>
          <div class="p-2 bg-light rounded border mt-1 small">${inv.company_address || '-'}</div>
        </div>
      `;
    } else {
      grid.innerHTML = `
        <div class="col-md-6">
          <small class="text-muted d-block">Nama Lengkap (Sesuai Identitas)</small>
          <strong>${inv.full_name || '-'}</strong>
        </div>
        <div class="col-md-6">
          <small class="text-muted d-block">Status Kewarganegaraan</small>
          <strong>${inv.citizenship || 'Indonesia (WNI)'}</strong>
        </div>
        <div class="col-md-6">
          <small class="text-muted d-block">Nomor Telepon / WhatsApp</small>
          <strong>${inv.phone || '-'}</strong>
        </div>
        <div class="col-md-6">
          <small class="text-muted d-block">Jenis Kegiatan Usaha</small>
          <strong class="text-primary">${inv.business_activity || '-'}</strong>
        </div>
        <div class="col-md-6">
          <small class="text-muted d-block">Tanggal Registrasi</small>
          <strong>${new Date(inv.created_at).toLocaleString('id-ID')}</strong>
        </div>
      `;
    }

    detailModalInstance.show();
  }

  async function updateInvestorStatus(e) {
    e.preventDefault();
    const id = document.getElementById('modalInvId').value;
    const status = document.getElementById('modalInvStatusSelect').value;
    const notes = document.getElementById('modalInvNotes').value.trim();
    const btn = document.getElementById('btnUpdateInvStatus');

    btn.disabled = true;
    btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span> Menyimpan...`;

    try {
      const res = await fetch('../api/admin/investors.php', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id, status, notes })
      });
      const json = await res.json();

      if (json.success) {
        AdminApp.showToast(json.message, 'success');
        detailModalInstance.hide();
        loadInvestors();
      } else {
        AdminApp.showToast(json.message, 'danger');
      }
    } catch (err) {
      AdminApp.showToast('Gagal memperbarui status investor.', 'danger');
    } finally {
      btn.disabled = false;
      btn.innerHTML = `<i class="bi bi-check2-circle me-1"></i> Simpan Status`;
    }
  }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
