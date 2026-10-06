<?php
$pageTitle = 'Laporan Keuangan & Billing (Odoo Style)';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Header Title & Quick Stats -->
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
  <div>
    <div class="d-flex align-items-center gap-2 mb-1">
      <span class="badge bg-primary text-white rounded-pill px-3 py-1" style="background: #142563 !important;">
        <i class="bi bi-diagram-3-fill me-1"></i> Modul Finansial Odoo / Kledo Style
      </span>
      <span class="badge bg-warning text-dark rounded-pill px-3 py-1 fw-bold" style="background: #C5A059 !important; color: #fff !important;">
        Real-Time Investor Sync
      </span>
    </div>
    <h4 class="fw-bold text-dark mb-1">Modul Keuangan &amp; Penerbitan Billing Pemodal</h4>
    <p class="text-muted small mb-0">Kelola 4 modul laporan finansial (Neraca, Laba Rugi, Pembelian Unit MIU, Penjualan) serta kirimkan billing laporan alokasi modal ke dashboard investor secara real-time.</p>
  </div>

  <div class="d-flex flex-wrap align-items-center gap-2">
    <button type="button" class="btn btn-outline-secondary btn-sm px-3" onclick="loadFinancialData()">
      <i class="bi bi-arrow-clockwise me-1"></i> Refresh
    </button>
    <div class="btn-group">
      <button type="button" class="btn btn-primary btn-sm dropdown-toggle shadow-sm px-3 py-2 fw-bold" data-bs-toggle="dropdown" aria-expanded="false" style="background: #142563; border-color: #142563;">
        <i class="bi bi-plus-circle-fill me-1 text-warning"></i> + Aksi Cepat Operator
      </button>
      <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-3">
        <li><h6 class="dropdown-header text-uppercase text-muted fw-bold" style="font-size: 0.68rem;">Input Finansial &amp; Billing</h6></li>
        <li><a class="dropdown-item py-2" href="javascript:void(0)" onclick="openOperatorInput('billing')"><i class="bi bi-calculator me-2 text-warning"></i> Buat Billing / Alokasi Dana</a></li>
        <li><a class="dropdown-item py-2" href="javascript:void(0)" onclick="openOperatorInput('pembelian')"><i class="bi bi-cart-check-fill me-2 text-danger"></i> Input Pembelian Unit (via MSI)</a></li>
        <li><a class="dropdown-item py-2" href="javascript:void(0)" onclick="openOperatorInput('biaya')"><i class="bi bi-cash-stack me-2 text-danger"></i> Input Biaya (Impor MIU / Workshop)</a></li>
        <li><a class="dropdown-item py-2" href="javascript:void(0)" onclick="openOperatorInput('penjualan')"><i class="bi bi-receipt-cutoff me-2 text-info"></i> Input Penjualan (Kontrak Sewa)</a></li>
        <li><hr class="dropdown-divider"></li>
        <li><h6 class="dropdown-header text-uppercase text-muted fw-bold" style="font-size: 0.68rem;">Inventaris &amp; Berkas PDF</h6></li>
        <li><a class="dropdown-item py-2" href="javascript:void(0)" onclick="openInventoryModal()"><i class="bi bi-truck-front-fill me-2 text-primary"></i> Input Inventory / Stok Unit Alat</a></li>
        <li><a class="dropdown-item py-2" href="javascript:void(0)" onclick="openPublishDocModal()"><i class="bi bi-file-earmark-pdf-fill me-2 text-danger"></i> Terbitkan Dokumen PDF Resmi</a></li>
      </ul>
    </div>
    <button type="button" class="btn btn-mgi-gold btn-sm d-flex align-items-center gap-2 shadow-sm px-3 py-2" onclick="openCreateModal()">
      <i class="bi bi-send-plus-fill fs-6"></i>
      <span class="fw-bold">+ Kirim Billing / Catat Transaksi</span>
    </button>
  </div>
</div>

<!-- 5 High-Level KPI Summary Cards (Odoo / Apple Dashboard Style) -->
<div class="row g-3 mb-4" id="kpiCardsContainer">
  <div class="col-12 col-sm-6 col-xl-2-4" style="flex: 0 0 20%; max-width: 20%;">
    <div class="admin-card p-3 h-100 bg-white border-start border-4 border-primary">
      <div class="text-muted small text-uppercase fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">Total Modal Ditempatkan</div>
      <div class="fs-5 fw-bold text-dark mt-1" id="kpiTotalCapital">Rp 0</div>
      <small class="text-muted" style="font-size: 0.72rem;">Penempatan Dana Proyek</small>
    </div>
  </div>

  <div class="col-12 col-sm-6 col-xl-2-4" style="flex: 0 0 20%; max-width: 20%;">
    <div class="admin-card p-3 h-100 bg-white border-start border-4 border-danger">
      <div class="text-muted small text-uppercase fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">Pembelian Unit (MIU)</div>
      <div class="fs-5 fw-bold text-danger mt-1" id="kpiPurchasesMIU">Rp 0</div>
      <small class="text-muted" style="font-size: 0.72rem;">Pengadaan Unit CBU via MIU</small>
    </div>
  </div>

  <div class="col-12 col-sm-6 col-xl-2-4" style="flex: 0 0 20%; max-width: 20%;">
    <div class="admin-card p-3 h-100 bg-white border-start border-4 border-success">
      <div class="text-muted small text-uppercase fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">Sisa Kas &amp; Likuiditas</div>
      <div class="fs-5 fw-bold text-success mt-1" id="kpiSisaKas">Rp 0</div>
      <small class="text-muted" style="font-size: 0.72rem;">Escrow Account Bank Mandiri</small>
    </div>
  </div>

  <div class="col-12 col-sm-6 col-xl-2-4" style="flex: 0 0 20%; max-width: 20%;">
    <div class="admin-card p-3 h-100 bg-white border-start border-4 border-info">
      <div class="text-muted small text-uppercase fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">Penjualan / Revenue</div>
      <div class="fs-5 fw-bold text-info mt-1" id="kpiPenjualan">Rp 0</div>
      <small class="text-muted" style="font-size: 0.72rem;">Kontrak Sewa &amp; Utilisasi</small>
    </div>
  </div>

  <div class="col-12 col-sm-6 col-xl-2-4" style="flex: 0 0 20%; max-width: 20%;">
    <div class="admin-card p-3 h-100 bg-white border-start border-4 border-warning">
      <div class="text-muted small text-uppercase fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">Laba Bersih &amp; Dividen</div>
      <div class="fs-5 fw-bold text-warning mt-1" id="kpiLabaBersih" style="color: #C5A059 !important;">Rp 0</div>
      <small class="text-muted" style="font-size: 0.72rem;">Dividen: <span id="kpiTotalDividen">Rp 0</span></small>
    </div>
  </div>
</div>

