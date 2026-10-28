<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$images = $pdo->query(
    "SELECT g.title, g.image_path, e.title AS event_title
     FROM gallery g JOIN events e ON g.event_id = e.event_id
     WHERE g.status = 'active'
     ORDER BY g.uploaded_at DESC LIMIT 24"
)->fetchAll();

$pageTitle = 'Gallery';
include __DIR__ . '/includes/header.php';
?>

<section class="container py-5">
  <h2 class="fw-bold text-center mb-5" data-aos="fade-up">Event Gallery</h2>

  <?php if (empty($images)): ?>
    <div class="ex-card text-center py-5 mx-auto" style="max-width:480px;" data-aos="fade-up">
      <i class="fa-regular fa-images fa-2x text-secondary mb-3"></i>
      <p class="text-secondary mb-0">No gallery photos have been uploaded yet. Check back after our next event!</p>
    </div>
  <?php else: ?>
    <div class="row g-4">
      <?php foreach ($images as $img): ?>
        <div class="col-md-4 col-sm-6" data-aos="zoom-in">
          <div class="ex-card p-0 overflow-hidden">
            <img src="<?= APP_URL . '/' . sanitize($img['image_path']) ?>" alt="<?= sanitize($img['title']) ?>" class="w-100" loading="lazy" style="aspect-ratio:4/3;object-fit:cover;">
            <div class="p-3">
              <h6 class="fw-semibold mb-1"><?= sanitize($img['title']) ?></h6>
              <p class="text-secondary small mb-0"><?= sanitize($img['event_title']) ?></p>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
