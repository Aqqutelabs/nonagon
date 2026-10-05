<?php
declare(strict_types=1);
require __DIR__.'/../app/investment-participation.php';

$promoter=rows("SELECT u.*,o.name company_name FROM users u JOIN owners o ON o.id=u.owner_id WHERE u.is_email_verified=1 AND LOWER(o.name) LIKE '%ofissa%' LIMIT 1")[0];
$investor=rows("SELECT u.*,o.name company_name FROM users u JOIN owners o ON o.id=u.owner_id WHERE u.is_email_verified=1 AND LOWER(o.name)='marketplace test buyer' LIMIT 1")[0];
$reviewer=rows("SELECT u.*,o.name company_name FROM users u JOIN owners o ON o.id=u.owner_id WHERE u.is_email_verified=1 AND LOWER(o.name)='commercial test ltd' LIMIT 1")[0];
$opportunityId='';$createdProfile=false;$createdReviewerPromoter=false;
try{
    $opportunityId=investment_save($promoter,['title'=>'Sprint 2 smoke-test opportunity','source_type'=>'PROMOTER','quantity'=>'1','target_funding_amount'=>'1000','currency'=>'NGN','asset_useful_life_days'=>'1825','minimum_term_days'=>'90','target_term_days'=>'365','opportunity_rationale'=>'Test','specification'=>'Test','supplier_name'=>'Test supplier','ownership_vehicle'=>'Equipment.ng vehicle','finance_security_partner'=>'Test security partner','controlled_account_details'=>'Controlled','recovery_rights'=>'Recovery','distribution_waterfall'=>'Waterfall','primary_risks'=>'Risk','downside_assumptions'=>'Down','base_assumptions'=>'Base','upside_assumptions'=>'Up']);
    db()->prepare("UPDATE investment_opportunities SET status='PUBLISHED',publication_mode='INVESTABLE',funding_status='OPEN',minimum_participation=100 WHERE id=?")->execute([$opportunityId]);
    $createdProfile=!rows('SELECT 1 FROM investment_investor_profiles WHERE user_id=?',[$investor['id']]);
    db()->prepare("INSERT INTO investment_investor_profiles(user_id,organization_id,legal_name,kyc_status,risk_acknowledged_at,title_acknowledged_at) VALUES(?,?,?,'VERIFIED',UTC_TIMESTAMP(),UTC_TIMESTAMP()) ON DUPLICATE KEY UPDATE kyc_status='VERIFIED'")->execute([$investor['id'],$investor['owner_id'],$investor['full_name']]);
    $contributionId=investment_participate($investor,$opportunityId,['amount'=>'500','signature_name'=>$investor['full_name'],'projection_acknowledgement'=>'1','risk_acknowledgement'=>'1','title_acknowledgement'=>'1']);
    $pendingTotal=(float)rows("SELECT COALESCE(SUM(amount),0) total FROM investment_capital_contributions WHERE opportunity_id=? AND status='CONFIRMED'",[$opportunityId])[0]['total'];if($pendingTotal!==0.0)throw new RuntimeException('Pending capital incorrectly changed funded progress.');
    $existing=rows('SELECT 1 FROM investment_promoters WHERE organization_id=?',[$reviewer['owner_id']]);if(!$existing){$createdReviewerPromoter=true;db()->prepare("INSERT INTO investment_promoters(id,organization_id,promoter_type,status,approved_stage,approved_by,approved_at) VALUES(?,?,'INTERNAL','VERIFIED',1,?,UTC_TIMESTAMP())")->execute([uuid(),$reviewer['owner_id'],$reviewer['id']]);}
    investment_confirm_contribution($reviewer,$contributionId,'SMOKE-'.bin2hex(random_bytes(5)));
    $record=rows('SELECT c.status,p.participation_amount,p.legal_title_conferred FROM investment_capital_contributions c JOIN investment_participations p ON p.contribution_id=c.id WHERE c.id=?',[$contributionId])[0]??null;
    if(!$record||$record['status']!=='CONFIRMED'||(float)$record['participation_amount']!==500.0||(int)$record['legal_title_conferred']!==0)throw new RuntimeException('Confirmed participation invariant failed.');
    echo "Sprint 2 smoke test passed: pending capital excluded; confirmation created economic participation without legal title.\n";
}finally{
    if($opportunityId){db()->prepare('DELETE FROM investment_participations WHERE opportunity_id=?')->execute([$opportunityId]);db()->prepare('DELETE FROM investment_capital_contributions WHERE opportunity_id=?')->execute([$opportunityId]);db()->prepare('DELETE FROM investment_opportunities WHERE id=?')->execute([$opportunityId]);}
    if($createdProfile)db()->prepare('DELETE FROM investment_investor_profiles WHERE user_id=?')->execute([$investor['id']]);
    if($createdReviewerPromoter)db()->prepare('DELETE FROM investment_promoters WHERE organization_id=?')->execute([$reviewer['owner_id']]);
}
