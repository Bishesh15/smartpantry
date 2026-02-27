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
} else {
    // All users (logged in or not) go to the landing page
    redirect(BASE_URL . 'views/user/home.php');
}

