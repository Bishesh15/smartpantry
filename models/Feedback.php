<?php
/**
 * Feedback Model
 * Handles user feedback management
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

class Feedback {
    private $conn;
    private $table = 'feedback';

    public $id;
    public $user_id;
    public $name;
    public $email;
    public $message;
    public $admin_response;
    public $status;
    public $created_at;

    public function __construct() {
        $this->conn = getDB();
    }

    /**
     * Create new feedback
     * @param array $data
     * @return array ['success' => bool, 'message' => string, 'feedback_id' => int|null]
     */
    public function create($data) {
        try {
            $query = "INSERT INTO " . $this->table . " 
                      (user_id, name, email, message) 
                      VALUES (:user_id, :name, :email, :message)";

            $stmt = $this->conn->prepare($query);
            $user_id = isset($data['user_id']) ? $data['user_id'] : null;
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':message', $data['message']);

            if ($stmt->execute()) {
                $feedback_id = $this->conn->lastInsertId();
                return ['success' => true, 'message' => 'Feedback submitted successfully', 'feedback_id' => $feedback_id];
            }

            return ['success' => false, 'message' => 'Failed to submit feedback'];
        } catch (PDOException $e) {
            error_log("Create Feedback Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Database error occurred'];
        }
    }

    /**
     * Get all feedback
     * @param string $status Optional status filter
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getAll($status = null, $limit = 100, $offset = 0) {
        try {
            if ($status) {
                $query = "SELECT f.*, u.username 
                          FROM " . $this->table . " f
                          LEFT JOIN users u ON f.user_id = u.id
                          WHERE f.status = :status
                          ORDER BY f.created_at DESC 
                          LIMIT :limit OFFSET :offset";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':status', $status);
            } else {
                $query = "SELECT f.*, u.username 
                          FROM " . $this->table . " f
                          LEFT JOIN users u ON f.user_id = u.id
                          ORDER BY f.created_at DESC 
                          LIMIT :limit OFFSET :offset";
                $stmt = $this->conn->prepare($query);
            }
            
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get All Feedback Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get feedback by ID
     * @param int $id
     * @return array|null
     */
    public function getById($id) {
        try {
            $query = "SELECT f.*, u.username 
                      FROM " . $this->table . " f
                      LEFT JOIN users u ON f.user_id = u.id
                      WHERE f.id = :id LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                return $stmt->fetch(PDO::FETCH_ASSOC);
            }
            return null;
        } catch (PDOException $e) {
            error_log("Get Feedback Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Update feedback (admin response)
     * @param int $id
     * @param string $admin_response
     * @param string $status
     * @return bool
     */
    public function update($id, $admin_response, $status = 'responded') {
        try {
            $query = "UPDATE " . $this->table . " 
                      SET admin_response = :admin_response, status = :status 
                      WHERE id = :id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':admin_response', $admin_response);
            $stmt->bindParam(':status', $status);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Update Feedback Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete feedback
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
            error_log("Delete Feedback Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get total feedback count
     * @param string $status Optional status filter
     * @return int
     */
    public function getTotalCount($status = null) {
        try {
            if ($status) {
                $query = "SELECT COUNT(*) as total FROM " . $this->table . " WHERE status = :status";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':status', $status);
            } else {
                $query = "SELECT COUNT(*) as total FROM " . $this->table;
                $stmt = $this->conn->prepare($query);
            }
            
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)$result['total'];
        } catch (PDOException $e) {
            error_log("Get Total Feedback Error: " . $e->getMessage());
            return 0;
        }
    }
}

