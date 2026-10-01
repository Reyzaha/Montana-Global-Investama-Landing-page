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
    'data/cities.json': 'api/cities.php',
    'data/company-profile.json': 'api/company-profile.php',
    'data/transformasi.json': 'api/transformasi.php',
    'data/preparation.json': 'api/preparation.php',
    'data/ekosistem.json': 'api/ekosistem.php'
  },

  // Utility: Fetch JSON data with API-First strategy and graceful static fallback
  fetchData: async function (endpoint) {
    const apiTarget = this.apiMap[endpoint] || endpoint;
    
    // 1. Try fetching from dynamic Backend API first (with 1.5s timeout protection)
    if (apiTarget !== endpoint) {
      try {
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 1500);

        const apiResponse = await fetch(apiTarget, { 
          cache: 'no-store',
          signal: controller.signal 
        });
        clearTimeout(timeoutId);

        if (apiResponse.ok) {
          const apiData = await apiResponse.json();
          // Check if response is standardized wrapper or raw object
          return (apiData && apiData.data && apiData.success !== undefined) ? apiData.data : apiData;
        }
      } catch (apiErr) {
        console.warn(`[MGI Renderer] Dynamic API (${apiTarget}) unavailable or timed out, falling back to static JSON (${endpoint})...`);
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
  }
};

window.MGI = MGI;
