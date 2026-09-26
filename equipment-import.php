<?php
declare(strict_types=1);
require __DIR__ . '/app/equipment-catalog.php';
$user = require_verified();
$active = 'equipment';
$pageTitle = 'Import equipment';
equipment_seed_statuses($user['owner_id']);
$catalog = equipment_catalog($user);
$locations = operation_locations($user);
require APP_ROOT . '/includes/operations-header.php';
?>
<section class="page-heading"><div><p class="eyebrow">EQUIPMENT / IMPORT</p><h1>Import equipment</h1><p>Add multiple equipment records from a CSV or Excel file.</p></div><a class="secondary" href="equipment">Back to equipment</a></section>
<?php if (!operation_can_manage($user)): ?>
<section class="panel"><div class="detail-content"><h2>Read-only access</h2><p>Your role cannot import equipment. Contact an administrator to request access.</p></div></section>
<?php else: ?>
<section class="panel form-panel"><h2>Upload spreadsheet</h2><p>Every imported record is assigned to the selected unit. Import one location at a time.</p><form action="operations-action" method="post" enctype="multipart/form-data" class="form-grid"><?= csrf_field() ?><input type="hidden" name="action" value="equipment.import"><label class="full">Spreadsheet (.csv or .xlsx, up to 5 MB)<input type="file" name="equipment_file" accept=".csv,.xlsx,text/csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required></label><label class="full">Assigned unit<select name="unit_id" <?= $user['role']==='OWNER_ADMIN'?'':'required' ?>><option value=""><?= $user['role']==='OWNER_ADMIN'?'Use / create Default location':'Choose an assigned unit' ?></option><?php foreach($locations as $location): ?><option value="<?= e($location['unit_id']) ?>"><?= e($location['site_name'].' / '.$location['unit_name']) ?></option><?php endforeach; ?></select></label><?php $bulk=true;$prefix='bulk';require APP_ROOT.'/includes/equipment-fields.php';?><p class="full"><strong>Required headers:</strong> Company Serial Number, Equipment, Serial Number, Equipment ID, Owner, Condition / Remarks.<br>Owner in the spreadsheet is a descriptive label; records always belong to your signed-in company.<br><a href="assets/templates/equipment-import.csv" download>Download CSV template</a> · <a href="Nonagon%20Equipment%20Import%20Guide.md">Read the import guide</a></p><div class="full"><button class="primary">Import equipment</button></div><?php if (!$locations && $user['role']!=='OWNER_ADMIN'): ?><p class="full">No units are assigned to your account. Contact your administrator.</p><?php endif; ?></form></section>
<?php endif; ?>
<?php require APP_ROOT . '/includes/operations-footer.php'; ?>
