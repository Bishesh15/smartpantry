<?php
$page_title = 'Admin Dashboard';
require_once __DIR__ . '/../includes/admin-header.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../models/Recipe.php';
require_once __DIR__ . '/../../models/Rating.php';
require_once __DIR__ . '/../../models/Feedback.php';

// Require admin login - redirect if not logged in
if (!isAdmin() && !isLoggedIn()) {
    redirect(BASE_URL . 'views/admin/login.php');
}
// If user tries to access, show error but allow viewing
if (isLoggedIn() && !isAdmin()) {
    $_SESSION['error'] = 'You are logged in as a regular user. This is an admin-only page. Please logout first to access admin panel.';
}

$user = new User();
$recipe = new Recipe();
$rating = new Rating();
$feedback = new Feedback();

$stats = [
    'total_users' => $user->getTotalUsers(),
    'total_recipes' => $recipe->getTotalCount(),
    'total_ratings' => $rating->getTotalCount(),
    'total_feedback' => $feedback->getTotalCount(),
    'pending_feedback' => $feedback->getTotalCount('pending')
];
?>

<div class="container">
    <div class="admin-dashboard">
        <h1>Admin Dashboard</h1>
        
        <div class="admin-nav">
            <a href="<?php echo BASE_URL; ?>views/admin/recipes.php" class="admin-nav-link">Recipes</a>
            <a href="<?php echo BASE_URL; ?>views/admin/ingredients.php" class="admin-nav-link">Ingredients</a>
            <a href="<?php echo BASE_URL; ?>views/admin/feedback.php" class="admin-nav-link">Feedback</a>
            <a href="<?php echo BASE_URL; ?>controllers/AdminController.php?action=logout" class="admin-nav-link">Logout</a>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Users</h3>
                <p class="stat-number"><?php echo number_format($stats['total_users']); ?></p>
            </div>

            <div class="stat-card">
                <h3>Total Recipes</h3>
                <p class="stat-number"><?php echo number_format($stats['total_recipes']); ?></p>
            </div>

            <div class="stat-card">
                <h3>Total Ratings</h3>
                <p class="stat-number"><?php echo number_format($stats['total_ratings']); ?></p>
            </div>

            <div class="stat-card">
                <h3>Total Feedback</h3>
                <p class="stat-number"><?php echo number_format($stats['total_feedback']); ?></p>
                <?php if ($stats['pending_feedback'] > 0): ?>
                    <p class="stat-badge"><?php echo $stats['pending_feedback']; ?> pending</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="quick-actions">
            <h2>Quick Actions</h2>
            <div class="action-buttons">
                <a href="<?php echo BASE_URL; ?>views/admin/recipes.php?action=add" class="btn btn-primary">Add New Recipe</a>
                <a href="<?php echo BASE_URL; ?>views/admin/ingredients.php?action=add" class="btn btn-primary">Add New Ingredient</a>
                <a href="<?php echo BASE_URL; ?>views/admin/feedback.php" class="btn btn-secondary">View Feedback</a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>

