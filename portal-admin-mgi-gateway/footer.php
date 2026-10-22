<?php
$pageTitle = 'Manajemen Footer Website';
require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
  <div>
    <h4 class="fw-bold text-dark mb-1">Manajemen Footer Website</h4>
    <p class="text-muted small mb-0">Atur nomor telepon, kontak, teks ringkasan, tautan navigasi, dan elemen apa saja yang ditampilkan di footer seluruh halaman website secara dinamis.</p>
  </div>
  <div class="d-flex gap-2">
    <a href="../index" target="_blank" class="btn btn-outline-secondary d-flex align-items-center gap-1.5 shadow-sm">
      <i class="bi bi-box-arrow-up-right"></i>
      <span>Lihat Website</span>
    </a>
    <button type="button" class="btn btn-mgi-primary shadow-sm d-flex align-items-center gap-2" id="btnSaveTop" onclick="document.getElementById('footerConfigForm').requestSubmit()">
      <i class="bi bi-check2-circle"></i>
      <span>Simpan Pengaturan Footer</span>
    </button>
  </div>
</div>

<!-- Live Interactive Preview Box -->
<div class="admin-card mb-4 bg-white border">
  <div class="admin-card-header bg-light border-bottom d-flex align-items-center justify-content-between py-2.5 px-3">
    <div class="d-flex align-items-center gap-2">
      <i class="bi bi-eye-fill text-primary" style="color: #142563 !important;"></i>
      <span class="fw-bold text-dark small">Pratinjau Langsung Footer (Live Preview)</span>
    </div>
    <span class="badge bg-success small">Tersinkronisasi Otomatis</span>
  </div>
  <div class="p-4 rounded-bottom" id="footerLivePreview" style="background-color: #0B1B3D; color: #FFFFFF;">
    <div class="row g-4">
      <!-- Col 1: Profil & Branding -->
      <div class="col-lg-4 col-md-6">
        <div class="bg-white p-2 rounded-2 d-inline-block shadow-sm mb-3">
          <img src="../assets/img/mgi-official-logo.png" id="prevLogo" alt="MGI Logo" height="38" style="max-height: 44px;">
        </div>
        <p class="small mb-3 lh-base" id="prevTagline" style="color: rgba(255, 255, 255, 0.7); font-size: 0.82rem;">
          Memuat ringkasan profil...
        </p>
        <div id="prevBadgeWrapper" class="d-inline-flex align-items-center gap-2 px-2.5 py-1 rounded-1 small fw-bold" style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.15); color: #C5A059; font-size: 0.76rem;">
          <i class="bi bi-shield-check text-white"></i>
          <span id="prevBadgeText">Standar Tata Kelola Perusahaan &amp; Kepatuhan Regulasi</span>
        </div>
        <div class="d-flex gap-2 mt-3" id="prevSocialWrapper">
          <span id="prevSocialInsta" class="badge rounded-circle p-2 bg-secondary bg-opacity-25 text-white" title="Instagram"><i class="bi bi-instagram"></i></span>
          <span id="prevSocialLinkedin" class="badge rounded-circle p-2 bg-secondary bg-opacity-25 text-white" title="LinkedIn"><i class="bi bi-linkedin"></i></span>
          <span id="prevSocialYoutube" class="badge rounded-circle p-2 bg-secondary bg-opacity-25 text-white" title="YouTube"><i class="bi bi-youtube"></i></span>
        </div>
      </div>

      <!-- Col 2: Navigasi Utama -->
      <div class="col-lg-2 col-md-6">
        <h6 class="fw-bold text-white text-uppercase mb-3 small tracking-wide pb-1 d-inline-block" id="prevCol2Title" style="border-bottom: 2px solid #C5A059; font-size: 0.8rem;">Navigasi Utama</h6>
        <ul class="list-unstyled mb-0 small" id="prevCol2List" style="line-height: 1.8; color: rgba(255,255,255,0.65);">
        </ul>
      </div>

      <!-- Col 3: Struktur & Sinergi -->
      <div class="col-lg-3 col-md-6">
        <h6 class="fw-bold text-white text-uppercase mb-3 small tracking-wide pb-1 d-inline-block" id="prevCol3Title" style="border-bottom: 2px solid #C5A059; font-size: 0.8rem;">Struktur &amp; Sinergi</h6>
        <ul class="list-unstyled mb-0 small" id="prevCol3List" style="line-height: 1.8; color: rgba(255,255,255,0.65);">
        </ul>
      </div>

      <!-- Col 4: Kontak & Hubungi Kami -->
      <div class="col-lg-3 col-md-6">
        <h6 class="fw-bold text-white text-uppercase mb-3 small tracking-wide pb-1 d-inline-block" style="border-bottom: 2px solid #C5A059; font-size: 0.8rem;">Hubungi Kami</h6>
        
        <div class="small mb-2" id="prevAddressWrap" style="color: rgba(255,255,255,0.7); font-size: 0.8rem;">
          <i class="bi bi-geo-alt me-1" style="color: #C5A059;"></i>
          <span id="prevAddress">Roseville Soho &amp; Suite...</span>
        </div>
        
        <div class="small mb-2" id="prevEmailWrap" style="color: rgba(255,255,255,0.7); font-size: 0.8rem;">
          <i class="bi bi-envelope me-1" style="color: #C5A059;"></i>
          <span id="prevEmail">contact@montanainvestama.com</span>
        </div>

        <div class="small mb-2" id="prevPhoneWrap" style="color: rgba(255,255,255,0.7); font-size: 0.8rem;">
          <i class="bi bi-telephone me-1" style="color: #C5A059;"></i>
          <span id="prevPhone" class="fw-bold text-white">+62 21 5315 8899</span>
        </div>

        <div class="small mb-2" id="prevWhatsappWrap" style="color: rgba(255,255,255,0.7); font-size: 0.8rem;">
          <i class="bi bi-whatsapp me-1 text-success"></i>
          <span id="prevWhatsapp">6281198765432</span>
        </div>

        <div class="small" id="prevHoursWrap" style="color: rgba(255,255,255,0.7); font-size: 0.8rem;">
          <i class="bi bi-clock me-1" style="color: #C5A059;"></i>
          <span id="prevHours">Senin – Jumat (08.00 – 17.00 WIB)</span>
        </div>
      </div>
    </div>

    <!-- Newsletter Preview Row -->
    <div class="p-3 my-3 rounded-2" id="prevNewsletterWrap" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.12);">
      <div class="row align-items-center justify-content-between">
        <div class="col-md-7 mb-2 mb-md-0">
          <div class="fw-bold small text-white d-flex align-items-center gap-1.5" id="prevNewsTitle">
            <i class="bi bi-envelope-paper-fill" style="color: #C5A059;"></i>
            <span>Langganan Buletin &amp; Wawasan Pasar MGI</span>
          </div>
          <div class="small text-white-50" id="prevNewsDesc" style="font-size: 0.76rem;">Dapatkan rilis portofolio proyek baru, analisis sektor riil, dan kabar dividen langsung di email Anda.</div>
        </div>
        <div class="col-md-5">
          <div class="input-group input-group-sm">
            <input type="text" class="form-control" placeholder="Email Anda..." disabled>
            <button class="btn btn-warning btn-sm fw-bold px-3" style="background: #C5A059; border-color: #C5A059; color: #fff;" disabled>Langganan</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Bottom Copyright Row -->
    <div class="d-flex flex-wrap justify-content-between align-items-center pt-3 border-top border-secondary border-opacity-25 small" style="color: rgba(255,255,255,0.5); font-size: 0.76rem;">
      <div id="prevCopyright">&copy; <?= date('Y') ?> PT Montana Global Investama. Seluruh Hak Cipta Dilindungi Undang-Undang.</div>
      <div class="d-flex align-items-center gap-2">
        <span id="prevStatusText">Rahasia &amp; Terbatas</span>
        <span>•</span>
        <span id="prevBottomEmail" style="color: #C5A059;">contact@montanainvestama.com</span>
      </div>
    </div>
  </div>
