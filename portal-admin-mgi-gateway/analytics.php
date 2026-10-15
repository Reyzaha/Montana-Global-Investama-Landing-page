<?php
$pageTitle = 'Analytics Ringkas';
require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
  <div>
    <h4 class="fw-bold text-dark mb-1">Analytics Ringkas &amp; Konversi</h4>
    <p class="text-muted small mb-0">Pantau performa traffic pengunjung, interaksi klik tombol CTA, serta konversi minat investor secara langsung dari database.</p>
  </div>
  <button type="button" class="btn btn-outline-secondary d-flex align-items-center gap-2 shadow-sm" onclick="loadAnalyticsData()">
    <i class="bi bi-arrow-clockwise"></i>
    <span>Perbarui Data</span>
  </button>
</div>

<!-- Alert Box -->
<div id="analyticsAlertContainer"></div>

<!-- 4 KPI Cards -->
<div class="row g-3 mb-4">
  <div class="col-6 col-lg-3">
    <div class="p-3 bg-white rounded-3 border shadow-xs h-100 border-start border-4 border-primary">
      <div class="d-flex align-items-center justify-content-between">
        <span class="text-muted small fw-semibold">Kunjungan Halaman</span>
        <i class="bi bi-eye text-primary fs-5"></i>
      </div>
      <div class="fs-3 fw-bold text-dark mt-2" id="kpiViewsTotal">0</div>
      <div class="small text-muted mt-1">
        <span class="text-success fw-bold" id="kpiViewsToday">+0</span> hari ini • <span id="kpiViews7d">0</span> (7 hr)
      </div>
    </div>
  </div>

  <div class="col-6 col-lg-3">
    <div class="p-3 bg-white rounded-3 border shadow-xs h-100 border-start border-4 border-warning">
      <div class="d-flex align-items-center justify-content-between">
        <span class="text-muted small fw-semibold">Klik Tombol CTA</span>
        <i class="bi bi-cursor-fill text-warning fs-5" style="color: #C5A059 !important;"></i>
      </div>
      <div class="fs-3 fw-bold text-dark mt-2" id="kpiClicksTotal">0</div>
      <div class="small text-muted mt-1">
        <span class="text-warning fw-bold" id="kpiClicksToday">+0</span> hari ini • <span id="kpiClicks7d">0</span> (7 hr)
      </div>
    </div>
  </div>

  <div class="col-6 col-lg-3">
    <div class="p-3 bg-white rounded-3 border shadow-xs h-100 border-start border-4 border-success">
      <div class="d-flex align-items-center justify-content-between">
        <span class="text-muted small fw-semibold">Total Konversi</span>
        <i class="bi bi-person-check-fill text-success fs-5"></i>
      </div>
      <div class="fs-3 fw-bold text-dark mt-2" id="kpiConvTotal">0</div>
      <div class="small text-muted mt-1">
        <span class="text-success fw-bold" id="kpiConvToday">+0</span> hari ini • <span id="kpiConv7d">0</span> (7 hr)
      </div>
    </div>
  </div>

  <div class="col-6 col-lg-3">
    <div class="p-3 bg-white rounded-3 border shadow-xs h-100 border-start border-4 border-info">
      <div class="d-flex align-items-center justify-content-between">
        <span class="text-muted small fw-semibold">Tingkat Konversi</span>
        <i class="bi bi-pie-chart-fill text-info fs-5"></i>
      </div>
      <div class="fs-3 fw-bold text-dark mt-2" id="kpiConvRate">0%</div>
      <div class="small text-muted mt-1">Rasio aksi terhadap traffic</div>
    </div>
  </div>
</div>

