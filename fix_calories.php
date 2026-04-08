<?php
require_once __DIR__ . '/config/database.php';
$db = getDB();

echo "Seeding calories for ingredients...\n";

$ingCals = [
    'Rice' => 1.3,      // 130 kcal / 100g
    'Dal' => 1.1,       // 110 kcal / 100g
    'Chicken' => 2.3,   // 239 kcal / 100g
    'Paneer' => 2.6,    // 265 kcal / 100g
    'Butter' => 7.2,    // 717 kcal / 100g
    'Cream' => 1.9,     // 195 kcal / 100g
    'Egg' => 1.5,       // 155 kcal / 100g
    'Oil' => 8.8,       // 884 kcal / 100g
    'Potatoes' => 0.7,  // 77 kcal / 100g
    'Onion' => 0.4,
    'Tomato' => 0.18,
    'Garlic' => 1.5,
    'Ginger' => 0.8,
    'Flour' => 3.6,
    'Pasta' => 1.5,
    'Milk' => 0.6,
    'Sugar' => 3.8,
    'Vegetable' => 0.4
];

foreach ($ingCals as $name => $cals) {
    $st = $db->prepare("UPDATE ingredients SET calories_per_unit = ? WHERE name LIKE ?");
    $st->execute([$cals, "%$name%"]);
}

// Fixed any with 0 left
$db->query("UPDATE ingredients SET calories_per_unit = 0.5 WHERE calories_per_unit = 0 OR calories_per_unit IS NULL");

echo "Ingredients updated. Recalculating recipe calories...\n";

// Recalculate all recipes
$recipes = $db->query("SELECT id FROM recipes")->fetchAll();
foreach ($recipes as $r) {
    $recipeId = $r['id'];
    $db->prepare("
        UPDATE recipes r
        SET calories = (
            SELECT COALESCE(SUM(ri.quantity * i.calories_per_unit), 0)
            FROM recipe_ingredients ri
            JOIN ingredients i ON ri.ingredient_id = i.id
            WHERE ri.recipe_id = ?
        )
        WHERE id = ?
    ")->execute([$recipeId, $recipeId]);
}

echo "Done! All recipes updated.\n";
