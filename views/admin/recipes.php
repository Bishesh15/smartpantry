<?php
$page_title = 'Manage Recipes';
require_once __DIR__ . '/../includes/admin-header.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../models/Recipe.php';
require_once __DIR__ . '/../../models/Ingredient.php';

// Require admin login - redirect if not logged in
if (!isAdmin() && !isLoggedIn()) {
    redirect(BASE_URL . 'views/admin/login.php');
}
// If user tries to access, show error but allow viewing
if (isLoggedIn() && !isAdmin()) {
    $_SESSION['error'] = 'You are logged in as a regular user. This is an admin-only page. Please logout first to access admin panel.';
}

$recipe = new Recipe();
$ingredient = new Ingredient();

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$recipe_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$all_ingredients = $ingredient->getAll();
$recipes = $recipe->getAll(100);
$current_recipe = null;
$current_ingredients = [];

if ($action === 'edit' && $recipe_id > 0) {
    $current_recipe = $recipe->getById($recipe_id);
    if ($current_recipe) {
        $current_ingredients = $recipe->getIngredients($recipe_id);
    }
}
?>

<div class="container">
    <div class="admin-page">
        <div class="admin-header">
            <h1>Manage Recipes</h1>
            <a href="<?php echo BASE_URL; ?>views/admin/dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>

        <?php if ($action === 'add' || $action === 'edit'): ?>
            <!-- Add/Edit Recipe Form -->
            <div class="admin-form-container">
                <h2><?php echo $action === 'add' ? 'Add New Recipe' : 'Edit Recipe'; ?></h2>
                
                <form method="POST" action="<?php echo BASE_URL; ?>controllers/AdminController.php" enctype="multipart/form-data" class="admin-form">
                    <input type="hidden" name="action" value="<?php echo $action === 'add' ? 'create_recipe' : 'update_recipe'; ?>">
                    <?php if ($action === 'edit'): ?>
                        <input type="hidden" name="recipe_id" value="<?php echo $recipe_id; ?>">
                    <?php endif; ?>

                    <div class="form-group">
                        <label>Recipe Name *</label>
                        <input type="text" name="name" required 
                               value="<?php echo $current_recipe ? htmlspecialchars($current_recipe['name']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" rows="3"><?php echo $current_recipe ? htmlspecialchars($current_recipe['description']) : ''; ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Instructions *</label>
                        <textarea name="instructions" rows="10" required><?php echo $current_recipe ? htmlspecialchars($current_recipe['instructions']) : ''; ?></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Prep Time (minutes) *</label>
                            <input type="number" name="prep_time" required min="1" 
                                   value="<?php echo $current_recipe ? $current_recipe['prep_time'] : ''; ?>">
                        </div>

                        <div class="form-group">
                            <label>Category *</label>
                            <select name="category" required>
                                <?php foreach (RECIPE_CATEGORIES as $cat): ?>
                                    <option value="<?php echo $cat; ?>" 
                                            <?php echo ($current_recipe && $current_recipe['category'] === $cat) ? 'selected' : ''; ?>>
                                        <?php echo $cat; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Image</label>
                        <input type="file" name="image" accept="image/*">
                        <?php if ($current_recipe && $current_recipe['image_url']): ?>
                            <p>Current: <?php echo htmlspecialchars($current_recipe['image_url']); ?></p>
                        <?php endif; ?>
                        <input type="hidden" name="image_url" value="<?php echo $current_recipe ? htmlspecialchars($current_recipe['image_url']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label>Ingredients</label>
                        <div id="ingredients-list">
                            <?php if (!empty($current_ingredients)): ?>
                                <?php foreach ($current_ingredients as $ing): ?>
                                    <div class="ingredient-row">
                                        <select name="ingredient_ids[]" required>
                                            <option value="">Select ingredient</option>
                                            <?php foreach ($all_ingredients as $ing_option): ?>
                                                <option value="<?php echo $ing_option['id']; ?>" 
                                                        <?php echo ($ing['id'] == $ing_option['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($ing_option['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <input type="number" name="quantities[]" step="0.1" min="0" required 
                                               placeholder="Quantity" value="<?php echo $ing['quantity']; ?>">
                                        <button type="button" class="btn-remove" onclick="this.parentElement.remove()">Remove</button>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="ingredient-row">
                                    <select name="ingredient_ids[]">
                                        <option value="">Select ingredient</option>
                                        <?php foreach ($all_ingredients as $ing): ?>
                                            <option value="<?php echo $ing['id']; ?>"><?php echo htmlspecialchars($ing['name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <input type="number" name="quantities[]" step="0.1" min="0" placeholder="Quantity">
                                    <button type="button" class="btn-remove" onclick="this.parentElement.remove()">Remove</button>
                                </div>
                            <?php endif; ?>
                        </div>
                        <button type="button" class="btn btn-secondary" onclick="addIngredientRow()">Add Ingredient</button>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary"><?php echo $action === 'add' ? 'Create Recipe' : 'Update Recipe'; ?></button>
                        <a href="<?php echo BASE_URL; ?>views/admin/recipes.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <!-- Recipe List -->
            <div class="admin-list-header">
                <a href="<?php echo BASE_URL; ?>views/admin/recipes.php?action=add" class="btn btn-primary">Add New Recipe</a>
            </div>

            <div class="admin-table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Prep Time</th>
                            <th>Calories</th>
                            <th>Rating</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recipes as $rec): ?>
                            <tr>
                                <td><?php echo $rec['id']; ?></td>
                                <td><?php echo htmlspecialchars($rec['name']); ?></td>
                                <td><?php echo htmlspecialchars($rec['category']); ?></td>
                                <td><?php echo $rec['prep_time']; ?> min</td>
                                <td><?php echo number_format($rec['calories']); ?></td>
                                <td><?php echo number_format($rec['average_rating'], 1); ?> (<?php echo $rec['total_ratings']; ?>)</td>
                                <td>
                                    <a href="<?php echo BASE_URL; ?>views/admin/recipes.php?action=edit&id=<?php echo $rec['id']; ?>" class="btn btn-small">Edit</a>
                                    <a href="<?php echo BASE_URL; ?>controllers/AdminController.php?action=delete_recipe&id=<?php echo $rec['id']; ?>" 
                                       class="btn btn-small btn-danger" 
                                       onclick="return confirm('Are you sure you want to delete this recipe?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function addIngredientRow() {
    const list = document.getElementById('ingredients-list');
    const row = document.createElement('div');
    row.className = 'ingredient-row';
    row.innerHTML = `
        <select name="ingredient_ids[]" required>
            <option value="">Select ingredient</option>
            <?php foreach ($all_ingredients as $ing): ?>
                <option value="<?php echo $ing['id']; ?>"><?php echo htmlspecialchars($ing['name']); ?></option>
            <?php endforeach; ?>
        </select>
        <input type="number" name="quantities[]" step="0.1" min="0" required placeholder="Quantity">
        <button type="button" class="btn-remove" onclick="this.parentElement.remove()">Remove</button>
    `;
    list.appendChild(row);
}
</script>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>

