<?php
declare(strict_types=1);
require_once __DIR__.'/marketplace.php';

function marketplace_negotiation_manager(array $user): bool
{
    return in_array($user['role'],['OWNER_ADMIN','ADMIN','SUPERVISOR'],true);
}

function marketplace_deal_listing(string $id): array
{
    $row=rows("SELECT l.*,e.name equipment_name,e.asset_code,o.name owner_name,lt.rate lease_rate,lt.currency lease_currency,lt.duration_unit,st.asking_price,st.currency sale_currency FROM marketplace_listings l JOIN equipment e ON e.id=l.asset_id JOIN owners o ON o.id=l.organization_id LEFT JOIN marketplace_lease_terms lt ON lt.listing_id=l.id LEFT JOIN marketplace_sale_terms st ON st.listing_id=l.id WHERE l.id=? AND l.listing_status IN ('ACTIVE','RESERVED') AND l.visibility='PUBLIC' AND e.archived_at IS NULL",[$id])[0]??null;
    if(!$row)throw new DomainException('This listing is not available for enquiries or offers.',404);
    return $row;
}

function marketplace_context_listing(string $id): array
{
    $row=rows("SELECT l.*,e.name equipment_name,e.asset_code,o.name owner_name,lt.rate lease_rate,lt.currency lease_currency,lt.duration_unit,st.asking_price,st.currency sale_currency FROM marketplace_listings l JOIN equipment e ON e.id=l.asset_id JOIN owners o ON o.id=l.organization_id LEFT JOIN marketplace_lease_terms lt ON lt.listing_id=l.id LEFT JOIN marketplace_sale_terms st ON st.listing_id=l.id WHERE l.id=?",[$id])[0]??null;
    if(!$row)throw new DomainException('Marketplace listing not found.',404);return $row;
}

function marketplace_party_scope(array $user,array $row,string $buyer='buyer_organization_id',string $seller='owner_organization_id'): void
{
    if(!in_array($user['owner_id'],[$row[$buyer]??null,$row[$seller]??null],true))throw new DomainException('You do not have access to this marketplace conversation.',403);
    if(!marketplace_negotiation_manager($user)&&(($row[$buyer]??null)!==$user['owner_id']||($row['created_by']??null)!==$user['id']))throw new DomainException('Your role does not have access to this company negotiation.',403);
}

function marketplace_notify_organization(string $ownerId,string $event,string $type,string $entity,string $title,string $body='',?string $exceptUser=null): void
{
    $stmt=db()->prepare("INSERT INTO marketplace_notifications(id,user_id,event_type,entity_type,entity_id,title,body) SELECT UUID(),u.id,?,?,?,?,? FROM users u WHERE u.owner_id=? AND u.is_active=1 AND (? IS NULL OR u.id<>?)");
    $stmt->execute([$event,$type,$entity,$title,$body,$ownerId,$exceptUser,$exceptUser]);
}

function marketplace_saved(array $user,string $listingId): bool
{
    return (bool)rows('SELECT 1 FROM marketplace_saved_listings WHERE user_id=? AND listing_id=?',[$user['id'],$listingId]);
}

function marketplace_toggle_save(array $user,string $listingId): bool
{
    $listing=marketplace_deal_listing($listingId);
    if($listing['organization_id']===$user['owner_id'])throw new DomainException('Your own listing is already available in My Listings.',409);
    if(marketplace_saved($user,$listingId)){db()->prepare('DELETE FROM marketplace_saved_listings WHERE user_id=? AND listing_id=?')->execute([$user['id'],$listingId]);return false;}
    db()->prepare('INSERT INTO marketplace_saved_listings(user_id,listing_id) VALUES(?,?)')->execute([$user['id'],$listingId]);return true;
}

function marketplace_enquiry(array $user,string $id,bool $lock=false): array
{
    $row=rows('SELECT q.*,c.id conversation_id,l.title listing_title,o.name buyer_name,own.name owner_name FROM marketplace_enquiries q JOIN marketplace_conversations c ON c.enquiry_id=q.id JOIN marketplace_listings l ON l.id=q.listing_id JOIN owners o ON o.id=q.buyer_organization_id JOIN owners own ON own.id=q.owner_organization_id WHERE q.id=?'.($lock?' FOR UPDATE':''),[$id])[0]??null;
    if(!$row)throw new DomainException('Enquiry not found.',404);marketplace_party_scope($user,$row);return $row;
}

