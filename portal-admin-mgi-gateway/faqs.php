<?php
$pageTitle = 'Manajemen FAQ & Pusat Bantuan';
require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
  <div>
    <h4 class="fw-bold text-dark mb-1">Manajemen FAQ (Tanya Jawab Terverifikasi)</h4>
    <p class="text-muted small mb-0">Kelola daftar pertanyaan dan jawaban seputar skema bagi hasil, jaminan aset riil, prosedur due diligence, dan tata kelola investasi.</p>
  </div>
  <button type="button" class="btn btn-mgi-gold shadow-sm d-flex align-items-center gap-2" onclick="openFaqModal()">
    <i class="bi bi-plus-circle-fill"></i>
    <span>Tambah FAQ Baru</span>
  </button>
</div>

<!-- Alert Box -->
<div id="faqAlertContainer"></div>

<!-- Category Filters -->
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
  <ul class="nav nav-pills gap-2 bg-white p-2 rounded-3 border border-subtle shadow-sm flex-wrap" id="faqCategoryFilter">
    <li class="nav-item">
      <button class="nav-link active fw-bold px-3 py-1.5" onclick="filterFaqCategory('')">Semua Kategori</button>
    </li>
    <li class="nav-item">
      <button class="nav-link fw-bold px-3 py-1.5" onclick="filterFaqCategory('Skema Investasi')">Skema Investasi</button>
    </li>
    <li class="nav-item">
      <button class="nav-link fw-bold px-3 py-1.5" onclick="filterFaqCategory('Aset & Legalitas')">Aset &amp; Legalitas</button>
    </li>
    <li class="nav-item">
      <button class="nav-link fw-bold px-3 py-1.5" onclick="filterFaqCategory('Tata Kelola & Monitoring')">Tata Kelola</button>
    </li>
    <li class="nav-item">
      <button class="nav-link fw-bold px-3 py-1.5" onclick="filterFaqCategory('Operasional')">Operasional</button>
    </li>
    <li class="nav-item">
      <button class="nav-link fw-bold px-3 py-1.5" onclick="filterFaqCategory('Bagi Hasil & Pajak')">Bagi Hasil</button>
    </li>
  </ul>

  <div class="input-group" style="max-width: 280px;">
    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
    <input type="text" class="form-control border-start-0" id="faqSearchInput" placeholder="Cari pertanyaan..." oninput="searchFaqs()">
  </div>
</div>

<!-- FAQ Table Card -->
<div class="admin-card">
  <div class="admin-card-header bg-white d-flex align-items-center justify-content-between p-3 border-bottom">
    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
      <i class="bi bi-question-circle-fill text-warning" style="color: #C5A059 !important;"></i>
      <span>Daftar Tanya Jawab Terdaftar</span>
    </h6>
    <button class="btn btn-sm btn-outline-secondary" onclick="loadFaqsList()">
      <i class="bi bi-arrow-clockwise me-1"></i> Refresh
    </button>
  </div>
  <div class="table-responsive">
    <table class="admin-table table-hover mb-0">
      <thead>
        <tr>
          <th style="width: 60px;">No</th>
          <th style="width: 140px;">Kategori</th>
          <th>Pertanyaan</th>
          <th>Ringkasan Jawaban</th>
          <th style="width: 80px;">Urutan</th>
          <th style="width: 100px;">Status</th>
          <th class="text-end" style="width: 130px;">Aksi</th>
        </tr>
      </thead>
      <tbody id="faqsTableBody">
        <tr>
          <td colspan="7" class="text-center py-4 text-muted">
            <span class="spinner-border spinner-border-sm me-2"></span> Memuat data FAQ...
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal Form Tambah / Edit FAQ -->
<div class="modal fade" id="faqModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-3">
      <div class="modal-header bg-light border-bottom py-3 px-4">
        <h5 class="modal-title fw-bold text-dark mb-0" id="faqModalTitle">Tambah FAQ Baru</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="faqForm" onsubmit="saveFaq(event)">
        <input type="hidden" id="faqId" name="id" value="">
        <div class="modal-body p-4 space-y-3">
          
          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label small fw-bold text-dark">Kategori FAQ <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="formFaqCategory" name="category" list="categoryOptions" placeholder="Contoh: Skema Investasi" required>
              <datalist id="categoryOptions">
                <option value="Skema Investasi">
                <option value="Aset & Legalitas">
                <option value="Tata Kelola & Monitoring">
                <option value="Operasional">
                <option value="Bagi Hasil & Pajak">
                <option value="Umum">
              </datalist>
            </div>
            <div class="col-md-3">
              <label class="form-label small fw-bold text-dark">Urutan Tampil</label>
              <input type="number" class="form-control" id="formFaqSort" name="sort_order" value="1" min="0">
            </div>
            <div class="col-md-3">
              <label class="form-label small fw-bold text-dark">Status Publikasi</label>
              <select class="form-select" id="formFaqIsActive" name="is_active">
                <option value="1">Aktif / Publikasi</option>
                <option value="0">Draft / Sembunyikan</option>
              </select>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label small fw-bold text-dark">Pertanyaan (Question) <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="formFaqQuestion" name="question" placeholder="Contoh: Bagaimana skema pembagian hasil proyek di MGI?" required>
          </div>

          <div class="mb-3">
            <label class="form-label small fw-bold text-dark">Jawaban Lengkap (Answer) <span class="text-danger">*</span></label>
            <textarea class="form-control" id="formFaqAnswer" name="answer" rows="5" placeholder="Tuliskan jawaban lengkap, transparan, dan terstruktur..." required></textarea>
          </div>

        </div>
        <div class="modal-footer bg-light border-top py-3 px-4">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-mgi-gold px-4 fw-bold shadow-sm" id="btnSaveFaq">
            <span class="spinner-border spinner-border-sm me-1 d-none" id="saveFaqSpinner"></span>
            Simpan FAQ
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
let faqModalInstance = null;
let allFaqs = [];
let currentFaqCategory = '';

