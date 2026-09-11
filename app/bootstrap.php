<?php
declare(strict_types=1);

const APP_ROOT = __DIR__ . '/..';

$appConfig = require APP_ROOT . '/includes/config.php';

function config(string $key, mixed $default = null): mixed
{
    global $appConfig;
    $value = $appConfig;
    foreach (explode('.', $key) as $segment) {
        if (!is_array($value) || !array_key_exists($segment, $value)) return $default;
        $value = $value[$segment];
    }
    return $value;
}

$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
session_set_cookie_params(['httponly' => true, 'secure' => $secure, 'samesite' => 'Lax', 'path' => '/']);
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

function db(): PDO
{
    static $pdo;
    if ($pdo instanceof PDO) return $pdo;
    $host = config('database.host');
    $port = config('database.port');
    $name = config('database.name');
    $charset = config('database.charset', 'utf8mb4');
    $dsn = "mysql:host={$host};port={$port};dbname={$name};charset={$charset}";
    $pdo = new PDO($dsn, config('database.username'), config('database.password'), [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    return $pdo;
}

function e(?string $value): string { return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8'); }
function uuid(): string
{
    $b = random_bytes(16); $b[6] = chr((ord($b[6]) & 0x0f) | 0x40); $b[8] = chr((ord($b[8]) & 0x3f) | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($b), 4));
}
function csrf_token(): string { return $_SESSION['csrf'] ??= bin2hex(random_bytes(32)); }
function csrf_field(): string { return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">'; }
function verify_csrf(): void
{
    if (!isset($_POST['csrf']) || !hash_equals($_SESSION['csrf'] ?? '', (string) $_POST['csrf'])) {
        http_response_code(419); exit('Your session expired. Please go back, refresh the page, and try again.');
    }
}
function redirect(string $path): never { header('Location: ' . $path); exit; }
function flash(string $key, ?string $value = null): ?string
{
    if ($value !== null) { $_SESSION['_flash'][$key] = $value; return null; }
    $message = $_SESSION['_flash'][$key] ?? null; unset($_SESSION['_flash'][$key]); return $message;
}
function current_user(): ?array
{
    if (empty($_SESSION['user_id'])) return null;
    $stmt = db()->prepare('SELECT u.*, o.name AS company_name FROM users u JOIN owners o ON o.id=u.owner_id WHERE u.id=? LIMIT 1');
    $stmt->execute([$_SESSION['user_id']]); return $stmt->fetch() ?: null;
}
function require_login(): array
{
    $user = current_user(); if (!$user) { flash('error', 'Please sign in to continue.'); redirect('login'); } return $user;
}
function require_verified(): array
{
    $user = require_login(); if (!(bool)$user['is_email_verified']) { flash('error', 'Verify your email to access operational features.'); redirect('dashboard'); } return $user;
}
function require_scope(string $siteId, string $unitId): array
{
    $user = require_verified();
    if ($user['role'] === 'OWNER_ADMIN') return $user;
    $stmt = db()->prepare('SELECT 1 FROM user_scopes WHERE user_id=? AND site_id=? AND unit_id=? LIMIT 1');
    $stmt->execute([$user['id'], $siteId, $unitId]);
    if (!$stmt->fetch()) { http_response_code(403); exit('You do not have access to this site and unit.'); }
    return $user;
}
function base_url(string $path = ''): string
{
    $configured = config('app.url', '');
    if ($configured !== '') return $configured . '/' . ltrim($path, '/');
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = preg_replace('/[^A-Za-z0-9.\-:\[\]]/', '', $_SERVER['HTTP_HOST'] ?? 'localhost');
    $dir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
    return $scheme . '://' . $host . $dir . '/' . ltrim($path, '/');
}
function issue_verification(array $user): string
{
    $raw = bin2hex(random_bytes(32));
    $stmt = db()->prepare('INSERT INTO verification_tokens (id,user_id,token_hash,expires_at) VALUES (?,?,?,DATE_ADD(NOW(), INTERVAL 24 HOUR))');
    $stmt->execute([uuid(), $user['id'], hash('sha256', $raw)]);
    $url = base_url('verify?token=' . urlencode($raw));
    $subject = 'Verify your Nonagon account';
    $body = "Hello {$user['full_name']},\n\nVerify your account: {$url}\n\nThis link expires in 24 hours.";
    $sent = @mail($user['email'], $subject, $body, 'From: ' . config('mail.from'));
    if (!$sent && config('app.environment') === 'local') $_SESSION['dev_verification_url'] = $url;
    return $url;
}
function password_error(string $password): ?string
{
    if (strlen($password) < 8) return 'Use at least 8 characters.';
    if (!preg_match('/[a-z]/', $password) || !preg_match('/[A-Z]/', $password) || !preg_match('/\d/', $password)) return 'Include uppercase, lowercase and a number.';
    return null;
}
