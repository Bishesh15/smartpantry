<?php
/**
 * GoogleAuthController — Verifies Google ID Tokens and manages sessions
 * Smart Pantry
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../models/User.php';

class GoogleAuthController {
    private User $user;

    public function __construct() {
        $this->user = new User();
    }

    public function handleGoogleLogin(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
            return;
        }

        $idToken = $_POST['credential'] ?? '';
        if (empty($idToken)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Token is missing.']);
            return;
        }

        /* ── Verify Token with Google ── */
        // We use Google's public tokeninfo API for simplicity (no library needed)
        $url = "https://oauth2.googleapis.com/tokeninfo?id_token=" . $idToken;
        $response = @file_get_contents($url);
        
        if (!$response) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Failed to verify Google Token.']);
            return;
        }

        $payload = json_decode($response, true);
        if (!$payload || isset($payload['error'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid Google Token.']);
            return;
        }

        // Audience check (Security)
        if ($payload['aud'] !== GOOGLE_CLIENT_ID) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Token audience mismatch. Access denied.']);
            return;
        }

        $googleId = $payload['sub'];      // Unique Google User ID
        $email    = $payload['email'];
        $fullName = $payload['name'];

        /* ── Authentication Logic ── */
        
        // 1. Check if user already exists with this Google ID
        $user = $this->user->findByGoogleId($googleId);
        if ($user) {
            $result = $this->user->establishSession($user);
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Welcome back!']);
            return;
        }

        // 2. Check if user exists with this email (Account Linking)
        $user = $this->user->findByEmail($email);
        if ($user) {
            $this->user->linkGoogleAccount($user['id'], $googleId);
            $result = $this->user->establishSession($user);
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Google account linked! Welcome back.']);
            return;
        }

        // 3. New User Registration via Google
        $result = $this->user->registerGoogleUser([
            'email'     => $email,
            'full_name' => $fullName,
            'google_id' => $googleId
        ]);

        header('Content-Type: application/json');
        echo json_encode($result);
    }
}

/* ── Route ── */
$ctrl = new GoogleAuthController();
$ctrl->handleGoogleLogin();
