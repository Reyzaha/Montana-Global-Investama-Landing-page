/**
 * PT MONTANA GLOBAL INVESTAMA — CLIENT-SIDE AUTHENTICATION ENGINE
 * Handles Investor Session, Role Types (Perorangan vs Perusahaan),
 * Gated Access to Investment Project Details, and Redirect Management.
 */

const MGIAuth = {
  // Flag konfigurasi deployment: Set true untuk mengaktifkan kembali popup login/register & proteksi halaman detail
  REQUIRE_AUTH_FOR_DETAILS: true,

  STORAGE_KEYS: {
    CURRENT_USER: 'mgi_auth_user',
    REDIRECT_TARGET: 'mgi_redirect_target',
    CSRF_TOKEN: 'mgi_csrf_token'
  },

  csrfToken: null,

  // Synchronous or asynchronous bridge to backend API with graceful fallback
  syncApiRequest: function (action, payload) {
    try {
      const xhr = new XMLHttpRequest();
      xhr.open('POST', `api/auth.php?action=${action}`, false);
      xhr.setRequestHeader('Content-Type', 'application/json');
      if (this.csrfToken) {
        xhr.setRequestHeader('X-CSRF-Token', this.csrfToken);
      }
      xhr.send(JSON.stringify(payload));
      if (xhr.status >= 200 && xhr.status <= 500 && xhr.responseText) {
        try {
          const res = JSON.parse(xhr.responseText);
          if (res && res.data && res.data.csrf_token) {
            this.csrfToken = res.data.csrf_token;
          }
          return res;
        } catch (parseErr) {}
      }
    } catch (e) {
      console.warn(`[MGIAuth] Backend API (action=${action}) unavailable:`, e);
    }
    return null;
  },

  // Initialize and sync system settings and CSRF token
  init: function () {
    // Clean up any legacy plaintext passwords stored in previous versions
    try {
      localStorage.removeItem('mgi_users_db');
    } catch(e) {}

    // Sync live system settings & CSRF token from backend API
    try {
      fetch('api/auth.php?action=settings')
        .then(res => res.json())
        .then(json => {
          if (json && json.success && json.data) {
            if (json.data.require_auth_for_details !== undefined) {
              MGIAuth.REQUIRE_AUTH_FOR_DETAILS = json.data.require_auth_for_details;
            }
            if (json.data.csrf_token) {
              MGIAuth.csrfToken = json.data.csrf_token;
            }
          }
        })
        .catch(() => {});
    } catch (e) {}
  },

  // Check if current visitor is authenticated
  isLoggedIn: function () {
    return !!this.getCurrentUser();
  },

  // Retrieve current active user session
  getCurrentUser: function () {
    try {
      const session = localStorage.getItem(this.STORAGE_KEYS.CURRENT_USER);
      return session ? JSON.parse(session) : null;
    } catch (e) {
      return null;
    }
  },

  // Register a new investor (Perorangan or Perusahaan)
  register: function (userData) {
    // 1. Backend API authentication
    const apiRes = this.syncApiRequest('register', userData);
    if (apiRes) {
      if (apiRes.success) {
        this.setCurrentUserSession(apiRes.data.user);
        if (apiRes.data.csrf_token) {
          this.csrfToken = apiRes.data.csrf_token;
        }
        return {
          success: true,
          message: apiRes.message || 'Registrasi berhasil! Selamat datang di Portal Investor PT Montana Global Investama.'
        };
      } else {
        return {
          success: false,
          message: apiRes.message || 'Registrasi gagal.'
        };
      }
    }

    return {
      success: false,
      message: 'Server backend sedang tidak dapat dihubungi. Demi keamanan akun dan kepatuhan APU-PPT, pendaftaran memerlukan koneksi aktif ke server.'
    };
  },

  // Login investor by email, password, and account type
  login: function (type, email, password) {
    // 1. Backend API authentication
    const apiRes = this.syncApiRequest('login', { type, email, password });
    if (apiRes) {
      if (apiRes.success) {
        this.setCurrentUserSession(apiRes.data.user);
        if (apiRes.data.csrf_token) {
          this.csrfToken = apiRes.data.csrf_token;
        }
        return {
          success: true,
          user: apiRes.data.user,
          message: apiRes.message || 'Autentikasi berhasil! Mengalihkan ke portal investasi...'
        };
      } else {
        return {
          success: false,
          message: apiRes.message || 'Email atau kata sandi tidak valid.'
        };
      }
    }

    return {
      success: false,
      message: 'Server backend sedang tidak dapat dihubungi. Demi keamanan akun, autentikasi memerlukan koneksi aktif ke server.'
    };
  },

  // Save current active user session
  setCurrentUserSession: function (user) {
    const sessionData = {
      type: user.type,
      email: user.email,
      fullName: user.type === 'perusahaan' ? (user.businessName || user.fullName) : (user.fullName || user.email.split('@')[0]),
      picName: user.picName || '',
      phone: user.phone || '',
      businessActivity: user.businessActivity || user.business_activity || '',
      legalEntity: user.legalEntity || '',
      loginAt: new Date().toISOString()
    };
    localStorage.setItem(this.STORAGE_KEYS.CURRENT_USER, JSON.stringify(sessionData));
  },

  // Logout investor
  logout: function (redirectUrl = 'index.html') {
    try {
      fetch('api/auth.php?action=logout', { method: 'POST' }).catch(() => {});
    } catch (e) {}
    localStorage.removeItem(this.STORAGE_KEYS.CURRENT_USER);
    window.location.href = redirectUrl;
  },

  // Manage redirect target after login/registration
  setRedirectUrl: function (url) {
    if (url) {
      sessionStorage.setItem(this.STORAGE_KEYS.REDIRECT_TARGET, url);
    }
  },

  getRedirectUrl: function () {
    const url = sessionStorage.getItem(this.STORAGE_KEYS.REDIRECT_TARGET);
    sessionStorage.removeItem(this.STORAGE_KEYS.REDIRECT_TARGET);
    return url;
  },

  // Handle Protected Project Detail Action
  handleProtectedDetail: function (projectId) {
    const targetUrl = `invest-detail.html?id=${encodeURIComponent(projectId)}`;
    if (this.REQUIRE_AUTH_FOR_DETAILS && !this.isLoggedIn()) {
      this.promptAuthModal(targetUrl);
    } else {
      window.location.href = targetUrl;
    }
  },

  // Show Auth Prompt Modal
  promptAuthModal: function (targetUrl = '') {
    if (targetUrl) {
      this.setRedirectUrl(targetUrl);
    }

    // Ensure modal element exists in DOM
    let modalEl = document.getElementById('mgiAuthModal');
    if (!modalEl) {
      if (window.MGIComponents && typeof window.MGIComponents.renderAuthModal === 'function') {
        window.MGIComponents.renderAuthModal();
        modalEl = document.getElementById('mgiAuthModal');
      }
    }

    if (modalEl && window.bootstrap && window.bootstrap.Modal) {
      // Update target links in modal
      const loginBtn = modalEl.querySelector('#authModalLoginBtn');
      const registerBtn = modalEl.querySelector('#authModalRegisterBtn');
      const queryParam = targetUrl ? `?redirect=${encodeURIComponent(targetUrl)}` : '';

      if (loginBtn) loginBtn.href = `login.html${queryParam}`;
      if (registerBtn) registerBtn.href = `register.html${queryParam}`;

      const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
      modalInstance.show();
    } else {
      // Fallback if modal cannot be initialized
      const queryParam = targetUrl ? `?redirect=${encodeURIComponent(targetUrl)}` : '';
      window.location.href = `login.html${queryParam}`;
    }
  }
};

// Initialize default storage immediately
MGIAuth.init();
window.MGIAuth = MGIAuth;
