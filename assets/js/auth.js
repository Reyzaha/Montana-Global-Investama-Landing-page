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
  loginWithGoogle: async function (credential, accountType = 'perorangan', extraData = {}) {
    try {
      const payload = Object.assign({
        credential: credential,
        account_type: accountType
      }, extraData || {});

      const response = await fetch('api/auth.php?action=google_login', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-Token': MGIAuth.csrfToken || ''
        },
        body: JSON.stringify(payload)
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
          data: res.data,
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

  // Complete Google Signup (Biodata + Privacy Policy Consent)
  completeGoogleSignup: async function (payload) {
    try {
      const response = await fetch('api/auth.php?action=complete_google_signup', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-Token': MGIAuth.csrfToken || ''
        },
        body: JSON.stringify(payload)
      });
      const res = await response.json();
      if (res && res.success && res.data && res.data.user) {
        this.setCurrentUserSession(res.data.user);
        if (res.data.csrf_token) this.csrfToken = res.data.csrf_token;
        return { success: true, user: res.data.user, data: res.data, message: res.message };
      }
      return { success: false, message: (res && res.message) ? res.message : 'Gagal menyimpan biodata.' };
    } catch (err) {
      return { success: false, message: 'Koneksi ke server terganggu.' };
    }
  },

  // Accept Privacy Policy directly (for users with existing complete biodata)
  acceptPrivacyPolicy: async function (version = '1.0') {
    try {
      const response = await fetch('api/auth.php?action=accept_privacy_policy', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-Token': MGIAuth.csrfToken || ''
        },
        body: JSON.stringify({ version: version })
      });
      const res = await response.json();
      if (res && res.success) {
        const current = this.getCurrentUser();
        if (current) {
          current.privacyPolicyAccepted = true;
          current.privacyPolicyVersion = version;
          localStorage.setItem(this.STORAGE_KEYS.CURRENT_USER, JSON.stringify(current));
        }
        if (res.data && res.data.csrf_token) this.csrfToken = res.data.csrf_token;
        return { success: true, message: res.message };
      }
      return { success: false, message: (res && res.message) ? res.message : 'Gagal mencatat persetujuan.' };
    } catch (err) {
      return { success: false, message: 'Koneksi ke server terganggu.' };
    }
  },

  // Show Mandatory Google Onboarding Modal (Biodata -> Privacy Policy Consent)
  showOnboardingModal: function (options = {}) {
    const user = options.user || this.getCurrentUser() || {};
    const needsBiodata = options.needsBiodata !== undefined ? !!options.needsBiodata : !user.phone;
    const onComplete = typeof options.onComplete === 'function' ? options.onComplete : () => {
      window.location.href = MGIAuth.getRedirectUrl() || 'investor-dashboard.html';
    };

    // Ensure Single Source of Truth Privacy Policy script is loaded
    if (!window.MGIPrivacyPolicy) {
      const script = document.createElement('script');
      script.src = 'assets/js/privacy-policy-content.js';
      script.onload = () => MGIAuth.showOnboardingModal(options);
      document.head.appendChild(script);
      return;
    }

    // Remove existing modal if any
    const existing = document.getElementById('mgiOnboardingModal');
    if (existing) {
      try {
        const inst = bootstrap.Modal.getInstance(existing);
        if (inst) inst.dispose();
      } catch (e) {}
      existing.remove();
    }

    const modalId = 'mgiOnboardingModal';
    const modalDiv = document.createElement('div');
    modalDiv.className = 'modal fade';
    modalDiv.id = modalId;
    modalDiv.setAttribute('tabindex', '-1');
    modalDiv.setAttribute('data-bs-backdrop', 'static');
    modalDiv.setAttribute('data-bs-keyboard', 'false');
    modalDiv.setAttribute('aria-hidden', 'true');

    const defaultType = (user.type === 'perusahaan' || user.account_type === 'perusahaan' || user.accountType === 'corporate') ? 'perusahaan' : 'perorangan';
    const fullNameVal = user.fullName || user.full_name || '';

    modalDiv.innerHTML = `
      <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
          
          <!-- Header -->
          <div class="modal-header bg-royal text-white py-3 px-4 d-flex justify-content-between align-items-center">
            <div>
              <span class="badge bg-gold text-dark small fw-bold px-2 py-1 mb-1">
                <i class="bi bi-shield-check me-1"></i> Kepatuhan UU PDP No. 27/2022
              </span>
              <h5 class="modal-title fw-bold text-white mb-0" id="onboardingModalTitle">
                ${needsBiodata ? 'Lengkapi Profil &amp; Kebijakan Privasi' : 'Persetujuan Kebijakan Privasi'}
              </h5>
            </div>
          </div>

          <!-- Body -->
          <div class="modal-body p-4 bg-light">
            <div id="onboardingAlertContainer"></div>

            <!-- STEP 1: FORM PENGISIAN BIODATA (Tampil jika needsBiodata = true) -->
            <div id="onboardingStepBiodata" style="${needsBiodata ? 'display:block;' : 'display:none;'}">
              <div class="p-3 bg-white rounded-3 border border-subtle mb-3">
                <div class="d-flex align-items-center gap-2 mb-2 text-royal fw-bold">
                  <i class="bi bi-person-lines-fill fs-5 text-gold"></i>
                  <span>Lengkapi Data Identitas Investor</span>
                </div>
                <p class="text-secondary small mb-0 lh-base">
                  Sesuai prinsip kehati-hatian APU-PPT dan tata kelola penempatan modal, seluruh pemodal wajib melengkapi data kontak dan aktivitas usaha resmi sebelum mengakses portal portofolio.
                </p>
              </div>

              <!-- Selector Tipe Akun -->
              <div class="mb-3">
                <label class="form-label small fw-bold text-dark">Kategori Pemodal</label>
                <div class="btn-group w-100" role="group">
                  <input type="radio" class="btn-check" name="onbAccountType" id="onbTypePerorangan" value="perorangan" ${defaultType === 'perorangan' ? 'checked' : ''}>
                  <label class="btn btn-outline-primary fw-semibold small py-2" for="onbTypePerorangan">
                    <i class="bi bi-person-fill me-1"></i> Perorangan (Individu)
                  </label>
                  <input type="radio" class="btn-check" name="onbAccountType" id="onbTypePerusahaan" value="perusahaan" ${defaultType === 'perusahaan' ? 'checked' : ''}>
                  <label class="btn btn-outline-primary fw-semibold small py-2" for="onbTypePerusahaan">
                    <i class="bi bi-building me-1"></i> Perusahaan (Institusi)
                  </label>
                </div>
              </div>

              <!-- Fields Perorangan -->
              <div id="onbWrapPerorangan" style="${defaultType === 'perorangan' ? 'display:block;' : 'display:none;'}">
                <div class="row g-3">
                  <div class="col-12 col-md-6">
                    <label class="form-label small fw-bold text-dark">Nama Lengkap Sesuai KTP <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="onbFullName" value="${MGIComponents.escapeHtml(fullNameVal)}" placeholder="Nama lengkap Anda" required>
                    <div class="invalid-feedback">Nama lengkap wajib diisi.</div>
                  </div>
                  <div class="col-12 col-md-6">
                    <label class="form-label small fw-bold text-dark">Nomor Telepon / WhatsApp <span class="text-danger">*</span></label>
                    <input type="tel" class="form-control" id="onbPhone" placeholder="08xxxxxxxxxx" required>
                    <div class="invalid-feedback">Nomor telepon / WhatsApp wajib diisi.</div>
                  </div>
                  <div class="col-12 col-md-6">
                    <label class="form-label small fw-bold text-dark">Kewarganegaraan <span class="text-danger">*</span></label>
                    <select class="form-select" id="onbCitizenship">
                      <option value="Indonesia (WNI)" selected>Indonesia (WNI)</option>
                      <option value="Warga Negara Asing (WNA)">Warga Negara Asing (WNA)</option>
                    </select>
                  </div>
                  <div class="col-12 col-md-6">
                    <label class="form-label small fw-bold text-dark">Bidang Usaha / Profesi <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="onbBusinessActivity" placeholder="Contoh: Wiraswasta / Profesional / Swasta" required>
                    <div class="invalid-feedback">Bidang usaha atau profesi wajib diisi.</div>
                  </div>
                </div>
              </div>

              <!-- Fields Perusahaan -->
              <div id="onbWrapPerusahaan" style="${defaultType === 'perusahaan' ? 'display:block;' : 'display:none;'}">
                <div class="row g-3">
                  <div class="col-12 col-md-6">
                    <label class="form-label small fw-bold text-dark">Nama Badan Usaha / PT <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="onbBusinessName" placeholder="Contoh: PT Investama Makmur Jaya" required>
                    <div class="invalid-feedback">Nama badan usaha wajib diisi.</div>
                  </div>
                  <div class="col-12 col-md-6">
                    <label class="form-label small fw-bold text-dark">Bentuk Entitas Hukum <span class="text-danger">*</span></label>
                    <select class="form-select" id="onbLegalEntity">
                      <option value="Perseroan Terbatas (PT)" selected>Perseroan Terbatas (PT)</option>
                      <option value="Persekutuan Komanditer (CV)">Persekutuan Komanditer (CV)</option>
                      <option value="PT Perorangan">PT Perorangan</option>
                      <option value="Usaha Dagang (UD)">Usaha Dagang (UD)</option>
                    </select>
                  </div>
                  <div class="col-12 col-md-6">
                    <label class="form-label small fw-bold text-dark">Nama Kontak Person (PIC) <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="onbPicName" value="${MGIComponents.escapeHtml(fullNameVal)}" placeholder="Nama perwakilan PIC" required>
                    <div class="invalid-feedback">Nama PIC wajib diisi.</div>
                  </div>
                  <div class="col-12 col-md-6">
                    <label class="form-label small fw-bold text-dark">Jabatan Kontak Person <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="onbPicPosition" value="Direktur Utama" placeholder="Contoh: Direktur Utama" required>
                    <div class="invalid-feedback">Jabatan PIC wajib diisi.</div>
                  </div>
                  <div class="col-12 col-md-6">
                    <label class="form-label small fw-bold text-dark">Nomor Telepon Kantor / PIC <span class="text-danger">*</span></label>
                    <input type="tel" class="form-control" id="onbCompanyPhone" placeholder="08xxxxxxxxxx / 021-xxxx" required>
                    <div class="invalid-feedback">Nomor telepon kantor / PIC wajib diisi.</div>
                  </div>
                  <div class="col-12 col-md-6">
                    <label class="form-label small fw-bold text-dark">Bidang Usaha Perusahaan <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="onbCompanyBusinessActivity" value="Investasi Sektor Riil" placeholder="Contoh: Konstruksi / Logistik" required>
                    <div class="invalid-feedback">Bidang usaha perusahaan wajib diisi.</div>
                  </div>
                  <div class="col-12">
                    <label class="form-label small fw-bold text-dark">Alamat Domisili Kantor Operasional <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="onbCompanyAddress" rows="2" placeholder="Alamat lengkap kantor operasional perusahaan" required></textarea>
                    <div class="invalid-feedback">Alamat kantor operasional wajib diisi.</div>
                  </div>
                  <div class="col-12">
                    <label class="form-label small fw-bold text-dark">Kisaran Omzet Tahunan Usaha <span class="text-danger">*</span></label>
                    <select class="form-select" id="onbAnnualTurnover">
                      <option value="Di bawah Rp500 Juta">Di bawah Rp500 Juta</option>
                      <option value="Rp500 Juta – Rp2,5 Miliar">Rp500 Juta – Rp2,5 Miliar</option>
                      <option value="Rp2,5 Miliar – Rp10 Miliar">Rp2,5 Miliar – Rp10 Miliar</option>
                      <option value="Rp10 Miliar – Rp50 Miliar" selected>Rp10 Miliar – Rp50 Miliar</option>
                      <option value="Rp50 Miliar – Rp250 Miliar">Rp50 Miliar – Rp250 Miliar</option>
                      <option value="Di atas Rp250 Miliar">Di atas Rp250 Miliar</option>
                    </select>
                  </div>
                </div>
              </div>

              <div class="mt-4 pt-2 border-top border-subtle d-flex justify-content-end">
                <button type="button" class="btn btn-royal text-white px-4 py-2 rounded-pill fw-semibold" id="btnNextToPrivacy">
                  Lanjutkan ke Kebijakan Privasi <i class="bi bi-arrow-right ms-1"></i>
                </button>
              </div>
            </div>

            <!-- STEP 2: POPUP KEBIJAKAN PRIVASI (Scrollable, Checkbox Opt-in) -->
            <div id="onboardingStepPrivacy" style="${needsBiodata ? 'display:none;' : 'display:block;'}">
              <div class="p-3 bg-white rounded-3 border border-subtle mb-3">
                <div class="d-flex align-items-center gap-2 mb-1 text-royal fw-bold">
                  <i class="bi bi-file-earmark-medical-fill fs-5 text-gold"></i>
                  <span>Persetujuan Pelindungan Data Pribadi (UU PDP No. 27 Tahun 2022)</span>
                </div>
                <p class="text-secondary small mb-0 lh-base">
                  Silakan tinjau ringkasan dan ketentuan resmi pengolahan data pribadi PT Montana Global Investama di bawah ini sebelum menyetujui.
                </p>
              </div>

              <!-- Scrollable Policy Content -->
              <div class="p-3 p-md-4 bg-white rounded-3 border border-subtle mb-3 shadow-inner" style="max-height: 360px; overflow-y: auto;" id="onboardingPolicyScrollBox">
                ${MGIPrivacyPolicy.getHtml({ isModal: true, showHeader: true })}
              </div>

              <!-- Opt-in Checkbox (Unchecked by default per UU PDP Section 6) -->
              <div class="p-3 bg-white rounded-3 border border-subtle mb-3">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="onboardingPrivacyCheckbox">
                  <label class="form-check-label small text-dark fw-semibold lh-base" for="onboardingPrivacyCheckbox">
                    Saya telah membaca, memahami, dan menyetujui seluruh ketentuan dalam Kebijakan Privasi PT Montana Global Investama (Versi ${MGIPrivacyPolicy.VERSION}).
                  </label>
                </div>
              </div>

              <!-- Footer Buttons -->
              <div class="d-flex justify-content-between align-items-center pt-2 border-top border-subtle">
                ${needsBiodata ? `
                  <button type="button" class="btn btn-outline-secondary px-3 py-2 rounded-pill small fw-semibold" id="btnBackToBiodata">
                    <i class="bi bi-arrow-left me-1"></i> Ubah Biodata
                  </button>
                ` : '<div></div>'}
                <button type="button" class="btn btn-gold text-white px-4 py-2.5 rounded-pill fw-bold shadow-sm" id="btnSubmitOnboarding" disabled>
                  <i class="bi bi-check-circle-fill me-1"></i> Setuju &amp; Lanjutkan ke Dashboard
                </button>
              </div>
            </div>

          </div>
        </div>
      </div>
    `;

    document.body.appendChild(modalDiv);

    // Wire up events
    const radioPerorangan = modalDiv.querySelector('#onbTypePerorangan');
    const radioPerusahaan = modalDiv.querySelector('#onbTypePerusahaan');
    const wrapPerorangan = modalDiv.querySelector('#onbWrapPerorangan');
    const wrapPerusahaan = modalDiv.querySelector('#onbWrapPerusahaan');

    if (radioPerorangan && radioPerusahaan) {
      radioPerorangan.addEventListener('change', () => {
        if (radioPerorangan.checked) {
          wrapPerorangan.style.display = 'block';
          wrapPerusahaan.style.display = 'none';
        }
      });
      radioPerusahaan.addEventListener('change', () => {
        if (radioPerusahaan.checked) {
          wrapPerusahaan.style.display = 'block';
          wrapPerorangan.style.display = 'none';
        }
      });
    }

    const stepBio = modalDiv.querySelector('#onboardingStepBiodata');
    const stepPriv = modalDiv.querySelector('#onboardingStepPrivacy');
    const btnNext = modalDiv.querySelector('#btnNextToPrivacy');
    const btnBack = modalDiv.querySelector('#btnBackToBiodata');
    const chkPrivacy = modalDiv.querySelector('#onboardingPrivacyCheckbox');
    const btnSubmit = modalDiv.querySelector('#btnSubmitOnboarding');
    const alertBox = modalDiv.querySelector('#onboardingAlertContainer');

    // Toggle submit button based on checkbox
    if (chkPrivacy && btnSubmit) {
      chkPrivacy.addEventListener('change', () => {
        btnSubmit.disabled = !chkPrivacy.checked;
      });
    }

    // Next button from Biodata to Privacy
    if (btnNext) {
      btnNext.addEventListener('click', () => {
        const selectedType = modalDiv.querySelector('input[name="onbAccountType"]:checked')?.value || 'perorangan';
        let isValid = true;
        alertBox.innerHTML = '';

        if (selectedType === 'perorangan') {
          const fn = modalDiv.querySelector('#onbFullName');
          const ph = modalDiv.querySelector('#onbPhone');
          const ba = modalDiv.querySelector('#onbBusinessActivity');

          [fn, ph, ba].forEach(el => {
            if (!el || !el.value.trim()) {
              if (el) el.classList.add('is-invalid');
              isValid = false;
            } else {
              el.classList.remove('is-invalid');
            }
          });
        } else {
          const bn = modalDiv.querySelector('#onbBusinessName');
          const pn = modalDiv.querySelector('#onbPicName');
          const pp = modalDiv.querySelector('#onbPicPosition');
          const cp = modalDiv.querySelector('#onbCompanyPhone');
          const ca = modalDiv.querySelector('#onbCompanyAddress');
          const cba = modalDiv.querySelector('#onbCompanyBusinessActivity');

          [bn, pn, pp, cp, ca, cba].forEach(el => {
            if (!el || !el.value.trim()) {
              if (el) el.classList.add('is-invalid');
              isValid = false;
            } else {
              el.classList.remove('is-invalid');
            }
          });
        }

        if (!isValid) {
          alertBox.innerHTML = `
            <div class="alert alert-danger border-0 p-3 small rounded-3 mb-3 d-flex align-items-center gap-2">
              <i class="bi bi-exclamation-circle-fill text-danger fs-5"></i>
              <div>Harap lengkapi semua kolom wajib bertanda bintang (*) sebelum melanjutkan.</div>
            </div>
          `;
          return;
        }

        stepBio.style.display = 'none';
        stepPriv.style.display = 'block';
        const scrollBox = modalDiv.querySelector('#onboardingPolicyScrollBox');
        if (scrollBox) scrollBox.scrollTop = 0;
      });
    }

    // Back button
    if (btnBack) {
      btnBack.addEventListener('click', () => {
        stepPriv.style.display = 'none';
        stepBio.style.display = 'block';
      });
    }

    // Submit handler
    if (btnSubmit) {
      btnSubmit.addEventListener('click', async () => {
        if (!chkPrivacy.checked) {
          alertBox.innerHTML = `
            <div class="alert alert-warning border-0 p-3 small rounded-3 mb-3">
              Anda wajib mencentang kotak persetujuan Kebijakan Privasi sebelum melanjutkan.
            </div>
          `;
          return;
        }

        btnSubmit.disabled = true;
        btnSubmit.innerHTML = `
          <span class="spinner-border spinner-border-sm me-1" role="status"></span>
          Menyimpan persetujuan...
        `;

        const selectedType = modalDiv.querySelector('input[name="onbAccountType"]:checked')?.value || defaultType;

        if (needsBiodata) {
          // Prepare payload
          const payload = {
            account_type: selectedType,
            privacy_policy_accepted: 1,
            privacy_policy_version: MGIPrivacyPolicy.VERSION
          };

          if (selectedType === 'perorangan') {
            payload.full_name = modalDiv.querySelector('#onbFullName')?.value?.trim();
            payload.phone = modalDiv.querySelector('#onbPhone')?.value?.trim();
            payload.citizenship = modalDiv.querySelector('#onbCitizenship')?.value;
            payload.business_activity = modalDiv.querySelector('#onbBusinessActivity')?.value?.trim();
          } else {
            payload.business_name = modalDiv.querySelector('#onbBusinessName')?.value?.trim();
            payload.legal_entity = modalDiv.querySelector('#onbLegalEntity')?.value;
            payload.company_address = modalDiv.querySelector('#onbCompanyAddress')?.value?.trim();
            payload.pic_name = modalDiv.querySelector('#onbPicName')?.value?.trim();
            payload.pic_position = modalDiv.querySelector('#onbPicPosition')?.value?.trim();
            payload.company_phone = modalDiv.querySelector('#onbCompanyPhone')?.value?.trim();
            payload.phone = payload.company_phone;
            payload.annual_turnover = modalDiv.querySelector('#onbAnnualTurnover')?.value;
            payload.business_activity = modalDiv.querySelector('#onbCompanyBusinessActivity')?.value?.trim();
          }

          const res = await MGIAuth.completeGoogleSignup(payload);
          if (res.success) {
            alertBox.innerHTML = `
              <div class="alert alert-success border-0 p-3 small rounded-3 mb-3">
                ${res.message || 'Biodata &amp; persetujuan berhasil disimpan! Mengalihkan ke Dashboard...'}
              </div>
            `;
            setTimeout(() => {
              const bModal = bootstrap.Modal.getInstance(modalDiv);
              if (bModal) bModal.hide();
              onComplete(res.user);
            }, 600);
          } else {
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = `<i class="bi bi-check-circle-fill me-1"></i> Setuju &amp; Lanjutkan ke Dashboard`;
            alertBox.innerHTML = `
              <div class="alert alert-danger border-0 p-3 small rounded-3 mb-3">
                ${res.message || 'Gagal menyimpan data ke server.'}
              </div>
            `;
          }
        } else {
          // Only privacy acceptance
          const res = await MGIAuth.acceptPrivacyPolicy(MGIPrivacyPolicy.VERSION);
          if (res.success) {
            alertBox.innerHTML = `
              <div class="alert alert-success border-0 p-3 small rounded-3 mb-3">
                Persetujuan Kebijakan Privasi berhasil dicatat. Mengalihkan ke Dashboard...
              </div>
            `;
            setTimeout(() => {
              const bModal = bootstrap.Modal.getInstance(modalDiv);
              if (bModal) bModal.hide();
              onComplete(MGIAuth.getCurrentUser());
            }, 600);
          } else {
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = `<i class="bi bi-check-circle-fill me-1"></i> Setuju &amp; Lanjutkan ke Dashboard`;
            alertBox.innerHTML = `
              <div class="alert alert-danger border-0 p-3 small rounded-3 mb-3">
                ${res.message || 'Gagal menyimpan persetujuan.'}
              </div>
            `;
          }
        }
      });
    }

    const modalInstance = new bootstrap.Modal(modalDiv, {
      backdrop: 'static',
      keyboard: false
    });
    modalInstance.show();
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

