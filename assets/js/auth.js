/**
 * PT MONTANA GLOBAL INVESTAMA — CLIENT-SIDE AUTHENTICATION ENGINE
 * Handles Investor Session, Role Types (Perorangan vs Perusahaan),
 * Gated Access to Investment Project Details, and Redirect Management.
 */

const MGIAuth = {
  STORAGE_KEYS: {
    CURRENT_USER: 'mgi_auth_user',
    USERS_DB: 'mgi_users_db',
    REDIRECT_TARGET: 'mgi_redirect_target'
  },

  // Initialize demo accounts if no accounts exist in storage
  init: function () {
    const existing = localStorage.getItem(this.STORAGE_KEYS.USERS_DB);
    if (!existing) {
      const demoUsers = [
        {
          type: 'perorangan',
          email: 'investor@gmail.com',
          password: 'password123',
          citizenship: 'Indonesia (WNI)',
          phone: '081234567890',
          fullName: 'Budi Pratama'
        },
        {
          type: 'perusahaan',
          email: 'corporate@holding.com',
          password: 'password123',
          businessName: 'PT Nusantara Capital Group',
          companyAddress: 'Equity Tower Lt. 28, SCBD, Jakarta Selatan',
          picName: 'Hendra Wijaya, S.E., M.B.A.',
          phone: '081198765432',
          picPosition: 'Managing Director',
          annualTurnover: 'Rp10 Miliar – Rp50 Miliar',
          legalEntity: 'Perseroan Terbatas (PT)'
        }
      ];
      localStorage.setItem(this.STORAGE_KEYS.USERS_DB, JSON.stringify(demoUsers));
    }
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
    this.init();
    const users = JSON.parse(localStorage.getItem(this.STORAGE_KEYS.USERS_DB) || '[]');

    // Check if email already registered
    const exists = users.find(u => u.email.toLowerCase() === userData.email.toLowerCase());
    if (exists) {
      return {
        success: false,
        message: 'Alamat email ini sudah terdaftar. Silakan gunakan email lain atau masuk ke akun Anda.'
      };
    }

    // Save new user
    users.push(userData);
    localStorage.setItem(this.STORAGE_KEYS.USERS_DB, JSON.stringify(users));

    // Automatically sign in the user
    this.setCurrentUserSession(userData);

    return {
      success: true,
      message: 'Registrasi berhasil! Selamat datang di Portal Investor PT Montana Global Investama.'
    };
  },

  // Login investor by email, password, and account type
  login: function (type, email, password) {
    this.init();
    const users = JSON.parse(localStorage.getItem(this.STORAGE_KEYS.USERS_DB) || '[]');

    const user = users.find(u =>
      u.email.toLowerCase() === email.toLowerCase().trim() &&
      u.password === password &&
      (!type || u.type === type)
    );

    if (!user) {
      // Check if user exists under a different account type
      const otherTypeUser = users.find(u =>
        u.email.toLowerCase() === email.toLowerCase().trim() &&
        u.password === password
      );

      if (otherTypeUser) {
        const typeLabel = otherTypeUser.type === 'perusahaan' ? 'Perusahaan' : 'Perorangan';
        return {
          success: false,
          message: `Akun ini terdaftar sebagai tipe "${typeLabel}". Silakan pilih tab "${typeLabel}" untuk masuk.`
        };
      }

      return {
        success: false,
        message: 'Email atau kata sandi tidak valid. Pastikan kombinasi akun Anda benar.'
      };
    }

    // Set active session
    this.setCurrentUserSession(user);

    return {
      success: true,
      user: user,
      message: 'Autentikasi berhasil! Mengalihkan ke portal investasi...'
    };
  },

  // Save current active user session
  setCurrentUserSession: function (user) {
    const sessionData = {
      type: user.type,
      email: user.email,
      fullName: user.type === 'perusahaan' ? user.businessName : (user.fullName || user.email.split('@')[0]),
      picName: user.picName || '',
      phone: user.phone || '',
      legalEntity: user.legalEntity || '',
      loginAt: new Date().toISOString()
    };
    localStorage.setItem(this.STORAGE_KEYS.CURRENT_USER, JSON.stringify(sessionData));
  },

  // Logout investor
  logout: function (redirectUrl = 'index.html') {
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
    if (this.isLoggedIn()) {
      window.location.href = targetUrl;
    } else {
      this.promptAuthModal(targetUrl);
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
