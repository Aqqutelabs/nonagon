<?php
declare(strict_types=1);
require __DIR__.'/app/marketplace.php';
header('Cache-Control: public, max-age=300');header('X-Content-Type-Options: nosniff');
try{
    $id=marketplace_text($_GET,'id',36);$listingId=marketplace_text($_GET,'listing_id',36);$user=current_user();
    if($id)$media=rows('SELECT m.*,l.organization_id,l.listing_status,l.visibility listing_visibility FROM marketplace_listing_media m JOIN marketplace_listings l ON l.id=m.listing_id JOIN equipment e ON e.id=l.asset_id WHERE m.id=? AND e.archived_at IS NULL',[$id])[0]??null;
    else{$media=rows("SELECT m.*,l.organization_id,l.listing_status,l.visibility listing_visibility FROM marketplace_listing_media m JOIN marketplace_listings l ON l.id=m.listing_id JOIN equipment e ON e.id=l.asset_id WHERE l.id=? AND e.archived_at IS NULL AND m.media_type='IMAGE' AND m.visibility='PUBLIC' ORDER BY m.is_primary DESC,m.sequence,m.created_at LIMIT 1",[$listingId])[0]??null;if(!$media)$media=rows("SELECT NULL id,l.id listing_id,p.id equipment_photo_id,'IMAGE' media_type,p.caption title,NULL external_url,p.mime_type,NULL file_data,'PUBLIC' visibility,p.is_primary,l.organization_id,l.listing_status,l.visibility listing_visibility FROM marketplace_listings l JOIN equipment e ON e.id=l.asset_id JOIN equipment_photos p ON p.equipment_id=e.id WHERE l.id=? AND e.archived_at IS NULL ORDER BY p.is_primary DESC,p.created_at,p.id LIMIT 1",[$listingId])[0]??null;if(!$media)$media=rows("SELECT NULL id,l.id listing_id,NULL equipment_photo_id,'IMAGE' media_type,e.name title,COALESCE(NULLIF(e.photo_primary_url,''),NULLIF(sc.artwork_url,''),'assets/images/equipment-placeholder.svg') external_url,NULL mime_type,NULL file_data,'PUBLIC' visibility,0 is_primary,l.organization_id,l.listing_status,l.visibility listing_visibility FROM marketplace_listings l JOIN equipment e ON e.id=l.asset_id LEFT JOIN equipment_subcategories sc ON sc.id=e.subcategory_id WHERE l.id=? AND e.archived_at IS NULL",[$listingId])[0]??null;}
    if(!$media)throw new DomainException('Media not found.',404);
    $owner=$user&&$user['owner_id']===$media['organization_id'];
    if(!$owner&&($media['listing_status']!=='ACTIVE'||$media['listing_visibility']!=='PUBLIC'||$media['visibility']!=='PUBLIC'))throw new DomainException('Media not found.',404);
    if($media['external_url']){header('Location: '.$media['external_url']);exit;}
    if($media['equipment_photo_id']){$photo=rows('SELECT mime_type,image_data FROM equipment_photos WHERE id=?',[$media['equipment_photo_id']])[0]??null;if(!$photo)throw new DomainException('Media not found.',404);$mime=$photo['mime_type'];$data=$photo['image_data'];}
    else{$mime=$media['mime_type'];$data=$media['file_data'];}
    if(!$data){header('Location: assets/images/equipment-placeholder.svg');exit;}
    header('Content-Type: '.$mime);header('Content-Length: '.strlen($data));echo $data;
}catch(Throwable $error){
    if(!$id){header('Location: assets/images/equipment-placeholder.svg');exit;}
    http_response_code($error instanceof DomainException?$error->getCode():503);header('Content-Type: text/plain');echo 'Media unavailable.';
}
