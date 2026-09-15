/**
 * PT MONTANA GLOBAL INVESTAMA — ADMIN DASHBOARD JAVASCRIPT
 */

const AdminApp = {
  formatRupiah: function (amount) {
    if (amount === null || amount === undefined || isNaN(amount)) return 'Rp 0';
    const num = Math.round(Number(amount));
    return 'Rp ' + num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
  },

  formatCompact: function (amount) {
    if (amount >= 1000000000000) {
      return 'Rp ' + (amount / 1000000000000).toFixed(2).replace('.00', '') + ' Triliun';
    } else if (amount >= 1000000000) {
      return 'Rp ' + (amount / 1000000000).toFixed(2).replace('.00', '') + ' Miliar';
    } else if (amount >= 1000000) {
      return 'Rp ' + (amount / 1000000).toFixed(2).replace('.00', '') + ' Juta';
    }
    return AdminApp.formatRupiah(amount);
  },

  showToast: function (message, type = 'success') {
    let container = document.getElementById('adminToastContainer');
    if (!container) {
      container = document.createElement('div');
      container.id = 'adminToastContainer';
      container.style.position = 'fixed';
      container.style.top = '24px';
      container.style.right = '24px';
      container.style.zIndex = '99999';
      document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    const isSuccess = type === 'success';
    toast.className = `alert alert-${isSuccess ? 'success' : 'danger'} alert-dismissible fade show shadow-lg`;
    toast.style.minWidth = '300px';
    toast.style.borderLeft = `5px solid ${isSuccess ? '#15803D' : '#DC2626'}`;
    toast.innerHTML = `
      <div class="d-flex align-items-center gap-2">
        <i class="bi bi-${isSuccess ? 'check-circle-fill' : 'exclamation-octagon-fill'} fs-5"></i>
        <div>${message}</div>
      </div>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;

    container.appendChild(toast);
    setTimeout(() => {
      toast.classList.remove('show');
      setTimeout(() => toast.remove(), 300);
    }, 4500);
  },

  logout: async function () {
    if (!confirm('Apakah Anda yakin ingin keluar dari dashboard administrator?')) return;
    try {
      await fetch('../api/admin/login.php?action=logout');
      window.location.href = 'login.php';
    } catch (e) {
      window.location.href = 'login.php';
    }
  }
};

window.AdminApp = AdminApp;
