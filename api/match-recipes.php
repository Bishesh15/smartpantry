<?php
/**
 * API: match-recipes.php
 * AJAX endpoint: POST array of ingredient IDs → returns JSON of scored recipes.
 *
 * POST params:
 *   ingredients[]  — ingredient IDs (int)
 *   diet_type      — optional filter: Vegetarian | Vegan | Non-Vegetarian
 *   sort_by        — match | calories | time | rating
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../models/Recipe.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$rawIds = $_POST['ingredients'] ?? [];
if (!is_array($rawIds)) {
    // Support JSON body too
    $body   = json_decode(file_get_contents('php://input'), true);
    $rawIds = $body['ingredients'] ?? [];
}

$ids        = array_values(array_filter(array_map('intval', $rawIds), fn($v) => $v > 0));
$dietFilter = sanitize($_POST['diet_type'] ?? '');
$sortBy     = sanitize($_POST['sort_by']   ?? 'match');

if (empty($ids)) {
    echo json_encode([
        'total'   => 0,
        'recipes' => [],
        'message' => 'No ingredients selected.'
    ]);
    exit;
}

$prefs = isLoggedIn() ? getFoodPreferences($_SESSION['food_preferences'] ?? '') : [];

$recipeModel = new Recipe();
$results     = $recipeModel->getMatchingRecipes($ids, $prefs, $dietFilter, $sortBy);

// Slim down fields for JSON response
$slim = array_map(function($r) {
    return [
        'id'                   => $r['id'],
        'name'                 => $r['name'],
        'description'          => $r['description'],
        'image_url'            => $r['image_url'],
        'diet_type'            => $r['diet_type'],
        'category'             => $r['category'],
        'prep_time'            => $r['prep_time'],
        'servings'             => $r['servings'],
        'calories'             => $r['calories'],
        'average_rating'       => $r['average_rating'],
        'total_ratings'        => $r['total_ratings'],
        'match_percentage'     => $r['match_percentage'],
        'hybrid_score'         => $r['hybrid_score'],
        'matched_ingredients'  => $r['matched_ingredients'],
        'missing_ingredients'  => $r['missing_ingredients'],
    ];
}, $results);

echo json_encode([
    'total'   => count($slim),
    'recipes' => $slim,
    'message' => count($slim) === 0
        ? 'No recipes match the selected ingredients.'
        : null,
]);
