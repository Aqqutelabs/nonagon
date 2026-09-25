<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
require dirname(__DIR__).'/app/equipment-catalog.php';
$pdo=db();$checks=0;
function expect_equipment(bool $condition,string $message):void{global $checks;if(!$condition)throw new RuntimeException($message);$checks++;}
function equipment_denied(callable $call,int $code):void{try{$call();}catch(DomainException $e){expect_equipment($e->getCode()===$code,'Wrong denial status: '.$e->getMessage());return;}throw new RuntimeException('Expected validation/access denial');}
function catalog_test_user():array{
    $owner=uuid();$id=uuid();db()->prepare('INSERT INTO owners(id) VALUES(?)')->execute([$owner]);
    db()->prepare("INSERT INTO users(id,owner_id,full_name,email,phone,password_hash,is_email_verified) VALUES(?,?,'Catalog fixture',?,'0','unused',1)")->execute([$id,$owner,$id.'@example.invalid']);
    return rows('SELECT * FROM users WHERE id=?',[$id])[0];
}
$pdo->beginTransaction();
try{
    $user=catalog_test_user();$foreign=catalog_test_user();$readOnly=[...$user,'role'=>'OPERATOR'];
    expect_equipment((int)rows('SELECT COUNT(*) AS n FROM equipment_subcategories WHERE owner_id IS NULL')[0]['n']===80,'80 base subcategories');
    expect_equipment(count(rows('SELECT DISTINCT industry FROM equipment_categories WHERE owner_id IS NULL'))===4,'Four industries');
    $unit=equipment_default_unit($user);expect_equipment(equipment_default_unit($user)===$unit,'Default hierarchy is idempotent');
    $loc=rows('SELECT sp.name AS block_name,s.name AS site_name,p.name AS plant_name,u.name AS unit_name FROM units u JOIN plants p ON p.id=u.plant_id JOIN sites s ON s.id=p.site_id JOIN spaces sp ON sp.id=s.space_id WHERE u.id=?',[$unit])[0];
    expect_equipment(count(array_filter($loc,fn($name)=>$name==='Default'))===4,'Every automatic level is named Default');
    equipment_denied(fn()=>equipment_default_unit($readOnly),403);
    $category=catalog_create($user,'category',['name'=>'Test category','industry'=>'Testing']);
    $sub=catalog_create($user,'subcategory',['name'=>'Test subcategory','category_id'=>$category]);
    $type=catalog_create($user,'type',['name'=>'Test type','subcategory_id'=>$sub]);
    $brand=catalog_create($user,'brand',['name'=>'Test brand']);$model=catalog_create($user,'model',['name'=>'Test model','brand_id'=>$brand]);
    equipment_denied(fn()=>catalog_record($foreign,'category',$category),403);
    equipment_denied(fn()=>catalog_create($readOnly,'category',['name'=>'No','industry'=>'No']),403);
    $base=rows('SELECT id FROM equipment_categories WHERE owner_id IS NULL LIMIT 1')[0]['id'];equipment_denied(fn()=>catalog_record($user,'category',$base,true),403);
    equipment_denied(fn()=>equipment_artwork_url('javascript:alert(1)'),422);
    $part=catalog_create($user,'part',['name'=>'Filter','manufacturer_part_number'=>'FILTER-1','unit_of_measure'=>'PCS','is_consumable'=>1]);
    expect_equipment(catalog_record($user,'part',$part)['is_global']==0,'New part is private and unverified');
    $template=catalog_create($user,'template',['name'=>'Engine','equipment_type_id'=>$type]);
    $nested=catalog_create($user,'template',['name'=>'Filter housing','equipment_type_id'=>$type]);
    assembly_template_add_item($user,['assembly_template_id'=>$template,'subassembly_id'=>$nested,'quantity'=>'2','is_required'=>1]);
    $templateItem=assembly_template_add_item($user,['assembly_template_id'=>$nested,'part_id'=>$part,'quantity'=>'3','is_required'=>1]);
    equipment_denied(fn()=>assembly_template_add_item($user,['assembly_template_id'=>$nested,'subassembly_id'=>$template,'quantity'=>'1']),422);
    equipment_denied(fn()=>assembly_template_add_item($user,['assembly_template_id'=>$nested,'part_id'=>$part,'is_custom_slot'=>1,'quantity'=>'1']),422);
    $asset=uuid();$pdo->prepare('INSERT INTO equipment(id,owner_id,unit_id,asset_code,name) VALUES(?,?,?,?,?)')->execute([$asset,$user['owner_id'],$unit,'CAT-001','Catalog equipment']);
    equipment_apply_metadata($user,$asset,['category_id'=>$category,'subcategory_id'=>$sub,'type_id'=>$type,'brand_id'=>$brand,'model_id'=>$model,'emd'=>'SERIAL-TEST','manufacture_year'=>'2020']);
    $equipment=operation_equipment($user,$asset);expect_equipment($equipment['aid']==='CAT-001'&&$equipment['emd']==='SERIAL-TEST','AID/EMD preserve import compatibility');
    expect_equipment((int)rows('SELECT COUNT(*) AS n FROM equipment_assemblies WHERE equipment_id=?',[$asset])[0]['n']===2,'Nested assemblies automatically instantiated');
    $installed=rows('SELECT i.* FROM equipment_assembly_items i JOIN equipment_assemblies a ON a.id=i.equipment_assembly_id WHERE a.equipment_id=?',[$asset])[0];expect_equipment((float)$installed['quantity']===6.0,'Nested assembly quantities multiply correctly');
    $pdo->prepare('UPDATE assembly_template_items SET quantity=8 WHERE id=?')->execute([$templateItem]);expect_equipment((float)rows('SELECT quantity FROM equipment_assembly_items WHERE id=?',[$installed['id']])[0]['quantity']===6.0,'Template edits never rewrite installed parts');
    expect_equipment(equipment_load_templates($user,$asset)===0,'Loading templates is idempotent');
    $custom=equipment_add_assembly($user,$asset,['name'=>'Site add-on']);$customPart=equipment_add_part($user,$asset,['equipment_assembly_id'=>$custom,'custom_name'=>'Local bracket','quantity'=>'1.5']);
    expect_equipment((int)rows('SELECT is_custom_addon FROM equipment_assembly_items WHERE id=?',[$customPart])[0]['is_custom_addon']===1,'Asset-specific custom part');
    equipment_denied(fn()=>equipment_add_part($foreign,$asset,['equipment_assembly_id'=>$custom,'custom_name'=>'No','quantity'=>'1']),404);
    equipment_denied(fn()=>equipment_apply_metadata($user,$asset,['category_id'=>$base,'subcategory_id'=>$sub]),422);
    $profile=['acquisition_cost'=>'1000.00','salvage_value'=>'100.00','useful_life_years'=>'3','start_date'=>'2020-01-01'];
    expect_equipment(equipment_book_value($profile,'2019-12-31')['book_value']==='1000.00','No depreciation before start');
    expect_equipment(equipment_book_value($profile,'2020-12-31')['book_value']==='700.00','Straight-line annual depreciation');
    expect_equipment(equipment_book_value($profile,'2040-01-01')['book_value']==='100.00','Book value floors at salvage');
    expect_equipment(equipment_book_value([...$profile,'acquisition_cost'=>'0.00','salvage_value'=>'0.00'],'2021-01-01')['change_percent']===null,'Zero-cost change avoids division by zero');
    equipment_denied(fn()=>equipment_save_depreciation($user,$asset,['acquisition_cost'=>'100','salvage_value'=>'101','useful_life_years'=>'5','acquisition_date'=>'2020-01-01']),422);
    equipment_save_depreciation($user,$asset,['acquisition_cost'=>'1000.00','salvage_value'=>'100.00','useful_life_years'=>'3','acquisition_date'=>'2020-01-01','currency'=>'NGN','is_active'=>1]);
    expect_equipment(count(rows('SELECT id FROM equipment_value_snapshot WHERE equipment_id=?',[$asset]))===2,'Purchase and formula snapshots created');
    equipment_add_value($user,$asset,['value_type'=>'MARKET_ESTIMATE_AI','source_type'=>'AI_AGENT','source_detail'=>'Externally supplied test model observation','as_of_date'=>gmdate('Y-m-d'),'amount'=>'600']);
    $valuation=equipment_refresh_values($asset);expect_equipment($valuation['market']['source_type']==='AI_AGENT'&&$valuation['calculation']['book_value']==='100.00','AI market source does not replace formula book value');
    equipment_denied(fn()=>equipment_add_value($user,$asset,['value_type'=>'BOOK_ESTIMATE_AI','source_type'=>'USER','source_detail'=>'Wrong attribution','as_of_date'=>gmdate('Y-m-d'),'amount'=>'500']),422);
    equipment_denied(fn()=>equipment_save_depreciation($user,$asset,['acquisition_cost'=>'1000','salvage_value'=>'100','useful_life_years'=>'3','acquisition_date'=>'2020-01-01','currency'=>'USD']),409);
    $inline=equipment_inline_catalog($user,['industry'=>'Testing','new_category'=>'Inline category','new_subcategory'=>'Inline subcategory','new_type'=>'Inline type']);
    expect_equipment($inline['type_id']!==null&&!isset($inline['new_type']),'Inline import classification resolves only once');
    $private=equipment_inline_catalog($user,['new_category'=>'Combobox private category','new_subcategory'=>'Combobox private subcategory','new_type'=>'Combobox private type','owner_id'=>$foreign['owner_id'],'is_global'=>1]);
    $sameCompany=[...$user,'id'=>uuid()];
    foreach(['category','subcategory','type'] as $kind){
        $entry=catalog_record($user,$kind,$private[$kind.'_id']);
        expect_equipment($entry['owner_id']===$user['owner_id'],'Inline '.$kind.' always belongs to authenticated company');
        expect_equipment(in_array($entry['id'],array_column(equipment_catalog($sameCompany)[$kind],'id'),true),'Company colleagues see '.$kind);
        expect_equipment(!in_array($entry['id'],array_column(equipment_catalog($foreign)[$kind],'id'),true),'Other companies cannot list '.$kind);
        equipment_denied(fn()=>catalog_record($foreign,$kind,$entry['id']),403);
    }
    expect_equipment(catalog_record($user,'category',$private['category_id'])['industry']==='General','Inline category defaults industry without an extra field');
    expect_equipment(catalog_record($user,'subcategory',$private['subcategory_id'])['category_id']===$private['category_id'],'Inline subcategory uses newly created parent');
    expect_equipment(catalog_record($user,'type',$private['type_id'])['subcategory_id']===$private['subcategory_id'],'Inline type uses newly created parent');
    $second=uuid();$pdo->prepare('INSERT INTO equipment(id,owner_id,unit_id,asset_code,name) VALUES(?,?,?,?,?)')->execute([$second,$user['owner_id'],$unit,'CAT-002','Second import']);equipment_apply_metadata($user,$second,$inline);
    $third=uuid();$pdo->prepare('INSERT INTO equipment(id,owner_id,unit_id,asset_code,name) VALUES(?,?,?,?,?)')->execute([$third,$user['owner_id'],$unit,'CAT-003','Third import']);equipment_apply_metadata($user,$third,$inline);
    expect_equipment(operation_equipment($user,$second)['category_id']===operation_equipment($user,$third)['category_id'],'Bulk metadata shares the newly created category');
    equipment_denied(fn()=>equipment_primary_photo($foreign,$asset,null),404);
    equipment_primary_photo($user,$asset,null);expect_equipment(operation_equipment($user,$asset)['photo_primary_url']===null,'Clearing primary restores artwork inheritance');
    $pdo->prepare('DELETE FROM equipment WHERE id=?')->execute([$third]);
    expect_equipment((int)rows('SELECT COUNT(*) AS n FROM audit_logs WHERE owner_id=?',[$user['owner_id']])[0]['n']>10,'Equipment mutations are audited');
    $archiveAsset=uuid();$pdo->prepare('INSERT INTO equipment(id,owner_id,unit_id,asset_code,name) VALUES(?,?,?,?,?)')->execute([$archiveAsset,$user['owner_id'],$unit,'ARCHIVE-001','Archive fixture']);
    equipment_denied(fn()=>equipment_archive($readOnly,$archiveAsset,'ARCHIVE-001'),403);
    equipment_denied(fn()=>equipment_archive($foreign,$archiveAsset,'ARCHIVE-001'),404);
    equipment_denied(fn()=>equipment_archive($user,$archiveAsset,'wrong'),422);
    $archiveWork=uuid();$pdo->prepare("INSERT INTO maintenance_records(id,equipment_id,title,due_at) VALUES(?,?,'Archive blocker',UTC_TIMESTAMP())")->execute([$archiveWork,$archiveAsset]);
    equipment_denied(fn()=>equipment_archive($user,$archiveAsset,'ARCHIVE-001'),409);
    $pdo->prepare("UPDATE maintenance_records SET status='COMPLETED' WHERE id=?")->execute([$archiveWork]);
    $archiveAlert=uuid();$pdo->prepare("INSERT INTO alerts(id,equipment_id,kind,severity,title) VALUES(?,?,'SAFETY','HIGH','Archive blocker')")->execute([$archiveAlert,$archiveAsset]);
    equipment_denied(fn()=>equipment_archive($user,$archiveAsset,'ARCHIVE-001'),409);
    $pdo->prepare("UPDATE alerts SET status='RESOLVED' WHERE id=?")->execute([$archiveAlert]);
    $beforeCount=(int)rows('SELECT total_assets FROM equipment_kpi_view WHERE owner_id=?',[$user['owner_id']])[0]['total_assets'];
    equipment_archive($user,$archiveAsset,'ARCHIVE-001');
    equipment_denied(fn()=>operation_equipment($user,$archiveAsset),404);
    expect_equipment(!rows('SELECT id FROM equipment_list_view WHERE id=?',[$archiveAsset]),'Archived equipment excluded from register');
    expect_equipment((int)rows('SELECT total_assets FROM equipment_kpi_view WHERE owner_id=?',[$user['owner_id']])[0]['total_assets']===$beforeCount-1,'Archive updates KPI totals');
    expect_equipment(count(rows('SELECT id FROM maintenance_records WHERE equipment_id=?',[$archiveAsset]))===1,'Archive preserves maintenance history');
    expect_equipment(count(rows('SELECT id FROM equipment_status_history WHERE equipment_id=?',[$archiveAsset]))===1,'Archive preserves status history');
    expect_equipment(rows('SELECT archived_by FROM equipment WHERE id=?',[$archiveAsset])[0]['archived_by']===$user['id'],'Archive records actor');
    expect_equipment(count(rows("SELECT id FROM audit_logs WHERE entity_id=? AND action='equipment.archive'",[$archiveAsset]))===1,'Archive is audited');
    equipment_denied(fn()=>equipment_archive($user,$archiveAsset,'ARCHIVE-001'),404);
    try{$pdo->prepare('INSERT INTO equipment(id,owner_id,unit_id,asset_code,name) VALUES(?,?,?,?,?)')->execute([uuid(),$user['owner_id'],$unit,'ARCHIVE-001','Duplicate AID']);throw new RuntimeException('Duplicate AID accepted');}catch(PDOException $e){expect_equipment(($e->errorInfo[1]??0)===1062,'AID remains unique including archived equipment');}
    $foreignUnit=equipment_default_unit($foreign);
    $pdo->prepare('INSERT INTO equipment(id,owner_id,unit_id,asset_code,name) VALUES(?,?,?,?,?)')->execute([uuid(),$foreign['owner_id'],$foreignUnit,'ARCHIVE-001','Other company same AID']);
    expect_equipment(true,'Same AID is allowed in another company');
    echo "PASS: {$checks} equipment catalog, location, depreciation, template, and isolation checks.\n";
}catch(Throwable $e){fwrite(STDERR,'FAIL: '.$e->getMessage()."\n");$failed=true;}
finally{if($pdo->inTransaction())$pdo->rollBack();echo "All catalog fixtures rolled back.\n";}
exit(!empty($failed)?1:0);
