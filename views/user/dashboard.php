<?php
$page_title = 'Dashboard';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../models/Recipe.php';

// Require login
if (!isLoggedIn()) {
    redirect(BASE_URL . 'views/user/login.php');
}

$userModel = new User();
$recipeModel = new Recipe();

// Get user data
$user = $userModel->getUserById($_SESSION['user_id']);
$stats = $userModel->getUserStats($_SESSION['user_id']);
$favorites = $userModel->getFavorites($_SESSION['user_id'], 3);
$recent_views = $userModel->getRecentViews($_SESSION['user_id'], 3);

// Parse current preferences
$current_food_prefs = !empty($user['food_preferences']) ? explode(',', $user['food_preferences']) : [];
$current_dietary = !empty($user['dietary_restrictions']) ? explode(',', $user['dietary_restrictions']) : [];
$current_food_prefs = array_map('trim', $current_food_prefs);
$current_dietary = array_map('trim', $current_dietary);

// Get active sidebar tab
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SmartPantry</title>
    <link rel="stylesheet" href="<?php echo ASSETS_PATH; ?>css/landing.css">
    <link rel="stylesheet" href="<?php echo ASSETS_PATH; ?>css/dashboard.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>

<!-- Navigation -->
<nav class="landing-nav">
    <div class="nav-container">
        <a href="<?php echo BASE_URL; ?>" class="nav-logo">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none">
                <rect width="24" height="24" rx="4" fill="#22c55e"/>
                <path d="M7 12l3 3 7-7" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span>SmartPantry</span>
        </a>
        <div class="nav-actions">
            <a href="<?php echo BASE_URL; ?>views/user/recipe-search.php" class="btn-nav-primary">Find Recipes</a>
            <a href="<?php echo BASE_URL; ?>views/user/dashboard.php" class="nav-user-icon active" title="<?php echo htmlspecialchars($_SESSION['username']); ?>">
                <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
            </a>
            <a href="<?php echo BASE_URL; ?>controllers/AuthController.php?action=logout" class="btn-nav-outline">Logout</a>
        </div>
    </div>
</nav>

