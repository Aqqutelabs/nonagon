<?php
declare(strict_types=1);
require __DIR__.'/app/commercial.php';
$user=require_verified();
$id=commercial_text($_GET,'id',36,true);
$asset=(string)($_GET['asset']??'');
if(!in_array($asset,['logo','header','footer'],true)){http_response_code(404);exit;}
$brand=rows('SELECT '.$asset.'_mime AS mime,'.$asset.'_data AS data FROM commercial_brands WHERE id=? AND organization_id=?',[$id,$user['owner_id']])[0]??null;
if(!$brand||!$brand['data']){http_response_code(404);exit;}
header('Content-Type: '.$brand['mime']);
header('Cache-Control: private, max-age=3600');
header('Content-Length: '.strlen($brand['data']));
echo $brand['data'];
