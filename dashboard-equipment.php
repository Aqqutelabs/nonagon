<?php
declare(strict_types=1);
require __DIR__.'/app/operations.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, private');
try {
    $user=current_user();
    if(!$user)throw new DomainException('Sign in to continue.',401);
    if(!email_access_allowed($user))throw new DomainException('Email verification required.',403);
    $filters=operation_filters($user,$_GET);
    [$scope,$params]=operation_scope($user,'e',$filters);
    $query=is_string($_GET['q']??'')?trim($_GET['q']??''):'';
    $status=is_string($_GET['status']??'')?($_GET['status']??''):'';
    if(strlen($query)>255)throw new DomainException('Search is too long.',422);
    if($status!=='') {
        if(!in_array($status,['OPERATIONAL','MAINTENANCE','DOWN'],true))throw new DomainException('Invalid equipment status.',422);
        $scope.=' AND e.status=?';$params[]=$status;
    }
    if($query!=='') {
        $scope.=" AND (e.name LIKE ? ESCAPE '=' OR e.asset_code LIKE ? ESCAPE '=')";
        $search='%'.str_replace(['=','%','_'],['==','=%','=_'],$query).'%';
        $params[]=$search;$params[]=$search;
    }
    $total=(int)rows("SELECT COUNT(*) AS n FROM equipment_status_view e WHERE {$scope}",$params)[0]['n'];
    $pages=max(1,(int)ceil($total/5));$page=max(1,min($pages,(int)($_GET['page']??1)));$offset=($page-1)*5;
    $items=rows("SELECT e.id,e.name,e.asset_code,e.status,e.operator_id,e.operator_name,e.unit_name,e.last_activity_at FROM equipment_status_view e WHERE {$scope} ORDER BY FIELD(e.status,'DOWN','MAINTENANCE','OPERATIONAL'),e.last_activity_at DESC,e.id LIMIT 5 OFFSET {$offset}",$params);
    echo json_encode(['items'=>$items,'total'=>$total,'page'=>$page,'pages'=>$pages],JSON_THROW_ON_ERROR);
} catch(Throwable $error) {
    http_response_code($error instanceof DomainException?$error->getCode():503);
    echo json_encode(['error'=>$error instanceof DomainException?$error->getMessage():'Equipment is temporarily unavailable.']);
}
