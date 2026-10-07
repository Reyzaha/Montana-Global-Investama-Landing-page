<?php
$pageTitle = 'Kelola Proyek Investasi';
require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
  <div>
    <h5 class="fw-bold text-dark mb-1">Manajemen Portofolio Proyek</h5>
    <p class="text-muted small mb-0">Kelola informasi proyek riil, rencana anggaran (RAB), dan simulasi ROI.</p>
  </div>
  <div class="d-flex align-items-center gap-2">
    <button type="button" class="btn btn-mgi-gold d-flex align-items-center gap-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#projectModal" onclick="openCreateModal()">
      <i class="bi bi-plus-circle-fill"></i>
      <span>Tambah Proyek Baru</span>
    </button>
  </div>
</div>

<!-- Table Card -->
<div class="admin-card">
  <div class="admin-card-header bg-white">
    <div class="d-flex align-items-center gap-2">
      <i class="bi bi-list-columns text-primary" style="color: #142563 !important;"></i>
      <span class="fw-bold text-dark">Daftar Proyek Aktif</span>
    </div>
    <div class="d-flex align-items-center gap-2">
      <input type="text" id="filterSearch" class="form-control form-control-sm" placeholder="Cari judul proyek..." style="width: 200px;" oninput="renderProjectsTable()">
      <select id="filterStatus" class="form-select form-select-sm" style="width: 130px;" onchange="renderProjectsTable()">
        <option value="">Semua Status</option>
        <option value="Open">Open</option>
        <option value="Fully Funded">Fully Funded</option>
        <option value="Coming Soon">Coming Soon</option>
        <option value="Closed">Closed</option>
      </select>
      <select id="filterCity" class="form-select form-select-sm" style="width: 135px;" onchange="renderProjectsTable()">
        <option value="">Semua Kota</option>
      </select>
    </div>
  </div>

  <div class="table-responsive">
    <table class="admin-table">
      <thead>
        <tr>
          <th style="width: 60px;">Foto</th>
          <th>ID &amp; Judul Proyek</th>
          <th>Target &amp; Terkumpul</th>
          <th>Tenor &amp; ROI</th>
          <th>Status</th>
          <th>Item RAB</th>
          <th class="text-end">Aksi</th>
        </tr>
      </thead>
      <tbody id="projectsListBody">
        <tr>
          <td colspan="7" class="text-center py-5 text-muted">
            <span class="spinner-border spinner-border-sm me-2"></span> Memuat katalog proyek...
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</div>

