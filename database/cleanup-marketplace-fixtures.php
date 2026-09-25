<?php
declare(strict_types=1);if(PHP_SAPI!=='cli')exit;require dirname(__DIR__).'/app/operations.php';
$pdo=db();$fixtures=rows("SELECT id,name FROM owners WHERE name IN ('Marketplace test supplier','Marketplace test buyer')");
foreach($fixtures as $fixture){$owner=$fixture['id'];$pdo->beginTransaction();try{
 $pdo->prepare('DELETE m FROM marketplace_listing_media m JOIN marketplace_listings l ON l.id=m.listing_id WHERE l.organization_id=?')->execute([$owner]);
 $pdo->prepare('DELETE lt FROM marketplace_lease_terms lt JOIN marketplace_listings l ON l.id=lt.listing_id WHERE l.organization_id=?')->execute([$owner]);
 $pdo->prepare('DELETE st FROM marketplace_sale_terms st JOIN marketplace_listings l ON l.id=st.listing_id WHERE l.organization_id=?')->execute([$owner]);
 $pdo->prepare('DELETE FROM marketplace_listings WHERE organization_id=?')->execute([$owner]);$pdo->prepare('DELETE FROM equipment WHERE owner_id=?')->execute([$owner]);
 foreach(['audit_logs','dashboard_events','equipment_statuses'] as $table)$pdo->prepare("DELETE FROM {$table} WHERE owner_id=?")->execute([$owner]);
 $pdo->prepare('DELETE FROM marketplace_oem_models WHERE oem_id IN (SELECT id FROM marketplace_oems WHERE suggested_by_owner_id=?)')->execute([$owner]);$pdo->prepare('DELETE FROM marketplace_oems WHERE suggested_by_owner_id=?')->execute([$owner]);
 foreach(['equipment_types','equipment_subcategories','equipment_categories'] as $table)$pdo->prepare("DELETE FROM {$table} WHERE owner_id=?")->execute([$owner]);
 $pdo->prepare('DELETE FROM owners WHERE id=? AND name=?')->execute([$owner,$fixture['name']]);$pdo->commit();echo 'Removed '.$fixture['name'].PHP_EOL;
}catch(Throwable $error){$pdo->rollBack();throw $error;}}
