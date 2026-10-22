/**
 * PT MONTANA GLOBAL INVESTAMA — KEBIJAKAN PRIVASI & KEPATUHAN UU PDP
 * Versi Resmi: 1.0 | Terakhir Diperbarui: 22 Oktober 2026
 *
 * Modul ini menjadi Single Source of Truth untuk teks Kebijakan Privasi,
 * digunakan pada halaman penuh (/kebijakan-privasi), modal registrasi,
 * serta alur persetujuan login Google & biodata pemodal.
 */

(function (window) {
  'use strict';

  const MGIPrivacyPolicy = {
    VERSION: '1.0',
    EFFECTIVE_DATE: '22 Oktober 2026',
    LAST_UPDATED: '22 Oktober 2026',
    CONTROLLER: {
      name: 'PT Montana Global Investama',
      address: 'Roseville Soho & Suite, Sunburst CBD Lot I.8, Jl. Kapten Soebijanto Djojohadikusumo, Lengkong Gudang, Kec. Serpong, Kota Tangerang Selatan, Banten 15321',
      email: 'contact@montanainvestama.com',
      phone: '0858-2050-0786',
      dpo: 'Manager Operasional'
    },

    /**
     * Menghasilkan markup HTML lengkap kebijakan privasi
     * @param {Object} opts - Opsi rendering { isModal: boolean, showHeader: boolean }
     * @returns {string} HTML string
     */
    getHtml: function (opts = {}) {
      const isModal = !!opts.isModal;
      const showHeader = opts.showHeader !== false;

      const headerHtml = showHeader ? `
        <div class="mgi-privacy-header ${isModal ? 'pb-3 mb-3 border-bottom' : 'mb-4'}">
          <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
            <span class="badge bg-royal text-gold px-3 py-1.5 rounded-pill fw-bold" style="font-size: 0.8rem; letter-spacing: 0.5px;">
              <i class="bi bi-shield-check me-1"></i> Versi ${this.VERSION} • UU PDP No. 27/2022
            </span>
            <span class="text-secondary small">
              <i class="bi bi-clock-history me-1"></i> Terakhir diperbarui: <strong>${this.LAST_UPDATED}</strong>
            </span>
          </div>
          <h2 class="${isModal ? 'fs-5' : 'fs-3'} fw-bold text-dark mb-1">
            Kebijakan Privasi PT Montana Global Investama
          </h2>
          <p class="text-secondary small mb-0">
            Berlaku efektif sejak <strong>${this.EFFECTIVE_DATE}</strong> untuk seluruh pengguna situs web, aplikasi seluler, dan portal investor digital.
          </p>
        </div>
      ` : '';

      return `
        <div class="mgi-privacy-document ${isModal ? 'mgi-privacy-modal-view' : 'mgi-privacy-page-view'}">
          ${headerHtml}

          <!-- 1. PENDAHULUAN -->
          <section class="mb-4">
            <h5 class="fw-bold text-dark d-flex align-items-center gap-2 mb-2">
              <span class="badge bg-light text-royal border border-subtle rounded-circle" style="width:28px; height:28px; display:inline-flex; align-items:center; justify-content:center; font-size:0.85rem;">1</span>
              Pendahuluan
            </h5>
            <p class="text-secondary small lh-base mb-2">
              PT Montana Global Investama (“Perusahaan”, “Kami”) menghargai dan melindungi data pribadi Anda. Kebijakan Privasi ini menjelaskan bagaimana Kami mengumpulkan, menggunakan, menyimpan, mengamankan, mengungkapkan, dan menghapus Data Pribadi Anda saat Anda menggunakan situs web, aplikasi, dan layanan digital Kami (“Layanan”), terutama pada proses verifikasi data dan tata kelola penempatan modal.
            </p>
            <p class="text-secondary small lh-base mb-2">
              Kebijakan ini disusun dengan merujuk pada:
            </p>
            <ul class="text-secondary small lh-base mb-2 ps-3">
              <li>Undang-Undang Nomor 27 Tahun 2022 tentang Pelindungan Data Pribadi (“UU PDP”);</li>
              <li>Undang-Undang Nomor 11 Tahun 2008 tentang Informasi dan Transaksi Elektronik sebagaimana telah diubah dengan Undang-Undang Nomor 1 Tahun 2024 (“UU ITE”); dan</li>
              <li>Peraturan Pemerintah Nomor 71 Tahun 2019 tentang Penyelenggaraan Sistem dan Transaksi Elektronik (“PP PSTE”).</li>
            </ul>
            <p class="text-secondary small lh-base mb-0">
              Mohon baca Kebijakan ini dengan saksama. Membaca atau menggunakan Layanan tidak dengan sendirinya berarti Anda telah memberikan persetujuan pemrosesan data. Persetujuan diminta secara tersendiri dan eksplisit sebagaimana diatur pada Bagian 6.
            </p>
          </section>

          <!-- 2. IDENTITAS PENGENDALI DATA PRIBADI -->
          <section class="mb-4 p-3 bg-light rounded-3 border border-subtle">
            <h5 class="fw-bold text-dark d-flex align-items-center gap-2 mb-2">
              <span class="badge bg-royal text-white rounded-circle" style="width:28px; height:28px; display:inline-flex; align-items:center; justify-content:center; font-size:0.85rem;">2</span>
              Identitas Pengendali Data Pribadi
            </h5>
            <p class="text-secondary small lh-base mb-2">
              Pengendali Data Pribadi yang bertanggung jawab atas pemrosesan data Anda adalah:
            </p>
            <div class="row g-2 small text-dark">
              <div class="col-12 col-md-6">
                <strong>Nama Badan Usaha:</strong><br>
                <span class="text-secondary">${this.CONTROLLER.name}</span>
              </div>
              <div class="col-12 col-md-6">
                <strong>Email Resmi Pelindungan Data:</strong><br>
                <a href="mailto:${this.CONTROLLER.email}" class="text-royal fw-semibold">${this.CONTROLLER.email}</a>
              </div>
              <div class="col-12 col-md-6">
                <strong>Saluran Telepon / Hotline:</strong><br>
                <span class="text-secondary">${this.CONTROLLER.phone}</span>
              </div>
              <div class="col-12 col-md-6">
                <strong>Pejabat / Petugas PDP (DPO):</strong><br>
                <span class="text-secondary">${this.CONTROLLER.dpo}</span>
              </div>
              <div class="col-12">
                <strong>Alamat Kantor Operasional:</strong><br>
                <span class="text-secondary">${this.CONTROLLER.address}</span>
              </div>
            </div>
          </section>

          <!-- 3. PRINSIP PELINDUNGAN DATA PRIBADI -->
          <section class="mb-4">
            <h5 class="fw-bold text-dark d-flex align-items-center gap-2 mb-2">
              <span class="badge bg-light text-royal border border-subtle rounded-circle" style="width:28px; height:28px; display:inline-flex; align-items:center; justify-content:center; font-size:0.85rem;">3</span>
              Prinsip Pelindungan Data Pribadi yang Kami Terapkan
            </h5>
            <p class="text-secondary small lh-base mb-2">Dalam setiap pemrosesan Data Pribadi, Kami berpegang teguh pada prinsip berikut:</p>
            <ul class="text-secondary small lh-base mb-0 ps-3">
              <li><strong>Pembatasan:</strong> Kami hanya mengumpulkan data yang relevan dan spesifik, dan tidak melebihi tujuan verifikasi itu sendiri.</li>
              <li><strong>Sah secara hukum:</strong> Kami memiliki dasar hukum yang sah sebelum meminta dan memproses data Anda.</li>
              <li><strong>Transparan:</strong> Kami menyampaikan informasi yang jelas tentang apa yang Kami kumpulkan, mengapa, dan untuk berapa lama.</li>
              <li><strong>Sesuai tujuan:</strong> Data tidak Kami gunakan untuk tujuan lain tanpa dasar hukum atau persetujuan baru dari Anda.</li>
              <li><strong>Akurat:</strong> Kami menjaga akurasi, kelengkapan, dan konsistensi data.</li>
              <li><strong>Aman:</strong> Kami menerapkan pengamanan teknis dan operasional yang memadai.</li>
              <li><strong>Retensi terbatas:</strong> Data tidak disimpan lebih lama dari yang diperlukan.</li>
            </ul>
          </section>

          <!-- 4. DATA PRIBADI YANG KAMI KUMPULKAN -->
          <section class="mb-4">
            <h5 class="fw-bold text-dark d-flex align-items-center gap-2 mb-2">
              <span class="badge bg-light text-royal border border-subtle rounded-circle" style="width:28px; height:28px; display:inline-flex; align-items:center; justify-content:center; font-size:0.85rem;">4</span>
              Data Pribadi yang Kami Kumpulkan
            </h5>
            <p class="text-secondary small lh-base mb-2">
              Kami hanya meminta data yang benar-benar diperlukan untuk tujuan verifikasi dan penyelenggaraan Layanan. Data yang diminta dapat mencakup, sesuai kebutuhan masing-masing Layanan:
            </p>
            <ul class="text-secondary small lh-base mb-2 ps-3">
              <li><strong>Data identitas:</strong> nama lengkap, Nomor Induk Kependudukan (NIK), tempat dan tanggal lahir, kewarganegaraan, serta foto atau salinan KTP atau dokumen identitas resmi lainnya.</li>
              <li><strong>Data kontak:</strong> alamat email, nomor telepon / WhatsApp, dan alamat tempat tinggal.</li>
              <li><strong>Data biometrik:</strong> swafoto (selfie) atau foto wajah untuk dicocokkan dengan foto pada dokumen identitas. Data ini bersifat spesifik dan hanya Kami proses dengan persetujuan eksplisit yang terpisah.</li>
              <li><strong>Data keuangan</strong> (hanya jika relevan dengan Layanan tertentu): NPWP, nomor rekening bank untuk pencairan dividen/bagi hasil, dan riwayat penempatan modal.</li>
              <li><strong>Data akun dan teknis:</strong> nama pengguna, catatan akses (log), alamat IP, jenis perangkat, dan waktu akses, yang digunakan untuk keamanan dan pencegahan penipuan.</li>
            </ul>
            <p class="text-secondary small lh-base mb-0">
              Kami mengumpulkan data langsung dari Anda. Apabila Kami memperoleh data dari sumber lain (misalnya instansi resmi untuk pencocokan data kependudukan), hal itu hanya dilakukan dengan dasar hukum yang sah dan terbatas pada tujuan verifikasi.
            </p>
          </section>

          <!-- 5. TUJUAN DAN DASAR HUKUM PEMROSESAN -->
          <section class="mb-4">
            <h5 class="fw-bold text-dark d-flex align-items-center gap-2 mb-2">
              <span class="badge bg-light text-royal border border-subtle rounded-circle" style="width:28px; height:28px; display:inline-flex; align-items:center; justify-content:center; font-size:0.85rem;">5</span>
              Tujuan dan Dasar Hukum Pemrosesan
            </h5>
            <p class="text-secondary small lh-base mb-2"><strong>Tujuan pemrosesan:</strong></p>
            <ul class="text-secondary small lh-base mb-2 ps-3">
              <li>Memverifikasi identitas Anda dan memastikan kebenaran data yang diberikan;</li>
              <li>Membuat, mengelola, dan mengamankan akun investor Anda;</li>
              <li>Melaksanakan perjanjian kemitraan dan menyediakan Layanan yang Anda minta;</li>
              <li>Mencegah penipuan, penyalahgunaan identitas, dan akses tidak sah;</li>
              <li>Memenuhi kewajiban berdasarkan peraturan perundang-undangan (termasuk kepatuhan APU-PPT); dan</li>
              <li>Mengirimkan informasi penting terkait Layanan, pembaruan operasional, dan akun Anda.</li>
            </ul>
            <p class="text-secondary small lh-base mb-0">
              <strong>Dasar hukum pemrosesan:</strong> persetujuan eksplisit dari Anda sebagai pemilik data; pemenuhan kewajiban perjanjian yang Anda buat dengan Kami; dan/atau pemenuhan kewajiban hukum yang ditentukan oleh peraturan perundang-undangan. Kami tidak menggunakan data verifikasi Anda untuk tujuan di luar yang tercantum di atas tanpa persetujuan baru dari Anda.
            </p>
          </section>

          <!-- 6. PERSETUJUAN ANDA -->
          <section class="mb-4 p-3 bg-light rounded-3 border border-subtle">
            <h5 class="fw-bold text-dark d-flex align-items-center gap-2 mb-2">
              <span class="badge bg-royal text-white rounded-circle" style="width:28px; height:28px; display:inline-flex; align-items:center; justify-content:center; font-size:0.85rem;">6</span>
              Persetujuan Anda
            </h5>
            <p class="text-secondary small lh-base mb-2">
              Sesuai UU PDP dan Pasal 26 ayat (1) UU ITE, penggunaan informasi yang menyangkut Data Pribadi melalui media elektronik wajib dilakukan atas persetujuan Anda, kecuali ditentukan lain oleh peraturan perundang-undangan. Karena itu:
            </p>
            <ul class="text-secondary small lh-base mb-0 ps-3">
              <li>Persetujuan Kami minta sebelum data Anda dikumpulkan.</li>
              <li>Persetujuan diberikan secara tertulis atau terekam secara elektronik, melalui kotak centang (checkbox) opt-in yang <strong>tidak tercentang otomatis</strong> dan disertai tautan ke Kebijakan Privasi ini.</li>
              <li>Kalimat persetujuan Kami tulis dengan bahasa yang jelas tanpa klausul rancu atau jebakan hukum.</li>
              <li>Persetujuan untuk tujuan yang berbeda diminta secara terpisah, misalnya persetujuan data biometrik dan promosi opsional. Menolak informasi promosi tidak memengaruhi akses Anda ke Layanan.</li>
              <li>Kami mencatat bukti persetujuan Anda secara elektronik (waktu persetujuan dan versi Kebijakan yang disetujui).</li>
              <li>Anda dapat menarik persetujuan kapan saja sesuai Bagian 13.</li>
            </ul>
          </section>

          <!-- 7. INFORMASI SAAT MEMINTA DATA -->
          <section class="mb-4">
            <h5 class="fw-bold text-dark d-flex align-items-center gap-2 mb-2">
              <span class="badge bg-light text-royal border border-subtle rounded-circle" style="width:28px; height:28px; display:inline-flex; align-items:center; justify-content:center; font-size:0.85rem;">7</span>
              Informasi yang Kami Sampaikan Saat Meminta Data
            </h5>
            <p class="text-secondary small lh-base mb-0">
              Saat meminta data verifikasi, Kami menyampaikan secara transparan: legalitas dan tujuan pemrosesan data; jenis dan relevansi data yang diminta; jangka waktu retensi atau penyimpanan data; serta hak-hak Anda sebagai pemilik data termasuk hak menghapus data dan menarik persetujuan.
            </p>
          </section>

          <!-- 8. PENGGUNAAN DAN PENGUNGKAPAN KEPADA PIHAK LAIN -->
          <section class="mb-4">
            <h5 class="fw-bold text-dark d-flex align-items-center gap-2 mb-2">
              <span class="badge bg-light text-royal border border-subtle rounded-circle" style="width:28px; height:28px; display:inline-flex; align-items:center; justify-content:center; font-size:0.85rem;">8</span>
              Penggunaan dan Pengungkapan kepada Pihak Lain
            </h5>
            <p class="text-secondary small lh-base mb-2">
              <strong>Kami tidak menjual Data Pribadi Anda.</strong> Data Anda hanya dapat dibagikan kepada:
            </p>
            <ul class="text-secondary small lh-base mb-2 ps-3">
              <li>Penyedia layanan dan mitra teknologi (misalnya penyedia infrastruktur server atau layanan verifikasi identitas) yang bertindak atas instruksi Kami dan terikat perjanjian kerahasiaan ketat;</li>
              <li>Instansi pemerintah, regulator, atau aparat penegak hukum, apabila diwajibkan oleh peraturan perundang-undangan atau putusan pengadilan yang sah; dan</li>
              <li>Pihak lain dengan persetujuan eksplisit Anda.</li>
            </ul>
            <p class="text-secondary small lh-base mb-0">
              Apabila data Anda perlu diproses di luar wilayah Indonesia, Kami memastikan negara tujuan memiliki tingkat pelindungan data pribadi yang setara atau lebih tinggi, atau Kami menerapkan pengamanan yang memadai sesuai hukum.
            </p>
          </section>

          <!-- 9. RETENSI DAN PENGHAPUSAN DATA -->
          <section class="mb-4">
            <h5 class="fw-bold text-dark d-flex align-items-center gap-2 mb-2">
              <span class="badge bg-light text-royal border border-subtle rounded-circle" style="width:28px; height:28px; display:inline-flex; align-items:center; justify-content:center; font-size:0.85rem;">9</span>
              Jangka Waktu Retensi dan Penghapusan Data
            </h5>
            <p class="text-secondary small lh-base mb-2">
              Kami menyimpan Data Pribadi hanya selama diperlukan untuk tujuan pemrosesan:
            </p>
            <ul class="text-secondary small lh-base mb-2 ps-3">
              <li><strong>Data identitas dan kontak:</strong> selama akun Anda aktif, dan setelah akun ditutup paling lama <strong>5 (lima) tahun</strong> apabila diperlukan untuk pemenuhan kewajiban hukum, pembuktian transaksi, atau penyelesaian sengketa perdata.</li>
              <li><strong>Data biometrik (swafoto):</strong> paling lama <strong>30 (tiga puluh) hari</strong> setelah proses verifikasi selesai.</li>
              <li><strong>Data akun dan teknis (log akses):</strong> paling lama <strong>12 (dua belas) bulan</strong>.</li>
            </ul>
            <p class="text-secondary small lh-base mb-0">
              Sesuai PP PSTE, Kami wajib menghapus Data Pribadi Anda apabila masa retensi telah berakhir atau Anda mengajukan penarikan persetujuan pemrosesan yang sah.
            </p>
          </section>

          <!-- 10. KEAMANAN DATA -->
          <section class="mb-4">
            <h5 class="fw-bold text-dark d-flex align-items-center gap-2 mb-2">
              <span class="badge bg-light text-royal border border-subtle rounded-circle" style="width:28px; height:28px; display:inline-flex; align-items:center; justify-content:center; font-size:0.85rem;">10</span>
              Keamanan Data
            </h5>
            <p class="text-secondary small lh-base mb-2">
              Kami melindungi Data Pribadi Anda dari pengaksesan tidak sah dengan pengamanan teknis dan operasional yang memadai, antara lain:
            </p>
            <ul class="text-secondary small lh-base mb-2 ps-3">
              <li>Enkripsi data saat dikirim (in transit) dengan protokol TLS/HTTPS maupun saat disimpan (at rest) dengan algoritma standar industri;</li>
              <li>Pembatasan akses berbasis peran (Role-Based Access Control) sehingga hanya personel berwenang yang dapat mengakses data;</li>
              <li>Autentikasi berlapis dan pencatatan audit log otomatis terhadap setiap akses sistem data;</li>
              <li>Pemantauan keamanan dan vulnerability assessment berkala; dan</li>
              <li>Perjanjian kerahasiaan (NDA) yang mengikat seluruh karyawan dan mitra teknologi.</li>
            </ul>
            <p class="text-secondary small lh-base mb-0">
              Tidak ada sistem elektronik yang sepenuhnya bebas risiko. Anda turut berperan aktif menjaga keamanan akun dengan merahasiakan kata sandi dan kode verifikasi satu kali (OTP/token) Anda.
            </p>
          </section>

          <!-- 11. AKURASI DAN VERIFIKASI DATA -->
          <section class="mb-4">
            <h5 class="fw-bold text-dark d-flex align-items-center gap-2 mb-2">
              <span class="badge bg-light text-royal border border-subtle rounded-circle" style="width:28px; height:28px; display:inline-flex; align-items:center; justify-content:center; font-size:0.85rem;">11</span>
              Akurasi dan Verifikasi Data
            </h5>
            <p class="text-secondary small lh-base mb-0">
              Kami wajib menjamin akurasi, kelengkapan, dan konsistensi Data Pribadi yang Kami proses. Anda berkewajiban memberikan data yang benar, lengkap, dan terkini, serta memperbarui data tersebut melalui pengaturan akun atau menghubungi tim Kami apabila terjadi perubahan.
            </p>
          </section>

          <!-- 12. HAK ANDA SEBAGAI PEMILIK DATA -->
          <section class="mb-4 p-3 bg-light rounded-3 border border-subtle">
            <h5 class="fw-bold text-dark d-flex align-items-center gap-2 mb-2">
              <span class="badge bg-royal text-white rounded-circle" style="width:28px; height:28px; display:inline-flex; align-items:center; justify-content:center; font-size:0.85rem;">12</span>
              Hak Anda sebagai Pemilik Data
            </h5>
            <p class="text-secondary small lh-base mb-2">Sesuai UU PDP, Anda memiliki hak penuh untuk:</p>
            <ul class="text-secondary small lh-base mb-2 ps-3">
              <li>Memperoleh informasi tentang kejelasan identitas, dasar hukum, dan tujuan penggunaan data Anda;</li>
              <li>Mengakses dan memperoleh salinan Data Pribadi Anda;</li>
              <li>Melengkapi, memperbarui, dan/atau memperbaiki kesalahan data Anda;</li>
              <li>Menarik kembali persetujuan pemrosesan yang telah Anda berikan;</li>
              <li>Mengakhiri pemrosesan, menghapus, dan/atau memusnahkan Data Pribadi Anda;</li>
              <li>Menunda atau membatasi pemrosesan data sesuai tujuan pemrosesan;</li>
              <li>Mengajukan keberatan atas keputusan yang diambil semata-mata berdasarkan pemrosesan otomatis;</li>
              <li>Menggunakan dan mengirimkan data Anda ke pengendali lain (portabilitas data); dan</li>
              <li>Menggugat dan menerima ganti rugi atas pelanggaran pemrosesan Data Pribadi Anda.</li>
            </ul>
            <p class="text-secondary small lh-base mb-0">
              <strong>Cara menggunakan hak:</strong> Ajukan permohonan melalui email <strong>${this.CONTROLLER.email}</strong>. Kami akan menanggapi permintaan Anda paling lambat <strong>14 (empat belas) hari kerja</strong>.
            </p>
          </section>

          <!-- 13. PENARIKAN PERSETUJUAN -->
          <section class="mb-4">
            <h5 class="fw-bold text-dark d-flex align-items-center gap-2 mb-2">
              <span class="badge bg-light text-royal border border-subtle rounded-circle" style="width:28px; height:28px; display:inline-flex; align-items:center; justify-content:center; font-size:0.85rem;">13</span>
              Penarikan Persetujuan
            </h5>
            <p class="text-secondary small lh-base mb-0">
              Anda dapat menarik persetujuan pemrosesan data kapan saja. Penarikan persetujuan tidak memengaruhi keabsahan pemrosesan yang telah dilakukan sebelumnya. Layanan atau hak akses yang bergantung pada data verifikasi tersebut mungkin tidak dapat dilanjutkan setelah penarikan dilakukan.
            </p>
          </section>

          <!-- 14. LARANGAN PENGGUNAAN DATA ORANG LAIN -->
          <section class="mb-4">
            <h5 class="fw-bold text-dark d-flex align-items-center gap-2 mb-2">
              <span class="badge bg-danger text-white rounded-circle" style="width:28px; height:28px; display:inline-flex; align-items:center; justify-content:center; font-size:0.85rem;">14</span>
              Larangan Penggunaan Data Pribadi Milik Orang Lain
            </h5>
            <p class="text-secondary small lh-base mb-2">
              Anda hanya boleh memberikan Data Pribadi milik Anda sendiri yang sah. Dilarang keras mengajukan verifikasi menggunakan data atau dokumen identitas pihak lain tanpa hak dan tanpa persetujuan tertulis pemiliknya.
            </p>
            <div class="alert alert-danger border-0 p-2.5 small mb-0 rounded-2">
              <i class="bi bi-exclamation-octagon-fill me-1"></i>
              <strong>Ketentuan Pidana UU PDP:</strong> Pasal 65 melarang perbuatan memperoleh atau menggunakan data pribadi bukan haknya secara melawan hukum, dengan ancaman pidana penjara paling lama <strong>5 (lima) tahun</strong> dan/atau denda miliaran rupiah (Pasal 67).
            </div>
          </section>

          <!-- 15. KEGAGALAN PELINDUNGAN DATA PRIBADI -->
          <section class="mb-4">
            <h5 class="fw-bold text-dark d-flex align-items-center gap-2 mb-2">
              <span class="badge bg-light text-royal border border-subtle rounded-circle" style="width:28px; height:28px; display:inline-flex; align-items:center; justify-content:center; font-size:0.85rem;">15</span>
              Kegagalan Pelindungan Data Pribadi
            </h5>
            <p class="text-secondary small lh-base mb-0">
              Apabila terjadi insiden keamanan atau kegagalan pelindungan Data Pribadi yang berpotensi merugikan Anda, Kami akan menyampaikan pemberitahuan tertulis kepada Anda dan lembaga otoritas berwenang paling lambat <strong>3 × 24 jam</strong> sejak insiden diketahui, disertai rincian data terungkap dan langkah penanganan pemulihan.
            </p>
          </section>

          <!-- 16. ANAK DI BAWAH UMUR -->
          <section class="mb-4">
            <h5 class="fw-bold text-dark d-flex align-items-center gap-2 mb-2">
              <span class="badge bg-light text-royal border border-subtle rounded-circle" style="width:28px; height:28px; display:inline-flex; align-items:center; justify-content:center; font-size:0.85rem;">16</span>
              Anak di Bawah Umur
            </h5>
            <p class="text-secondary small lh-base mb-0">
              Layanan investasi dan penempatan modal Kami diperuntukkan bagi subjek hukum yang telah berusia <strong>18 tahun ke atas</strong> atau cakap melakukan tindakan hukum mandiri. Kami tidak dengan sengaja memproses data anak di bawah umur.
            </p>
          </section>

          <!-- 17. PERUBAHAN KEBIJAKAN PRIVASI -->
          <section class="mb-4">
            <h5 class="fw-bold text-dark d-flex align-items-center gap-2 mb-2">
              <span class="badge bg-light text-royal border border-subtle rounded-circle" style="width:28px; height:28px; display:inline-flex; align-items:center; justify-content:center; font-size:0.85rem;">17</span>
              Perubahan Kebijakan Privasi
            </h5>
            <p class="text-secondary small lh-base mb-0">
              Kami dapat memperbarui Kebijakan ini dari waktu ke waktu. Setiap perubahan nomor versi (misal versi 1.1 atau 2.0) akan dipublikasikan dengan tanggal pembaruan. Apabila perubahan menyangkut tujuan pemrosesan atau penambahan jenis data yang dikumpulkan, Kami akan meminta persetujuan baru dari Anda melalui portal Layanan.
            </p>
          </section>

          <!-- 18. KONTAK DAN PENGADUAN -->
          <section class="p-3 bg-light rounded-3 border border-subtle">
            <h5 class="fw-bold text-dark d-flex align-items-center gap-2 mb-2">
              <span class="badge bg-royal text-white rounded-circle" style="width:28px; height:28px; display:inline-flex; align-items:center; justify-content:center; font-size:0.85rem;">18</span>
              Kontak dan Pengaduan
            </h5>
            <p class="text-secondary small lh-base mb-2">
              Untuk pertanyaan, permintaan pelaksanaan hak pemilik data, atau pengaduan tata kelola data, silakan hubungi kontak resmi Kami:
            </p>
            <p class="text-dark small mb-2 lh-base">
              <strong>${this.CONTROLLER.name}</strong><br>
              ${this.CONTROLLER.address}<br>
              Email: <a href="mailto:${this.CONTROLLER.email}" class="text-royal fw-semibold">${this.CONTROLLER.email}</a> • Telepon: <strong>${this.CONTROLLER.phone}</strong>
            </p>
            <p class="text-secondary small mb-0 fst-italic">
              Anda juga berhak menyampaikan pengaduan atas dugaan pelanggaran pelindungan Data Pribadi kepada Komisi / Otoritas Pelindungan Data Pribadi Republik Indonesia.
            </p>
          </section>
        </div>
      `;
    },

    /**
     * Render langsung ke elemen DOM
     */
    renderInto: function (containerOrId, opts = {}) {
      const el = typeof containerOrId === 'string' ? document.getElementById(containerOrId) : containerOrId;
      if (el) {
        el.innerHTML = this.getHtml(opts);
      }
    }
  };

  window.MGIPrivacyPolicy = MGIPrivacyPolicy;
})(window);
