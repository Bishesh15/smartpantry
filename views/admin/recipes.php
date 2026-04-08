<?php
$page_title = 'Recipe Studio';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../models/Recipe.php';
require_once __DIR__ . '/../../models/Ingredient.php';

require_once __DIR__ . '/../includes/admin-header.php';

$recipeModel     = new Recipe();
$ingredientModel = new Ingredient();
$allRecipes      = $recipeModel->getAll(100);
$allIngredients  = $ingredientModel->getAll();

// Build calorie map for JS
$calMap = [];
foreach ($allIngredients as $ing) {
    $calMap[(int)$ing['id']] = (float)$ing['calories_per_unit'];
}

// Edit mode?
$editRecipe  = null;
$editIngreds = [];
if (!empty($_GET['edit'])) {
    $editRecipe  = $recipeModel->getById((int)$_GET['edit']);
    if ($editRecipe) $editIngreds = $recipeModel->getIngredients((int)$_GET['edit']);
}
?>

<!-- ── Recipes Table ───────────────────────────────────── -->
<div class="admin-card mb-4">
  <div class="admin-card-header d-flex justify-content-between align-items-center">
    <span><i class="bi bi-book me-2"></i>All Recipes (<?= count($allRecipes) ?>)</span>
    <button onclick="document.getElementById('addRecipePanel').style.display='block';this.closest('.admin-card').style.display='none';"
            class="btn-admin-primary btn-sm">
      <i class="bi bi-plus-lg"></i> Add Recipe
    </button>
  </div>
  <!-- Filter -->
  <div class="admin-card-body pb-0">
    <input type="text" id="recipeSearch" class="admin-input" placeholder="Search recipes…" style="max-width:320px;">
  </div>
  <table class="admin-table">
    <thead>
      <tr>
        <th>Recipe</th><th>Diet</th><th>Category</th><th>Calories</th><th>Prep</th><th>Rating</th><th>Views</th><th>Actions</th>
      </tr>
    </thead>
    <tbody id="recipeTableBody">
      <?php foreach ($allRecipes as $r): ?>
        <tr class="recipe-row" data-name="<?= strtolower(htmlspecialchars($r['name'])) ?>">
          <td>
            <div class="d-flex align-items-center gap-2">
              <img src="<?= resolveImageUrl($r['image_url'] ?? '') ?>"
                   class="recipe-thumb"
                   onerror="this.src='<?= ASSETS_PATH ?>images/default-recipes.jpg'">
              <div>
                <div class="fw-bold" style="font-size:.88rem;"><?= htmlspecialchars($r['name']) ?></div>
                <div class="text-muted" style="font-size:.72rem;"><?= truncate($r['description'] ?? '', 50) ?></div>
              </div>
            </div>
          </td>
          <td><span class="badge <?= dietBadgeClass($r['diet_type']) ?>"><?= dietBadgeIcon($r['diet_type']) ?> <?= htmlspecialchars($r['diet_type']) ?></span></td>
          <td><?= htmlspecialchars($r['category']) ?></td>
          <td><?= number_format($r['calories']) ?> kcal</td>
          <td><?= $r['prep_time'] ?>m</td>
          <td>⭐ <?= number_format($r['average_rating'], 1) ?> <small class="text-muted">(<?= $r['total_ratings'] ?>)</small></td>
          <td><?= number_format($r['view_count']) ?></td>
          <td>
            <div class="d-flex gap-1">
              <a href="?edit=<?= $r['id'] ?>" class="btn-admin-warning btn-sm">
                <i class="bi bi-pencil"></i>
              </a>
              <a href="<?= BASE_URL ?>controllers/AdminController.php?action=delete_recipe&id=<?= $r['id'] ?>"
                 class="btn-admin-danger btn-sm"
                 data-confirm="Delete '<?= htmlspecialchars($r['name']) ?>'? This cannot be undone.">
                <i class="bi bi-trash"></i>
              </a>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<!-- ── Add/Edit Recipe Form ──────────────────────────────── -->
