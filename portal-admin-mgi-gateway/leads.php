<?php
$pageTitle = 'Leads Konsultasi (Calon Investor)';
require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
  <div>
    <h5 class="fw-bold text-dark mb-1">Daftar Leads &amp; Calon Investor</h5>
    <p class="text-muted small mb-0">Kelola data calon investor yang masuk dari Landing Page Konsultasi Google Ads (Perorangan &amp; Perusahaan), pantau pipeline, dan tindak lanjuti via Email resmi.</p>
  </div>
  <div class="d-flex align-items-center gap-2">
    <a href="../api/admin/leads.php?export=csv" class="btn btn-outline-success btn-sm d-flex align-items-center gap-2 shadow-sm">
      <i class="bi bi-file-earmark-spreadsheet-fill"></i>
      <span>Export ke Excel/CSV</span>
    </a>
  </div>
</div>

<!-- Stats Metric Cards -->
<div class="row g-3 mb-4" id="leadsMetricsRow">
  <div class="col-6 col-md-3 col-xl-2">
    <div class="p-3 bg-white rounded-3 border shadow-xs h-100">
      <div class="text-muted small fw-semibold">Masuk Hari Ini</div>
      <div class="fs-4 fw-bold text-primary mt-1" id="statToday">0</div>
    </div>
  </div>
  <div class="col-6 col-md-3 col-xl-2">
    <div class="p-3 bg-white rounded-3 border shadow-xs h-100">
      <div class="text-muted small fw-semibold">7 Hari Terakhir</div>
      <div class="fs-4 fw-bold text-dark mt-1" id="statWeek">0</div>
    </div>
  </div>
  <div class="col-6 col-md-3 col-xl-2">
    <div class="p-3 bg-white rounded-3 border shadow-xs h-100 border-start border-4 border-warning">
      <div class="text-muted small fw-semibold">Status: Baru</div>
      <div class="fs-4 fw-bold text-warning mt-1" id="statNew">0</div>
    </div>
  </div>
  <div class="col-6 col-md-3 col-xl-2">
    <div class="p-3 bg-white rounded-3 border shadow-xs h-100 border-start border-4 border-info">
      <div class="text-muted small fw-semibold">Status: Dihubungi</div>
      <div class="fs-4 fw-bold text-info mt-1" id="statContacted">0</div>
    </div>
  </div>
  <div class="col-6 col-md-3 col-xl-2">
    <div class="p-3 bg-white rounded-3 border shadow-xs h-100 border-start border-4 border-primary">
      <div class="text-muted small fw-semibold">Meeting / Visit</div>
      <div class="fs-4 fw-bold text-primary mt-1" id="statMeeting">0</div>
    </div>
  </div>
  <div class="col-6 col-md-3 col-xl-2">
    <div class="p-3 bg-white rounded-3 border shadow-xs h-100 border-start border-4 border-success">
      <div class="text-muted small fw-semibold">Closing (Deal)</div>
      <div class="fs-4 fw-bold text-success mt-1" id="statDeal">0</div>
    </div>
  </div>
</div>

<!-- Main Table Card -->
<div class="admin-card">
  <div class="admin-card-header bg-white d-flex flex-wrap align-items-center justify-content-between gap-3">
    <ul class="nav nav-pills" id="leadTypeTabs">
      <li class="nav-item">
        <button class="nav-link active py-1 px-3 small fw-bold" onclick="filterLeadType('')">Semua Kategori</button>
      </li>
      <li class="nav-item">
        <button class="nav-link py-1 px-3 small fw-bold" onclick="filterLeadType('perorangan')">Perorangan</button>
      </li>
      <li class="nav-item">
        <button class="nav-link py-1 px-3 small fw-bold" onclick="filterLeadType('perusahaan')">Perusahaan</button>
      </li>
    </ul>

    <div class="d-flex flex-wrap align-items-center gap-2">
      <input type="text" id="leadSearch" class="form-control form-control-sm" placeholder="Cari nama, perusahaan, email, kota..." style="width: 250px;" oninput="loadLeads()">
      <select id="leadStatusFilter" class="form-select form-select-sm" style="width: 150px;" onchange="loadLeads()">
        <option value="">Semua Status</option>
        <option value="new">Baru</option>
        <option value="contacted">Dihubungi</option>
        <option value="meeting">Meeting</option>
        <option value="site_visit">Site Visit</option>
        <option value="deal">Deal</option>
        <option value="lost">Tidak Lanjut</option>
      </select>
    </div>
  </div>

  <div class="table-responsive">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Tanggal</th>
          <th>Nama / Entitas</th>
          <th>Kategori</th>
          <th>Email Resmi</th>
          <th>Kota Domisili</th>
          <th>Rencana Modal</th>
          <th>Sumber Iklan</th>
          <th>Status Lead</th>
          <th class="text-end">Aksi Tindak Lanjut</th>
        </tr>
      </thead>
      <tbody id="leadsTableBody">
        <tr>
          <td colspan="9" class="text-center py-5 text-muted">
            <span class="spinner-border spinner-border-sm me-2"></span> Memuat data leads...
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</div>

