<?php
/**
 * Admin Header / Layout Wrapper
 * Smart Pantry
 * $page_title must be set.
 */

require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/constants.php';

$current_page = basename($_SERVER['PHP_SELF']);
if (!isAdmin() && $current_page !== 'login.php') {
    redirect(BASE_URL . 'views/admin/login.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($page_title ?? 'Admin') ?> | SmartPantry Admin</title>
  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <!-- Admin CSS -->
  <link href="<?= ASSETS_PATH ?>css/admin.css?v=2" rel="stylesheet">
</head>
<body>
<div class="admin-wrap">

<!-- ── Sidebar ─────────────────────────────────────────────── -->
<?php if (isAdmin()): ?>
<aside class="admin-sidebar">
  <a href="<?= BASE_URL ?>views/admin/dashboard.php" class="sidebar-brand text-decoration-none">
    <span class="brand-icon"><i class="bi bi-egg-fried text-white"></i></span>
    <span>SmartPantry</span>
  </a>

  <nav>
    <div class="sidebar-label">Main</div>
    <a href="<?= BASE_URL ?>views/admin/dashboard.php"
       class="admin-nav-item <?= $current_page === 'dashboard.php' ? 'active' : '' ?>">
      <i class="bi bi-house-door"></i><span>Dashboard</span>
    </a>
    <a href="<?= BASE_URL ?>views/admin/recipes.php"
       class="admin-nav-item <?= $current_page === 'recipes.php' ? 'active' : '' ?>">
      <i class="bi bi-book"></i><span>Recipe Studio</span>
    </a>
    <a href="<?= BASE_URL ?>views/admin/ingredients.php"
       class="admin-nav-item <?= $current_page === 'ingredients.php' ? 'active' : '' ?>">
      <i class="bi bi-basket"></i><span>Ingredients</span>
    </a>

    <div class="sidebar-label">Users &amp; Support</div>
    <a href="<?= BASE_URL ?>views/admin/users.php"
       class="admin-nav-item <?= $current_page === 'users.php' ? 'active' : '' ?>">
      <i class="bi bi-people"></i><span>Users</span>
    </a>
    <a href="<?= BASE_URL ?>views/admin/feedback.php"
       class="admin-nav-item <?= $current_page === 'feedback.php' ? 'active' : '' ?>">
      <i class="bi bi-chat-left-text"></i><span>Feedback</span>
    </a>

    <div class="sidebar-label">Site</div>
    <a href="<?= BASE_URL ?>views/user/home.php" target="_blank" class="admin-nav-item">
      <i class="bi bi-box-arrow-up-right"></i><span>View Site</span>
    </a>
  </nav>

  <div class="sidebar-bottom">
    <span class="d-block text-muted" style="font-size:.72rem;padding:.25rem .75rem;">
      <i class="bi bi-person-circle me-1"></i>
      <?= htmlspecialchars($_SESSION['admin_username'] ?? 'Admin') ?>
    </span>
    <a href="<?= BASE_URL ?>controllers/AdminController.php?action=logout" class="admin-nav-item text-danger">
      <i class="bi bi-box-arrow-right"></i><span>Logout</span>
    </a>
  </div>
</aside>
<?php endif; ?>

<!-- ── Main area starts here ──────────────────────────────── -->
<main class="admin-main">

  <?php if (isAdmin()): ?>
  <div class="admin-topbar">
    <h1><?= htmlspecialchars($page_title ?? '') ?></h1>
    <span class="admin-badge">
      <i class="bi bi-shield-check me-1"></i>
      <?= htmlspecialchars($_SESSION['admin_username'] ?? '') ?>
    </span>
  </div>
  <?php endif; ?>

  <!-- Flash Messages -->
  <?= renderFlash() ?>
