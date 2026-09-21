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

<!-- Shortcuts Banner -->
<div class="admin-card p-3 mb-4 bg-white">
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
    <div class="d-flex align-items-center gap-2">
      <span class="badge bg-royal text-white px-2 py-1" style="background: #142563;">Navigasi Cepat</span>
      <span class="text-secondary small">Akses instan modul manajemen operasional:</span>
    </div>
    <div class="d-flex flex-wrap gap-2">
      <a href="projects.php" class="btn btn-sm btn-outline-dark"><i class="bi bi-briefcase me-1"></i> Semua Proyek</a>
      <a href="projects.php?action=create" class="btn btn-sm btn-mgi-gold"><i class="bi bi-plus-circle me-1"></i> Buat Proyek Baru</a>
      <a href="investors.php" class="btn btn-sm btn-outline-dark"><i class="bi bi-person-check me-1"></i> Verifikasi Investor</a>
      <a href="content.php" class="btn btn-sm btn-outline-dark"><i class="bi bi-clock-history me-1"></i> Milestone Transformasi</a>
      <a href="settings.php" class="btn btn-sm btn-outline-secondary"><i class="bi bi-sliders me-1"></i> Gated Content Toggle</a>
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
  document.addEventListener('DOMContentLoaded', async () => {
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
  });
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