<!-- MODAL UPDATE STATUS & CATATAN LEAD -->
<div class="modal fade" id="leadDetailModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      <div class="modal-header text-white" style="background: #142563;">
        <h5 class="modal-title fw-bold">
          <i class="bi bi-person-lines-fill me-2 text-warning" style="color: #C5A059 !important;"></i>
          <span>Detail &amp; Tindak Lanjut Calon Investor</span>
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-4 bg-light">
        <!-- Lead Info Box -->
        <div class="card p-4 border-0 rounded-3 shadow-sm bg-white mb-3">
          <div class="d-flex align-items-center justify-content-between border-bottom pb-3 mb-3">
            <div>
              <h5 class="fw-bold text-dark mb-1" id="mLeadName">-</h5>
              <div class="text-muted small" id="mLeadSubTitle">-</div>
            </div>
            <span id="mLeadTypeBadge" class="badge bg-primary px-3 py-2 rounded-pill small">Perorangan</span>
          </div>

          <div class="row g-3" id="mLeadGrid">
            <!-- Dynamic fields -->
          </div>
        </div>

        <!-- Follow-up Form -->
        <div class="card p-4 border-0 rounded-3 shadow-sm bg-white">
          <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">Update Status Pipeline &amp; Catatan Tim</h6>
          <form id="leadUpdateForm" onsubmit="saveLeadStatus(event)">
            <input type="hidden" id="mLeadId">
            <div class="row g-3">
              <div class="col-md-4">
                <label class="form-label small fw-bold">Status Pipeline</label>
                <select id="mLeadStatus" class="form-select">
                  <option value="new">Baru (Belum Dihubungi)</option>
                  <option value="contacted">Dihubungi (Follow-up Email/Diskusi)</option>
                  <option value="meeting">Jadwal Meeting Kantor Pusat</option>
                  <option value="site_visit">Site Visit Workshop Kebumen</option>
                  <option value="deal">Deal (Closing)</option>
                  <option value="lost">Tidak Lanjut (Lost)</option>
                </select>
              </div>
              <div class="col-md-4">
                <label class="form-label small fw-bold">PIC Sales / Relationship Manager</label>
                <input type="text" id="mLeadAssigned" class="form-control" placeholder="Contoh: RM Jakarta / Admin 1">
              </div>
              <div class="col-md-4 d-flex align-items-end">
                <a href="#" id="mLeadEmailBtn" class="btn btn-outline-primary w-100 fw-bold d-flex align-items-center justify-content-center gap-2">
                  <i class="bi bi-envelope-fill"></i> Kirim Email
                </a>
              </div>
              <div class="col-12">
                <label class="form-label small fw-bold">Catatan Perkembangan Diskusi (Follow-up Notes)</label>
                <textarea id="mLeadNotes" class="form-control" rows="3" placeholder="Contoh: Klien minta dikirimkan prospektus paket 5M via email dan dijadwalkan meeting di kantor BSD hari Kamis."></textarea>
              </div>
              <div class="col-12 text-end">
                <button type="submit" id="btnSaveLead" class="btn btn-mgi-primary px-4">
                  <i class="bi bi-check2-circle me-1"></i> Simpan Catatan
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
  let currentLeadType = '';
  let leadsData = [];
  let leadModalInstance = null;

  document.addEventListener('DOMContentLoaded', () => {
    leadModalInstance = new bootstrap.Modal(document.getElementById('leadDetailModal'));
    loadLeads();
  });

  function filterLeadType(type) {
    currentLeadType = type;
    document.querySelectorAll('#leadTypeTabs .nav-link').forEach(btn => btn.classList.remove('active'));
    event.target.classList.add('active');
    loadLeads();
  }

  async function loadLeads() {
    const search = document.getElementById('leadSearch').value.trim();
    const status = document.getElementById('leadStatusFilter').value;
    const tb = document.getElementById('leadsTableBody');

    const params = new URLSearchParams();
    if (currentLeadType) params.set('type', currentLeadType);
    if (status) params.set('status', status);
    if (search) params.set('search', search);

    try {
      const res = await fetch(`../api/admin/leads.php?${params.toString()}`);
      const json = await res.json();
      if (!json.success) {
        if (json.status === 401) window.location.href = 'login.php';
        return;
      }

      leadsData = json.data.leads || [];

      // Update Metric Cards
      const stats = json.data.stats || {};
      document.getElementById('statToday').textContent = json.data.today || 0;
      document.getElementById('statWeek').textContent = json.data.week || 0;
      document.getElementById('statNew').textContent = stats.new || 0;
      document.getElementById('statContacted').textContent = stats.contacted || 0;
      document.getElementById('statMeeting').textContent = (stats.meeting || 0) + (stats.site_visit || 0);
      document.getElementById('statDeal').textContent = stats.deal || 0;

      if (leadsData.length === 0) {
        tb.innerHTML = `<tr><td colspan="9" class="text-center py-5 text-muted">Belum ada leads yang cocok dengan filter.</td></tr>`;
        return;
      }

      tb.innerHTML = leadsData.map(l => {
        const isCorp = l.account_type === 'perusahaan';
        const displayName = isCorp ? (l.business_name || '-') : l.full_name;
        const subName = isCorp ? `PIC: ${l.full_name} (${l.pic_position || '-'})` : (l.email || '-');
        
        let statusBadge = `<span class="badge bg-warning text-dark">Baru</span>`;
        if (l.status === 'contacted') statusBadge = `<span class="badge bg-info text-white">Dihubungi</span>`;
        if (l.status === 'meeting') statusBadge = `<span class="badge bg-primary text-white">Meeting</span>`;
        if (l.status === 'site_visit') statusBadge = `<span class="badge bg-purple text-white" style="background:#6f42c1;">Site Visit</span>`;
        if (l.status === 'deal') statusBadge = `<span class="badge bg-success text-white">Deal</span>`;
        if (l.status === 'lost') statusBadge = `<span class="badge bg-secondary text-white">Tidak Lanjut</span>`;

        const dateStr = new Date(l.created_at).toLocaleDateString('id-ID', {
          day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit'
        });

        const sourceLabel = l.utm_source ? `${l.utm_source}` : (l.gclid ? 'Google Ads' : 'Langsung');
        const mailtoSub = encodeURIComponent(`Tindak Lanjut Konsultasi Investasi Proyek — PT Montana Global Investama`);
        const mailtoLink = l.email ? `mailto:${l.email}?subject=${mailtoSub}` : '#';

        return `
          <tr>
            <td class="small text-muted" style="white-space: nowrap;">${dateStr}</td>
            <td>
              <div class="fw-bold text-dark">${displayName}</div>
              <small class="text-muted">${subName}</small>
            </td>
            <td>
              <span class="badge ${isCorp ? 'bg-primary' : 'bg-secondary'}" style="${isCorp ? 'background: #142563 !important;' : ''}">
                ${isCorp ? 'Perusahaan' : 'Perorangan'}
              </span>
            </td>
            <td>
              ${l.email ? `
                <a href="${mailtoLink}" class="fw-semibold text-primary text-decoration-none d-inline-flex align-items-center gap-1">
                  <i class="bi bi-envelope"></i> ${l.email}
                </a>
              ` : '<span class="text-muted">-</span>'}
            </td>
            <td>${l.city || '-'}</td>
            <td>
              <span class="badge bg-light text-dark border fw-bold">${l.investment_range || '-'}</span>
            </td>
            <td>
              <span class="badge bg-light text-secondary border small">${sourceLabel}</span>
              ${l.utm_campaign ? `<div class="text-muted" style="font-size:0.7rem;">${l.utm_campaign}</div>` : ''}
            </td>
            <td>${statusBadge}</td>
            <td class="text-end" style="white-space: nowrap;">
              ${l.email ? `
                <a href="${mailtoLink}" class="btn btn-sm btn-outline-secondary py-1 px-2.5 d-inline-flex align-items-center gap-1 me-1" title="Kirim Email Resmi">
                  <i class="bi bi-envelope-fill"></i>
                  <span class="small fw-semibold">Email</span>
                </a>
              ` : ''}
              <button type="button" class="btn btn-sm btn-primary py-1 px-2.5 d-inline-flex align-items-center gap-1" onclick="viewLeadDetail(${l.id})">
                <i class="bi bi-pencil-square"></i>
                <span>Kelola</span>
              </button>
            </td>
          </tr>
        `;
      }).join('');

    } catch (e) {
      console.error(e);
      AdminApp.showToast('Gagal memuat data leads.', 'danger');
    }
  }

  function viewLeadDetail(id) {
    const l = leadsData.find(i => Number(i.id) === Number(id));
    if (!l) return;

    const isCorp = l.account_type === 'perusahaan';
    document.getElementById('mLeadId').value = l.id;
    document.getElementById('mLeadName').textContent = isCorp ? l.business_name : l.full_name;
    document.getElementById('mLeadSubTitle').textContent = isCorp ? `PIC: ${l.full_name} (${l.pic_position || '-'})` : (l.email || 'Investor Perorangan');
    document.getElementById('mLeadTypeBadge').textContent = isCorp ? 'Investor Perusahaan' : 'Investor Perorangan';
    document.getElementById('mLeadStatus').value = l.status;
    document.getElementById('mLeadAssigned').value = l.assigned_to || '';
    document.getElementById('mLeadNotes').value = l.admin_notes || '';

    // Set Email link
    const emailSubject = encodeURIComponent(`Tindak Lanjut Konsultasi Investasi Proyek — PT Montana Global Investama`);
    const emailBtn = document.getElementById('mLeadEmailBtn');
    if (l.email) {
      emailBtn.href = `mailto:${l.email}?subject=${emailSubject}`;
      emailBtn.classList.remove('disabled');
    } else {
      emailBtn.href = '#';
      emailBtn.classList.add('disabled');
    }

    const grid = document.getElementById('mLeadGrid');
    grid.innerHTML = `
      <div class="col-md-6">
        <small class="text-muted d-block">Alamat Email Resmi</small>
        <a href="mailto:${l.email || ''}" class="fs-6 fw-bold text-primary text-decoration-none">
          <i class="bi bi-envelope me-1"></i>${l.email || '-'}
        </a>
      </div>
      <div class="col-md-6">
        <small class="text-muted d-block">Kota Domisili</small>
        <strong>${l.city || '-'}</strong>
      </div>
      <div class="col-md-6">
        <small class="text-muted d-block">Rencana Nominal Penempatan Modal</small>
        <strong class="text-success fs-6">${l.investment_range || '-'}</strong>
      </div>
      <div class="col-md-6">
        <small class="text-muted d-block">Kategori Calon Investor</small>
        <strong>${isCorp ? 'Perusahaan / Korporasi' : 'Perorangan / Individu'}</strong>
      </div>
      ${isCorp ? `
        <div class="col-md-6">
          <small class="text-muted d-block">Badan Hukum</small>
          <strong>${l.legal_entity || '-'}</strong>
        </div>
        <div class="col-md-6">
          <small class="text-muted d-block">Jabatan PIC</small>
          <strong>${l.pic_position || '-'}</strong>
        </div>
      ` : ''}
      <div class="col-md-6">
        <small class="text-muted d-block">Sumber Kampanye / Iklan</small>
        <span class="badge bg-light text-dark border">${l.utm_source || (l.gclid ? 'Google Ads' : 'Organik')}</span>
        ${l.utm_term ? `<small class="text-muted d-block mt-1">Keyword: <em>${l.utm_term}</em></small>` : ''}
      </div>
      <div class="col-md-6">
        <small class="text-muted d-block">Waktu Konsultasi Masuk</small>
        <small class="text-secondary">${new Date(l.created_at).toLocaleString('id-ID')}</small>
      </div>
    `;

    leadModalInstance.show();
  }

  async function saveLeadStatus(e) {
    e.preventDefault();
    const id = document.getElementById('mLeadId').value;
    const status = document.getElementById('mLeadStatus').value;
    const assigned_to = document.getElementById('mLeadAssigned').value.trim();
    const admin_notes = document.getElementById('mLeadNotes').value.trim();
    const btn = document.getElementById('btnSaveLead');

    btn.disabled = true;
    btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span> Menyimpan...`;

    try {
      const res = await fetch('../api/admin/leads.php', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id, status, assigned_to, admin_notes })
      });
      const json = await res.json();

      if (json.success) {
        AdminApp.showToast(json.message, 'success');
        leadModalInstance.hide();
        loadLeads();
      } else {
        AdminApp.showToast(json.message, 'danger');
      }
    } catch (err) {
      AdminApp.showToast('Gagal memperbarui status leads.', 'danger');
    } finally {
      btn.disabled = false;
      btn.innerHTML = `<i class="bi bi-check2-circle me-1"></i> Simpan Catatan`;
    }
  }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
