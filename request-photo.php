<?php
declare(strict_types=1);
require __DIR__.'/app/request-supply.php';
header('Cache-Control: public, max-age=300');header('X-Content-Type-Options: nosniff');
try{$id=marketplace_text($_GET,'id',36,true);$photo=rows('SELECT p.mime_type,p.image_data,r.organization_id,r.visibility FROM request_photos p JOIN marketplace_requests r ON r.id=p.request_id WHERE p.id=?',[$id])[0]??null;if(!$photo)throw new DomainException('Photo not found.',404);$user=current_user();if($photo['visibility']!=='PUBLIC'&&(!$user||$user['owner_id']!==$photo['organization_id']))throw new DomainException('Photo not found.',404);header('Content-Type: '.$photo['mime_type']);header('Content-Length: '.strlen($photo['image_data']));echo $photo['image_data'];}catch(Throwable $error){http_response_code($error instanceof DomainException?$error->getCode():503);header('Content-Type: text/plain');echo 'Photo unavailable.';}
