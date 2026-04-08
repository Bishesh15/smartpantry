<?php
$page_title = 'Dashboard';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../models/Recipe.php';

requireLogin();
$userModel = new User();
$userId    = (int)$_SESSION['user_id'];
$user      = $userModel->getById($userId);
$stats     = $userModel->getStats($userId);
$pantry    = $userModel->getPantry($userId);
$favorites = $userModel->getFavorites($userId, 4);
$recent    = $userModel->getRecentlyViewed($userId, 4);

// Recommendations from pantry
$recommendations = [];
if (!empty($pantry)) {
    $recipeModel     = new Recipe();
    $pantryIds       = array_column($pantry, 'id');
    $prefs           = getFoodPreferences($user['food_preferences'] ?? '');
    $recommendations = $recipeModel->getMatchingRecipes($pantryIds, $prefs, '', 'match');
    $recommendations = array_slice($recommendations, 0, 6);
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-wrap">
  <!-- Sidebar -->
  <aside class="db-sidebar d-none d-lg-flex flex-column">
    <a href="<?= BASE_URL ?>views/user/home.php" class="sidebar-logo mb-4 text-decoration-none">
      <div class="logo-icon"><i class="bi bi-egg-fried text-white"></i></div>
      <span>SmartPantry</span>
    </a>
    <nav class="flex-grow-1">
      <?php $navItems = [
        ['tab'=>'overview',  'icon'=>'speedometer2',   'label'=>'Overview'],
        ['tab'=>'pantry',    'icon'=>'basket',          'label'=>'My Pantry', 'url'=>BASE_URL.'views/user/pantry.php'],
        ['tab'=>'saved',     'icon'=>'heart',           'label'=>'Favorites', 'url'=>BASE_URL.'views/user/favorites.php'],
        ['tab'=>'history',   'icon'=>'clock-history',  'label'=>'Recently Viewed', 'url'=>BASE_URL.'views/user/recently-viewed.php'],
        ['tab'=>'profile',   'icon'=>'person-gear',    'label'=>'Profile', 'url'=>BASE_URL.'views/user/profile.php'],
      ]; ?>
      <?php foreach ($navItems as $item): ?>
        <a href="<?= $item['url'] ?? '?tab=' . $item['tab'] ?>"
           class="sidebar-nav-item <?= (($_GET['tab'] ?? 'overview') === $item['tab']) ? 'active' : '' ?>">
          <i class="bi bi-<?= $item['icon'] ?>"></i>
          <span><?= $item['label'] ?></span>
        </a>
      <?php endforeach; ?>
      <div class="mt-auto pt-3 border-top">
        <a href="<?= BASE_URL ?>controllers/AuthController.php?action=logout" class="sidebar-nav-item text-danger">
          <i class="bi bi-box-arrow-right"></i><span>Logout</span>
        </a>
      </div>
    </nav>
  </aside>

  <!-- Main Content -->
  <main class="db-main">
    <!-- Welcome Banner -->
    <div class="rounded-4 p-4 mb-4 text-white"
         style="background:linear-gradient(135deg,#0f172a 0%,#1e3a5f 100%);">
      <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
        <div>
          <div class="small text-success fw-bold mb-1" style="letter-spacing:1px;">WELCOME BACK</div>
          <h2 class="fw-black mb-1"><?= htmlspecialchars($user['full_name'] ?? $_SESSION['username']) ?></h2>
          <p class="mb-0" style="color:#94a3b8;"><?= date('l, F j, Y') ?></p>
        </div>
        <div class="d-flex gap-4">
          <div class="text-center">
            <div style="font-size:2rem;font-weight:900;"><?= $stats['pantry'] ?></div>
            <div style="font-size:.7rem;text-transform:uppercase;color:#94a3b8;">Pantry Items</div>
          </div>
          <div class="text-center">
            <div style="font-size:2rem;font-weight:900;color:#16a34a;"><?= $stats['favorites'] ?></div>
            <div style="font-size:.7rem;text-transform:uppercase;color:#94a3b8;">Saved Recipes</div>
          </div>
          <div class="text-center">
            <div style="font-size:2rem;font-weight:900;color:#f59e0b;"><?= $stats['ratings'] ?></div>
            <div style="font-size:.7rem;text-transform:uppercase;color:#94a3b8;">Reviews Given</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Recommendations from Pantry -->
    <div class="mb-4">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-black mb-0">
          <i class="bi bi-cpu-fill text-success me-2"></i>Recommended For You
        </h4>
        <a href="<?= BASE_URL ?>views/user/recipe-search.php" class="btn-sp-outline btn-sm">View All</a>
      </div>
      <?php if (empty($recommendations)): ?>
        <div class="sp-card p-5 text-center border-2 border-dashed">
          <i class="bi bi-basket" style="font-size:3rem;color:#cbd5e1;"></i>
          <h5 class="mt-3 text-muted">Your pantry is empty!</h5>
          <p class="text-muted small">Add ingredients to your pantry to get personalised recipe recommendations.</p>
          <a href="<?= BASE_URL ?>views/user/pantry.php" class="btn-sp-primary mt-2">
            <i class="bi bi-plus-lg"></i> Add Ingredients
          </a>
        </div>
      <?php else: ?>
        <div class="row g-3">
          <?php foreach ($recommendations as $r): ?>
            <div class="col-sm-6 col-xl-4">
              <div class="sp-card recipe-card">
                <?php if ($r['match_percentage'] > 0):
                  $col = $r['match_percentage'] >= 70 ? '#16a34a' : ($r['match_percentage'] >= 40 ? '#d97706' : '#ef4444'); ?>
                  <div class="match-badge" style="background:<?= $col ?>;"><?= $r['match_percentage'] ?>% Match</div>
                <?php endif; ?>
                <a href="<?= BASE_URL ?>views/user/recipe-detail.php?id=<?= $r['id'] ?>">
                  <img src="<?= resolveImageUrl($r['image_url'] ?? '') ?>"
                       class="card-img-top w-100" style="height:160px;object-fit:cover;"
                       onerror="this.src='<?= ASSETS_PATH ?>images/default-recipes.jpg'">
                </a>
                <div class="card-body">
                  <h6 class="card-title fw-bold">
                    <a href="<?= BASE_URL ?>views/user/recipe-detail.php?id=<?= $r['id'] ?>"
                       class="text-dark text-decoration-none"><?= htmlspecialchars($r['name']) ?></a>
                  </h6>
                  <div class="card-meta">
                    <span><i class="bi bi-clock me-1"></i><?= $r['prep_time'] ?>m</span>
                    <span><i class="bi bi-fire me-1 text-success"></i><?= number_format($r['calories']) ?> kcal</span>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- Two columns: Favorites + Recent -->
    <div class="row g-4">
      <!-- Saved Recipes preview -->
      <div class="col-lg-6">
        <div class="sp-card p-4 h-100">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-black mb-0"><i class="bi bi-heart-fill text-danger me-2"></i>Favorites</h5>
            <a href="<?= BASE_URL ?>views/user/favorites.php" class="small text-success fw-bold">See all</a>
          </div>
          <?php if (empty($favorites)): ?>
            <p class="text-muted small text-center py-3">No saved recipes yet. Explore and hit the ❤ button!</p>
          <?php else: ?>
            <?php foreach ($favorites as $f): ?>
              <a href="<?= BASE_URL ?>views/user/recipe-detail.php?id=<?= $f['id'] ?>"
                 class="d-flex gap-3 mb-3 text-decoration-none text-dark align-items-center">
                <img src="<?= resolveImageUrl($f['image_url'] ?? '') ?>"
                     style="width:56px;height:56px;border-radius:12px;object-fit:cover;"
                     onerror="this.src='<?= ASSETS_PATH ?>images/default-recipes.jpg'">
                <div>
                  <div class="fw-bold small"><?= htmlspecialchars($f['name']) ?></div>
                  <div class="text-muted" style="font-size:.75rem;"><?= number_format($f['calories']) ?> kcal · <?= $f['prep_time'] ?>m</div>
                </div>
              </a>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>

      <!-- Recently Viewed -->
      <div class="col-lg-6">
        <div class="sp-card p-4 h-100">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-black mb-0"><i class="bi bi-clock-history text-primary me-2"></i>Recently Viewed</h5>
            <a href="<?= BASE_URL ?>views/user/recently-viewed.php" class="small text-success fw-bold">See all</a>
          </div>
          <?php if (empty($recent)): ?>
            <p class="text-muted small text-center py-3">No recipes viewed yet. Start exploring!</p>
          <?php else: ?>
            <?php foreach ($recent as $r): ?>
              <a href="<?= BASE_URL ?>views/user/recipe-detail.php?id=<?= $r['id'] ?>"
                 class="d-flex gap-3 mb-3 text-decoration-none text-dark align-items-center">
                <img src="<?= resolveImageUrl($r['image_url'] ?? '') ?>"
                     style="width:56px;height:56px;border-radius:12px;object-fit:cover;"
                     onerror="this.src='<?= ASSETS_PATH ?>images/default-recipes.jpg'">
                <div>
                  <div class="fw-bold small"><?= htmlspecialchars($r['name']) ?></div>
                  <div class="text-muted" style="font-size:.75rem;"><?= timeAgo($r['viewed_at'] ?? '') ?></div>
                </div>
              </a>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>

  </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
