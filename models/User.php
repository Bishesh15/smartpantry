<?php
/**
 * User Model
 * Handles user data and operations
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

class User {
    private $conn;
    private $table = 'users';

    public $id;
    public $username;
    public $email;
    public $password_hash;
    public $food_preferences;
    public $dietary_restrictions;
    public $created_at;

    public function __construct() {
        $this->conn = getDB();
    }

    /**
     * Register new user
     */
    public function register($username, $email, $password_hash, $food_preferences = '', $dietary_restrictions = '') {
        if (empty($username) || empty($email) || empty($password_hash)) {
            return ['success' => false, 'message' => 'All fields are required'];
        }

        if (!validateEmail($email)) {
            return ['success' => false, 'message' => 'Invalid email format'];
        }

        if ($this->usernameExists($username)) {
            return ['success' => false, 'message' => 'Username already exists'];
        }

        if ($this->emailExists($email)) {
            return ['success' => false, 'message' => 'Email already exists'];
        }

        $server_hash = password_hash($password_hash, PASSWORD_BCRYPT);

        $username = sanitize($username);
        $email = sanitize($email);
        $food_preferences = sanitize($food_preferences);
        $dietary_restrictions = sanitize($dietary_restrictions);

        try {
            $query = "INSERT INTO " . $this->table . " 
                      (username, email, password_hash, food_preferences, dietary_restrictions) 
                      VALUES (:username, :email, :password_hash, :food_preferences, :dietary_restrictions)";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password_hash', $server_hash);
            $stmt->bindParam(':food_preferences', $food_preferences);
            $stmt->bindParam(':dietary_restrictions', $dietary_restrictions);

            if ($stmt->execute()) {
                $user_id = $this->conn->lastInsertId();
                return ['success' => true, 'message' => 'Registration successful', 'user_id' => $user_id];
            }

            return ['success' => false, 'message' => 'Registration failed'];
        } catch (PDOException $e) {
            error_log("Registration Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Database error occurred'];
        }
    }

    /**
     * Login user
     */
    public function login($username, $password_hash) {
        if (empty($username) || empty($password_hash)) {
            return ['success' => false, 'message' => 'Username and password are required'];
        }

        $username = sanitize($username);

        try {
            $query = "SELECT id, username, email, password_hash, food_preferences, dietary_restrictions 
                      FROM " . $this->table . " 
                      WHERE username = :username LIMIT 1";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (password_verify($password_hash, $row['password_hash'])) {
                    $_SESSION['user_id'] = $row['id'];
                    $_SESSION['username'] = $row['username'];
                    $_SESSION['email'] = $row['email'];
                    $_SESSION['food_preferences'] = $row['food_preferences'];
                    $_SESSION['dietary_restrictions'] = $row['dietary_restrictions'];
                    
                    session_regenerate_id(true);

                    return [
                        'success' => true,
                        'message' => 'Login successful',
                        'user' => [
                            'id' => $row['id'],
                            'username' => $row['username'],
                            'email' => $row['email']
                        ]
                    ];
                }
            }

            return ['success' => false, 'message' => 'Invalid username or password'];
        } catch (PDOException $e) {
            error_log("Login Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Database error occurred'];
        }
    }

    /**
     * Logout user
     */
    public function logout() {
        unset($_SESSION['user_id']);
        unset($_SESSION['username']);
        unset($_SESSION['email']);
        unset($_SESSION['food_preferences']);
        unset($_SESSION['dietary_restrictions']);
    }

    /**
     * Get user by ID
     */
    public function getUserById($user_id) {
        try {
            $query = "SELECT id, username, email, food_preferences, dietary_restrictions, created_at 
                      FROM " . $this->table . " 
                      WHERE id = :id LIMIT 1";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                return $stmt->fetch(PDO::FETCH_ASSOC);
            }

            return null;
        } catch (PDOException $e) {
            error_log("Get User Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Update user preferences
     */
    public function updatePreferences($user_id, $food_preferences, $dietary_restrictions) {
        try {
            $query = "UPDATE " . $this->table . " 
                      SET food_preferences = :food_preferences, 
                          dietary_restrictions = :dietary_restrictions 
                      WHERE id = :id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':food_preferences', $food_preferences);
            $stmt->bindParam(':dietary_restrictions', $dietary_restrictions);

            if ($stmt->execute()) {
                $_SESSION['food_preferences'] = $food_preferences;
                $_SESSION['dietary_restrictions'] = $dietary_restrictions;
                return true;
            }

            return false;
        } catch (PDOException $e) {
            error_log("Update Preferences Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update username
     */
    public function updateUsername($user_id, $new_username) {
        $new_username = sanitize($new_username);
        if (empty($new_username) || strlen($new_username) < 3) {
            return ['success' => false, 'message' => 'Username must be at least 3 characters'];
        }
        if ($this->usernameExists($new_username)) {
            return ['success' => false, 'message' => 'Username already taken'];
        }
        try {
            $query = "UPDATE " . $this->table . " SET username = :username WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':username', $new_username);
            $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
            if ($stmt->execute()) {
                $_SESSION['username'] = $new_username;
                return ['success' => true, 'message' => 'Username updated successfully'];
            }
            return ['success' => false, 'message' => 'Failed to update username'];
        } catch (PDOException $e) {
            error_log("Update Username Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Database error occurred'];
        }
    }

    /**
     * Update password (expects pre-hashed from client bcrypt)
     */
    public function updatePassword($user_id, $current_password_hash, $new_password_hash) {
        try {
            // Verify current password
            $query = "SELECT password_hash FROM " . $this->table . " WHERE id = :id LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row || !password_verify($current_password_hash, $row['password_hash'])) {
                return ['success' => false, 'message' => 'Current password is incorrect'];
            }

            $server_hash = password_hash($new_password_hash, PASSWORD_BCRYPT);
            $query = "UPDATE " . $this->table . " SET password_hash = :password_hash WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':password_hash', $server_hash);
            $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Password updated successfully'];
            }
            return ['success' => false, 'message' => 'Failed to update password'];
        } catch (PDOException $e) {
            error_log("Update Password Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Database error occurred'];
        }
    }

    /**
     * Get user's favorite recipes
     */
    public function getFavorites($user_id, $limit = 10) {
        try {
            $query = "SELECT r.* FROM recipes r
                      INNER JOIN favorites f ON r.id = f.recipe_id
                      WHERE f.user_id = :user_id
                      ORDER BY f.created_at DESC
                      LIMIT :limit";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get Favorites Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get user's recent views
     */
    public function getRecentViews($user_id, $limit = 10) {
        try {
            $query = "SELECT r.*, rv.viewed_at FROM recipes r
                      INNER JOIN recent_views rv ON r.id = rv.recipe_id
                      WHERE rv.user_id = :user_id
                      ORDER BY rv.viewed_at DESC
                      LIMIT :limit";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get Recent Views Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get user stats
     */
    public function getUserStats($user_id) {
        try {
            $conn = $this->conn;
            // Favorites count
            $stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM favorites WHERE user_id = :uid");
            $stmt->execute([':uid' => $user_id]);
            $fav_count = $stmt->fetch(PDO::FETCH_ASSOC)['cnt'];

            // Ratings count
            $stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM ratings WHERE user_id = :uid");
            $stmt->execute([':uid' => $user_id]);
            $rating_count = $stmt->fetch(PDO::FETCH_ASSOC)['cnt'];

            // Recent views count
            $stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM recent_views WHERE user_id = :uid");
            $stmt->execute([':uid' => $user_id]);
            $view_count = $stmt->fetch(PDO::FETCH_ASSOC)['cnt'];

            return [
                'favorites' => (int)$fav_count,
                'ratings' => (int)$rating_count,
                'views' => (int)$view_count
            ];
        } catch (PDOException $e) {
            error_log("Get User Stats Error: " . $e->getMessage());
            return ['favorites' => 0, 'ratings' => 0, 'views' => 0];
        }
    }

    /**
     * Check if username exists
     */
    private function usernameExists($username) {
        try {
            $query = "SELECT id FROM " . $this->table . " WHERE username = :username LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Username Check Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if email exists
     */
    private function emailExists($email) {
        try {
            $query = "SELECT id FROM " . $this->table . " WHERE email = :email LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Email Check Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Find user by Google ID
     */
    public function findByGoogleId($googleId) {
        try {
            $query = "SELECT id, username, email, food_preferences, dietary_restrictions 
                      FROM " . $this->table . " 
                      WHERE google_id = :google_id LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':google_id', $googleId);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                return $stmt->fetch(PDO::FETCH_ASSOC);
            }
            return null;
        } catch (PDOException $e) {
            error_log("Find By Google ID Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Find user by email (public version)
     */
    public function findByEmail($email) {
        try {
            $query = "SELECT id, username, email, food_preferences, dietary_restrictions 
                      FROM " . $this->table . " 
                      WHERE email = :email LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                return $stmt->fetch(PDO::FETCH_ASSOC);
            }
            return null;
        } catch (PDOException $e) {
            error_log("Find By Email Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Find user by username (public version)
     */
    public function findByUsername($username) {
        try {
            $query = "SELECT id, username FROM " . $this->table . " WHERE username = :username LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                return $stmt->fetch(PDO::FETCH_ASSOC);
            }
            return null;
        } catch (PDOException $e) {
            error_log("Find By Username Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Link Google account to existing user
     */
    public function linkGoogleAccount($userId, $googleId) {
        try {
            $query = "UPDATE " . $this->table . " SET google_id = :google_id WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':google_id', $googleId);
            $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Link Google Account Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Register new user via Google OAuth
     */
    public function registerWithGoogle($username, $email, $googleId) {
        if (empty($username) || empty($email) || empty($googleId)) {
            return ['success' => false, 'message' => 'Missing required information from Google'];
        }

        if ($this->usernameExists($username)) {
            return ['success' => false, 'message' => 'Username already exists. Please try again.'];
        }

        if ($this->emailExists($email)) {
            return ['success' => false, 'message' => 'Email already registered. Please login instead.'];
        }

        $username = sanitize($username);
        $email = sanitize($email);

        // Generate a random password hash for Google users (they won't use it)
        $randomPassword = bin2hex(random_bytes(32));
        $passwordHash = password_hash($randomPassword, PASSWORD_BCRYPT);

        try {
            $query = "INSERT INTO " . $this->table . " 
                      (username, email, password_hash, google_id, food_preferences, dietary_restrictions) 
                      VALUES (:username, :email, :password_hash, :google_id, '', '')";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password_hash', $passwordHash);
            $stmt->bindParam(':google_id', $googleId);

            if ($stmt->execute()) {
                $userId = $this->conn->lastInsertId();
                return ['success' => true, 'message' => 'Registration successful', 'user_id' => $userId];
            }

            return ['success' => false, 'message' => 'Registration failed'];
        } catch (PDOException $e) {
            error_log("Google Registration Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Database error occurred'];
        }
    }

    /**
     * Get total users count
     */
    public function getTotalUsers() {
        try {
            $query = "SELECT COUNT(*) as total FROM " . $this->table;
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)$result['total'];
        } catch (PDOException $e) {
            error_log("Get Total Users Error: " . $e->getMessage());
            return 0;
        }
    }
}
?>

