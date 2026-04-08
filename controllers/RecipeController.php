<?php
/**
 * RecipeController — Search, Match (SuperCook-style), Detail view
 * Smart Pantry
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../models/Recipe.php';
require_once __DIR__ . '/../models/Ingredient.php';
require_once __DIR__ . '/../models/User.php';

class RecipeController {
    private Recipe     $recipe;
    private Ingredient $ingredient;

    public function __construct() {
        $this->recipe     = new Recipe();
        $this->ingredient = new Ingredient();
    }

    /**
     * Match recipes from selected ingredient IDs.
     * Called by the main search form (POST) and by AJAX (api/match-recipes.php).
     */
    public function matchFromIngredients(
        array  $ingredientIds,
        string $dietFilter = '',
        string $sortBy     = 'match'
    ): array {
        if (empty($ingredientIds)) return [];

        $prefs = isLoggedIn()
            ? getFoodPreferences($_SESSION['food_preferences'] ?? '')
            : [];

        return $this->recipe->getMatchingRecipes($ingredientIds, $prefs, $dietFilter, $sortBy);
    }

    /**
     * Text search — tries ingredient resolution first, falls back to name/description search.
     */
    public function searchByText(string $term): array {
        $term = trim($term);
        if (empty($term)) return [];

        // Try to resolve as ingredient name(s)
        $ids = $this->ingredient->resolveNamesToIds($term);
        if (!empty($ids)) {
            $prefs = isLoggedIn() ? getFoodPreferences($_SESSION['food_preferences'] ?? '') : [];
            return $this->recipe->getMatchingRecipes($ids, $prefs);
        }
        // Generic text search
        return $this->recipe->search($term);
    }

    /**
     * Load a recipe detail page — increments view counter, records user view.
     */
    public function viewDetail(int $id): ?array {
        if ($id <= 0) return null;
        $recipe = $this->recipe->getById($id);
        if (!$recipe) return null;

        // Attach ingredients list
        $recipe['ingredients'] = $this->recipe->getIngredients($id);

        // Attach similar recipes
        $recipe['similar'] = $this->recipe->getSimilarRecipes($id, 4);

        // Increment global view count
        $this->recipe->incrementViewCount($id);

        // Record user view
        if (isLoggedIn()) {
            $userModel = new User();
            $userModel->recordView($_SESSION['user_id'], $id);

            // Mark matched / missing vs user pantry
            $pantryIds = $userModel->getPantryIngredientIds($_SESSION['user_id']);
            foreach ($recipe['ingredients'] as &$ing) {
                $ing['in_pantry'] = in_array((int)$ing['id'], array_map('intval', $pantryIds));
            }
            unset($ing);
        }

        return $recipe;
    }
}

/* ── Route (for direct form POST) ───────────────────────────── */
$ctrl = new RecipeController();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'match') {
        $ids        = array_map('intval', (array)($_POST['ingredients'] ?? []));
        $ids        = array_filter($ids, fn($id) => $id > 0);
        $dietFilter = sanitize($_POST['diet_type'] ?? '');
        $sortBy     = sanitize($_POST['sort_by']   ?? 'match');

        if (empty($ids)) {
            flashError('Please select at least one ingredient.');
            redirect(BASE_URL . 'views/user/recipe-search.php');
        }
        $results = $ctrl->matchFromIngredients(array_values($ids), $dietFilter, $sortBy);

        $_SESSION['match_results']        = $results;
        $_SESSION['match_ingredient_ids'] = $ids;
        $_SESSION['match_diet_filter']    = $dietFilter;
        $_SESSION['match_sort_by']        = $sortBy;

        redirect(BASE_URL . 'views/user/recipe-search.php');
    }

    if ($_POST['action'] === 'search') {
        $term    = sanitize($_POST['search_term'] ?? '');
        $results = $ctrl->searchByText($term);

        $_SESSION['search_results'] = $results;
        $_SESSION['search_term']    = $term;

        redirect(BASE_URL . 'views/user/recipe-search.php');
    }
}

// Guard: Redirect direct GET access back to home
if ($_SERVER['REQUEST_METHOD'] === 'GET' && basename($_SERVER['PHP_SELF']) === 'RecipeController.php') {
    redirect(BASE_URL . 'views/user/home.php');
}
