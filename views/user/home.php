<?php
$page_title = 'Home';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../models/Recipe.php';
require_once __DIR__ . '/../../models/Ingredient.php';
require_once __DIR__ . '/../../controllers/RecipeController.php';

// Require user login - redirect if not logged in
if (!isLoggedIn()) {
    redirect(BASE_URL . 'views/user/login.php');
}

$recipe = new Recipe();
$ingredient = new Ingredient();
$recipeController = new RecipeController();

// Get all ingredients grouped by category
$ingredients_by_category = $ingredient->getByCategory();

// Get recipes to display
$recipes_to_show = [];

if (isset($_SESSION['matched_recipes'])) {
    $recipes_to_show = $_SESSION['matched_recipes'];
    unset($_SESSION['matched_recipes']);
} elseif (isset($_SESSION['search_results'])) {
    $recipes_to_show = $_SESSION['search_results'];
    unset($_SESSION['search_results']);
} else {
    // Show all recipes by default
    $recipes_to_show = $recipe->getAll(20);
}

// Get recently viewed recipes
$recent_recipes = [];
if (isLoggedIn() && isset($_SESSION['user_id'])) {
    $recent_recipes = $recipeController->getRecentViews($_SESSION['user_id'], 5);
}
?>

    <div class="home-layout">
        <!-- Ingredient Selection Sidebar -->
        <aside class="ingredient-sidebar">
            <h2>Select Ingredients</h2>
            <form method="POST" action="<?php echo BASE_URL; ?>controllers/RecipeController.php" id="ingredientForm">
                <input type="hidden" name="action" value="match">
                
                <div class="ingredient-search">
                    <input type="text" id="ingredientSearch" placeholder="Search ingredients...">
                </div>

                <div class="ingredient-categories">
                    <?php foreach ($ingredients_by_category as $category => $ingredients): ?>
                        <div class="ingredient-category">
                            <h3><?php echo htmlspecialchars($category); ?></h3>
                            <div class="ingredient-list">
                                <?php foreach ($ingredients as $ing): ?>
                                    <label class="ingredient-item">
                                        <input type="checkbox" 
                                               name="ingredients[]" 
                                               value="<?php echo $ing['id']; ?>"
                                               class="ingredient-checkbox"
                                               data-name="<?php echo htmlspecialchars(strtolower($ing['name'])); ?>">
                                        <span><?php echo htmlspecialchars($ing['name']); ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <button type="submit" class="btn btn-primary">Find Recipes</button>
                <button type="button" class="btn btn-secondary" id="clearSelection">Clear Selection</button>
            </form>
        </aside>

        <!-- Main Content -->
        <main class="main-content-area">
            <!-- Search Bar -->
            <div class="search-section">
                <form method="POST" action="<?php echo BASE_URL; ?>controllers/RecipeController.php" class="search-form">
                    <input type="hidden" name="action" value="search">
                    <input type="text" name="search" placeholder="Search recipes..." 
                           value="<?php echo isset($_SESSION['search_term']) ? htmlspecialchars($_SESSION['search_term']) : ''; ?>"
                           class="search-input">
                    <button type="submit" class="btn btn-primary">Search</button>
                </form>
                <?php if (isset($_SESSION['search_term'])): ?>
                    <p class="search-info">Search results for: <strong><?php echo htmlspecialchars($_SESSION['search_term']); ?></strong></p>
                    <?php unset($_SESSION['search_term']); ?>
                <?php endif; ?>
            </div>

            <!-- Recently Viewed Recipes -->
            <?php if (!empty($recent_recipes)): ?>
                <section class="recent-recipes">
                    <h2>Recently Viewed</h2>
                    <div class="recipe-grid">
                        <?php foreach ($recent_recipes as $recent_recipe): ?>
                            <div class="recipe-card">
                                <a href="<?php echo BASE_URL; ?>views/user/recipe-detail.php?id=<?php echo $recent_recipe['id']; ?>">
                                    <div class="recipe-image">
                                        <img src="<?php echo ASSETS_PATH . 'images/' . ($recent_recipe['image_url'] ?: 'recipes/default.jpg'); ?>" 
                                             alt="<?php echo htmlspecialchars($recent_recipe['name']); ?>">
                                    </div>
                                    <div class="recipe-info">
                                        <h3><?php echo htmlspecialchars($recent_recipe['name']); ?></h3>
                                        <div class="recipe-meta">
                                            <span class="time"><?php echo $recent_recipe['prep_time']; ?> min</span>
                                            <span class="calories"><?php echo number_format($recent_recipe['calories']); ?> cal</span>
                                        </div>
                                        <?php if ($recent_recipe['average_rating'] > 0): ?>
                                            <div class="recipe-rating">
                                                <?php echo displayStars($recent_recipe['average_rating']); ?>
                                                <span>(<?php echo $recent_recipe['total_ratings']; ?>)</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

            <!-- Recipe Results -->
            <section class="recipe-results">
                <h2><?php echo isset($_SESSION['matched_recipes']) ? 'Matching Recipes' : 'All Recipes'; ?></h2>
                
                <?php if (empty($recipes_to_show)): ?>
                    <div class="no-results">
                        <p>No recipes found. Try selecting different ingredients or search for recipes.</p>
                    </div>
                <?php else: ?>
                    <div class="recipe-grid">
                        <?php foreach ($recipes_to_show as $recipe_item): ?>
                            <div class="recipe-card">
                                <a href="<?php echo BASE_URL; ?>views/user/recipe-detail.php?id=<?php echo $recipe_item['id']; ?>">
                                    <div class="recipe-image">
                                        <img src="<?php echo ASSETS_PATH . 'images/' . ($recipe_item['image_url'] ?: 'recipes/default.jpg'); ?>" 
                                             alt="<?php echo htmlspecialchars($recipe_item['name']); ?>"
                                             onerror="this.src='<?php echo ASSETS_PATH; ?>images/recipes/default.jpg'">
                                    </div>
                                    <div class="recipe-info">
                                        <h3><?php echo htmlspecialchars($recipe_item['name']); ?></h3>
                                        <p class="recipe-description"><?php echo truncate($recipe_item['description'] ?? '', 80); ?></p>
                                        <div class="recipe-meta">
                                            <span class="time">⏱ <?php echo $recipe_item['prep_time']; ?> min</span>
                                            <span class="calories">🔥 <?php echo number_format($recipe_item['calories']); ?> cal</span>
                                        </div>
                                        <?php if ($recipe_item['average_rating'] > 0): ?>
                                            <div class="recipe-rating">
                                                <?php echo displayStars($recipe_item['average_rating']); ?>
                                                <span>(<?php echo $recipe_item['total_ratings']; ?>)</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
    </div>

<script src="<?php echo ASSETS_PATH; ?>js/recipe-matching.js"></script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

