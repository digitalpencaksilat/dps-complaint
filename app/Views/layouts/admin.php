<!doctype html>
<html lang="id">
<?php
  $currentPath = trim((string) uri_string(), '/');
  $isActiveAdminMenu = static function (string $path, bool $exact = false) use ($currentPath): string {
    $path = trim($path, '/');

    if ($exact) {
      return $currentPath === $path ? ' active' : '';
    }

    return ($currentPath === $path || str_starts_with($currentPath, $path . '/')) ? ' active' : '';
  };
?>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= esc($title ?? 'Admin Complain') ?></title>
  <link rel="icon" type="image/x-icon" href="<?= base_url('favicon.ico') ?>">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">
  <link href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link href="<?= base_url('assets/css/admin.css') ?>" rel="stylesheet">
</head>
<body class="admin-body">
  <div class="admin-shell">
    <aside class="admin-sidebar" id="adminSidebar">
      <div class="admin-brand">
        <img class="admin-brand-logo" src="<?= base_url('assets/img/dps-logo.png') ?>" alt="Digital Pencak Silat">
        <div class="admin-brand-copy">
          <div class="admin-brand-title">Complain Panel</div>
          <div class="admin-brand-subtitle">Digital Pencak Silat</div>
        </div>
      </div>

      <button class="admin-sidebar-collapse" type="button" id="adminSidebarCollapse" aria-label="Minimize menu admin" aria-expanded="true">
        <i class="fas fa-angles-left"></i>
        <span>Minimize Menu</span>
      </button>

      <div class="admin-section-label">Navigasi</div>
      <nav class="d-flex flex-column gap-2">
        <a class="admin-nav-link<?= $isActiveAdminMenu('admin/complaints', true) ?>" href="<?= base_url('admin/complaints') ?>"<?= $isActiveAdminMenu('admin/complaints', true) ? ' aria-current="page"' : '' ?>>
          <span class="label-block"><i class="fas fa-ticket"></i><span>Dashboard Complain</span></span>
        </a>
        <a class="admin-nav-link<?= $isActiveAdminMenu('admin/complaints/report') ?>" href="<?= base_url('admin/complaints/report') ?>"<?= $isActiveAdminMenu('admin/complaints/report') ? ' aria-current="page"' : '' ?>>
          <span class="label-block"><i class="fas fa-table"></i><span>Rekap Complain</span></span>
        </a>
        <a class="admin-nav-link<?= $isActiveAdminMenu('admin/complaints/contingents') ?>" href="<?= base_url('admin/complaints/contingents') ?>"<?= $isActiveAdminMenu('admin/complaints/contingents') ? ' aria-current="page"' : '' ?>>
          <span class="label-block"><i class="fas fa-circle-check"></i><span>Konfirmasi Kontingen</span></span>
        </a>
        <a class="admin-nav-link<?= $isActiveAdminMenu('admin/events') ?>" href="<?= base_url('admin/events') ?>"<?= $isActiveAdminMenu('admin/events') ? ' aria-current="page"' : '' ?>>
          <span class="label-block"><i class="fas fa-trophy"></i><span>Kelola Kejuaraan</span></span>
        </a>
        <form method="post" action="<?= base_url('admin/logout') ?>" class="m-0">
          <?= csrf_field() ?>
          <button class="admin-nav-link border-0 w-100 text-start" type="submit">
            <span class="label-block"><i class="fas fa-sign-out-alt"></i><span>Logout</span></span>
          </button>
        </form>
      </nav>
    </aside>

    <div class="admin-overlay" id="adminOverlay"></div>

    <main class="admin-main">
      <header class="admin-topbar">
        <button class="admin-menu-toggle" type="button" id="adminMenuToggle" aria-label="Buka menu admin">
          <i class="fas fa-bars"></i>
        </button>
        <div>
          <div class="eyebrow">Area Admin</div>
          <h1 class="admin-page-title h2 mb-0"><?= esc($title ?? 'Admin') ?></h1>
        </div>
      </header>

      <?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          <?= esc(session('success')) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
      <?php endif; ?>
      <?php if(session('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <?= esc(session('error')) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
      <?php endif; ?>

      <?= $this->renderSection('content') ?>
    </main>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.colVis.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="<?= base_url('assets/js/admin-layout.js') ?>"></script>
  <script src="<?= base_url('assets/js/admin-export-datatable.js') ?>"></script>
  <?= $this->renderSection('scripts') ?>
</body>
</html>
