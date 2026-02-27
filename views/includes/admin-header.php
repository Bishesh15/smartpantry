<?php
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/constants.php';

// Require admin login for admin pages (except login/register pages)
$current_page = basename($_SERVER['PHP_SELF']);
if (!isAdmin() && $current_page !== 'login.php' && $current_page !== 'register.php') {
    // If not logged in as admin, redirect to admin login
    redirect(BASE_URL . 'views/admin/login.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) : 'Admin Panel'; ?> - Smart Pantry Admin</title>
    <link rel="stylesheet" href="<?php echo ASSETS_PATH; ?>css/style.css">
    <link rel="stylesheet" href="<?php echo ASSETS_PATH; ?>css/admin.css">
</head>
<body>
    <header class="admin-header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <a href="<?php echo BASE_URL; ?>views/admin/dashboard.php">
                        <h1>Smart Pantry - Admin Panel</h1>
                    </a>
                </div>
                <nav class="admin-nav">
                    <?php if (isAdmin()): ?>
                        <a href="<?php echo BASE_URL; ?>views/admin/dashboard.php">Dashboard</a>
                        <a href="<?php echo BASE_URL; ?>views/admin/recipes.php">Recipes</a>
                        <a href="<?php echo BASE_URL; ?>views/admin/ingredients.php">Ingredients</a>
                        <a href="<?php echo BASE_URL; ?>views/admin/feedback.php">Feedback</a>
                        <span class="admin-info">Admin: <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                        <a href="<?php echo BASE_URL; ?>controllers/AdminController.php?action=logout">Logout</a>
                    <?php else: ?>
                        <a href="<?php echo BASE_URL; ?>views/admin/login.php">Admin Login</a>
                        <a href="<?php echo BASE_URL; ?>views/admin/register.php">Admin Register</a>
                    <?php endif; ?>
                </nav>
            </div>
        </div>
    </header>

    <main class="main-content">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php 
                echo htmlspecialchars($_SESSION['success']); 
                unset($_SESSION['success']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?php 
                echo htmlspecialchars($_SESSION['error']); 
                unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