<!-- Main Admin Card with Filters & Module Tabs -->
<div class="admin-card">
  <!-- Top Filters Bar -->
  <div class="admin-card-header bg-white p-3 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-3">
    <!-- Left: Investor & Project Selectors -->
    <div class="d-flex flex-wrap align-items-center gap-2">
      <div style="min-width: 220px;">
        <label class="form-label small text-muted mb-0 fw-semibold">Pilih Investor Pemodal:</label>
        <select id="filterInvestor" class="form-select form-select-sm" onchange="loadFinancialData()">
          <option value="">-- Semua Investor --</option>
        </select>
      </div>

      <div style="min-width: 260px;">
        <label class="form-label small text-muted mb-0 fw-semibold">Pilih Proyek Investasi:</label>
        <select id="filterProject" class="form-select form-select-sm" onchange="loadFinancialData()">
          <option value="">-- Semua Proyek Investasi --</option>
        </select>
      </div>
    </div>

    <!-- Right: Search Box -->
    <div class="d-flex align-items-end gap-2">
      <div style="min-width: 240px;">
        <label class="form-label small text-muted mb-0 fw-semibold">Pencarian Transaksi / Billing:</label>
        <div class="input-group input-group-sm">
          <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
          <input type="text" id="filterSearch" class="form-control" placeholder="No. invoice, unit, vendor..." oninput="loadFinancialData()">
        </div>
      </div>
    </div>
  </div>

  <!-- 5 Module Tabs (Odoo Navigation Style) -->
  <div class="bg-light px-3 pt-2 border-bottom">
    <ul class="nav nav-tabs border-0" id="financialModuleTabs">
      <li class="nav-item">
        <button class="nav-link active py-2 px-3 fw-bold small" onclick="switchModuleTab('')">
          <i class="bi bi-grid-fill me-1 text-primary"></i> Semua Transaksi
        </button>
      </li>
      <li class="nav-item">
        <button class="nav-link py-2 px-3 fw-bold small" onclick="switchModuleTab('neraca')">
          <i class="bi bi-bank2 me-1 text-primary"></i> Modul 1: Neraca Keuangan
        </button>
      </li>
      <li class="nav-item">
        <button class="nav-link py-2 px-3 fw-bold small" onclick="switchModuleTab('labarugi')">
          <i class="bi bi-graph-up-arrow me-1 text-success"></i> Modul 2: Laba Rugi
        </button>
      </li>
      <li class="nav-item">
        <button class="nav-link py-2 px-3 fw-bold small" onclick="switchModuleTab('pembelian')">
          <i class="bi bi-cart-check-fill me-1 text-danger"></i> Modul 3: Pembelian (Pengadaan Unit MIU)
        </button>
      </li>
      <li class="nav-item">
        <button class="nav-link py-2 px-3 fw-bold small" onclick="switchModuleTab('penjualan')">
          <i class="bi bi-receipt-cutoff me-1 text-info"></i> Modul 4: Penjualan (Revenue)
        </button>
      </li>
      <li class="nav-item">
        <button class="nav-link py-2 px-3 fw-bold small" onclick="switchModuleTab('billing_only')">
          <i class="bi bi-envelope-paper-fill me-1 text-warning" style="color: #C5A059 !important;"></i> Billing Resmi Terkirim
        </button>
      </li>
    </ul>
  </div>

  <!-- Transactions Table -->
  <div class="table-responsive">
    <table class="admin-table table-hover align-middle mb-0">
      <thead>
        <tr>
          <th>No. Billing / Ref</th>
          <th>Investor &amp; Proyek</th>
          <th>Modul &amp; Kategori</th>
          <th>Deskripsi &amp; Transparansi Aliran Dana</th>
          <th>Vendor / Klien</th>
          <th>Nominal (IDR)</th>
          <th>Aliran</th>
          <th>Status Billing</th>
          <th class="text-end">Aksi</th>
        </tr>
      </thead>
      <tbody id="financialTableBody">
        <tr>
          <td colspan="9" class="text-center py-5 text-muted">
            <span class="spinner-border spinner-border-sm me-2"></span> Memuat data transaksi keuangan...
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</div>

