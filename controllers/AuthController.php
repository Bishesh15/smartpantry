<?php
/**
 * AuthController — User Registration & Login
 * Smart Pantry
 * 
 * Handles: POST register | POST login | GET logout
 * Passwords: plain from form → server-side password_hash (bcrypt)
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../models/User.php';

class AuthController {
    private User $user;

    public function __construct() { $this->user = new User(); }

    /* ── Register ───────────────────────────────────────────── */
    public function register(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(BASE_URL . 'views/user/register.php');
        }
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            flashError('Security token mismatch. Please try again.');
            redirect(BASE_URL . 'views/user/register.php');
        }

        $result = $this->user->register([
            'full_name' => $_POST['full_name'] ?? '',
            'username'  => $_POST['username']  ?? '',
            'email'     => $_POST['email']     ?? '',
            'password'  => $_POST['password']  ?? '',
        ]);

        // Validate confirm password
        if (($_POST['password'] ?? '') !== ($_POST['confirm_password'] ?? '')) {
            flashError('Passwords do not match.');
            redirect(BASE_URL . 'views/user/register.php');
        }

        if ($result['success']) {
            flashSuccess('Account created! Please log in.');
            redirect(BASE_URL . 'views/user/login.php');
        } else {
            flashError($result['message']);
            redirect(BASE_URL . 'views/user/register.php');
        }
    }

    /* ── Login ──────────────────────────────────────────────── */
    public function login(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(BASE_URL . 'views/user/login.php');
        }
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            flashError('Security token mismatch. Please try again.');
            redirect(BASE_URL . 'views/user/login.php');
        }

        $result = $this->user->login(
            $_POST['username'] ?? '',
            $_POST['password'] ?? ''
        );

        if ($result['success']) {
            flashSuccess('Welcome back, ' . htmlspecialchars($_SESSION['full_name']) . '!');
            redirect(BASE_URL . 'views/user/dashboard.php');
        } else {
            flashError($result['message']);
            redirect(BASE_URL . 'views/user/login.php');
        }
    }

    /* ── Logout ─────────────────────────────────────────────── */
    public function logout(): void {
        // Only clear user session keys, keep admin if present
        foreach (['user_id','username','full_name','email','food_preferences','dietary_restrictions'] as $k) {
            unset($_SESSION[$k]);
        }
        flashSuccess('You have been logged out.');
        redirect(BASE_URL . 'views/user/login.php');
    }
}

/* ── Route ──────────────────────────────────────────────────── */
$ctrl = new AuthController();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    match ($_POST['action'] ?? '') {
        'register' => $ctrl->register(),
        'login'    => $ctrl->login(),
        default    => redirect(BASE_URL . 'views/user/login.php'),
    };
}
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    $ctrl->logout();
}
