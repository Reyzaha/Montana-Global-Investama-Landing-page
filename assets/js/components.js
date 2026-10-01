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
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle ${isAboutActive ? 'active' : ''}" href="about.html" id="navbarDropdownAbout" role="button" data-bs-toggle="dropdown" aria-expanded="false">
          Tentang Kami
        </a>
        <ul class="dropdown-menu shadow-lg border-0" aria-labelledby="navbarDropdownAbout">
          <li>
            <a class="dropdown-item py-2 fw-semibold" href="about.html">
              <i class="bi bi-building text-gold me-2"></i> Profil Montana Group
            </a>
          </li>
          <li>
            <a class="dropdown-item py-2" href="about.html#transformasi">
              <i class="bi bi-clock-history text-gold me-2"></i> Perjalanan Transformasi
            </a>
          </li>
          <li>
            <a class="dropdown-item py-2" href="about.html#persiapan-entitas">
              <i class="bi bi-diagram-3 text-gold me-2"></i> Sinergi &amp; Persiapan Entitas
            </a>
          </li>
          <li><hr class="dropdown-divider border-secondary opacity-25 my-1"></li>
          <li>
            <a class="dropdown-item py-2" href="about.html#tata-kelola">
              <i class="bi bi-shield-check text-gold me-2"></i> Tata Kelola TARIF
            </a>
          </li>
        </ul>
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

  // 3. Render Status Badge (Solid Colors)
  renderStatusBadge: function (status) {
    const s = (status || 'Open').toLowerCase();
    let badgeClass = 'badge-solid-open';
    let icon = 'bi-record-circle-fill';
    let label = 'Dibuka';

    if (s.includes('fully') || s.includes('funded') || s.includes('fund') || s.includes('didanai')) {
      badgeClass = 'badge-solid-funded';
      icon = 'bi-check-circle-fill';
      label = 'Didanai Penuh';
    } else if (s.includes('close') || s.includes('tutup')) {
      badgeClass = 'badge-solid-closed';
      icon = 'bi-dash-circle-fill';
      label = 'Ditutup';
    } else if (s.includes('soon') || s.includes('segera')) {
      badgeClass = 'badge-solid-coming';
      icon = 'bi-clock-fill';
      label = 'Segera Hadir';
    }

    return `<span class="badge ${badgeClass} px-2.5 py-1 rounded-1 d-inline-flex align-items-center gap-1" style="font-size: 0.75rem; font-weight: 600;"><i class="bi ${icon} small"></i> ${label}</span>`;
  },

  // 4. Render Funding Progress Bar (Solid Colors)
  renderProgressBar: function (collected, target) {
    const col = Number(collected) || 0;
    const tar = Number(target) || 1;
    const pct = Math.min(100, Math.max(0, Math.round((col / tar) * 100)));
    const isCompleted = pct >= 100;

    return `
      <div class="mb-3">
        <div class="d-flex justify-content-between align-items-center small mb-2">
          <span class="fw-bold text-mgi-dark">${MGI.formatRupiah(col)}</span>
          <span class="text-mgi-muted fw-semibold">${pct}% / Target ${MGI.formatRupiahCompact(tar)}</span>
        </div>
        <div class="progress progress-solid">
          <div class="progress-bar progress-bar-gold ${isCompleted ? 'completed' : ''}" role="progressbar" style="width: ${pct}%;" aria-valuenow="${pct}" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
      </div>
    `;
  },

  // 5. Render Key Metrics Box (Investor Fast-Scan: Return, Tenor, Min Ticket)
  renderKeyMetrics: function (info) {
    if (!info) return '';

    // 1. Clean ROI Return (Never wrap awkwardly)
    let rawRet = (info.return || '≥30% (p.a.)').replace(/\s*\(p\.a\.\)/i, '').trim();

    // 2. Clean Tenor & Cycle Target
    let rawTenor = info.tenor || '12 Bulan';
    let cleanTenor = rawTenor.split('(')[0].trim();
    let tenorSub = 'TENOR PROYEK';
    if (rawTenor.toLowerCase().includes('2–3x') || rawTenor.toLowerCase().includes('2-3x')) {
      tenorSub = '2–3X SIKLUS / THN';
    } else if (rawTenor.toLowerCase().includes('putaran')) {
      tenorSub = 'PERPUTARAN UNIT';
    }

    // 3. Clean Minimum Ticket (Compact, sharp)
    let cleanMin = info.min_investment || 'Rp 500 Juta';
    if (cleanMin.includes('500.000.000')) {
      cleanMin = 'Rp 500 Juta';
    } else if (cleanMin.includes('1.000.000.000')) {
      cleanMin = 'Rp 1 Miliar';
    } else if (cleanMin.includes('2.000.000.000')) {
      cleanMin = 'Rp 2 Miliar';
    } else {
      const num = Number(cleanMin.replace(/[^0-9]/g, ''));
      if (num >= 1000000000) {
        cleanMin = 'Rp ' + (num / 1000000000) + ' Miliar';
      } else if (num >= 1000000) {
        cleanMin = 'Rp ' + (num / 1000000) + ' Juta';
      }
    }

    return `
      <div class="project-metrics-box p-2.5 px-1 rounded-3 mb-3 bg-light border border-subtle">
        <div class="row g-0 text-center align-items-stretch">
          <div class="col-4 border-end border-subtle px-1 d-flex flex-column justify-content-center">
            <div class="text-success fw-bold text-nowrap" style="font-size: 0.92rem; line-height: 1.2;">${rawRet}</div>
            <div class="text-muted fw-semibold mt-1 text-truncate text-uppercase" style="font-size: 0.64rem; letter-spacing: 0.3px;">ROI (p.a.)</div>
          </div>
          <div class="col-4 border-end border-subtle px-1 d-flex flex-column justify-content-center">
            <div class="text-dark fw-bold text-nowrap" style="font-size: 0.94rem; line-height: 1.2;">${cleanTenor}</div>
            <div class="text-muted fw-semibold mt-1 text-truncate text-uppercase" style="font-size: 0.64rem; letter-spacing: 0.3px;">${tenorSub}</div>
          </div>
          <div class="col-4 px-1 d-flex flex-column justify-content-center">
            <div class="text-royal fw-bold text-nowrap" style="font-size: 0.92rem; line-height: 1.2;">${cleanMin}</div>
            <div class="text-muted fw-semibold mt-1 text-truncate text-uppercase" style="font-size: 0.64rem; letter-spacing: 0.3px;">Min. Investasi</div>
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

    let payoutText = 'Tiap Siklus Penjualan';
    const rawPayout = (info.payout || '').toLowerCase();
    if (rawPayout.includes('siklus') || rawPayout.includes('putaran')) {
      payoutText = 'Bagi Hasil Tiap Siklus';
    } else if (rawPayout.includes('kuartal')) {
      payoutText = 'Bagi Hasil Kuartalan';
    } else if (rawPayout.includes('bulan')) {
      payoutText = 'Bagi Hasil Bulanan';
    }

    let assetBadge = 'Komatsu PC57-7 CBU';
    if (info.asset_backed) {
      if (info.asset_backed.toLowerCase().includes('komatsu')) {
        assetBadge = 'Komatsu CBU Grade A';
      } else {
        assetBadge = 'Unit CBU & BPKB';
      }
    }

    return `
      <div class="project-meta-details mb-3 pt-2.5 border-top border-subtle" style="font-size: 0.8rem;">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <span class="text-muted" style="font-size: 0.76rem;">Wilayah Operasi</span>
          <span class="fw-semibold text-dark text-end text-truncate ms-2" style="max-width: 175px;" title="${info.lokasi || ''}">
            ${lokasi}
          </span>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-2">
          <span class="text-muted" style="font-size: 0.76rem;">Distribusi Laba</span>
          <span class="fw-semibold text-primary text-end">
            ${payoutText}
          </span>
        </div>

        <div class="d-flex justify-content-between align-items-center">
          <span class="text-muted" style="font-size: 0.76rem;">Jaminan Aset</span>
          <span class="badge bg-success-subtle text-success border border-success-subtle fw-semibold px-2 py-1" style="font-size: 0.72rem;">
            ${assetBadge}
          </span>
        </div>
      </div>
    `;
  },

  // 7. Render Project Card (Bootstrap 5 Card - High Contrast Luxury)
  renderProjectCard: function (project) {
    if (!project) return '';
    const info = project.info || {};
    const remainingDays = info.remaining_days || '18 Hari Tersisa';
    const city = project.city || (info.lokasi ? info.lokasi.split(',')[0] : 'Regional');

    // Resolving City Icon Image (Pojok Kanan Atas)
    let cityIconImg = project.city_icon_img || '';
    if (!cityIconImg) {
      const cStr = ((project.city || '') + ' ' + (project.title || '') + ' ' + (info.lokasi || '')).toLowerCase();
      if (cStr.includes('jkt') || cStr.includes('jakarta') || cStr.includes('jabar') || cStr.includes('jabodetabek')) {
        cityIconImg = 'assets/img/city-jkt-jabar.png';
      } else if (cStr.includes('makassar') || cStr.includes('sulawesi')) {
        cityIconImg = 'assets/img/city-makassar.png';
      } else if (cStr.includes('surabaya') || cStr.includes('jatim')) {
        cityIconImg = 'assets/img/city-surabaya.png';
      } else if (cStr.includes('denpasar') || cStr.includes('bali')) {
        cityIconImg = 'assets/img/city-denpasar.png';
      }
    }

    return `
      <div class="card mgi-card h-100 shadow-sm border-0 d-flex flex-column">
        <div class="project-card-cover position-relative">
          <img src="${project.image || 'assets/img/komatsu.jpg'}" alt="${project.title}">
          
          <!-- Pojok Kiri Atas: Lokasi -->
          <div class="position-absolute top-0 start-0 m-3 d-flex flex-column gap-1" style="z-index: 3;">
            <span class="badge bg-gold text-white px-2.5 py-1 rounded-1 small fw-bold shadow-sm">
              <i class="bi bi-geo-alt-fill me-1"></i>${city}
            </span>
          </div>

          <!-- Pojok Kanan Atas: Status Badge -->
          <div class="position-absolute top-0 end-0 m-3 d-flex flex-column align-items-end gap-2" style="z-index: 3;">
            ${MGIComponents.renderStatusBadge(project.status)}
          </div>
        </div>

        <div class="card-body p-4 d-flex flex-column">
          <div class="d-flex justify-content-between align-items-center mb-2 small">
            <span class="badge bg-light text-secondary border fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.5px;">KODE: ${project.id.toUpperCase()}</span>
            <span class="text-muted fw-semibold" style="font-size: 0.75rem;"><i class="bi bi-shield-check text-success me-1"></i>Aset Terverifikasi</span>
          </div>

          <h5 class="card-title fw-bold text-dark mb-3" style="font-size: 1.05rem; line-height: 1.4; min-height: 48px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;" title="${project.title}">${project.title}</h5>

          ${MGIComponents.renderKeyMetrics(info)}

          ${MGIComponents.renderProgressBar(project.funding.collected, project.funding.target)}

          ${MGIComponents.renderMetaRow(info)}

          <div class="mt-auto pt-1">
            <button type="button" onclick="MGIAuth.handleProtectedDetail('${project.id}')" class="btn btn-outline-mgi w-100 py-2 fw-semibold d-flex align-items-center justify-content-center gap-2">
              <span>Lihat Detail Proyek</span>
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

    let rowsHtml = rows.map(r => `
      <tr>
        <td class="text-center fw-bold" style="width: 60px;">${r.no}</td>
        <td><strong class="text-mgi-dark">${r.item}</strong></td>
        <td class="text-center">${r.quantity} Unit</td>
        <td class="text-end">${MGI.formatRupiah(r.unit_price)}</td>
        <td class="text-end text-mgi-gold fw-bold">${MGI.formatRupiah(r.total)}</td>
      </tr>
    `).join('');

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
                      <td class="pe-3 py-2 fw-bold text-dark text-end">${sp.barang || 'Excavator PC57-7 (Komatsu)'}</td>
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

  // 11. Render Multi-Tier Investment Participation ("Modal Anda Mendapatkan Apa Saja?")
  renderInvestmentTiers: function (tiers) {
    if (!tiers || !tiers.length) return '';

    const cardsHtml = tiers.map(t => {
      const isFeatured = t.nominal === 1000000000 || t.nominal === 2000000000;
      const borderStyle = isFeatured ? 'border: 2px solid #C5A059 !important; box-shadow: 0 10px 25px rgba(197, 160, 89, 0.15);' : 'border: 1px solid #CBD5E1 !important;';
      const badgeHtml = t.badge ? `<span class="badge ${isFeatured ? 'bg-gold text-white' : 'bg-light text-dark border'} rounded-pill px-3 py-1 small fw-semibold">${t.badge}</span>` : '';

      return `
        <div class="col-12 col-md-6 col-xl-4">
          <div class="card h-100 bg-white rounded-4 overflow-hidden d-flex flex-column" style="${borderStyle}">
            <div class="p-4 border-bottom bg-light d-flex justify-content-between align-items-start">
              <div>
                ${badgeHtml}
                <h4 class="fw-bold text-dark mt-2 mb-0">${MGI.formatRupiah(t.nominal)}</h4>
                <div class="text-muted small mt-1 fw-semibold">${t.label}</div>
              </div>
              <div class="rounded-circle p-2 bg-white border shadow-sm">
                <i class="bi bi-shield-check text-gold fs-4"></i>
              </div>
            </div>

            <div class="p-4 flex-grow-1 d-flex flex-column">
              <p class="text-secondary small mb-3">${t.deskripsi || ''}</p>

              <div class="bg-light p-3 rounded-3 mb-3 small">
                <div class="d-flex justify-content-between mb-2">
                  <span class="text-muted"><i class="bi bi-truck me-1 text-primary"></i>Alokasi Fisik:</span>
                  <strong class="text-dark">${t.unit_qty} Unit PC57-7</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                  <span class="text-muted"><i class="bi bi-box me-1 text-primary"></i>Kapasitas Logistik:</span>
                  <strong class="text-dark">${t.container_qty}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                  <span class="text-muted"><i class="bi bi-pie-chart me-1 text-primary"></i>Porsi Proyek:</span>
                  <strong class="text-primary">${t.porsi_proyek}</strong>
                </div>
                <div class="border-top pt-2 mt-2">
                  <div class="text-muted small" style="font-size: 0.75rem;">Jaminan Aset (*Underlying*):</div>
                  <strong class="text-dark small d-block">${t.jaminan_aset}</strong>
                </div>
              </div>

              <!-- Imbal Hasil Box -->
              <div class="p-3 rounded-3 mb-4" style="background-color: #F0FDF4; border: 1px solid #BBF7D0;">
                <div class="small text-muted mb-1 text-uppercase fw-bold" style="font-size: 0.72rem; letter-spacing: 0.5px;">Proyeksi Bagi Hasil Tahunan:</div>
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <span class="small text-dark">Skenario 2x Putaran (28,6%):</span>
                  <strong class="text-success">${MGI.formatRupiah(t.profit_2x_tahunan)}/thn</strong>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                  <span class="small text-dark">Skenario 3x Putaran (42,9%):</span>
                  <strong class="text-success fs-6">${MGI.formatRupiah(t.profit_3x_tahunan)}/thn</strong>
                </div>
              </div>

              <div class="mt-auto pt-2">
                <button type="button" onclick="MGIComponents.selectTier(${t.nominal})" class="btn ${isFeatured ? 'btn-gold text-white' : 'btn-outline-primary'} w-100 rounded-pill fw-bold py-2 shadow-sm d-flex align-items-center justify-content-center gap-2 mb-2">
                  <i class="bi bi-calculator-fill"></i>
                  <span>Simulasikan Tiket Ini</span>
                </button>
                <a href="contact.html?subject=Konsultasi+Investasi+${encodeURIComponent(t.label)}+${encodeURIComponent(MGI.formatRupiah(t.nominal))}" class="btn btn-sm btn-link text-muted w-100 text-decoration-none text-center">
                  <i class="bi bi-chat-dots me-1"></i> Konsultasi Private via WhatsApp
                </a>
              </div>
            </div>
          </div>
        </div>
      `;
    }).join('');

    return `
      <div class="row g-4 mb-4" id="investmentTiersContainer">
        ${cardsHtml}
      </div>
    `;
  },

  // Helper: Pilih Tier dan Otomatis Scroll & Sinkronkan ke Simulator BEP
  selectTier: function (nominal) {
    if (typeof MGISimulator !== 'undefined' && MGISimulator.setInvestment) {
      MGISimulator.setInvestment(nominal);
    }
    const simSec = document.getElementById('sectionSimulation');
    if (simSec) {
      simSec.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
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
