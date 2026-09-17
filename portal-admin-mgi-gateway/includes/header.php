<?php
require_once __DIR__ . '/../../backend/helpers/auth_helper.php';
$adminUser = requireAdminAuth();
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle ?? 'Admin Dashboard') ?> — PT Montana Global Investama</title>
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
  <link rel="stylesheet" href="assets/admin.css">
</head>
<body>

  <!-- Sidebar -->
  <aside class="admin-sidebar">
    <div class="brand-box">
      <div class="d-inline-flex align-items-center justify-content-center bg-white rounded-circle p-1" style="width: 40px; height: 40px;">
        <i class="bi bi-shield-shaded fs-4 text-primary" style="color: #142563 !important;"></i>
      </div>
      <div>
        <div class="fw-bold text-white fs-6 lh-1">MGI Admin</div>
        <small class="text-warning" style="font-size: 0.72rem; color: #C5A059 !important; font-weight: 600;">MONTANA GROUP</small>
      </div>
    </div>

    <div class="py-3 flex-grow-1">
      <div class="px-3 mb-2 text-uppercase text-white-50" style="font-size: 0.7rem; font-weight: 700; letter-spacing: 0.5px;">Menu Utama</div>
      
      <a href="index.php" class="nav-link <?= $currentPage === 'index.php' ? 'active' : '' ?>">
        <i class="bi bi-grid-1x2-fill"></i>
        <span>Dashboard Overview</span>
      </a>

      <a href="projects.php" class="nav-link <?= $currentPage === 'projects.php' ? 'active' : '' ?>">
        <i class="bi bi-briefcase-fill"></i>
        <span>Kelola Proyek Investasi</span>
      </a>

      <a href="investors.php" class="nav-link <?= $currentPage === 'investors.php' ? 'active' : '' ?>">
        <i class="bi bi-people-fill"></i>
        <span>Manajemen Investor</span>
      </a>

      <a href="content.php" class="nav-link <?= $currentPage === 'content.php' ? 'active' : '' ?>">
        <i class="bi bi-file-earmark-text-fill"></i>
        <span>CMS Konten Grup</span>
      </a>

      <div class="px-3 mt-4 mb-2 text-uppercase text-white-50" style="font-size: 0.7rem; font-weight: 700; letter-spacing: 0.5px;">Sistem &amp; Tautan</div>

      <a href="settings.php" class="nav-link <?= $currentPage === 'settings.php' ? 'active' : '' ?>">
        <i class="bi bi-sliders"></i>
        <span>Pengaturan Sistem</span>
      </a>

      <a href="../index.html" target="_blank" class="nav-link">
        <i class="bi bi-box-arrow-up-right"></i>
        <span>Lihat Website Publik</span>
      </a>
    </div>

    <div class="p-3 border-top border-secondary border-opacity-25">
      <div class="d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2 overflow-hidden">
          <div class="bg-warning text-dark rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 34px; height: 34px; background: #C5A059 !important; color: #fff !important;">
            <?= strtoupper(substr($adminUser['username'], 0, 1)) ?>
          </div>
          <div class="overflow-hidden">
            <div class="text-white small fw-semibold text-truncate"><?= htmlspecialchars($adminUser['name']) ?></div>
            <div class="text-white-50" style="font-size: 0.7rem;"><?= htmlspecialchars($adminUser['role']) ?></div>
          </div>
        </div>
        <button type="button" onclick="AdminApp.logout()" class="btn btn-sm btn-outline-light border-0 text-white-50 hover-white p-1" title="Keluar">
          <i class="bi bi-box-arrow-right fs-5"></i>
        </button>
      </div>
    </div>
  </aside>

  <!-- Main Content Wrapper -->
  <div class="admin-main">
    <!-- Topbar -->
    <header class="admin-topbar">
      <div class="d-flex align-items-center gap-3">
        <button class="btn btn-light d-lg-none" type="button" onclick="document.querySelector('.admin-sidebar').classList.toggle('show')">
          <i class="bi bi-list fs-4"></i>
        </button>
        <h5 class="fw-bold text-dark mb-0"><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?></h5>
      </div>

      <div class="d-flex align-items-center gap-3">
        <span class="badge bg-light text-dark border py-2 px-3 rounded-pill small d-none d-sm-inline-flex align-items-center gap-2">
          <span class="spinner-grow spinner-grow-sm text-success" style="width: 8px; height: 8px;"></span>
          <span>Database MySQL: <strong>Port <?= Database::getConnectedPort() ?: 3307 ?></strong></span>
        </span>
        <a href="projects.php?action=create" class="btn btn-mgi-gold btn-sm d-flex align-items-center gap-2 shadow-sm">
          <i class="bi bi-plus-circle-fill"></i>
          <span>Tambah Proyek</span>
        </a>
      </div>
    </header>

    <div class="admin-content">