<div id="addRecipePanel" style="display:<?= $editRecipe ? 'block' : 'none' ?>;">
  <div class="admin-card">
    <div class="admin-card-header d-flex justify-content-between align-items-center">
      <span><i class="bi bi-<?= $editRecipe ? 'pencil' : 'plus-circle' ?> me-2"></i>
        <?= $editRecipe ? 'Edit: ' . htmlspecialchars($editRecipe['name']) : 'Add New Recipe' ?>
      </span>
      <a href="<?= BASE_URL ?>views/admin/recipes.php" class="btn-admin-secondary btn-sm">← Cancel</a>
    </div>
    <div class="admin-card-body">
      <form method="POST" action="<?= BASE_URL ?>controllers/AdminController.php"
            enctype="multipart/form-data">
        <?php if ($editRecipe): ?>
          <input type="hidden" name="action" value="update_recipe">
          <input type="hidden" name="recipe_id" value="<?= $editRecipe['id'] ?>">
        <?php else: ?>
          <input type="hidden" name="action" value="create_recipe">
        <?php endif; ?>
        <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">

        <div class="row g-4">
          <!-- Left: Basic Info -->
          <div class="col-lg-8">
            <div class="row g-3">
              <div class="col-12">
                <label class="admin-label">Recipe Name *</label>
                <input type="text" name="name" class="admin-input" required
                       value="<?= htmlspecialchars($editRecipe['name'] ?? '') ?>"
                       placeholder="e.g. Garlic Butter Pasta">
              </div>
              <div class="col-12">
                <label class="admin-label">Short Description</label>
                <textarea name="description" class="admin-input" rows="2"
                          placeholder="A brief, appetizing description…"><?= htmlspecialchars($editRecipe['description'] ?? '') ?></textarea>
              </div>
              <div class="col-sm-4">
                <label class="admin-label">Diet Type *</label>
                <select name="diet_type" class="admin-input" required>
                  <?php foreach (DIET_TYPES as $dt): ?>
                    <option value="<?= $dt ?>" <?= ($editRecipe['diet_type'] ?? '') === $dt ? 'selected' : '' ?>>
                      <?= dietBadgeIcon($dt) ?> <?= $dt ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-sm-4">
                <label class="admin-label">Cuisine Category</label>
                <select name="category" class="admin-input">
                  <?php foreach (RECIPE_CATEGORIES as $cat): ?>
                    <option value="<?= $cat ?>" <?= ($editRecipe['category'] ?? '') === $cat ? 'selected' : '' ?>><?= $cat ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-sm-2">
                <label class="admin-label">Prep Time (min)</label>
                <input type="number" name="prep_time" class="admin-input" min="1" required
                       value="<?= $editRecipe['prep_time'] ?? 30 ?>">
              </div>
              <div class="col-sm-2">
                <label class="admin-label">Servings</label>
                <input type="number" name="servings" class="admin-input" min="1" required
                       value="<?= $editRecipe['servings'] ?? 2 ?>">
              </div>
              <div class="col-12">
                <label class="admin-label">Step-by-Step Instructions *</label>
                <textarea name="instructions" class="admin-input" rows="10" required
                          placeholder="1. Boil water...&#10;2. Add pasta..."><?= htmlspecialchars($editRecipe['instructions'] ?? '') ?></textarea>
                <small class="text-muted">One step per line. Number each step (1. 2. 3. …)</small>
              </div>
            </div>
          </div>

          <!-- Right: Image + Calories + Ingredients -->
          <div class="col-lg-4">
            <!-- Image Upload -->
            <label class="admin-label mb-2">Recipe Image</label>
            <div class="image-preview-box mb-2" id="image-preview-box"
                 onclick="document.getElementById('image-file-input').click();">
              <?php if (!empty($editRecipe['image_url'])): ?>
                <img src="<?= resolveImageUrl($editRecipe['image_url']) ?>" style="width:100%;height:100%;object-fit:cover;">
              <?php else: ?>
                <div class="preview-placeholder text-center">
                  <i class="bi bi-camera" style="font-size:2rem;display:block;color:#94a3b8"></i>
                  <small>Click to upload</small>
                </div>
              <?php endif; ?>
            </div>
            <input type="file" name="image_file" id="image-file-input" accept="image/*" class="d-none">
            <div class="mb-3">
              <label class="admin-label">— OR paste image URL —</label>
              <input type="text" name="image_url" id="image-url-input" class="admin-input"
                     placeholder="https://example.com/image.jpg"
                     value="<?= htmlspecialchars(str_starts_with($editRecipe['image_url'] ?? '', 'http') ? $editRecipe['image_url'] : '') ?>">
              <small class="text-muted d-block mt-1">URL takes lower priority than uploaded file.</small>
            </div>

            <!-- Calorie Preview -->
            <div class="calorie-display mb-3">
              <span class="cal-label">Calculated Calories</span>
              <span class="cal-value" id="calorie-count">
                <?= number_format($editRecipe['calories'] ?? 0) ?>
              </span>
              <span class="cal-label">kcal (auto-calculated)</span>
            </div>
          </div>

          <!-- Ingredients Section -->
          <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <label class="admin-label mb-0">Recipe Ingredients *</label>
              <button type="button" class="btn-admin-primary btn-sm" onclick="addIngredientRow()">
                <i class="bi bi-plus-lg"></i> Add Row
              </button>
            </div>
            <div id="ingredients-container">
              <?php
              $displayIngreds = !empty($editIngreds) ? $editIngreds : [['id'=>0,'quantity'=>1]];
              foreach ($displayIngreds as $idx => $ei):
              ?>
                <div class="ingredient-row">
                  <select name="ingredient_ids[]" class="admin-input" required>
                    <option value="">— Select Ingredient —</option>
                    <?php foreach ($allIngredients as $ing): ?>
                      <option value="<?= $ing['id'] ?>"
                              data-cal="<?= $ing['calories_per_unit'] ?>"
                              <?= ((int)$ei['id'] === (int)$ing['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($ing['name']) ?>
                        (<?= htmlspecialchars($ing['calories_per_unit']) ?> kcal/<?= $ing['unit'] ?>)
                      </option>
                    <?php endforeach; ?>
                  </select>
                  <input type="number" name="quantities[]" class="admin-input qty-input"
                         value="<?= $ei['quantity'] ?? 1 ?>" min="0.1" step="0.5" placeholder="Qty">
                  <button type="button" class="btn-remove-row" onclick="this.closest('.ingredient-row').remove();">
                    <i class="bi bi-trash"></i>
                  </button>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

        <div class="mt-4 d-flex gap-2">
          <button type="submit" class="btn-admin-primary">
            <i class="bi bi-check-circle me-1"></i>
            <?= $editRecipe ? 'Update Recipe' : 'Create Recipe' ?>
          </button>
          <a href="<?= BASE_URL ?>views/admin/recipes.php" class="btn-admin-secondary">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// Pass calorie map to JS for live calculation
window.ingredientCalMap = <?= json_encode($calMap) ?>;

// Live recipe filter
document.getElementById('recipeSearch')?.addEventListener('input', function() {
  const term = this.value.toLowerCase();
  document.querySelectorAll('.recipe-row').forEach(row => {
    row.style.display = row.dataset.name.includes(term) ? '' : 'none';
  });
});
</script>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>
