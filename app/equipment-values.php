<?php
declare(strict_types=1);
function equipment_date(?string $value):string
{
    $date=DateTimeImmutable::createFromFormat('!Y-m-d',$value??'',new DateTimeZone('UTC'));
    if(!$date||$date->format('Y-m-d')!==$value)throw new DomainException('Enter a valid date (YYYY-MM-DD).',422);
    return $value;
}
function equipment_money(?string $value):int
{
    if(!preg_match('/^\d{1,11}(\.\d{1,2})?$/D',$value??''))throw new DomainException('Enter a non-negative amount with at most two decimal places.',422);
    [$whole,$fraction]=array_pad(explode('.',$value,2),2,'');return (int)$whole*100+(int)str_pad($fraction,2,'0');
}
function equipment_book_value(array $profile,string $asOf):array
{
    $asOf=equipment_date($asOf);$start=new DateTimeImmutable($profile['start_date'],new DateTimeZone('UTC'));$date=new DateTimeImmutable($asOf,new DateTimeZone('UTC'));
    $cost=equipment_money((string)$profile['acquisition_cost']);$salvage=equipment_money((string)$profile['salvage_value']);$life=(float)$profile['useful_life_years'];
    if($life<=0||$salvage>$cost)throw new DomainException('Invalid depreciation inputs.',422);
    $years=max(0,(int)$start->diff($date)->format('%r%a'))/365;
    $book=max($salvage,$cost-(int)round(($cost-$salvage)*min(1,$years/$life)));
    return ['book_value'=>number_format($book/100,2,'.',''),'annual_depreciation'=>number_format(($cost-$salvage)/$life/100,2,'.',''),'years_active'=>round($years,2),'change_percent'=>$cost>0?round(($book-$cost)/$cost*100,2):null];
}
function equipment_refresh_values(string $equipmentId):array
{
    $profile=rows('SELECT * FROM equipment_depreciation_profile WHERE equipment_id=?',[$equipmentId])[0]??null;
    $calculation=$profile&&$profile['is_active']?equipment_book_value($profile,gmdate('Y-m-d')):null;
    $currency=$profile['currency']??'NGN';
    $market=rows("SELECT * FROM equipment_value_snapshot WHERE equipment_id=? AND value_type IN ('MARKET','MARKET_ESTIMATE_AI') AND as_of_date<=UTC_DATE() AND currency=? ORDER BY as_of_date DESC,created_at DESC,id DESC LIMIT 1",[$equipmentId,$currency])[0]??null;
    $aiBook=rows("SELECT * FROM equipment_value_snapshot WHERE equipment_id=? AND value_type='BOOK_ESTIMATE_AI' AND as_of_date<=UTC_DATE() AND currency=? ORDER BY as_of_date DESC,created_at DESC,id DESC LIMIT 1",[$equipmentId,$currency])[0]??null;
    db()->prepare("INSERT INTO equipment_value_current(equipment_id,current_book_value,current_book_value_source_type,current_book_value_source_detail,current_market_value,current_market_value_source_type,current_market_value_source_detail,last_calculated_at) VALUES(?,?,?,?,?,?,?,UTC_TIMESTAMP()) ON DUPLICATE KEY UPDATE current_book_value=VALUES(current_book_value),current_book_value_source_type=VALUES(current_book_value_source_type),current_book_value_source_detail=VALUES(current_book_value_source_detail),current_market_value=VALUES(current_market_value),current_market_value_source_type=VALUES(current_market_value_source_type),current_market_value_source_detail=VALUES(current_market_value_source_detail),last_calculated_at=VALUES(last_calculated_at)")->execute([$equipmentId,$calculation['book_value']??null,$calculation?'SYSTEM':null,$calculation?'Straight-line depreciation, actual elapsed days / 365':null,$market['amount']??null,$market['source_type']??null,$market['source_detail']??null]);
    return ['profile'=>$profile,'calculation'=>$calculation,'market'=>$market,'ai_book'=>$aiBook,'currency'=>$currency];
}
function equipment_save_depreciation(array $user,string $id,array $input):void
{
    operation_equipment($user,$id);
    $cost=equipment_text($input,'acquisition_cost',20,true);$salvage=equipment_text($input,'salvage_value',20)??'0';$life=equipment_text($input,'useful_life_years',12,true);
    if(equipment_money($salvage)>equipment_money($cost)||!preg_match('/^\d{1,3}(\.\d{1,3})?$/D',$life)||(float)$life<=0)throw new DomainException('Useful life must be positive and salvage value cannot exceed cost.',422);
    $acquired=equipment_date(equipment_text($input,'acquisition_date',10,true));$start=equipment_date(equipment_text($input,'start_date',10)??$acquired);
    if($start<$acquired)throw new DomainException('Depreciation cannot begin before acquisition.',422);
    $currency=strtoupper(equipment_text($input,'currency',3)??'NGN');
    if(!preg_match('/^[A-Z]{3}$/D',$currency))throw new DomainException('Enter a three-letter currency code.',422);
    $before=rows('SELECT * FROM equipment_depreciation_profile WHERE equipment_id=?',[$id])[0]??null;
    if(rows('SELECT id FROM equipment_value_snapshot WHERE equipment_id=? AND currency<>? LIMIT 1',[$id,$currency]))throw new DomainException('Use the currency already recorded in this equipment value history.',409);
    if($before&&$before['currency']!==$currency&&rows('SELECT id FROM equipment_value_snapshot WHERE equipment_id=? LIMIT 1',[$id]))throw new DomainException('The valuation currency cannot change after history is recorded.',409);
    db()->prepare('INSERT INTO equipment_depreciation_profile(id,equipment_id,currency,acquisition_cost,acquisition_date,useful_life_years,salvage_value,start_date,is_active,notes) VALUES(?,?,?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE acquisition_cost=VALUES(acquisition_cost),acquisition_date=VALUES(acquisition_date),useful_life_years=VALUES(useful_life_years),salvage_value=VALUES(salvage_value),start_date=VALUES(start_date),is_active=VALUES(is_active),notes=VALUES(notes)')->execute([uuid(),$id,$currency,$cost,$acquired,$life,$salvage,$start,isset($input['is_active'])?1:0,equipment_text($input,'notes',20000)]);
    if(!$before||$before['acquisition_cost']!=$cost||$before['acquisition_date']!==$acquired)
        db()->prepare("INSERT INTO equipment_value_snapshot(id,equipment_id,as_of_date,value_type,amount,currency,source_type,source_detail) VALUES(?,?,?,'PURCHASE',?,?,'USER','Acquisition profile')")->execute([uuid(),$id,$acquired,$cost,$currency]);
    $values=equipment_refresh_values($id);
    if($values['calculation'])db()->prepare("INSERT INTO equipment_value_snapshot(id,equipment_id,as_of_date,value_type,amount,currency,source_type,source_detail) VALUES(?,?,UTC_DATE(),'BOOK',?,?,'SYSTEM','Straight-line depreciation: elapsed days / 365')")->execute([uuid(),$id,$values['calculation']['book_value'],$currency]);
    operation_audit($user,'depreciation.save','equipment',$id,$before,$values['profile']);
}
function equipment_add_value(array $user,string $id,array $input):void
{
    operation_equipment($user,$id);$profile=rows('SELECT currency FROM equipment_depreciation_profile WHERE equipment_id=?',[$id])[0]??null;
    $type=equipment_text($input,'value_type',30,true);$source=equipment_text($input,'source_type',30,true);$detail=equipment_text($input,'source_detail',20000,true);
    if(!in_array($type,['MARKET','MARKET_ESTIMATE_AI','BOOK_ESTIMATE_AI'],true)||!in_array($source,['USER','AI_AGENT','EXTERNAL_API'],true))throw new DomainException('Choose a supported value and source type.',422);
    if(str_ends_with($type,'_AI')!==($source==='AI_AGENT'))throw new DomainException('AI estimates must identify an AI source; other values must identify their actual source.',422);
    $amount=equipment_text($input,'amount',20,true);equipment_money($amount);$date=equipment_date(equipment_text($input,'as_of_date',10,true));
    if($date>gmdate('Y-m-d'))throw new DomainException('A value observation cannot be dated in the future.',422);
    $snapshot=uuid();db()->prepare('INSERT INTO equipment_value_snapshot(id,equipment_id,as_of_date,value_type,amount,currency,source_type,source_detail) VALUES(?,?,?,?,?,?,?,?)')->execute([$snapshot,$id,$date,$type,$amount,$profile['currency']??'NGN',$source,$detail]);
    equipment_refresh_values($id);operation_audit($user,'value.record','equipment',$id,null,['snapshot_id'=>$snapshot,'amount'=>$amount,'value_type'=>$type,'source_type'=>$source,'source_detail'=>$detail]);
}
