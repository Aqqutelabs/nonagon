<?php
declare(strict_types=1);
require_once __DIR__.'/bootstrap.php';

final class XinngTransportException extends RuntimeException
{
    public function __construct(public readonly string $method, public readonly string $endpoint, string $detail)
    {
        parent::__construct('Xinng '.$method.' '.$endpoint.' failed before returning an HTTP response: '.$detail);
    }
}

final class XinngApiException extends DomainException
{
    public function __construct(public readonly int $status, public readonly array $response, string $endpoint, string $method)
    {
        $error = $response['error'] ?? 'request_failed';
        $detail = [];
        foreach (['error','message','details'] as $key) {
            if (isset($response[$key]) && is_scalar($response[$key])) {
                $value = trim((string)$response[$key]);
                if ($value !== '') $detail[] = $value;
            }
        }
        $detail = array_values(array_unique($detail));
        $reason = $detail ? implode(': ', $detail) : 'The API did not provide an error description.';
        $message = match ($status) {
            401 => 'Xinng rejected '.$method.' '.$endpoint.' with HTTP 401: '.$reason.' Check that XINNG_API_BASE_URL resolves to the expected Xinng API endpoint and that the request includes the customer user_id.',
            402 => 'The Xinng API returned HTTP 402. Verify the short-link API creation policy.',
            404 => 'The saved Xinng short link is no longer available.',
            409 => !empty($response['requires_confirmation'])
                ? 'The QR destination changed. Confirm creation of a new short link to continue.'
                : (is_string($error) ? $error : 'Xinng reported a link conflict.'),
            422 => is_string($error) ? $error : 'Xinng rejected the short-link details.',
            default => 'Xinng '.$method.' '.$endpoint.' failed with HTTP '.$status.': '.$reason,
        };
        parent::__construct($message, $status);
    }
}

function xinng_api_endpoint(string $script): string
{
    if (!in_array($script,['short-links.php','qr-codes.php'],true)) {
        throw new InvalidArgumentException('Unsupported Xinng API endpoint.');
    }
    $configured = trim((string)config('xinng.api_base_url',''));
    if ($configured === '') throw new DomainException('Configure XINNG_API_BASE_URL on the server before creating QR links.',503);

    $parts = parse_url($configured);
    $scheme = strtolower((string)($parts['scheme'] ?? ''));
    $host = strtolower(trim((string)($parts['host'] ?? ''),'[]'));
    $isLoopback = $host === 'localhost' || $host === '::1'
        || (filter_var($host,FILTER_VALIDATE_IP,FILTER_FLAG_IPV4) && str_starts_with($host,'127.'));
    if (!$parts || !$host || !in_array($scheme,['https','http'],true)
        || ($scheme !== 'https' && !$isLoopback)
        || isset($parts['user']) || isset($parts['pass']) || isset($parts['query']) || isset($parts['fragment'])) {
        throw new DomainException('XINNG_API_BASE_URL must be an HTTPS URL (HTTP is allowed only for localhost development) without credentials, query, or fragment.',503);
    }

    $path = rtrim((string)($parts['path'] ?? ''),'/');
    if (preg_match('~/api/(short-links|qr-codes)\.php$~i',$path)) {
        $path = preg_replace('~/(?:short-links|qr-codes)\.php$~i','/'.$script,$path);
    } elseif (preg_match('~/api$~i',$path)) {
        $path .= '/'.$script;
    } else {
        $path .= '/api/'.$script;
    }
    $authority = $host;
    if (str_contains($host,':')) $authority = '['.$host.']';
    if (isset($parts['port'])) $authority .= ':'.(int)$parts['port'];
    return $scheme.'://'.$authority.$path;
}

function xinng_user_id(string $userId): string
{
    if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $userId)) {
        throw new InvalidArgumentException('Xinng customer IDs must be UUIDs.');
    }
    return $userId;
}

