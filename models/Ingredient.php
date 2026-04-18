<?php
/**
 * Ingredient Model
 * Smart Pantry – CRUD + search for ingredients
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

class Ingredient {
    private ?PDO   $db;
    private string $table = 'ingredients';

    public function __construct() { $this->db = getDB(); }

    public function getAll(?string $category = null): array {
        if ($category) {
            $st = $this->db->prepare(
                "SELECT * FROM {$this->table} WHERE category=? ORDER BY name"
            );
            $st->execute([$category]);
        } else {
            $st = $this->db->prepare(
                "SELECT * FROM {$this->table} ORDER BY category, name"
            );
            $st->execute();
        }
        return $st->fetchAll();
    }

    /** Returns ingredients grouped by category — used for pantry sidebar */
    public function getGroupedByCategory(): array {
        $all    = $this->getAll();
        $groups = [];
        foreach ($all as $ing) {
            $groups[$ing['category']][] = $ing;
        }
        return $groups;
    }

    public function getById(int $id): ?array {
        $st = $this->db->prepare(
            "SELECT * FROM {$this->table} WHERE id=? LIMIT 1"
        );
        $st->execute([$id]);
        return $st->fetch() ?: null;
    }

    public function search(string $term, int $limit = 20): array {
        $like = '%' . trim($term) . '%';
        $st   = $this->db->prepare(
            "SELECT * FROM {$this->table} WHERE name LIKE ? ORDER BY name LIMIT ?"
        );
        $st->execute([$like, $limit]);
        return $st->fetchAll();
    }

    public function getTotalCount(): int {
        return (int) $this->db->query("SELECT COUNT(*) FROM {$this->table}")->fetchColumn();
    }

    public function resolveNamesToIds(string $namesString): array {
        $names = array_filter(array_map('trim', explode(',', $namesString)));
        if (empty($names)) return [];
        $ph  = implode(',', array_fill(0, count($names), '?'));
        $st  = $this->db->prepare("SELECT id FROM {$this->table} WHERE name IN ($ph)");
        $st->execute($names);
        return $st->fetchAll(PDO::FETCH_COLUMN, 0);
    }

    /* ── Admin CRUD ─────────────────────────────────────────── */

    public function create(array $data): array {
        try {
            $st = $this->db->prepare(
                "INSERT INTO {$this->table} (name, category, calories_per_unit, unit, image_url)
                 VALUES (?,?,?,?,?)"
            );
            $st->execute([
                sanitize($data['name']),
                sanitize($data['category']),
                (float) ($data['calories_per_unit'] ?? 0),
                sanitize($data['unit'] ?? 'piece'),
                $data['image_url'] ?? '',
            ]);
            return ['success' => true, 'message' => 'Ingredient added.', 'id' => (int)$this->db->lastInsertId()];
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                return ['success' => false, 'message' => 'An ingredient with that name already exists.'];
            }
            error_log('Ingredient::create ' . $e->getMessage());
            return ['success' => false, 'message' => 'Database error.'];
        }
    }

    public function update(int $id, array $data): array {
        try {
            $st = $this->db->prepare(
                "UPDATE {$this->table}
                 SET name=?, category=?, calories_per_unit=?, unit=?, image_url=?
                 WHERE id=?"
            );
            $st->execute([
                sanitize($data['name']),
                sanitize($data['category']),
                (float) ($data['calories_per_unit'] ?? 0),
                sanitize($data['unit'] ?? 'piece'),
                $data['image_url'] ?? '',
                $id,
            ]);
            return ['success' => true, 'message' => 'Ingredient updated.'];
        } catch (PDOException $e) {
            error_log('Ingredient::update ' . $e->getMessage());
            return ['success' => false, 'message' => 'Database error.'];
        }
    }

    public function delete(int $id): bool {
        return $this->db->prepare(
            "DELETE FROM {$this->table} WHERE id=?"
        )->execute([$id]);
    }

    /* ── Stats ────────────────────────────────────────────── */

    public function getCategoryStats(): array {
        $st = $this->db->query(
            "SELECT category, COUNT(*) as count FROM {$this->table} GROUP BY category ORDER BY count DESC"
        );
        return $st->fetchAll(PDO::FETCH_KEY_PAIR);
    }
}
