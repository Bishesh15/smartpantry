<?php
$page_title = 'Contact Us';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../models/Feedback.php';

// Require user login - redirect if not logged in
if (!isLoggedIn()) {
    redirect(BASE_URL . 'views/user/login.php');
}

$feedback = new Feedback();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $_SESSION['error'] = 'Invalid security token. Please try again.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $message = trim($_POST['message'] ?? '');

        // Validate required fields
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
            // Sanitize after validation
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

    <div class="contact-page">
        <h1>Contact Us</h1>
        <p class="contact-intro">We'd love to hear from you! Send us your feedback, suggestions, or any questions you have.</p>

        <div class="contact-form-container">
            <form method="POST" action="<?php echo BASE_URL; ?>views/user/contact.php" class="contact-form">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                <div class="form-group">
                    <label for="name">Name *</label>
                    <input type="text" id="name" name="name" required 
                           value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : htmlspecialchars($_SESSION['username'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" required 
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : htmlspecialchars($_SESSION['email'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="message">Message *</label>
                    <textarea id="message" name="message" rows="6" required 
                              placeholder="Tell us what's on your mind..."><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary">Send Feedback</button>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

