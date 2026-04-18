<?php
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../models/Recipe.php';
require_once __DIR__ . '/../models/User.php';

$recipeModel = new Recipe();
$userModel = new User();

echo "Category Stats:\n";
print_r($recipeModel->getCategoryStats());

echo "\nDiet Type Stats:\n";
print_r($recipeModel->getDietTypeStats());

echo "\nUser Growth Stats:\n";
print_r($userModel->getRegistrationStats(7));
