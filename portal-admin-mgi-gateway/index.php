<?php
$pageTitle = 'Dashboard Overview';
require_once __DIR__ . '/includes/header.php';
?>

<!-- KPI Metrics Section -->
<div class="row g-3 mb-4">
  <div class="col-12 col-sm-6 col-xl-3">
    <div class="kpi-card">
      <div class="kpi-icon"><i class="bi bi-wallet2"></i></div>
      <div>
        <div class="kpi-label">Dana Terhimpun</div>
        <div class="kpi-value" id="kpiCollected">Rp ...</div>
        <small class="text-muted" id="kpiTargetRatio">Target: Rp ...</small>
      </div>
    </div>
  </div>

  <div class="col-12 col-sm-6 col-xl-3">
    <div class="kpi-card">
      <div class="kpi-icon" style="background: rgba(20, 37, 99, 0.1); color: #142563;"><i class="bi bi-pie-chart-fill"></i></div>
      <div>
        <div class="kpi-label">Progres Portofolio</div>
        <div class="kpi-value" id="kpiPercent">0%</div>
        <small class="text-success fw-semibold" id="kpiOpenProjects">0 Proyek Open</small>
      </div>
    </div>
  </div>

  <div class="col-12 col-sm-6 col-xl-3">
    <div class="kpi-card">
      <div class="kpi-icon" style="background: rgba(16, 185, 129, 0.1); color: #10B981;"><i class="bi bi-people-fill"></i></div>
      <div>
        <div class="kpi-label">Investor Terdaftar</div>
        <div class="kpi-value" id="kpiTotalInvestors">0</div>
        <small class="text-muted" id="kpiInvestorSplit">0 Perorangan | 0 PT/CV</small>
      </div>
    </div>
  </div>

  <div class="col-12 col-sm-6 col-xl-3">
    <div class="kpi-card">
      <div class="kpi-icon" style="background: rgba(245, 158, 11, 0.1); color: #F59E0B;"><i class="bi bi-briefcase-fill"></i></div>
      <div>
        <div class="kpi-label">Total Proyek</div>
        <div class="kpi-value" id="kpiTotalProjects">0</div>
        <small class="text-muted">Instrumen Sektor Riil</small>
      </div>
    </div>
  </div>
</div>

