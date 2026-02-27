<?php
$page_title = 'Recipe Details';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../controllers/RecipeController.php';
require_once __DIR__ . '/../../controllers/UserController.php';

// Require user login - redirect if not logged in
if (!isLoggedIn()) {
    redirect(BASE_URL . 'views/user/login.php');
}

$recipe_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($recipe_id <= 0) {
    $_SESSION['error'] = 'Invalid recipe';
    redirect(BASE_URL . 'views/user/home.php');
}

$recipeController = new RecipeController();
$userController = new UserController();

$recipe = $recipeController->viewDetail($recipe_id);

if (!$recipe) {
    $_SESSION['error'] = 'Recipe not found';
    redirect(BASE_URL . 'views/user/home.php');
}

// Check if recipe is favorited (only if user is logged in)
$is_favorited = false;
$user_rating = null;
if (isLoggedIn() && isset($_SESSION['user_id'])) {
    $is_favorited = $userController->isFavorited($_SESSION['user_id'], $recipe_id);
    
    // Get user's rating
    require_once __DIR__ . '/../../models/Rating.php';
    $ratingModel = new Rating();
    $user_rating = $ratingModel->getUserRating($_SESSION['user_id'], $recipe_id);
}
?>

    <div class="recipe-detail">
        <div class="recipe-header">
            <div class="recipe-image-large">
                <img src="<?php echo ASSETS_PATH . 'images/' . ($recipe['image_url'] ?: 'recipes/default.jpg'); ?>" 
                     alt="<?php echo htmlspecialchars($recipe['name']); ?>"
                     onerror="this.src='<?php echo ASSETS_PATH; ?>images/recipes/default.jpg'">
            </div>
            <div class="recipe-header-info">
                <h1><?php echo htmlspecialchars($recipe['name']); ?></h1>
                <p class="recipe-category"><?php echo htmlspecialchars($recipe['category']); ?></p>
                
                <?php if ($recipe['average_rating'] > 0): ?>
                    <div class="recipe-rating-large">
                        <?php echo displayStars($recipe['average_rating']); ?>
                        <span class="rating-text">
                            <?php echo number_format($recipe['average_rating'], 1); ?> 
                            (<?php echo $recipe['total_ratings']; ?> ratings)
                        </span>
                    </div>
                <?php endif; ?>

                <div class="recipe-stats">
                    <div class="stat-item">
                        <span class="stat-label">Prep Time</span>
                        <span class="stat-value"><?php echo $recipe['prep_time']; ?> min</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Calories</span>
                        <span class="stat-value"><?php echo number_format($recipe['calories']); ?> cal</span>
                    </div>
                </div>

                <!-- Favorite Button -->
                <form method="POST" action="<?php echo BASE_URL; ?>controllers/UserController.php" class="favorite-form">
                    <input type="hidden" name="action" value="<?php echo $is_favorited ? 'remove_favorite' : 'add_favorite'; ?>">
                    <input type="hidden" name="recipe_id" value="<?php echo $recipe_id; ?>">
                    <input type="hidden" name="redirect" value="<?php echo BASE_URL . 'views/user/recipe-detail.php?id=' . $recipe_id; ?>">
                    <button type="submit" class="btn <?php echo $is_favorited ? 'btn-secondary' : 'btn-primary'; ?>">
                        <?php echo $is_favorited ? '★ Remove from Favorites' : '☆ Add to Favorites'; ?>
                    </button>
                </form>
            </div>
        </div>

        <?php if (!empty($recipe['description'])): ?>
            <div class="recipe-section">
                <h2>Description</h2>
                <p><?php echo nl2br(htmlspecialchars($recipe['description'])); ?></p>
            </div>
        <?php endif; ?>

        <div class="recipe-content">
            <div class="recipe-ingredients">
                <h2>Ingredients</h2>
                <ul class="ingredient-list">
                    <?php foreach ($recipe['ingredients'] as $ingredient): ?>
                        <li>
                            <span class="ingredient-name"><?php echo htmlspecialchars($ingredient['name']); ?></span>
                            <span class="ingredient-quantity">
                                <?php echo number_format($ingredient['quantity'], 1); ?> 
                                <?php echo htmlspecialchars($ingredient['unit']); ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="recipe-instructions">
                <h2>Instructions</h2>
                <div class="instructions-content">
                    <?php echo nl2br(htmlspecialchars($recipe['instructions'])); ?>
                </div>
            </div>
        </div>

        <!-- Rating Section -->
        <div class="recipe-rating-section">
            <h2>Rate this Recipe</h2>
            
            <?php if ($user_rating): ?>
                <p class="rating-info">You rated this recipe: <?php echo $user_rating['rating']; ?> stars</p>
            <?php endif; ?>

            <form method="POST" action="<?php echo BASE_URL; ?>controllers/UserController.php" class="rating-form">
                <input type="hidden" name="action" value="add_rating">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <input type="hidden" name="recipe_id" value="<?php echo $recipe_id; ?>">
                
                <div class="rating-input">
                    <label>Rating (1-5 stars):</label>
                    <select name="rating" required>
                        <option value="">Select rating</option>
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <option value="<?php echo $i; ?>" <?php echo ($user_rating && $user_rating['rating'] == $i) ? 'selected' : ''; ?>>
                                <?php echo $i; ?> star<?php echo $i > 1 ? 's' : ''; ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div class="comment-input">
                    <label>Comment (optional):</label>
                    <textarea name="comment" rows="4" placeholder="Share your thoughts about this recipe..."><?php echo $user_rating ? htmlspecialchars($user_rating['comment']) : ''; ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary">Submit Rating</button>
            </form>
        </div>

        <!-- User Reviews -->
        <?php if (!empty($recipe['ratings'])): ?>
            <div class="recipe-reviews">
                <h2>User Reviews</h2>
                <div class="reviews-list">
                    <?php foreach ($recipe['ratings'] as $review): ?>
                        <div class="review-item">
                            <div class="review-header">
                                <span class="review-author"><?php echo htmlspecialchars($review['username']); ?></span>
                                <span class="review-rating"><?php echo displayStars($review['rating']); ?></span>
                                <span class="review-date"><?php echo timeAgo($review['created_at']); ?></span>
                            </div>
                            <?php if (!empty($review['comment'])): ?>
                                <p class="review-comment"><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

