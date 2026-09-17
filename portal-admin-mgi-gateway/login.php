<?php
require_once __DIR__ . '/../backend/helpers/auth_helper.php';

// Redirect if already logged in
if (getAdminSession()) {
    header('Location: index.php');
    exit;
}

$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login Administrator &amp; MFA — PT Montana Global Investama</title>
  <!-- Favicon Icons -->
  <link rel="icon" type="image/png" href="../assets/img/mgi-official-logo.png">
  <link rel="shortcut icon" href="../favicon.ico" type="image/x-icon">
  <link rel="apple-touch-icon" href="../assets/img/mgi-official-logo.png">

  <!-- Google Fonts Outfit -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <!-- QRCode.js library for rendering QR Code offline/in-browser -->
  <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
  <link rel="stylesheet" href="assets/admin.css">
  <style>
    body {
      background: radial-gradient(circle at top right, #1E3380, #0E1838);
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      margin: 0;
      padding: 20px;
    }
    .login-card {
      background: #FFFFFF;
      border-radius: 16px;
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.35);
      width: 100%;
      max-width: 460px;
      overflow: hidden;
      border: 1px solid rgba(255, 255, 255, 0.1);
      transition: max-width 0.3s ease;
    }
    .login-header {
      background: #142563;
      padding: 28px 24px;
      text-align: center;
      color: #FFFFFF;
      position: relative;
    }
    .login-header::after {
      content: "";
      position: absolute;
      bottom: 0;
      left: 0;
      width: 100%;
      height: 4px;
      background: #C5A059;
    }
    .totp-input {
      letter-spacing: 0.5rem;
      font-size: 1.5rem;
      text-align: center;
      font-weight: 700;
    }
    .qr-container {
      background: #ffffff;
      padding: 12px;
      display: inline-block;
      border-radius: 12px;
      border: 2px solid #E2E8F0;
      box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
  </style>
</head>
<body>

  <div class="login-card">
    <div class="login-header">
      <div class="d-inline-flex align-items-center justify-content-center bg-white rounded-circle p-2 mb-2 shadow-sm" style="width: 52px; height: 52px;">
        <i class="bi bi-shield-lock-fill text-primary fs-3" style="color: #142563 !important;"></i>
      </div>
      <h4 class="fw-bold mb-1">MGI Superadmin</h4>
      <p class="text-white-50 small mb-0" id="headerSubtitle">Portal Kendali &amp; Manajemen Data Investasi</p>
    </div>

    <div class="p-4 p-sm-5">
      <div id="loginAlert" class="alert alert-danger d-none py-2 small" role="alert"></div>

      <?php if ($error === 'auth_required'): ?>
        <div class="alert alert-warning py-2 small mb-3">
          <i class="bi bi-exclamation-triangle-fill me-1"></i> Sesi Anda telah berakhir. Silakan masuk kembali.
        </div>
      <?php endif; ?>

      <!-- STEP 1: LOGIN KREDENSIAL -->
      <form id="adminLoginForm">
        <input type="hidden" id="csrfToken" value="<?= htmlspecialchars(getCsrfToken()) ?>">
        <div class="mb-3">
          <label class="form-label">Username atau Email Admin</label>
          <div class="input-group">
            <span class="input-group-text bg-light border-end-0"><i class="bi bi-person text-muted"></i></span>
            <input type="text" id="adminUsername" class="form-control border-start-0" placeholder="Masukkan username atau email" required autofocus>
          </div>
        </div>

        <div class="mb-4">
          <label class="form-label mb-1">Kata Sandi</label>
          <div class="input-group">
            <span class="input-group-text bg-light border-end-0"><i class="bi bi-key text-muted"></i></span>
            <input type="password" id="adminPassword" class="form-control border-start-0" placeholder="••••••••" required>
            <button class="btn btn-light border border-start-0 text-muted" type="button" onclick="togglePassword()">
              <i class="bi bi-eye" id="eyeIcon"></i>
            </button>
          </div>
        </div>

        <button type="submit" id="btnLogin" class="btn btn-mgi-primary w-100 py-2 d-flex align-items-center justify-content-center gap-2">
          <span>Lanjutkan Autentikasi</span>
          <i class="bi bi-arrow-right"></i>
        </button>
      </form>

      <!-- STEP 2: MFA / GOOGLE AUTHENTICATOR -->
      <div id="mfaSection" class="d-none">
        <div id="mfaSetupNotice" class="text-center mb-3 d-none">
          <span class="badge bg-warning text-dark px-3 py-1 mb-2">
            <i class="bi bi-qr-code-scan me-1"></i> Setup Google Authenticator
          </span>
          <p class="small text-muted mb-2">Buka aplikasi <strong>Google Authenticator</strong> di HP Anda, pilih menu Scan QR, lalu scan barcode di bawah:</p>
          <div class="qr-container mb-2" id="qrcodeCanvas"></div>
          <div class="bg-light p-2 rounded small text-muted font-monospace user-select-all mb-3" style="font-size: 0.8rem;">
            Secret Key: <strong id="mfaSecretText" class="text-dark"></strong>
          </div>
        </div>

        <div id="mfaExistingNotice" class="text-center mb-3 d-none">
          <div class="d-inline-flex align-items-center justify-content-center bg-light rounded-circle p-3 mb-2 text-primary">
            <i class="bi bi-phone fs-2 text-mgi-primary"></i>
          </div>
          <h5 class="fw-bold mb-1">Verifikasi 2 Langkah (MFA)</h5>
          <p class="small text-muted">Masukkan 6 digit kode yang tertera di aplikasi <strong>Google Authenticator</strong> Anda.</p>
        </div>

        <form id="mfaVerifyForm">
          <div class="mb-3">
            <label class="form-label text-center d-block small fw-bold text-uppercase text-secondary">Kode 6 Digit</label>
            <input type="text" id="totpCode" class="form-control totp-input" maxlength="6" pattern="[0-9]{6}" inputmode="numeric" placeholder="000000" required autocomplete="one-time-code" autofocus>
          </div>

          <button type="submit" id="btnVerifyMfa" class="btn btn-mgi-primary w-100 py-2 mb-2 d-flex align-items-center justify-content-center gap-2">
            <span>Verifikasi &amp; Masuk</span>
            <i class="bi bi-check-circle-fill"></i>
          </button>

          <button type="button" class="btn btn-link text-muted w-100 btn-sm text-decoration-none" onclick="resetToLogin()">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Form Kredensial
          </button>
        </form>
      </div>

      <div class="mt-4 pt-3 border-top text-center">
        <a href="../index.html" class="text-decoration-none text-muted small">
          <i class="bi bi-arrow-left me-1"></i> Kembali ke Landing Page Publik
        </a>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    let qrcodeInstance = null;

    function togglePassword() {
      const pass = document.getElementById('adminPassword');
      const eye = document.getElementById('eyeIcon');
      if (pass.type === 'password') {
        pass.type = 'text';
        eye.classList.replace('bi-eye', 'bi-eye-slash');
      } else {
        pass.type = 'password';
        eye.classList.replace('bi-eye-slash', 'bi-eye');
      }
    }

    function resetToLogin() {
      document.getElementById('mfaSection').classList.add('d-none');
      document.getElementById('adminLoginForm').classList.remove('d-none');
      document.getElementById('headerSubtitle').textContent = 'Portal Kendali & Manajemen Data Investasi';
      document.getElementById('loginAlert').classList.add('d-none');
      document.getElementById('adminPassword').value = '';
      document.getElementById('adminPassword').focus();
    }

    // TAHAP 1: KREDENSIAL LOGIN
    document.getElementById('adminLoginForm').addEventListener('submit', async (e) => {
      e.preventDefault();
      const btn = document.getElementById('btnLogin');
      const alertBox = document.getElementById('loginAlert');
      const username = document.getElementById('adminUsername').value.trim();
      const password = document.getElementById('adminPassword').value;
      const csrfToken = document.getElementById('csrfToken').value;

      btn.disabled = true;
      btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span> Memverifikasi...`;
      alertBox.classList.add('d-none');

      try {
        const res = await fetch('../api/admin/login.php?action=login', {
          method: 'POST',
          headers: { 
            'Content-Type': 'application/json',
            'X-CSRF-Token': csrfToken
          },
          body: JSON.stringify({ username, password, csrf_token: csrfToken })
        });
        const data = await res.json();

        if (data.success && data.data && data.data.requires_mfa) {
          // Sukses kredensial -> Beralih ke layar MFA Google Authenticator
          document.getElementById('adminLoginForm').classList.add('d-none');
          document.getElementById('mfaSection').classList.remove('d-none');
          document.getElementById('headerSubtitle').textContent = 'Verifikasi Keamanan Multi-Faktor (MFA)';

          if (data.data.mfa_setup) {
            // Pengguna baru / belum setup MFA: Tampilkan QR Code
            document.getElementById('mfaSetupNotice').classList.remove('d-none');
            document.getElementById('mfaExistingNotice').classList.add('d-none');
            document.getElementById('mfaSecretText').textContent = data.data.secret;

            // Render QR Code
            const qrContainer = document.getElementById('qrcodeCanvas');
            qrContainer.innerHTML = '';
            qrcodeInstance = new QRCode(qrContainer, {
              text: data.data.qr_uri,
              width: 170,
              height: 170,
              colorDark : "#0F2C59",
              colorLight : "#FFFFFF",
              correctLevel : QRCode.CorrectLevel.M
            });
          } else {
            // Sudah pernah setup MFA: Cukup minta 6 digit kode
            document.getElementById('mfaSetupNotice').classList.add('d-none');
            document.getElementById('mfaExistingNotice').classList.remove('d-none');
          }

          document.getElementById('totpCode').value = '';
          document.getElementById('totpCode').focus();

        } else {
          alertBox.textContent = data.message || 'Login gagal.';
          alertBox.classList.remove('d-none');
        }
      } catch (err) {
        alertBox.textContent = 'Terjadi kesalahan jaringan atau server tidak merespons.';
        alertBox.classList.remove('d-none');
      } finally {
        btn.disabled = false;
        btn.innerHTML = `<span>Lanjutkan Autentikasi</span> <i class="bi bi-arrow-right"></i>`;
      }
    });

    // TAHAP 2: VERIFIKASI KODE MFA / GOOGLE AUTHENTICATOR
    document.getElementById('mfaVerifyForm').addEventListener('submit', async (e) => {
      e.preventDefault();
      const btn = document.getElementById('btnVerifyMfa');
      const alertBox = document.getElementById('loginAlert');
      const totpCode = document.getElementById('totpCode').value.trim();
      const csrfToken = document.getElementById('csrfToken').value;

      btn.disabled = true;
      btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span> Memverifikasi Kode...`;
      alertBox.classList.add('d-none');

      try {
        const res = await fetch('../api/admin/login.php?action=verify_mfa', {
          method: 'POST',
          headers: { 
            'Content-Type': 'application/json',
            'X-CSRF-Token': csrfToken
          },
          body: JSON.stringify({ totp_code: totpCode, csrf_token: csrfToken })
        });
        const data = await res.json();

        if (data.success) {
          btn.innerHTML = `<i class="bi bi-check-circle-fill me-2"></i> Berhasil! Mengalihkan...`;
          btn.classList.replace('btn-mgi-primary', 'btn-success');
          setTimeout(() => {
            window.location.href = data.data.redirect || 'index.php';
          }, 600);
        } else {
          alertBox.textContent = data.message || 'Kode verifikasi salah.';
          alertBox.classList.remove('d-none');
          btn.disabled = false;
          btn.innerHTML = `<span>Verifikasi &amp; Masuk</span> <i class="bi bi-check-circle-fill"></i>`;
          document.getElementById('totpCode').select();
        }
      } catch (err) {
        alertBox.textContent = 'Terjadi kesalahan saat memverifikasi kode MFA.';
        alertBox.classList.remove('d-none');
        btn.disabled = false;
        btn.innerHTML = `<span>Verifikasi &amp; Masuk</span> <i class="bi bi-check-circle-fill"></i>`;
      }
    });
  </script>
</body>
</html>
