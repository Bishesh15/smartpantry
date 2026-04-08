<?php
/**
 * Shared User-Side Header
 * Smart Pantry
 * Include this at the top of every user-facing page.
 * $page_title must be set before including.
 */

require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/constants.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Smart Pantry – Discover recipes from ingredients you already have. AI-powered hybrid recommendation engine.">
  <title><?= htmlspecialchars($page_title ?? 'SmartPantry') ?> | SmartPantry</title>

  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <!-- SmartPantry Style -->
  <link href="<?= ASSETS_PATH ?>css/style.css?v=2" rel="stylesheet">

  <!-- Base URL for JS -->
  <html data-base-url="<?= BASE_URL ?>">
</head>
<body>

<!-- ── Navbar ──────────────────────────────────────────────── -->
<nav class="sp-navbar navbar navbar-expand-lg">
  <div class="container-xl">
    <a class="navbar-brand sp-navbar-brand d-flex align-items-center gap-2" href="<?= BASE_URL ?>views/user/home.php">
      <div class="logo-icon"><i class="bi bi-egg-fried text-white"></i></div>
      <span>SmartPantry</span>
    </a>

    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="mainNav">
      <ul class="navbar-nav ms-auto align-items-center gap-1">
        <li class="nav-item">
          <a class="nav-link" href="<?= BASE_URL ?>views/user/home.php">
            <i class="bi bi-house-door me-1"></i>Home
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="<?= BASE_URL ?>views/user/recipe-search.php">
            <i class="bi bi-search me-1"></i>Find Recipes
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="<?= BASE_URL ?>views/user/about.php">
            <i class="bi bi-info-circle me-1"></i>About
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="<?= BASE_URL ?>views/user/contact.php">
            <i class="bi bi-envelope me-1"></i>Contact
          </a>
        </li>

        <?php if (isLoggedIn()): ?>
          <li class="nav-item">
            <a class="nav-link" href="<?= BASE_URL ?>views/user/pantry.php">
              <i class="bi bi-basket me-1"></i>My Pantry
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?= BASE_URL ?>views/user/dashboard.php">
              <i class="bi bi-speedometer2 me-1"></i>Dashboard
            </a>
          </li>
          <li class="nav-item">
            <a class="btn-sp-primary ms-2" href="<?= BASE_URL ?>controllers/AuthController.php?action=logout">
              <i class="bi bi-box-arrow-right"></i> Sign Out
            </a>
          </li>
        <?php else: ?>
          <li class="nav-item">
            <a class="nav-link" href="<?= BASE_URL ?>views/user/login.php">Login</a>
          </li>
          <li class="nav-item">
            <a class="btn-sp-primary ms-2" href="<?= BASE_URL ?>views/user/register.php">
              <i class="bi bi-person-plus"></i> Register
            </a>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<!-- Flash Messages -->
<div class="container-xl mt-3">
  <?= renderFlash() ?>
</div>