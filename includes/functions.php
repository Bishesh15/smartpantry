<?php
/**
 * Global Helper Functions
 * Smart Pantry – A Recipe Recommendation System
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';

/* ── Auth Helpers ──────────────────────────────────────────── */

function isLoggedIn(): bool {
    return !empty($_SESSION['user_id']);
}

function isAdmin(): bool {
    return !empty($_SESSION['admin_id']);
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        redirect(BASE_URL . 'views/user/login.php');
    }
}

function requireAdmin(): void {
    if (!isAdmin()) {
        redirect(BASE_URL . 'views/admin/login.php');
    }
}

/* ── Redirect ──────────────────────────────────────────────── */

function redirect(string $url): void {
    header('Location: ' . $url);
    exit();
}

/* ── Input / Sanitize ──────────────────────────────────────── */

function sanitize(mixed $data): string {
    if (!is_string($data)) return '';
    return htmlspecialchars(trim(stripslashes($data)), ENT_QUOTES, 'UTF-8');
}

function validateEmail(string $email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function validateUsername(string $u): bool {
    return (bool) preg_match('/^[a-zA-Z0-9_]{3,30}$/', $u);
}

function validateInteger(mixed $v, ?int $min = null, ?int $max = null): int|false {
    $i = filter_var($v, FILTER_VALIDATE_INT);
    if ($i === false) return false;
    if ($min !== null && $i < $min) return false;
    if ($max !== null && $i > $max) return false;
    return $i;
}

function validateFloat(mixed $v, ?float $min = null, ?float $max = null): float|false {
    $f = filter_var($v, FILTER_VALIDATE_FLOAT);
    if ($f === false) return false;
    if ($min !== null && $f < $min) return false;
    if ($max !== null && $f > $max) return false;
    return $f;
}

/* ── CSRF ──────────────────────────────────────────────────── */

function generateCSRFToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken(string $token): bool {
    return !empty($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

/* ── Date / Time Helpers ───────────────────────────────────── */

function formatDate(string $d): string {
    return date('F j, Y', strtotime($d));
}

function timeAgo(string $datetime): string {
    $diff = time() - strtotime($datetime);
    if ($diff < 60)     return 'just now';
    if ($diff < 3600)   return floor($diff/60)   . ' min ago';
    if ($diff < 86400)  return floor($diff/3600)  . ' hr ago';
    if ($diff < 604800) return floor($diff/86400) . ' days ago';
    return formatDate($datetime);
}

/* ── Text Helpers ──────────────────────────────────────────── */

function truncate(string $text, int $len = 120): string {
    return mb_strlen($text) <= $len
        ? $text
        : mb_substr($text, 0, $len) . '…';
}

/* ── Image Upload ──────────────────────────────────────────── */

/**
 * Upload an image file.
 * Returns relative path on success (e.g. "uploads/recipes/abc123.jpg"), false on failure.
 */
function uploadImage(array $file, string $subfolder = 'recipes'): string|false {
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) return false;
    if ($file['size'] > MAX_FILE_SIZE) return false;

    $finfo    = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, ALLOWED_IMAGE_TYPES)) return false;

    $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('img_', true) . '.' . strtolower($ext);
    $dir      = UPLOADS_PATH . $subfolder . '/';

    if (!is_dir($dir)) mkdir($dir, 0755, true);

    $destPath = $dir . $filename;
    if (!move_uploaded_file($file['tmp_name'], $destPath)) return false;

    return 'uploads/' . $subfolder . '/' . $filename;
}

function deleteImage(string $relativePath): bool {
    if (empty($relativePath)) return false;
    $full = ROOT_PATH . 'assets/images/' . $relativePath;
    return file_exists($full) ? unlink($full) : false;
}

/* ── Image URL resolution ──────────────────────────────────── */

/**
 * Returns a usable <img src="…"> URL.
 * Priority: external URL → uploaded file → default placeholder.
 */
function resolveImageUrl(string $imageField, string $type = 'recipes'): string {
    if (empty($imageField)) {
        return ASSETS_PATH . 'images/default-' . $type . '.jpg';
    }
    // External URL (starts with http/https)
    if (str_starts_with($imageField, 'http')) {
        return $imageField;
    }
    // Uploaded file (relative path stored without leading slash)
    return ASSETS_PATH . 'images/' . ltrim($imageField, '/');
}

/* ── Flash Messages ────────────────────────────────────────── */

function flashSuccess(string $msg): void { $_SESSION['flash_success'] = $msg; }
function flashError(string $msg): void   { $_SESSION['flash_error']   = $msg; }
function flashInfo(string $msg): void    { $_SESSION['flash_info']    = $msg; }

