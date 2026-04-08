<?php
$page_title = 'My Pantry';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../models/User.php';

requireLogin();
$userModel = new User();
$userId    = (int)$_SESSION['user_id'];
$pantry    = $userModel->getPantry($userId);
$grouped   = [];
foreach ($pantry as $item) { $grouped[$item['category']][] = $item; }

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-xl py-4">
  <!-- Header -->
  <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
    <div>
      <h1 class="fw-black mb-1"><i class="bi bi-basket-fill text-success me-2"></i>My Pantry</h1>
      <p class="text-muted mb-0">
        You have <strong><?= count($pantry) ?></strong> ingredient<?= count($pantry) != 1 ? 's' : '' ?> stocked.
        <a href="<?= BASE_URL ?>views/user/recipe-search.php" class="ms-2 fw-bold text-success">
          <i class="bi bi-search me-1"></i>Find Recipes from My Pantry
        </a>
      </p>
    </div>
    <div class="d-flex gap-2">
      <button data-open-pantry-modal class="btn-sp-primary">
        <i class="bi bi-plus-lg"></i> Add Ingredient
      </button>
      <?php if (!empty($pantry)): ?>
        <form method="POST" action="<?= BASE_URL ?>controllers/UserController.php"
              onsubmit="return confirm('Clear ALL pantry items?')">
          <input type="hidden" name="action" value="clear_pantry">
          <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
          <button type="submit" class="btn btn-outline-danger fw-bold rounded-3">
            <i class="bi bi-trash"></i> Clear All
          </button>
        </form>
      <?php endif; ?>
    </div>
  </div>

  <!-- Search Filter -->
  <div class="mb-4">
    <div class="input-group" style="max-width:320px;">
      <span class="input-group-text bg-white border-end-0">
        <i class="bi bi-search text-muted"></i>
      </span>
      <input type="text" id="pantrySearch" class="form-control border-start-0"
             placeholder="Filter pantry items…" style="border-radius:0 10px 10px 0;">
    </div>
  </div>

  <!-- Pantry Grid -->
  <?php if (empty($pantry)): ?>
    <div class="sp-card p-5 text-center" style="border: 2px dashed #e2e8f0;">
      <i class="bi bi-basket" style="font-size:4rem;color:#cbd5e1;"></i>
      <h4 class="mt-3 fw-black text-muted">Your pantry is empty</h4>
      <p class="text-muted">Add ingredients you have at home to get personalised recipe recommendations.</p>
      <button data-open-pantry-modal class="btn-sp-primary mt-2">
        <i class="bi bi-plus-lg me-1"></i> Add Your First Ingredient
      </button>
    </div>
  <?php else: ?>
    <?php foreach ($grouped as $category => $items): ?>
      <div class="mb-4 category-section">
        <h5 class="fw-black mb-3 text-muted" style="font-size:.75rem;text-transform:uppercase;letter-spacing:2px;">
          <?= htmlspecialchars($category) ?>
        </h5>
        <div class="pantry-grid" id="pantry-items">
          <?php foreach ($items as $item): ?>
            <div class="pantry-item animate-fade" data-name="<?= strtolower(htmlspecialchars($item['name'])) ?>">
              <!-- Remove button -->
              <form method="POST" action="<?= BASE_URL ?>controllers/UserController.php">
                <input type="hidden" name="action" value="remove_from_pantry">
                <input type="hidden" name="ingredient_id" value="<?= $item['id'] ?>">
                <input type="hidden" name="redirect" value="<?= BASE_URL ?>views/user/pantry.php">
                <button type="submit" class="remove-btn" title="Remove">✕</button>
              </form>
              <div class="ing-icon">
                <?php
                $icons = ['Vegetables'=>'🥦','Fruits'=>'🍎','Proteins'=>'🍗','Grains'=>'🌾','Legumes'=>'🫘','Dairy'=>'🥛','Spices'=>'🌶️','Oils'=>'🫙','Extras'=>'🧂'];
                echo $icons[$item['category']] ?? '🍴';
                ?>
              </div>
              <h6 class="fw-black mb-1" style="font-size:.9rem;"><?= htmlspecialchars($item['name']) ?></h6>
              <small class="text-muted d-block" style="font-size:.7rem;text-transform:uppercase;"><?= htmlspecialchars($item['category']) ?></small>
              <small class="text-success fw-bold" style="font-size:.7rem;"><?= $item['calories_per_unit'] ?> kcal/<?= $item['unit'] ?></small>

              <!-- Quantity edit -->
              <form method="POST" action="<?= BASE_URL ?>controllers/UserController.php" class="mt-2 qty-edit-form">
                <input type="hidden" name="action" value="add_to_pantry">
                <input type="hidden" name="ingredient_id" value="<?= $item['id'] ?>">
                <input type="hidden" name="redirect" value="<?= BASE_URL ?>views/user/pantry.php">
                <div class="d-flex align-items-center gap-1 justify-content-center">
                  <input type="number" name="quantity" value="<?= $item['quantity'] ?>"
                         min="0.1" step="0.5" class="form-control form-control-sm text-center qty-input"
                         style="width:60px;border-radius:8px;"
                         onchange="this.closest('form').submit()">
                  <small class="text-muted"><?= $item['unit'] ?></small>
                </div>
              </form>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>

  <!-- "Find Recipes" CTA -->
  <?php if (count($pantry) >= 2): ?>
    <div class="mt-5 p-4 rounded-4 text-center" style="background:#f0fdf4;border:1.5px solid #bbf7d0;">
      <h5 class="fw-black mb-2">Ready to cook?</h5>
      <p class="text-muted small mb-3">You have <?= count($pantry) ?> ingredients — let the algorithm find your best matches.</p>
      <a href="<?= BASE_URL ?>views/user/recipe-search.php" class="btn-sp-primary">
        <i class="bi bi-cpu-fill me-1"></i> Find Matching Recipes
      </a>
    </div>
  <?php endif; ?>
</div>

<!-- ── Add Ingredient Modal ─────────────────────────────── -->
<div id="add-ingredient-modal" class="sp-modal-overlay" style="display:none;">
  <div class="sp-modal">
    <div class="sp-modal-header">
      <h5 class="sp-modal-title"><i class="bi bi-plus-circle-fill text-success me-2"></i>Add Ingredient</h5>
      <button id="modal-close" class="sp-modal-close">✕</button>
    </div>
    <p class="text-muted small mb-3">Search for ingredients to add to your pantry.</p>
    <input type="text" id="modal-search" class="sp-form-control mb-3" placeholder="Type ingredient name…" autocomplete="off">
    <div id="modal-results" style="max-height:300px;overflow-y:auto;"></div>
    <p class="text-muted text-center" style="font-size:.75rem;margin-top:1rem;">
      Can't find it? Ask your admin to add it to the database.
    </p>
  </div>
</div>

<script>
// Pantry filter
document.getElementById('pantrySearch')?.addEventListener('input', function() {
  const term = this.value.toLowerCase();
  document.querySelectorAll('.pantry-item').forEach(item => {
    item.style.display = item.dataset.name.includes(term) ? '' : 'none';
  });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