<!-- MODAL TAMBAH / EDIT PROYEK -->
<div class="modal fade" id="projectModal" tabindex="-1" aria-labelledby="projectModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <div class="modal-header text-white" style="background: #142563;">
        <h5 class="modal-title fw-bold" id="projectModalLabel">
          <i class="bi bi-pencil-square me-2 text-warning" style="color: #C5A059 !important;"></i>
          <span id="modalTitleText">Tambah Proyek Investasi</span>
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-4 bg-light">
        <!-- Tabs Navigation -->
        <ul class="nav nav-pills nav-fill mb-4 bg-white p-2 rounded-3 border" id="projectFormTabs" role="tablist">
          <li class="nav-item">
            <button class="nav-link active fw-bold small" id="tab-basic-btn" data-bs-toggle="pill" data-bs-target="#tab-basic" type="button">
              1. Info Utama &amp; Target
            </button>
          </li>
          <li class="nav-item">
            <button class="nav-link fw-bold small" id="tab-desc-btn" data-bs-toggle="pill" data-bs-target="#tab-desc" type="button">
              2. Deskripsi Proposal Proyek
            </button>
          </li>
          <li class="nav-item">
            <button class="nav-link fw-bold small" id="tab-rab-btn" data-bs-toggle="pill" data-bs-target="#tab-rab" type="button">
              3. Rencana Anggaran (RAB)
            </button>
          </li>
          <li class="nav-item">
            <button class="nav-link fw-bold small" id="tab-sim-btn" data-bs-toggle="pill" data-bs-target="#tab-sim" type="button">
              4. Parameter Simulasi BEP
            </button>
          </li>
          <li class="nav-item">
            <button class="nav-link fw-bold small" id="tab-rab-exec-btn" data-bs-toggle="pill" data-bs-target="#tab-rab-exec" type="button">
              <i class="bi bi-ui-radios-grid text-warning me-1"></i> 5. Opsi Paket &amp; Fasilitas
            </button>
          </li>
        </ul>

        <form id="projectForm">
          <input type="hidden" id="formMode" value="create">

          <div class="tab-content" id="projectFormTabContent">
            
            <!-- TAB 1: INFO UTAMA & FINANSIAL -->
            <div class="tab-pane fade show active" id="tab-basic">
              <div class="card p-4 border-0 rounded-3 shadow-sm bg-white mb-3">
                <div class="d-flex align-items-center justify-content-between border-bottom pb-2 mb-3">
                  <h6 class="fw-bold text-dark mb-0"><i class="bi bi-info-circle-fill text-primary me-2"></i>1. Identitas, Wilayah Kota &amp; Status Proyek</h6>
                  <span class="badge bg-primary-subtle text-primary border border-primary-subtle">Wajib Dilengkapi</span>
                </div>

                <div class="row g-3">
                  <div class="col-md-3">
                    <label class="form-label">ID Proyek (Unik) <span class="text-danger">*</span></label>
                    <input type="text" id="pId" class="form-control" placeholder="proj-005" required>
                    <small class="text-muted" style="font-size: 0.72rem;">Contoh: proj-001</small>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Judul Proyek <span class="text-danger">*</span></label>
                    <input type="text" id="pTitle" class="form-control" placeholder="Pengadaan Unit Alat Berat CBU..." required>
                  </div>
                  <div class="col-md-3">
                    <label class="form-label">Kategori Proyek</label>
                    <input type="text" id="pCategory" class="form-control" value="Alat Berat &amp; Infrastruktur">
                  </div>

                  <!-- SEGMEN KOTA PILIHAN ADMIN -->
                  <div class="col-md-4">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                      <label class="form-label fw-bold text-dark mb-0">
                        <i class="bi bi-geo-alt-fill text-warning me-1"></i> Segmen Kota <span class="text-danger">*</span>
                      </label>
                      <a href="cities.php" target="_blank" class="small text-primary text-decoration-none fw-semibold" title="Buka manajemen kota">
                        <i class="bi bi-plus-circle-fill me-1"></i>Kelola Kota
                      </a>
                    </div>
                    <select id="pCityId" class="form-select border-primary fw-semibold" required>
                      <option value="">-- Pilih Kota Wilayah Proyek --</option>
                    </select>
                    <small class="text-muted" style="font-size: 0.72rem;">Menentukan penempatan di segmen "Per Kota" pada halaman investasi</small>
                  </div>

                  <div class="col-md-5">
                    <label class="form-label">Detail Alamat / Lokasi Operasional</label>
                    <input type="text" id="pLokasi" class="form-control" placeholder="Kebumen &amp; Cilacap, Jawa Tengah">
                    <small class="text-muted" style="font-size: 0.72rem;">Area pool, kecamatan, atau zona kawasan industri</small>
                  </div>

                  <div class="col-md-3">
                    <label class="form-label">Status Proyek <span class="text-danger">*</span></label>
                    <select id="pStatus" class="form-select fw-semibold" required>
                      <option value="Open">Open</option>
                      <option value="Fully Funded">Fully Funded</option>
                      <option value="Coming Soon">Coming Soon</option>
                      <option value="Closed">Closed</option>
                    </select>
                  </div>

                  <div class="col-md-9">
                    <label class="form-label fw-semibold">Attachment / Foto Sampul Proyek <span class="text-danger">*</span></label>
                    <div class="d-flex align-items-center gap-3 p-2 bg-light rounded border">
                      <!-- Thumbnail Preview -->
                      <div class="position-relative border rounded p-1 bg-white flex-shrink-0" style="width: 72px; height: 52px; overflow: hidden;">
                        <img id="projectImagePreview" src="../assets/img/project-jabodetabek.jpg" alt="Preview" class="w-100 h-100 rounded" style="object-fit: cover;" onerror="this.onerror=null;this.src='../assets/img/project-jabodetabek.jpg'">
                      </div>
                      
                      <!-- Upload Controller -->
                      <div class="flex-grow-1 overflow-hidden">
                        <input type="hidden" id="pImage" value="assets/img/project-jabodetabek.jpg">
                        <input type="file" id="pImageUpload" class="d-none" accept="image/png, image/jpeg, image/webp, image/gif, image/svg+xml" onchange="handleProjectImageUpload(this)">
                        
                        <div class="d-flex align-items-center gap-2 mb-1">
                          <button type="button" class="btn btn-outline-primary btn-sm d-flex align-items-center gap-1 shadow-sm" onclick="document.getElementById('pImageUpload').click()" id="btnUploadImageTrigger">
                            <i class="bi bi-cloud-arrow-up-fill"></i>
                            <span>Upload Attachment Gambar</span>
                          </button>
                          <span class="small text-muted" id="uploadImageStatus" style="font-size: 0.75rem;">Maks. 10MB</span>
                        </div>
                        <div class="small text-muted font-monospace text-truncate" id="currentImagePathDisplay" style="font-size: 0.72rem;">
                          assets/img/project-jabodetabek.jpg
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="col-md-3 d-flex align-items-center">
                    <div class="form-check p-3 bg-light rounded border w-100 mb-0">
                      <input class="form-check-input ms-0 me-2" type="checkbox" id="pFeatured">
                      <label class="form-check-label fw-bold text-dark" for="pFeatured">Sorotan (Featured)</label>
                      <div class="text-muted" style="font-size: 0.7rem;">Ditampilkan di hero syndication</div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="card p-4 border-0 rounded-3 shadow-sm bg-white">
                <h6 class="fw-bold text-dark border-bottom pb-2 mb-3"><i class="bi bi-cash-stack text-success me-2"></i>2. Target Finansial &amp; Metrik Investor</h6>
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label">Target Pendanaan (Rupiah) <span class="text-danger">*</span></label>
                    <input type="number" id="pFundingTarget" class="form-control" placeholder="20000000000" min="1" step="1" required oninput="updateMoneyPreview('pFundingTarget', 'previewFundingTarget')">
                    <div class="live-rupiah-preview" id="previewFundingTarget">Rp 0</div>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Dana Terhimpun (Rupiah)</label>
                    <input type="number" id="pFundingCollected" class="form-control" placeholder="15500000000" min="0" step="1" oninput="updateMoneyPreview('pFundingCollected', 'previewFundingCollected')">
                    <div class="live-rupiah-preview" id="previewFundingCollected">Rp 0</div>
                  </div>

                  <div class="col-md-3">
                    <label class="form-label">Tenor Investasi</label>
                    <input type="text" id="pTenor" class="form-control" value="36 Bulan">
                  </div>
                  <div class="col-md-3">
                    <label class="form-label">Proyeksi Return Rate</label>
                    <input type="text" id="pReturnRate" class="form-control" value="≥30% (p.a.)">
                  </div>
                  <div class="col-md-3">
                    <label class="form-label">Profil Risiko</label>
                    <input type="text" id="pRiskLevel" class="form-control" value="Menengah - Terukur">
                  </div>
                  <div class="col-md-3">
                    <label class="form-label">Tiket Min. Investasi</label>
                    <input type="text" id="pMinInvest" class="form-control" value="Rp 500.000.000">
                  </div>

                  <div class="col-md-4">
                    <label class="form-label">Jadwal Bagi Hasil</label>
                    <input type="text" id="pPayout" class="form-control" value="Bagi Hasil Kompetitif">
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Sisa Waktu Penawaran</label>
                    <input type="text" id="pRemainingDays" class="form-control" value="18 Hari Tersisa">
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Proteksi Aset Riil (Asset-Backed)</label>
                    <input type="text" id="pAssetBacked" class="form-control" value="Unit CBU Grade A &amp; BPKB">
                  </div>
                </div>
              </div>
            </div>

            <!-- TAB 2: DESKRIPSI PROSPEKTUS -->
            <div class="tab-pane fade" id="tab-desc">
              <div class="card p-4 border-0 rounded-3 shadow-sm bg-white">
                <div class="mb-3">
                  <label class="form-label">Tagline Proposal Proyek</label>
                  <input type="text" id="pTagline" class="form-control" placeholder="Ekspansi Unit Produktif CBU Jepang Siap Operasi Proyek Nasional">
                </div>

                <div class="mb-3">
                  <label class="form-label">1. Alokasi Penggunaan Modal (What Your Invest Will Provide)</label>
                  <textarea id="pWhatWillProvide" class="form-control" rows="4" placeholder="Jelaskan secara transparan peruntukan modal yang dihimpun..."></textarea>
                </div>

                <div class="mb-3">
                  <label class="form-label">2. Struktur Kemitraan &amp; Penyerapan Pasar (About Sinergi Foundation / Offtake)</label>
                  <textarea id="pSinergi" class="form-control" rows="4" placeholder="Jelaskan kontrak kerja sama, kepastian penyerapan pasar, dan program sosial..."></textarea>
                </div>

                <div class="mb-3">
                  <label class="form-label">3. Ringkasan Kelayakan Investasi &amp; Profil Risiko (Summary)</label>
                  <textarea id="pSummary" class="form-control" rows="4" placeholder="Kesimpulan analisis fundamental, daya saing pasar, dan mitigasi risiko..."></textarea>
                </div>
              </div>
            </div>

            <!-- TAB 3: DYNAMIC RAB (FUNDING ITEMS) -->
            <div class="tab-pane fade" id="tab-rab">
              <div class="card p-4 border-0 rounded-3 shadow-sm bg-white">
                <div class="d-flex align-items-center justify-content-between mb-3">
                  <div>
                    <h6 class="fw-bold text-dark mb-0">Rincian Rencana Anggaran Biaya (CAPEX Breakdown)</h6>
                    <small class="text-muted">Baris item fleksibel yang ditampilkan sebagai tabel pendanaan di halaman detail.</small>
                  </div>
                  <button type="button" class="btn btn-sm btn-outline-primary d-flex align-items-center gap-1" onclick="addRabRow()">
                    <i class="bi bi-plus-lg"></i>
                    <span>Tambah Baris Item</span>
                  </button>
                </div>

                <div class="table-responsive">
                  <table class="table table-bordered table-sm align-middle" id="rabTable">
                    <thead class="table-light">
                      <tr class="small text-uppercase">
                        <th style="width: 40px;" class="text-center">No</th>
                        <th>Nama Item Pengadaan / Spesifikasi Unit</th>
                        <th style="width: 90px;" class="text-center">Qty</th>
                        <th style="width: 200px;">Harga Satuan (Rp)</th>
                        <th style="width: 220px;">Total Alokasi (Rp)</th>
                        <th style="width: 50px;" class="text-center">Aksi</th>
                      </tr>
                    </thead>
                    <tbody id="rabTableBody">
                      <!-- Dynamic rows will appear here -->
                    </tbody>
                    <tfoot>
                      <tr class="table-secondary fw-bold">
                        <td colspan="4" class="text-end">TOTAL KESELURUHAN (RAB):</td>
                        <td id="rabGrandTotalDisplay" class="text-success fw-bold">Rp 0</td>
                        <td></td>
                      </tr>
                    </tfoot>
                  </table>
                </div>
              </div>
            </div>

            <!-- TAB 4: PARAMETER SIMULASI BEP -->
            <div class="tab-pane fade" id="tab-sim">
              <div class="card p-4 border-0 rounded-3 shadow-sm bg-white">
                <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">Parameter Kalkulator BEP &amp; ROI Investor</h6>
                <div class="row g-3">
                  <div class="col-md-4">
                    <label class="form-label">Tenor Simulasi (Bulan)</label>
                    <input type="number" id="simTenor" class="form-control" value="36" min="1">
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Estimasi Return (% per tahun)</label>
                    <input type="number" id="simReturn" class="form-control" value="30.00" step="0.1" min="0">
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Modal Kerja Bulanan (% per bulan)</label>
                    <input type="number" id="simModalKerja" class="form-control" value="2.20" step="0.1" min="0">
                  </div>

                  <div class="col-md-4">
                    <label class="form-label">Tiket Minimum Investasi (Rp)</label>
                    <input type="number" id="simMin" class="form-control" value="500000000" min="1000000" step="1000000" oninput="updateMoneyPreview('simMin', 'previewSimMin')">
                    <div class="live-rupiah-preview" id="previewSimMin">Rp 500.000.000</div>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Batas Maksimum Investasi (Rp)</label>
                    <input type="number" id="simMax" class="form-control" value="10000000000" min="10000000" step="10000000" oninput="updateMoneyPreview('simMax', 'previewSimMax')">
                    <div class="live-rupiah-preview" id="previewSimMax">Rp 10.000.000.000</div>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Default Nilai Investasi (Rp)</label>
                    <input type="number" id="simDefault" class="form-control" value="500000000" min="1000000" step="1000000" oninput="updateMoneyPreview('simDefault', 'previewSimDefault')">
                    <div class="live-rupiah-preview" id="previewSimDefault">Rp 500.000.000</div>
                  </div>

                  <div class="col-12">
                    <label class="form-label">Catatan / Disclaimer Simulasi</label>
                    <textarea id="simNotes" class="form-control" rows="2">Simulasi bersifat ilustratif, bukan jaminan — mengacu pada Risk Disclosure Statement.</textarea>
                  </div>
                </div>
              </div>
            </div>

            <!-- TAB 5: OPSI PAKET INVESTASI & FASILITAS OPERATOR -->
            <div class="tab-pane fade" id="tab-rab-exec">
              <div class="card p-4 border-0 rounded-3 shadow-sm bg-white">
                <div class="d-flex align-items-center justify-content-between border-bottom pb-3 mb-3 flex-wrap gap-2">
                  <div>
                    <h6 class="fw-bold text-dark mb-1">
                      <i class="bi bi-ui-radios-grid text-gold me-2"></i>Pengaturan Opsi Paket Investasi &amp; Fasilitas Operator
                    </h6>
                    <small class="text-muted">Tentukan apakah di dalam proyek ini ada 2 pilihan paket, hanya 1 pilihan saja, atau tanpa pilihan paket (target tunggal).</small>
                  </div>
                  <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-outline-primary d-flex align-items-center gap-1 shadow-sm" onclick="loadDefaultMgiPresets()">
                      <i class="bi bi-lightning-charge-fill text-warning"></i>
                      <span>Gunakan Preset MGI (5M &amp; 10M)</span>
                    </button>
                  </div>
                </div>

                <!-- 1. Skema Selector: 2 Pilihan / 1 Pilihan / Tanpa Pilihan -->
                <div class="mb-4">
                  <label class="form-label fw-bold text-dark small mb-2">
                    <i class="bi bi-sliders2 text-primary me-1"></i>Pilih Skema Paket untuk Proyek Ini:
                  </label>
                  <div class="row g-3">
                    <div class="col-md-4">
                      <div class="card p-3 border rounded-3 cursor-pointer h-100 package-mode-card" id="cardMode2" onclick="setPackageMode('2')">
                        <div class="d-flex align-items-center gap-2 mb-2">
                          <input type="radio" name="packageModeRadio" id="radioMode2" value="2" class="form-check-input mt-0" checked>
                          <strong class="text-dark">2 Pilihan Paket</strong>
                          <span class="badge bg-primary text-white rounded-pill ms-auto small" style="font-size: 0.65rem;">Populer</span>
                        </div>
                        <small class="text-muted lh-sm d-block">Investor dapat memilih 1 dari 2 opsi paket investasi (misal: Paket 5M vs Paket 10M).</small>
                      </div>
                    </div>
                    <div class="col-md-4">
                      <div class="card p-3 border rounded-3 cursor-pointer h-100 package-mode-card" id="cardMode1" onclick="setPackageMode('1')">
                        <div class="d-flex align-items-center gap-2 mb-2">
                          <input type="radio" name="packageModeRadio" id="radioMode1" value="1" class="form-check-input mt-0">
                          <strong class="text-dark">1 Pilihan Paket Saja</strong>
                        </div>
                        <small class="text-muted lh-sm d-block">Hanya ada 1 opsi paket terarah (tanpa opsi komparasi lain) bagi calon investor.</small>
                      </div>
                    </div>
                    <div class="col-md-4">
                      <div class="card p-3 border rounded-3 cursor-pointer h-100 package-mode-card" id="cardModeNone" onclick="setPackageMode('none')">
                        <div class="d-flex align-items-center gap-2 mb-2">
                          <input type="radio" name="packageModeRadio" id="radioModeNone" value="none" class="form-check-input mt-0">
                          <strong class="text-dark">Tanpa Pilihan Paket</strong>
                        </div>
                        <small class="text-muted lh-sm d-block">Pendanaan tunggal standar. Tidak ada rentang atau opsi pilihan paket di landing page.</small>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- 2. Kontainer Form Paket (Muncul jika Mode 1 atau 2) -->
                <div id="packageFormsContainer">
                  <div class="row g-3 mb-4">
                    
                    <!-- PILIHAN 1 (OPSI A) -->
                    <div class="col-12 col-md-6" id="colPackage1">
                      <div class="card border rounded-3 p-3 bg-light h-100 shadow-sm" style="border-left: 4px solid #142563 !important;">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                          <span class="fw-bold text-primary small d-flex align-items-center gap-1">
                            <i class="bi bi-1-circle-fill"></i> PILIHAN 1 (OPSI UTAMA)
                          </span>
                          <span class="badge bg-primary text-white small" id="badgePilihan1">Opsi A</span>
                        </div>

                        <div class="row g-2">
                          <div class="col-12">
                            <label class="form-label small text-muted mb-1">Nama / Label Paket</label>
                            <input type="text" id="opt1_label" class="form-control form-control-sm fw-semibold" value="Paket Kemitraan Eksekutif (5 Miliar)">
                          </div>
                          <div class="col-12">
                            <label class="form-label small text-muted mb-1">Nominal Investasi (Rp)</label>
                            <input type="number" id="opt1_nominal" class="form-control form-control-sm fw-bold text-primary" value="5000000000" step="500000000" oninput="updateMoneyPreview('opt1_nominal', 'previewOpt1Nominal')">
                            <div class="live-rupiah-preview text-primary small fw-semibold" id="previewOpt1Nominal">Rp 5.000.000.000</div>
                          </div>
                          <div class="col-12">
                            <label class="form-label small text-muted mb-1">Fasilitas Operator Resmi</label>
                            <input type="text" id="opt1_operator" class="form-control form-control-sm" value="1 Operator Profesional Bersertifikat &amp; Berpengalaman">
                            <small class="text-muted" style="font-size: 0.7rem;">Gaji &amp; operasional dikelola penuh oleh MIU</small>
                          </div>
                          <div class="col-12">
                            <label class="form-label small text-muted mb-1">Alokasi Fisik Unit &amp; Kontainer</label>
                            <input type="text" id="opt1_units" class="form-control form-control-sm" value="20 Unit Alat Berat (5 Kontainer 40FT HC)">
                          </div>
                          <div class="col-12">
                            <label class="form-label small text-muted mb-1">Badge Promosi / Tag</label>
                            <input type="text" id="opt1_badge" class="form-control form-control-sm" value="Bonus 1 Operator Profesional">
                          </div>
                          <div class="col-12">
                            <label class="form-label small text-muted mb-1">Estimasi Proyeksi Imbal Hasil</label>
                            <input type="text" id="opt1_roi" class="form-control form-control-sm" value="14,31% / siklus (28,6% – 42,9% p.a.)">
                          </div>
                          <div class="col-12">
                            <label class="form-label small text-muted mb-1">Deskripsi Singkat Paket</label>
                            <textarea id="opt1_desc" class="form-control form-control-sm" rows="2">Penempatan modal Rp 5 Miliar dengan fasilitas 1 Operator alat berat resmi yang disediakan, digaji, dan dikelola langsung oleh tim operasional PT Montana Indo Utama (MIU) untuk memastikan kesiapan unit di lapangan tanpa repot manajemen operasional harian.</textarea>
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- PILIHAN 2 (OPSI B) -->
                    <div class="col-12 col-md-6" id="colPackage2">
                      <div class="card border rounded-3 p-3 bg-light h-100 shadow-sm" style="border-left: 4px solid #C5A059 !important;">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                          <span class="fw-bold text-warning small d-flex align-items-center gap-1" style="color: #9A7B38 !important;">
                            <i class="bi bi-2-circle-fill"></i> PILIHAN 2 (OPSI KONSORSIUM / BESAR)
                          </span>
                          <span class="badge bg-gold text-white small" id="badgePilihan2">Opsi B</span>
                        </div>

                        <div class="row g-2">
                          <div class="col-12">
                            <label class="form-label small text-muted mb-1">Nama / Label Paket</label>
                            <input type="text" id="opt2_label" class="form-control form-control-sm fw-semibold" value="Paket Konsorsium Utama (10 Miliar)">
                          </div>
                          <div class="col-12">
                            <label class="form-label small text-muted mb-1">Nominal Investasi (Rp)</label>
                            <input type="number" id="opt2_nominal" class="form-control form-control-sm fw-bold text-warning" style="color: #9A7B38 !important;" value="10000000000" step="500000000" oninput="updateMoneyPreview('opt2_nominal', 'previewOpt2Nominal')">
                            <div class="live-rupiah-preview text-warning small fw-semibold" id="previewOpt2Nominal" style="color: #9A7B38 !important;">Rp 10.000.000.000</div>
                          </div>
                          <div class="col-12">
                            <label class="form-label small text-muted mb-1">Fasilitas Operator Resmi</label>
                            <input type="text" id="opt2_operator" class="form-control form-control-sm" value="2 Operator Profesional (Double Shift / 24 Jam)">
                            <small class="text-muted" style="font-size: 0.7rem;">Gaji, asuransi &amp; rotasi shift terkelola penuh</small>
                          </div>
                          <div class="col-12">
                            <label class="form-label small text-muted mb-1">Alokasi Fisik Unit &amp; Kontainer</label>
                            <input type="text" id="opt2_units" class="form-control form-control-sm" value="40 Unit Alat Berat (10 Kontainer 40FT HC)">
                          </div>
                          <div class="col-12">
                            <label class="form-label small text-muted mb-1">Badge Promosi / Tag</label>
                            <input type="text" id="opt2_badge" class="form-control form-control-sm" value="Bonus 2 Operator Profesional (Double Shift)">
                          </div>
                          <div class="col-12">
                            <label class="form-label small text-muted mb-1">Estimasi Proyeksi Imbal Hasil</label>
                            <input type="text" id="opt2_roi" class="form-control form-control-sm" value="14,31% / siklus (28,6% – 42,9% p.a.)">
                          </div>
                          <div class="col-12">
                            <label class="form-label small text-muted mb-1">Deskripsi Singkat Paket</label>
                            <textarea id="opt2_desc" class="form-control form-control-sm" rows="2">Kemitraan tunggal mendanai kapasitas penuh proyek regional (Rp 10 Miliar) dengan fasilitas 2 Operator berlisensi untuk rotasi shift 24/7, hak inspeksi fisik &amp; audit berkala di Central Workshop Kebumen, serta prioritas utama penyerapan pasar kontraktor rekanan nasional.</textarea>
                          </div>
                        </div>
                      </div>
                    </div>

                  </div>
                </div>

                <!-- 3. Alert Saat Mode None Aktif -->
                <div id="noticeNonePackage" class="alert alert-light border border-info-subtle p-3 rounded-3 mb-4" style="display: none; background-color: #F0F9FF;">
                  <div class="d-flex align-items-start gap-2">
                    <i class="bi bi-info-circle-fill text-info fs-5 flex-shrink-0 mt-0.5"></i>
                    <div>
                      <strong class="text-dark d-block mb-1">Mode Tanpa Pilihan Paket Aktif</strong>
                      <div class="text-secondary small">
                        Proyek ini tidak akan menampilkan pilihan paket opsi (A/B) di landing page. Halaman proyek akan menampilkan target pendanaan tunggal standar tanpa rentang yang membingungkan.
                      </div>
                    </div>
                  </div>
                </div>

                <!-- 4. Parameter Spesifikasi Alat Berat & Alokasi (Collapsible) -->
                <details class="mb-3">
                  <summary class="fw-semibold text-dark cursor-pointer small pb-2 border-bottom">
                    <i class="bi bi-gear-fill me-1 text-muted"></i> Pengaturan Tambahan: Spesifikasi Unit &amp; Logistik (Opsional)
                  </summary>
                  <div class="row g-3 mt-1 p-3 bg-light rounded-3 border">
                    <div class="col-md-3">
                      <label class="form-label small text-muted">Model Alat Berat</label>
                      <input type="text" id="execModel" class="form-control form-control-sm fw-bold" value="Unit Alat Berat">
                    </div>
                    <div class="col-md-3">
                      <label class="form-label small text-muted">Estimasi Total Unit</label>
                      <input type="number" id="execTotalUnits" class="form-control form-control-sm fw-bold text-primary" value="40">
                    </div>
                    <div class="col-md-3">
                      <label class="form-label small text-muted">Estimasi Kontainer 40FT HC</label>
                      <input type="number" id="execTotalContainers" class="form-control form-control-sm fw-bold text-primary" value="10">
                    </div>
                    <div class="col-md-3">
                      <label class="form-label small text-muted">Target Perputaran</label>
                      <input type="text" id="execTargetPerputaran" class="form-control form-control-sm fw-bold text-success" value="2 – 3 Kali per Tahun">
                    </div>
                  </div>
                </details>

                <details class="small">
                  <summary class="text-muted cursor-pointer">Lihat / Edit Raw JSON Struktur Executive RAB &amp; Paket</summary>
                  <textarea id="execRabJsonRaw" class="form-control font-monospace mt-2 small" rows="6" style="font-size: 0.75rem;"></textarea>
                </details>
              </div>
            </div>

          </div><!-- /.tab-content -->
        </form>
      </div>

      <div class="modal-footer bg-white border-top p-3 d-flex justify-content-between">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" id="btnSaveProject" class="btn btn-mgi-primary px-4 d-flex align-items-center gap-2" onclick="saveProject()">
          <i class="bi bi-cloud-arrow-up-fill"></i>
          <span>Simpan Data Proyek</span>
        </button>
      </div>
    </div>
  </div>
