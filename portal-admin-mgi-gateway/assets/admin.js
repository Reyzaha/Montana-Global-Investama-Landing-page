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

  formatFileSize: function (bytes) {
    if (!bytes || bytes <= 0) return '0 B';
    const units = ['B', 'KB', 'MB', 'GB'];
    const i = Math.min(Math.floor(Math.log(bytes) / Math.log(1024)), units.length - 1);
    const size = (bytes / Math.pow(1024, i)).toFixed(i === 0 ? 0 : 1);
    return `${size} ${units[i]}`;
  },

  /**
   * Mengompresi file gambar otomatis di browser menggunakan HTML5 Canvas
   * Mencegah error upload_max_filesize di server tanpa perlu kompresi manual
   */
  compressImage: function (file, customOptions = {}) {
    return new Promise((resolve, reject) => {
      const opts = Object.assign({
        maxWidth: 1600,
        maxHeight: 1200,
        quality: 0.82,
        preferWebp: true
      }, customOptions);

      // Lewati kompresi jika SVG atau GIF kecil
      if (file.type === 'image/svg+xml') {
        return resolve({ file: file, compressed: false, originalSize: file.size, newSize: file.size });
      }
      if (file.type === 'image/gif' && file.size <= 2 * 1024 * 1024) {
        return resolve({ file: file, compressed: false, originalSize: file.size, newSize: file.size });
      }

      const reader = new FileReader();
      reader.onerror = () => reject(new Error('Gagal membaca file gambar'));
      reader.onload = (e) => {
        const img = new Image();
        img.onerror = () => reject(new Error('Format file gambar tidak valid atau korup'));
        img.onload = () => {
          let width = img.width;
          let height = img.height;

          // Skala proporsional jika melebihi resolusi maksimal
          if (width > opts.maxWidth || height > opts.maxHeight) {
            const ratio = Math.min(opts.maxWidth / width, opts.maxHeight / height);
            width = Math.round(width * ratio);
            height = Math.round(height * ratio);
          }

          const canvas = document.createElement('canvas');
          canvas.width = width;
          canvas.height = height;
          const ctx = canvas.getContext('2d');

          // Pilih tipe output optimal
          let outputType = file.type || 'image/jpeg';
          if (opts.preferWebp) {
            outputType = 'image/webp';
          } else if (file.type === 'image/png') {
            outputType = 'image/png';
          } else {
            outputType = 'image/jpeg';
          }

          // Background putih jika JPEG untuk mencegah background hitam pada transparansi
          if (outputType === 'image/jpeg') {
            ctx.fillStyle = '#FFFFFF';
            ctx.fillRect(0, 0, width, height);
          }

          ctx.drawImage(img, 0, 0, width, height);

          canvas.toBlob((blob) => {
            if (!blob) {
              return resolve({ file: file, compressed: false, originalSize: file.size, newSize: file.size });
            }

            // Gunakan blob hasil kompres jika ukurannya lebih hemat dari file asli
            if (blob.size < file.size) {
              let newFileName = file.name;
              if (outputType === 'image/webp' && !newFileName.toLowerCase().endsWith('.webp')) {
                newFileName = newFileName.replace(/\.[^/.]+$/, "") + ".webp";
              } else if (outputType === 'image/jpeg' && !newFileName.toLowerCase().match(/\.(jpg|jpeg)$/)) {
                newFileName = newFileName.replace(/\.[^/.]+$/, "") + ".jpg";
              }

              const compressedFile = new File([blob], newFileName, {
                type: outputType,
                lastModified: Date.now()
              });

              resolve({
                file: compressedFile,
                compressed: true,
                originalSize: file.size,
                newSize: blob.size,
                savings: Math.round((1 - (blob.size / file.size)) * 100)
              });
            } else {
              resolve({ file: file, compressed: false, originalSize: file.size, newSize: file.size });
            }
          }, outputType, opts.quality);
        };
        img.src = e.target.result;
      };
      reader.readAsDataURL(file);
    });
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
