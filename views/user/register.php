<?php
$page_title = 'Register';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/constants.php';

// Redirect if already logged in as user
if (isLoggedIn() && !isAdmin()) {
    redirect(BASE_URL . 'views/user/home.php');
}

// Google OAuth URL
$google_client_id = defined('GOOGLE_CLIENT_ID') ? GOOGLE_CLIENT_ID : '';
$google_redirect_uri = BASE_URL . 'controllers/GoogleAuthController.php';
$google_oauth_url = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
    'client_id' => $google_client_id,
    'redirect_uri' => $google_redirect_uri,
    'response_type' => 'code',
    'scope' => 'openid email profile',
    'access_type' => 'online',
    'prompt' => 'select_account',
    'state' => generateCSRFToken()
]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> - Smart Pantry</title>
    <link rel="stylesheet" href="<?php echo ASSETS_PATH; ?>css/auth.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body>

<div class="auth-page-wrapper">
    <!-- LEFT SIDE - FORM -->
    <div class="auth-left">
        <a href="<?php echo BASE_URL; ?>" class="auth-logo">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 3h18v18H3z" stroke="#22c55e" fill="none"/>
                <path d="M8 12c0-2 1-4 4-4s4 2 4 4-1 4-4 4" stroke="#22c55e"/>
            </svg>
            <span>SmartPantry</span>
        </a>

        <div class="auth-form-section">
            <h1>Create account</h1>
            <p class="auth-subtitle">Join Smart Pantry to discover amazing recipes.</p>

            <!-- Tabs -->
            <div class="auth-tabs">
                <a href="<?php echo BASE_URL; ?>views/user/login.php" class="auth-tab">Log In</a>
                <a href="<?php echo BASE_URL; ?>views/user/register.php" class="auth-tab active">Sign Up</a>
            </div>

            <!-- Alert Messages -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="auth-alert auth-alert-success">
                    <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="auth-alert auth-alert-error">
                    <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <!-- Register Form -->
            <form id="registerForm" method="POST" action="<?php echo BASE_URL; ?>controllers/AuthController.php">
                <input type="hidden" name="action" value="register">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <input type="hidden" name="password_hash" id="password_hash">

                <div class="auth-field">
                    <div class="auth-field-label">
                        <label for="username">USERNAME</label>
                    </div>
                    <div class="auth-input-wrapper">
                        <span class="auth-input-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                        </span>
                        <input type="text" id="username" name="username" placeholder="Choose a username" required autofocus
                               pattern="[a-zA-Z0-9_]{3,20}" 
                               title="3-20 characters (letters, numbers, underscore only)">
                    </div>
                    <span class="error-message" id="username-error"></span>
                </div>

                <div class="auth-field">
                    <div class="auth-field-label">
                        <label for="email">EMAIL</label>
                    </div>
                    <div class="auth-input-wrapper">
                        <span class="auth-input-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                <polyline points="22,6 12,13 2,6"/>
                            </svg>
                        </span>
                        <input type="email" id="email" name="email" placeholder="name@example.com" required>
                    </div>
                    <span class="error-message" id="email-error"></span>
                </div>

                <div class="auth-field">
                    <div class="auth-field-label">
                        <label for="password">PASSWORD</label>
                    </div>
                    <div class="auth-input-wrapper">
                        <span class="auth-input-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                        </span>
                        <input type="password" id="password" name="password" placeholder="••••••••" required minlength="8">
                        <button type="button" class="auth-toggle-password" onclick="togglePassword('password', this)">
                            <svg class="eye-off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                                <line x1="1" y1="1" x2="23" y2="23"/>
                            </svg>
                            <svg class="eye-on" style="display:none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                    <span class="error-message" id="password-error"></span>
                    <span class="form-hint">Must contain: uppercase, lowercase, number, and special character</span>
                </div>

                <div class="auth-field">
                    <div class="auth-field-label">
                        <label for="confirm_password">CONFIRM PASSWORD</label>
                    </div>
                    <div class="auth-input-wrapper">
                        <span class="auth-input-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                        </span>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="••••••••" required>
                        <button type="button" class="auth-toggle-password" onclick="togglePassword('confirm_password', this)">
                            <svg class="eye-off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                                <line x1="1" y1="1" x2="23" y2="23"/>
                            </svg>
                            <svg class="eye-on" style="display:none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                    <span class="error-message" id="confirm_password-error"></span>
                </div>

                <button type="submit" class="auth-submit-btn">
                    Create account
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18">
                        <line x1="5" y1="12" x2="19" y2="12"/>
                        <polyline points="12 5 19 12 12 19"/>
                    </svg>
                </button>
            </form>

            <!-- Divider -->
            <div class="auth-divider">
                <span>or continue with</span>
            </div>

            <!-- Social Login -->
            <div class="auth-social-buttons">
                <a href="<?php echo htmlspecialchars($google_oauth_url); ?>" class="auth-social-btn">
                    <svg viewBox="0 0 24 24" width="20" height="20">
                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" fill="#4285F4"/>
                        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                    </svg>
                    Google
                </a>
            </div>

            <!-- Footer Link -->
            <p class="auth-footer-link">
                Already have an account? <a href="<?php echo BASE_URL; ?>views/user/login.php">Log In</a>
            </p>

            <div class="auth-page-footer">
                &copy; <?php echo date('Y'); ?> SmartPantry. All rights reserved.
            </div>
        </div>
    </div>

    <!-- RIGHT SIDE - HERO -->
    <div class="auth-right">
        <div class="auth-right-bg">
            <img src="https://images.unsplash.com/photo-1496116218417-1a781b1c416c?w=1200&q=80" alt="Delicious food" loading="lazy">
        </div>
        <div class="auth-right-content">
            <div class="auth-badge">
                <svg viewBox="0 0 24 24" fill="currentColor" width="14" height="14"><circle cx="12" cy="12" r="5"/></svg>
                Your Personal Chef
            </div>
            <h2 class="auth-hero-heading">
                Turn simple ingredients into <span class="green">healthy feasts</span>.
            </h2>
            <div class="auth-features">
                <div class="auth-feature-card">
                    <div class="auth-feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                            <line x1="3" y1="9" x2="21" y2="9"/>
                            <line x1="9" y1="21" x2="9" y2="9"/>
                        </svg>
                    </div>
                    <div>
                        <h4>Pantry-First Cooking</h4>
                        <p>Find recipes matching what you already have at home.</p>
                    </div>
                </div>
                <div class="auth-feature-card">
                    <div class="auth-feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 20V10"/><path d="M18 20V4"/><path d="M6 20v-4"/>
                        </svg>
                    </div>
                    <div>
                        <h4>Calorie &amp; Macro Tracking</h4>
                        <p>Stay on top of your nutrition with automatic calculations.</p>
                    </div>
                </div>
                <div class="auth-feature-card">
                    <div class="auth-feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/>
                            <polyline points="12 6 12 12 16 14"/>
                        </svg>
                    </div>
                    <div>
                        <h4>Personalized Feed</h4>
                        <p>Get recipe suggestions tailored to your taste profile.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword(fieldId, btn) {
    var $input = $('#' + fieldId);
    var $eyeOff = $(btn).find('.eye-off');
    var $eyeOn = $(btn).find('.eye-on');
    if ($input.attr('type') === 'password') {
        $input.attr('type', 'text');
        $eyeOff.hide();
        $eyeOn.show();
    } else {
        $input.attr('type', 'password');
        $eyeOff.show();
        $eyeOn.hide();
    }
}

