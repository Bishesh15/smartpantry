/**
 * Form Validation JavaScript
 * Client-side validation for forms
 */

document.addEventListener('DOMContentLoaded', function() {
    // Validate registration form
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', validateRegistrationForm);
        
        // Real-time validation
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');
        
        if (password) {
            password.addEventListener('input', validatePasswordStrength);
        }
        
        if (confirmPassword) {
            confirmPassword.addEventListener('input', validatePasswordMatch);
        }
    }

    // Validate login form
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', validateLoginForm);
    }

    // Validate contact form
    const contactForm = document.querySelector('.contact-form');
    if (contactForm) {
        contactForm.addEventListener('submit', validateContactForm);
    }
});

/**
 * Validate registration form
 */
function validateRegistrationForm(e) {
    const username = document.getElementById('username');
    const email = document.getElementById('email');
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');

    let isValid = true;

    // Validate username
    if (!username.value.trim()) {
        showError('username-error', 'Username is required');
        isValid = false;
    } else if (username.value.length < 3 || username.value.length > 20) {
        showError('username-error', 'Username must be 3-20 characters');
        isValid = false;
    } else if (!/^[a-zA-Z0-9_]+$/.test(username.value)) {
        showError('username-error', 'Username can only contain letters, numbers, and underscores');
        isValid = false;
    } else {
        clearError('username-error');
    }

    // Validate email
    if (!email.value.trim()) {
        showError('email-error', 'Email is required');
        isValid = false;
    } else if (!isValidEmail(email.value)) {
        showError('email-error', 'Please enter a valid email address');
        isValid = false;
    } else {
        clearError('email-error');
    }

    // Validate password
    if (!password.value) {
        showError('password-error', 'Password is required');
        isValid = false;
    } else if (!isStrongPassword(password.value)) {
        showError('password-error', 'Password must be at least 8 characters with uppercase, lowercase, number, and special character');
        isValid = false;
    } else {
        clearError('password-error');
    }

    // Validate confirm password
    if (!confirmPassword.value) {
        showError('confirm_password-error', 'Please confirm your password');
        isValid = false;
    } else if (password.value !== confirmPassword.value) {
        showError('confirm_password-error', 'Passwords do not match');
        isValid = false;
    } else {
        clearError('confirm_password-error');
    }

    if (!isValid) {
        e.preventDefault();
        return false;
    }

    return true;
}

/**
 * Validate login form
 */
function validateLoginForm(e) {
    const username = document.getElementById('username');
    const password = document.getElementById('password');

    let isValid = true;

    if (!username.value.trim()) {
        showError('username-error', 'Username is required');
        isValid = false;
    } else {
        clearError('username-error');
    }

    if (!password.value) {
        showError('password-error', 'Password is required');
        isValid = false;
    } else {
        clearError('password-error');
    }

    if (!isValid) {
        e.preventDefault();
        return false;
    }

    return true;
}

/**
 * Validate contact form
 */
function validateContactForm(e) {
    const name = document.getElementById('name');
    const email = document.getElementById('email');
    const message = document.getElementById('message');

    let isValid = true;

    if (!name.value.trim()) {
        alert('Name is required');
        isValid = false;
    }

    if (!email.value.trim()) {
        alert('Email is required');
        isValid = false;
    } else if (!isValidEmail(email.value)) {
        alert('Please enter a valid email address');
        isValid = false;
    }

    if (!message.value.trim()) {
        alert('Message is required');
        isValid = false;
    }

    if (!isValid) {
        e.preventDefault();
        return false;
    }

    return true;
}

/**
 * Validate password strength
 */
function validatePasswordStrength() {
    const password = document.getElementById('password');
    const errorElement = document.getElementById('password-error');
    
    if (!password.value) {
        clearError('password-error');
        return;
    }

    if (!isStrongPassword(password.value)) {
        showError('password-error', 'Password must be at least 8 characters with uppercase, lowercase, number, and special character');
    } else {
        clearError('password-error');
    }
}

/**
 * Validate password match
 */
function validatePasswordMatch() {
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');
    
    if (!confirmPassword.value) {
        clearError('confirm_password-error');
        return;
    }

    if (password.value !== confirmPassword.value) {
        showError('confirm_password-error', 'Passwords do not match');
    } else {
        clearError('confirm_password-error');
    }
}

/**
 * Check if password is strong
 */
function isStrongPassword(password) {
    // At least 8 characters, with uppercase, lowercase, number, and special character
    const strongPasswordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
    return strongPasswordRegex.test(password);
}

/**
 * Validate email format
 */
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

/**
 * Show error message
 */
function showError(elementId, message) {
    const errorElement = document.getElementById(elementId);
    if (errorElement) {
        errorElement.textContent = message;
        errorElement.style.display = 'block';
    }
}

/**
 * Clear error message
 */
function clearError(elementId) {
    const errorElement = document.getElementById(elementId);
    if (errorElement) {
        errorElement.textContent = '';
        errorElement.style.display = 'none';
    }
}

