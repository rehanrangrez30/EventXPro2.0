<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$token = $_GET['token'] ?? $_POST['token'] ?? '';
$errors = [];
$validToken = false;
$resetRow = null;

if ($token !== '') {
    $tokenHash = hash('sha256', $token);
    $stmt = $pdo->prepare(
        "SELECT reset_id, user_id, expires_at FROM password_resets
         WHERE token_hash = ? AND used = 0 ORDER BY reset_id DESC LIMIT 1"
    );
    $stmt->execute([$tokenHash]);
    $resetRow = $stmt->fetch();

    if ($resetRow && strtotime($resetRow['expires_at']) > time()) {
        $validToken = true;
    }
}

if (!$validToken) {
    $errors[] = 'This reset link is invalid or has expired. Please request a new one.';
}

if ($validToken && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid form submission. Please try again.';
    }

    $password        = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long.';
    } elseif (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password)
        || !preg_match('/[0-9]/', $password) || !preg_match('/[^A-Za-z0-9]/', $password)) {
        $errors[] = 'Password must include an uppercase letter, a lowercase letter, a number and a special character.';
    }
    if ($password !== $confirmPassword) {
        $errors[] = 'Password and Confirm Password do not match.';
    }

    if (empty($errors)) {
        $update = $pdo->prepare('UPDATE users SET password_hash = ? WHERE user_id = ?');
        $update->execute([password_hash($password, PASSWORD_DEFAULT), $resetRow['user_id']]);

        $markUsed = $pdo->prepare('UPDATE password_resets SET used = 1 WHERE reset_id = ?');
        $markUsed->execute([$resetRow['reset_id']]);

        // Invalidate any remember-me tokens too, since the password changed.
        $pdo->prepare('DELETE FROM remember_tokens WHERE user_id = ?')->execute([$resetRow['user_id']]);

        $logStmt = $pdo->prepare('INSERT INTO activity_logs (user_id, action, description, ip_address) VALUES (?, ?, ?, ?)');
        $logStmt->execute([$resetRow['user_id'], 'password_reset', 'Password reset via email link', $_SERVER['REMOTE_ADDR'] ?? null]);

        set_flash('success', 'Your password has been reset. Please log in with your new password.');
        redirect('login.php');
    }
}

$pageTitle = 'Reset Password';
include __DIR__ . '/includes/header.php';
?>

<section class="container py-5">
  <div class="ex-auth-shell" data-aos="fade-up">
    <div class="ex-auth-visual">
      <img src="<?= APP_URL ?>/assets/images/icons/logo-nav.png" alt="<?= APP_NAME ?>" class="ex-auth-logo">
      <h3>Choose a New Password</h3>
      <p>Pick something strong you haven't used before — this will replace your current password immediately once saved.</p>
      <ul>
        <li><i class="fa-solid fa-circle-check"></i>Uppercase, lowercase, a number and a special character</li>
        <li><i class="fa-solid fa-circle-check"></i>At least 8 characters long</li>
        <li><i class="fa-solid fa-circle-check"></i>Other signed-in devices will need to log in again</li>
      </ul>
    </div>

    <div class="ex-auth-form-panel">
      <div class="ex-auth-form-inner">
        <h3 class="fw-bold mb-1">Reset Password</h3>
        <p class="text-secondary mb-4">Choose a new password for your account.</p>

        <?php if (!empty($errors)): ?>
          <div class="alert alert-danger">
            <ul class="mb-0 ps-3"><?php foreach ($errors as $e): ?><li><?= sanitize($e) ?></li><?php endforeach; ?></ul>
          </div>
        <?php endif; ?>

        <?php if ($validToken): ?>
          <form method="POST" class="ex-auth-form" novalidate>
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <input type="hidden" name="token" value="<?= sanitize($token) ?>">

            <div class="mb-3">
              <label class="form-label">New Password</label>
              <div class="position-relative">
                <input type="password" name="password" id="resetPassword" class="form-control" minlength="8"
                       pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}" required>
                <button type="button" class="ex-password-toggle" data-target="resetPassword" tabindex="-1" aria-label="Show password">
                  <i class="fa-regular fa-eye"></i>
                </button>
              </div>
              <div class="form-text">At least 8 characters, with uppercase, lowercase, a number and a special character.</div>
            </div>
            <div class="mb-3">
              <label class="form-label">Confirm New Password</label>
              <div class="position-relative">
                <input type="password" name="confirm_password" id="resetPasswordConfirm" class="form-control" minlength="8" required>
                <button type="button" class="ex-password-toggle" data-target="resetPasswordConfirm" tabindex="-1" aria-label="Show password">
                  <i class="fa-regular fa-eye"></i>
                </button>
              </div>
            </div>
            <button type="submit" class="ex-btn ex-btn-primary w-100 mt-2 justify-content-center">Reset Password</button>
          </form>
        <?php else: ?>
          <a href="<?= APP_URL ?>/forgot_password.php" class="ex-btn ex-btn-outline w-100 justify-content-center">Request a New Link</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
