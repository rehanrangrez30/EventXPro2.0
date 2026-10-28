<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$eventId = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT e.*, c.category_name, c.icon_class, u.full_name AS organizer_name FROM events e LEFT JOIN event_categories c ON e.category_id=c.category_id LEFT JOIN users u ON e.organizer_id=u.user_id WHERE e.event_id=? AND e.status='published'");
$stmt->execute([$eventId]);
$event = $stmt->fetch();
if (!$event) redirect('errors/404.php');

$seatsStmt=$pdo->prepare("SELECT COUNT(*) FROM registrations WHERE event_id=? AND status='registered'");
$seatsStmt->execute([$eventId]);
$seatsTaken=(int)$seatsStmt->fetchColumn();
$seatsLeft=$event['max_participants']>0 ? max(0,(int)$event['max_participants']-$seatsTaken) : null;
$deadlinePassed=strtotime($event['registration_deadline'])<time();
$isFull=$seatsLeft!==null && $seatsLeft<=0;
$alreadyRegistered=false; $registration=null;
if(is_logged_in() && ($_SESSION['role_name']??'')==='student'){
  $check=$pdo->prepare("SELECT * FROM registrations WHERE event_id=? AND student_id=? AND status='registered'");
  $check->execute([$eventId,$_SESSION['user_id']]); $registration=$check->fetch(); $alreadyRegistered=(bool)$registration;
}
$errors=[];
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['register'])){
  if(!is_logged_in()) redirect('login.php');
  if(($_SESSION['role_name']??'')!=='student') $errors[]='Only students can register for events.';
  if(!verify_csrf($_POST['csrf_token']??'')) $errors[]='Invalid form submission. Please try again.';
  if($deadlinePassed) $errors[]='The registration deadline for this event has passed.';
  if($isFull) $errors[]='This event has reached its maximum number of participants.';
  if($alreadyRegistered) $errors[]='You are already registered for this event.';
  $method=$_POST['payment_method']??'none';
  $price=(float)$event['ticket_price'];
  if($price>0 && !in_array($method,['upi','card','netbanking'],true)) $errors[]='Please select a payment method.';
  if(empty($errors)){
    try{
      $pdo->beginTransaction();
      $status=$price>0?'pending':'not_required';
      $insert=$pdo->prepare("INSERT INTO registrations(event_id,student_id,status,payment_status,payment_method,amount_paid) VALUES(?,?, 'registered', ?, ?, ?)");
      $insert->execute([$eventId,$_SESSION['user_id'],$status,$price>0?$method:'none',$price]);
      $regId=(int)$pdo->lastInsertId();
      if($price>0){
        // Demo payment: replace this block with Razorpay/Stripe verification in production.
        $tx=demo_transaction_id();
        $pay=$pdo->prepare("INSERT INTO payments(registration_id,event_id,student_id,amount,currency,payment_method,transaction_id,gateway_name,status,paid_at) VALUES(?,?,?,?,?,?,?,?, 'paid', NOW())");
        $pay->execute([$regId,$eventId,$_SESSION['user_id'],$price,$event['currency'],$method,$tx,'demo']);
        $pdo->prepare("UPDATE registrations SET payment_status='paid' WHERE registration_id=?")->execute([$regId]);
      }
      $logStmt=$pdo->prepare('INSERT INTO activity_logs(user_id,action,description,ip_address) VALUES(?,?,?,?)');
      $logStmt->execute([$_SESSION['user_id'],'event_registration','Registered for: '.$event['title'],$_SERVER['REMOTE_ADDR']??null]);
      notify_user($pdo,$_SESSION['user_id'],'Registration Confirmed','You are registered for "'.$event['title'].'".','registration',$eventId,'student/my_registrations.php');
      $pdo->commit();
      set_flash('success',$price>0?'Registration and demo payment completed successfully.':'You have successfully registered for "'.$event['title'].'".');
      redirect('student/my_registrations.php');
    }catch(Throwable $e){ if($pdo->inTransaction()) $pdo->rollBack(); $errors[]='Registration/payment could not be completed. Please try again.'; }
  }
}
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['cancel_registration']) && is_logged_in() && verify_csrf($_POST['csrf_token']??'')){
  $q=$pdo->prepare("SELECT * FROM registrations WHERE event_id=? AND student_id=? AND status='registered'"); $q->execute([$eventId,$_SESSION['user_id']]); $reg=$q->fetch();
  if($reg){
    if((float)$reg['amount_paid']>0){
      $refundId = create_refund_request($pdo,(int)$reg['registration_id'],'Student cancelled registration');
      if ($refundId) {
        set_flash('success','Registration cancelled. Your refund request has been created for organizer/admin processing.');
      } else {
        $pdo->prepare("UPDATE registrations SET status='cancelled', payment_status='refund_rejected' WHERE registration_id=?")->execute([(int)$reg['registration_id']]);
        set_flash('warning','Registration cancelled, but the refund deadline has passed, so no refund request was created.');
      }
    }else{
      $pdo->prepare("UPDATE registrations SET status='cancelled' WHERE registration_id=?")->execute([(int)$reg['registration_id']]);
      set_flash('success','Your registration has been cancelled.');
    }
  }
  redirect('student/my_registrations.php');
}

