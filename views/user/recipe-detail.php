<?php
$page_title = 'Recipe Detail';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../controllers/RecipeController.php';
require_once __DIR__ . '/../../models/Rating.php';

$recipe_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($recipe_id <= 0) redirect(BASE_URL . 'views/user/recipe-search.php');

$ctrl   = new RecipeController();
$recipe = $ctrl->viewDetail($recipe_id);
if (!$recipe) {
    flashError('Recipe not found.');
    redirect(BASE_URL . 'views/user/recipe-search.php');
}

// Nutrition estimates
$cals    = (float) $recipe['calories'];
$protein = round($cals * 0.25 / 4);
$carbs   = round($cals * 0.50 / 4);
$fat     = round($cals * 0.25 / 9);
$goal    = isLoggedIn() ? ($_SESSION['daily_calorie_goal'] ?? 2000) : 2000;
$pctDay  = min(100, round(($cals / $goal) * 100));

// Base values for calculator
$baseServings = $recipe['servings'] ?? 2;

// User rating
$ratingModel = new Rating();
$userRating  = isLoggedIn() ? $ratingModel->getUserRating($_SESSION['user_id'], $recipe_id) : null;
$allRatings  = $ratingModel->getRecipeRatings($recipe_id, 10);

// Ingredients (with in_pantry flag set by RecipeController::viewDetail)
$ingredients = $recipe['ingredients'] ?? [];
$similar     = $recipe['similar']     ?? [];

// Check if favorited
$isFav = false;
if (isLoggedIn()) {
    require_once __DIR__ . '/../../models/User.php';
    $userModel = new User();
    $isFav     = $userModel->isFavorited($_SESSION['user_id'], $recipe_id);
}

