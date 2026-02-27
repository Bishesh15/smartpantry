<?php
$page_title = 'Admin Registration';
require_once __DIR__ . '/../includes/admin-header.php';

// Redirect if already logged in as admin
if (isAdmin()) {
    redirect(BASE_URL . 'views/admin/dashboard.php');
}

// If user is logged in, show message
if (isLoggedIn() && !isAdmin()) {
    $_SESSION['error'] = 'You are logged in as a regular user. Please logout first.';
}
?>

<div class="container">
    <div class="auth-container">
        <div class="auth-form">
            <h2>Admin Registration</h2>
            <p class="auth-subtitle">Create administrator account (one-time registration)</p>

            <form id="adminRegisterForm" method="POST" action="<?php echo BASE_URL; ?>controllers/AdminController.php">
                <input type="hidden" name="action" value="admin_register">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <input type="hidden" name="password_hash" id="password_hash">

                <div class="form-group">
                    <label for="username">Username *</label>
                    <input type="text" id="username" name="username" required 
                           pattern="[a-zA-Z0-9_]{3,20}" 
                           title="Username must be 3-20 characters (letters, numbers, underscore only)">
                    <span class="error-message" id="username-error"></span>
                </div>

                <div class="form-group">
                    <label for="password">Password *</label>
                    <input type="password" id="password" name="password" required 
                           minlength="8"
                           pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}"
                           title="Password must be at least 8 characters with uppercase, lowercase, number, and special character">
                    <span class="error-message" id="password-error"></span>
                    <small class="form-hint">Must contain: uppercase, lowercase, number, and special character</small>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password *</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                    <span class="error-message" id="confirm_password-error"></span>
                </div>

                <button type="submit" class="btn btn-primary">Register as Admin</button>
            </form>

            <p class="auth-link">
                Already have an admin account? <a href="<?php echo BASE_URL; ?>views/admin/login.php">Login here</a>
            </p>
        </div>
    </div>
</div>

<script>
// Client-side password hashing using Web Crypto API
async function hashPassword(password) {
    try {
        const encoder = new TextEncoder();
        const data = encoder.encode(password);
        const hashBuffer = await crypto.subtle.digest('SHA-256', data);
        const hashArray = Array.from(new Uint8Array(hashBuffer));
        const hashHex = hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
        return hashHex;
    } catch (error) {
        console.error('Password hashing error:', error);
        throw error;
    }
}

// Initialize form handler
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('adminRegisterForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        
        // Validate passwords match
        if (password !== confirmPassword) {
            document.getElementById('confirm_password-error').textContent = 'Passwords do not match';
            return;
        }
        
        // Disable submit button
        const submitBtn = this.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Processing...';
        
        try {
            // Hash password using Web Crypto API
            const hashedPassword = await hashPassword(password);
            
            // Set the hashed password
            document.getElementById('password_hash').value = hashedPassword;
            
            // Submit the form
            this.submit();
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred while processing your password. Please try again.');
            submitBtn.disabled = false;
            submitBtn.textContent = 'Register as Admin';
        }
    });
});
</script>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>