function xinng_api_call(string $method, string $userId, ?array $payload = null): array
{
    $method = strtoupper($method);
    if ($method === 'GET') throw new InvalidArgumentException('The Xinng short-link API does not support GET requests; create links with POST.');
    $userId = xinng_user_id($userId);
    $endpoint = xinng_api_endpoint('short-links.php');

    $handle = curl_init($endpoint);
    curl_setopt_array($handle, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => [
            'Accept: application/json',
            'Content-Type: application/json',
        ],
        CURLOPT_CONNECTTIMEOUT => 30,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_FOLLOWLOCATION => false,
    ]);
    if ($method !== 'GET') {
        $payload = array_merge($payload ?? [], ['user_id'=>$userId]);
        curl_setopt($handle, CURLOPT_POSTFIELDS, json_encode($payload, JSON_THROW_ON_ERROR));
    }
    $body = curl_exec($handle);
    $curlError = curl_error($handle);
    $status = (int)curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
    curl_close($handle);
    if ($body === false || $status === 0) {
        throw new XinngTransportException($method,$endpoint,$curlError ?: 'cURL returned no HTTP response and no error detail.');
    }

    $response = json_decode((string)$body, true);
    if (!is_array($response)) {
        throw new DomainException('Xinng '.$method.' '.$endpoint.' returned HTTP '.$status.' with an invalid JSON response.',502);
    }
    if ($status < 200 || $status >= 300 || empty($response['ok'])) throw new XinngApiException($status,$response,$endpoint,$method);
    return $response;
}

function xinng_link_from_response(array $response): array
{
    $link = $response['short_link'] ?? null;
    if (!is_array($link) || !isset($link['id'], $link['full_short_url'], $link['back_half']) || !ctype_digit((string)$link['id'])) {
        throw new DomainException('Xinng returned an incomplete short-link record.', 502);
    }
    $url = xinng_strip_xinngqr_path((string)$link['full_short_url']);
    $parts = parse_url($url);
    $host = strtolower((string)($parts['host'] ?? ''));
    if (strtolower((string)($parts['scheme'] ?? '')) === 'http' && in_array($host, ['localhost','127.0.0.1','::1','[::1]'], true)) {
        $publicBase = (string)config('xinng.public_base_url', '');
        $publicParts = parse_url($publicBase);
        if (strtolower((string)($publicParts['scheme'] ?? '')) !== 'https' || empty($publicParts['host'])) {
            throw new DomainException('XINNG_PUBLIC_BASE_URL must be an HTTPS URL when Xinng returns a local short URL.', 503);
        }
        $url = rtrim($publicBase, '/').'/'.ltrim((string)($parts['path'] ?? ''), '/');
        if (isset($parts['query'])) $url .= '?'.$parts['query'];
        if (isset($parts['fragment'])) $url .= '#'.$parts['fragment'];
        $parts = parse_url($url);
    }
    if (strtolower((string)($parts['scheme'] ?? '')) !== 'https' || empty($parts['host'])) {
        throw new DomainException('Xinng returned a short URL that is not HTTPS.', 502);
    }
    $backHalf = (string)$link['back_half'];
    if (!xinng_short_url_has_back_half($url,$backHalf)) {
        throw new DomainException('Xinng returned a short link whose URL does not use the requested four-letter back-half.',502);
    }
    return ['id'=>(int)$link['id'],'url'=>$url,'back_half'=>$backHalf];
}

function xinng_strip_xinngqr_path(string $url): string
{
    return preg_replace('~^(https?://[^/?#]+)/xinngqr(?=/)~', '$1', $url) ?? $url;
}

function xinng_short_url_has_back_half(string $url, ?string $backHalf): bool
{
    if ($backHalf === null || !preg_match('/^[a-z]{4}$/',$backHalf)) return false;
    $parts = parse_url($url);
    if (strtolower((string)($parts['scheme'] ?? '')) !== 'https' || empty($parts['host'])) return false;
    $path = rawurldecode((string)($parts['path'] ?? ''));
    return basename(rtrim($path,'/')) === $backHalf;
}

