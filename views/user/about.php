<?php
$page_title = 'About';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-xl py-5">

  <!-- Hero -->
  <div class="row align-items-center g-5 mb-5">
    <div class="col-lg-6">
      <span class="badge bg-success mb-3 px-3 py-2">ACADEMIC PROJECT</span>
      <h1 class="fw-black mb-4">Smart Pantry<br><span class="text-success">Recipe Recommendation System</span></h1>
      <p class="lead text-muted">
        A full-stack web application demonstrating a hybrid recommendation engine that suggests recipes based on ingredients users already have — reducing food waste and improving meal planning.
      </p>
    </div>
    <div class="col-lg-6">
      <div class="sp-card p-4 h-100" style="background:linear-gradient(135deg,#0f172a,#1e3a5f);color:#fff;">
        <h4 class="fw-black mb-3 text-white">How it works</h4>
        <ul class="list-unstyled mb-0">
          <li class="mb-3 d-flex gap-2">
            <i class="bi bi-check-circle-fill text-success"></i>
            <span><strong>Smart Match:</strong> We prioritize recipes that use the ingredients you already have in your pantry.</span>
          </li>
          <li class="mb-3 d-flex gap-2">
            <i class="bi bi-check-circle-fill text-success"></i>
            <span><strong>Taste Discovery:</strong> Our system suggests similar meals based on flavor profiles you enjoy.</span>
          </li>
          <li class="d-flex gap-2">
            <i class="bi bi-check-circle-fill text-success"></i>
            <span><strong>Zero Waste:</strong> Cook exactly what you need and stop throwing away perfectly good food.</span>
          </li>
        </ul>
      </div>
    </div>
  </div>

  <!-- Project Mission -->
  <div class="sp-card p-5 mb-5">
    <div class="row g-4 align-items-center">
      <div class="col-lg-6">
        <h2 class="fw-black mb-4">Our Mission</h2>
        <p class="text-muted">
          Smart Pantry was born out of a simple problem: a fridge full of food but "nothing to eat." By leveraging modern web technology, we've created a platform that bridges the gap between your inventory and your dinner table.
        </p>
        <p class="text-muted">
          Whether you're a student building a meal on a budget or a home cook looking to experiment with new cuisines, our goal is to make mealtime effortless and sustainable.
        </p>
      </div>
      <div class="col-lg-6">
        <div class="p-4 rounded-4" style="background:#f8fafc; border:1px solid #e2e8f0;">
          <h5 class="fw-bold mb-3 text-success">Why use Smart Pantry?</h5>
          <div class="mb-3">
            <h6 class="fw-bold">Reduce Food Waste</h6>
            <p class="text-muted small">Stop letting ingredients expire. Find recipes for exactly what you have.</p>
          </div>
          <div class="mb-3">
            <h6 class="fw-bold">Save Money</h6>
            <p class="mt-3 small text-muted">Helping you find the perfect meal with the ingredients you already have.</p>
      </div>
          <div>
            <h6 class="fw-bold">Balanced Nutrition</h6>
            <p class="text-muted small">Monitor calories and dietary types to keep your health on track.</p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Tech Stack -->
  <div class="mb-5">
    <h2 class="fw-black text-center mb-4">Technology Stack</h2>
    <div class="row g-4 justify-content-center">
      <?php $stack = [
        ['PHP 8.x', 'Backend logic, MVC pattern, PDO prepared statements', '#0f172a', 'bi-server'],
        ['MySQL', 'Relational database with proper foreign keys and indexing', '#00758f', 'bi-database-fill'],
        ['Bootstrap 5', 'Responsive UI framework with custom CSS design system', '#7952b3', 'bi-grid-1x2'],
        ['JavaScript', 'AJAX ingredient matching, real-time UI updates, toast notifications', '#f0db4f', 'bi-code-slash'],
        ['XAMPP', 'Local development server (Apache + MySQL + PHP)', '#f76d2b', 'bi-laptop'],
      ];
      foreach ($stack as $s): ?>
        <div class="col-sm-6 col-lg-4">
          <div class="sp-card p-4 text-center">
            <i class="bi <?= $s[3] ?>" style="font-size:2rem;color:<?= $s[2] ?>;"></i>
            <h6 class="fw-black mt-2 mb-1"><?= $s[0] ?></h6>
            <p class="text-muted small mb-0"><?= $s[1] ?></p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Credits -->
  <div class="sp-card p-5 text-center">
    <h3 class="fw-black mb-2">Academic Project</h3>
    <p class="text-muted mb-4">Built for academic demonstration of full-stack PHP/MySQL web development, REST-like API design, and algorithmic recipe recommendation.</p>
    <div class="badge bg-success px-4 py-2" style="font-size:.9rem;">
      Smart Pantry v1.0 — <?= date('Y') ?>
    </div>
  </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