$page_title = $recipe['name'];
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-xl py-4" style="margin-top:10px;">
  <div class="row g-4">
    <!-- ── Main Content ─────────────────────────────────────── -->
    <div class="col-lg-8">

      <!-- Breadcrumb -->
      <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="<?= BASE_URL ?>views/user/home.php">Home</a></li>
          <li class="breadcrumb-item"><a href="<?= BASE_URL ?>views/user/recipe-search.php">Recipes</a></li>
          <li class="breadcrumb-item active"><?= htmlspecialchars($recipe['name']) ?></li>
        </ol>
      </nav>

      <!-- Header -->
      <div class="mb-4">
        <div class="d-flex flex-wrap gap-2 mb-3">
          <span class="badge <?= dietBadgeClass($recipe['diet_type']) ?> px-3 py-2" style="font-size:.85rem;">
            <?= dietBadgeIcon($recipe['diet_type']) ?> <?= htmlspecialchars($recipe['diet_type']) ?>
          </span>
          <span class="badge bg-light text-dark px-3 py-2" style="font-size:.85rem;">
            <?= htmlspecialchars($recipe['category']) ?>
          </span>
        </div>
        <h1 class="fw-black mb-3" style="font-size:2.5rem;"><?= htmlspecialchars($recipe['name']) ?></h1>
        <p class="lead text-muted"><?= htmlspecialchars($recipe['description'] ?? '') ?></p>
      </div>

      <!-- Hero Image -->
      <img src="<?= resolveImageUrl($recipe['image_url'] ?? '') ?>"
           class="recipe-detail-hero mb-4 w-100"
           alt="<?= htmlspecialchars($recipe['name']) ?>"
           onerror="this.src='<?= ASSETS_PATH ?>images/default-recipes.jpg'">

      <!-- Quick Stats -->
      <div class="row g-3 mb-4">
        <div class="col-3">
          <div class="sp-card p-3 text-center">
            <i class="bi bi-clock-fill text-success d-block mb-1" style="font-size:1.5rem;"></i>
            <div class="fw-black"><?= $recipe['prep_time'] ?> <small class="fw-normal">min</small></div>
            <div class="text-muted" style="font-size:.7rem;text-transform:uppercase;">Prep Time</div>
          </div>
        </div>
        <div class="col-3">
          <div class="sp-card p-3 text-center">
            <i class="bi bi-people-fill text-primary d-block mb-1" style="font-size:1.5rem;"></i>
            <div class="d-flex align-items-center justify-content-center gap-2">
                <button class="btn btn-sm p-0 text-primary" id="servings-dec" style="border:0;background:none;"><i class="bi bi-dash-circle-fill"></i></button>
                <div class="fw-black" id="servings-val" data-base="<?= $baseServings ?>"><?= $baseServings ?></div>
                <button class="btn btn-sm p-0 text-primary" id="servings-inc" style="border:0;background:none;"><i class="bi bi-plus-circle-fill"></i></button>
            </div>
            <div class="text-muted" style="font-size:.7rem;text-transform:uppercase;">Servings</div>
          </div>
        </div>
        <div class="col-3">
          <div class="sp-card p-3 text-center">
            <i class="bi bi-fire text-warning d-block mb-1" style="font-size:1.5rem;"></i>
            <div class="fw-black" id="total-cals" data-base="<?= $cals ?>"><?= number_format($cals) ?></div>
            <div class="text-muted" style="font-size:.7rem;text-transform:uppercase;">Calories</div>
          </div>
        </div>
        <div class="col-3">
          <div class="sp-card p-3 text-center">
            <i class="bi bi-star-fill text-warning d-block mb-1" style="font-size:1.5rem;"></i>
            <div class="fw-black"><?= number_format($recipe['average_rating'], 1) ?></div>
            <div class="text-muted" style="font-size:.7rem;text-transform:uppercase;"><?= $recipe['total_ratings'] ?> Ratings</div>
          </div>
        </div>
      </div>

      <!-- Ingredients -->
      <div class="sp-card p-4 mb-4">
        <h3 class="fw-black mb-3 d-flex align-items-center gap-2">
          <i class="bi bi-basket-fill text-success"></i> Ingredients
        </h3>
        <?php if (isLoggedIn()): ?>
          <p class="text-muted small mb-3">
            <span class="text-success fw-bold">✓ Green = In your pantry</span> &nbsp;|&nbsp;
            <span class="text-danger fw-bold">✗ Red = Missing</span>
            — click to check off as you cook
          </p>
        <?php endif; ?>
        <div class="row g-0">
          <?php foreach ($ingredients as $ing): ?>
            <div class="col-sm-6">
              <div class="ing-list-item">
                <?php if (isLoggedIn()): ?>
                  <div class="ing-check <?= $ing['in_pantry'] ? 'have' : '' ?>"
                       title="<?= $ing['in_pantry'] ? 'In your pantry' : 'Not in pantry' ?>">
                    <?php if ($ing['in_pantry']): ?>
                      <i class="bi bi-check text-white" style="font-size:.75rem;"></i>
                    <?php endif; ?>
                  </div>
                  <span class="<?= $ing['in_pantry'] ? 'text-success fw-bold' : 'text-danger' ?>">
                    <span class="calc-qty" data-base="<?= $ing['quantity'] ?>"><?= htmlspecialchars(number_format($ing['quantity'], $ing['quantity'] == floor($ing['quantity']) ? 0 : 1)) ?></span>
                    <?= htmlspecialchars($ing['unit']) ?>
                    <?= htmlspecialchars($ing['name']) ?>
                  </span>
                  <?php if (!$ing['in_pantry']): ?>
                    <?php $subs = getIngredientSubstitutes($ing['name']); ?>
                    <?php if (!empty($subs)): ?>
                      <div class="sub-icon" data-bs-toggle="popover" data-bs-trigger="hover" 
                           title="Substitutions for <?= htmlspecialchars($ing['name']) ?>" 
                           data-bs-content="<div class='small'><?php foreach($subs as $s) echo '• '.htmlspecialchars($s).'<br>'; ?></div>" 
                           data-bs-html="true">
                        <i class="bi bi-arrow-left-right"></i>
                      </div>
                    <?php endif; ?>
                  <?php endif; ?>
                <?php else: ?>
                  <div class="ing-check"></div>
                  <span>
                    <span class="calc-qty" data-base="<?= $ing['quantity'] ?>"><?= htmlspecialchars(number_format($ing['quantity'], $ing['quantity'] == floor($ing['quantity']) ? 0 : 1)) ?></span>
                    <?= htmlspecialchars($ing['unit']) ?>
                    <strong><?= htmlspecialchars($ing['name']) ?></strong>
                  </span>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
        <?php if (!isLoggedIn()): ?>
          <div class="alert alert-light border mt-3 small">
            <i class="bi bi-info-circle me-1 text-success"></i>
            <a href="<?= BASE_URL ?>views/user/login.php" class="fw-bold">Log in</a>
            to see which ingredients you already have highlighted in green.
          </div>
        <?php endif; ?>
      </div>

      <!-- Instructions -->
      <div class="sp-card p-4 mb-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h3 class="fw-black mb-0 d-flex align-items-center gap-2">
            <i class="bi bi-list-ol text-success"></i> Instructions
          </h3>
          <button class="btn btn-sp-primary" id="btn-start-cooking">
            <i class="bi bi-play-fill"></i> Start Cooking
          </button>
        </div>
        <?php
        $steps    = array_filter(array_map('trim', explode("\n", $recipe['instructions'])));
        $stepNum  = 1;
        ?>
        <?php foreach ($steps as $step): ?>
          <div class="step-item">
            <div class="step-num"><?= $stepNum++ ?></div>
            <div class="mt-1" style="line-height:1.7;color:#334155;">
              <?= htmlspecialchars($step) ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Actions: Favorite -->
      <div class="d-flex gap-3 mb-4 flex-wrap">
        <?php if (isLoggedIn()): ?>
          <form method="POST" action="<?= BASE_URL ?>controllers/UserController.php">
            <input type="hidden" name="action" value="<?= $isFav ? 'remove_favorite' : 'add_favorite' ?>">
            <input type="hidden" name="recipe_id" value="<?= $recipe_id ?>">
            <input type="hidden" name="redirect" value="<?= BASE_URL ?>views/user/recipe-detail.php?id=<?= $recipe_id ?>">
            <button type="submit"
                    class="btn <?= $isFav ? 'btn-danger' : 'btn-outline-danger' ?> fw-bold rounded-3">
              <i class="bi bi-heart<?= $isFav ? '-fill' : '' ?> me-1"></i>
              <?= $isFav ? 'Remove from Favorites' : 'Save to Favorites' ?>
            </button>
          </form>
        <?php else: ?>
          <a href="<?= BASE_URL ?>views/user/login.php" class="btn btn-outline-secondary rounded-3">
            <i class="bi bi-heart me-1"></i> Login to Save
          </a>
        <?php endif; ?>
        <a href="<?= BASE_URL ?>views/user/recipe-search.php" class="btn btn-outline-success rounded-3">
          <i class="bi bi-arrow-left me-1"></i> Back to Recipes
        </a>
      </div>

      <!-- Ratings -->
      <?php if (isLoggedIn()): ?>
      <div class="sp-card p-4 mb-4">
        <h4 class="fw-black mb-3"><i class="bi bi-star-fill text-warning me-2"></i>Rate This Recipe</h4>
        <form method="POST" action="<?= BASE_URL ?>controllers/UserController.php">
          <input type="hidden" name="action" value="add_rating">
          <input type="hidden" name="recipe_id" value="<?= $recipe_id ?>">
          <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
          <div class="star-group mb-3" style="display:flex;gap:.5rem;flex-direction:row-reverse;justify-content:flex-end;">
            <?php for ($i = 5; $i >= 1; $i--): ?>
              <input type="radio" name="rating" id="star<?= $i ?>" value="<?= $i ?>"
                     <?= ($userRating && $userRating['rating'] == $i) ? 'checked' : '' ?>>
              <label for="star<?= $i ?>" class="rating-star" data-value="<?= $i ?>"
                     style="font-size:2rem;color:<?= ($userRating && $userRating['rating'] >= $i) ? '#fbbf24' : '#e2e8f0' ?>;cursor:pointer;">★</label>
            <?php endfor; ?>
          </div>
          <textarea name="comment" rows="3" class="sp-form-control mb-3"
                    placeholder="Share your experience with this recipe…"><?= htmlspecialchars($userRating['comment'] ?? '') ?></textarea>
          <button type="submit" class="btn-sp-primary">
            <i class="bi bi-send-fill me-1"></i> Submit Rating
          </button>
        </form>
      </div>
      <?php endif; ?>

      <!-- Reviews -->
      <?php if (!empty($allRatings)): ?>
      <div class="sp-card p-4 mb-4">
        <h4 class="fw-black mb-3">Community Reviews (<?= count($allRatings) ?>)</h4>
        <?php foreach ($allRatings as $rev): ?>
          <div class="d-flex gap-3 mb-3 pb-3 border-bottom">
            <div class="rounded-circle bg-success d-flex align-items-center justify-content-center text-white fw-bold flex-shrink-0"
                 style="width:40px;height:40px;font-size:.9rem;">
              <?= strtoupper(substr($rev['username'] ?? 'U', 0, 1)) ?>
            </div>
            <div>
              <div class="d-flex align-items-center gap-2 mb-1">
                <strong><?= htmlspecialchars($rev['username'] ?? 'User') ?></strong>
                <span style="color:#fbbf24;"><?= str_repeat('★', $rev['rating']) ?><?= str_repeat('☆', 5 - $rev['rating']) ?></span>
                <small class="text-muted"><?= timeAgo($rev['created_at']) ?></small>
              </div>
              <?php if ($rev['comment']): ?>
                <p class="mb-0 text-muted small"><?= htmlspecialchars($rev['comment']) ?></p>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>

    <!-- ── Sidebar: Nutrition + Similar ─────────────────────── -->
    <div class="col-lg-4">

      <!-- Nutrition Card -->
      <div class="nutrition-sidebar mb-4">
        <h4 class="text-white fw-black mb-4 text-center">Nutrition Facts</h4>
        <!-- Gauge -->
        <div style="position:relative;width:140px;height:140px;margin:0 auto 1.5rem;">
          <svg width="140" height="140" style="transform:rotate(-90deg);">
            <circle cx="70" cy="70" r="58" fill="none" stroke="rgba(255,255,255,.1)" stroke-width="10"></circle>
            <circle cx="70" cy="70" r="58" fill="none" stroke="#16a34a" stroke-width="10"
                    stroke-linecap="round"
                    stroke-dasharray="<?= 2 * M_PI * 58 ?>"
                    stroke-dashoffset="<?= 2 * M_PI * 58 * (1 - $pctDay/100) ?>"
                    style="transition:stroke-dashoffset 1s ease;"></circle>
          </svg>
          <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);text-align:center;">
            <div style="font-size:1.5rem;font-weight:900;" id="goal-pct"><?= $pctDay ?>%</div>
            <div style="font-size:.6rem;color:#94a3b8;text-transform:uppercase;">Daily Goal</div>
          </div>
        </div>
        <div class="text-center mb-4">
          <div style="font-size:2.2rem;font-weight:900;color:#16a34a;" id="side-cals"><?= number_format($cals) ?></div>
          <div style="font-size:.72rem;color:#94a3b8;text-transform:uppercase;">Calories per Serving</div>
        </div>
        <div class="row g-2 text-center">
          <div class="col-4">
            <div style="background:rgba(255,255,255,.05);padding:.75rem;border-radius:12px;">
              <div style="font-size:1.2rem;font-weight:900;color:#16a34a;" id="calc-protein" data-base="<?= $protein ?>"><?= $protein ?>g</div>
              <div style="font-size:.6rem;color:#94a3b8;">Protein</div>
            </div>
          </div>
          <div class="col-4">
            <div style="background:rgba(255,255,255,.05);padding:.75rem;border-radius:12px;">
              <div style="font-size:1.2rem;font-weight:900;color:#3b82f6;" id="calc-carbs" data-base="<?= $carbs ?>"><?= $carbs ?>g</div>
              <div style="font-size:.6rem;color:#94a3b8;">Carbs</div>
            </div>
          </div>
          <div class="col-4">
            <div style="background:rgba(255,255,255,.05);padding:.75rem;border-radius:12px;">
              <div style="font-size:1.2rem;font-weight:900;color:#f59e0b;" id="calc-fat" data-base="<?= $fat ?>"><?= $fat ?>g</div>
              <div style="font-size:.6rem;color:#94a3b8;">Fat</div>
            </div>
          </div>
        </div>
        <p class="text-center mt-3" style="font-size:.72rem;color:#475569;line-height:1.5;">
          ⚠ Estimates based on standard ingredient values. Actual nutrition may vary.
        </p>
      </div>

      <!-- Similar Recipes -->
      <?php if (!empty($similar)): ?>
      <div class="sp-card p-3">
        <h5 class="fw-black mb-3 p-1">Similar Recipes</h5>
        <?php foreach ($similar as $s): ?>
          <a href="<?= BASE_URL ?>views/user/recipe-detail.php?id=<?= $s['id'] ?>"
             class="d-flex gap-3 mb-3 pb-3 border-bottom text-decoration-none text-dark">
            <img src="<?= resolveImageUrl($s['image_url'] ?? '') ?>"
                 style="width:72px;height:72px;border-radius:12px;object-fit:cover;flex-shrink:0;"
                 onerror="this.src='<?= ASSETS_PATH ?>images/default-recipes.jpg'"
                 alt="<?= htmlspecialchars($s['name']) ?>">
            <div>
              <div class="fw-bold small mb-1"><?= htmlspecialchars($s['name']) ?></div>
              <div class="text-muted" style="font-size:.75rem;">
                <i class="bi bi-clock me-1"></i><?= $s['prep_time'] ?>m ·
                <i class="bi bi-fire me-1 text-success"></i><?= number_format($s['calories']) ?> kcal
              </div>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- ── Focus Mode Overlay ─────────────────────────────────────── -->
