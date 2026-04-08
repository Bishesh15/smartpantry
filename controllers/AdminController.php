<?php
/**
 * AdminController — Admin auth + full CRUD for recipes, ingredients, users, feedback
 * Smart Pantry
 *
 * Admin login is COMPLETELY SEPARATE from user login.
 * No Google OAuth. No email whitelist. Simple bcrypt.
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../models/Recipe.php';
require_once __DIR__ . '/../models/Ingredient.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Feedback.php';

class AdminController {
    private ?PDO       $db;
    private Recipe     $recipe;
    private Ingredient $ingredient;
    private User       $user;
    private Feedback   $feedback;

    public function __construct() {
        $this->db         = getDB();
        $this->recipe     = new Recipe();
        $this->ingredient = new Ingredient();
        $this->user       = new User();
        $this->feedback   = new Feedback();
    }

    /* ── Auth ───────────────────────────────────────────────── */

    public function login(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(BASE_URL . 'views/admin/login.php');
        }

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            flashError('Username and password are required.');
            redirect(BASE_URL . 'views/admin/login.php');
        }

        // Guard: DB connection check
        if (!$this->db) {
            flashError('Database connection failed. Check config/database.php.');
            redirect(BASE_URL . 'views/admin/login.php');
        }

        $st = $this->db->prepare("SELECT * FROM admins WHERE username = ? LIMIT 1");
        $st->execute([$username]);
        $admin = $st->fetch();

        if (!$admin) {
            flashError('No admin account found with that username.');
            redirect(BASE_URL . 'views/admin/login.php');
        }

        if (!password_verify($password, $admin['password'])) {
            flashError('Incorrect password. Please try again.');
            redirect(BASE_URL . 'views/admin/login.php');
        }

        // All good — start session
        $_SESSION['admin_id']       = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        session_regenerate_id(true);
        redirect(BASE_URL . 'views/admin/dashboard.php');
    }

    public function logout(): void {
        unset($_SESSION['admin_id'], $_SESSION['admin_username']);
        flashSuccess('Admin logged out.');
        redirect(BASE_URL . 'views/admin/login.php');
    }

    /* ── Recipes ────────────────────────────────────────────── */

    public function createRecipe(): void {
        requireAdmin();
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            flashError('Security token mismatch.'); redirect(BASE_URL . 'views/admin/recipes.php');
        }

        $imageUrl = $this->handleImageUpload('recipe');
        if (!$imageUrl) $imageUrl = sanitize($_POST['image_url'] ?? '');   // fallback to URL field

        $ingredients = $this->parseIngredientRows();

        $result = $this->recipe->create([
            'name'         => $_POST['name']         ?? '',
            'description'  => $_POST['description']  ?? '',
            'instructions' => $_POST['instructions'] ?? '',
            'prep_time'    => $_POST['prep_time']    ?? 30,
            'servings'     => $_POST['servings']     ?? 2,
            'diet_type'    => $_POST['diet_type']    ?? 'Vegetarian',
            'category'     => $_POST['category']     ?? 'Other',
            'image_url'    => $imageUrl,
            'ingredients'  => $ingredients,
        ]);
        $result['success'] ? flashSuccess($result['message']) : flashError($result['message']);
        redirect(BASE_URL . 'views/admin/recipes.php');
    }

    public function updateRecipe(int $id): void {
        requireAdmin();
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            flashError('Security token mismatch.'); redirect(BASE_URL . 'views/admin/recipes.php');
        }

        $existing = $this->recipe->getById($id);
        $imageUrl = $this->handleImageUpload('recipe');
        if (!$imageUrl) {
            // Check if URL field provided
            $urlField = sanitize($_POST['image_url'] ?? '');
            $imageUrl = $urlField ?: ($existing['image_url'] ?? '');
        } elseif ($existing && $existing['image_url'] && !str_starts_with($existing['image_url'], 'http')) {
            deleteImage($existing['image_url']);
        }

        $result = $this->recipe->update($id, [
            'name'         => $_POST['name']         ?? '',
            'description'  => $_POST['description']  ?? '',
            'instructions' => $_POST['instructions'] ?? '',
            'prep_time'    => $_POST['prep_time']    ?? 30,
            'servings'     => $_POST['servings']     ?? 2,
            'diet_type'    => $_POST['diet_type']    ?? 'Vegetarian',
            'category'     => $_POST['category']     ?? 'Other',
            'image_url'    => $imageUrl,
            'ingredients'  => $this->parseIngredientRows(),
        ]);
        $result['success'] ? flashSuccess($result['message']) : flashError($result['message']);
        redirect(BASE_URL . 'views/admin/recipes.php');
    }

    public function deleteRecipe(int $id): void {
        requireAdmin();
        $r = $this->recipe->getById($id);
        if ($r && $r['image_url'] && !str_starts_with($r['image_url'], 'http')) {
            deleteImage($r['image_url']);
        }
        $this->recipe->delete($id)
            ? flashSuccess('Recipe deleted.')
            : flashError('Could not delete recipe.');
        redirect(BASE_URL . 'views/admin/recipes.php');
    }

    /* ── Ingredients ────────────────────────────────────────── */

    public function createIngredient(): void {
        requireAdmin();
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            flashError('Security token mismatch.'); redirect(BASE_URL . 'views/admin/ingredients.php');
        }

        $imageUrl = $this->handleImageUpload('ingredient');
        if (!$imageUrl) $imageUrl = sanitize($_POST['image_url'] ?? '');

        $result = $this->ingredient->create([
            'name'              => $_POST['name']              ?? '',
            'category'          => $_POST['category']          ?? '',
            'calories_per_unit' => $_POST['calories_per_unit'] ?? 0,
            'unit'              => $_POST['unit']              ?? 'piece',
            'image_url'         => $imageUrl,
        ]);
        $result['success'] ? flashSuccess($result['message']) : flashError($result['message']);
        redirect(BASE_URL . 'views/admin/ingredients.php');
    }

    public function updateIngredient(int $id): void {
        requireAdmin();
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            flashError('Security token mismatch.'); redirect(BASE_URL . 'views/admin/ingredients.php');
        }

        $existing = $this->ingredient->getById($id);
        $imageUrl = $this->handleImageUpload('ingredient');
        if (!$imageUrl) {
            $urlField = sanitize($_POST['image_url'] ?? '');
            $imageUrl = $urlField ?: ($existing['image_url'] ?? '');
        }

        $result = $this->ingredient->update($id, [
            'name'              => $_POST['name']              ?? '',
            'category'          => $_POST['category']          ?? '',
            'calories_per_unit' => $_POST['calories_per_unit'] ?? 0,
            'unit'              => $_POST['unit']              ?? 'piece',
            'image_url'         => $imageUrl,
        ]);
        $result['success'] ? flashSuccess($result['message']) : flashError($result['message']);
        redirect(BASE_URL . 'views/admin/ingredients.php');
    }

    public function deleteIngredient(int $id): void {
        requireAdmin();
        $ing = $this->ingredient->getById($id);
        if ($ing && $ing['image_url'] && !str_starts_with($ing['image_url'], 'http')) {
            deleteImage($ing['image_url']);
        }
        $this->ingredient->delete($id)
            ? flashSuccess('Ingredient deleted.')
            : flashError('Could not delete ingredient.');
        redirect(BASE_URL . 'views/admin/ingredients.php');
    }

    /* ── Users ──────────────────────────────────────────────── */

    public function deactivateUser(int $id): void {
        requireAdmin();
        $this->user->deactivate($id)
            ? flashSuccess('User deactivated.')
            : flashError('Could not deactivate user.');
        redirect(BASE_URL . 'views/admin/users.php');
    }

    public function activateUser(int $id): void {
        requireAdmin();
        $this->user->activate($id)
            ? flashSuccess('User activated.')
            : flashError('Could not activate user.');
        redirect(BASE_URL . 'views/admin/users.php');
    }

    public function deleteUser(int $id): void {
        requireAdmin();
        $this->user->delete($id)
            ? flashSuccess('User deleted permanently.')
            : flashError('Could not delete user.');
        redirect(BASE_URL . 'views/admin/users.php');
    }

    /* ── Feedback ───────────────────────────────────────────── */

    public function respondToFeedback(int $id): void {
        requireAdmin();
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            flashError('Security token mismatch.'); redirect(BASE_URL . 'views/admin/feedback.php');
        }
        $response = sanitize($_POST['admin_response'] ?? '');
        $status   = in_array($_POST['status'] ?? '', ['responded','resolved']) ? $_POST['status'] : 'responded';
        if (empty($response)) { flashError('Response cannot be empty.'); redirect(BASE_URL . 'views/admin/feedback.php'); }
        $this->feedback->respond($id, $response, $status)
            ? flashSuccess('Response sent.')
            : flashError('Could not save response.');
        redirect(BASE_URL . 'views/admin/feedback.php');
    }

    public function deleteFeedback(int $id): void {
        requireAdmin();
        $this->feedback->delete($id)
            ? flashSuccess('Feedback deleted.')
            : flashError('Could not delete.');
        redirect(BASE_URL . 'views/admin/feedback.php');
    }

    /* ── Helpers ────────────────────────────────────────────── */

    /**
     * Handle image upload OR URL.
     * Returns stored path/URL string, or '' if nothing provided.
     */
    private function handleImageUpload(string $type): string {
        $fileKey = 'image_file';
        if (isset($_FILES[$fileKey]) && $_FILES[$fileKey]['error'] === UPLOAD_ERR_OK) {
            $path = uploadImage($_FILES[$fileKey], $type . 's');
            return $path ?: '';
        }
        return '';
    }

    /** Parse ingredient_ids[] + quantities[] arrays from form */
    private function parseIngredientRows(): array {
        $ids = $_POST['ingredient_ids']  ?? [];
        $qty = $_POST['quantities']      ?? [];
        $rows = [];
        foreach ($ids as $i => $ingId) {
            $ingId = (int) $ingId;
            $q     = (float) ($qty[$i] ?? 1);
            if ($ingId > 0 && $q > 0) {
                $rows[] = ['ingredient_id' => $ingId, 'quantity' => $q];
            }
        }
        return $rows;
    }
}

