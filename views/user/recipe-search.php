<?php
$page_title = 'Recipe Search';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/constants.php';
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

// Handle ingredient search from landing page
$search_ingredients_text = isset($_GET['ingredients']) ? trim($_GET['ingredients']) : '';
$selected_ingredient_names = [];

if (!empty($search_ingredients_text)) {
    $selected_ingredient_names = array_map('trim', explode(',', $search_ingredients_text));
}

// Get recipes to display
$recipes_to_show = [];
$search_term = '';
$is_search = false;

if (isset($_SESSION['matched_recipes'])) {
    $recipes_to_show = $_SESSION['matched_recipes'];
    $is_search = true;
    unset($_SESSION['matched_recipes']);
} elseif (isset($_SESSION['search_results'])) {
    $recipes_to_show = $_SESSION['search_results'];
    $search_term = $_SESSION['search_term'] ?? '';
    $is_search = true;
    unset($_SESSION['search_results']);
    unset($_SESSION['search_term']);
} elseif (!empty($selected_ingredient_names)) {
    // Search by ingredient names from landing page
    $recipes_to_show = $recipe->search(implode(' ', $selected_ingredient_names));
    $is_search = true;
} else {
    // Show all recipes by default
    $recipes_to_show = $recipe->getAll(20);
}

$total_results = count($recipes_to_show);

// Get unique categories from recipes for filters
$all_categories = defined('RECIPE_CATEGORIES') ? RECIPE_CATEGORIES : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> - SmartPantry</title>
    <link rel="stylesheet" href="<?php echo ASSETS_PATH; ?>css/landing.css">
    <link rel="stylesheet" href="<?php echo ASSETS_PATH; ?>css/recipe-search.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>

<!-- Navigation (same as landing) -->
<nav class="landing-nav">
    <div class="nav-container">
        <a href="<?php echo BASE_URL; ?>" class="nav-logo">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none">
                <rect width="24" height="24" rx="4" fill="#22c55e"/>
                <path d="M7 12l3 3 7-7" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span>SmartPantry</span>
        </a>
        <form method="POST" action="<?php echo BASE_URL; ?>controllers/RecipeController.php" class="nav-search-bar">
            <input type="hidden" name="action" value="search">
            <svg class="nav-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2">
                <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
            </svg>
            <input type="text" name="search" placeholder="Search recipes or ingredients..." 
                   value="<?php echo htmlspecialchars($search_ingredients_text ?: $search_term); ?>" class="nav-search-input">
            <button type="submit" class="nav-search-btn">Search</button>
        </form>
        <div class="nav-actions">
            <a href="<?php echo BASE_URL; ?>views/user/dashboard.php" class="nav-user-icon" title="<?php echo htmlspecialchars($_SESSION['username']); ?>">
                <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
            </a>
            <a href="<?php echo BASE_URL; ?>views/user/recipe-search.php" class="btn-nav-primary">Find Recipes</a>
            <a href="<?php echo BASE_URL; ?>controllers/AuthController.php?action=logout" class="btn-nav-outline">Logout</a>
        </div>
        <button class="mobile-menu-btn" id="mobileMenuBtn">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>
            </svg>
        </button>
    </div>
</nav>

<!-- Breadcrumb -->
<div class="rs-breadcrumb">
    <div class="rs-container">
        <a href="<?php echo BASE_URL; ?>views/user/home.php">Home</a>
        <span>&gt;</span>
        <a href="<?php echo BASE_URL; ?>views/user/recipe-search.php">Search</a>
        <span>&gt;</span>
        <span class="current">Results</span>
    </div>
</div>

