<?php
declare(strict_types=1);
require __DIR__.'/equipment-catalog.php';
$user=require_verified();if(!operation_can_manage($user)){http_response_code(403);exit('Read-only access.');}
equipment_seed_statuses($user['owner_id']);$catalog=equipment_catalog($user);$locations=operation_locations($user);
$active='equipment';$pageTitle='Register equipment';require APP_ROOT.'/includes/operations-header.php';
?>
<section class="page-heading"><div><p class="eyebrow">EQUIPMENT REGISTER</p><h1>Register equipment</h1><p>Add one asset and its optional classification details.</p></div><div class="page-actions"><a class="secondary" href="equipment-settings">Equipment Settings</a></div></section>
<div class="equipment-register-grid"><section class="panel form-panel"><h2>Add an asset</h2><form action="operations-action" method="post" enctype="multipart/form-data" class="form-grid"><?= csrf_field() ?><input type="hidden" name="action" value="equipment.create"><label>Equipment name<input name="name" maxlength="255" required></label><label>Asset ID (AID)<input name="asset_code" maxlength="100" required><small>Your company's unique equipment ID.</small></label><label class="full">Location<select name="unit_id" <?= $user['role']==='OWNER_ADMIN'?'':'required' ?>><option value=""><?= $user['role']==='OWNER_ADMIN'?'Use / create Default location':'Choose an assigned unit' ?></option><?php foreach($locations as $l):?><option value="<?= e($l['unit_id']) ?>"><?= e($l['site_name'].' / '.$l['unit_name']) ?></option><?php endforeach;?></select></label>
<?php $values=[];$bulk=false;$prefix='single';require APP_ROOT.'/includes/equipment-fields.php';?>
<label>Equipment photo <small>Optional · JPEG / PNG / WebP · 2 MB max</small><input type="file" name="photo" accept="image/jpeg,image/png,image/webp"></label><label>Photo caption<input name="caption" maxlength="255"></label><label class="check-label full"><input type="checkbox" name="make_primary" value="1"> Use this uploaded photo as the primary photo</label><p class="full">Without a primary photo, equipment inherits its subcategory artwork.</p><div class="full"><button class="primary">Register equipment</button> <a href="equipment">Cancel</a></div></form></section>
 </div>
<script src="assets/js/equipment-catalog.js?v=<?= filemtime(APP_ROOT.'/assets/js/equipment-catalog.js') ?>" defer></script>
<?php require APP_ROOT.'/includes/operations-footer.php';?>
