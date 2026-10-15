<?php
$pageTitle = 'Manajemen Wawasan & Artikel';
require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
  <div>
    <h4 class="fw-bold text-dark mb-1">Manajemen Wawasan &amp; Artikel Industri</h4>
    <p class="text-muted small mb-0">Kelola artikel edukasi, analisis pasar alat berat, dan transparansi tata kelola untuk pemodal dan publik.</p>
  </div>
  <button type="button" class="btn btn-mgi-gold shadow-sm d-flex align-items-center gap-2" onclick="openArticleModal()">
    <i class="bi bi-file-earmark-plus-fill"></i>
    <span>Tulis Artikel Baru</span>
  </button>
</div>

<!-- Alert Box -->
<div id="articleAlertContainer"></div>

<!-- Filter Tabs -->
<ul class="nav nav-pills mb-3 gap-2 bg-white p-2 rounded-3 border border-subtle shadow-sm flex-wrap" id="articleCategoryFilter">
  <li class="nav-item">
    <button class="nav-link active fw-bold px-3 py-1.5" onclick="filterArticleCategory('')">Semua Kategori</button>
  </li>
  <li class="nav-item">
    <button class="nav-link fw-bold px-3 py-1.5" onclick="filterArticleCategory('Wawasan Industri')">Wawasan Industri</button>
  </li>
  <li class="nav-item">
    <button class="nav-link fw-bold px-3 py-1.5" onclick="filterArticleCategory('Tata Kelola (GCG)')">Tata Kelola (GCG)</button>
  </li>
  <li class="nav-item">
    <button class="nav-link fw-bold px-3 py-1.5" onclick="filterArticleCategory('Sinergi Ekosistem')">Sinergi Ekosistem</button>
  </li>
</ul>

<!-- Table Card -->
<div class="admin-card">
  <div class="admin-card-header bg-white d-flex align-items-center justify-content-between p-3 border-bottom">
    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
      <i class="bi bi-newspaper text-warning" style="color: #C5A059 !important;"></i>
      <span>Daftar Artikel &amp; Publikasi</span>
    </h6>
    <button class="btn btn-sm btn-outline-secondary" onclick="loadArticlesList()">
      <i class="bi bi-arrow-clockwise me-1"></i> Refresh
    </button>
  </div>
  <div class="table-responsive">
    <table class="admin-table table-hover mb-0">
      <thead>
        <tr>
          <th>Artikel</th>
          <th>Kategori</th>
          <th>Penulis</th>
          <th class="text-center" style="width: 80px;">Dilihat</th>
          <th>Status</th>
          <th>Diterbitkan</th>
          <th class="text-end" style="width: 140px;">Aksi</th>
        </tr>
      </thead>
      <tbody id="articleTableBody">
        <tr>
          <td colspan="7" class="text-center py-4 text-muted">
            <span class="spinner-border spinner-border-sm me-2"></span> Memuat artikel...
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal Form Tambah / Edit Artikel -->
<div class="modal fade" id="articleModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-3">
      <div class="modal-header bg-light border-bottom py-3 px-4">
        <h5 class="modal-title fw-bold text-dark mb-0" id="articleModalTitle">Tulis Artikel Baru</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="articleForm" onsubmit="saveArticle(event)">
        <input type="hidden" id="articleId" name="id" value="">
        <div class="modal-body p-4 space-y-3">
          
          <div class="row g-3 mb-3">
            <div class="col-md-8">
              <label class="form-label small fw-bold text-dark">Judul Artikel <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="formArticleTitle" name="title" placeholder="Contoh: Analisis Tren Investasi Sektor Riil 2026" required onkeyup="autoSlug()">
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-bold text-dark">Kategori <span class="text-danger">*</span></label>
              <select class="form-select" id="formArticleCategory" name="category" required>
                <option value="Wawasan Industri">Wawasan Industri</option>
                <option value="Tata Kelola (GCG)">Tata Kelola (GCG)</option>
                <option value="Sinergi Ekosistem">Sinergi Ekosistem</option>
                <option value="Regulasi & Pasar">Regulasi &amp; Pasar</option>
                <option value="Berita Perusahaan">Berita Perusahaan</option>
              </select>
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label small fw-bold text-dark">Slug URL (Permalink)</label>
              <input type="text" class="form-control" id="formArticleSlug" name="slug" placeholder="analisis-tren-investasi-2026">
              <small class="text-muted">Dibuat otomatis bila dikosongkan.</small>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-bold text-dark">Penulis / Sumber</label>
              <input type="text" class="form-control" id="formArticleAuthor" name="author_name" value="Tim Riset & Analis MGI">
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-8">
              <label class="form-label small fw-bold text-dark">URL Cover Image</label>
              <input type="text" class="form-control" id="formArticleCover" name="cover_image" placeholder="assets/img/hero-alat-berat.png">
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-bold text-dark">Status Publikasi</label>
              <select class="form-select" id="formArticleStatus" name="status">
                <option value="published">Dipublikasikan (Published)</option>
                <option value="draft">Konsep (Draft)</option>
              </select>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label small fw-bold text-dark">Ringkasan Singkat (Excerpt)</label>
            <textarea class="form-control" id="formArticleExcerpt" name="excerpt" rows="2" placeholder="Ringkasan 1-2 kalimat untuk pratinjau di kartu artikel..."></textarea>
          </div>

          <div class="mb-3">
            <label class="form-label small fw-bold text-dark">Isi Konten Artikel (Format HTML didukung) <span class="text-danger">*</span></label>
            <textarea class="form-control font-monospace" id="formArticleContent" name="content" rows="10" placeholder="Tuliskan isi artikel lengkap di sini. Tag <p>, <h3>, <ul>, <strong> didukung..." required></textarea>
          </div>

        </div>
        <div class="modal-footer bg-light border-top px-4 py-3">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-mgi-primary px-4 fw-bold" id="btnSaveArticle">
            <i class="bi bi-check-circle-fill me-1"></i> Simpan Artikel
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
let allArticles = [];
let currentCategoryFilter = '';
let articleModalInstance = null;

