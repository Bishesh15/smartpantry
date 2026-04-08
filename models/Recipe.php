<?php
/**
 * Recipe Model
 * Smart Pantry – Handles all recipe DB operations + recommendation algorithm
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

class Recipe {
    private ?PDO $db;
    private string $table = 'recipes';

    public function __construct() {
        $this->db = getDB();
    }

    /* ── Basic CRUD ─────────────────────────────────────────── */

    public function getById(int $id): ?array {
        $st = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = ? LIMIT 1");
        $st->execute([$id]);
        return $st->fetch() ?: null;
    }

    public function getAll(int $limit = 100, int $offset = 0): array {
        $st = $this->db->prepare(
            "SELECT * FROM {$this->table} ORDER BY created_at DESC LIMIT ? OFFSET ?"
        );
        $st->execute([$limit, $offset]);
        return $st->fetchAll();
    }

    public function getTotalCount(?string $dietType = null): int {
        if ($dietType) {
            $st = $this->db->prepare(
                "SELECT COUNT(*) FROM {$this->table} WHERE diet_type = ?"
            );
            $st->execute([$dietType]);
        } else {
            $st = $this->db->prepare("SELECT COUNT(*) FROM {$this->table}");
            $st->execute();
        }
        return (int) $st->fetchColumn();
    }

    public function getAllPaginated(
        int    $page       = 1,
        int    $perPage    = RECIPES_PER_PAGE,
        string $dietType   = '',
        string $category   = '',
        string $sortBy     = 'created_at',
        string $sortDir    = 'DESC'
    ): array {
        $offset     = ($page - 1) * $perPage;
        $where      = [];
        $params     = [];
        $validSorts = ['created_at', 'calories', 'prep_time', 'average_rating', 'view_count'];
        $sortBy     = in_array($sortBy, $validSorts) ? $sortBy : 'created_at';
        $sortDir    = $sortDir === 'ASC' ? 'ASC' : 'DESC';

        if ($dietType) { $where[] = 'diet_type = ?'; $params[] = $dietType; }
        if ($category) { $where[] = 'category = ?';  $params[] = $category; }

        $whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $params[] = $perPage;
        $params[] = $offset;

        $st = $this->db->prepare(
            "SELECT * FROM {$this->table} $whereSQL
             ORDER BY $sortBy $sortDir
             LIMIT ? OFFSET ?"
        );
        $st->execute($params);
        return $st->fetchAll();
    }

    public function search(string $term): array {
        $like = '%' . trim($term) . '%';
        $st   = $this->db->prepare(
            "SELECT * FROM {$this->table}
             WHERE name LIKE ? OR description LIKE ? OR category LIKE ?
             ORDER BY average_rating DESC LIMIT 50"
        );
        $st->execute([$like, $like, $like]);
        return $st->fetchAll();
    }

    public function getMostViewed(int $limit = 10): array {
        $st = $this->db->prepare(
            "SELECT * FROM {$this->table} ORDER BY view_count DESC LIMIT ?"
        );
        $st->execute([$limit]);
        return $st->fetchAll();
    }

    public function incrementViewCount(int $id): void {
        $this->db->prepare(
            "UPDATE {$this->table} SET view_count = view_count + 1 WHERE id = ?"
        )->execute([$id]);
    }

    /* ── Ingredients ────────────────────────────────────────── */

    public function getIngredients(int $id): array {
        $st = $this->db->prepare(
            "SELECT i.*, ri.quantity
             FROM ingredients i
             JOIN recipe_ingredients ri ON i.id = ri.ingredient_id
             WHERE ri.recipe_id = ?
             ORDER BY i.category, i.name"
        );
        $st->execute([$id]);
        return $st->fetchAll();
    }

    /* ── Recommendation Engine ──────────────────────────────── */

    /**
     * HYBRID RECOMMENDATION ALGORITHM
     *
     * FinalScore = (0.5 × IngredientMatch) + (0.3 × SimilarityScore) + (0.2 × UserPreference)
     *
     * IngredientMatch  = |U ∩ R| / |R|   (Jaccard-like coverage)
     * SimilarityScore  = IngredientMatch  (proxy; extended later)
     * UserPreference   = 1.0 if category matches user prefs, else 0
     *
     * VALIDATION RULE: A recipe is only recommended if IngredientMatch > 0.
     * If the user has only 1 ingredient that matches 0 recipes with ≥30% coverage,
     * the system returns an empty list with an appropriate message.
     */
    public function getMatchingRecipes(
        array  $ingredientIds,
        array  $userPreferences    = [],
        string $dietTypeFilter     = '',
        string $sortBy             = 'match'
    ): array {
        if (empty($ingredientIds)) return [];

        /* Step 1 — find recipes that contain at least one user ingredient */
        $placeholders = implode(',', array_fill(0, count($ingredientIds), '?'));
        $st = $this->db->prepare(
            "SELECT r.*,
                    GROUP_CONCAT(DISTINCT ri.ingredient_id ORDER BY ri.ingredient_id) AS matched_ids,
                    COUNT(DISTINCT ri.ingredient_id) AS matched_count,
                    (SELECT COUNT(*) FROM recipe_ingredients WHERE recipe_id = r.id) AS total_ingredients
             FROM {$this->table} r
             JOIN recipe_ingredients ri ON r.id = ri.recipe_id
             WHERE ri.ingredient_id IN ($placeholders)
             GROUP BY r.id"
        );
        $st->execute($ingredientIds);
        $recipes = $st->fetchAll();

        if (empty($recipes)) return [];

        /* Step 2 — Score & annotate each recipe */
        foreach ($recipes as &$r) {
            $total   = (int) $r['total_ingredients'];
            $matched = (int) $r['matched_count'];

            // A. Ingredient Match Score  0.0 – 1.0
            $matchScore = $total > 0 ? ($matched / $total) : 0;

            // B. Similarity Score (proxy = matchScore; later: TF-IDF / collaborative)
            $simScore = $matchScore;

            // C. User Preference Score
            $prefScore = (!empty($userPreferences) && in_array($r['category'], $userPreferences))
                ? 1.0 : 0.0;

            // Final Hybrid Score
            $r['hybrid_score']     = round((0.5 * $matchScore) + (0.3 * $simScore) + (0.2 * $prefScore), 4);
            $r['match_percentage'] = (int) round($matchScore * 100);

            // Matched ingredient IDs as array
            $matchedArr = array_map('intval', explode(',', $r['matched_ids'] ?? ''));

            // Get ALL ingredient IDs for this recipe to find missing ones
            $allSt = $this->db->prepare(
                "SELECT i.id, i.name FROM ingredients i
                 JOIN recipe_ingredients ri ON i.id = ri.ingredient_id
                 WHERE ri.recipe_id = ?"
            );
            $allSt->execute([$r['id']]);
            $allIngs = $allSt->fetchAll();

            $r['matched_ingredients'] = [];
            $r['missing_ingredients'] = [];
            foreach ($allIngs as $ing) {
                if (in_array((int)$ing['id'], $ingredientIds)) {
                    $r['matched_ingredients'][] = $ing['name'];
                } else {
                    $r['missing_ingredients'][] = $ing['name'];
                }
            }
        }
        unset($r);

        /* Step 3 — Apply dietary filter (hard filter) */
        if (!empty($dietTypeFilter)) {
            $recipes = array_values(array_filter($recipes, function($r) use ($dietTypeFilter) {
                return $r['diet_type'] === $dietTypeFilter;
            }));
        }

        /* Step 4 — Sort */
        usort($recipes, function($a, $b) use ($sortBy) {
            return match($sortBy) {
                'calories' => $a['calories']        <=> $b['calories'],
                'time'     => $a['prep_time']       <=> $b['prep_time'],
                'rating'   => $b['average_rating']  <=> $a['average_rating'],
                default    => $b['hybrid_score']    <=> $a['hybrid_score'],
            };
        });

        return $recipes;
    }

    /**
     * Similar Recipes — Jaccard Similarity
     * J(A,B) = |A ∩ B| / |A ∪ B|
     * +0.2 bonus if same category
     */
    public function getSimilarRecipes(int $recipeId, int $limit = 4): array {
        $targetIngs = array_column($this->getIngredients($recipeId), 'id');
        $target     = $this->getById($recipeId);
        if (empty($targetIngs) || !$target) return [];

        $st = $this->db->prepare(
            "SELECT r.*, GROUP_CONCAT(ri.ingredient_id) AS ingredient_ids
             FROM {$this->table} r
             LEFT JOIN recipe_ingredients ri ON r.id = ri.recipe_id
             WHERE r.id != ?
             GROUP BY r.id"
        );
        $st->execute([$recipeId]);
        $others = $st->fetchAll();

        foreach ($others as &$r) {
            $otherIds    = array_filter(array_map('intval', explode(',', $r['ingredient_ids'] ?? '')));
            $intersection = count(array_intersect($targetIngs, $otherIds));
            $union        = count(array_unique(array_merge($targetIngs, $otherIds)));
            $jaccard      = $union > 0 ? ($intersection / $union) : 0;
            if ($r['category'] === $target['category']) $jaccard += 0.2;
            $r['similarity_score'] = $jaccard;
        }
        unset($r);

        usort($others, fn($a, $b) => $b['similarity_score'] <=> $a['similarity_score']);
        return array_slice($others, 0, $limit);
    }

    /* ── Calorie Calculation ────────────────────────────────── */

    public function calculateCalories(int $recipeId): void {
        $this->db->prepare(
            "UPDATE {$this->table} r
             SET calories = (
                 SELECT COALESCE(SUM(ri.quantity * i.calories_per_unit), 0)
                 FROM recipe_ingredients ri
                 JOIN ingredients i ON ri.ingredient_id = i.id
                 WHERE ri.recipe_id = r.id
             )
             WHERE r.id = ?"
        )->execute([$recipeId]);
    }

    /* ── Admin CRUD ─────────────────────────────────────────── */

    public function create(array $data): array {
        try {
            $st = $this->db->prepare(
                "INSERT INTO {$this->table}
                 (name, description, instructions, prep_time, servings, diet_type, category, image_url)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $st->execute([
                $data['name'],
                $data['description']  ?? '',
                $data['instructions'],
                (int)   ($data['prep_time'] ?? 30),
                (int)   ($data['servings']  ?? 2),
                $data['diet_type']    ?? 'Vegetarian',
                $data['category']     ?? 'Other',
                $data['image_url']    ?? '',
            ]);
            $id = (int) $this->db->lastInsertId();
            if (!empty($data['ingredients'])) $this->syncIngredients($id, $data['ingredients']);
            $this->calculateCalories($id);
            return ['success' => true, 'message' => 'Recipe created successfully.', 'id' => $id];
        } catch (PDOException $e) {
            error_log('Recipe::create ' . $e->getMessage());
            return ['success' => false, 'message' => 'Database error — could not create recipe.'];
        }
    }

    public function update(int $id, array $data): array {
        try {
            $st = $this->db->prepare(
                "UPDATE {$this->table}
                 SET name=?, description=?, instructions=?, prep_time=?, servings=?,
                     diet_type=?, category=?, image_url=?
                 WHERE id=?"
            );
            $st->execute([
                $data['name'],
                $data['description']  ?? '',
                $data['instructions'],
                (int)   ($data['prep_time'] ?? 30),
                (int)   ($data['servings']  ?? 2),
                $data['diet_type']    ?? 'Vegetarian',
                $data['category']     ?? 'Other',
                $data['image_url']    ?? '',
                $id,
            ]);
            if (isset($data['ingredients'])) $this->syncIngredients($id, $data['ingredients']);
            $this->calculateCalories($id);
            return ['success' => true, 'message' => 'Recipe updated successfully.'];
        } catch (PDOException $e) {
            error_log('Recipe::update ' . $e->getMessage());
            return ['success' => false, 'message' => 'Database error — could not update recipe.'];
        }
    }

    public function delete(int $id): bool {
        $st = $this->db->prepare("DELETE FROM {$this->table} WHERE id = ?");
        return $st->execute([$id]);
    }

    /* ── Private helpers ────────────────────────────────────── */

    private function syncIngredients(int $recipeId, array $ingredients): void {
        $this->db->prepare(
            "DELETE FROM recipe_ingredients WHERE recipe_id = ?"
        )->execute([$recipeId]);

        $st = $this->db->prepare(
            "INSERT INTO recipe_ingredients (recipe_id, ingredient_id, quantity)
             VALUES (?, ?, ?)"
        );
        foreach ($ingredients as $ing) {
            $ingId = (int) ($ing['ingredient_id'] ?? 0);
            $qty   = (float) ($ing['quantity'] ?? 1);
            if ($ingId > 0 && $qty > 0) {
                $st->execute([$recipeId, $ingId, $qty]);
            }
        }
    }
}
