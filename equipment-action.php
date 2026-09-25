<?php
declare(strict_types=1);
require __DIR__.'/app/marketplace.php';
header('Cache-Control: no-store, private');$destination='equipment-settings';
try {
    if($_SERVER['REQUEST_METHOD']!=='POST'){header('Allow: POST');throw new DomainException('Use POST to save equipment changes.',405);}
    $user=current_user();if(!$user)throw new DomainException('Sign in to continue.',401);
    if(!isset($_POST['csrf'])||!is_string($_POST['csrf'])||!hash_equals($_SESSION['csrf']??'',$_POST['csrf']))throw new DomainException('Refresh the page before saving.',403);
    if(!operation_can_manage($user))throw new DomainException('Your account has read-only access.',403);
    $action=equipment_text($_POST,'action',40,true);$id=equipment_text($_POST,'id',36);
    $pdo=db();$pdo->beginTransaction();
    if($action==='archive'){
        equipment_archive($user,$id??'',equipment_text($_POST,'confirmation',100,true));
        $destination='equipment';
    }elseif(in_array($action,['details','depreciation','value','photo.upload','photo.primary','assembly.add','part.add','part.remove','templates.load'],true)){
        if(!$id)throw new DomainException('Equipment is required.',422);
        $before=operation_equipment($user,$id);$destination='equipment?id='.rawurlencode($id);
        rows('SELECT id FROM equipment WHERE id=? FOR UPDATE',[$id]);
        if($action==='details'){
            $name=equipment_text($_POST,'name',255,true);$code=equipment_text($_POST,'asset_code',100,true);
            $pdo->prepare('UPDATE equipment SET name=?,asset_code=? WHERE id=?')->execute([$name,$code,$id]);
            equipment_apply_metadata($user,$id,$_POST);
            marketplace_apply_operational_status($user,$id);
            operation_audit($user,'equipment.details','equipment',$id,$before,operation_equipment($user,$id));
        }elseif($action==='depreciation')equipment_save_depreciation($user,$id,$_POST);
        elseif($action==='value')equipment_add_value($user,$id,$_POST);
        elseif($action==='photo.upload')equipment_upload_photo($user,$id,$_FILES['photo']??[],$_POST);
        elseif($action==='photo.primary')equipment_primary_photo($user,$id,equipment_text($_POST,'photo_id',36));
        elseif($action==='assembly.add')equipment_add_assembly($user,$id,$_POST);
        elseif($action==='part.add')equipment_add_part($user,$id,$_POST);
        elseif($action==='templates.load'){$count=equipment_load_templates($user,$id);operation_audit($user,'templates.load','equipment',$id,null,['loaded'=>$count]);}
        else {
            $item=rows('SELECT i.* FROM equipment_assembly_items i JOIN equipment_assemblies a ON a.id=i.equipment_assembly_id WHERE a.equipment_id=? AND i.id=? FOR UPDATE',[$id,equipment_text($_POST,'item_id',36,true)])[0]??null;
            if(!$item)throw new DomainException('Installed part not found.',404);
            if($item['removed_at'])throw new DomainException('This part has already been removed.',409);
            $pdo->prepare("UPDATE equipment_assembly_items SET removed_at=UTC_TIMESTAMP(),status='REMOVED' WHERE id=?")->execute([$item['id']]);
            operation_audit($user,'assembly.part.remove','equipment',$id,$item,['item_id'=>$item['id'],'status'=>'REMOVED']);
        }
    }elseif($action==='catalog.create')catalog_create($user,equipment_text($_POST,'kind',20,true),$_POST);
    elseif($action==='catalog.edit'){
        $kind=equipment_text($_POST,'kind',20,true);$existing=catalog_record($user,$kind,$id,true);if(!$existing)throw new DomainException('Directory record required.',422);
        $values=['name'=>equipment_text($_POST,'name',150,true)];
        if($kind==='category')$values['industry']=equipment_text($_POST,'industry',100,true);
        if($kind==='subcategory')$values['artwork_url']=equipment_artwork_url(equipment_text($_POST,'artwork_url',500));
        if(in_array($kind,['part','template'],true))$values['description']=equipment_text($_POST,'description',20000);
        $table=catalog_table($kind);$sets=implode(',',array_map(fn($k)=>$k.'=?',array_keys($values)));
        $pdo->prepare("UPDATE {$table} SET {$sets} WHERE id=? AND owner_id=?")->execute([...array_values($values),$id,$user['owner_id']]);
        operation_audit($user,'catalog.edit',$kind,$id,$existing,$values);
    }elseif($action==='template.item')assembly_template_add_item($user,$_POST);
    elseif($action==='location.save')equipment_save_location($user,$_POST);
    elseif($action==='company.save'){
        if($user['role']!=='OWNER_ADMIN')throw new DomainException('Only the company owner can edit its profile.',403);
        $email=equipment_text($_POST,'email',255);if($email&&!filter_var($email,FILTER_VALIDATE_EMAIL))throw new DomainException('Enter a valid company contact email or leave it blank.',422);
        $values=['name'=>equipment_text($_POST,'name',255),'email'=>$email,'phone'=>equipment_text($_POST,'phone',30),'address'=>equipment_text($_POST,'address',500)];
        $before=rows('SELECT name,email,phone,address FROM owners WHERE id=?',[$user['owner_id']])[0];
        $pdo->prepare('UPDATE owners SET name=?,email=?,phone=?,address=? WHERE id=?')->execute([...array_values($values),$user['owner_id']]);operation_audit($user,'company.profile','owner',$user['owner_id'],$before,$values);
    }else throw new DomainException('Unknown equipment action.',422);
    $pdo->commit();flash('success','Saved. Changes are recorded in the audit log.');redirect($destination);
}catch(Throwable $error){
    if(isset($pdo)&&$pdo->inTransaction())$pdo->rollBack();
    $message=$error instanceof DomainException?$error->getMessage():'The change could not be saved. Check the entered values.';
    $code=$error instanceof DomainException?$error->getCode():500;
    if($error instanceof PDOException&&($error->errorInfo[1]??null)===1062){$message='A record with that name or asset ID already exists.';$code=409;}
    http_response_code($code);
    if(!$error instanceof DomainException)error_log('Equipment save: '.$error->getMessage());
    if(!isset($user)||!$user){echo e($message);exit;}
    $active='equipment';$pageTitle='Change not saved';require __DIR__.'/includes/operations-header.php';
    echo '<div class="notice error" role="alert">'.e($message).'</div><a href="'.e($destination).'">Return to equipment</a>';require __DIR__.'/includes/operations-footer.php';
}
