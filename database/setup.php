<?php
declare(strict_types=1);

// Database provisioning is only available from the command line.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

define('APP_ROOT', dirname(__DIR__));
$config = require APP_ROOT . '/includes/config.php';
$database = $config['database'];
$name = $database['name'];
if (!preg_match('/^[A-Za-z0-9_]+$/D', $name)) {
    fwrite(STDERR, "DB_DATABASE must contain only letters, numbers, and underscores.\n");
    exit(1);
}

try {
    $pdo = new PDO(
        "mysql:host={$database['host']};port={$database['port']};charset=utf8mb4",
        $database['username'],
        $database['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_EMULATE_PREPARES => false]
    );
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `{$name}`");
    $schema = file_get_contents(__DIR__ . '/schema.sql');
    if ($schema === false) throw new RuntimeException('Cannot read schema.sql.');
    foreach (explode(';', $schema) as $statement) {
        $statement = trim($statement);
        // The CLI uses the configured database instead of the SQL import default.
        if ($statement === '' || preg_match('/^(CREATE DATABASE|USE)\b/i', $statement)) continue;
        $pdo->exec($statement);
    }
    echo "Database {$name} initialized. Existing tables and records preserved.\n";
    echo "Open /nonagon/register to create your owner and administrator account.\n";
} catch (Throwable $error) {
    fwrite(STDERR, "Database setup failed. Check MySQL, .env credentials, and database permissions.\n");
    // Do not print connection details or credentials.
    fwrite(STDERR, 'Error code: ' . $error->getCode() . "\n");
    exit(1);
}
