<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
require dirname(__DIR__).'/app/equipment-catalog.php';
try {
    $count=0;
    foreach(rows('SELECT * FROM users WHERE is_active=1 AND (is_email_verified=1 OR ?=1)', [(int)development_verification_bypass()]) as $user) {
        dashboard_state($user,['site'=>'','unit'=>''],true);
        $count++;
    }
    db()->exec('DELETE FROM dashboard_cache WHERE captured_at<DATE_SUB(UTC_TIMESTAMP(),INTERVAL 2 DAY)');
    db()->exec('DELETE FROM dashboard_history WHERE snapshot_date<DATE_SUB(UTC_DATE(),INTERVAL 35 DAY)');
    db()->exec('DELETE FROM dashboard_events WHERE occurred_at<DATE_SUB(UTC_TIMESTAMP(),INTERVAL 7 DAY)');
    foreach(rows('SELECT equipment_id FROM equipment_depreciation_profile WHERE is_active=1') as $profile){
        $values=equipment_refresh_values($profile['equipment_id']);
        if($values['calculation'])db()->prepare("INSERT INTO equipment_value_snapshot(id,equipment_id,as_of_date,value_type,amount,currency,source_type,source_detail) SELECT ?,?,UTC_DATE(),'BOOK',?,?,'SYSTEM','Scheduled straight-line calculation' WHERE NOT EXISTS(SELECT 1 FROM equipment_value_snapshot WHERE equipment_id=? AND as_of_date=UTC_DATE() AND value_type='BOOK' AND source_type='SYSTEM')")->execute([uuid(),$profile['equipment_id'],$values['calculation']['book_value'],$values['currency'],$profile['equipment_id']]);
    }
    echo "Refreshed {$count} user scopes; overdue alerts and daily trends updated.\n";
}catch(Throwable $error){fwrite(STDERR,'Dashboard refresh failed: '.$error->getMessage()."\n");exit(1);}
