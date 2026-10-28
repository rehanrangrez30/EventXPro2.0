<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$errors = [];
$old = ['name' => '', 'email' => '', 'subject' => '', 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid form submission. Please try again.';
    }

    $old['name']    = trim($_POST['name'] ?? '');
    $old['email']   = trim($_POST['email'] ?? '');
    $old['subject'] = trim($_POST['subject'] ?? '');
    $old['message'] = trim($_POST['message'] ?? '');

    if (strlen($old['name']) < 2) {
        $errors[] = 'Please enter your name.';
    }
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if (strlen($old['subject']) < 3) {
        $errors[] = 'Please enter a subject.';
    }
    if (strlen($old['message']) < 10) {
        $errors[] = 'Your message should be at least 10 characters.';
    }

    if (empty($errors)) {
        $logStmt = $pdo->prepare('INSERT INTO activity_logs (user_id, action, description, ip_address) VALUES (?, ?, ?, ?)');
        $logStmt->execute([
            $_SESSION['user_id'] ?? null,
            'contact_message',
            $old['name'] . ' <' . $old['email'] . '> — ' . $old['subject'] . ': ' . $old['message'],
            $_SERVER['REMOTE_ADDR'] ?? null,
        ]);
        set_flash('success', 'Thank you! Your message has been received. We will get back to you soon.');
        redirect('contact.php');
    }
}

$contactEmail = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'contact_email'");
$contactEmail->execute();
$displayEmail = $contactEmail->fetchColumn() ?: 'support@eventxpro.local';

$pageTitle = 'Contact';
include __DIR__ . '/includes/header.php';
?>

<section class="container py-5">
  <div class="row g-4">
    <div class="col-lg-5" data-aos="fade-right">
      <h2 class="fw-bold mb-3">Get in Touch</h2>
      <p class="text-secondary mb-4">Questions about an event, your account, or the platform? Send us a message.</p>
      <div class="ex-card">
        <p class="mb-2"><i class="fa-solid fa-envelope text-primary me-2"></i><?= sanitize($displayEmail) ?></p>
        <p class="mb-0"><i class="fa-solid fa-location-dot text-primary me-2"></i>College Campus, India</p>
      </div>
    </div>

    <div class="col-lg-7" data-aos="fade-left">
      <div class="ex-card">
        <?php if (!empty($errors)): ?>
          <div class="alert alert-danger">
            <ul class="mb-0 ps-3"><?php foreach ($errors as $e): ?><li><?= sanitize($e) ?></li><?php endforeach; ?></ul>
          </div>
        <?php endif; ?>

        <form method="POST" novalidate>
          <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Name <span class="text-danger">*</span></label>
              <input type="text" name="name" class="form-control" value="<?= sanitize($old['name']) ?>" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Email <span class="text-danger">*</span></label>
              <input type="email" name="email" class="form-control" value="<?= sanitize($old['email']) ?>" required>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Subject <span class="text-danger">*</span></label>
            <input type="text" name="subject" class="form-control" value="<?= sanitize($old['subject']) ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Message <span class="text-danger">*</span></label>
            <textarea name="message" class="form-control" rows="4" required><?= sanitize($old['message']) ?></textarea>
          </div>
          <button type="submit" class="btn btn-primary">Send Message</button>
        </form>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
