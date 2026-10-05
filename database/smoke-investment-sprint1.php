<?php
declare(strict_types=1);

require __DIR__ . '/../app/investment.php';

$user = rows("SELECT u.*, o.name company_name FROM users u JOIN owners o ON o.id=u.owner_id WHERE u.is_email_verified=1 AND LOWER(o.name) LIKE '%ofissa%' ORDER BY u.created_at LIMIT 1")[0] ?? null;
if (!$user) throw new RuntimeException('No verified OFISSA user is available for the Sprint 1 smoke test.');

$opportunityId = '';
try {
    if (!investment_can_create($user)) throw new RuntimeException('Stage 1 OFISSA promoter access was not recognized.');
    foreach ([['asset_useful_life_days'=>'365','minimum_term_days'=>'89','target_term_days'=>'180'],['asset_useful_life_days'=>'180','minimum_term_days'=>'90','target_term_days'=>'181']] as $invalidTerms) {
        try { investment_save($user,['title'=>'Invalid term smoke test','currency'=>'NGN',...$invalidTerms]); throw new RuntimeException('Invalid investment terms were accepted.'); }
        catch (DomainException $expected) { if ($expected->getCode() !== 422) throw $expected; }
    }
    $opportunityId = investment_save($user, [
        'title' => 'Sprint 1 smoke-test opportunity',
        'source_type' => 'PROMOTER',
        'industry' => 'Testing',
        'quantity' => '1',
        'target_geography' => 'Nigeria',
        'opportunity_rationale' => 'Validate the controlled opportunity workflow.',
        'target_funding_amount' => '1000000',
        'currency' => 'NGN',
        'asset_useful_life_days' => '1825',
        'minimum_term_days' => '90',
        'target_term_days' => '365',
        'investment_structure' => 'Economic participation only',
        'specification' => 'Test equipment specification',
        'supplier_name' => 'Test supplier',
        'ownership_vehicle' => 'Equipment.ng controlled vehicle',
        'finance_security_partner' => 'Test finance partner',
        'controlled_account_details' => 'Controlled account required before funding.',
        'recovery_rights' => 'Recovery, refinance and liquidation rights documented.',
        'distribution_waterfall' => 'Costs, secured obligations, liabilities, investors, residual.',
        'primary_risks' => 'Demand, utilization and equipment risks.',
        'downside_assumptions' => 'Lower utilization',
        'base_assumptions' => 'Expected utilization',
        'upside_assumptions' => 'Higher utilization',
    ]);
    $counts = rows('SELECT (SELECT COUNT(*) FROM investment_opportunity_versions WHERE opportunity_id=?) versions,(SELECT COUNT(*) FROM investment_financial_scenarios WHERE opportunity_id=?) scenarios,(SELECT COUNT(*) FROM equipment WHERE id=?) physical_assets', [$opportunityId,$opportunityId,$opportunityId])[0];
    if ((int)$counts['versions'] !== 1 || (int)$counts['scenarios'] !== 3 || (int)$counts['physical_assets'] !== 0) throw new RuntimeException('Opportunity persistence or Asset separation failed.');
    try { investment_transition($user,$opportunityId,'SUBMITTED'); throw new RuntimeException('Submission incorrectly succeeded without verified demand.'); }
    catch (DomainException $expected) { if ($expected->getCode() !== 422) throw $expected; }
    echo "Sprint 1 smoke test passed: versioning, three scenarios, demand gate and Asset separation.\n";
} finally {
    if ($opportunityId !== '') db()->prepare('DELETE FROM investment_opportunities WHERE id=?')->execute([$opportunityId]);
}
