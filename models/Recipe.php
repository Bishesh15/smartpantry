<?php
/**
 * Recipe Model
 * Handles recipe CRUD operations and recipe matching
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

class Recipe {
    private $conn;
    private $table = 'recipes';

    public $id;
    public $name;
    public $description;
    public $instructions;
    public $prep_time;
    public $image_url;
    public $calories;
    public $category;
    public $average_rating;
    public $total_ratings;

    public function __construct() {
        $this->conn = getDB();
    }

    /**
     * Get all recipes
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getAll($limit = 100, $offset = 0) {
        try {
            $query = "SELECT * FROM " . $this->table . " 
                      ORDER BY created_at DESC 
                      LIMIT :limit OFFSET :offset";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get All Recipes Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get recipe by ID
     * @param int $id
     * @return array|null
     */
    public function getById($id) {
        try {
            $query = "SELECT * FROM " . $this->table . " WHERE id = :id LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                return $stmt->fetch(PDO::FETCH_ASSOC);
            }
            return null;
        } catch (PDOException $e) {
            error_log("Get Recipe Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get recipes matching selected ingredients
     * @param array $ingredient_ids
     * @param array $user_preferences
     * @param array $dietary_restrictions
     * @return array
     */
    public function getMatchingRecipes($ingredient_ids, $user_preferences = [], $dietary_restrictions = []) {
        if (empty($ingredient_ids)) {
            return [];
        }

        try {
            $placeholders = implode(',', array_fill(0, count($ingredient_ids), '?'));
            
            $query = "SELECT r.*, 
                      COUNT(DISTINCT ri.ingredient_id) as match_count,
                      COUNT(DISTINCT ri2.ingredient_id) as total_ingredients
                      FROM " . $this->table . " r
                      INNER JOIN recipe_ingredients ri ON r.id = ri.recipe_id
                      LEFT JOIN recipe_ingredients ri2 ON r.id = ri2.recipe_id
                      WHERE ri.ingredient_id IN ($placeholders)
                      GROUP BY r.id
                      HAVING match_count > 0
                      ORDER BY (match_count / total_ingredients) DESC, match_count DESC, r.average_rating DESC";

            $stmt = $this->conn->prepare($query);
            $stmt->execute($ingredient_ids);
            $recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Filter by user preferences if provided
            if (!empty($user_preferences)) {
                $recipes = array_filter($recipes, function($recipe) use ($user_preferences) {
                    return in_array($recipe['category'], $user_preferences);
                });
            }

            // Filter by dietary restrictions
            if (!empty($dietary_restrictions) && !in_array('None', $dietary_restrictions)) {
                // This is a simplified filter - can be enhanced
                $recipes = array_filter($recipes, function($recipe) use ($dietary_restrictions) {
                    return matchesDietaryRestrictions($dietary_restrictions, $recipe['category']);
                });
            }

            return array_values($recipes);
        } catch (PDOException $e) {
            error_log("Get Matching Recipes Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Search recipes by name or description
     * @param string $search_term
     * @return array
     */
    public function search($search_term) {
        if (empty($search_term)) {
            return [];
        }

        try {
            $search_term = '%' . sanitize($search_term) . '%';
            $query = "SELECT * FROM " . $this->table . " 
                      WHERE name LIKE :search 
                      OR description LIKE :search 
                      OR category LIKE :search
                      ORDER BY average_rating DESC, name ASC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':search', $search_term);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Search Recipes Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get recipe ingredients
     * @param int $recipe_id
     * @return array
     */
    public function getIngredients($recipe_id) {
        try {
            $query = "SELECT i.*, ri.quantity 
                      FROM ingredients i
                      INNER JOIN recipe_ingredients ri ON i.id = ri.ingredient_id
                      WHERE ri.recipe_id = :recipe_id
                      ORDER BY i.name";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':recipe_id', $recipe_id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get Recipe Ingredients Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Create new recipe
     * @param array $data
     * @return array ['success' => bool, 'message' => string, 'recipe_id' => int|null]
     */
    public function create($data) {
        try {
            $query = "INSERT INTO " . $this->table . " 
                      (name, description, instructions, prep_time, image_url, category) 
                      VALUES (:name, :description, :instructions, :prep_time, :image_url, :category)";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':description', $data['description']);
            $stmt->bindParam(':instructions', $data['instructions']);
            $stmt->bindParam(':prep_time', $data['prep_time'], PDO::PARAM_INT);
            $stmt->bindParam(':image_url', $data['image_url']);
            $stmt->bindParam(':category', $data['category']);

            if ($stmt->execute()) {
                $recipe_id = $this->conn->lastInsertId();
                
                // Add ingredients if provided
                if (isset($data['ingredients']) && is_array($data['ingredients'])) {
                    $this->addIngredients($recipe_id, $data['ingredients']);
                }
                
                // Calculate and update calories
                $this->calculateCalories($recipe_id);

                return ['success' => true, 'message' => 'Recipe created successfully', 'recipe_id' => $recipe_id];
            }

            return ['success' => false, 'message' => 'Failed to create recipe'];
        } catch (PDOException $e) {
            error_log("Create Recipe Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Database error occurred'];
        }
    }

    /**
     * Update recipe
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, $data) {
        try {
            $query = "UPDATE " . $this->table . " 
                      SET name = :name, description = :description, instructions = :instructions, 
                          prep_time = :prep_time, image_url = :image_url, category = :category
                      WHERE id = :id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':description', $data['description']);
            $stmt->bindParam(':instructions', $data['instructions']);
            $stmt->bindParam(':prep_time', $data['prep_time'], PDO::PARAM_INT);
            $stmt->bindParam(':image_url', $data['image_url']);
            $stmt->bindParam(':category', $data['category']);

            if ($stmt->execute()) {
                // Update ingredients if provided
                if (isset($data['ingredients']) && is_array($data['ingredients'])) {
                    $this->removeIngredients($id);
                    $this->addIngredients($id, $data['ingredients']);
                }
                
                // Recalculate calories
                $this->calculateCalories($id);

                return true;
            }

            return false;
        } catch (PDOException $e) {
            error_log("Update Recipe Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete recipe
     * @param int $id
     * @return bool
     */
    public function delete($id) {
        try {
            $query = "DELETE FROM " . $this->table . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Delete Recipe Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Add ingredients to recipe
     * @param int $recipe_id
     * @param array $ingredients [['ingredient_id' => int, 'quantity' => float], ...]
     * @return bool
     */
    private function addIngredients($recipe_id, $ingredients) {
        try {
            $query = "INSERT INTO recipe_ingredients (recipe_id, ingredient_id, quantity) 
                      VALUES (:recipe_id, :ingredient_id, :quantity)";
            $stmt = $this->conn->prepare($query);

            foreach ($ingredients as $ing) {
                $stmt->bindParam(':recipe_id', $recipe_id, PDO::PARAM_INT);
                $stmt->bindParam(':ingredient_id', $ing['ingredient_id'], PDO::PARAM_INT);
                $stmt->bindParam(':quantity', $ing['quantity']);
                $stmt->execute();
            }

            return true;
        } catch (PDOException $e) {
            error_log("Add Ingredients Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Remove all ingredients from recipe
     * @param int $recipe_id
     * @return bool
     */
    private function removeIngredients($recipe_id) {
        try {
            $query = "DELETE FROM recipe_ingredients WHERE recipe_id = :recipe_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':recipe_id', $recipe_id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Remove Ingredients Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Calculate and update recipe calories
     * @param int $recipe_id
     * @return bool
     */
    public function calculateCalories($recipe_id) {
        try {
            $query = "UPDATE " . $this->table . " r
                      SET calories = (
                          SELECT COALESCE(SUM(ri.quantity * i.calories_per_unit), 0)
                          FROM recipe_ingredients ri
                          JOIN ingredients i ON ri.ingredient_id = i.id
                          WHERE ri.recipe_id = r.id
                      )
                      WHERE r.id = :recipe_id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':recipe_id', $recipe_id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Calculate Calories Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get total recipe count
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
            error_log("Get Total Recipes Error: " . $e->getMessage());
            return 0;
        }
    }
}

