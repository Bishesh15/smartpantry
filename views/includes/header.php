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
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) : 'Smart Pantry'; ?> - Smart Pantry</title>
    <link rel="stylesheet" href="<?php echo ASSETS_PATH; ?>css/style.css">
</head>
<body>
    <header class="main-header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <a href="<?php echo BASE_URL; ?>views/user/home.php">
                        <h1>Smart Pantry</h1>
                    </a>
                </div>
                <nav class="main-nav">
                    <?php if (isLoggedIn()): ?>
                        <!-- USER MENU -->
                        <a href="<?php echo BASE_URL; ?>views/user/recipe-search.php">Recipes</a>
                        <a href="<?php echo BASE_URL; ?>views/user/contact.php">Contact</a>
                        <span class="user-info">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                        <a href="<?php echo BASE_URL; ?>controllers/AuthController.php?action=logout">Logout</a>
                    <?php else: ?>
                        <!-- NOT LOGGED IN -->
                        <a href="<?php echo BASE_URL; ?>views/user/login.php">Login</a>
                        <a href="<?php echo BASE_URL; ?>views/user/register.php">Register</a>
                    <?php endif; ?>
                </nav>
            </div>
        </div>
    </header>

    <main class="main-content">
        <div class="container">
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