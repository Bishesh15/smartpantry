<?php
$page_title = 'Recipe Details';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../controllers/RecipeController.php';
require_once __DIR__ . '/../../controllers/UserController.php';

// Require user login
if (!isLoggedIn()) {
    redirect(BASE_URL . 'views/user/login.php');
}

$recipe_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($recipe_id <= 0) {
    $_SESSION['error'] = 'Invalid recipe';
    redirect(BASE_URL . 'views/user/recipe-search.php');
}

$recipeController = new RecipeController();
$userController = new UserController();

$recipe = $recipeController->viewDetail($recipe_id);

if (!$recipe) {
    $_SESSION['error'] = 'Recipe not found';
    redirect(BASE_URL . 'views/user/recipe-search.php');
}

// Check if recipe is favorited
$is_favorited = false;
$user_rating = null;
if (isLoggedIn() && isset($_SESSION['user_id'])) {
    $is_favorited = $userController->isFavorited($_SESSION['user_id'], $recipe_id);
    require_once __DIR__ . '/../../models/Rating.php';
    $ratingModel = new Rating();
    $user_rating = $ratingModel->getUserRating($_SESSION['user_id'], $recipe_id);
}

// Get similar recipes (same category, excluding current)
$recipeModel = new Recipe();
$similar_recipes = $recipeModel->getAll(20);
$similar_recipes = array_filter($similar_recipes, function($r) use ($recipe) {
    return $r['id'] != $recipe['id'] && $r['category'] === $recipe['category'];
});
$similar_recipes = array_slice($similar_recipes, 0, 4);

// If not enough similar, fill with other recipes
if (count($similar_recipes) < 4) {
    $other = array_filter($recipeModel->getAll(20), function($r) use ($recipe, $similar_recipes) {
        $similar_ids = array_column($similar_recipes, 'id');
        return $r['id'] != $recipe['id'] && !in_array($r['id'], $similar_ids);
    });
    $similar_recipes = array_merge($similar_recipes, array_slice($other, 0, 4 - count($similar_recipes)));
}

// Parse instructions into steps
$raw_instructions = $recipe['instructions'];
$steps = [];
// Try splitting by numbered patterns like "1." "2." etc.
if (preg_match('/\d+\.\s/', $raw_instructions)) {
    $parts = preg_split('/(?=\d+\.\s)/', $raw_instructions, -1, PREG_SPLIT_NO_EMPTY);
    foreach ($parts as $part) {
        $clean = preg_replace('/^\d+\.\s*/', '', trim($part));
        if (!empty($clean)) {
            // Try to extract a title (first sentence or line)
            $lines = preg_split('/\n/', $clean, 2);
            if (count($lines) > 1 && strlen($lines[0]) < 60) {
                $steps[] = ['title' => trim($lines[0]), 'body' => trim($lines[1])];
            } else {
                $steps[] = ['title' => '', 'body' => trim($clean)];
            }
        }
    }
} else {
    // Split by double newline or single newline
    $parts = preg_split('/\n\s*\n|\n/', $raw_instructions, -1, PREG_SPLIT_NO_EMPTY);
    foreach ($parts as $part) {
        $clean = trim($part);
        if (!empty($clean)) {
            $steps[] = ['title' => '', 'body' => $clean];
        }
    }
}
if (empty($steps)) {
    $steps[] = ['title' => '', 'body' => $raw_instructions];
}

// Estimate nutrition (protein, carbs, fat, fiber) from calories
$cals = floatval($recipe['calories']);
$est_protein = round($cals * 0.25 / 4); // 25% from protein
$est_carbs = round($cals * 0.45 / 4);   // 45% from carbs
$est_fat = round($cals * 0.30 / 9);     // 30% from fat
$est_fiber = round($cals * 0.02 / 2);   // rough estimate

