<?php
$pageTitle = 'CMS Konten Grup';
require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
  <div>
    <h5 class="fw-bold text-dark mb-1">Manajemen Konten Publik Grup</h5>
    <p class="text-muted small mb-0">Kelola secara terpusat data grafik pertumbuhan, roadmap transformasi, struktur ekosistem, dan alur kerja preparation.</p>
  </div>
</div>

<!-- Nav Tabs -->
<ul class="nav nav-pills mb-4 gap-2 bg-white p-2 rounded-3 border border-subtle shadow-sm flex-wrap" id="contentTabs" role="tablist">
  <li class="nav-item" role="presentation">
    <button class="nav-link active fw-bold px-3 py-2" id="tab-growth-btn" data-bs-toggle="pill" data-bs-target="#tab-growth" type="button" role="tab">
      <i class="bi bi-graph-up-arrow me-2 text-warning" style="color: #C5A059 !important;"></i>
      <span>Grafik Pertumbuhan &amp; KPI</span>
    </button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link fw-bold px-3 py-2" id="tab-transformasi-btn" data-bs-toggle="pill" data-bs-target="#tab-transformasi" type="button" role="tab">
      <i class="bi bi-clock-history me-2 text-primary" style="color: #142563 !important;"></i>
      <span>Roadmap Transformasi</span>
    </button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link fw-bold px-3 py-2" id="tab-ekosistem-btn" data-bs-toggle="pill" data-bs-target="#tab-ekosistem" type="button" role="tab" onclick="loadEkosistemNodes()">
      <i class="bi bi-diagram-3-fill me-2 text-success"></i>
      <span>Ekosistem Korporat</span>
    </button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link fw-bold px-3 py-2" id="tab-preparation-btn" data-bs-toggle="pill" data-bs-target="#tab-preparation" type="button" role="tab" onclick="loadPreparationData()">
      <i class="bi bi-boxes me-2 text-info"></i>
      <span>Sinergi Preparation</span>
    </button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link fw-bold px-3 py-2" id="tab-profile-btn" data-bs-toggle="pill" data-bs-target="#tab-profile" type="button" role="tab" onclick="loadCompanyProfile()">
      <i class="bi bi-shield-check me-2 text-warning" style="color: #C5A059 !important;"></i>
      <span>Profil &amp; Tata Kelola (TARIF)</span>
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
            Ekosistem MGI
          </button>
          <button type="button" class="btn btn-sm btn-outline-primary" id="segBtn-miu" onclick="switchGrowthSegment('miu')">
            PT MIU (Alat Berat)
          </button>
          <button type="button" class="btn btn-sm btn-outline-primary" id="segBtn-msi" onclick="switchGrowthSegment('msi')">
            MSI (Import)
          </button>
        </div>
      </div>

      <div class="p-4">
        <!-- Info Segmen Terpilih -->
        <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded-3 border border-subtle mb-4">
          <div>
            <div class="small text-muted text-uppercase fw-bold" id="adminSegBadge">Grup &amp; Investment Manager</div>
            <h5 class="fw-bold text-dark mb-0" id="adminSegTitle">Ekosistem Grup PT Montana Global Investama</h5>
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
          <i class="bi bi-table text-primary"></i>
          <span>Pengaturan Angka Titik Grafik Performa per Tahun</span>
        </h6>
        <div class="table-responsive mb-4">
          <table class="table table-bordered table-hover align-middle bg-white mb-0" id="growthDataTable">
            <thead class="table-light">
              <tr id="growthDataTableHead">
                <!-- Dynamic Header -->
              </tr>
            </thead>
            <tbody id="growthDataTableBody">
              <!-- Dynamic Rows -->
            </tbody>
          </table>
        </div>

        <!-- Tombol Simpan -->
        <div class="d-flex justify-content-end gap-2 border-top pt-3">
          <button type="button" class="btn btn-mgi-primary px-4 py-2 d-flex align-items-center gap-2 shadow-sm" id="btnSaveGrowth" onclick="saveAdminGrowthData()">
            <i class="bi bi-cloud-arrow-up-fill"></i>
            <span>Simpan Semua Perubahan Grafik &amp; KPI</span>
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

  <!-- ================= TAB 3: BAGAN EKOSISTEM KORPORAT ================= -->
  <div class="tab-pane fade" id="tab-ekosistem" role="tabpanel">
    
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
      <div>
        <h6 class="fw-bold text-dark mb-0">Daftar Node Bagan Hierarki Ekosistem</h6>
        <small class="text-muted">Kelola struktur grup, anak usaha operasional, fasilitas teknis, dan mitra strategis.</small>
      </div>
      <button type="button" class="btn btn-mgi-gold d-flex align-items-center gap-2 shadow-sm" onclick="openEkosistemModal()">
        <i class="bi bi-plus-circle-fill"></i>
        <span>Tambah Node Ekosistem</span>
      </button>
    </div>

    <div class="admin-card mb-4">
      <div class="table-responsive">
        <table class="admin-table">
          <thead>
            <tr>
              <th>ID Node</th>
              <th>Nama Entitas / Label</th>
              <th>Tipe &amp; Kategori</th>
              <th>Level &amp; Induk (Parent)</th>
              <th>Badge Label</th>
              <th>Deskripsi Peran</th>
              <th class="text-end">Aksi</th>
            </tr>
          </thead>
          <tbody id="ekosistemTableBody">
            <tr>
              <td colspan="7" class="text-center py-4 text-muted">
                <span class="spinner-border spinner-border-sm me-2"></span> Memuat data ekosistem...
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- ================= TAB 4: ALUR KERJA & SINERGI PREPARATION ================= -->
  <div class="tab-pane fade" id="tab-preparation" role="tabpanel">
    
    <!-- Bagian 1: 4 Entitas Utama -->
    <div class="admin-card mb-4">
      <div class="admin-card-header bg-white">
        <div class="d-flex align-items-center gap-2">
          <i class="bi bi-building-check text-primary" style="color: #142563 !important;"></i>
          <span class="fw-bold text-dark">1. Profil 4 Entitas Utama Sinergi Montana Group</span>
        </div>
      </div>
      <div class="p-4">
        <div class="row g-3" id="prepEntitiesCardsContainer">
          <div class="col-12 text-center py-4 text-muted">
            <span class="spinner-border spinner-border-sm me-2"></span> Memuat profil entitas...
          </div>
        </div>
      </div>
    </div>

    <!-- Bagian 2: Tahapan Alur Kerja Sinergi -->
    <div class="admin-card mb-4">
      <div class="admin-card-header bg-white d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div class="d-flex align-items-center gap-2">
          <i class="bi bi-arrow-right-circle text-warning" style="color: #C5A059 !important;"></i>
          <span class="fw-bold text-dark">2. Tahapan Alur Kerja &amp; Sinergi Operasional (Workflow)</span>
        </div>
        <button type="button" class="btn btn-sm btn-mgi-gold d-flex align-items-center gap-1 shadow-sm" onclick="openPrepWorkflowModal()">
          <i class="bi bi-plus-circle"></i> Tambah Tahapan Alur
        </button>
      </div>

      <div class="table-responsive">
        <table class="admin-table">
          <thead>
            <tr>
              <th style="width: 80px;" class="text-center">Tahap</th>
              <th>Judul Tahapan</th>
              <th>Aktor / Pihak Terlibat</th>
              <th>Uraian Alur Kerja &amp; Akuntabilitas</th>
              <th class="text-end">Aksi</th>
            </tr>
          </thead>
          <tbody id="prepWorkflowTableBody">
            <tr>
              <td colspan="5" class="text-center py-4 text-muted">
                <span class="spinner-border spinner-border-sm me-2"></span> Memuat tahapan workflow...
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- ================= TAB 5: PROFIL PERUSAHAAN & TATA KELOLA (TARIF) ================= -->
  <div class="tab-pane fade" id="tab-profile" role="tabpanel">
    <form id="profileForm" onsubmit="saveCompanyProfile(event)">
      <!-- Bagian 1: Identitas & Tagline -->
      <div class="admin-card mb-4">
        <div class="admin-card-header bg-white d-flex align-items-center gap-2">
          <i class="bi bi-building text-primary" style="color: #142563 !important;"></i>
          <span class="fw-bold text-dark">1. Identitas &amp; Tagline Korporat</span>
        </div>
        <div class="p-4">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-bold small">Nama Resmi Perusahaan</label>
              <input type="text" id="profCompanyName" class="form-control" required>
            </div>
            <div class="col-md-3">
              <label class="form-label fw-bold small">Nama Singkat / Inisial</label>
              <input type="text" id="profShortName" class="form-control" required>
            </div>
            <div class="col-md-3">
              <label class="form-label fw-bold small">Nama Grup</label>
              <input type="text" id="profGroupName" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-bold small">Tagline Utama (Bahasa Indonesia)</label>
              <input type="text" id="profTaglineId" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-bold small">Tagline (Bahasa Inggris)</label>
              <input type="text" id="profTaglineEn" class="form-control">
            </div>
          </div>
        </div>
      </div>

      <!-- Bagian 2: Visi & Misi Perusahaan -->
      <div class="admin-card mb-4">
        <div class="admin-card-header bg-white d-flex align-items-center gap-2">
          <i class="bi bi-compass text-warning" style="color: #C5A059 !important;"></i>
          <span class="fw-bold text-dark">2. Visi &amp; Misi Korporat</span>
        </div>
        <div class="p-4">
          <div class="mb-3">
            <label class="form-label fw-bold small">Visi Perusahaan</label>
            <textarea id="profVision" class="form-control" rows="2" required></textarea>
          </div>
          <div>
            <label class="form-label fw-bold small">Misi Perusahaan (1 baris = 1 poin misi)</label>
            <textarea id="profMissions" class="form-control" rows="5" required></textarea>
            <small class="text-muted" style="font-size: 0.75rem;">Pisahkan setiap poin misi dengan menekan tombol Enter.</small>
          </div>
        </div>
      </div>

      <!-- Bagian 3: Nilai-Nilai Perusahaan -->
      <div class="admin-card mb-4">
        <div class="admin-card-header bg-white d-flex align-items-center gap-2">
          <i class="bi bi-gem text-success"></i>
          <span class="fw-bold text-dark">3. Nilai-Nilai Utama Perusahaan (Core Values)</span>
        </div>
        <div class="p-4">
          <div class="row g-3" id="profValuesContainer">
            <!-- Rendered dynamically -->
          </div>
        </div>
      </div>

      <!-- Bagian 4: Prinsip Tata Kelola Perusahaan (TARIF) -->
      <div class="admin-card mb-4">
        <div class="admin-card-header bg-white d-flex align-items-center gap-2">
          <i class="bi bi-shield-check text-primary" style="color: #142563 !important;"></i>
          <span class="fw-bold text-dark">4. Tata Kelola Perusahaan yang Baik (Kerangka TARIF)</span>
        </div>
        <div class="p-4">
          <p class="text-muted small mb-3">Prinsip tata kelola perusahaan yang baik, transparan, dan akuntabel diimplementasikan melalui 5 pilar TARIF:</p>
          <div class="row g-3" id="profTarifContainer">
            <!-- Rendered dynamically (T, A, R, I, F) -->
          </div>
        </div>
      </div>

      <!-- Bagian 5: Alamat & Kontak Resmi -->
      <div class="admin-card mb-4">
        <div class="admin-card-header bg-white d-flex align-items-center gap-2">
          <i class="bi bi-geo-alt text-danger"></i>
          <span class="fw-bold text-dark">5. Alamat Kantor &amp; Kontak Resmi</span>
        </div>
        <div class="p-4">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-bold small">Kantor Pusat (Corporate Office)</label>
              <textarea id="profCorpOffice" class="form-control" rows="3" required></textarea>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-bold small">Kantor Operasional &amp; Workshop (Operational Office)</label>
              <textarea id="profOpOffice" class="form-control" rows="3" required></textarea>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-bold small">Email Resmi Perusahaan</label>
              <input type="email" id="profEmail" class="form-control" required>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-bold small">Jam Kerja Operasional</label>
              <input type="text" id="profWorkHours" class="form-control" required>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-bold small">Link Google Maps</label>
              <input type="text" id="profMapsUrl" class="form-control">
            </div>
          </div>
        </div>
      </div>

      <!-- Tombol Simpan Profil -->
      <div class="d-flex justify-content-end gap-2 mb-4">
        <button type="submit" id="btnSaveProfile" class="btn btn-mgi-primary px-4 py-2 d-flex align-items-center gap-2 shadow-sm">
          <i class="bi bi-cloud-arrow-up-fill"></i>
          <span>Simpan Perubahan Profil &amp; Tata Kelola</span>
        </button>
      </div>
    </form>
  </div>

