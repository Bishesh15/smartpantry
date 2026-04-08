<?php
$page_title = 'My Profile';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../models/User.php';

requireLogin();
$userModel = new User();
$user      = $userModel->getById((int)$_SESSION['user_id']);
$prefs     = getFoodPreferences($user['food_preferences'] ?? '');
$diets     = getDietaryRestrictions($user['dietary_restrictions'] ?? '');

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-xl py-4">
  <h1 class="fw-black mb-4"><i class="bi bi-person-circle text-success me-2"></i>My Profile</h1>
  <div class="row g-4">

    <!-- Profile Update -->
    <div class="col-lg-7">
      <div class="sp-card p-4 mb-4">
        <h5 class="fw-black mb-4">Personal Information</h5>
        <form method="POST" action="<?= BASE_URL ?>controllers/UserController.php">
          <input type="hidden" name="action" value="update_profile">
          <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">

          <div class="mb-3">
            <label class="sp-label">Full Name</label>
            <input type="text" name="full_name" class="sp-form-control" required
                   value="<?= htmlspecialchars($user['full_name'] ?? '') ?>">
          </div>
          <div class="mb-3">
            <label class="sp-label">Email (read-only)</label>
            <input type="email" class="sp-form-control" value="<?= htmlspecialchars($user['email'] ?? '') ?>" readonly
                   style="background:#f8fafc;cursor:not-allowed;">
          </div>
          <div class="mb-3">
            <label class="sp-label">Daily Calorie Goal</label>
            <input type="number" name="daily_calorie_goal" class="sp-form-control"
                   value="<?= $user['daily_calorie_goal'] ?? 2000 ?>" min="800" max="5000">
          </div>

          <div class="mb-3">
            <label class="sp-label">Food Preferences</label>
            <div class="d-flex flex-wrap gap-2">
              <?php foreach (RECIPE_CATEGORIES as $cat): ?>
                <label class="ingredient-chip <?= in_array($cat, $prefs) ? 'active' : '' ?>">
                  <input type="checkbox" name="food_preferences[]" value="<?= $cat ?>"
                         style="display:none;"
                         <?= in_array($cat, $prefs) ? 'checked' : '' ?>>
                  <?= $cat ?>
                </label>
              <?php endforeach; ?>
            </div>
          </div>

          <div class="mb-4">
            <label class="sp-label">Dietary Restriction</label>
            <div class="d-flex flex-wrap gap-2">
              <?php foreach (DIET_TYPES as $dt): ?>
                <label class="ingredient-chip <?= in_array($dt, $diets) ? 'active' : '' ?>">
                  <input type="checkbox" name="dietary_restrictions[]" value="<?= $dt ?>"
                         style="display:none;"
                         <?= in_array($dt, $diets) ? 'checked' : '' ?>>
                  <?= dietBadgeIcon($dt) ?> <?= $dt ?>
                </label>
              <?php endforeach; ?>
            </div>
          </div>

          <button type="submit" class="btn-sp-primary">
            <i class="bi bi-check-circle me-1"></i> Save Profile
          </button>
        </form>
      </div>

      <!-- Password Change -->
      <div class="sp-card p-4">
        <h5 class="fw-black mb-4">Change Password</h5>
        <form method="POST" action="<?= BASE_URL ?>controllers/UserController.php">
          <input type="hidden" name="action" value="update_password">
          <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
          <div class="mb-3">
            <label class="sp-label">Current Password</label>
            <input type="password" name="current_password" class="sp-form-control" required>
          </div>
          <div class="mb-3">
            <label class="sp-label">New Password (min. 6 chars)</label>
            <input type="password" name="new_password" class="sp-form-control" minlength="6" required>
          </div>
          <div class="mb-3">
            <label class="sp-label">Confirm New Password</label>
            <input type="password" name="confirm_password" class="sp-form-control" minlength="6" required>
          </div>
          <button type="submit" class="btn-sp-primary">
            <i class="bi bi-lock me-1"></i> Update Password
          </button>
        </form>
      </div>
    </div>

    <!-- Sidebar: account info -->
    <div class="col-lg-5">
      <div class="sp-card p-4">
        <div class="text-center mb-4">
          <div class="mx-auto d-flex align-items-center justify-content-center rounded-circle text-white fw-black"
               style="width:80px;height:80px;font-size:2rem;background:var(--sp-primary);">
            <?= strtoupper(substr($user['full_name'] ?? 'U', 0, 1)) ?>
          </div>
          <h4 class="fw-black mt-3 mb-0"><?= htmlspecialchars($user['full_name'] ?? '') ?></h4>
          <p class="text-muted">@<?= htmlspecialchars($user['username'] ?? '') ?></p>
        </div>
        <div class="divider"></div>
        <div class="d-flex flex-column gap-2" style="font-size:.88rem;">
          <div class="d-flex justify-content-between">
            <span class="text-muted">Username</span>
            <strong><?= htmlspecialchars($user['username'] ?? '') ?></strong>
          </div>
          <div class="d-flex justify-content-between">
            <span class="text-muted">Email</span>
            <strong><?= htmlspecialchars($user['email'] ?? '') ?></strong>
          </div>
          <div class="d-flex justify-content-between">
            <span class="text-muted">Member since</span>
            <strong><?= formatDate($user['created_at'] ?? date('Y-m-d')) ?></strong>
          </div>
          <div class="d-flex justify-content-between">
            <span class="text-muted">Status</span>
            <span class="badge bg-success">Active</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
// Toggle chip checkboxes with visual
document.querySelectorAll('.ingredient-chip input[type=checkbox]').forEach(cb => {
  cb.closest('.ingredient-chip').addEventListener('click', function() {
    this.classList.toggle('active');
    const box = this.querySelector('input');
    if (box) box.checked = !box.checked;
  });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
