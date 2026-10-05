<?php
declare(strict_types=1);
require __DIR__.'/app/operations.php';
header('Cache-Control: no-store');
header('X-Robots-Tag: noindex, nofollow');
$token=trim((string)($_GET['token']??''));
$record=null;
if(preg_match('/^[a-f0-9]{64}$/',$token)){
    $record=rows("SELECT e.name,e.asset_code,e.serial_no,e.status,e.short_description,e.long_description,e.manufacture_year,o.name company_name,c.name category_name,sc.name subcategory_name,t.name type_name,b.name brand_name,m.name model_name FROM equipment e JOIN owners o ON o.id=e.owner_id LEFT JOIN equipment_categories c ON c.id=e.category_id LEFT JOIN equipment_subcategories sc ON sc.id=e.subcategory_id LEFT JOIN equipment_types t ON t.id=e.type_id LEFT JOIN equipment_brands b ON b.id=e.brand_id LEFT JOIN equipment_models m ON m.id=e.model_id WHERE e.public_qr_token=? AND e.archived_at IS NULL",[$token])[0]??null;
}
if(!$record)http_response_code(404);
$statusLabels=['OPERATIONAL'=>'Operational','MAINTENANCE'=>'Under maintenance','DOWN'=>'Down'];
$statusLabel=$record?($statusLabels[$record['status']]??'Status unavailable'):'';
$statusClass=$record?strtolower((string)$record['status']):'';
$classification=$record?array_filter([
    'Category'=>$record['category_name'],
    'Subcategory'=>$record['subcategory_name'],
    'Type'=>$record['type_name'],
    'Brand'=>$record['brand_name'],
    'Model'=>$record['model_name'],
    'Year'=>$record['manufacture_year'],
],static fn($value)=>$value!==null&&$value!==''):[];
?><!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= $record?e($record['name'].' · Equipment · Nonagon'):'Equipment unavailable · Nonagon' ?></title>
    <link rel="stylesheet" href="assets/css/xinng-qr.css">
</head>
<body class="equipment-public-page">
    <main class="equipment-public-content">
        <header class="equipment-public-header">
            <span class="equipment-public-brand">NONAGON</span>
            <span class="equipment-public-label">Public equipment profile</span>
        </header>
        <?php if(!$record):?>
            <section class="equipment-public-empty" aria-labelledby="equipment-public-title">
                <h1 id="equipment-public-title">Equipment record unavailable</h1>
                <p>This QR code is invalid, or the equipment is no longer active.</p>
            </section>
        <?php else:?>
            <section class="equipment-public-heading" aria-labelledby="equipment-public-title">
                <p class="equipment-public-company"><?= e($record['company_name']?:'Equipment owner') ?></p>
                <h1 id="equipment-public-title"><?= e($record['name']) ?></h1>
                <p class="equipment-public-id">Asset ID <strong><?= e($record['asset_code']) ?></strong></p>
            </section>
            <section class="equipment-public-status status-<?= e($statusClass) ?>" aria-label="Operational status">
                <span class="equipment-public-status-dot" aria-hidden="true"></span>
                <div><span>Operational status</span><strong><?= e($statusLabel) ?></strong></div>
            </section>
            <section class="equipment-public-section" aria-labelledby="equipment-identification-title">
                <h2 id="equipment-identification-title">Identification</h2>
                <dl class="equipment-public-facts">
                    <div><dt>Serial number</dt><dd><?= e($record['serial_no']?:'Not recorded') ?></dd></div>
                    <?php foreach($classification as $label=>$value):?><div><dt><?= e($label) ?></dt><dd><?= e((string)$value) ?></dd></div><?php endforeach;?>
                </dl>
            </section>
            <?php if(trim((string)($record['short_description']??''))!==''||trim((string)($record['long_description']??''))!==''):?>
                <section class="equipment-public-section equipment-public-description" aria-labelledby="equipment-description-title">
                    <h2 id="equipment-description-title">Description</h2>
                    <?php if(trim((string)($record['short_description']??''))!==''):?><p class="equipment-public-summary"><?= e($record['short_description']) ?></p><?php endif;?>
                    <?php if(trim((string)($record['long_description']??''))!==''):?><p><?= nl2br(e($record['long_description'])) ?></p><?php endif;?>
                </section>
            <?php endif;?>
            <footer class="equipment-public-footer">Public equipment information · Internal operational details are not shown.</footer>
        <?php endif;?>
    </main>
</body>
</html>