<?php
/**
 * Helper Functions
 * Common utility functions used throughout the application
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';

/**
 * Sanitize input data
 */
function sanitize($data) {
    if (!is_string($data)) {
        return '';
    }
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Validate email format
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate username format
 */
function validateUsername($username) {
    return preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username) === 1;
}

/**
 * Validate password strength
 */
function validatePasswordStrength($password) {
    return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $password) === 1;
}

/**
 * Validate integer input
 */
function validateInteger($value, $min = null, $max = null) {
    $int = filter_var($value, FILTER_VALIDATE_INT);
    if ($int === false) {
        return false;
    }
    if ($min !== null && $int < $min) {
        return false;
    }
    if ($max !== null && $int > $max) {
        return false;
    }
    return $int;
}

/**
 * Validate float input
 */
function validateFloat($value, $min = null, $max = null) {
    $float = filter_var($value, FILTER_VALIDATE_FLOAT);
    if ($float === false) {
        return false;
    }
    if ($min !== null && $float < $min) {
        return false;
    }
    if ($max !== null && $float > $max) {
        return false;
    }
    return $float;
}

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Redirect to a URL
 */
function redirect($url) {
    header("Location: " . $url);
    exit();
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if user is admin
 */
function isAdmin() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

/**
 * Require user to be logged in
 */
function requireLogin() {
    if (!isLoggedIn()) {
        redirect(BASE_URL . 'views/user/login.php');
    }
}

/**
 * Require admin access
 */
function requireAdmin() {
    if (!isAdmin()) {
        redirect(BASE_URL . 'views/admin/login.php');
    }
}

/**
 * Format date for display
 */
function formatDate($date) {
    return date('F j, Y', strtotime($date));
}

/**
 * Format datetime for display
 */
function formatDateTime($datetime) {
    return date('F j, Y, g:i a', strtotime($datetime));
}

/**
 * Calculate time ago
 */
function timeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;
    
    if ($diff < 60) {
        return 'just now';
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return $mins . ' minute' . ($mins > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } else {
        return formatDate($datetime);
    }
}

/**
 * Truncate text
 */
function truncate($text, $length = 100) {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . '...';
}

/**
 * Display star rating
 */
function displayStars($rating, $maxRating = 5) {
    $html = '<div class="star-rating">';
    $fullStars = floor($rating);
    $halfStar = ($rating - $fullStars) >= 0.5;
    
    for ($i = 1; $i <= $maxRating; $i++) {
        if ($i <= $fullStars) {
            $html .= '<span class="star filled">★</span>';
        } elseif ($i == $fullStars + 1 && $halfStar) {
            $html .= '<span class="star half">★</span>';
        } else {
            $html .= '<span class="star">★</span>';
        }
    }
    
    $html .= '</div>';
    return $html;
}

/**
 * Get user's food preferences as array
 */
function getFoodPreferences($preferences) {
    if (empty($preferences)) {
        return [];
    }
    return explode(',', $preferences);
}

/**
 * Get user's dietary restrictions as array
 */
function getDietaryRestrictions($restrictions) {
    if (empty($restrictions)) {
        return [];
    }
    return explode(',', $restrictions);
}

/**
 * Check if recipe matches dietary restrictions
 */
function matchesDietaryRestrictions($restrictions, $recipeCategory) {
    if (empty($restrictions) || in_array('None', $restrictions)) {
        return true;
    }
    
    if (in_array('Vegetarian', $restrictions) && strpos($recipeCategory, 'Meat') !== false) {
        return false;
    }
    
    return true;
}

/**
 * Upload image file
 */
function uploadImage($file, $directory = 'images') {
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    if ($file['size'] > MAX_FILE_SIZE) {
        return false;
    }
    
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, ALLOWED_IMAGE_TYPES)) {
        return false;
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '.' . $extension;
    $uploadPath = IMAGES_PATH . $directory . '/' . $filename;
    
    $uploadDir = dirname($uploadPath);
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        return $directory . '/' . $filename;
    }
    
    return false;
}

/**
 * Delete image file
 */
function deleteImage($imagePath) {
    $fullPath = IMAGES_PATH . $imagePath;
    if (file_exists($fullPath)) {
        return unlink($fullPath);
    }
    return false;
}
?>