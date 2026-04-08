<?php
$page_title = 'Welcome';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../models/Recipe.php';
require_once __DIR__ . '/../../config/database.php';

$db          = getDB();
$recipeModel = new Recipe();
$featured    = $recipeModel->getMostViewed(6);
if (count($featured) < 3) {
    $featured = $recipeModel->getAll(6);
}

require_once __DIR__ . '/../includes/header.php';
?>

<!-- ── Hero ─────────────────────────────────────────────── -->
<section class="sp-hero">
  <div class="container-xl">
    <div class="row align-items-center g-5">
      <div class="col-lg-6">
        <div class="animate-fade-up">
          <span class="badge bg-success mb-3 px-3 py-2" style="font-size:.8rem;letter-spacing:1px;box-shadow: 0 4px 10px rgba(22,163,74,0.3);">
            🍳 SMART MATCHING
          </span>
          <h1 class="fw-black mb-4" style="font-size: 4rem; line-height: 1.1;">
            Cook Smarter.<br>
            <span class="text-success text-gradient">Waste Nothing.</span>
          </h1>
          <p class="lead mb-4 text-light" style="opacity: 0.9; max-width: 500px;">
            Turn your leftover ingredients into chef-quality meals. We rank thousands of recipes based on what's in your kitchen 
            <strong>right now</strong>.
          </p>
          <div class="d-flex flex-wrap gap-3">
            <a href="<?= BASE_URL ?>views/user/recipe-search.php" class="btn-sp-primary" style="padding:1.1rem 2.5rem;font-size:1.1rem;box-shadow: 0 8px 20px rgba(22,163,74,0.4);">
              <i class="bi bi-search"></i> Find Recipes Now
            </a>
            <?php if (!isLoggedIn()): ?>
              <a href="<?= BASE_URL ?>views/user/register.php" class="btn btn-outline-light rounded-pill px-4 py-3 border-2 fw-bold" style="font-size:1rem;">
                <i class="bi bi-person-plus"></i> Create Account
              </a>
            <?php else: ?>
              <a href="<?= BASE_URL ?>views/user/pantry.php" class="btn btn-outline-light rounded-pill px-4 py-3 border-2 fw-bold" style="font-size:1rem;">
                <i class="bi bi-basket"></i> My Pantry
              </a>
            <?php endif; ?>
          </div>
          <div class="mt-5 d-flex gap-5" style="font-size:.9rem;color:rgba(255,255,255,.7);">
            <div><strong class="text-white fs-4 d-block">40+</strong> Ingredients</div>
            <div><strong class="text-white fs-4 d-block">15+</strong> Recipes</div>
            <div><strong class="text-white fs-4 d-block">24/7</strong> Support</div>
          </div>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="animate-float">
          <div class="bg-white rounded-5 p-5 shadow-2xl text-dark" style="border: 1px solid rgba(0,0,0,0.05);">
            <div class="text-center mb-4">
              <div class="bg-success-subtle d-inline-block px-3 py-1 rounded-pill text-success fw-bold small mb-2">QUICK MATCH</div>
              <h4 class="fw-black mb-1">What's in your fridge?</h4>
              <p class="text-muted small">Enter an ingredient to see instant results</p>
            </div>
            
            <form method="POST" action="<?= BASE_URL ?>controllers/RecipeController.php" id="hero-search-form">
              <input type="hidden" name="action" value="search">
              <div class="search-input-group mb-4">
                <i class="bi bi-search search-icon"></i>
                <input type="text" name="search_term" class="form-control form-control-lg border-0 bg-light py-3 ps-5"
                       placeholder="Garlic, Tomato, Chicken..."
                       style="border-radius:15px;">
              </div>
              <button type="submit" class="btn btn-success w-100 py-3 fw-bold rounded-4 fs-5 mb-3 transition-all hover-scale">
                Find Matching Recipes
              </button>
            </form>
            
            <div class="text-center">
              <div class="text-muted small fw-bold mb-2">TRY THESE:</div>
              <div class="d-flex flex-wrap justify-content-center gap-2">
                <?php foreach (['Garlic','Rice','Tomato','Egg','Chicken'] as $ing): ?>
                  <a href="javascript:void(0)" 
                     onclick="const f=document.getElementById('hero-search-form'); f.search_term.value='<?= $ing ?>'; f.submit();"
                     class="badge rounded-pill border bg-white text-dark py-2 px-3 fw-medium hover-bg-success" 
                     style="cursor:pointer; font-size:.8rem; transition: all 0.2s;"><?= $ing ?></a>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ── Stats Section ────────────────────────────────────── -->
