<?php
declare(strict_types=1);require __DIR__.'/app/request-supply.php';header('Cache-Control: no-store, private');$destination='requests';
try{if($_SERVER['REQUEST_METHOD']!=='POST'){header('Allow: POST');throw new DomainException('Use POST for request changes.',405);}$user=require_verified();verify_csrf();$action=(string)($_POST['action']??'');$pdo=db();$pdo->beginTransaction();
 if($action==='request.create'){$id=request_create($user,$_POST,!empty($_POST['publish']));$destination='requests?view=detail&id='.rawurlencode($id);}
 elseif($action==='request.update'){$id=marketplace_text($_POST,'id',36,true);request_update($user,$id,$_POST);$destination='requests?view=detail&id='.rawurlencode($id);}
 elseif($action==='request.publish'){$id=marketplace_text($_POST,'id',36,true);request_publish($user,$id);$destination='requests?view=detail&id='.rawurlencode($id);}
 elseif($action==='request.extend'){$id=marketplace_text($_POST,'id',36,true);request_extend($user,$id,marketplace_text($_POST,'response_deadline',16,true));$destination='requests?view=detail&id='.rawurlencode($id);}
 elseif($action==='request.close'||$action==='request.cancel'){$id=marketplace_text($_POST,'id',36,true);request_close($user,$id,$action==='request.close'?'CLOSED':'CANCELLED');$destination='requests?view=detail&id='.rawurlencode($id);}
 elseif($action==='offer.create'){$input=$_POST;$input['_file']=$_FILES['supporting_document']??[];$id=supply_offer_save($user,$input,!empty($_POST['submit']));$destination='request-offer?id='.rawurlencode($id);}
 elseif($action==='offer.submit'){$id=marketplace_text($_POST,'id',36,true);supply_offer_submit($user,$id);$destination='request-offer?id='.rawurlencode($id);}
 elseif($action==='offer.revise'){$id=marketplace_text($_POST,'id',36,true);$input=$_POST;$input['_file']=$_FILES['supporting_document']??[];supply_offer_revise($user,$id,$input);$destination='request-offer?id='.rawurlencode($id);}
 elseif($action==='offer.message'){$id=marketplace_text($_POST,'id',36,true);supply_offer_message($user,$id,marketplace_text($_POST,'message',5000,true));$destination='request-offer?id='.rawurlencode($id);}
 elseif($action==='offer.not-select'){$id=marketplace_text($_POST,'id',36,true);supply_offer_not_select($user,$id);$destination='request-offer?id='.rawurlencode($id);}
 elseif($action==='offer.withdraw'){$id=marketplace_text($_POST,'id',36,true);supply_offer_withdraw($user,$id);$destination='request-offer?id='.rawurlencode($id);}
 elseif($action==='offer.award'){$id=marketplace_text($_POST,'id',36,true);$transaction=request_award_offer($user,$id,marketplace_text($_POST,'transaction_type',10,true));$destination='marketplace-transaction?id='.rawurlencode($transaction);}
 else throw new DomainException('Unknown request action.',422);$pdo->commit();flash('success','Request workspace updated.');redirect($destination);
}catch(Throwable $error){if(isset($pdo)&&$pdo->inTransaction())$pdo->rollBack();flash('error',$error instanceof DomainException?$error->getMessage():'The request change could not be saved.');redirect($destination);}