<!-- 2 Columns: Top Pages & Top CTA Buttons -->
<div class="row g-4 mb-4">
  <div class="col-12 col-lg-6">
    <div class="admin-card h-100">
      <div class="admin-card-header bg-white p-3 border-bottom d-flex align-items-center justify-content-between">
        <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
          <i class="bi bi-file-earmark-bar-graph text-primary" style="color: #142563 !important;"></i>
          <span>Halaman Paling Sering Dikunjungi</span>
        </h6>
      </div>
      <div class="table-responsive">
        <table class="admin-table table-hover mb-0">
          <thead>
            <tr>
              <th>URL Halaman</th>
              <th class="text-end" style="width: 120px;">Jumlah Kunjungan</th>
            </tr>
          </thead>
          <tbody id="topPagesBody">
            <tr>
              <td colspan="2" class="text-center py-3 text-muted">Memuat data...</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="col-12 col-lg-6">
    <div class="admin-card h-100">
      <div class="admin-card-header bg-white p-3 border-bottom d-flex align-items-center justify-content-between">
        <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
          <i class="bi bi-hand-index-thumb-fill text-warning" style="color: #C5A059 !important;"></i>
          <span>Tombol CTA Paling Diminati</span>
        </h6>
      </div>
      <div class="table-responsive">
        <table class="admin-table table-hover mb-0">
          <thead>
            <tr>
              <th>Elemen / Tombol CTA</th>
              <th class="text-end" style="width: 120px;">Jumlah Klik</th>
            </tr>
          </thead>
          <tbody id="topCtasBody">
            <tr>
              <td colspan="2" class="text-center py-3 text-muted">Memuat data...</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- 7-Day Performance Trend & Live Activity Feed -->
<div class="row g-4">
  <div class="col-12 col-lg-6">
    <div class="admin-card h-100">
      <div class="admin-card-header bg-white p-3 border-bottom">
        <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
          <i class="bi bi-graph-up-arrow text-success"></i>
          <span>Tren Kinerja 7 Hari Terakhir</span>
        </h6>
      </div>
      <div class="table-responsive">
        <table class="admin-table table-hover mb-0">
          <thead>
            <tr>
              <th>Tanggal</th>
              <th class="text-center">Kunjungan</th>
              <th class="text-center">Klik CTA</th>
              <th class="text-end">Konversi</th>
            </tr>
          </thead>
          <tbody id="dailyTrendBody">
            <tr>
              <td colspan="4" class="text-center py-3 text-muted">Memuat tren...</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="col-12 col-lg-6">
    <div class="admin-card h-100">
      <div class="admin-card-header bg-white p-3 border-bottom">
        <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
          <i class="bi bi-broadcast text-danger"></i>
          <span>Live Telemetry (15 Aktivitas Terakhir)</span>
        </h6>
      </div>
      <div class="table-responsive">
        <table class="admin-table table-hover mb-0">
          <thead>
            <tr>
              <th>Tipe Event</th>
              <th>Target / Halaman</th>
              <th>Waktu</th>
            </tr>
          </thead>
          <tbody id="recentEventsBody">
            <tr>
              <td colspan="3" class="text-center py-3 text-muted">Memuat aktivitas...</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  loadAnalyticsData();
});

