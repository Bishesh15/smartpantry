<?php
$page_title = 'Recently Viewed';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../models/User.php';

requireLogin();
$userModel = new User();
$userId    = (int)$_SESSION['user_id'];
$recent    = $userModel->getRecentlyViewed($userId, 30);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-xl py-4">
  <h1 class="fw-black mb-1"><i class="bi bi-clock-history text-primary me-2"></i>Recently Viewed</h1>
  <p class="text-muted mb-4"><?= count($recent) ?> recipe<?= count($recent) != 1 ? 's' : '' ?> viewed</p>

  <?php if (empty($recent)): ?>
    <div class="sp-card p-5 text-center" style="border:2px dashed #e2e8f0;">
      <i class="bi bi-clock-history" style="font-size:4rem;color:#cbd5e1;"></i>
      <h4 class="mt-3 fw-black text-muted">Nothing viewed yet</h4>
      <a href="<?= BASE_URL ?>views/user/recipe-search.php" class="btn-sp-primary mt-3">Explore Recipes</a>
    </div>
  <?php else: ?>
    <div class="row g-4">
      <?php foreach ($recent as $r): ?>
        <div class="col-sm-6 col-lg-4">
          <div class="sp-card recipe-card">
            <a href="<?= BASE_URL ?>views/user/recipe-detail.php?id=<?= $r['id'] ?>">
              <img src="<?= resolveImageUrl($r['image_url'] ?? '') ?>"
                   class="card-img-top w-100" style="height:180px;object-fit:cover;"
                   onerror="this.src='<?= ASSETS_PATH ?>images/default-recipes.jpg'">
            </a>
            <div class="card-body">
              <h5 class="card-title">
                <a href="<?= BASE_URL ?>views/user/recipe-detail.php?id=<?= $r['id'] ?>"
                   class="text-dark text-decoration-none"><?= htmlspecialchars($r['name']) ?></a>
              </h5>
              <div class="card-meta mb-2">
                <span><i class="bi bi-clock me-1"></i><?= $r['prep_time'] ?>m</span>
                <span><i class="bi bi-fire me-1 text-success"></i><?= number_format($r['calories']) ?> kcal</span>
              </div>
              <span class="badge <?= dietBadgeClass($r['diet_type']) ?> me-2">
                <?= dietBadgeIcon($r['diet_type']) ?> <?= htmlspecialchars($r['diet_type']) ?>
              </span>
              <small class="text-muted d-block mt-2">
                <i class="bi bi-eye me-1"></i>Viewed <?= timeAgo($r['viewed_at'] ?? '') ?>
              </small>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
