<?php
declare(strict_types=1);
function equipment_save_location(array $user,array $input):string
{
    if($user['role']!=='OWNER_ADMIN')throw new DomainException('Only the company owner can change the location hierarchy.',403);
    $kind=equipment_text($input,'kind',20,true);
    $map=['block'=>['spaces','owner_id'],'site'=>['sites','space_id'],'plant'=>['plants','site_id'],'unit'=>['units','plant_id']];
    if(!isset($map[$kind]))throw new DomainException('Unknown location level.',422);
    [$table,$parentColumn]=$map[$kind];$id=equipment_text($input,'location_id',36);$parent=equipment_text($input,'parent_id',36)??$user['owner_id'];
    $paths=[
        'block'=>'SELECT sp.id,sp.owner_id AS parent_id FROM spaces sp WHERE sp.owner_id=?',
        'site'=>'SELECT s.id,s.space_id AS parent_id FROM sites s JOIN spaces sp ON sp.id=s.space_id WHERE sp.owner_id=?',
        'plant'=>'SELECT p.id,p.site_id AS parent_id FROM plants p JOIN sites s ON s.id=p.site_id JOIN spaces sp ON sp.id=s.space_id WHERE sp.owner_id=?',
        'unit'=>'SELECT u.id,u.plant_id AS parent_id FROM units u JOIN plants p ON p.id=u.plant_id JOIN sites s ON s.id=p.site_id JOIN spaces sp ON sp.id=s.space_id WHERE sp.owner_id=?',
    ];
    if($id){$existing=array_values(array_filter(rows($paths[$kind],[$user['owner_id']]),fn($r)=>$r['id']===$id))[0]??null;if(!$existing)throw new DomainException('Location not found.',404);$parent=$existing['parent_id'];}
    elseif($kind!=='block'){$parentKind=['site'=>'block','plant'=>'site','unit'=>'plant'][$kind];if(!array_filter(rows($paths[$parentKind],[$user['owner_id']]),fn($r)=>$r['id']===$parent))throw new DomainException('Parent location is outside this company.',403);}
    else $parent=$user['owner_id'];
    $values=['name'=>equipment_text($input,'name',255,(bool)$id)??'Default'];
    foreach(['block'=>['description'=>20000],'site'=>['state'=>100,'lga'=>100,'address'=>500],'plant'=>['description'=>20000],'unit'=>['description'=>20000,'department_code'=>100]][$kind] as $field=>$max)$values[$field]=equipment_text($input,$field,$max);
    if(in_array($kind,['site','plant'],true))foreach(['gps_lat'=>90,'gps_lng'=>180] as $field=>$max){$value=equipment_text($input,$field,20);if($value!==null&&(!is_numeric($value)||abs((float)$value)>$max))throw new DomainException('Invalid GPS coordinate.',422);$values[$field]=$value;}
    if($id){$sets=implode(',',array_map(fn($field)=>$field.'=?',array_keys($values)));db()->prepare("UPDATE {$table} SET {$sets} WHERE id=?")->execute([...array_values($values),$id]);}
    else{$id=uuid();$values=['id'=>$id,$parentColumn=>$parent,...$values];$markers=implode(',',array_fill(0,count($values),'?'));db()->prepare('INSERT INTO '.$table.'('.implode(',',array_keys($values)).") VALUES({$markers})")->execute(array_values($values));}
    operation_audit($user,'location.save',$kind,$id,null,$values);return $id;
}
