<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
require dirname(__DIR__).'/app/qhse.php';
$owner=uuid();$user=uuid();$control=uuid();$event=uuid();$checks=0;$failed=false;
function qhse_test(bool $condition,string $message):void{global $checks;if(!$condition)throw new RuntimeException($message);$checks++;}
try{
 db()->beginTransaction();
 db()->prepare("INSERT INTO owners(id,name,email,phone) VALUES(?,?,?,'0')")->execute([$owner,'QHSE Fixture',$owner.'@example.invalid']);
 db()->prepare("INSERT INTO users(id,owner_id,full_name,email,phone,password_hash,is_email_verified,role) VALUES(?,?,?,?,'0','unused',1,'OWNER_ADMIN')")->execute([$user,$owner,'QHSE Manager',$user.'@example.invalid']);
 $record=rows('SELECT * FROM users WHERE id=?',[$user])[0];
 db()->prepare("INSERT INTO qhse_controls(id,owner_id,control_code,title,domain,status,control_owner_id) VALUES(?,?,?,'Equipment inspection control','QUALITY','DRAFT',?)")->execute([$control,$owner,'QMS-001',$user]);
 db()->prepare("INSERT INTO qhse_control_standards(control_id,standard_id,clause_reference) VALUES(?,'00000000-0000-4000-8000-000000009001','8.6')")->execute([$control]);
 db()->prepare("INSERT INTO qhse_activity_events(id,owner_id,actor_id,event_type,domain,title,source_module,source_type,source_id,source_url) VALUES(?,?,?,'INSPECTION_COMPLETED','QUALITY','Inspection completed','equipment','inspection','fixture','equipment')")->execute([$event,$owner,$user]);
 $state=qhse_overview($record);
 qhse_test(count($state['standards'])===4,'Standards catalog is incomplete.');
 qhse_test($state['metrics']['pending_approvals']===1,'Pending approval metric is not tenant scoped.');
 qhse_test(count($state['activity'])===1&&$state['activity'][0]['source_id']==='fixture','Activity did not retain its source link.');
 qhse_test($state['roles']===['QHSE_ADMINISTRATOR'],'Owner did not receive safe QHSE role fallback.');
 qhse_test(str_contains(qhse_assistant_answer($state,'summary')['answer'],'pending approvals'),'Assistant is not grounded in deterministic metrics.');
 db()->rollBack();echo "PASS: {$checks} QHSE Sprint 1 checks.\n";
}catch(Throwable $error){if(db()->inTransaction())db()->rollBack();fwrite(STDERR,'FAIL: '.$error->getMessage()."\n");$failed=true;}
exit($failed?1:0);
