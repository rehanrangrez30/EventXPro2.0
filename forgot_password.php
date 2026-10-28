<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) {
    redirect($_SESSION['role_name'] . '/dashboard.php');
}

$errors = [];
$devResetLink = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid form submission. Please try again.';
    }

    $email = trim($_POST['email'] ?? '');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare('SELECT user_id, full_name FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // Always show the same message whether or not the email exists,
        // so the form can't be used to guess registered addresses.
        if ($user) {
            $token     = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', time() + 60 * 60); // 1 hour

            $insert = $pdo->prepare(
                'INSERT INTO password_resets (user_id, token_hash, expires_at) VALUES (?, ?, ?)'
            );
            $insert->execute([$user['user_id'], hash('sha256', $token), $expiresAt]);

            $resetLink = APP_URL . '/reset_password.php?token=' . $token;

            $sent = @mail(
                $email,
                'Reset your ' . APP_NAME . ' password',
                "Hello {$user['full_name']},\n\nUse this link to reset your password (valid 1 hour):\n$resetLink\n\nIf you didn't request this, you can ignore this email.",
                'From: no-reply@eventxpro.local'
            );

            // Most local XAMPP setups have no SMTP configured, so mail()
            // will typically fail. Show the link directly in that case
            // (development convenience) rather than leaving the user stuck.
            if (!$sent && APP_ENV === 'development') {
                $devResetLink = $resetLink;
            }

            $logStmt = $pdo->prepare('INSERT INTO activity_logs (user_id, action, description, ip_address) VALUES (?, ?, ?, ?)');
            $logStmt->execute([$user['user_id'], 'password_reset_request', 'Password reset link generated', $_SERVER['REMOTE_ADDR'] ?? null]);
        }

        set_flash('success', 'If an account with that email exists, a password reset link has been sent.');
        if ($devResetLink === null) {
            redirect('forgot_password.php');
        }
    }
}

$pageTitle = 'Forgot Password';
include __DIR__ . '/includes/header.php';
?>

<section class="container py-5">
  <div class="ex-auth-shell" data-aos="fade-up">
    <div class="ex-auth-visual">
      <img src="<?= APP_URL ?>/assets/images/icons/logo-nav.png" alt="<?= APP_NAME ?>" class="ex-auth-logo">
      <h3>Reset Your Password</h3>
      <p>No worries — it happens. Enter the email on your account and we'll send you a secure link to set a new one.</p>
      <ul>
        <li><i class="fa-solid fa-circle-check"></i>Link expires after 1 hour for security</li>
        <li><i class="fa-solid fa-circle-check"></i>We never reveal whether an email is registered</li>
        <li><i class="fa-solid fa-circle-check"></i>Your existing password stays valid until you reset it</li>
      </ul>
    </div>

    <div class="ex-auth-form-panel">
      <div class="ex-auth-form-inner">
        <h3 class="fw-bold mb-1">Forgot Password</h3>
        <p class="text-secondary mb-4">Enter your email and we'll send you a reset link.</p>

        <?php if (!empty($errors)): ?>
          <div class="alert alert-danger">
            <ul class="mb-0 ps-3"><?php foreach ($errors as $e): ?><li><?= sanitize($e) ?></li><?php endforeach; ?></ul>
          </div>
        <?php endif; ?>

        <?php if ($devResetLink): ?>
          <div class="alert alert-info small">
            No mail server is configured on this local XAMPP install, so here's
            your reset link directly (development mode only):<br>
            <a href="<?= sanitize($devResetLink) ?>"><?= sanitize($devResetLink) ?></a>
          </div>
        <?php endif; ?>

        <form method="POST" class="ex-auth-form" novalidate>
          <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
          <div class="mb-3">
            <label class="form-label">Email Address</label>
            <input type="email" name="email" class="form-control" required autofocus>
          </div>
          <button type="submit" class="ex-btn ex-btn-primary w-100 mt-2 justify-content-center">Send Reset Link</button>
        </form>

        <p class="text-center text-secondary small mt-4 mb-0">
          <a href="<?= APP_URL ?>/login.php">Back to Login</a>
        </p>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
