<?php
/**
 * User Model
 * Smart Pantry – Handles user CRUD, pantry, favorites, recently viewed
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

class User {
    private ?PDO   $db;
    private string $table = 'users';

    public function __construct() { $this->db = getDB(); }

    /* ── Auth ───────────────────────────────────────────────── */

    public function register(array $data): array {
        $username  = sanitize($data['username']  ?? '');
        $fullName  = sanitize($data['full_name'] ?? '');
        $email     = sanitize($data['email']     ?? '');
        $password  = $data['password'] ?? '';

        if (empty($username) || empty($fullName) || empty($email) || empty($password)) {
            return ['success' => false, 'message' => 'All fields are required.'];
        }
        if (!validateUsername($username)) {
            return ['success' => false, 'message' => 'Username must be 3–30 characters (letters, numbers, underscore).'];
        }
        if (!validateEmail($email)) {
            return ['success' => false, 'message' => 'Invalid email address.'];
        }
        if (strlen($password) < 6) {
            return ['success' => false, 'message' => 'Password must be at least 6 characters.'];
        }
        if ($this->existsByColumn('username', $username)) {
            return ['success' => false, 'message' => 'Username already taken.'];
        }
        if ($this->existsByColumn('email', $email)) {
            return ['success' => false, 'message' => 'Email already registered.'];
        }

        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $st   = $this->db->prepare(
            "INSERT INTO {$this->table} (full_name, username, email, password) VALUES (?,?,?,?)"
        );
        $st->execute([$fullName, $username, $email, $hash]);
        return ['success' => true, 'message' => 'Registration successful! Please log in.', 'user_id' => (int)$this->db->lastInsertId()];
    }

    public function login(string $username, string $password): array {
        $username = sanitize($username);
        if (empty($username) || empty($password)) {
            return ['success' => false, 'message' => 'Username and password are required.'];
        }
        $st = $this->db->prepare(
            "SELECT * FROM {$this->table} WHERE username = ? LIMIT 1"
        );
        $st->execute([$username]);
        $user = $st->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            return ['success' => false, 'message' => 'Invalid username or password.'];
        }
        if ($user['status'] === 'deactivated') {
            return ['success' => false, 'message' => 'Your account has been deactivated. Contact support.'];
        }

        return $this->establishSession($user);
    }

    public function findByGoogleId(string $googleId): ?array {
        $st = $this->db->prepare("SELECT * FROM {$this->table} WHERE google_id = ? LIMIT 1");
        $st->execute([$googleId]);
        return $st->fetch() ?: null;
    }

    public function findByEmail(string $email): ?array {
        $st = $this->db->prepare("SELECT * FROM {$this->table} WHERE email = ? LIMIT 1");
        $st->execute([$email]);
        return $st->fetch() ?: null;
    }

    public function linkGoogleAccount(int $userId, string $googleId): bool {
        $st = $this->db->prepare("UPDATE {$this->table} SET google_id = ? WHERE id = ?");
        return $st->execute([$googleId, $userId]);
    }

    public function registerGoogleUser(array $data): array {
        $email    = sanitize($data['email']);
        $fullName = sanitize($data['full_name']);
        $googleId = sanitize($data['google_id']);
        
        // Auto-generate a username from email (e.g. sita_devi@test.com -> sita_devi_g)
        $username = explode('@', $email)[0] . '_g';
        
        // Ensure username is unique
        $original = $username;
        $count = 1;
        while ($this->existsByColumn('username', $username)) {
            $username = $original . $count++;
        }

        $st = $this->db->prepare(
            "INSERT INTO {$this->table} (full_name, username, email, google_id) VALUES (?,?,?,?)"
        );
        $st->execute([$fullName, $username, $email, $googleId]);
        
        $userId = (int)$this->db->lastInsertId();
        $user = $this->getById($userId);
        return $this->establishSession($user);
    }

    public function establishSession(array $user): array {
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['username']  = $user['username'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['email']     = $user['email'];
        $_SESSION['food_preferences']    = $user['food_preferences']    ?? '';
        $_SESSION['dietary_restrictions']= $user['dietary_restrictions'] ?? '';

        session_regenerate_id(true);
        return ['success' => true, 'message' => 'Login successful!', 'user' => $user];
    }

    /* ── Getters ────────────────────────────────────────────── */

    public function getById(int $id): ?array {
        $st = $this->db->prepare(
            "SELECT id, full_name, username, email, food_preferences,
                    dietary_restrictions, daily_calorie_goal, status, created_at
             FROM {$this->table} WHERE id = ? LIMIT 1"
        );
        $st->execute([$id]);
        return $st->fetch() ?: null;
    }

    public function getAll(int $limit = USERS_PER_PAGE, int $offset = 0): array {
        $st = $this->db->prepare(
            "SELECT id, full_name, username, email, status, created_at
             FROM {$this->table}
             ORDER BY created_at DESC
             LIMIT ? OFFSET ?"
        );
        $st->execute([$limit, $offset]);
        return $st->fetchAll();
    }

    public function getTotalCount(): int {
        return (int) $this->db->query("SELECT COUNT(*) FROM {$this->table}")->fetchColumn();
    }

    /* ── Update ─────────────────────────────────────────────── */

    public function updateProfile(int $id, array $data): array {
        $fullName = sanitize($data['full_name'] ?? '');
        if (empty($fullName)) {
            return ['success' => false, 'message' => 'Full name cannot be empty.'];
        }
        $st = $this->db->prepare(
            "UPDATE {$this->table} SET full_name=?, food_preferences=?, dietary_restrictions=?,
             daily_calorie_goal=? WHERE id=?"
        );
        $st->execute([
            $fullName,
            sanitize($data['food_preferences']    ?? ''),
            sanitize($data['dietary_restrictions'] ?? ''),
            (int) ($data['daily_calorie_goal'] ?? 2000),
            $id,
        ]);
        // Sync session
        $_SESSION['full_name']             = $fullName;
        $_SESSION['food_preferences']      = $data['food_preferences']    ?? '';
        $_SESSION['dietary_restrictions']  = $data['dietary_restrictions'] ?? '';
        return ['success' => true, 'message' => 'Profile updated successfully.'];
    }

    public function updatePassword(int $id, string $current, string $newPass): array {
        if (strlen($newPass) < 6) {
            return ['success' => false, 'message' => 'New password must be at least 6 characters.'];
        }
        $st = $this->db->prepare("SELECT password FROM {$this->table} WHERE id=?");
        $st->execute([$id]);
        $row = $st->fetch();
        if (!$row || !password_verify($current, $row['password'])) {
            return ['success' => false, 'message' => 'Current password is incorrect.'];
        }
        $hash = password_hash($newPass, PASSWORD_BCRYPT, ['cost' => 12]);
        $this->db->prepare("UPDATE {$this->table} SET password=? WHERE id=?")->execute([$hash, $id]);
        return ['success' => true, 'message' => 'Password changed successfully.'];
    }

    /* ── Admin: User Management ─────────────────────────────── */

    public function deactivate(int $id): bool {
        return $this->db->prepare(
            "UPDATE {$this->table} SET status='deactivated' WHERE id=?"
        )->execute([$id]);
    }

    public function activate(int $id): bool {
        return $this->db->prepare(
            "UPDATE {$this->table} SET status='active' WHERE id=?"
        )->execute([$id]);
    }

    public function delete(int $id): bool {
        return $this->db->prepare("DELETE FROM {$this->table} WHERE id=?")->execute([$id]);
    }

    /* ── Pantry ─────────────────────────────────────────────── */

    public function getPantry(int $userId): array {
        $st = $this->db->prepare(
            "SELECT i.*, p.quantity, p.added_at
             FROM ingredients i
             JOIN pantry_items p ON i.id = p.ingredient_id
             WHERE p.user_id = ?
             ORDER BY i.category, i.name"
        );
        $st->execute([$userId]);
        return $st->fetchAll();
    }

    public function getPantryIngredientIds(int $userId): array {
        $st = $this->db->prepare(
            "SELECT ingredient_id FROM pantry_items WHERE user_id = ?"
        );
        $st->execute([$userId]);
        return $st->fetchAll(PDO::FETCH_COLUMN, 0);
    }

    public function addToPantry(int $userId, int $ingredientId, float $qty = 1): bool {
        $st = $this->db->prepare(
            "INSERT INTO pantry_items (user_id, ingredient_id, quantity)
             VALUES (?,?,?)
             ON DUPLICATE KEY UPDATE quantity = ?"
        );
        return $st->execute([$userId, $ingredientId, $qty, $qty]);
    }

    public function removeFromPantry(int $userId, int $ingredientId): bool {
        return $this->db->prepare(
            "DELETE FROM pantry_items WHERE user_id=? AND ingredient_id=?"
        )->execute([$userId, $ingredientId]);
    }

    public function updatePantryQuantity(int $userId, int $ingredientId, float $qty): bool {
        return $this->db->prepare(
            "UPDATE pantry_items SET quantity=? WHERE user_id=? AND ingredient_id=?"
        )->execute([$qty, $userId, $ingredientId]);
    }

    public function clearPantry(int $userId): bool {
        return $this->db->prepare(
            "DELETE FROM pantry_items WHERE user_id=?"
        )->execute([$userId]);
    }

    /* ── Favorites ──────────────────────────────────────────── */

    public function getFavorites(int $userId, int $limit = 50): array {
        $st = $this->db->prepare(
            "SELECT r.*, f.created_at AS saved_at
             FROM recipes r
             JOIN favorites f ON r.id = f.recipe_id
             WHERE f.user_id = ?
             ORDER BY f.created_at DESC
             LIMIT ?"
        );
        $st->execute([$userId, $limit]);
        return $st->fetchAll();
    }

    public function isFavorited(int $userId, int $recipeId): bool {
        $st = $this->db->prepare(
            "SELECT id FROM favorites WHERE user_id=? AND recipe_id=? LIMIT 1"
        );
        $st->execute([$userId, $recipeId]);
        return (bool) $st->fetch();
    }

    public function addFavorite(int $userId, int $recipeId): bool {
        $st = $this->db->prepare(
            "INSERT IGNORE INTO favorites (user_id, recipe_id) VALUES (?,?)"
        );
        return $st->execute([$userId, $recipeId]);
    }

    public function removeFavorite(int $userId, int $recipeId): bool {
        return $this->db->prepare(
            "DELETE FROM favorites WHERE user_id=? AND recipe_id=?"
        )->execute([$userId, $recipeId]);
    }

    /* ── Recently Viewed ────────────────────────────────────── */

    public function recordView(int $userId, int $recipeId): void {
        $this->db->prepare(
            "INSERT INTO recently_viewed (user_id, recipe_id)
             VALUES (?,?)
             ON DUPLICATE KEY UPDATE viewed_at = CURRENT_TIMESTAMP"
        )->execute([$userId, $recipeId]);
    }

    public function getRecentlyViewed(int $userId, int $limit = 10): array {
        $st = $this->db->prepare(
            "SELECT r.*, rv.viewed_at
             FROM recipes r
             JOIN recently_viewed rv ON r.id = rv.recipe_id
             WHERE rv.user_id = ?
             ORDER BY rv.viewed_at DESC
             LIMIT ?"
        );
        $st->execute([$userId, $limit]);
        return $st->fetchAll();
    }

    /* ── Stats ──────────────────────────────────────────────── */

    public function getStats(int $userId): array {
        $favCount = $this->db->prepare("SELECT COUNT(*) FROM favorites WHERE user_id=?");
        $favCount->execute([$userId]);

        $viewCount = $this->db->prepare("SELECT COUNT(*) FROM recently_viewed WHERE user_id=?");
        $viewCount->execute([$userId]);

        $ratingCount = $this->db->prepare("SELECT COUNT(*) FROM ratings WHERE user_id=?");
        $ratingCount->execute([$userId]);

        $pantryCount = $this->db->prepare("SELECT COUNT(*) FROM pantry_items WHERE user_id=?");
        $pantryCount->execute([$userId]);

        return [
            'favorites'  => (int) $favCount->fetchColumn(),
            'views'      => (int) $viewCount->fetchColumn(),
            'ratings'    => (int) $ratingCount->fetchColumn(),
            'pantry'     => (int) $pantryCount->fetchColumn(),
        ];
    }

    /* ── Private ────────────────────────────────────────────── */

    private function existsByColumn(string $col, string $val): bool {
        $st = $this->db->prepare("SELECT id FROM {$this->table} WHERE $col = ? LIMIT 1");
        $st->execute([$val]);
        return (bool) $st->fetch();
    }
}