/* ── Route ──────────────────────────────────────────────────── */
$ctrl = new AdminController();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    match ($_POST['action']) {
        'admin_login'         => $ctrl->login(),
        'create_recipe'       => $ctrl->createRecipe(),
        'update_recipe'       => $ctrl->updateRecipe((int)($_POST['recipe_id'] ?? 0)),
        'create_ingredient'   => $ctrl->createIngredient(),
        'update_ingredient'   => $ctrl->updateIngredient((int)($_POST['ingredient_id'] ?? 0)),
        'respond_feedback'    => $ctrl->respondToFeedback((int)($_POST['feedback_id'] ?? 0)),
        default               => redirect(BASE_URL . 'views/admin/dashboard.php'),
    };
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    $id = (int)($_GET['id'] ?? 0);
    match ($_GET['action']) {
        'delete_recipe'     => $ctrl->deleteRecipe($id),
        'delete_ingredient' => $ctrl->deleteIngredient($id),
        'delete_feedback'   => $ctrl->deleteFeedback($id),
        'deactivate_user'   => $ctrl->deactivateUser($id),
        'activate_user'     => $ctrl->activateUser($id),
        'delete_user'       => $ctrl->deleteUser($id),
        'logout'            => $ctrl->logout(),
        default             => redirect(BASE_URL . 'views/admin/dashboard.php'),
    };
}