async function hashPassword(password) {
    var encoder = new TextEncoder();
    var data = encoder.encode(password);
    var hashBuffer = await crypto.subtle.digest('SHA-256', data);
    var hashArray = Array.from(new Uint8Array(hashBuffer));
    return hashArray.map(function(b) { return b.toString(16).padStart(2, '0'); }).join('');
}

function showAlert(type, message) {
    $('.auth-alert').remove();
    var alertClass = type === 'success' ? 'auth-alert-success' : 'auth-alert-error';
    var $alert = $('<div class="auth-alert ' + alertClass + '">' + $('<span>').text(message).html() + '</div>');
    $('#registerForm').before($alert);
    setTimeout(function() { $alert.fadeOut(300, function() { $(this).remove(); }); }, 5000);
}

$(document).ready(function() {
    // Clear field errors on input
    $('#registerForm input').on('input', function() {
        $(this).closest('.auth-field').find('.error-message').text('');
    });

    $('#registerForm').on('submit', async function(e) {
        e.preventDefault();

        var $form = $(this);
        var $btn = $form.find('.auth-submit-btn');
        var btnOriginalHtml = $btn.html();
        var username = $.trim($('#username').val());
        var email = $.trim($('#email').val());
        var password = $('#password').val();
        var confirmPassword = $('#confirm_password').val();

        // Clear previous errors
        $form.find('.error-message').text('');
        $('.auth-alert').remove();

        // Client-side validation
        var hasError = false;
        if (!username) {
            $('#username-error').text('Username is required.');
            hasError = true;
        } else if (!/^[a-zA-Z0-9_]{3,20}$/.test(username)) {
            $('#username-error').text('3-20 characters (letters, numbers, underscore only).');
            hasError = true;
        }
        if (!email) {
            $('#email-error').text('Email is required.');
            hasError = true;
        } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            $('#email-error').text('Please enter a valid email.');
            hasError = true;
        }
        if (!password) {
            $('#password-error').text('Password is required.');
            hasError = true;
        } else if (password.length < 8) {
            $('#password-error').text('Password must be at least 8 characters.');
            hasError = true;
        }
        if (password !== confirmPassword) {
            $('#confirm_password-error').text('Passwords do not match.');
            hasError = true;
        }
        if (hasError) return;

        // Disable button and show loading
        $btn.prop('disabled', true).html('<span class="auth-spinner"></span> Creating account...');

        try {
            var hashedPassword = await hashPassword(password);
            $('#password_hash').val(hashedPassword);

            $.ajax({
                url: $form.attr('action'),
                type: 'POST',
                data: $form.serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showAlert('success', response.message);
                        // Redirect to login after brief delay
                        setTimeout(function() {
                            window.location.href = response.redirect;
                        }, 1000);
                    } else {
                        showAlert('error', response.message);
                        $btn.prop('disabled', false).html(btnOriginalHtml);
                    }
                },
                error: function(xhr) {
                    if (xhr.responseJSON) {
                        showAlert('error', xhr.responseJSON.message || 'An error occurred.');
                    } else {
                        showAlert('error', 'Something went wrong. Please try again.');
                    }
                    $btn.prop('disabled', false).html(btnOriginalHtml);
                }
            });
        } catch (error) {
            console.error('Error:', error);
            showAlert('error', 'An error occurred while processing. Please try again.');
            $btn.prop('disabled', false).html(btnOriginalHtml);
        }
    });
});
</script>
</body>
</html>

