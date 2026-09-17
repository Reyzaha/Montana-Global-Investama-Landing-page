<?php
$pageTitle = 'CMS Konten Grup';
require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
  <div>
    <h5 class="fw-bold text-dark mb-1">Manajemen Konten Publik Grup</h5>
    <p class="text-muted small mb-0">Kelola tahapan roadmap milestone Transformasi, rentang tahun, dan persentase performa riil MGI, MIU, &amp; MSI.</p>
  </div>
</div>

<!-- Nav Tabs -->
<ul class="nav nav-pills mb-4 gap-2 bg-white p-2 rounded-3 border border-subtle shadow-sm" id="contentTabs" role="tablist">
  <li class="nav-item" role="presentation">
    <button class="nav-link active fw-bold px-4 py-2" id="tab-growth-btn" data-bs-toggle="pill" data-bs-target="#tab-growth" type="button" role="tab">
      <i class="bi bi-graph-up-arrow me-2 text-warning" style="color: #C5A059 !important;"></i>
      <span>Grafik Pertumbuhan &amp; Persentase (2020 – 2026)</span>
    </button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link fw-bold px-4 py-2" id="tab-transformasi-btn" data-bs-toggle="pill" data-bs-target="#tab-transformasi" type="button" role="tab">
      <i class="bi bi-clock-history me-2 text-primary" style="color: #142563 !important;"></i>
      <span>Roadmap Milestone Transformasi</span>
    </button>
  </li>
</ul>

