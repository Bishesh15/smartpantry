<?php
require_once __DIR__ . '/config/database.php';
$db = getDB();

echo "Checking calories...\n";

// 1. Check ingredients with 0 calories
$st = $db->query("SELECT COUNT(*) FROM ingredients WHERE calories_per_unit = 0 OR calories_per_unit IS NULL");
echo "Ingredients with 0 calories: " . $st->fetchColumn() . "\n";

// 2. Check recipes with 0 calories
$st = $db->query("SELECT COUNT(*) FROM recipes WHERE calories = 0 OR calories IS NULL");
echo "Recipes with 0 calories: " . $st->fetchColumn() . "\n";

// 3. Sample data check
$st = $db->query("SELECT name, calories_per_unit FROM ingredients LIMIT 5");
while($row = $st->fetch()) {
    echo " - " . $row['name'] . ": " . $row['calories_per_unit'] . "\n";
}