function renderFlash(): string {
    $html = '';
    if (!empty($_SESSION['flash_success'])) {
        $html .= '<div class="alert alert-success alert-dismissible fade show" role="alert">'
               . htmlspecialchars($_SESSION['flash_success'])
               . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
        unset($_SESSION['flash_success']);
    }
    if (!empty($_SESSION['flash_error'])) {
        $html .= '<div class="alert alert-danger alert-dismissible fade show" role="alert">'
               . htmlspecialchars($_SESSION['flash_error'])
               . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
        unset($_SESSION['flash_error']);
    }
    if (!empty($_SESSION['flash_info'])) {
        $html .= '<div class="alert alert-info alert-dismissible fade show" role="alert">'
               . htmlspecialchars($_SESSION['flash_info'])
               . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
        unset($_SESSION['flash_info']);
    }
    return $html;
}

/* ── Misc ──────────────────────────────────────────────────── */

function getFoodPreferences(string $p): array {
    return !empty($p) ? array_filter(array_map('trim', explode(',', $p))) : [];
}

function getDietaryRestrictions(string $r): array {
    return !empty($r) ? array_filter(array_map('trim', explode(',', $r))) : [];
}

function dietBadgeClass(string $dietType): string {
    return match($dietType) {
        'Vegan'           => 'badge-vegan',
        'Vegetarian'      => 'badge-veg',
        'Non-Vegetarian'  => 'badge-nonveg',
        default           => 'badge-secondary',
    };
}

function dietBadgeIcon(string $dietType): string {
    return match($dietType) {
        'Vegan'           => '🌱',
        'Vegetarian'      => '🥦',
        'Non-Vegetarian'  => '🍗',
        default           => '🍽️',
    };
}

/**
 * Returns a list of substitutes for a given ingredient.
 */
function getIngredientSubstitutes(string $name): array {
    $subs = [
        'Butter'      => ['Olive Oil (1:1 Ratio)', 'Margarine (1:1 Ratio)', 'Coconut Oil (melted)'],
        'Milk'        => ['Soy Milk', 'Almond Milk', 'Coconut Milk', 'Greek Yogurt (diluted)'],
        'Egg'         => ['Mashed Banana (1/4 cup per egg)', 'Applesauce (1/4 cup per egg)', 'Silken Tofu'],
        'Paneer'      => ['Extra Firm Tofu', 'Halloumi Cheese', 'Ricotta (firm)'],
        'Chicken'     => ['Firm Tofu Cubes', 'Jackfruit', 'Mushrooms (large)', 'Seitan'],
        'Soy Sauce'   => ['Tamari (Gluten-free)', 'Coconut Aminos', 'Worcestershire Sauce'],
        'Honey'       => ['Maple Syrup', 'Agave Nectar', 'Brown Sugar'],
        'Onion'       => ['Shallots', 'Green Onions (scallions)', 'Leeks'],
        'Garlic'      => ['Garlic Powder (1/8 tsp per clove)', 'Shallots', 'Garlic Chives'],
        'Sugar'       => ['Brown Sugar', 'Maple Syrup', 'Honey (3/4 cup per 1 cup sugar)'],
        'Yogurt'      => ['Sour Cream', 'Greek Yogurt', 'Mayonnaise (for baking)', 'Coconut Cream'],
        'Lemon'       => ['Lime Juice', 'Orange Juice', 'White Wine Vinegar', 'Apple Cider Vinegar'],
        'Vinegar'     => ['Lemon Juice', 'Lime Juice', 'White Wine'],
        'Olive Oil'   => ['Vegetable Oil', 'Grapeseed Oil', 'Avocado Oil'],
        'Spinach'     => ['Kale', 'Swiss Chard', 'Arugula'],
        'Potato'      => ['Sweet Potato', 'Yam', 'Cauliflower (for mash)'],
        'Pasta'       => ['Zucchini Noodles', 'Rice Noodles', 'Quinoa'],
        'Brown Sugar' => ['Granulated Sugar + 1 tbsp Molasses', 'Coconut Sugar'],
        'Cream'       => ['Evaporated Milk', 'Full-fat Coconut Milk', 'Greek Yogurt'],
        'Rice'        => ['Quinoa', 'Cauliflower Rice', 'Bulgur Wheat'],
        'Bread'       => ['Tortillas', 'Lettuce Wraps (for low carb)', 'Gluten-free Bread'],
    ];

    $name = trim($name);
    // Partial matching (e.g. "Unsalted Butter" -> "Butter")
    foreach ($subs as $ing => $options) {
        if (stripos($name, $ing) !== false) return $options;
    }

    return [];
}