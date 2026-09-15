/**
 * PT MONTANA GLOBAL INVESTAMA — CORE RENDERER ENGINE
 * Modular asynchronous data fetcher and dynamic templating
 */

const MGI = {
  // Utility: Format Rupiah Currency
  formatRupiah: function (number, prefix = 'Rp ') {
    if (number === null || number === undefined || isNaN(number)) return 'Rp 0';
    const num = Math.round(Number(number));
    const str = num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    return prefix + str;
  },

  // Utility: Shorten large number representation
  formatRupiahCompact: function (number) {
    if (number >= 1000000000000) {
      return 'Rp ' + (number / 1000000000000).toFixed(1).replace('.0', '') + ' Triliun';
    } else if (number >= 1000000000) {
      return 'Rp ' + (number / 1000000000).toFixed(1).replace('.0', '') + ' Miliar';
    } else if (number >= 1000000) {
      return 'Rp ' + (number / 1000000).toFixed(1).replace('.0', '') + ' Juta';
    }
    return MGI.formatRupiah(number);
  },

  // Endpoint mapping: Maps static JSON paths to dynamic backend REST API endpoints
  apiMap: {
    'data/projects.json': 'api/projects.php',
    'data/company-profile.json': 'api/company-profile.php',
    'data/transformasi.json': 'api/transformasi.php',
    'data/preparation.json': 'api/preparation.php',
    'data/ekosistem.json': 'api/ekosistem.php'
  },

  // Utility: Fetch JSON data with API-First strategy and graceful static fallback
  fetchData: async function (endpoint) {
    const apiTarget = this.apiMap[endpoint] || endpoint;
    
    // 1. Try fetching from dynamic Backend API first
    if (apiTarget !== endpoint) {
      try {
        const apiResponse = await fetch(apiTarget, { cache: 'no-store' });
        if (apiResponse.ok) {
          const apiData = await apiResponse.json();
          // Check if response is standardized wrapper or raw object
          return (apiData && apiData.data && apiData.success !== undefined) ? apiData.data : apiData;
        }
      } catch (apiErr) {
        console.warn(`[MGI Renderer] Dynamic API (${apiTarget}) unavailable, falling back to static JSON (${endpoint})...`);
      }
    }

    // 2. Fallback to static JSON file
    try {
      const response = await fetch(endpoint, { cache: 'no-store' });
      if (!response.ok) {
        throw new Error(`Gagal memuat ${endpoint}: HTTP ${response.status}`);
      }
      return await response.json();
    } catch (err) {
      console.error('[MGI Renderer] Error fetching data:', err);
      return null;
    }
  },

  // Utility: Read URL query parameter
  getQueryParam: function (param) {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get(param);
  },

  // Utility: Render generic section (text, list, cards, table)
  renderSection: function (section) {
    if (!section) return '';

    if (section.type === 'text') {
      return `
        <div class="detail-section" id="${section.id || ''}">
          <h2 class="detail-section-title">${section.title || ''}</h2>
          <div class="detail-content-box">
            <p>${section.content || ''}</p>
            ${section.quote ? `<blockquote style="border-left: 3px solid var(--gold-400); padding-left: 16px; margin-top: 16px; color: var(--gold-300); font-style: italic;">"${section.quote}"</blockquote>` : ''}
          </div>
        </div>
      `;
    }

    if (section.type === 'cards') {
      const cardsHtml = (section.items || []).map(item => `
        <div class="sim-result-card" style="padding: 24px;">
          <div style="color: var(--gold-400); font-size: 1.5rem; margin-bottom: 12px;">★</div>
          <h4 style="font-size: 1.15rem; margin-bottom: 8px; color: #FFFFFF;">${item.name || ''}</h4>
          <p style="font-size: 0.92rem; color: var(--text-muted); line-height: 1.6;">${item.desc || ''}</p>
        </div>
      `).join('');

      return `
        <div class="detail-section" id="${section.id || ''}">
          <div class="section-header" style="text-align: left; margin-bottom: 30px;">
            <h2 class="detail-section-title">${section.title || ''}</h2>
            ${section.subtitle ? `<p class="section-subtitle">${section.subtitle}</p>` : ''}
          </div>
          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px;">
            ${cardsHtml}
          </div>
        </div>
      `;
    }

    if (section.type === 'list') {
      const itemsHtml = (section.items || []).map((item, idx) => `
        <li style="display: flex; gap: 14px; margin-bottom: 16px; font-size: 1rem; color: #CBD5E1;">
          <span style="display: inline-flex; align-items: center; justify-content: center; width: 26px; height: 26px; border-radius: 50%; background: rgba(212, 175, 55, 0.2); color: var(--gold-400); font-size: 0.8rem; font-weight: 700; flex-shrink: 0;">${idx + 1}</span>
          <span>${item}</span>
        </li>
      `).join('');

      return `
        <div class="detail-section" id="${section.id || ''}">
          <h2 class="detail-section-title">${section.title || ''}</h2>
          <div class="detail-content-box">
            <ul style="list-style: none; padding: 0; margin: 0;">${itemsHtml}</ul>
          </div>
        </div>
      `;
    }

    return '';
  }
};

window.MGI = MGI;