// Servings estimate (based on calories for ~400 cal serving)
$servings = max(1, round($cals / 400));
if ($servings < 1) $servings = 4;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($recipe['name']); ?> - SmartPantry</title>
    <link rel="stylesheet" href="<?php echo ASSETS_PATH; ?>css/landing.css">
    <link rel="stylesheet" href="<?php echo ASSETS_PATH; ?>css/recipe-detail.css">
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
            <input type="text" name="search" placeholder="Search recipes or ingredients..." class="nav-search-input">
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
<div class="rd-breadcrumb">
    <div class="rd-container">
        <a href="<?php echo BASE_URL; ?>views/user/home.php">Home</a>
        <span>&rsaquo;</span>
        <a href="<?php echo BASE_URL; ?>views/user/recipe-search.php">Recipes</a>
        <span>&rsaquo;</span>
        <span class="current"><?php echo htmlspecialchars($recipe['name']); ?></span>
    </div>
</div>

<!-- Flash Messages -->
<?php if (isset($_SESSION['success'])): ?>
    <div class="rd-container"><div class="rd-alert rd-alert-success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div></div>
<?php endif; ?>
<?php if (isset($_SESSION['error'])): ?>
    <div class="rd-container"><div class="rd-alert rd-alert-error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div></div>
<?php endif; ?>

<!-- Hero Section -->
<section class="rd-hero">
    <div class="rd-container">
        <div class="rd-hero-grid">
            <div class="rd-hero-content">
                <div class="rd-badges">
                    <span class="rd-badge rd-badge-fresh">&#127807; FRESH &amp; LOCAL</span>
                    <span class="rd-badge rd-badge-category"><?php echo htmlspecialchars(strtoupper($recipe['category'])); ?></span>
                </div>
                <h1 class="rd-title">
                    Authentic <span class="text-green"><?php echo htmlspecialchars($recipe['name']); ?></span>
                    <br><?php echo htmlspecialchars($recipe['category']); ?> Style
                </h1>
                <p class="rd-description">
                    <?php echo !empty($recipe['description']) ? htmlspecialchars($recipe['description']) : 'A delicious ' . htmlspecialchars($recipe['category']) . ' recipe perfect for any occasion.'; ?>
                </p>

                <div class="rd-meta-bar">
                    <div class="rd-meta-item">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        <div>
                            <span class="rd-meta-label">PREP TIME</span>
                            <span class="rd-meta-value"><?php echo $recipe['prep_time']; ?> mins</span>
                        </div>
                    </div>
                    <div class="rd-meta-item">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="2"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/></svg>
                        <div>
                            <span class="rd-meta-label">CALORIES</span>
                            <span class="rd-meta-value"><?php echo number_format($recipe['calories']); ?> kcal</span>
                        </div>
                    </div>
                    <div class="rd-meta-item">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        <div>
                            <span class="rd-meta-label">SERVINGS</span>
                            <span class="rd-meta-value"><?php echo $servings; ?> People</span>
                        </div>
                    </div>
                    <div class="rd-meta-item">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2" fill="#f59e0b"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        <div>
                            <span class="rd-meta-label">RATING</span>
                            <span class="rd-meta-value"><?php echo number_format($recipe['average_rating'], 1); ?> (<?php echo $recipe['total_ratings']; ?>)</span>
                        </div>
                    </div>
                </div>

                <div class="rd-author-row">
                    <div class="rd-author">
                        <div class="rd-author-avatar">
                            <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                        </div>
                        <div>
                            <span class="rd-author-label">Recipe by</span>
                            <span class="rd-author-name">Chef <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                        </div>
                    </div>
                    <form method="POST" action="<?php echo BASE_URL; ?>controllers/UserController.php" class="rd-save-form">
                        <input type="hidden" name="action" value="<?php echo $is_favorited ? 'remove_favorite' : 'add_favorite'; ?>">
                        <input type="hidden" name="recipe_id" value="<?php echo $recipe_id; ?>">
                        <input type="hidden" name="redirect" value="<?php echo BASE_URL . 'views/user/recipe-detail.php?id=' . $recipe_id; ?>">
                        <button type="submit" class="rd-save-btn <?php echo $is_favorited ? 'saved' : ''; ?>">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="<?php echo $is_favorited ? '#22c55e' : 'none'; ?>" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
                            <?php echo $is_favorited ? 'Saved' : 'Save Recipe'; ?>
                        </button>
                    </form>
                </div>
            </div>
            <div class="rd-hero-image">
                <img src="<?php echo ASSETS_PATH . 'images/' . ($recipe['image_url'] ?: 'recipes/default.jpg'); ?>" 
                     alt="<?php echo htmlspecialchars($recipe['name']); ?>"
                     onerror="this.src='<?php echo ASSETS_PATH; ?>images/recipes/default.jpg'">
                <div class="rd-nutrition-badge">
                    <span class="rd-nutrition-icon">&#9889;</span>
                    <span class="rd-nutrition-label">HIGH PROTEIN</span>
                    <span class="rd-nutrition-text">Ideal for post-workout meal</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Ingredients & Nutrition Section -->