</div>

<script>
  let projectsData = [];
  let citiesData = [];
  let modalInstance = null;
  let currentRabExecutive = null;

  document.addEventListener('DOMContentLoaded', () => {
    modalInstance = new bootstrap.Modal(document.getElementById('projectModal'));
    loadCities();
    loadProjects();

    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('action') === 'create') {
      openCreateModal();
    }
  });

  async function loadCities() {
    try {
      const res = await fetch('../api/admin/cities.php');
      const json = await res.json();
      if (json.success && Array.isArray(json.data)) {
        citiesData = json.data;

        // Populate table city filter
        const fCity = document.getElementById('filterCity');
        if (fCity) {
          fCity.innerHTML = '<option value="">Semua Kota</option>' + 
            citiesData.map(c => `<option value="${c.id}">${c.name}${c.is_active_segment ? '' : ' (Non-aktif)'}</option>`).join('');
        }

        // Populate modal city select
        const pCity = document.getElementById('pCityId');
        if (pCity) {
          pCity.innerHTML = '<option value="">-- Pilih Segmen Kota --</option>' + 
            citiesData.map(c => `<option value="${c.id}">${c.name} (${c.is_active_segment ? 'Segmen Aktif' : 'Non-aktif'})</option>`).join('');
        }
      }
    } catch (e) {
      console.error('Failed to load cities in projects manager:', e);
    }
  }

  function updateMoneyPreview(inputId, previewId) {
    const val = document.getElementById(inputId).value;
    document.getElementById(previewId).textContent = AdminApp.formatRupiah(val);
  }

  async function loadProjects() {
    const tb = document.getElementById('projectsListBody');
    try {
      const res = await fetch('../api/admin/projects.php');
      const text = await res.text();
      let json;
      try {
        json = JSON.parse(text);
      } catch (err) {
        console.error('Projects API response not JSON:', text);
        if (tb) {
          tb.innerHTML = `<tr><td colspan="7" class="text-center py-5 text-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i>Respon server tidak valid atau database belum terhubung. <button class="btn btn-sm btn-outline-secondary ms-2" onclick="loadProjects()">Coba Lagi</button></td></tr>`;
        }
        return;
      }
      if (!json.success) {
        if (json.status === 401) {
          window.location.href = 'login.php';
          return;
        }
        if (tb) {
          tb.innerHTML = `<tr><td colspan="7" class="text-center py-5 text-danger"><i class="bi bi-exclamation-circle me-2"></i>${json.message || 'Gagal memuat proyek.'} <button class="btn btn-sm btn-outline-secondary ms-2" onclick="loadProjects()">Coba Lagi</button></td></tr>`;
        }
        return;
      }
      projectsData = json.data || [];
      renderProjectsTable();
    } catch (e) {
      console.error(e);
      if (tb) {
        tb.innerHTML = `<tr><td colspan="7" class="text-center py-5 text-danger"><i class="bi bi-wifi-off me-2"></i>Koneksi ke API proyek gagal. <button class="btn btn-sm btn-outline-secondary ms-2" onclick="loadProjects()">Coba Lagi</button></td></tr>`;
      }
      AdminApp.showToast('Gagal memuat data proyek dari server.', 'danger');
    }
  }

  function renderProjectsTable() {
    const search = document.getElementById('filterSearch').value.toLowerCase().trim();
    const status = document.getElementById('filterStatus').value;
    const cityFilter = document.getElementById('filterCity').value;
    const tb = document.getElementById('projectsListBody');

    let filtered = projectsData.filter(p => {
      const matchSearch = !search || p.title.toLowerCase().includes(search) || p.id.toLowerCase().includes(search);
      const matchStatus = !status || p.status === status;
      const matchCity = !cityFilter || p.city_id == cityFilter || (p.city && p.city.toLowerCase() === cityFilter.toLowerCase());
      return matchSearch && matchStatus && matchCity;
    });

    if (filtered.length === 0) {
      tb.innerHTML = `<tr><td colspan="7" class="text-center py-5 text-muted">Tidak ada proyek yang sesuai filter.</td></tr>`;
      return;
    }

    tb.innerHTML = filtered.map(p => {
      const target = Number(p.funding_target);
      const collected = Number(p.funding_collected);
      const pct = target > 0 ? Math.min(100, Math.round((collected / target) * 100)) : 0;
      
      let badgeClass = 'badge-open';
      if (p.status === 'Fully Funded') badgeClass = 'badge-funded';
      if (p.status === 'Coming Soon') badgeClass = 'badge-coming';
      if (p.status === 'Closed') badgeClass = 'badge-closed';

      // Deteksi skema pilihan paket
      let pMode = p.package_options_mode;
      if (!pMode) {
        if (p.target_display && p.target_display.includes('2 Pilihan')) pMode = '2';
        else if (p.target_display && p.target_display.includes('1 Pilihan')) pMode = '1';
        else if (p.target_display && (p.target_display.includes('–') || p.target_display.includes('-'))) pMode = '2';
        else pMode = '2';
      }
      let modeBadge = '';
      if (pMode === '2') {
        modeBadge = '<span class="badge bg-primary-subtle text-primary border border-primary-subtle ms-1" style="font-size: 0.68rem;"><i class="bi bi-ui-radios-grid me-1"></i>2 Pilihan</span>';
      } else if (pMode === '1') {
        modeBadge = '<span class="badge bg-warning-subtle text-dark border border-warning-subtle ms-1" style="font-size: 0.68rem;"><i class="bi bi-check-circle-fill me-1"></i>1 Pilihan</span>';
      } else {
        modeBadge = '<span class="badge bg-light text-muted border ms-1" style="font-size: 0.68rem;">Tanpa Pilihan</span>';
      }

      return `
        <tr>
          <td>
            <img src="../${p.image}" alt="" class="rounded" style="width: 44px; height: 44px; object-fit: cover;" onerror="this.onerror=null;this.src='../assets/img/project-jabodetabek.jpg'">
          </td>
          <td>
            <div class="fw-bold text-dark">${p.title}</div>
            <div class="text-muted small mt-1">
              <code>${p.id}</code> &bull; ${p.category}
              ${p.city ? `<span class="badge bg-primary-subtle text-primary border border-primary-subtle ms-1"><i class="bi bi-geo-alt-fill me-1"></i>${p.city}</span>` : '<span class="badge bg-secondary-subtle text-secondary ms-1">Belum Ada Kota</span>'}
              ${modeBadge}
              ${p.featured == 1 ? '<span class="badge bg-warning text-dark ms-1">Featured</span>' : ''}
            </div>
          </td>
          <td>
            <div class="fw-semibold text-dark">${AdminApp.formatCompact(target)}</div>
            <div class="small text-muted">Terkumpul: ${AdminApp.formatCompact(collected)} (${pct}%)</div>
            <div class="progress mt-1" style="height: 4px; max-width: 140px;">
              <div class="progress-bar" style="width: ${pct}%; background: #C5A059;"></div>
            </div>
          </td>
          <td>
            <div class="text-dark small fw-semibold">${p.tenor}</div>
            <div class="text-success small fw-bold">${p.return_rate}</div>
          </td>
          <td><span class="badge-status ${badgeClass}">${p.status}</span></td>
          <td><span class="badge bg-light text-dark border">${p.item_count || 0} unit RAB</span></td>
          <td class="text-end">
            <div class="btn-group btn-group-sm">
              <button class="btn btn-outline-primary" title="Edit Proyek" onclick="openEditModal('${p.id}')">
                <i class="bi bi-pencil-square"></i>
              </button>
              <button class="btn btn-outline-danger" title="Hapus Proyek" onclick="deleteProject('${p.id}', '${p.title.replace(/'/g, "\\'")}')">
                <i class="bi bi-trash"></i>
              </button>
            </div>
          </td>
        </tr>
      `;
    }).join('');
  }

  function addRabRow(item = { item: '', quantity: 1, unit_price: 0, total: 0 }) {
    const tb = document.getElementById('rabTableBody');
    const rowIdx = tb.children.length + 1;
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td class="text-center fw-bold text-muted rab-no">${rowIdx}</td>
      <td>
        <input type="text" class="form-control form-control-sm rab-item-name" value="${item.item || ''}" placeholder="Nama Unit / Spesifikasi">
      </td>
      <td>
        <input type="number" class="form-control form-control-sm text-center rab-qty" value="${item.quantity || 1}" min="1" oninput="calcRabTotal(this)">
      </td>
      <td>
        <input type="number" class="form-control form-control-sm rab-price" value="${item.unit_price || 0}" min="0" oninput="calcRabTotal(this)">
      </td>
      <td>
        <input type="number" class="form-control form-control-sm rab-total" value="${item.total || (item.quantity * item.unit_price) || 0}" readonly style="background: #F8FAFC;">
      </td>
      <td class="text-center">
        <button type="button" class="btn btn-sm btn-outline-danger border-0 p-1" onclick="removeRabRow(this)">
          <i class="bi bi-x-lg"></i>
        </button>
      </td>
    `;
    tb.appendChild(tr);
    calcRabGrandTotal();
  }

  function removeRabRow(btn) {
    const tr = btn.closest('tr');
    tr.remove();
    // Renumber rows
    const rows = document.querySelectorAll('#rabTableBody tr');
    rows.forEach((r, idx) => {
      r.querySelector('.rab-no').textContent = idx + 1;
    });
    calcRabGrandTotal();
  }

  function calcRabTotal(input) {
    const tr = input.closest('tr');
    const qty = Number(tr.querySelector('.rab-qty').value) || 0;
    const price = Number(tr.querySelector('.rab-price').value) || 0;
    const total = qty * price;
    tr.querySelector('.rab-total').value = total;
    calcRabGrandTotal();
  }

  function calcRabGrandTotal() {
    let grand = 0;
    document.querySelectorAll('.rab-total').forEach(inp => {
      grand += Number(inp.value) || 0;
    });
    document.getElementById('rabGrandTotalDisplay').textContent = AdminApp.formatRupiah(grand);
  }

  // =========================================================================
  // MANAJEMEN SKEMA OPSI PAKET INVESTASI & FASILITAS OPERATOR
  // =========================================================================
  let currentPackageMode = '2'; // '2' | '1' | 'none'

  function setPackageMode(mode) {
    currentPackageMode = mode;

    // Update Radio buttons
    const rad2 = document.getElementById('radioMode2');
    const rad1 = document.getElementById('radioMode1');
    const radNone = document.getElementById('radioModeNone');
    if (rad2) rad2.checked = (mode === '2');
    if (rad1) rad1.checked = (mode === '1');
    if (radNone) radNone.checked = (mode === 'none');

    // Update visual card border
    ['cardMode2', 'cardMode1', 'cardModeNone'].forEach(id => {
      const el = document.getElementById(id);
      if (el) el.classList.remove('border-primary', 'bg-light', 'shadow-sm');
    });
    const activeCardId = mode === '2' ? 'cardMode2' : (mode === '1' ? 'cardMode1' : 'cardModeNone');
    const activeEl = document.getElementById(activeCardId);
    if (activeEl) {
      activeEl.classList.add('border-primary', 'bg-light', 'shadow-sm');
    }

    // Toggle container views
    const formsWrap = document.getElementById('packageFormsContainer');
    const col1 = document.getElementById('colPackage1');
    const col2 = document.getElementById('colPackage2');
    const noticeNone = document.getElementById('noticeNonePackage');

    if (mode === '2') {
      if (formsWrap) formsWrap.style.display = 'block';
      if (col1) {
        col1.style.display = 'block';
        col1.className = 'col-12 col-md-6';
      }
      if (col2) {
        col2.style.display = 'block';
        col2.className = 'col-12 col-md-6';
      }
      if (noticeNone) noticeNone.style.display = 'none';
      const b1 = document.getElementById('badgePilihan1');
      if (b1) b1.textContent = 'Opsi A';
    } else if (mode === '1') {
      if (formsWrap) formsWrap.style.display = 'block';
      if (col1) {
        col1.style.display = 'block';
        col1.className = 'col-12';
      }
      if (col2) col2.style.display = 'none';
      if (noticeNone) noticeNone.style.display = 'none';
      const b1 = document.getElementById('badgePilihan1');
      if (b1) b1.textContent = 'Paket Tunggal';
    } else {
      // Mode None (Tanpa Pilihan Paket)
      if (formsWrap) formsWrap.style.display = 'none';
      if (noticeNone) noticeNone.style.display = 'block';
    }

    syncExecutiveJsonRaw();
  }

  function loadDefaultMgiPresets() {
    setPackageMode('2');
    document.getElementById('opt1_label').value = 'Paket Kemitraan Eksekutif (5 Miliar)';
    document.getElementById('opt1_nominal').value = '5000000000';
    document.getElementById('opt1_operator').value = '1 Operator Profesional Bersertifikat & Berpengalaman';
    document.getElementById('opt1_units').value = '20 Unit Alat Berat (5 Kontainer 40FT HC)';
    document.getElementById('opt1_badge').value = 'Bonus 1 Operator Profesional';
    document.getElementById('opt1_roi').value = '14,31% / siklus (28,6% – 42,9% p.a.)';
    document.getElementById('opt1_desc').value = 'Penempatan modal Rp 5 Miliar dengan fasilitas 1 Operator alat berat resmi yang disediakan, digaji, dan dikelola langsung oleh tim operasional PT Montana Indo Utama (MIU) untuk memastikan kesiapan unit di lapangan tanpa repot manajemen operasional harian.';

    document.getElementById('opt2_label').value = 'Paket Konsorsium Utama (10 Miliar)';
    document.getElementById('opt2_nominal').value = '10000000000';
    document.getElementById('opt2_operator').value = '2 Operator Profesional (Double Shift / 24 Jam)';
    document.getElementById('opt2_units').value = '40 Unit Alat Berat (10 Kontainer 40FT HC)';
    document.getElementById('opt2_badge').value = 'Bonus 2 Operator Profesional (Double Shift)';
    document.getElementById('opt2_roi').value = '14,31% / siklus (28,6% – 42,9% p.a.)';
    document.getElementById('opt2_desc').value = 'Kemitraan tunggal mendanai kapasitas penuh proyek regional (Rp 10 Miliar) dengan fasilitas 2 Operator berlisensi untuk rotasi shift 24/7, hak inspeksi fisik & audit berkala di Central Workshop Kebumen, serta prioritas utama penyerapan pasar kontraktor rekanan nasional.';

    updateMoneyPreview('opt1_nominal', 'previewOpt1Nominal');
    updateMoneyPreview('opt2_nominal', 'previewOpt2Nominal');
    syncExecutiveJsonRaw();
    AdminApp.showToast('Preset Standar MGI (Paket 5M & 10M) berhasil dimuat!', 'info');
  }

  function extractUnitCount(strVal, fallbackNum = 20) {
    if (!strVal) return fallbackNum;
    const str = String(strVal);
    // 1. Cocokkan angka sebelum kata 'unit' atau 'alat'
    const unitMatch = str.match(/(\d+)\s*(?:unit|alat)/i);
    if (unitMatch) return Number(unitMatch[1]);
    // 2. Cek apakah ada baris RAB pertama dengan Qty valid
    const firstRabTr = document.querySelector('#rabTableBody tr');
    if (firstRabTr) {
      const rabQty = Number(firstRabTr.querySelector('.rab-qty')?.value);
      if (rabQty > 0) return rabQty;
    }
    // 3. Ambil angka bilangan bulat terbesar (menghindari mengambil 6 dari 6.25 kontainer)
    const numMatches = str.match(/\b\d+\b/g);
    if (numMatches && numMatches.length > 0) {
      return Math.max(...numMatches.map(Number));
    }
    return fallbackNum;
  }

  function buildPackageOptionsFromForm() {
    if (currentPackageMode === 'none') {
      return [];
    }

    const nom1 = Number(document.getElementById('opt1_nominal').value) || 5000000000;
    const opt1UnitsRaw = document.getElementById('opt1_units').value.trim();
    const opt1UnitQty = extractUnitCount(opt1UnitsRaw, 25);
    const opt1 = {
      nominal: nom1,
      nominal_display: AdminApp.formatRupiah(nom1),
      label: document.getElementById('opt1_label').value.trim() || 'Paket Kemitraan Eksekutif (5 Miliar)',
      badge: document.getElementById('opt1_badge').value.trim() || 'Bonus 1 Operator Profesional',
      featured: false,
      unit_qty: opt1UnitQty,
      container_qty: opt1UnitsRaw || `${opt1UnitQty} Unit Alat Berat`,
      porsi_proyek: `Alokasi ${opt1UnitsRaw || opt1UnitQty + ' Unit Alat Berat'}`,
      jaminan_aset: `${opt1UnitsRaw || opt1UnitQty + ' Unit'} Siap Operasi & Jual`,
      operator_perk: document.getElementById('opt1_operator').value.trim() || '1 Operator Profesional',
      deskripsi: document.getElementById('opt1_desc').value.trim(),
      profit_per_siklus: Math.round(nom1 * 0.14308),
      profit_2x_tahunan: Math.round(nom1 * 0.14308 * 2),
      profit_3x_tahunan: Math.round(nom1 * 0.14308 * 3),
      roi_est: document.getElementById('opt1_roi').value.trim() || '28,6% – 42,9% (p.a.)'
    };

    if (currentPackageMode === '1') {
      return [opt1];
    }

    const nom2 = Number(document.getElementById('opt2_nominal').value) || 10000000000;
    const opt2UnitsRaw = document.getElementById('opt2_units').value.trim();
    const opt2UnitQty = extractUnitCount(opt2UnitsRaw, 40);
    const opt2 = {
      nominal: nom2,
      nominal_display: AdminApp.formatRupiah(nom2),
      label: document.getElementById('opt2_label').value.trim() || 'Paket Konsorsium Utama (10 Miliar)',
      badge: document.getElementById('opt2_badge').value.trim() || 'Bonus 2 Operator Profesional (Double Shift)',
      featured: true,
      unit_qty: opt2UnitQty,
      container_qty: opt2UnitsRaw || `${opt2UnitQty} Unit Alat Berat`,
      porsi_proyek: `Eksklusivitas Penuh Seluruh Batch Regional`,
      jaminan_aset: `${opt2UnitsRaw || opt2UnitQty + ' Unit'} Siap Operasi & Jual`,
      operator_perk: document.getElementById('opt2_operator').value.trim() || '2 Operator Profesional (Double Shift)',
      deskripsi: document.getElementById('opt2_desc').value.trim(),
      profit_per_siklus: Math.round(nom2 * 0.14308),
      profit_2x_tahunan: Math.round(nom2 * 0.14308 * 2),
      profit_3x_tahunan: Math.round(nom2 * 0.14308 * 3),
      roi_est: document.getElementById('opt2_roi').value.trim() || '28,6% – 42,9% (p.a.)'
    };

    return [opt1, opt2];
  }

  function syncExecutiveJsonRaw() {
    const target = Number(document.getElementById('pFundingTarget').value) || 10000000000;
    const model = document.getElementById('execModel').value || 'Unit Alat Berat';
    const units = Number(document.getElementById('execTotalUnits').value) || Math.max(1, Math.round(target / 250000000));
    const containers = Number(document.getElementById('execTotalContainers').value) || Math.max(1, Math.round(units / 4));
    const targetPerputaran = document.getElementById('execTargetPerputaran').value || '2 – 3 Kali per Tahun';

    const packageOptions = buildPackageOptionsFromForm();

    currentRabExecutive = {
      package_options_mode: currentPackageMode,
      package_options: packageOptions,
      tiers: packageOptions, // backwards compatibility
      unit_model: model,
      total_target: target,
      total_target_display: AdminApp.formatRupiah(target),
      total_units: units,
      total_containers: containers,
      target_perputaran: targetPerputaran,
      cost_per_unit: 250000000,
      cost_per_container: 1000000000,
      selling_price_per_unit: 346000000,
      investor_net_profit_per_unit: 35771712,
      investor_roi_per_cycle_pct: "14.31%",
      spesifikasi: {
        barang: model,
        target_perputaran: targetPerputaran,
        estimasi_unit: `${units} Unit (${containers} Kontainer)`
      }
    };

    const rawEl = document.getElementById('execRabJsonRaw');
    if (rawEl) {
      rawEl.value = JSON.stringify(currentRabExecutive, null, 2);
    }
  }

  function openCreateModal() {
    document.getElementById('formMode').value = 'create';
    document.getElementById('modalTitleText').textContent = 'Tambah Proyek Investasi Baru';
    document.getElementById('pId').disabled = false;
    document.getElementById('pId').value = `proj-00${projectsData.length + 1}`;
    document.getElementById('pTitle').value = '';
    document.getElementById('pCategory').value = 'Alat Berat & Infrastruktur';
    document.getElementById('pStatus').value = 'Open';
    document.getElementById('pImage').value = 'assets/img/project-jabodetabek.jpg';
    document.getElementById('projectImagePreview').src = '../assets/img/project-jabodetabek.jpg';
    document.getElementById('currentImagePathDisplay').textContent = 'assets/img/project-jabodetabek.jpg';
    document.getElementById('uploadImageStatus').innerHTML = 'Maks. 10MB';
    document.getElementById('pFeatured').checked = false;
    document.getElementById('pFundingTarget').value = '10000000000';
    document.getElementById('pFundingCollected').value = '0';
    document.getElementById('pCityId').value = '';
    document.getElementById('pLokasi').value = 'Kebumen & Cilacap, Jawa Tengah';
    document.getElementById('pTenor').value = '12 Bulan';
    document.getElementById('pReturnRate').value = '28,6% – 42,9% (p.a.)';
    document.getElementById('pRiskLevel').value = 'Menengah - Terukur';
    document.getElementById('pMinInvest').value = 'Rp 5.000.000.000';
    document.getElementById('pPayout').value = 'Bagi Hasil Per Siklus';
    document.getElementById('pRemainingDays').value = '30 Hari Tersisa';
    document.getElementById('pAssetBacked').value = 'Unit CBU Grade A & BPKB';

    document.getElementById('pTagline').value = '';
    document.getElementById('pWhatWillProvide').value = '';
    document.getElementById('pSinergi').value = '';
    document.getElementById('pSummary').value = '';

    document.getElementById('rabTableBody').innerHTML = '';
    addRabRow({ item: 'Unit Alat Berat Produktif Siap Operasi', quantity: 4, unit_price: 850000000, total: 3400000000 });

    document.getElementById('simTenor').value = 12;
    document.getElementById('simReturn').value = 28.62;
    document.getElementById('simModalKerja').value = 2.38;
    document.getElementById('simMin').value = 5000000000;
    document.getElementById('simMax').value = 10000000000;
    document.getElementById('simDefault').value = 5000000000;

    updateMoneyPreview('pFundingTarget', 'previewFundingTarget');
    updateMoneyPreview('pFundingCollected', 'previewFundingCollected');
    updateMoneyPreview('simMin', 'previewSimMin');
    updateMoneyPreview('simMax', 'previewSimMax');
    updateMoneyPreview('simDefault', 'previewSimDefault');

    // Default preset 2 pilihan paket (5M & 10M)
    loadDefaultMgiPresets();

    // Reset to tab 1
    const tabEl = document.getElementById('tab-basic-btn');
    if (tabEl) {
      const tab = bootstrap.Tab.getOrCreateInstance(tabEl);
      if (tab) tab.show();
    }
    if (!modalInstance) {
      modalInstance = new bootstrap.Modal(document.getElementById('projectModal'));
    }
    modalInstance.show();
  }

  async function openEditModal(id) {
    try {
      const res = await fetch(`../api/admin/projects.php?id=${encodeURIComponent(id)}`);
      const json = await res.json();
      if (!json.success) {
        AdminApp.showToast(json.message, 'danger');
        return;
      }

      const p = json.data.project;
      const d = json.data.detail || {};
      const rab = json.data.funding_items || [];
      const sim = json.data.simulation || {};

      document.getElementById('formMode').value = 'edit';
      document.getElementById('modalTitleText').textContent = `Edit Proyek: ${p.title}`;
      document.getElementById('pId').disabled = true;
      document.getElementById('pId').value = p.id;
      document.getElementById('pTitle').value = p.title;
      document.getElementById('pCategory').value = p.category;
      document.getElementById('pStatus').value = p.status;
      const imgPath = p.image || 'assets/img/project-jabodetabek.jpg';
      document.getElementById('pImage').value = imgPath;
      document.getElementById('projectImagePreview').src = `../${imgPath}`;
      document.getElementById('currentImagePathDisplay').textContent = imgPath;
      document.getElementById('uploadImageStatus').innerHTML = 'Maks. 10MB';
      document.getElementById('pFeatured').checked = p.featured == 1;
      document.getElementById('pFundingTarget').value = p.funding_target;
      document.getElementById('pFundingCollected').value = p.funding_collected;
      document.getElementById('pCityId').value = p.city_id || '';
      document.getElementById('pLokasi').value = p.lokasi;
      document.getElementById('pTenor').value = p.tenor;
      document.getElementById('pReturnRate').value = p.return_rate;
      document.getElementById('pRiskLevel').value = p.risk_level;
      document.getElementById('pMinInvest').value = p.min_investment;
      document.getElementById('pPayout').value = p.payout;
      document.getElementById('pRemainingDays').value = p.remaining_days;
      document.getElementById('pAssetBacked').value = p.asset_backed;

      document.getElementById('pTagline').value = d.tagline || '';
      document.getElementById('pWhatWillProvide').value = d.what_will_provide_content || '';
      document.getElementById('pSinergi').value = d.sinergi_content || '';
      document.getElementById('pSummary').value = d.summary_content || '';

      // RAB Rows
      const tb = document.getElementById('rabTableBody');
      tb.innerHTML = '';
      if (rab.length === 0) {
        addRabRow();
      } else {
        rab.forEach(item => {
          addRabRow({
            item: item.item_name,
            quantity: item.quantity,
            unit_price: item.unit_price,
            total: item.total
          });
        });
      }

      // Simulation
      document.getElementById('simTenor').value = sim.tenor_bulan || 12;
      document.getElementById('simReturn').value = sim.estimasi_return_persen || 28.62;
      document.getElementById('simModalKerja').value = sim.modal_kerja_bulanan_persen || 2.38;
      document.getElementById('simMin').value = sim.minimum_investasi || 5000000000;
      document.getElementById('simMax').value = sim.maximum_investasi || 10000000000;
      document.getElementById('simDefault').value = sim.default_investasi || 5000000000;
      document.getElementById('simNotes').value = sim.notes || '';

      updateMoneyPreview('pFundingTarget', 'previewFundingTarget');
      updateMoneyPreview('pFundingCollected', 'previewFundingCollected');
      updateMoneyPreview('simMin', 'previewSimMin');
      updateMoneyPreview('simMax', 'previewSimMax');
      updateMoneyPreview('simDefault', 'previewSimDefault');

      // Populate Tab 5: Opsi Paket Investasi & Fasilitas Operator
      if (d && d.rab_executive && typeof d.rab_executive === 'object') {
        currentRabExecutive = d.rab_executive;
        const sp = currentRabExecutive.spesifikasi || {};
        
        document.getElementById('execModel').value = currentRabExecutive.unit_model || sp.barang || 'Unit Alat Berat';
        document.getElementById('execTotalUnits').value = currentRabExecutive.total_units || 40;
        document.getElementById('execTotalContainers').value = currentRabExecutive.total_containers || 10;
        document.getElementById('execTargetPerputaran').value = currentRabExecutive.target_perputaran || sp.target_perputaran || '2 – 3 Kali per Tahun';

        // Deteksi mode paket
        let detectedMode = currentRabExecutive.package_options_mode;
        const optionsList = currentRabExecutive.package_options || currentRabExecutive.tiers || [];
        if (!detectedMode) {
          if (optionsList.length >= 2) detectedMode = '2';
          else if (optionsList.length === 1) detectedMode = '1';
          else detectedMode = 'none';
        }
        setPackageMode(detectedMode);

        if (optionsList.length > 0) {
          const opt1 = optionsList[0];
          document.getElementById('opt1_label').value = opt1.label || opt1.name || 'Paket Kemitraan Eksekutif (5 Miliar)';
          document.getElementById('opt1_nominal').value = opt1.nominal || 5000000000;
          document.getElementById('opt1_operator').value = opt1.operator_perk || '1 Operator Profesional Bersertifikat & Berpengalaman';
          let u1 = opt1.unit_qty || opt1.units || 25;
          let c1 = opt1.container_qty || opt1.unit_description || `${u1} Unit Alat Berat`;
          if (!c1.toLowerCase().includes('unit')) c1 = `${u1} Unit Alat Berat (${c1})`;
          document.getElementById('opt1_units').value = c1;
          document.getElementById('opt1_badge').value = opt1.badge || opt1.benefit_tag || 'Bonus 1 Operator Profesional';
          document.getElementById('opt1_roi').value = opt1.roi_est || opt1.est_roi_pa_pct || '14,31% / siklus (28,6% – 42,9% p.a.)';
          document.getElementById('opt1_desc').value = opt1.deskripsi || '';
        }

        if (optionsList.length > 1) {
          const opt2 = optionsList[1];
          document.getElementById('opt2_label').value = opt2.label || opt2.name || 'Paket Konsorsium Utama (10 Miliar)';
          document.getElementById('opt2_nominal').value = opt2.nominal || 10000000000;
          document.getElementById('opt2_operator').value = opt2.operator_perk || '2 Operator Profesional (Double Shift / 24 Jam)';
          let u2 = opt2.unit_qty || opt2.units || 40;
          let c2 = opt2.container_qty || opt2.unit_description || `${u2} Unit Alat Berat`;
          if (!c2.toLowerCase().includes('unit')) c2 = `${u2} Unit Alat Berat (${c2})`;
          document.getElementById('opt2_units').value = c2;
          document.getElementById('opt2_badge').value = opt2.badge || opt2.benefit_tag || 'Bonus 2 Operator Profesional (Double Shift)';
          document.getElementById('opt2_roi').value = opt2.roi_est || opt2.est_roi_pa_pct || '14,31% / siklus (28,6% – 42,9% p.a.)';
          document.getElementById('opt2_desc').value = opt2.deskripsi || '';
        }

        updateMoneyPreview('opt1_nominal', 'previewOpt1Nominal');
        updateMoneyPreview('opt2_nominal', 'previewOpt2Nominal');
        syncExecutiveJsonRaw();
      } else {
        loadDefaultMgiPresets();
      }

      modalInstance.show();

    } catch (e) {
      console.error(e);
      AdminApp.showToast('Gagal memuat detail proyek.', 'danger');
    }
  }

  async function saveProject() {
    const mode = document.getElementById('formMode').value;
    const id = document.getElementById('pId').value.trim();
    const title = document.getElementById('pTitle').value.trim();
    const fundingTarget = document.getElementById('pFundingTarget').value;

    if (!id || !title || !fundingTarget) {
      AdminApp.showToast('ID Proyek, Judul, dan Target Pendanaan wajib diisi.', 'danger');
      return;
    }

    // Collect RAB rows
    const rabRows = [];
    document.querySelectorAll('#rabTableBody tr').forEach(tr => {
      const name = tr.querySelector('.rab-item-name').value.trim();
      const qty = Number(tr.querySelector('.rab-qty').value) || 1;
      const price = Number(tr.querySelector('.rab-price').value) || 0;
      const total = Number(tr.querySelector('.rab-total').value) || (qty * price);
      if (name) {
        rabRows.push({ item_name: name, quantity: qty, unit_price: price, total: total });
      }
    });

    const citySelect = document.getElementById('pCityId');
    const selectedCityId = citySelect.value ? Number(citySelect.value) : null;
    const selectedCityName = citySelect.selectedIndex > 0 ? citySelect.options[citySelect.selectedIndex].text.split(' (')[0].trim() : '';

    // Synchronize package options & build target_display
    syncExecutiveJsonRaw();

    let targetDisplay = AdminApp.formatRupiah(fundingTarget);
    let minInvest = AdminApp.formatRupiah(fundingTarget);

    if (currentPackageMode === '2') {
      const opts = currentRabExecutive.package_options || [];
      const n1 = opts[0] ? opts[0].nominal : 5000000000;
      const n2 = opts[1] ? opts[1].nominal : 10000000000;
      targetDisplay = `Pilihan Paket: ${AdminApp.formatCompact(n1)} & ${AdminApp.formatCompact(n2)}`;
      minInvest = AdminApp.formatRupiah(Math.min(n1, n2));
    } else if (currentPackageMode === '1') {
      const opts = currentRabExecutive.package_options || [];
      const n1 = opts[0] ? opts[0].nominal : fundingTarget;
      targetDisplay = `Pilihan Paket: ${AdminApp.formatRupiah(n1)}`;
      minInvest = AdminApp.formatRupiah(n1);
    } else {
      targetDisplay = AdminApp.formatRupiah(fundingTarget);
      minInvest = AdminApp.formatRupiah(fundingTarget);
    }

    const payload = {
      id: id,
      title: title,
      category: document.getElementById('pCategory').value,
      city_id: selectedCityId,
      city: selectedCityName,
      status: document.getElementById('pStatus').value,
      image: document.getElementById('pImage').value,
      featured: document.getElementById('pFeatured').checked,
      funding_target: fundingTarget,
      funding_collected: document.getElementById('pFundingCollected').value || 0,
      lokasi: document.getElementById('pLokasi').value,
      target_display: targetDisplay,
      tenor: document.getElementById('pTenor').value,
      return_rate: document.getElementById('pReturnRate').value,
      risk_level: document.getElementById('pRiskLevel').value,
      min_investment: minInvest,
      payout: document.getElementById('pPayout').value,
      remaining_days: document.getElementById('pRemainingDays').value,
      asset_backed: document.getElementById('pAssetBacked').value,
      tagline: document.getElementById('pTagline').value,
      what_will_provide_content: document.getElementById('pWhatWillProvide').value,
      sinergi_content: document.getElementById('pSinergi').value,
      summary_content: document.getElementById('pSummary').value,
      funding_items: rabRows,
      simulation: {
        tenor_bulan: document.getElementById('simTenor').value,
        estimasi_return_persen: document.getElementById('simReturn').value,
        modal_kerja_bulanan_persen: document.getElementById('simModalKerja').value,
        minimum_investasi: document.getElementById('simMin').value,
        maximum_investasi: document.getElementById('simMax').value,
        default_investasi: document.getElementById('simDefault').value,
        notes: document.getElementById('simNotes').value
      },
      package_options_mode: currentPackageMode,
      rab_executive: currentRabExecutive
    };

    const btn = document.getElementById('btnSaveProject');
    btn.disabled = true;
    btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span> Menyimpan...`;

    try {
      payload._method = mode === 'create' ? 'POST' : 'PUT';
      const res = await fetch('../api/admin/projects.php', {
        method: 'POST',
        headers: { 
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify(payload)
      });
      const text = await res.text();
      let json;
      try {
        json = JSON.parse(text);
      } catch (err) {
        console.error('Save Project Server Error:', text);
        AdminApp.showToast('Respon server tidak valid saat menyimpan proyek.', 'danger');
        return;
      }

      if (json.success) {
        AdminApp.showToast(json.message || 'Proyek berhasil disimpan.', 'success');
        modalInstance.hide();
        loadProjects();
      } else {
        AdminApp.showToast(json.message || 'Gagal menyimpan proyek.', 'danger');
      }
    } catch (e) {
      console.error(e);
      AdminApp.showToast('Terjadi kesalahan koneksi server: ' + (e.message || ''), 'danger');
    } finally {
      btn.disabled = false;
      btn.innerHTML = `<i class="bi bi-cloud-arrow-up-fill me-2"></i><span>Simpan Data Proyek</span>`;
    }
  }

  async function deleteProject(id, title) {
    if (!confirm(`Apakah Anda yakin ingin menghapus proyek "${title}" (${id})?\nSeluruh rincian RAB dan simulasi terkait akan ikut terhapus.`)) {
      return;
    }

    try {
      const res = await fetch(`../api/admin/projects.php?action=delete&id=${encodeURIComponent(id)}`, {
        method: 'POST',
        headers: { 
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({ _method: 'DELETE', id: id })
      });
      const text = await res.text();
      let json;
      try {
        json = JSON.parse(text);
      } catch (err) {
        console.error('Delete Project Server Error:', text);
        AdminApp.showToast('Respon server tidak valid saat menghapus proyek.', 'danger');
        return;
      }
      if (json.success) {
        AdminApp.showToast(json.message || 'Proyek berhasil dihapus.', 'success');
        loadProjects();
      } else {
        AdminApp.showToast(json.message || 'Gagal menghapus proyek.', 'danger');
      }
    } catch (e) {
      console.error(e);
      AdminApp.showToast('Gagal menghapus proyek: ' + (e.message || ''), 'danger');
    }
  }

  async function handleProjectImageUpload(input) {
    if (!input.files || !input.files[0]) return;
    const file = input.files[0];
    
    const statusEl = document.getElementById('uploadImageStatus');
    const btnTrigger = document.getElementById('btnUploadImageTrigger');
    const pathDisplay = document.getElementById('currentImagePathDisplay');
    const previewImg = document.getElementById('projectImagePreview');
    const hiddenInput = document.getElementById('pImage');

    statusEl.innerHTML = `<span class="spinner-border spinner-border-sm text-primary me-1"></span> Mengunggah ${file.name}...`;
    btnTrigger.disabled = true;

    const formData = new FormData();
    formData.append('file', file);
    formData.append('folder', 'projects');

    try {
      const res = await fetch('../api/admin/upload.php', {
        method: 'POST',
        body: formData
      });
      const text = await res.text();
      let json;
      try {
        json = JSON.parse(text);
      } catch (parseErr) {
        console.error('Upload JSON Parse Error:', text);
        statusEl.innerHTML = `<span class="text-danger fw-bold"><i class="bi bi-exclamation-triangle-fill me-1"></i> Respon server tidak valid</span>`;
        AdminApp.showToast('Gagal memproses respon server upload: ' + (text.substring(0, 100) || ''), 'danger');
        return;
      }

      if (json.success && json.data && json.data.file_path) {
        hiddenInput.value = json.data.file_path;
        pathDisplay.textContent = json.data.file_path;
        previewImg.src = `../${json.data.file_path}`;
        statusEl.innerHTML = `<span class="text-success fw-bold"><i class="bi bi-check-circle-fill me-1"></i> Terunggah</span>`;
        AdminApp.showToast('Foto attachment proyek berhasil diunggah!', 'success');
      } else {
        statusEl.innerHTML = `<span class="text-danger fw-bold"><i class="bi bi-exclamation-triangle-fill me-1"></i> ${json.message || 'Gagal upload'}</span>`;
        AdminApp.showToast(json.message || 'Gagal mengunggah gambar', 'danger');
      }
    } catch (err) {
      statusEl.innerHTML = `<span class="text-danger fw-bold"><i class="bi bi-x-circle me-1"></i> Koneksi upload gagal</span>`;
      AdminApp.showToast('Koneksi upload gagal: ' + (err.message || ''), 'danger');
    } finally {
      btnTrigger.disabled = false;
      input.value = ''; // Reset input agar bisa memilih file yang sama atau mengunggah ulang
    }
  }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
