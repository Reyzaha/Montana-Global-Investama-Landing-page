<?php
$pageTitle = 'Galeri Media';
require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
  <div>
    <h4 class="fw-bold text-dark mb-1">Manajemen Media &amp; Dokumen</h4>
    <p class="text-muted small mb-0">Unggah dan kelola aset gambar alat berat, brosur penawaran investasi, logo rekanan, dan materi publikasi.</p>
  </div>
  <button type="button" class="btn btn-mgi-gold shadow-sm d-flex align-items-center gap-2" onclick="document.getElementById('uploadFileModal').show ? uploadModalInstance.show() : null">
    <i class="bi bi-cloud-arrow-up-fill"></i>
    <span>Unggah File Baru</span>
  </button>
</div>

<!-- Alert Box -->
<div id="mediaAlertContainer"></div>

<!-- Upload Dropzone Card -->
<div class="admin-card mb-4">
  <div class="admin-card-header bg-white d-flex align-items-center justify-content-between p-3 border-bottom">
    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
      <i class="bi bi-upload text-warning" style="color: #C5A059 !important;"></i>
      <span>Unggah File Cepat</span>
    </h6>
    <small class="text-muted">Maksimal 5 MB per file (JPG, PNG, WEBP, SVG, PDF)</small>
  </div>
  <div class="p-4 bg-light">
    <form id="quickUploadForm" onsubmit="uploadMedia(event)" class="row g-3 align-items-center">
      <div class="col-md-5">
        <label class="form-label small fw-bold text-dark mb-1">Pilih File</label>
        <input type="file" class="form-control" id="uploadFileInput" accept="image/*,.pdf" required>
      </div>
      <div class="col-md-5">
        <label class="form-label small fw-bold text-dark mb-1">Keterangan / Caption (Opsional)</label>
        <input type="text" class="form-control" id="uploadFileCaption" placeholder="Contoh: Unit Excavator Komatsu PC200 Proyek Solo">
      </div>
      <div class="col-md-2 d-flex align-items-end">
        <button type="submit" class="btn btn-mgi-primary w-100 fw-bold d-flex align-items-center justify-content-center gap-2" id="btnUploadMedia" style="height: 38px;">
          <i class="bi bi-cloud-upload"></i>
          <span>Upload</span>
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Filter & Search Toolbar -->
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3 bg-white p-2 rounded-3 border shadow-sm">
  <ul class="nav nav-pills gap-1" id="mediaTypeTabs">
    <li class="nav-item">
      <button class="nav-link active fw-bold px-3 py-1.5" onclick="filterMediaType('')">Semua File</button>
    </li>
    <li class="nav-item">
      <button class="nav-link fw-bold px-3 py-1.5" onclick="filterMediaType('image')">
        <i class="bi bi-image me-1 text-primary"></i> Gambar
      </button>
    </li>
    <li class="nav-item">
      <button class="nav-link fw-bold px-3 py-1.5" onclick="filterMediaType('pdf')">
        <i class="bi bi-file-earmark-pdf me-1 text-danger"></i> Dokumen PDF
      </button>
    </li>
  </ul>
  <div class="d-flex align-items-center gap-2">
    <input type="text" id="mediaSearch" class="form-control form-control-sm" placeholder="Cari nama file / keterangan..." style="width: 250px;" oninput="debouncedSearch()">
    <button class="btn btn-sm btn-outline-secondary" onclick="loadMediaLibrary()">
      <i class="bi bi-arrow-clockwise"></i>
    </button>
  </div>
</div>

<!-- Media Grid -->
<div id="mediaGridContainer" class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-3 mb-4">
  <div class="col-12 py-5 text-center text-muted">
    <span class="spinner-border spinner-border-sm me-2"></span> Memuat galeri media...
  </div>
</div>

<script>
let currentMediaTypeFilter = '';
let searchMediaTimeout = null;
let allMediaFiles = [];

document.addEventListener('DOMContentLoaded', () => {
  loadMediaLibrary();
});

function showAlert(message, type = 'success') {
  const container = document.getElementById('mediaAlertContainer');
  if (!container) return;
  container.innerHTML = `
    <div class="alert alert-${type} alert-dismissible fade show shadow-sm d-flex align-items-center" role="alert">
      <i class="bi bi-${type === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill'} me-2 fs-5"></i>
      <div>${message}</div>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  `;
  setTimeout(() => { container.innerHTML = ''; }, 4000);
}

function debouncedSearch() {
  clearTimeout(searchMediaTimeout);
  searchMediaTimeout = setTimeout(loadMediaLibrary, 300);
}

function filterMediaType(type) {
  currentMediaTypeFilter = type;
  const buttons = document.querySelectorAll('#mediaTypeTabs button');
  buttons.forEach(btn => {
    btn.classList.remove('active');
    if ((type === '' && btn.innerText.includes('Semua')) || (type !== '' && btn.getAttribute('onclick').includes(type))) {
      btn.classList.add('active');
    }
  });
  loadMediaLibrary();
}