<section class="py-5 bg-light" style="margin-top: -50px;">
  <div class="container-xl">
    <div class="bg-white rounded-5 shadow-sm p-4 border border-info-subtle">
      <div class="row text-center gy-4 align-items-center">
        <div class="col-6 col-md-3">
          <h2 class="fw-black text-success mb-0 counter"><?= $recipeModel->getTotalCount() ?>+</h2>
          <p class="text-muted small mb-0 fw-bold">RECIPES AVAILABLE</p>
        </div>
        <div class="col-6 col-md-3 border-start-md">
          <h2 class="fw-black text-primary mb-0 counter"><?= $db->query("SELECT COUNT(*) FROM ingredients")->fetchColumn() ?>+</h2>
          <p class="text-muted small mb-0 fw-bold">INGREDIENTS READY</p>
        </div>
        <div class="col-6 col-md-3 border-start-md">
          <h2 class="fw-black text-warning mb-0 counter"><?= $db->query("SELECT COUNT(*) FROM users")->fetchColumn() ?></h2>
          <p class="text-muted small mb-0 fw-bold">TOTAL COOKS</p>
        </div>
        <div class="col-6 col-md-3 border-start-md">
          <h2 class="fw-black text-danger mb-0 counter"><?= $db->query("SELECT COUNT(*) FROM ratings")->fetchColumn() ?></h2>
          <p class="text-muted small mb-0 fw-bold">SATISFIED RATINGS</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ── How It Works ────────────────────────────────────── -->
<section class="py-5 bg-white overflow-hidden">
  <div class="container-xl">
    <div class="row justify-content-center text-center mb-5">
      <div class="col-lg-7 animate-fade-up">
        <h2 class="fw-black mb-3 fs-1">Simple Steps to Your Next Meal</h2>
        <p class="text-muted fs-5">We make it incredibly easy to find meals you actually want to cook.</p>
      </div>
    </div>
    <div class="row g-4 justify-content-center">
      <?php 
      $steps = [
        ['1', 'List Ingredients', 'Select the items you have — from spices to proteins and vegetables.', 'bi-basket-fill', '#dcfce7', '#16a34a'],
        ['2', 'Smart Matching', 'We instantly find recipes that use the most of your ingredients.', 'bi-stars', '#dbeafe', '#2563eb'],
        ['3', 'Cook & Enjoy', 'Follow simple steps and enjoy a delicious meal without waste.', 'bi-egg-fried', '#fef3c7', '#d97706']
      ];
      foreach ($steps as $s): ?>
      <div class="col-md-4">
        <div class="sp-card border-0 p-5 text-center h-100 bg-light rounded-5 position-relative">
          <div class="step-number" style="position: absolute; top: 1.5rem; right: 2rem; font-size: 3rem; font-weight: 900; color: rgba(0,0,0,0.03);"><?= $s[0] ?></div>
          <div class="mx-auto mb-4 d-flex align-items-center justify-content-center rounded-circle"
               style="width:80px;height:80px;background:<?= $s[4] ?>;color:<?= $s[5] ?>;font-size:2.2rem;box-shadow: 0 10px 20px rgba(0,0,0,0.05);">
            <i class="bi <?= $s[3] ?>"></i>
          </div>
          <h4 class="fw-bold mb-3"><?= $s[1] ?></h4>
          <p class="text-muted"><?= $s[2] ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ── Cuisine Explorer ─────────────────────────────────── -->
<section class="py-5 bg-light">
  <div class="container-xl">
    <div class="d-flex justify-content-between align-items-end mb-5">
      <div>
        <h2 class="fw-black mb-2 fs-1">Explore Cuisines</h2>
        <p class="text-muted mb-0">Discover flavors from around the world.</p>
      </div>
      <a href="<?= BASE_URL ?>views/user/recipe-search.php" class="btn btn-outline-dark fw-bold rounded-pill px-4">See All</a>
    </div>
    <div class="row g-4">
      <div class="col-6 col-md-3">
        <a href="<?= BASE_URL ?>views/user/recipe-search.php?category=Nepali" class="cuisine-card">
          <img src="<?= ASSETS_PATH ?>images/cuisine-nepali.jpg" alt="Nepali" onerror="this.src='<?= ASSETS_PATH ?>images/default-recipes.jpg'">
          <div class="overlay"><h5>NEPALI</h5></div>
        </a>
      </div>
      <div class="col-6 col-md-3">
        <a href="<?= BASE_URL ?>views/user/recipe-search.php?category=Italian" class="cuisine-card">
          <img src="<?= ASSETS_PATH ?>images/cuisine-italian.jpg" alt="Italian" onerror="this.src='<?= ASSETS_PATH ?>images/default-recipes.jpg'">
          <div class="overlay"><h5>ITALIAN</h5></div>
        </a>
      </div>
      <div class="col-6 col-md-3">
        <a href="<?= BASE_URL ?>views/user/recipe-search.php?category=Indian" class="cuisine-card">
          <img src="https://images.unsplash.com/photo-1585937421612-70a008356fbe?q=80&w=800&auto=format&fit=crop" alt="Indian">
          <div class="overlay"><h5>INDIAN</h5></div>
        </a>
      </div>
      <div class="col-6 col-md-3">
        <a href="<?= BASE_URL ?>views/user/recipe-search.php?category=Chinese" class="cuisine-card">
          <img src="https://images.unsplash.com/photo-1552611052-33e04de081de?q=80&w=800&auto=format&fit=crop" alt="Chinese">
          <div class="overlay"><h5>CHINESE</h5></div>
        </a>
      </div>
    </div>
  </div>
