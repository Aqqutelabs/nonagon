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

if (PHP_SAPI !== 'cli') {
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    session_set_cookie_params(['httponly' => true, 'secure' => $secure, 'samesite' => 'Lax', 'path' => '/']);
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
}

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
    $pdo->exec("SET time_zone = '+00:00'");
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
    $stmt = db()->prepare("SELECT u.*, COALESCE(NULLIF(o.name,''),'My workspace') AS company_name FROM users u JOIN owners o ON o.id=u.owner_id WHERE u.id=? AND u.is_active=1 LIMIT 1");
    $stmt->execute([$_SESSION['user_id']]); return $stmt->fetch() ?: null;
}
function require_login(): array
{
    $user = current_user(); if (!$user) { flash('error', 'Please sign in to continue.'); redirect('login'); } return $user;
}
function development_verification_bypass(): bool
{
    return config('app.environment') === 'local' && config('app.bypass_email_verification', false) === true;
}
function email_access_allowed(array $user): bool
{
    return (bool)($user['is_email_verified'] ?? false) || development_verification_bypass();
}
function require_verified(): array
{
    $user = require_login(); if (!email_access_allowed($user)) { flash('error', 'Verify your email to access operational features.'); redirect('dashboard'); } return $user;
}
function require_scope(string $siteId, string $unitId): array
{
    $user = require_verified();
    $sql = 'SELECT 1 FROM units un JOIN plants p ON p.id=un.plant_id JOIN sites s ON s.id=p.site_id JOIN spaces sp ON sp.id=s.space_id WHERE sp.owner_id=? AND s.id=? AND un.id=?';
    $params = [$user['owner_id'], $siteId, $unitId];
    if ($user['role'] !== 'OWNER_ADMIN') {
        $sql .= ' AND EXISTS(SELECT 1 FROM user_scopes us WHERE us.user_id=? AND us.site_id=s.id AND us.unit_id=un.id)';
        $params[] = $user['id'];
    }
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
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
function send_transactional_email(string $to, string $subject, string $html): bool
{
    $apiKey = trim((string)config('mail.api_key', ''));
    $apiUrl = rtrim((string)config('mail.api_url', 'https://api.sendbyte.africa/v1'), '/');
    $from = trim((string)config('mail.from', ''));
    if ($apiKey === '' || $from === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
        error_log('SendByte email skipped: mail configuration or recipient is invalid.');
        return false;
    }
    if (!function_exists('curl_init')) {
        error_log('SendByte email failed: the PHP cURL extension is unavailable.');
        return false;
    }

    $payload = json_encode([
        'from' => $from,
        'to' => $to,
        'subject' => $subject,
        'html' => $html,
    ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
    $curl = curl_init($apiUrl . '/emails');
    curl_setopt_array($curl, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json',
            'Accept: application/json',
        ],
        CURLOPT_POSTFIELDS => $payload,
    ]);
    $response = curl_exec($curl);
    $status = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $curlError = curl_error($curl);
    curl_close($curl);
    if ($response !== false && $status >= 200 && $status < 300) return true;

    $message = $curlError !== '' ? $curlError : 'HTTP ' . $status;
    if (is_string($response) && $response !== '') {
        $decoded = json_decode($response, true);
        $apiMessage = $decoded['error']['message'] ?? null;
        if (is_string($apiMessage) && $apiMessage !== '') $message .= ': ' . $apiMessage;
    }
    error_log('SendByte email failed: ' . substr($message, 0, 500));
    return false;
}
function issue_verification(array $user): bool
{
    $raw = bin2hex(random_bytes(32));
    $stmt = db()->prepare('INSERT INTO verification_tokens (id,user_id,token_hash,expires_at) VALUES (?,?,?,DATE_ADD(NOW(), INTERVAL 24 HOUR))');
    $stmt->execute([uuid(), $user['id'], hash('sha256', $raw)]);
    $url = base_url('verify?token=' . urlencode($raw));
    $subject = 'Verify your Nonagon account';
    $name = htmlspecialchars((string)$user['full_name'], ENT_QUOTES, 'UTF-8');
    $safeUrl = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
    $body = '<p>Hello ' . $name . ',</p>'
        . '<p>Verify your Nonagon account to unlock operational features.</p>'
        . '<p><a href="' . $safeUrl . '" style="display:inline-block;padding:12px 18px;background:#111827;color:#ffffff;text-decoration:none;border-radius:6px">Verify email address</a></p>'
        . '<p>This link expires in 24 hours. If you did not create this account, you can ignore this email.</p>'
        . '<p style="color:#6b7280;font-size:12px">If the button does not work, open: ' . $safeUrl . '</p>';
    $sent = send_transactional_email((string)$user['email'], $subject, $body);
    if (!$sent && config('app.environment') === 'local') $_SESSION['dev_verification_url'] = $url;
    return $sent;
}
function password_error(string $password): ?string
{
    if (strlen($password) < 8) return 'Use at least 8 characters.';
    if (!preg_match('/[a-z]/', $password) || !preg_match('/[A-Z]/', $password) || !preg_match('/\d/', $password)) return 'Include uppercase, lowercase and a number.';
    return null;
}
