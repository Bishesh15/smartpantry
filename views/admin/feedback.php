<?php
$page_title = 'Feedback';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../models/Feedback.php';

require_once __DIR__ . '/../includes/admin-header.php';

$feedbackModel  = new Feedback();
$filterStatus   = sanitize($_GET['status'] ?? '');
$allFeedback    = $feedbackModel->getAll($filterStatus, 50, 0);
$totalPending   = $feedbackModel->getTotalCount('pending');
$totalResponded = $feedbackModel->getTotalCount('responded');
$totalResolved  = $feedbackModel->getTotalCount('resolved');
?>

<!-- Summary -->
<div class="row g-3 mb-4">
  <?php foreach ([['Pending',$totalPending,'badge-pending','bi-hourglass-split'],['Responded',$totalResponded,'badge-veg','bi-chat-right-check'],['Resolved',$totalResolved,'badge-resolved','bi-check-circle']] as [$label,$count,$cls,$icon]): ?>
    <div class="col-4">
      <a href="?status=<?= strtolower($label) ?>" class="text-decoration-none">
        <div class="admin-stat-card" style="cursor:pointer;">
          <div class="admin-stat-icon <?= $cls ?>" style="width:52px;height:52px;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:1.4rem;">
            <i class="bi <?= $icon ?>"></i>
          </div>
          <div>
            <div class="admin-stat-val"><?= $count ?></div>
            <div class="admin-stat-label"><?= $label ?></div>
          </div>
        </div>
      </a>
    </div>
  <?php endforeach; ?>
</div>

<div class="d-flex gap-2 mb-3 flex-wrap">
  <a href="?" class="btn btn-sm <?= !$filterStatus ? 'btn-success' : 'btn-outline-secondary' ?> rounded-3">All</a>
  <?php foreach (['pending','responded','resolved'] as $s): ?>
    <a href="?status=<?= $s ?>" class="btn btn-sm <?= $filterStatus === $s ? 'btn-success' : 'btn-outline-secondary' ?> rounded-3"><?= ucfirst($s) ?></a>
  <?php endforeach; ?>
</div>

<!-- Feedback List -->
<?php if (empty($allFeedback)): ?>
  <div class="admin-card p-5 text-center text-muted">
    <i class="bi bi-inbox" style="font-size:3rem;opacity:.3;"></i>
    <h5 class="mt-3">No feedback found.</h5>
  </div>
<?php else: ?>
  <?php foreach ($allFeedback as $f): ?>
    <div class="feedback-card">
      <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
        <div>
          <strong><?= htmlspecialchars($f['name']) ?></strong>
          <span class="text-muted ms-2 small"><?= htmlspecialchars($f['email']) ?></span>
          <?php if ($f['username']): ?>
            <span class="badge bg-light text-dark border ms-2" style="font-size:.7rem;">
              @<?= htmlspecialchars($f['username']) ?>
            </span>
          <?php endif; ?>
        </div>
        <div class="d-flex align-items-center gap-2">
          <span class="badge <?= $f['status'] === 'pending' ? 'badge-pending' : ($f['status'] === 'resolved' ? 'badge-resolved' : 'badge-veg') ?> rounded-pill">
            <?= ucfirst($f['status']) ?>
          </span>
          <small class="text-muted"><?= timeAgo($f['created_at']) ?></small>
          <a href="<?= BASE_URL ?>controllers/AdminController.php?action=delete_feedback&id=<?= $f['id'] ?>"
             class="btn-admin-danger btn-sm"
             data-confirm="Delete this feedback?">
            <i class="bi bi-trash"></i>
          </a>
        </div>
      </div>
      <p class="mb-2" style="font-size:.9rem;"><?= htmlspecialchars($f['message']) ?></p>

      <?php if ($f['admin_response']): ?>
        <div class="bg-light rounded p-2 mb-2" style="font-size:.82rem;border-left:3px solid #16a34a;">
          <strong class="text-success">Admin reply:</strong>
          <?= htmlspecialchars($f['admin_response']) ?>
        </div>
      <?php endif; ?>

      <!-- Response Form -->
      <?php if ($f['status'] !== 'resolved'): ?>
        <form method="POST" action="<?= BASE_URL ?>controllers/AdminController.php" class="mt-2">
          <input type="hidden" name="action" value="respond_feedback">
          <input type="hidden" name="feedback_id" value="<?= $f['id'] ?>">
          <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
          <div class="d-flex gap-2 align-items-start">
            <textarea name="admin_response" class="admin-input" rows="2"
                      placeholder="Type your response…"
                      style="flex:1;"><?= htmlspecialchars($f['admin_response'] ?? '') ?></textarea>
            <div class="d-flex flex-column gap-1">
              <select name="status" class="admin-input" style="min-width:130px;padding:.4rem .6rem;">
                <option value="responded">Mark as Responded</option>
                <option value="resolved">Mark as Resolved</option>
              </select>
              <button type="submit" class="btn-admin-primary">
                <i class="bi bi-send me-1"></i>Send
              </button>
            </div>
          </div>
        </form>
      <?php else: ?>
        <span class="badge badge-resolved rounded-pill"><i class="bi bi-check-circle me-1"></i>Resolved</span>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>
