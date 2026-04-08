<?php
/**
 * Recipe Search — SuperCook-style Ingredient Selector with live AJAX matching
 */
$page_title = 'Find Recipes';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../models/Ingredient.php';
require_once __DIR__ . '/../../models/Recipe.php';

$ingredientModel = new Ingredient();
$grouped         = $ingredientModel->getGroupedByCategory();

// Pre-selected from session (after a form search/match)
$preSelectedIds  = $_SESSION['match_ingredient_ids'] ?? [];
$preResults      = $_SESSION['match_results']        ?? $_SESSION['search_results'] ?? [];
$searchTerm      = $_SESSION['search_term']          ?? '';

// If no results in session, fetch default recipes for the gallery
if (empty($preResults)) {
    $recipeModel = new Recipe();
    $preResults  = $recipeModel->getAll(24);
}

// Pantry IDs for "Use My Pantry" button
$pantryIds    = [];
$pantryNames  = [];
if (isLoggedIn()) {
    require_once __DIR__ . '/../../models/User.php';
    $userModel   = new User();
    $pantryItems = $userModel->getPantry($_SESSION['user_id']);
    $pantryIds   = array_column($pantryItems, 'id');
    $pantryNames = array_column($pantryItems, 'name');
}

// Build JS-friendly pre-selected array
$preSelectedJS = [];
foreach ($preSelectedIds as $id) {
    $ing = $ingredientModel->getById((int)$id);
    if ($ing) $preSelectedJS[] = ['id' => (int)$id, 'name' => $ing['name']];
}

require_once __DIR__ . '/../includes/header.php';
?>