<!-- Main Layout -->
<div class="rs-layout">
    <!-- Sidebar Filters -->
    <aside class="rs-sidebar">
        <div class="rs-filter-header">
            <h3>Filters</h3>
            <button type="button" class="rs-filter-reset" id="resetFilters">Reset</button>
        </div>

        <!-- Cuisine Filter -->
        <div class="rs-filter-group">
            <h4>CUISINE</h4>
            <?php foreach (array_slice($all_categories, 0, 5) as $cat): ?>
                <label class="rs-filter-option">
                    <input type="checkbox" class="rs-filter-checkbox cuisine-filter" value="<?php echo htmlspecialchars($cat); ?>">
                    <span class="rs-checkmark"></span>
                    <span><?php echo htmlspecialchars($cat); ?></span>
                </label>
            <?php endforeach; ?>
        </div>

        <!-- Dietary Filter -->
        <div class="rs-filter-group">
            <h4>DIETARY</h4>
            <label class="rs-filter-option">
                <input type="checkbox" class="rs-filter-checkbox dietary-filter" value="vegetarian">
                <span class="rs-checkmark"></span>
                <span>Vegetarian</span>
            </label>
            <label class="rs-filter-option">
                <input type="checkbox" class="rs-filter-checkbox dietary-filter" value="vegan">
                <span class="rs-checkmark"></span>
                <span>Vegan</span>
            </label>
            <label class="rs-filter-option">
                <input type="checkbox" class="rs-filter-checkbox dietary-filter" value="gluten-free">
                <span class="rs-checkmark"></span>
                <span>Gluten-Free</span>
            </label>
        </div>

        <!-- Cook Time Filter -->
        <div class="rs-filter-group">
            <h4>COOK TIME</h4>
            <div class="rs-range-wrapper">
                <input type="range" id="cookTimeRange" min="15" max="120" value="120" class="rs-range-input">
                <div class="rs-range-labels">
                    <span>15m</span>
                    <span id="cookTimeValue">2h+</span>
                </div>
            </div>
        </div>

        <button type="button" class="rs-more-filters-btn" id="moreFiltersBtn">More Filters</button>
    </aside>

    <!-- Main Content -->
    <main class="rs-main">
        <!-- Results Header -->
        <div class="rs-results-header">
            <div class="rs-results-info">
                <h1>Found <span id="resultCount"><?php echo $total_results; ?></span> recipes<?php echo $is_search ? ' for your ingredients' : ''; ?></h1>
                <?php if (!empty($selected_ingredient_names)): ?>
                    <div class="rs-ingredient-tags">
                        <span class="rs-tag-label">Ingredients included:</span>
                        <?php foreach ($selected_ingredient_names as $ing_name): ?>
                            <span class="rs-ingredient-tag">
                                <?php echo htmlspecialchars($ing_name); ?>
                                <button type="button" class="rs-tag-remove" data-ingredient="<?php echo htmlspecialchars($ing_name); ?>">&times;</button>
                            </span>
                        <?php endforeach; ?>
                        <button type="button" class="rs-add-more">+ Add more</button>
                    </div>
                <?php endif; ?>
                <p class="rs-showing-text">Showing 1-<?php echo min($total_results, 5); ?> of <?php echo $total_results; ?> results</p>
            </div>
            <div class="rs-sort-wrapper">
                <label>Sort by:</label>
                <select id="sortRecipes" class="rs-sort-select">
                    <option value="best">Best Match</option>
                    <option value="rating">Highest Rated</option>
                    <option value="time">Quickest</option>
                    <option value="calories-low">Lowest Calories</option>
                    <option value="calories-high">Highest Calories</option>
                </select>
            </div>
        </div>

        <!-- Alert Messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="rs-alert rs-alert-success">
                <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="rs-alert rs-alert-error">
                <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <!-- Recipe List -->
        <div class="rs-recipe-list" id="recipeList">
            <?php if (empty($recipes_to_show)): ?>
                <div class="rs-no-results">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#d1d5db" stroke-width="1.5">
                        <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                    </svg>
                    <h3>No recipes found</h3>
                    <p>Try adjusting your filters or search for different ingredients.</p>
                    <a href="<?php echo BASE_URL; ?>views/user/recipe-search.php" class="rs-btn-primary">Browse All Recipes</a>
                </div>
            <?php else: ?>
                <?php $index = 0; foreach ($recipes_to_show as $recipe_item): $index++; ?>
                    <div class="rs-recipe-card" 
                         data-category="<?php echo htmlspecialchars($recipe_item['category'] ?? ''); ?>"
                         data-time="<?php echo intval($recipe_item['prep_time'] ?? 0); ?>"
                         data-calories="<?php echo intval($recipe_item['calories'] ?? 0); ?>"
                         data-rating="<?php echo floatval($recipe_item['average_rating'] ?? 0); ?>">
                        <?php if ($index === 1 && $is_search): ?>
                            <span class="rs-badge-pick">BEST MATCH</span>
                        <?php endif; ?>
                        <div class="rs-card-image">
                            <img src="<?php echo ASSETS_PATH . 'images/' . ($recipe_item['image_url'] ?: 'recipes/default.jpg'); ?>" 
                                 alt="<?php echo htmlspecialchars($recipe_item['name']); ?>"
                                 onerror="this.src='<?php echo ASSETS_PATH; ?>images/recipes/default.jpg'">
                        </div>
                        <div class="rs-card-content">
                            <div class="rs-card-top">
                                <h3 class="rs-card-title"><?php echo htmlspecialchars($recipe_item['name']); ?></h3>
                                <button class="rs-favorite-btn" title="Add to favorites">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                                    </svg>
                                </button>
                            </div>
                            <?php if (($recipe_item['average_rating'] ?? 0) > 0): ?>
                                <div class="rs-card-rating">
                                    <?php echo displayStars($recipe_item['average_rating']); ?>
                                    <span class="rs-rating-count">(<?php echo $recipe_item['total_ratings'] ?? 0; ?> reviews)</span>
                                </div>
                            <?php endif; ?>
                            <p class="rs-card-desc"><?php echo truncate($recipe_item['description'] ?? 'A delicious recipe waiting to be discovered.', 120); ?></p>
                            <div class="rs-card-tags">
                                <?php if (!empty($recipe_item['category'])): ?>
                                    <span class="rs-tag rs-tag-cuisine"><?php echo htmlspecialchars($recipe_item['category']); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="rs-card-footer">
                                <div class="rs-card-meta">
                                    <span class="rs-meta-item">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                                        <?php echo intval($recipe_item['prep_time']); ?> min
                                    </span>
                                    <span class="rs-meta-item">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                                        <?php echo number_format($recipe_item['calories']); ?> kcal
                                    </span>
                                    <?php if ($is_search): ?>
                                        <span class="rs-meta-match">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="M22 4L12 14.01l-3-3"/></svg>
                                            100% Match
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <a href="<?php echo BASE_URL; ?>views/user/recipe-detail.php?id=<?php echo $recipe_item['id']; ?>" class="rs-view-btn">View Recipe</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Load More -->
        <?php if ($total_results > 5): ?>
            <div class="rs-load-more">
                <button type="button" class="rs-load-more-btn" id="loadMoreBtn">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 4v6h6M23 20v-6h-6"/><path d="M20.49 9A9 9 0 0 0 5.64 5.64L1 10m22 4l-4.64 4.36A9 9 0 0 1 3.51 15"/></svg>
                    Load More Recipes
                </button>
            </div>
        <?php endif; ?>
    </main>
