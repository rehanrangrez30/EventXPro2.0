<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) {
    redirect($_SESSION['role_name'] . '/dashboard.php');
}

$errors = [];
$old = ['full_name' => '', 'email' => '', 'phone' => '', 'department' => '', 'role' => 'student'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid form submission. Please try again.';
    }

    $old['full_name']  = trim($_POST['full_name'] ?? '');
    $old['email']      = trim($_POST['email'] ?? '');
    $old['phone']      = trim($_POST['phone'] ?? '');
    $old['department'] = trim($_POST['department'] ?? '');
    $old['role']       = $_POST['role'] ?? 'student';
    $password          = $_POST['password'] ?? '';
    $confirmPassword   = $_POST['confirm_password'] ?? '';

    $allowedRoles = ['student', 'faculty', 'organizer', 'volunteer'];

    if ($old['full_name'] === '' || strlen($old['full_name']) < 3) {
        $errors[] = 'Please enter your full name (at least 3 characters).';
    }
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if ($old['phone'] !== '' && !preg_match('/^[0-9]{10}$/', $old['phone'])) {
        $errors[] = 'Phone number must be exactly 10 digits.';
    }
    if (!in_array($old['role'], $allowedRoles, true)) {
        $errors[] = 'Please select a valid account type.';
    }
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
        $check = $pdo->prepare('SELECT user_id FROM users WHERE email = ?');
        $check->execute([$old['email']]);
        if ($check->fetch()) {
            $errors[] = 'An account with this email already exists.';
        }
    }

    if (empty($errors)) {
        $roleStmt = $pdo->prepare('SELECT role_id FROM roles WHERE role_name = ?');
        $roleStmt->execute([$old['role']]);
        $roleRow = $roleStmt->fetch();

        $insert = $pdo->prepare(
            'INSERT INTO users (role_id, full_name, email, phone, password_hash, department)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $insert->execute([
            $roleRow['role_id'],
            $old['full_name'],
            $old['email'],
            $old['phone'] ?: null,
            password_hash($password, PASSWORD_DEFAULT),
            $old['department'] ?: null,
        ]);

        $logStmt = $pdo->prepare('INSERT INTO activity_logs (user_id, action, description, ip_address) VALUES (?, ?, ?, ?)');
        $logStmt->execute([$pdo->lastInsertId(), 'register', 'New account created', $_SERVER['REMOTE_ADDR'] ?? null]);

        set_flash('success', 'Account created successfully. Please log in.');
        redirect('login.php');
    }
}

$pageTitle = 'Register';
include __DIR__ . '/includes/header.php';
?>

<section class="container py-5">
  <div class="ex-auth-shell ex-auth-shell-wide" data-aos="fade-up">
    <div class="ex-auth-visual">
      <img src="<?= APP_URL ?>/assets/images/icons/logo-nav.png" alt="<?= APP_NAME ?>" class="ex-auth-logo">
      <h3>Join <?= APP_NAME ?></h3>
      <p>Create your account in a few seconds and get access to every event on campus.</p>
      <ul>
        <li><i class="fa-solid fa-circle-check"></i>Students, Faculty, Organizers &amp; Volunteers welcome</li>
        <li><i class="fa-solid fa-circle-check"></i>One account, a dashboard built for your role</li>
        <li><i class="fa-solid fa-circle-check"></i>Secure by default — hashed passwords, CSRF-protected</li>
      </ul>
    </div>

    <div class="ex-auth-form-panel">
      <div class="ex-auth-form-inner" style="max-width:460px;">
        <h3 class="fw-bold mb-1">Create Your Account</h3>
        <p class="text-secondary mb-4">Join <?= APP_NAME ?> in a few seconds.</p>

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
            <label class="form-label">Full Name <span class="text-danger">*</span></label>
            <input type="text" name="full_name" class="form-control" value="<?= sanitize($old['full_name']) ?>" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Email Address <span class="text-danger">*</span></label>
            <input type="email" name="email" class="form-control" value="<?= sanitize($old['email']) ?>" required>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Phone Number</label>
              <input type="text" name="phone" class="form-control" maxlength="10" value="<?= sanitize($old['phone']) ?>">
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Department</label>
              <input type="text" name="department" class="form-control" value="<?= sanitize($old['department']) ?>">
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Account Type <span class="text-danger">*</span></label>
            <select name="role" class="form-select" required>
              <?php foreach (['student' => 'Student', 'faculty' => 'Faculty', 'organizer' => 'Event Organizer', 'volunteer' => 'Volunteer'] as $value => $label): ?>
                <option value="<?= $value ?>" <?= $old['role'] === $value ? 'selected' : '' ?>><?= $label ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Password <span class="text-danger">*</span></label>
              <div class="position-relative">
                <input type="password" name="password" id="regPassword" class="form-control" minlength="8"
                       pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}"
                       title="At least 8 characters, with uppercase, lowercase, a number and a special character" required>
                <button type="button" class="ex-password-toggle" data-target="regPassword" tabindex="-1" aria-label="Show password">
                  <i class="fa-regular fa-eye"></i>
                </button>
              </div>
              <div class="form-text">At least 8 characters, with uppercase, lowercase, a number and a special character.</div>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
              <div class="position-relative">
                <input type="password" name="confirm_password" id="regConfirmPassword" class="form-control" minlength="8" required>
                <button type="button" class="ex-password-toggle" data-target="regConfirmPassword" tabindex="-1" aria-label="Show password">
                  <i class="fa-regular fa-eye"></i>
                </button>
              </div>
            </div>
          </div>

          <button type="submit" class="ex-btn ex-btn-primary w-100 mt-2">Create Account</button>
        </form>

        <p class="text-center text-secondary small mt-4 mb-0">
          Already have an account? <a href="<?= APP_URL ?>/login.php">Log in</a>
        </p>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
