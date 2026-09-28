<?php
declare(strict_types=1);
require __DIR__.'/app/commercial.php';
header('Content-Type: application/json; charset=utf-8');
try{
    if($_SERVER['REQUEST_METHOD']!=='POST')throw new DomainException('Use POST.',405);
    $user=require_verified();if(empty($_POST['csrf'])&&!empty($_POST['csrf_token']))$_POST['csrf']=$_POST['csrf_token'];verify_csrf();$type=(string)($_POST['modal_type']??'');
    if($type==='customer'){
        $id=commercial_customer_save($user,$_POST);$row=commercial_customer($user,$id);$result=['id'=>$id,'label'=>$row['name'],'currency'=>$row['currency']];
    }elseif($type==='account'){
        $id=commercial_bank_save($user,$_POST);$row=rows('SELECT * FROM commercial_bank_accounts WHERE id=? AND organization_id=?',[$id,$user['owner_id']])[0];$result=['id'=>$id,'label'=>$row['bank_name'].' · '.$row['currency'].' · '.$row['account_number']];
    }elseif($type==='brand'){
        $id=commercial_brand_save($user,$_POST,$_FILES);$row=rows('SELECT * FROM commercial_brands WHERE id=? AND organization_id=?',[$id,$user['owner_id']])[0];$result=['id'=>$id,'label'=>$row['brand_name']];
    }elseif($type==='identity'){
        $current=commercial_profile($user);$merged=array_merge($current,$_POST);commercial_profile_save($user,$merged);$result=['id'=>'profile','label'=>commercial_text($_POST,'legal_name',255,true)];
    }elseif($type==='currency'){
        $code=strtoupper(commercial_text($_POST,'currency',3,true));if(!preg_match('/^[A-Z]{3}$/',$code))throw new DomainException('Enter a valid three-letter currency code.',422);$result=['id'=>$code,'label'=>$code];
    }else throw new DomainException('Unknown create-new form.',422);
    echo json_encode(['ok'=>true,'item'=>$result],JSON_THROW_ON_ERROR);
}catch(Throwable $error){http_response_code($error instanceof DomainException?$error->getCode():500);echo json_encode(['ok'=>false,'message'=>$error instanceof DomainException?$error->getMessage():'The option could not be saved.']);}