</div>

<!-- Footer -->
<footer class="rs-footer">
    <div class="rs-footer-container">
        <div class="rs-footer-brand">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                <rect width="24" height="24" rx="4" fill="#22c55e"/>
                <path d="M7 12l3 3 7-7" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span>SmartPantry</span>
        </div>
        <div class="rs-footer-links">
            <a href="<?php echo BASE_URL; ?>views/user/home.php">About Us</a>
            <a href="#">Privacy Policy</a>
            <a href="<?php echo BASE_URL; ?>views/user/contact.php">Contact</a>
        </div>
        <p>&copy; <?php echo date('Y'); ?> SmartPantry. All rights reserved.</p>
    </div>
</footer>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Cook time range slider
    const cookTimeRange = document.getElementById('cookTimeRange');
    const cookTimeValue = document.getElementById('cookTimeValue');
    if (cookTimeRange) {
        cookTimeRange.addEventListener('input', function() {
            const val = parseInt(this.value);
            cookTimeValue.textContent = val >= 120 ? '2h+' : val + 'm';
            filterRecipes();
        });
    }

    // Filter checkboxes
    document.querySelectorAll('.rs-filter-checkbox').forEach(cb => {
        cb.addEventListener('change', filterRecipes);
    });

    // Sort select
    const sortSelect = document.getElementById('sortRecipes');
    if (sortSelect) {
        sortSelect.addEventListener('change', sortRecipes);
    }

    // Reset filters
    document.getElementById('resetFilters')?.addEventListener('click', function() {
        document.querySelectorAll('.rs-filter-checkbox').forEach(cb => cb.checked = false);
        if (cookTimeRange) { cookTimeRange.value = 120; cookTimeValue.textContent = '2h+'; }
        filterRecipes();
    });

    // Remove ingredient tag
    document.querySelectorAll('.rs-tag-remove').forEach(btn => {
        btn.addEventListener('click', function() {
            this.closest('.rs-ingredient-tag').remove();
        });
    });

    // Auto-hide alerts
    document.querySelectorAll('.rs-alert').forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });
});

function filterRecipes() {
    const cards = document.querySelectorAll('.rs-recipe-card');
    const cuisineFilters = [...document.querySelectorAll('.cuisine-filter:checked')].map(cb => cb.value.toLowerCase());
    const maxTime = parseInt(document.getElementById('cookTimeRange')?.value || 120);
    let visibleCount = 0;

    cards.forEach(card => {
        const category = (card.dataset.category || '').toLowerCase();
        const time = parseInt(card.dataset.time || 0);
        
        let show = true;
        if (cuisineFilters.length > 0 && !cuisineFilters.includes(category)) show = false;
        if (maxTime < 120 && time > maxTime) show = false;

        card.style.display = show ? '' : 'none';
        if (show) visibleCount++;
    });

    document.getElementById('resultCount').textContent = visibleCount;
}

function sortRecipes() {
    const list = document.getElementById('recipeList');
    const cards = [...list.querySelectorAll('.rs-recipe-card')];
    const sortBy = document.getElementById('sortRecipes').value;

    cards.sort((a, b) => {
        switch(sortBy) {
            case 'rating': return parseFloat(b.dataset.rating) - parseFloat(a.dataset.rating);
            case 'time': return parseInt(a.dataset.time) - parseInt(b.dataset.time);
            case 'calories-low': return parseInt(a.dataset.calories) - parseInt(b.dataset.calories);
            case 'calories-high': return parseInt(b.dataset.calories) - parseInt(a.dataset.calories);
            default: return 0;
        }
    });

    cards.forEach(card => list.appendChild(card));
}
</script>

</body>
</html>

