<?php
$page_title = 'My Favorites';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../models/User.php';

requireLogin();
$userModel = new User();
$userId    = (int)$_SESSION['user_id'];
$favorites = $userModel->getFavorites($userId, 50);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-xl py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h1 class="fw-black mb-1"><i class="bi bi-heart-fill text-danger me-2"></i>My Favorites</h1>
      <p class="text-muted mb-0"><?= count($favorites) ?> saved recipe<?= count($favorites) != 1 ? 's' : '' ?></p>
    </div>
    <a href="<?= BASE_URL ?>views/user/recipe-search.php" class="btn-sp-outline">
      <i class="bi bi-search me-1"></i> Find More
    </a>
  </div>

  <?php if (empty($favorites)): ?>
    <div class="sp-card p-5 text-center" style="border:2px dashed #e2e8f0;">
      <i class="bi bi-heart" style="font-size:4rem;color:#cbd5e1;"></i>
      <h4 class="mt-3 fw-black text-muted">No saved recipes yet</h4>
      <p class="text-muted">Browse recipes and click the heart button to save your favorites here.</p>
      <a href="<?= BASE_URL ?>views/user/recipe-search.php" class="btn-sp-primary mt-2">
        <i class="bi bi-search me-1"></i> Explore Recipes
      </a>
    </div>
  <?php else: ?>
    <div class="row g-4">
      <?php foreach ($favorites as $r): ?>
        <div class="col-sm-6 col-lg-4">
          <div class="sp-card recipe-card">
            <a href="<?= BASE_URL ?>views/user/recipe-detail.php?id=<?= $r['id'] ?>">
              <img src="<?= resolveImageUrl($r['image_url'] ?? '') ?>"
                   class="card-img-top w-100" style="height:200px;object-fit:cover;"
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
                <span><i class="bi bi-star-fill me-1 text-warning"></i><?= number_format($r['average_rating'], 1) ?></span>
              </div>
              <span class="badge <?= dietBadgeClass($r['diet_type']) ?> mb-2">
                <?= dietBadgeIcon($r['diet_type']) ?> <?= htmlspecialchars($r['diet_type']) ?>
              </span>
              <div class="d-flex justify-content-between align-items-center mt-2">
                <small class="text-muted">Saved <?= timeAgo($r['saved_at'] ?? '') ?></small>
                <form method="POST" action="<?= BASE_URL ?>controllers/UserController.php">
                  <input type="hidden" name="action" value="remove_favorite">
                  <input type="hidden" name="recipe_id" value="<?= $r['id'] ?>">
                  <input type="hidden" name="redirect" value="<?= BASE_URL ?>views/user/favorites.php">
                  <button type="submit" class="btn btn-sm btn-outline-danger rounded-3 border-0"
                          style="background:#fee2e2;font-size:.78rem;">
                    <i class="bi bi-heart-break me-1"></i>Remove
                  </button>
                </form>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
