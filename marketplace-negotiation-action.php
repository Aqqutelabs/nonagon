<?php
declare(strict_types=1);
require __DIR__.'/app/marketplace-negotiation.php';
header('Cache-Control: no-store, private');
$destination='marketplace-manage?view=enquiries';
try{
    if($_SERVER['REQUEST_METHOD']!=='POST'){header('Allow: POST');throw new DomainException('Use POST for marketplace changes.',405);}
    $user=require_verified();verify_csrf();$action=(string)($_POST['action']??'');$pdo=db();$pdo->beginTransaction();
    if($action==='save.toggle'){
        $listingId=marketplace_text($_POST,'listing_id',36,true);$destination='marketplace-listing?id='.rawurlencode($listingId);$saved=marketplace_toggle_save($user,$listingId);flash('success',$saved?'Equipment saved.':'Equipment removed from saved items.');
    }elseif($action==='enquiry.create'){
        $listingId=marketplace_text($_POST,'listing_id',36,true);$destination='marketplace-deal?view=new-enquiry&listing='.rawurlencode($listingId);$id=marketplace_create_enquiry($user,$_POST);$destination='marketplace-deal?view=enquiry&id='.rawurlencode($id);flash('success','Enquiry sent to the equipment owner.');
    }elseif($action==='message.send'){
        $id=marketplace_text($_POST,'enquiry_id',36,true);$destination='marketplace-deal?view=enquiry&id='.rawurlencode($id);marketplace_send_message($user,$id,$_POST,$_FILES['attachment']??[]);flash('success','Message sent.');
    }elseif(in_array($action,['offer.submit','offer.draft'],true)){
        if(!marketplace_negotiation_manager($user))throw new DomainException('Your role cannot submit commercial offers.',403);$listingId=marketplace_text($_POST,'listing_id',36,true);$destination='marketplace-deal?view=new-offer&listing='.rawurlencode($listingId).'&type='.rawurlencode((string)($_POST['transaction_type']??'LEASE'));$id=marketplace_create_offer($user,$_POST,$action==='offer.submit');$destination='marketplace-deal?view=offer&id='.rawurlencode($id);flash('success',$action==='offer.submit'?'Offer submitted.':'Draft offer saved.');
    }elseif($action==='offer.counter'){
        if(!marketplace_negotiation_manager($user))throw new DomainException('Your role cannot negotiate commercial offers.',403);$id=marketplace_text($_POST,'offer_id',36,true);$destination='marketplace-deal?view=offer&id='.rawurlencode($id);$version=marketplace_counter_offer($user,$id,$_POST);flash('success','Counter offer version '.$version.' submitted.');
    }elseif($action==='offer.submit-draft'){
        if(!marketplace_negotiation_manager($user))throw new DomainException('Your role cannot submit commercial offers.',403);$id=marketplace_text($_POST,'offer_id',36,true);$destination='marketplace-deal?view=offer&id='.rawurlencode($id);marketplace_submit_draft($user,$id);flash('success','Draft offer submitted.');
    }elseif(in_array($action,['offer.accept','offer.reject'],true)){
        if(!marketplace_negotiation_manager($user))throw new DomainException('Your role cannot decide commercial offers.',403);$id=marketplace_text($_POST,'offer_id',36,true);$destination='marketplace-deal?view=offer&id='.rawurlencode($id);if($action==='offer.accept'&&empty($_POST['confirm_terms']))throw new DomainException('Confirm that you reviewed the exact offer version before accepting.',422);marketplace_offer_decision($user,$id,$action==='offer.accept'?'accept':'reject',(int)($_POST['confirmed_version']??0));flash('success',$action==='offer.accept'?'Offer accepted and its terms have been frozen.':'Offer rejected.');
    }elseif($action==='offer.withdraw'){
        $id=marketplace_text($_POST,'offer_id',36,true);$destination='marketplace-deal?view=offer&id='.rawurlencode($id);marketplace_withdraw_offer($user,$id);flash('success','Offer withdrawn.');
    }elseif($action==='enquiry.close'){
        $id=marketplace_text($_POST,'enquiry_id',36,true);$destination='marketplace-deal?view=enquiry&id='.rawurlencode($id);$enquiry=marketplace_enquiry($user,$id,true);db()->prepare("UPDATE marketplace_enquiries SET status='CLOSED' WHERE id=?")->execute([$id]);operation_audit($user,'marketplace.enquiry_closed','enquiry',$id,['status'=>$enquiry['status']],['status'=>'CLOSED']);flash('success','Enquiry closed.');
    }else throw new DomainException('Unknown marketplace negotiation action.',422);
    $pdo->commit();if(!isset($_SESSION['_flash']['success']))flash('success','Marketplace change saved.');redirect($destination);
}catch(Throwable $error){
    if(isset($pdo)&&$pdo->inTransaction())$pdo->rollBack();$message=$error instanceof DomainException?$error->getMessage():'The marketplace change could not be saved.';$code=$error instanceof DomainException?$error->getCode():500;http_response_code($code);error_log('Marketplace negotiation '.get_class($error).': '.$error->getMessage());flash('error',$message);redirect($destination);
}
