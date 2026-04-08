<?php
$page_title = 'Ingredients';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../models/Ingredient.php';

require_once __DIR__ . '/../includes/admin-header.php';

$ingredientModel = new Ingredient();
$allIngredients  = $ingredientModel->getAll();
$grouped         = [];
foreach ($allIngredients as $i) { $grouped[$i['category']][] = $i; }

$editIng = null;
if (!empty($_GET['edit'])) {
    $editIng = $ingredientModel->getById((int)$_GET['edit']);
}
?>

<!-- Add New Ingredient -->
<div class="admin-card mb-4" id="addIngredientPanel">
  <div class="admin-card-header">
    <i class="bi bi-<?= $editIng ? 'pencil' : 'plus-circle' ?> me-2"></i>
    <?= $editIng ? 'Edit: ' . htmlspecialchars($editIng['name']) : 'Add New Ingredient' ?>
  </div>
  <div class="admin-card-body">
    <form method="POST" action="<?= BASE_URL ?>controllers/AdminController.php" enctype="multipart/form-data">
      <?php if ($editIng): ?>
        <input type="hidden" name="action" value="update_ingredient">
        <input type="hidden" name="ingredient_id" value="<?= $editIng['id'] ?>">
      <?php else: ?>
        <input type="hidden" name="action" value="create_ingredient">
      <?php endif; ?>
      <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">

      <div class="row g-3 align-items-start">
        <div class="col-sm-4">
          <label class="admin-label">Ingredient Name *</label>
          <input type="text" name="name" class="admin-input" required
                 value="<?= htmlspecialchars($editIng['name'] ?? '') ?>"
                 placeholder="e.g. Bell Pepper">
        </div>
        <div class="col-sm-3">
          <label class="admin-label">Category *</label>
          <select name="category" class="admin-input" required>
            <?php foreach (INGREDIENT_CATEGORIES as $cat): ?>
              <option value="<?= $cat ?>" <?= ($editIng['category'] ?? '') === $cat ? 'selected' : '' ?>><?= $cat ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-sm-2">
          <label class="admin-label">Calories/Unit</label>
          <input type="number" name="calories_per_unit" class="admin-input" step="0.01" min="0" required
                 value="<?= $editIng['calories_per_unit'] ?? 0 ?>" placeholder="0.00">
        </div>
        <div class="col-sm-3">
          <label class="admin-label">Unit</label>
          <input type="text" name="unit" class="admin-input" required
                 value="<?= htmlspecialchars($editIng['unit'] ?? 'piece') ?>"
                 placeholder="piece / cup / tsp / tbsp / gram">
        </div>
        <div class="col-sm-6">
          <label class="admin-label">Image — Upload File</label>
          <input type="file" name="image_file" class="admin-input" accept="image/*">
        </div>
        <div class="col-sm-6">
          <label class="admin-label">— OR Image URL —</label>
          <input type="text" name="image_url" class="admin-input"
                 placeholder="https://…"
                 value="<?= htmlspecialchars(str_starts_with($editIng['image_url'] ?? '', 'http') ? $editIng['image_url'] : '') ?>">
        </div>
        <div class="col-12">
          <button type="submit" class="btn-admin-primary">
            <i class="bi bi-check-circle me-1"></i>
            <?= $editIng ? 'Update Ingredient' : 'Add Ingredient' ?>
          </button>
          <?php if ($editIng): ?>
            <a href="<?= BASE_URL ?>views/admin/ingredients.php" class="btn-admin-secondary ms-2">Cancel</a>
          <?php endif; ?>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Ingredient Table -->
<div class="admin-card">
  <div class="admin-card-header d-flex justify-content-between align-items-center">
    <span><i class="bi bi-basket me-2"></i>All Ingredients (<?= count($allIngredients) ?>)</span>
    <input type="text" id="ingSearch" class="admin-input" placeholder="Filter…" style="max-width:220px;padding:.4rem .75rem;">
  </div>
  <table class="admin-table">
    <thead>
      <tr><th>Name</th><th>Category</th><th>Calories/Unit</th><th>Unit</th><th>Image</th><th>Actions</th></tr>
    </thead>
    <tbody>
      <?php foreach ($allIngredients as $ing): ?>
        <tr class="ing-row" data-name="<?= strtolower(htmlspecialchars($ing['name'])) ?>">
          <td class="fw-bold"><?= htmlspecialchars($ing['name']) ?></td>
          <td>
            <span class="badge bg-light text-dark border"><?= htmlspecialchars($ing['category']) ?></span>
          </td>
          <td><?= $ing['calories_per_unit'] ?> kcal</td>
          <td class="text-muted">per <?= htmlspecialchars($ing['unit']) ?></td>
          <td>
            <?php if (!empty($ing['image_url'])): ?>
              <img src="<?= resolveImageUrl($ing['image_url']) ?>"
                   style="width:40px;height:40px;border-radius:8px;object-fit:cover;"
                   onerror="this.style.display='none'">
            <?php else: ?>
              <span class="text-muted small">—</span>
            <?php endif; ?>
          </td>
          <td>
            <div class="d-flex gap-1">
              <a href="?edit=<?= $ing['id'] ?>" class="btn-admin-warning btn-sm">
                <i class="bi bi-pencil"></i> Edit
              </a>
              <a href="<?= BASE_URL ?>controllers/AdminController.php?action=delete_ingredient&id=<?= $ing['id'] ?>"
                 class="btn-admin-danger btn-sm"
                 data-confirm="Delete '<?= htmlspecialchars($ing['name']) ?>'? This will remove it from all recipes.">
                <i class="bi bi-trash"></i>
              </a>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<script>
document.getElementById('ingSearch')?.addEventListener('input', function() {
  const term = this.value.toLowerCase();
  document.querySelectorAll('.ing-row').forEach(r => {
    r.style.display = r.dataset.name.includes(term) ? '' : 'none';
  });
});
</script>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>
