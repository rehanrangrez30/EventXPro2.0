<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$totalEvents = (int) $pdo->query("SELECT COUNT(*) FROM events WHERE status = 'published'")->fetchColumn();
$totalUsers = (int) $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();

$pageTitle = 'About';
include __DIR__ . '/includes/header.php';
?>

<section class="container py-5">
  <div class="row justify-content-center">
    <div class="col-lg-8 text-center" data-aos="fade-up">
      <h2 class="fw-bold mb-3">About <?= APP_NAME ?></h2>
      <p class="text-secondary">
        <?= APP_NAME ?> is a centralized platform that brings students,
        organizers, faculty and volunteers together to run college events
        without paperwork — from proposal and registration through
        attendance and certificates.
      </p>
    </div>
  </div>

  <div class="row g-4 mt-4">
    <div class="col-md-4" data-aos="fade-up">
      <div class="ex-card text-center h-100">
        <i class="fa-solid fa-bullseye fa-2x text-primary mb-3"></i>
        <h6 class="fw-semibold">Our Purpose</h6>
        <p class="text-secondary small mb-0">Give every campus event a single, reliable home — from planning to completion.</p>
      </div>
    </div>
    <div class="col-md-4" data-aos="fade-up">
      <div class="ex-card text-center h-100">
        <i class="fa-solid fa-users fa-2x text-primary mb-3"></i>
        <h6 class="fw-semibold">Built for Every Role</h6>
        <p class="text-secondary small mb-0">Administrators, organizers, faculty, students and volunteers each get a dashboard suited to their work.</p>
      </div>
    </div>
    <div class="col-md-4" data-aos="fade-up">
      <div class="ex-card text-center h-100">
        <i class="fa-solid fa-shield-halved fa-2x text-primary mb-3"></i>
        <h6 class="fw-semibold">Secure by Design</h6>
        <p class="text-secondary small mb-0">Hashed passwords, role-based access and full activity logging protect every action on the platform.</p>
      </div>
    </div>
  </div>

  <div class="ex-card text-center mt-5" data-aos="fade-up">
    <div class="row">
      <div class="col-6">
        <div class="ex-stat-number"><?= $totalUsers ?></div>
        <div class="text-secondary small">Community Members</div>
      </div>
      <div class="col-6">
        <div class="ex-stat-number"><?= $totalEvents ?></div>
        <div class="text-secondary small">Events Hosted</div>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
