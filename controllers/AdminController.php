<?php
/**
 * Admin Controller
 * Handles admin authentication and CRUD operations for recipes and ingredients
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../models/Recipe.php';
require_once __DIR__ . '/../models/Ingredient.php';
require_once __DIR__ . '/../models/Feedback.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Rating.php';

class AdminController {
    private $conn;
    private $recipe;
    private $ingredient;
    private $feedback;
    private $user;
    private $rating;

    public function __construct() {
        $this->conn = getDB();
        $this->recipe = new Recipe();
        $this->ingredient = new Ingredient();
        $this->feedback = new Feedback();
        $this->user = new User();
        $this->rating = new Rating();
    }

    /**
     * Admin registration
     */
    public function register() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(BASE_URL . 'views/admin/register.php');
        }

        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
            $_SESSION['error'] = 'Invalid security token. Please try again.';
            redirect(BASE_URL . 'views/admin/register.php');
        }

        $username = isset($_POST['username']) ? trim($_POST['username']) : '';
        $password_hash = isset($_POST['password_hash']) ? $_POST['password_hash'] : '';

        if (empty($username) || empty($password_hash)) {
            $_SESSION['error'] = 'All required fields must be filled';
            redirect(BASE_URL . 'views/admin/register.php');
        }

        // Validate username format
        if (!validateUsername($username)) {
            $_SESSION['error'] = 'Username must be 3-20 characters (letters, numbers, underscore only)';
            redirect(BASE_URL . 'views/admin/register.php');
        }

        // Validate password hash (should be 64 chars for SHA-256)
        if (strlen($password_hash) !== 64 || !ctype_xdigit($password_hash)) {
            $_SESSION['error'] = 'Invalid password format. Please try again.';
            redirect(BASE_URL . 'views/admin/register.php');
        }

        // Sanitize after validation
        $username = sanitize($username);

        // Check if admin already exists
        try {
            $checkQuery = "SELECT id FROM admins WHERE username = :username LIMIT 1";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->bindParam(':username', $username);
            $checkStmt->execute();

            if ($checkStmt->rowCount() > 0) {
                $_SESSION['error'] = 'Admin account with this username already exists. Please login instead.';
                redirect(BASE_URL . 'views/admin/register.php');
            }
        } catch (PDOException $e) {
            error_log("Admin Check Error: " . $e->getMessage());
            $_SESSION['error'] = 'Database error occurred';
            redirect(BASE_URL . 'views/admin/register.php');
        }

        // Hash password again on server-side (double hashing)
        $server_hash = password_hash($password_hash, PASSWORD_BCRYPT);

        try {
            $query = "INSERT INTO admins (username, password_hash) VALUES (:username, :password_hash)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':password_hash', $server_hash);

            if ($stmt->execute()) {
                $_SESSION['success'] = 'Admin registration successful! You can now login.';
                redirect(BASE_URL . 'views/admin/login.php');
            } else {
                $_SESSION['error'] = 'Registration failed';
                redirect(BASE_URL . 'views/admin/register.php');
            }
        } catch (PDOException $e) {
            error_log("Admin Registration Error: " . $e->getMessage());
            $_SESSION['error'] = 'Database error occurred';
            redirect(BASE_URL . 'views/admin/register.php');
        }
    }

    /**
     * Admin login
     */
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(BASE_URL . 'views/admin/login.php');
        }

        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
            $_SESSION['error'] = 'Invalid security token. Please try again.';
            redirect(BASE_URL . 'views/admin/login.php');
        }

        $username = isset($_POST['username']) ? trim($_POST['username']) : '';
        $password_hash = isset($_POST['password_hash']) ? $_POST['password_hash'] : '';

        if (empty($username) || empty($password_hash)) {
            $_SESSION['error'] = 'Username and password are required';
            redirect(BASE_URL . 'views/admin/login.php');
        }

        // Validate password hash format
        if (strlen($password_hash) !== 64 || !ctype_xdigit($password_hash)) {
            $_SESSION['error'] = 'Invalid password format. Please try again.';
            redirect(BASE_URL . 'views/admin/login.php');
        }

        // Sanitize username
        $username = sanitize($username);

        try {
            $query = "SELECT id, username, password_hash FROM admins WHERE username = :username LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $admin = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Verify password (password_hash is SHA-256 from client, stored as Bcrypt on server)
                if (password_verify($password_hash, $admin['password_hash'])) {
                    // Allow both admin and user to be logged in simultaneously - don't clear user session
                    $_SESSION['admin_id'] = $admin['id'];
                    $_SESSION['admin_username'] = $admin['username'];
                    session_regenerate_id(true);
                    $_SESSION['success'] = 'Login successful';
                    redirect(BASE_URL . 'views/admin/dashboard.php');
                }
            }

            $_SESSION['error'] = 'Invalid username or password';
            redirect(BASE_URL . 'views/admin/login.php');
        } catch (PDOException $e) {
            error_log("Admin Login Error: " . $e->getMessage());
            $_SESSION['error'] = 'Database error occurred';
            redirect(BASE_URL . 'views/admin/login.php');
        }
    }

    /**
     * Admin logout
     */
    public function logout() {
        // Only clear admin session, keep user session if exists
        unset($_SESSION['admin_id']);
        unset($_SESSION['admin_username']);
        $_SESSION['success'] = 'Logged out successfully';
        redirect(BASE_URL . 'views/admin/login.php');
    }

    /**
     * Create recipe
     */
    public function createRecipe() {
        if (!isAdmin()) {
            $_SESSION['error'] = 'Admin access required';
            redirect(BASE_URL . 'views/admin/login.php');
        }

        // Validate required fields
        $name = trim($_POST['name'] ?? '');
        $instructions = trim($_POST['instructions'] ?? '');
        $prep_time = validateInteger($_POST['prep_time'] ?? 0, 1, 1000);
        $category = sanitize($_POST['category'] ?? '');

        if (empty($name) || empty($instructions) || $prep_time === false || empty($category)) {
            $_SESSION['error'] = 'Please fill all required fields correctly';
            redirect(BASE_URL . 'views/admin/recipes.php?action=add');
        }

        // Validate category
        if (!in_array($category, RECIPE_CATEGORIES)) {
            $_SESSION['error'] = 'Invalid recipe category';
            redirect(BASE_URL . 'views/admin/recipes.php?action=add');
        }

        $data = [
            'name' => sanitize($name),
            'description' => sanitize($_POST['description'] ?? ''),
            'instructions' => sanitize($instructions),
            'prep_time' => $prep_time,
            'image_url' => sanitize($_POST['image_url'] ?? ''),
            'category' => $category,
            'ingredients' => []
        ];

        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $image_path = uploadImage($_FILES['image'], 'recipes');
            if ($image_path) {
                $data['image_url'] = $image_path;
            }
        }

        // Process ingredients
        if (isset($_POST['ingredient_ids']) && is_array($_POST['ingredient_ids'])) {
            foreach ($_POST['ingredient_ids'] as $index => $ingredient_id) {
                if (isset($_POST['quantities'][$index])) {
                    $data['ingredients'][] = [
                        'ingredient_id' => intval($ingredient_id),
                        'quantity' => floatval($_POST['quantities'][$index])
                    ];
                }
            }
        }

        $result = $this->recipe->create($data);
        
        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
        } else {
            $_SESSION['error'] = $result['message'];
        }

        redirect(BASE_URL . 'views/admin/recipes.php');
    }

    /**
     * Update recipe
     */
    public function updateRecipe($recipe_id) {
        if (!isAdmin()) {
            $_SESSION['error'] = 'Admin access required';
            redirect(BASE_URL . 'views/admin/login.php');
        }

        $data = [
            'name' => sanitize($_POST['name'] ?? ''),
            'description' => sanitize($_POST['description'] ?? ''),
            'instructions' => sanitize($_POST['instructions'] ?? ''),
            'prep_time' => intval($_POST['prep_time'] ?? 0),
            'image_url' => sanitize($_POST['image_url'] ?? ''),
            'category' => sanitize($_POST['category'] ?? ''),
            'ingredients' => []
        ];

        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $old_recipe = $this->recipe->getById($recipe_id);
            if ($old_recipe && $old_recipe['image_url']) {
                deleteImage($old_recipe['image_url']);
            }
            
            $image_path = uploadImage($_FILES['image'], 'recipes');
            if ($image_path) {
                $data['image_url'] = $image_path;
            }
        }

        // Process ingredients
        if (isset($_POST['ingredient_ids']) && is_array($_POST['ingredient_ids'])) {
            foreach ($_POST['ingredient_ids'] as $index => $ingredient_id) {
                if (isset($_POST['quantities'][$index])) {
                    $data['ingredients'][] = [
                        'ingredient_id' => intval($ingredient_id),
                        'quantity' => floatval($_POST['quantities'][$index])
                    ];
                }
            }
        }

        if ($this->recipe->update($recipe_id, $data)) {
            $_SESSION['success'] = 'Recipe updated successfully';
        } else {
            $_SESSION['error'] = 'Failed to update recipe';
        }

        redirect(BASE_URL . 'views/admin/recipes.php');
    }

    /**
     * Delete recipe
     */
    public function deleteRecipe($recipe_id) {
        if (!isAdmin()) {
            $_SESSION['error'] = 'Admin access required';
            redirect(BASE_URL . 'views/admin/login.php');
        }

        $recipe = $this->recipe->getById($recipe_id);
        if ($recipe && $recipe['image_url']) {
            deleteImage($recipe['image_url']);
        }

        if ($this->recipe->delete($recipe_id)) {
            $_SESSION['success'] = 'Recipe deleted successfully';
        } else {
            $_SESSION['error'] = 'Failed to delete recipe';
        }

        redirect(BASE_URL . 'views/admin/recipes.php');
    }

    /**
     * Create ingredient
     */
    public function createIngredient() {
        if (!isAdmin()) {
            $_SESSION['error'] = 'Admin access required';
            redirect(BASE_URL . 'views/admin/login.php');
        }

        // Validate required fields
        $name = trim($_POST['name'] ?? '');
        $category = sanitize($_POST['category'] ?? '');
        $calories = validateFloat($_POST['calories_per_unit'] ?? 0, 0, 10000);
        $unit = sanitize($_POST['unit'] ?? 'gram');

        if (empty($name) || empty($category) || $calories === false || empty($unit)) {
            $_SESSION['error'] = 'Please fill all required fields correctly';
            redirect(BASE_URL . 'views/admin/ingredients.php?action=add');
        }

        // Validate category
        if (!in_array($category, INGREDIENT_CATEGORIES)) {
            $_SESSION['error'] = 'Invalid ingredient category';
            redirect(BASE_URL . 'views/admin/ingredients.php?action=add');
        }

        $data = [
            'name' => sanitize($name),
            'category' => $category,
            'calories_per_unit' => $calories,
            'unit' => $unit,
            'image_url' => sanitize($_POST['image_url'] ?? '')
        ];

        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $image_path = uploadImage($_FILES['image'], 'ingredients');
            if ($image_path) {
                $data['image_url'] = $image_path;
            }
        }

        $result = $this->ingredient->create($data);
        
        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
        } else {
            $_SESSION['error'] = $result['message'];
        }

        redirect(BASE_URL . 'views/admin/ingredients.php');
    }

    /**
     * Update ingredient
     */
    public function updateIngredient($ingredient_id) {
        if (!isAdmin()) {
            $_SESSION['error'] = 'Admin access required';
            redirect(BASE_URL . 'views/admin/login.php');
        }

        $data = [
            'name' => sanitize($_POST['name'] ?? ''),
            'category' => sanitize($_POST['category'] ?? ''),
            'calories_per_unit' => floatval($_POST['calories_per_unit'] ?? 0),
            'unit' => sanitize($_POST['unit'] ?? 'gram'),
            'image_url' => sanitize($_POST['image_url'] ?? '')
        ];

        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $old_ingredient = $this->ingredient->getById($ingredient_id);
            if ($old_ingredient && $old_ingredient['image_url']) {
                deleteImage($old_ingredient['image_url']);
            }
            
            $image_path = uploadImage($_FILES['image'], 'ingredients');
            if ($image_path) {
                $data['image_url'] = $image_path;
            }
        }

        if ($this->ingredient->update($ingredient_id, $data)) {
            $_SESSION['success'] = 'Ingredient updated successfully';
        } else {
            $_SESSION['error'] = 'Failed to update ingredient';
        }

        redirect(BASE_URL . 'views/admin/ingredients.php');
    }

    /**
     * Delete ingredient
     */
    public function deleteIngredient($ingredient_id) {
        if (!isAdmin()) {
            $_SESSION['error'] = 'Admin access required';
            redirect(BASE_URL . 'views/admin/login.php');
        }

        $ingredient = $this->ingredient->getById($ingredient_id);
        if ($ingredient && $ingredient['image_url']) {
            deleteImage($ingredient['image_url']);
        }

        if ($this->ingredient->delete($ingredient_id)) {
            $_SESSION['success'] = 'Ingredient deleted successfully';
        } else {
            $_SESSION['error'] = 'Failed to delete ingredient';
        }

        redirect(BASE_URL . 'views/admin/ingredients.php');
    }

    /**
     * Respond to feedback
     */
    public function respondToFeedback($feedback_id) {
        if (!isAdmin()) {
            $_SESSION['error'] = 'Admin access required';
            redirect(BASE_URL . 'views/admin/login.php');
        }

        $response = sanitize($_POST['admin_response'] ?? '');
        $status = sanitize($_POST['status'] ?? 'responded');

        if (empty($response)) {
            $_SESSION['error'] = 'Response cannot be empty';
            redirect(BASE_URL . 'views/admin/feedback.php');
        }

        if ($this->feedback->update($feedback_id, $response, $status)) {
            $_SESSION['success'] = 'Response saved successfully';
        } else {
            $_SESSION['error'] = 'Failed to save response';
        }

        redirect(BASE_URL . 'views/admin/feedback.php');
    }

    /**
     * Delete feedback
     */
    public function deleteFeedback($feedback_id) {
        if (!isAdmin()) {
            $_SESSION['error'] = 'Admin access required';
            redirect(BASE_URL . 'views/admin/login.php');
        }

        if ($this->feedback->delete($feedback_id)) {
            $_SESSION['success'] = 'Feedback deleted successfully';
        } else {
            $_SESSION['error'] = 'Failed to delete feedback';
        }

        redirect(BASE_URL . 'views/admin/feedback.php');
    }
}

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $controller = new AdminController();
    
    switch ($_POST['action']) {
        case 'admin_register':
            $controller->register();
            break;
        case 'admin_login':
            $controller->login();
            break;
        case 'create_recipe':
            $controller->createRecipe();
            break;
        case 'update_recipe':
            $recipe_id = isset($_POST['recipe_id']) ? intval($_POST['recipe_id']) : 0;
            if ($recipe_id > 0) {
                $controller->updateRecipe($recipe_id);
            }
            break;
        case 'create_ingredient':
            $controller->createIngredient();
            break;
        case 'update_ingredient':
            $ingredient_id = isset($_POST['ingredient_id']) ? intval($_POST['ingredient_id']) : 0;
            if ($ingredient_id > 0) {
                $controller->updateIngredient($ingredient_id);
            }
            break;
        case 'respond_feedback':
            $feedback_id = isset($_POST['feedback_id']) ? intval($_POST['feedback_id']) : 0;
            if ($feedback_id > 0) {
                $controller->respondToFeedback($feedback_id);
            }
            break;
        default:
            redirect(BASE_URL . 'views/admin/dashboard.php');
    }
}

// Handle GET requests (delete actions and logout)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    $controller = new AdminController();
    
    switch ($_GET['action']) {
        case 'delete_recipe':
            $recipe_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
            if ($recipe_id > 0) {
                $controller->deleteRecipe($recipe_id);
            }
            break;
        case 'delete_ingredient':
            $ingredient_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
            if ($ingredient_id > 0) {
                $controller->deleteIngredient($ingredient_id);
            }
            break;
        case 'delete_feedback':
            $feedback_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
            if ($feedback_id > 0) {
                $controller->deleteFeedback($feedback_id);
            }
            break;
        case 'logout':
            $controller->logout();
            break;
        default:
            redirect(BASE_URL . 'views/admin/dashboard.php');
    }
}