<!-- Action Center / Pusat Tindakan Prioritas Operasional -->
<div class="admin-card mb-4 border-0 shadow-sm" id="actionCenterCard" style="background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);">
  <div class="admin-card-header bg-transparent border-bottom py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
    <div class="d-flex align-items-center gap-2">
      <div class="p-2 rounded-3 bg-danger-subtle text-danger">
        <i class="bi bi-bell-fill fs-5"></i>
      </div>
      <div>
        <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
          <span>Pusat Tindakan &amp; Kepatuhan Operasional (Action Center)</span>
          <span class="badge bg-secondary rounded-pill px-2.5 py-1" id="actionCenterBadge" style="font-size: 0.72rem;">Memeriksa...</span>
        </h6>
        <small class="text-muted">Antrean verifikasi pembayaran, tindak lanjut calon investor baru, dan pengingat jatuh tempo</small>
      </div>
    </div>
    <div class="d-flex align-items-center gap-2">
      <button type="button" class="btn btn-sm btn-outline-secondary" onclick="loadActionCenterData()">
        <i class="bi bi-arrow-clockwise me-1"></i> Refresh Antrean
      </button>
    </div>
  </div>

  <div class="p-3">
    <!-- Quick Filter / Metric Counter Pills -->
    <div class="row g-2 mb-3">
      <div class="col-6 col-md-3">
        <div class="p-2.5 rounded-3 border bg-white d-flex align-items-center justify-content-between cursor-pointer shadow-xs" onclick="filterActionCenterTab('payments')" id="pillPayments" style="cursor: pointer; transition: all 0.2s ease;">
          <div class="d-flex align-items-center gap-2">
            <span class="badge bg-warning text-dark rounded-circle p-2"><i class="bi bi-receipt-cutoff"></i></span>
            <div>
              <div class="text-muted small fw-semibold" style="font-size: 0.75rem;">Verifikasi Bayar</div>
              <div class="fw-bold text-dark fs-6" id="cntUnverifiedPayments">0</div>
            </div>
          </div>
          <span class="badge bg-warning-subtle text-dark border border-warning-subtle small">Cek Bukti</span>
        </div>
      </div>

      <div class="col-6 col-md-3">
        <div class="p-2.5 rounded-3 border bg-white d-flex align-items-center justify-content-between cursor-pointer shadow-xs" onclick="filterActionCenterTab('leads')" id="pillLeads" style="cursor: pointer; transition: all 0.2s ease;">
          <div class="d-flex align-items-center gap-2">
            <span class="badge bg-danger text-white rounded-circle p-2"><i class="bi bi-person-exclamation"></i></span>
            <div>
              <div class="text-muted small fw-semibold" style="font-size: 0.75rem;">Lead &gt;24 Jam</div>
              <div class="fw-bold text-dark fs-6" id="cntLeadsOver24h">0</div>
            </div>
          </div>
          <span class="badge bg-danger-subtle text-danger border border-danger-subtle small">Follow Up</span>
        </div>
      </div>

      <div class="col-6 col-md-3">
        <div class="p-2.5 rounded-3 border bg-white d-flex align-items-center justify-content-between cursor-pointer shadow-xs" onclick="filterActionCenterTab('investors')" id="pillInvestors" style="cursor: pointer; transition: all 0.2s ease;">
          <div class="d-flex align-items-center gap-2">
            <span class="badge bg-primary text-white rounded-circle p-2" style="background: #142563 !important;"><i class="bi bi-person-check-fill"></i></span>
            <div>
              <div class="text-muted small fw-semibold" style="font-size: 0.75rem;">Investor Pending</div>
              <div class="fw-bold text-dark fs-6" id="cntPendingInvestors">0</div>
            </div>
          </div>
          <span class="badge bg-primary-subtle text-primary border border-primary-subtle small">Verifikasi</span>
        </div>
      </div>

      <div class="col-6 col-md-3">
        <div class="p-2.5 rounded-3 border bg-white d-flex align-items-center justify-content-between cursor-pointer shadow-xs" onclick="filterActionCenterTab('overdue')" id="pillOverdue" style="cursor: pointer; transition: all 0.2s ease;">
          <div class="d-flex align-items-center gap-2">
            <span class="badge bg-danger text-white rounded-circle p-2"><i class="bi bi-clock-history"></i></span>
            <div>
              <div class="text-muted small fw-semibold" style="font-size: 0.75rem;">Lewat Jatuh Tempo</div>
              <div class="fw-bold text-dark fs-6" id="cntOverdueBillings">0</div>
            </div>
          </div>
          <span class="badge bg-danger-subtle text-danger border border-danger-subtle small">Ingatkan</span>
        </div>
      </div>
    </div>

    <!-- Priority Action Items Container -->
    <div id="actionCenterItemsContainer">
      <div class="text-center py-4 text-muted small">
        <span class="spinner-border spinner-border-sm me-2"></span> Memeriksa antrean tindakan operasional...
      </div>
    </div>
  </div>
</div>

<!-- Shortcuts Banner -->
<div class="admin-card p-3 mb-4 bg-white">
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
    <div class="d-flex align-items-center gap-2">
      <span class="badge bg-royal text-white px-2 py-1" style="background: #142563;">Navigasi Cepat</span>
      <span class="text-secondary small">Akses instan modul manajemen operasional:</span>
    </div>
    <div class="d-flex flex-wrap gap-2">
      <a href="leads.php" class="btn btn-sm btn-primary"><i class="bi bi-person-lines-fill me-1"></i> Calon Investor (Konsultasi)</a>
      <a href="projects.php" class="btn btn-sm btn-outline-dark"><i class="bi bi-briefcase me-1"></i> Semua Proyek</a>
      <a href="projects.php?action=create" class="btn btn-sm btn-mgi-gold"><i class="bi bi-plus-circle me-1"></i> Buat Proyek Baru</a>
      <a href="investors.php" class="btn btn-sm btn-outline-dark"><i class="bi bi-person-check me-1"></i> Verifikasi Investor</a>
      <a href="financial.php" class="btn btn-sm btn-outline-dark"><i class="bi bi-receipt me-1"></i> Keuangan &amp; Billing</a>
      <a href="content.php" class="btn btn-sm btn-outline-dark"><i class="bi bi-clock-history me-1"></i> Milestone Transformasi</a>
    </div>
  </div>
</div>