document.addEventListener('DOMContentLoaded', () => {
  faqModalInstance = new bootstrap.Modal(document.getElementById('faqModal'));
  loadFaqsList();
});

function showAlert(message, type = 'success') {
  const container = document.getElementById('faqAlertContainer');
  container.innerHTML = `
    <div class="alert alert-${type} alert-dismissible fade show shadow-sm" role="alert">
      <div class="d-flex align-items-center gap-2">
        <i class="bi ${type === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill'} fs-5"></i>
        <div>${escapeHtml(message)}</div>
      </div>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  `;
}

function filterFaqCategory(cat) {
  currentFaqCategory = cat;
  const buttons = document.querySelectorAll('#faqCategoryFilter button');
  buttons.forEach(btn => btn.classList.remove('active'));
  event.target.classList.add('active');
  renderFaqTable();
}

function searchFaqs() {
  renderFaqTable();
}

async function loadFaqsList() {
  const tbody = document.getElementById('faqsTableBody');
  tbody.innerHTML = `
    <tr>
      <td colspan="7" class="text-center py-4 text-muted">
        <span class="spinner-border spinner-border-sm me-2"></span> Memuat data FAQ...
      </td>
    </tr>
  `;

  try {
    const res = await fetch('../api/admin/faqs.php', { cache: 'no-store' });
    const json = await res.json();
    if (!json.success) throw new Error(json.message || 'Gagal memuat FAQ.');

    allFaqs = json.data || [];
    renderFaqTable();
  } catch (err) {
    tbody.innerHTML = `
      <tr>
        <td colspan="7" class="text-center py-4 text-danger">
          <i class="bi bi-exclamation-circle me-1"></i> ${escapeHtml(err.message)}
          <br><button class="btn btn-sm btn-outline-dark mt-2" onclick="loadFaqsList()">Coba Lagi</button>
        </td>
      </tr>
    `;
  }
}