</div>

<!-- Main Form -->
<form id="footerConfigForm" onsubmit="saveFooterSettings(event)">
  <div class="row g-4">
    <!-- Kolom Kiri: Tab Kontrol -->
    <div class="col-12 col-lg-8">
      
      <!-- Nav Tabs -->
      <ul class="nav nav-pills mb-3 gap-2" id="footerTab" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link active d-flex align-items-center gap-1.5 px-3 py-2" id="tab-contact-tab" data-bs-toggle="pill" data-bs-target="#tab-contact" type="button" role="tab">
            <i class="bi bi-telephone-fill"></i>
            <span>Kontak &amp; Telepon</span>
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link d-flex align-items-center gap-1.5 px-3 py-2" id="tab-branding-tab" data-bs-toggle="pill" data-bs-target="#tab-branding" type="button" role="tab">
            <i class="bi bi-briefcase-fill"></i>
            <span>Profil &amp; Media Sosial</span>
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link d-flex align-items-center gap-1.5 px-3 py-2" id="tab-links-tab" data-bs-toggle="pill" data-bs-target="#tab-links" type="button" role="tab">
            <i class="bi bi-link-45deg"></i>
            <span>Menu &amp; Tautan Footer</span>
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link d-flex align-items-center gap-1.5 px-3 py-2" id="tab-bottom-tab" data-bs-toggle="pill" data-bs-target="#tab-bottom" type="button" role="tab">
            <i class="bi bi-card-text"></i>
            <span>Buletin &amp; Hak Cipta</span>
          </button>
        </li>
      </ul>

      <div class="tab-content" id="footerTabContent">
        
        <!-- ========================================== -->
        <!-- TAB 1: KONTAK & TELEPON -->
        <!-- ========================================== -->
        <div class="tab-pane fade show active" id="tab-contact" role="tabpanel">
          <div class="admin-card">
            <div class="admin-card-header bg-white border-bottom p-3">
              <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                <i class="bi bi-telephone-outbound text-primary" style="color: #142563 !important;"></i>
                <span>Pengaturan Kontak Resmi di Footer</span>
              </h6>
            </div>
            <div class="admin-card-body p-4">
              
              <!-- Nomor Telepon Kantor -->
              <div class="p-3 bg-light rounded-3 border mb-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                  <label class="form-label fw-bold text-dark mb-0">
                    <i class="bi bi-telephone-fill text-gold me-1" style="color: #C5A059;"></i>
                    Nomor Telepon Kantor (Hotline)
                  </label>
                  <div class="form-check form-switch mb-0">
                    <input class="form-check-input" type="checkbox" id="checkPhoneShow" checked onchange="updateLivePreview()">
                    <label class="form-check-label small" for="checkPhoneShow">Tampilkan di Footer</label>
                  </div>
                </div>
                <input type="text" id="inputPhone" class="form-control" placeholder="+62 21 5315 8899 atau 021 5315 8899" oninput="updateLivePreview()">
                <small class="text-muted">Nomor ini otomatis disinkronkan ke seluruh tautan klik panggilan telepon (<code>tel:...</code>) di semua halaman.</small>
              </div>

              <!-- Nomor WhatsApp -->
              <div class="p-3 bg-light rounded-3 border mb-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                  <label class="form-label fw-bold text-dark mb-0">
                    <i class="bi bi-whatsapp text-success me-1"></i>
                    Nomor WhatsApp Resmi
                  </label>
                  <div class="form-check form-switch mb-0">
                    <input class="form-check-input" type="checkbox" id="checkWhatsappShow" checked onchange="updateLivePreview()">
                    <label class="form-check-label small" for="checkWhatsappShow">Tampilkan di Footer</label>
                  </div>
                </div>
                <input type="text" id="inputWhatsapp" class="form-control" placeholder="6281198765432" oninput="updateLivePreview()">
                <small class="text-muted">Format internasional tanpa spasi/tanda plus (contoh: <code>6281198765432</code>).</small>
              </div>

              <!-- Email Korespondensi -->
              <div class="p-3 bg-light rounded-3 border mb-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                  <label class="form-label fw-bold text-dark mb-0">
                    <i class="bi bi-envelope-fill text-primary me-1" style="color: #142563 !important;"></i>
                    Email Resmi Korespondensi
                  </label>
                  <div class="form-check form-switch mb-0">
                    <input class="form-check-input" type="checkbox" id="checkEmailShow" checked onchange="updateLivePreview()">
                    <label class="form-check-label small" for="checkEmailShow">Tampilkan di Footer</label>
                  </div>
                </div>
                <input type="email" id="inputEmail" class="form-control" placeholder="contact@montanainvestama.com" oninput="updateLivePreview()">
              </div>

              <!-- Jam Layanan -->
              <div class="p-3 bg-light rounded-3 border mb-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                  <label class="form-label fw-bold text-dark mb-0">
                    <i class="bi bi-clock-fill text-gold me-1" style="color: #C5A059;"></i>
                    Jam Layanan Operasional
                  </label>
                  <div class="form-check form-switch mb-0">
                    <input class="form-check-input" type="checkbox" id="checkHoursShow" checked onchange="updateLivePreview()">
                    <label class="form-check-label small" for="checkHoursShow">Tampilkan di Footer</label>
                  </div>
                </div>
                <input type="text" id="inputHours" class="form-control" placeholder="Senin – Jumat (08.00 – 17.00 WIB)" oninput="updateLivePreview()">
              </div>

              <!-- Alamat Kantor Pusat -->
              <div class="p-3 bg-light rounded-3 border">
                <div class="d-flex align-items-center justify-content-between mb-2">
                  <label class="form-label fw-bold text-dark mb-0">
                    <i class="bi bi-geo-alt-fill text-danger me-1"></i>
                    Alamat Kantor Pusat
                  </label>
                  <div class="form-check form-switch mb-0">
                    <input class="form-check-input" type="checkbox" id="checkAddressShow" checked onchange="updateLivePreview()">
                    <label class="form-check-label small" for="checkAddressShow">Tampilkan di Footer</label>
                  </div>
                </div>
                <textarea id="inputAddress" class="form-control" rows="2" placeholder="Roseville Soho & Suite, Sunburst CBD Lot I.8, Serpong, Tangerang Selatan" oninput="updateLivePreview()"></textarea>
              </div>

            </div>
          </div>
        </div>

        <!-- ========================================== -->
        <!-- TAB 2: PROFIL & MEDIA SOSIAL -->
        <!-- ========================================== -->
        <div class="tab-pane fade" id="tab-branding" role="tabpanel">
          <div class="admin-card">
            <div class="admin-card-header bg-white border-bottom p-3">
              <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                <i class="bi bi-building text-primary" style="color: #142563 !important;"></i>
                <span>Profil Perusahaan &amp; Media Sosial</span>
              </h6>
            </div>
            <div class="admin-card-body p-4">
              
              <div class="mb-3">
                <label class="form-label fw-bold small text-dark">URL / Path Logo Footer</label>
                <input type="text" id="inputLogo" class="form-control" placeholder="assets/img/mgi-official-logo.png" oninput="updateLivePreview()">
              </div>

              <div class="mb-3">
                <label class="form-label fw-bold small text-dark">Teks Ringkasan Profil di Footer</label>
                <textarea id="inputTagline" class="form-control" rows="3" placeholder="Ringkasan singkat tentang perusahaan..." oninput="updateLivePreview()"></textarea>
                <small class="text-muted">Teks ini tampil di bawah logo pada kolom pertama footer.</small>
              </div>

              <div class="p-3 bg-light rounded-3 border mb-4">
                <div class="d-flex align-items-center justify-content-between mb-2">
                  <label class="form-label fw-bold text-dark mb-0">Teks Badge Kepatuhan &amp; Regulasi</label>
                  <div class="form-check form-switch mb-0">
                    <input class="form-check-input" type="checkbox" id="checkBadgeShow" checked onchange="updateLivePreview()">
                    <label class="form-check-label small" for="checkBadgeShow">Tampilkan</label>
                  </div>
                </div>
                <input type="text" id="inputBadgeText" class="form-control" placeholder="Standar Tata Kelola Perusahaan & Kepatuhan Regulasi" oninput="updateLivePreview()">
              </div>

              <h6 class="fw-bold text-dark mb-3 pt-2 border-top">Akun Media Sosial Resmi</h6>

              <!-- Instagram -->
              <div class="row g-2 align-items-center mb-3">
                <div class="col-md-3">
                  <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="checkInstaShow" checked onchange="updateLivePreview()">
                    <label class="form-check-label fw-semibold small" for="checkInstaShow">
                      <i class="bi bi-instagram text-danger me-1"></i> Instagram
                    </label>
                  </div>
                </div>
                <div class="col-md-9">
                  <input type="url" id="inputInstagram" class="form-control form-control-sm" placeholder="https://instagram.com/montanainvestama" oninput="updateLivePreview()">
                </div>
              </div>

              <!-- LinkedIn -->
              <div class="row g-2 align-items-center mb-3">
                <div class="col-md-3">
                  <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="checkLinkedinShow" checked onchange="updateLivePreview()">
                    <label class="form-check-label fw-semibold small" for="checkLinkedinShow">
                      <i class="bi bi-linkedin text-primary me-1"></i> LinkedIn
                    </label>
                  </div>
                </div>
                <div class="col-md-9">
                  <input type="url" id="inputLinkedin" class="form-control form-control-sm" placeholder="https://linkedin.com/company/montana-global-investama" oninput="updateLivePreview()">
                </div>
              </div>

              <!-- YouTube -->
              <div class="row g-2 align-items-center mb-2">
                <div class="col-md-3">
                  <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="checkYoutubeShow" checked onchange="updateLivePreview()">
                    <label class="form-check-label fw-semibold small" for="checkYoutubeShow">
                      <i class="bi bi-youtube text-danger me-1"></i> YouTube
                    </label>
                  </div>
                </div>
                <div class="col-md-9">
                  <input type="url" id="inputYoutube" class="form-control form-control-sm" placeholder="https://youtube.com/@montanainvestama" oninput="updateLivePreview()">
                </div>
              </div>

            </div>
          </div>
        </div>

        <!-- ========================================== -->
        <!-- TAB 3: MENU & TAUTAN NAVIGASI -->
        <!-- ========================================== -->
        <div class="tab-pane fade" id="tab-links" role="tabpanel">
          <div class="admin-card mb-4">
            <div class="admin-card-header bg-white border-bottom p-3 d-flex align-items-center justify-content-between">
              <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                <i class="bi bi-list-nested text-warning" style="color: #C5A059 !important;"></i>
                <span>Kolom Navigasi 2 (Default: Navigasi Utama)</span>
              </h6>
              <button type="button" class="btn btn-sm btn-outline-primary" onclick="addCol2Row('', '')">
                <i class="bi bi-plus-circle me-1"></i> Tambah Tautan
              </button>
            </div>
            <div class="admin-card-body p-4">
              <div class="mb-3">
                <label class="form-label fw-bold small text-dark">Judul Kolom</label>
                <input type="text" id="inputCol2Title" class="form-control" placeholder="Navigasi Utama" oninput="updateLivePreview()">
              </div>
              <div class="table-responsive">
                <table class="table table-bordered table-sm align-middle">
                  <thead class="table-light small">
                    <tr>
                      <th style="width: 45%;">Teks Label Tautan</th>
                      <th style="width: 45%;">Target URL / Anchor</th>
                      <th style="width: 10%;" class="text-center">Aksi</th>
                    </tr>
                  </thead>
                  <tbody id="col2LinksBody">
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <div class="admin-card">
            <div class="admin-card-header bg-white border-bottom p-3 d-flex align-items-center justify-content-between">
              <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                <i class="bi bi-diagram-2 text-primary" style="color: #142563 !important;"></i>
                <span>Kolom Navigasi 3 (Default: Struktur &amp; Sinergi)</span>
              </h6>
              <button type="button" class="btn btn-sm btn-outline-primary" onclick="addCol3Row('', '')">
                <i class="bi bi-plus-circle me-1"></i> Tambah Tautan
              </button>
            </div>
            <div class="admin-card-body p-4">
              <div class="mb-3">
                <label class="form-label fw-bold small text-dark">Judul Kolom</label>
                <input type="text" id="inputCol3Title" class="form-control" placeholder="Struktur & Sinergi" oninput="updateLivePreview()">
              </div>
              <div class="table-responsive">
                <table class="table table-bordered table-sm align-middle">
                  <thead class="table-light small">
                    <tr>
                      <th style="width: 45%;">Teks Label Tautan</th>
                      <th style="width: 45%;">Target URL / Anchor</th>
                      <th style="width: 10%;" class="text-center">Aksi</th>
                    </tr>
                  </thead>
                  <tbody id="col3LinksBody">
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        <!-- ========================================== -->
        <!-- TAB 4: BULETIN & HAK CIPTA -->
        <!-- ========================================== -->
        <div class="tab-pane fade" id="tab-bottom" role="tabpanel">
          <div class="admin-card mb-4">
            <div class="admin-card-header bg-white border-bottom p-3">
              <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                <i class="bi bi-envelope-paper text-warning" style="color: #C5A059 !important;"></i>
                <span>Banner Langganan Buletin &amp; Newsletter</span>
              </h6>
            </div>
            <div class="admin-card-body p-4">
              <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" id="checkNewsShow" checked onchange="updateLivePreview()">
                <label class="form-check-label fw-bold" for="checkNewsShow">Tampilkan Banner Buletin di Footer</label>
              </div>

              <div class="mb-3">
                <label class="form-label fw-semibold small text-dark">Judul Banner Buletin</label>
                <input type="text" id="inputNewsTitle" class="form-control" placeholder="Langganan Buletin & Wawasan Pasar MGI" oninput="updateLivePreview()">
              </div>

              <div class="mb-2">
                <label class="form-label fw-semibold small text-dark">Deskripsi Banner Buletin</label>
                <textarea id="inputNewsDesc" class="form-control" rows="2" placeholder="Dapatkan rilis portofolio proyek baru, analisis sektor riil, dan kabar dividen langsung di email Anda." oninput="updateLivePreview()"></textarea>
              </div>
            </div>
          </div>

          <div class="admin-card">
            <div class="admin-card-header bg-white border-bottom p-3">
              <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                <i class="bi bi-c-circle text-primary" style="color: #142563 !important;"></i>
                <span>Baris Bawah (Copyright &amp; Status)</span>
              </h6>
            </div>
            <div class="admin-card-body p-4">
              <div class="mb-3">
                <label class="form-label fw-semibold small text-dark">Teks Hak Cipta (Copyright)</label>
                <input type="text" id="inputCopyright" class="form-control" placeholder="PT Montana Global Investama. Seluruh Hak Cipta Dilindungi Undang-Undang." oninput="updateLivePreview()">
                <small class="text-muted">Tahun &copy; akan otomatis disesuaikan dengan tahun berjalan.</small>
              </div>

              <div class="mb-2">
                <label class="form-label fw-semibold small text-dark">Teks Status / Klasifikasi Dokumen</label>
                <input type="text" id="inputStatusText" class="form-control" placeholder="Rahasia & Terbatas" oninput="updateLivePreview()">
              </div>
            </div>
          </div>
        </div>

      </div>

    </div>

    <!-- Kolom Kanan: Panduan & Tombol Simpan -->
    <div class="col-12 col-lg-4">
      <div class="admin-card mb-4 border border-warning-subtle shadow-sm">
        <div class="admin-card-header bg-white border-bottom border-warning-subtle">
          <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
            <i class="bi bi-lightbulb-fill text-warning"></i>
            <span>Panduan Manajemen Footer</span>
          </h6>
        </div>
        <div class="admin-card-body p-3 small text-secondary">
          <ul class="ps-3 mb-0 lh-base">
            <li class="mb-2"><strong>Nomor Telepon:</strong> Mengubah nomor telepon di sini akan otomatis memperbarui tampilan di seluruh halaman website tanpa perlu mengedit file HTML satu per satu.</li>
            <li class="mb-2"><strong>Switch Toggle:</strong> Anda dapat menyembunyikan atau menampilkan elemen tertentu (misalnya tombol WhatsApp atau nomor telepon) hanya dengan mengklik toggle.</li>
            <li class="mb-2"><strong>Menu Tautan:</strong> Anda bebas menambah atau mengurangi link navigasi di Kolom 2 dan Kolom 3 footer.</li>
            <li><strong>Pratinjau Langsung:</strong> Kotak pratinjau di bagian atas akan otomatis merespons saat Anda mengetik atau mengubah opsi.</li>
          </ul>
        </div>
      </div>

      <div class="admin-card">
        <div class="p-3">
          <button type="submit" id="btnSaveFooter" class="btn btn-mgi-primary w-100 py-2.5 fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2">
            <i class="bi bi-check2-circle fs-5"></i>
            <span>Simpan Semua Pengaturan</span>
          </button>
          <small class="text-muted d-block text-center mt-2" style="font-size: 0.76rem;">
            Perubahan langsung aktif di seluruh halaman publik &amp; investor portal.
          </small>
        </div>
      </div>
    </div>
  </div>
