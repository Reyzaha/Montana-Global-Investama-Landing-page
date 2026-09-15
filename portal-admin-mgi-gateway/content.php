<?php
$pageTitle = 'CMS Konten Grup';
require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
  <div>
    <h5 class="fw-bold text-dark mb-1">Manajemen Konten Publik Grup</h5>
    <p class="text-muted small mb-0">Kelola tahapan roadmap milestone Transformasi dan struktur ekosistem korporat.</p>
  </div>
  <button type="button" class="btn btn-mgi-gold d-flex align-items-center gap-2 shadow-sm" onclick="openStepModal()">
    <i class="bi bi-plus-circle-fill"></i>
    <span>Tambah Milestone Baru</span>
  </button>
</div>

<!-- Transformasi Steps Manager -->
<div class="admin-card mb-4">
  <div class="admin-card-header bg-white">
    <div class="d-flex align-items-center gap-2">
      <i class="bi bi-clock-history text-warning" style="color: #C5A059 !important;"></i>
      <span class="fw-bold text-dark">Roadmap Milestone Transformasi Bisnis MGI</span>
    </div>
  </div>

  <div class="table-responsive">
    <table class="admin-table">
      <thead>
        <tr>
          <th style="width: 70px;" class="text-center">Urutan</th>
          <th>Fase / Tahun</th>
          <th>Judul Milestone &amp; Tagline</th>
          <th>Deskripsi Singkat</th>
          <th>Pencapaian Kunci</th>
          <th class="text-end">Aksi</th>
        </tr>
      </thead>
      <tbody id="stepsTableBody">
        <tr>
          <td colspan="6" class="text-center py-4 text-muted">
            <span class="spinner-border spinner-border-sm me-2"></span> Memuat data milestone...
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</div>

