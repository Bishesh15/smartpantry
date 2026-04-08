<?php
/**
 * API: search-ingredients.php
 * Returns JSON list of ingredients matching a search term.
 * Used by AJAX in pantry add-modal.
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../models/Ingredient.php';

$term = sanitize($_GET['term'] ?? '');

if (strlen($term) < 1) {
    echo json_encode([]);
    exit;
}

$model = new Ingredient();
$items = $model->search($term, 25);

echo json_encode($items);
