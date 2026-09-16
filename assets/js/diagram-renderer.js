/**
 * PT MONTANA GLOBAL INVESTAMA — BOOTSTRAP 5 DIAGRAM & TIMELINE RENDERER
 * Clean White Corporate Luxury with Solid Colors (No Gradients)
 */

const MGIDiagrams = {
  // 1. Render Timeline for Transformasi Page (Bootstrap 5)
  renderTimeline: function (containerId, journeyData) {
    const container = document.getElementById(containerId);
    if (!container || !journeyData || !journeyData.journey) return;

    const items = journeyData.journey;
    const timelineHtml = items.map((item, index) => {
      const achievementsHtml = item.achievements && item.achievements.length > 0 ? `
        <div class="mt-3 pt-3 border-top border-subtle">
          <div class="small fw-bold text-dark mb-2 text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">Milestone Pencapaian:</div>
          <ul class="list-unstyled mb-0 small text-secondary">
            ${item.achievements.map(ach => `<li class="mb-1 d-flex align-items-start gap-2"><i class="bi bi-check2-circle text-gold flex-shrink-0 mt-1"></i><span>${ach}</span></li>`).join('')}
          </ul>
        </div>
      ` : '';

      return `
        <div class="timeline-entry mb-4 position-relative ps-5">
          <div class="timeline-marker-circle position-absolute start-0 top-0 bg-royal text-white rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 38px; height: 38px; font-size: 1rem; border: 3px solid #C5A059;">
            ${item.step || (index + 1)}
          </div>
          <div class="card mgi-card border border-subtle bg-white p-4 rounded-3 shadow-sm">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
              <span class="badge bg-gold text-white px-3 py-1 rounded-pill small fw-bold">
                ${item.badge || 'Milestone ' + (index + 1)} • ${item.year_or_phase}
              </span>
            </div>
            <h4 class="fw-bold text-dark mb-1">${item.title}</h4>
            ${item.tagline ? `<div class="text-royal fw-semibold small mb-3">${item.tagline}</div>` : ''}
            <p class="text-secondary mb-0 lh-base">${item.description}</p>
            ${achievementsHtml}
          </div>
        </div>
      `;
    }).join('');

    container.innerHTML = `
      <div class="timeline-list position-relative" style="border-left: 2px solid #E2E8F0; margin-left: 18px; padding-left: 8px;">
        ${timelineHtml}
      </div>
    `;
  },

  // 2. Render Preparation Structure & Workflow (Bootstrap 5)
  renderPreparation: function (entitiesContainerId, workflowContainerId, prepData) {
    if (!prepData) return;

    // Render Entities Cards & Table
    const entContainer = document.getElementById(entitiesContainerId);
    if (entContainer && prepData.entities_summary) {
      entContainer.className = "row g-4 mb-5";
      entContainer.innerHTML = prepData.entities_summary.map(ent => {
        let borderTopColor = '#C5A059';
        let badgeColor = 'bg-gold';
        if (ent.color === 'blue' || ent.code === 'MIU') {
          borderTopColor = '#1D3589';
          badgeColor = 'bg-royal';
        } else if (ent.color === 'navy' || ent.code === 'MSI') {
          borderTopColor = '#059669';
          badgeColor = 'bg-success';
        } else if (ent.code === 'Mypurcase') {
          borderTopColor = '#C5A059';
          badgeColor = 'bg-gold';
        }

        return `
          <div class="col-12 col-md-6 col-lg-4">
            <div class="card mgi-card h-100 shadow-sm p-4 bg-white d-flex flex-column" style="border: 1.5px solid #CBD5E1; border-top: 6px solid ${borderTopColor} !important;">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="badge ${badgeColor} text-white px-3 py-1 rounded-pill small fw-bold">
                  ${ent.code}
                </span>
                <i class="bi bi-${ent.icon || 'building'} text-secondary fs-4"></i>
              </div>
              <h5 class="fw-bold text-dark mb-1">${ent.name}</h5>
              <div class="small fw-bold text-gold mb-3">${ent.role}</div>
              
              <div class="mt-auto pt-3 border-top border-subtle small bg-light p-3 rounded-2">
                <div class="mb-2">
                  <span class="text-uppercase small text-muted fw-bold" style="font-size: 0.72rem;">Fokus Kerja:</span>
                  <div class="small text-dark fw-medium">${ent.fokus_kerja}</div>
                </div>
                <div>
                  <span class="text-uppercase small text-muted fw-bold" style="font-size: 0.72rem;">Output Utama:</span>
                  <div class="small text-secondary">${ent.output_utama}</div>
                </div>
              </div>
            </div>
          </div>
        `;
      }).join('');
    }

    // Render 5-Step Workflow Cards
    const wfContainer = document.getElementById(workflowContainerId);
    if (wfContainer && prepData.workflow) {
      wfContainer.innerHTML = `
        <div class="d-flex flex-column gap-3 mx-auto" style="max-width: 900px;">
          ${prepData.workflow.map((wf, idx) => {
            const isGold = idx % 2 === 1;
            const borderTopColor = isGold ? '#C5A059' : '#1D3589';
            return `
              <div class="card mgi-card bg-white p-3 p-md-4 rounded-3 shadow-sm" style="border: 1.5px solid #CBD5E1; border-top: 5px solid ${borderTopColor} !important;">
                <div class="d-flex align-items-center gap-3 gap-md-4">
                  <div class="bg-royal text-white rounded-3 fs-4 fw-bold d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm" style="width: 52px; height: 52px; border: 2.5px solid #C5A059;">
                    ${wf.step}
                  </div>
                  <div class="flex-grow-1">
                    <div class="d-flex align-items-center gap-2 mb-1">
                      <span class="badge bg-light text-dark border border-subtle small fw-bold px-2 py-1">
                        ${wf.actor}
                      </span>
                    </div>
                    <h5 class="fw-bold text-dark mb-1">${wf.title}</h5>
                    <p class="small text-secondary mb-0 lh-base">${wf.desc}</p>
                  </div>
                </div>
              </div>
            `;
          }).join('')}
        </div>
      `;
    }
  },

  // 3. Render Ekosistem Tree & Hierarchy Table (Bootstrap 5)
  renderEkosistem: function (containerId, drawerId, ekosistemData) {
    const container = document.getElementById(containerId);
    if (!container || !ekosistemData) return;

    const root = ekosistemData.nodes.find(n => n.level === 0) || ekosistemData.nodes[0];
    const level1 = ekosistemData.nodes.filter(n => n.level === 1 && n.type !== 'partner');
    const partners = ekosistemData.nodes.filter(n => n.type === 'partner');
    const level2 = ekosistemData.nodes.filter(n => n.level === 2);

    const level1Html = level1.map(node => {
      const children = level2.filter(c => c.parent === node.id);
      const childrenHtml = children.length > 0 ? `
        <div class="mt-auto pt-3 border-top border-subtle">
          <div class="small text-muted text-uppercase fw-bold mb-2" style="font-size: 0.72rem;">Unit Fasilitas Lapangan:</div>
          ${children.map(ch => `
            <div class="small fw-semibold text-royal bg-light p-2 rounded-2 border border-subtle d-flex align-items-center gap-2">
              <i class="bi bi-geo-alt-fill text-gold"></i>
              <span>${ch.label}</span>
            </div>
          `).join('')}
        </div>
      ` : '';

      let borderTopColor = '#1D3589';
      let badgeClass = 'bg-royal';
      let iconColorClass = 'text-royal';
      let iconName = 'bi-building';

      if (node.id === 'miu') {
        borderTopColor = '#1D3589';
        badgeClass = 'bg-royal';
        iconColorClass = 'text-royal';
        iconName = 'bi-truck';
      } else if (node.id === 'montana-sentra-industri') {
        borderTopColor = '#059669';
        badgeClass = 'bg-success';
        iconColorClass = 'text-success';
        iconName = 'bi-tools';
      } else if (node.id === 'mypurcase') {
        borderTopColor = '#C5A059';
        badgeClass = 'bg-gold';
        iconColorClass = 'text-gold';
        iconName = 'bi-cart-check-fill';
      }

      return `
        <div class="col-12 col-md-6 col-lg-4">
          <div class="card mgi-card bg-white p-4 rounded-3 h-100 shadow-sm d-flex flex-column" style="border: 1.5px solid #CBD5E1; border-top: 6px solid ${borderTopColor} !important;">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <span class="badge ${badgeClass} text-white px-3 py-1 rounded-pill small fw-bold">
                ${node.badge || 'Unit Usaha'}
              </span>
              <i class="bi ${iconName} ${iconColorClass} fs-4"></i>
            </div>
            <h5 class="fw-bold text-dark mb-1">${node.label}</h5>
            <div class="small text-gold fw-bold mb-3">${node.category || ''}</div>
            <p class="small text-secondary mb-3 lh-base">${node.description || ''}</p>
            ${childrenHtml}
          </div>
        </div>
      `;
    }).join('');

    // Partners Section HTML
    const partnerHtml = partners.map(p => `
      <div class="col-12 col-md-8 col-lg-6 mx-auto">
        <div class="card mgi-card bg-white p-4 rounded-3 shadow-sm" style="border: 1.5px solid #CBD5E1; border-top: 6px solid #C5A059 !important;">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <span class="badge bg-gold text-white px-3 py-1 rounded-pill small fw-bold">
              <i class="bi bi-handshake me-1"></i> ${p.badge || 'Mitra Strategis'}
            </span>
            <span class="badge bg-light text-dark border border-subtle small fw-bold">Level 1 (Eksternal)</span>
          </div>
          <h5 class="fw-bold text-dark mb-1">${p.label}</h5>
          <div class="small text-gold fw-bold mb-2">${p.category || ''}</div>
          <p class="small text-secondary mb-0 lh-base">${p.description || ''}</p>
        </div>
      </div>
    `).join('');

    // Hierarchy table summary with bold headers and cards
    const tableHtml = ekosistemData.table_summary ? `
      <div class="card mgi-card shadow-sm bg-white mt-5 p-4 p-md-5 rounded-3" style="border: 1.5px solid #CBD5E1; border-top: 6px solid #1D3589 !important;">
        <div class="d-flex align-items-center gap-2 mb-3">
          <div class="rounded-circle bg-royal text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px; font-size: 1.2rem;">
            <i class="bi bi-diagram-3-fill"></i>
          </div>
          <h4 class="fw-bold text-dark mb-0">Tabel Struktur Hierarki Montana Group</h4>
        </div>
        <p class="text-secondary small mb-4">Ringkasan struktur hierarki entitas dan fungsi utama pada ekosistem Montana Group.</p>
        <div class="table-responsive">
          <table class="table table-striped table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th scope="col" class="fw-bold text-dark">Entitas</th>
                <th scope="col" class="fw-bold text-dark">Level</th>
                <th scope="col" class="fw-bold text-dark">Induk Langsung</th>
                <th scope="col" class="fw-bold text-dark">Fungsi Utama (Ringkas)</th>
              </tr>
            </thead>
            <tbody>
              ${ekosistemData.table_summary.map(row => `
                <tr>
                  <td class="fw-bold text-dark">${row.entitas}</td>
                  <td><span class="badge ${row.level.includes('0') ? 'bg-royal' : 'bg-gold'} text-white fw-bold px-3 py-1 rounded-pill">${row.level}</span></td>
                  <td class="text-secondary fw-medium">${row.induk_langsung}</td>
                  <td class="small text-secondary lh-base">${row.fungsi_utama}</td>
                </tr>
              `).join('')}
            </tbody>
          </table>
        </div>
      </div>
    ` : '';

    container.innerHTML = `
      <div class="text-center mb-4">
        <!-- Root Holding Node (Bold Luxury Styling) -->
        <div class="card mgi-card bg-white p-4 p-md-5 rounded-3 shadow-sm d-inline-block text-center mx-auto" style="max-width: 760px; border: 1.5px solid #CBD5E1; border-top: 7px solid #1D3589 !important;">
          <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-royal text-white mb-3 mx-auto shadow-sm" style="width: 58px; height: 58px; font-size: 1.6rem; border: 2.5px solid #C5A059;">
            <i class="bi bi-shield-lock-fill"></i>
          </div>
          <div>
            <span class="badge bg-gold text-white px-3 py-1 rounded-pill small fw-bold text-uppercase mb-2">
              ${root.badge || 'Induk Holding'}
            </span>
          </div>
          <h2 class="fw-bold text-dark mb-2">${root.label}</h2>
          <div class="small fw-bold text-royal text-uppercase tracking-wide mb-3">${root.category || 'Holding & Investment Manager'}</div>
          <p class="text-secondary small mb-0 lh-lg" style="max-width: 620px;">
            ${root.description || ''}
          </p>
        </div>

        <!-- Connecting Line -->
        <div class="d-flex flex-column align-items-center my-3">
          <div style="width: 3px; height: 36px; background-color: #C5A059;"></div>
          <div class="badge bg-gold text-white px-3 py-1 rounded-pill small fw-bold">Sinergi Unit Usaha Mandiri</div>
          <div style="width: 3px; height: 24px; background-color: #C5A059;"></div>
        </div>

        <!-- Level 1 Children Grid -->
        <div class="row g-4 text-start justify-content-center mb-4">
          ${level1Html}
        </div>

        <!-- Connecting Line to Strategic Partner -->
        <div class="d-flex flex-column align-items-center my-3">
          <div style="width: 3px; height: 24px; background-color: #CBD5E1;"></div>
          <div class="badge bg-secondary text-white px-3 py-1 rounded-pill small fw-bold">Dukungan Offtake & Kemitraan Strategis</div>
          <div style="width: 3px; height: 24px; background-color: #CBD5E1;"></div>
        </div>

        <!-- Strategic Partner Row -->
        <div class="row text-start justify-content-center">
          ${partnerHtml}
        </div>

        <!-- Table Summary -->
        ${tableHtml}
      </div>
    `;
  }
};

window.MGIDiagrams = MGIDiagrams;
