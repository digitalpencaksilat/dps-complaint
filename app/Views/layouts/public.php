<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= esc($title ?? 'DPS Complain') ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link href="<?= base_url('assets/css/complaint-theme.css') ?>" rel="stylesheet">
</head>
<body class="public-body">
  <nav class="navbar navbar-expand-lg navbar-dark fixed-top py-2" id="mainNav">
    <div class="container">
      <a class="navbar-brand d-flex align-items-center gap-2" href="<?= base_url('/') ?>">
        <img
          class="navbar-logo"
          src="<?= base_url('assets/img/dps-logo.png') ?>"
          alt="Digital Pencak Silat"
        >
        <span class="visually-hidden">DPS Complain</span>
      </a>

      <button
        class="navbar-toggler"
        type="button"
        data-bs-toggle="collapse"
        data-bs-target="#publicNavbar"
        aria-controls="publicNavbar"
        aria-expanded="false"
        aria-label="Toggle navigation"
      >
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="publicNavbar">
        <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-3">
          <li class="nav-item">
            <a class="nav-link" href="<?= base_url('/') ?>">Home</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?= base_url('complaints/track') ?>">Tracking Tiket</a>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <?= $this->renderSection('content') ?>

  <footer class="public-footer py-4 text-center small">
    © 2026 Digital Pencak Silat. All Rights Reserved.
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <?= $this->renderSection('scripts') ?>
</body>
</html>
