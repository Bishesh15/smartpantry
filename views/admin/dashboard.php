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

// Fetch stats for charts
$userGrowth      = $userModel->getRegistrationStats(7);
$recipeCats      = $recipeModel->getCategoryStats();
$recipeDiets     = $recipeModel->getDietTypeStats();
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

<!-- Insights Charts -->
<div class="row g-4 mb-4">
  <!-- User Growth Chart -->
  <div class="col-lg-7">
    <div class="admin-card h-100">
      <div class="admin-card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-graph-up-arrow text-primary"></i> User Growth (Last 7 Days)</span>
        <span class="badge bg-primary-subtle text-primary border border-primary-subtle">Real-time</span>
      </div>
      <div class="p-4">
        <div style="height: 300px; position: relative;">
          <canvas id="growthChart"></canvas>
        </div>
      </div>
    </div>
  </div>
  
  <!-- Recipe & Diet Distribution -->
  <div class="col-lg-5">
    <div class="admin-card h-100">
      <div class="admin-card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-pie-chart-fill text-danger"></i> Pantry Composition</span>
      </div>
      <div class="p-4">
        <div class="row g-3">
          <div class="col-6 text-center">
            <div class="mb-3 fw-bold" style="font-size:.78rem;color:var(--admin-gray);">Cuisine Types</div>
            <div style="height: 200px; position: relative;">
              <canvas id="categoryChart"></canvas>
            </div>
          </div>
          <div class="col-6 text-center">
            <div class="mb-3 fw-bold" style="font-size:.78rem;color:var(--admin-gray);">Dietary Split</div>
            <div style="height: 200px; position: relative;">
              <canvas id="dietChart"></canvas>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

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

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctxGrowth = document.getElementById('growthChart').getContext('2d');
    const ctxCat    = document.getElementById('categoryChart').getContext('2d');
    const ctxDiet   = document.getElementById('dietChart').getContext('2d');

    // Colors
    const colors = {
        primary: '#3b82f6',
        success: '#10b981',
        warning: '#f59e0b',
        danger: '#ef4444',
        purple: '#8b5cf6',
        rose: '#f43f5e',
        teal: '#14b8a6'
    };

    // User Growth Bar Chart
    new Chart(ctxGrowth, {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_map(fn($d) => date('M j', strtotime($d)), array_keys($userGrowth))) ?>,
            datasets: [{
                label: 'New Users',
                data: <?= json_encode(array_values($userGrowth)) ?>,
                backgroundColor: colors.primary + '33',
                borderColor: colors.primary,
                borderWidth: 2,
                borderRadius: 5,
                hoverBackgroundColor: colors.primary
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1, precision: 0 } },
                x: { grid: { display: false } }
            }
        }
    });

    // Category Pie Chart
    new Chart(ctxCat, {
        type: 'pie',
        data: {
            labels: <?= json_encode(array_keys($recipeCats)) ?>,
            datasets: [{
                data: <?= json_encode(array_values($recipeCats)) ?>,
                backgroundColor: [colors.primary, colors.success, colors.warning, colors.danger, colors.purple, colors.rose],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10 } } } }
        }
    });

    // Diet Type Doughnut Chart
    new Chart(ctxDiet, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode(array_keys($recipeDiets)) ?>,
            datasets: [{
                data: <?= json_encode(array_values($recipeDiets)) ?>,
                backgroundColor: [colors.success, colors.rose, colors.warning],
                borderWidth: 0,
                hoverOffset: 15
            }]
        },
        options: {
            cutout: '70%',
            responsive: true,
            maintainAspectRatio: false,
            plugins: { 
                legend: { position: 'bottom', labels: { boxWidth: 8, font: { size: 9 }, padding: 15 } } 
            }
        }
    });
});
</script>
