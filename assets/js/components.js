/**
 * PT MONTANA GLOBAL INVESTAMA — BOOTSTRAP 5 UI COMPONENTS
 * Clean White Corporate Luxury with Solid Colors (No Gradients)
 */

const MGIComponents = {
  // 1. Render Global Navigation Bar (Bootstrap 5)
  renderNavbar: function (activePage = '') {
    const navContainer = document.getElementById('global-navbar');
    if (!navContainer) return;

    const navItems = [
      { id: 'home', label: 'Home', href: 'index.html' },
      { id: 'about', label: 'About Us', href: 'about.html' },
      { id: 'invest', label: 'Investasi', href: 'invest.html' },
      { id: 'transformasi', label: 'Transformasi', href: 'transformasi.html' },
      { id: 'preparation', label: 'Preparation', href: 'preparation.html' },
      { id: 'ekosistem', label: 'Ekosistem', href: 'ekosistem.html' },
      { id: 'contact', label: 'Kontak Kami', href: 'contact.html' }
    ];

    const linksHtml = navItems.map(item => `
      <li class="nav-item">
        <a class="nav-link ${activePage === item.id ? 'active' : ''}" href="${item.href}">
          ${item.label}
        </a>
      </li>
    `).join('');

    // Check Authentication Status
    const isAuth = typeof MGIAuth !== 'undefined' && MGIAuth.isLoggedIn();
    const user = isAuth ? MGIAuth.getCurrentUser() : null;

    let authCtaHtml = '';
    if (isAuth && user) {
      const typeLabel = user.type === 'perusahaan' ? 'Korporasi' : 'Perorangan';
      const displayName = user.fullName || user.businessName || user.email.split('@')[0];

      authCtaHtml = `
        <div class="d-flex align-items-center gap-2 mt-3 mt-lg-0">
          <div class="dropdown">
            <button class="btn btn-navbar-cta btn-sm px-3 py-2 rounded-pill dropdown-toggle d-flex align-items-center gap-2 shadow-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="bi bi-person-circle fs-6"></i>
              <span class="text-truncate fw-bold" style="max-width: 140px;">${displayName}</span>
              <span class="badge bg-royal text-white small ms-1">${typeLabel}</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-2">
              <li class="px-3 py-2 border-bottom">
                <div class="small fw-bold text-dark text-truncate">${displayName}</div>
                <div class="text-muted small text-truncate" style="font-size: 0.75rem;">${user.email}</div>
                <div class="badge bg-mgi-gold-subtle text-gold small mt-1">Investor ${typeLabel}</div>
              </li>
              <li><a class="dropdown-item py-2" href="invest.html"><i class="bi bi-briefcase me-2 text-gold"></i>Portofolio Investasi</a></li>
              <li><a class="dropdown-item py-2" href="contact.html"><i class="bi bi-geo-alt me-2 text-gold"></i>Lokasi & Layanan</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item py-2 text-danger fw-semibold" href="javascript:void(0)" onclick="MGIAuth.logout()"><i class="bi bi-box-arrow-right me-2"></i>Keluar (Logout)</a></li>
            </ul>
          </div>
        </div>
      `;
    } else {
      authCtaHtml = `
        <div class="d-flex align-items-center gap-2 mt-3 mt-lg-0">
          <a href="login.html" class="btn btn-outline-light btn-sm px-3 py-2 rounded-pill fw-semibold text-white">
            <i class="bi bi-box-arrow-in-right me-1"></i> Masuk
          </a>
          <a href="register.html" class="btn btn-navbar-cta btn-sm px-3 py-2 rounded-pill shadow-sm">
            <i class="bi bi-person-plus-fill me-1"></i> Daftar
          </a>
        </div>
      `;
    }

    navContainer.innerHTML = `
      <!-- Main Navigation Bar (Clean Corporate with Solid Royal Navy & Gold Accent) -->
      <nav class="navbar navbar-expand-lg site-navbar sticky-top py-3" id="mainHeader">
        <div class="container">
          <a class="navbar-brand d-flex align-items-center py-0" href="index.html" title="PT Montana Global Investama">
            <div class="navbar-logo-badge">
              <img src="assets/img/mgi-official-logo.png" alt="MGI Logo" height="56" class="d-inline-block align-text-top">
            </div>
          </a>

          <button class="navbar-toggler border-gold shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#mgiNavbar" aria-controls="mgiNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <i class="bi bi-list fs-2 text-white"></i>
          </button>

          <div class="collapse navbar-collapse" id="mgiNavbar">
            <ul class="navbar-nav mx-auto mb-2 mb-lg-0 gap-lg-1">
              ${linksHtml}
            </ul>

            ${authCtaHtml}
          </div>
        </div>
      </nav>
    `;

    // Ensure Auth Modal is present in DOM
    MGIComponents.renderAuthModal();

    // Scroll styling enhancement
    const header = document.getElementById('mainHeader');
    window.addEventListener('scroll', () => {
      if (window.scrollY > 30) {
        header.classList.add('scrolled');
      } else {
        header.classList.remove('scrolled');
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
                <div class="bg-white p-2 rounded-3 d-inline-block shadow-sm">
                  <img src="assets/img/mgi-official-logo.png" alt="MGI Logo" height="48">
                </div>
              </a>
              <p class="text-footer-muted small mb-3 lh-base">
                <strong>PT Montana Global Investama</strong> adalah entitas manajer investasi dan pemegang kendali strategis (holding) sektor riil berbasis aset fisik produktif, menghadirkan pertumbuhan nilai terukur melalui kepatuhan tata kelola terpercaya dan integrasi ekosistem terpadu.
              </p>
              <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded bg-footer-card border border-secondary small fw-bold text-gold">
                <i class="bi bi-shield-check text-white"></i> Standar Tata Kelola GCG &amp; APU-PPT
              </div>
            </div>

            <div class="col-lg-2 col-md-6">
              <h6 class="fw-bold text-gold text-uppercase mb-3 small tracking-wide">Navigasi Utama</h6>
              <ul class="list-unstyled mb-0">
                <li><a href="index.html" class="footer-link">Halaman Utama</a></li>
                <li><a href="about.html" class="footer-link">Company Profile</a></li>
                <li><a href="invest.html" class="footer-link">Portofolio Investasi</a></li>
                <li><a href="transformasi.html" class="footer-link">Transformasi Perusahaan</a></li>
                <li><a href="contact.html" class="footer-link">Kontak Kami</a></li>
              </ul>
            </div>

            <div class="col-lg-3 col-md-6">
              <h6 class="fw-bold text-gold text-uppercase mb-3 small tracking-wide">Struktur & Sinergi</h6>
              <ul class="list-unstyled mb-0">
                <li><a href="preparation.html" class="footer-link">Struktur Alur Kerja Entitas</a></li>
                <li><a href="ekosistem.html" class="footer-link">Bagan Ekosistem Terpadu</a></li>
                <li><a href="javascript:void(0)" onclick="MGIAuth.handleProtectedDetail('proj-001')" class="footer-link">Simulasi BEP &amp; ROI</a></li>
                <li><a href="about.html#tata-kelola" class="footer-link">Tata Kelola &amp; Prinsip GCG</a></li>
              </ul>
            </div>

            <div class="col-lg-3 col-md-6">
              <h6 class="fw-bold text-gold text-uppercase mb-3 small tracking-wide">Hubungi Kami</h6>
              <div class="small text-footer-muted mb-2">
                <i class="bi bi-geo-alt text-gold me-1"></i> Jl. Sunburst CBD No.8 Lot I, Serpong, Tangerang Selatan
              </div>
              <div class="small text-footer-muted mb-2">
                <i class="bi bi-envelope text-gold me-1"></i> Montanaglobalinvestamaom@gmail.com
              </div>
              <div class="small text-footer-muted">
                <i class="bi bi-clock text-gold me-1"></i> Senin – Jumat (08.00 – 17.00 WIB)
              </div>
            </div>
          </div>

          <!-- Mandatory Legal Disclaimer Card (Solid Theme) -->
          <div class="card border border-warning bg-footer-card p-3 p-md-4 rounded-3 mb-4 shadow-none">
            <div class="d-flex align-items-start gap-3">
              <i class="bi bi-exclamation-triangle-fill text-gold fs-5 flex-shrink-0 mt-1"></i>
              <div class="small text-footer-muted lh-base">
                <strong class="text-white d-block mb-1">PEMBERITAHUAN RISIKO & KEPATUHAN (RISK DISCLOSURE STATEMENT):</strong>
                Seluruh proyeksi imbal hasil, estimasi pengembalian modal pokok (BEP), dan kalkulasi ROI yang ditampilkan di website ini bersifat ilustrasi matematis berbasis kinerja historis dan proyeksi bisnis, bukan merupakan jaminan kepastian hasil. Investasi pada sektor riil dan pengadaan mesin/alat berat mengandung risiko fluktuasi pasar, operasional proyek, dan risiko likuiditas. Calon investor diwajibkan mempelajari seluruh prospektus/proposal penawaran dan menandatangani Risk Disclosure Statement sebelum berpartisipasi.
              </div>
            </div>
          </div>

          <div class="d-flex flex-column flex-md-row justify-content-between align-items-center pt-3 border-top border-secondary small text-footer-muted">
            <div class="mb-2 mb-md-0">
              &copy; ${new Date().getFullYear()} PT Montana Global Investama. Seluruh Hak Cipta Dilindungi Undang-Undang.
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

    if (s.includes('fully') || s.includes('funded')) {
      badgeClass = 'badge-solid-funded';
      icon = 'bi-check-circle-fill';
    } else if (s.includes('close')) {
      badgeClass = 'badge-solid-closed';
      icon = 'bi-dash-circle-fill';
    } else if (s.includes('soon')) {
      badgeClass = 'badge-solid-coming';
      icon = 'bi-clock-fill';
    }

    return `<span class="badge ${badgeClass} px-3 py-1 rounded-pill d-inline-flex align-items-center gap-1"><i class="bi ${icon} small"></i> ${status}</span>`;
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
    const ret = info.return || '≥30% (p.a.)';
    const tenor = info.tenor || '36 Bulan';
    const minTicket = info.min_investment || 'Rp 10 Juta';

    return `
      <div class="project-metrics-box p-3 rounded-3 mb-3 bg-light border border-subtle">
        <div class="row g-2 text-center align-items-center">
          <div class="col-4 border-end border-subtle">
            <div class="text-success fw-bold fs-5 lh-1 mb-1">${ret}</div>
            <div class="text-muted small text-uppercase fw-semibold" style="font-size: 0.68rem; letter-spacing: 0.5px;">Proyeksi ROI</div>
          </div>
          <div class="col-4 border-end border-subtle">
            <div class="text-dark fw-bold fs-6 lh-1 mb-1">${tenor}</div>
            <div class="text-muted small text-uppercase fw-semibold" style="font-size: 0.68rem; letter-spacing: 0.5px;">Tenor</div>
          </div>
          <div class="col-4">
            <div class="text-royal fw-bold fs-6 lh-1 mb-1">${minTicket}</div>
            <div class="text-muted small text-uppercase fw-semibold" style="font-size: 0.68rem; letter-spacing: 0.5px;">Min. Tiket</div>
          </div>
        </div>
      </div>
    `;
  },

  // 6. Render Secondary Meta Row (Location, Payout, Risk)
  renderMetaRow: function (info) {
    if (!info) return '';
    const lokasi = info.lokasi ? info.lokasi.split(',')[0] : 'Jawa Tengah';
    const payout = info.payout || 'Bagi Hasil Kuartalan';
    const risk = info.risk || 'Menengah - Terukur';

    return `
      <div class="small text-secondary mb-3 pt-2 border-top border-subtle">
        <div class="d-flex justify-content-between align-items-center mb-1">
          <span><i class="bi bi-geo-alt-fill text-gold me-1"></i>${lokasi}</span>
          <span><i class="bi bi-calendar2-check text-royal me-1"></i>${payout}</span>
        </div>
        <div class="d-flex justify-content-between align-items-center">
          <span class="text-muted" style="font-size: 0.75rem;">Profil Risiko:</span>
          <span class="fw-semibold text-dark" style="font-size: 0.78rem;"><i class="bi bi-shield-check text-royal me-1"></i>${risk}</span>
        </div>
      </div>
    `;
  },

  // 7. Render Project Card (Bootstrap 5 Card - High Contrast Luxury)
  renderProjectCard: function (project) {
    if (!project) return '';
    const info = project.info || {};
    const remainingDays = info.remaining_days || '18 Hari Tersisa';
    const assetTag = info.asset_backed || 'Asset-Backed';

    return `
      <div class="card mgi-card h-100 shadow-sm border-0 d-flex flex-column">
        <div class="project-card-cover position-relative">
          <img src="${project.image || 'assets/img/project-excavator.svg'}" alt="${project.title}">
          <div class="position-absolute top-0 start-0 m-3">
            <span class="badge bg-gold text-white px-2 py-1 rounded-pill small fw-bold shadow-sm">
              ${project.category || 'Investasi Proyek'}
            </span>
          </div>
          <div class="position-absolute top-0 end-0 m-3">
            ${MGIComponents.renderStatusBadge(project.status)}
          </div>
          <div class="position-absolute bottom-0 start-0 m-3">
            <span class="badge bg-royal text-white px-2 py-1 rounded small fw-semibold shadow-sm" style="font-size: 0.72rem;">
              <i class="bi bi-shield-lock-fill text-gold me-1"></i>${assetTag}
            </span>
          </div>
        </div>

        <div class="card-body p-4 d-flex flex-column">
          <div class="d-flex justify-content-between align-items-center mb-2 small">
            <span class="text-muted fw-semibold" style="font-size: 0.78rem;">
              <i class="bi bi-clock-history text-gold me-1"></i>${remainingDays}
            </span>
            <span class="text-muted" style="font-size: 0.75rem;">Kode: <strong class="text-dark">${project.id.toUpperCase()}</strong></span>
          </div>

          <h5 class="card-title fw-bold text-mgi-dark mb-3 lh-base" style="min-height: 52px;">${project.title}</h5>

          ${MGIComponents.renderKeyMetrics(info)}

          ${MGIComponents.renderProgressBar(project.funding.collected, project.funding.target)}

          ${MGIComponents.renderMetaRow(info)}

          <div class="mt-auto pt-1">
            <button type="button" onclick="MGIAuth.handleProtectedDetail('${project.id}')" class="btn btn-outline-mgi w-100 py-2 fw-semibold d-flex align-items-center justify-content-center gap-2">
              <span>Lihat Detail &amp; Simulasi BEP</span>
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
            <strong class="text-warning-emphasis d-block mb-1">Kepatuhan Keterbukaan Risiko (Risk Disclosure):</strong>
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
                Untuk memenuhi kepatuhan regulasi <strong>Good Corporate Governance (GCG)</strong> serta kerangka <strong>APU-PPT</strong>, rincian anggaran belanja modal (RAB), spesifikasi aset fisik, dan simulasi BEP/ROI hanya dapat diakses oleh investor terdaftar.
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
  }
};

window.MGIComponents = MGIComponents;
