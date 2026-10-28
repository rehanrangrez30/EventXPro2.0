<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

if (!is_logged_in()) {
    redirect('login.php');
}

$stmt = $pdo->prepare('SELECT full_name, email, phone, department FROM users WHERE user_id = ?');
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid form submission. Please try again.';
    }

    $fullName   = trim($_POST['full_name'] ?? '');
    $phone      = trim($_POST['phone'] ?? '');
    $department = trim($_POST['department'] ?? '');

    if ($fullName === '' || strlen($fullName) < 3) {
        $errors[] = 'Please enter your full name (at least 3 characters).';
    }
    if ($phone !== '' && !preg_match('/^[0-9]{10}$/', $phone)) {
        $errors[] = 'Phone number must be exactly 10 digits.';
    }

    if (empty($errors)) {
        $update = $pdo->prepare('UPDATE users SET full_name = ?, phone = ?, department = ? WHERE user_id = ?');
        $update->execute([$fullName, $phone ?: null, $department ?: null, $_SESSION['user_id']]);
        $_SESSION['full_name'] = $fullName;

        set_flash('success', 'Profile updated successfully.');
        redirect('profile.php');
    }

    $user = ['full_name' => $fullName, 'email' => $user['email'], 'phone' => $phone, 'department' => $department];
}

$pageTitle = 'My Profile';
include __DIR__ . '/includes/header.php';
?>

<section class="container py-5">
  <div class="row">
    <?php include __DIR__ . '/includes/sidebar.php'; ?>

    <div class="col-12 col-lg-9">
      <div class="ex-card" data-aos="fade-up">
        <h4 class="fw-bold mb-4">My Profile</h4>

        <?php if (!empty($errors)): ?>
          <div class="alert alert-danger">
            <ul class="mb-0 ps-3">
              <?php foreach ($errors as $error): ?><li><?= sanitize($error) ?></li><?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <form method="POST" novalidate>
          <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

          <div class="mb-3">
            <label class="form-label">Full Name</label>
            <input type="text" name="full_name" class="form-control" value="<?= sanitize($user['full_name']) ?>" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Email Address</label>
            <input type="email" class="form-control" value="<?= sanitize($user['email']) ?>" disabled>
            <div class="form-text">Email cannot be changed. Contact an administrator if needed.</div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Phone Number</label>
              <input type="text" name="phone" class="form-control" maxlength="10" value="<?= sanitize($user['phone'] ?? '') ?>">
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Department</label>
              <input type="text" name="department" class="form-control" value="<?= sanitize($user['department'] ?? '') ?>">
            </div>
          </div>

          <button type="submit" class="btn btn-primary">Save Changes</button>
        </form>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
