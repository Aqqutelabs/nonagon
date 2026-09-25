<?php
declare(strict_types=1);
require_once __DIR__.'/operations.php';

function equipment_text(array $input,string $key,int $max=255,bool $required=false):?string
{
    if(isset($input[$key])&&!is_scalar($input[$key]))throw new DomainException('Invalid '.$key.'.',422);
    $value=trim((string)($input[$key]??''));
    if(($required&&$value==='')||strlen($value)>$max)throw new DomainException('Check '.$key.' (maximum '.$max.' characters).',422);
    return $value===''?null:$value;
}
function catalog_table(string $kind):string
{
    return ['category'=>'equipment_categories','subcategory'=>'equipment_subcategories','type'=>'equipment_types','brand'=>'equipment_brands','model'=>'equipment_models','status'=>'equipment_statuses','part'=>'part_master','template'=>'assembly_templates'][$kind]??throw new DomainException('Unknown directory.',422);
}
function catalog_record(array $user,string $kind,?string $id,bool $write=false):?array
{
    if(!$id)return null;
    $table=catalog_table($kind);
    $scope=$write?'owner_id=?':'(owner_id=? OR owner_id IS NULL)';
    if(!$write&&in_array($kind,['part','template'],true))$scope='(owner_id=? OR (owner_id IS NULL AND is_global=1 AND is_verified=1))';
    $row=rows("SELECT * FROM {$table} WHERE id=? AND {$scope}",[$id,$user['owner_id']])[0]??null;
    if(!$row)throw new DomainException('That '.$kind.' is not available to this company.',403);
    return $row;
}
function equipment_catalog(array $user):array
{
    $result=[];
    foreach(['category','subcategory','type','brand','model','status','part','template'] as $kind){
        $table=catalog_table($kind);$scope='owner_id=? OR owner_id IS NULL';
        if(in_array($kind,['part','template'],true))$scope='owner_id=? OR (owner_id IS NULL AND is_global=1 AND is_verified=1)';
        $result[$kind]=rows("SELECT * FROM {$table} WHERE {$scope} ORDER BY name,id",[$user['owner_id']]);
    }
    return $result;
}
function equipment_seed_catalog():void
{
    foreach(json_decode(file_get_contents(APP_ROOT.'/database/category-seed.json'),true,512,JSON_THROW_ON_ERROR) as $entry){
        $category=rows('SELECT id FROM equipment_categories WHERE owner_id IS NULL AND industry=? AND name=?',[$entry['industry'],$entry['category']])[0]['id']??uuid();
        db()->prepare('INSERT IGNORE INTO equipment_categories(id,industry,name) VALUES(?,?,?)')->execute([$category,$entry['industry'],$entry['category']]);
        db()->prepare('INSERT IGNORE INTO equipment_subcategories(id,category_id,name,artwork_url) VALUES(?,?,?,?)')->execute([uuid(),$category,$entry['subcategory'],'assets/images/equipment-placeholder.svg']);
    }
}
function equipment_seed_statuses(string $owner):void
{
    foreach(['OPERATIONAL'=>'Operational','MAINTENANCE'=>'Maintenance','DOWN'=>'Critical / Down'] as $state=>$name)
        db()->prepare('INSERT IGNORE INTO equipment_statuses(id,owner_id,name,operational_state,is_default,severity) VALUES(?,?,?,?,1,?)')->execute([uuid(),$owner,$name,$state,['OPERATIONAL'=>0,'MAINTENANCE'=>1,'DOWN'=>2][$state]]);
    db()->prepare('UPDATE equipment e JOIN equipment_statuses s ON s.owner_id=e.owner_id AND s.operational_state=e.status AND s.is_default=1 SET e.status_id=s.id WHERE e.owner_id=? AND e.status_id IS NULL')->execute([$owner]);
}
function equipment_default_unit(array $user):string
{
    if($user['role']!=='OWNER_ADMIN')throw new DomainException('Select a unit assigned to your account.',403);
    // Lock the tenant row to serialize first-use location creation.
    rows('SELECT id FROM owners WHERE id=? FOR UPDATE',[$user['owner_id']]);
    $parent=$user['owner_id'];
    foreach(['spaces'=>'owner_id','sites'=>'space_id','plants'=>'site_id','units'=>'plant_id'] as $table=>$column){
        $row=rows("SELECT id FROM {$table} WHERE {$column}=? AND is_default=1 ORDER BY created_at,id LIMIT 1",[$parent])[0]??null;
        if($row){$parent=$row['id'];continue;}
        $id=uuid();db()->prepare("INSERT INTO {$table}(id,{$column},name,is_default) VALUES(?,?,'Default',1)")->execute([$id,$parent]);$parent=$id;
    }
    return $parent;
}
function equipment_unit(array $user,?string $unit):string
{
    if(!$unit)return equipment_default_unit($user);
    if(!array_filter(operation_locations($user),fn($l)=>$l['unit_id']===$unit))throw new DomainException('Choose an accessible unit.',403);
    return $unit;
}
function catalog_create(array $user,string $kind,array $input):string
{
    if(!operation_can_manage($user))throw new DomainException('Directory changes require operational management access.',403);
    $values=['id'=>uuid(),'owner_id'=>$user['owner_id'],'name'=>equipment_text($input,'name',150,true)];
    if($kind==='category')$values['industry']=equipment_text($input,'industry',100,true);
    foreach(['subcategory'=>['category','category_id'],'type'=>['subcategory','subcategory_id'],'model'=>['brand','brand_id']] as $child=>$parent){
        if($kind!==$child)continue;
        $id=equipment_text($input,$parent[1],36,true);catalog_record($user,$parent[0],$id);$values[$parent[1]]=$id;
    }
    if($kind==='subcategory')$values['artwork_url']=equipment_artwork_url(equipment_text($input,'artwork_url',500));
    if($kind==='status'){
        $state=equipment_text($input,'operational_state',20,true);
        if(!in_array($state,['OPERATIONAL','MAINTENANCE','DOWN'],true))throw new DomainException('Choose an operational state.',422);
        $values['operational_state']=$state;
        $values['severity']=['OPERATIONAL'=>0,'MAINTENANCE'=>1,'DOWN'=>2][$state];
    }
    if(in_array($kind,['part','template'],true)){
        $values['description']=equipment_text($input,'description',20000);
        $values['created_by']=$user['owner_id'];
        $values['brand_id']=equipment_text($input,'brand_id',36);$values['model_id']=equipment_text($input,'model_id',36);
        catalog_record($user,'brand',$values['brand_id']);$model=catalog_record($user,'model',$values['model_id']);
        if($model&&$model['brand_id']!==$values['brand_id'])throw new DomainException('Model must belong to the selected brand.',422);
        if($kind==='template'){$values['equipment_type_id']=equipment_text($input,'equipment_type_id',36,true);catalog_record($user,'type',$values['equipment_type_id']);}
        else {
            foreach(['manufacturer_part_number'=>150,'oem_part_number'=>150,'function_name'=>100] as $key=>$max)$values[$key]=equipment_text($input,$key,$max);
            $values['unit_of_measure']=equipment_text($input,'unit_of_measure',20)??'PCS';
            $values['is_serviceable']=isset($input['is_serviceable'])?1:0;$values['is_consumable']=isset($input['is_consumable'])?1:0;
        }
    }
    $table=catalog_table($kind);$columns=implode(',',array_keys($values));$markers=implode(',',array_fill(0,count($values),'?'));
    db()->prepare("INSERT INTO {$table}({$columns}) VALUES({$markers})")->execute(array_values($values));
    operation_audit($user,'catalog.create',$kind,$values['id'],null,$values);
    return $values['id'];
}
function equipment_artwork_url(?string $url):?string
{
    if(!$url)return null;
    if($url==='assets/images/equipment-placeholder.svg')return $url;
    if(!filter_var($url,FILTER_VALIDATE_URL)||strtolower((string)parse_url($url,PHP_URL_SCHEME))!=='https'||parse_url($url,PHP_URL_USER)||parse_url($url,PHP_URL_PASS))throw new DomainException('Artwork must use an HTTPS image URL.',422);
    return $url;
}
function equipment_inline_catalog(array $user,array $input):array
{
    $category=equipment_text($input,'category_id',36);$subcategory=equipment_text($input,'subcategory_id',36);$type=equipment_text($input,'type_id',36);
    if(equipment_text($input,'new_category',150))$category=catalog_create($user,'category',['name'=>$input['new_category'],'industry'=>$input['industry']??'General']);
    if(equipment_text($input,'new_subcategory',150))$subcategory=catalog_create($user,'subcategory',['name'=>$input['new_subcategory'],'category_id'=>$category]);
    if(equipment_text($input,'new_type',150))$type=catalog_create($user,'type',['name'=>$input['new_type'],'subcategory_id'=>$subcategory]);
    unset($input['new_category'],$input['new_subcategory'],$input['new_type']);
    return [...$input,'category_id'=>$category,'subcategory_id'=>$subcategory,'type_id'=>$type];
}
function equipment_apply_metadata(array $user,string $id,array $input,bool $clone=true):void
{
    operation_equipment($user,$id);$input=equipment_inline_catalog($user,$input);
    $category=$input['category_id'];$subcategory=$input['subcategory_id'];$type=$input['type_id'];
    $t=catalog_record($user,'type',$type);$s=catalog_record($user,'subcategory',$subcategory);
    if($t&&$t['subcategory_id']!==$subcategory)throw new DomainException('Equipment type must belong to its subcategory.',422);
    if($s&&$s['category_id']!==$category)throw new DomainException('Subcategory must belong to its category.',422);
    catalog_record($user,'category',$category);
    $brand=equipment_text($input,'brand_id',36);$model=equipment_text($input,'model_id',36);
    catalog_record($user,'brand',$brand);$m=catalog_record($user,'model',$model);
    if($m&&$m['brand_id']!==$brand)throw new DomainException('Model must belong to its brand.',422);
    $year=equipment_text($input,'manufacture_year',4);
    if($year&&(!ctype_digit($year)||(int)$year<1800||(int)$year>(int)gmdate('Y')+1))throw new DomainException('Enter a valid manufacture year.',422);
    equipment_seed_statuses($user['owner_id']);
    $current=rows('SELECT status,status_id,serial_no FROM equipment WHERE id=?',[$id])[0];
    $status=catalog_record($user,'status',equipment_text($input,'status_id',36))??catalog_record($user,'status',$current['status_id']);
    $serial=array_key_exists('emd',$input)?equipment_text($input,'emd',100):$current['serial_no'];
    if(($status['operational_state']??$current['status'])!==$current['status']&&!equipment_can_action($user,'change_status'))throw new DomainException('Status changes are disabled.',403);
    db()->prepare('UPDATE equipment SET category_id=?,subcategory_id=?,type_id=?,brand_id=?,model_id=?,manufacture_year=?,serial_no=?,short_description=?,long_description=?,status_id=?,status=? WHERE id=?')->execute([$category,$subcategory,$type,$brand,$model,$year,$serial,equipment_text($input,'short_description',500),equipment_text($input,'long_description',20000),$status['id']??null,$status['operational_state']??$current['status'],$id]);
    if(($status['operational_state']??$current['status'])!==$current['status']){
        if($status['operational_state']==='DOWN')db()->prepare("INSERT INTO alerts(id,equipment_id,kind,severity,title) VALUES(?,?,'BREAKDOWN','CRITICAL','Equipment reported down')")->execute([uuid(),$id]);
        else db()->prepare("UPDATE alerts SET status='RESOLVED' WHERE equipment_id=? AND kind='BREAKDOWN' AND status<>'RESOLVED'")->execute([$id]);
    }
    if($clone&&$type)equipment_load_templates($user,$id);
}

