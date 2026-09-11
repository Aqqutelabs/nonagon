<?php
declare(strict_types=1);
require __DIR__ . '/app/bootstrap.php';
$user = require_verified();
if (!in_array($user['role'], ['OWNER_ADMIN', 'ADMIN'], true)) {
    http_response_code(403);
    exit('You do not have permission to invite users.');
}
$stmt = db()->prepare('SELECT s.id AS site_id,s.name AS site_name,u.id AS unit_id,u.name AS unit_name FROM spaces sp JOIN sites s ON s.space_id=sp.id JOIN plants p ON p.site_id=s.id JOIN units u ON u.plant_id=p.id WHERE sp.owner_id=? ORDER BY s.name,u.name');
$stmt->execute([$user['owner_id']]);
$locations = $stmt->fetchAll();
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = strtolower(trim($_POST['email'] ?? ''));
    $role = $_POST['role'] ?? '';
    $siteId = $_POST['site_id'] ?? '';
    $unitId = $_POST['unit_id'] ?? '';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email address.';
    if (!in_array($role, ['OPERATOR', 'SUPERVISOR', 'ADMIN'], true)) $errors[] = 'Choose a valid role.';
    $validLocation = false;
    foreach ($locations as $location) {
        if ($location['site_id'] === $siteId && $location['unit_id'] === $unitId) $validLocation = true;
    }
    if (!$validLocation) $errors[] = 'Choose a valid site and unit.';
    if (!$errors) {
        $exists = db()->prepare('SELECT 1 FROM users WHERE email=?');
        $exists->execute([$email]);
        if ($exists->fetch()) $errors[] = 'That email already belongs to a user.';
    }
    if (!$errors) {
        $raw = bin2hex(random_bytes(32));
        db()->prepare('INSERT INTO invitations(id,owner_id,email,role,site_id,unit_id,token_hash,expires_at,created_by) VALUES(?,?,?,?,?,?,?,DATE_ADD(NOW(),INTERVAL 7 DAY),?)')->execute([uuid(), $user['owner_id'], $email, $role, $siteId, $unitId, hash('sha256', $raw), $user['id']]);
        $url = base_url('accept-invite?token=' . urlencode($raw));
        $sent = @mail($email, 'You are invited to Nonagon', "You have been invited to {$user['company_name']}. Accept: {$url}\n\nThis link expires in 7 days.", 'From: ' . config('mail.from'));
        if (!$sent && config('app.environment') === 'local') $_SESSION['dev_invitation_url'] = $url;
        flash('success', 'Invitation created and prepared for delivery.');
        redirect('invite');
    }
}
$pageTitle = 'Invite a user';
require __DIR__ . '/includes/auth-header.php';
?>
<section class="auth-card compact"><p class="kicker">TEAM ACCESS</p><h1>Invite someone.</h1><p>Their company, role and work location are fixed by this invitation.</p><?php if ($errors): ?><div class="notice error" role="alert"><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?><form method="post" class="stack"><?= csrf_field() ?><label>Email address<input type="email" name="email" required></label><label>Role<select name="role" required><option value="OPERATOR">Operator</option><option value="SUPERVISOR">Supervisor</option><option value="ADMIN">Admin</option></select></label><label>Assigned site and unit<select id="location" required><option value="">Choose a location</option><?php foreach ($locations as $location): ?><option value="<?= e($location['site_id'] . '|' . $location['unit_id']) ?>"><?= e($location['site_name'] . ' → ' . $location['unit_name']) ?></option><?php endforeach; ?></select><input type="hidden" name="site_id" id="site-id"><input type="hidden" name="unit_id" id="unit-id"></label><button class="primary">Send invitation</button></form><?php if (!empty($_SESSION['dev_invitation_url'])): ?><p class="dev-link">Local development link: <a href="<?= e($_SESSION['dev_invitation_url']) ?>">Open invitation</a></p><?php endif; ?><a class="back-link" href="dashboard">← Back to dashboard</a></section>
<script>document.querySelector('#location').addEventListener('change',function(){const p=this.value.split('|');document.querySelector('#site-id').value=p[0]||'';document.querySelector('#unit-id').value=p[1]||'';});</script>
<?php require __DIR__ . '/includes/auth-footer.php'; ?>