function xinng_resource_settings(string $type): array
{
    return match ($type) {
        'equipment' => ['equipment','owner_id','Equipment QR','eq'],
        'request' => ['marketplace_requests','organization_id','Request QR','rq'],
        'certificate' => ['qhse_certificates','owner_id','Certificate verification QR','cert'],
        default => throw new InvalidArgumentException('Unsupported Xinng resource type.'),
    };
}

function xinng_back_half(string $type, string $resourceId): string
{
    xinng_resource_settings($type);
    $backHalf = '';
    for ($index = 0; $index < 4; $index++) {
        $backHalf .= chr(random_int(ord('a'),ord('z')));
    }
    return $backHalf;
}

function xinng_back_half_taken(XinngApiException $error): bool
{
    if ($error->status !== 409) return false;
    $details = [];
    foreach (['error','message','details'] as $key) {
        if (isset($error->response[$key]) && is_scalar($error->response[$key])) {
            $details[] = (string)$error->response[$key];
        }
    }
    $message = strtolower(implode(' ', $details));
    return str_contains($message, 'back-half') && str_contains($message, 'already taken');
}

function xinng_call_with_unique_back_half(string $method, string $ownerId, array $payload, string $type, string $resourceId): array
{
    for ($attempt = 0; $attempt < 5; $attempt++) {
        $payload['back_half'] = xinng_back_half($type, $resourceId);
        try {
            return xinng_api_call($method, $ownerId, $payload);
        } catch (XinngApiException $error) {
            if (!xinng_back_half_taken($error) || $attempt === 4) throw $error;
        }
    }
    throw new LogicException('Xinng back-half retry loop ended unexpectedly.');
}