require_once __DIR__.'/equipment-assemblies.php';
require_once __DIR__.'/equipment-values.php';
require_once __DIR__.'/equipment-photos.php';
require_once __DIR__.'/equipment-locations.php';

/** Archive instead of deleting operational and financial history. Caller owns transaction. */
function equipment_archive(array $user,string $id,string $confirmation):void
{
    if(!operation_can_manage($user))throw new DomainException('Your account has read-only access.',403);
    if(!db()->inTransaction())throw new LogicException('Equipment archive requires a transaction.');
    rows('SELECT id FROM equipment WHERE id=? AND owner_id=? FOR UPDATE',[$id,$user['owner_id']]);
    $before=operation_equipment($user,$id);
    if(!hash_equals($before['asset_code'],trim($confirmation)))throw new DomainException('Enter the exact asset ID to archive this equipment.',422);
    if(rows("SELECT id FROM maintenance_records WHERE equipment_id=? AND status IN ('SCHEDULED','IN_PROGRESS') LIMIT 1",[$id]))throw new DomainException('Complete or cancel open maintenance before archiving this equipment.',409);
    if(rows("SELECT id FROM alerts WHERE equipment_id=? AND status<>'RESOLVED' LIMIT 1",[$id]))throw new DomainException('Resolve active risk alerts before archiving this equipment.',409);
    db()->prepare('UPDATE equipment SET archived_at=UTC_TIMESTAMP(),archived_by=?,operator_id=NULL WHERE id=?')->execute([$user['id'],$id]);
    operation_audit($user,'equipment.archive','equipment',$id,$before,['archived_at'=>gmdate('Y-m-d H:i:s'),'archived_by'=>$user['id']]);
}
