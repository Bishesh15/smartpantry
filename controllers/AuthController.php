<?php
/**
 * Authentication Controller
 * Handles user login, registration, and logout
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../models/User.php';

class AuthController {
    private $user;
    private $isAjax;

    public function __construct() {
        $this->user = new User();
        $this->isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) 
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Send JSON response for AJAX requests
     */
    private function jsonResponse($success, $message, $data = []) {
        header('Content-Type: application/json');
        echo json_encode(array_merge([
            'success' => $success,
            'message' => $message
        ], $data));
        exit();
    }

    /**
     * Handle error - JSON for AJAX, redirect for regular
     */
    private function handleError($message, $redirectUrl) {
        if ($this->isAjax) {
            $this->jsonResponse(false, $message);
        } else {
            $_SESSION['error'] = $message;
            redirect($redirectUrl);
        }
    }

    /**
     * Handle success - JSON for AJAX, redirect for regular
     */
    private function handleSuccess($message, $redirectUrl) {
        if ($this->isAjax) {
            $this->jsonResponse(true, $message, ['redirect' => $redirectUrl]);
        } else {
            $_SESSION['success'] = $message;
            redirect($redirectUrl);
        }
    }

    /**
     * Handle user registration
     */
    public function register() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(BASE_URL . 'views/user/register.php');
        }

        $registerUrl = BASE_URL . 'views/user/register.php';
        $loginUrl = BASE_URL . 'views/user/login.php';

        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
            $this->handleError('Invalid security token. Please try again.', $registerUrl);
            return;
        }

        // Get and validate input
        $username = isset($_POST['username']) ? trim($_POST['username']) : '';
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $password_hash = isset($_POST['password_hash']) ? $_POST['password_hash'] : '';

        // Validate required fields
        if (empty($username) || empty($email) || empty($password_hash)) {
            $this->handleError('All required fields must be filled.', $registerUrl);
            return;
        }

        // Validate username format
        if (!validateUsername($username)) {
            $this->handleError('Username must be 3-20 characters (letters, numbers, underscore only).', $registerUrl);
            return;
        }

        // Validate email format
        if (!validateEmail($email)) {
            $this->handleError('Invalid email format.', $registerUrl);
            return;
        }

        // Validate password hash (should be 64 chars for SHA-256)
        if (strlen($password_hash) !== 64 || !ctype_xdigit($password_hash)) {
            $this->handleError('Invalid password format. Please try again.', $registerUrl);
            return;
        }

        // Sanitize after validation
        $username = sanitize($username);
        $email = sanitize($email);

        // Register user (no food preferences or dietary restrictions at registration)
        $result = $this->user->register($username, $email, $password_hash, '', '');

        if ($result['success']) {
            $this->handleSuccess('Registration successful! Please login.', $loginUrl);
        } else {
            $this->handleError($result['message'], $registerUrl);
        }
    }

    /**
     * Handle user login
     */
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(BASE_URL . 'views/user/login.php');
        }

        $loginUrl = BASE_URL . 'views/user/login.php';
        $homeUrl = BASE_URL . 'views/user/recipe-search.php';

        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
            $this->handleError('Invalid security token. Please try again.', $loginUrl);
            return;
        }

        // Get input
        $username = isset($_POST['username']) ? trim($_POST['username']) : '';
        $password_hash = isset($_POST['password_hash']) ? $_POST['password_hash'] : '';

        // Validate required fields
        if (empty($username) || empty($password_hash)) {
            $this->handleError('Username and password are required.', $loginUrl);
            return;
        }

        // Validate password hash format
        if (strlen($password_hash) !== 64 || !ctype_xdigit($password_hash)) {
            $this->handleError('Invalid password format. Please try again.', $loginUrl);
            return;
        }

        // Sanitize username
        $username = sanitize($username);

        // Login user
        $result = $this->user->login($username, $password_hash);

        if ($result['success']) {
            $this->handleSuccess('Login successful!', $homeUrl);
        } else {
            $this->handleError($result['message'], $loginUrl);
        }
    }

    /**
     * Handle user logout
     */
    public function logout() {
        // Only clear user session, keep admin session if exists
        unset($_SESSION['user_id']);
        unset($_SESSION['username']);
        unset($_SESSION['email']);
        unset($_SESSION['food_preferences']);
        unset($_SESSION['dietary_restrictions']);
        $_SESSION['success'] = 'You have been logged out successfully';
        redirect(BASE_URL . 'views/user/home.php');
    }
}

// Handle requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $controller = new AuthController();
    
    switch ($_POST['action']) {
        case 'register':
            $controller->register();
            break;
        case 'login':
            $controller->login();
            break;
        default:
            redirect(BASE_URL . 'views/user/login.php');
    }
}

// Handle logout (GET request)
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    $controller = new AuthController();
    $controller->logout();
}

