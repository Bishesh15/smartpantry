<?php
$page_title = 'Admin Login';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/constants.php';

if (isAdmin()) redirect(BASE_URL . 'views/admin/dashboard.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Admin Login | SmartPantry</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="<?= ASSETS_PATH ?>css/admin.css?v=2" rel="stylesheet">
</head>
<body>
<div class="admin-auth-wrap">
  <div class="admin-auth-card">
    <div class="text-center mb-4">
      <div class="mx-auto mb-3 d-flex align-items-center justify-content-center rounded-3"
           style="width:60px;height:60px;background:#16a34a;">
        <i class="bi bi-shield-lock-fill text-white" style="font-size:1.8rem;"></i>
      </div>
      <h2 class="fw-black mb-1">Admin Portal</h2>
      <p class="text-muted small">SmartPantry Administration</p>
    </div>

    <?= renderFlash() ?>

    <form method="POST" action="<?= BASE_URL ?>controllers/AdminController.php">
      <input type="hidden" name="action" value="admin_login">
      <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
      <div class="mb-3">
        <label class="admin-label">Admin Username</label>
        <input type="text" name="username" class="admin-input" placeholder="admin" autocomplete="username" required>
      </div>
      <div class="mb-3">
        <label class="admin-label">Password</label>
        <input type="password" name="password" class="admin-input" placeholder="Your password" required>
      </div>
      <button type="submit" class="btn-admin-primary w-100 justify-content-center py-3 mt-2">
        <i class="bi bi-box-arrow-in-right"></i> Sign in to Admin
      </button>
    </form>

    <p class="text-center text-muted mt-4" style="font-size:.78rem;">
      Default: <strong>admin</strong> / <strong>admin123</strong>
    </p>
    <p class="text-center mt-1">
      <a href="<?= BASE_URL ?>views/user/login.php" style="font-size:.78rem;color:#64748b;">← User Login</a>
    </p>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>document.documentElement.dataset.baseUrl='<?= BASE_URL ?>';</script>
<script src="<?= ASSETS_PATH ?>js/main.js"></script>
</body>
</html>
