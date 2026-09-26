<?php
declare(strict_types=1);require __DIR__.'/app/marketplace-transaction.php';header('Cache-Control: no-store, private');$destination='marketplace-manage?view=leases';
try{if($_SERVER['REQUEST_METHOD']!=='POST'){header('Allow: POST');throw new DomainException('Use POST for transaction changes.',405);}$user=require_verified();verify_csrf();$action=(string)($_POST['action']??'');$id=marketplace_text($_POST,'transaction_id',36,true);$destination='marketplace-transaction?id='.rawurlencode($id).'&view='.rawurlencode((string)($_POST['return_view']??'overview'));$pdo=db();$pdo->beginTransaction();
 if($action==='agreement.generate')marketplace_generate_agreement($user,$id);
 elseif($action==='agreement.send')marketplace_send_agreement($user,$id);
 elseif($action==='agreement.sign'){if(empty($_POST['confirm_signature']))throw new DomainException('Confirm that the signature is authorized and binding.',422);marketplace_sign_agreement($user,$id,marketplace_text($_POST,'signer_name',255,true));}
 elseif($action==='payment.confirm')marketplace_confirm_payment($user,$id,marketplace_text($_POST,'payment_id',36,true),marketplace_text($_POST,'provider',80,true),marketplace_text($_POST,'provider_reference',255,true));
 elseif($action==='escrow.fund')marketplace_fund_escrow($user,$id,marketplace_text($_POST,'provider',80,true),marketplace_text($_POST,'provider_reference',255,true));
 elseif($action==='insurance.save')marketplace_save_insurance($user,$id,$_POST);
 elseif($action==='gate.exception')marketplace_add_gate_exception($user,$id,marketplace_text($_POST,'gate_key',80,true),marketplace_text($_POST,'reason',5000,true));
 elseif($action==='handover.complete')marketplace_complete_handover($user,$id,$_POST,$_FILES['evidence']??[]);
 elseif($action==='lease.activate')marketplace_activate_lease($user,$id);
 elseif($action==='lease.return-start')marketplace_begin_return($user,$id);
 elseif($action==='transaction.complete')marketplace_complete_transaction($user,$id);
 else throw new DomainException('Unknown transaction action.',422);
 $pdo->commit();flash('success','Transaction workspace updated.');redirect($destination);
}catch(Throwable $error){if(isset($pdo)&&$pdo->inTransaction())$pdo->rollBack();flash('error',$error instanceof DomainException?$error->getMessage():'The transaction change could not be saved.');redirect($destination);}