function marketplace_create_enquiry(array $user,array $input): string
{
    $listing=marketplace_deal_listing(marketplace_text($input,'listing_id',36,true));
    if($listing['organization_id']===$user['owner_id'])throw new DomainException('You cannot enquire about your own listing.',409);
    $category=marketplace_enum($input,'category',['AVAILABILITY','SPECIFICATION','CERTIFICATION','INSPECTION','MOBILIZATION','OPERATOR','COMMERCIAL_TERMS','OTHER']);
    $subject=marketplace_text($input,'subject',255,true);$message=marketplace_text($input,'message',5000,true);
    $id=uuid();$conversation=uuid();$messageId=uuid();
    db()->prepare('INSERT INTO marketplace_enquiries(id,listing_id,asset_id,buyer_organization_id,owner_organization_id,created_by,category,subject) VALUES(?,?,?,?,?,?,?,?)')->execute([$id,$listing['id'],$listing['asset_id'],$user['owner_id'],$listing['organization_id'],$user['id'],$category,$subject]);
    db()->prepare('INSERT INTO marketplace_conversations(id,enquiry_id,listing_id,buyer_organization_id,owner_organization_id) VALUES(?,?,?,?,?)')->execute([$conversation,$id,$listing['id'],$user['owner_id'],$listing['organization_id']]);
    db()->prepare('INSERT INTO marketplace_messages(id,conversation_id,sender_user_id,sender_organization_id,message_text) VALUES(?,?,?,?,?)')->execute([$messageId,$conversation,$user['id'],$user['owner_id'],$message]);
    marketplace_notify_organization($listing['organization_id'],'ENQUIRY_CREATED','enquiry',$id,'New marketplace enquiry',$listing['title'].' — '.$subject,$user['id']);
    operation_audit($user,'marketplace.enquiry_created','enquiry',$id,null,['listing_id'=>$listing['id'],'category'=>$category,'subject'=>$subject]);
    return $id;
}

