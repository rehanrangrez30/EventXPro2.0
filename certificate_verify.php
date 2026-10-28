<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$query = trim($_GET['code'] ?? $_GET['number'] ?? '');
$certificate = null;
$searched = $query !== '';

if ($searched) {
    $stmt = $pdo->prepare(
        "SELECT c.certificate_number, c.verification_code, c.certificate_type, c.certificate_status,
                c.issue_date, e.title AS event_title, e.event_date, u.full_name AS student_name
         FROM certificates c
         JOIN events e ON c.event_id = e.event_id
         JOIN users u ON c.student_id = u.user_id
         WHERE c.verification_code = ? OR c.certificate_number = ?"
    );
    $stmt->execute([$query, $query]);
    $certificate = $stmt->fetch();
}

$pageTitle = 'Verify Certificate';
include __DIR__ . '/includes/header.php';
?>

<section class="container py-5">
  <div class="row justify-content-center">
    <div class="col-lg-6">
      <div class="ex-card" data-aos="fade-up">
        <h3 class="fw-bold text-center mb-1">Certificate Verification</h3>
        <p class="text-secondary text-center mb-4">
          Enter a certificate number or verification code to confirm its authenticity.
        </p>

        <form method="GET" class="d-flex gap-2 mb-4">
          <input type="text" name="code" class="form-control" placeholder="Certificate number or verification code"
                 value="<?= sanitize($query) ?>" required>
          <button type="submit" class="btn btn-primary">Verify</button>
        </form>

        <?php if ($searched && !$certificate): ?>
          <div class="alert alert-danger mb-0">
            No certificate found matching that number or code.
          </div>
        <?php elseif ($certificate): ?>
          <?php if ($certificate['certificate_status'] === 'generated'): ?>
            <div class="alert alert-success">
              <i class="fa-solid fa-circle-check me-1"></i>This certificate is valid.
            </div>
          <?php else: ?>
            <div class="alert alert-danger">
              <i class="fa-solid fa-circle-xmark me-1"></i>This certificate has been revoked.
            </div>
          <?php endif; ?>

          <table class="table table-borderless mb-4">
            <tr><th class="text-secondary small">Certificate No.</th><td><?= sanitize($certificate['certificate_number']) ?></td></tr>
            <tr><th class="text-secondary small">Participant</th><td><?= sanitize($certificate['student_name']) ?></td></tr>
            <tr><th class="text-secondary small">Event</th><td><?= sanitize($certificate['event_title']) ?></td></tr>
            <tr><th class="text-secondary small">Event Date</th><td><?= date('d M Y', strtotime($certificate['event_date'])) ?></td></tr>
            <tr><th class="text-secondary small">Type</th><td class="text-capitalize"><?= sanitize($certificate['certificate_type']) ?></td></tr>
            <tr><th class="text-secondary small">Issued</th><td><?= date('d M Y', strtotime($certificate['issue_date'])) ?></td></tr>
          </table>

          <div class="text-center">
            <div id="verifyQrCode" class="d-inline-block"></div>
            <p class="text-secondary small mt-2 mb-0">Scan to open this verification page</p>
          </div>

          <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
          <script>
            new QRCode(document.getElementById('verifyQrCode'), {
              text: window.location.href,
              width: 140,
              height: 140,
            });
          </script>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
