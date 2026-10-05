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

    /** Inisialisasi standar untuk halaman publik. */
    init(options) {
      this.captureAttribution();
      return this.loadConfig().then((config) => {
        this.initGtag(config);
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