async function loadAnalyticsData() {
  try {
    const res = await fetch('../api/admin/analytics.php');
    const result = await res.json();

    if (!res.ok || !result.success) {
      throw new Error(result.message || 'Gagal memuat analitik');
    }

    const data = result.data;
    const kpi = data.kpi || {};

    // Populate KPIs
    document.getElementById('kpiViewsTotal').textContent = (kpi.views_total || 0).toLocaleString();
    document.getElementById('kpiViewsToday').textContent = '+' + (kpi.views_today || 0).toLocaleString();
    document.getElementById('kpiViews7d').textContent = (kpi.views_7d || 0).toLocaleString();

    document.getElementById('kpiClicksTotal').textContent = (kpi.cta_clicks_total || 0).toLocaleString();
    document.getElementById('kpiClicksToday').textContent = '+' + (kpi.cta_clicks_today || 0).toLocaleString();
    document.getElementById('kpiClicks7d').textContent = (kpi.cta_clicks_7d || 0).toLocaleString();

    document.getElementById('kpiConvTotal').textContent = (kpi.conversions_total || 0).toLocaleString();
    document.getElementById('kpiConvToday').textContent = '+' + (kpi.conversions_today || 0).toLocaleString();
    document.getElementById('kpiConv7d').textContent = (kpi.conversions_7d || 0).toLocaleString();

    document.getElementById('kpiConvRate').textContent = (kpi.conversion_rate || 0) + '%';

    // Top Pages
    const pagesBody = document.getElementById('topPagesBody');
    const topPages = data.top_pages || [];
    if (topPages.length === 0) {
      pagesBody.innerHTML = `<tr><td colspan="2" class="text-center py-3 text-muted">Belum ada rekaman kunjungan halaman.</td></tr>`;
    } else {
      pagesBody.innerHTML = topPages.map(p => `
        <tr>
          <td><code class="text-dark">${p.page_url}</code></td>
          <td class="text-end fw-bold text-primary">${p.view_count.toLocaleString()}</td>
        </tr>
      `).join('');
    }

    // Top CTAs
    const ctasBody = document.getElementById('topCtasBody');
    const topCtas = data.top_ctas || [];
    if (topCtas.length === 0) {
      ctasBody.innerHTML = `<tr><td colspan="2" class="text-center py-3 text-muted">Belum ada klik tombol CTA tercatat.</td></tr>`;
    } else {
      ctasBody.innerHTML = topCtas.map(c => `
        <tr>
          <td><span class="badge bg-light text-dark border">${c.element_id}</span></td>
          <td class="text-end fw-bold text-warning" style="color: #C5A059 !important;">${c.click_count.toLocaleString()}</td>
        </tr>
      `).join('');
    }

    // Daily Trend
    const trendBody = document.getElementById('dailyTrendBody');
    const dailyTrend = data.daily_trend || [];
    if (dailyTrend.length === 0) {
      trendBody.innerHTML = `<tr><td colspan="4" class="text-center py-3 text-muted">Belum ada data tren 7 hari terakhir.</td></tr>`;
    } else {
      trendBody.innerHTML = dailyTrend.map(d => `
        <tr>
          <td class="fw-semibold text-dark">${d.event_date}</td>
          <td class="text-center text-primary fw-bold">${d.views}</td>
          <td class="text-center text-warning fw-bold">${d.cta_clicks}</td>
          <td class="text-end text-success fw-bold">${d.conversions}</td>
        </tr>
      `).join('');
    }

    // Recent Events
    const eventsBody = document.getElementById('recentEventsBody');
    const recentEvents = data.recent_events || [];
    if (recentEvents.length === 0) {
      eventsBody.innerHTML = `<tr><td colspan="3" class="text-center py-3 text-muted">Belum ada event live tercatat.</td></tr>`;
    } else {
      eventsBody.innerHTML = recentEvents.map(e => {
        let badge = '<span class="badge bg-secondary">Event</span>';
        if (e.event_type === 'page_view') badge = '<span class="badge bg-primary">Page View</span>';
        else if (e.event_type === 'cta_click') badge = '<span class="badge bg-warning text-dark">CTA Click</span>';
        else if (e.event_type === 'conversion') badge = '<span class="badge bg-success">Conversion</span>';

        return `
          <tr>
            <td>${badge}</td>
            <td class="small text-truncate" style="max-width: 180px;" title="${e.element_id || e.page_url}">
              ${e.element_id || e.page_url}
            </td>
            <td class="small text-muted">${(e.created_at || '').substring(11, 19)}</td>
          </tr>
        `;
      }).join('');
    }

  } catch (err) {
    const alertContainer = document.getElementById('analyticsAlertContainer');
    alertContainer.innerHTML = `
      <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i> ${err.message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    `;
  }
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
