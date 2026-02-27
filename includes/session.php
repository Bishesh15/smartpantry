<?php
/**
 * Session Management
 * Handles session initialization and security
 */

// Load constants if not already loaded
if (!defined('SESSION_NAME')) {
    require_once __DIR__ . '/../config/constants.php';
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    // Configure session settings
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 0); // Set to 1 in production with HTTPS
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_samesite', 'Lax'); // Lax required for OAuth redirects (Strict blocks cross-site redirects)
    
    // Set session name
    $session_name = defined('SESSION_NAME') ? SESSION_NAME : 'SMARTPANTRY_SESSION';
    session_name($session_name);
    
    // Set session lifetime
    $session_lifetime = defined('SESSION_LIFETIME') ? SESSION_LIFETIME : 3600;
    ini_set('session.gc_maxlifetime', $session_lifetime);
    
    // Start session
    session_start();
    
    // Regenerate session ID periodically for security
    if (!isset($_SESSION['created'])) {
        $_SESSION['created'] = time();
    } else if (time() - $_SESSION['created'] > 1800) {
        // Regenerate ID every 30 minutes
        session_regenerate_id(true);
        $_SESSION['created'] = time();
    }
}

// Check for session timeout - only clear if both sessions are expired
$session_lifetime = defined('SESSION_LIFETIME') ? SESSION_LIFETIME : 3600;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $session_lifetime)) {
    // Check if user or admin is still active
    $has_active_session = (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) || 
                          (isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']));
    
    if (!$has_active_session) {
        // Only clear if no active sessions
        session_unset();
        session_destroy();
        session_start();
    }
}

// Update last activity time
$_SESSION['last_activity'] = time();