<div class="focus-mode-overlay" id="focus-mode">
  <div class="focus-mode-header">
    <div class="d-flex align-items-center gap-3">
        <div class="bg-primary-sp text-white rounded-3 px-3 py-1 fw-bold">Chef View</div>
        <h4 class="mb-0 fw-black text-white"><?= htmlspecialchars($recipe['name']) ?></h4>
    </div>
    <button class="btn btn-outline-light border-0" id="btn-close-focus" style="font-size:1.5rem;"><i class="bi bi-x-lg"></i></button>
  </div>
  
  <div class="focus-mode-content">
    <div class="focus-step-card animate-fade">
        <div class="focus-step-num" id="focus-step-label">Step 1 of 5</div>
        <div class="focus-step-text" id="focus-step-text">First step goes here...</div>
    </div>
    
    <div class="focus-controls">
        <button class="btn-focus" id="focus-prev">Previous</button>
        <button class="btn-focus btn-focus-primary" id="focus-next">Next Step</button>
    </div>
    
    <div class="focus-progress-wrap" style="width:100%;">
        <div class="focus-progress">
            <div class="focus-progress-bar" id="focus-bar"></div>
        </div>
        <div class="text-center mt-3 small text-muted" id="focus-pct">20% Complete</div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // ── Initializations ──
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl)
    })

    // ── Calculator Logic ──
    const servingsVal = document.getElementById('servings-val');
    const baseServings = parseInt(servingsVal.dataset.base);
    const dailyGoal = <?= (int)$goal ?>;
    
    function updateCalculator(newServings) {
        if (newServings < 1) newServings = 1;
        if (newServings > 20) newServings = 20;
        
        servingsVal.textContent = newServings;
        const factor = newServings / baseServings;
        
        document.querySelectorAll('.calc-qty').forEach(el => {
            const base = parseFloat(el.dataset.base);
            const val = base * factor;
            el.textContent = val % 1 === 0 ? val : val.toFixed(1);
        });
        
        const baseCals = parseFloat(document.getElementById('total-cals').dataset.base);
        const newCals = baseCals * factor;
        const formattedCals = Math.round(newCals).toLocaleString();
        
        document.getElementById('total-cals').textContent = formattedCals;
        document.getElementById('side-cals').textContent = formattedCals;
        
        const nutrients = ['protein', 'carbs', 'fat'];
        nutrients.forEach(n => {
            const el = document.getElementById('calc-' + n);
            const base = parseFloat(el.dataset.base);
            el.textContent = Math.round(base * factor) + 'g';
        });
        
        const pct = Math.min(100, Math.round((newCals / dailyGoal) * 100));
        document.getElementById('goal-pct').textContent = pct + '%';
        const circle = document.querySelector('.nutrition-sidebar circle[stroke="#16a34a"]');
        const r = 58;
        const circ = 2 * Math.PI * r;
        circle.style.strokeDashoffset = circ * (1 - pct/100);
    }
    
    document.getElementById('servings-inc').addEventListener('click', () => updateCalculator(parseInt(servingsVal.textContent) + 1));
    document.getElementById('servings-dec').addEventListener('click', () => updateCalculator(parseInt(servingsVal.textContent) - 1));

    // ── Focus Mode Logic ──
    const steps = <?= json_encode($steps) ?>;
    let currentStep = 0;
    const focusMode = document.getElementById('focus-mode');

    function updateFocusView() {
        const stepText = steps[currentStep];
        const card = document.querySelector('.focus-step-card');
        
        // Simple re-trigger animation
        card.classList.remove('animate-fade');
        void card.offsetWidth; 
        card.classList.add('animate-fade');

        document.getElementById('focus-step-label').textContent = `Step ${currentStep + 1} of ${steps.length}`;
        document.getElementById('focus-step-text').textContent = stepText;
        
        const pct = Math.round(((currentStep + 1) / steps.length) * 100);
        document.getElementById('focus-bar').style.width = pct + '%';
        document.getElementById('focus-pct').textContent = pct + '% Complete';

        document.getElementById('focus-prev').disabled = (currentStep === 0);
        document.getElementById('focus-next').textContent = (currentStep === steps.length - 1) ? 'Finish Cooking' : 'Next Step';
    }

    document.getElementById('btn-start-cooking').addEventListener('click', () => {
        focusMode.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        currentStep = 0;
        updateFocusView();
    });

    document.getElementById('btn-close-focus').addEventListener('click', () => {
        focusMode.style.display = 'none';
        document.body.style.overflow = 'auto';
    });

    document.getElementById('focus-next').addEventListener('click', () => {
        if (currentStep < steps.length - 1) {
            currentStep++;
            updateFocusView();
        } else {
            focusMode.style.display = 'none';
            document.body.style.overflow = 'auto';
            alert('Congratulations! Hope your meal turned out amazing! 🍳');
        }
    });

    document.getElementById('focus-prev').addEventListener('click', () => {
        if (currentStep > 0) {
            currentStep--;
            updateFocusView();
        }
    });

    // Keyboard navigation
    document.addEventListener('keydown', (e) => {
        if (focusMode.style.display === 'flex') {
            if (e.key === 'ArrowRight' || e.key === ' ') e.preventDefault(), document.getElementById('focus-next').click();
            if (e.key === 'ArrowLeft') document.getElementById('focus-prev').click();
            if (e.key === 'Escape') document.getElementById('btn-close-focus').click();
        }
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
