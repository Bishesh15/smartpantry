<?php
$page_title = 'Admin Login';
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
            <h2>Admin Login</h2>
            <p class="auth-subtitle">Administrator access only</p>

            <form id="adminLoginForm" method="POST" action="<?php echo BASE_URL; ?>controllers/AdminController.php">
                <input type="hidden" name="action" value="admin_login">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <input type="hidden" name="password_hash" id="password_hash">

                <div class="form-group">
                    <label for="username">Username *</label>
                    <input type="text" id="username" name="username" required autofocus>
                </div>

                <div class="form-group">
                    <label for="password">Password *</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit" class="btn btn-primary">Login</button>
            </form>

            <p class="auth-link">
                Don't have an admin account? <a href="<?php echo BASE_URL; ?>views/admin/register.php">Register here</a>
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
    document.getElementById('adminLoginForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const password = document.getElementById('password').value;
        
        // Disable submit button
        const submitBtn = this.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Logging in...';
        
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
            submitBtn.textContent = 'Login';
        }
    });
});
</script>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>

