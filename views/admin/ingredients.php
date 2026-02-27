<?php
$page_title = 'Manage Ingredients';
require_once __DIR__ . '/../includes/admin-header.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../models/Ingredient.php';

// Require admin login - redirect if not logged in
if (!isAdmin() && !isLoggedIn()) {
    redirect(BASE_URL . 'views/admin/login.php');
}
// If user tries to access, show error but allow viewing
if (isLoggedIn() && !isAdmin()) {
    $_SESSION['error'] = 'You are logged in as a regular user. This is an admin-only page. Please logout first to access admin panel.';
}

$ingredient = new Ingredient();

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$ingredient_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$ingredients = $ingredient->getAll();
$current_ingredient = null;

if ($action === 'edit' && $ingredient_id > 0) {
    $current_ingredient = $ingredient->getById($ingredient_id);
}
?>

<div class="container">
    <div class="admin-page">
        <div class="admin-header">
            <h1>Manage Ingredients</h1>
            <a href="<?php echo BASE_URL; ?>views/admin/dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>

        <?php if ($action === 'add' || $action === 'edit'): ?>
            <!-- Add/Edit Ingredient Form -->
            <div class="admin-form-container">
                <h2><?php echo $action === 'add' ? 'Add New Ingredient' : 'Edit Ingredient'; ?></h2>
                
                <form method="POST" action="<?php echo BASE_URL; ?>controllers/AdminController.php" enctype="multipart/form-data" class="admin-form">
                    <input type="hidden" name="action" value="<?php echo $action === 'add' ? 'create_ingredient' : 'update_ingredient'; ?>">
                    <?php if ($action === 'edit'): ?>
                        <input type="hidden" name="ingredient_id" value="<?php echo $ingredient_id; ?>">
                    <?php endif; ?>

                    <div class="form-group">
                        <label>Ingredient Name *</label>
                        <input type="text" name="name" required 
                               value="<?php echo $current_ingredient ? htmlspecialchars($current_ingredient['name']) : ''; ?>">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Category *</label>
                            <select name="category" required>
                                <?php foreach (INGREDIENT_CATEGORIES as $cat): ?>
                                    <option value="<?php echo $cat; ?>" 
                                            <?php echo ($current_ingredient && $current_ingredient['category'] === $cat) ? 'selected' : ''; ?>>
                                        <?php echo $cat; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Unit *</label>
                            <input type="text" name="unit" required 
                                   value="<?php echo $current_ingredient ? htmlspecialchars($current_ingredient['unit']) : 'gram'; ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Calories per Unit *</label>
                        <input type="number" name="calories_per_unit" step="0.01" min="0" required 
                               value="<?php echo $current_ingredient ? $current_ingredient['calories_per_unit'] : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label>Image</label>
                        <input type="file" name="image" accept="image/*">
                        <?php if ($current_ingredient && $current_ingredient['image_url']): ?>
                            <p>Current: <?php echo htmlspecialchars($current_ingredient['image_url']); ?></p>
                        <?php endif; ?>
                        <input type="hidden" name="image_url" value="<?php echo $current_ingredient ? htmlspecialchars($current_ingredient['image_url']) : ''; ?>">
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary"><?php echo $action === 'add' ? 'Create Ingredient' : 'Update Ingredient'; ?></button>
                        <a href="<?php echo BASE_URL; ?>views/admin/ingredients.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <!-- Ingredient List -->
            <div class="admin-list-header">
                <a href="<?php echo BASE_URL; ?>views/admin/ingredients.php?action=add" class="btn btn-primary">Add New Ingredient</a>
            </div>

            <div class="admin-table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Calories/Unit</th>
                            <th>Unit</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ingredients as $ing): ?>
                            <tr>
                                <td><?php echo $ing['id']; ?></td>
                                <td><?php echo htmlspecialchars($ing['name']); ?></td>
                                <td><?php echo htmlspecialchars($ing['category']); ?></td>
                                <td><?php echo number_format($ing['calories_per_unit'], 2); ?></td>
                                <td><?php echo htmlspecialchars($ing['unit']); ?></td>
                                <td>
                                    <a href="<?php echo BASE_URL; ?>views/admin/ingredients.php?action=edit&id=<?php echo $ing['id']; ?>" class="btn btn-small">Edit</a>
                                    <a href="<?php echo BASE_URL; ?>controllers/AdminController.php?action=delete_ingredient&id=<?php echo $ing['id']; ?>" 
                                       class="btn btn-small btn-danger" 
                                       onclick="return confirm('Are you sure you want to delete this ingredient?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>

