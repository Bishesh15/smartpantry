<?php
/**
 * Feedback Model
 * Smart Pantry
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

class Feedback {
    private ?PDO   $db;
    private string $table = 'feedback';

    public function __construct() { $this->db = getDB(); }

    public function create(array $data): bool {
        $st = $this->db->prepare(
            "INSERT INTO {$this->table} (user_id, name, email, message)
             VALUES (?,?,?,?)"
        );
        return $st->execute([
            $data['user_id'] ?? null,
            sanitize($data['name']),
            sanitize($data['email']),
            sanitize($data['message']),
        ]);
    }

    public function getAll(string $status = '', int $limit = 50, int $offset = 0): array {
        if ($status) {
            $st = $this->db->prepare(
                "SELECT f.*, u.username
                 FROM {$this->table} f LEFT JOIN users u ON f.user_id = u.id
                 WHERE f.status=? ORDER BY f.created_at DESC LIMIT ? OFFSET ?"
            );
            $st->execute([$status, $limit, $offset]);
        } else {
            $st = $this->db->prepare(
                "SELECT f.*, u.username
                 FROM {$this->table} f LEFT JOIN users u ON f.user_id = u.id
                 ORDER BY f.created_at DESC LIMIT ? OFFSET ?"
            );
            $st->execute([$limit, $offset]);
        }
        return $st->fetchAll();
    }

    public function getById(int $id): ?array {
        $st = $this->db->prepare("SELECT * FROM {$this->table} WHERE id=? LIMIT 1");
        $st->execute([$id]);
        return $st->fetch() ?: null;
    }

    public function respond(int $id, string $response, string $status = 'responded'): bool {
        return $this->db->prepare(
            "UPDATE {$this->table} SET admin_response=?, status=? WHERE id=?"
        )->execute([sanitize($response), $status, $id]);
    }

    public function delete(int $id): bool {
        return $this->db->prepare("DELETE FROM {$this->table} WHERE id=?")->execute([$id]);
    }

    public function getTotalCount(string $status = ''): int {
        if ($status) {
            $st = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} WHERE status=?");
            $st->execute([$status]);
        } else {
            $st = $this->db->prepare("SELECT COUNT(*) FROM {$this->table}");
            $st->execute();
        }
        return (int) $st->fetchColumn();
    }

    public function getUserFeedback(int $userId): array {
        $st = $this->db->prepare(
            "SELECT * FROM {$this->table} WHERE user_id = ? ORDER BY created_at DESC"
        );
        $st->execute([$userId]);
        return $st->fetchAll();
    }
}
