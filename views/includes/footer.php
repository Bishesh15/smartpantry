<!-- ── Footer ─────────────────────────────────────────────────── -->
<footer class="sp-footer mt-5">
  <div class="container-xl">
    <div class="row gy-4">
      <div class="col-lg-4">
        <div class="footer-logo mb-3">
          <i class="bi bi-egg-fried text-success me-2"></i>SmartPantry
        </div>
        <p>An academic project demonstrating hybrid recipe recommendation using Jaccard Similarity and user preference weighting. Reduce food waste, discover great meals.</p>
      </div>
      <div class="col-6 col-lg-2">
        <h6 class="text-white fw-bold mb-3">Explore</h6>
        <ul class="list-unstyled">
          <li class="mb-2"><a href="<?= BASE_URL ?>views/user/recipe-search.php">Find Recipes</a></li>
          <li class="mb-2"><a href="<?= BASE_URL ?>views/user/pantry.php">My Pantry</a></li>
          <li class="mb-2"><a href="<?= BASE_URL ?>views/user/favorites.php">Saved Recipes</a></li>
        </ul>
      </div>
      <div class="col-6 col-lg-2">
        <h6 class="text-white fw-bold mb-3">Info</h6>
        <ul class="list-unstyled">
          <li class="mb-2"><a href="<?= BASE_URL ?>views/user/about.php">About Project</a></li>
          <li class="mb-2"><a href="<?= BASE_URL ?>views/user/contact.php">Contact</a></li>
        </ul>
      </div>
      <div class="col-lg-4">
        <h6 class="text-white fw-bold mb-3">Diet Key</h6>
        <div class="d-flex gap-2 flex-wrap">
          <span class="badge" style="background:#dcfce7;color:#166534;padding:.4rem .8rem;">🥦 Vegetarian</span>
          <span class="badge" style="background:#ccfbf1;color:#134e4a;padding:.4rem .8rem;">🌱 Vegan</span>
          <span class="badge" style="background:#fee2e2;color:#991b1b;padding:.4rem .8rem;">🍗 Non-Veg</span>
        </div>
        <p class="mt-3 small text-muted">Helping you find the perfect meal with the ingredients you already have.</p>
      </div>
    </div>
    <hr style="border-color:rgba(255,255,255,.1);margin-top:3rem;">
    <div class="text-center small">
      &copy; <?= date('Y') ?> SmartPantry Academic Project. Built with PHP, MySQL &amp; Bootstrap 5.
    </div>
  </div>
</footer>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- SmartPantry JS -->
<script>
  // Pass BASE_URL to JS
  document.documentElement.dataset.baseUrl = '<?= BASE_URL ?>';
</script>
<script src="<?= ASSETS_PATH ?>js/main.js"></script>
</body>
</html>