function xinng_ensure_resource_link(string $type, string $resourceId, string $ownerId, string $destination, bool $confirmDestinationChange = false, ?string $titleOverride = null): array
{
    if (strtolower((string)parse_url($destination, PHP_URL_SCHEME)) !== 'https' || !parse_url($destination, PHP_URL_HOST)) {
        throw new DomainException('The public QR destination must use HTTPS. Set APP_URL to the public HTTPS address.', 422);
    }
    [$table,$ownerColumn,$title] = xinng_resource_settings($type);
    $title = $titleOverride ?? $title;
    $pdo = db();
    if ($pdo->inTransaction()) throw new LogicException('Xinng link creation must run outside an existing database transaction.');
    $pdo->beginTransaction();
    try {
        $record = rows("SELECT xinng_short_link_id,xinng_short_url,xinng_destination_url,xinng_back_half FROM {$table} WHERE id=? AND {$ownerColumn}=? FOR UPDATE",[$resourceId,$ownerId])[0] ?? null;
        if (!$record) throw new DomainException('The QR resource is no longer available in this organization.',404);

        if ($type === 'equipment') {
            $existing = !empty($record['xinng_short_link_id']) && !empty($record['xinng_short_url']);
            $qrResponse = xinng_qr_api_call('GET',$ownerId);
            $link = xinng_qr_link_for_destination($qrResponse,$destination);

            if ($link && (!$existing || ($record['xinng_destination_url'] ?? '') === $destination)) {
                if ($existing && xinng_strip_xinngqr_path((string)$record['xinng_short_url']) !== $link['url']) {
                    if (!$confirmDestinationChange) {
                        throw new DomainException('The saved short link is not a four-letter QR link. Confirm to replace it.',409);
                    }
                } else {
                    $pdo->prepare("UPDATE {$table} SET xinng_short_link_id=?,xinng_short_url=?,xinng_destination_url=?,xinng_back_half=? WHERE id=? AND {$ownerColumn}=?")
                        ->execute([$link['id'],$link['url'],$destination,$link['back_half'],$resourceId,$ownerId]);
                    $pdo->commit();
                    $link['destination'] = $destination;
                    return $link;
                }
            } elseif ($existing && !$confirmDestinationChange) {
                throw new DomainException('The saved short link is not a four-letter QR link. Confirm to replace it.',409);
            }

            if ($existing && ($record['xinng_destination_url'] ?? '') !== $destination && !$confirmDestinationChange) {
                throw new DomainException('The QR destination changed. Confirm to create a replacement short link.',409);
            }

            if (!$link || $existing) {
                $qrCode = xinng_qr_create_link($ownerId,$type,$resourceId,$title,$destination);
                $link = [
                    'id'=>$qrCode['id'],
                    'url'=>$qrCode['url'],
                    'back_half'=>$qrCode['back_half'],
                    'destination'=>$destination,
                ];
            }
        } elseif (!empty($record['xinng_short_link_id']) && !empty($record['xinng_short_url'])) {
            if (($record['xinng_destination_url'] ?? '') === $destination) {
                $savedUrl = xinng_strip_xinngqr_path((string)$record['xinng_short_url']);
                if (xinng_short_url_has_back_half($savedUrl,isset($record['xinng_back_half'])?(string)$record['xinng_back_half']:null)) {
                    if ($savedUrl !== (string)$record['xinng_short_url']) {
                        $pdo->prepare("UPDATE {$table} SET xinng_short_url=? WHERE id=? AND {$ownerColumn}=?")
                            ->execute([$savedUrl,$resourceId,$ownerId]);
                    }
                    $pdo->commit();
                    return ['id'=>(int)$record['xinng_short_link_id'],'url'=>$savedUrl,'back_half'=>$record['xinng_back_half'],'destination'=>$destination];
                }
                if (!$confirmDestinationChange) {
                    throw new DomainException('The saved short link does not use a four-letter code. Confirm to create a replacement link.',409);
                }
            }
            $payload = ['id'=>(int)$record['xinng_short_link_id'],'destination_url'=>$destination];
            if ($confirmDestinationChange) $payload['confirm_create_new'] = true;
            try {
                $response = xinng_call_with_unique_back_half('PATCH',$ownerId,$payload,$type,$resourceId);
            } catch (XinngApiException $error) {
                if ($error->status === 409 && !empty($error->response['requires_confirmation'])) {
                    throw new DomainException($error->getMessage(),409);
                }
                throw $error;
            }
            if (empty($response['created_new'])) throw new DomainException('Xinng did not confirm creation of the replacement short link.',502);
            $link = xinng_link_from_response($response);
        } else {
            $payload = ['title'=>$title,'destination_url'=>$destination];
            try {
                $response = xinng_call_with_unique_back_half('POST',$ownerId,$payload,$type,$resourceId);
                $link = xinng_link_from_response($response);
            } catch (XinngTransportException $error) {
                throw new DomainException($error->getMessage().' The outcome is unknown, so no retry was sent; verify the link in Xinng before retrying.',502,$error);
            }
        }

        $pdo->prepare("UPDATE {$table} SET xinng_short_link_id=?,xinng_short_url=?,xinng_destination_url=?,xinng_back_half=? WHERE id=? AND {$ownerColumn}=?")
            ->execute([$link['id'],$link['url'],$destination,$link['back_half'],$resourceId,$ownerId]);
        $pdo->commit();
        $link['destination'] = $destination;
        return $link;
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        throw $error;
    }
}

