/**
 * growth-chart.js
 * Visualisasi Pertumbuhan Kinerja Montana Group (2020 - 2026)
 * Menampilkan performa segmen: Konsolidasi MGI, PT Montana Indo Utama (MIU), dan Montana Sentra Industri (MSI).
 */

let MGIGrowthData = {
  years: ['2020', '2021', '2022', '2023', '2024', '2025', '2026 (P)'],
  
  // Data Konsolidasi Holding MGI (Nilai Aset Terkelola / AUM dalam Miliar IDR)
  mgi: {
    title: 'Konsolidasi Grup PT Montana Global Investama',
    subtitle: 'Pertumbuhan Nilai Aset Terkelola (AUM) & Skala Portofolio Project Investasi Sektor Riil',
    badge: 'Holding & Project Investment Manager',
    badgeClass: 'bg-dark',
    chartLabel: 'Nilai Aset Terkelola / AUM (Rp Miliar)',
    data: [24, 42, 80, 136, 214, 317, 435],
    unit: 'Miliar IDR',
    kpis: [
      { label: 'Total Aset Terkelola (2025)', value: 'Rp 100 M', note: 'Target 2026: Rp 150 M', icon: 'bi-bank2', color: 'text-royal' },
      { label: 'Tingkat Ketepatan Bagi Hasil', value: '100%', note: 'On-Time Payout Rekam Jejak', icon: 'bi-check-circle-fill', color: 'text-success' },
      { label: 'Rasio Default (NPA)', value: '0.0%', note: 'Underlying Asset Terproteksi', icon: 'bi-shield-fill-check', color: 'text-gold' },
      { label: 'Batch Sindikasi Teralokasi', value: '18 Batch', note: 'Jabodetabek, Surabaya, Denpasar, Makassar', icon: 'bi-layers-fill', color: 'text-primary' }
    ]
  },

  // Data PT Montana Indo Utama (MIU) - Alat Berat Khusus Komatsu & Armada Towing
  miu: {
    title: 'PT Montana Indo Utama (MIU)',
    subtitle: 'Populasi Armada Khusus Komatsu CBU Jepang, Nilai Kapitalisasi & Ekspansi Wilayah',
    badge: 'Armada Komatsu & Workshop Sentral',
    badgeClass: 'bg-royal text-white',
    chartLabel: 'Populasi Unit Komatsu Aktif (Unit)',
    secondaryChartLabel: 'Nilai Kapitalisasi Unit (Rp Miliar)',
    data: [16, 28, 45, 68, 92, 125, 165],
    assetValue: [24, 42, 68, 105, 148, 208, 280],
    unit: 'Unit',
    kpis: [
      { label: 'Populasi Komatsu Aktif', value: '125 Unit', note: 'Eksklusif Merek Komatsu', icon: 'bi-truck-flatbed', color: 'text-royal' },
      { label: 'Nilai Kapitalisasi Unit', value: 'Rp 208 M+', note: 'Aset Fisik Liquid Grade A', icon: 'bi-cash-coin', color: 'text-gold' },
      { label: 'Stok Unit Tersedia di Pool', value: '24 Unit Ready', note: 'Pool Kebumen & Siap Mobilisasi', icon: 'bi-check2-square', color: 'text-success' },
      { label: 'Wilayah Operasional Aktif', value: 'Jateng, Bali, NTB', note: 'Rencana Ekspansi Berkelanjutan', icon: 'bi-geo-alt-fill', color: 'text-primary' }
    ]
  },

  // Data Montana Sentra Industri (MSI) - Supply Import Jepang & Cina
  msi: {
    title: 'Montana Sentra Industri (MSI)',
    subtitle: 'Supply Import Langsung dari Jepang atau Cina ke Indonesia Sampai ke Alamat Customer',
    badge: 'Supply Import Direct to Customer',
    badgeClass: 'bg-gold text-white',
    chartLabel: 'Kapasitas Output & Supply Unit (Ton/Thn)',
    secondaryChartLabel: 'Nilai Logistik Impor (Rp Miliar)',
    data: [0, 0, 350, 820, 1650, 2800, 4200],
    assetValue: [0, 0, 8, 21, 46, 82, 125],
    unit: 'Ton/Tahun',
    kpis: [
      { label: 'Volume Supply Import (2025)', value: '2.800 Ton', note: 'Target 2026: 4.200 Ton/Thn', icon: 'bi-ship', color: 'text-gold' },
      { label: 'Pengiriman Door-to-Door', value: '100%', note: 'Sampai Alamat Customer', icon: 'bi-box-seam-fill', color: 'text-success' },
      { label: 'Efisiensi Rantai Pasok', value: '31.2%', note: 'Pangkas Biaya Perantara', icon: 'bi-graph-down-arrow', color: 'text-royal' },
      { label: 'Asal Pengadaan Import', value: 'Jepang & Cina', note: 'Mitra Manufaktur Resmi', icon: 'bi-globe-americas', color: 'text-primary' }
    ]
  }
};

