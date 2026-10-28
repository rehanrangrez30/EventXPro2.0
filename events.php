<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$search   = trim($_GET['search'] ?? '');
$category = (int) ($_GET['category'] ?? 0);

// --- Smart search: multi-field match + simple relevance ranking ---
// (title match ranks above description/venue/category match) — pure SQL,
// no external search service, keeps the project dependency-free.
$sql = "SELECT e.event_id, e.title, e.event_date, e.start_time, e.venue,
               e.max_participants, e.registration_deadline, e.poster_image,
               c.category_name, c.icon_class,
               (SELECT COUNT(*) FROM registrations r WHERE r.event_id = e.event_id AND r.status = 'registered') AS seats_taken";
if ($search !== '') {
    $sql .= ", (CASE WHEN e.title LIKE ? THEN 3 WHEN e.venue LIKE ? THEN 2 ELSE 1 END) AS relevance";
}
$sql .= " FROM events e
        LEFT JOIN event_categories c ON e.category_id = c.category_id
        WHERE e.status = 'published' AND e.event_date >= CURDATE()";
$params = [];
if ($search !== '') {
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($search !== '') {
    $sql .= " AND (e.title LIKE ? OR e.venue LIKE ? OR e.description LIKE ? OR c.category_name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($category > 0) {
    $sql .= " AND e.category_id = ?";
    $params[] = $category;
}
$sql .= $search !== '' ? " ORDER BY relevance DESC, e.event_date ASC" : " ORDER BY e.event_date ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$events = $stmt->fetchAll();

// Fallback: if a search returned nothing, suggest a few upcoming events anyway.
$suggestedEvents = [];
if ($search !== '' && empty($events)) {
    $suggestedEvents = $pdo->query(
        "SELECT e.event_id, e.title, e.event_date, e.venue, c.icon_class
         FROM events e LEFT JOIN event_categories c ON e.category_id = c.category_id
         WHERE e.status = 'published' AND e.event_date >= CURDATE()
         ORDER BY e.event_date ASC LIMIT 3"
    )->fetchAll();
}

$categories = $pdo->query("SELECT category_id, category_name, icon_class FROM event_categories WHERE is_active = 1")->fetchAll();

$pageTitle = 'Events';
include __DIR__ . '/includes/header.php';
?>

<section class="container py-5">
  <h2 class="fw-bold mb-4" data-aos="fade-up">Upcoming Events</h2>

  <form method="GET" class="row g-2 mb-4" data-aos="fade-up">
    <div class="col-md-6">
      <input type="text" name="search" class="form-control" placeholder="Search by title, venue, category or description..." value="<?= sanitize($search) ?>">
    </div>
    <div class="col-md-4">
      <select name="category" class="form-select">
        <option value="0">All Categories</option>
        <?php foreach ($categories as $cat): ?>
          <option value="<?= $cat['category_id'] ?>" <?= $category === (int) $cat['category_id'] ? 'selected' : '' ?>>
            <?= sanitize($cat['category_name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2">
      <button type="submit" class="btn btn-primary w-100"><i class="fa-solid fa-filter me-1"></i>Filter</button>
    </div>
  </form>

  <?php if (empty($events)): ?>
    <div class="ex-card text-center py-5" data-aos="fade-up">
      <i class="fa-regular fa-calendar-xmark fa-2x text-secondary mb-3"></i>
      <p class="mb-0 text-secondary">
        <?= $search !== '' ? 'No events matched "' . sanitize($search) . '".' : 'No Upcoming Events Available.' ?>
      </p>
    </div>
    <?php if (!empty($suggestedEvents)): ?>
      <div class="mt-4" data-aos="fade-up">
        <h6 class="fw-semibold mb-3">You might be interested in these instead</h6>
        <div class="row g-3">
          <?php foreach ($suggestedEvents as $sug): ?>
            <div class="col-md-4">
              <div class="ex-card h-100">
                <i class="fa-solid <?= sanitize($sug['icon_class'] ?: 'fa-calendar') ?> fa-2x text-primary mb-3"></i>
                <h6 class="fw-semibold"><?= sanitize($sug['title']) ?></h6>
                <p class="text-secondary small mb-3">
                  <i class="fa-regular fa-calendar me-1"></i><?= date('d M Y', strtotime($sug['event_date'])) ?>
                  &nbsp;·&nbsp;<i class="fa-solid fa-location-dot me-1"></i><?= sanitize($sug['venue']) ?>
                </p>
                <a href="<?= APP_URL ?>/event_details.php?id=<?= $sug['event_id'] ?>" class="btn btn-sm btn-outline-primary mt-auto">View Details</a>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>
  <?php else: ?>
    <div class="row g-4">
      <?php foreach ($events as $event):
        $seatsLeft = $event['max_participants'] > 0 ? max(0, $event['max_participants'] - (int) $event['seats_taken']) : null;
        $closed = strtotime($event['registration_deadline']) < time() || ($seatsLeft !== null && $seatsLeft <= 0);
      ?>
        <div class="col-md-4 col-sm-6" data-aos="zoom-in">
          <div class="ex-card ex-tilt h-100">
            <i class="fa-solid <?= sanitize($event['icon_class'] ?: 'fa-calendar') ?> fa-2x text-primary mb-3"></i>
            <h5 class="fw-semibold"><?= sanitize($event['title']) ?></h5>
            <p class="text-secondary small mb-2">
              <i class="fa-regular fa-calendar me-1"></i><?= date('d M Y', strtotime($event['event_date'])) ?>
              &nbsp;·&nbsp;<i class="fa-regular fa-clock me-1"></i><?= date('h:i A', strtotime($event['start_time'])) ?>
            </p>
            <p class="text-secondary small mb-3"><i class="fa-solid fa-location-dot me-1"></i><?= sanitize($event['venue']) ?></p>
            <span class="badge <?= $closed ? 'text-bg-secondary' : 'text-bg-success' ?> mb-3">
              <?= $closed ? 'Registration Closed' : 'Open for Registration' ?>
            </span>
            <a href="<?= APP_URL ?>/event_details.php?id=<?= $event['event_id'] ?>" class="btn btn-outline-primary w-100 mt-auto">View Details</a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
