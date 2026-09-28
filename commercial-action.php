<?php
declare(strict_types=1);
require __DIR__.'/app/commercial.php';
$destination='commercial';
try{
    if($_SERVER['REQUEST_METHOD']!=='POST')throw new DomainException('Use POST for Commercial changes.',405);
    $user=require_verified();verify_csrf();$action=(string)($_POST['action']??'');
    if($action==='profile.save'){
        commercial_profile_save($user,$_POST);$destination='commercial?view=settings';$message='Commercial profile saved.';
    }elseif($action==='bank.save'){
        commercial_bank_save($user,$_POST);$destination='commercial?view=settings';$message='Bank account added.';
    }elseif($action==='customer.save'){
        $id=commercial_customer_save($user,$_POST);$destination='commercial?view=customers&id='.rawurlencode($id);$message='Customer saved.';
    }elseif($action==='brand.save'){
        $id=commercial_brand_save($user,$_POST,$_FILES);$destination='commercial?view=branding&id='.rawurlencode($id);$message='Commercial brand saved.';
    }elseif($action==='document.save'){
        $type=(string)($_POST['document_type']??'');$id=commercial_document_save($user,$_POST,$type);commercial_document_brand_set($user,$id,commercial_text($_POST,'brand_id',36));commercial_document_signatory_set($user,$id,$_POST,$_FILES);commercial_document_adjustments_set($user,$id,$_POST);commercial_tax_sync($user,$id);
        $destination='commercial?view=document&id='.rawurlencode($id);$message=ucfirst(strtolower($type)).' draft saved.';
    }elseif($action==='document.issue'){
        $id=commercial_text($_POST,'id',36,true);commercial_issue($user,$id);$destination='commercial?view=document&id='.rawurlencode($id);$message='Document issued and frozen.';
    }elseif($action==='quote.convert'){
        $quote=commercial_document($user,commercial_text($_POST,'id',36,true));$id=commercial_convert_quote($user,$quote['id']);commercial_document_brand_set($user,$id,(string)($quote['brand_id']??''));commercial_tax_sync($user,$id);
        $destination='commercial?view=document&id='.rawurlencode($id);$message='Invoice draft created from quotation.';
    }else throw new DomainException('Unknown Commercial action.',422);
    flash('success',$message);redirect($destination);
}catch(Throwable $error){flash('error',$error instanceof DomainException?$error->getMessage():'The Commercial change could not be saved.');redirect($destination);}
