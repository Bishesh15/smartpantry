<?php
/**
 * Application Constants
 * Defines application-wide constants
 */

// Base URL
define('BASE_URL', 'http://localhost/smartpantry/');

// Paths
define('ROOT_PATH', dirname(__DIR__) . '/');
define('VIEWS_PATH', ROOT_PATH . 'views/');
define('MODELS_PATH', ROOT_PATH . 'models/');
define('CONTROLLERS_PATH', ROOT_PATH . 'controllers/');
define('ASSETS_PATH', BASE_URL . 'assets/');
define('IMAGES_PATH', ROOT_PATH . 'assets/images/');

// Session settings
define('SESSION_LIFETIME', 3600); // 1 hour
define('SESSION_NAME', 'SMARTPANTRY_SESSION');

// Pagination
define('RECIPES_PER_PAGE', 12);
define('FEEDBACK_PER_PAGE', 10);

// File upload settings
define('MAX_FILE_SIZE', 5242880); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);

// Recipe categories
define('RECIPE_CATEGORIES', [
    'Nepali',
    'Indian',
    'Continental',
    'Chinese',
    'Italian',
    'Mexican',
    'Thai',
    'Other'
]);

// Ingredient categories
define('INGREDIENT_CATEGORIES', [
    'Vegetables',
    'Fruits',
    'Proteins',
    'Grains',
    'Legumes',
    'Dairy',
    'Spices',
    'Oils',
    'Herbs',
    'Other'
]);

// Food preferences
define('FOOD_PREFERENCES', [
    'Nepali',
    'Indian',
    'Continental',
    'Chinese',
    'Italian',
    'Mexican',
    'Thai',
    'Mixed'
]);

// Dietary restrictions
define('DIETARY_RESTRICTIONS', [
    'Vegetarian',
    'Vegan',
    'Gluten-Free',
    'Dairy-Free',
    'Nut-Free',
    'None'
]);

// Rating scale
define('MIN_RATING', 1);
define('MAX_RATING', 5);

// Google OAuth Settings
// Load credentials from secrets.php (not committed to git)
$secretsFile = __DIR__ . '/secrets.php';
if (file_exists($secretsFile)) {
    require_once $secretsFile;
}
if (!defined('GOOGLE_CLIENT_ID')) {
    define('GOOGLE_CLIENT_ID', '');
}
if (!defined('GOOGLE_CLIENT_SECRET')) {
    define('GOOGLE_CLIENT_SECRET', '');
}
define('GOOGLE_REDIRECT_URI', BASE_URL . 'controllers/GoogleAuthController.php');