let mgiChartInstance = null;

function renderGrowthKpis(kpis) {
  const container = document.getElementById('growthKpiContainer');
  if (!container) return;

  container.innerHTML = kpis.map(kpi => `
    <div class="col-6 col-md-3">
      <div class="card mgi-card bg-white p-3 rounded-3 h-100 shadow-sm border border-subtle">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <span class="small text-secondary fw-medium">${kpi.label}</span>
          <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-light ${kpi.color}" style="width: 34px; height: 34px;">
            <i class="bi ${kpi.icon}"></i>
          </div>
        </div>
        <div class="fs-4 fw-bold text-dark mb-1">${kpi.value}</div>
        <div class="small text-muted" style="font-size: 0.78rem;">${kpi.note}</div>
      </div>
    </div>
  `).join('');
}

let hasFetchedGrowthData = false;

async function loadGrowthData() {
  if (hasFetchedGrowthData) return;
  try {
    const res = await fetch('api/growth.php');
    if (res.ok) {
      const json = await res.json();
      if (json && json.years && json.mgi) {
        MGIGrowthData = json;
        hasFetchedGrowthData = true;
      }
    }
  } catch (e) {
    console.warn('Fallback to local default growth data:', e);
  }
}

async function initGrowthChart(activeSegment = 'mgi') {
  await loadGrowthData();
  const ctx = document.getElementById('mgiGrowthCanvas');
  if (!ctx) return;

  if (mgiChartInstance) {
    mgiChartInstance.destroy();
  }

  const segmentTitle = document.getElementById('growthSegmentTitle');
  const segmentSubtitle = document.getElementById('growthSegmentSubtitle');
  const segmentBadge = document.getElementById('growthSegmentBadge');

  if (activeSegment === 'compare') {
    // Mode Perbandingan MIU vs MSI
    if (segmentTitle) segmentTitle.innerText = 'Perbandingan Kinerja: MIU vs MSI (2020 - 2026)';
    if (segmentSubtitle) segmentSubtitle.innerText = 'Korelasi pertumbuhan antara distribusi armada alat berat (MIU) dan kapasitas manufaktur penunjang (MSI).';
    if (segmentBadge) {
      segmentBadge.className = 'badge bg-royal text-white px-3 py-2 rounded-pill small fw-bold';
      segmentBadge.innerText = 'Sinergi Lintas Entitas';
    }

    renderGrowthKpis([
      { label: 'Armada Aktif MIU (2025)', value: '124 Unit', note: '+34.7% YoY Growth', icon: 'bi-truck-flatbed', color: 'text-royal' },
      { label: 'Output Fabrikasi MSI (2025)', value: '2.800 Ton', note: '+69.6% YoY Growth', icon: 'bi-gear-wide-connected', color: 'text-gold' },
      { label: 'Utilisasi Armada MIU', value: '94.8%', note: 'Offtake Agreement Aktif', icon: 'bi-speedometer2', color: 'text-success' },
      { label: 'Kemandirian Pasokan MSI', value: '72%', note: 'Suku Cadang & Attachment', icon: 'bi-shield-check', color: 'text-primary' }
    ]);

    mgiChartInstance = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: MGIGrowthData.years,
        datasets: [
          {
            label: 'MIU - Populasi Alat Berat (Unit)',
            data: MGIGrowthData.miu.data,
            backgroundColor: '#1D3589',
            borderColor: '#0F2C59',
            borderWidth: 1,
            borderRadius: 6,
            yAxisID: 'y'
          },
          {
            label: 'MSI - Kapasitas Fabrikasi (Ton)',
            data: MGIGrowthData.msi.data,
            backgroundColor: '#C5A059',
            borderColor: '#9E7E36',
            borderWidth: 1,
            borderRadius: 6,
            yAxisID: 'y1'
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
          mode: 'index',
          intersect: false
        },
        plugins: {
          legend: {
            position: 'top',
            labels: { font: { family: 'Inter', weight: 'bold', size: 12 }, usePointStyle: true, padding: 15 }
          },
          tooltip: {
            backgroundColor: '#0F2C59',
            titleFont: { family: 'Outfit', size: 13, weight: 'bold' },
            bodyFont: { family: 'Inter', size: 12 },
            padding: 12,
            cornerRadius: 8
          }
        },
        scales: {
          x: {
            grid: { display: false },
            ticks: { font: { family: 'Inter', weight: '600' } }
          },
          y: {
            type: 'linear',
            display: true,
            position: 'left',
            title: { display: true, text: 'MIU: Jumlah Unit Armada', font: { family: 'Inter', weight: 'bold', size: 11 } },
            grid: { color: '#E2E8F0' }
          },
          y1: {
            type: 'linear',
            display: true,
            position: 'right',
            title: { display: true, text: 'MSI: Kapasitas Fabrikasi (Ton)', font: { family: 'Inter', weight: 'bold', size: 11 } },
            grid: { drawOnChartArea: false }
          }
        }
      }
    });
    return;
  }

  // Single Segment Mode (MGI, MIU, or MSI)
  const currentSeg = MGIGrowthData[activeSegment];
  if (!currentSeg) return;

  if (segmentTitle) segmentTitle.innerText = currentSeg.title;
  if (segmentSubtitle) segmentSubtitle.innerText = currentSeg.subtitle;
  if (segmentBadge) {
    segmentBadge.className = `badge ${currentSeg.badgeClass} px-3 py-2 rounded-pill small fw-bold`;
    segmentBadge.innerText = currentSeg.badge;
  }

  renderGrowthKpis(currentSeg.kpis);

  // Setup datasets based on segment
  let datasets = [];
  if (activeSegment === 'mgi') {
    datasets = [
      {
        type: 'line',
        label: 'Nilai Aset Terkelola / AUM (Rp Miliar)',
        data: currentSeg.data,
        borderColor: '#C5A059',
        backgroundColor: 'rgba(197, 160, 89, 0.12)',
        borderWidth: 3,
        fill: true,
        tension: 0.35,
        pointBackgroundColor: '#C5A059',
        pointBorderColor: '#FFFFFF',
        pointBorderWidth: 2,
        pointRadius: 6,
        pointHoverRadius: 8
      }
    ];
  } else if (activeSegment === 'miu') {
    datasets = [
      {
        type: 'bar',
        label: 'Populasi Armada CBU Aktif (Unit)',
        data: currentSeg.data,
        backgroundColor: '#1D3589',
        borderColor: '#0F2C59',
        borderWidth: 1,
        borderRadius: 6,
        yAxisID: 'y'
      },
      {
        type: 'line',
        label: 'Nilai Kapitalisasi Armada (Rp Miliar)',
        data: currentSeg.assetValue,
        borderColor: '#C5A059',
        backgroundColor: 'transparent',
        borderWidth: 3,
        tension: 0.3,
        pointBackgroundColor: '#C5A059',
        pointRadius: 5,
        yAxisID: 'y1'
      }
    ];
  } else if (activeSegment === 'msi') {
    datasets = [
      {
        type: 'bar',
        label: 'Kapasitas Output Fabrikasi (Ton)',
        data: currentSeg.data,
        backgroundColor: '#C5A059',
        borderColor: '#9E7E36',
        borderWidth: 1,
        borderRadius: 6,
        yAxisID: 'y'
      },
      {
        type: 'line',
        label: 'Nilai Perputaran Manufaktur (Rp Miliar)',
        data: currentSeg.assetValue,
        borderColor: '#1D3589',
        backgroundColor: 'transparent',
        borderWidth: 3,
        tension: 0.3,
        pointBackgroundColor: '#1D3589',
        pointRadius: 5,
        yAxisID: 'y1'
      }
    ];
  }

  const hasDualAxis = datasets.length > 1;

  mgiChartInstance = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: MGIGrowthData.years,
      datasets: datasets
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      interaction: {
        mode: 'index',
        intersect: false
      },
      plugins: {
        legend: {
          position: 'top',
          labels: { font: { family: 'Inter', weight: 'bold', size: 12 }, usePointStyle: true, padding: 15 }
        },
        tooltip: {
          backgroundColor: '#0F2C59',
          titleFont: { family: 'Outfit', size: 13, weight: 'bold' },
          bodyFont: { family: 'Inter', size: 12 },
          padding: 12,
          cornerRadius: 8,
          callbacks: {
            label: function(context) {
              let label = context.dataset.label || '';
              if (label) label += ': ';
              if (context.parsed.y !== null) {
                if (label.includes('Miliar')) {
                  label += 'Rp ' + context.parsed.y + ' Miliar';
                } else if (label.includes('Unit')) {
                  label += context.parsed.y + ' Unit Armada';
                } else if (label.includes('Ton')) {
                  label += context.parsed.y + ' Ton Output';
                } else {
                  label += context.parsed.y;
                }
              }
              return label;
            }
          }
        }
      },
      scales: {
        x: {
          grid: { display: false },
          ticks: { font: { family: 'Inter', weight: '600' } }
        },
        y: {
          type: 'linear',
          display: true,
          position: 'left',
          grid: { color: '#E2E8F0' },
          ticks: {
            callback: function(value) {
              return activeSegment === 'mgi' ? 'Rp ' + value + ' M' : value;
            }
          }
        },
        ...(hasDualAxis ? {
          y1: {
            type: 'linear',
            display: true,
            position: 'right',
            grid: { drawOnChartArea: false },
            ticks: {
              callback: function(value) {
                return 'Rp ' + value + ' M';
              }
            }
          }
        } : {})
      }
    }
  });
}

// Global hook
window.initGrowthChart = initGrowthChart;
