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
  googleClientId: '674380740237-cuia5ftfji23ulm3htn1veb0eed3p7g9.apps.googleusercontent.com',

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
            if (json.data.google_client_id) {
              MGIAuth.googleClientId = json.data.google_client_id;
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

  // Login investor using Google ID Token Credential
  loginWithGoogle: async function (credential, accountType = 'perorangan') {
    try {
      const response = await fetch('api/auth.php?action=google_login', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-Token': MGIAuth.csrfToken || ''
        },
        body: JSON.stringify({
          credential: credential,
          account_type: accountType
        })
      });

      const res = await response.json();
      if (res && res.success && res.data && res.data.user) {
        this.setCurrentUserSession(res.data.user);
        if (res.data.csrf_token) {
          this.csrfToken = res.data.csrf_token;
        }
        return {
          success: true,
          user: res.data.user,
          message: res.message || 'Login dengan akun Google berhasil!'
        };
      } else {
        return {
          success: false,
          message: (res && res.message) ? res.message : 'Verifikasi login Google gagal.'
        };
      }
    } catch (err) {
      console.error('[MGIAuth] Google login error:', err);
      return {
        success: false,
        message: 'Koneksi ke server terganggu saat memproses login Google.'
      };
    }
  },

  // Render official Google Sign-In button into container
  renderGoogleSignInButton: function (containerId, onCredentialCallback, customOptions = {}) {
    const container = document.getElementById(containerId);
    if (!container) return;

    const initButton = () => {
      if (typeof google !== 'undefined' && google.accounts && google.accounts.id) {
        google.accounts.id.initialize({
          client_id: MGIAuth.googleClientId,
          callback: onCredentialCallback,
          auto_select: false,
          cancel_on_tap_outside: true
        });

        const defaultOptions = {
          theme: 'outline',
          size: 'large',
          type: 'standard',
          shape: 'rectangular',
          text: 'signin_with',
          logo_alignment: 'left',
          width: container.offsetWidth > 320 ? Math.min(container.offsetWidth, 400) : 300
        };

        google.accounts.id.renderButton(
          container,
          Object.assign({}, defaultOptions, customOptions)
        );
      } else {
        // Retry shortly if SDK is still downloading
        setTimeout(initButton, 200);
      }
    };

    initButton();
  },

  // Save current active user session
  setCurrentUserSession: function (user) {
    const isCorp = (user.type === 'perusahaan' || user.account_type === 'perusahaan' || user.accountType === 'corporate' || user.type === 'corporate');
    const name = isCorp ? (user.businessName || user.business_name || user.fullName || user.full_name) : (user.fullName || user.full_name || (user.email ? user.email.split('@')[0] : ''));
    const sessionData = {
      id: user.id || null,
      type: isCorp ? 'perusahaan' : 'perorangan',
      accountType: isCorp ? 'corporate' : 'individual',
      account_type: isCorp ? 'corporate' : 'individual',
      email: user.email,
      fullName: name,
      full_name: name,
      avatarUrl: user.avatarUrl || user.avatar_url || '',
      authProvider: user.authProvider || (user.google_id ? 'google' : 'email'),
      businessName: user.businessName || user.business_name || '',
      picName: user.picName || user.pic_name || '',
      phone: user.phone || user.companyPhone || '',
      businessActivity: user.businessActivity || user.business_activity || '',
      business_activity: user.businessActivity || user.business_activity || '',
      legalEntity: user.legalEntity || user.legal_entity || '',
      loginAt: new Date().toISOString()
    };
    localStorage.setItem(this.STORAGE_KEYS.CURRENT_USER, JSON.stringify(sessionData));
  },

  // Logout investor
  logout: function (redirectUrl = 'index.html') {
    try {
      fetch('api/auth.php?action=logout', { method: 'POST', credentials: 'same-origin' }).catch(() => {});
    } catch (e) {}
    localStorage.removeItem(this.STORAGE_KEYS.CURRENT_USER);
    sessionStorage.removeItem(this.STORAGE_KEYS.CURRENT_USER);
    sessionStorage.removeItem(this.STORAGE_KEYS.REDIRECT_TARGET);
    window.location.replace(redirectUrl);
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

  // Enforce Navigation Guard: Logged-in investors must stay inside investor dashboard
  enforceInvestorLock: function () {
    if (!this.isLoggedIn()) return false;

    const path = window.location.pathname.toLowerCase();
    const filename = path.substring(path.lastIndexOf('/') + 1) || 'index.html';
    
    // List of public landing pages that logged-in investors must not access
    const publicPages = [
      'index.html', 
      'invest.html', 
      'invest-detail.html', 
      'about.html', 
      'ekosistem.html', 
      'contact.html', 
      'login.html', 
      'register.html', 
      ''
    ];

    if (publicPages.includes(filename) && !filename.includes('investor-dashboard')) {
      const urlParams = new URLSearchParams(window.location.search);
      const projId = urlParams.get('id') || urlParams.get('project') || urlParams.get('open_project');
      let target = 'investor-dashboard.html';
      if (projId) {
        target += `?tab=katalog&open_project=${encodeURIComponent(projId)}`;
      }
      window.location.replace(target);
      return true;
    }
    return false;
  },

  // Handle Protected Project Detail Action: opens in-dashboard modal for logged-in users or prompts login
  handleProtectedDetail: function (projectId) {
    const dashboardTarget = `investor-dashboard.html?tab=katalog&open_project=${encodeURIComponent(projectId)}`;
    if (this.isLoggedIn()) {
      window.location.replace(dashboardTarget);
    } else {
      // Prompt modal with redirect leading straight into the in-dashboard project detail
      this.promptAuthModal(dashboardTarget);
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

// Initialize default storage immediately and enforce navigation lock for investors
MGIAuth.init();
MGIAuth.enforceInvestorLock();
window.MGIAuth = MGIAuth;

