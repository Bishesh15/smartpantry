<?php
$page_title = 'Admin Dashboard';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../models/Recipe.php';
require_once __DIR__ . '/../../models/Ingredient.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../models/Rating.php';
require_once __DIR__ . '/../../models/Feedback.php';

require_once __DIR__ . '/../includes/admin-header.php';

$recipeModel     = new Recipe();
$ingredientModel = new Ingredient();
$userModel       = new User();
$ratingModel     = new Rating();
$feedbackModel   = new Feedback();

$totalRecipes     = $recipeModel->getTotalCount();
$totalIngredients = $ingredientModel->getTotalCount();
$totalUsers       = $userModel->getTotalCount();
$totalRatings     = $ratingModel->getTotalCount();
$pendingFeedback  = $feedbackModel->getTotalCount('pending');
$mostViewed       = $recipeModel->getMostViewed(5);
$recentUsers      = $userModel->getAll(5, 0);
?>

<!-- Stat Cards -->
<div class="row g-4 mb-4">
  <?php $stats = [
    ['Total Recipes',     $totalRecipes,     '#16a34a', '#dcfce7', 'bi-book-fill'],
    ['Ingredients',       $totalIngredients, '#3b82f6', '#dbeafe', 'bi-basket-fill'],
    ['Registered Users',  $totalUsers,       '#8b5cf6', '#ede9fe', 'bi-people-fill'],
    ['Ratings Given',     $totalRatings,     '#f59e0b', '#fef3c7', 'bi-star-fill'],
  ];
  foreach ($stats as [$label,$val,$color,$bg,$icon]): ?>
    <div class="col-sm-6 col-xl-3">
      <div class="admin-stat-card">
        <div class="admin-stat-icon" style="background:<?= $bg ?>;color:<?= $color ?>;">
          <i class="bi <?= $icon ?>"></i>
        </div>
        <div>
          <div class="admin-stat-val"><?= number_format($val) ?></div>
          <div class="admin-stat-label"><?= $label ?></div>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<?php if ($pendingFeedback > 0): ?>
<div class="alert alert-warning border-warning d-flex align-items-center gap-2 mb-4">
  <i class="bi bi-chat-left-text-fill"></i>
  <strong><?= $pendingFeedback ?> pending feedback message<?= $pendingFeedback > 1 ? 's' : '' ?></strong>
  awaiting response. <a href="<?= BASE_URL ?>views/admin/feedback.php" class="alert-link ms-2">View Now →</a>
</div>
<?php endif; ?>

<div class="row g-4">
  <!-- Most Viewed Recipes -->
  <div class="col-lg-6">
    <div class="admin-card">
      <div class="admin-card-header">
        <i class="bi bi-fire text-warning"></i> Most Viewed Recipes
      </div>
      <table class="admin-table">
        <thead>
          <tr>
            <th>Recipe</th>
            <th>Diet</th>
            <th>Views</th>
            <th>Rating</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($mostViewed as $r): ?>
          <tr>
            <td>
              <div class="d-flex align-items-center gap-2">
                <img src="<?= resolveImageUrl($r['image_url'] ?? '') ?>"
                     class="recipe-thumb"
                     onerror="this.src='<?= ASSETS_PATH ?>images/default-recipes.jpg'">
                <div>
                  <a href="<?= BASE_URL ?>views/user/recipe-detail.php?id=<?= $r['id'] ?>"
                     class="fw-bold text-dark text-decoration-none" style="font-size:.88rem;">
                    <?= htmlspecialchars($r['name']) ?>
                  </a>
                  <div class="text-muted" style="font-size:.72rem;"><?= htmlspecialchars($r['category']) ?></div>
                </div>
              </div>
            </td>
            <td><span class="badge <?= dietBadgeClass($r['diet_type']) ?>"><?= dietBadgeIcon($r['diet_type']) ?></span></td>
            <td class="fw-bold"><?= number_format($r['view_count']) ?></td>
            <td><span style="color:#fbbf24;">★</span> <?= number_format($r['average_rating'], 1) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <div class="p-3 text-end">
        <a href="<?= BASE_URL ?>views/admin/recipes.php" class="btn-admin-primary btn-sm">Manage Recipes</a>
      </div>
    </div>
  </div>

  <!-- Recent Users -->
  <div class="col-lg-6">
    <div class="admin-card">
      <div class="admin-card-header">
        <i class="bi bi-person-plus text-success"></i> Recent Registrations
      </div>
      <table class="admin-table">
        <thead>
          <tr><th>User</th><th>Email</th><th>Status</th><th>Joined</th></tr>
        </thead>
        <tbody>
          <?php foreach ($recentUsers as $u): ?>
          <tr>
            <td class="fw-bold"><?= htmlspecialchars($u['username']) ?></td>
            <td class="text-muted" style="font-size:.82rem;"><?= htmlspecialchars($u['email']) ?></td>
            <td>
              <span class="badge <?= $u['status'] === 'active' ? 'badge-veg' : 'bg-secondary text-white' ?>">
                <?= ucfirst($u['status'] ?? 'active') ?>
              </span>
            </td>
            <td class="text-muted" style="font-size:.78rem;"><?= timeAgo($u['created_at']) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <div class="p-3 text-end">
        <a href="<?= BASE_URL ?>views/admin/users.php" class="btn-admin-primary btn-sm">Manage Users</a>
      </div>
    </div>
  </div>
</div>

<!-- Quick Actions -->
<div class="row g-4 mt-2">
  <?php $actions = [
    ['bi-plus-circle-fill', 'Add New Recipe',      BASE_URL.'views/admin/recipes.php#add',      '#dcfce7','#16a34a'],
    ['bi-basket-fill',      'Add Ingredient',       BASE_URL.'views/admin/ingredients.php#add',  '#dbeafe','#3b82f6'],
    ['bi-people-fill',      'View All Users',       BASE_URL.'views/admin/users.php',             '#ede9fe','#8b5cf6'],
    ['bi-chat-left-text',   'View Feedback',        BASE_URL.'views/admin/feedback.php',          '#fef3c7','#d97706'],
  ];
  foreach ($actions as [$icon, $label, $url, $bg, $col]): ?>
    <div class="col-6 col-lg-3">
      <a href="<?= $url ?>" class="text-decoration-none">
        <div class="admin-stat-card" style="cursor:pointer;transition:transform .2s;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform=''">
          <div class="admin-stat-icon" style="background:<?= $bg ?>;color:<?= $col ?>;">
            <i class="bi <?= $icon ?>"></i>
          </div>
          <div class="fw-bold" style="font-size:.9rem;"><?= $label ?></div>
        </div>
      </a>
    </div>
  <?php endforeach; ?>
</div>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>