function renderFaqTable() {
  const tbody = document.getElementById('faqsTableBody');
  const query = (document.getElementById('faqSearchInput').value || '').toLowerCase().trim();

  let filtered = allFaqs;
  if (currentFaqCategory) {
    filtered = filtered.filter(f => f.category === currentFaqCategory);
  }
  if (query) {
    filtered = filtered.filter(f => f.question.toLowerCase().includes(query) || f.answer.toLowerCase().includes(query));
  }

  if (filtered.length === 0) {
    tbody.innerHTML = `
      <tr>
        <td colspan="7" class="text-center py-5 text-muted">
          <i class="bi bi-inbox fs-3 d-block mb-2"></i>
          Tidak ada pertanyaan FAQ yang sesuai. Klik "Tambah FAQ Baru" untuk membuat.
        </td>
      </tr>
    `;
    return;
  }

  tbody.innerHTML = filtered.map((item, idx) => {
    const statusBadge = item.is_active == 1
      ? '<span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 py-1 px-2">Aktif</span>'
      : '<span class="badge bg-secondary bg-opacity-10 text-muted border border-secondary border-opacity-25 py-1 px-2">Draft</span>';

    return `
      <tr>
        <td class="text-muted fw-bold">${idx + 1}</td>
        <td>
          <span class="badge bg-light text-primary border">${escapeHtml(item.category)}</span>
        </td>
        <td>
          <div class="fw-bold text-dark">${escapeHtml(item.question)}</div>
        </td>
        <td>
          <div class="small text-muted text-truncate" style="max-width: 320px;" title="${escapeHtml(item.answer)}">
            ${escapeHtml(item.answer)}
          </div>
        </td>
        <td><span class="badge bg-light text-secondary border">${item.sort_order}</span></td>
        <td>
          <button type="button" class="btn btn-sm p-0 border-0" onclick="toggleFaqStatus(${item.id})" title="Klik untuk ubah status">
            ${statusBadge}
          </button>
        </td>
        <td class="text-end">
          <button type="button" class="btn btn-sm btn-outline-primary me-1 p-1 px-2" onclick="editFaq(${item.id})" title="Edit">
            <i class="bi bi-pencil-square"></i>
          </button>
          <button type="button" class="btn btn-sm btn-outline-danger p-1 px-2" onclick="deleteFaq(${item.id}, '${escapeHtml(item.question)}')" title="Hapus">
            <i class="bi bi-trash"></i>
          </button>
        </td>
      </tr>
    `;
  }).join('');
}

function openFaqModal(faq = null) {
  const form = document.getElementById('faqForm');
  form.reset();

  if (faq) {
    document.getElementById('faqModalTitle').innerText = 'Edit FAQ';
    document.getElementById('faqId').value = faq.id;
    document.getElementById('formFaqCategory').value = faq.category;
    document.getElementById('formFaqQuestion').value = faq.question;
    document.getElementById('formFaqAnswer').value = faq.answer;
    document.getElementById('formFaqSort').value = faq.sort_order;
    document.getElementById('formFaqIsActive').value = faq.is_active;
  } else {
    document.getElementById('faqModalTitle').innerText = 'Tambah FAQ Baru';
    document.getElementById('faqId').value = '';
    document.getElementById('formFaqCategory').value = currentFaqCategory || 'Skema Investasi';
    document.getElementById('formFaqSort').value = allFaqs.length + 1;
    document.getElementById('formFaqIsActive').value = '1';
  }

  faqModalInstance.show();
}

function editFaq(id) {
  const item = allFaqs.find(f => f.id == id);
  if (item) openFaqModal(item);
}

async function saveFaq(e) {
  e.preventDefault();
  const id = document.getElementById('faqId').value;
  const isEdit = !!id;

  const payload = {
    id: id ? parseInt(id) : undefined,
    category: document.getElementById('formFaqCategory').value,
    question: document.getElementById('formFaqQuestion').value,
    answer: document.getElementById('formFaqAnswer').value,
    sort_order: parseInt(document.getElementById('formFaqSort').value) || 0,
    is_active: parseInt(document.getElementById('formFaqIsActive').value) || 0
  };

  const btn = document.getElementById('btnSaveFaq');
  const spinner = document.getElementById('saveFaqSpinner');
  btn.disabled = true;
  spinner.classList.remove('d-none');

  try {
    const res = await fetch('../api/admin/faqs.php', {
      method: isEdit ? 'PUT' : 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const json = await res.json();
    if (!json.success) throw new Error(json.message || 'Gagal menyimpan.');

    showAlert(isEdit ? 'FAQ berhasil diperbarui.' : 'FAQ baru berhasil ditambahkan.');
    faqModalInstance.hide();
    loadFaqsList();
  } catch (err) {
    showAlert(err.message, 'danger');
  } finally {
    btn.disabled = false;
    spinner.classList.add('d-none');
  }
}

async function toggleFaqStatus(id) {
  try {
    const res = await fetch('../api/admin/faqs.php', {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id, action: 'toggle_status' })
    });
    const json = await res.json();
    if (!json.success) throw new Error(json.message);
    showAlert('Status publikasi FAQ berhasil diubah.');
    loadFaqsList();
  } catch (err) {
    showAlert(err.message, 'danger');
  }
}

async function deleteFaq(id, question) {
  if (!confirm(`Hapus pertanyaan "${question}" secara permanen?`)) return;

  try {
    const res = await fetch('../api/admin/faqs.php', {
      method: 'DELETE',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id })
    });
    const json = await res.json();
    if (!json.success) throw new Error(json.message);
    showAlert('FAQ berhasil dihapus.');
    loadFaqsList();
  } catch (err) {
    showAlert(err.message, 'danger');
  }
}

function escapeHtml(str) {
  if (!str) return '';
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