<!-- ======================================================== -->
<!-- MODAL: KIRIM BILLING / CATAT TRANSAKSI KEUANGAN -->
<!-- ======================================================== -->
<div class="modal fade" id="financialModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      <div class="modal-header text-white" style="background: #142563;">
        <div>
          <h5 class="modal-title fw-bold mb-0">
            <i class="bi bi-send-plus-fill me-2 text-warning" style="color: #C5A059 !important;"></i>
            <span id="modalFormTitle">Kirim Billing &amp; Catat Transaksi Finansial</span>
          </h5>
          <small class="text-white-50">Laporan transaksi akan langsung tersinkronisasi ke Dashboard &amp; Grafik Investor secara real-time.</small>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form id="financialRecordForm" onsubmit="saveFinancialRecord(event)">
        <input type="hidden" id="formRecordId">

        <div class="modal-body p-4 bg-light">
          <!-- Step 1: Target Pemodal & Proyek -->
          <div class="card p-3 border-0 rounded-3 shadow-sm bg-white mb-3">
            <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
              <span class="badge bg-primary rounded-circle me-1" style="width: 22px; height: 22px; display: inline-flex; align-items: center; justify-content: center; background: #142563 !important;">1</span>
              Target Investor Pemodal &amp; Proyek Penempatan
            </h6>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label small fw-bold">Pilih Investor <span class="text-danger">*</span></label>
                <select id="formInvestorId" class="form-select" required>
                  <option value="">-- Pilih Investor --</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label small fw-bold">Pilih Proyek Investasi <span class="text-danger">*</span></label>
                <select id="formProjectId" class="form-select" required>
                  <option value="">-- Pilih Proyek --</option>
                </select>
              </div>
            </div>
          </div>

          <!-- Step 2: Modul & Data Transaksi Finansial -->
          <div class="card p-3 border-0 rounded-3 shadow-sm bg-white mb-3">
            <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
              <span class="badge bg-primary rounded-circle me-1" style="width: 22px; height: 22px; display: inline-flex; align-items: center; justify-content: center; background: #142563 !important;">2</span>
              Modul Finansial &amp; Nilai Transaksi
            </h6>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label small fw-bold">Modul Keuangan <span class="text-danger">*</span></label>
                <select id="formModule" class="form-select" required onchange="handleModuleChange()">
                  <option value="pembelian">Modul 3: Pembelian (Pengadaan Unit MSI/MIU / Belanja Modal)</option>
                  <option value="biaya">Modul Biaya: Biaya (Biaya Impor MIU / Workshop / Bea Cukai)</option>
                  <option value="penjualan">Modul 4: Penjualan (Revenue / Kontrak Sewa Alat)</option>
                  <option value="labarugi">Modul 2: Laba Rugi (Beban Operasional / Dividen Bagi Hasil)</option>
                  <option value="neraca">Modul 1: Neraca Keuangan (Kas Bank / Aset Tetap Unit / Modal)</option>
                </select>
              </div>

              <div class="col-md-6">
                <label class="form-label small fw-bold">Kategori Transaksi <span class="text-danger">*</span></label>
                <input type="text" id="formCategory" class="form-control" placeholder="Contoh: Pengadaan Unit Alat Berat (MIU)" required list="categorySuggestions">
                <datalist id="categorySuggestions">
                  <option value="Pengadaan Unit Alat Berat (MIU)">
                  <option value="Attachment & Logistik CBU (MIU)">
                  <option value="Suku Cadang & Komponen Fast-Moving (MIU)">
                  <option value="Kontrak Sewa Unit Mining">
                  <option value="Kontrak Sewa Unit Infrastruktur">
                  <option value="Beban Pemeliharaan & Workshop">
                  <option value="Distribusi Dividen Bagi Hasil">
                  <option value="Modal Disetor & Kas Awal">
                  <option value="Aset Tetap Unit Fisik (MIU)">
                </datalist>
              </div>

              <div class="col-md-8">
                <label class="form-label small fw-bold">Judul Transaksi / Billing <span class="text-danger">*</span></label>
                <input type="text" id="formTitle" class="form-control" placeholder="Contoh: Pembelian 2x Unit Alat Berat via MIU" required>
              </div>

              <div class="col-md-4">
                <label class="form-label small fw-bold">No. Referensi / Billing</label>
                <input type="text" id="formRecordNumber" class="form-control" placeholder="Auto-generate jika kosong">
              </div>

              <div class="col-md-6">
                <label class="form-label small fw-bold">Nominal Transaksi (Rp) <span class="text-danger">*</span></label>
                <div class="input-group">
                  <span class="input-group-text bg-light fw-bold">Rp</span>
                  <input type="text" id="formAmount" class="form-control fw-bold" placeholder="Contoh: 2.800.000.000" required oninput="formatRupiahInput(this)">
                </div>
              </div>

              <div class="col-md-3">
                <label class="form-label small fw-bold">Tanggal Transaksi <span class="text-danger">*</span></label>
                <input type="date" id="formTransactionDate" class="form-control" required>
              </div>

              <div class="col-md-3">
                <label class="form-label small fw-bold">Tanggal Jatuh Tempo</label>
                <input type="date" id="formDueDate" class="form-control">
              </div>
            </div>
          </div>

          <!-- Step 3: Pengadaan Unit & Vendor MIU -->
          <div class="card p-3 border-0 rounded-3 shadow-sm bg-white mb-3">
            <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
              <span class="badge bg-primary rounded-circle me-1" style="width: 22px; height: 22px; display: inline-flex; align-items: center; justify-content: center; background: #142563 !important;">3</span>
              Rincian Pengadaan Unit &amp; Vendor MIU / Klien
            </h6>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label small fw-bold">Vendor / Mitra Penyedia</label>
                <input type="text" id="formVendorClient" class="form-control" placeholder="Contoh: PT Montana Indo Utama (MIU)" value="PT Montana Indo Utama (MIU)">
              </div>

              <div class="col-md-6">
                <label class="form-label small fw-bold">Rincian Unit Alat Berat / Seri Fisik</label>
                <input type="text" id="formUnitDetail" class="form-control" placeholder="Contoh: 2x Unit Alat Berat Siap Operasi">
              </div>

              <div class="col-md-6">
                <label class="form-label small fw-bold">Arah Aliran Dana</label>
                <select id="formFlowType" class="form-select">
                  <option value="out">Pengeluaran Kas / Belanja Modal (Out)</option>
                  <option value="in">Pemasukan / Modal / Revenue (In)</option>
                  <option value="balance_asset">Pergeseran Kas ke Aset Tetap Unit Fisik</option>
                </select>
              </div>

              <div class="col-md-6">
                <label class="form-label small fw-bold">Status Pembayaran</label>
                <select id="formBillingStatus" class="form-select">
                  <option value="settled">Lunas / Terealisasi (Settled / Paid)</option>
                  <option value="paid">Telah Ditransfer (Paid)</option>
                  <option value="reported">Tercatat dalam Laporan (Reported)</option>
                  <option value="unpaid">Menunggu Realisasi (Unpaid / Open)</option>
                </select>
              </div>
            </div>
          </div>

          <!-- Step 4: Transparansi Aliran Dana & Opsi Pengiriman Billing -->
          <div class="card p-3 border-0 rounded-3 shadow-sm bg-white">
            <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
              <span class="badge bg-primary rounded-circle me-1" style="width: 22px; height: 22px; display: inline-flex; align-items: center; justify-content: center; background: #142563 !important;">4</span>
              Transparansi Aliran Dana &amp; Notifikasi Billing Investor
            </h6>
            
            <div class="mb-3">
              <label class="form-label small fw-bold">Penjelasan Transparan Alokasi Dana (Tampil di Dashboard Investor):</label>
              <textarea id="formDescription" class="form-control" rows="3" placeholder="Jelaskan secara transparan kepada investor ke mana dana dialokasikan. Contoh: Modal 5 Miliar berkurang sebesar Rp 2.800.000.000 untuk realisasi pembelian 2 unit alat berat siap operasi melalui vendor internal grup PT Montana Indo Utama (MIU). Sisa kas dialokasikan untuk kesiapan operasional lapangan."></textarea>
            </div>

            <div class="d-flex align-items-center justify-content-between p-3 rounded-3 border bg-light">
              <div class="d-flex align-items-center gap-2">
                <input class="form-check-input mt-0" type="checkbox" id="formIsBilling" checked style="width: 20px; height: 20px;">
                <label class="form-check-label fw-bold text-dark small" for="formIsBilling">
                  Terbitkan sebagai Faktur / Billing Resmi ke Portal Investor
                </label>
              </div>
              <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1.5 rounded-pill small fw-semibold">
                <i class="bi bi-shield-check me-1"></i> Transparansi Audit Resmi
              </span>
            </div>
          </div>
        </div>

        <div class="modal-footer bg-white border-top p-3 d-flex justify-content-between">
          <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Batal</button>
          <button type="submit" id="btnSubmitForm" class="btn btn-mgi-primary px-4 d-inline-flex align-items-center gap-2">
            <i class="bi bi-check2-circle fs-5"></i>
            <span class="fw-bold">Simpan &amp; Kirim Billing ke Investor</span>
          </button>
        </div>
      </form>

    </div>
  </div>
</div>

