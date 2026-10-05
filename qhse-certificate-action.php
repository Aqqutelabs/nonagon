<?php
declare(strict_types=1);
require __DIR__.'/app/qhse.php';
require_once __DIR__.'/app/xinng.php';
$user=require_verified();verify_csrf();$action=(string)($_POST['action']??'');
try{
 if($action==='qr.create'){
  if(!qhse_can_approve($user))throw new DomainException('You cannot create QR links for certificates.',403);
  $id=qhse_text($_POST,'id',36,true);$certificate=qhse_certificate($user,$id,true);
  if($certificate['status']!=='ISSUED')throw new DomainException('Generate a certificate QR only after the certificate is published.',409);
  xinng_ensure_resource_link('certificate',$id,$user['owner_id'],base_url('qhse-certificate-verify?token='.rawurlencode($certificate['verification_token'])),($_POST['confirm_destination_change']??'')==='1');
  flash('success','Certificate verification QR link is ready.');
  redirect('qhse-certificate-print?id='.urlencode($id));
 }
 if($action==='save'){
  $id=qhse_certificate_save($user,$_POST);
  flash('success','Certificate draft saved. Publish it when ready.');
  redirect('qhse-certificates?id='.urlencode($id));
 }
 if($action==='duplicate'){
  $newId=qhse_certificate_duplicate($user,qhse_text($_POST,'id',36,true));
  flash('success','New pressure test draft created with a new certificate number. Dates, results and signatures were reset.');
  redirect('qhse-certificates?id='.urlencode($newId).'&edit=1');
 }
 if($action==='supersede'){
  $newId=qhse_certificate_supersede($user,qhse_text($_POST,'id',36,true),qhse_text($_POST,'notes',1000,true));
  flash('success','Revision draft created. The issued certificate remains preserved as superseded.');
  redirect('qhse-certificates?id='.urlencode($newId).'&edit=1');
 }
 if(in_array($action,['publish','revoke'],true)){
  $id=qhse_text($_POST,'id',36,true);
  qhse_certificate_transition($user,$id,$action,qhse_text($_POST,'notes',1000));
  $message=$action==='publish'?'Certificate published.':'Certificate revoked.';
  if($action==='publish'){
   try{
    $certificate=qhse_certificate($user,$id);
    xinng_ensure_resource_link('certificate',$id,$user['owner_id'],base_url('qhse-certificate-verify?token='.rawurlencode($certificate['verification_token'])));
    $message='Certificate published and its verification QR is ready. One Xinng credit was used.';
   }catch(Throwable $qrError){
    error_log('Certificate QR creation: '.$qrError->getMessage());
    $message='Certificate published, but its QR could not be created: '.($qrError instanceof DomainException?$qrError->getMessage():'Xinng is temporarily unavailable.');
   }
  }
  flash('success',$message);
  redirect('qhse-certificates?id='.urlencode($id));
 }
 throw new DomainException('Unknown certificate action.',422);
}catch(Throwable $error){
 $id=trim((string)($_POST['id']??''));
 $message=$error instanceof DomainException?$error->getMessage():'The certificate action could not be completed.';
 flash('error',$message);
 redirect($id?($action==='qr.create'?'qhse-certificate-print?id='.urlencode($id).(($error instanceof DomainException&&$error->getCode()===409)?'&confirm_qr=1':''):'qhse-certificates?id='.urlencode($id)):'qhse-certificates?create=1');
}
