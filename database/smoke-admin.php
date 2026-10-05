<?php
declare(strict_types=1);
require __DIR__.'/../app/admin.php';
$admin=rows("SELECT * FROM users WHERE LOWER(email)='aqqute.dev@gmail.com' LIMIT 1")[0]??null;
$other=rows("SELECT * FROM users WHERE LOWER(email)<>'aqqute.dev@gmail.com' LIMIT 1")[0]??null;
if(!$admin||!is_nonagon_admin($admin))throw new RuntimeException('Dedicated admin account was not authorized.');
if($other&&is_nonagon_admin($other))throw new RuntimeException('Non-admin account crossed the admin boundary.');
if(!investment_can_review($admin))throw new RuntimeException('Dedicated admin cannot review investments.');
echo "Admin smoke test passed: exclusive access and investment-review authority.\n";
