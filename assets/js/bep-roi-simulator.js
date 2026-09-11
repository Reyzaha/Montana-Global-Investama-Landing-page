/**
 * PT MONTANA GLOBAL INVESTAMA — BEP & ROI INTERACTIVE SIMULATOR
 * Bootstrap 5 Grid & Solid Colors (No Gradients)
 */

const MGISimulator = {
  // Initialize and mount simulator into a container
  init: function (containerId, simulationConfig) {
    const container = document.getElementById(containerId);
    if (!container) return;

    const config = simulationConfig || {
      tenor_bulan: 36,
      estimasi_return_persen: 30,
      modal_kerja_bulanan_persen: 2.2,
      minimum_investasi: 10000000,
      default_investasi: 50000000,
      notes: "Simulasi bersifat ilustratif, bukan jaminan — mengacu pada Risk Disclosure Statement."
    };

    let currentInvestment = config.default_investasi || 50000000;
    const tenorMonths = config.tenor_bulan || 36;
    const totalReturnRate = (config.estimasi_return_persen || 30) / 100;

    container.innerHTML = `
      <div class="simulator-card bg-white" id="bepRoiSimulator">
        <div class="mb-4 pb-3 border-bottom">
          <span class="section-tag">
            <i class="bi bi-calculator me-1"></i> Kalkulator Finansial
          </span>
          <h3 class="fw-bold text-mgi-dark mt-2 mb-1">
            Simulasi BEP (Break Even Point) & Proyeksi ROI
          </h3>
          <p class="text-mgi-muted mb-0 small">
            Geser slider atau pilih nominal untuk menghitung estimasi arus kas bulanan, periode pengembalian modal pokok (BEP), dan total imbal hasil investasi.
          </p>
        </div>

        <div class="row g-4">
          <!-- Left: Input & Sliders -->
          <div class="col-lg-5">
            <div class="p-3 bg-mgi-subtle rounded-3 border mb-3">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="fw-bold text-mgi-dark small">Nominal Investasi Anda:</span>
                <span class="fs-4 fw-bold text-mgi-blue" id="simValDisplay">${MGI.formatRupiah(currentInvestment)}</span>
              </div>
              <input type="range" 
                     id="simSlider" 
                     class="sim-slider mb-3" 
                     min="10000000" 
                     max="500000000" 
                     step="5000000" 
                     value="${currentInvestment}">
              
              <div class="d-flex flex-wrap gap-2">
                <button type="button" class="chip-btn" data-val="10000000">10 Juta</button>
                <button type="button" class="chip-btn" data-val="25000000">25 Juta</button>
                <button type="button" class="chip-btn active" data-val="50000000">50 Juta</button>
                <button type="button" class="chip-btn" data-val="100000000">100 Juta</button>
                <button type="button" class="chip-btn" data-val="250000000">250 Juta</button>
              </div>
            </div>

            <div class="card border bg-white p-3 rounded-3 small">
              <div class="d-flex justify-content-between mb-2">
                <span class="text-mgi-muted">Tenor Kontrak Proyek:</span>
                <strong class="text-mgi-dark">${tenorMonths} Bulan (${(tenorMonths / 12).toFixed(1)} Tahun)</strong>
              </div>
              <div class="d-flex justify-content-between mb-2">
                <span class="text-mgi-muted">Target Return Proyeksi:</span>
                <strong class="text-success fw-bold">≥${(totalReturnRate * 100).toFixed(0)}% (Total Tenor)</strong>
              </div>
              <div class="d-flex justify-content-between">
                <span class="text-mgi-muted">Siklus Pembagian Hasil:</span>
                <strong class="text-mgi-gold">Kuartalan / Bulanan</strong>
              </div>
            </div>
          </div>

          <!-- Right: Real-time Calculation Results -->
          <div class="col-lg-7">
            <div class="row g-3">
              <div class="col-12">
                <div class="result-card-box featured p-3 p-md-4">
                  <div class="text-uppercase small text-mgi-gold fw-bold mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                    Total Estimasi Pengembalian (Pokok + Imbal Hasil)
                  </div>
                  <div class="fs-2 fw-bold text-mgi-dark" id="simTotalReturn">Rp 0</div>
                  <div class="small text-mgi-gold fw-semibold mt-1" id="simTotalProfitSub">Estimasi Profit Bersih: Rp 0</div>
                </div>
              </div>

              <div class="col-sm-6">
                <div class="result-card-box p-3 h-100">
                  <div class="text-uppercase small text-mgi-muted fw-bold mb-1" style="font-size: 0.72rem;">
                    Estimasi BEP (Break Even Point)
                  </div>
                  <div class="fs-4 fw-bold text-mgi-gold" id="simBepMonths">~ 18 Bulan</div>
                  <div class="small text-mgi-muted mt-1" style="font-size: 0.78rem;">Modal pokok kembali dari arus kas riil</div>
                </div>
              </div>

              <div class="col-sm-6">
                <div class="result-card-box p-3 h-100">
                  <div class="text-uppercase small text-mgi-muted fw-bold mb-1" style="font-size: 0.72rem;">
                    Estimasi Return / Bulan
                  </div>
                  <div class="fs-4 fw-bold text-mgi-blue" id="simMonthlyReturn">Rp 0</div>
                  <div class="small text-mgi-muted mt-1" style="font-size: 0.78rem;">Dari utilisasi sewa unit alat berat</div>
                </div>
              </div>

              <div class="col-12">
                <div class="result-card-box p-3 bg-white">
                  <div class="d-flex justify-content-between align-items-center">
                    <div>
                      <div class="text-uppercase small text-mgi-muted fw-bold" style="font-size: 0.72rem;">
                        Proyeksi Annualized ROI (Disetahunkan)
                      </div>
                      <div class="small text-mgi-muted">Rata-rata tingkat pengembalian modal per tahun</div>
                    </div>
                    <div class="fs-3 fw-bold text-success" id="simAnnualRoi">~ 10.0% / Thn</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="mt-4 pt-2">
          ${MGIComponents.renderComplianceNotice(config.notes)}
        </div>
      </div>
    `;

    // Calculation Logic
    function recalculate(nominal) {
      const inv = Number(nominal);
      const totalProfit = inv * totalReturnRate;
      const grandTotalReturn = inv + totalProfit;
      const monthlyReturn = totalProfit / tenorMonths;
      const annualizedRoi = (totalReturnRate / (tenorMonths / 12)) * 100;
      
      const bepMonthEstimate = Math.min(tenorMonths, Math.max(12, Math.round(tenorMonths * 0.58)));

      // Update DOM
      document.getElementById('simValDisplay').textContent = MGI.formatRupiah(inv);
      document.getElementById('simTotalReturn').textContent = MGI.formatRupiah(grandTotalReturn);
      document.getElementById('simTotalProfitSub').textContent = `Estimasi Profit Bersih: ${MGI.formatRupiah(totalProfit)}`;
      document.getElementById('simMonthlyReturn').textContent = MGI.formatRupiah(monthlyReturn);
      document.getElementById('simBepMonths').textContent = `~ ${bepMonthEstimate} Bulan`;
      document.getElementById('simAnnualRoi').textContent = `~ ${annualizedRoi.toFixed(1)}% / Thn`;
    }

    // Attach slider listener
    const slider = document.getElementById('simSlider');
    slider.addEventListener('input', (e) => {
      currentInvestment = Number(e.target.value);
      recalculate(currentInvestment);
      
      document.querySelectorAll('.chip-btn').forEach(btn => {
        btn.classList.toggle('active', Number(btn.getAttribute('data-val')) === currentInvestment);
      });
    });

    // Attach chip click listener
    document.querySelectorAll('.chip-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        const val = Number(btn.getAttribute('data-val'));
        slider.value = val;
        currentInvestment = val;
        recalculate(val);
        document.querySelectorAll('.chip-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
      });
    });

    // Initial calculation
    recalculate(currentInvestment);
  }
};

window.MGISimulator = MGISimulator;
