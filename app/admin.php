<?php
declare(strict_types=1);
require_once __DIR__.'/investment-participation.php';

const NONAGON_ADMIN_EMAIL='aqqute.dev@gmail.com';
function is_nonagon_admin(?array $user):bool{return $user&&strtolower((string)$user['email'])===NONAGON_ADMIN_EMAIL&&in_array($user['role'],['OWNER_ADMIN','ADMIN'],true);}
function require_nonagon_admin():array{$user=require_login();if(!is_nonagon_admin($user)){http_response_code(403);exit('Nonagon administrator access required.');}return $user;}
function admin_audit(array $user,string $event,string $entityType,string $entityId,array $data=[]):void
{
    db()->prepare('INSERT INTO nonagon_admin_events(actor_user_id,event_type,entity_type,entity_id,event_data) VALUES(?,?,?,?,?)')->execute([$user['id'],$event,$entityType,$entityId,$data?json_encode($data,JSON_THROW_ON_ERROR):null]);
}
