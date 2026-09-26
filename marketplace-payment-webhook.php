<?php
declare(strict_types=1);
require __DIR__.'/app/marketplace-transaction.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Allow: POST');
        throw new DomainException('Use POST for payment webhooks.', 405);
    }
    $secret=(string)config('marketplace.payment_webhook_secret','');
    if($secret==='')throw new DomainException('Payment webhook is not configured.',503);
    $raw=file_get_contents('php://input')?:'';$provided=(string)($_SERVER['HTTP_X_NONAGON_SIGNATURE']??'');
    if(str_starts_with($provided,'sha256='))$provided=substr($provided,7);
    if($provided===''||!hash_equals(hash_hmac('sha256',$raw,$secret),$provided))throw new DomainException('Invalid webhook signature.',401);
    $event=json_decode($raw,true,32,JSON_THROW_ON_ERROR);$key=trim((string)($event['idempotency_key']??''));$status=strtoupper(trim((string)($event['status']??'')));$provider=substr(trim((string)($event['provider']??'')),0,80);$reference=substr(trim((string)($event['provider_reference']??'')),0,255);
    if($key===''||$provider===''||$reference===''||!in_array($status,['CONFIRMED','FAILED'],true))throw new DomainException('Webhook payload is incomplete.',422);
    $pdo=db();$pdo->beginTransaction();$payment=rows('SELECT * FROM marketplace_payments WHERE idempotency_key=? FOR UPDATE',[$key])[0]??null;if(!$payment)throw new DomainException('Payment obligation not found.',404);
    if(isset($event['amount'])&&(float)$event['amount']!==(float)$payment['amount'])throw new DomainException('Payment amount does not match the obligation.',409);if(isset($event['currency'])&&strtoupper((string)$event['currency'])!==$payment['currency'])throw new DomainException('Payment currency does not match the obligation.',409);
    if($payment['status']!==$status){db()->prepare('UPDATE marketplace_payments SET payment_provider=?,provider_reference=?,status=?,confirmed_at=IF(?=\'CONFIRMED\',UTC_TIMESTAMP(),NULL),provider_payload=? WHERE id=?')->execute([$provider,$reference,$status,$status,json_encode($event,JSON_THROW_ON_ERROR),$payment['id']]);if($status==='CONFIRMED')db()->prepare("UPDATE marketplace_transactions t SET t.status='PREMOBILIZATION' WHERE t.id=? AND t.status='PAYMENT_PENDING' AND EXISTS(SELECT 1 FROM marketplace_agreements a WHERE a.transaction_id=t.id AND a.status='SIGNED')")->execute([$payment['transaction_id']]);$auditData=['payment_id'=>$payment['id'],'provider'=>$provider,'reference'=>$reference];db()->prepare('INSERT INTO marketplace_transaction_activity(transaction_id,event_type,event_data) VALUES(?,?,?)')->execute([$payment['transaction_id'],'PAYMENT_WEBHOOK_'.$status,json_encode($auditData,JSON_THROW_ON_ERROR)]);marketplace_commercial_audit($payment['transaction_id'],null,'PAYMENT_WEBHOOK_'.$status,$auditData);}
    $pdo->commit();http_response_code(200);echo json_encode(['ok'=>true,'payment_id'=>$payment['id'],'status'=>$status],JSON_THROW_ON_ERROR);
} catch(Throwable $error) {
    if(isset($pdo)&&$pdo->inTransaction())$pdo->rollBack();$code=$error instanceof DomainException&&$error->getCode()>=400?$error->getCode():400;http_response_code($code);echo json_encode(['ok'=>false,'error'=>$error instanceof DomainException?$error->getMessage():'Webhook could not be processed.'],JSON_THROW_ON_ERROR);
}
