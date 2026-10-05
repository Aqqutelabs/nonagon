<?php
declare(strict_types=1);
require __DIR__ . '/app/bootstrap.php';
$token = $_GET['token'] ?? $_POST['token'] ?? '';
$invite = null;
$errors = [];
if (preg_match('/^[a-f0-9]{64}$/', $token)) {
    $stmt = db()->prepare('SELECT i.*,o.name AS company_name,s.name AS site_name,u.name AS unit_name FROM invitations i JOIN owners o ON o.id=i.owner_id JOIN sites s ON s.id=i.site_id JOIN units u ON u.id=i.unit_id WHERE i.token_hash=? AND i.accepted_at IS NULL AND i.expires_at>NOW() LIMIT 1');
    $stmt->execute([hash('sha256', $token)]);
    $invite = $stmt->fetch() ?: null;
}
if (!$invite) {
    http_response_code(410); $pageTitle = 'Invitation unavailable'; require __DIR__ . '/includes/auth-header.php';
    echo '<section class="auth-card compact"><h1>That invitation is unavailable.</h1><p>It may be invalid, expired, or already accepted. Ask your administrator for a new invitation.</p></section>';
    require __DIR__ . '/includes/auth-footer.php'; exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $full = trim($_POST['full_name'] ?? ''); $phone = trim($_POST['phone'] ?? ''); $position = trim($_POST['position'] ?? ''); $password = $_POST['password'] ?? '';
    if (!$full) $errors[] = 'Full name is required.';
    if (!$phone) $errors[] = 'Phone number is required.';
    if ($password !== ($_POST['password_confirmation'] ?? '')) $errors[] = 'Passwords do not match.';
    if ($error = password_error($password)) $errors[] = $error;
    if (!$errors) {
        $check = db()->prepare('SELECT 1 FROM users WHERE email=?'); $check->execute([$invite['email']]);
        if ($check->fetch()) $errors[] = 'An account already uses this email.';
    }
    if (!$errors) {
        $pdo = db();
        try {
            $pdo->beginTransaction(); $userId = uuid();
            $pdo->prepare('INSERT INTO users(id,owner_id,full_name,email,phone,position,role,password_hash) VALUES(?,?,?,?,?,?,?,?)')->execute([$userId,$invite['owner_id'],$full,$invite['email'],$phone,$position ?: null,$invite['role'],password_hash($password,PASSWORD_DEFAULT)]);
            $pdo->prepare('INSERT INTO user_scopes(id,user_id,site_id,unit_id) VALUES(?,?,?,?)')->execute([uuid(),$userId,$invite['site_id'],$invite['unit_id']]);
            $updated = $pdo->prepare('UPDATE invitations SET accepted_at=NOW() WHERE id=? AND accepted_at IS NULL'); $updated->execute([$invite['id']]);
            if ($updated->rowCount() !== 1) throw new RuntimeException('Invitation already used');
            $pdo->commit(); session_regenerate_id(true); $_SESSION['user_id'] = $userId;
            issue_verification(['id'=>$userId,'full_name'=>$full,'email'=>$invite['email']]);
            flash('success','Account created. Verify your email to unlock your assigned operational access.'); redirect('dashboard');
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack(); $errors[] = 'We could not complete the invitation. Please try again.';
        }
    }
}
$pageTitle = 'Accept invitation'; require __DIR__ . '/includes/auth-header.php';
?>
<section class="auth-card compact"><p class="kicker">YOU’RE INVITED</p><h1>Join <?= e($invite['company_name']) ?>.</h1><p>Your access and work location have already been set by your administrator.</p><div class="assignment"><span><?= e($invite['role']) ?></span><strong><?= e($invite['site_name']) ?> → <?= e($invite['unit_name']) ?></strong></div><?php if ($errors): ?><div class="notice error" role="alert"><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?><form method="post" class="stack"><?= csrf_field() ?><input type="hidden" name="token" value="<?= e($token) ?>"><label>Email address<input value="<?= e($invite['email']) ?>" disabled></label><label>Full name<input name="full_name" required></label><label>Phone number<input name="phone" required></label><label>Position / title <small>Optional</small><input name="position"></label><label>Password<input id="password" type="password" name="password" minlength="8" required><meter id="password-meter" min="0" max="4" value="0"></meter><small id="password-strength">Use 8+ characters, uppercase, lowercase and a number.</small></label><label>Confirm password<input type="password" name="password_confirmation" minlength="8" required></label><button class="primary">Create operator account</button></form></section>
<?php require __DIR__ . '/includes/auth-footer.php'; ?>
