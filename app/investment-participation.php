<?php
declare(strict_types=1);
require_once __DIR__.'/investment.php';

function investable_opportunity(string $id):array
{
    $row=rows("SELECT o.*,own.name organization_name,t.name equipment_type_name,c.name category_name,tp.oem_name,tp.model_name,tp.equipment_condition,tp.specification,ap.supplier_name,ap.landed_cost,(SELECT COALESCE(SUM(cc.amount),0) FROM investment_capital_contributions cc WHERE cc.opportunity_id=o.id AND cc.status='CONFIRMED') confirmed_funding FROM investment_opportunities o JOIN owners own ON own.id=o.organization_id LEFT JOIN equipment_types t ON t.id=o.equipment_type_id LEFT JOIN equipment_categories c ON c.id=o.equipment_category_id LEFT JOIN investment_technical_profiles tp ON tp.opportunity_id=o.id LEFT JOIN investment_acquisition_profiles ap ON ap.opportunity_id=o.id WHERE o.id=? AND o.status='PUBLISHED' AND o.publication_mode='INVESTABLE' LIMIT 1",[$id])[0]??null;
    if(!$row)throw new DomainException('Investment opportunity not found or not open to investors.',404);
    return $row;
}
function investor_profile(array $user):array
{
    return rows('SELECT * FROM investment_investor_profiles WHERE user_id=?',[$user['id']])[0]??['user_id'=>$user['id'],'organization_id'=>$user['owner_id'],'investor_type'=>'INDIVIDUAL','legal_name'=>$user['full_name'],'identity_reference'=>'','tax_reference'=>'','address'=>'','country'=>'','bank_details'=>null,'kyc_status'=>'NOT_STARTED','risk_acknowledged_at'=>null,'title_acknowledged_at'=>null];
}
function investor_profile_save(array $user,array $input):void
{
    $type=in_array($input['investor_type']??'', ['INDIVIDUAL','ORGANIZATION'],true)?$input['investor_type']:'INDIVIDUAL';
    $legal=investment_text($input,'legal_name',255);if($legal==='')throw new DomainException('Enter the investor legal name.',422);
    if(empty($input['risk_acknowledgement'])||empty($input['title_acknowledgement']))throw new DomainException('Accept the risk and economic-participation acknowledgements.',422);
    $bank=['bank_name'=>investment_text($input,'bank_name',255),'account_name'=>investment_text($input,'account_name',255),'account_number'=>investment_text($input,'account_number',100)];
    db()->prepare("INSERT INTO investment_investor_profiles(user_id,organization_id,investor_type,legal_name,identity_reference,tax_reference,address,country,bank_details,kyc_status,risk_acknowledged_at,title_acknowledged_at) VALUES(?,?,?,?,?,?,?,?,?,'PENDING',UTC_TIMESTAMP(),UTC_TIMESTAMP()) ON DUPLICATE KEY UPDATE organization_id=VALUES(organization_id),investor_type=VALUES(investor_type),legal_name=VALUES(legal_name),identity_reference=VALUES(identity_reference),tax_reference=VALUES(tax_reference),address=VALUES(address),country=VALUES(country),bank_details=VALUES(bank_details),kyc_status=IF(kyc_status='VERIFIED','VERIFIED','PENDING'),risk_acknowledged_at=UTC_TIMESTAMP(),title_acknowledged_at=UTC_TIMESTAMP()")
      ->execute([$user['id'],$user['owner_id'],$type,$legal,investment_text($input,'identity_reference',150),investment_text($input,'tax_reference',150),investment_text($input,'address'),investment_text($input,'country',100),json_encode($bank,JSON_THROW_ON_ERROR)]);
}
function investment_terms_snapshot(array $op,float $amount):array
{
    return ['opportunity_id'=>$op['id'],'opportunity_version'=>(int)$op['current_version'],'title'=>$op['title'],'amount'=>$amount,'currency'=>$op['currency'],'target_funding_amount'=>(float)$op['target_funding_amount'],'asset_useful_life_days'=>(int)$op['asset_useful_life_days'],'maximum_term_days'=>(int)$op['maximum_term_days'],'minimum_term_days'=>(int)$op['minimum_term_days'],'target_term_days'=>(int)$op['target_term_days'],'target_term_purpose'=>'Official agreement term for the planned asset sale','ownership_vehicle'=>$op['ownership_vehicle'],'finance_security_partner'=>$op['finance_security_partner'],'controlled_account_details'=>$op['controlled_account_details'],'recovery_rights'=>$op['recovery_rights'],'distribution_waterfall'=>$op['distribution_waterfall'],'primary_risks'=>$op['primary_risks'],'legal_title_conferred'=>false,'captured_at'=>gmdate('c')];
}
function investment_participate(array $user,string $opportunityId,array $input):string
{
    $op=investable_opportunity($opportunityId);$profile=investor_profile($user);
    if($profile['kyc_status']!=='VERIFIED')throw new DomainException('Investor KYC must be verified before submitting capital.',403);
    if($op['funding_status']!=='OPEN')throw new DomainException('Funding is not currently open.',409);
    if($op['funding_closes_at']&&strtotime($op['funding_closes_at'])<time())throw new DomainException('The funding window has closed.',409);
    $amount=investment_number($input,'amount');$minimum=(float)($op['minimum_participation']??0);if($amount<=0||$amount<$minimum)throw new DomainException('Enter an amount at or above the minimum participation.',422);
    $committed=(float)rows("SELECT COALESCE(SUM(amount),0) total FROM investment_capital_contributions WHERE opportunity_id=? AND status IN ('PENDING_CONFIRMATION','CONFIRMED')",[$opportunityId])[0]['total'];if($committed+$amount>(float)$op['target_funding_amount'])throw new DomainException('This amount exceeds the remaining funding capacity.',422);
    if(empty($input['projection_acknowledgement'])||empty($input['risk_acknowledgement'])||empty($input['title_acknowledgement']))throw new DomainException('Accept all participation acknowledgements.',422);
    $signature=investment_text($input,'signature_name',255);if($signature==='')throw new DomainException('Enter the signing name.',422);
    if(rows("SELECT 1 FROM investment_capital_contributions WHERE opportunity_id=? AND investor_user_id=? AND status IN ('INITIATED','PENDING_CONFIRMATION','CONFIRMED') LIMIT 1",[$opportunityId,$user['id']]))throw new DomainException('You already have an active contribution for this opportunity.',409);
    $pdo=db();$pdo->beginTransaction();try{
        $agreement=rows('SELECT * FROM investment_agreements WHERE opportunity_id=? AND investor_user_id=? FOR UPDATE',[$opportunityId,$user['id']])[0]??null;$terms=investment_terms_snapshot($op,$amount);
        $body='Equipment Investment Participation Agreement. The target term recorded in the attached terms snapshot is the official agreement term for when the asset is to be sold. The maximum term is automatically limited to the recorded asset useful life. Economic participation only; this agreement does not transfer legal title to the physical equipment. Returns are projections and are not guaranteed.';
        if($agreement){$agreementId=$agreement['id'];db()->prepare("UPDATE investment_agreements SET body_snapshot=?,terms_snapshot=?,status='SIGNED',accepted_at=UTC_TIMESTAMP(),signed_at=UTC_TIMESTAMP(),signature_name=? WHERE id=?")->execute([$body,json_encode($terms,JSON_THROW_ON_ERROR),$signature,$agreementId]);}
        else{$agreementId=uuid();db()->prepare("INSERT INTO investment_agreements(id,opportunity_id,investor_user_id,body_snapshot,terms_snapshot,status,accepted_at,signed_at,signature_name) VALUES(?,?,?,?,?,'SIGNED',UTC_TIMESTAMP(),UTC_TIMESTAMP(),?)")->execute([$agreementId,$opportunityId,$user['id'],$body,json_encode($terms,JSON_THROW_ON_ERROR),$signature]);}
        $id=uuid();db()->prepare("INSERT INTO investment_capital_contributions(id,opportunity_id,investor_user_id,agreement_id,amount,currency,status,investor_reference,submitted_at) VALUES(?,?,?,?,?,?,'PENDING_CONFIRMATION',?,UTC_TIMESTAMP())")->execute([$id,$opportunityId,$user['id'],$agreementId,$amount,$op['currency'],investment_text($input,'investor_reference',255)]);
        db()->prepare("INSERT INTO investment_funding_events(opportunity_id,actor_user_id,event_type,contribution_id,event_data) VALUES(?,?,'CONTRIBUTION_SUBMITTED',?,?)")->execute([$opportunityId,$user['id'],$id,json_encode(['amount'=>$amount,'currency'=>$op['currency']],JSON_THROW_ON_ERROR)]);
        $pdo->commit();return $id;
    }catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();throw $e;}
}
function investment_confirm_contribution(array $user,string $id,string $providerReference):void
{
    if(!investment_can_review($user))throw new DomainException('Platform finance authorization required.',403);
    $pdo=db();$pdo->beginTransaction();try{
        $contribution=rows("SELECT c.*,o.target_funding_amount FROM investment_capital_contributions c JOIN investment_opportunities o ON o.id=c.opportunity_id WHERE c.id=? FOR UPDATE",[$id])[0]??null;if(!$contribution||$contribution['status']!=='PENDING_CONFIRMATION')throw new DomainException('Pending contribution not found.',404);
        $confirmedBefore=(float)rows("SELECT COALESCE(SUM(amount),0) total FROM investment_capital_contributions WHERE opportunity_id=? AND status='CONFIRMED'",[$contribution['opportunity_id']])[0]['total'];if($confirmedBefore+(float)$contribution['amount']>(float)$contribution['target_funding_amount'])throw new DomainException('Confirming this contribution would exceed the funding target.',409);
        db()->prepare("UPDATE investment_capital_contributions SET status='CONFIRMED',provider_reference=?,confirmed_at=UTC_TIMESTAMP(),confirmed_by=? WHERE id=?")->execute([$providerReference,$user['id'],$id]);
        $confirmed=$confirmedBefore+(float)$contribution['amount'];$share=(float)$contribution['target_funding_amount']>0?((float)$contribution['amount']/(float)$contribution['target_funding_amount'])*100:0;
        db()->prepare("INSERT INTO investment_participations(id,opportunity_id,investor_user_id,contribution_id,participation_amount,currency,economic_share_percent,legal_title_conferred) VALUES(?,?,?,?,?,?,?,0)")->execute([uuid(),$contribution['opportunity_id'],$contribution['investor_user_id'],$id,$contribution['amount'],$contribution['currency'],$share]);
        if($confirmed>=(float)$contribution['target_funding_amount'])db()->prepare("UPDATE investment_opportunities SET funding_status='TARGET_REACHED' WHERE id=? AND funding_status='OPEN'")->execute([$contribution['opportunity_id']]);
        db()->prepare("INSERT INTO investment_funding_events(opportunity_id,actor_user_id,event_type,contribution_id,event_data) VALUES(?,?,'CONTRIBUTION_CONFIRMED',?,?)")->execute([$contribution['opportunity_id'],$user['id'],$id,json_encode(['confirmed_total'=>$confirmed],JSON_THROW_ON_ERROR)]);
        $pdo->commit();
    }catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();throw $e;}
}
