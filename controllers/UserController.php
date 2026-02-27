<?php
/**
 * User Controller
 * Handles user preferences, favorites, and ratings
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../models/Rating.php';

class UserController {
    private $rating;

    public function __construct() {
        $this->rating = new Rating();
    }

    /**
     * Add or update recipe rating
     */
    public function addRating() {
        if (!isLoggedIn()) {
            $_SESSION['error'] = 'Please login to rate recipes';
            redirect(BASE_URL . 'views/user/login.php');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(BASE_URL . 'views/user/home.php');
        }

        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
            $_SESSION['error'] = 'Invalid security token';
            redirect(BASE_URL . 'views/user/home.php');
        }

        $recipe_id = validateInteger($_POST['recipe_id'] ?? 0, 1);
        $rating = validateInteger($_POST['rating'] ?? 0, MIN_RATING, MAX_RATING);
        $comment = sanitize($_POST['comment'] ?? '');

        // Validate comment length
        if (strlen($comment) > 1000) {
            $_SESSION['error'] = 'Comment must be less than 1000 characters';
            redirect(BASE_URL . 'views/user/recipe-detail.php?id=' . ($recipe_id ?: ''));
        }

        if ($recipe_id === false || $rating === false) {
            $_SESSION['error'] = 'Invalid rating data. Please provide valid recipe ID and rating (1-5)';
            redirect(BASE_URL . 'views/user/recipe-detail.php?id=' . ($recipe_id ?: ''));
        }

        $result = $this->rating->addRating($_SESSION['user_id'], $recipe_id, $rating, $comment);

        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
        } else {
            $_SESSION['error'] = $result['message'];
        }

        redirect(BASE_URL . 'views/user/recipe-detail.php?id=' . $recipe_id);
    }

    /**
     * Add recipe to favorites
     */
    public function addFavorite() {
        if (!isLoggedIn()) {
            $_SESSION['error'] = 'Please login to save favorites';
            redirect(BASE_URL . 'views/user/login.php');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(BASE_URL . 'views/user/home.php');
        }

        $recipe_id = validateInteger($_POST['recipe_id'] ?? 0, 1);

        if ($recipe_id === false) {
            $_SESSION['error'] = 'Invalid recipe ID';
            redirect(BASE_URL . 'views/user/home.php');
        }

        try {
            $conn = getDB();
            
            // Check if already favorited
            $query = "SELECT id FROM favorites 
                      WHERE user_id = :user_id AND recipe_id = :recipe_id LIMIT 1";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
            $stmt->bindParam(':recipe_id', $recipe_id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $_SESSION['info'] = 'Recipe already in favorites';
            } else {
                $query = "INSERT INTO favorites (user_id, recipe_id) 
                          VALUES (:user_id, :recipe_id)";
                $stmt = $conn->prepare($query);
                $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
                $stmt->bindParam(':recipe_id', $recipe_id, PDO::PARAM_INT);
                $stmt->execute();
                $_SESSION['success'] = 'Recipe added to favorites';
            }
        } catch (PDOException $e) {
            error_log("Add Favorite Error: " . $e->getMessage());
            $_SESSION['error'] = 'Failed to add to favorites';
        }

        $redirect_url = isset($_POST['redirect']) ? $_POST['redirect'] : BASE_URL . 'views/user/recipe-detail.php?id=' . $recipe_id;
        redirect($redirect_url);
    }

    /**
     * Remove recipe from favorites
     */
    public function removeFavorite() {
        if (!isLoggedIn()) {
            redirect(BASE_URL . 'views/user/login.php');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(BASE_URL . 'views/user/home.php');
        }

        $recipe_id = validateInteger($_POST['recipe_id'] ?? 0, 1);

        if ($recipe_id === false) {
            $_SESSION['error'] = 'Invalid recipe ID';
            redirect(BASE_URL . 'views/user/home.php');
        }

        try {
            $conn = getDB();
            $query = "DELETE FROM favorites 
                      WHERE user_id = :user_id AND recipe_id = :recipe_id";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
            $stmt->bindParam(':recipe_id', $recipe_id, PDO::PARAM_INT);
            $stmt->execute();
            $_SESSION['success'] = 'Recipe removed from favorites';
        } catch (PDOException $e) {
            error_log("Remove Favorite Error: " . $e->getMessage());
            $_SESSION['error'] = 'Failed to remove from favorites';
        }

        $redirect_url = isset($_POST['redirect']) ? $_POST['redirect'] : BASE_URL . 'views/user/recipe-detail.php?id=' . $recipe_id;
        redirect($redirect_url);
    }

    /**
     * Check if recipe is favorited by user
     * @param int $user_id
     * @param int $recipe_id
     * @return bool
     */
    public function isFavorited($user_id, $recipe_id) {
        try {
            $conn = getDB();
            $query = "SELECT id FROM favorites 
                      WHERE user_id = :user_id AND recipe_id = :recipe_id LIMIT 1";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':recipe_id', $recipe_id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Check Favorite Error: " . $e->getMessage());
            return false;
        }
    }
}

// Handle requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $controller = new UserController();
    
    switch ($_POST['action']) {
        case 'add_rating':
            $controller->addRating();
            break;
        case 'add_favorite':
            $controller->addFavorite();
            break;
        case 'remove_favorite':
            $controller->removeFavorite();
            break;
        default:
            redirect(BASE_URL . 'views/user/home.php');
    }
}