document.addEventListener('DOMContentLoaded', () => {
  const modalEl = document.getElementById('articleModal');
  if (modalEl) {
    articleModalInstance = new bootstrap.Modal(modalEl);
  }
  loadArticlesList();
});

function showAlert(message, type = 'success') {
  const container = document.getElementById('articleAlertContainer');
  if (!container) return;
  const alertHtml = `
    <div class="alert alert-${type} alert-dismissible fade show shadow-sm d-flex align-items-center" role="alert">
      <i class="bi bi-${type === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill'} me-2 fs-5"></i>
      <div>${message}</div>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  `;
  container.innerHTML = alertHtml;
  setTimeout(() => {
    container.innerHTML = '';
  }, 4000);
}

function autoSlug() {
  const title = document.getElementById('formArticleTitle').value;
  const slugInput = document.getElementById('formArticleSlug');
  if (!document.getElementById('articleId').value) {
    slugInput.value = title.toLowerCase()
      .replace(/[^\w\s-]/g, '')
      .replace(/[\s_-]+/g, '-')
      .replace(/^-+|-+$/g, '');
  }
}

async function loadArticlesList() {
  const tbody = document.getElementById('articleTableBody');
  tbody.innerHTML = `
    <tr>
      <td colspan="7" class="text-center py-4 text-muted">
        <span class="spinner-border spinner-border-sm me-2"></span> Memuat artikel...
      </td>
    </tr>
  `;

  try {
    const res = await fetch('../api/admin/articles.php');
    const result = await res.json();

    if (!res.ok || !result.success) {
      throw new Error(result.message || 'Gagal memuat artikel dari server');
    }

    allArticles = result.data || [];
    renderArticlesTable();
  } catch (err) {
    tbody.innerHTML = `
      <tr>
        <td colspan="7" class="text-center py-4 text-danger">
          <i class="bi bi-exclamation-triangle-fill me-1"></i> ${err.message}
          <div class="mt-2">
            <button class="btn btn-sm btn-outline-primary" onclick="loadArticlesList()">Coba Lagi</button>
          </div>
        </td>
      </tr>
    `;
  }
}

function filterArticleCategory(cat) {
  currentCategoryFilter = cat;
  const buttons = document.querySelectorAll('#articleCategoryFilter button');
  buttons.forEach(btn => {
    btn.classList.remove('active');
    if ((cat === '' && btn.innerText.includes('Semua')) || (cat !== '' && btn.innerText.includes(cat))) {
      btn.classList.add('active');
    }
  });
  renderArticlesTable();
}

function renderArticlesTable() {
  const tbody = document.getElementById('articleTableBody');
  let filtered = allArticles;

  if (currentCategoryFilter !== '') {
    filtered = allArticles.filter(a => (a.category || '').toLowerCase() === currentCategoryFilter.toLowerCase());
  }

  if (filtered.length === 0) {
    tbody.innerHTML = `
      <tr>
        <td colspan="7" class="text-center py-5 text-muted">
          <i class="bi bi-file-earmark-x fs-1 d-block mb-2 text-secondary"></i>
          Belum ada artikel${currentCategoryFilter ? ` untuk kategori "${currentCategoryFilter}"` : ''}.
        </td>
      </tr>
    `;
    return;
  }

  tbody.innerHTML = filtered.map(a => {
    const isPublished = a.status === 'published';
    const coverThumb = a.cover_image
      ? `<img src="../${a.cover_image}" alt="Cover" class="rounded object-fit-cover shadow-sm me-2" style="width: 48px; height: 38px;" onerror="this.style.display='none'">`
      : `<div class="rounded bg-light text-muted d-flex align-items-center justify-content-center me-2 border" style="width: 48px; height: 38px;"><i class="bi bi-card-image"></i></div>`;

    return `
      <tr>
        <td>
          <div class="d-flex align-items-center">
            ${coverThumb}
            <div>
              <div class="fw-bold text-dark">${a.title}</div>
              <div class="text-muted small">slug: /${a.slug}</div>
            </div>
          </div>
        </td>
        <td>
          <span class="badge bg-light text-dark border small">${a.category || 'Wawasan'}</span>
        </td>
        <td class="small text-secondary">${a.author_name || '-'}</td>
        <td class="text-center fw-bold text-secondary">${a.views_count || 0}</td>
        <td>
          <button class="badge border-0 ${isPublished ? 'bg-success' : 'bg-secondary'} px-2 py-1 cursor-pointer" onclick="toggleArticleStatus(${a.id})">
            ${isPublished ? 'Published' : 'Draft'}
          </button>
        </td>
        <td class="small text-muted">${(a.published_at || '').substring(0, 10)}</td>
        <td class="text-end">
          <div class="btn-group btn-group-sm">
            <button type="button" class="btn btn-outline-primary" onclick="openArticleModal(${a.id})" title="Edit">
              <i class="bi bi-pencil-fill"></i>
            </button>
            <button type="button" class="btn btn-outline-danger" onclick="deleteArticle(${a.id}, '${a.title.replace(/'/g, "\\'")}')" title="Hapus">
              <i class="bi bi-trash3-fill"></i>
            </button>
          </div>
        </td>
      </tr>
    `;
  }).join('');
}