</form>

<script>
  document.addEventListener('DOMContentLoaded', loadFooterSettings);

  async function loadFooterSettings() {
    try {
      const res = await fetch('../api/admin/footer.php');
      const json = await res.json();
      if (!json.success) {
        if (json.status === 401) window.location.href = 'login.php';
        return;
      }

      const d = json.data || {};

      // Contact tab
      document.getElementById('inputPhone').value = d.contact_phone || '';
      document.getElementById('checkPhoneShow').checked = d.contact_phone_show !== '0';
      document.getElementById('inputWhatsapp').value = d.whatsapp_number || '';
      document.getElementById('checkWhatsappShow').checked = d.whatsapp_show !== '0';
      document.getElementById('inputEmail').value = d.official_email || '';
      document.getElementById('checkEmailShow').checked = d.official_email_show !== '0';
      document.getElementById('inputHours').value = d.operating_hours || '';
      document.getElementById('checkHoursShow').checked = d.operating_hours_show !== '0';
      document.getElementById('inputAddress').value = d.contact_address || '';
      document.getElementById('checkAddressShow').checked = d.contact_address_show !== '0';

      // Branding tab
      document.getElementById('inputLogo').value = d.footer_logo || 'assets/img/mgi-official-logo.png';
      document.getElementById('inputTagline').value = d.footer_tagline || '';
      document.getElementById('inputBadgeText').value = d.footer_badge_text || '';
      document.getElementById('checkBadgeShow').checked = d.footer_badge_show !== '0';
      document.getElementById('inputInstagram').value = d.social_instagram || '';
      document.getElementById('checkInstaShow').checked = d.social_instagram_show !== '0';
      document.getElementById('inputLinkedin').value = d.social_linkedin || '';
      document.getElementById('checkLinkedinShow').checked = d.social_linkedin_show !== '0';
      document.getElementById('inputYoutube').value = d.social_youtube || '';
      document.getElementById('checkYoutubeShow').checked = d.social_youtube_show !== '0';

      // Links tab
      document.getElementById('inputCol2Title').value = d.footer_col2_title || 'Navigasi Utama';
      document.getElementById('inputCol3Title').value = d.footer_col3_title || 'Struktur & Sinergi';

      const col2Links = d.col2_links_parsed || [];
      const col2Tbody = document.getElementById('col2LinksBody');
      col2Tbody.innerHTML = '';
      col2Links.forEach(item => addCol2Row(item.label, item.url));

      const col3Links = d.col3_links_parsed || [];
      const col3Tbody = document.getElementById('col3LinksBody');
      col3Tbody.innerHTML = '';
      col3Links.forEach(item => addCol3Row(item.label, item.url));

      // Bottom tab
      document.getElementById('checkNewsShow').checked = d.footer_newsletter_show !== '0';
      document.getElementById('inputNewsTitle').value = d.footer_newsletter_title || 'Langganan Buletin & Wawasan Pasar MGI';
      document.getElementById('inputNewsDesc').value = d.footer_newsletter_desc || '';
      document.getElementById('inputCopyright').value = d.footer_copyright || 'PT Montana Global Investama. Seluruh Hak Cipta Dilindungi Undang-Undang.';
      document.getElementById('inputStatusText').value = d.footer_status_text || 'Rahasia & Terbatas';

      updateLivePreview();
    } catch (e) {
      console.error(e);
      AdminApp.showToast('Gagal memuat konfigurasi footer.', 'danger');
    }
  }

  function addCol2Row(label = '', url = '') {
    const tbody = document.getElementById('col2LinksBody');
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td><input type="text" class="form-control form-control-sm col2-label" value="${escapeHtml(label)}" placeholder="Nama menu..." oninput="updateLivePreview()"></td>
      <td><input type="text" class="form-control form-control-sm col2-url" value="${escapeHtml(url)}" placeholder="URL (contoh: about atau https://...)" oninput="updateLivePreview()"></td>
      <td class="text-center">
        <button type="button" class="btn btn-outline-danger btn-sm p-1" onclick="this.closest('tr').remove(); updateLivePreview();" title="Hapus">
          <i class="bi bi-trash"></i>
        </button>
      </td>
    `;
    tbody.appendChild(tr);
    updateLivePreview();
  }

  function addCol3Row(label = '', url = '') {
    const tbody = document.getElementById('col3LinksBody');
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td><input type="text" class="form-control form-control-sm col3-label" value="${escapeHtml(label)}" placeholder="Nama menu..." oninput="updateLivePreview()"></td>
      <td><input type="text" class="form-control form-control-sm col3-url" value="${escapeHtml(url)}" placeholder="URL (contoh: preparation)" oninput="updateLivePreview()"></td>
      <td class="text-center">
        <button type="button" class="btn btn-outline-danger btn-sm p-1" onclick="this.closest('tr').remove(); updateLivePreview();" title="Hapus">
          <i class="bi bi-trash"></i>
        </button>
      </td>
    `;
    tbody.appendChild(tr);
    updateLivePreview();
  }

  function escapeHtml(str) {
    if (!str) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  function updateLivePreview() {
    // Branding
    const logoSrc = document.getElementById('inputLogo').value.trim() || 'assets/img/mgi-official-logo.png';
    document.getElementById('prevLogo').src = logoSrc.startsWith('http') || logoSrc.startsWith('/') ? logoSrc : ('../' + logoSrc);
    document.getElementById('prevTagline').textContent = document.getElementById('inputTagline').value.trim() || 'Ringkasan profil perusahaan...';
    
    const badgeShow = document.getElementById('checkBadgeShow').checked;
    document.getElementById('prevBadgeWrapper').style.display = badgeShow ? 'inline-flex' : 'none';
    document.getElementById('prevBadgeText').textContent = document.getElementById('inputBadgeText').value.trim() || 'Standar Tata Kelola';

    // Social Media
    document.getElementById('prevSocialInsta').style.display = document.getElementById('checkInstaShow').checked ? 'inline-block' : 'none';
    document.getElementById('prevSocialLinkedin').style.display = document.getElementById('checkLinkedinShow').checked ? 'inline-block' : 'none';
    document.getElementById('prevSocialYoutube').style.display = document.getElementById('checkYoutubeShow').checked ? 'inline-block' : 'none';

    // Col 2
    document.getElementById('prevCol2Title').textContent = document.getElementById('inputCol2Title').value.trim() || 'Navigasi Utama';
    const col2List = document.getElementById('prevCol2List');
    col2List.innerHTML = '';
    document.querySelectorAll('#col2LinksBody tr').forEach(tr => {
      const label = tr.querySelector('.col2-label')?.value.trim();
      if (label) {
        const li = document.createElement('li');
        li.textContent = label;
        col2List.appendChild(li);
      }
    });

    // Col 3
    document.getElementById('prevCol3Title').textContent = document.getElementById('inputCol3Title').value.trim() || 'Struktur & Sinergi';
    const col3List = document.getElementById('prevCol3List');
    col3List.innerHTML = '';
    document.querySelectorAll('#col3LinksBody tr').forEach(tr => {
      const label = tr.querySelector('.col3-label')?.value.trim();
      if (label) {
        const li = document.createElement('li');
        li.textContent = label;
        col3List.appendChild(li);
      }
    });

    // Contact
    const phone = document.getElementById('inputPhone').value.trim();
    const phoneShow = document.getElementById('checkPhoneShow').checked && phone.length > 0;
    document.getElementById('prevPhoneWrap').style.display = phoneShow ? 'block' : 'none';
    document.getElementById('prevPhone').textContent = phone;

    const wa = document.getElementById('inputWhatsapp').value.trim();
    const waShow = document.getElementById('checkWhatsappShow').checked && wa.length > 0;
    document.getElementById('prevWhatsappWrap').style.display = waShow ? 'block' : 'none';
    document.getElementById('prevWhatsapp').textContent = wa;

    const email = document.getElementById('inputEmail').value.trim();
    const emailShow = document.getElementById('checkEmailShow').checked && email.length > 0;
    document.getElementById('prevEmailWrap').style.display = emailShow ? 'block' : 'none';
    document.getElementById('prevEmail').textContent = email;

    const hours = document.getElementById('inputHours').value.trim();
    const hoursShow = document.getElementById('checkHoursShow').checked && hours.length > 0;
    document.getElementById('prevHoursWrap').style.display = hoursShow ? 'block' : 'none';
    document.getElementById('prevHours').textContent = hours;

    const address = document.getElementById('inputAddress').value.trim();
    const addressShow = document.getElementById('checkAddressShow').checked && address.length > 0;
    document.getElementById('prevAddressWrap').style.display = addressShow ? 'block' : 'none';
    document.getElementById('prevAddress').textContent = address;

    // Newsletter
    const newsShow = document.getElementById('checkNewsShow').checked;
    document.getElementById('prevNewsletterWrap').style.display = newsShow ? 'block' : 'none';
    document.getElementById('prevNewsTitle').querySelector('span').textContent = document.getElementById('inputNewsTitle').value.trim() || 'Langganan Buletin';
    document.getElementById('prevNewsDesc').textContent = document.getElementById('inputNewsDesc').value.trim() || '';

    // Bottom
    const copy = document.getElementById('inputCopyright').value.trim() || 'PT Montana Global Investama. Seluruh Hak Cipta Dilindungi Undang-Undang.';
    document.getElementById('prevCopyright').innerHTML = `&copy; ${new Date().getFullYear()} ${escapeHtml(copy)}`;
    document.getElementById('prevStatusText').textContent = document.getElementById('inputStatusText').value.trim() || 'Rahasia & Terbatas';
    document.getElementById('prevBottomEmail').textContent = email || 'contact@montanainvestama.com';
  }

  async function saveFooterSettings(e) {
    if (e) e.preventDefault();

    // Gather Col 2 links
    const col2Links = [];
    document.querySelectorAll('#col2LinksBody tr').forEach(tr => {
      const label = tr.querySelector('.col2-label')?.value.trim();
      const url = tr.querySelector('.col2-url')?.value.trim();
      if (label) col2Links.push({ label: label, url: url || '#' });
    });

    // Gather Col 3 links
    const col3Links = [];
    document.querySelectorAll('#col3LinksBody tr').forEach(tr => {
      const label = tr.querySelector('.col3-label')?.value.trim();
      const url = tr.querySelector('.col3-url')?.value.trim();
      if (label) col3Links.push({ label: label, url: url || '#' });
    });

    const payload = {
      // Contact
      contact_phone: document.getElementById('inputPhone').value.trim(),
      contact_phone_show: document.getElementById('checkPhoneShow').checked ? '1' : '0',
      whatsapp_number: document.getElementById('inputWhatsapp').value.trim(),
      whatsapp_show: document.getElementById('checkWhatsappShow').checked ? '1' : '0',
      official_email: document.getElementById('inputEmail').value.trim(),
      contact_email: document.getElementById('inputEmail').value.trim(),
      official_email_show: document.getElementById('checkEmailShow').checked ? '1' : '0',
      operating_hours: document.getElementById('inputHours').value.trim(),
      operating_hours_show: document.getElementById('checkHoursShow').checked ? '1' : '0',
      contact_address: document.getElementById('inputAddress').value.trim(),
      contact_address_show: document.getElementById('checkAddressShow').checked ? '1' : '0',

      // Branding
      footer_logo: document.getElementById('inputLogo').value.trim(),
      footer_tagline: document.getElementById('inputTagline').value.trim(),
      footer_badge_text: document.getElementById('inputBadgeText').value.trim(),
      footer_badge_show: document.getElementById('checkBadgeShow').checked ? '1' : '0',
      social_instagram: document.getElementById('inputInstagram').value.trim(),
      social_instagram_show: document.getElementById('checkInstaShow').checked ? '1' : '0',
      social_linkedin: document.getElementById('inputLinkedin').value.trim(),
      social_linkedin_show: document.getElementById('checkLinkedinShow').checked ? '1' : '0',
      social_youtube: document.getElementById('inputYoutube').value.trim(),
      social_youtube_show: document.getElementById('checkYoutubeShow').checked ? '1' : '0',

      // Links
      footer_col2_title: document.getElementById('inputCol2Title').value.trim(),
      footer_col2_links: JSON.stringify(col2Links),
      footer_col3_title: document.getElementById('inputCol3Title').value.trim(),
      footer_col3_links: JSON.stringify(col3Links),

      // Newsletter & Bottom
      footer_newsletter_show: document.getElementById('checkNewsShow').checked ? '1' : '0',
      footer_newsletter_title: document.getElementById('inputNewsTitle').value.trim(),
      footer_newsletter_desc: document.getElementById('inputNewsDesc').value.trim(),
      footer_copyright: document.getElementById('inputCopyright').value.trim(),
      footer_status_text: document.getElementById('inputStatusText').value.trim()
    };

    const btn1 = document.getElementById('btnSaveFooter');
    const btn2 = document.getElementById('btnSaveTop');
    if (btn1) btn1.disabled = true;
    if (btn2) btn2.disabled = true;

    try {
      const res = await fetch('../api/admin/footer.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      const json = await res.json();
      if (json.success) {
        AdminApp.showToast('Pengaturan footer berhasil disimpan dan disinkronkan ke seluruh website!', 'success');
        // Clear public sessionStorage cache so fresh settings load
        try {
          sessionStorage.removeItem('mgi_public_settings');
        } catch (e) {}
      } else {
        AdminApp.showToast(json.message || 'Gagal menyimpan pengaturan footer.', 'danger');
      }
    } catch (e) {
      console.error(e);
      AdminApp.showToast('Gangguan koneksi saat menyimpan pengaturan footer.', 'danger');
    } finally {
      if (btn1) btn1.disabled = false;
      if (btn2) btn2.disabled = false;
    }
  }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
