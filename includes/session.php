<?php
/**
 * Session Management
 * Smart Pantry – A Recipe Recommendation System
 */

if (!defined('SESSION_NAME')) {
    require_once __DIR__ . '/../config/constants.php';
}

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure',   0);   // set 1 on HTTPS
    ini_set('session.use_only_cookies',1);
    ini_set('session.gc_maxlifetime',  SESSION_LIFETIME);

    session_name(SESSION_NAME);
    session_start();

    // Regenerate ID periodically to prevent fixation
    if (!isset($_SESSION['_created'])) {
        $_SESSION['_created'] = time();
    } elseif (time() - $_SESSION['_created'] > 1800) {
        session_regenerate_id(true);
        $_SESSION['_created'] = time();
    }
}

// Update last activity
$_SESSION['_last_activity'] = time();
