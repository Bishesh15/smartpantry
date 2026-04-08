<?php
$page_title = 'User Management';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../models/User.php';

require_once __DIR__ . '/../includes/admin-header.php';

$userModel = new User();
$page      = max(1, (int)($_GET['page'] ?? 1));
$offset    = ($page - 1) * USERS_PER_PAGE;
$users     = $userModel->getAll(USERS_PER_PAGE, $offset);
$total     = $userModel->getTotalCount();
$pages     = ceil($total / USERS_PER_PAGE);
?>

<div class="admin-card">
  <div class="admin-card-header d-flex justify-content-between align-items-center">
    <span><i class="bi bi-people me-2"></i>All Users (<?= $total ?>)</span>
    <input type="text" id="userSearch" class="admin-input" placeholder="Search users…"
           style="max-width:240px;padding:.4rem .75rem;">
  </div>

  <table class="admin-table">
    <thead>
      <tr>
        <th>#</th>
        <th>Full Name</th>
        <th>Username</th>
        <th>Email</th>
        <th>Status</th>
        <th>Registered</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($users)): ?>
        <tr><td colspan="7" class="text-center text-muted py-4">No users found.</td></tr>
      <?php else: ?>
        <?php foreach ($users as $u): ?>
          <tr class="user-row" data-name="<?= strtolower(htmlspecialchars($u['username'] . ' ' . $u['email'])) ?>">
            <td class="text-muted"><?= $u['id'] ?></td>
            <td class="fw-bold"><?= htmlspecialchars($u['full_name'] ?? '—') ?></td>
            <td>@<?= htmlspecialchars($u['username']) ?></td>
            <td class="text-muted" style="font-size:.82rem;"><?= htmlspecialchars($u['email']) ?></td>
            <td>
              <span class="badge <?= $u['status'] === 'active' ? 'badge-veg' : 'bg-secondary text-white' ?>">
                <?= ucfirst($u['status'] ?? 'active') ?>
              </span>
            </td>
            <td class="text-muted" style="font-size:.78rem;"><?= formatDate($u['created_at'] ?? '') ?></td>
            <td>
              <div class="d-flex gap-1 flex-wrap">
                <?php if (($u['status'] ?? 'active') === 'active'): ?>
                  <a href="<?= BASE_URL ?>controllers/AdminController.php?action=deactivate_user&id=<?= $u['id'] ?>"
                     class="btn-admin-warning btn-sm"
                     data-confirm="Deactivate user '<?= htmlspecialchars($u['username']) ?>'?">
                    <i class="bi bi-pause-circle"></i> Deactivate
                  </a>
                <?php else: ?>
                  <a href="<?= BASE_URL ?>controllers/AdminController.php?action=activate_user&id=<?= $u['id'] ?>"
                     class="btn-admin-primary btn-sm"
                     style="background:#3b82f6;">
                    <i class="bi bi-play-circle"></i> Activate
                  </a>
                <?php endif; ?>
                <a href="<?= BASE_URL ?>controllers/AdminController.php?action=delete_user&id=<?= $u['id'] ?>"
                   class="btn-admin-danger btn-sm"
                   data-confirm="Permanently delete user '<?= htmlspecialchars($u['username']) ?>' and ALL their data? This CANNOT be undone.">
                  <i class="bi bi-trash"></i> Delete
                </a>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>

  <?php if ($pages > 1): ?>
    <div class="p-3 d-flex justify-content-center gap-2">
      <?php for ($p = 1; $p <= $pages; $p++): ?>
        <a href="?page=<?= $p ?>"
           class="btn btn-sm <?= $p === $page ? 'btn-success' : 'btn-outline-secondary' ?> rounded-3">
          <?= $p ?>
        </a>
      <?php endfor; ?>
    </div>
  <?php endif; ?>
</div>

<script>
document.getElementById('userSearch')?.addEventListener('input', function() {
  const term = this.value.toLowerCase();
  document.querySelectorAll('.user-row').forEach(r => {
    r.style.display = r.dataset.name.includes(term) ? '' : 'none';
  });
});
</script>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>
