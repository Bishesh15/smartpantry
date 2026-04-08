<?php
/**
 * UserController — Pantry, Favorites, Ratings, Profile actions
 * Smart Pantry
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Rating.php';

class UserController {
    private User   $userModel;
    private Rating $ratingModel;

    public function __construct() {
        $this->userModel   = new User();
        $this->ratingModel = new Rating();
    }

    /* ── Pantry ─────────────────────────────────────────────── */

    public function addToPantry(): void {
        requireLogin();
        $ingId = validateInteger($_POST['ingredient_id'] ?? 0, 1);
        $qty   = max(0.1, (float)($_POST['quantity'] ?? 1));
        if ($ingId === false) { flashError('Invalid ingredient.'); }
        else {
            $this->userModel->addToPantry($_SESSION['user_id'], $ingId, $qty)
                ? flashSuccess('Ingredient added to your pantry.')
                : flashError('Could not add ingredient.');
        }
        redirect($_POST['redirect'] ?? BASE_URL . 'views/user/pantry.php');
    }

    public function removeFromPantry(): void {
        requireLogin();
        $ingId = validateInteger($_POST['ingredient_id'] ?? 0, 1);
        if ($ingId === false) { flashError('Invalid ingredient.'); }
        else {
            $this->userModel->removeFromPantry($_SESSION['user_id'], $ingId)
                ? flashSuccess('Ingredient removed.')
                : flashError('Could not remove ingredient.');
        }
        redirect($_POST['redirect'] ?? BASE_URL . 'views/user/pantry.php');
    }

    public function clearPantry(): void {
        requireLogin();
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            flashError('Security token mismatch.'); redirect(BASE_URL . 'views/user/pantry.php');
        }
        $this->userModel->clearPantry($_SESSION['user_id']);
        flashSuccess('Pantry cleared.');
        redirect(BASE_URL . 'views/user/pantry.php');
    }

    /* ── Favorites ──────────────────────────────────────────── */

    public function addFavorite(): void {
        requireLogin();
        $recipeId = validateInteger($_POST['recipe_id'] ?? 0, 1);
        if ($recipeId === false) { flashError('Invalid recipe.'); }
        else {
            $this->userModel->isFavorited($_SESSION['user_id'], $recipeId)
                ? flashInfo('Already in your favorites.')
                : ($this->userModel->addFavorite($_SESSION['user_id'], $recipeId)
                    ? flashSuccess('Recipe saved to favorites!')
                    : flashError('Could not save recipe.'));
        }
        redirect($_POST['redirect'] ?? BASE_URL . 'views/user/recipe-detail.php?id=' . ($recipeId ?: ''));
    }

    public function removeFavorite(): void {
        requireLogin();
        $recipeId = validateInteger($_POST['recipe_id'] ?? 0, 1);
        if ($recipeId === false) { flashError('Invalid recipe.'); }
        else {
            $this->userModel->removeFavorite($_SESSION['user_id'], $recipeId)
                ? flashSuccess('Removed from favorites.')
                : flashError('Could not remove.');
        }
        redirect($_POST['redirect'] ?? BASE_URL . 'views/user/favorites.php');
    }

    /* ── Ratings ────────────────────────────────────────────── */

    public function addRating(): void {
        requireLogin();
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            flashError('Security token mismatch.'); redirect(BASE_URL . 'views/user/recipe-search.php');
        }
        $recipeId = validateInteger($_POST['recipe_id'] ?? 0, 1);
        $rating   = validateInteger($_POST['rating'] ?? 0, 1, 5);
        $comment  = sanitize($_POST['comment'] ?? '');

        if ($recipeId === false || $rating === false) {
            flashError('Please select a star rating.');
            redirect(BASE_URL . 'views/user/recipe-detail.php?id=' . ($_POST['recipe_id'] ?? ''));
        }
        $result = $this->ratingModel->addOrUpdate($_SESSION['user_id'], $recipeId, $rating, $comment);
        $result['success'] ? flashSuccess($result['message']) : flashError($result['message']);
        redirect(BASE_URL . 'views/user/recipe-detail.php?id=' . $recipeId);
    }

    /* ── Profile ────────────────────────────────────────────── */

    public function updateProfile(): void {
        requireLogin();
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            flashError('Security token mismatch.'); redirect(BASE_URL . 'views/user/profile.php');
        }
        $fp = is_array($_POST['food_preferences'] ?? null)
            ? implode(',', $_POST['food_preferences'])
            : '';
        $dr = is_array($_POST['dietary_restrictions'] ?? null)
            ? implode(',', $_POST['dietary_restrictions'])
            : '';

        $result = $this->userModel->updateProfile($_SESSION['user_id'], [
            'full_name'            => $_POST['full_name']        ?? '',
            'food_preferences'     => $fp,
            'dietary_restrictions' => $dr,
            'daily_calorie_goal'   => $_POST['daily_calorie_goal'] ?? 2000,
        ]);
        $result['success'] ? flashSuccess($result['message']) : flashError($result['message']);
        redirect(BASE_URL . 'views/user/profile.php');
    }

    public function updatePassword(): void {
        requireLogin();
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            flashError('Security token mismatch.'); redirect(BASE_URL . 'views/user/profile.php');
        }
        if (($_POST['new_password'] ?? '') !== ($_POST['confirm_password'] ?? '')) {
            flashError('New passwords do not match.');
            redirect(BASE_URL . 'views/user/profile.php');
        }
        $result = $this->userModel->updatePassword(
            $_SESSION['user_id'],
            $_POST['current_password'] ?? '',
            $_POST['new_password']     ?? ''
        );
        $result['success'] ? flashSuccess($result['message']) : flashError($result['message']);
        redirect(BASE_URL . 'views/user/profile.php');
    }
}

/* ── Route ──────────────────────────────────────────────────── */
$ctrl = new UserController();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    match ($_POST['action']) {
        'add_to_pantry'     => $ctrl->addToPantry(),
        'remove_from_pantry'=> $ctrl->removeFromPantry(),
        'clear_pantry'      => $ctrl->clearPantry(),
        'add_favorite'      => $ctrl->addFavorite(),
        'remove_favorite'   => $ctrl->removeFavorite(),
        'add_rating'        => $ctrl->addRating(),
        'update_profile'    => $ctrl->updateProfile(),
        'update_password'   => $ctrl->updatePassword(),
        default             => redirect(BASE_URL . 'views/user/dashboard.php'),
    };
}
