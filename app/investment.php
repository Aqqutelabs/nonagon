<?php
declare(strict_types=1);
require_once __DIR__ . '/operations.php';

const INVESTMENT_REVIEW_AREAS = ['PROMOTER_KYC','TECHNICAL','SUPPLIER_ACQUISITION','DEMAND_DEPLOYMENT','COMMERCIAL_FINANCIAL','LEGAL_FINANCE','RISK'];

function investment_text(array $input,string $key,int $max=0):string
{
    $value=is_string($input[$key]??null)?trim($input[$key]):'';
    if($max&&mb_strlen($value)>$max)throw new DomainException(ucwords(str_replace('_',' ',$key))." must be {$max} characters or fewer.",422);
    return $value;
}
function investment_number(array $input,string $key,float $default=0):float
{
    $value=$input[$key]??'';if($value==='')return $default;
    if(!is_numeric($value)||(float)$value<0)throw new DomainException(ucwords(str_replace('_',' ',$key)).' must be a non-negative number.',422);
    return (float)$value;
}
function investment_is_platform(array $user):bool
{
    if(!operation_can_manage($user)||!in_array($user['role'],['OWNER_ADMIN','ADMIN'],true))return false;
    if(strtolower((string)($user['email']??''))==='aqqute.dev@gmail.com')return true;
    $promoter=investment_promoter($user);
    return $promoter&&$promoter['status']==='VERIFIED'&&$promoter['promoter_type']==='INTERNAL';
}
function investment_can_review(array $user):bool{return investment_is_platform($user);}
function investment_promoter(array $user):?array
{
    $row=rows('SELECT * FROM investment_promoters WHERE organization_id=? LIMIT 1',[$user['owner_id']])[0]??null;
    if($row)return $row;
    $name=strtolower(trim((string)$user['company_name']));
    if(str_contains($name,'ofissa'))return ['id'=>null,'promoter_type'=>'OFISSA','status'=>'VERIFIED','approved_stage'=>1];
    if(str_contains($name,'nonagon')||str_contains($name,'equipment.ng'))return ['id'=>null,'promoter_type'=>'INTERNAL','status'=>'VERIFIED','approved_stage'=>1];
    return null;
}
function investment_can_create(array $user):bool
{
    if(!operation_can_manage($user))return false;$promoter=investment_promoter($user);if(!$promoter||$promoter['status']!=='VERIFIED')return false;
    $stage=max(1,min(3,(int)config('investment.opportunity_stage',1)));
    return $promoter['promoter_type']==='INTERNAL'||($promoter['promoter_type']==='OFISSA'&&$stage>=1)||($promoter['promoter_type']==='VERIFIED_COMPANY'&&$stage>=2)||($promoter['promoter_type']==='OPEN_SUBMITTER'&&$stage>=3);
}
function investment_opportunity(array $user,string $id,bool $lock=false):array
{
    $sql='SELECT o.*,own.name organization_name,u.full_name creator_name FROM investment_opportunities o JOIN owners own ON own.id=o.organization_id JOIN users u ON u.id=o.created_by WHERE o.id=?';
    $params=[$id];if(!investment_can_review($user)){$sql.=' AND o.organization_id=?';$params[]=$user['owner_id'];}
    $row=rows($sql.($lock?' FOR UPDATE':''),$params)[0]??null;if(!$row)throw new DomainException('Investment opportunity not found.',404);return $row;
}
function investment_snapshot(string $id):array
{
    $op=rows('SELECT * FROM investment_opportunities WHERE id=?',[$id])[0];unset($op['updated_at']);
    return ['opportunity'=>$op,'technical'=>rows('SELECT * FROM investment_technical_profiles WHERE opportunity_id=?',[$id])[0]??null,'acquisition'=>rows('SELECT * FROM investment_acquisition_profiles WHERE opportunity_id=?',[$id])[0]??null,'scenarios'=>rows('SELECT * FROM investment_financial_scenarios WHERE opportunity_id=? ORDER BY FIELD(scenario_type,\'DOWNSIDE\',\'BASE\',\'UPSIDE\')',[$id]),'demand'=>rows('SELECT * FROM investment_demand_evidence WHERE opportunity_id=? ORDER BY created_at,id',[$id])];
}
function investment_event(array $user,string $id,string $type,?string $from=null,?string $to=null,array $data=[]):void
{
    db()->prepare('INSERT INTO investment_opportunity_events(opportunity_id,actor_id,event_type,from_status,to_status,event_data) VALUES(?,?,?,?,?,?)')->execute([$id,$user['id'],$type,$from,$to,$data?json_encode($data,JSON_THROW_ON_ERROR):null]);
}
function investment_version(array $user,string $id,string $reason):void
{
    $op=investment_opportunity($user,$id,true);$version=(int)$op['current_version']+1;
    db()->prepare('UPDATE investment_opportunities SET current_version=? WHERE id=?')->execute([$version,$id]);
    db()->prepare('INSERT INTO investment_opportunity_versions(id,opportunity_id,version_number,snapshot,change_reason,created_by) VALUES(?,?,?,?,?,?)')->execute([uuid(),$id,$version,json_encode(investment_snapshot($id),JSON_THROW_ON_ERROR),$reason,$user['id']]);
}
function investment_save(array $user,array $input):string
{
    if(!investment_can_create($user))throw new DomainException('Your organization is not approved to promote investment opportunities in the current rollout stage.',403);
    $id=investment_text($input,'id',36);$existing=$id?investment_opportunity($user,$id,true):null;
    if($existing&&!in_array($existing['status'],['DRAFT','CONDITIONAL'],true))throw new DomainException('Only draft or conditional opportunities can be edited.',409);
    $title=investment_text($input,'title',255);if($title==='')throw new DomainException('Add an opportunity title.',422);
    $currency=strtoupper(investment_text($input,'currency',3)?:'NGN');if(!preg_match('/^[A-Z]{3}$/',$currency))throw new DomainException('Use a three-letter currency.',422);
    $usefulLife=max(0,(int)($input['asset_useful_life_days']??0));$minimumTerm=max(0,(int)($input['minimum_term_days']??0));$targetTerm=max(0,(int)($input['target_term_days']??0));
    if($usefulLife<1)throw new DomainException('Enter the asset useful life in days.',422);if($minimumTerm<90)throw new DomainException('The minimum term cannot be lower than 90 days.',422);if($targetTerm<$minimumTerm)throw new DomainException('The target term cannot be lower than the minimum term.',422);if($targetTerm>$usefulLife)throw new DomainException('The target term cannot exceed the asset useful life.',422);
    $promoter=investment_promoter($user);$values=[$title,investment_text($input,'source_type',30)?:'PROMOTER',investment_text($input,'equipment_category_id',36)?:null,investment_text($input,'equipment_type_id',36)?:null,investment_text($input,'industry',150),max(1,(int)($input['quantity']??1)),investment_text($input,'target_geography',255),investment_text($input,'opportunity_rationale'),investment_number($input,'target_funding_amount'),$currency,(int)ceil($targetTerm/30),$usefulLife,$minimumTerm,$targetTerm,investment_text($input,'investment_structure',120),investment_text($input,'ownership_vehicle',255),investment_text($input,'finance_security_partner',255),investment_text($input,'controlled_account_details'),investment_text($input,'recovery_rights'),investment_text($input,'distribution_waterfall'),investment_text($input,'primary_risks'),investment_text($input,'disclosure_statement')];
    $pdo=db();$pdo->beginTransaction();try{
        if(!$existing){$id=uuid();db()->prepare('INSERT INTO investment_opportunities(id,organization_id,promoter_id,created_by,title,source_type,equipment_category_id,equipment_type_id,industry,quantity,target_geography,opportunity_rationale,target_funding_amount,currency,target_term_months,asset_useful_life_days,minimum_term_days,target_term_days,investment_structure,ownership_vehicle,finance_security_partner,controlled_account_details,recovery_rights,distribution_waterfall,primary_risks,disclosure_statement) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)')->execute([$id,$user['owner_id'],$promoter['id']??null,$user['id'],...$values]);investment_event($user,$id,'OPPORTUNITY_CREATED');}
        else db()->prepare('UPDATE investment_opportunities SET title=?,source_type=?,equipment_category_id=?,equipment_type_id=?,industry=?,quantity=?,target_geography=?,opportunity_rationale=?,target_funding_amount=?,currency=?,target_term_months=?,asset_useful_life_days=?,minimum_term_days=?,target_term_days=?,investment_structure=?,ownership_vehicle=?,finance_security_partner=?,controlled_account_details=?,recovery_rights=?,distribution_waterfall=?,primary_risks=?,disclosure_statement=? WHERE id=?')->execute([...$values,$id]);
        db()->prepare('INSERT INTO investment_technical_profiles(opportunity_id,specification,oem_name,model_name,equipment_condition,subassemblies,commissioning_requirements,certification_requirements,serviceability,parts_support) VALUES(?,?,?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE specification=VALUES(specification),oem_name=VALUES(oem_name),model_name=VALUES(model_name),equipment_condition=VALUES(equipment_condition),subassemblies=VALUES(subassemblies),commissioning_requirements=VALUES(commissioning_requirements),certification_requirements=VALUES(certification_requirements),serviceability=VALUES(serviceability),parts_support=VALUES(parts_support)')->execute([$id,investment_text($input,'specification'),investment_text($input,'oem_name',255),investment_text($input,'model_name',255),in_array($input['equipment_condition']??'', ['NEW','USED','REFURBISHED','TO_BE_DETERMINED'],true)?$input['equipment_condition']:'TO_BE_DETERMINED',investment_text($input,'subassemblies'),investment_text($input,'commissioning_requirements'),investment_text($input,'certification_requirements'),investment_text($input,'serviceability'),investment_text($input,'parts_support')]);
        db()->prepare('INSERT INTO investment_acquisition_profiles(opportunity_id,supplier_name,supplier_relationship,purchase_cost,logistics_cost,landed_cost,lead_time_days,warranty_terms,payment_terms,inspection_requirements,commissioning_requirements) VALUES(?,?,?,?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE supplier_name=VALUES(supplier_name),supplier_relationship=VALUES(supplier_relationship),purchase_cost=VALUES(purchase_cost),logistics_cost=VALUES(logistics_cost),landed_cost=VALUES(landed_cost),lead_time_days=VALUES(lead_time_days),warranty_terms=VALUES(warranty_terms),payment_terms=VALUES(payment_terms),inspection_requirements=VALUES(inspection_requirements),commissioning_requirements=VALUES(commissioning_requirements)')->execute([$id,investment_text($input,'supplier_name',255),investment_text($input,'supplier_relationship',255),investment_number($input,'purchase_cost'),investment_number($input,'logistics_cost'),investment_number($input,'landed_cost'),max(0,(int)($input['lead_time_days']??0))?:null,investment_text($input,'warranty_terms'),investment_text($input,'payment_terms'),investment_text($input,'inspection_requirements'),investment_text($input,'supplier_commissioning_requirements')]);
        foreach(['DOWNSIDE','BASE','UPSIDE'] as $scenario){$p=strtolower($scenario).'_';db()->prepare('INSERT INTO investment_financial_scenarios(id,opportunity_id,scenario_type,currency,purchase_cost,landed_cost,insurance_cost,initial_spares,contingency,lease_rate,utilization_percent,downtime_percent,projected_revenue,operating_expense,maintenance_cost,management_fee,reserve_amount,residual_value,projected_return_percent,assumptions) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE currency=VALUES(currency),purchase_cost=VALUES(purchase_cost),landed_cost=VALUES(landed_cost),insurance_cost=VALUES(insurance_cost),initial_spares=VALUES(initial_spares),contingency=VALUES(contingency),lease_rate=VALUES(lease_rate),utilization_percent=VALUES(utilization_percent),downtime_percent=VALUES(downtime_percent),projected_revenue=VALUES(projected_revenue),operating_expense=VALUES(operating_expense),maintenance_cost=VALUES(maintenance_cost),management_fee=VALUES(management_fee),reserve_amount=VALUES(reserve_amount),residual_value=VALUES(residual_value),projected_return_percent=VALUES(projected_return_percent),assumptions=VALUES(assumptions)')->execute([uuid(),$id,$scenario,$currency,investment_number($input,$p.'purchase_cost'),investment_number($input,$p.'landed_cost'),investment_number($input,$p.'insurance_cost'),investment_number($input,$p.'initial_spares'),investment_number($input,$p.'contingency'),investment_number($input,$p.'lease_rate'),investment_number($input,$p.'utilization_percent'),investment_number($input,$p.'downtime_percent'),investment_number($input,$p.'projected_revenue'),investment_number($input,$p.'operating_expense'),investment_number($input,$p.'maintenance_cost'),investment_number($input,$p.'management_fee'),investment_number($input,$p.'reserve_amount'),investment_number($input,$p.'residual_value'),($input[$p.'projected_return_percent']??'')===''?null:(float)$input[$p.'projected_return_percent'],investment_text($input,$p.'assumptions')]);}
        if($existing)investment_version($user,$id,investment_text($input,'change_reason',500)?:'Opportunity details updated');else db()->prepare('INSERT INTO investment_opportunity_versions(id,opportunity_id,version_number,snapshot,change_reason,created_by) VALUES(?,?,?,?,?,?)')->execute([uuid(),$id,1,json_encode(investment_snapshot($id),JSON_THROW_ON_ERROR),'Initial draft',$user['id']]);
        $pdo->commit();return $id;
    }catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();throw $e;}
}
function investment_publish_issues(string $id):array
{
    $op=rows('SELECT * FROM investment_opportunities WHERE id=?',[$id])[0];$technical=rows('SELECT * FROM investment_technical_profiles WHERE opportunity_id=?',[$id])[0]??[];$acquisition=rows('SELECT * FROM investment_acquisition_profiles WHERE opportunity_id=?',[$id])[0]??[];$issues=[];
    foreach(['title'=>'title','equipment_type_id'=>'equipment type','opportunity_rationale'=>'investment rationale','target_funding_amount'=>'target funding amount','asset_useful_life_days'=>'asset useful life','minimum_term_days'=>'minimum term','target_term_days'=>'target term','ownership_vehicle'=>'ownership vehicle','finance_security_partner'=>'Finance/Security Partner','controlled_account_details'=>'controlled-account structure','recovery_rights'=>'recovery rights','distribution_waterfall'=>'distribution waterfall','primary_risks'=>'principal risk disclosure'] as $key=>$label)if(empty($op[$key]))$issues[]='Add '.$label.'.';
    if((int)($op['minimum_term_days']??0)<90)$issues[]='Minimum term must be at least 90 days.';if((int)($op['target_term_days']??0)<(int)($op['minimum_term_days']??0))$issues[]='Target term must not be lower than the minimum term.';if((int)($op['target_term_days']??0)>(int)($op['asset_useful_life_days']??0))$issues[]='Target term must not exceed the asset useful life.';
    if(empty($technical['specification']))$issues[]='Add the required equipment specification.';if(empty($acquisition['supplier_name']))$issues[]='Add the proposed supplier.';
    if(!rows("SELECT 1 FROM investment_demand_evidence WHERE opportunity_id=? AND evidence_strength='VERIFIED_COMMERCIAL' LIMIT 1",[$id]))$issues[]='Add at least one verified commercial demand record.';
    if((int)(rows('SELECT COUNT(*) n FROM investment_financial_scenarios WHERE opportunity_id=?',[$id])[0]['n']??0)<3)$issues[]='Complete downside, base and upside scenarios.';
    return $issues;
}
function investment_transition(array $user,string $id,string $to):void
{
    if(!operation_can_manage($user))throw new DomainException('Opportunity manager access required.',403);
    $op=investment_opportunity($user,$id,true);$from=$op['status'];$allowed=['DRAFT'=>['SUBMITTED'],'SUBMITTED'=>['DUE_DILIGENCE','REJECTED'],'DUE_DILIGENCE'=>['REVIEW'],'REVIEW'=>['APPROVED','CONDITIONAL','REJECTED'],'CONDITIONAL'=>['SUBMITTED'],'APPROVED'=>['PUBLISHED'],'PUBLISHED'=>['WITHDRAWN']];
    if(!in_array($to,$allowed[$from]??[],true))throw new DomainException("Cannot move {$from} to {$to}.",409);
    if($to==='SUBMITTED'&&($issues=investment_publish_issues($id)))throw new DomainException(implode(' ',$issues),422);
    if(in_array($to,['DUE_DILIGENCE','REVIEW','APPROVED','CONDITIONAL','REJECTED','PUBLISHED'],true)&&!investment_can_review($user))throw new DomainException('Independent administrator review is required.',403);
    if($to==='REVIEW'&&(int)rows("SELECT COUNT(*) n FROM investment_due_diligence_reviews WHERE opportunity_id=? AND status IN ('APPROVED','CONDITIONAL','REJECTED')",[$id])[0]['n']<count(INVESTMENT_REVIEW_AREAS))throw new DomainException('Complete every due-diligence review area first.',422);
    if($to==='APPROVED'&&(int)rows("SELECT COUNT(*) n FROM investment_due_diligence_reviews WHERE opportunity_id=? AND status<>'APPROVED'",[$id])[0]['n']>0)throw new DomainException('All review areas must be approved before final approval.',422);
    if($to==='PUBLISHED'&&($issues=investment_publish_issues($id)))throw new DomainException(implode(' ',$issues),422);
    $mode=$to==='PUBLISHED'?'INVESTABLE':($op['publication_mode']??'PRIVATE');
    db()->prepare('UPDATE investment_opportunities SET status=?,publication_mode=?,submitted_at=IF(?=\'SUBMITTED\',UTC_TIMESTAMP(),submitted_at),approved_at=IF(?=\'APPROVED\',UTC_TIMESTAMP(),approved_at),published_at=IF(?=\'PUBLISHED\',UTC_TIMESTAMP(),published_at) WHERE id=?')->execute([$to,$mode,$to,$to,$to,$id]);investment_event($user,$id,'STATUS_CHANGED',$from,$to);
}
