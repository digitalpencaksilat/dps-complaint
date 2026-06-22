<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login Admin - DPS Complain</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link href="<?= base_url('assets/css/complaint-theme.css') ?>" rel="stylesheet">
</head>
<body class="admin-auth-body admin-auth-centered">
  <main class="admin-login-wrap">
    <div class="login-card">
      <div class="card-body-custom">
        <div class="text-center mb-4">
          <img
            src="<?= base_url('assets/img/dps-logo.png') ?>"
            alt="Digital Pencak Silat"
            class="logo-img"
          >
          <h1 class="app-title">Admin Panel</h1>
          <p class="app-subtitle">DPS Complain</p>
        </div>

        <?php if (session('error')): ?>
          <div class="alert alert-danger rounded-4">
            <?= esc(session('error')) ?>
          </div>
        <?php endif; ?>

        <form method="post" action="<?= base_url('admin/login') ?>">
          <?= csrf_field() ?>

          <div class="mb-4">
            <div class="input-group input-group-lg">
              <span class="input-group-text"><i class="far fa-user"></i></span>
              <input
                type="text"
                name="username"
                value="<?= old('username') ?>"
                class="form-control"
                placeholder="Username"
                required
                autofocus
              >
            </div>
          </div>

          <div class="mb-4">
            <div class="input-group input-group-lg">
              <span class="input-group-text"><i class="fas fa-lock"></i></span>
              <input
                type="password"
                name="password"
                class="form-control"
                placeholder="Password"
                required
              >
            </div>
          </div>

          <button type="submit" class="btn btn-brand-login w-100">
            Masuk Admin <i class="fas fa-arrow-right ms-2"></i>
          </button>
        </form>

        <div class="text-center mt-3">
          <small class="text-muted">Akses terbatas Administrator</small>
        </div>
      </div>
    </div>

    <footer class="text-center mt-4 text-muted small">
      <span class="fw-bold text-admin-brand">DIGITAL PENCAK SILAT</span> &copy; <?= date('Y') ?>
    </footer>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
