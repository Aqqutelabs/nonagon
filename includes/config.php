<?php
declare(strict_types=1);

if (!defined('APP_ROOT')) {
    throw new RuntimeException('Application root is not defined.');
}

function load_environment(string $path): void
{
    if (!is_file($path)) return;

    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) continue;

        [$key, $value] = array_map('trim', explode('=', $line, 2));
        if (getenv($key) === false) putenv($key . '=' . trim($value, "\"'"));
    }
}

function env_value(string $key, string $default = ''): string
{
    $value = getenv($key);
    return $value === false ? $default : $value;
}

load_environment(APP_ROOT . '/.env');

return [
    'app' => [
        'environment' => env_value('APP_ENV', 'production'),
        'url' => rtrim(env_value('APP_URL'), '/'),
    ],
    'database' => [
        'host' => env_value('DB_HOST', '127.0.0.1'),
        'port' => env_value('DB_PORT', '3306'),
        'name' => env_value('DB_DATABASE', 'nonagon'),
        'username' => env_value('DB_USERNAME', 'root'),
        'password' => env_value('DB_PASSWORD'),
        'charset' => 'utf8mb4',
    ],
    'mail' => [
        'from' => env_value('MAIL_FROM', 'no-reply@nonagon.ng'),
    ],
];
