<?php
$page_title = 'Contact Us';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../models/Feedback.php';

// Require user login
if (!isLoggedIn()) {
    redirect(BASE_URL . 'views/user/login.php');
}

$feedback = new Feedback();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $_SESSION['error'] = 'Invalid security token. Please try again.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $message = trim($_POST['message'] ?? '');

        if (empty($name) || empty($email) || empty($message)) {
            $_SESSION['error'] = 'All fields are required';
        } elseif (strlen($name) > 100) {
            $_SESSION['error'] = 'Name must be less than 100 characters';
        } elseif (!validateEmail($email)) {
            $_SESSION['error'] = 'Invalid email format';
        } elseif (strlen($message) > 2000) {
            $_SESSION['error'] = 'Message must be less than 2000 characters';
        } elseif (strlen($message) < 10) {
            $_SESSION['error'] = 'Message must be at least 10 characters';
        } else {
            $name = sanitize($name);
            $email = sanitize($email);
            $message = sanitize($message);

            $result = $feedback->create([
                'user_id' => isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null,
                'name' => $name,
                'email' => $email,
                'message' => $message
            ]);

            if ($result['success']) {
                $_SESSION['success'] = $result['message'];
                redirect(BASE_URL . 'views/user/contact.php');
            } else {
                $_SESSION['error'] = $result['message'];
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - SmartPantry</title>
    <link rel="stylesheet" href="<?php echo ASSETS_PATH; ?>css/landing.css">
    <link rel="stylesheet" href="<?php echo ASSETS_PATH; ?>css/contact.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>

<!-- Navigation -->
<nav class="landing-nav">
    <div class="nav-container">
        <a href="<?php echo BASE_URL; ?>" class="nav-logo">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none">
                <rect width="24" height="24" rx="4" fill="#22c55e"/>
                <path d="M7 12l3 3 7-7" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span>SmartPantry</span>
        </a>
        <div class="nav-actions">
            <a href="<?php echo BASE_URL; ?>views/user/dashboard.php" class="nav-user-icon" title="<?php echo htmlspecialchars($_SESSION['username']); ?>">
                <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
            </a>
            <a href="<?php echo BASE_URL; ?>views/user/recipe-search.php" class="btn-nav-primary">Find Recipes</a>
            <a href="<?php echo BASE_URL; ?>controllers/AuthController.php?action=logout" class="btn-nav-outline">Logout</a>
        </div>
    </div>
</nav>

<!-- Hero / Header -->
<section class="ct-hero">
    <div class="ct-hero-inner">
        <span class="ct-badge">&#128172; GET IN TOUCH</span>
        <h1>We'd Love to Hear From You</h1>
        <p>Have a suggestion, question, or just want to say hello? Drop us a message and we'll get back to you as soon as possible.</p>
    </div>
</section>

<div class="ct-container">

    <?php if (isset($_SESSION['success'])): ?>
        <div class="ct-alert ct-alert-success">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="ct-alert ct-alert-error">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
            <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <div class="ct-grid">
        <!-- Contact Form -->
        <div class="ct-form-card">
            <h2>Send Us a Message</h2>
            <p class="ct-form-desc">Fill out the form below and we'll respond within 24 hours.</p>
            <form method="POST" action="<?php echo BASE_URL; ?>views/user/contact.php" class="ct-form">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                <div class="ct-form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" required maxlength="100"
                           placeholder="Your name"
                           value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : htmlspecialchars($_SESSION['username'] ?? ''); ?>"
                           class="ct-input">
                </div>

                <div class="ct-form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required
                           placeholder="you@example.com"
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : htmlspecialchars($_SESSION['email'] ?? ''); ?>"
                           class="ct-input">
                </div>

                <div class="ct-form-group">
                    <label for="message">Message</label>
                    <textarea id="message" name="message" rows="6" required minlength="10" maxlength="2000"
                              placeholder="Tell us what's on your mind..."
                              class="ct-textarea"><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                    <span class="ct-char-hint">Min 10 characters, max 2000</span>
                </div>

                <button type="submit" class="ct-submit-btn">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                    Send Message
                </button>
            </form>
        </div>

        <!-- Info Sidebar -->
        <div class="ct-info-col">
            <div class="ct-info-card">
                <div class="ct-info-icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                </div>
                <h3>Email Us</h3>
                <p>support@smartpantry.com</p>
            </div>

            <div class="ct-info-card">
                <div class="ct-info-icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                </div>
                <h3>Location</h3>
                <p>Kathmandu, Nepal</p>
            </div>

            <div class="ct-info-card">
                <div class="ct-info-icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </div>
                <h3>Response Time</h3>
                <p>We typically respond within 24 hours</p>
            </div>

            <div class="ct-faq-card">
                <h3>Frequently Asked</h3>
                <details class="ct-faq-item">
                    <summary>How do I save a recipe?</summary>
                    <p>Click the bookmark icon on any recipe detail page to save it to your favorites.</p>
                </details>
                <details class="ct-faq-item">
                    <summary>Can I change my dietary preferences?</summary>
                    <p>Yes! Go to your <a href="<?php echo BASE_URL; ?>views/user/dashboard.php?tab=preferences">Dashboard &rarr; Preferences</a> to update them anytime.</p>
                </details>
                <details class="ct-faq-item">
                    <summary>How does recipe matching work?</summary>
                    <p>Enter the ingredients you have and we'll find recipes that use them. The more ingredients you enter, the better the matches.</p>
                </details>
            </div>
        </div>
    </div>
</div>

<!-- Footer -->
<footer class="ct-footer">
    <div class="ct-footer-inner">
        <div class="ct-footer-grid">
            <div class="ct-footer-brand">
                <div class="ct-footer-logo">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <rect width="24" height="24" rx="4" fill="#22c55e"/>
                        <path d="M7 12l3 3 7-7" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span>SmartPantry</span>
                </div>
                <p>Cook healthy, authentic meals with what you already have in your kitchen.</p>
            </div>
            <div class="ct-footer-links">
                <h4>Quick Links</h4>
                <a href="<?php echo BASE_URL; ?>views/user/home.php">Home</a>
                <a href="<?php echo BASE_URL; ?>views/user/recipe-search.php">Recipes</a>
                <a href="<?php echo BASE_URL; ?>views/user/dashboard.php">Dashboard</a>
            </div>
            <div class="ct-footer-links">
                <h4>Account</h4>
                <a href="<?php echo BASE_URL; ?>views/user/dashboard.php?tab=profile">Profile</a>
                <a href="<?php echo BASE_URL; ?>views/user/dashboard.php?tab=preferences">Preferences</a>
                <a href="<?php echo BASE_URL; ?>views/user/dashboard.php?tab=saved">Saved Recipes</a>
            </div>
        </div>
        <div class="ct-footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> SmartPantry. All rights reserved.</p>
        </div>
    </div>
</footer>

<script>
// Auto-dismiss alerts
document.querySelectorAll('.ct-alert').forEach(el => {
    setTimeout(() => el.style.opacity = '0', 4000);
    setTimeout(() => el.remove(), 4500);
});
</script>

</body>
</html>

