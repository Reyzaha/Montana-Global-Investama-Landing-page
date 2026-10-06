/**
 * PT MONTANA GLOBAL INVESTAMA — BOOTSTRAP 5 UI COMPONENTS
 * Clean White Corporate Luxury with Solid Colors (No Gradients)
 */

const MGIComponents = {
  // 1. Render Global Navigation Bar (Bootstrap 5)
  renderNavbar: function (activePage = '') {
    const navContainer = document.getElementById('global-navbar');
    if (!navContainer) return;

    const isAboutActive = ['about', 'transformasi', 'preparation'].includes(activePage);

    const linksHtml = `
      <li class="nav-item">
        <a class="nav-link ${activePage === 'home' ? 'active' : ''}" href="index.html">
          Beranda
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link ${isAboutActive ? 'active' : ''}" href="about.html">
          Tentang Kami
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link ${activePage === 'invest' ? 'active' : ''}" href="invest.html">
          Proyek Investasi
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link ${activePage === 'ekosistem' ? 'active' : ''}" href="ekosistem.html">
          Ekosistem
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link ${activePage === 'contact' ? 'active' : ''}" href="contact.html">
          Kontak Kami
        </a>
      </li>
    `;

    // Check Authentication Status
    // Aktifkan tombol Masuk & Daftar (serta info investor jika login) di header navbar
    const SHOW_HEADER_AUTH = true;
    const isAuth = typeof MGIAuth !== 'undefined' && MGIAuth.isLoggedIn();
    const user = isAuth ? MGIAuth.getCurrentUser() : null;

    let authCtaHtml = '';
    if (SHOW_HEADER_AUTH) {
      if (isAuth && user) {
        const typeLabel = user.type === 'perusahaan' ? 'Korporasi' : 'Perorangan';
        const displayName = user.fullName || user.businessName || user.email.split('@')[0];

        authCtaHtml = `
          <div class="d-flex align-items-center gap-2 mt-3 mt-xl-0">
            <div class="dropdown">
              <button class="btn btn-navbar-cta btn-apple-cta dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-person-circle fs-6"></i>
                <span class="text-truncate fw-bold" style="max-width: 140px;">${displayName}</span>
                <span class="badge bg-dark text-gold small ms-1" style="border-radius: var(--apple-radius-pill); font-size: 0.72rem;">${typeLabel}</span>
              </button>
              <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-2">
                <li class="px-3 py-2 border-bottom border-secondary opacity-75">
                  <div class="small fw-bold text-white text-truncate">${displayName}</div>
                  <div class="text-white-50 small text-truncate" style="font-size: 0.75rem;">${user.email}</div>
                  <div class="badge bg-gold text-dark small mt-1" style="border-radius: var(--apple-radius-pill);">Investor ${typeLabel}</div>
                </li>
                <li><a class="dropdown-item py-2 fw-semibold text-white" href="investor-dashboard.html"><i class="bi bi-speedometer2 me-2 text-gold"></i>Dashboard Investor</a></li>
                <li><a class="dropdown-item py-2 text-white" href="investor-dashboard.html"><i class="bi bi-pie-chart-fill me-2 text-gold"></i>My Portofolio &amp; Dividen</a></li>
                <li><a class="dropdown-item py-2 text-white" href="invest.html"><i class="bi bi-grid me-2 text-gold"></i>Katalog Project Terbuka</a></li>
                <li><a class="dropdown-item py-2 text-white" href="contact.html"><i class="bi bi-geo-alt me-2 text-gold"></i>Lokasi & Layanan</a></li>
                <li><hr class="dropdown-divider border-secondary opacity-25"></li>
                <li><a class="dropdown-item py-2 text-danger fw-semibold" href="javascript:void(0)" onclick="MGIAuth.logout('index.html')"><i class="bi bi-box-arrow-right me-2"></i>Keluar (Logout)</a></li>
              </ul>
            </div>
          </div>
        `;
      } else {
        authCtaHtml = `
          <div class="d-flex align-items-center gap-2 mt-3 mt-xl-0">
            <a href="login.html" class="btn btn-navbar-login btn-apple-login">
              <i class="bi bi-box-arrow-in-right"></i> Masuk
            </a>
            <a href="register.html" class="btn btn-navbar-cta btn-apple-cta">
              <i class="bi bi-shield-lock-fill"></i> Portal Investor
            </a>
          </div>
        `;
      }
    }

    navContainer.innerHTML = `
      <nav class="navbar navbar-expand-xl site-navbar sticky-top">
        <div class="container">
          <a class="navbar-brand d-flex align-items-center gap-2" href="index.html">
            <div class="navbar-logo-badge">
              <img src="assets/img/mgi-official-logo.png" alt="PT Montana Global Investama Logo" height="34" class="d-inline-block">
            </div>
          </a>
          <button class="navbar-toggler border-0 text-white shadow-none px-2 py-1" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
            <i class="bi bi-list fs-2 text-white"></i>
          </button>
          <div class="collapse navbar-collapse" id="navbarContent">
            <ul class="navbar-nav mx-auto mb-2 mb-xl-0 gap-xl-1">
              ${linksHtml}
            </ul>
            ${authCtaHtml}
          </div>
        </div>
      </nav>
    `;

    // Ensure Auth Modal is present in DOM only if enabled
    if (typeof MGIAuth !== 'undefined' && MGIAuth.REQUIRE_AUTH_FOR_DETAILS) {
      MGIComponents.renderAuthModal();
    }

    // Scroll styling enhancement
    const header = document.querySelector('.site-navbar');
    window.addEventListener('scroll', () => {
      if (window.scrollY > 30) {
        if (header) header.classList.add('scrolled');
      } else {
        if (header) header.classList.remove('scrolled');
      }
    });
  },

  // 2. Render Global Footer (Bootstrap 5)
  renderFooter: function () {
    const footerContainer = document.getElementById('global-footer');
    if (!footerContainer) return;

    footerContainer.innerHTML = `
      <footer class="site-footer">
        <div class="container">
          <div class="row g-4 mb-5">
            <div class="col-lg-4 col-md-6">
              <a href="index.html" class="d-inline-block mb-3">
                <div class="bg-white p-2 rounded-2 d-inline-block shadow-sm">
                  <img src="assets/img/mgi-official-logo.png" alt="MGI Logo" height="48">
                </div>
              </a>
              <p class="text-footer-muted small mb-3 lh-base">
                <strong>PT Montana Global Investama</strong> adalah entitas manajer investasi project dan pemegang kendali strategis (grup) sektor riil berbasis aset fisik produktif, menghadirkan pertumbuhan nilai per project terukur melalui kepatuhan tata kelola terpercaya dan integrasi ekosistem terpadu.
              </p>
              <div class="d-inline-flex align-items-center gap-2 px-3 py-1.5 rounded-1 bg-footer-card border border-secondary small fw-bold text-gold">
                <i class="bi bi-shield-check text-white"></i> Standar Tata Kelola Perusahaan &amp; Kepatuhan Regulasi
              </div>
            </div>

            <div class="col-lg-2 col-md-6">
              <h6 class="fw-bold text-white text-uppercase mb-3 small tracking-wide pb-1 d-inline-block" style="border-bottom: 2px solid var(--mgi-gold);">Navigasi Utama</h6>
              <ul class="list-unstyled mb-0">
                <li><a href="index.html" class="footer-link">Beranda</a></li>
                <li><a href="about.html" class="footer-link">Profil Perusahaan</a></li>
                <li><a href="invest.html" class="footer-link">Portofolio Proyek Investasi</a></li>
                <li><a href="transformasi.html" class="footer-link">Transformasi Perusahaan</a></li>
                <li><a href="contact.html" class="footer-link">Kontak Kami</a></li>
              </ul>
            </div>

            <div class="col-lg-3 col-md-6">
              <h6 class="fw-bold text-white text-uppercase mb-3 small tracking-wide pb-1 d-inline-block" style="border-bottom: 2px solid var(--mgi-gold);">Struktur &amp; Sinergi</h6>
              <ul class="list-unstyled mb-0">
                <li><a href="preparation.html" class="footer-link">Struktur Alur Kerja Entitas</a></li>
                <li><a href="ekosistem.html" class="footer-link">Ekosistem Terpadu</a></li>
                <li><a href="javascript:void(0)" onclick="MGIAuth.handleProtectedDetail('proj-jkt-jabar')" class="footer-link">Simulasi Titik Impas &amp; Bagi Hasil</a></li>
                <li><a href="about.html#tata-kelola" class="footer-link">Tata Kelola Perusahaan (TARIF)</a></li>
              </ul>
            </div>

            <div class="col-lg-3 col-md-6">
              <h6 class="fw-bold text-white text-uppercase mb-3 small tracking-wide pb-1 d-inline-block" style="border-bottom: 2px solid var(--mgi-gold);">Hubungi Kami</h6>
              <div class="small text-footer-muted mb-2">
                <i class="bi bi-geo-alt text-gold me-1"></i> Roseville Soho &amp; Suite, Sunburst CBD Lot I.8, Serpong, Tangerang Selatan
              </div>
              <div class="small text-footer-muted mb-2">
                <i class="bi bi-envelope text-gold me-1"></i> kontak@montanaglobalinvestama.com
              </div>
              <div class="small text-footer-muted">
                <i class="bi bi-clock text-gold me-1"></i> Senin – Jumat (08.00 – 17.00 WIB)
              </div>
            </div>
          </div>

          <div class="d-flex flex-column flex-md-row justify-content-between align-items-center pt-3 border-top border-secondary small text-footer-muted">
            <div class="mb-2 mb-md-0">
              &copy; ${new Date().getFullYear()} PT Montana Global Investama. Seluruh Hak Cipta Dilindungi Undang-Undang.
            </div>
            <div class="d-flex align-items-center gap-3">
              <span>Rahasia &amp; Terbatas</span>
              <span>•</span>
              <a href="mailto:kontak@montanaglobalinvestama.com" class="text-gold text-decoration-none fw-semibold">kontak@montanaglobalinvestama.com</a>
            </div>
          </div>
        </div>
      </footer>
    `;
  },

  // 3. Render Status Badge (Eksklusif Model)
  renderStatusBadge: function (status) {
    const s = (status || 'Open').toLowerCase();
    if (s.includes('fully') || s.includes('funded') || s.includes('fund') || s.includes('didanai') || s.includes('secured')) {
      return `<span class="badge-slot-secured"><i class="bi bi-lock-fill"></i> Mitra Terkunci</span>`;
    } else if (s.includes('close') || s.includes('tutup')) {
      return `<span class="badge badge-solid-closed px-2.5 py-1 rounded-pill fw-bold" style="font-size: 0.72rem;"><i class="bi bi-dash-circle-fill"></i> Ditutup</span>`;
    } else if (s.includes('soon') || s.includes('segera')) {
      return `<span class="badge badge-solid-coming px-2.5 py-1 rounded-pill fw-bold" style="font-size: 0.72rem;"><i class="bi bi-clock-fill"></i> Segera Dibuka</span>`;
    }
    // Default open
    return `<span class="badge-slot-open"><i class="bi bi-person-check-fill"></i> 1 Slot Tersedia</span>`;
  },

  // 4. Render Exclusive Partnership Header / Progress Bar
  renderProgressBar: function (collected, target, customRange) {
    const rangeText = customRange || (target ? MGI.formatRupiahCompact(target) : 'Rp 7 – 10 Miliar');

    return `
      <div class="mb-3">
        <div class="d-flex justify-content-between align-items-center small mb-1">
          <span class="fw-bold text-dark"><i class="bi bi-award-fill text-gold me-1"></i>Eksklusif</span>
          <span class="badge bg-royal text-white px-2 py-0.5 rounded-pill" style="font-size: 0.7rem;">Kemitraan Tunggal</span>
        </div>
        <div class="d-flex justify-content-between align-items-center p-2 rounded-2 bg-light border border-subtle small mt-1">
          <span class="text-muted" style="font-size: 0.75rem;">Estimasi Kebutuhan Modal:</span>
          <strong class="text-mgi-blue">${rangeText}</strong>
        </div>
      </div>
    `;
  },

  // 5. Render Key Metrics Box (Investor Fast-Scan: Return, Tenor, Model 1-to-1)
  renderKeyMetrics: function (info) {
    if (!info) return '';

    // 1. Clean ROI Return
    let rawRet = (info.return || '28,6% – 42,9%').replace(/\s*\(p\.a\.\)/i, '').trim();

    // 2. Clean Tenor & Cycle Target
    let rawTenor = info.tenor || '12 Bulan';
    let cleanTenor = rawTenor.split('(')[0].trim();
    let tenorSub = 'TENOR PROYEK';
    if (rawTenor.toLowerCase().includes('2–3x') || rawTenor.toLowerCase().includes('2-3x') || rawTenor.toLowerCase().includes('putaran')) {
      tenorSub = '2–3X PUTARAN/THN';
    }

    return `
      <div class="project-metrics-box p-2.5 px-1 rounded-3 mb-3 bg-light border border-subtle">
        <div class="row g-0 text-center align-items-stretch">
          <div class="col-4 border-end border-subtle px-1 d-flex flex-column justify-content-center">
            <div class="text-success fw-bold text-nowrap" style="font-size: 0.88rem; line-height: 1.2;">${rawRet}</div>
            <div class="text-muted fw-semibold mt-1 text-truncate text-uppercase" style="font-size: 0.62rem; letter-spacing: 0.3px;">ROI (p.a.)</div>
          </div>
          <div class="col-4 border-end border-subtle px-1 d-flex flex-column justify-content-center">
            <div class="text-dark fw-bold text-nowrap" style="font-size: 0.9rem; line-height: 1.2;">${cleanTenor}</div>
            <div class="text-muted fw-semibold mt-1 text-truncate text-uppercase" style="font-size: 0.62rem; letter-spacing: 0.3px;">${tenorSub}</div>
          </div>
          <div class="col-4 px-1 d-flex flex-column justify-content-center">
            <div class="text-royal fw-bold text-nowrap" style="font-size: 0.88rem; line-height: 1.2;">1 Investor</div>
            <div class="text-gold fw-bold mt-1 text-truncate text-uppercase" style="font-size: 0.62rem; letter-spacing: 0.3px;">Eksklusif</div>
          </div>
        </div>
      </div>
    `;
  },

  // 6. Render Secondary Meta Row (Location, Distribution, Asset Protection)
  renderMetaRow: function (info) {
    if (!info) return '';
    let lokasi = info.lokasi ? info.lokasi.split(',')[0].trim() : 'Regional';
    if (lokasi.length > 26) {
      lokasi = lokasi.substring(0, 24) + '...';
    }

    let payoutText = 'Tutup Buku Laporan Tahunan';
    const rawPayout = (info.payout || '').toLowerCase();
    if (rawPayout.includes('tutup buku')) {
      payoutText = 'Tutup Buku Tahunan';
    } else if (rawPayout.includes('kuartal')) {
      payoutText = 'Tutup Buku Tahunan';
    }

    let assetBadge = 'Alat Berat Siap Operasi';
    if (info.asset_backed) {
      assetBadge = 'Alat Berat Produktif';
    }

    return `
      <div class="project-meta-details mb-3 pt-2.5 border-top border-subtle" style="font-size: 0.8rem;">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <span class="text-muted" style="font-size: 0.76rem;">Wilayah Proyek</span>
          <span class="fw-semibold text-dark text-end text-truncate ms-2" style="max-width: 175px;" title="${info.lokasi || ''}">
            ${lokasi}
          </span>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-2">
          <span class="text-muted" style="font-size: 0.76rem;">Distribusi Hasil</span>
          <span class="fw-semibold text-primary text-end">
            ${payoutText}
          </span>
        </div>

        <div class="d-flex justify-content-between align-items-center">
          <span class="text-muted" style="font-size: 0.76rem;">Jaminan Aset Fisik</span>
          <span class="badge bg-success-subtle text-success border border-success-subtle fw-semibold px-2 py-1" style="font-size: 0.72rem;">
            ${assetBadge}
          </span>
        </div>
      </div>
    `;
  },

  // 7. Render Project Card (Bootstrap 5 Card - High Contrast Luxury 1-to-1 Model)
  renderProjectCard: function (project) {
    if (!project) return '';
    const info = project.info || {};
    const city = project.city || (info.lokasi ? info.lokasi.split(',')[0] : 'Regional');
    const rabExec = (project.detail && project.detail.rab_executive) ? project.detail.rab_executive : null;
    
    // Resolve Mode: '2', '1', or 'none'
    const mode = project.package_options_mode || (rabExec && rabExec.package_options_mode) || ((project.package_options && project.package_options.length === 2) || (rabExec && rabExec.tiers && rabExec.tiers.length === 2) ? '2' : ((project.package_options && project.package_options.length === 1) || (rabExec && rabExec.tiers && rabExec.tiers.length === 1) ? '1' : 'none'));
    const options = project.package_options || (rabExec && (rabExec.package_options || rabExec.tiers)) || project.tiers || [];
    const stockArmada = (info.stock_available) ? info.stock_available : 'Batch Armada Alat Berat Siap Operasi';

    let optionsPillsHtml = '';
    let fundingBoxHtml = '';
    let operatorCalloutHtml = '';
    let ctaText = 'Lihat Detail Proyek';

    if (mode === '2' && options.length >= 2) {
      const opt1 = options[0];
      const opt2 = options[1];
      const nom1Disp = opt1.nominal_display || MGI.formatRupiahCompact(opt1.nominal);
      const nom2Disp = opt2.nominal_display || MGI.formatRupiahCompact(opt2.nominal);
      const perk1 = opt1.operator_perk ? '1 Operator' : 'Opsi 1';
      const perk2 = opt2.operator_perk ? '2 Operator' : 'Opsi 2';

      optionsPillsHtml = `
        <div class="project-options-pills">
          <span class="project-option-pill pill-opt1" title="${opt1.label || ''}">
            <i class="bi bi-1-circle-fill"></i> Pilihan 1: ${nom1Disp} (${perk1})
          </span>
          <span class="project-option-pill pill-opt2" title="${opt2.label || ''}">
            <i class="bi bi-2-circle-fill"></i> Pilihan 2: ${nom2Disp} (${perk2})
          </span>
        </div>
      `;

      fundingBoxHtml = `
        <div class="project-funding-range-box">
          <div class="funding-range-label">
            <span>Pilihan Paket Investasi:</span>
            <span class="badge bg-gold text-dark fw-bold px-2 py-0.5 rounded-pill" style="font-size: 0.65rem;">2 Pilihan Paket</span>
          </div>
          <div class="funding-range-value">${nom1Disp} &amp; ${nom2Disp}</div>
          <div class="small text-muted mt-1 text-truncate" style="font-size: 0.75rem;" title="${stockArmada}">
            <i class="bi bi-check2-circle me-1 text-primary"></i>Investor dapat memilih Paket 1 (${nom1Disp}) atau Paket 2 (${nom2Disp})
          </div>
        </div>
      `;

      operatorCalloutHtml = `
        <div class="operator-facility-callout">
          <i class="bi bi-person-badge-fill"></i>
          <div>
            <strong>Fasilitas Operator Profesional:</strong>
            <div class="small text-muted">Paket 1 (${nom1Disp}) = 1 Operator • Paket 2 (${nom2Disp}) = 2 Operator</div>
          </div>
        </div>
      `;

      ctaText = 'Lihat Detail &amp; Pilih Paket';
    } else if (mode === '1' && options.length >= 1) {
      const opt = options[0];
      const nomDisp = opt.nominal_display || MGI.formatRupiah(opt.nominal);
      const perk = opt.operator_perk || 'Termasuk Fasilitas 1 Operator Profesional';

      optionsPillsHtml = `
        <div class="project-options-pills">
          <span class="project-option-pill pill-single" title="${opt.label || ''}">
            <i class="bi bi-check-circle-fill"></i> Paket Tunggal: ${nomDisp}
          </span>
        </div>
      `;

      fundingBoxHtml = `
        <div class="project-funding-range-box">
          <div class="funding-range-label">
            <span>Nilai Paket Kemitraan:</span>
            <span class="badge bg-royal text-white fw-bold px-2 py-0.5 rounded-pill" style="font-size: 0.65rem;">1 Pilihan Paket</span>
          </div>
          <div class="funding-range-value">${nomDisp}</div>
          <div class="small text-muted mt-1 text-truncate" style="font-size: 0.75rem;" title="${stockArmada}">
            <i class="bi bi-truck me-1 text-primary"></i>${opt.unit_qty ? opt.unit_qty + ' Unit Alat Berat' : stockArmada}
          </div>
        </div>
      `;

      if (perk) {
        operatorCalloutHtml = `
          <div class="operator-facility-callout">
            <i class="bi bi-person-badge-fill"></i>
            <div>
              <strong>Bonus Fasilitas Operator:</strong>
              <div class="small text-muted">${perk}</div>
            </div>
          </div>
        `;
      }

      ctaText = 'Lihat Detail Kemitraan';
    } else {
      // Mode 'none' (Tanpa Pilihan Paket)
      const targetVal = project.funding && project.funding.target ? MGI.formatRupiah(project.funding.target) : (info.target || 'Rp 10.000.000.000');

      fundingBoxHtml = `
        <div class="project-funding-range-box">
          <div class="funding-range-label">
            <span>Target Pendanaan Proyek:</span>
            <span class="badge bg-secondary text-white fw-bold px-2 py-0.5 rounded-pill" style="font-size: 0.65rem;">Target Riil</span>
          </div>
          <div class="funding-range-value">${targetVal}</div>
          <div class="small text-muted mt-1 text-truncate" style="font-size: 0.75rem;" title="${stockArmada}">
            <i class="bi bi-truck me-1 text-primary"></i>${stockArmada}
          </div>
        </div>
      `;

      ctaText = 'Lihat Detail Proyek';
    }

    return `
      <div class="card mgi-card h-100 shadow-sm border-0 d-flex flex-column">
        <div class="project-card-cover position-relative">
          <img src="${project.image || 'assets/img/project-jabodetabek.jpg'}" alt="${project.title}" onerror="this.src='assets/img/project-jabodetabek.jpg'">
          
          <!-- Pojok Kiri Atas: Lokasi -->
          <div class="position-absolute top-0 start-0 m-3 d-flex flex-column gap-1" style="z-index: 3;">
            <span class="badge bg-gold text-white px-2.5 py-1 rounded-1 small fw-bold shadow-sm">
              <i class="bi bi-geo-alt-fill me-1"></i>${city}
            </span>
          </div>

          <!-- Pojok Kanan Atas: Slot Kemitraan Tunggal -->
          <div class="position-absolute top-0 end-0 m-3 d-flex flex-column align-items-end gap-2" style="z-index: 3;">
            ${MGIComponents.renderStatusBadge(project.status)}
          </div>
        </div>

        <div class="card-body p-4 d-flex flex-column">
          <!-- Model Header Badge -->
          <div class="d-flex justify-content-between align-items-center mb-2 small">
            <span class="badge-exclusive-model">
              <i class="bi bi-award-fill"></i> Eksklusif
            </span>
            <span class="badge bg-light text-secondary border fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.5px;">KODE: ${project.id.toUpperCase()}</span>
          </div>

          <h5 class="card-title fw-bold text-dark mb-2" style="font-size: 1.08rem; line-height: 1.4; min-height: 48px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;" title="${project.title}">${project.title}</h5>

          ${optionsPillsHtml}

          ${fundingBoxHtml}

          ${operatorCalloutHtml}

          ${MGIComponents.renderKeyMetrics(info)}

          ${MGIComponents.renderMetaRow(info)}

          <div class="mt-auto pt-1">
            <button type="button" onclick="MGIAuth.handleProtectedDetail('${project.id}')" class="btn btn-outline-mgi w-100 py-2.5 fw-semibold d-flex align-items-center justify-content-center gap-2">
              <span>${ctaText}</span>
              <i class="bi bi-arrow-right"></i>
            </button>
          </div>
        </div>
      </div>
    `;
  },

  // 7. Render Funding Target Table (Bootstrap 5 Table)
  renderFundingTable: function (fundingData) {
    if (!fundingData || !fundingData.rows) return '';

    const cols = fundingData.columns || ['No', 'Item', 'Quantity', 'Unit Price', 'Total'];
    const rows = fundingData.rows;
    let grandTotal = fundingData.grand_total || 0;

    let rowsHtml = rows.map(r => {
      const isPencadangan = (r.item && (r.item.toLowerCase().includes('cadangan') || r.item.toLowerCase().includes('pencadangan') || r.item.toLowerCase().includes('likuiditas')));
      const volDisp = isPencadangan ? '-' : (r.quantity ? r.quantity + ' Unit' : '-');
      const priceDisp = isPencadangan ? '-' : MGI.formatRupiah(r.unit_price);
      return `
      <tr>
        <td class="text-center fw-bold" style="width: 60px;">${r.no}</td>
        <td><strong class="text-mgi-dark">${r.item}</strong></td>
        <td class="text-center">${volDisp}</td>
        <td class="text-end">${priceDisp}</td>
        <td class="text-end text-mgi-gold fw-bold">${MGI.formatRupiah(r.total)}</td>
      </tr>
      `;
    }).join('');

    return `
      <div class="table-responsive table-mgi shadow-sm">
        <table class="table table-hover align-middle mb-0">
          <thead>
            <tr>
              ${cols.map((col, idx) => `
                <th class="${idx === 0 || idx === 2 ? 'text-center' : idx >= 3 ? 'text-end' : 'text-start'}">
                  ${col}
                </th>
              `).join('')}
            </tr>
          </thead>
          <tbody>
            ${rowsHtml}
          </tbody>
          <tfoot>
            <tr>
              <td colspan="4" class="text-end fw-bold">GRAND TOTAL ALOKASI DANA:</td>
              <td class="text-end fw-bold fs-5 text-mgi-gold">${MGI.formatRupiah(grandTotal)}</td>
            </tr>
          </tfoot>
        </table>
      </div>
    `;
  },

  // 8. Render Compliance Notice Box (Bootstrap 5 Alert)
  renderComplianceNotice: function (customText) {
    const text = customText || 'Target ilustratif berdasarkan proyeksi kinerja, bukan jaminan — hasil aktual mengikuti kinerja riil usaha dan dapat lebih rendah dari target.';
    return `
      <div class="alert alert-warning border-0 border-start border-4 border-warning bg-mgi-warning-bg p-3 rounded-end mb-4" role="alert">
        <div class="d-flex align-items-start gap-3">
          <i class="bi bi-shield-exclamation text-warning fs-5 flex-shrink-0 mt-1"></i>
          <div>
            <strong class="text-warning-emphasis d-block mb-1">Kepatuhan Keterbukaan Informasi:</strong>
            <span class="text-mgi-body small">${text}</span>
          </div>
        </div>
      </div>
    `;
  },

  // 9. Render Authentication Prompt Modal (Bootstrap 5)
  renderAuthModal: function () {
    if (document.getElementById('mgiAuthModal')) return;

    const modalWrap = document.createElement('div');
    modalWrap.innerHTML = `
      <div class="modal fade" id="mgiAuthModal" tabindex="-1" aria-labelledby="mgiAuthModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-royal text-white border-bottom-0 py-3 px-4">
              <div class="d-flex align-items-center gap-2">
                <i class="bi bi-shield-lock-fill text-gold fs-5"></i>
                <h5 class="modal-title fw-bold mb-0 text-white" id="mgiAuthModalLabel">Akses Terbatas Investor</h5>
              </div>
              <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body p-4 text-center">
              <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-mgi-gold-subtle text-gold mb-3 mx-auto" style="width: 64px; height: 64px; font-size: 1.8rem; border: 2px solid #C5A059;">
                <i class="bi bi-file-earmark-bar-graph-fill"></i>
              </div>
              <h4 class="fw-bold text-dark mb-2">Prospektus &amp; Simulasi BEP</h4>
              <p class="text-secondary small mb-4 lh-base">
                Untuk memenuhi standar <strong>Tata Kelola Perusahaan yang Baik</strong> serta kepatuhan regulasi penawaran investasi, rincian alokasi belanja modal, spesifikasi aset fisik, dan simulasi BEP/ROI hanya dapat diakses oleh investor terdaftar.
              </p>

              <div class="d-grid gap-2 mb-3">
                <a href="login.html" class="btn btn-mgi-blue py-2 px-4 rounded-pill fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2" id="authModalLoginBtn">
                  <i class="bi bi-box-arrow-in-right"></i>
                  <span>Masuk ke Akun Investor</span>
                </a>
                <a href="register.html" class="btn btn-gold py-2 px-4 rounded-pill fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2 text-white" id="authModalRegisterBtn">
                  <i class="bi bi-person-plus-fill"></i>
                  <span>Daftar Akun Baru (Perorangan / Perusahaan)</span>
                </a>
              </div>

              <div class="pt-2 border-top">
                <button type="button" class="btn btn-link text-muted btn-sm text-decoration-none" data-bs-dismiss="modal">
                  <i class="bi bi-arrow-left me-1"></i> Kembali Melihat Portofolio Publik
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    `;
    document.body.appendChild(modalWrap.firstElementChild);
  },

  // 10. Render Executive RAB 3-Card Layout (Matching Official Proposal PDF)
  renderRabExecutive: function (rab) {
    if (!rab) return '';
    const sp = rab.spesifikasi || {};
    const al = rab.alokasi_dana || {};
    const sb = rab.struktur_biaya_unit || {};

    return `
      <div class="row g-4 mb-4" id="rabExecutiveCards">
        <!-- Card 1: Spesifikasi Investasi -->
        <div class="col-12 col-lg-4">
          <div class="card h-100 shadow-sm border-0 rounded-4 overflow-hidden" style="border: 1px solid #CBD5E1 !important;">
            <div class="py-3 px-3 text-center text-white fw-bold" style="background-color: #0F224A; letter-spacing: 0.5px; font-size: 0.95rem;">
              <i class="bi bi-card-checklist me-1 text-gold"></i> SPESIFIKASI INVESTASI
            </div>
            <div class="card-body p-0 d-flex flex-column">
              <div class="table-responsive flex-grow-1">
                <table class="table table-sm table-striped align-middle mb-0" style="font-size: 0.85rem;">
                  <tbody>
                    <tr>
                      <td class="ps-3 py-2 text-muted" style="width: 45%;"><i class="bi bi-box-seam me-2 text-primary"></i>Barang</td>
                      <td class="pe-3 py-2 fw-bold text-dark text-end">${sp.barang || 'Unit Alat Berat Produktif'}</td>
                    </tr>
                    <tr>
                      <td class="ps-3 py-2 text-muted"><i class="bi bi-cash-stack me-2 text-primary"></i>Min. Target Proyek</td>
                      <td class="pe-3 py-2 fw-bold text-dark text-end">${MGI.formatRupiah(sp.minimum_investasi || 10000000000)}</td>
                    </tr>
                    <tr>
                      <td class="ps-3 py-2 text-muted"><i class="bi bi-ticket-perforated me-2 text-primary"></i>Min. Tiket Pemodal</td>
                      <td class="pe-3 py-2 fw-bold text-primary text-end">${MGI.formatRupiah(sp.minimum_tiket || 500000000)}</td>
                    </tr>
                    <tr>
                      <td class="ps-3 py-2 text-muted"><i class="bi bi-clock-history me-2 text-primary"></i>Periode Investasi</td>
                      <td class="pe-3 py-2 fw-bold text-dark text-end">${sp.periode_investasi || '12 Bulan'}</td>
                    </tr>
                    <tr>
                      <td class="ps-3 py-2 text-muted"><i class="bi bi-arrow-repeat me-2 text-primary"></i>Target Perputaran</td>
                      <td class="pe-3 py-2 fw-bold text-success text-end">${sp.target_perputaran || '2 – 3 Kali per Tahun'}</td>
                    </tr>
                    <tr>
                      <td class="ps-3 py-2 text-muted"><i class="bi bi-geo-alt me-2 text-primary"></i>Sistem Penjualan</td>
                      <td class="pe-3 py-2 text-dark text-end small fw-semibold">${sp.sistem_penjualan || 'Door to Door via PT MIU'}</td>
                    </tr>
                    <tr>
                      <td class="ps-3 py-2 text-muted"><i class="bi bi-truck me-2 text-primary"></i>Estimasi Armada</td>
                      <td class="pe-3 py-2 fw-bold text-dark text-end">${sp.estimasi_unit || '40 Unit (10 Kontainer)'}</td>
                    </tr>
                    <tr>
                      <td class="ps-3 py-2 text-muted"><i class="bi bi-box me-2 text-primary"></i>Container Digunakan</td>
                      <td class="pe-3 py-2 text-dark text-end small">${sp.container_used || '40FT HC Door-to-Door (MSI)'}</td>
                    </tr>
                    <tr>
                      <td class="ps-3 py-2 text-muted"><i class="bi bi-grid-3x2 me-2 text-primary"></i>Kapasitas Container</td>
                      <td class="pe-3 py-2 fw-bold text-dark text-end">${sp.kapasitas_container || '4 Unit / Container'}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <div class="p-3 bg-light border-top small text-muted lh-sm" style="font-size: 0.76rem;">
                <i class="bi bi-info-circle me-1 text-primary"></i>${sp.catatan || 'Alokasi pengadaan armada dari total dana investasi.'}
              </div>
            </div>
          </div>
        </div>

        <!-- Card 2: Alokasi Penggunaan Dana Investasi -->
        <div class="col-12 col-lg-4">
          <div class="card h-100 shadow-sm border-0 rounded-4 overflow-hidden" style="border: 1px solid #CBD5E1 !important;">
            <div class="py-3 px-3 text-center text-white fw-bold" style="background-color: #0F224A; letter-spacing: 0.5px; font-size: 0.95rem;">
              <i class="bi bi-pie-chart-fill me-1 text-gold"></i> ALOKASI PENGGUNAAN DANA
            </div>
            <div class="card-body p-0 d-flex flex-column">
              <div class="table-responsive flex-grow-1">
                <table class="table table-sm align-middle mb-0" style="font-size: 0.88rem;">
                  <tbody>
                    <tr class="border-bottom">
                      <td class="ps-3 py-3 text-dark fw-semibold" style="width: 55%;">
                        ${al.modal_operasional_label || 'Modal Operasional Awal (Pengadaan Unit)'}
                        <div class="small text-muted" style="font-size: 0.75rem;">(Beli + Kontainer + Rekondisi)</div>
                      </td>
                      <td class="pe-3 py-3 fw-bold text-dark text-end fs-6">${MGI.formatRupiah(al.modal_operasional_awal || 9100000000)}</td>
                    </tr>
                    <tr class="border-bottom">
                      <td class="ps-3 py-3 text-dark fw-semibold">
                        ${al.dana_pencadangan_label || 'Dana Pencadangan / Likuiditas (9%)'}
                        <div class="small text-muted" style="font-size: 0.75rem;">Cadangan kas operasional tahap awal</div>
                      </td>
                      <td class="pe-3 py-3 fw-bold text-dark text-end fs-6">${MGI.formatRupiah(al.dana_pencadangan || 900000000)}</td>
                    </tr>
                  </tbody>
                </table>

                <div class="py-3 px-3 d-flex justify-content-between align-items-center rounded-bottom" style="background-color: #0F224A; color: #FFFFFF; border-top: 2px solid #C5A059;">
                  <span class="fw-bold text-uppercase" style="font-size: 0.85rem; letter-spacing: 0.5px; color: #C5A059;">TOTAL ALOKASI MODAL</span>
                  <span class="fw-bold fs-5 text-white">${MGI.formatRupiah(al.total_investasi || 10000000000)}</span>
                </div>
              </div>

              <div class="p-3 bg-light border-top small text-muted lh-sm" style="font-size: 0.76rem;">
                <i class="bi bi-info-circle me-1 text-primary"></i>${al.catatan || 'Komponen PPN, budget garansi, budget insentif, dan fee sukses MIU baru timbul saat unit terjual sehingga tidak dibebankan pada dana awal.'}
              </div>
            </div>
          </div>
        </div>

        <!-- Card 3: Ringkasan Struktur Biaya per Unit -->
        <div class="col-12 col-lg-4">
          <div class="card h-100 shadow-sm border-0 rounded-4 overflow-hidden" style="border: 1px solid #CBD5E1 !important;">
            <div class="py-3 px-3 text-center text-white fw-bold" style="background-color: #0F224A; letter-spacing: 0.5px; font-size: 0.95rem;">
              <i class="bi bi-receipt me-1 text-gold"></i> STRUKTUR BIAYA PER UNIT
            </div>
            <div class="card-body p-0 d-flex flex-column">
              <div class="table-responsive flex-grow-1">
                <table class="table table-sm table-striped align-middle mb-0" style="font-size: 0.83rem;">
                  <tbody>
                    <tr>
                      <td class="ps-3 py-1">Harga Pokok Pembelian</td>
                      <td class="pe-3 py-1 text-end fw-semibold text-dark">${MGI.formatRupiah(sb.harga_pokok_beli || 150000000)}</td>
                    </tr>
                    <tr>
                      <td class="ps-3 py-1">PPN 11% (Dalam Harga Jual)</td>
                      <td class="pe-3 py-1 text-end text-muted">${MGI.formatRupiah(sb.ppn_11 || 34288288)}</td>
                    </tr>
                    <tr>
                      <td class="ps-3 py-1">Ongkos Rekondisi (MIU)</td>
                      <td class="pe-3 py-1 text-end text-muted">${MGI.formatRupiah(sb.ongkos_rekondisi || 25000000)}</td>
                    </tr>
                    <tr>
                      <td class="ps-3 py-1">Container 40FT HC (Alokasi/Unit)</td>
                      <td class="pe-3 py-1 text-end text-muted">${MGI.formatRupiah(sb.container_alokasi || 52500000)}</td>
                    </tr>
                    <tr>
                      <td class="ps-3 py-1">Budget Garansi (1% Harga Jual)</td>
                      <td class="pe-3 py-1 text-end text-muted">${MGI.formatRupiah(sb.budget_garansi || 3460000)}</td>
                    </tr>
                    <tr>
                      <td class="ps-3 py-1">Budget Insentif (1% Harga Jual)</td>
                      <td class="pe-3 py-1 text-end text-muted">${MGI.formatRupiah(sb.budget_insentif || 3460000)}</td>
                    </tr>
                    <tr>
                      <td class="ps-3 py-1">Fee Successful Sale MIU (12%)</td>
                      <td class="pe-3 py-1 text-end text-muted">${MGI.formatRupiah(sb.fee_miu || 41520000)}</td>
                    </tr>
                  </tbody>
                </table>

                <div class="py-2 px-3 d-flex justify-content-between align-items-center" style="background-color: #0F224A; color: #FFFFFF; border-top: 2px solid #C5A059;">
                  <span class="fw-bold text-uppercase" style="font-size: 0.82rem; color: #C5A059;">TOTAL BIAYA OPERASIONAL/UNIT</span>
                  <span class="fw-bold text-white">${MGI.formatRupiah(sb.total_biaya_unit || 310228288)}</span>
                </div>

                <div class="p-3 bg-white border-top">
                  <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="text-muted small">Harga Jual Unit (Inc. PPN):</span>
                    <strong class="text-dark">${MGI.formatRupiah(sb.harga_jual_unit || 346000000)}</strong>
                  </div>
                  <div class="d-flex justify-content-between align-items-center">
                    <span class="text-success small fw-bold">PROFIT BERSIH / UNIT:</span>
                    <strong class="text-success fs-6">${MGI.formatRupiah(sb.profit_bersih_unit || 35771712)}</strong>
                  </div>
                  <div class="text-muted small text-end" style="font-size: 0.75rem;">Estimasi Return per Siklus: <strong>${sb.return_per_siklus || 14.31}%</strong></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    `;
  },

  // 11. Render Multi-Tier Investment Participation ("Paket Kemitraan & Fasilitas Operator Profesional")
  renderInvestmentTiers: function (tiers, optionsMode = '2', project = null) {
    if (optionsMode === 'none' || !tiers || !tiers.length) {
      return '';
    }

    const projectId = project ? project.id : (typeof MGI !== 'undefined' ? (MGI.getQueryParam('id') || 'proj-jkt-jabar') : 'proj-jkt-jabar');
    const projectTitle = project ? project.title : 'Dealer Alat Berat';

    // 1 OPTION MODE
    if (optionsMode === '1' || tiers.length === 1) {
      const t = tiers[0];
      const nomDisp = t.nominal_display || MGI.formatRupiah(t.nominal);
      const opPerk = t.operator_perk || '1 Operator Profesional Bersertifikat';

      return `
        <div class="row justify-content-center mb-4" id="investmentTiersContainer">
          <div class="col-12 col-lg-8">
            <div class="card bg-white rounded-4 overflow-hidden border shadow-sm" style="border: 2px solid #1D3589 !important;">
              <div class="p-4 bg-light border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                  <span class="badge bg-royal text-white rounded-pill px-3 py-1 small fw-bold mb-1">
                    <i class="bi bi-star-fill text-gold me-1"></i> Paket Kemitraan Tunggal
                  </span>
                  <h3 class="fw-bold text-dark mt-1 mb-0" style="font-family: 'Outfit', sans-serif;">${nomDisp}</h3>
                  <div class="text-royal small mt-1 fw-bold text-uppercase" style="letter-spacing: 0.5px;">${t.label}</div>
                </div>
                <div class="rounded-circle p-2 bg-white border shadow-sm">
                  <i class="bi bi-shield-check text-gold fs-3"></i>
                </div>
              </div>

              <div class="p-4">
                <div class="package-operator-highlight mb-3">
                  <div class="operator-icon-circle">
                    <i class="bi bi-person-badge-fill"></i>
                  </div>
                  <div>
                    <div class="fw-bold small text-dark">${opPerk}</div>
                    <div class="text-muted" style="font-size: 0.75rem;">Gaji &amp; biaya operasional operator terkelola penuh dalam sistem MIU (Profesional)</div>
                  </div>
                </div>

                <p class="text-secondary small mb-3 lh-base">${t.deskripsi || 'Penempatan modal proyek dengan alokasi unit fisik terukur dan fasilitas operasional terpadu.'}</p>

                <div class="bg-light p-3 rounded-3 mb-3 small">
                  <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted"><i class="bi bi-truck me-1 text-primary"></i>Alokasi Fisik:</span>
                    <strong class="text-dark">${t.unit_qty || '20'} Unit Alat Berat</strong>
                  </div>
                  <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted"><i class="bi bi-box me-1 text-primary"></i>Kapasitas Logistik:</span>
                    <strong class="text-dark">${t.container_qty || '5 Kontainer 40FT HC'}</strong>
                  </div>
                  <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted"><i class="bi bi-pie-chart me-1 text-primary"></i>Porsi Kemitraan:</span>
                    <strong class="text-primary">${t.porsi_proyek || 'Alokasi Penuh'}</strong>
                  </div>
                  <div class="border-top pt-2 mt-2">
                    <div class="text-muted small" style="font-size: 0.75rem;">Jaminan Aset (*Underlying*):</div>
                    <strong class="text-dark small d-block">${t.jaminan_aset || 'Unit Alat Berat Siap Operasi'}</strong>
                  </div>
                </div>

                <!-- Imbal Hasil Box -->
                <div class="p-3 rounded-3 mb-4" style="background-color: #F0FDF4; border: 1px solid #BBF7D0;">
                  <div class="small text-muted mb-1 text-uppercase fw-bold" style="font-size: 0.72rem; letter-spacing: 0.5px;">Proyeksi Bagi Hasil Bersih Tahunan:</div>
                  <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="small text-dark">Skenario 2x Putaran (28,6%):</span>
                    <strong class="text-success">${MGI.formatRupiah(t.profit_2x_tahunan || (t.nominal * 0.286))}/thn</strong>
                  </div>
                  <div class="d-flex justify-content-between align-items-center">
                    <span class="small text-dark">Skenario 3x Putaran (42,9%):</span>
                    <strong class="text-success fs-6">${MGI.formatRupiah(t.profit_3x_tahunan || (t.nominal * 0.429))}/thn</strong>
                  </div>
                </div>

                <div class="row g-2">
                  <div class="col-12 col-sm-6">
                    <button type="button" onclick="MGIComponents.selectTier(${t.nominal})" class="btn btn-outline-primary fw-semibold w-100 rounded-pill py-2.5 shadow-sm d-flex align-items-center justify-content-center gap-2">
                      <i class="bi bi-calculator-fill"></i>
                      <span>Simulasikan Paket</span>
                    </button>
                  </div>
                  <div class="col-12 col-sm-6">
                    <button type="button" onclick="MGIComponents.openInterestModal('${projectId}', 0)" class="btn btn-gold text-dark fw-bold w-100 rounded-pill py-2.5 shadow-sm d-flex align-items-center justify-content-center gap-2">
                      <i class="bi bi-check2-circle"></i>
                      <span>Ajukan Minat Investasi</span>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      `;
    }

    // 2 OPTIONS MODE
    const cardsHtml = tiers.slice(0, 2).map((t, idx) => {
      const isOption2 = idx === 1;
      const borderClass = isOption2 ? 'active-selected' : '';
      const badgeHtml = isOption2 
        ? `<span class="badge bg-gold text-dark rounded-pill px-3 py-1 small fw-bold">Pilihan 2 (Kapasitas Penuh)</span>`
        : `<span class="badge bg-royal text-white rounded-pill px-3 py-1 small fw-bold">Pilihan 1 (Tiket Eksekutif)</span>`;
      const opPerk = t.operator_perk || (isOption2 ? '2 Operator Profesional Bersertifikat (Double Shift)' : '1 Operator Profesional Bersertifikat');
      const nomDisp = t.nominal_display || MGI.formatRupiah(t.nominal);

      return `
        <div class="col-12 col-lg-6">
          <div class="option-select-card card h-100 bg-white rounded-4 overflow-hidden d-flex flex-column ${borderClass}" id="tierCard-${idx}" onclick="MGIComponents.selectOption(${idx}, ${t.nominal}, '${projectId}')">
            
            <div class="option-select-indicator" id="tierCheck-${idx}" title="Status Pilihan">
              <i class="bi bi-check-lg"></i>
            </div>

            <div class="p-4 border-bottom bg-light d-flex justify-content-between align-items-start pe-5">
              <div>
                ${badgeHtml}
                <h3 class="fw-bold text-dark mt-2 mb-0" style="font-family: 'Outfit', sans-serif;">${nomDisp}</h3>
                <div class="text-royal small mt-1 fw-bold text-uppercase" style="letter-spacing: 0.5px;">${t.label}</div>
              </div>
            </div>

            <div class="p-4 flex-grow-1 d-flex flex-column">
              <!-- Operator Perk Callout Inside Tier Card -->
              <div class="package-operator-highlight mb-3">
                <div class="operator-icon-circle">
                  <i class="bi bi-person-badge-fill"></i>
                </div>
                <div>
                  <div class="fw-bold small text-dark">${opPerk}</div>
                  <div class="text-muted" style="font-size: 0.75rem;">Gaji &amp; biaya operasional operator terkelola penuh dalam sistem MIU (Profesional)</div>
                </div>
              </div>

              <p class="text-secondary small mb-3 lh-base">${t.deskripsi || ''}</p>

              <div class="bg-light p-3 rounded-3 mb-3 small">
                <div class="d-flex justify-content-between mb-2">
                  <span class="text-muted"><i class="bi bi-truck me-1 text-primary"></i>Alokasi Fisik:</span>
                  <strong class="text-dark">${t.unit_qty || (isOption2 ? '40' : '20')} Unit Alat Berat</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                  <span class="text-muted"><i class="bi bi-box me-1 text-primary"></i>Kapasitas Logistik:</span>
                  <strong class="text-dark">${t.container_qty || (isOption2 ? '10 Kontainer 40FT HC' : '5 Kontainer 40FT HC')}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                  <span class="text-muted"><i class="bi bi-pie-chart me-1 text-primary"></i>Porsi Kemitraan:</span>
                  <strong class="text-primary">${t.porsi_proyek || (isOption2 ? 'Eksklusivitas Penuh' : 'Alokasi 20 Unit')}</strong>
                </div>
                <div class="border-top pt-2 mt-2">
                  <div class="text-muted small" style="font-size: 0.75rem;">Jaminan Aset (*Underlying*):</div>
                  <strong class="text-dark small d-block">${t.jaminan_aset || 'Unit Fisik Alat Berat Siap Operasi'}</strong>
                </div>
              </div>

              <!-- Imbal Hasil Box -->
              <div class="p-3 rounded-3 mb-4" style="background-color: #F0FDF4; border: 1px solid #BBF7D0;">
                <div class="small text-muted mb-1 text-uppercase fw-bold" style="font-size: 0.72rem; letter-spacing: 0.5px;">Proyeksi Bagi Hasil Bersih Tahunan:</div>
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <span class="small text-dark">Skenario 2x Putaran (28,6%):</span>
                  <strong class="text-success">${MGI.formatRupiah(t.profit_2x_tahunan || (t.nominal * 0.286))}/thn</strong>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                  <span class="small text-dark">Skenario 3x Putaran (42,9%):</span>
                  <strong class="text-success fs-6">${MGI.formatRupiah(t.profit_3x_tahunan || (t.nominal * 0.429))}/thn</strong>
                </div>
              </div>

              <div class="mt-auto pt-2">
                <div class="row g-2 mb-2">
                  <div class="col-6">
                    <button type="button" onclick="event.stopPropagation(); MGIComponents.selectOption(${idx}, ${t.nominal}, '${projectId}')" class="btn btn-outline-primary btn-sm fw-semibold w-100 rounded-pill py-2">
                      <i class="bi bi-hand-index-thumb me-1"></i> Pilih Opsi Ini
                    </button>
                  </div>
                  <div class="col-6">
                    <button type="button" onclick="event.stopPropagation(); MGIComponents.openInterestModal('${projectId}', ${idx})" class="btn ${isOption2 ? 'btn-gold text-dark' : 'btn-royal text-white'} btn-sm fw-bold w-100 rounded-pill py-2 shadow-sm">
                      <i class="bi bi-check2-circle me-1"></i> Ajukan Minat
                    </button>
                  </div>
                </div>
                <a href="contact.html?subject=Konsultasi+Kemitraan+${encodeURIComponent(t.label)}+${encodeURIComponent(nomDisp)}" class="btn btn-sm btn-link text-muted w-100 text-decoration-none text-center" onclick="event.stopPropagation();">
                  <i class="bi bi-chat-dots me-1"></i> Konsultasi WhatsApp untuk Opsi Ini
                </a>
              </div>
            </div>
          </div>
        </div>
      `;
    }).join('');

    return `
      <div class="alert alert-light border border-subtle p-3 rounded-3 mb-4 text-center">
        <i class="bi bi-info-circle text-primary me-1"></i>
        <span class="small text-secondary">Silakan klik salah satu kartu opsi di bawah untuk memilih nominal dan fasilitas operator yang Anda inginkan:</span>
      </div>
      <div class="row g-4 mb-4 justify-content-center" id="investmentTiersContainer">
        ${cardsHtml}
      </div>
    `;
  },

  // Helper: Pilih Opsi 1 atau Opsi 2 dan perbarui state visual serta simulator
  selectOption: function (index, nominal, projectId) {
    const card0 = document.getElementById('tierCard-0');
    const card1 = document.getElementById('tierCard-1');
    if (card0 && card1) {
      if (index === 0) {
        card0.classList.add('active-selected');
        card1.classList.remove('active-selected');
      } else {
        card1.classList.add('active-selected');
        card0.classList.remove('active-selected');
      }
    }

    if (typeof MGISimulator !== 'undefined' && MGISimulator.setInvestment) {
      MGISimulator.setInvestment(nominal);
    }
  },

  selectTier: function (nominal) {
    if (typeof MGISimulator !== 'undefined' && MGISimulator.setInvestment) {
      MGISimulator.setInvestment(nominal);
    }
    const simSec = document.getElementById('sectionSimulation');
    if (simSec) {
      simSec.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  },

  // 11.1 Dialog Modal Minat Investasi (Capture Lead & Connect to WhatsApp)
  openInterestModal: function (projectId, optionIndex) {
    let p = window.currentProjectDetail;
    if (!p && typeof MGI !== 'undefined' && MGI._cachedData && MGI._cachedData.projects) {
      p = MGI._cachedData.projects.find(item => item.id === projectId);
    }
    const title = p ? p.title : 'Proyek Investasi Montana';
    const rabExec = (p && p.detail && p.detail.rab_executive) ? p.detail.rab_executive : null;
    const options = (p && p.package_options) || (rabExec && (rabExec.package_options || rabExec.tiers)) || [];
    const opt = options[optionIndex] || options[0] || null;

    const optLabel = opt ? opt.label : (optionIndex === 1 ? 'Paket 10 Miliar' : 'Paket 5 Miliar');
    const optNominal = opt ? (opt.nominal_display || MGI.formatRupiah(opt.nominal)) : (optionIndex === 1 ? 'Rp 10.000.000.000' : 'Rp 5.000.000.000');
    const optPerk = opt ? (opt.operator_perk || (optionIndex === 1 ? 'Bonus 2 Operator Profesional' : 'Bonus 1 Operator Profesional')) : 'Termasuk Fasilitas Operator';

    let modalEl = document.getElementById('mgiInterestModal');
    if (!modalEl) {
      const wrap = document.createElement('div');
      wrap.innerHTML = `
        <div class="modal fade" id="mgiInterestModal" tabindex="-1" aria-labelledby="mgiInterestModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
              <div class="modal-header bg-royal text-white border-bottom-0 py-3 px-4">
                <div class="d-flex align-items-center gap-2">
                  <i class="bi bi-bookmark-check-fill text-gold fs-5"></i>
                  <h5 class="modal-title fw-bold mb-0 text-white" id="mgiInterestModalLabel">Formulir Minat Investasi</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
              </div>
              <div class="modal-body p-4">
                <!-- Selected Package Callout -->
                <div class="p-3 rounded-3 mb-3 bg-light border border-subtle" id="interestModalPackageCard">
                  <!-- Rendered dynamically -->
                </div>

                <form id="interestSubmitForm" onsubmit="event.preventDefault(); MGIComponents.submitInterestForm();">
                  <input type="hidden" id="interestProjectId" value="">
                  <input type="hidden" id="interestOptionIndex" value="0">
                  <input type="hidden" id="interestOptionNominal" value="">
                  <input type="hidden" id="interestOptionLabel" value="">

                  <div class="mb-3">
                    <label class="form-label small fw-bold text-dark">Nama Investor / Nama Perusahaan <span class="text-danger">*</span></label>
                    <input type="text" class="form-control rounded-pill px-3" id="interestFullName" placeholder="Contoh: Bpk. Bambang / PT Maju Mandiri" required>
                  </div>

                  <div class="mb-3">
                    <label class="form-label small fw-bold text-dark">Nomor WhatsApp Aktif <span class="text-danger">*</span></label>
                    <input type="tel" class="form-control rounded-pill px-3" id="interestPhone" placeholder="Contoh: 081234567890" required>
                    <div class="form-text small">Dokumen resmi &amp; jadwal konsultasi akan dikirimkan via WhatsApp.</div>
                  </div>

                  <div class="mb-3">
                    <label class="form-label small fw-bold text-dark">Kota Domisili <span class="text-danger">*</span></label>
                    <input type="text" class="form-control rounded-pill px-3" id="interestCity" placeholder="Contoh: Jakarta / Surabaya / Denpasar" required>
                  </div>

                  <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" id="interestConsent" required checked>
                    <label class="form-check-label small text-secondary" for="interestConsent">
                      Saya bersedia dihubungi oleh Tim Manajer Investasi PT Montana Global Investama terkait dokumen penawaran kemitraan ini.
                    </label>
                  </div>

                  <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-gold py-2.5 rounded-pill fw-bold text-dark shadow-sm d-flex align-items-center justify-content-center gap-2" id="interestSubmitBtn">
                      <i class="bi bi-whatsapp"></i>
                      <span>Konfirmasi Minat &amp; Hubungi Tim</span>
                    </button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      `;
      document.body.appendChild(wrap.firstElementChild);
      modalEl = document.getElementById('mgiInterestModal');
    }

    // Populate data
    document.getElementById('interestProjectId').value = projectId;
    document.getElementById('interestOptionIndex').value = optionIndex;
    document.getElementById('interestOptionNominal').value = optNominal;
    document.getElementById('interestOptionLabel').value = optLabel;

    const pkgCard = document.getElementById('interestModalPackageCard');
    if (pkgCard) {
      pkgCard.innerHTML = `
        <div class="d-flex justify-content-between align-items-center mb-1">
          <span class="badge bg-royal text-white small px-2 py-0.5 rounded-pill">Pilihan Terpilih</span>
          <span class="small text-muted">${title}</span>
        </div>
        <div class="fs-5 fw-bold text-dark mt-1">${optNominal}</div>
        <div class="text-royal small fw-semibold">${optLabel}</div>
        <div class="text-success small mt-1"><i class="bi bi-person-badge-fill me-1"></i>${optPerk}</div>
      `;
    }

    // Pre-fill user data if logged in
    if (typeof MGIAuth !== 'undefined' && MGIAuth.isLoggedIn()) {
      const user = MGIAuth.getCurrentUser();
      if (user) {
        const nameField = document.getElementById('interestFullName');
        if (nameField && !nameField.value) nameField.value = user.fullName || user.businessName || '';
        const phoneField = document.getElementById('interestPhone');
        if (phoneField && !phoneField.value) phoneField.value = user.phone || '';
      }
    }

    const modal = new bootstrap.Modal(modalEl);
    modal.show();
  },

  // Submit Interest Form and Redirect to WhatsApp
  submitInterestForm: async function () {
    const submitBtn = document.getElementById('interestSubmitBtn');
    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>Memproses...`;
    }

    const fullName = document.getElementById('interestFullName').value.trim();
    const phone = document.getElementById('interestPhone').value.trim();
    const city = document.getElementById('interestCity').value.trim();
    const projectId = document.getElementById('interestProjectId').value;
    const optionNominal = document.getElementById('interestOptionNominal').value;
    const optionLabel = document.getElementById('interestOptionLabel').value;
    const consent = document.getElementById('interestConsent').checked;

    // Send to leads API
    try {
      let csrf = '';
      try {
        const cfgRes = await fetch('api/leads.php?action=config');
        if (cfgRes.ok) {
          const cfg = await cfgRes.json();
          csrf = (cfg.data && cfg.data.csrf_token) ? cfg.data.csrf_token : (cfg.csrf_token || '');
        }
      } catch (e) {}

      await fetch('api/leads.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          csrf_token: csrf,
          full_name: fullName,
          phone: phone,
          city: city,
          investment_range: '> Rp 5 Miliar',
          consent: consent,
          attribution: {
            landing_page: window.location.href,
            referrer: document.referrer,
            project_id: projectId,
            option_selected: `${optionLabel} (${optionNominal})`
          }
        })
      });
    } catch (err) {
      console.warn('[MGI Interest] Error saving lead to backend:', err);
    }

    // Dismiss Modal
    const modalEl = document.getElementById('mgiInterestModal');
    if (modalEl) {
      const bsModal = bootstrap.Modal.getInstance(modalEl);
      if (bsModal) bsModal.hide();
    }

    // Build WhatsApp Message
    const targetPhone = '6281211116666'; // Official MGI line
    const text = `Halo Tim Manajer Investasi PT Montana Global Investama,\n\nSaya *${fullName}* dari *${city}*.\nSaya berminat untuk penempatan modal kemitraan pada:\n• Proyek: *${projectId.toUpperCase()}*\n• Paket Dipilih: *${optionLabel}*\n• Nilai Permodalan: *${optionNominal}*\n\nMohon informasi ketersediaan slot kemitraan, berkas prospektus, dan jadwal konsultasi tatap muka. Terima kasih.`;
    const waUrl = `https://wa.me/${targetPhone}?text=${encodeURIComponent(text)}`;

    window.open(waUrl, '_blank');
  },

  // 12. Render Project Workflow (5 Tahap Ekosistem Montana)
  renderProjectWorkflow: function (steps) {
    if (!steps || !steps.length) return '';
    return `
      <div class="row g-3">
        ${steps.map(s => `
          <div class="col-12 col-md">
            <div class="p-3 bg-light rounded-3 border h-100 position-relative">
              <span class="badge bg-royal text-white rounded-pill px-2 py-1 mb-2 fw-bold" style="font-size: 0.75rem;">Tahap ${s.step}</span>
              <h6 class="fw-bold text-dark mb-1" style="font-size: 0.9rem;">${s.title}</h6>
              <p class="text-muted mb-0 small" style="font-size: 0.78rem; line-height: 1.4;">${s.desc}</p>
            </div>
          </div>
        `).join('')}
      </div>
    `;
  },

  // 13. Render Payment Breakdown (Halaman 6 PDF)
  renderPaymentBreakdown: function (breakdown, totalMiu, totalMsi, totalBiaya) {
    if (!breakdown || !breakdown.length) return '';
    const rows = breakdown.map(b => `
      <tr>
        <td class="text-center" style="width: 50px;">${b.no || ''}</td>
        <td><strong class="text-dark">${b.komponen}</strong></td>
        <td><span class="badge ${b.penerima === 'PT MIU' ? 'bg-primary' : 'bg-success'} text-white rounded-pill px-2 py-1">${b.penerima}</span></td>
        <td class="text-end fw-semibold">${MGI.formatRupiah(b.nilai)}</td>
      </tr>
    `).join('');

    return `
      <div class="table-responsive shadow-sm rounded-3 overflow-hidden border">
        <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
          <thead class="table-light">
            <tr>
              <th class="text-center">No</th>
              <th>Komponen Biaya</th>
              <th>Pihak Penerima</th>
              <th class="text-end">Nilai per Unit (Rp)</th>
            </tr>
          </thead>
          <tbody>
            ${rows}
          </tbody>
          <tfoot class="table-light fw-bold">
            <tr class="border-top">
              <td colspan="3" class="text-end text-uppercase">Total Biaya per Unit:</td>
              <td class="text-end text-dark fs-6">${MGI.formatRupiah(totalBiaya || 310228288)}</td>
            </tr>
            <tr style="background-color: #EEF2FF;">
              <td colspan="2" class="ps-3"><i class="bi bi-building me-1 text-primary"></i>Total Pembayaran ke PT MIU (Per Unit):</td>
              <td colspan="2" class="text-end text-primary fs-6">${MGI.formatRupiah(totalMiu || 257728288)}</td>
            </tr>
            <tr style="background-color: #ECFDF5;">
              <td colspan="2" class="ps-3"><i class="bi bi-ship me-1 text-success"></i>Total Pembayaran ke PT MSI (Per Unit):</td>
              <td colspan="2" class="text-end text-success fs-6">${MGI.formatRupiah(totalMsi || 52500000)}</td>
            </tr>
          </tfoot>
        </table>
      </div>
    `;
  }
};

window.MGIComponents = MGIComponents;