function xinng_equipment_link(array $user, string $equipmentId, bool $confirmDestinationChange = false): array
{
    require_once __DIR__.'/operations.php';
    $equipment = operation_equipment($user,$equipmentId);
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $record = rows('SELECT public_qr_token FROM equipment WHERE id=? AND owner_id=? AND archived_at IS NULL FOR UPDATE',[$equipmentId,$user['owner_id']])[0] ?? null;
        if (!$record) throw new DomainException('Equipment is no longer available.',404);
        $token = $record['public_qr_token'] ?: bin2hex(random_bytes(32));
        if (!$record['public_qr_token']) db()->prepare('UPDATE equipment SET public_qr_token=? WHERE id=? AND owner_id=?')->execute([$token,$equipmentId,$user['owner_id']]);
        $pdo->commit();
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        throw $error;
    }
    return xinng_ensure_resource_link('equipment',$equipmentId,$user['owner_id'],base_url('equipment-public?token='.rawurlencode($token)),$confirmDestinationChange,(string)$equipment['name']);
}

function xinng_request_link(array $user, string $requestId): array
{
    require_once __DIR__.'/request-distribution.php';
    $request = request_owned($user,$requestId);
    $type = $request['visibility']==='PUBLIC'?'PUBLIC':'PRIVATE_LINK';
    $share = request_share_create($user,$requestId,$type,null,$request['response_deadline']);
    $destination = base_url('request-share?t='.rawurlencode($share['token']));
    return xinng_ensure_resource_link('request',$requestId,$user['owner_id'],$destination);
}

function xinng_archive_link(int $linkId, string $userId): void
{
    if ($linkId < 1) throw new InvalidArgumentException('Invalid Xinng link ID.');
    xinng_api_call('DELETE',$userId,['id'=>$linkId]);
}

function xinng_decode_api_response(string $body, string $method, string $endpoint, int $status): array
{
    $response = json_decode($body,true);
    if (is_array($response)) return $response;

    $diagnostics = [];
    $jsonBody = $body;
    while (preg_match('/^\s*(?:<br\s*\/?>\s*)?<(?:b|strong)>(Warning|Notice|Deprecated|Fatal error)<\/(?:b|strong)>:\s*(.*?)(?:<br\s*\/?>|$)\s*/is', $jsonBody, $match)) {
        $diagnostics[] = trim(strip_tags($match[0]));
        $jsonBody = substr($jsonBody,strlen($match[0]));
    }
    if ($diagnostics) {
        $response = json_decode($jsonBody,true);
        if (is_array($response)) {
            error_log('Xinng '.$method.' '.$endpoint.' emitted PHP diagnostics before its JSON response: '.implode(' | ',$diagnostics));
            return $response;
        }
    }
    throw new DomainException('Xinng '.$method.' '.$endpoint.' returned HTTP '.$status.' with an invalid JSON response.',502);
}

function xinng_qr_api_call(string $method, string $userId, ?array $payload = null): array
{
    $method = strtoupper($method);
    if (!in_array($method,['GET','POST'],true)) {
        throw new InvalidArgumentException('The Xinng QR-code API supports GET and POST requests only.');
    }
    $userId = xinng_user_id($userId);
    $endpoint = xinng_api_endpoint('qr-codes.php');
    if ($method === 'GET') $endpoint .= '?'.http_build_query(['user_id'=>$userId]);

    $handle = curl_init($endpoint);
    curl_setopt_array($handle,[
        CURLOPT_RETURNTRANSFER=>true,
        CURLOPT_CUSTOMREQUEST=>$method,
        CURLOPT_HTTPHEADER=>['Accept: application/json','Content-Type: application/json'],
        CURLOPT_CONNECTTIMEOUT=>30,
        CURLOPT_TIMEOUT=>60,
        CURLOPT_FOLLOWLOCATION=>false,
    ]);
    if ($method === 'POST') {
        $payload = array_merge($payload ?? [],['user_id'=>$userId]);
        curl_setopt($handle,CURLOPT_POSTFIELDS,json_encode($payload,JSON_THROW_ON_ERROR));
    }
    $body = curl_exec($handle);
    $curlError = curl_error($handle);
    $status = (int)curl_getinfo($handle,CURLINFO_RESPONSE_CODE);
    curl_close($handle);
    if ($body === false || $status === 0) {
        throw new XinngTransportException($method,$endpoint,$curlError ?: 'cURL returned no HTTP response and no error detail.');
    }
    $response = xinng_decode_api_response((string)$body,$method,$endpoint,$status);
    if ($status < 200 || $status >= 300 || empty($response['ok'])) {
        throw new XinngApiException($status,$response,$endpoint,$method);
    }
    return $response;
}

