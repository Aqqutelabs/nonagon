<?php
declare(strict_types=1); require __DIR__ . '/app/bootstrap.php';
if (current_user()) redirect('dashboard');
$errors=[]; $old=$_POST;
if ($_SERVER['REQUEST_METHOD']==='POST') {
 verify_csrf();
 $full=trim($_POST['full_name']??''); $email=strtolower(trim($_POST['email']??'')); $phone=trim($_POST['phone']??'');
 $company=trim($_POST['company_name']??'') ?: $full; $position=trim($_POST['position']??''); $password=$_POST['password']??'';
 $mode=$_POST['location_mode']??'default';
 $space=trim($_POST['space_name']??'') ?: $company; $site=trim($_POST['site_name']??'') ?: $company; $plant=trim($_POST['plant_name']??'') ?: $company; $unit=trim($_POST['unit_name']??'') ?: $company;
 if ($full==='') $errors[]='Full name is required.'; if (!filter_var($email,FILTER_VALIDATE_EMAIL)) $errors[]='Enter a valid email address.'; if ($phone==='') $errors[]='Phone number is required.';
 if ($password!==($_POST['password_confirmation']??'')) $errors[]='Passwords do not match.'; if ($error=password_error($password)) $errors[]=$error;
 if (!$errors) {
  try { $pdo=db(); $check=$pdo->prepare('SELECT 1 FROM users WHERE email=? UNION SELECT 1 FROM owners WHERE email=? LIMIT 1'); $check->execute([$email,$email]); if($check->fetch()) $errors[]='An account already uses that email.';
  } catch(PDOException $e){ $errors[]='The database is not ready. Import database/schema.sql and check your .env settings.'; }
 }
 if (!$errors) {
  try { $pdo->beginTransaction(); $ownerId=uuid(); $userId=uuid(); $spaceId=uuid(); $siteId=uuid(); $plantId=uuid(); $unitId=uuid();
   $pdo->prepare('INSERT INTO owners(id,name,email,phone) VALUES(?,?,?,?)')->execute([$ownerId,$company,$email,$phone]);
   $pdo->prepare('INSERT INTO users(id,owner_id,full_name,email,phone,position,password_hash) VALUES(?,?,?,?,?,?,?)')->execute([$userId,$ownerId,$full,$email,$phone,$position?:null,password_hash($password,PASSWORD_DEFAULT)]);
   $pdo->prepare('INSERT INTO spaces(id,owner_id,name,is_default) VALUES(?,?,?,1)')->execute([$spaceId,$ownerId,$mode==='custom'?$space:$company]);
   $pdo->prepare('INSERT INTO sites(id,space_id,name,is_default) VALUES(?,?,?,1)')->execute([$siteId,$spaceId,$mode==='custom'?$site:$company]);
   $pdo->prepare('INSERT INTO plants(id,site_id,name,is_default) VALUES(?,?,?,1)')->execute([$plantId,$siteId,$mode==='custom'?$plant:$company]);
   $pdo->prepare('INSERT INTO units(id,plant_id,name,is_default) VALUES(?,?,?,1)')->execute([$unitId,$plantId,$mode==='custom'?$unit:$company]);
   $pdo->commit(); session_regenerate_id(true); $_SESSION['user_id']=$userId; issue_verification(['id'=>$userId,'full_name'=>$full,'email'=>$email]); flash('success','Account created. Verify your email to unlock operational features.'); redirect('dashboard');
  } catch(Throwable $e){ if($pdo->inTransaction())$pdo->rollBack(); $errors[]='We could not create the account. Please try again.'; }
 }
}
$pageTitle='Create your company account'; require __DIR__.'/includes/auth-header.php';
?>
<section class="auth-card wide"><div class="auth-intro"><p class="kicker">GET STARTED</p><h1>Create your company account.</h1><p>Start with the essentials. You can complete setup later.</p></div>
<?php if($errors): ?><div class="notice error" role="alert"><strong>Please check the form.</strong><ul><?php foreach($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<form method="post" class="form-grid" novalidate><?= csrf_field() ?><div class="step full"><span>01</span><div><strong>Account details</strong><small>Tell us who you are.</small></div></div>
<label>Full name<input name="full_name" autocomplete="name" required value="<?= e($old['full_name']??'') ?>"></label><label>Email address<input type="email" name="email" autocomplete="email" required value="<?= e($old['email']??'') ?>"></label><label>Phone number<input type="tel" name="phone" autocomplete="tel" required value="<?= e($old['phone']??'') ?>"></label><label>Company name <small>Optional</small><input name="company_name" autocomplete="organization" placeholder="Defaults to your name" value="<?= e($old['company_name']??'') ?>"></label><label class="full">Position / title <small>Optional</small><input name="position" autocomplete="organization-title" value="<?= e($old['position']??'') ?>"></label>
<div class="step full"><span>02</span><div><strong>Secure your account</strong><small>Your password works independently of future sign-in providers.</small></div></div><label>Password<input id="password" type="password" name="password" minlength="8" autocomplete="new-password" required aria-describedby="password-strength"><meter id="password-meter" min="0" max="4" value="0"></meter><small id="password-strength">Use 8+ characters, uppercase, lowercase and a number.</small></label><label>Confirm password<input type="password" name="password_confirmation" minlength="8" autocomplete="new-password" required></label>
<div class="step full"><span>03</span><div><strong>Location structure</strong><small>Choose defaults now or customize them.</small></div></div><fieldset class="full choices"><legend class="sr-only">Location setup</legend><label><input type="radio" name="location_mode" value="default" checked> Accept defaults</label><label><input type="radio" name="location_mode" value="custom"> Customize</label><label><input type="radio" name="location_mode" value="skip"> Skip for now</label></fieldset><div class="location-fields full" hidden><label>Space<input name="space_name"></label><label>Site<input name="site_name"></label><label>Plant<input name="plant_name"></label><label>Unit<input name="unit_name"></label></div>
<div class="full submit-row"><button class="primary" type="submit">Create account <span>→</span></button><p>Already registered? <a href="login">Sign in</a></p></div></form></section>
<?php require __DIR__.'/includes/auth-footer.php'; ?>