<div class="db-wrapper">
    <!-- Sidebar -->
    <aside class="db-sidebar">
        <a href="?tab=dashboard" class="db-sidebar-item <?php echo $active_tab === 'dashboard' ? 'active' : ''; ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
            Dashboard
        </a>
        <a href="?tab=saved" class="db-sidebar-item <?php echo $active_tab === 'saved' ? 'active' : ''; ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
            Saved Recipes
        </a>
        <a href="?tab=history" class="db-sidebar-item <?php echo $active_tab === 'history' ? 'active' : ''; ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            History
        </a>

        <div class="db-sidebar-divider"></div>
        <span class="db-sidebar-label">SETTINGS</span>

        <a href="?tab=profile" class="db-sidebar-item <?php echo $active_tab === 'profile' ? 'active' : ''; ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            Profile
        </a>
        <a href="?tab=preferences" class="db-sidebar-item <?php echo $active_tab === 'preferences' ? 'active' : ''; ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
            Preferences
        </a>
    </aside>

    <!-- Main Content -->
    <main class="db-main">

        <?php if (isset($_SESSION['success'])): ?>
            <div class="db-alert db-alert-success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="db-alert db-alert-error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <?php if ($active_tab === 'dashboard'): ?>
        <!-- ======================== DASHBOARD TAB ======================== -->
        <div class="db-header">
            <div>
                <h1>Welcome back, <?php echo htmlspecialchars($user['username']); ?>! &#128075;</h1>
                <p class="db-subtitle">Ready to cook something healthy today?</p>
            </div>
            <a href="<?php echo BASE_URL; ?>views/user/recipe-search.php" class="db-cta-btn">+ Find New Recipe</a>
        </div>

        <!-- Stats Cards -->
        <div class="db-stats-grid">
            <div class="db-stat-card">
                <div class="db-stat-icon db-stat-blue">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                </div>
                <div>
                    <span class="db-stat-label">SAVED RECIPES</span>
                    <span class="db-stat-value"><?php echo $stats['favorites']; ?></span>
                </div>
            </div>
            <div class="db-stat-card">
                <div class="db-stat-icon db-stat-green">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                </div>
                <div>
                    <span class="db-stat-label">RECIPES RATED</span>
                    <span class="db-stat-value"><?php echo $stats['ratings']; ?></span>
                </div>
            </div>
            <div class="db-stat-card">
                <div class="db-stat-icon db-stat-orange">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </div>
                <div>
                    <span class="db-stat-label">RECIPES VIEWED</span>
                    <span class="db-stat-value"><?php echo $stats['views']; ?></span>
                </div>
            </div>
        </div>

        <!-- Quick Cook / Saved Recipes -->
        <?php if (!empty($favorites)): ?>
        <div class="db-section">
            <div class="db-section-header">
                <h2>Saved Recipes</h2>
                <a href="?tab=saved" class="db-view-all">View All</a>
            </div>
            <div class="db-recipe-grid">
                <?php foreach ($favorites as $fav): ?>
                    <a href="<?php echo BASE_URL; ?>views/user/recipe-detail.php?id=<?php echo $fav['id']; ?>" class="db-recipe-card">
                        <div class="db-recipe-img">
                            <img src="<?php echo ASSETS_PATH . 'images/' . ($fav['image_url'] ?: 'recipes/default.jpg'); ?>" 
                                 alt="<?php echo htmlspecialchars($fav['name']); ?>"
                                 onerror="this.src='<?php echo ASSETS_PATH; ?>images/recipes/default.jpg'">
                            <span class="db-recipe-cal"><?php echo number_format($fav['calories']); ?> kcal</span>
                            <?php if ($fav['prep_time']): ?>
                                <span class="db-recipe-time"><?php echo $fav['prep_time']; ?>m</span>
                            <?php endif; ?>
                        </div>
                        <div class="db-recipe-info">
                            <h4><?php echo htmlspecialchars($fav['name']); ?></h4>
                            <p><?php echo htmlspecialchars(substr($fav['description'] ?: 'Delicious recipe', 0, 50)); ?></p>
                            <div class="db-recipe-meta">
                                <?php echo displayStars($fav['average_rating']); ?>
                                <span class="db-recipe-bookmark">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="#22c55e" stroke="#22c55e" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
                                </span>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Recent History -->
        <?php if (!empty($recent_views)): ?>
        <div class="db-section">
            <div class="db-section-header">
                <h2>Recently Viewed</h2>
                <a href="?tab=history" class="db-view-all">View All</a>
            </div>
            <div class="db-recipe-grid">
                <?php foreach ($recent_views as $rv): ?>
                    <a href="<?php echo BASE_URL; ?>views/user/recipe-detail.php?id=<?php echo $rv['id']; ?>" class="db-recipe-card">
                        <div class="db-recipe-img">
                            <img src="<?php echo ASSETS_PATH . 'images/' . ($rv['image_url'] ?: 'recipes/default.jpg'); ?>" 
                                 alt="<?php echo htmlspecialchars($rv['name']); ?>"
                                 onerror="this.src='<?php echo ASSETS_PATH; ?>images/recipes/default.jpg'">
                            <span class="db-recipe-cal"><?php echo number_format($rv['calories']); ?> kcal</span>
                        </div>
                        <div class="db-recipe-info">
                            <h4><?php echo htmlspecialchars($rv['name']); ?></h4>
                            <p><?php echo htmlspecialchars(substr($rv['description'] ?: 'Delicious recipe', 0, 50)); ?></p>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Explore Cuisines -->
        <div class="db-section">
            <h2>Explore Cuisines</h2>
            <div class="db-cuisine-grid">
                <?php foreach (RECIPE_CATEGORIES as $cat): ?>
                    <a href="<?php echo BASE_URL; ?>views/user/recipe-search.php?ingredients=<?php echo urlencode($cat); ?>" class="db-cuisine-card">
                        <span><?php echo htmlspecialchars($cat); ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <?php elseif ($active_tab === 'saved'): ?>
        <!-- ======================== SAVED RECIPES TAB ======================== -->
        <h1 class="db-page-title">Saved Recipes</h1>
        <?php
        $all_favorites = $userModel->getFavorites($_SESSION['user_id'], 50);
        if (!empty($all_favorites)):
        ?>
        <div class="db-recipe-grid db-recipe-grid-full">
            <?php foreach ($all_favorites as $fav): ?>
                <a href="<?php echo BASE_URL; ?>views/user/recipe-detail.php?id=<?php echo $fav['id']; ?>" class="db-recipe-card">
                    <div class="db-recipe-img">
                        <img src="<?php echo ASSETS_PATH . 'images/' . ($fav['image_url'] ?: 'recipes/default.jpg'); ?>" 
                             alt="<?php echo htmlspecialchars($fav['name']); ?>"
                             onerror="this.src='<?php echo ASSETS_PATH; ?>images/recipes/default.jpg'">
                        <span class="db-recipe-cal"><?php echo number_format($fav['calories']); ?> kcal</span>
                    </div>
                    <div class="db-recipe-info">
                        <h4><?php echo htmlspecialchars($fav['name']); ?></h4>
                        <p><?php echo htmlspecialchars(substr($fav['description'] ?: 'Delicious recipe', 0, 60)); ?></p>
                        <div class="db-recipe-meta">
                            <?php echo displayStars($fav['average_rating']); ?>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
            <div class="db-empty">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#d1d5db" stroke-width="1.5"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                <h3>No saved recipes yet</h3>
                <p>Start exploring and save your favorite recipes!</p>
                <a href="<?php echo BASE_URL; ?>views/user/recipe-search.php" class="db-empty-btn">Browse Recipes</a>
            </div>
        <?php endif; ?>

        <?php elseif ($active_tab === 'history'): ?>
        <!-- ======================== HISTORY TAB ======================== -->
        <h1 class="db-page-title">Recently Viewed</h1>
        <?php
        $all_recent = $userModel->getRecentViews($_SESSION['user_id'], 50);
        if (!empty($all_recent)):
        ?>
        <div class="db-recipe-grid db-recipe-grid-full">
            <?php foreach ($all_recent as $rv): ?>
                <a href="<?php echo BASE_URL; ?>views/user/recipe-detail.php?id=<?php echo $rv['id']; ?>" class="db-recipe-card">
                    <div class="db-recipe-img">
                        <img src="<?php echo ASSETS_PATH . 'images/' . ($rv['image_url'] ?: 'recipes/default.jpg'); ?>" 
                             alt="<?php echo htmlspecialchars($rv['name']); ?>"
                             onerror="this.src='<?php echo ASSETS_PATH; ?>images/recipes/default.jpg'">
                        <span class="db-recipe-cal"><?php echo number_format($rv['calories']); ?> kcal</span>
                    </div>
                    <div class="db-recipe-info">
                        <h4><?php echo htmlspecialchars($rv['name']); ?></h4>
                        <p><?php echo htmlspecialchars(substr($rv['description'] ?: 'Delicious recipe', 0, 60)); ?></p>
                        <div class="db-recipe-meta">
                            <?php echo displayStars($rv['average_rating']); ?>
                            <span class="db-view-date"><?php echo date('M j', strtotime($rv['viewed_at'])); ?></span>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
            <div class="db-empty">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#d1d5db" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                <h3>No recent views</h3>
                <p>Start browsing recipes and your history will appear here.</p>
                <a href="<?php echo BASE_URL; ?>views/user/recipe-search.php" class="db-empty-btn">Browse Recipes</a>
            </div>
        <?php endif; ?>

        <?php elseif ($active_tab === 'profile'): ?>
        <!-- ======================== PROFILE TAB ======================== -->
        <h1 class="db-page-title">Profile Settings</h1>

        <div class="db-settings-grid">
            <!-- Change Username -->
            <div class="db-settings-card">
                <h3>Change Username</h3>
                <p class="db-settings-desc">Your current username is <strong><?php echo htmlspecialchars($user['username']); ?></strong></p>
                <form method="POST" action="<?php echo BASE_URL; ?>controllers/UserController.php" class="db-form">
                    <input type="hidden" name="action" value="update_username">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <div class="db-form-group">
                        <label>New Username</label>
                        <input type="text" name="new_username" required minlength="3" maxlength="50" placeholder="Enter new username" class="db-input">
                    </div>
                    <button type="submit" class="db-submit-btn">Update Username</button>
                </form>
            </div>

            <!-- Change Password -->
            <div class="db-settings-card">
                <h3>Change Password</h3>
                <p class="db-settings-desc">Use a strong password to keep your account secure.</p>
                <form method="POST" action="<?php echo BASE_URL; ?>controllers/UserController.php" class="db-form" id="passwordForm">
                    <input type="hidden" name="action" value="update_password">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="current_password" id="currentPasswordHash">
                    <input type="hidden" name="new_password" id="newPasswordHash">
                    <div class="db-form-group">
                        <label>Current Password</label>
                        <input type="password" id="currentPasswordInput" required minlength="6" placeholder="Enter current password" class="db-input">
                    </div>
                    <div class="db-form-group">
                        <label>New Password</label>
                        <input type="password" id="newPasswordInput" required minlength="6" placeholder="Enter new password" class="db-input">
                    </div>
                    <div class="db-form-group">
                        <label>Confirm New Password</label>
                        <input type="password" id="confirmPasswordInput" required minlength="6" placeholder="Confirm new password" class="db-input">
                    </div>
                    <button type="submit" class="db-submit-btn">Update Password</button>
                </form>
            </div>

            <!-- Account Info -->
            <div class="db-settings-card">
                <h3>Account Info</h3>
                <div class="db-info-row">
                    <span class="db-info-label">Email</span>
                    <span class="db-info-value"><?php echo htmlspecialchars($user['email']); ?></span>
                </div>
                <div class="db-info-row">
                    <span class="db-info-label">Member since</span>
                    <span class="db-info-value"><?php echo date('F j, Y', strtotime($user['created_at'])); ?></span>
                </div>
                <div class="db-info-row">
                    <span class="db-info-label">Recipes saved</span>
                    <span class="db-info-value"><?php echo $stats['favorites']; ?></span>
                </div>
                <div class="db-info-row">
                    <span class="db-info-label">Recipes rated</span>
                    <span class="db-info-value"><?php echo $stats['ratings']; ?></span>
                </div>
            </div>
        </div>

        <?php elseif ($active_tab === 'preferences'): ?>
        <!-- ======================== PREFERENCES TAB ======================== -->
        <h1 class="db-page-title">Dietary Preferences</h1>

        <form method="POST" action="<?php echo BASE_URL; ?>controllers/UserController.php" class="db-pref-form">
            <input type="hidden" name="action" value="update_preferences">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

            <!-- Food Preferences -->
            <div class="db-settings-card db-pref-card">
                <h3>Food Preferences</h3>
                <p class="db-settings-desc">Select your preferred cuisines. We'll prioritize recipes matching your taste.</p>
                <div class="db-checkbox-grid">
                    <?php foreach (FOOD_PREFERENCES as $pref): ?>
                        <label class="db-checkbox-item">
                            <input type="checkbox" name="food_preferences[]" value="<?php echo htmlspecialchars($pref); ?>"
                                <?php echo in_array($pref, $current_food_prefs) ? 'checked' : ''; ?>>
                            <span class="db-checkbox-label"><?php echo htmlspecialchars($pref); ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Dietary Restrictions -->
            <div class="db-settings-card db-pref-card">
                <h3>Dietary Restrictions &amp; Allergies</h3>
                <p class="db-settings-desc">Let us know your dietary needs so we can filter recipes accordingly.</p>
                <div class="db-checkbox-grid">
                    <?php foreach (DIETARY_RESTRICTIONS as $restriction): ?>
                        <label class="db-checkbox-item">
                            <input type="checkbox" name="dietary_restrictions[]" value="<?php echo htmlspecialchars($restriction); ?>"
                                <?php echo in_array($restriction, $current_dietary) ? 'checked' : ''; ?>>
                            <span class="db-checkbox-label"><?php echo htmlspecialchars($restriction); ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <button type="submit" class="db-submit-btn db-submit-lg">Save Preferences</button>
        </form>

        <?php endif; ?>
    </main>
</div>

<script src="<?php echo ASSETS_PATH; ?>js/bcrypt.min.js"></script>
<script>
// Password form: hash before submit
document.getElementById('passwordForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const current = document.getElementById('currentPasswordInput').value;
    const newPass = document.getElementById('newPasswordInput').value;
    const confirm = document.getElementById('confirmPasswordInput').value;

    if (newPass !== confirm) {
        alert('New passwords do not match!');
        return;
    }
    if (newPass.length < 6) {
        alert('Password must be at least 6 characters.');
        return;
    }

    // Hash passwords client-side (double-hash pattern)
    const salt = dcodeIO.bcrypt.genSaltSync(10);
    document.getElementById('currentPasswordHash').value = dcodeIO.bcrypt.hashSync(current, salt);
    document.getElementById('newPasswordHash').value = dcodeIO.bcrypt.hashSync(newPass, salt);
    this.submit();
});

// Auto-dismiss alerts
document.querySelectorAll('.db-alert').forEach(el => {
    setTimeout(() => el.style.opacity = '0', 3000);
    setTimeout(() => el.remove(), 3500);
});
</script>
</body>
</html>