function openArticleModal(id = null) {
  const form = document.getElementById('articleForm');
  form.reset();
  document.getElementById('articleId').value = '';
  document.getElementById('articleModalTitle').textContent = 'Tulis Artikel Baru';
  document.getElementById('formArticleAuthor').value = 'Tim Riset & Analis MGI';
  document.getElementById('formArticleStatus').value = 'published';

  if (id) {
    const article = allArticles.find(a => parseInt(a.id) === parseInt(id));
    if (article) {
      document.getElementById('articleId').value = article.id;
      document.getElementById('articleModalTitle').textContent = 'Edit Artikel #' + article.id;
      document.getElementById('formArticleTitle').value = article.title;
      document.getElementById('formArticleSlug').value = article.slug;
      document.getElementById('formArticleCategory').value = article.category;
      document.getElementById('formArticleAuthor').value = article.author_name;
      document.getElementById('formArticleCover').value = article.cover_image || '';
      document.getElementById('formArticleStatus').value = article.status;
      document.getElementById('formArticleExcerpt').value = article.excerpt || '';
      document.getElementById('formArticleContent').value = article.content || '';
    }
  }

  if (articleModalInstance) {
    articleModalInstance.show();
  }
}

async function saveArticle(e) {
  e.preventDefault();
  const btn = document.getElementById('btnSaveArticle');
  btn.disabled = true;
  btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...`;

  const id = document.getElementById('articleId').value;
  const isEdit = !!id;

  const payload = {
    id: id ? parseInt(id) : undefined,
    title: document.getElementById('formArticleTitle').value.trim(),
    slug: document.getElementById('formArticleSlug').value.trim(),
    category: document.getElementById('formArticleCategory').value,
    author_name: document.getElementById('formArticleAuthor').value.trim(),
    cover_image: document.getElementById('formArticleCover').value.trim(),
    status: document.getElementById('formArticleStatus').value,
    excerpt: document.getElementById('formArticleExcerpt').value.trim(),
    content: document.getElementById('formArticleContent').value.trim()
  };

  try {
    const url = '../api/admin/articles.php';
    const method = isEdit ? 'PUT' : 'POST';

    const res = await fetch(url, {
      method: method,
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });

    const result = await res.json();
    if (!res.ok || !result.success) {
      throw new Error(result.message || 'Gagal menyimpan artikel');
    }

    if (articleModalInstance) {
      articleModalInstance.hide();
    }
    showAlert(result.message || 'Artikel berhasil disimpan.', 'success');
    loadArticlesList();
  } catch (err) {
    showAlert(err.message, 'danger');
  } finally {
    btn.disabled = false;
    btn.innerHTML = `<i class="bi bi-check-circle-fill me-1"></i> Simpan Artikel`;
  }
}

async function toggleArticleStatus(id) {
  try {
    const res = await fetch('../api/admin/articles.php', {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: id, action: 'toggle_status' })
    });
    const result = await res.json();
    if (!res.ok || !result.success) {
      throw new Error(result.message || 'Gagal mengubah status');
    }
    showAlert('Status publikasi artikel diperbarui.', 'success');
    loadArticlesList();
  } catch (err) {
    showAlert(err.message, 'danger');
  }
}

async function deleteArticle(id, title) {
  if (!confirm(`Apakah Anda yakin ingin menghapus artikel "${title}"?`)) {
    return;
  }

  try {
    const res = await fetch('../api/admin/articles.php', {
      method: 'DELETE',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: id })
    });
    const result = await res.json();
    if (!res.ok || !result.success) {
      throw new Error(result.message || 'Gagal menghapus');
    }
    showAlert('Artikel berhasil dihapus.', 'success');
    loadArticlesList();
  } catch (err) {
    showAlert(err.message, 'danger');
  }
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
