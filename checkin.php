<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$eventId = (int) ($_GET['event_id'] ?? 0);

$eventStmt = $pdo->prepare("SELECT event_id, title, event_date, venue FROM events WHERE event_id = ? AND status IN ('published','archived')");
$eventStmt->execute([$eventId]);
$event = $eventStmt->fetch();

$pageTitle = 'Event Check-in';
$result = null; // 'success' | 'already' | 'not_registered' | 'not_today' | 'not_found' | 'login_required'

if (!$event) {
    $result = 'not_found';
} elseif (!is_logged_in()) {
    $result = 'login_required';
} elseif (($_SESSION['role_name'] ?? '') !== 'student') {
    $result = 'wrong_role';
} else {
    $regStmt = $pdo->prepare("SELECT registration_id FROM registrations WHERE event_id = ? AND student_id = ? AND status = 'registered'");
    $regStmt->execute([$eventId, $_SESSION['user_id']]);
    $registration = $regStmt->fetch();

    if (!$registration) {
        $result = 'not_registered';
    } elseif (strtotime($event['event_date']) > strtotime(date('Y-m-d'))) {
        $result = 'not_today';
    } else {
        $already = $pdo->prepare("SELECT attendance_status FROM attendance WHERE registration_id = ?");
        $already->execute([$registration['registration_id']]);
        $existing = $already->fetch();

        if ($existing && $existing['attendance_status'] === 'present') {
            $result = 'already';
        } else {
            $upsert = $pdo->prepare(
                'INSERT INTO attendance (registration_id, event_id, student_id, attendance_status, check_in_time, marked_by, remarks)
                 VALUES (?, ?, ?, "present", NOW(), ?, "Self check-in via QR code")
                 ON DUPLICATE KEY UPDATE attendance_status = "present", check_in_time = NOW(), marked_by = VALUES(marked_by), remarks = VALUES(remarks)'
            );
            $upsert->execute([$registration['registration_id'], $eventId, $_SESSION['user_id'], $_SESSION['user_id']]);

            $logStmt = $pdo->prepare('INSERT INTO activity_logs (user_id, action, description, ip_address) VALUES (?, ?, ?, ?)');
            $logStmt->execute([$_SESSION['user_id'], 'qr_checkin', 'Self check-in: ' . $event['title'], $_SERVER['REMOTE_ADDR'] ?? null]);

            $result = 'success';
        }
    }
}

include __DIR__ . '/includes/header.php';

$config = [
    'success'         => ['icon' => 'fa-circle-check', 'color' => 'success', 'title' => 'Checked In!', 'msg' => 'Your attendance for "' . ($event['title'] ?? '') . '" has been marked present.'],
    'already'         => ['icon' => 'fa-circle-check', 'color' => 'success', 'title' => 'Already Checked In', 'msg' => 'You were already marked present for this event.'],
    'not_registered'  => ['icon' => 'fa-circle-xmark', 'color' => 'danger', 'title' => 'Not Registered', 'msg' => 'You are not registered for this event, so check-in isn\'t available.'],
    'not_today'       => ['icon' => 'fa-clock', 'color' => 'warning', 'title' => 'Too Early', 'msg' => 'Check-in opens on the day of the event.'],
    'not_found'       => ['icon' => 'fa-circle-question', 'color' => 'secondary', 'title' => 'Event Not Found', 'msg' => 'This check-in code doesn\'t match a valid event.'],
    'login_required'  => ['icon' => 'fa-right-to-bracket', 'color' => 'primary', 'title' => 'Please Log In', 'msg' => 'Log in with your student account, then scan the code again.'],
    'wrong_role'      => ['icon' => 'fa-circle-xmark', 'color' => 'danger', 'title' => 'Students Only', 'msg' => 'Only student accounts can self check-in.'],
][$result];
?>

<section class="container py-5 text-center">
  <div class="ex-card mx-auto" style="max-width:480px;" data-aos="fade-up">
    <i class="fa-solid <?= $config['icon'] ?> text-<?= $config['color'] ?> fa-3x mb-3"></i>
    <h4 class="fw-bold mb-2"><?= $config['title'] ?></h4>
    <p class="text-secondary mb-4"><?= sanitize($config['msg']) ?></p>

    <?php if ($event): ?>
      <p class="small text-secondary mb-4">
        <?= sanitize($event['title']) ?> — <?= date('d M Y', strtotime($event['event_date'])) ?> at <?= sanitize($event['venue']) ?>
      </p>
    <?php endif; ?>

    <?php if ($result === 'login_required'): ?>
      <a href="<?= APP_URL ?>/login.php?redirect=checkin.php%3Fevent_id%3D<?= $eventId ?>" class="btn btn-primary">Log In</a>
    <?php elseif (in_array($result, ['success', 'already'], true)): ?>
      <a href="<?= APP_URL ?>/student/dashboard.php" class="btn btn-primary">Go to Dashboard</a>
    <?php else: ?>
      <a href="<?= APP_URL ?>/index.php" class="btn btn-outline-secondary">Back to Home</a>
    <?php endif; ?>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