</section>

<!-- ── Featured Recipes ──────────────────────────────────── -->
<section class="py-5 bg-white">
  <div class="container-xl">
    <div class="row mb-5 align-items-center">
      <div class="col">
        <h2 class="fw-black mb-1 fs-1">Trending Recipes</h2>
        <p class="text-muted mb-0">The most matched and cooked recipes this week.</p>
      </div>
      <div class="col-auto">
        <a href="<?= BASE_URL ?>views/user/recipe-search.php" class="btn-sp-outline px-4 py-2">
          View All <i class="bi bi-arrow-right ms-2"></i>
        </a>
      </div>
    </div>
    <div class="row g-4">
      <?php foreach ($featured as $r): ?>
      <div class="col-sm-6 col-lg-4">
        <div class="sp-card recipe-card border-light h-100">
          <div class="position-relative">
            <a href="<?= BASE_URL ?>views/user/recipe-detail.php?id=<?= $r['id'] ?>">
              <img src="<?= resolveImageUrl($r['image_url'] ?? '') ?>"
                   class="card-img-top w-100" style="height:240px;object-fit:cover; border-radius: 20px 20px 0 0;"
                   alt="<?= htmlspecialchars($r['name']) ?>"
                   onerror="this.src='<?= ASSETS_PATH ?>images/default-recipes.jpg'">
            </a>
            <div class="recipe-badge-top">
              <span class="badge <?= dietBadgeClass($r['diet_type']) ?> py-2 px-3">
                <?= dietBadgeIcon($r['diet_type']) ?> <?= htmlspecialchars($r['diet_type']) ?>
              </span>
            </div>
          </div>
          <div class="card-body p-4">
            <h4 class="card-title fw-black mb-3">
              <a href="<?= BASE_URL ?>views/user/recipe-detail.php?id=<?= $r['id'] ?>"
                 class="text-dark text-decoration-none hover-text-success">
                <?= htmlspecialchars($r['name']) ?>
              </a>
            </h4>
            <div class="d-flex justify-content-between align-items-center">
              <div class="card-meta">
                <span class="text-muted small"><i class="bi bi-clock me-1 text-success"></i><?= $r['prep_time'] ?>m</span>
                <span class="text-muted small ms-3"><i class="bi bi-fire me-1 text-danger"></i><?= number_format($r['calories']) ?> kcal</span>
              </div>
              <div class="rating fw-bold text-dark">
                <i class="bi bi-star-fill text-warning"></i> <?= number_format($r['average_rating'],1) ?>
              </div>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ── CTA Section ──────────────────────────────────────── -->
<section class="py-5">
  <div class="container-xl">
    <div class="rounded-5 p-5 text-center text-white animate-fade-up" 
         style="background: linear-gradient(135deg, #16a34a 0%, #15803d 100%); position: relative; overflow: hidden;">
      <div style="position: absolute; top: -50px; left: -50px; width: 200px; height: 200px; background: rgba(255,255,255,0.1); border-radius: 50%;"></div>
      <div style="position: absolute; bottom: -50px; right: -50px; width: 300px; height: 300px; background: rgba(0,0,0,0.05); border-radius: 50%;"></div>
      
      <h2 class="fw-black mb-4 fs-1">Ready to cook sustainably?</h2>
      <p class="fs-5 mb-5 mx-auto" style="max-width: 600px; opacity: 0.9;">Join thousands of home cooks who are saving ingredients and discovering amazing meals every day.</p>
      
      <div class="d-flex flex-wrap justify-content-center gap-3">
        <?php if (!isLoggedIn()): ?>
          <a href="<?= BASE_URL ?>views/user/register.php" class="btn btn-light rounded-pill px-5 py-3 fw-black text-success fs-5 shadow-lg">
            Join Now – It's Free
          </a>
        <?php else: ?>
          <a href="<?= BASE_URL ?>views/user/recipe-search.php" class="btn btn-light rounded-pill px-5 py-3 fw-black text-success fs-5 shadow-lg">
            Find Your Next Meal
          </a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<!-- Add some helper styles just for the search behavior -->
<style>
.search-input-group {
    position: relative;
    display: flex;
    align-items: center;
}
.search-icon {
    position: absolute;
    left: 1.25rem;
    color: #94a3b8;
    font-size: 1.2rem;
}
.hover-scale:hover {
    transform: scale(1.02);
}
.hover-bg-success:hover {
    background-color: var(--bs-success) !important;
    color: white !important;
    border-color: var(--bs-success) !important;
}
.text-gradient {
    background: linear-gradient(135deg, #16a34a 0%, #4ade80 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}
.shadow-2xl {
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
}
.border-start-md {
    border-left: 1px solid rgba(0,0,0,0.05);
}
@media (max-width: 768px) {
    .border-start-md {
        border-left: none;
    }
}
.recipe-badge-top {
    position: absolute;
    top: 1rem;
    left: 1rem;
    z-index: 2;
}
.hover-text-success:hover {
    color: var(--bs-success) !important;
}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
