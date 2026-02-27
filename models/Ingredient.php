<?php
/**
 * Ingredient Model
 * Handles ingredient CRUD operations
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

class Ingredient {
    private $conn;
    private $table = 'ingredients';

    public $id;
    public $name;
    public $category;
    public $calories_per_unit;
    public $unit;
    public $image_url;

    public function __construct() {
        $this->conn = getDB();
    }

    /**
     * Get all ingredients
     * @param string $category Optional category filter
     * @return array
     */
    public function getAll($category = null) {
        try {
            if ($category) {
                $query = "SELECT * FROM " . $this->table . " 
                          WHERE category = :category 
                          ORDER BY name ASC";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':category', $category);
            } else {
                $query = "SELECT * FROM " . $this->table . " ORDER BY category, name ASC";
                $stmt = $this->conn->prepare($query);
            }
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get All Ingredients Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get ingredients grouped by category
     * @return array
     */
    public function getByCategory() {
        try {
            $query = "SELECT * FROM " . $this->table . " ORDER BY category, name ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $ingredients = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $grouped = [];
            foreach ($ingredients as $ingredient) {
                $category = $ingredient['category'];
                if (!isset($grouped[$category])) {
                    $grouped[$category] = [];
                }
                $grouped[$category][] = $ingredient;
            }
            
            return $grouped;
        } catch (PDOException $e) {
            error_log("Get Ingredients By Category Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get ingredient by ID
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
            error_log("Get Ingredient Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Search ingredients by name
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
                      ORDER BY name ASC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':search', $search_term);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Search Ingredients Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Create new ingredient
     * @param array $data
     * @return array ['success' => bool, 'message' => string, 'ingredient_id' => int|null]
     */
    public function create($data) {
        try {
            $query = "INSERT INTO " . $this->table . " 
                      (name, category, calories_per_unit, unit, image_url) 
                      VALUES (:name, :category, :calories_per_unit, :unit, :image_url)";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':category', $data['category']);
            $stmt->bindParam(':calories_per_unit', $data['calories_per_unit']);
            $stmt->bindParam(':unit', $data['unit']);
            $stmt->bindParam(':image_url', $data['image_url']);

            if ($stmt->execute()) {
                $ingredient_id = $this->conn->lastInsertId();
                return ['success' => true, 'message' => 'Ingredient created successfully', 'ingredient_id' => $ingredient_id];
            }

            return ['success' => false, 'message' => 'Failed to create ingredient'];
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { // Duplicate entry
                return ['success' => false, 'message' => 'Ingredient name already exists'];
            }
            error_log("Create Ingredient Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Database error occurred'];
        }
    }

    /**
     * Update ingredient
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, $data) {
        try {
            $query = "UPDATE " . $this->table . " 
                      SET name = :name, category = :category, 
                          calories_per_unit = :calories_per_unit, unit = :unit, image_url = :image_url
                      WHERE id = :id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':category', $data['category']);
            $stmt->bindParam(':calories_per_unit', $data['calories_per_unit']);
            $stmt->bindParam(':unit', $data['unit']);
            $stmt->bindParam(':image_url', $data['image_url']);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Update Ingredient Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete ingredient
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
            error_log("Delete Ingredient Error: " . $e->getMessage());
            return false;
        }
    }
}

