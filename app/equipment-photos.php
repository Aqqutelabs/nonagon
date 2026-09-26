<?php
declare(strict_types=1);
function equipment_upload_photo(array $user,string $equipmentId,array $file,array $input):string
{
    operation_equipment($user,$equipmentId);
    if(($file['error']??UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_OK||!is_uploaded_file($file['tmp_name']??''))throw new DomainException('Choose a photo to upload.',422);
    if(($file['size']??0)>2*1024*1024)throw new DomainException('Photos must be 2 MB or smaller.',422);
    $bytes=file_get_contents($file['tmp_name']);$info=@getimagesizefromstring($bytes);
    if(!$info||!in_array($info['mime'],['image/jpeg','image/png','image/webp'],true)||$info[0]*$info[1]>40000000)throw new DomainException('Upload a valid JPEG, PNG, or WebP photo up to 40 megapixels.',422);
    $id=uuid();$url='equipment-photo?id='.$id;$primary=isset($input['make_primary'])||!rows('SELECT id FROM equipment_photos WHERE equipment_id=? LIMIT 1',[$equipmentId]);
    rows('SELECT id FROM equipment WHERE id=? FOR UPDATE',[$equipmentId]);
    if($primary)db()->prepare('UPDATE equipment_photos SET is_primary=0 WHERE equipment_id=?')->execute([$equipmentId]);
    db()->prepare('INSERT INTO equipment_photos(id,equipment_id,url,caption,is_primary,mime_type,image_data) VALUES(?,?,?,?,?,?,?)')->execute([$id,$equipmentId,$url,equipment_text($input,'caption',255),$primary?1:0,$info['mime'],$bytes]);
    if($primary)db()->prepare('UPDATE equipment SET photo_primary_url=? WHERE id=?')->execute([$url,$equipmentId]);
    operation_audit($user,'photo.upload','equipment',$equipmentId,null,['photo_id'=>$id,'is_primary'=>$primary]);return $id;
}

function equipment_photo_files(array $files):array
{
    if(!isset($files['name'])||!is_array($files['name']))return isset($files['name'])?[$files]:[];
    $result=[];foreach($files['name'] as $index=>$name)$result[]=['name'=>$name,'type'=>$files['type'][$index]??'','tmp_name'=>$files['tmp_name'][$index]??'','error'=>$files['error'][$index]??UPLOAD_ERR_NO_FILE,'size'=>$files['size'][$index]??0];return $result;
}

function equipment_upload_photos(array $user,string $equipmentId,array $files,array $input):array
{
    $items=array_values(array_filter(equipment_photo_files($files),fn($file)=>($file['error']??UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_NO_FILE));
    if(!$items)throw new DomainException('Choose at least one photo to upload.',422);
    if(count($items)>20)throw new DomainException('Upload no more than 20 photos at once.',422);
    $primaryIndex=filter_var($input['primary_photo_index']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>0]]);$ids=[];
    foreach($items as $index=>$file){$photoInput=$input;$photoInput['caption']=pathinfo((string)($file['name']??''),PATHINFO_FILENAME)?:'Equipment photo';if($primaryIndex===$index)$photoInput['make_primary']='1';else unset($photoInput['make_primary']);$ids[]=equipment_upload_photo($user,$equipmentId,$file,$photoInput);}
    return $ids;
}
function equipment_primary_photo(array $user,string $equipmentId,?string $photoId):void
{
    operation_equipment($user,$equipmentId);rows('SELECT id FROM equipment WHERE id=? FOR UPDATE',[$equipmentId]);
    if(!$photoId&&rows('SELECT id FROM equipment_photos WHERE equipment_id=? LIMIT 1',[$equipmentId]))throw new DomainException('Choose one of the equipment photos as primary.',409);
    $photo=$photoId?rows('SELECT id,url FROM equipment_photos WHERE id=? AND equipment_id=?',[$photoId,$equipmentId])[0]??null:null;
    if($photoId&&!$photo)throw new DomainException('Photo not found on this equipment.',404);
    db()->prepare('UPDATE equipment_photos SET is_primary=0 WHERE equipment_id=?')->execute([$equipmentId]);
    if($photo)db()->prepare('UPDATE equipment_photos SET is_primary=1 WHERE id=?')->execute([$photoId]);
    db()->prepare('UPDATE equipment SET photo_primary_url=? WHERE id=?')->execute([$photo['url']??null,$equipmentId]);
    operation_audit($user,'photo.primary','equipment',$equipmentId,null,['photo_id'=>$photoId]);
}

function equipment_delete_photo(array $user,string $equipmentId,string $photoId):void
{
    operation_equipment($user,$equipmentId);rows('SELECT id FROM equipment WHERE id=? FOR UPDATE',[$equipmentId]);$photos=rows('SELECT id,url,is_primary FROM equipment_photos WHERE equipment_id=? ORDER BY created_at,id FOR UPDATE',[$equipmentId]);$photo=null;foreach($photos as $candidate)if($candidate['id']===$photoId)$photo=$candidate;
    if(!$photo)throw new DomainException('Photo not found on this equipment.',404);if(count($photos)<=1)throw new DomainException('Equipment must keep at least one photo.',409);
    $linked=rows('SELECT DISTINCT listing_id FROM marketplace_listing_media WHERE equipment_photo_id=?',[$photoId]);db()->prepare('DELETE FROM marketplace_listing_media WHERE equipment_photo_id=?')->execute([$photoId]);db()->prepare('DELETE FROM equipment_photos WHERE id=? AND equipment_id=?')->execute([$photoId,$equipmentId]);
    $next=null;if($photo['is_primary']){$next=rows('SELECT id,url FROM equipment_photos WHERE equipment_id=? ORDER BY created_at,id LIMIT 1',[$equipmentId])[0];db()->prepare('UPDATE equipment_photos SET is_primary=1 WHERE id=?')->execute([$next['id']]);db()->prepare('UPDATE equipment SET photo_primary_url=? WHERE id=?')->execute([$next['url'],$equipmentId]);}
    foreach($linked as $listing){if(!rows("SELECT id FROM marketplace_listing_media WHERE listing_id=? AND media_type='IMAGE' AND is_primary=1",[$listing['listing_id']]))db()->prepare("UPDATE marketplace_listing_media SET is_primary=1 WHERE listing_id=? AND media_type='IMAGE' ORDER BY sequence,created_at LIMIT 1")->execute([$listing['listing_id']]);}
    operation_audit($user,'photo.delete','equipment',$equipmentId,['photo_id'=>$photoId,'is_primary'=>(bool)$photo['is_primary']],['photo_id'=>$photoId,'deleted'=>true,'new_primary_id'=>$next['id']??null]);
}
