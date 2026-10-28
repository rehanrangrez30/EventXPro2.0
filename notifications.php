<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

if (!is_logged_in()) {
    redirect('login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_read_id'])) {
    if (verify_csrf($_POST['csrf_token'] ?? '')) {
        $upd = $pdo->prepare('UPDATE notifications SET is_read = 1 WHERE notification_id = ? AND user_id = ?');
        $upd->execute([(int) $_POST['mark_read_id'], $_SESSION['user_id']]);
    }
    redirect('notifications.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_all_read'])) {
    if (verify_csrf($_POST['csrf_token'] ?? '')) {
        $upd = $pdo->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = ?');
        $upd->execute([$_SESSION['user_id']]);
    }
    redirect('notifications.php');
}

$stmt = $pdo->prepare('SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 50');
$stmt->execute([$_SESSION['user_id']]);
$notifications = $stmt->fetchAll();

$pageTitle = 'Notifications';
include __DIR__ . '/includes/header.php';
?>

<section class="container py-5">
  <div class="row justify-content-center">
    <div class="col-lg-8">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Notifications</h4>
        <?php if (!empty($notifications)): ?>
          <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <button type="submit" name="mark_all_read" value="1" class="btn btn-sm btn-outline-secondary">Mark all read</button>
          </form>
        <?php endif; ?>
      </div>

      <?php if (empty($notifications)): ?>
        <div class="ex-card text-center py-5"><p class="text-secondary mb-0">You have no notifications yet.</p></div>
      <?php else: ?>
        <div class="ex-card p-0">
          <ul class="list-group list-group-flush">
            <?php foreach ($notifications as $n): ?>
              <li class="list-group-item d-flex justify-content-between align-items-start <?= !$n['is_read'] ? 'bg-primary bg-opacity-10' : '' ?>">
                <div>
                  <strong><?= sanitize($n['title']) ?></strong>
                  <?php if (!$n['is_read']): ?><span class="badge text-bg-primary ms-1">New</span><?php endif; ?>
                  <p class="text-secondary small mb-1 mt-1"><?= sanitize($n['message']) ?></p>
                  <span class="text-secondary small"><?= date('d M Y, H:i', strtotime($n['created_at'])) ?></span>
                  <?php if ($n['action_url']): ?>
                    <a href="<?= APP_URL . '/' . sanitize($n['action_url']) ?>" class="ms-2 small">View →</a>
                  <?php endif; ?>
                </div>
                <?php if (!$n['is_read']): ?>
                  <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <input type="hidden" name="mark_read_id" value="<?= $n['notification_id'] ?>">
                    <button type="submit" class="btn btn-sm btn-outline-primary">Mark read</button>
                  </form>
                <?php endif; ?>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
