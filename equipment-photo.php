<?php
declare(strict_types=1);
require __DIR__.'/app/equipment-catalog.php';
header('Cache-Control: private, no-store');header('X-Content-Type-Options: nosniff');
try {
    $user=current_user();if(!$user||!email_access_allowed($user))throw new DomainException('Access denied.',403);
    $id=equipment_text($_GET,'id',36);
    if($id){$photo=rows('SELECT equipment_id FROM equipment_photos WHERE id=?',[$id])[0]??null;if(!$photo)throw new DomainException('Photo not found.',404);operation_equipment($user,$photo['equipment_id']);}
    else {
        $equipment=operation_equipment($user,equipment_text($_GET,'equipment_id',36,true));
        $id=rows('SELECT id FROM equipment_photos WHERE equipment_id=? AND is_primary=1',[$equipment['id']])[0]['id']??null;
        if(!$id){$category=catalog_record($user,'subcategory',$equipment['subcategory_id']);$url=$category['artwork_url']??'assets/images/equipment-placeholder.svg';header('Location: '.equipment_artwork_url($url));exit;}
    }
    $photo=rows('SELECT mime_type,image_data FROM equipment_photos WHERE id=?',[$id])[0];
    header('Content-Type: '.$photo['mime_type']);header('Content-Length: '.strlen($photo['image_data']));echo $photo['image_data'];
}catch(Throwable $error){http_response_code($error instanceof DomainException?$error->getCode():503);header('Content-Type: text/plain');echo 'Photo unavailable.';}