<!-- ======================================================== -->
<!-- MODAL: PREVIEW & CETAK E-BILLING / INVOICE RESMI -->
<!-- ======================================================== -->
<div class="modal fade" id="billingViewModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title fw-bold">
          <i class="bi bi-receipt me-2 text-warning" style="color: #C5A059 !important;"></i>
          <span>Faktur Billing &amp; Laporan Alokasi Modal Resmi</span>
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-4 bg-white" id="printableBillingArea">
        <!-- Letterhead Header -->
        <div class="d-flex justify-content-between align-items-start border-bottom pb-4 mb-4">
          <div class="d-flex align-items-center gap-3">
            <img src="../assets/img/mgi-official-logo.png" alt="Logo" style="height: 52px;" onerror="this.style.display='none'">
            <div>
              <h5 class="fw-bold text-dark mb-0" style="letter-spacing: -0.01em;">PT MONTANA GLOBAL INVESTAMA</h5>
              <div class="text-secondary small fw-semibold">Holding Pembiayaan &amp; Ekosistem Pengadaan Alat Berat Nasional</div>
              <small class="text-muted">Gedung Bursa Efek Indonesia, Tower 2, SCBD Jakarta Selatan | Telp: (021) 515-0555</small>
            </div>
          </div>
          <div class="text-end">
            <span class="badge bg-success fs-6 px-3 py-1.5 rounded-pill mb-1" id="viewBillingBadge">TEREALISASI</span>
            <div class="fw-bold text-dark" id="viewBillingNumber">BILL/MGI/2026/001</div>
            <small class="text-muted" id="viewBillingDate">Tanggal: 18 Jan 2026</small>
          </div>
        </div>

        <!-- Detail Investor & Proyek -->
        <div class="row g-3 mb-4">
          <div class="col-6">
            <div class="p-3 bg-light rounded-3">
              <span class="text-muted small text-uppercase fw-bold d-block mb-1">Ditujukan Kepada Investor:</span>
              <h6 class="fw-bold text-dark mb-1" id="viewInvestorName">-</h6>
              <div class="small text-secondary" id="viewInvestorEmail">-</div>
              <div class="small text-muted" id="viewInvestorType">Akun Investor Terverifikasi</div>
            </div>
          </div>
          <div class="col-6">
            <div class="p-3 bg-light rounded-3">
              <span class="text-muted small text-uppercase fw-bold d-block mb-1">Proyek Penempatan Modal:</span>
              <h6 class="fw-bold text-dark mb-1" id="viewProjectTitle">-</h6>
              <div class="small text-secondary" id="viewProjectCategory">Kategori Alat Berat</div>
              <div class="small text-muted" id="viewVendorClient">Vendor: PT Montana Indo Utama (MIU)</div>
            </div>
          </div>
        </div>

        <!-- Tabel Rincian Billing -->
        <div class="table-responsive mb-4">
          <table class="table table-bordered align-middle">
            <thead class="table-light">
              <tr class="small text-uppercase fw-bold text-secondary">
                <th>Deskripsi Alokasi Finansial / Pengadaan Unit</th>
                <th>Spesifikasi Unit Fisik</th>
                <th>Modul</th>
                <th class="text-end">Total Nominal</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>
                  <strong class="text-dark d-block" id="viewItemTitle">-</strong>
                  <small class="text-muted" id="viewItemDescription">-</small>
                </td>
                <td id="viewUnitDetail">-</td>
                <td><span class="badge bg-primary text-white" id="viewModuleBadge">PEMBELIAN</span></td>
                <td class="text-end fw-bold text-dark fs-6" id="viewItemAmount">Rp 0</td>
              </tr>
            </tbody>
            <tfoot>
              <tr class="table-light">
                <td colspan="3" class="text-end fw-bold">TOTAL NILAI TRANSAKSI / BILLING:</td>
                <td class="text-end fw-bold text-primary fs-5" id="viewTotalAmount">Rp 0</td>
              </tr>
            </tfoot>
          </table>
        </div>

        <!-- Catatan Transparansi & QR Verification -->
        <div class="row g-3 align-items-center p-3 rounded-3 border bg-light mb-4">
          <div class="col-8">
            <h6 class="fw-bold text-dark mb-1"><i class="bi bi-shield-check text-success me-1"></i> Deklarasi Transparansi Aliran Modal</h6>
            <p class="small text-secondary mb-0">Dokumen faktur billing ini merupakan laporan resmi realisasi penggunaan modal investor yang mengurangi saldo kas dan menambah aktiva berwujud armada fisik alat berat yang dikelola oleh workshop grup PT Montana Indo Utama (MIU).</p>
          </div>
          <div class="col-4 text-end">
            <div class="d-inline-block text-center border p-2 bg-white rounded-2">
              <i class="bi bi-qr-code fs-1 text-dark"></i>
              <div style="font-size: 0.65rem;" class="text-muted">VALIDATED DIGITAL SIGN</div>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer bg-light border-top p-3 d-flex justify-content-between">
        <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Tutup</button>
        <button type="button" class="btn btn-primary px-4 d-inline-flex align-items-center gap-2" onclick="printBillingDocument()">
          <i class="bi bi-printer-fill"></i>
          <span>Cetak / Download PDF Billing</span>
        </button>
      </div>

    </div>
  </div>
</div>

