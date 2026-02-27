<?php
/**
 * Google OAuth Controller
 * Handles Google OAuth2 login/registration callback
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../models/User.php';

class GoogleAuthController {
    private $user;
    private $clientId;
    private $clientSecret;
    private $redirectUri;

    public function __construct() {
        $this->user = new User();
        $this->clientId = GOOGLE_CLIENT_ID;
        $this->clientSecret = GOOGLE_CLIENT_SECRET;
        $this->redirectUri = GOOGLE_REDIRECT_URI;
    }

    /**
     * Handle Google OAuth callback
     */
    public function handleCallback() {
        // Verify state (CSRF protection)
        if (!isset($_GET['state']) || !verifyCSRFToken($_GET['state'])) {
            $_SESSION['error'] = 'Invalid security token. Please try again.';
            redirect(BASE_URL . 'views/user/login.php');
        }

        // Check for errors from Google
        if (isset($_GET['error'])) {
            $_SESSION['error'] = 'Google login was cancelled or failed. Please try again.';
            redirect(BASE_URL . 'views/user/login.php');
        }

        // Get authorization code
        if (!isset($_GET['code'])) {
            $_SESSION['error'] = 'No authorization code received from Google.';
            redirect(BASE_URL . 'views/user/login.php');
        }

        $code = $_GET['code'];

        // Exchange code for access token
        $tokenData = $this->exchangeCodeForToken($code);
        if (!$tokenData) {
            $_SESSION['error'] = 'Failed to verify Google login. Please try again.';
            redirect(BASE_URL . 'views/user/login.php');
        }

        // Get user info from Google
        $googleUser = $this->getGoogleUserInfo($tokenData['access_token']);
        if (!$googleUser) {
            $_SESSION['error'] = 'Failed to get user information from Google.';
            redirect(BASE_URL . 'views/user/login.php');
        }

        // Try to login or register the user
        $this->loginOrRegisterUser($googleUser);
    }

    /**
     * Exchange authorization code for access token
     */
    private function exchangeCodeForToken($code) {
        $url = 'https://oauth2.googleapis.com/token';
        $params = [
            'code' => $code,
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri' => $this->redirectUri,
            'grant_type' => 'authorization_code'
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // XAMPP localhost - set to true in production with proper CA bundle
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);     // XAMPP localhost - set to 2 in production
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($curlError) {
            error_log("Google OAuth cURL error: $curlError");
            return null;
        }

        if ($httpCode !== 200 || !$response) {
            error_log("Google OAuth token exchange failed. HTTP: $httpCode, Response: $response");
            return null;
        }

        $data = json_decode($response, true);
        if (!isset($data['access_token'])) {
            error_log("Google OAuth: No access token in response: $response");
            return null;
        }

        return $data;
    }

    /**
     * Get user information from Google API
     */
    private function getGoogleUserInfo($accessToken) {
        $url = 'https://www.googleapis.com/oauth2/v2/userinfo';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken,
            'Accept: application/json'
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // XAMPP localhost
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);     // XAMPP localhost
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($curlError) {
            error_log("Google OAuth userinfo cURL error: $curlError");
        }

        if ($httpCode !== 200 || !$response) {
            error_log("Google OAuth user info failed. HTTP: $httpCode");
            return null;
        }

        $userData = json_decode($response, true);
        if (!isset($userData['id']) || !isset($userData['email'])) {
            error_log("Google OAuth: Invalid user data received");
            return null;
        }

        return $userData;
    }

    /**
     * Login existing user or register new user via Google
     */
    private function loginOrRegisterUser($googleUser) {
        $googleId = $googleUser['id'];
        $email = $googleUser['email'];
        $name = isset($googleUser['name']) ? $googleUser['name'] : '';

        // First, try to find user by Google ID
        $existingUser = $this->user->findByGoogleId($googleId);

        if ($existingUser) {
            // User exists with this Google ID - log them in
            $this->setUserSession($existingUser);
            $_SESSION['success'] = 'Welcome back! Logged in with Google.';
            redirect(BASE_URL . 'views/user/recipe-search.php');
        }

        // Try to find user by email
        $existingUser = $this->user->findByEmail($email);

        if ($existingUser) {
            // User exists with this email - link Google account and log in
            $this->user->linkGoogleAccount($existingUser['id'], $googleId);
            $this->setUserSession($existingUser);
            $_SESSION['success'] = 'Google account linked! Logged in successfully.';
            redirect(BASE_URL . 'views/user/recipe-search.php');
        }

        // New user - register with Google
        $username = $this->generateUsername($name, $email);
        $result = $this->user->registerWithGoogle($username, $email, $googleId);

        if ($result['success']) {
            $newUser = $this->user->getUserById($result['user_id']);
            if ($newUser) {
                $this->setUserSession($newUser);
                $_SESSION['success'] = 'Account created with Google! Welcome to Smart Pantry.';
                redirect(BASE_URL . 'views/user/recipe-search.php');
            }
        }

        $_SESSION['error'] = isset($result['message']) ? $result['message'] : 'Failed to create account. Please try again.';
        redirect(BASE_URL . 'views/user/register.php');
    }

    /**
     * Set the user session variables
     */
    private function setUserSession($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['food_preferences'] = isset($user['food_preferences']) ? $user['food_preferences'] : '';
        $_SESSION['dietary_restrictions'] = isset($user['dietary_restrictions']) ? $user['dietary_restrictions'] : '';
        session_regenerate_id(true);
    }

    /**
     * Generate a unique username from Google name/email
     */
    private function generateUsername($name, $email) {
        // Try using the name first
        if (!empty($name)) {
            $base = preg_replace('/[^a-zA-Z0-9_]/', '', str_replace(' ', '_', strtolower($name)));
        } else {
            // Use the part before @ in email
            $base = preg_replace('/[^a-zA-Z0-9_]/', '', explode('@', $email)[0]);
        }

        // Ensure minimum length
        if (strlen($base) < 3) {
            $base = 'user_' . $base;
        }

        // Truncate to max 15 chars to leave room for suffix
        $base = substr($base, 0, 15);

        // Check if username is available
        $username = $base;
        $counter = 1;
        while ($this->user->findByUsername($username)) {
            $username = $base . '_' . $counter;
            $counter++;
            if ($counter > 100) {
                $username = $base . '_' . bin2hex(random_bytes(3));
                break;
            }
        }

        return $username;
    }
}

// Handle callback
if (isset($_GET['code']) || isset($_GET['error'])) {
    $controller = new GoogleAuthController();
    $controller->handleCallback();
} else {
    // If accessed directly without code, redirect to login
    redirect(BASE_URL . 'views/user/login.php');
}
