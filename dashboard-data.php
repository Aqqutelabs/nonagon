<?php
declare(strict_types=1);
require __DIR__ . '/app/operations.php';
header('Cache-Control: no-store, private');
header('Content-Type: application/json; charset=utf-8');
$started = microtime(true);
try {
    $user = current_user();
    if (!$user) throw new DomainException('Sign in to continue.',401);
    if (!email_access_allowed($user)) throw new DomainException('Verify your email to view operations.',403);
    $filters = operation_filters($user,$_GET);
    $rawHashes = $_GET['hashes'] ?? '{}';
    if (!is_string($rawHashes) || strlen($rawHashes)>1000) throw new DomainException('Invalid update cursor.',422);
    $hashes = json_decode($rawHashes,true);
    if (!is_array($hashes)) $hashes = [];
    $stream = ($_GET['stream'] ?? '') === '1';
    session_write_close();
    if ($stream) {
        ini_set('session.use_cookies','0');
        session_cache_limiter('');
        header('Content-Type: text/event-stream; charset=utf-8');
        header('X-Accel-Buffering: no');
        @ini_set('zlib.output_compression','0');
        while (ob_get_level()>0) ob_end_flush();
        echo "retry: 3000\n\n";
        flush();
    }
    $scopeKey = dashboard_scope_key($user,$filters);
    do {
        // Reload session state so logout in another tab also revokes this stream.
        if ($stream) {
            $_SESSION = [];
            session_start(['read_and_close'=>true]);
        }
        $fresh = current_user();
        if (!$fresh || !email_access_allowed($fresh) || dashboard_scope_key($fresh,$filters) !== $scopeKey) {
            if ($stream) { echo "event: revoked\ndata: {}\n\n"; flush(); exit; }
            throw new DomainException('Access changed. Reload your dashboard.',403);
        }
        $state = dashboard_state($fresh,$filters);
        $delta = dashboard_delta($state,$hashes);
        $json = json_encode($delta,JSON_THROW_ON_ERROR);
        if (!$stream) {
            header('Server-Timing: dashboard;dur=' . round((microtime(true)-$started)*1000,1));
            echo $json;
            break;
        }
        echo 'event: ' . ($delta['changes'] ? 'delta' : 'freshness') . "\ndata: {$json}\n\n";
        flush();
        $hashes = $delta['hashes'];
        if (connection_aborted()) break;
        sleep(3);
    } while (microtime(true)-$started<24);
} catch (Throwable $error) {
    if (!empty($stream) && headers_sent()) {
        echo "event: unavailable\ndata: {}\n\n";
        flush();
    } else {
        http_response_code($error instanceof DomainException ? $error->getCode() : 503);
        echo json_encode(['error'=>$error instanceof DomainException ? $error->getMessage() : 'Operational data is temporarily unavailable.']);
    }
    if (!$error instanceof DomainException) error_log('Dashboard data error: ' . $error->getMessage());
}
