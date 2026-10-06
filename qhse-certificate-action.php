<?php
declare(strict_types=1);
require __DIR__.'/app/qhse.php';
require_once __DIR__.'/app/xinng.php';
$user=require_verified();verify_csrf();$action=(string)($_POST['action']??'');
try{
 if($action==='save'){$id=qhse_certificate_save($user,$_POST);flash('success','Certificate draft saved. Review it before submitting.');redirect('qhse-certificates?id='.urlencode($id));}
 if($action==='supersede'){$newId=qhse_certificate_supersede($user,qhse_text($_POST,'id',36,true),qhse_text($_POST,'notes',1000,true));flash('success','Revision draft created. The issued certificate remains preserved as superseded.');redirect('qhse-certificates?id='.urlencode($newId).'&edit=1');}
 if($action==='qr.create'){$id=qhse_text($_POST,'id',36,true);xinng_certificate_link($user,$id,($_POST['confirm_destination_change']??'')==='1');flash('success','Certificate verification QR is ready.');redirect('qhse-certificate-print?id='.urlencode($id));}
 if(in_array($action,['submit','approve','reject','issue','revoke'],true)){$id=qhse_text($_POST,'id',36,true);qhse_certificate_transition($user,$id,$action,qhse_text($_POST,'notes',1000));$message='Certificate '.($action==='submit'?'submitted for review':$action.'d').'.';if($action==='issue'){try{xinng_certificate_link($user,$id);$message='Certificate issued and its xin.ng verification QR is ready.';}catch(Throwable $qrError){error_log('Certificate QR creation: '.$qrError->getMessage());$message='Certificate issued, but its xin.ng QR could not be created: '.($qrError instanceof DomainException?$qrError->getMessage():'xin.ng is temporarily unavailable.');}}flash('success',$message);redirect('qhse-certificates?id='.urlencode($id));}
 throw new DomainException('Unknown certificate action.',422);
}catch(Throwable $error){$id=trim((string)($_POST['id']??''));$message=$error instanceof DomainException?$error->getMessage():'The certificate action could not be completed.';flash('error',$message);redirect($id?($action==='qr.create'?'qhse-certificate-print?id='.urlencode($id).(($error instanceof DomainException&&$error->getCode()===409)?'&confirm_qr=1':''):'qhse-certificates?id='.urlencode($id)):'qhse-certificates?create=1');}