<div class="tab-content" id="contentTabsContent">

  <!-- ================= TAB 1: GRAFIK PERTUMBUHAN & PERSENTASE ================= -->
  <div class="tab-pane fade show active" id="tab-growth" role="tabpanel">
    
    <!-- 1. KELOLA TAHUN -->
    <div class="admin-card mb-4">
      <div class="admin-card-header bg-white d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div class="d-flex align-items-center gap-2">
          <i class="bi bi-calendar3 text-warning" style="color: #C5A059 !important;"></i>
          <span class="fw-bold text-dark">1. Pengaturan Rentang Tahun Grafik</span>
        </div>
        <div class="d-flex gap-2">
          <button type="button" class="btn btn-sm btn-outline-danger d-flex align-items-center gap-1" onclick="removeLastYear()">
            <i class="bi bi-dash-circle"></i> Hapus Tahun Terakhir
          </button>
          <button type="button" class="btn btn-sm btn-mgi-gold d-flex align-items-center gap-1 shadow-sm" onclick="addNewYearPrompt()">
            <i class="bi bi-plus-circle"></i> Tambah Tahun Baru
          </button>
        </div>
      </div>
      <div class="p-4 bg-light">
        <div class="small text-muted mb-2">Tahun yang saat ini aktif di grafik (klik tombol di atas untuk menambah rentang tahun baru):</div>
        <div class="d-flex flex-wrap gap-2" id="growthYearsBadgeContainer">
          <!-- Rendered dynamically -->
        </div>
      </div>
    </div>

    <!-- 2. PILIH SEGMEN ENTITAS & EDITOR METRIK -->
    <div class="admin-card mb-4">
      <div class="admin-card-header bg-white d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div class="d-flex align-items-center gap-2">
          <i class="bi bi-sliders text-primary" style="color: #142563 !important;"></i>
          <span class="fw-bold text-dark">2. Editor Angka Pertumbuhan &amp; Kartu KPI Persentase</span>
        </div>
        <div class="btn-group" role="group">
          <button type="button" class="btn btn-sm btn-outline-primary active" id="segBtn-mgi" onclick="switchGrowthSegment('mgi')">
            Konsolidasi MGI
          </button>
          <button type="button" class="btn btn-sm btn-outline-primary" id="segBtn-miu" onclick="switchGrowthSegment('miu')">
            PT MIU (Alat Berat)
          </button>
          <button type="button" class="btn btn-sm btn-outline-primary" id="segBtn-msi" onclick="switchGrowthSegment('msi')">
            MSI (Manufaktur)
          </button>
        </div>
      </div>

      <div class="p-4">
        <!-- Info Segmen Terpilih -->
        <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded-3 border border-subtle mb-4">
          <div>
            <div class="small text-muted text-uppercase fw-bold" id="adminSegBadge">Holding &amp; Investment Manager</div>
            <h5 class="fw-bold text-dark mb-0" id="adminSegTitle">Konsolidasi Grup PT Montana Global Investama</h5>
          </div>
          <div class="badge bg-primary px-3 py-2" id="adminSegUnit">Satuan: Miliar IDR</div>
        </div>

        <!-- FORM EDITOR 4 KARTU KPI & PERSENTASE -->
        <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
          <i class="bi bi-percent text-success"></i>
          <span>Pengaturan 4 Kartu Ringkasan KPI &amp; Persentase di Halaman Publik</span>
        </h6>
        <div class="row g-3 mb-4" id="kpiEditorCardsContainer">
          <!-- Rendered dynamically for 4 KPIs -->
        </div>

        <hr class="my-4">

        <!-- FORM TABEL ANGKA PER TAHUN -->
        <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
          <i class="bi bi-bar-chart-line-fill text-warning" style="color: #C5A059 !important;"></i>
          <span>Angka Pertumbuhan Tahunan (Sumbu Grafik)</span>
        </h6>
        <div class="table-responsive mb-4">
          <table class="table table-bordered table-hover align-middle bg-white mb-0">
            <thead class="table-light">
              <tr>
                <th style="width: 140px;" class="text-center">Tahun</th>
                <th id="thPrimaryValue">Nilai Utama</th>
                <th id="thSecondaryValue">Nilai Sekunder (Rp Miliar)</th>
              </tr>
            </thead>
            <tbody id="growthYearlyTableBody">
              <!-- Rendered dynamically -->
            </tbody>
          </table>
        </div>

        <div class="p-3 bg-light rounded-3 d-flex flex-wrap align-items-center justify-content-between gap-3">
          <div class="small text-muted">
            <i class="bi bi-info-circle me-1 text-primary"></i>
            Perubahan data akan langsung tersimpan dan otomatis memperbarui grafik interaktif di halaman <strong>About Us</strong>.
          </div>
          <button type="button" class="btn btn-mgi-primary px-4 py-2 fw-bold shadow-sm" id="btnSaveGrowth" onclick="saveAllGrowthData()">
            <i class="bi bi-cloud-arrow-up-fill me-1"></i> Simpan Seluruh Perubahan Kinerja
          </button>
        </div>

      </div>
    </div>
  </div>

  <!-- ================= TAB 2: ROADMAP MILESTONE TRANSFORMASI ================= -->
  <div class="tab-pane fade" id="tab-transformasi" role="tabpanel">
    
    <div class="d-flex justify-content-end mb-3">
      <button type="button" class="btn btn-mgi-gold d-flex align-items-center gap-2 shadow-sm" onclick="openStepModal()">
        <i class="bi bi-plus-circle-fill"></i>
        <span>Tambah Milestone Baru</span>
      </button>
    </div>

    <!-- Transformasi Steps Manager -->
    <div class="admin-card mb-4">
      <div class="admin-card-header bg-white">
        <div class="d-flex align-items-center gap-2">
          <i class="bi bi-clock-history text-warning" style="color: #C5A059 !important;"></i>
          <span class="fw-bold text-dark">Roadmap Milestone Transformasi Bisnis MGI</span>
        </div>
      </div>

      <div class="table-responsive">
        <table class="admin-table">
          <thead>
            <tr>
              <th style="width: 70px;" class="text-center">Urutan</th>
              <th>Fase / Tahun</th>
              <th>Judul Milestone &amp; Tagline</th>
              <th>Deskripsi Singkat</th>
              <th>Pencapaian Kunci</th>
              <th class="text-end">Aksi</th>
            </tr>
          </thead>
          <tbody id="stepsTableBody">
            <tr>
              <td colspan="6" class="text-center py-4 text-muted">
                <span class="spinner-border spinner-border-sm me-2"></span> Memuat data milestone...
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

</div>

