<?php
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
require dirname(__DIR__).'/app/operations.php';
$fixtures=rows("SELECT id FROM owners WHERE name='Dashboard test' AND email=CONCAT('owner-',id,'@example.invalid')");
db()->beginTransaction();
try{
foreach($fixtures as $fixture){$id=$fixture['id'];
    db()->prepare('DELETE a FROM alerts a JOIN equipment e ON e.id=a.equipment_id WHERE e.owner_id=?')->execute([$id]);
    db()->prepare('DELETE m FROM maintenance_records m JOIN equipment e ON e.id=m.equipment_id WHERE e.owner_id=?')->execute([$id]);
    db()->prepare('DELETE FROM equipment WHERE owner_id=?')->execute([$id]);
    db()->prepare('DELETE FROM equipment_statuses WHERE owner_id=?')->execute([$id]);
    db()->prepare('DELETE FROM audit_logs WHERE owner_id=?')->execute([$id]);
    db()->prepare('DELETE FROM dashboard_events WHERE owner_id=?')->execute([$id]);
    db()->prepare('DELETE us FROM user_scopes us JOIN users u ON u.id=us.user_id WHERE u.owner_id=?')->execute([$id]);
    db()->prepare('DELETE FROM owners WHERE id=?')->execute([$id]);
}
db()->commit();echo 'Removed '.count($fixtures)." abandoned dashboard test organizations.\n";
}catch(Throwable $e){db()->rollBack();throw $e;}