<section class="rd-ingredients-section">
    <div class="rd-container">
        <div class="rd-ing-grid">
            <!-- Ingredients Card -->
            <div class="rd-ing-card">
                <div class="rd-ing-header">
                    <h2>Ingredients</h2>
                    <div class="rd-unit-toggle">
                        <button class="rd-unit-btn active">Metric</button>
                        <button class="rd-unit-btn">US</button>
                    </div>
                </div>

                <?php
                // Group ingredients by category if available, otherwise show flat
                $grouped = [];
                foreach ($recipe['ingredients'] as $ing) {
                    $cat = isset($ing['category']) ? $ing['category'] : 'Ingredients';
                    $grouped[$cat][] = $ing;
                }
                ?>

                <?php foreach ($grouped as $cat_name => $items): ?>
                    <h4 class="rd-ing-category"><?php echo htmlspecialchars(strtoupper('For the ' . $cat_name)); ?></h4>
                    <ul class="rd-ing-list">
                        <?php foreach ($items as $ing): ?>
                            <li class="rd-ing-item">
                                <span class="rd-ing-circle"></span>
                                <span><?php echo number_format($ing['quantity'], 1); ?> <?php echo htmlspecialchars($ing['unit']); ?> <?php echo htmlspecialchars($ing['name']); ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endforeach; ?>

                <button class="rd-shopping-btn">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 1.98-1.67L23 6H6"/></svg>
                    Add ingredients to shopping list
                </button>
            </div>

            <!-- Nutrition Card -->
            <div class="rd-nutrition-card">
                <div class="rd-nutrition-header">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
                    <h3>Nutrition Facts</h3>
                </div>
                <div class="rd-nutrition-list">
                    <div class="rd-nutrition-row">
                        <span>Calories</span>
                        <span class="rd-nutrition-val"><?php echo number_format($recipe['calories']); ?> kcal</span>
                    </div>
                    <div class="rd-nutrition-row">
                        <span>Carbs</span>
                        <span class="rd-nutrition-val"><?php echo $est_carbs; ?>g</span>
                    </div>
                    <div class="rd-nutrition-row rd-highlight-row">
                        <span>Protein</span>
                        <span class="rd-nutrition-val rd-highlight"><?php echo $est_protein; ?>g</span>
                    </div>
                    <div class="rd-nutrition-row">
                        <span>Fat</span>
                        <span class="rd-nutrition-val"><?php echo $est_fat; ?>g</span>
                    </div>
                    <div class="rd-nutrition-row">
                        <span>Fiber</span>
                        <span class="rd-nutrition-val"><?php echo $est_fiber; ?>g</span>
                    </div>
                </div>
                <div class="rd-protein-badge">
                    This recipe covers ~<?php echo min(100, round($est_protein / 50 * 100)); ?>% of your daily recommended protein intake.
                </div>

                <!-- Community Photo Upload -->
                <div class="rd-community-card">
                    <h4 class="rd-community-title">Tried this recipe?</h4>
                    <p class="rd-community-text">Upload a photo of your creation and inspire others in the SmartPantry community!</p>
                    <button class="rd-upload-btn">Upload Photo</button>
                </div>

                <!-- Related Tags -->
                <div class="rd-tags-section">
                    <h4 class="rd-tags-title">RELATED TAGS</h4>
                    <div class="rd-tags-list">
                        <span class="rd-tag"><?php echo htmlspecialchars($recipe['category']); ?></span>
                        <?php
                        // Generate tags from category and recipe properties
                        $tags = ['Dinner', 'Homemade'];
                        if ($recipe['prep_time'] <= 30) $tags[] = 'Quick';
                        if ($recipe['calories'] < 500) $tags[] = 'Light';
                        if ($est_protein > 20) $tags[] = 'High Protein';
                        foreach (array_slice($tags, 0, 4) as $tag):
                        ?>
                            <span class="rd-tag"><?php echo $tag; ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Instructions Section -->