function xinng_qr_create_payload(string $type, string $resourceId, string $title, string $destination): array
{
    return [
        'title'=>$title,
        'type'=>'website',
        'destination_url'=>$destination,
        'back_half'=>xinng_back_half($type,$resourceId),
    ];
}

function xinng_qr_link_for_destination(array $response, string $destination): ?array
{
    foreach (($response['qr_codes'] ?? []) as $qrCode) {
        if (!is_array($qrCode)
            || ($qrCode['destination_url'] ?? null) !== $destination
            || ($qrCode['status'] ?? 'active') !== 'active'
            || !isset($qrCode['id'],$qrCode['full_short_url'],$qrCode['back_half'])
            || !ctype_digit((string)$qrCode['id'])) {
            continue;
        }
        try {
            $link = xinng_link_from_response(['short_link'=>$qrCode]);
        } catch (DomainException) {
            continue;
        }
        $link['qr_image_url'] = xinng_public_qr_image_url((string)($qrCode['qr_image_url'] ?? ''),$link['url']);
        return $link;
    }
    return null;
}

function xinng_qr_create_link(string $userId, string $type, string $resourceId, string $title, string $destination): array
{
    $payload = xinng_qr_create_payload($type,$resourceId,$title,$destination);
    for ($attempt = 0; ; $attempt++) {
        try {
            $response = xinng_qr_api_call('POST',$userId,$payload);
            $qrCode = $response['qr_code'] ?? null;
            if (!is_array($qrCode)) throw new DomainException('Xinng created a QR record without returning its link.',502);
            $link = xinng_link_from_response(['short_link'=>$qrCode]);
            $link['qr_image_url'] = xinng_public_qr_image_url((string)($qrCode['qr_image_url'] ?? ''),$link['url']);
            return $link;
        } catch (XinngApiException $error) {
            if (!xinng_back_half_taken($error) || $attempt >= 4) throw $error;
            $payload['back_half'] = xinng_back_half($type,$resourceId);
        }
    }
}

function xinng_qr_image_url(string $destination, string $userId, string $title, string $resourceType, string $resourceId, ?string $expectedShortUrl = null): string
{
    if (strtolower((string)parse_url($destination,PHP_URL_SCHEME))!=='https' || !parse_url($destination,PHP_URL_HOST)) {
        throw new InvalidArgumentException('QR destinations must use HTTPS.');
    }
    if ($expectedShortUrl !== null) $expectedShortUrl = xinng_strip_xinngqr_path($expectedShortUrl);
    $userId = xinng_user_id($userId);
    $response = xinng_qr_api_call('GET',$userId);
    foreach (($response['qr_codes'] ?? []) as $qrCode) {
        if (!is_array($qrCode)
            || ($qrCode['destination_url'] ?? null) !== $destination
            || ($qrCode['status'] ?? 'active') !== 'active'
            || !isset($qrCode['id'],$qrCode['full_short_url'],$qrCode['back_half'])
            || !ctype_digit((string)$qrCode['id'])) continue;
        try {
            $link = xinng_link_from_response(['short_link'=>$qrCode]);
        } catch (DomainException) {
            continue;
        }
        if ($expectedShortUrl !== null && $link['url'] !== $expectedShortUrl) continue;
        return xinng_public_qr_image_url((string)($qrCode['qr_image_url'] ?? ''),$link['url']);
    }
    if ($expectedShortUrl !== null) {
        throw new DomainException('Xinng did not return the QR image belonging to this short link.',502);
    }

    $payload = xinng_qr_create_payload($resourceType,$resourceId,$title,$destination);
    for ($attempt = 0; ; $attempt++) {
        try {
            $response = xinng_qr_api_call('POST',$userId,$payload);
            break;
        } catch (XinngApiException $error) {
            if (!xinng_back_half_taken($error) || $attempt >= 4) throw $error;
            $payload['back_half'] = xinng_back_half($resourceType,$resourceId);
        }
    }
    $qrCode = $response['qr_code'] ?? null;
    if (!is_array($qrCode)
        || !is_string($qrCode['qr_image_url'] ?? null)
        || !is_string($qrCode['full_short_url'] ?? null)
        || !is_string($qrCode['back_half'] ?? null)) {
        throw new DomainException('Xinng created a QR record without returning its short URL and image URL.',502);
    }
    $link = xinng_link_from_response(['short_link'=>$qrCode]);
    return xinng_public_qr_image_url($qrCode['qr_image_url'],$link['url']);
}