function marketplace_message_attachment(string $messageId,array $file): ?string
{
    if(($file['error']??UPLOAD_ERR_NO_FILE)===UPLOAD_ERR_NO_FILE)return null;
    if(($file['error']??UPLOAD_ERR_OK)!==UPLOAD_ERR_OK||!is_uploaded_file((string)($file['tmp_name']??'')))throw new DomainException('The attachment upload failed.',422);
    $size=(int)($file['size']??0);if($size<1||$size>10*1024*1024)throw new DomainException('Attachments must be 10 MB or smaller.',422);
    $mime=(new finfo(FILEINFO_MIME_TYPE))->file((string)$file['tmp_name']);$allowed=['image/jpeg','image/png','image/webp','application/pdf','application/msword','application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
    if(!in_array($mime,$allowed,true))throw new DomainException('Attach a JPEG, PNG, WebP, PDF, DOC, or DOCX file.',422);
    $name=preg_replace('/[^A-Za-z0-9._ -]/','_',basename((string)($file['name']??'attachment')));$id=uuid();
    db()->prepare('INSERT INTO marketplace_message_attachments(id,message_id,file_name,mime_type,file_size,file_data) VALUES(?,?,?,?,?,?)')->execute([$id,$messageId,substr($name,0,255),$mime,$size,file_get_contents($file['tmp_name'])]);return $id;
}

function marketplace_send_message(array $user,string $enquiryId,array $input,array $file=[]): string
{
    $enquiry=marketplace_enquiry($user,$enquiryId,true);if($enquiry['status']==='CLOSED')throw new DomainException('This enquiry is closed.',409);
    $text=marketplace_text($input,'message',5000);$hasFile=($file['error']??UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_NO_FILE;if(!$text&&!$hasFile)throw new DomainException('Write a message or attach a file.',422);
    $offerId=marketplace_text($input,'offer_id',36);if($offerId)marketplace_offer($user,$offerId);
    $id=uuid();db()->prepare('INSERT INTO marketplace_messages(id,conversation_id,sender_user_id,sender_organization_id,message_text,referenced_offer_id) VALUES(?,?,?,?,?,?)')->execute([$id,$enquiry['conversation_id'],$user['id'],$user['owner_id'],$text,$offerId]);
    $attachment=marketplace_message_attachment($id,$file);$recipient=$user['owner_id']===$enquiry['buyer_organization_id']?$enquiry['owner_organization_id']:$enquiry['buyer_organization_id'];
    if($user['owner_id']===$enquiry['owner_organization_id'])db()->prepare("UPDATE marketplace_enquiries SET status='RESPONDED' WHERE id=? AND status='OPEN'")->execute([$enquiryId]);
    marketplace_notify_organization($recipient,'MESSAGE_RECEIVED','enquiry',$enquiryId,'New marketplace message',$enquiry['listing_title'],$user['id']);
    operation_audit($user,'marketplace.message_sent','enquiry',$enquiryId,null,['message_id'=>$id,'attachment'=>(bool)$attachment,'offer_id'=>$offerId]);return $id;
}

function marketplace_offer(array $user,string $id,bool $lock=false): array
{
    $row=rows("SELECT f.*,l.title listing_title,e.name equipment_name,bo.name buyer_name,so.name seller_name,v.id version_id,v.version_number,v.proposed_by_user_id,v.proposed_by_organization_id,v.amount,v.currency,v.pricing_basis,v.lease_start_date,v.lease_end_date,v.project_country,v.project_state,v.project_city,v.intended_use,v.operator_requirement,v.mobilization_cost,v.security_deposit,v.commercial_conditions,v.additional_requirements,v.valid_until FROM marketplace_offers f JOIN marketplace_listings l ON l.id=f.listing_id JOIN equipment e ON e.id=f.asset_id JOIN owners bo ON bo.id=f.buyer_organization_id JOIN owners so ON so.id=f.seller_organization_id JOIN marketplace_offer_versions v ON v.offer_id=f.id AND v.version_number=f.current_version WHERE f.id=?".($lock?' FOR UPDATE':''),[$id])[0]??null;
    if(!$row)throw new DomainException('Offer not found.',404);marketplace_party_scope($user,$row,'buyer_organization_id','seller_organization_id');return $row;
}

function marketplace_expire_offers(): void
{
    db()->exec("UPDATE marketplace_offers SET status='EXPIRED' WHERE status IN ('SUBMITTED','VIEWED','COUNTERED','REVISED') AND expires_at<UTC_TIMESTAMP()");
}

function marketplace_offer_values(array $input,string $type): array
{
    $amount=marketplace_number($input,'amount');if($amount===null||(float)$amount<=0)throw new DomainException('Enter an offer amount greater than zero.',422);
    $currency=strtoupper(marketplace_text($input,'currency',3)??'NGN');if(!preg_match('/^[A-Z]{3}$/',$currency))throw new DomainException('Use a three-letter currency code.',422);
    $basis=$type==='SALE'?'SALE':marketplace_enum($input,'pricing_basis',['HOURLY','DAILY','WEEKLY','MONTHLY','PROJECT'],'DAILY');
    $start=$type==='LEASE'?marketplace_text($input,'lease_start_date',10,true):null;$end=$type==='LEASE'?marketplace_text($input,'lease_end_date',10,true):null;
    if($type==='LEASE'&&(!preg_match('/^\d{4}-\d{2}-\d{2}$/',$start)||!preg_match('/^\d{4}-\d{2}-\d{2}$/',$end)||$end<$start||$start<gmdate('Y-m-d')))throw new DomainException('Choose a valid current or future lease date range.',422);
    $valid=marketplace_text($input,'valid_until',16,true);$validDate=DateTimeImmutable::createFromFormat('Y-m-d\TH:i',$valid,new DateTimeZone('UTC'));if(!$validDate||$validDate<=new DateTimeImmutable('now',new DateTimeZone('UTC')))throw new DomainException('Offer validity must be in the future.',422);
    $operator=$type==='SALE'?'NOT_APPLICABLE':marketplace_enum($input,'operator_requirement',['OWNER_OPERATOR','LESSEE_OPERATOR','TO_BE_DETERMINED'],'TO_BE_DETERMINED');
    return [$amount,$currency,$basis,$start,$end,marketplace_text($input,'project_country',100),marketplace_text($input,'project_state',100),marketplace_text($input,'project_city',100),marketplace_text($input,'intended_use',5000),$operator,marketplace_number($input,'mobilization_cost'),marketplace_number($input,'security_deposit'),marketplace_text($input,'commercial_conditions',5000),marketplace_text($input,'additional_requirements',5000),$validDate->format('Y-m-d H:i:s')];
}

function marketplace_create_offer(array $user,array $input,bool $submit=true): string
{
    $listing=marketplace_deal_listing(marketplace_text($input,'listing_id',36,true));if($listing['organization_id']===$user['owner_id'])throw new DomainException('You cannot make an offer on your own listing.',409);
    $type=marketplace_enum($input,'transaction_type',['LEASE','SALE']);if(($type==='LEASE'&&$listing['purpose']==='SALE')||($type==='SALE'&&$listing['purpose']==='LEASE'))throw new DomainException('That transaction type is not available for this listing.',422);
    $enquiryId=marketplace_text($input,'enquiry_id',36);if($enquiryId){$enquiry=marketplace_enquiry($user,$enquiryId);if($enquiry['listing_id']!==$listing['id']||$enquiry['buyer_organization_id']!==$user['owner_id'])throw new DomainException('The selected enquiry does not match this offer.',422);}
    $values=marketplace_offer_values($input,$type);$id=uuid();$version=uuid();$status=$submit?'SUBMITTED':'DRAFT';
    db()->prepare('INSERT INTO marketplace_offers(id,enquiry_id,listing_id,asset_id,transaction_type,buyer_organization_id,seller_organization_id,created_by,status,submitted_at,expires_at) VALUES(?,?,?,?,?,?,?,?,?,IF(?="SUBMITTED",UTC_TIMESTAMP(),NULL),?)')->execute([$id,$enquiryId,$listing['id'],$listing['asset_id'],$type,$user['owner_id'],$listing['organization_id'],$user['id'],$status,$status,$values[14]]);
    db()->prepare('INSERT INTO marketplace_offer_versions(id,offer_id,version_number,proposed_by_user_id,proposed_by_organization_id,amount,currency,pricing_basis,lease_start_date,lease_end_date,project_country,project_state,project_city,intended_use,operator_requirement,mobilization_cost,security_deposit,commercial_conditions,additional_requirements,valid_until) VALUES(?,?,1,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)')->execute([$version,$id,$user['id'],$user['owner_id'],...$values]);
    if($enquiryId){$conversation=rows('SELECT id FROM marketplace_conversations WHERE enquiry_id=?',[$enquiryId])[0]['id'];db()->prepare('INSERT INTO marketplace_messages(id,conversation_id,sender_user_id,sender_organization_id,message_text,referenced_offer_id) VALUES(?,?,?,?,?,?)')->execute([uuid(),$conversation,$user['id'],$user['owner_id'],$submit?'Submitted offer version 1.':'Created draft offer version 1.',$id]);}
    if($submit)marketplace_notify_organization($listing['organization_id'],'OFFER_SUBMITTED','offer',$id,'New '.$type.' offer',$listing['title'],$user['id']);
    operation_audit($user,$submit?'marketplace.offer_submitted':'marketplace.offer_drafted','offer',$id,null,['listing_id'=>$listing['id'],'version'=>1,'type'=>$type]);return $id;
}

function marketplace_counter_offer(array $user,string $id,array $input): int
{
    $offer=marketplace_offer($user,$id,true);if(!in_array($offer['status'],['SUBMITTED','VIEWED','COUNTERED','REVISED'],true))throw new DomainException('This offer can no longer be countered.',409);
    if($offer['proposed_by_organization_id']===$user['owner_id'])throw new DomainException('Wait for the other company to respond to the current version.',409);
    if($offer['valid_until']<gmdate('Y-m-d H:i:s')){db()->prepare("UPDATE marketplace_offers SET status='EXPIRED' WHERE id=?")->execute([$id]);throw new DomainException('This offer has expired.',409);}
    $values=marketplace_offer_values($input,$offer['transaction_type']);$number=(int)$offer['current_version']+1;$version=uuid();
    db()->prepare('INSERT INTO marketplace_offer_versions(id,offer_id,version_number,proposed_by_user_id,proposed_by_organization_id,amount,currency,pricing_basis,lease_start_date,lease_end_date,project_country,project_state,project_city,intended_use,operator_requirement,mobilization_cost,security_deposit,commercial_conditions,additional_requirements,valid_until,response_to_version_id) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)')->execute([$version,$id,$number,$user['id'],$user['owner_id'],...$values,$offer['version_id']]);
    $status=$user['owner_id']===$offer['seller_organization_id']?'COUNTERED':'REVISED';db()->prepare('UPDATE marketplace_offers SET current_version=?,status=?,expires_at=? WHERE id=?')->execute([$number,$status,$values[14],$id]);
    if($offer['enquiry_id']){$conversation=rows('SELECT id FROM marketplace_conversations WHERE enquiry_id=?',[$offer['enquiry_id']])[0]['id'];db()->prepare('INSERT INTO marketplace_messages(id,conversation_id,sender_user_id,sender_organization_id,message_text,referenced_offer_id) VALUES(?,?,?,?,?,?)')->execute([uuid(),$conversation,$user['id'],$user['owner_id'],'Submitted counter offer version '.$number.'.',$id]);}
    $recipient=$user['owner_id']===$offer['buyer_organization_id']?$offer['seller_organization_id']:$offer['buyer_organization_id'];marketplace_notify_organization($recipient,'OFFER_COUNTERED','offer',$id,'Marketplace offer updated',$offer['listing_title'].' — version '.$number,$user['id']);
    operation_audit($user,'marketplace.offer_countered','offer',$id,['version'=>(int)$offer['current_version']],['version'=>$number,'status'=>$status]);return $number;
}

function marketplace_submit_draft(array $user,string $id): void
{
    $offer=marketplace_offer($user,$id,true);if($offer['buyer_organization_id']!==$user['owner_id']||$offer['status']!=='DRAFT')throw new DomainException('This draft cannot be submitted.',409);if($offer['valid_until']<gmdate('Y-m-d H:i:s'))throw new DomainException('Update the expired validity period before submitting this draft.',409);
    db()->prepare("UPDATE marketplace_offers SET status='SUBMITTED',submitted_at=UTC_TIMESTAMP() WHERE id=?")->execute([$id]);marketplace_notify_organization($offer['seller_organization_id'],'OFFER_SUBMITTED','offer',$id,'New '.$offer['transaction_type'].' offer',$offer['listing_title'],$user['id']);operation_audit($user,'marketplace.offer_submitted','offer',$id,['status'=>'DRAFT'],['status'=>'SUBMITTED','version'=>(int)$offer['current_version']]);
}

function marketplace_offer_decision(array $user,string $id,string $decision,int $confirmedVersion=0): void
{
    $offer=marketplace_offer($user,$id,true);if(!in_array($offer['status'],['SUBMITTED','VIEWED','COUNTERED','REVISED'],true))throw new DomainException('This offer has already reached a final state.',409);
    if($offer['proposed_by_organization_id']===$user['owner_id'])throw new DomainException('The company that proposed the current version cannot respond to it.',409);
    if($offer['valid_until']<gmdate('Y-m-d H:i:s')){db()->prepare("UPDATE marketplace_offers SET status='EXPIRED' WHERE id=?")->execute([$id]);throw new DomainException('This offer has expired.',409);}
    if($decision==='reject'){
        db()->prepare("UPDATE marketplace_offers SET status='REJECTED',rejected_at=UTC_TIMESTAMP() WHERE id=?")->execute([$id]);$recipient=$offer['proposed_by_organization_id'];marketplace_notify_organization($recipient,'OFFER_REJECTED','offer',$id,'Marketplace offer rejected',$offer['listing_title'],$user['id']);operation_audit($user,'marketplace.offer_rejected','offer',$id,['status'=>$offer['status']],['status'=>'REJECTED','version'=>(int)$offer['current_version']]);return;
    }
    if($confirmedVersion!==(int)$offer['current_version'])throw new DomainException('The offer changed before acceptance. Review and confirm the latest version.',409);
    rows('SELECT id FROM equipment WHERE id=? FOR UPDATE',[$offer['asset_id']]);
    if($offer['transaction_type']==='LEASE'&&rows("SELECT id FROM marketplace_reservations WHERE asset_id=? AND status IN ('HOLD','CONFIRMED') AND start_date<=? AND end_date>=? FOR UPDATE",[$offer['asset_id'],$offer['lease_end_date'],$offer['lease_start_date']]))throw new DomainException('These dates conflict with an existing reservation.',409);
    if($offer['transaction_type']==='SALE'&&rows("SELECT id FROM marketplace_transactions WHERE asset_id=? AND transaction_type='SALE' AND status='OFFER_ACCEPTED' FOR UPDATE",[$offer['asset_id']]))throw new DomainException('A purchase offer has already been accepted for this equipment.',409);
    $terms=$offer;foreach(['id','listing_title','equipment_name','buyer_name','seller_name','created_by','submitted_at','viewed_at','accepted_at','rejected_at','withdrawn_at','updated_at'] as $field)unset($terms[$field]);$transaction=uuid();
    db()->prepare('UPDATE marketplace_offers SET status="ACCEPTED",accepted_version_id=?,accepted_at=UTC_TIMESTAMP() WHERE id=?')->execute([$offer['version_id'],$id]);
    db()->prepare('INSERT INTO marketplace_transactions(id,transaction_type,listing_id,asset_id,accepted_offer_id,accepted_offer_version_id,supplier_organization_id,customer_organization_id,gross_value,currency,frozen_terms) VALUES(?,?,?,?,?,?,?,?,?,?,?)')->execute([$transaction,$offer['transaction_type'],$offer['listing_id'],$offer['asset_id'],$id,$offer['version_id'],$offer['seller_organization_id'],$offer['buyer_organization_id'],$offer['amount'],$offer['currency'],json_encode($terms,JSON_THROW_ON_ERROR)]);
    if($offer['transaction_type']==='LEASE')db()->prepare("INSERT INTO marketplace_reservations(id,asset_id,offer_id,offer_version_id,start_date,end_date,status) VALUES(?,?,?,?,?,?,'CONFIRMED')")->execute([uuid(),$offer['asset_id'],$id,$offer['version_id'],$offer['lease_start_date'],$offer['lease_end_date']]);
    db()->prepare("UPDATE marketplace_listings SET listing_status='RESERVED',marketplace_status='RESERVED' WHERE id=?")->execute([$offer['listing_id']]);
    marketplace_notify_organization($offer['proposed_by_organization_id'],'OFFER_ACCEPTED','offer',$id,'Marketplace offer accepted',$offer['listing_title'].' — version '.$offer['current_version'],$user['id']);operation_audit($user,'marketplace.offer_accepted','offer',$id,['status'=>$offer['status']],['status'=>'ACCEPTED','version'=>(int)$offer['current_version'],'transaction_id'=>$transaction]);
}

function marketplace_withdraw_offer(array $user,string $id): void
{
    $offer=marketplace_offer($user,$id,true);if($offer['buyer_organization_id']!==$user['owner_id'])throw new DomainException('Only the buyer company can withdraw this offer.',403);if(!in_array($offer['status'],['DRAFT','SUBMITTED','VIEWED','COUNTERED','REVISED'],true))throw new DomainException('This offer cannot be withdrawn.',409);
    db()->prepare("UPDATE marketplace_offers SET status='WITHDRAWN',withdrawn_at=UTC_TIMESTAMP() WHERE id=?")->execute([$id]);marketplace_notify_organization($offer['seller_organization_id'],'OFFER_WITHDRAWN','offer',$id,'Marketplace offer withdrawn',$offer['listing_title'],$user['id']);operation_audit($user,'marketplace.offer_withdrawn','offer',$id,['status'=>$offer['status']],['status'=>'WITHDRAWN']);
}

function marketplace_mark_offer_viewed(array $user,array $offer): void
{
    if($offer['status']==='SUBMITTED'&&$offer['proposed_by_organization_id']!==$user['owner_id'])db()->prepare("UPDATE marketplace_offers SET status='VIEWED',viewed_at=COALESCE(viewed_at,UTC_TIMESTAMP()) WHERE id=? AND status='SUBMITTED'")->execute([$offer['id']]);
}
