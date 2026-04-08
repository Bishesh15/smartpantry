<?php
/**
 * Smart Pantry — Entry Point
 * Redirects to home page (or admin if admin session exists)
 */

require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/constants.php';

if (isAdmin()) {
    redirect(BASE_URL . 'views/admin/dashboard.php');
}
redirect(BASE_URL . 'views/user/home.php');
