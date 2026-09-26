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
<section class="equipment-photo-manager registration-photo-manager full" data-registration-photos><div class="photo-manager-heading"><div><h3>Equipment photos</h3><p>Add one or more JPEG, PNG, or WebP photos. Maximum 2 MB per photo.</p></div><span data-photo-count>0 photos</span></div><div class="photo-manager-grid" data-photo-preview><label class="photo-add-card"><input type="file" name="photos[]" accept="image/jpeg,image/png,image/webp" multiple><span class="photo-add-icon" aria-hidden="true">+</span><strong>Add photos</strong><small>Select multiple files</small></label></div><input type="hidden" name="primary_photo_index" value="0" data-primary-photo-index><p class="photo-manager-help">The first selected photo is primary by default. Select another preview to change it before registration.</p></section><div class="full"><button class="primary">Register equipment</button> <a href="equipment">Cancel</a></div></form></section>
 </div>
<script src="assets/js/equipment-catalog.js?v=<?= filemtime(APP_ROOT.'/assets/js/equipment-catalog.js') ?>" defer></script>
<script src="assets/js/equipment-photos.js?v=<?= filemtime(APP_ROOT.'/assets/js/equipment-photos.js') ?>" defer></script>
<?php require APP_ROOT.'/includes/operations-footer.php';?>