$similarEvents=[];
if(!empty($event['category_id'])){
 $sim=$pdo->prepare("SELECT e.event_id,e.title,e.event_date,e.venue,c.icon_class FROM events e LEFT JOIN event_categories c ON e.category_id=c.category_id WHERE e.status='published' AND e.event_date>=CURDATE() AND e.category_id=? AND e.event_id!=? ORDER BY e.event_date ASC LIMIT 3");
 $sim->execute([$event['category_id'],$eventId]); $similarEvents=$sim->fetchAll();
}
$pageTitle=$event['title']; include __DIR__.'/includes/header.php';
?>
<section class="container py-5"><div class="row g-4">
<div class="col-lg-8"><div class="ex-card"><span class="badge text-bg-primary mb-2"><?=sanitize($event['category_name']??'General')?></span><h2 class="fw-bold mb-3"><?=sanitize($event['title'])?></h2><p class="text-secondary"><?=nl2br(sanitize($event['description']))?></p><hr><div class="row g-3"><div class="col-sm-6">📅 <?=date('d M Y',strtotime($event['event_date']))?></div><div class="col-sm-6">🕒 <?=date('h:i A',strtotime($event['start_time']))?> – <?=date('h:i A',strtotime($event['end_time']))?></div><div class="col-sm-6">📍 <?=sanitize($event['venue'])?></div><div class="col-sm-6">👤 <?=sanitize($event['organizer_name']??'N/A')?></div></div></div></div>
<div class="col-lg-4"><div class="ex-card"><h6 class="fw-semibold mb-3">Registration & Payment</h6>
<?php if(!empty($errors)):?><div class="alert alert-danger small"><ul class="mb-0 ps-3"><?php foreach($errors as $e):?><li><?=sanitize($e)?></li><?php endforeach;?></ul></div><?php endif;?>
<p class="small text-secondary mb-1">Seats: <?=$seatsLeft===null?'Unlimited':$seatsLeft.' left of '.(int)$event['max_participants']?></p><p class="small text-secondary">Deadline: <?=date('d M Y, h:i A',strtotime($event['registration_deadline']))?></p>
<div class="alert alert-light border"><strong>Price:</strong> <?=((float)$event['ticket_price']<=0)?'Free':'₹'.number_format((float)$event['ticket_price'],2)?></div>
<?php if(!is_logged_in()):?><a href="<?=APP_URL?>/login.php" class="btn btn-primary w-100">Log In to Register</a>
<?php elseif(($_SESSION['role_name']??'')!=='student'):?><p class="text-secondary small mb-0">Only student accounts can register for events.</p>
<?php elseif($alreadyRegistered):?><form method="POST"><input type="hidden" name="csrf_token" value="<?=csrf_token()?>"><button type="submit" name="cancel_registration" value="1" class="btn btn-outline-danger w-100">Cancel Registration</button></form>
<?php elseif($deadlinePassed):?><button class="btn btn-secondary w-100" disabled>Registration Closed</button>
<?php elseif($isFull):?><button class="btn btn-secondary w-100" disabled>Event Full</button>
<?php else:?><form method="POST"><input type="hidden" name="csrf_token" value="<?=csrf_token()?>"><?php if((float)$event['ticket_price']>0):?><label class="form-label small">Payment Method</label><select name="payment_method" class="form-select mb-3" required><option value="">Select</option><option value="upi">UPI</option><option value="card">Card</option><option value="netbanking">Net Banking</option></select><div class="form-text mb-3">Demo payment mode is enabled for this project. No real money is charged.</div><?php endif;?><button type="submit" name="register" value="1" class="btn btn-primary w-100"><?=((float)$event['ticket_price']>0)?'Pay & Register':'Register Now'?></button></form><?php endif;?></div></div></div>
<?php if($similarEvents):?><div class="mt-5"><h5 class="fw-bold mb-3">You Might Also Like</h5><div class="row g-3"><?php foreach($similarEvents as $sim):?><div class="col-md-4"><div class="ex-card h-100"><h6><?=sanitize($sim['title'])?></h6><p class="text-secondary small"><?=date('d M Y',strtotime($sim['event_date']))?> · <?=sanitize($sim['venue'])?></p><a href="<?=APP_URL?>/event_details.php?id=<?=$sim['event_id']?>" class="btn btn-sm btn-outline-primary">View Details</a></div></div><?php endforeach;?></div></div><?php endif;?></section>
<?php include __DIR__.'/includes/footer.php'; ?>