<section class="rd-instructions-section">
    <div class="rd-container">
        <h2 class="rd-section-title">Instructions</h2>
        <div class="rd-steps">
            <?php foreach ($steps as $i => $step): ?>
                <div class="rd-step">
                    <div class="rd-step-number"><?php echo $i + 1; ?></div>
                    <div class="rd-step-content">
                        <?php if (!empty($step['title'])): ?>
                            <h3 class="rd-step-title"><?php echo htmlspecialchars($step['title']); ?></h3>
                        <?php else: ?>
                            <h3 class="rd-step-title">Step <?php echo $i + 1; ?></h3>
                        <?php endif; ?>
                        <p class="rd-step-body"><?php echo nl2br(htmlspecialchars($step['body'])); ?></p>
                    </div>
                </div>
                <?php if ($i < count($steps) - 1): ?>
                    <div class="rd-step-connector"></div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Rating Section -->
<section class="rd-rating-section">
    <div class="rd-container">
        <h2 class="rd-section-title">Rate this Recipe</h2>
        <?php if ($user_rating): ?>
            <p class="rd-rating-info">You rated this recipe: <strong><?php echo $user_rating['rating']; ?> stars</strong></p>
        <?php endif; ?>
        <form method="POST" action="<?php echo BASE_URL; ?>controllers/UserController.php" class="rd-rating-form">
            <input type="hidden" name="action" value="add_rating">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <input type="hidden" name="recipe_id" value="<?php echo $recipe_id; ?>">
            <div class="rd-rating-stars">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <label class="rd-star-label">
                        <input type="radio" name="rating" value="<?php echo $i; ?>" <?php echo ($user_rating && $user_rating['rating'] == $i) ? 'checked' : ''; ?> required>
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#d1d5db" stroke-width="1.5"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                    </label>
                <?php endfor; ?>
            </div>
            <textarea name="comment" class="rd-comment-input" rows="3" placeholder="Share your thoughts about this recipe..."><?php echo $user_rating ? htmlspecialchars($user_rating['comment']) : ''; ?></textarea>
            <button type="submit" class="rd-submit-btn">Submit Rating</button>
        </form>

        <!-- User Reviews -->
        <?php if (!empty($recipe['ratings'])): ?>
            <div class="rd-reviews">
                <h3 class="rd-reviews-title">User Reviews</h3>
                <?php foreach ($recipe['ratings'] as $review): ?>
                    <div class="rd-review">
                        <div class="rd-review-header">
                            <div class="rd-review-avatar"><?php echo strtoupper(substr($review['username'], 0, 1)); ?></div>
                            <div>
                                <span class="rd-review-author"><?php echo htmlspecialchars($review['username']); ?></span>
                                <span class="rd-review-date"><?php echo timeAgo($review['created_at']); ?></span>
                            </div>
                            <div class="rd-review-stars"><?php echo displayStars($review['rating']); ?></div>
                        </div>
                        <?php if (!empty($review['comment'])): ?>
                            <p class="rd-review-comment"><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- You Might Also Like -->