</div>

<!-- ========================================================================= -->
<!-- MODALS -->
<!-- ========================================================================= -->

<!-- MODAL 1: TAMBAH / EDIT STEP (TRANSFORMASI) -->
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

<!-- MODAL 2: TAMBAH / EDIT NODE (EKOSISTEM) -->
<div class="modal fade" id="ekosistemModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      <div class="modal-header text-white" style="background: #142563;">
        <h5 class="modal-title fw-bold" id="ekoModalTitle">Tambah Node Ekosistem</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form id="ekosistemForm" onsubmit="saveEkosistemNode(event)">
        <input type="hidden" id="ekoIsEdit" value="0">
        <div class="modal-body p-4 bg-light">
          <div class="card p-4 border-0 rounded-3 shadow-sm bg-white mb-3">
            <div class="row g-3">
              <div class="col-md-4">
                <label class="form-label fw-bold small">ID Node (Unik) <span class="text-danger">*</span></label>
                <input type="text" id="ekoId" class="form-control" placeholder="mgi-holding / miu / custom-unit" required>
                <small class="text-muted" style="font-size: 0.7rem;">Contoh: miu, mypurcase, mitra-abc</small>
              </div>
              <div class="col-md-8">
                <label class="form-label fw-bold small">Nama Resmi Entitas / Label <span class="text-danger">*</span></label>
                <input type="text" id="ekoLabel" class="form-control" placeholder="PT Montana Indo Utama (MIU)" required>
              </div>

              <div class="col-md-6">
                <label class="form-label fw-bold small">Label Singkat</label>
                <input type="text" id="ekoShortLabel" class="form-control" placeholder="Montana Indo Utama">
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold small">Tipe Entitas</label>
                <select id="ekoType" class="form-select">
                  <option value="holding">Grup (Induk Usaha)</option>
                  <option value="subsidiary" selected>Subsidiary (Anak Usaha Operasional)</option>
                  <option value="facility">Facility (Fasilitas Workshop / Pool)</option>
                  <option value="partner">Partner (Mitra Strategis / Offtake)</option>
                </select>
              </div>

              <div class="col-md-6">
                <label class="form-label fw-bold small">Kategori Usaha</label>
                <input type="text" id="ekoCategory" class="form-control" placeholder="Distribusi CBU, Workshop & Operasional">
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold small">Badge Teks</label>
                <input type="text" id="ekoBadge" class="form-control" placeholder="Distribusi & Operasional">
              </div>

              <div class="col-md-6">
                <label class="form-label fw-bold small">Node Induk (Parent)</label>
                <select id="ekoParentId" class="form-select">
                  <option value="">— Tidak Ada (Root / Grup Utama) —</option>
                  <!-- Populated dynamically -->
                </select>
              </div>
              <div class="col-md-3">
                <label class="form-label fw-bold small">Level Hierarki</label>
                <select id="ekoLevel" class="form-select">
                  <option value="0">0 (Grup Utama)</option>
                  <option value="1" selected>1 (Unit Usaha / Mitra)</option>
                  <option value="2">2 (Fasilitas / Sub-Unit)</option>
                </select>
              </div>
              <div class="col-md-3">
                <label class="form-label fw-bold small">Ikon</label>
                <select id="ekoIcon" class="form-select">
                  <option value="building">Gedung (building)</option>
                  <option value="truck">Truk / Alat (truck)</option>
                  <option value="gear-wide-connected">Mesin (gear)</option>
                  <option value="cart-check">Pengadaan (cart)</option>
                  <option value="wrench-adjustable">Workshop (wrench)</option>
                  <option value="shield-check">Mitra (shield)</option>
                  <option value="briefcase">Bisnis (briefcase)</option>
                </select>
              </div>

              <div class="col-12">
                <label class="form-label fw-bold small">Deskripsi Peran Entitas</label>
                <textarea id="ekoRoleDesc" class="form-control" rows="3" placeholder="Jelaskan peran operasional, keunggulan, fasilitas, dan kontribusi entitas ini dalam ekosistem Montana Group..."></textarea>
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer bg-white border-top p-3 d-flex justify-content-between">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" id="btnSaveEko" class="btn btn-mgi-primary px-4">
            <i class="bi bi-cloud-arrow-up-fill me-1"></i> Simpan Node Ekosistem
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- MODAL 3: EDIT PROFIL ENTITAS PREPARATION -->
<div class="modal fade" id="prepEntityModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      <div class="modal-header text-white" style="background: #142563;">
        <h5 class="modal-title fw-bold">
          <i class="bi bi-pencil-square me-2 text-warning"></i>
          <span>Edit Profil Entitas: <span id="prepEntModalCode">-</span></span>
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form id="prepEntityForm" onsubmit="savePrepEntity(event)">
        <input type="hidden" id="prepEntCode">
        <div class="modal-body p-4 bg-light">
          <div class="card p-4 border-0 rounded-3 shadow-sm bg-white mb-3">
            <div class="row g-3">
              <div class="col-md-8">
                <label class="form-label fw-bold small">Nama Resmi Entitas <span class="text-danger">*</span></label>
                <input type="text" id="prepEntName" class="form-control" required>
              </div>
              <div class="col-md-4">
                <label class="form-label fw-bold small">Warna Aksen</label>
                <select id="prepEntColor" class="form-select">
                  <option value="gold">Gold (Emas MGI)</option>
                  <option value="blue">Blue (Royal Blue MIU)</option>
                  <option value="navy">Navy (MSI)</option>
                  <option value="green">Green (Mypurcase)</option>
                </select>
              </div>

              <div class="col-12">
                <label class="form-label fw-bold small">Peran Utama dalam Sinergi <span class="text-danger">*</span></label>
                <input type="text" id="prepEntRole" class="form-control" required>
              </div>

              <div class="col-12">
                <label class="form-label fw-bold small">Fokus Kerja Lapangan</label>
                <textarea id="prepEntFocus" class="form-control" rows="3"></textarea>
              </div>

              <div class="col-12">
                <label class="form-label fw-bold small">Output Utama &amp; Akuntabilitas Hasil</label>
                <textarea id="prepEntOutput" class="form-control" rows="3"></textarea>
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer bg-white border-top p-3 d-flex justify-content-between">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" id="btnSavePrepEnt" class="btn btn-mgi-primary px-4">
            <i class="bi bi-cloud-arrow-up-fill me-1"></i> Simpan Profil Entitas
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- MODAL 4: TAMBAH / EDIT TAHAPAN WORKFLOW PREPARATION -->
<div class="modal fade" id="prepWorkflowModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      <div class="modal-header text-white" style="background: #142563;">
        <h5 class="modal-title fw-bold" id="prepWfModalTitle">Tambah Tahapan Alur Kerja</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form id="prepWorkflowForm" onsubmit="savePrepWorkflow(event)">
        <input type="hidden" id="prepWfId">
        <div class="modal-body p-4 bg-light">
          <div class="card p-4 border-0 rounded-3 shadow-sm bg-white mb-3">
            <div class="row g-3">
              <div class="col-md-3">
                <label class="form-label fw-bold small">Nomor Tahap <span class="text-danger">*</span></label>
                <input type="number" id="prepWfStep" class="form-control" min="1" required>
              </div>
              <div class="col-md-9">
                <label class="form-label fw-bold small">Judul Tahapan <span class="text-danger">*</span></label>
                <input type="text" id="prepWfTitle" class="form-control" placeholder="1. Penempatan Modal Investasi" required>
              </div>

              <div class="col-12">
                <label class="form-label fw-bold small">Pihak / Aktor Terlibat <span class="text-danger">*</span></label>
                <input type="text" id="prepWfActor" class="form-control" placeholder="Investor → PT Montana Global Investama (MGI)" required>
              </div>

              <div class="col-12">
                <label class="form-label fw-bold small">Uraian Alur Kerja</label>
                <textarea id="prepWfDesc" class="form-control" rows="3" placeholder="Jelaskan mekanisme kerja dan alur proses pada tahapan ini..."></textarea>
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer bg-white border-top p-3 d-flex justify-content-between">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" id="btnSavePrepWf" class="btn btn-mgi-primary px-4">
            <i class="bi bi-cloud-arrow-up-fill me-1"></i> Simpan Tahapan
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

  let ekosistemNodes = [];
  let ekosistemModalInstance = null;

  let prepData = null;
  let prepEntityModalInstance = null;
  let prepWorkflowModalInstance = null;

  document.addEventListener('DOMContentLoaded', () => {
    stepModalInstance = new bootstrap.Modal(document.getElementById('stepModal'));
    ekosistemModalInstance = new bootstrap.Modal(document.getElementById('ekosistemModal'));
    prepEntityModalInstance = new bootstrap.Modal(document.getElementById('prepEntityModal'));
    prepWorkflowModalInstance = new bootstrap.Modal(document.getElementById('prepWorkflowModal'));

    loadSteps();
    loadAdminGrowthData();
  });

  // =========================================================================
  // 1. GROWTH DATA CONTROLLER
  // =========================================================================
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
    captureCurrentSegmentInputs();
    currentActiveSegment = seg;
    ['mgi', 'miu', 'msi'].forEach(s => {
      const btn = document.getElementById(`segBtn-${s}`);
      if (btn) {
        if (s === seg) btn.classList.add('active');
        else btn.classList.remove('active');
      }
    });
    renderActiveSegmentForm();
  }

  function renderActiveSegmentForm() {
    if (!adminGrowthData) return;
    const seg = adminGrowthData[currentActiveSegment];
    if (!seg) return;

    document.getElementById('adminSegTitle').innerText = seg.title;
    document.getElementById('adminSegBadge').innerText = seg.subtitle;
    document.getElementById('adminSegUnit').innerText = `Satuan: ${seg.unit}`;

    // Render 4 KPIs
    const kpiContainer = document.getElementById('kpiEditorCardsContainer');
    kpiContainer.innerHTML = (seg.kpis || []).map((kpi, idx) => {
      const kpiVal = kpi.value !== undefined ? kpi.value : (kpi.val !== undefined ? kpi.val : '');
      const kpiDesc = kpi.note !== undefined ? kpi.note : (kpi.desc !== undefined ? kpi.desc : '');
      return `
      <div class="col-12 col-md-6 col-lg-3">
        <div class="card p-3 border rounded-3 bg-white h-100 shadow-sm">
          <div class="small fw-bold text-muted text-uppercase mb-1">Kartu KPI #${idx + 1}</div>
          <div class="mb-2">
            <label class="form-label small mb-1">Judul / Label</label>
            <input type="text" class="form-control form-control-sm kpi-label-input" data-kpi-idx="${idx}" value="${kpi.label || ''}">
          </div>
          <div class="mb-2">
            <label class="form-label small mb-1 fw-bold text-success">Angka / Nilai Persentase</label>
            <input type="text" class="form-control form-control-sm fw-bold text-success kpi-val-input" data-kpi-idx="${idx}" value="${kpiVal}">
          </div>
          <div>
            <label class="form-label small mb-1 text-muted">Keterangan Subteks</label>
            <input type="text" class="form-control form-control-sm text-muted kpi-desc-input" data-kpi-idx="${idx}" value="${kpiDesc}">
          </div>
        </div>
      </div>
      `;
    }).join('');

    // Render Growth Table
    const thead = document.getElementById('growthDataTableHead');
    const tbody = document.getElementById('growthDataTableBody');

    const yearsList = adminGrowthData.years || [];
    thead.innerHTML = `
      <th style="width: 260px;">Metrik Performa</th>
      ${yearsList.map(y => `<th class="text-center">${y}</th>`).join('')}
    `;

    const metricTitle = seg.chartLabel || seg.metricName || 'Performa Utama';
    let rowsHtml = `
      <tr>
        <td>
          <div class="fw-bold text-dark">${metricTitle}</div>
          <small class="text-muted">Nilai grafik utama (${seg.unit || ''})</small>
        </td>
        ${(seg.data || []).map((val, idx) => `
          <td class="text-center">
            <input type="number" step="any" class="form-control form-control-sm text-center metric-val-input" data-year-idx="${idx}" value="${val}">
          </td>
        `).join('')}
      </tr>
    `;

    if (seg.assetValue) {
      rowsHtml += `
        <tr>
          <td>
            <div class="fw-bold text-dark">${seg.assetMetricName || 'Nilai Aset Terkelola'}</div>
            <small class="text-muted">Metrik pendukung (${seg.assetUnit || 'Miliar IDR'})</small>
          </td>
          ${seg.assetValue.map((val, idx) => `
            <td class="text-center">
              <input type="number" step="any" class="form-control form-control-sm text-center asset-val-input" data-year-idx="${idx}" value="${val}">
            </td>
          `).join('')}
        </tr>
      `;
    }

    tbody.innerHTML = rowsHtml;
  }

  function captureCurrentSegmentInputs() {
    if (!adminGrowthData) return;
    const seg = adminGrowthData[currentActiveSegment];
    if (!seg) return;

    // Capture KPIs
    document.querySelectorAll('.kpi-label-input').forEach(inp => {
      const idx = Number(inp.dataset.kpiIdx);
      if (seg.kpis[idx]) seg.kpis[idx].label = inp.value.trim();
    });
    document.querySelectorAll('.kpi-val-input').forEach(inp => {
      const idx = Number(inp.dataset.kpiIdx);
      if (seg.kpis[idx]) {
        seg.kpis[idx].value = inp.value.trim();
        seg.kpis[idx].val = inp.value.trim();
      }
    });
    document.querySelectorAll('.kpi-desc-input').forEach(inp => {
      const idx = Number(inp.dataset.kpiIdx);
      if (seg.kpis[idx]) {
        seg.kpis[idx].note = inp.value.trim();
        seg.kpis[idx].desc = inp.value.trim();
      }
    });

    // Capture Table Values
    document.querySelectorAll('.metric-val-input').forEach(inp => {
      const idx = Number(inp.dataset.yearIdx);
      seg.data[idx] = Number(inp.value) || 0;
    });

    if (seg.assetValue) {
      document.querySelectorAll('.asset-val-input').forEach(inp => {
        const idx = Number(inp.dataset.yearIdx);
        seg.assetValue[idx] = Number(inp.value) || 0;
      });
    }
  }

  async function saveAdminGrowthData() {
    captureCurrentSegmentInputs();
    const btn = document.getElementById('btnSaveGrowth');
    btn.disabled = true;
    btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span> Menyimpan...`;

    try {
      const res = await fetch('../api/admin/growth.php', {
        method: 'POST',
        headers: { 
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify(adminGrowthData)
      });
      const text = await res.text();
      let json;
      try {
        json = JSON.parse(text);
      } catch (err) {
        console.error('Server response is not valid JSON:', text);
        AdminApp.showToast('Respon server tidak valid. Silakan coba lagi.', 'danger');
        return;
      }
      if (json.success) {
        AdminApp.showToast(json.message || 'Data performa pertumbuhan dan persentase KPI berhasil disimpan!', 'success');
      } else {
        AdminApp.showToast(json.message || 'Gagal menyimpan data.', 'danger');
      }
    } catch (e) {
      console.error('Save Growth Error:', e);
      AdminApp.showToast('Gagal memproses data: ' + (e.message || 'Koneksi bermasalah'), 'danger');
    } finally {
      btn.disabled = false;
      btn.innerHTML = `<i class="bi bi-cloud-arrow-up-fill me-2"></i><span>Simpan Semua Perubahan Grafik &amp; KPI</span>`;
    }
  }

  // =========================================================================
  // 2. TRANSFORMASI STEPS CONTROLLER
  // =========================================================================
  async function loadSteps() {
    try {
      const res = await fetch('../api/admin/content.php?section=transformasi');
      const json = await res.json();
      if (json.success) {
        stepsData = json.data || [];
        renderStepsTable();
      }
    } catch (e) {
      console.error(e);
    }
  }

  function renderStepsTable() {
    const tb = document.getElementById('stepsTableBody');
    if (stepsData.length === 0) {
      tb.innerHTML = `<tr><td colspan="6" class="text-center py-4 text-muted">Belum ada milestone transformasi.</td></tr>`;
      return;
    }

    tb.innerHTML = stepsData.map(st => {
      let highlights = [];
      try {
        highlights = typeof st.highlights === 'string' ? JSON.parse(st.highlights) : (st.highlights || []);
      } catch (e) {
        highlights = [];
      }

      return `
        <tr>
          <td class="text-center fw-bold text-muted">${st.step_order}</td>
          <td><span class="badge bg-royal text-white">${st.year_or_phase}</span></td>
          <td>
            <div class="fw-bold text-dark">${st.title}</div>
            <div class="small text-muted">${st.subtitle || '-'}</div>
          </td>
          <td class="small text-muted" style="max-width: 250px;">${st.description}</td>
          <td>
            <ul class="mb-0 ps-3 small text-muted">
              ${highlights.map(h => `<li>${h}</li>`).join('')}
            </ul>
          </td>
          <td class="text-end">
            <div class="btn-group btn-group-sm">
              <button class="btn btn-outline-primary" onclick="openStepModal(${st.id})">
                <i class="bi bi-pencil-square"></i>
              </button>
              <button class="btn btn-outline-danger" onclick="deleteStep(${st.id}, '${st.title}')">
                <i class="bi bi-trash"></i>
              </button>
            </div>
          </td>
        </tr>
      `;
    }).join('');
  }

  function openStepModal(id = null) {
    document.getElementById('stepForm').reset();
    if (id) {
      const st = stepsData.find(s => Number(s.id) === Number(id));
      if (!st) return;
      document.getElementById('stepModalTitle').textContent = `Edit Milestone: ${st.title}`;
      document.getElementById('stepId').value = st.id;
      document.getElementById('stepOrder').value = st.step_order;
      document.getElementById('stepYear').value = st.year_or_phase;
      document.getElementById('stepStatus').value = st.status || 'completed';
      document.getElementById('stepTitle').value = st.title;
      document.getElementById('stepSubtitle').value = st.subtitle || '';
      document.getElementById('stepDesc').value = st.description || '';

      let highlights = [];
      try {
        highlights = typeof st.highlights === 'string' ? JSON.parse(st.highlights) : (st.highlights || []);
      } catch (e) {}
      document.getElementById('stepHighlights').value = highlights.join('\n');
    } else {
      document.getElementById('stepModalTitle').textContent = 'Tambah Milestone Transformasi';
      document.getElementById('stepId').value = '';
      document.getElementById('stepOrder').value = stepsData.length + 1;
      document.getElementById('stepStatus').value = 'completed';
    }
    stepModalInstance.show();
  }

  async function saveStep(e) {
    e.preventDefault();
    const id = document.getElementById('stepId').value;
    const highlightsArr = document.getElementById('stepHighlights').value.split('\n').map(h => h.trim()).filter(Boolean);

    const payload = {
      id: id || null,
      step_order: Number(document.getElementById('stepOrder').value),
      year_or_phase: document.getElementById('stepYear').value.trim(),
      status: document.getElementById('stepStatus').value,
      title: document.getElementById('stepTitle').value.trim(),
      subtitle: document.getElementById('stepSubtitle').value.trim(),
      description: document.getElementById('stepDesc').value.trim(),
      highlights: highlightsArr
    };

    const btn = document.getElementById('btnSaveStep');
    btn.disabled = true;

    try {
      const res = await fetch('../api/admin/content.php?section=transformasi', {
        method: 'POST',
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
    } catch (e) {
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
    } catch (e) {
      AdminApp.showToast('Gagal menghapus.', 'danger');
    }
  }

  // =========================================================================
  // 3. EKOSISTEM NODES CONTROLLER
  // =========================================================================
  async function loadEkosistemNodes() {
    try {
      const res = await fetch('../api/admin/content.php?section=ekosistem');
      const json = await res.json();
      if (json.success) {
        ekosistemNodes = json.data || [];
        renderEkosistemTable();
      }
    } catch (e) {
      console.error(e);
      AdminApp.showToast('Gagal memuat data ekosistem.', 'danger');
    }
  }

  function renderEkosistemTable() {
    const tb = document.getElementById('ekosistemTableBody');
    if (!ekosistemNodes.length) {
      tb.innerHTML = `<tr><td colspan="7" class="text-center py-4 text-muted">Belum ada node ekosistem.</td></tr>`;
      return;
    }

    tb.innerHTML = ekosistemNodes.map(n => {
      const typeBadge = n.type === 'holding' 
        ? '<span class="badge bg-warning text-dark">Grup</span>'
        : (n.type === 'facility' 
            ? '<span class="badge bg-secondary text-white">Facility</span>' 
            : (n.type === 'partner' ? '<span class="badge bg-info text-dark">Partner</span>' : '<span class="badge bg-primary text-white">Subsidiary</span>'));

      const parentNode = ekosistemNodes.find(p => p.id === n.parent_id);
      const parentLabel = parentNode ? parentNode.label : (n.parent_id || '<span class="text-muted fst-italic">Root (Induk)</span>');

      return `
        <tr>
          <td><code class="fw-bold text-dark">${n.id}</code></td>
          <td>
            <div class="fw-bold text-dark d-flex align-items-center gap-1">
              <i class="bi bi-${n.icon || 'building'} text-primary me-1"></i>
              <span>${n.label}</span>
            </div>
            <small class="text-muted">${n.short_label || '-'}</small>
          </td>
          <td>
            <div>${typeBadge}</div>
            <small class="text-muted">${n.category || '-'}</small>
          </td>
          <td>
            <div class="small fw-semibold">Level ${n.level}</div>
            <small class="text-muted">${parentLabel}</small>
          </td>
          <td><span class="badge bg-light text-dark border">${n.badge || '-'}</span></td>
          <td class="small text-muted" style="max-width: 250px;">${n.role_desc || '-'}</td>
          <td class="text-end">
            <div class="btn-group btn-group-sm">
              <button class="btn btn-outline-primary" onclick="openEkosistemModal('${n.id}')">
                <i class="bi bi-pencil-square"></i>
              </button>
              <button class="btn btn-outline-danger" onclick="deleteEkosistemNode('${n.id}', '${n.label.replace(/'/g, "\\'")}')">
                <i class="bi bi-trash"></i>
              </button>
            </div>
          </td>
        </tr>
      `;
    }).join('');
  }

  function openEkosistemModal(id = null) {
    document.getElementById('ekosistemForm').reset();

    // Populate Parent Select Options dynamically
    const parentSelect = document.getElementById('ekoParentId');
    parentSelect.innerHTML = `<option value="">— Tidak Ada (Root / Grup Utama) —</option>` + 
      ekosistemNodes.filter(n => n.id !== id).map(n => `
        <option value="${n.id}">${n.label} (${n.id})</option>
      `).join('');

    if (id) {
      const node = ekosistemNodes.find(n => n.id === id);
      if (!node) return;
      document.getElementById('ekoModalTitle').textContent = `Edit Node: ${node.label}`;
      document.getElementById('ekoIsEdit').value = '1';
      document.getElementById('ekoId').value = node.id;
      document.getElementById('ekoId').readOnly = true;
      document.getElementById('ekoLabel').value = node.label;
      document.getElementById('ekoShortLabel').value = node.short_label || '';
      document.getElementById('ekoType').value = node.type || 'subsidiary';
      document.getElementById('ekoCategory').value = node.category || '';
      document.getElementById('ekoBadge').value = node.badge || '';
      document.getElementById('ekoParentId').value = node.parent_id || '';
      document.getElementById('ekoLevel').value = node.level || 0;
      document.getElementById('ekoIcon').value = node.icon || 'building';
      document.getElementById('ekoRoleDesc').value = node.role_desc || '';
    } else {
      document.getElementById('ekoModalTitle').textContent = 'Tambah Node Ekosistem Baru';
      document.getElementById('ekoIsEdit').value = '0';
      document.getElementById('ekoId').readOnly = false;
      document.getElementById('ekoId').value = '';
      document.getElementById('ekoLevel').value = '1';
      document.getElementById('ekoType').value = 'subsidiary';
      document.getElementById('ekoParentId').value = 'mgi-holding';
    }
    ekosistemModalInstance.show();
  }

  async function saveEkosistemNode(e) {
    e.preventDefault();
    const isEdit = document.getElementById('ekoIsEdit').value === '1';
    const payload = {
      is_edit: isEdit,
      id: document.getElementById('ekoId').value.trim(),
      label: document.getElementById('ekoLabel').value.trim(),
      short_label: document.getElementById('ekoShortLabel').value.trim(),
      type: document.getElementById('ekoType').value,
      category: document.getElementById('ekoCategory').value.trim(),
      badge: document.getElementById('ekoBadge').value.trim(),
      parent_id: document.getElementById('ekoParentId').value || null,
      level: Number(document.getElementById('ekoLevel').value),
      icon: document.getElementById('ekoIcon').value,
      role_desc: document.getElementById('ekoRoleDesc').value.trim()
    };

    const btn = document.getElementById('btnSaveEko');
    btn.disabled = true;

    try {
      const res = await fetch('../api/admin/content.php?section=ekosistem', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      const json = await res.json();
      if (json.success) {
        AdminApp.showToast(json.message || 'Node ekosistem berhasil disimpan!', 'success');
        ekosistemModalInstance.hide();
        loadEkosistemNodes();
      } else {
        AdminApp.showToast(json.message || 'Gagal menyimpan node.', 'danger');
      }
    } catch (e) {
      AdminApp.showToast('Gagal menyimpan node ekosistem.', 'danger');
    } finally {
      btn.disabled = false;
    }
  }

  async function deleteEkosistemNode(id, label) {
    if (!confirm(`Hapus node ekosistem "${label}" (${id})?\nPastikan tidak ada sub-node yang masih bergantung pada node ini.`)) return;
    try {
      const res = await fetch(`../api/admin/content.php?section=ekosistem&id=${encodeURIComponent(id)}`, {
        method: 'DELETE'
      });
      const json = await res.json();
      if (json.success) {
        AdminApp.showToast(json.message, 'success');
        loadEkosistemNodes();
      } else {
        AdminApp.showToast(json.message, 'danger');
      }
    } catch (e) {
      AdminApp.showToast('Gagal menghapus node.', 'danger');
    }
  }

  // =========================================================================
  // 4. PREPARATION ENTITIES & WORKFLOW CONTROLLER
  // =========================================================================
  async function loadPreparationData() {
    try {
      const res = await fetch('../api/admin/content.php?section=preparation');
      const json = await res.json();
      if (json.success && json.data) {
        prepData = json.data;
        renderPrepEntities();
        renderPrepWorkflow();
      }
    } catch (e) {
      console.error(e);
      AdminApp.showToast('Gagal memuat data preparation.', 'danger');
    }
  }

  function renderPrepEntities() {
    const container = document.getElementById('prepEntitiesCardsContainer');
    if (!prepData || !prepData.entities || !prepData.entities.length) {
      container.innerHTML = `<div class="col-12 text-center py-4 text-muted">Belum ada profil entitas preparation.</div>`;
      return;
    }

    container.innerHTML = prepData.entities.map(e => `
      <div class="col-12 col-md-6">
        <div class="card p-4 border rounded-3 bg-white h-100 shadow-sm d-flex flex-column justify-content-between">
          <div>
            <div class="d-flex align-items-center justify-content-between mb-2">
              <span class="badge bg-royal text-white px-3 py-1 font-monospace fw-bold">${e.code}</span>
              <button class="btn btn-outline-primary btn-sm d-flex align-items-center gap-1" onclick="openPrepEntityModal('${e.code}')">
                <i class="bi bi-pencil-square"></i>
                <span>Edit Profil</span>
              </button>
            </div>
            <h6 class="fw-bold text-dark mb-1">${e.name}</h6>
            <div class="small text-muted mb-3 fst-italic">${e.role}</div>

            <div class="bg-light p-3 rounded mb-2 border">
              <div class="small fw-bold text-secondary text-uppercase" style="font-size: 0.72rem;">Fokus Kerja Lapangan:</div>
              <div class="small text-dark lh-sm">${e.focus || '-'}</div>
            </div>

            <div class="bg-light p-3 rounded border">
              <div class="small fw-bold text-success text-uppercase" style="font-size: 0.72rem;">Output Utama & Akuntabilitas:</div>
              <div class="small text-dark lh-sm">${e.output || '-'}</div>
            </div>
          </div>
        </div>
      </div>
    `).join('');
  }

  function openPrepEntityModal(code) {
    if (!prepData || !prepData.entities) return;
    const e = prepData.entities.find(ent => ent.code === code);
    if (!e) return;

    document.getElementById('prepEntCode').value = e.code;
    document.getElementById('prepEntModalCode').textContent = `${e.name} (${e.code})`;
    document.getElementById('prepEntName').value = e.name;
    document.getElementById('prepEntColor').value = e.color || 'gold';
    document.getElementById('prepEntRole').value = e.role || '';
    document.getElementById('prepEntFocus').value = e.focus || '';
    document.getElementById('prepEntOutput').value = e.output || '';

    prepEntityModalInstance.show();
  }

  async function savePrepEntity(evt) {
    evt.preventDefault();
    const code = document.getElementById('prepEntCode').value;
    const payload = {
      code: code,
      name: document.getElementById('prepEntName').value.trim(),
      color: document.getElementById('prepEntColor').value,
      role: document.getElementById('prepEntRole').value.trim(),
      focus: document.getElementById('prepEntFocus').value.trim(),
      output: document.getElementById('prepEntOutput').value.trim()
    };

    const btn = document.getElementById('btnSavePrepEnt');
    btn.disabled = true;

    try {
      const res = await fetch('../api/admin/content.php?section=preparation&type=entity', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      const json = await res.json();
      if (json.success) {
        AdminApp.showToast(json.message || 'Profil entitas berhasil disimpan!', 'success');
        prepEntityModalInstance.hide();
        loadPreparationData();
      } else {
        AdminApp.showToast(json.message || 'Gagal menyimpan.', 'danger');
      }
    } catch (e) {
      AdminApp.showToast('Gagal menyimpan profil entitas.', 'danger');
    } finally {
      btn.disabled = false;
    }
  }

  function renderPrepWorkflow() {
    const tb = document.getElementById('prepWorkflowTableBody');
    if (!prepData || !prepData.workflow || !prepData.workflow.length) {
      tb.innerHTML = `<tr><td colspan="5" class="text-center py-4 text-muted">Belum ada tahapan alur kerja.</td></tr>`;
      return;
    }

    tb.innerHTML = prepData.workflow.map(w => `
      <tr>
        <td class="text-center fw-bold"><span class="badge bg-warning text-dark">${w.step_number}</span></td>
        <td class="fw-bold text-dark">${w.title}</td>
        <td><span class="badge bg-light text-primary border">${w.actor}</span></td>
        <td class="small text-muted" style="max-width: 320px;">${w.description}</td>
        <td class="text-end">
          <div class="btn-group btn-group-sm">
            <button class="btn btn-outline-primary" onclick="openPrepWorkflowModal(${w.id})">
              <i class="bi bi-pencil-square"></i>
            </button>
            <button class="btn btn-outline-danger" onclick="deletePrepWorkflow(${w.id}, '${w.title.replace(/'/g, "\\'")}')">
              <i class="bi bi-trash"></i>
            </button>
          </div>
        </td>
      </tr>
    `).join('');
  }

  function openPrepWorkflowModal(id = null) {
    document.getElementById('prepWorkflowForm').reset();
    if (id && prepData && prepData.workflow) {
      const w = prepData.workflow.find(wf => Number(wf.id) === Number(id));
      if (!w) return;
      document.getElementById('prepWfModalTitle').textContent = `Edit Tahapan: ${w.title}`;
      document.getElementById('prepWfId').value = w.id;
      document.getElementById('prepWfStep').value = w.step_number;
      document.getElementById('prepWfTitle').value = w.title;
      document.getElementById('prepWfActor').value = w.actor;
      document.getElementById('prepWfDesc').value = w.description || '';
    } else {
      document.getElementById('prepWfModalTitle').textContent = 'Tambah Tahapan Alur Kerja';
      document.getElementById('prepWfId').value = '';
      const nextStep = (prepData && prepData.workflow) ? prepData.workflow.length + 1 : 1;
      document.getElementById('prepWfStep').value = nextStep;
      document.getElementById('prepWfTitle').value = `${nextStep}. `;
    }
    prepWorkflowModalInstance.show();
  }

  async function savePrepWorkflow(evt) {
    evt.preventDefault();
    const id = document.getElementById('prepWfId').value;
    const payload = {
      id: id || null,
      step_number: Number(document.getElementById('prepWfStep').value),
      title: document.getElementById('prepWfTitle').value.trim(),
      actor: document.getElementById('prepWfActor').value.trim(),
      description: document.getElementById('prepWfDesc').value.trim()
    };

    const btn = document.getElementById('btnSavePrepWf');
    btn.disabled = true;

    try {
      const res = await fetch('../api/admin/content.php?section=preparation&type=workflow', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      const json = await res.json();
      if (json.success) {
        AdminApp.showToast(json.message || 'Tahapan alur kerja berhasil disimpan!', 'success');
        prepWorkflowModalInstance.hide();
        loadPreparationData();
      } else {
        AdminApp.showToast(json.message || 'Gagal menyimpan tahapan.', 'danger');
      }
    } catch (e) {
      AdminApp.showToast('Gagal menyimpan tahapan workflow.', 'danger');
    } finally {
      btn.disabled = false;
    }
  }

  async function deletePrepWorkflow(id, title) {
    if (!confirm(`Hapus tahapan workflow "${title}"?`)) return;
    try {
      const res = await fetch(`../api/admin/content.php?section=preparation&type=workflow&id=${id}`, {
        method: 'DELETE'
      });
      const json = await res.json();
      if (json.success) {
        AdminApp.showToast(json.message, 'success');
        loadPreparationData();
      } else {
        AdminApp.showToast(json.message, 'danger');
      }
    } catch (e) {
      AdminApp.showToast('Gagal menghapus tahapan.', 'danger');
    }
  }

  // =========================================================================
  // 5. PROFIL PERUSAHAAN & TATA KELOLA (TARIF) CONTROLLER
  // =========================================================================
  let companyProfileData = null;

  async function loadCompanyProfile() {
    try {
      const res = await fetch('../api/admin/content.php?section=profile');
      const json = await res.json();
      if (json.success && json.data) {
        companyProfileData = json.data;
        renderCompanyProfileForm();
      } else {
        AdminApp.showToast('Gagal memuat profil perusahaan.', 'danger');
      }
    } catch (e) {
      console.error(e);
      AdminApp.showToast('Gagal terhubung ke API profil perusahaan.', 'danger');
    }
  }

  function renderCompanyProfileForm() {
    if (!companyProfileData) return;
    const p = companyProfileData;

    document.getElementById('profCompanyName').value = p.company_name || '';
    document.getElementById('profShortName').value = p.short_name || '';
    document.getElementById('profGroupName').value = p.group_name || '';
    document.getElementById('profTaglineId').value = p.tagline_id || '';
    document.getElementById('profTaglineEn').value = p.tagline_en || '';

    // Visi & Misi
    const vmSection = (p.sections || []).find(s => s.type === 'vision_mission') || {};
    document.getElementById('profVision').value = vmSection.vision || '';
    document.getElementById('profMissions').value = (vmSection.missions || []).join('\n');

    // Core Values
    const valSection = (p.sections || []).find(s => s.id === 'core-values') || {};
    const valContainer = document.getElementById('profValuesContainer');
    if (valContainer && valSection.items) {
      valContainer.innerHTML = valSection.items.map((item, idx) => `
        <div class="col-12 col-md-6 col-lg-4">
          <div class="card p-3 border rounded-3 bg-light h-100">
            <div class="d-flex align-items-center gap-2 mb-2">
              <i class="bi bi-${item.icon || 'star'} text-warning"></i>
              <input type="text" class="form-control form-control-sm fw-bold prof-value-name" data-val-idx="${idx}" value="${item.name}">
            </div>
            <textarea class="form-control form-control-sm prof-value-desc" data-val-idx="${idx}" rows="3">${item.desc}</textarea>
          </div>
        </div>
      `).join('');
    }

    // TARIF Items
    const tarifSection = (p.sections || []).find(s => s.id === 'tata-kelola') || {};
    const tarifContainer = document.getElementById('profTarifContainer');
    if (tarifContainer && tarifSection.items) {
      tarifContainer.innerHTML = tarifSection.items.map((item, idx) => `
        <div class="col-12 col-md-6 col-lg-4">
          <div class="card p-3 border rounded-3 bg-light h-100">
            <div class="d-flex align-items-center gap-2 mb-2">
              <span class="badge bg-royal text-white font-monospace fs-6 px-2">${item.code}</span>
              <input type="text" class="form-control form-control-sm fw-bold prof-tarif-name" data-tarif-idx="${idx}" value="${item.name}">
            </div>
            <textarea class="form-control form-control-sm prof-tarif-desc" data-tarif-idx="${idx}" rows="3">${item.desc}</textarea>
          </div>
        </div>
      `).join('');
    }

    // Kontak
    const contact = p.contact || {};
    document.getElementById('profCorpOffice').value = contact.corporate_office || '';
    document.getElementById('profOpOffice').value = contact.operational_office || '';
    document.getElementById('profEmail').value = contact.email || '';
    document.getElementById('profWorkHours').value = contact.work_hours || '';
    document.getElementById('profMapsUrl').value = contact.google_maps_url || '';
  }

  async function saveCompanyProfile(e) {
    e.preventDefault();
    if (!companyProfileData) return;

    // Capture values
    companyProfileData.company_name = document.getElementById('profCompanyName').value.trim();
    companyProfileData.short_name = document.getElementById('profShortName').value.trim();
    companyProfileData.group_name = document.getElementById('profGroupName').value.trim();
    companyProfileData.tagline_id = document.getElementById('profTaglineId').value.trim();
    companyProfileData.tagline_en = document.getElementById('profTaglineEn').value.trim();

    // Visi Misi
    const vmSection = (companyProfileData.sections || []).find(s => s.type === 'vision_mission');
    if (vmSection) {
      vmSection.vision = document.getElementById('profVision').value.trim();
      vmSection.missions = document.getElementById('profMissions').value.split('\n').map(m => m.trim()).filter(Boolean);
    }

    // Values
    const valSection = (companyProfileData.sections || []).find(s => s.id === 'core-values');
    if (valSection && valSection.items) {
      document.querySelectorAll('.prof-value-name').forEach(inp => {
        const idx = Number(inp.dataset.valIdx);
        if (valSection.items[idx]) valSection.items[idx].name = inp.value.trim();
      });
      document.querySelectorAll('.prof-value-desc').forEach(inp => {
        const idx = Number(inp.dataset.valIdx);
        if (valSection.items[idx]) valSection.items[idx].desc = inp.value.trim();
      });
    }

    // TARIF
    const tarifSection = (companyProfileData.sections || []).find(s => s.id === 'tata-kelola');
    if (tarifSection && tarifSection.items) {
      document.querySelectorAll('.prof-tarif-name').forEach(inp => {
        const idx = Number(inp.dataset.tarifIdx);
        if (tarifSection.items[idx]) tarifSection.items[idx].name = inp.value.trim();
      });
      document.querySelectorAll('.prof-tarif-desc').forEach(inp => {
        const idx = Number(inp.dataset.tarifIdx);
        if (tarifSection.items[idx]) tarifSection.items[idx].desc = inp.value.trim();
      });
    }

    // Contact
    if (!companyProfileData.contact) companyProfileData.contact = {};
    companyProfileData.contact.corporate_office = document.getElementById('profCorpOffice').value.trim();
    companyProfileData.contact.operational_office = document.getElementById('profOpOffice').value.trim();
    companyProfileData.contact.email = document.getElementById('profEmail').value.trim();
    companyProfileData.contact.work_hours = document.getElementById('profWorkHours').value.trim();
    companyProfileData.contact.google_maps_url = document.getElementById('profMapsUrl').value.trim();

    const btn = document.getElementById('btnSaveProfile');
    btn.disabled = true;
    btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span> Menyimpan...`;

    try {
      const res = await fetch('../api/admin/content.php?section=profile', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(companyProfileData)
      });
      const json = await res.json();
      if (json.success) {
        AdminApp.showToast('Profil perusahaan & data tata kelola berhasil disimpan!', 'success');
      } else {
        AdminApp.showToast(json.message || 'Gagal menyimpan profil.', 'danger');
      }
    } catch (e) {
      AdminApp.showToast('Gagal menyimpan profil perusahaan.', 'danger');
    } finally {
      btn.disabled = false;
      btn.innerHTML = `<i class="bi bi-cloud-arrow-up-fill me-2"></i><span>Simpan Perubahan Profil &amp; Tata Kelola</span>`;
    }
  }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
