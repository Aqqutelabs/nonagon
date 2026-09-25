<?php
declare(strict_types=1);
function equipment_quantity(array $input):string
{
    $value=equipment_text($input,'quantity',16,true);
    if(!preg_match('/^\d{1,9}(\.\d{1,3})?$/D',$value)||(float)$value<=0)throw new DomainException('Quantity must be positive with at most three decimal places.',422);
    return $value;
}
function assembly_contains(string $root,string $target,array $path=[]):bool
{
    if($root===$target)return true;
    if(in_array($root,$path,true)||count($path)>16)throw new DomainException('Assembly nesting is cyclic or too deep.',422);
    foreach(rows('SELECT subassembly_id FROM assembly_template_items WHERE assembly_template_id=? AND subassembly_id IS NOT NULL',[$root]) as $item)
        if(assembly_contains($item['subassembly_id'],$target,[...$path,$root]))return true;
    return false;
}
function assembly_template_add_item(array $user,array $input):string
{
    $template=catalog_record($user,'template',equipment_text($input,'assembly_template_id',36,true),true);
    // Serialize graph changes for this tenant before checking cycles.
    rows('SELECT id FROM owners WHERE id=? FOR UPDATE',[$user['owner_id']]);
    $part=equipment_text($input,'part_id',36);$sub=equipment_text($input,'subassembly_id',36);$slot=isset($input['is_custom_slot']);
    if((int)(bool)$part+(int)(bool)$sub+(int)$slot!==1)throw new DomainException('Choose exactly one part, subassembly, or custom slot.',422);
    catalog_record($user,'part',$part);
    if($sub){
        $child=catalog_record($user,'template',$sub);
        if($child['equipment_type_id']!==$template['equipment_type_id'])throw new DomainException('Nested assemblies must use the same equipment type.',422);
        if(assembly_contains($sub,$template['id']))throw new DomainException('This would create a circular assembly.',422);
    }
    $id=uuid();$qty=equipment_quantity($input);
    db()->prepare('INSERT INTO assembly_template_items(id,assembly_template_id,part_id,subassembly_id,quantity,sequence,is_required,is_custom_slot,notes) VALUES(?,?,?,?,?,?,?,?,?)')->execute([$id,$template['id'],$part,$sub,$qty,(int)($input['sequence']??0),isset($input['is_required'])?1:0,$slot?1:0,equipment_text($input,'notes',500)]);
    operation_audit($user,'template.item.add','template',$template['id'],null,['item_id'=>$id,'part_id'=>$part,'subassembly_id'=>$sub,'quantity'=>$qty]);
    return $id;
}
function equipment_load_templates(array $user,string $equipmentId):int
{
    $equipment=operation_equipment($user,$equipmentId);
    if(!$equipment['type_id'])return 0;
    rows('SELECT id FROM equipment WHERE id=? FOR UPDATE',[$equipmentId]);
    $templates=rows('SELECT t.* FROM assembly_templates t WHERE (t.owner_id=? OR (t.owner_id IS NULL AND t.is_global=1 AND t.is_verified=1)) AND t.equipment_type_id=? AND (t.brand_id IS NULL OR t.brand_id=?) AND (t.model_id IS NULL OR t.model_id=?) AND NOT EXISTS(SELECT 1 FROM assembly_template_items ti JOIN assembly_templates parent ON parent.id=ti.assembly_template_id WHERE ti.subassembly_id=t.id AND (parent.owner_id=? OR (parent.owner_id IS NULL AND parent.is_global=1 AND parent.is_verified=1)) AND parent.equipment_type_id=? AND (parent.brand_id IS NULL OR parent.brand_id=?) AND (parent.model_id IS NULL OR parent.model_id=?)) ORDER BY t.name,t.id',[$user['owner_id'],$equipment['type_id'],$equipment['brand_id'],$equipment['model_id'],$user['owner_id'],$equipment['type_id'],$equipment['brand_id'],$equipment['model_id']]);
    $loaded=0;$budget=500;
    foreach($templates as $template){
        if(rows('SELECT id FROM equipment_assemblies WHERE equipment_id=? AND template_id=? LIMIT 1',[$equipmentId,$template['id']]))continue;
        equipment_clone_assembly($user,$equipment,$template['id'],null,1,[],$budget);$loaded++;
    }
    return $loaded;
}
function equipment_clone_assembly(array $user,array $equipment,string $templateId,?string $parent,float $multiplier,array $path,int &$budget):void
{
    if(--$budget<0||count($path)>=16||in_array($templateId,$path,true))throw new DomainException('Assembly template exceeds safe size or nesting limits.',422);
    $template=catalog_record($user,'template',$templateId);
    if($template['equipment_type_id']!==$equipment['type_id']||($template['brand_id']&&$template['brand_id']!==$equipment['brand_id'])||($template['model_id']&&$template['model_id']!==$equipment['model_id']))throw new DomainException('Assembly template does not match this equipment.',422);
    $id=uuid();db()->prepare('INSERT INTO equipment_assemblies(id,equipment_id,parent_assembly_id,name,description,template_id) VALUES(?,?,?,?,?,?)')->execute([$id,$equipment['id'],$parent,$template['name'],$template['description'],$templateId]);
    foreach(rows('SELECT * FROM assembly_template_items WHERE assembly_template_id=? ORDER BY sequence,id',[$templateId]) as $item){
        if(--$budget<0)throw new DomainException('Assembly template exceeds 500 nodes.',422);
        $quantity=(float)$item['quantity']*$multiplier;
        if($quantity>999999999)throw new DomainException('Nested quantities exceed the supported range.',422);
        if($item['subassembly_id']){equipment_clone_assembly($user,$equipment,$item['subassembly_id'],$id,$quantity,[...$path,$templateId],$budget);continue;}
        catalog_record($user,'part',$item['part_id']);
        db()->prepare('INSERT INTO equipment_assembly_items(id,equipment_assembly_id,part_id,custom_name,quantity,sequence,template_item_id,is_custom_addon,installed_at,status,notes) VALUES(?,?,?,?,?,?,?,?,?,?,?)')->execute([uuid(),$id,$item['part_id'],$item['is_custom_slot']?'Custom add-on slot':null,$quantity,$item['sequence'],$item['id'],$item['is_custom_slot'],$item['is_custom_slot']?null:gmdate('Y-m-d H:i:s'),$item['is_custom_slot']?'UNFILLED':'INSTALLED',$item['notes']]);
    }
    operation_audit($user,'assembly.instantiate','equipment',$equipment['id'],null,['assembly_id'=>$id,'template_id'=>$templateId]);
}
function equipment_add_assembly(array $user,string $equipmentId,array $input):string
{
    operation_equipment($user,$equipmentId);$id=uuid();
    db()->prepare('INSERT INTO equipment_assemblies(id,equipment_id,name,description,sequence,is_custom_addon) VALUES(?,?,?,?,?,1)')->execute([$id,$equipmentId,equipment_text($input,'name',255,true),equipment_text($input,'description',20000),(int)($input['sequence']??0)]);
    operation_audit($user,'assembly.custom.add','equipment',$equipmentId,null,['assembly_id'=>$id]);return $id;
}
function equipment_add_part(array $user,string $equipmentId,array $input):string
{
    operation_equipment($user,$equipmentId);
    $assembly=rows('SELECT id FROM equipment_assemblies WHERE id=? AND equipment_id=?',[equipment_text($input,'equipment_assembly_id',36,true),$equipmentId])[0]??null;
    if(!$assembly)throw new DomainException('Assembly not found on this equipment.',404);
    $part=equipment_text($input,'part_id',36);$custom=equipment_text($input,'custom_name',255);
    if(!$part&&!$custom)throw new DomainException('Choose a canonical part or enter a custom part name.',422);
    catalog_record($user,'part',$part);$id=uuid();
    db()->prepare("INSERT INTO equipment_assembly_items(id,equipment_assembly_id,part_id,custom_name,quantity,sequence,is_custom_addon,installed_at,status) VALUES(?,?,?,?,?,?,1,UTC_TIMESTAMP(),'INSTALLED')")->execute([$id,$assembly['id'],$part,$custom,equipment_quantity($input),(int)($input['sequence']??0)]);
    operation_audit($user,'assembly.part.install','equipment',$equipmentId,null,['item_id'=>$id,'assembly_id'=>$assembly['id']]);return $id;
}