<!-- ── Page Layout ───────────────────────────────────────── -->
<div class="container-xl py-4" style="margin-top:20px;">
  <!-- Title + Search Bar -->
  <div class="row mb-4 align-items-center">
    <div class="col">
      <h1 class="fw-black mb-1">Find Recipes</h1>
      <p class="text-muted mb-0">Select what you have — we rank every recipe by how well it matches</p>
    </div>
    <div class="col-auto">
      <form method="POST" action="<?= BASE_URL ?>controllers/RecipeController.php" class="d-flex gap-2" id="text-search-form">
        <input type="hidden" name="action" value="search">
        <input type="text" name="search_term" class="form-control"
               placeholder="Search by name…"
               value="<?= htmlspecialchars($searchTerm) ?>"
               style="min-width:220px;border-radius:10px;">
        <button type="submit" class="btn btn-outline-success fw-bold rounded-3">
          <i class="bi bi-search"></i>
        </button>
      </form>
    </div>
  </div>

  <div class="row g-4">
    <!-- ── Left: Ingredient Panel ─────────────────────────── -->
    <div class="col-lg-3">
      <div class="ingredient-panel">
        <!-- Action Buttons -->
        <div class="d-flex gap-2 mb-3 flex-wrap">
          <?php if (isLoggedIn() && !empty($pantryIds)): ?>
            <button id="use-pantry-btn" class="btn btn-success btn-sm fw-bold"
                    data-pantry-ids='<?= json_encode(array_map('intval', $pantryIds)) ?>'
                    data-pantry-names='<?= htmlspecialchars(json_encode($pantryNames), ENT_QUOTES) ?>'>
              <i class="bi bi-basket-fill me-1"></i> Use My Pantry
            </button>
          <?php elseif (isLoggedIn()): ?>
            <a href="<?= BASE_URL ?>views/user/pantry.php" class="btn btn-outline-success btn-sm fw-bold">
              <i class="bi bi-basket me-1"></i> Add to Pantry
            </a>
          <?php endif; ?>
          <button id="clear-all-btn" class="btn btn-outline-secondary btn-sm fw-bold">
            <i class="bi bi-x-circle me-1"></i> Clear
          </button>
        </div>

        <!-- Ingredient Search within panel -->
        <div class="mb-3">
          <input type="text" id="panel-search"
                 class="form-control form-control-sm"
                 placeholder="Filter ingredients…"
                 style="border-radius:8px;">
        </div>

        <!-- Grouped Ingredient Chips -->
        <?php foreach ($grouped as $category => $ings): ?>
          <div class="ingredient-category" data-category="<?= htmlspecialchars($category) ?>">
            <h6><?= htmlspecialchars($category) ?></h6>
            <?php foreach ($ings as $ing): ?>
              <span class="ingredient-chip"
                    data-id="<?= $ing['id'] ?>"
                    data-name="<?= htmlspecialchars($ing['name']) ?>"
                    title="<?= htmlspecialchars($ing['calories_per_unit'] . ' kcal/' . $ing['unit']) ?>">
                <?= htmlspecialchars($ing['name']) ?>
              </span>
            <?php endforeach; ?>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- ── Right: Selected + Results ──────────────────────── -->
    <div class="col-lg-9">
      <!-- Selected Ingredients Bar -->
      <div class="selected-bar mb-3" id="selected-bar">
        <span class="text-muted small">Click ingredients on the left to add them here…</span>
      </div>

      <!-- Results Grid -->
      <div class="results-header">
        <div class="results-count" id="results-count">
          <?= count($preResults) ?> recipes found
        </div>
        <div class="d-flex gap-2 flex-wrap">
          <select class="form-select form-select-sm" id="diet-filter" style="width:auto;border-radius:8px;">
            <option value="">All Diet Types</option>
            <?php foreach (DIET_TYPES as $dt): ?>
              <option value="<?= $dt ?>"><?= $dt ?></option>
            <?php endforeach; ?>
          </select>
          <select class="form-select form-select-sm" id="sort-select" style="width:auto;border-radius:8px;">
            <option value="match">Best Match</option>
            <option value="calories">Lowest Calories</option>
            <option value="time">Fastest Prep</option>
            <option value="rating">Highest Rated</option>
          </select>
        </div>
      </div>

      <!-- No-results placeholder -->
      <div id="no-results-msg" style="display:none;"
           class="text-center py-5">
        <i class="bi bi-basket" style="font-size:4rem;color:#cbd5e1;"></i>
        <h5 class="mt-3 text-muted">Select ingredients on the left to find matching recipes.</h5>
        <p class="text-muted small">We rank every recipe by how many of your selected ingredients it uses.</p>
      </div>

      <!-- Results Grid -->
      <div class="row g-4" id="results-grid">
        <?php if (!empty($preResults)): ?>
          <?php foreach ($preResults as $r): ?>
            <div class="col-sm-6 col-xl-4">
              <div class="sp-card recipe-card animate-fade"
                   data-diet="<?= htmlspecialchars($r['diet_type']) ?>"
                   data-time="<?= $r['prep_time'] ?>">
                <?php if (($r['match_percentage'] ?? 0) > 0): ?>
                  <?php
                  $mp  = $r['match_percentage'];
                  $col = $mp >= 70 ? '#16a34a' : ($mp >= 40 ? '#d97706' : '#ef4444');
                  ?>
                  <div class="match-badge" style="background:<?= $col ?>;"><?= $mp ?>% Match</div>
                <?php endif; ?>
                <a href="<?= BASE_URL ?>views/user/recipe-detail.php?id=<?= $r['id'] ?>">
                  <img src="<?= resolveImageUrl($r['image_url'] ?? '') ?>"
                       class="card-img-top w-100" style="height:200px;object-fit:cover;"
                       alt="<?= htmlspecialchars($r['name']) ?>"
                       onerror="this.src='<?= ASSETS_PATH ?>images/default-recipes.jpg'">
                </a>
                <div class="card-body">
                  <h5 class="card-title">
                    <a href="<?= BASE_URL ?>views/user/recipe-detail.php?id=<?= $r['id'] ?>"
                       class="text-dark text-decoration-none">
                      <?= htmlspecialchars($r['name']) ?>
                    </a>
                  </h5>
                  <div class="card-meta mb-2">
                    <span><i class="bi bi-clock me-1"></i><?= $r['prep_time'] ?>m</span>
                    <span><i class="bi bi-fire me-1 text-success"></i><?= number_format($r['calories']) ?> kcal</span>
                  </div>
                  <span class="badge <?= dietBadgeClass($r['diet_type']) ?> mb-2">
                    <?= dietBadgeIcon($r['diet_type']) ?> <?= htmlspecialchars($r['diet_type']) ?>
                  </span>
                  <?php if (!empty($r['matched_ingredients'])): ?>
                    <div class="ingredient-tags mt-1">
                      <?php foreach (array_slice($r['matched_ingredients'], 0, 4) as $m): ?>
                        <span class="ing-have small">✓ <?= htmlspecialchars($m) ?></span>
                      <?php endforeach; ?>
                      <?php foreach (array_slice($r['missing_ingredients'] ?? [], 0, 3) as $n): ?>
                        <span class="ing-need small">✗ <?= htmlspecialchars($n) ?></span>
                      <?php endforeach; ?>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
      <!-- End Results Grid -->
    </div>
  </div>
</div>

<script>
// Pass pre-selected ingredients to JS (from previous session search)
window.preSelectedIngredients = <?= json_encode($preSelectedJS) ?>;
</script>

<script>
// Panel ingredient filter
document.getElementById('panel-search')?.addEventListener('input', function() {
  const term = this.value.toLowerCase();
  document.querySelectorAll('.ingredient-chip').forEach(chip => {
    chip.style.display = chip.dataset.name.toLowerCase().includes(term) ? '' : 'none';
  });
  // Hide/show category headers
  document.querySelectorAll('.ingredient-category').forEach(cat => {
    const visible = [...cat.querySelectorAll('.ingredient-chip')].some(c => c.style.display !== 'none');
    cat.style.display = visible ? '' : 'none';
  });
});

// Clear session variables when user freshly navigates here
<?php
// Clear previous session results when page is loaded fresh (no POST)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    unset($_SESSION['match_results'], $_SESSION['match_ingredient_ids'],
          $_SESSION['search_results'], $_SESSION['search_term']);
}
?>
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
