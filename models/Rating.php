<?php
/**
 * Rating Model
 * Smart Pantry
 */

require_once __DIR__ . '/../config/database.php';

class Rating {
    private ?PDO   $db;
    private string $table = 'ratings';

    public function __construct() { $this->db = getDB(); }

    public function addOrUpdate(int $userId, int $recipeId, int $rating, string $comment = ''): array {
        try {
            $st = $this->db->prepare(
                "INSERT INTO {$this->table} (user_id, recipe_id, rating, comment)
                 VALUES (?,?,?,?)
                 ON DUPLICATE KEY UPDATE rating=?, comment=?"
            );
            $st->execute([$userId, $recipeId, $rating, $comment, $rating, $comment]);
            $this->updateRecipeAverage($recipeId);
            return ['success' => true, 'message' => 'Rating saved.'];
        } catch (PDOException $e) {
            error_log('Rating::addOrUpdate ' . $e->getMessage());
            return ['success' => false, 'message' => 'Could not save rating.'];
        }
    }

    public function getUserRating(int $userId, int $recipeId): ?array {
        $st = $this->db->prepare(
            "SELECT * FROM {$this->table} WHERE user_id=? AND recipe_id=? LIMIT 1"
        );
        $st->execute([$userId, $recipeId]);
        return $st->fetch() ?: null;
    }

    public function getRecipeRatings(int $recipeId, int $limit = 20): array {
        $st = $this->db->prepare(
            "SELECT r.*, u.username, u.full_name
             FROM {$this->table} r
             JOIN users u ON r.user_id = u.id
             WHERE r.recipe_id = ?
             ORDER BY r.created_at DESC
             LIMIT ?"
        );
        $st->execute([$recipeId, $limit]);
        return $st->fetchAll();
    }

    public function getTotalCount(): int {
        return (int) $this->db->query("SELECT COUNT(*) FROM {$this->table}")->fetchColumn();
    }

    private function updateRecipeAverage(int $recipeId): void {
        $this->db->prepare(
            "UPDATE recipes
             SET average_rating = (SELECT AVG(rating) FROM {$this->table} WHERE recipe_id = ?),
                 total_ratings  = (SELECT COUNT(*)    FROM {$this->table} WHERE recipe_id = ?)
             WHERE id = ?"
        )->execute([$recipeId, $recipeId, $recipeId]);
    }
}
