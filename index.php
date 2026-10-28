<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
$pageTitle = 'Home';
include __DIR__ . '/includes/header.php';

// Live counts for the stats section.
$totalUsers = (int) $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalEvents = (int) $pdo->query("SELECT COUNT(*) FROM events WHERE status = 'published'")->fetchColumn();

$featuredEvents = $pdo->query(
    "SELECT e.event_id, e.title, e.event_date, e.venue, c.category_name, c.icon_class
     FROM events e LEFT JOIN event_categories c ON e.category_id = c.category_id
     WHERE e.status = 'published' AND e.event_date >= CURDATE()
     ORDER BY e.event_date ASC LIMIT 3"
)->fetchAll();
?>

<!-- Hero Section -->
<section class="ex-hero text-center">
  <div class="container position-relative" data-aos="fade-up">
    <span class="ex-hero-badge mb-4">
      <i class="fa-solid fa-graduation-cap me-2"></i>COLLEGE EVENT MANAGEMENT PLATFORM
    </span>
    <img src="<?= APP_URL ?>/assets/images/icons/logo-hero.png" alt="<?= APP_NAME ?>" width="140" height="140"
         class="mb-4 d-block mx-auto" style="filter: drop-shadow(0 8px 24px rgba(0,0,0,.35));">
    <h1 class="ex-hero-title mb-4">Run Every College Event<br class="d-none d-md-block"> Without the Paperwork</h1>
    <p class="ex-hero-lead mb-5 mx-auto">
      <?= APP_NAME ?> brings organizers, faculty, students and volunteers onto one platform —
      from event approval to attendance and certificates.
    </p>
    <div class="d-flex flex-wrap justify-content-center gap-3">
      <a href="<?= APP_URL ?>/register.php" class="ex-btn ex-btn-light">
        Get Started <i class="fa-solid fa-arrow-right ms-2"></i>
      </a>
      <a href="<?= APP_URL ?>/events.php" class="ex-btn ex-btn-outline-light">
        <i class="fa-regular fa-calendar me-2"></i>Browse Events
      </a>
    </div>
  </div>
</section>

<!-- About Section -->
<section class="container py-5">
  <div class="row align-items-center g-5">
    <div class="col-lg-6" data-aos="fade-right">
      <h2 class="fw-bold mb-3">Built for Every Role on Campus</h2>
      <p class="text-secondary">
        Whether you're approving a proposal as faculty, organizing a fest as a committee lead,
        registering as a student, or coordinating logistics as a volunteer —
        <?= APP_NAME ?> gives each role its own dashboard and workflow.
      </p>
      <ul class="ex-feature-list">
        <li><span class="ex-feature-check"><i class="fa-solid fa-check"></i></span>Centralized event lifecycle management</li>
        <li><span class="ex-feature-check"><i class="fa-solid fa-check"></i></span>Role-based dashboards and permissions</li>
        <li><span class="ex-feature-check"><i class="fa-solid fa-check"></i></span>Secure registration &amp; attendance tracking</li>
      </ul>
    </div>
    <div class="col-lg-6" data-aos="fade-left">
      <div class="ex-dashboard-visual">
      <div class="ex-card">
        <h5 class="fw-semibold mb-4">Platform Snapshot</h5>
        <div class="ex-stats-strip">
          <div class="ex-stat-block">
            <div class="ex-stat-icon"><i class="fa-solid fa-users"></i></div>
            <div class="ex-stat-number ex-counter" data-value="<?= $totalUsers ?>">0</div>
            <div class="ex-stat-label">Registered Users</div>
          </div>
          <div class="ex-stat-block">
            <div class="ex-stat-icon"><i class="fa-solid fa-calendar-check"></i></div>
            <div class="ex-stat-number ex-counter" data-value="<?= $totalEvents ?>">0</div>
            <div class="ex-stat-label">Published Events</div>
          </div>
        </div>
      </div>
      </div>
    </div>
  </div>
</section>

<!-- Event Categories Preview -->
<section class="container py-5">
  <h2 class="fw-bold text-center mb-5" data-aos="fade-up">Popular Event Categories</h2>
  <div class="row g-4">
    <?php
    $categories = [
        ['icon' => 'fa-microphone',     'name' => 'Technical Seminars'],
        ['icon' => 'fa-music',          'name' => 'Cultural Fests'],
        ['icon' => 'fa-basketball',     'name' => 'Sports Events'],
        ['icon' => 'fa-hands-holding',  'name' => 'Workshops'],
    ];
    foreach ($categories as $cat): ?>
      <div class="col-md-3 col-6" data-aos="zoom-in">
        <a href="<?= APP_URL ?>/events.php" class="ex-category-tile ex-tilt">
          <div class="ex-category-icon"><i class="fa-solid <?= $cat['icon'] ?>"></i></div>
          <div class="ex-category-name"><?= $cat['name'] ?></div>
        </a>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- Featured Events -->
<section class="container py-5">
  <h2 class="fw-bold text-center mb-5" data-aos="fade-up">Featured Upcoming Events</h2>

  <?php if (empty($featuredEvents)): ?>
    <div class="ex-card text-center mx-auto" style="max-width:480px;" data-aos="fade-up">
      <p class="text-secondary mb-0">No Upcoming Events Available.</p>
    </div>
  <?php else: ?>
    <div class="row g-4">
      <?php foreach ($featuredEvents as $fe): ?>
        <div class="col-md-4" data-aos="fade-up">
          <div class="ex-card-premium ex-tilt h-100 d-flex flex-column">
            <div class="d-flex justify-content-between align-items-start mb-3">
              <div class="ex-card-icon"><i class="fa-solid <?= sanitize($fe['icon_class'] ?: 'fa-calendar') ?>"></i></div>
              <span class="ex-badge-category"><?= sanitize($fe['category_name'] ?? 'General') ?></span>
            </div>
            <h6 class="fw-bold mb-2"><?= sanitize($fe['title']) ?></h6>
            <p class="text-secondary small mb-4">
              <i class="fa-regular fa-calendar me-1"></i><?= date('d M Y', strtotime($fe['event_date'])) ?>
              &nbsp;·&nbsp;<i class="fa-solid fa-location-dot me-1"></i><?= sanitize($fe['venue']) ?>
            </p>
            <a href="<?= APP_URL ?>/event_details.php?id=<?= $fe['event_id'] ?>" class="ex-btn ex-btn-outline w-100 mt-auto">Register Now</a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="text-center mt-4">
      <a href="<?= APP_URL ?>/events.php" class="btn btn-link">View All Events →</a>
    </div>
  <?php endif; ?>
</section>

<!-- Contact CTA -->
<section class="container py-5 text-center" data-aos="fade-up">
  <div class="ex-cta-dark mx-auto" style="max-width:720px;">
    <h4 class="fw-bold mb-2">Have an event to organize?</h4>
    <p class="mb-4">Create an organizer account and publish your first event in minutes.</p>
    <a href="<?= APP_URL ?>/register.php" class="ex-btn ex-btn-gold">Create an Account</a>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
