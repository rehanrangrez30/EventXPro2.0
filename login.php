<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) {
    redirect($_SESSION['role_name'] . '/dashboard.php');
}

// Optional post-login redirect target (e.g. from checkin.php). Whitelisted
// to a same-site relative .php path so this can't be abused as an open
// redirect; anything else silently falls back to the normal dashboard.
$redirectTarget = $_GET['redirect'] ?? $_POST['redirect'] ?? '';
if (!preg_match('/^[a-zA-Z0-9_\-\/]+\.php(\?[a-zA-Z0-9_=&%.]*)?$/', $redirectTarget)) {
    $redirectTarget = '';
}

$errors = [];
$oldEmail = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid form submission. Please try again.';
    }

    $oldEmail = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($oldEmail === '' || $password === '') {
        $errors[] = 'Please enter both email and password.';
    }

    if (empty($errors)) {
        // Brute-force protection: block further attempts if this IP has
        // racked up 5+ failed logins in the last 15 minutes.
        $attemptCheck = $pdo->prepare(
            "SELECT COUNT(*) FROM login_logs
             WHERE ip_address = ? AND login_status = 'failed' AND login_time >= NOW() - INTERVAL 15 MINUTE"
        );
        $attemptCheck->execute([$_SERVER['REMOTE_ADDR'] ?? '']);
        $recentFailures = (int) $attemptCheck->fetchColumn();

        if ($recentFailures >= 5) {
            $errors[] = 'Too many failed login attempts. Please wait 15 minutes and try again.';
            eventx_log_security('Login blocked (brute-force threshold) for IP ' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare(
            'SELECT u.user_id, u.full_name, u.email, u.password_hash, u.status, r.role_name
             FROM users u JOIN roles r ON u.role_id = r.role_id
             WHERE u.email = ?'
        );
        $stmt->execute([$oldEmail]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $errors[] = 'Invalid email or password.';
            $failLog = $pdo->prepare('INSERT INTO login_logs (user_id, login_time, login_status, ip_address, failure_reason) VALUES (?, NOW(), "failed", ?, ?)');
            $failLog->execute([$user['user_id'] ?? null, $_SERVER['REMOTE_ADDR'] ?? null, $user ? 'Invalid password' : 'User not found']);
        } elseif ($user['status'] !== 'active') {
            $errors[] = 'Your account is not active. Please contact the administrator.';
            $failLog = $pdo->prepare('INSERT INTO login_logs (user_id, login_time, login_status, ip_address, failure_reason) VALUES (?, NOW(), "blocked", ?, "Inactive account")');
            $failLog->execute([$user['user_id'], $_SERVER['REMOTE_ADDR'] ?? null]);
        }
    }

    if (empty($errors)) {
        session_regenerate_id(true);
        $_SESSION['user_id']   = $user['user_id'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['email']     = $user['email'];
        $_SESSION['role_name'] = $user['role_name'];

        $logStmt = $pdo->prepare('INSERT INTO activity_logs (user_id, action, description, ip_address) VALUES (?, ?, ?, ?)');
        $logStmt->execute([$user['user_id'], 'login', 'User logged in', $_SERVER['REMOTE_ADDR'] ?? null]);

        $loginLogStmt = $pdo->prepare('INSERT INTO login_logs (user_id, login_time, login_status, ip_address) VALUES (?, NOW(), "success", ?)');
        $loginLogStmt->execute([$user['user_id'], $_SERVER['REMOTE_ADDR'] ?? null]);
        $_SESSION['login_log_id'] = (int) $pdo->lastInsertId();

        if (!empty($_POST['remember_me'])) {
            $selector  = bin2hex(random_bytes(9));
            $validator = bin2hex(random_bytes(33));
            $expiresAt = date('Y-m-d H:i:s', time() + 30 * 24 * 60 * 60); // 30 days

            $tokenStmt = $pdo->prepare(
                'INSERT INTO remember_tokens (user_id, selector, token_hash, expires_at) VALUES (?, ?, ?, ?)'
            );
            $tokenStmt->execute([$user['user_id'], $selector, hash('sha256', $validator), $expiresAt]);

            setcookie('eventx_remember', $selector . ':' . $validator, [
                'expires'  => time() + 30 * 24 * 60 * 60,
                'path'     => '/',
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        }

        redirect($redirectTarget !== '' ? $redirectTarget : $user['role_name'] . '/dashboard.php');
    }
}

$pageTitle = 'Login';
include __DIR__ . '/includes/header.php';
?>

<section class="container py-5">
  <div class="ex-auth-shell" data-aos="fade-up">
    <div class="ex-auth-visual">
      <img src="<?= APP_URL ?>/assets/images/icons/logo-nav.png" alt="<?= APP_NAME ?>" class="ex-auth-logo">
      <h3>Welcome back to <?= APP_NAME ?></h3>
      <p><?= APP_TAGLINE ?>. Log in to pick up right where you left off.</p>
      <ul>
        <li><i class="fa-solid fa-circle-check"></i>Browse and register for campus events</li>
        <li><i class="fa-solid fa-circle-check"></i>Track certificates and attendance</li>
        <li><i class="fa-solid fa-circle-check"></i>Role-based dashboards for every position</li>
      </ul>
    </div>

    <div class="ex-auth-form-panel">
      <div class="ex-auth-form-inner">
        <h3 class="fw-bold mb-1">Log In</h3>
        <p class="text-secondary mb-4">Enter your details to access your account.</p>

        <?php if (!empty($errors)): ?>
          <div class="alert alert-danger">
            <ul class="mb-0 ps-3">
              <?php foreach ($errors as $error): ?>
                <li><?= sanitize($error) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <form method="POST" class="ex-auth-form" novalidate>
          <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

          <div class="mb-3">
            <label class="form-label">Email Address</label>
            <input type="email" name="email" class="form-control" value="<?= sanitize($oldEmail) ?>" required autofocus>
          </div>

          <div class="mb-3">
            <label class="form-label">Password</label>
            <div class="position-relative">
              <input type="password" name="password" id="loginPassword" class="form-control" required>
              <button type="button" class="ex-password-toggle" data-target="loginPassword" tabindex="-1" aria-label="Show password">
                <i class="fa-regular fa-eye"></i>
              </button>
            </div>
          </div>

          <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="form-check">
              <input type="checkbox" name="remember_me" value="1" class="form-check-input" id="rememberMe">
              <label class="form-check-label small" for="rememberMe">Remember me</label>
            </div>
            <a href="<?= APP_URL ?>/forgot_password.php" class="small">Forgot password?</a>
          </div>

          <button type="submit" class="ex-btn ex-btn-primary w-100 mt-2 justify-content-center">Log In</button>
        </form>

        <p class="text-center text-secondary small mt-4 mb-0">
          Don't have an account? <a href="<?= APP_URL ?>/register.php">Register here</a>
        </p>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
