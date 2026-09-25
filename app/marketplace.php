<?php
declare(strict_types=1);
require_once __DIR__.'/equipment-catalog.php';

function marketplace_can_publish(array $user):bool
{
    return operation_can_manage($user);
}
function marketplace_text(array $input,string $key,int $max=255,bool $required=false):?string
{
    return equipment_text($input,$key,$max,$required);
}
function marketplace_enum(array $input,string $key,array $allowed,string $default=''):string
{
    $value=is_scalar($input[$key]??null)?(string)$input[$key]:'';
    if($value===''&&$default!=='')return $default;
    if(!in_array($value,$allowed,true))throw new DomainException('Choose a valid '.str_replace('_',' ',$key).'.',422);
    return $value;
}
function marketplace_number(array $input,string $key):?string
{
    $value=trim((string)($input[$key]??''));
    if($value==='')return null;
    if(!preg_match('/^\d{1,12}(?:\.\d{1,2})?$/',$value))throw new DomainException('Enter a valid non-negative '.$key.'.',422);
    return number_format((float)$value,2,'.','');
}
function marketplace_integer(array $input,string $key,int $default=0):int
{
    $value=trim((string)($input[$key]??''));
    if($value==='')return $default;
    if(!ctype_digit($value)||strlen($value)>9)throw new DomainException('Enter a valid '.$key.'.',422);
    return (int)$value;
}
function marketplace_term_options(string $kind):array
{
    return match($kind){
        'mobilization'=>['OWNER_DELIVERS'=>'Owner delivers to site','LESSEE_COLLECTS'=>'Lessee collects from owner','OWNER_ARRANGES_LESSEE_PAYS'=>'Owner arranges transport, lessee pays','LESSEE_ARRANGES_PAYS'=>'Lessee arranges and pays transport','COST_SHARED'=>'Cost shared','INCLUDED_DISTANCE'=>'Included within specified distance','THIRD_PARTY'=>'Third-party logistics required','TO_BE_AGREED'=>'To be agreed','CUSTOM'=>'Custom terms'],
        'demobilization'=>['OWNER_COLLECTS'=>'Owner collects from site','LESSEE_RETURNS'=>'Lessee returns to owner','OWNER_ARRANGES_LESSEE_PAYS'=>'Owner arranges return, lessee pays','LESSEE_ARRANGES_PAYS'=>'Lessee arranges and pays return','COST_SHARED'=>'Cost shared','INCLUDED_DISTANCE'=>'Included within specified distance','THIRD_PARTY'=>'Third-party logistics required','TO_BE_AGREED'=>'To be agreed','CUSTOM'=>'Custom terms'],
        'maintenance'=>['OWNER_FULL'=>'Owner fully responsible','LESSEE_FULL'=>'Lessee fully responsible','OWNER_MAJOR_LESSEE_ROUTINE'=>'Owner: major maintenance / Lessee: routine maintenance','OWNER_SCHEDULED_LESSEE_DAILY'=>'Owner: scheduled maintenance / Lessee: daily maintenance & consumables','SHARED'=>'Shared responsibility','INCLUDED'=>'Maintenance included in lease rate','SEPARATE_CHARGE'=>'Maintenance charged separately','CASE_BY_CASE'=>'Case-by-case approval required','CUSTOM'=>'Custom terms'],
        'insurance'=>['OWNER_PROVIDES'=>'Owner provides insurance','LESSEE_PROVIDES'=>'Lessee must provide insurance','BOTH'=>'Both parties maintain insurance','INCLUDED'=>'Insurance included in lease rate','PROOF_BEFORE_MOBILIZATION'=>'Lessee must provide proof before mobilization','PROJECT_SPECIFIC'=>'Specific project/site insurance required','SEPARATE'=>'Insurance arranged separately','NONE'=>'No additional insurance required','TO_BE_AGREED'=>'To be agreed','CUSTOM'=>'Custom terms'],
        'payment'=>['FULL'=>'Full payment','DEPOSIT_BALANCE'=>'Deposit + balance','INSTALMENT'=>'Instalment','FINANCING'=>'Financing permitted','TO_BE_AGREED'=>'To be agreed','CUSTOM'=>'Custom'],
        'sale_condition'=>['AS_IS'=>'As-is','INSPECTED'=>'Inspected condition','SERVICED_BEFORE_HANDOVER'=>'Serviced before handover','TO_BE_AGREED'=>'To be agreed'],
        'taxes'=>['INCLUDED'=>'Included','BUYER'=>'Buyer responsibility','SELLER'=>'Seller responsibility','SHARED'=>'Shared','TO_BE_AGREED'=>'To be agreed'],
        default=>[]
    };
}
function marketplace_option(array $input,string $key,string $kind,string $default=''):string
{
    return marketplace_enum($input,$key,array_keys(marketplace_term_options($kind)),$default);
}
function marketplace_option_label(string $kind,?string $value,?string $legacy=null):string
{
    if($value&&isset(marketplace_term_options($kind)[$value]))return marketplace_term_options($kind)[$value];
    return trim((string)$legacy)?:'Not specified';
}
function marketplace_locations():array
{
    return rows("SELECT id,canonical_name name,location_type type,country_code,country_name FROM marketplace_locations ORDER BY CASE location_type WHEN 'COUNTRY' THEN 0 WHEN 'REGION' THEN 1 ELSE 2 END,canonical_name");
}
function marketplace_location_rules(string $listingId):array
{
    return rows('SELECT r.location_id id,r.rule_type rule,l.canonical_name name,l.location_type type,l.country_code,l.country_name country FROM marketplace_listing_location_rules r JOIN marketplace_locations l ON l.id=r.location_id WHERE r.listing_id=? ORDER BY r.rule_type,l.canonical_name',[$listingId]);
}
function marketplace_save_location_rules(array $user,string $listingId,string $json):void
{
    marketplace_owned_listing($user,$listingId);try{$decoded=$json===''?[]:json_decode($json,true,32,JSON_THROW_ON_ERROR);}catch(JsonException){throw new DomainException('Invalid geographic rules.',422);}
    if(!is_array($decoded)||count($decoded)>40)throw new DomainException('Choose no more than 40 geographic rules.',422);
    $clean=[];
    foreach($decoded as $rule){
        if(!is_array($rule))throw new DomainException('Invalid geographic rule.',422);
        $id=trim((string)($rule['id']??''));$type=(string)($rule['rule']??'');
        if(!preg_match('/^[a-f0-9-]{36}$/i',$id)||!in_array($type,['ALLOW','RESTRICT'],true)||isset($clean[$id]))throw new DomainException('Remove contradictory or duplicate geographic rules.',422);
        if(!rows('SELECT id FROM marketplace_locations WHERE id=?',[$id]))throw new DomainException('Choose a location from the global directory.',422);
        $clean[$id]=$type;
    }
    db()->prepare('DELETE FROM marketplace_listing_location_rules WHERE listing_id=?')->execute([$listingId]);
    $insert=db()->prepare('INSERT INTO marketplace_listing_location_rules(id,listing_id,location_id,rule_type) VALUES(?,?,?,?)');
    foreach($clean as $location=>$type)$insert->execute([uuid(),$listingId,$location,$type]);
}
function marketplace_specs(?string $text):array
{
    $specs=[];
    foreach(preg_split('/\R/',trim((string)$text))?:[] as $line){
        if(trim($line)==='')continue;
        $line=trim($line);$parts=preg_split('/\s*(?::|=|\t)\s*/',$line,2)?:[];
        if(count($parts)===2&&trim($parts[0])!==''&&trim($parts[1])!=='')[$key,$value]=array_map('trim',$parts);
        else{$key='Specification '.(count($specs)+1);$value=$line;}
        if(mb_strlen($key)>80||mb_strlen($value)>250)throw new DomainException('Keep each specification label under 80 characters and each value under 250 characters.',422);
        $specs[$key]=$value;if(count($specs)>30)throw new DomainException('Add no more than 30 specifications.',422);
    }
    return $specs;
}
function marketplace_slug(string $name):string
{
    $slug=strtolower(trim((string)preg_replace('/[^a-z0-9]+/i','-',$name),'-'));
    return substr($slug?:'manufacturer',0,160);
}
function marketplace_create_standard_asset(array $user,string $unit,string $assetCode,string $name):string
{
    equipment_seed_statuses($user['owner_id']);
    $status=rows("SELECT id FROM equipment_statuses WHERE owner_id=? AND operational_state='OPERATIONAL' AND is_default=1",[$user['owner_id']])[0]['id']??null;
    if(!$status)throw new RuntimeException('The default operational status is unavailable.');
    $id=uuid();
    db()->prepare("INSERT INTO equipment(id,owner_id,unit_id,asset_code,name,status,status_id,marketplace_only) VALUES(?,?,?,?,?,'OPERATIONAL',?,0)")->execute([$id,$user['owner_id'],$unit,$assetCode,$name,$status]);
    operation_audit($user,'marketplace.asset_created','equipment',$id,null,['asset_code'=>$assetCode,'name'=>$name,'marketplace_only'=>false,'source'=>'marketplace']);
    return $id;
}
function marketplace_oem(array $user,array $input):string
{
    $id=marketplace_text($input,'oem_id',36);
    if($id){
        if(!rows('SELECT id FROM marketplace_oems WHERE id=?',[$id]))throw new DomainException('Choose an available OEM.',422);
        return $id;
    }
    $name=marketplace_text($input,'new_oem',255,true);$slug=marketplace_slug($name);
    $existing=rows('SELECT id FROM marketplace_oems WHERE slug=?',[$slug])[0]['id']??null;
    if($existing)return $existing;
    $id=uuid();db()->prepare("INSERT INTO marketplace_oems(id,suggested_by_owner_id,legal_name,brand_name,slug,verification_status) VALUES(?,?,?,?,?,'PENDING')")->execute([$id,$user['owner_id'],$name,$name,$slug]);
    operation_audit($user,'marketplace.oem_suggested','oem',$id,null,['brand_name'=>$name,'verification_status'=>'PENDING']);
    return $id;
}
function marketplace_model(array $user,array $input,string $oem,?string $type):?string
{
    $id=marketplace_text($input,'oem_model_id',36);
    if($id){
        $model=rows('SELECT * FROM marketplace_oem_models WHERE id=? AND oem_id=?',[$id,$oem])[0]??null;
        if(!$model)throw new DomainException('Choose a model belonging to the selected OEM.',422);
        return $id;
    }
    $name=marketplace_text($input,'new_model',255);
    if(!$name)return null;
    $existing=rows('SELECT id FROM marketplace_oem_models WHERE oem_id=? AND model_name=?',[$oem,$name])[0]['id']??null;
    if($existing)return $existing;
    $id=uuid();db()->prepare('INSERT INTO marketplace_oem_models(id,oem_id,equipment_type_id,model_name) VALUES(?,?,?,?)')->execute([$id,$oem,$type,$name]);
    operation_audit($user,'marketplace.model_suggested','oem_model',$id,null,['oem_id'=>$oem,'model_name'=>$name]);
    return $id;
}
function marketplace_owned_listing(array $user,string $id,bool $lock=false):array
{
    $row=rows('SELECT l.*,e.name AS equipment_name,e.asset_code,e.status AS operational_status,e.archived_at FROM marketplace_listings l JOIN equipment e ON e.id=l.asset_id WHERE l.id=? AND l.organization_id=?'.($lock?' FOR UPDATE':''),[$id,$user['owner_id']])[0]??null;
    if(!$row)throw new DomainException('Marketplace listing not found.',404);
    return $row;
}
function marketplace_public_listing(string $id):array
{
    $row=rows("SELECT l.*,e.name AS equipment_name,e.asset_code,e.manufacture_year,e.short_description,e.marketplace_specifications,e.marketplace_oem_id,e.marketplace_oem_model_id,c.name category_name,t.name type_name,o.brand_name oem_name,o.slug oem_slug,o.verification_status oem_verification,m.model_name,own.name owner_name,own.public_description owner_description,own.public_city owner_city,own.public_state owner_state,own.public_country owner_country,own.marketplace_verified,lt.daily_rate,lt.weekly_rate,lt.monthly_rate,lt.project_rate,lt.currency lease_currency,lt.rate lease_rate,lt.negotiable lease_negotiable,lt.operator_included,lt.operator_rate,lt.fuel_included,lt.consumables_included,lt.consumables_details,lt.minimum_duration,lt.maximum_duration,lt.duration_unit,lt.security_deposit,lt.mobilization_terms,lt.mobilization_option,lt.mobilization_custom,lt.demobilization_terms,lt.demobilization_option,lt.demobilization_custom,lt.maintenance_responsibility,lt.maintenance_option,lt.maintenance_details,lt.insurance_requirement,lt.insurance_option,lt.insurance_details,st.asking_price,st.currency sale_currency,st.negotiable sale_negotiable,st.payment_terms,st.payment_terms_custom,st.inspection_allowed,st.condition sale_condition,st.taxes_fees,st.delivery_terms,st.sale_notes FROM marketplace_listings l JOIN equipment e ON e.id=l.asset_id JOIN owners own ON own.id=l.organization_id LEFT JOIN equipment_categories c ON c.id=e.category_id LEFT JOIN equipment_types t ON t.id=e.type_id LEFT JOIN marketplace_oems o ON o.id=e.marketplace_oem_id LEFT JOIN marketplace_oem_models m ON m.id=e.marketplace_oem_model_id LEFT JOIN marketplace_lease_terms lt ON lt.listing_id=l.id LEFT JOIN marketplace_sale_terms st ON st.listing_id=l.id WHERE l.id=? AND l.listing_status='ACTIVE' AND l.visibility='PUBLIC' AND e.archived_at IS NULL",[$id])[0]??null;
    if(!$row)throw new DomainException('Marketplace listing not found.',404);
    return $row;
}
function marketplace_public_scope(array $input):array
{
    $sql="l.listing_status='ACTIVE' AND l.visibility='PUBLIC' AND e.archived_at IS NULL";$params=[];
    $q=mb_substr(trim((string)($input['q']??'')),0,150);
    if($q!=='')foreach(array_slice(preg_split('/\s+/',mb_strtolower($q))?:[],0,8) as $term){if($term==='')continue;$escaped='%'.str_replace(['!','%','_'],['!!','!%','!_'],$term).'%';$sql.=" AND LOWER(CONCAT_WS(' ',l.title,l.description,e.name,c.name,t.name,o.brand_name,m.model_name,l.city,l.state_region,l.public_specifications)) LIKE ? ESCAPE '!'";$params[]=$escaped;}
    $enums=['purpose'=>['LEASE','SALE','LEASE_OR_SALE'],'status'=>['AVAILABLE','RESERVED','MOBILIZING','IN_USE','MAINTENANCE','OFFLINE','BLOCKED'],'compliance'=>['NOT_PROVIDED','AVAILABLE_ON_REQUEST','VALID','EXPIRED']];
    foreach($enums as $field=>$allowed){$value=(string)($input[$field]??'');if($value!==''){if(!in_array($value,$allowed,true))throw new DomainException('Invalid marketplace filter.',422);$column=$field==='status'?'marketplace_status':($field==='compliance'?'compliance_status':$field);$sql.=" AND l.{$column}=?";$params[]=$value;}}
    foreach(['category'=>'e.category_id','type'=>'e.type_id','oem'=>'e.marketplace_oem_id','model'=>'e.marketplace_oem_model_id'] as $field=>$column){$value=trim((string)($input[$field]??''));if($value!==''){$sql.=" AND {$column}=?";$params[]=$value;}}
    foreach(['country','state','city'] as $field){$value=mb_substr(trim((string)($input[$field]??'')),0,100);if($value!==''){$column=$field==='state'?'state_region':$field;$sql.=" AND l.{$column} LIKE ?";$params[]='%'.$value.'%';}}
    if(!empty($input['available_now']))$sql.=" AND l.marketplace_status='AVAILABLE' AND (l.available_from IS NULL OR l.available_from<=UTC_DATE()) AND NOT EXISTS(SELECT 1 FROM marketplace_availability_periods blocked WHERE blocked.listing_id=l.id AND blocked.availability='UNAVAILABLE' AND UTC_DATE() BETWEEN blocked.start_date AND blocked.end_date)";
    if(!empty($input['available_from'])){$date=(string)$input['available_from'];if(!preg_match('/^\d{4}-\d{2}-\d{2}$/',$date))throw new DomainException('Choose a valid availability date.',422);$sql.=" AND (l.available_from IS NULL OR l.available_from<=?) AND NOT EXISTS(SELECT 1 FROM marketplace_availability_periods blocked WHERE blocked.listing_id=l.id AND blocked.availability='UNAVAILABLE' AND ? BETWEEN blocked.start_date AND blocked.end_date)";$params[]=$date;$params[]=$date;}
    if(!empty($input['certified']))$sql.=" AND l.compliance_status='VALID' AND (l.certification_valid_until IS NULL OR l.certification_valid_until>=UTC_DATE())";
    if(!empty($input['certification_type'])){$sql.=' AND l.certification_type LIKE ?';$params[]='%'.mb_substr(trim((string)$input['certification_type']),0,150).'%';}
    if(!empty($input['recently_inspected']))$sql.=' AND l.last_inspected_on>=DATE_SUB(UTC_DATE(),INTERVAL 90 DAY)';
    if(!empty($input['request_quote']))$sql.=" AND l.price_visibility='REQUEST_QUOTE'";
    if(!empty($input['negotiable']))$sql.=' AND (lt.negotiable=1 OR st.negotiable=1)';
    if(!empty($input['operator_included']))$sql.=' AND lt.operator_included=1';
    foreach(['max_daily'=>'lt.daily_rate','max_monthly'=>'lt.monthly_rate','max_sale'=>'st.asking_price'] as $field=>$column){$value=marketplace_number($input,$field);if($value!==null){$sql.=" AND {$column}<=?";$params[]=$value;}}
    return [$sql,$params];
}
function marketplace_media_upload(array $user,string $listingId,array $file,array $input):string
{
    $listing=marketplace_owned_listing($user,$listingId);
    if(($file['error']??UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_OK)throw new DomainException('Choose a marketplace image or document.',422);
    if(($file['size']??0)>10*1024*1024)throw new DomainException('Marketplace media must be 10 MB or smaller.',422);
    $mime=(new finfo(FILEINFO_MIME_TYPE))->file((string)$file['tmp_name']);
    $map=['image/jpeg'=>'IMAGE','image/png'=>'IMAGE','image/webp'=>'IMAGE','application/pdf'=>'OTHER'];
    if(!isset($map[$mime]))throw new DomainException('Upload a JPEG, PNG, WebP, or PDF file.',422);
    $type=$map[$mime]==='IMAGE'?'IMAGE':marketplace_enum($input,'media_type',['SPECIFICATION_SHEET','BROCHURE','CERTIFICATE','INSPECTION','OTHER'],'OTHER');
    $mediaInput=$input;$mediaInput['visibility']=$input['media_visibility']??'PUBLIC';
    $visibility=marketplace_enum($mediaInput,'visibility',['PUBLIC','ON_REQUEST','PRIVATE'],'PUBLIC');
    $id=uuid();$primary=$type==='IMAGE'&&!rows("SELECT id FROM marketplace_listing_media WHERE listing_id=? AND media_type='IMAGE' AND is_primary=1",[$listingId]);$bytes=file_get_contents($file['tmp_name']);$equipmentPhoto=null;
    if($type==='IMAGE'){
        $info=@getimagesizefromstring($bytes);if(!$info||$info[0]*$info[1]>40000000)throw new DomainException('Upload a valid marketplace image up to 40 megapixels.',422);
        $equipmentPhoto=uuid();$equipmentPrimary=!rows('SELECT id FROM equipment_photos WHERE equipment_id=? AND is_primary=1',[$listing['asset_id']]);$url='equipment-photo?id='.$equipmentPhoto;$caption=marketplace_text($input,'media_title',255)??'Marketplace equipment image';
        db()->prepare('INSERT INTO equipment_photos(id,equipment_id,url,caption,is_primary,mime_type,image_data) VALUES(?,?,?,?,?,?,?)')->execute([$equipmentPhoto,$listing['asset_id'],$url,$caption,(int)$equipmentPrimary,$mime,$bytes]);
        if($equipmentPrimary)db()->prepare('UPDATE equipment SET photo_primary_url=? WHERE id=?')->execute([$url,$listing['asset_id']]);
    }
    db()->prepare('INSERT INTO marketplace_listing_media(id,listing_id,equipment_photo_id,media_type,title,mime_type,file_data,visibility,is_primary) VALUES(?,?,?,?,?,?,?,?,?)')->execute([$id,$listingId,$equipmentPhoto,$type,marketplace_text($input,'media_title',255),$mime,$equipmentPhoto?null:$bytes,$visibility,(int)$primary]);
    operation_audit($user,'marketplace.media_added','listing',$listingId,null,['media_id'=>$id,'type'=>$type,'visibility'=>$visibility]);
    return $id;
}
function marketplace_external_media(array $user,string $listingId,array $input):string
{
    marketplace_owned_listing($user,$listingId);$url=marketplace_text($input,'external_url',500,true);
    if(!filter_var($url,FILTER_VALIDATE_URL)||strtolower((string)parse_url($url,PHP_URL_SCHEME))!=='https'||parse_url($url,PHP_URL_USER)||parse_url($url,PHP_URL_PASS))throw new DomainException('External media must use a safe HTTPS URL.',422);
    $type=marketplace_enum($input,'external_media_type',['VIDEO','SPECIFICATION_SHEET','BROCHURE','OTHER'],'VIDEO');$visibility=marketplace_enum($input,'external_visibility',['PUBLIC','ON_REQUEST','PRIVATE'],'PUBLIC');$id=uuid();
    db()->prepare('INSERT INTO marketplace_listing_media(id,listing_id,media_type,title,external_url,visibility) VALUES(?,?,?,?,?,?)')->execute([$id,$listingId,$type,marketplace_text($input,'external_title',255,true),$url,$visibility]);operation_audit($user,'marketplace.media_added','listing',$listingId,null,['media_id'=>$id,'type'=>$type,'visibility'=>$visibility]);return $id;
}
function marketplace_sync_erp_status(string $listingId):void
{
    db()->prepare("UPDATE marketplace_listings l JOIN equipment e ON e.id=l.asset_id SET l.marketplace_status=CASE WHEN e.status='MAINTENANCE' THEN 'MAINTENANCE' WHEN e.status='DOWN' THEN 'BLOCKED' ELSE l.marketplace_status END WHERE l.id=?")->execute([$listingId]);
}
function marketplace_apply_operational_status(array $user,string $equipmentId):void
{
    $equipment=rows('SELECT status FROM equipment WHERE id=? AND owner_id=?',[$equipmentId,$user['owner_id']])[0]??null;if(!$equipment)return;
    foreach(rows("SELECT * FROM marketplace_listings WHERE asset_id=? AND listing_status IN ('ACTIVE','RESERVED') FOR UPDATE",[$equipmentId]) as $before){
        $next=$equipment['status']==='MAINTENANCE'?'MAINTENANCE':($equipment['status']==='DOWN'?'BLOCKED':(in_array($before['marketplace_status'],['MAINTENANCE','BLOCKED'],true)?'AVAILABLE':$before['marketplace_status']));
        if($next===$before['marketplace_status'])continue;
        db()->prepare('UPDATE marketplace_listings SET marketplace_status=? WHERE id=?')->execute([$next,$before['id']]);
        operation_audit($user,'marketplace.status_from_erp','listing',$before['id'],$before,['marketplace_status'=>$next,'equipment_status'=>$equipment['status']]);
    }
}
function marketplace_publish_issues(array $user,string $id):array
{
    $listing=marketplace_owned_listing($user,$id);$issues=[];
    if(!$listing['title']||!$listing['description']||!$listing['country']||!$listing['state_region']||!$listing['city'])$issues[]='Complete the listing details and public location.';
    $asset=rows('SELECT category_id,subcategory_id,type_id,marketplace_oem_id,archived_at FROM equipment WHERE id=?',[$listing['asset_id']])[0];
    if($asset['archived_at'])$issues[]='Restore the archived equipment asset.';
    if(!$asset['category_id']&&!$asset['subcategory_id']&&!$asset['type_id'])$issues[]='Choose an equipment category, subcategory, or type.';
    if(!$asset['marketplace_oem_id'])$issues[]='Choose or create the equipment OEM.';
    if(!rows("SELECT id FROM marketplace_listing_media WHERE listing_id=? AND media_type='IMAGE' AND visibility='PUBLIC'",[$id]))$issues[]='Add at least one public image.';
    if($listing['purpose']!=='SALE'){
        $lease=rows('SELECT rate,duration_unit,minimum_duration,operator_included,operator_rate FROM marketplace_lease_terms WHERE listing_id=?',[$id])[0]??null;
        if(!$lease||$lease['rate']===null||!$lease['duration_unit']||(int)$lease['minimum_duration']<1)$issues[]='Add the lease duration, rate, and minimum duration.';
        elseif($lease['operator_included']&&$lease['operator_rate']===null)$issues[]='Add the operator rate or mark the operator as not included.';
    }
    if($listing['purpose']!=='LEASE'&&!rows('SELECT listing_id FROM marketplace_sale_terms WHERE listing_id=? AND asking_price IS NOT NULL',[$id]))$issues[]='Add the sale asking price.';
    return $issues;
}
function marketplace_publish(array $user,string $id):void
{
    $listing=marketplace_owned_listing($user,$id,true);marketplace_sync_erp_status($id);$listing=marketplace_owned_listing($user,$id,true);
    if(!in_array($listing['listing_status'],['DRAFT','PAUSED'],true))throw new DomainException('Only draft or paused listings can be published.',409);
    $issues=marketplace_publish_issues($user,$id);if($issues)throw new DomainException(implode(' ',$issues),422);
    $before=$listing;db()->prepare("UPDATE marketplace_listings SET listing_status='ACTIVE',published_at=COALESCE(published_at,UTC_TIMESTAMP()) WHERE id=?")->execute([$id]);
    operation_audit($user,'marketplace.listing_published','listing',$id,$before,marketplace_owned_listing($user,$id));
}
function marketplace_transition(array $user,string $id,string $target):void
{
    $before=marketplace_owned_listing($user,$id,true);$allowed=['ACTIVE'=>['PAUSED','CLOSED'],'RESERVED'=>['PAUSED','CLOSED'],'DRAFT'=>['CLOSED'],'PAUSED'=>['CLOSED'],'CLOSED'=>[]];
    if(!in_array($target,$allowed[$before['listing_status']]??[],true))throw new DomainException('That listing transition is not available.',409);
    db()->prepare('UPDATE marketplace_listings SET listing_status=? WHERE id=?')->execute([$target,$id]);
    operation_audit($user,'marketplace.listing_'.strtolower($target),'listing',$id,$before,marketplace_owned_listing($user,$id));
}
function marketplace_status_badge(string $status):string
{
    $labels=['AVAILABLE'=>'Available','RESERVED'=>'Reserved','MOBILIZING'=>'Mobilizing','IN_USE'=>'In use','MAINTENANCE'=>'Maintenance','OFFLINE'=>'Offline','BLOCKED'=>'Blocked'];
    return '<span class="market-badge status-'.strtolower($status).'">'.e($labels[$status]??$status).'</span>';
}
function marketplace_purpose_label(string $purpose):string
{
    return ['LEASE'=>'For lease','SALE'=>'For sale','LEASE_OR_SALE'=>'Lease / sale'][$purpose]??$purpose;
}
function marketplace_price(array $listing):string
{
    if(($listing['price_visibility']??'')==='REQUEST_QUOTE')return 'Request quote';
    $currency=e($listing['lease_currency']??$listing['sale_currency']??'NGN');
    if(isset($listing['lease_rate'])&&$listing['lease_rate']!==null)return $currency.' '.number_format((float)$listing['lease_rate'],0).' / '.strtolower((string)($listing['duration_unit']??'unit'));
    foreach(['daily_rate'=>' / day','monthly_rate'=>' / month','asking_price'=>' sale'] as $field=>$suffix)if(isset($listing[$field])&&$listing[$field]!==null)return $currency.' '.number_format((float)$listing[$field],0).$suffix;
    return 'Request quote';
}