function formatBytes(bytes) {
  if (bytes === 0) return '0 B';
  const k = 1024;
  const sizes = ['B', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
}

async function loadMediaLibrary() {
  const container = document.getElementById('mediaGridContainer');
  container.innerHTML = `
    <div class="col-12 py-5 text-center text-muted">
      <span class="spinner-border spinner-border-sm me-2"></span> Memuat galeri media...
    </div>
  `;

  const search = document.getElementById('mediaSearch').value.trim();
  let url = `../api/admin/media.php?type=${encodeURIComponent(currentMediaTypeFilter)}&search=${encodeURIComponent(search)}`;

  try {
    const res = await fetch(url);
    const result = await res.json();

    if (!res.ok || !result.success) {
      throw new Error(result.message || 'Gagal memuat galeri');
    }

    allMediaFiles = result.data || [];
    renderMediaGrid();
  } catch (err) {
    container.innerHTML = `
      <div class="col-12 py-4 text-center text-danger">
        <i class="bi bi-exclamation-triangle-fill me-1"></i> ${err.message}
        <div class="mt-2"><button class="btn btn-sm btn-outline-primary" onclick="loadMediaLibrary()">Coba Lagi</button></div>
      </div>
    `;
  }
}

function renderMediaGrid() {
  const container = document.getElementById('mediaGridContainer');
  if (allMediaFiles.length === 0) {
    container.innerHTML = `
      <div class="col-12 py-5 text-center text-muted">
        <i class="bi bi-images fs-1 d-block mb-2 text-secondary"></i>
        Belum ada file media yang diunggah${currentMediaTypeFilter ? ` untuk filter "${currentMediaTypeFilter}"` : ''}.
      </div>
    `;
    return;
  }

  container.innerHTML = allMediaFiles.map(f => {
    const isImage = (f.file_type || '').includes('image');
    const preview = isImage
      ? `<img src="../${f.file_path}" alt="${f.original_name}" class="card-img-top object-fit-cover" style="height: 150px;" onerror="this.src='../assets/img/hero-alat-berat.png'">`
      : `<div class="bg-light d-flex flex-column align-items-center justify-content-center text-danger border-bottom" style="height: 150px;">
           <i class="bi bi-file-earmark-pdf fs-1"></i>
           <small class="fw-bold mt-1 text-uppercase">${f.file_type.split('/')[1] || 'DOC'}</small>
         </div>`;

    return `
      <div class="col">
        <div class="card h-100 shadow-sm border rounded-3 overflow-hidden bg-white d-flex flex-column">
          ${preview}
          <div class="card-body p-3 d-flex flex-column">
            <div class="fw-bold text-dark small text-truncate mb-1" title="${f.original_name}">${f.original_name}</div>
            ${f.caption ? `<div class="text-secondary small text-truncate mb-2" style="font-size: 0.78rem;" title="${f.caption}">${f.caption}</div>` : ''}
            <div class="d-flex align-items-center justify-content-between text-muted small mt-auto pt-2 border-top" style="font-size: 0.72rem;">
              <span>${formatBytes(f.file_size)}</span>
              <span>${(f.uploaded_at || '').substring(0, 10)}</span>
            </div>
            <div class="d-flex align-items-center gap-1 mt-2">
              <button type="button" class="btn btn-sm btn-outline-secondary flex-grow-1 small py-1" onclick="copyFilePath('${f.file_path}')" title="Salin URL Path">
                <i class="bi bi-copy me-1"></i> Salin URL
              </button>
              <button type="button" class="btn btn-sm btn-outline-danger py-1 px-2" onclick="deleteMedia(${f.id}, '${f.original_name.replace(/'/g, "\\'")}')" title="Hapus">
                <i class="bi bi-trash3-fill"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
    `;
  }).join('');
}

async function uploadMedia(e) {
  e.preventDefault();
  const fileInput = document.getElementById('uploadFileInput');
  if (!fileInput.files || fileInput.files.length === 0) {
    showAlert('Pilih file yang ingin diunggah terlebih dahulu.', 'warning');
    return;
  }

  const btn = document.getElementById('btnUploadMedia');
  btn.disabled = true;
  btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> Mengunggah...`;

  const formData = new FormData();
  formData.append('file', fileInput.files[0]);
  formData.append('caption', document.getElementById('uploadFileCaption').value.trim());

  try {
    const res = await fetch('../api/admin/media.php', {
      method: 'POST',
      body: formData
    });
    const result = await res.json();

    if (!res.ok || !result.success) {
      throw new Error(result.message || 'Gagal mengunggah file');
    }

    document.getElementById('quickUploadForm').reset();
    showAlert(`File "${result.data.original_name}" berhasil diunggah! Path: ${result.data.file_path}`, 'success');
    loadMediaLibrary();
  } catch (err) {
    showAlert(err.message, 'danger');
  } finally {
    btn.disabled = false;
    btn.innerHTML = `<i class="bi bi-cloud-upload"></i> <span>Upload</span>`;
  }
}

function copyFilePath(path) {
  navigator.clipboard.writeText(path).then(() => {
    showAlert(`Path file berhasil disalin ke clipboard: <code>${path}</code>`, 'success');
  }).catch(() => {
    prompt('Salin path file di bawah ini:', path);
  });
}

async function deleteMedia(id, name) {
  if (!confirm(`Apakah Anda yakin ingin menghapus file media "${name}"? Tindakan ini tidak dapat dibatalkan.`)) {
    return;
  }

  try {
    const res = await fetch('../api/admin/media.php', {
      method: 'DELETE',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: id })
    });
    const json = await res.json();
    if (!res.ok || !json.success) {
      throw new Error(json.message || 'Gagal menghapus');
    }

    showAlert('File media berhasil dihapus.', 'success');
    loadMediaLibrary();
  } catch (err) {
    showAlert(err.message, 'danger');
  }
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
