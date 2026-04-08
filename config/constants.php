<?php
/**
 * Application Constants
 * Smart Pantry – A Recipe Recommendation System
 */

// Base URL — adjust if you rename the folder
define('BASE_URL', 'http://localhost/smartpantry/');

// Paths
define('ROOT_PATH',        dirname(__DIR__) . '/');
define('VIEWS_PATH',       ROOT_PATH . 'views/');
define('MODELS_PATH',      ROOT_PATH . 'models/');
define('CONTROLLERS_PATH', ROOT_PATH . 'controllers/');
define('ASSETS_PATH',      BASE_URL  . 'assets/');
define('UPLOADS_PATH',     ROOT_PATH . 'assets/images/uploads/');
define('UPLOADS_URL',      BASE_URL  . 'assets/images/uploads/');

// Session
define('SESSION_LIFETIME', 7200);           // 2 hours
define('SESSION_NAME',     'SMARTPANTRY');

// Pagination
define('RECIPES_PER_PAGE',   12);
define('USERS_PER_PAGE',     20);

// File upload
define('MAX_FILE_SIZE',       5242880);     // 5 MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg','image/png','image/gif','image/webp']);

// Diet types
define('DIET_TYPES', ['Vegetarian', 'Vegan', 'Non-Vegetarian']);

// Recipe cuisine categories
define('RECIPE_CATEGORIES', [
    'Nepali', 'Indian', 'Italian', 'Chinese',
    'Continental', 'Mexican', 'Thai', 'Other'
]);

// Ingredient categories
define('INGREDIENT_CATEGORIES', [
    'Vegetables', 'Fruits', 'Proteins', 'Grains',
    'Legumes', 'Dairy', 'Spices', 'Oils', 'Extras'
]);

// Sort options for recipe search
define('SORT_OPTIONS', [
    'match'    => 'Best Match',
    'calories' => 'Calories (Low-High)',
    'time'     => 'Prep Time (Fast)',
    'rating'   => 'Highest Rated',
]);

// Rating
define('MIN_RATING', 1);
define('MAX_RATING', 5);

// Google OAuth Credentials
define('GOOGLE_CLIENT_ID', 'YOUR_GOOGLE_CLIENT_ID_HERE');
define('GOOGLE_CLIENT_SECRET', 'YOUR_GOOGLE_CLIENT_SECRET_HERE');
