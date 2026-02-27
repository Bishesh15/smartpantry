<?php
/**
 * Recipe Controller
 * Handles recipe display, search, matching, and detail views
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../models/Recipe.php';
require_once __DIR__ . '/../models/Ingredient.php';
require_once __DIR__ . '/../models/Rating.php';

class RecipeController {
    private $recipe;
    private $ingredient;
    private $rating;

    public function __construct() {
        $this->recipe = new Recipe();
        $this->ingredient = new Ingredient();
        $this->rating = new Rating();
    }

    /**
     * Handle recipe matching based on selected ingredients
     */
    public function matchRecipes() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(BASE_URL . 'views/user/recipe-search.php');
        }

        $ingredient_ids = isset($_POST['ingredients']) && is_array($_POST['ingredients']) 
            ? $_POST['ingredients'] : [];
        
        // Validate ingredient IDs
        $valid_ids = [];
        foreach ($ingredient_ids as $id) {
            $valid_id = validateInteger($id, 1);
            if ($valid_id !== false) {
                $valid_ids[] = $valid_id;
            }
        }
        
        if (empty($valid_ids)) {
            $_SESSION['error'] = 'Please select at least one valid ingredient';
            redirect(BASE_URL . 'views/user/recipe-search.php');
        }
        
        $ingredient_ids = $valid_ids;

        // Get user preferences if logged in
        $user_preferences = [];
        $dietary_restrictions = [];
        
        if (isLoggedIn()) {
            $user_preferences = getFoodPreferences($_SESSION['food_preferences'] ?? '');
            $dietary_restrictions = getDietaryRestrictions($_SESSION['dietary_restrictions'] ?? '');
        }

        // Get matching recipes
        $recipes = $this->recipe->getMatchingRecipes($ingredient_ids, $user_preferences, $dietary_restrictions);
        
        $_SESSION['matched_recipes'] = $recipes;
        $_SESSION['selected_ingredients'] = $ingredient_ids;
        
        redirect(BASE_URL . 'views/user/recipe-search.php');
    }

    /**
     * Handle recipe search
     */
    public function search() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(BASE_URL . 'views/user/recipe-search.php');
        }

        $search_term = isset($_POST['search']) ? sanitize($_POST['search']) : '';
        
        if (empty($search_term)) {
            $_SESSION['error'] = 'Please enter a search term';
            redirect(BASE_URL . 'views/user/recipe-search.php');
        }

        $recipes = $this->recipe->search($search_term);
        
        $_SESSION['search_results'] = $recipes;
        $_SESSION['search_term'] = $search_term;
        
        redirect(BASE_URL . 'views/user/recipe-search.php');
    }

    /**
     * Handle recipe detail view
     */
    public function viewDetail($recipe_id) {
        // Validate recipe ID
        $recipe_id = validateInteger($recipe_id, 1);
        if ($recipe_id === false) {
            $_SESSION['error'] = 'Invalid recipe ID';
            redirect(BASE_URL . 'views/user/recipe-search.php');
        }
        
        $recipe = $this->recipe->getById($recipe_id);
        
        if (!$recipe) {
            $_SESSION['error'] = 'Recipe not found';
            redirect(BASE_URL . 'views/user/recipe-search.php');
        }

        // Get recipe ingredients
        $ingredients = $this->recipe->getIngredients($recipe_id);
        $recipe['ingredients'] = $ingredients;

        // Get ratings
        $ratings = $this->rating->getRecipeRatings($recipe_id);
        $recipe['ratings'] = $ratings;

        // Get user's rating if logged in
        $user_rating = null;
        if (isLoggedIn()) {
            $user_rating = $this->rating->getUserRating($_SESSION['user_id'], $recipe_id);
        }

        // Track recent view if logged in
        if (isLoggedIn()) {
            $this->trackRecentView($_SESSION['user_id'], $recipe_id);
        }

        return $recipe;
    }

    /**
     * Track recent recipe view
     * @param int $user_id
     * @param int $recipe_id
     */
    private function trackRecentView($user_id, $recipe_id) {
        try {
            $conn = getDB();
            
            // Check if view already exists today
            $query = "SELECT id FROM recent_views 
                      WHERE user_id = :user_id AND recipe_id = :recipe_id 
                      AND DATE(viewed_at) = CURDATE() 
                      LIMIT 1";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':recipe_id', $recipe_id, PDO::PARAM_INT);
            $stmt->execute();
            
            if ($stmt->rowCount() == 0) {
                // Insert new view
                $query = "INSERT INTO recent_views (user_id, recipe_id) 
                          VALUES (:user_id, :recipe_id)";
                $stmt = $conn->prepare($query);
                $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                $stmt->bindParam(':recipe_id', $recipe_id, PDO::PARAM_INT);
                $stmt->execute();
            } else {
                // Update existing view timestamp
                $query = "UPDATE recent_views 
                          SET viewed_at = CURRENT_TIMESTAMP 
                          WHERE user_id = :user_id AND recipe_id = :recipe_id 
                          AND DATE(viewed_at) = CURDATE()";
                $stmt = $conn->prepare($query);
                $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                $stmt->bindParam(':recipe_id', $recipe_id, PDO::PARAM_INT);
                $stmt->execute();
            }
        } catch (PDOException $e) {
            error_log("Track Recent View Error: " . $e->getMessage());
        }
    }

    /**
     * Get recently viewed recipes for user
     * @param int $user_id
     * @param int $limit
     * @return array
     */
    public function getRecentViews($user_id, $limit = 5) {
        try {
            $conn = getDB();
            $query = "SELECT r.*, rv.viewed_at 
                      FROM recipes r
                      INNER JOIN recent_views rv ON r.id = rv.recipe_id
                      WHERE rv.user_id = :user_id
                      ORDER BY rv.viewed_at DESC
                      LIMIT :limit";
            
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get Recent Views Error: " . $e->getMessage());
            return [];
        }
    }
}

// Handle requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $controller = new RecipeController();
    
    switch ($_POST['action']) {
        case 'match':
            $controller->matchRecipes();
            break;
        case 'search':
            $controller->search();
            break;
        default:
            redirect(BASE_URL . 'views/user/recipe-search.php');
    }
}