<?php if (!empty($similar_recipes)): ?>
<section class="rd-similar-section">
    <div class="rd-container">
        <h2 class="rd-section-title">You might also like</h2>
        <div class="rd-similar-grid">
            <?php foreach ($similar_recipes as $sr): ?>
                <a href="<?php echo BASE_URL; ?>views/user/recipe-detail.php?id=<?php echo $sr['id']; ?>" class="rd-similar-card">
                    <div class="rd-similar-img">
                        <img src="<?php echo ASSETS_PATH . 'images/' . ($sr['image_url'] ?: 'recipes/default.jpg'); ?>" 
                             alt="<?php echo htmlspecialchars($sr['name']); ?>"
                             onerror="this.src='<?php echo ASSETS_PATH; ?>images/recipes/default.jpg'">
                        <span class="rd-similar-cal"><?php echo number_format($sr['calories']); ?> kcal</span>
                    </div>
                    <div class="rd-similar-info">
                        <h4><?php echo htmlspecialchars($sr['name']); ?></h4>
                        <p><?php echo htmlspecialchars(substr($sr['description'] ?: 'A delicious recipe.', 0, 60)); ?></p>
                        <div class="rd-similar-footer">
                            <?php echo displayStars($sr['average_rating']); ?>
                            <span class="rd-similar-link">View Recipe</span>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Footer -->
<footer class="rd-footer">
    <div class="rd-footer-container">
        <div class="rd-footer-col rd-footer-brand">
            <div class="rd-footer-logo">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <rect width="24" height="24" rx="4" fill="#22c55e"/>
                    <path d="M7 12l3 3 7-7" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span>SmartPantry</span>
            </div>
            <p>Making healthy cooking accessible, fun, and personalized for everyone, everywhere.</p>
        </div>
        <div class="rd-footer-col">
            <h4>Product</h4>
            <a href="<?php echo BASE_URL; ?>views/user/recipe-search.php">Recipes</a>
            <a href="<?php echo BASE_URL; ?>views/user/home.php">Home</a>
        </div>
        <div class="rd-footer-col">
            <h4>Company</h4>
            <a href="<?php echo BASE_URL; ?>views/user/home.php#how-it-works">About Us</a>
            <a href="<?php echo BASE_URL; ?>views/user/contact.php">Contact</a>
        </div>
        <div class="rd-footer-col">
            <h4>Stay Updated</h4>
            <div class="rd-footer-newsletter">
                <input type="email" placeholder="Email address">
                <button>&#10148;</button>
            </div>
        </div>
    </div>
    <div class="rd-footer-bottom">
        <p>&copy; <?php echo date('Y'); ?> SmartPantry. All rights reserved.</p>
    </div>
</footer>

<script>
// Star rating interactivity
document.querySelectorAll('.rd-star-label').forEach((label, idx) => {
    label.addEventListener('mouseenter', () => {
        document.querySelectorAll('.rd-star-label svg').forEach((svg, si) => {
            if (si <= idx) {
                svg.setAttribute('fill', '#f59e0b');
                svg.setAttribute('stroke', '#f59e0b');
            } else {
                svg.setAttribute('fill', 'none');
                svg.setAttribute('stroke', '#d1d5db');
            }
        });
    });
    label.addEventListener('click', () => {
        label.querySelector('input').checked = true;
        document.querySelectorAll('.rd-star-label svg').forEach((svg, si) => {
            if (si <= idx) {
                svg.setAttribute('fill', '#f59e0b');
                svg.setAttribute('stroke', '#f59e0b');
            } else {
                svg.setAttribute('fill', 'none');
                svg.setAttribute('stroke', '#d1d5db');
            }
        });
    });
});
document.querySelector('.rd-rating-stars')?.addEventListener('mouseleave', () => {
    const checked = document.querySelector('.rd-star-label input:checked');
    const checkedIdx = checked ? parseInt(checked.value) - 1 : -1;
    document.querySelectorAll('.rd-star-label svg').forEach((svg, si) => {
        if (si <= checkedIdx) {
            svg.setAttribute('fill', '#f59e0b');
            svg.setAttribute('stroke', '#f59e0b');
        } else {
            svg.setAttribute('fill', 'none');
            svg.setAttribute('stroke', '#d1d5db');
        }
    });
});

// Unit toggle (visual only)
document.querySelectorAll('.rd-unit-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.rd-unit-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
    });
});

// Auto-dismiss alerts
document.querySelectorAll('.rd-alert').forEach(el => {
    setTimeout(() => el.style.opacity = '0', 3000);
    setTimeout(() => el.remove(), 3500);
});
</script>
</body>
</html>

