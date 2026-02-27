<?php
/**
 * Entry Point
 * Redirects to appropriate page based on user status
 */

require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/constants.php';

// Redirect based on login status
if (isAdmin()) {
    // Admin logged in - go to admin panel
    redirect(BASE_URL . 'views/admin/dashboard.php');
} elseif (isLoggedIn()) {
    // Regular user logged in - go to user home
    redirect(BASE_URL . 'views/user/home.php');
} else {
    // Not logged in - go to user login (public side)
    redirect(BASE_URL . 'views/user/login.php');
}