<div class="row g-4">
  <!-- Daftar Proyek Terkini -->
  <div class="col-12 col-xl-8">
    <div class="admin-card mb-0 h-100">
      <div class="admin-card-header">
        <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
          <i class="bi bi-bar-chart-steps text-warning" style="color: #C5A059 !important;"></i>
          <span>Katalog Proyek Investasi MGI</span>
        </h6>
        <a href="projects.php" class="btn btn-sm btn-link text-decoration-none">Kelola Seluruhnya &rarr;</a>
      </div>
      <div class="table-responsive">
        <table class="admin-table">
          <thead>
            <tr>
              <th>Proyek</th>
              <th>Status</th>
              <th>Target &amp; Terkumpul</th>
              <th>Progres</th>
              <th class="text-end">Aksi</th>
            </tr>
          </thead>
          <tbody id="projectsTableBody">
            <tr>
              <td colspan="5" class="text-center py-4 text-muted">
                <span class="spinner-border spinner-border-sm me-2"></span> Memuat data proyek...
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Audit Log Aktivitas -->
  <div class="col-12 col-xl-4">
    <div class="admin-card mb-0 h-100">
      <div class="admin-card-header">
        <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
          <i class="bi bi-shield-check text-success"></i>
          <span>Log Kepatuhan &amp; Tata Kelola</span>
        </h6>
      </div>
      <div class="admin-card-body p-0">
        <div class="list-group list-group-flush" id="auditLogsList" style="max-height: 420px; overflow-y: auto;">
          <div class="text-center py-4 text-muted small">
            <span class="spinner-border spinner-border-sm me-2"></span> Memuat log aktivitas...
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  let cachedActionCenter = null;
  let currentActionTab = 'all';

  document.addEventListener('DOMContentLoaded', async () => {
    await loadDashboardStats();
  });

  async function loadDashboardStats() {
    try {
      const res = await fetch('../api/admin/stats.php');
      const json = await res.json();
      if (!json.success) {
        if (json.status === 401) window.location.href = 'login.php';
        return;
      }

      const m = json.data.metrics;
      document.getElementById('kpiCollected').textContent = AdminApp.formatCompact(m.total_collected);
      document.getElementById('kpiTargetRatio').textContent = `Target: ${AdminApp.formatCompact(m.total_target)}`;
      document.getElementById('kpiPercent').textContent = `${m.funding_percentage}%`;
      document.getElementById('kpiOpenProjects').textContent = `${m.open_projects} Proyek Open (${m.funded_projects} Funded)`;
      document.getElementById('kpiTotalInvestors').textContent = m.total_investors;
      document.getElementById('kpiInvestorSplit').textContent = `${m.total_individual} Perorangan | ${m.total_corporate} Korporasi`;
      document.getElementById('kpiTotalProjects').textContent = m.total_projects;

      // Cache & Render Action Center
      cachedActionCenter = json.data.action_center || {};
      renderActionCenter();

      // Render Recent Projects Table
      const tb = document.getElementById('projectsTableBody');
      const projects = json.data.recent_projects || [];
      if (projects.length === 0) {
        tb.innerHTML = `<tr><td colspan="5" class="text-center py-4 text-muted">Belum ada proyek terdaftar.</td></tr>`;
      } else {
        tb.innerHTML = projects.map(p => {
          const target = Number(p.funding_target);
          const collected = Number(p.funding_collected);
          const pct = target > 0 ? Math.min(100, Math.round((collected / target) * 100)) : 0;
          
          let statusBadgeClass = 'badge-open';
          if (p.status === 'Fully Funded') statusBadgeClass = 'badge-funded';
          if (p.status === 'Coming Soon') statusBadgeClass = 'badge-coming';
          if (p.status === 'Closed') statusBadgeClass = 'badge-closed';

          return `
            <tr>
              <td>
                <div class="fw-bold text-dark">${p.title}</div>
                <small class="text-muted">${p.category || 'Alat Berat'} &bull; ID: <code>${p.id}</code></small>
              </td>
              <td><span class="badge-status ${statusBadgeClass}">${p.status}</span></td>
              <td>
                <div class="fw-semibold text-dark">${AdminApp.formatCompact(collected)}</div>
                <small class="text-muted">dari ${AdminApp.formatCompact(target)}</small>
              </td>
              <td style="min-width: 140px;">
                <div class="d-flex align-items-center gap-2">
                  <div class="progress flex-grow-1" style="height: 6px;">
                    <div class="progress-bar" style="width: ${pct}%; background: #C5A059;"></div>
                  </div>
                  <small class="fw-bold text-muted">${pct}%</small>
                </div>
              </td>
              <td class="text-end">
                <a href="projects.php?action=edit&id=${encodeURIComponent(p.id)}" class="btn btn-sm btn-outline-primary py-1 px-2" title="Edit Proyek">
                  <i class="bi bi-pencil-square"></i>
                </a>
              </td>
            </tr>
          `;
        }).join('');
      }

      // Render Audit Logs
      const logsContainer = document.getElementById('auditLogsList');
      const logs = json.data.recent_logs || [];
      if (logs.length === 0) {
        logsContainer.innerHTML = `<div class="p-3 text-center text-muted small">Belum ada catatan log aktivitas.</div>`;
      } else {
        logsContainer.innerHTML = logs.map(l => {
          const dateStr = new Date(l.created_at).toLocaleDateString('id-ID', {
            day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit'
          });
          return `
            <div class="list-group-item px-3 py-2 border-start-0 border-end-0">
              <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="badge bg-light text-dark border small">${l.action.toUpperCase()}</span>
                <small class="text-muted" style="font-size: 0.72rem;">${dateStr}</small>
              </div>
              <div class="small text-dark fw-medium lh-sm">${l.details || l.target_type}</div>
              <small class="text-muted" style="font-size: 0.72rem;">Oleh: <strong>${l.admin_username || 'System'}</strong> &bull; IP: ${l.ip_address || '-'}</small>
            </div>
          `;
        }).join('');
      }

    } catch (e) {
      console.error('Failed to load admin stats:', e);
    }
  }

  function loadActionCenterData() {
    loadDashboardStats();
  }

  function filterActionCenterTab(tab) {
    currentActionTab = (currentActionTab === tab) ? 'all' : tab;
    renderActionCenter();
  }

  function renderActionCenter() {
    if (!cachedActionCenter) return;
    const ac = cachedActionCenter;

    const cntPay = ac.unverified_payments_count || 0;
    const cntLead = ac.leads_over_24h_count || 0;
    const cntInv = ac.pending_investors_count || 0;
    const cntOver = ac.overdue_billings_count || 0;
    const total = cntPay + cntLead + cntInv + cntOver;

    document.getElementById('cntUnverifiedPayments').textContent = cntPay;
    document.getElementById('cntLeadsOver24h').textContent = cntLead;
    document.getElementById('cntPendingInvestors').textContent = cntInv;
    document.getElementById('cntOverdueBillings').textContent = cntOver;

    const badge = document.getElementById('actionCenterBadge');
    if (total > 0) {
      badge.textContent = `${total} Perlu Tindakan Segera`;
      badge.className = 'badge bg-danger rounded-pill px-2.5 py-1';
    } else {
      badge.textContent = 'Semua Terkendali';
      badge.className = 'badge bg-success rounded-pill px-2.5 py-1';
    }

    // Highlight active pill
    ['pillPayments', 'pillLeads', 'pillInvestors', 'pillOverdue'].forEach(id => {
      const el = document.getElementById(id);
      if (el) el.classList.remove('border-primary', 'bg-light');
    });
    if (currentActionTab === 'payments') document.getElementById('pillPayments')?.classList.add('border-primary', 'bg-light');
    if (currentActionTab === 'leads') document.getElementById('pillLeads')?.classList.add('border-primary', 'bg-light');
    if (currentActionTab === 'investors') document.getElementById('pillInvestors')?.classList.add('border-primary', 'bg-light');
    if (currentActionTab === 'overdue') document.getElementById('pillOverdue')?.classList.add('border-primary', 'bg-light');

    const container = document.getElementById('actionCenterItemsContainer');
    
    // Build array of items
    let items = [];

    if (currentActionTab === 'all' || currentActionTab === 'payments') {
      (ac.action_payments || []).forEach(p => {
        items.push({
          type: 'payment',
          title: `Bukti Transfer Perlu Diverifikasi: ${p.title} (${p.record_number})`,
          subtitle: `Investor: <strong>${p.investor_name}</strong> &bull; Proyek: <strong>${p.project_title}</strong> &bull; Nominal: <strong class="text-success">Rp ${new Intl.NumberFormat('id-ID').format(Math.round(p.amount))}</strong>`,
          date: p.paid_at ? new Date(p.paid_at).toLocaleString('id-ID', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' }) : '-',
          actionUrl: `financial.php?billing_only=1`,
          actionText: 'Tinjau Bukti Bayar',
          actionBtnClass: 'btn-warning text-dark fw-bold',
          badgeText: 'BUKTI BAYAR',
          badgeClass: 'bg-warning text-dark'
        });
      });
    }

    if (currentActionTab === 'all' || currentActionTab === 'leads') {
      (ac.action_leads || []).forEach(l => {
        const isOver = Boolean(Number(l.is_over_24h));
        items.push({
          type: 'lead',
          title: `Calon Investor Baru Perlu Dihubungi: ${l.full_name}`,
          subtitle: `Email: <strong>${l.email}</strong> &bull; Tipe: <strong>${l.account_type || 'Perorangan'}</strong> &bull; Status: <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Belum Dihubungi</span>`,
          date: new Date(l.created_at).toLocaleString('id-ID', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' }),
          actionUrl: `leads.php`,
          actionText: 'Tindak Lanjut CRM',
          actionBtnClass: 'btn-danger text-white fw-bold',
          badgeText: isOver ? 'LEAD > 24 JAM' : 'LEAD BARU',
          badgeClass: isOver ? 'bg-danger text-white' : 'bg-primary text-white'
        });
      });
    }

    if (currentActionTab === 'all' || currentActionTab === 'investors') {
      (ac.action_investors || []).forEach(i => {
        items.push({
          type: 'investor',
          title: `Pendaftaran Investor Menunggu Verifikasi KYC: ${i.full_name}`,
          subtitle: `Email: <strong>${i.email}</strong> &bull; Tipe Akun: <strong>${i.account_type || 'Perorangan'}</strong>`,
          date: new Date(i.created_at).toLocaleString('id-ID', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' }),
          actionUrl: `investors.php`,
          actionText: 'Verifikasi Akun',
          actionBtnClass: 'btn-primary text-white fw-bold',
          badgeText: 'KYC PENDING',
          badgeClass: 'bg-primary text-white'
        });
      });
    }

    if (currentActionTab === 'all' || currentActionTab === 'overdue') {
      (ac.action_overdue || []).forEach(o => {
        items.push({
          type: 'overdue',
          title: `Tagihan Lewat Jatuh Tempo (${o.days_overdue} Hari): ${o.title}`,
          subtitle: `Faktur: <strong>${o.record_number}</strong> &bull; Investor: <strong>${o.investor_name}</strong> &bull; Nominal: <strong class="text-danger">Rp ${new Intl.NumberFormat('id-ID').format(Math.round(o.amount))}</strong>`,
          date: 'Jatuh tempo: ' + new Date(o.due_date).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }),
          actionUrl: `financial.php?billing_only=1`,
          actionText: 'Kirim Pengingat',
          actionBtnClass: 'btn-outline-danger fw-bold',
          badgeText: 'JATUH TEMPO',
          badgeClass: 'bg-danger text-white'
        });
      });
    }

    if (items.length === 0) {
      container.innerHTML = `
        <div class="text-center py-4 text-muted bg-white rounded-3 border">
          <i class="bi bi-shield-check text-success fs-1 d-block mb-1"></i>
          <span class="fw-bold text-dark">Tidak ada antrean tertunda untuk kategori ini.</span>
          <div class="small text-muted mt-1">Semua konfirmasi pembayaran, calon investor baru, dan pengingat jatuh tempo telah diselesaikan.</div>
        </div>
      `;
      return;
    }

    container.innerHTML = `
      <div class="list-group list-group-flush border rounded-3 bg-white overflow-hidden shadow-xs">
        ${items.map(it => `
          <div class="list-group-item p-3 d-flex flex-wrap align-items-center justify-content-between gap-3 border-bottom">
            <div class="d-flex align-items-start gap-3 flex-grow-1" style="min-width: 280px;">
              <span class="badge ${it.badgeClass} mt-1 text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.5px;">${it.badgeText}</span>
              <div>
                <div class="fw-bold text-dark mb-0.5">${it.title}</div>
                <div class="small text-muted mb-1">${it.subtitle}</div>
                <small class="text-secondary" style="font-size: 0.72rem;"><i class="bi bi-clock me-1"></i>${it.date}</small>
              </div>
            </div>
            <div class="d-flex align-items-center gap-2">
              <a href="${it.actionUrl}" class="btn btn-sm ${it.actionBtnClass} px-3 py-1.5 shadow-sm d-inline-flex align-items-center gap-1">
                <span>${it.actionText}</span>
                <i class="bi bi-arrow-right-short fs-6"></i>
              </a>
            </div>
          </div>
        `).join('')}
      </div>
    `;
  }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
