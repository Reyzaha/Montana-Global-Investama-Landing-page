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
          <th>Proyek Kemitraan (1-on-1)</th>
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

<!-- MODAL ASSIGN INVESTOR KE PROYEK (1-ON-1 EKSKLUSIF) -->
<div class="modal fade" id="assignProjectModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      <div class="modal-header text-white" style="background: #142563;">
        <h5 class="modal-title fw-bold">
          <i class="bi bi-shield-check text-warning me-2" style="color: #C5A059 !important;"></i>
          <span>Tautkan Investor ke Proyek Kemitraan Eksklusif (1-on-1)</span>
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form id="assignProjectForm" onsubmit="submitAssignProject(event)">
        <div class="modal-body p-4 bg-light">
          <input type="hidden" id="assignInvId">
          
          <div class="card p-3 border-0 rounded-3 shadow-sm bg-white mb-3">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <small class="text-muted d-block">Investor Terpilih</small>
                <h6 class="fw-bold text-dark mb-0" id="assignInvName">-</h6>
                <div class="text-muted small" id="assignInvEmail">-</div>
              </div>
              <span class="badge bg-royal text-white px-3 py-2 rounded-pill small" id="assignInvType">-</span>
            </div>
          </div>

          <div id="assignActiveBanner" class="alert alert-info py-2 px-3 small d-none mb-3 d-flex align-items-center justify-content-between rounded-3 border-info">
            <div>
              <i class="bi bi-info-circle-fill me-1 text-primary"></i>
              <span>Memegang proyek aktif: <strong id="assignCurrentProjTitle">-</strong></span>
            </div>
            <button type="button" class="btn btn-sm btn-outline-danger py-0.5 px-2 fw-semibold" id="btnModalUnassign" onclick="unassignFromModal()">
              <i class="bi bi-x-circle me-1"></i>Lepaskan Proyek Ini
            </button>
          </div>

          <div class="card p-4 border-0 rounded-3 shadow-sm bg-white mb-3">
            <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
              <i class="bi bi-gear-wide-connected text-primary me-2"></i>Parameter Kemitraan Proyek
            </h6>
            <div class="row g-3">
              <div class="col-md-12">
                <label class="form-label fw-bold">Pilih Proyek Investasi Eksklusif <span class="text-danger">*</span></label>
                <select id="assignProjectId" class="form-select" required onchange="handleProjectSelectChange()">
                  <option value="">-- Pilih Proyek yang Tersedia --</option>
                </select>
                <div class="form-text small text-muted">1 Proyek hanya dapat didanai oleh 1 Investor secara eksklusif. Proyek yang sudah didanai penuh tidak dapat dipilih kembali.</div>
              </div>

              <div class="col-md-6">
                <label class="form-label fw-bold">Nomor Kontrak / SPK <span class="text-muted small">(Auto jika kosong)</span></label>
                <input type="text" id="assignContractNumber" class="form-control" placeholder="Contoh: MGI-SPK/2026/PROJ-001">
              </div>

              <div class="col-md-6">
                <label class="form-label fw-bold">Nominal Penempatan Modal (IDR) <span class="text-danger">*</span></label>
                <input type="text" id="assignAmount" class="form-control fw-bold text-primary" placeholder="5.000.000.000" required oninput="formatInputCurrency(this)">
              </div>

              <div class="col-md-6">
                <label class="form-label fw-bold">Target Imbal Hasil (ROI p.a.)</label>
                <input type="text" id="assignReturnRate" class="form-control" value="≥30% (p.a.)">
              </div>

              <div class="col-md-6">
                <label class="form-label fw-bold">Tenor Kemitraan</label>
                <input type="text" id="assignTenor" class="form-control" value="36 Bulan">
              </div>

              <div class="col-md-6">
                <label class="form-label fw-bold">Tanggal Mulai Efektif</label>
                <input type="date" id="assignStartDate" class="form-control" value="<?php echo date('Y-m-d'); ?>">
              </div>

              <div class="col-md-6">
                <label class="form-label fw-bold">Tanggal Berakhir Kontrak</label>
                <input type="date" id="assignEndDate" class="form-control" value="<?php echo date('Y-m-d', strtotime('+3 years')); ?>">
              </div>
            </div>
          </div>

          <div class="card p-3 border-0 rounded-3 shadow-sm bg-white">
            <h6 class="fw-bold text-dark mb-2">
              <i class="bi bi-bell-fill text-warning me-2"></i>Otomasi Penagihan &amp; Notifikasi Real-Time
            </h6>
            <div class="form-check form-switch mb-2">
              <input class="form-check-input" type="checkbox" id="assignCreateBilling" checked>
              <label class="form-check-label small fw-semibold" for="assignCreateBilling">
                Otomatis terbitkan Billing / Tagihan Penempatan Modal Awal ke Investor
              </label>
            </div>
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" id="assignSendNotif" checked disabled>
              <label class="form-check-label small fw-semibold text-muted" for="assignSendNotif">
                Kirim notifikasi real-time &amp; aktifkan modul pembukuan di dashboard investor
              </label>
            </div>
          </div>
        </div>

        <div class="modal-footer bg-white border-0 py-3">
          <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Batal</button>
          <button type="submit" id="btnSubmitAssign" class="btn btn-mgi-primary px-4 fw-bold">
            <i class="bi bi-check-circle-fill me-1"></i> Tautkan &amp; Aktifkan Kemitraan
          </button>
        </div>
      </form>
    </div>
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

        <!-- Checklist Verifikasi Due Diligence / KYC -->
        <div class="card p-4 border-0 rounded-3 shadow-sm bg-white mb-3" id="modalKycCard">
          <div class="d-flex align-items-center justify-content-between border-bottom pb-2 mb-3">
            <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
              <i class="bi bi-patch-check-fill text-success"></i>
              <span>Checklist Kepatuhan &amp; Uji Kelayakan (KYC / Due Diligence)</span>
            </h6>
            <span class="badge bg-light text-muted border small" id="modalKycVerifiedBadge">Belum Diverifikasi</span>
          </div>

          <div class="row g-2 mb-3">
            <div class="col-12 col-md-6">
              <div class="p-2.5 rounded-2 border bg-light d-flex align-items-center gap-2">
                <input class="form-check-input mt-0" type="checkbox" id="chkKycIdentity" style="width: 18px; height: 18px;">
                <label class="form-check-label small fw-semibold text-dark cursor-pointer" for="chkKycIdentity">
                  Identitas Sah (KTP / Paspor valid)
                </label>
              </div>
            </div>

            <div class="col-12 col-md-6">
              <div class="p-2.5 rounded-2 border bg-light d-flex align-items-center gap-2">
                <input class="form-check-input mt-0" type="checkbox" id="chkKycTax" style="width: 18px; height: 18px;">
                <label class="form-check-label small fw-semibold text-dark cursor-pointer" for="chkKycTax">
                  NPWP / Pajak Terverifikasi Sah
                </label>
              </div>
            </div>

            <div class="col-12 col-md-6">
              <div class="p-2.5 rounded-2 border bg-light d-flex align-items-center gap-2">
                <input class="form-check-input mt-0" type="checkbox" id="chkKycBank" style="width: 18px; height: 18px;">
                <label class="form-check-label small fw-semibold text-dark cursor-pointer" for="chkKycBank">
                  Rekening Bank &amp; Sumber Dana Sah
                </label>
              </div>
            </div>

            <div class="col-12 col-md-6" id="wrapKycCorporate">
              <div class="p-2.5 rounded-2 border bg-light d-flex align-items-center gap-2">
                <input class="form-check-input mt-0" type="checkbox" id="chkKycCorporate" style="width: 18px; height: 18px;">
                <label class="form-check-label small fw-semibold text-dark cursor-pointer" for="chkKycCorporate">
                  Legalitas Perusahaan (NIB &amp; Akta)
                </label>
              </div>
            </div>
          </div>

          <!-- Quick Identity Data Fields -->
          <div class="row g-2 pt-2 border-top">
            <div class="col-md-6" id="wrapNikInput">
              <label class="form-label small fw-bold text-muted mb-1">No. KTP / NIK / Paspor</label>
              <input type="text" id="modalInvNik" class="form-control form-control-sm" placeholder="Contoh: 3171xxxxxxxxxxxx">
            </div>
            <div class="col-md-6" id="wrapNpwpInput">
              <label class="form-label small fw-bold text-muted mb-1">No. Pokok Wajib Pajak (NPWP)</label>
              <input type="text" id="modalInvNpwp" class="form-control form-control-sm" placeholder="Contoh: 01.xxx.xxx.x-xxx.xxx">
            </div>
            <div class="col-md-6 d-none" id="wrapNibInput">
              <label class="form-label small fw-bold text-muted mb-1">Nomor Induk Berusaha (NIB Perusahaan)</label>
              <input type="text" id="modalInvNib" class="form-control form-control-sm" placeholder="Contoh: 1234567890123">
            </div>
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
              <div class="col-12 d-flex justify-content-between align-items-center">
                <a href="#" id="modalBtnGoFinancial" class="btn btn-outline-warning text-dark border-warning" style="background: #FFFBF0;">
                  <i class="bi bi-wallet2 text-warning me-1"></i> Buka Modul Keuangan &amp; Kirim Billing
                </a>
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
  let availableProjectsData = [];
  let detailModalInstance = null;
  let assignModalInstance = null;

  document.addEventListener('DOMContentLoaded', () => {
    detailModalInstance = new bootstrap.Modal(document.getElementById('investorDetailModal'));
    assignModalInstance = new bootstrap.Modal(document.getElementById('assignProjectModal'));
    loadInvestors();
  });

  function formatRupiah(val) {
    const num = parseFloat(val) || 0;
    return num.toLocaleString('id-ID');
  }

  function formatInputCurrency(input) {
    let val = input.value.replace(/[^0-9]/g, '');
    if (!val) {
      input.value = '';
      return;
    }
    input.value = parseInt(val, 10).toLocaleString('id-ID');
  }

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

      investorsData = Array.isArray(json.data) ? json.data : (json.data.investors || []);
      availableProjectsData = json.data.available_projects || [];

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

        // Partnership status 1-on-1
        let projectInfoHtml = '';
        if (inv.project_id && inv.project_title) {
          projectInfoHtml = `
            <div>
              <div class="fw-bold text-dark text-truncate" style="max-width: 200px;" title="${inv.project_title}">
                <i class="bi bi-shield-check text-success me-1"></i>${inv.project_title}
              </div>
              <div class="small text-muted">
                Rp ${formatRupiah(inv.portfolio_amount)} • <span class="badge bg-success text-white py-0.5 px-1.5" style="font-size:0.7rem;">${inv.portfolio_status || 'Aktif'}</span>
              </div>
              <div class="d-flex align-items-center gap-2 mt-1">
                <small class="text-muted font-monospace" style="font-size:0.7rem;">${inv.contract_number || '-'}</small>
                <button type="button" class="btn btn-xs btn-outline-danger py-0 px-1.5 fw-semibold" style="font-size: 0.68rem;" onclick="confirmUnassignProject(${inv.id}, '${inv.project_id}', '${(inv.project_title || '').replace(/'/g, "\\'")}')" title="Lepaskan kemitraan dari proyek ini">
                  <i class="bi bi-x-circle me-1"></i>Lepas
                </button>
              </div>
            </div>
          `;
        } else {
          projectInfoHtml = `
            <div>
              <span class="badge bg-light text-secondary border py-1 px-2 mb-1 d-inline-block">
                <i class="bi bi-hourglass-split me-1 text-muted"></i>Belum Ada Modal
              </span>
              <div>
                <button type="button" class="btn btn-xs btn-outline-success py-0.5 px-2 fw-semibold" style="font-size: 0.75rem;" onclick="openAssignModal(${inv.id})">
                  <i class="bi bi-plus-circle me-1"></i>Tautkan Proyek
                </button>
              </div>
            </div>
          `;
        }

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
              ${projectInfoHtml}
            </td>
            <td>${statusBadge}</td>
            <td class="small text-muted">${dateStr}</td>
            <td class="text-end">
              <button type="button" class="btn btn-sm btn-outline-success py-1 px-2.5 d-inline-flex align-items-center gap-1 me-1" onclick="openAssignModal(${inv.id})" title="Tautkan / Kelola Kemitraan Proyek (1-on-1)">
                <i class="bi bi-link-45deg"></i>
                <span class="small fw-semibold">Kemitraan</span>
              </button>
              <a href="financial.php?investor_id=${inv.id}" class="btn btn-sm btn-outline-warning py-1 px-2.5 d-inline-flex align-items-center gap-1 me-1 text-dark" style="border-color: #C5A059;" title="Kelola Keuangan & Kirim Billing">
                <i class="bi bi-receipt-cutoff" style="color: #C5A059;"></i>
                <span class="small fw-semibold">Billing</span>
              </a>
              <button type="button" class="btn btn-sm btn-outline-primary py-1 px-2.5 d-inline-flex align-items-center gap-1" onclick="viewInvestorDetail(${inv.id})">
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

  function openAssignModal(investorId) {
    const inv = investorsData.find(i => Number(i.id) === Number(investorId));
    if (!inv) return;
    if (inv.status !== 'active') {
      AdminApp.showToast('Investor belum berstatus Active. Verifikasi akun terlebih dahulu (tombol Tinjau).', 'warning');
      return;
    }

    const isCorp = inv.account_type === 'perusahaan';
    document.getElementById('assignInvId').value = inv.id;
    document.getElementById('assignInvName').textContent = isCorp ? inv.business_name : (inv.full_name || inv.email);
    document.getElementById('assignInvEmail').textContent = inv.email;
    document.getElementById('assignInvType').textContent = isCorp ? 'Investor Korporasi' : 'Investor Perorangan';

    // Populate project select dropdown
    const projSelect = document.getElementById('assignProjectId');
    projSelect.innerHTML = `<option value="">-- Pilih Proyek yang Tersedia --</option>`;

    availableProjectsData.forEach(p => {
      const isCurrent = (inv.project_id === p.id);
      const isTaken = (p.active_investor_count > 0 && !isCurrent);
      const opt = document.createElement('option');
      opt.value = p.id;
      opt.textContent = `${p.title} (Target: Rp ${formatRupiah(p.funding_target)})${isTaken ? ' — [Sudah Ada Investor]' : ''}`;
      if (isTaken) {
        opt.disabled = true;
        opt.style.color = '#999';
      }
      if (isCurrent) {
        opt.selected = true;
      }
      projSelect.appendChild(opt);
    });

    const activeBanner = document.getElementById('assignActiveBanner');
    if (inv.project_id && inv.project_title) {
      activeBanner.classList.remove('d-none');
      document.getElementById('assignCurrentProjTitle').textContent = inv.project_title;
      document.getElementById('assignContractNumber').value = inv.contract_number || '';
      document.getElementById('assignAmount').value = formatRupiah(inv.portfolio_amount || 500000000);
      document.getElementById('assignReturnRate').value = inv.portfolio_return_rate || '≥30% (p.a.)';
      document.getElementById('assignTenor').value = inv.portfolio_tenor || '36 Bulan';
    } else {
      activeBanner.classList.add('d-none');
      document.getElementById('assignContractNumber').value = '';
      document.getElementById('assignAmount').value = '500.000.000';
      document.getElementById('assignReturnRate').value = '≥30% (p.a.)';
      document.getElementById('assignTenor').value = '36 Bulan';
    }

    handleProjectSelectChange();
    assignModalInstance.show();
  }

  function handleProjectSelectChange() {
    const projId = document.getElementById('assignProjectId').value;
    const invId = document.getElementById('assignInvId').value;
    const contractInput = document.getElementById('assignContractNumber');
    const amountInput = document.getElementById('assignAmount');

    if (!projId) return;

    const proj = availableProjectsData.find(p => p.id === projId);
    if (proj) {
      if (!contractInput.value) {
        contractInput.value = `MGI-SPK/${new Date().getFullYear()}/${projId.substring(0, 10).toUpperCase()}-${String(invId).padStart(4, '0')}`;
      }
      if (!amountInput.value || amountInput.value === '0') {
        amountInput.value = formatRupiah(proj.funding_target || 500000000);
      }
    }
  }

  async function submitAssignProject(e) {
    e.preventDefault();
    const investor_id = document.getElementById('assignInvId').value;
    const project_id = document.getElementById('assignProjectId').value;
    const contract_number = document.getElementById('assignContractNumber').value.trim();
    const rawAmount = document.getElementById('assignAmount').value.replace(/[^0-9]/g, '');
    const amount = parseFloat(rawAmount) || 0;
    const return_rate = document.getElementById('assignReturnRate').value.trim();
    const tenor = document.getElementById('assignTenor').value.trim();
    const start_date = document.getElementById('assignStartDate').value;
    const end_date = document.getElementById('assignEndDate').value;
    const create_billing = document.getElementById('assignCreateBilling').checked;

    if (!project_id) {
      AdminApp.showToast('Silakan pilih proyek kemitraan.', 'warning');
      return;
    }
    if (amount <= 0) {
      AdminApp.showToast('Nominal penempatan modal harus lebih dari 0.', 'warning');
      return;
    }

    const btn = document.getElementById('btnSubmitAssign');
    btn.disabled = true;
    btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span> Memproses Kemitraan...`;

    try {
      const res = await fetch('../api/admin/investors.php?action=assign_project', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          investor_id,
          project_id,
          contract_number,
          amount,
          return_rate,
          tenor,
          start_date,
          end_date,
          create_billing
        })
      });

      const json = await res.json();
      if (json.success) {
        AdminApp.showToast(json.message, 'success');
        assignModalInstance.hide();
        loadInvestors();
      } else {
        AdminApp.showToast(json.message || 'Gagal menautkan investor ke proyek.', 'danger');
      }
    } catch (err) {
      console.error(err);
      AdminApp.showToast('Terjadi kesalahan koneksi server.', 'danger');
    } finally {
      btn.disabled = false;
      btn.innerHTML = `<i class="bi bi-check-circle-fill me-1"></i> Tautkan &amp; Aktifkan Kemitraan`;
    }
  }

  async function confirmUnassignProject(investorId, projectId, projectTitle) {
    if (!confirm(`Apakah Anda yakin ingin melepaskan kemitraan pada proyek "${projectTitle}"?\n\nProyek akan kembali terbuka (Open) dan portofolio kemitraan investor ini dinonaktifkan.`)) {
      return;
    }
    const reason = prompt('Masukkan alasan atau catatan pelepasan proyek (opsional):', 'Pergantian / pembatalan kemitraan');
    if (reason === null) return;

    try {
      const res = await fetch('../api/admin/investors.php?action=unassign_project', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          investor_id: investorId,
          project_id: projectId,
          reason: reason.trim() || 'Dilepaskan oleh admin'
        })
      });
      const json = await res.json();
      if (json.success) {
        AdminApp.showToast(json.message, 'success');
        if (assignModalInstance) assignModalInstance.hide();
        loadInvestors();
      } else {
        AdminApp.showToast(json.message || 'Gagal melepaskan kemitraan proyek.', 'danger');
      }
    } catch (e) {
      console.error(e);
      AdminApp.showToast('Terjadi kesalahan koneksi server.', 'danger');
    }
  }

  function unassignFromModal() {
    const invId = document.getElementById('assignInvId').value;
    const inv = investorsData.find(i => Number(i.id) === Number(invId));
    if (!inv || !inv.project_id) return;
    confirmUnassignProject(inv.id, inv.project_id, inv.project_title || inv.project_id);
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
    document.getElementById('modalBtnGoFinancial').href = `financial.php?investor_id=${inv.id}`;

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
          <small class="text-muted d-block">Nomor Induk Berusaha (NIB)</small>
          <strong class="font-monospace text-primary">${inv.nib || '<span class="text-muted fst-italic">Belum dicatat</span>'}</strong>
        </div>
        <div class="col-md-6">
          <small class="text-muted d-block">NPWP Perusahaan</small>
          <strong class="font-monospace">${inv.npwp_perusahaan || inv.npwp || '<span class="text-muted fst-italic">Belum dicatat</span>'}</strong>
        </div>
        <div class="col-md-6">
          <small class="text-muted d-block">Bidang Usaha / Sektor Industri</small>
          <strong>${inv.business_activity || '-'}</strong>
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
          <small class="text-muted d-block">Nomor Identitas (NIK / No. Paspor)</small>
          <strong class="font-monospace text-primary">${inv.nik_paspor || '<span class="text-muted fst-italic">Belum dicatat</span>'}</strong>
        </div>
        <div class="col-md-6">
          <small class="text-muted d-block">Nomor Pokok Wajib Pajak (NPWP)</small>
          <strong class="font-monospace">${inv.npwp || '<span class="text-muted fst-italic">Belum dicatat</span>'}</strong>
        </div>
        <div class="col-md-6">
          <small class="text-muted d-block">Nomor Telepon / WhatsApp</small>
          <strong>${inv.phone || '-'}</strong>
        </div>
        <div class="col-md-6">
          <small class="text-muted d-block">Jenis Kegiatan / Bidang Usaha</small>
          <strong class="text-primary">${inv.business_activity || '-'}</strong>
        </div>
        <div class="col-md-6">
          <small class="text-muted d-block">Tanggal Registrasi</small>
          <strong>${new Date(inv.created_at).toLocaleString('id-ID')}</strong>
        </div>
      `;
    }

    // Populate KYC Checklist & Input Fields
    let kycList = [];
    try {
      if (inv.kyc_checklist) {
        kycList = typeof inv.kyc_checklist === 'string' ? JSON.parse(inv.kyc_checklist) : inv.kyc_checklist;
      }
    } catch (e) {
      kycList = [];
    }

    document.getElementById('chkKycIdentity').checked = Array.isArray(kycList) && kycList.includes('identity');
    document.getElementById('chkKycTax').checked = Array.isArray(kycList) && kycList.includes('tax');
    document.getElementById('chkKycBank').checked = Array.isArray(kycList) && kycList.includes('bank');
    document.getElementById('chkKycCorporate').checked = Array.isArray(kycList) && kycList.includes('corporate');

    document.getElementById('modalInvNik').value = inv.nik_paspor || '';
    document.getElementById('modalInvNpwp').value = isCorp ? (inv.npwp_perusahaan || inv.npwp || '') : (inv.npwp || '');
    document.getElementById('modalInvNib').value = inv.nib || '';

    const wrapNib = document.getElementById('wrapNibInput');
    const wrapKycCorp = document.getElementById('wrapKycCorporate');
    const wrapNik = document.getElementById('wrapNikInput');

    if (isCorp) {
      wrapNib.classList.remove('d-none');
      wrapKycCorp.classList.remove('d-none');
      wrapNik.classList.add('d-none');
    } else {
      wrapNib.classList.add('d-none');
      wrapKycCorp.classList.add('d-none');
      wrapNik.classList.remove('d-none');
    }

    const kycBadge = document.getElementById('modalKycVerifiedBadge');
    if (inv.kyc_verified_at) {
      kycBadge.textContent = 'Diverifikasi: ' + new Date(inv.kyc_verified_at).toLocaleDateString('id-ID');
      kycBadge.className = 'badge bg-success-subtle text-success border border-success-subtle small';
    } else {
      kycBadge.textContent = 'Menunggu Uji Kelayakan';
      kycBadge.className = 'badge bg-warning-subtle text-dark border border-warning-subtle small';
    }

    detailModalInstance.show();
  }

  async function updateInvestorStatus(e) {
    e.preventDefault();
    const id = document.getElementById('modalInvId').value;
    const status = document.getElementById('modalInvStatusSelect').value;
    const notes = document.getElementById('modalInvNotes').value.trim();
    const btn = document.getElementById('btnUpdateInvStatus');

    // Gather KYC Checklist
    const kycChecklist = [];
    if (document.getElementById('chkKycIdentity').checked) kycChecklist.push('identity');
    if (document.getElementById('chkKycTax').checked) kycChecklist.push('tax');
    if (document.getElementById('chkKycBank').checked) kycChecklist.push('bank');
    if (document.getElementById('chkKycCorporate').checked) kycChecklist.push('corporate');

    const nikPaspor = document.getElementById('modalInvNik').value.trim();
    const npwp = document.getElementById('modalInvNpwp').value.trim();
    const nib = document.getElementById('modalInvNib').value.trim();

    btn.disabled = true;
    btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span> Menyimpan...`;

    try {
      const res = await fetch('../api/admin/investors.php', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ 
          id, 
          status, 
          notes,
          kyc_checklist: kycChecklist,
          nik_paspor: nikPaspor,
          npwp: npwp,
          nib: nib,
          npwp_perusahaan: npwp
        })
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
