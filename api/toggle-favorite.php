<?php
/**
 * API: toggle-favorite.php
 * AJAX endpoint: POST recipe_id → toggle favorite on/off
 * Returns JSON {favorited: bool, message: string}
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../models/User.php';

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Login required.']);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed.']);
    exit;
}

$recipeId = (int)($_POST['recipe_id'] ?? 0);
if ($recipeId <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid recipe ID.']);
    exit;
}

$userModel  = new User();
$userId     = (int)$_SESSION['user_id'];
$isFav      = $userModel->isFavorited($userId, $recipeId);

if ($isFav) {
    $userModel->removeFavorite($userId, $recipeId);
    echo json_encode(['favorited' => false, 'message' => 'Removed from favorites.']);
} else {
    $userModel->addFavorite($userId, $recipeId);
    echo json_encode(['favorited' => true, 'message' => 'Saved to favorites!']);
}
