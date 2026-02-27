<?php
/**
 * Rating Model
 * Handles recipe ratings and comments
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

class Rating {
    private $conn;
    private $table = 'ratings';

    public $id;
    public $user_id;
    public $recipe_id;
    public $rating;
    public $comment;
    public $created_at;

    public function __construct() {
        $this->conn = getDB();
    }

    /**
     * Add or update rating
     * @param int $user_id
     * @param int $recipe_id
     * @param int $rating
     * @param string $comment
     * @return array ['success' => bool, 'message' => string]
     */
    public function addRating($user_id, $recipe_id, $rating, $comment = '') {
        // Validate rating
        if ($rating < MIN_RATING || $rating > MAX_RATING) {
            return ['success' => false, 'message' => 'Invalid rating value'];
        }

        try {
            // Check if rating already exists
            $existing = $this->getUserRating($user_id, $recipe_id);
            
            if ($existing) {
                // Update existing rating
                $query = "UPDATE " . $this->table . " 
                          SET rating = :rating, comment = :comment 
                          WHERE user_id = :user_id AND recipe_id = :recipe_id";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                $stmt->bindParam(':recipe_id', $recipe_id, PDO::PARAM_INT);
                $stmt->bindParam(':rating', $rating, PDO::PARAM_INT);
                $stmt->bindParam(':comment', $comment);
            } else {
                // Insert new rating
                $query = "INSERT INTO " . $this->table . " 
                          (user_id, recipe_id, rating, comment) 
                          VALUES (:user_id, :recipe_id, :rating, :comment)";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                $stmt->bindParam(':recipe_id', $recipe_id, PDO::PARAM_INT);
                $stmt->bindParam(':rating', $rating, PDO::PARAM_INT);
                $stmt->bindParam(':comment', $comment);
            }

            if ($stmt->execute()) {
                // Update recipe average rating
                $this->updateRecipeRating($recipe_id);
                return ['success' => true, 'message' => 'Rating saved successfully'];
            }

            return ['success' => false, 'message' => 'Failed to save rating'];
        } catch (PDOException $e) {
            error_log("Add Rating Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Database error occurred'];
        }
    }

    /**
     * Get user's rating for a recipe
     * @param int $user_id
     * @param int $recipe_id
     * @return array|null
     */
    public function getUserRating($user_id, $recipe_id) {
        try {
            $query = "SELECT * FROM " . $this->table . " 
                      WHERE user_id = :user_id AND recipe_id = :recipe_id LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':recipe_id', $recipe_id, PDO::PARAM_INT);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                return $stmt->fetch(PDO::FETCH_ASSOC);
            }
            return null;
        } catch (PDOException $e) {
            error_log("Get User Rating Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get all ratings for a recipe
     * @param int $recipe_id
     * @return array
     */
    public function getRecipeRatings($recipe_id) {
        try {
            $query = "SELECT r.*, u.username 
                      FROM " . $this->table . " r
                      INNER JOIN users u ON r.user_id = u.id
                      WHERE r.recipe_id = :recipe_id 
                      ORDER BY r.created_at DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':recipe_id', $recipe_id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get Recipe Ratings Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Update recipe average rating
     * @param int $recipe_id
     * @return bool
     */
    private function updateRecipeRating($recipe_id) {
        try {
            $query = "UPDATE recipes r
                      SET average_rating = (
                          SELECT COALESCE(AVG(rating), 0)
                          FROM " . $this->table . "
                          WHERE recipe_id = r.id
                      ),
                      total_ratings = (
                          SELECT COUNT(*)
                          FROM " . $this->table . "
                          WHERE recipe_id = r.id
                      )
                      WHERE r.id = :recipe_id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':recipe_id', $recipe_id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Update Recipe Rating Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get total ratings count
     * @return int
     */
    public function getTotalCount() {
        try {
            $query = "SELECT COUNT(*) as total FROM " . $this->table;
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)$result['total'];
        } catch (PDOException $e) {
            error_log("Get Total Ratings Error: " . $e->getMessage());
            return 0;
        }
    }
}

