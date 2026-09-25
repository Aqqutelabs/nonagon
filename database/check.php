<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require dirname(__DIR__) . '/app/bootstrap.php';

try {
    $pdo = db();
    $tables = ['owners', 'users', 'spaces', 'sites', 'plants', 'units', 'verification_tokens', 'invitations', 'user_scopes', 'login_attempts'];
    foreach ($tables as $table) {
        $pdo->query("SELECT 1 FROM `{$table}` LIMIT 0");
    }
    $pdo->beginTransaction();
    $owner = uuid();
    $user = uuid();
    $email = 'database-check-' . $user . '@example.invalid';
    $pdo->prepare('INSERT INTO owners(id,name,email,phone) VALUES(?,?,?,?)')
        ->execute([$owner, 'Database check', $email, '0000000000']);
    $password = bin2hex(random_bytes(16));
    $pdo->prepare('INSERT INTO users(id,owner_id,full_name,email,phone,password_hash) VALUES(?,?,?,?,?,?)')
        ->execute([$user, $owner, 'Database check', $email, '0000000000', password_hash($password, PASSWORD_DEFAULT)]);
    $query = $pdo->prepare('SELECT u.* FROM users u JOIN owners o ON o.id=u.owner_id WHERE u.id=?');
    $query->execute([$user]);
    $record = $query->fetch();
    if (!$record || $record['role'] !== 'OWNER_ADMIN' || !password_verify($password, $record['password_hash'])) {
        throw new RuntimeException('Account relationship, role, or password verification failed.');
    }
    $parent = $owner;
    foreach (['spaces' => 'owner_id', 'sites' => 'space_id', 'plants' => 'site_id', 'units' => 'plant_id'] as $table => $column) {
        $id = uuid();
        $pdo->prepare("INSERT INTO {$table}(id,{$column},name,is_default) VALUES(?,?,?,1)")
            ->execute([$id, $parent, 'Database check']);
        $parent = $id;
    }
    try {
        $pdo->prepare('UPDATE users SET owner_id=? WHERE id=?')->execute([uuid(), $user]);
        throw new RuntimeException('Owner foreign key was not enforced.');
    } catch (PDOException $error) {
        if (($error->errorInfo[1] ?? null) !== 1452) throw $error;
    }
    try {
        $pdo->prepare('INSERT INTO users(id,owner_id,full_name,email,phone,password_hash) VALUES(?,?,?,?,?,?)')
            ->execute([uuid(), $owner, 'Duplicate check', $email, '0000000000', $record['password_hash']]);
        throw new RuntimeException('Unique user email was not enforced.');
    } catch (PDOException $error) {
        if (($error->errorInfo[1] ?? null) !== 1062) throw $error;
    }
    $pdo->rollBack();
    echo "PASS: All 10 tables accessible; owner/user creation, password verification, default role, location hierarchy, foreign key, and unique email checked.\n";
    echo "Test records rolled back.\n";
} catch (Throwable $error) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    fwrite(STDERR, "Database check failed (code {$error->getCode()}). Check schema and connection settings.\n");
    exit(1);
}