<!-- ======================================================== -->
<!-- MODAL: INPUT INVENTORY / STOK ALAT BERAT OPERATOR       -->
<!-- ======================================================== -->
<div class="modal fade" id="inventoryModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      <div class="modal-header text-white" style="background: #142563;">
        <div>
          <h5 class="modal-title fw-bold mb-0">
            <i class="bi bi-truck-front-fill me-2 text-warning"></i>
            Input Inventory / Stok Fisik Unit Alat Berat
          </h5>
          <small class="text-white-50">Data inventaris akan tersimpan ke database & langsung memperbarui grafik investor secara real-time.</small>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form id="inventoryRecordForm" onsubmit="saveInventoryRecord(event)">
        <div class="modal-body p-4 bg-light">
          <div class="card p-3 border-0 rounded-3 shadow-sm bg-white mb-3">
            <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">1. Target Proyek &amp; Identitas Unit</h6>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label small fw-bold">Pilih Proyek Investasi <span class="text-danger">*</span></label>
                <select id="invProjectId" class="form-select" required>
                  <option value="">-- Pilih Proyek --</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label small fw-bold">Kode Unit (Item Code) <span class="text-danger">*</span></label>
                <input type="text" id="invItemCode" class="form-control" placeholder="Contoh: EXC-KM-138-03" required>
              </div>
              <div class="col-md-8">
                <label class="form-label small fw-bold">Nama / Model Unit <span class="text-danger">*</span></label>
                <input type="text" id="invItemName" class="form-control" placeholder="Contoh: Unit Alat Berat Siap Operasi" required>
              </div>
              <div class="col-md-4">
                <label class="form-label small fw-bold">Kategori</label>
                <input type="text" id="invCategory" class="form-control" value="Excavator / Alat Berat">
              </div>
              <div class="col-md-6">
                <label class="form-label small fw-bold">Serial Number / VIN</label>
                <input type="text" id="invSerial" class="form-control" placeholder="Contoh: KMTC882912-JP">
              </div>
              <div class="col-md-3">
                <label class="form-label small fw-bold">Jumlah Unit</label>
                <input type="number" id="invQuantity" class="form-control" value="1" min="1" required>
              </div>
              <div class="col-md-3">
                <label class="form-label small fw-bold">Jam Kerja SMH</label>
                <input type="number" id="invSmh" class="form-control" value="1150" placeholder="Jam SMH">
              </div>
            </div>
          </div>

          <div class="card p-3 border-0 rounded-3 shadow-sm bg-white">
            <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">2. Nilai Perolehan &amp; Status Operasional</h6>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label small fw-bold">Nilai Perolehan / Satuan (Rp) <span class="text-danger">*</span></label>
                <div class="input-group">
                  <span class="input-group-text bg-light fw-bold">Rp</span>
                  <input type="text" id="invUnitCost" class="form-control font-monospace fw-bold" placeholder="1.400.000.000" onkeyup="formatRupiahInput(this)" required>
                </div>
              </div>
              <div class="col-md-6">
                <label class="form-label small fw-bold">Status Kondisi Fisik</label>
                <select id="invCondition" class="form-select">
                  <option value="Grade A (Prima)">Grade A (Prima &amp; Siap Kerja)</option>
                  <option value="Grade B (Baik)">Grade B (Kondisi Baik)</option>
                  <option value="Maintenance">Dalam Perawatan Rutin</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label small fw-bold">Status Operasional Proyek</label>
                <select id="invOperationalStatus" class="form-select">
                  <option value="Aktif Beroperasi">Aktif Beroperasi di Proyek</option>
                  <option value="Standby Pool">Standby di Pool Workshop</option>
                  <option value="Dalam Mobilisasi">Dalam Mobilisasi Logistik</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label small fw-bold">Lokasi Pool / Lapangan</label>
                <input type="text" id="invLocation" class="form-control" value="Pool Narogong & Cikarang Industrial Corridor">
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer bg-white border-top">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary px-4 fw-bold" id="btnSubmitInventory">
            <i class="bi bi-check2-circle me-1"></i> Simpan Stok &amp; Terbitkan Sertifikat Unit
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ======================================================== -->
<!-- MODAL: TERBITKAN DOKUMEN PDF RESMI OPERATOR              -->
<!-- ======================================================== -->
<div class="modal fade" id="publishDocModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      <div class="modal-header text-white" style="background: #142563;">
        <div>
          <h5 class="modal-title fw-bold mb-0">
            <i class="bi bi-file-earmark-pdf-fill me-2 text-danger"></i>
            Terbitkan Dokumen PDF Resmi ke Investor
          </h5>
          <small class="text-white-50">Dokumen akan langsung muncul di Dashboard Operasional Investor.</small>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form id="publishDocForm" onsubmit="savePublishDocument(event)">
        <div class="modal-body p-4 bg-light space-y-3">
          <div class="card p-3 border-0 rounded-3 shadow-sm bg-white mb-3">
            <div class="mb-3">
              <label class="form-label small fw-bold">Pilih Proyek Investasi <span class="text-danger">*</span></label>
              <select id="docProjectId" class="form-select" required>
                <option value="">-- Pilih Proyek --</option>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label small fw-bold">Pilih Investor Pemodal <span class="text-danger">*</span></label>
              <select id="docInvestorId" class="form-select" required>
                <option value="">-- Pilih Investor --</option>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label small fw-bold">Judul Dokumen PDF <span class="text-danger">*</span></label>
              <input type="text" id="docTitleInput" class="form-control" placeholder="Contoh: Buku Laporan Operasional Kuartal III 2026" required>
            </div>
            <div>
              <label class="form-label small fw-bold">Tipe Dokumen</label>
              <select id="docTypeInput" class="form-select">
                <option value="laporan">Laporan Operasional Berkala</option>
                <option value="billing">Faktur Billing / Alokasi Dana</option>
                <option value="pembelian">Faktur Pembelian Unit CBU</option>
                <option value="inventory">Sertifikat / Berita Acara Unit</option>
              </select>
            </div>
          </div>
        </div>

        <div class="modal-footer bg-white border-top">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-danger px-4 fw-bold" id="btnSubmitDoc">
            <i class="bi bi-send-fill me-1"></i> Terbitkan PDF ke Investor
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  let financialRecordsData = [];
  let currentModuleFilter = '';
  let createModalInstance = null;
  let viewModalInstance = null;
  let inventoryModalInstance = null;
  let publishDocModalInstance = null;
  let activeInvestors = [];
  let activeProjects = [];

  document.addEventListener('DOMContentLoaded', () => {
    createModalInstance = new bootstrap.Modal(document.getElementById('financialModal'));
    viewModalInstance = new bootstrap.Modal(document.getElementById('billingViewModal'));
    inventoryModalInstance = new bootstrap.Modal(document.getElementById('inventoryModal'));
    publishDocModalInstance = new bootstrap.Modal(document.getElementById('publishDocModal'));

    // Check URL parameters for pre-selected investor or project
    const urlParams = new URLSearchParams(window.location.search);
    const preInv = urlParams.get('investor_id');
    const preProj = urlParams.get('project_id');

    loadFinancialData(preInv, preProj);
  });

  function formatRupiahInput(input) {
    let val = input.value.replace(/[^0-9]/g, '');
    if (!val) {
      input.value = '';
      return;
    }
    input.value = new Intl.NumberFormat('id-ID').format(val);
  }

  function formatIDR(amount) {
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(amount));
  }

  function switchModuleTab(moduleName) {
    currentModuleFilter = moduleName;
    document.querySelectorAll('#financialModuleTabs .nav-link').forEach(btn => btn.classList.remove('active'));
    event.currentTarget.classList.add('active');
    loadFinancialData();
  }

  async function loadFinancialData(preInv = null, preProj = null) {
    const invSelect = document.getElementById('filterInvestor');
    const projSelect = document.getElementById('filterProject');
    const search = document.getElementById('filterSearch').value.trim();
    const tb = document.getElementById('financialTableBody');

    const invVal = preInv || invSelect.value;
    const projVal = preProj || projSelect.value;

    const params = new URLSearchParams();
    if (invVal) params.set('investor_id', invVal);
    if (projVal) params.set('project_id', projVal);
    if (search) params.set('search', search);

    if (currentModuleFilter === 'billing_only') {
      params.set('billing_only', '1');
    } else if (currentModuleFilter) {
      params.set('module', currentModuleFilter);
    }

    try {
      const res = await fetch(`../api/admin/financial.php?${params.toString()}`);
      const json = await res.json();

      if (!json.success) {
        if (json.status === 401) window.location.href = 'login.php';
        AdminApp.showToast(json.message || 'Gagal memuat data keuangan.', 'danger');
        return;
      }

      const data = json.data;
      financialRecordsData = data.records || [];
      activeInvestors = data.investors || [];
      activeProjects = data.projects || [];

      // Populate filter dropdowns if not yet populated
      if (invSelect.options.length <= 1) {
        activeInvestors.forEach(inv => {
          const opt = document.createElement('option');
          opt.value = inv.id;
          opt.textContent = `${inv.full_name || inv.business_name} (${inv.email})`;
          if (preInv && Number(preInv) === Number(inv.id)) opt.selected = true;
          invSelect.appendChild(opt);
        });
      }

      if (projSelect.options.length <= 1) {
        activeProjects.forEach(p => {
          const opt = document.createElement('option');
          opt.value = p.id;
          opt.textContent = `${p.title} (${p.category})`;
          if (preProj && preProj === p.id) opt.selected = true;
          projSelect.appendChild(opt);
        });
      }

      // Update KPI Cards
      const sum = data.summary;
      document.getElementById('kpiTotalCapital').textContent = formatIDR(sum.total_capital || 0);
      document.getElementById('kpiPurchasesMIU').textContent = formatIDR(sum.total_purchases_miu || 0);
      document.getElementById('kpiSisaKas').textContent = formatIDR(sum.sisa_kas || 0);
      document.getElementById('kpiPenjualan').textContent = formatIDR(sum.total_penjualan || 0);
      document.getElementById('kpiLabaBersih').textContent = formatIDR(sum.laba_bersih || 0);
      document.getElementById('kpiTotalDividen').textContent = formatIDR(sum.total_dividen || 0);

      // Render Table Rows
      if (financialRecordsData.length === 0) {
        tb.innerHTML = `<tr><td colspan="9" class="text-center py-5 text-muted">Belum ada catatan transaksi atau billing yang cocok dengan filter.</td></tr>`;
        return;
      }

      tb.innerHTML = financialRecordsData.map(r => {
        let moduleBadge = `<span class="badge bg-secondary">Neraca</span>`;
        if (r.module === 'neraca') moduleBadge = `<span class="badge bg-primary text-white" style="background: #142563 !important;"><i class="bi bi-bank2 me-1"></i>Neraca</span>`;
        if (r.module === 'labarugi') moduleBadge = `<span class="badge bg-success text-white"><i class="bi bi-graph-up-arrow me-1"></i>Laba Rugi</span>`;
        if (r.module === 'pembelian') moduleBadge = `<span class="badge bg-danger text-white"><i class="bi bi-cart-check-fill me-1"></i>Pembelian (MIU)</span>`;
        if (r.module === 'penjualan') moduleBadge = `<span class="badge bg-info text-dark"><i class="bi bi-receipt-cutoff me-1"></i>Penjualan</span>`;

        let flowBadge = `<span class="badge bg-danger-subtle text-danger border border-danger-subtle">OUT</span>`;
        if (r.flow_type === 'in') flowBadge = `<span class="badge bg-success-subtle text-success border border-success-subtle">IN</span>`;
        if (r.flow_type === 'balance_asset') flowBadge = `<span class="badge bg-primary-subtle text-primary border border-primary-subtle">ASET</span>`;

        let billBadge = `<span class="badge bg-light text-muted border">Tercatat</span>`;
        if (r.is_billing) {
          if (r.billing_status === 'settled' || r.billing_status === 'paid') {
            billBadge = `<span class="badge bg-success text-white"><i class="bi bi-check-circle-fill me-1"></i>Billing Lunas</span>`;
          } else {
            billBadge = `<span class="badge bg-warning text-dark"><i class="bi bi-send-fill me-1"></i>Billing Terkirim</span>`;
          }
        }

        const dateFormatted = new Date(r.transaction_date).toLocaleDateString('id-ID', {
          day: 'numeric', month: 'short', year: 'numeric'
        });

        return `
          <tr>
            <td>
              <div class="fw-bold text-dark font-monospace">${r.record_number}</div>
              <small class="text-muted">${dateFormatted}</small>
            </td>
            <td>
              <div class="fw-bold text-dark small">${r.investor_name || r.investor_company || r.investor_email}</div>
              <small class="text-muted d-block text-truncate" style="max-width: 180px;">${r.project_title}</small>
            </td>
            <td>
              <div>${moduleBadge}</div>
              <small class="text-muted" style="font-size: 0.75rem;">${r.category}</small>
            </td>
            <td>
              <div class="fw-semibold text-dark small">${r.title}</div>
              ${r.unit_detail ? `<small class="text-primary d-block"><i class="bi bi-truck me-1"></i>${r.unit_detail}</small>` : ''}
              ${r.description ? `<small class="text-muted d-block text-truncate" style="max-width: 260px;" title="${r.description}">${r.description}</small>` : ''}
            </td>
            <td>
              <div class="small fw-semibold text-dark">${r.vendor_client || '-'}</div>
            </td>
            <td>
              <div class="fw-bold fs-6 text-dark">${formatIDR(r.amount)}</div>
            </td>
            <td>${flowBadge}</td>
            <td>${billBadge}</td>
            <td class="text-end">
              <div class="btn-group btn-group-sm">
                <button type="button" class="btn btn-outline-primary" onclick="viewBillingDetail(${r.id})" title="Lihat E-Billing / Faktur">
                  <i class="bi bi-file-earmark-text"></i>
                </button>
                <button type="button" class="btn btn-outline-secondary" onclick="editRecord(${r.id})" title="Edit">
                  <i class="bi bi-pencil"></i>
                </button>
                <button type="button" class="btn btn-outline-danger" onclick="deleteRecord(${r.id})" title="Hapus">
                  <i class="bi bi-trash"></i>
                </button>
              </div>
            </td>
          </tr>
        `;
      }).join('');

    } catch (e) {
      console.error(e);
      AdminApp.showToast('Gagal terhubung ke server.', 'danger');
    }
  }

  function openCreateModal(defaultInvestorId = null, defaultProjectId = null) {
    document.getElementById('financialRecordForm').reset();
    document.getElementById('formRecordId').value = '';
    document.getElementById('modalFormTitle').textContent = 'Kirim Billing & Catat Transaksi Finansial';
    document.getElementById('formTransactionDate').value = new Date().toISOString().split('T')[0];

    // Populate investor select
    const invSelect = document.getElementById('formInvestorId');
    invSelect.innerHTML = '<option value="">-- Pilih Investor --</option>' + activeInvestors.map(i => {
      return `<option value="${i.id}">${i.full_name || i.business_name} (${i.email})</option>`;
    }).join('');

    if (defaultInvestorId) invSelect.value = defaultInvestorId;
    else if (document.getElementById('filterInvestor').value) invSelect.value = document.getElementById('filterInvestor').value;

    // Populate project select
    const projSelect = document.getElementById('formProjectId');
    projSelect.innerHTML = '<option value="">-- Pilih Proyek --</option>' + activeProjects.map(p => {
      return `<option value="${p.id}">${p.title} (${p.category})</option>`;
    }).join('');

    if (defaultProjectId) projSelect.value = defaultProjectId;
    else if (document.getElementById('filterProject').value) projSelect.value = document.getElementById('filterProject').value;

    handleModuleChange();
    createModalInstance.show();
  }

  function handleModuleChange() {
    const mod = document.getElementById('formModule').value;
    const flowSelect = document.getElementById('formFlowType');
    const vendorInput = document.getElementById('formVendorClient');
    const descArea = document.getElementById('formDescription');

    if (mod === 'pembelian') {
      flowSelect.value = 'out';
      vendorInput.value = 'PT Montana Indo Utama (MIU)';
      if (!descArea.value) {
        descArea.value = 'Alokasi penyerapan modal: pengurangan kas untuk pembelian unit alat berat via workshop PT Montana Indo Utama (MIU).';
      }
    } else if (mod === 'penjualan') {
      flowSelect.value = 'in';
      vendorInput.value = 'PT Surya Semesta Mandiri';
    } else if (mod === 'labarugi') {
      flowSelect.value = 'out';
    } else if (mod === 'neraca') {
      flowSelect.value = 'balance_asset';
    }
  }

  async function saveFinancialRecord(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSubmitForm');
    btn.disabled = true;
    btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span> Menyimpan...`;

    const id = document.getElementById('formRecordId').value;
    const rawAmt = document.getElementById('formAmount').value.replace(/[^0-9]/g, '');

    const payload = {
      investor_id: document.getElementById('formInvestorId').value,
      project_id: document.getElementById('formProjectId').value,
      module: document.getElementById('formModule').value,
      category: document.getElementById('formCategory').value,
      title: document.getElementById('formTitle').value,
      record_number: document.getElementById('formRecordNumber').value,
      amount: rawAmt,
      transaction_date: document.getElementById('formTransactionDate').value,
      due_date: document.getElementById('formDueDate').value,
      vendor_client: document.getElementById('formVendorClient').value,
      unit_detail: document.getElementById('formUnitDetail').value,
      flow_type: document.getElementById('formFlowType').value,
      billing_status: document.getElementById('formBillingStatus').value,
      description: document.getElementById('formDescription').value,
      is_billing: document.getElementById('formIsBilling').checked ? 1 : 0
    };

    try {
      const url = '../api/admin/financial.php' + (id ? `?id=${id}` : '');
      const method = id ? 'PUT' : 'POST';

      const res = await fetch(url, {
        method: method,
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      const json = await res.json();

      if (!json.success) {
        AdminApp.showToast(json.message || 'Gagal menyimpan transaksi.', 'danger');
        btn.disabled = false;
        btn.innerHTML = `<i class="bi bi-check2-circle fs-5"></i> <span class="fw-bold">Simpan &amp; Kirim Billing ke Investor</span>`;
        return;
      }

      AdminApp.showToast(json.message, 'success');
      createModalInstance.hide();
      loadFinancialData();

    } catch (err) {
      console.error(err);
      AdminApp.showToast('Terjadi kesalahan jaringan.', 'danger');
    } finally {
      btn.disabled = false;
      btn.innerHTML = `<i class="bi bi-check2-circle fs-5"></i> <span class="fw-bold">Simpan &amp; Kirim Billing ke Investor</span>`;
    }
  }

  function viewBillingDetail(recordId) {
    const r = financialRecordsData.find(item => Number(item.id) === Number(recordId));
    if (!r) return;

    document.getElementById('viewBillingNumber').textContent = r.record_number;
    document.getElementById('viewBillingDate').textContent = 'Tanggal: ' + new Date(r.transaction_date).toLocaleDateString('id-ID', {
      day: 'numeric', month: 'long', year: 'numeric'
    });

    const isPaid = (r.billing_status === 'settled' || r.billing_status === 'paid');
    const badge = document.getElementById('viewBillingBadge');
    badge.textContent = isPaid ? 'LUNAS / TEREALISASI' : 'TERKIRIM (OPEN)';
    badge.className = `badge ${isPaid ? 'bg-success' : 'bg-warning text-dark'} fs-6 px-3 py-1.5 rounded-pill mb-1`;

    document.getElementById('viewInvestorName').textContent = r.investor_name || r.investor_company || 'Pemodal MGI';
    document.getElementById('viewInvestorEmail').textContent = r.investor_email;
    document.getElementById('viewInvestorType').textContent = (r.account_type === 'perusahaan' ? 'Investor Korporasi' : 'Investor Perorangan') + ' • ID #' + r.investor_id;

    document.getElementById('viewProjectTitle').textContent = r.project_title;
    document.getElementById('viewProjectCategory').textContent = r.project_category;
    document.getElementById('viewVendorClient').textContent = 'Vendor/Klien: ' + (r.vendor_client || 'PT Montana Indo Utama (MIU)');

    document.getElementById('viewItemTitle').textContent = r.title;
    document.getElementById('viewItemDescription').textContent = r.description || 'Pengurangan modal untuk pengadaan unit alat berat melalui MIU.';
    document.getElementById('viewUnitDetail').textContent = r.unit_detail || 'Unit Fisik Alat Berat Siap Operasi';
    document.getElementById('viewModuleBadge').textContent = r.module.toUpperCase();
    document.getElementById('viewItemAmount').textContent = formatIDR(r.amount);
    document.getElementById('viewTotalAmount').textContent = formatIDR(r.amount);

    viewModalInstance.show();
  }

  function printBillingDocument() {
    window.print();
  }

  function editRecord(recordId) {
    const r = financialRecordsData.find(item => Number(item.id) === Number(recordId));
    if (!r) return;

    openCreateModal(r.investor_id, r.project_id);
    document.getElementById('formRecordId').value = r.id;
    document.getElementById('modalFormTitle').textContent = 'Edit Catatan Finansial & Billing';

    document.getElementById('formModule').value = r.module;
    document.getElementById('formCategory').value = r.category;
    document.getElementById('formTitle').value = r.title;
    document.getElementById('formRecordNumber').value = r.record_number;
    document.getElementById('formAmount').value = new Intl.NumberFormat('id-ID').format(Math.round(r.amount));
    document.getElementById('formTransactionDate').value = r.transaction_date;
    document.getElementById('formDueDate').value = r.due_date || '';
    document.getElementById('formVendorClient').value = r.vendor_client || '';
    document.getElementById('formUnitDetail').value = r.unit_detail || '';
    document.getElementById('formFlowType').value = r.flow_type || 'out';
    document.getElementById('formBillingStatus').value = r.billing_status || 'settled';
    document.getElementById('formDescription').value = r.description || '';
    document.getElementById('formIsBilling').checked = Boolean(Number(r.is_billing));
  }

  async function deleteRecord(recordId) {
    if (!confirm('Apakah Anda yakin ingin menghapus catatan transaksi ini? Tindakan ini akan memutakhirkan grafik investor secara real-time.')) {
      return;
    }

    try {
      const res = await fetch(`../api/admin/financial.php?id=${recordId}`, {
        method: 'DELETE'
      });
      const json = await res.json();
      if (json.success) {
        AdminApp.showToast(json.message, 'success');
        loadFinancialData();
      } else {
        AdminApp.showToast(json.message || 'Gagal menghapus catatan.', 'danger');
      }
    } catch (e) {
      console.error(e);
      AdminApp.showToast('Terjadi kesalahan jaringan.', 'danger');
    }
  }

  // Operator Action Handlers (Stage 3 & 4)
  function openOperatorInput(type) {
    openCreateModal();
    const moduleSelect = document.getElementById('formModule');
    const catInput = document.getElementById('formCategory');
    const vendorInput = document.getElementById('formVendorClient');
    const titleInput = document.getElementById('formTitle');
    const isBilling = document.getElementById('formIsBilling');

    if (type === 'biaya') {
      moduleSelect.value = 'biaya';
      catInput.value = 'Biaya Impor MIU & Customs';
      vendorInput.value = 'PT Montana Indo Utama (MIU)';
      titleInput.value = 'Biaya Impor & Customs Clearance Unit via MIU';
      isBilling.checked = false;
    } else if (type === 'pembelian') {
      moduleSelect.value = 'pembelian';
      catInput.value = 'Pengadaan Unit Alat Berat (MSI)';
      vendorInput.value = 'PT Montana Sentra Industri (MSI)';
      titleInput.value = 'Pembelian Unit Alat Berat via MSI';
      isBilling.checked = true;
    } else if (type === 'penjualan') {
      moduleSelect.value = 'penjualan';
      catInput.value = 'Kontrak Sewa Infrastruktur';
      vendorInput.value = 'PT Wijaya Kusuma Kontraktor';
      titleInput.value = 'Invoice Kontrak Sewa Unit Alat Berat';
      isBilling.checked = false;
    } else if (type === 'billing') {
      moduleSelect.value = 'pembelian';
      catInput.value = 'Alokasi Modal Proyek (MSI & MIU)';
      vendorInput.value = 'PT Montana Sentra Industri (MSI)';
      titleInput.value = 'Faktur Alokasi Dana Pembelian Unit via MSI & MIU';
      isBilling.checked = true;
    }
    handleModuleChange();
  }

  function openInventoryModal() {
    populateInvDropdowns();
    inventoryModalInstance.show();
  }

  function populateInvDropdowns() {
    const projSelect = document.getElementById('invProjectId');
    if (projSelect.options.length <= 1) {
      activeProjects.forEach(p => {
        const opt = document.createElement('option');
        opt.value = p.id;
        opt.textContent = `${p.title} (${p.category})`;
        projSelect.appendChild(opt);
      });
    }
  }

  async function saveInventoryRecord(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSubmitInventory');
    btn.disabled = true;
    btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span> Menyimpan...`;

    const pId = document.getElementById('invProjectId').value;
    const rawCost = document.getElementById('invUnitCost').value.replace(/[^0-9]/g, '');

    const payload = {
      project_id: pId,
      item_code: document.getElementById('invItemCode').value,
      item_name: document.getElementById('invItemName').value,
      category: document.getElementById('invCategory').value,
      serial_number: document.getElementById('invSerial').value,
      quantity: Number(document.getElementById('invQuantity').value) || 1,
      unit_cost: rawCost,
      smh_hours: Number(document.getElementById('invSmh').value) || 0,
      condition_status: document.getElementById('invCondition').value,
      operational_status: document.getElementById('invOperationalStatus').value,
      location: document.getElementById('invLocation').value
    };

    try {
      const res = await fetch(`../api/projects/${pId}/inventory`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      const json = await res.json();
      if (json.success) {
        AdminApp.showToast(json.message || 'Stok unit berhasil dicatat dan disinkronkan ke investor.', 'success');
        inventoryModalInstance.hide();
        document.getElementById('inventoryRecordForm').reset();
      } else {
        AdminApp.showToast(json.message || 'Gagal menyimpan stok unit.', 'danger');
      }
    } catch(err) {
      console.error(err);
      AdminApp.showToast('Terjadi kesalahan jaringan.', 'danger');
    } finally {
      btn.disabled = false;
      btn.innerHTML = `<i class="bi bi-check2-circle me-1"></i> Simpan Stok &amp; Terbitkan Sertifikat Unit`;
    }
  }

  function openPublishDocModal() {
    const projSelect = document.getElementById('docProjectId');
    const invSelect = document.getElementById('docInvestorId');
    if (projSelect.options.length <= 1) {
      activeProjects.forEach(p => {
        const opt = document.createElement('option');
        opt.value = p.id;
        opt.textContent = `${p.title} (${p.category})`;
        projSelect.appendChild(opt);
      });
    }
    if (invSelect.options.length <= 1) {
      activeInvestors.forEach(inv => {
        const opt = document.createElement('option');
        opt.value = inv.id;
        opt.textContent = `${inv.full_name || inv.business_name} (${inv.email})`;
        invSelect.appendChild(opt);
      });
    }
    publishDocModalInstance.show();
  }

  async function savePublishDocument(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSubmitDoc');
    btn.disabled = true;
    btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span> Menerbitkan...`;

    const pId = document.getElementById('docProjectId').value;
    const invId = document.getElementById('docInvestorId').value;
    const docTitle = document.getElementById('docTitleInput').value;
    const docType = document.getElementById('docTypeInput').value;

    const payload = {
      project_id: pId,
      investor_id: invId,
      doc_title: docTitle,
      doc_type: docType
    };

    try {
      const res = await fetch(`../api/projects/${pId}/documents`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      const json = await res.json();
      if (json.success) {
        AdminApp.showToast(json.message || 'Dokumen PDF resmi berhasil diterbitkan ke investor.', 'success');
        publishDocModalInstance.hide();
        document.getElementById('publishDocForm').reset();
      } else {
        AdminApp.showToast(json.message || 'Gagal menerbitkan dokumen.', 'danger');
      }
    } catch(err) {
      console.error(err);
      AdminApp.showToast('Terjadi kesalahan jaringan.', 'danger');
    } finally {
      btn.disabled = false;
      btn.innerHTML = `<i class="bi bi-send-fill me-1"></i> Terbitkan PDF ke Investor`;
    }
  }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