<!-- MODAL TAMBAH / EDIT STEP (TRANSFORMASI) -->
<div class="modal fade" id="stepModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      <div class="modal-header text-white" style="background: #142563;">
        <h5 class="modal-title fw-bold" id="stepModalTitle">Tambah Milestone Transformasi</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form id="stepForm" onsubmit="saveStep(event)">
        <input type="hidden" id="stepId">
        <div class="modal-body p-4 bg-light">
          <div class="card p-4 border-0 rounded-3 shadow-sm bg-white mb-3">
            <div class="row g-3">
              <div class="col-md-3">
                <label class="form-label">Urutan Langkah <span class="text-danger">*</span></label>
                <input type="number" id="stepOrder" class="form-control" min="1" required>
              </div>
              <div class="col-md-5">
                <label class="form-label">Label Fase / Tahun <span class="text-danger">*</span></label>
                <input type="text" id="stepYear" class="form-control" placeholder="2024 atau Fase Ekspansi" required>
              </div>
              <div class="col-md-4">
                <label class="form-label">Status</label>
                <select id="stepStatus" class="form-select">
                  <option value="completed">Completed (Terlaksana)</option>
                  <option value="in_progress">In Progress (Berjalan)</option>
                  <option value="planned">Planned (Rencana)</option>
                </select>
              </div>

              <div class="col-md-6">
                <label class="form-label">Judul Milestone <span class="text-danger">*</span></label>
                <input type="text" id="stepTitle" class="form-control" placeholder="Judul Transformasi" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Tagline / Subjudul</label>
                <input type="text" id="stepSubtitle" class="form-control" placeholder="Penjelasan singkat fokus milestone">
              </div>

              <div class="col-12">
                <label class="form-label">Deskripsi Lengkap</label>
                <textarea id="stepDesc" class="form-control" rows="3" placeholder="Uraikan detail perkembangan dan signifikansi milestone ini..."></textarea>
              </div>

              <div class="col-12">
                <label class="form-label">Poin-Poin Pencapaian Kunci (1 baris = 1 poin)</label>
                <textarea id="stepHighlights" class="form-control" rows="3" placeholder="Poin 1&#10;Poin 2&#10;Poin 3"></textarea>
                <small class="text-muted" style="font-size: 0.72rem;">Pisahkan setiap pencapaian dengan baris baru (Enter).</small>
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer bg-white border-top p-3 d-flex justify-content-between">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" id="btnSaveStep" class="btn btn-mgi-primary px-4">
            <i class="bi bi-cloud-arrow-up-fill me-1"></i> Simpan Milestone
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  let stepsData = [];
  let stepModalInstance = null;
  let adminGrowthData = null;
  let currentActiveSegment = 'mgi';

  document.addEventListener('DOMContentLoaded', () => {
    stepModalInstance = new bootstrap.Modal(document.getElementById('stepModal'));
    loadSteps();
    loadAdminGrowthData();
  });

  // ================= 1. GROWTH DATA CONTROLLER =================
  async function loadAdminGrowthData() {
    try {
      const res = await fetch('../api/admin/growth.php');
      const json = await res.json();
      if (json.success && json.data) {
        adminGrowthData = json.data;
        renderYearsBadges();
        renderActiveSegmentForm();
      } else {
        if (json.status === 401) window.location.href = 'login.php';
        AdminApp.showToast('Gagal memuat data pertumbuhan.', 'danger');
      }
    } catch (e) {
      AdminApp.showToast('Gagal terhubung ke API data pertumbuhan.', 'danger');
    }
  }

  function renderYearsBadges() {
    const container = document.getElementById('growthYearsBadgeContainer');
    if (!container || !adminGrowthData) return;

    container.innerHTML = adminGrowthData.years.map((y, idx) => `
      <span class="badge bg-white text-dark border border-subtle px-3 py-2 fs-6 shadow-sm d-inline-flex align-items-center gap-2">
        <i class="bi bi-calendar-event text-warning"></i>
        <span>${y}</span>
      </span>
    `).join('');
  }

  function addNewYearPrompt() {
    const newYear = prompt("Masukkan label tahun baru yang ingin ditambahkan ke grafik (misal: 2027 atau 2027 (P)):");
    if (!newYear || !newYear.trim()) return;

    const trimmed = newYear.trim();
    if (adminGrowthData.years.includes(trimmed)) {
      alert("Tahun tersebut sudah ada di grafik.");
      return;
    }

    adminGrowthData.years.push(trimmed);

    // Tambah slot nilai default di tiap segmen
    ['mgi', 'miu', 'msi'].forEach(seg => {
      if (adminGrowthData[seg]) {
        const lastVal = adminGrowthData[seg].data[adminGrowthData[seg].data.length - 1] || 0;
        adminGrowthData[seg].data.push(lastVal);
        if (adminGrowthData[seg].assetValue) {
          const lastAsset = adminGrowthData[seg].assetValue[adminGrowthData[seg].assetValue.length - 1] || 0;
          adminGrowthData[seg].assetValue.push(lastAsset);
        }
      }
    });

    renderYearsBadges();
    renderActiveSegmentForm();
    AdminApp.showToast(`Tahun ${trimmed} berhasil ditambahkan! Jangan lupa klik Simpan.`, 'info');
  }

  function removeLastYear() {
    if (adminGrowthData.years.length <= 3) {
      alert("Minimal harus ada 3 tahun di grafik.");
      return;
    }
    const lastYear = adminGrowthData.years[adminGrowthData.years.length - 1];
    if (!confirm(`Hapus tahun terakhir "${lastYear}" dari grafik dan seluruh segmen?`)) return;

    adminGrowthData.years.pop();
    ['mgi', 'miu', 'msi'].forEach(seg => {
      if (adminGrowthData[seg]) {
        adminGrowthData[seg].data.pop();
        if (adminGrowthData[seg].assetValue) {
          adminGrowthData[seg].assetValue.pop();
        }
      }
    });

    renderYearsBadges();
    renderActiveSegmentForm();
    AdminApp.showToast(`Tahun ${lastYear} berhasil dihapus! Klik Simpan untuk memperbarui.`, 'warning');
  }

  function switchGrowthSegment(seg) {
    // Simpan dulu perubahan input saat ini sebelum berpindah
    captureCurrentSegmentInputs();

    currentActiveSegment = seg;
    ['mgi', 'miu', 'msi'].forEach(s => {
      const btn = document.getElementById(`segBtn-${s}`);
      if (btn) {
        if (s === seg) {
          btn.classList.add('active');
        } else {
          btn.classList.remove('active');
        }
      }
    });
    renderActiveSegmentForm();
  }

  function renderActiveSegmentForm() {
    if (!adminGrowthData) return;
    const seg = adminGrowthData[currentActiveSegment];
    if (!seg) return;

    document.getElementById('adminSegTitle').innerText = seg.title;
    document.getElementById('adminSegBadge').innerText = seg.badge;
    document.getElementById('adminSegUnit').innerText = `Satuan: ${seg.unit}`;

    // 1. Render 4 Kartu KPI & Persentase
    const kpiContainer = document.getElementById('kpiEditorCardsContainer');
    kpiContainer.innerHTML = seg.kpis.map((kpi, idx) => `
      <div class="col-12 col-md-6 col-lg-3">
        <div class="card p-3 border border-subtle rounded-3 bg-light h-100 shadow-sm">
          <div class="small fw-bold text-dark mb-2">Kartu KPI #${idx + 1}</div>
          <div class="mb-2">
            <label class="form-label small text-muted mb-1">Judul / Label:</label>
            <input type="text" class="form-control form-control-sm kpi-input-label" data-index="${idx}" value="${kpi.label}">
          </div>
          <div class="mb-2">
            <label class="form-label small text-muted mb-1">Nilai / Persentase (%):</label>
            <input type="text" class="form-control form-control-sm fw-bold kpi-input-value" data-index="${idx}" value="${kpi.value}">
          </div>
          <div>
            <label class="form-label small text-muted mb-1">Catatan Tambahan:</label>
            <input type="text" class="form-control form-control-sm text-secondary kpi-input-note" data-index="${idx}" value="${kpi.note}">
          </div>
        </div>
      </div>
    `).join('');

    // 2. Render Tabel Angka Tahunan
    const thPrimary = document.getElementById('thPrimaryValue');
    const thSecondary = document.getElementById('thSecondaryValue');
    const tb = document.getElementById('growthYearlyTableBody');

    thPrimary.innerText = `${seg.chartLabel} (${seg.unit})`;

    const hasSecondary = !!seg.assetValue;
    if (hasSecondary) {
      thSecondary.style.display = '';
      thSecondary.innerText = seg.secondaryChartLabel || 'Nilai Aset (Rp Miliar)';
    } else {
      thSecondary.style.display = 'none';
    }

    tb.innerHTML = adminGrowthData.years.map((y, idx) => {
      const valPrimary = seg.data[idx] !== undefined ? seg.data[idx] : 0;
      const valSecondary = hasSecondary && seg.assetValue[idx] !== undefined ? seg.assetValue[idx] : 0;

      return `
        <tr>
          <td class="text-center fw-bold bg-light">${y}</td>
          <td>
            <div class="input-group input-group-sm">
              <input type="number" step="any" class="form-control growth-input-primary" data-index="${idx}" value="${valPrimary}">
              <span class="input-group-text">${seg.unit}</span>
            </div>
          </td>
          ${hasSecondary ? `
            <td>
              <div class="input-group input-group-sm">
                <input type="number" step="any" class="form-control growth-input-secondary" data-index="${idx}" value="${valSecondary}">
                <span class="input-group-text">Rp Miliar</span>
              </div>
            </td>
          ` : ''}
        </tr>
      `;
    }).join('');
  }

  function captureCurrentSegmentInputs() {
    if (!adminGrowthData || !adminGrowthData[currentActiveSegment]) return;
    const seg = adminGrowthData[currentActiveSegment];

    // Capture KPIs
    document.querySelectorAll('.kpi-input-label').forEach(inp => {
      const idx = parseInt(inp.getAttribute('data-index'));
      if (seg.kpis[idx]) seg.kpis[idx].label = inp.value.trim();
    });
    document.querySelectorAll('.kpi-input-value').forEach(inp => {
      const idx = parseInt(inp.getAttribute('data-index'));
      if (seg.kpis[idx]) seg.kpis[idx].value = inp.value.trim();
    });
    document.querySelectorAll('.kpi-input-note').forEach(inp => {
      const idx = parseInt(inp.getAttribute('data-index'));
      if (seg.kpis[idx]) seg.kpis[idx].note = inp.value.trim();
    });

    // Capture Yearly Primary
    document.querySelectorAll('.growth-input-primary').forEach(inp => {
      const idx = parseInt(inp.getAttribute('data-index'));
      seg.data[idx] = parseFloat(inp.value) || 0;
    });

    // Capture Yearly Secondary (if exists)
    if (seg.assetValue) {
      document.querySelectorAll('.growth-input-secondary').forEach(inp => {
        const idx = parseInt(inp.getAttribute('data-index'));
        seg.assetValue[idx] = parseFloat(inp.value) || 0;
      });
    }
  }

  async function saveAllGrowthData() {
    captureCurrentSegmentInputs();

    const btn = document.getElementById('btnSaveGrowth');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';

    try {
      const res = await fetch('../api/admin/growth.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(adminGrowthData)
      });
      const json = await res.json();

      if (json.success) {
        AdminApp.showToast(json.message || 'Data pertumbuhan & persentase berhasil disimpan!', 'success');
      } else {
        AdminApp.showToast(json.message || 'Gagal menyimpan data pertumbuhan.', 'danger');
      }
    } catch (e) {
      AdminApp.showToast('Terjadi kesalahan koneksi server.', 'danger');
    } finally {
      btn.disabled = false;
      btn.innerHTML = '<i class="bi bi-cloud-arrow-up-fill me-1"></i> Simpan Seluruh Perubahan Kinerja';
    }
  }

  // ================= 2. TRANSFORMASI STEPS CONTROLLER =================
  async function loadSteps() {
    const tb = document.getElementById('stepsTableBody');
    try {
      const res = await fetch('../api/admin/content.php?section=transformasi');
      const json = await res.json();
      if (!json.success) {
        if (json.status === 401) window.location.href = 'login.php';
        return;
      }

      stepsData = json.data || [];
      if (stepsData.length === 0) {
        tb.innerHTML = `<tr><td colspan="6" class="text-center py-4 text-muted">Belum ada milestone transformasi.</td></tr>`;
        return;
      }

      tb.innerHTML = stepsData.map(st => {
        const highlights = typeof st.highlights === 'string' ? JSON.parse(st.highlights || '[]') : (st.highlights || []);
        return `
          <tr>
            <td class="text-center fw-bold">${st.step_order}</td>
            <td><span class="badge bg-light text-dark border px-2 py-1">${st.year_or_phase}</span></td>
            <td>
              <div class="fw-bold text-dark">${st.title}</div>
              <small class="text-muted">${st.subtitle || '-'}</small>
            </td>
            <td><small class="text-secondary text-truncate d-inline-block" style="max-width: 250px;">${st.description || '-'}</small></td>
            <td>
              <span class="badge bg-info-subtle text-info border border-info-subtle">${highlights.length} Poin</span>
            </td>
            <td class="text-end">
              <button class="btn btn-sm btn-outline-primary me-1" onclick="editStep(${st.id})">
                <i class="bi bi-pencil-square"></i>
              </button>
              <button class="btn btn-sm btn-outline-danger" onclick="deleteStep(${st.id}, '${st.title}')">
                <i class="bi bi-trash"></i>
              </button>
            </td>
          </tr>
        `;
      }).join('');

    } catch (err) {
      tb.innerHTML = `<tr><td colspan="6" class="text-center py-4 text-danger">Gagal memuat milestone.</td></tr>`;
    }
  }

  function openStepModal() {
    document.getElementById('stepForm').reset();
    document.getElementById('stepId').value = '';
    document.getElementById('stepModalTitle').innerText = 'Tambah Milestone Transformasi';
    document.getElementById('stepOrder').value = stepsData.length + 1;
    stepModalInstance.show();
  }

  function editStep(id) {
    const st = stepsData.find(s => s.id == id);
    if (!st) return;

    document.getElementById('stepId').value = st.id;
    document.getElementById('stepOrder').value = st.step_order;
    document.getElementById('stepYear').value = st.year_or_phase;
    document.getElementById('stepTitle').value = st.title;
    document.getElementById('stepSubtitle').value = st.subtitle || '';
    document.getElementById('stepDesc').value = st.description || '';
    document.getElementById('stepStatus').value = st.status || 'completed';

    const highlights = typeof st.highlights === 'string' ? JSON.parse(st.highlights || '[]') : (st.highlights || []);
    document.getElementById('stepHighlights').value = highlights.join('\n');

    document.getElementById('stepModalTitle').innerText = 'Edit Milestone Transformasi';
    stepModalInstance.show();
  }

  async function saveStep(e) {
    e.preventDefault();
    const id = document.getElementById('stepId').value;
    const rawHighlights = document.getElementById('stepHighlights').value
      .split('\n')
      .map(s => s.trim())
      .filter(s => s.length > 0);

    const payload = {
      id: id || null,
      step_order: parseInt(document.getElementById('stepOrder').value),
      year_or_phase: document.getElementById('stepYear').value.trim(),
      title: document.getElementById('stepTitle').value.trim(),
      subtitle: document.getElementById('stepSubtitle').value.trim(),
      description: document.getElementById('stepDesc').value.trim(),
      highlights: rawHighlights,
      status: document.getElementById('stepStatus').value
    };

    const btn = document.getElementById('btnSaveStep');
    btn.disabled = true;

    try {
      const res = await fetch('../api/admin/content.php?section=transformasi', {
        method: id ? 'PUT' : 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      const json = await res.json();

      if (json.success) {
        AdminApp.showToast(json.message, 'success');
        stepModalInstance.hide();
        loadSteps();
      } else {
        AdminApp.showToast(json.message, 'danger');
      }
    } catch (err) {
      AdminApp.showToast('Gagal menyimpan milestone.', 'danger');
    } finally {
      btn.disabled = false;
    }
  }

  async function deleteStep(id, title) {
    if (!confirm(`Hapus milestone "${title}"?`)) return;

    try {
      const res = await fetch(`../api/admin/content.php?section=transformasi&id=${id}`, {
        method: 'DELETE'
      });
      const json = await res.json();
      if (json.success) {
        AdminApp.showToast(json.message, 'success');
        loadSteps();
      } else {
        AdminApp.showToast(json.message, 'danger');
      }
    } catch (err) {
      AdminApp.showToast('Gagal menghapus milestone.', 'danger');
    }
  }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