<!-- MODAL TAMBAH / EDIT STEP -->
<div class="modal fade" id="stepModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      <div class="modal-header text-white" style="background: #142563;">
        <h5 class="modal-title fw-bold" id="stepModalTitle">Tambah Milestone Transformasi</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form id="stepForm" onsubmit="saveStep(event)">
        <input type="hidden" id="stepId">
        <div class="modal-body p-4 bg-light">
          <div class="card p-4 border-0 rounded-3 shadow-sm bg-white mb-3">
            <div class="row g-3">
              <div class="col-md-3">
                <label class="form-label">Urutan Langkah <span class="text-danger">*</span></label>
                <input type="number" id="stepOrder" class="form-control" min="1" required>
              </div>
              <div class="col-md-5">
                <label class="form-label">Label Fase / Tahun <span class="text-danger">*</span></label>
                <input type="text" id="stepYear" class="form-control" placeholder="2024 atau Fase Ekspansi" required>
              </div>
              <div class="col-md-4">
                <label class="form-label">Status</label>
                <select id="stepStatus" class="form-select">
                  <option value="completed">Completed (Terlaksana)</option>
                  <option value="in_progress">In Progress (Berjalan)</option>
                  <option value="planned">Planned (Rencana)</option>
                </select>
              </div>

              <div class="col-md-6">
                <label class="form-label">Judul Milestone <span class="text-danger">*</span></label>
                <input type="text" id="stepTitle" class="form-control" placeholder="Judul Transformasi" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Tagline / Subjudul</label>
                <input type="text" id="stepSubtitle" class="form-control" placeholder="Penjelasan singkat fokus milestone">
              </div>

              <div class="col-12">
                <label class="form-label">Deskripsi Lengkap</label>
                <textarea id="stepDesc" class="form-control" rows="3" placeholder="Uraikan detail perkembangan dan signifikansi milestone ini..."></textarea>
              </div>

              <div class="col-12">
                <label class="form-label">Poin-Poin Pencapaian Kunci (1 baris = 1 poin)</label>
                <textarea id="stepHighlights" class="form-control" rows="3" placeholder="Poin 1&#10;Poin 2&#10;Poin 3"></textarea>
                <small class="text-muted" style="font-size: 0.72rem;">Pisahkan setiap pencapaian dengan baris baru (Enter).</small>
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer bg-white border-top p-3 d-flex justify-content-between">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" id="btnSaveStep" class="btn btn-mgi-primary px-4">
            <i class="bi bi-cloud-arrow-up-fill me-1"></i> Simpan Milestone
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  let stepsData = [];
  let stepModalInstance = null;

  document.addEventListener('DOMContentLoaded', () => {
    stepModalInstance = new bootstrap.Modal(document.getElementById('stepModal'));
    loadSteps();
  });

  async function loadSteps() {
    const tb = document.getElementById('stepsTableBody');
    try {
      const res = await fetch('../api/admin/content.php?section=transformasi');
      const json = await res.json();
      if (!json.success) {
        if (json.status === 401) window.location.href = 'login.php';
        return;
      }

      stepsData = json.data || [];
      if (stepsData.length === 0) {
        tb.innerHTML = `<tr><td colspan="6" class="text-center py-4 text-muted">Belum ada milestone transformasi.</td></tr>`;
        return;
      }

      tb.innerHTML = stepsData.map(st => {
        const highlights = typeof st.highlights === 'string' ? JSON.parse(st.highlights || '[]') : (st.highlights || []);
        const highlightsHtml = highlights.map(h => `<li class="small text-muted">${h}</li>`).join('');

        return `
          <tr>
            <td class="text-center fw-bold fs-6" style="color: #C5A059;">#${st.step_order}</td>
            <td><span class="badge bg-royal text-white px-2 py-1" style="background: #142563;">${st.year_or_phase}</span></td>
            <td>
              <div class="fw-bold text-dark">${st.title}</div>
              <small class="text-muted">${st.subtitle || '-'}</small>
            </td>
            <td style="max-width: 250px;">
              <div class="small text-truncate" title="${st.description}">${st.description}</div>
            </td>
            <td style="max-width: 260px;">
              <ul class="mb-0 ps-3" style="max-height: 70px; overflow-y: auto;">
                ${highlightsHtml || '<span class="text-muted small">-</span>'}
              </ul>
            </td>
            <td class="text-end">
              <div class="btn-group btn-group-sm">
                <button class="btn btn-outline-primary" title="Edit" onclick="editStep(${st.id})">
                  <i class="bi bi-pencil"></i>
                </button>
                <button class="btn btn-outline-danger" title="Hapus" onclick="deleteStep(${st.id}, '${st.title.replace(/'/g, "\\'")}')">
                  <i class="bi bi-trash"></i>
                </button>
              </div>
            </td>
          </tr>
        `;
      }).join('');

    } catch (e) {
      console.error(e);
      AdminApp.showToast('Gagal memuat konten transformasi.', 'danger');
    }
  }

  function openStepModal() {
    document.getElementById('stepId').value = '';
    document.getElementById('stepModalTitle').textContent = 'Tambah Milestone Transformasi Baru';
    document.getElementById('stepOrder').value = stepsData.length + 1;
    document.getElementById('stepYear').value = '';
    document.getElementById('stepTitle').value = '';
    document.getElementById('stepSubtitle').value = '';
    document.getElementById('stepDesc').value = '';
    document.getElementById('stepHighlights').value = '';
    document.getElementById('stepStatus').value = 'completed';
    stepModalInstance.show();
  }

  function editStep(id) {
    const st = stepsData.find(s => Number(s.id) === Number(id));
    if (!st) return;

    const highlights = typeof st.highlights === 'string' ? JSON.parse(st.highlights || '[]') : (st.highlights || []);

    document.getElementById('stepId').value = st.id;
    document.getElementById('stepModalTitle').textContent = `Edit Milestone #${st.step_order}`;
    document.getElementById('stepOrder').value = st.step_order;
    document.getElementById('stepYear').value = st.year_or_phase;
    document.getElementById('stepTitle').value = st.title;
    document.getElementById('stepSubtitle').value = st.subtitle || '';
    document.getElementById('stepDesc').value = st.description || '';
    document.getElementById('stepHighlights').value = highlights.join('\n');
    document.getElementById('stepStatus').value = st.status || 'completed';
    stepModalInstance.show();
  }

  async function saveStep(e) {
    e.preventDefault();
    const id = document.getElementById('stepId').value;
    const rawHighlights = document.getElementById('stepHighlights').value.split('\n').map(h => h.trim()).filter(Boolean);

    const payload = {
      id: id || null,
      step_order: document.getElementById('stepOrder').value,
      year_or_phase: document.getElementById('stepYear').value.trim(),
      title: document.getElementById('stepTitle').value.trim(),
      subtitle: document.getElementById('stepSubtitle').value.trim(),
      description: document.getElementById('stepDesc').value.trim(),
      highlights: rawHighlights,
      status: document.getElementById('stepStatus').value
    };

    const btn = document.getElementById('btnSaveStep');
    btn.disabled = true;

    try {
      const res = await fetch('../api/admin/content.php?section=transformasi', {
        method: id ? 'PUT' : 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      const json = await res.json();

      if (json.success) {
        AdminApp.showToast(json.message, 'success');
        stepModalInstance.hide();
        loadSteps();
      } else {
        AdminApp.showToast(json.message, 'danger');
      }
    } catch (err) {
      AdminApp.showToast('Gagal menyimpan milestone.', 'danger');
    } finally {
      btn.disabled = false;
    }
  }

  async function deleteStep(id, title) {
    if (!confirm(`Hapus milestone "${title}"?`)) return;

    try {
      const res = await fetch(`../api/admin/content.php?section=transformasi&id=${id}`, {
        method: 'DELETE'
      });
      const json = await res.json();
      if (json.success) {
        AdminApp.showToast(json.message, 'success');
        loadSteps();
      } else {
        AdminApp.showToast(json.message, 'danger');
      }
    } catch (err) {
      AdminApp.showToast('Gagal menghapus milestone.', 'danger');
    }
  }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
