<?php
<?php
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/constants.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) : 'Admin'; ?> - Smart Pantry</title>
    <link rel="stylesheet" href="<?php echo ASSETS_PATH; ?>css/admin-style.css">
</head>
<body>
    <header class="admin-header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <a href="<?php echo BASE_URL; ?>views/admin/dashboard.php">
                        <h1>Smart Pantry - Admin</h1>
                    </a>
                </div>
                <nav class="admin-nav">
                    <?php if (isAdmin() && !isLoggedIn()): ?>
                        <!-- ADMIN MENU ONLY -->
                        <a href="<?php echo BASE_URL; ?>views/admin/dashboard.php">Dashboard</a>
                        <span class="user-info">Admin Panel</span>
                        <a href="<?php echo BASE_URL; ?>controllers/AdminAuthController.php?action=logout">Admin Logout</a>
                    <?php else: ?>
                        <a href="<?php echo BASE_URL; ?>views/admin/login.php">Admin Login</a>
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