function xinng_public_qr_image_url(string $imageUrl, string $shortUrl): string
{
    $imageUrl = xinng_validate_qr_image_url($imageUrl);
    $parts = parse_url($imageUrl);
    parse_str((string)($parts['query'] ?? ''),$parameters);
    if (!isset($parameters['data']) || !is_string($parameters['data'])) {
        throw new DomainException('Xinng returned a QR image URL without an encoded destination.',502);
    }
    $parameters['data'] = $shortUrl;
    return 'https://api.qrserver.com/v1/create-qr-code/?'.http_build_query($parameters,'','&',PHP_QUERY_RFC3986);
}

function xinng_validate_qr_image_url(string $url): string
{
    $parts = parse_url($url);
    if (strtolower((string)($parts['scheme'] ?? '')) !== 'https'
        || strtolower((string)($parts['host'] ?? '')) !== 'api.qrserver.com'
        || ($parts['path'] ?? '') !== '/v1/create-qr-code/'
        || isset($parts['user']) || isset($parts['pass'])) {
        throw new DomainException('Xinng returned an unsupported QR image URL.',502);
    }
    return $url;
}

function xinng_qr_data_uri(string $destination, string $userId, string $title, string $resourceType, string $resourceId, ?string $expectedShortUrl = null): string
{
    return xinng_qr_data_uri_from_image_url(xinng_qr_image_url($destination,$userId,$title,$resourceType,$resourceId,$expectedShortUrl));
}

function xinng_qr_data_uri_from_image_url(string $imageUrl): string
{
    $imageUrl = xinng_validate_qr_image_url($imageUrl);
    $handle = curl_init($imageUrl);
    $image = '';
    curl_setopt_array($handle, [
        CURLOPT_RETURNTRANSFER => false,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_CONNECTTIMEOUT => 30,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_WRITEFUNCTION => static function ($handle, string $chunk) use (&$image): int {
            $image ??= '';
            if (strlen($image) + strlen($chunk) > 1048576) return 0;
            $image .= $chunk;
            return strlen($chunk);
        },
    ]);
    $downloaded = curl_exec($handle);
    $curlError = curl_error($handle);
    $status = (int)curl_getinfo($handle,CURLINFO_RESPONSE_CODE);
    $contentType = strtolower(trim(explode(';',(string)curl_getinfo($handle,CURLINFO_CONTENT_TYPE))[0]));
    curl_close($handle);
    if ($downloaded === false || $status < 200 || $status >= 300) {
        throw new DomainException('QR image download from '.$imageUrl.' failed'.($curlError ? ': '.$curlError : ' (HTTP '.$status.').'),502);
    }
    if ($contentType !== 'image/png' || $image === '') {
        throw new DomainException('Xinng returned an invalid QR image.',502);
    }
    return 'data:'.$contentType.';base64,'.base64_encode($image);
}