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
        'bypass_email_verification' => filter_var(env_value('DEV_BYPASS_EMAIL_VERIFICATION', 'false'), FILTER_VALIDATE_BOOLEAN),
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
    'features' => [
        'change_status' => filter_var(env_value('FEATURE_CHANGE_STATUS', 'true'), FILTER_VALIDATE_BOOLEAN),
        'operators' => filter_var(env_value('FEATURE_OPERATORS', 'true'), FILTER_VALIDATE_BOOLEAN),
    ],
    'mail' => [
        'from' => env_value('MAIL_FROM', 'no-reply@nonagon.ng'),
    ],
    'investment' => [
        'opportunity_stage' => (int)env_value('INVESTMENT_OPPORTUNITY_STAGE', '1'),
    ],
    'marketplace' => [
        'payment_webhook_secret' => env_value('MARKETPLACE_PAYMENT_WEBHOOK_SECRET'),
    ],
    'xinng' => [
        'api_base_url' => rtrim(env_value('XINNG_API_BASE_URL'), '/'),
        'public_base_url' => rtrim(env_value('XINNG_PUBLIC_BASE_URL') ?: env_value('XINNG_API_BASE_URL'), '/'),
    ],
];
