<?php
declare(strict_types=1);
function equipment_upload_photo(array $user,string $equipmentId,array $file,array $input):string
{
    operation_equipment($user,$equipmentId);
    if(($file['error']??UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_OK||!is_uploaded_file($file['tmp_name']??''))throw new DomainException('Choose a photo to upload.',422);
    if(($file['size']??0)>2*1024*1024)throw new DomainException('Photos must be 2 MB or smaller.',422);
    $bytes=file_get_contents($file['tmp_name']);$info=@getimagesizefromstring($bytes);
    if(!$info||!in_array($info['mime'],['image/jpeg','image/png','image/webp'],true)||$info[0]*$info[1]>40000000)throw new DomainException('Upload a valid JPEG, PNG, or WebP photo up to 40 megapixels.',422);
    $id=uuid();$url='equipment-photo?id='.$id;$primary=isset($input['make_primary']);
    rows('SELECT id FROM equipment WHERE id=? FOR UPDATE',[$equipmentId]);
    if($primary)db()->prepare('UPDATE equipment_photos SET is_primary=0 WHERE equipment_id=?')->execute([$equipmentId]);
    db()->prepare('INSERT INTO equipment_photos(id,equipment_id,url,caption,is_primary,mime_type,image_data) VALUES(?,?,?,?,?,?,?)')->execute([$id,$equipmentId,$url,equipment_text($input,'caption',255),$primary?1:0,$info['mime'],$bytes]);
    if($primary)db()->prepare('UPDATE equipment SET photo_primary_url=? WHERE id=?')->execute([$url,$equipmentId]);
    operation_audit($user,'photo.upload','equipment',$equipmentId,null,['photo_id'=>$id,'is_primary'=>$primary]);return $id;
}
function equipment_primary_photo(array $user,string $equipmentId,?string $photoId):void
{
    operation_equipment($user,$equipmentId);rows('SELECT id FROM equipment WHERE id=? FOR UPDATE',[$equipmentId]);
    $photo=$photoId?rows('SELECT id,url FROM equipment_photos WHERE id=? AND equipment_id=?',[$photoId,$equipmentId])[0]??null:null;
    if($photoId&&!$photo)throw new DomainException('Photo not found on this equipment.',404);
    db()->prepare('UPDATE equipment_photos SET is_primary=0 WHERE equipment_id=?')->execute([$equipmentId]);
    if($photo)db()->prepare('UPDATE equipment_photos SET is_primary=1 WHERE id=?')->execute([$photoId]);
    db()->prepare('UPDATE equipment SET photo_primary_url=? WHERE id=?')->execute([$photo['url']??null,$equipmentId]);
    operation_audit($user,'photo.primary','equipment',$equipmentId,null,['photo_id'=>$photoId]);
}
