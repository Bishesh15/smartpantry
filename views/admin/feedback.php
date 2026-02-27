<?php
$page_title = 'Manage Feedback';
require_once __DIR__ . '/../includes/admin-header.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../models/Feedback.php';

// Require admin login - redirect if not logged in
if (!isAdmin() && !isLoggedIn()) {
    redirect(BASE_URL . 'views/admin/login.php');
}
// If user tries to access, show error but allow viewing
if (isLoggedIn() && !isAdmin()) {
    $_SESSION['error'] = 'You are logged in as a regular user. This is an admin-only page. Please logout first to access admin panel.';
}

$feedback = new Feedback();

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$feedback_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$all_feedback = $feedback->getAll(null, 100);
$current_feedback = null;

if ($action === 'respond' && $feedback_id > 0) {
    $current_feedback = $feedback->getById($feedback_id);
}
?>

<div class="container">
    <div class="admin-page">
        <div class="admin-header">
            <h1>Manage Feedback</h1>
            <a href="<?php echo BASE_URL; ?>views/admin/dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>

        <?php if ($action === 'respond' && $current_feedback): ?>
            <!-- Respond to Feedback -->
            <div class="admin-form-container">
                <h2>Respond to Feedback</h2>
                
                <div class="feedback-view">
                    <div class="feedback-item">
                        <p><strong>From:</strong> <?php echo htmlspecialchars($current_feedback['name']); ?> 
                           (<?php echo htmlspecialchars($current_feedback['email']); ?>)</p>
                        <?php if ($current_feedback['username']): ?>
                            <p><strong>Username:</strong> <?php echo htmlspecialchars($current_feedback['username']); ?></p>
                        <?php endif; ?>
                        <p><strong>Date:</strong> <?php echo formatDateTime($current_feedback['created_at']); ?></p>
                        <p><strong>Status:</strong> <?php echo htmlspecialchars($current_feedback['status']); ?></p>
                        <div class="feedback-message">
                            <strong>Message:</strong>
                            <p><?php echo nl2br(htmlspecialchars($current_feedback['message'])); ?></p>
                        </div>
                        <?php if ($current_feedback['admin_response']): ?>
                            <div class="feedback-response">
                                <strong>Previous Response:</strong>
                                <p><?php echo nl2br(htmlspecialchars($current_feedback['admin_response'])); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <form method="POST" action="<?php echo BASE_URL; ?>controllers/AdminController.php" class="admin-form">
                    <input type="hidden" name="action" value="respond_feedback">
                    <input type="hidden" name="feedback_id" value="<?php echo $feedback_id; ?>">

                    <div class="form-group">
                        <label>Admin Response *</label>
                        <textarea name="admin_response" rows="6" required><?php echo $current_feedback['admin_response'] ? htmlspecialchars($current_feedback['admin_response']) : ''; ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Status</label>
                        <select name="status">
                            <option value="pending" <?php echo ($current_feedback['status'] === 'pending') ? 'selected' : ''; ?>>Pending</option>
                            <option value="responded" <?php echo ($current_feedback['status'] === 'responded') ? 'selected' : ''; ?>>Responded</option>
                            <option value="resolved" <?php echo ($current_feedback['status'] === 'resolved') ? 'selected' : ''; ?>>Resolved</option>
                        </select>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Save Response</button>
                        <a href="<?php echo BASE_URL; ?>views/admin/feedback.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <!-- Feedback List -->
            <div class="admin-table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Message</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($all_feedback as $fb): ?>
                            <tr>
                                <td><?php echo $fb['id']; ?></td>
                                <td><?php echo htmlspecialchars($fb['name']); ?></td>
                                <td><?php echo htmlspecialchars($fb['email']); ?></td>
                                <td><?php echo truncate($fb['message'], 50); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $fb['status']; ?>">
                                        <?php echo htmlspecialchars($fb['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo formatDate($fb['created_at']); ?></td>
                                <td>
                                    <a href="<?php echo BASE_URL; ?>views/admin/feedback.php?action=respond&id=<?php echo $fb['id']; ?>" class="btn btn-small">Respond</a>
                                    <a href="<?php echo BASE_URL; ?>controllers/AdminController.php?action=delete_feedback&id=<?php echo $fb['id']; ?>" 
                                       class="btn btn-small btn-danger" 
                                       onclick="return confirm('Are you sure you want to delete this feedback?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>

