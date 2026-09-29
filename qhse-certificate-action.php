<?php
declare(strict_types=1);
require __DIR__.'/app/qhse.php';
$user=require_verified();verify_csrf();$action=(string)($_POST['action']??'');
try{
 if($action==='save'){$id=qhse_certificate_save($user,$_POST);flash('success','Certificate draft saved. Review it before submitting.');redirect('qhse-certificates?id='.urlencode($id));}
 if($action==='supersede'){$newId=qhse_certificate_supersede($user,qhse_text($_POST,'id',36,true),qhse_text($_POST,'notes',1000,true));flash('success','Revision draft created. The issued certificate remains preserved as superseded.');redirect('qhse-certificates?id='.urlencode($newId).'&edit=1');}
 if(in_array($action,['submit','approve','reject','issue','revoke'],true)){$id=qhse_text($_POST,'id',36,true);qhse_certificate_transition($user,$id,$action,qhse_text($_POST,'notes',1000));flash('success','Certificate '.($action==='submit'?'submitted for review':$action.'d').'.');redirect('qhse-certificates?id='.urlencode($id));}
 throw new DomainException('Unknown certificate action.',422);
}catch(Throwable $error){flash('error',$error instanceof DomainException?$error->getMessage():'The certificate action could not be completed.');$id=trim((string)($_POST['id']??''));redirect($id?'qhse-certificates?id='.urlencode($id):'qhse-certificates?create=1');}
