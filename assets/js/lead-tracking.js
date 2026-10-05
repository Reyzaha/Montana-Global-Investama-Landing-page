/**
 * PT MONTANA GLOBAL INVESTAMA — LEAD TRACKING & WHATSAPP
 * - Menyimpan atribusi iklan (gclid / UTM) saat pengunjung pertama datang
 * - Memuat Google Ads tag (gtag.js) jika ID sudah diisi di Admin → Pengaturan
 * - Menampilkan tombol WhatsApp melayang jika nomor sudah diisi
 * - Menyediakan helper konversi untuk halaman terima kasih
 */
(function () {
  'use strict';

  const ATTR_KEY = 'mgi_attribution';
  const ATTR_TTL_DAYS = 90;
  const ATTR_PARAMS = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'gclid', 'gbraid', 'wbraid'];
  const API_BASE = (document.currentScript && document.currentScript.dataset.apiBase) || 'api/';

  let configPromise = null;
  let gtagReady = false;

  const MGILead = {
    /** Simpan parameter iklan dari URL (last non-empty click wins, berlaku 90 hari). */
    captureAttribution() {
      try {
        const params = new URLSearchParams(window.location.search);
        const found = {};
        ATTR_PARAMS.forEach((k) => {
          const v = params.get(k);
          if (v) found[k] = v.slice(0, 255);
        });

        const existing = this.getAttribution();
        if (Object.keys(found).length > 0) {
          const record = Object.assign({}, found, {
            landing_page: (window.location.pathname + window.location.search).slice(0, 255),
            referrer: (document.referrer || '').slice(0, 255),
            captured_at: Date.now()
          });
          localStorage.setItem(ATTR_KEY, JSON.stringify(record));
        } else if (!existing) {
          localStorage.setItem(ATTR_KEY, JSON.stringify({
            landing_page: window.location.pathname.slice(0, 255),
            referrer: (document.referrer || '').slice(0, 255),
            captured_at: Date.now()
          }));
        }
      } catch (e) { /* storage tidak tersedia: abaikan */ }
    },

    getAttribution() {
      try {
        const raw = localStorage.getItem(ATTR_KEY);
        if (!raw) return null;
        const data = JSON.parse(raw);
        if (!data.captured_at || (Date.now() - data.captured_at) > ATTR_TTL_DAYS * 86400000) {
          localStorage.removeItem(ATTR_KEY);
          return null;
        }
        return data;
      } catch (e) {
        return null;
      }
    },

    /** Ambil konfigurasi publik dari backend (sekali per halaman). */
    loadConfig() {
      if (!configPromise) {
        configPromise = fetch(API_BASE + 'leads.php?action=config', { credentials: 'same-origin' })
          .then((r) => r.json())
          .then((json) => (json && json.success && json.data) ? json.data : {})
          .catch(() => ({}));
      }
      return configPromise;
    },

    /** Muat gtag.js Google Ads jika ID tersedia. */
    initGtag(config) {
      if (gtagReady || !config || !config.google_ads_id) return;
      gtagReady = true;
      window.dataLayer = window.dataLayer || [];
      window.gtag = window.gtag || function () { window.dataLayer.push(arguments); };

      // Jika script sudah ada di <head>, jangan inject ulang
      if (document.querySelector('script[src*="googletagmanager.com/gtag/js"]')) {
        return;
      }

      window.gtag('js', new Date());
      window.gtag('config', config.google_ads_id);

      const s = document.createElement('script');
      s.async = true;
      s.src = 'https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(config.google_ads_id);
      document.head.appendChild(s);
    },

    /** Kirim konversi Google Ads (dipanggil di halaman terima kasih). */
    trackConversion(config, leadId) {
      if (!config || !config.google_ads_id || !config.google_ads_conversion_label || typeof window.gtag !== 'function') return false;
      window.gtag('event', 'conversion', {
        send_to: config.google_ads_id + '/' + config.google_ads_conversion_label,
        transaction_id: leadId ? 'lead-' + leadId : undefined
      });
      return true;
    },

    /** Event ringan untuk klik WhatsApp (bisa dijadikan konversi sekunder di Google Ads). */
    trackEvent(name, params) {
      if (typeof window.gtag === 'function') {
        window.gtag('event', name, params || {});
      }
    },

    waLink(number, text) {
      if (!number) return '';
      return 'https://wa.me/' + encodeURIComponent(number) + (text ? '?text=' + encodeURIComponent(text) : '');
    },

    /** Tombol WhatsApp melayang di pojok kanan bawah. */
    renderFloatingWA(config, message) {
      if (!config || !config.whatsapp_number || document.getElementById('mgiWaFloat')) return;

      const style = document.createElement('style');
      style.textContent = `
        #mgiWaFloat{position:fixed;right:20px;bottom:20px;z-index:1080;display:inline-flex;align-items:center;gap:10px;
          background:#25D366;color:#fff;border-radius:999px;padding:12px 18px 12px 14px;font-weight:700;font-size:.95rem;
          text-decoration:none;box-shadow:0 10px 30px rgba(37,211,102,.35),0 2px 8px rgba(0,0,0,.15);
          transition:transform .2s ease, box-shadow .2s ease;font-family:inherit}
        #mgiWaFloat:hover{transform:translateY(-3px);box-shadow:0 14px 36px rgba(37,211,102,.45),0 4px 10px rgba(0,0,0,.18);color:#fff}
        #mgiWaFloat svg{width:26px;height:26px;flex-shrink:0}
        #mgiWaFloat::before{content:'';position:absolute;inset:0;border-radius:inherit;box-shadow:0 0 0 0 rgba(37,211,102,.55);
          animation:mgiWaPulse 2.4s infinite}
        @keyframes mgiWaPulse{0%{box-shadow:0 0 0 0 rgba(37,211,102,.5)}70%{box-shadow:0 0 0 16px rgba(37,211,102,0)}100%{box-shadow:0 0 0 0 rgba(37,211,102,0)}}
        @media (max-width:575.98px){#mgiWaFloat .mgi-wa-label{display:none}#mgiWaFloat{padding:14px;right:16px;bottom:16px}}
        @media (prefers-reduced-motion:reduce){#mgiWaFloat::before{animation:none}}
        body.has-mobile-cta #mgiWaFloat{bottom:84px}
        @media (min-width:768px){body.has-mobile-cta #mgiWaFloat{bottom:20px}}
      `;
      document.head.appendChild(style);

      const a = document.createElement('a');
      a.id = 'mgiWaFloat';
      a.href = this.waLink(config.whatsapp_number, message || 'Halo tim Montana Global Investama, saya ingin konsultasi mengenai investasi alat berat.');
      a.target = '_blank';
      a.rel = 'noopener';
      a.setAttribute('aria-label', 'Chat WhatsApp dengan tim investasi');
      a.innerHTML = MGILead.waIcon() + '<span class="mgi-wa-label">Chat WhatsApp</span>';
      a.addEventListener('click', () => MGILead.trackEvent('whatsapp_click', { location: 'floating' }));
      document.body.appendChild(a);
    },

    waIcon() {
      return '<svg viewBox="0 0 32 32" fill="currentColor" aria-hidden="true"><path d="M16.04 3C9.41 3 4.03 8.38 4.03 15c0 2.12.55 4.18 1.6 6L4 29l8.2-1.6A12 12 0 0 0 16.04 27C22.66 27 28 21.62 28 15S22.66 3 16.04 3Zm0 21.8c-1.86 0-3.68-.5-5.27-1.45l-.38-.22-4.87.95.97-4.74-.25-.4A9.8 9.8 0 0 1 6.2 15c0-5.42 4.41-9.83 9.84-9.83 5.42 0 9.8 4.41 9.8 9.83 0 5.43-4.38 9.8-9.8 9.8Zm5.38-7.35c-.3-.15-1.75-.86-2.02-.96-.27-.1-.47-.15-.67.15-.2.3-.77.96-.94 1.16-.17.2-.35.22-.64.07-.3-.15-1.25-.46-2.38-1.47a8.9 8.9 0 0 1-1.65-2.05c-.17-.3-.02-.46.13-.6.13-.14.3-.35.44-.52.15-.18.2-.3.3-.5.1-.2.05-.37-.03-.52-.07-.15-.67-1.6-.91-2.2-.24-.57-.49-.5-.67-.5h-.57c-.2 0-.52.08-.79.37-.27.3-1.04 1.02-1.04 2.48 0 1.46 1.06 2.87 1.21 3.07.15.2 2.09 3.2 5.07 4.48.71.31 1.26.5 1.69.63.71.23 1.36.2 1.87.12.57-.08 1.75-.71 2-1.4.25-.7.25-1.29.17-1.41-.07-.13-.27-.2-.57-.35Z"/></svg>';
    },

    /** Inisialisasi standar untuk halaman publik. */
    init(options) {
      const opts = Object.assign({ floatingWA: true, waMessage: '' }, options || {});
      this.captureAttribution();
      return this.loadConfig().then((config) => {
        this.initGtag(config);
        if (opts.floatingWA) this.renderFloatingWA(config, opts.waMessage);
        return config;
      });
    }
  };

  window.MGILead = MGILead;

  // Auto-init kecuali halaman meminta kontrol manual (data-manual="true")
  const manual = document.currentScript && document.currentScript.dataset.manual === 'true';
  if (!manual) {
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', () => MGILead.init());
    } else {
      MGILead.init();
    }
  }
})();
