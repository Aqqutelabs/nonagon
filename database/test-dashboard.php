<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
require dirname(__DIR__).'/app/operations.php';
$pdo=db();$owners=[];$keys=[];$checks=0;$cookie=null;$photoFixture=null;
function check(bool $condition,string $message):void {global $checks;if(!$condition)throw new RuntimeException($message);$checks++;}
function denied(callable $call,int $code):void {try{$call();}catch(DomainException $e){check($e->getCode()===$code,'Wrong denial code');return;}throw new RuntimeException('Expected access denial');}
function fixtureOwner():array {
    global $pdo,$owners;
    $owner=uuid();$owners[]=$owner;
    $pdo->prepare('INSERT INTO owners(id,name,email,phone) VALUES(?,?,?,?)')->execute([$owner,'Dashboard test','owner-'.$owner.'@example.invalid','0000000']);
    $space=uuid();$site=uuid();$plant=uuid();$unit=uuid();$other=uuid();
    foreach([['spaces','owner_id',$space,$owner],['sites','space_id',$site,$space],['plants','site_id',$plant,$site],['units','plant_id',$unit,$plant],['units','plant_id',$other,$plant]] as [$table,$column,$id,$parent])$pdo->prepare("INSERT INTO {$table}(id,{$column},name) VALUES(?,?,?)")->execute([$id,$parent,'Dashboard test']);
    return ['owner'=>$owner,'site'=>$site,'unit'=>$unit,'other'=>$other];
}
function fixtureUser(array $org,string $role,bool $scoped=true):array {
    global $pdo;
    $id=uuid();$email='test-'.$id.'@example.invalid';
    $pdo->prepare('INSERT INTO users(id,owner_id,full_name,email,phone,role,password_hash,is_email_verified) VALUES(?,?,?,?,?,?,?,1)')->execute([$id,$org['owner'],'Dashboard test',$email,'0000000',$role,password_hash('FixturePassword123!',PASSWORD_DEFAULT)]);
    if($scoped)$pdo->prepare('INSERT INTO user_scopes(id,user_id,site_id,unit_id) VALUES(?,?,?,?)')->execute([uuid(),$id,$org['site'],$org['unit']]);
    return rows('SELECT * FROM users WHERE id=?',[$id])[0];
}
function fixtureEquipment(array $org,string $unit,string $status='OPERATIONAL'):string {
    global $pdo;$id=uuid();$pdo->prepare('INSERT INTO equipment(id,owner_id,unit_id,asset_code,name,status) VALUES(?,?,?,?,?,?)')->execute([$id,$org['owner'],$unit,$id,'Test equipment',$status]);return $id;
}
function request(string $path,?array $post=null):array {
    global $cookie;
    $handle=curl_init('http://localhost/nonagon/'.$path);
    curl_setopt_array($handle,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_COOKIEFILE=>$cookie,CURLOPT_COOKIEJAR=>$cookie,CURLOPT_TIMEOUT=>12,CURLOPT_FOLLOWLOCATION=>false]);
    if($post!==null)curl_setopt_array($handle,[CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>array_filter($post,fn($value)=>$value instanceof CURLFile)?$post:http_build_query($post)]);
    $body=curl_exec($handle);$status=curl_getinfo($handle,CURLINFO_HTTP_CODE);$elapsed=curl_getinfo($handle,CURLINFO_TOTAL_TIME);$error=curl_error($handle);curl_close($handle);
    if($body===false)throw new RuntimeException($error);
    return [$status,$body,$elapsed];
}
try {
    $a=fixtureOwner();$b=fixtureOwner();
    $owner=fixtureUser($a,'OWNER_ADMIN',false);$supervisor=fixtureUser($a,'SUPERVISOR');$operator=fixtureUser($a,'OPERATOR');$outsider=fixtureUser($b,'OWNER_ADMIN',false);$noScope=fixtureUser($a,'ADMIN',false);$manager=fixtureUser($a,'OPS_MANAGER');
    $one=fixtureEquipment($a,$a['unit'],'DOWN');$two=fixtureEquipment($a,$a['other'],'MAINTENANCE');$foreign=fixtureEquipment($b,$b['unit']);
    $pdo->prepare('UPDATE equipment SET operator_id=? WHERE id=?')->execute([$operator['id'],$one]);
    check(operation_equipment($operator,$one)['id']===$one,'Assigned operator can read equipment');
    denied(fn()=>operation_equipment($operator,$two),404);
    $unassigned=fixtureEquipment($a,$a['unit']);
    denied(fn()=>operation_equipment($operator,$unassigned),404);
    check((int)rows('SELECT COUNT(*) n FROM equipment_operator_assignments WHERE equipment_id=? AND unassigned_at IS NULL',[$one])[0]['n']===1,'Active assignment recorded');
    $pdo->prepare('UPDATE equipment SET operator_id=NULL WHERE id=?')->execute([$one]);
    denied(fn()=>operation_equipment($operator,$one),404);
    check((int)rows('SELECT COUNT(*) n FROM equipment_operator_assignments WHERE equipment_id=? AND unassigned_at IS NOT NULL',[$one])[0]['n']===1,'Unassignment history retained');
    $pdo->prepare('UPDATE equipment SET operator_id=? WHERE id=?')->execute([$operator['id'],$one]);
    check((int)rows('SELECT COUNT(*) n FROM equipment_status_history WHERE equipment_id=?',[$one])[0]['n']===1,'Initial status captured');
    $pdo->prepare('DELETE FROM equipment WHERE id=?')->execute([$unassigned]);
    check(!equipment_can_action($operator,'change_status'),'Operator cannot change status');
    $appConfig['features']['change_status']=false;
    check(!equipment_can_action($owner,'change_status'),'Feature gate denies even owner');
    $appConfig['features']['change_status']=true;
    $alert=uuid();$pdo->prepare("INSERT INTO alerts(id,equipment_id,kind,severity,title) VALUES(?,?,'SAFETY','HIGH','Fixture safety risk')")->execute([$alert,$one]);
    $work=uuid();$pdo->prepare("INSERT INTO maintenance_records(id,equipment_id,title,due_at) VALUES(?,?,'Fixture overdue work',DATE_SUB(UTC_TIMESTAMP(),INTERVAL 1 DAY))")->execute([$work,$one]);
    $filters=['site'=>'','unit'=>''];
    foreach([$owner,$supervisor,$operator,$outsider,$noScope,$manager] as $u)$keys[]=dashboard_scope_key($u,$filters);
    $operatorState=dashboard_state($operator,$filters,true);check($operatorState['summary']['metrics']['total']===1,'Operator dashboard only aggregates active assignments');
    $full=dashboard_state($owner,$filters,true);$limited=dashboard_state($supervisor,$filters,true);
    check($full['summary']['metrics']['total']===2,'Owner count is scoped to organization');
    check($limited['summary']['metrics']['total']===1,'Supervisor cannot see another unit');
    check($limited['summary']['metrics']['operators']===1,'Operator count');
    check($limited['equipment'][0]['operator_name']==='Dashboard test','Operator assignment');
    check($limited['summary']['maintenance']['overdue']===1,'Overdue count');
    check($limited['summary']['risks']['overdue']===1,'Derived overdue risk');
    check(dashboard_state($noScope,$filters,true)['summary']['metrics']['total']===0,'No scope must yield zero');
    check(dashboard_state($manager,$filters,true)['summary']['metrics']['total']===1,'Operations manager scope');
    check($limited['summary']['trends']['total']===null,'No fabricated historical trend');
    $scopeKey=dashboard_scope_key($supervisor,$filters);
    $pdo->prepare('INSERT INTO dashboard_history(scope_key,snapshot_date,metrics) VALUES(?,DATE_SUB(UTC_DATE(),INTERVAL 7 DAY),?)')->execute([$scopeKey,json_encode(['total'=>0,'active'=>0,'maintenance'=>0,'down'=>0,'operators'=>0])]);
    check(dashboard_state($supervisor,$filters,true)['summary']['trends']['total']===1,'Seven-day baseline produces a real delta');
    denied(fn()=>operation_equipment($supervisor,$two),404);
    denied(fn()=>operation_equipment($owner,$foreign),404);
    denied(fn()=>operation_filters($supervisor,['unit'=>$a['other']]),403);
    denied(fn()=>operation_alert_action($operator,$alert,'acknowledge',[]),403);
    denied(fn()=>operation_alert_action($outsider,$alert,'acknowledge',[]),404);
    denied(fn()=>operation_alert_action($supervisor,$alert,'assign',['assigned_to'=>$outsider['id']]),422);
    operation_alert_action($supervisor,$alert,'assign',['assigned_to'=>$operator['id']]);
    operation_alert_action($supervisor,$alert,'acknowledge',[]);
    denied(fn()=>operation_alert_action($supervisor,$alert,'acknowledge',[]),409);
    operation_alert_action($supervisor,$alert,'escalate',['reason'=>'Safety response required']);
    check(operation_alert($supervisor,$alert)['status']==='ESCALATED','Escalation persists');
    check((int)rows('SELECT COUNT(*) AS n FROM audit_logs WHERE entity_id=?',[$alert])[0]['n']===3,'Every successful alert action audited once');
    $updated=dashboard_state($supervisor,$filters,true);$delta=dashboard_delta($updated,dashboard_hashes($limited));
    check(isset($delta['changes']['alerts'])&&!isset($delta['changes']['equipment']),'Delta sends only changed sections');
    check(dashboard_delta($updated,dashboard_hashes($updated))['changes']===[],'No-op update has no sections');
    check(dashboard_state($supervisor,$filters)['meta']['cache_hit']===true,'Scoped cache hit');
    check(strlen(json_encode($updated))<65536,'Bounded payload');
    $revision=rows('SELECT MAX(id) AS n FROM dashboard_events WHERE owner_id=?',[$a['owner']])[0]['n'];
    operation_sync_risks($supervisor);
    check(rows('SELECT MAX(id) AS n FROM dashboard_events WHERE owner_id=?',[$a['owner']])[0]['n']===$revision,'Risk refresh is idempotent');
    $pdo->prepare("UPDATE maintenance_records SET status='COMPLETED' WHERE id=?")->execute([$work]);
    $after=dashboard_state($supervisor,$filters,true);
    check($after['summary']['risks']['overdue']===0,'Completed maintenance resolves derived risk');
    // HTTP checks exercise real sessions, rendered routes, auth failures and CSRF.
    if(function_exists('curl_init')) {
        for($i=0;$i<7;$i++)fixtureEquipment($a,$a['unit']);
        $cookie=tempnam(sys_get_temp_dir(),'nonagon-dashboard-');
        [$status,$body]=request('dashboard-data');check($status===401,'Anonymous API rejected');
        [$status,$body]=request('login');check($status===200,'Login available');
        preg_match('/name="csrf" value="([a-f0-9]+)"/',$body,$match);$csrf=$match[1]??'';
        [$status]=request('login',['csrf'=>$csrf,'email'=>$supervisor['email'],'password'=>'FixturePassword123!']);check($status===302,'Login succeeds');
        [$status,$body,$elapsed]=request('dashboard');check($status===200&&str_contains($body,'Equipment Status'),'Dashboard renders');check($elapsed<2,'Dashboard local load under 2 seconds');
        check(str_contains($body,'assets/images/nonagon-logo-white.png'),'Blue sidebar uses the supplied white logo');
        check(str_contains($body,'>Lease</span>')&&str_contains($body,'marketplace-manage?view=listings'),'Operations navigation links to the Lease workspace');
        [$status,$publicBody]=request('');check($status===200&&str_contains($publicBody,'class="public-profile"')&&!str_contains($publicBody,'Login / Sign up'),'Authenticated public header shows the profile control');
        [$status,$body]=request('dashboard-equipment');$listing=json_decode($body,true);check($status===200&&count($listing['items'])===5&&$listing['total']===8,'Equipment pagination stays within scope');
        [$status,$body]=request('dashboard-equipment?page=2');$listing=json_decode($body,true);check($status===200&&count($listing['items'])===3&&$listing['page']===2,'Second equipment page');
        [$status,$body]=request('dashboard-equipment?q='.$one);$listing=json_decode($body,true);check($status===200&&$listing['total']===1&&$listing['items'][0]['id']===$one,'Equipment search by asset ID');
        [$status,$body]=request('dashboard-equipment?status=MAINTENANCE');check($status===200&&json_decode($body,true)['total']===0,'Equipment status filter excludes inaccessible maintenance asset');
        [$status,$body]=request('dashboard-equipment?q=%25');check($status===200&&json_decode($body,true)['total']===0,'Search treats SQL wildcard as literal');
        [$status]=request('dashboard-equipment?unit='.$a['other']);check($status===403,'Equipment search rejects inaccessible unit');
        [$status,$body]=request('dashboard');
        preg_match('/name="csrf" value="([a-f0-9]+)"/',$body,$match);$csrf=$match[1]??'';
        foreach(['equipment','equipment?create=1','equipment-settings','equipment?id='.$one,'maintenance','maintenance?id='.$work,'operators','operators?id='.$operator['id'],'alerts','alert?id='.$alert,'marketplace-manage?view=listings','profile'] as $route){[$status,$body]=request($route);check($status===200&&!str_contains($body,'Fatal error'),'Route '.$route);check(str_contains($body,'class="sidebar"'),'Master navigation on '.$route);}
        [$status]=request('equipment?id='.$two);check($status===404,'HTTP cross-unit detail blocked');
        [$status]=request('dashboard-data?unit='.$a['other']);check($status===403,'API cross-unit filter blocked');
        [$status,$body]=request('dashboard-data');check($status===200&&isset(json_decode($body,true)['changes']),'Polling API works');
        $streamBody='';$streamHandle=curl_init('http://localhost/nonagon/dashboard-data?stream=1');
        curl_setopt_array($streamHandle,[CURLOPT_COOKIEFILE=>$cookie,CURLOPT_TIMEOUT=>8,CURLOPT_WRITEFUNCTION=>function($handle,$chunk)use(&$streamBody){$streamBody.=$chunk;return str_contains($streamBody,'event: delta')&&str_ends_with($streamBody,"\n\n")?0:strlen($chunk);}]);
        curl_exec($streamHandle);$streamType=curl_getinfo($streamHandle,CURLINFO_CONTENT_TYPE);curl_close($streamHandle);
        check(str_starts_with($streamType??'','text/event-stream')&&str_contains($streamBody,'event: delta'),'SSE emits a scoped delta: '.substr($streamBody,0,500));
        [$status]=request('operations-action',['action'=>'equipment.create','name'=>'HTTP fixture asset','asset_code'=>uuid(),'unit_id'=>$a['unit'],'csrf'=>$csrf]);check($status===302,'Equipment registration action');
        $created=rows("SELECT id FROM equipment WHERE owner_id=? AND name='HTTP fixture asset'",[$a['owner']])[0]['id'];
        [$status]=request('equipment-action',['action'=>'depreciation','id'=>$created,'acquisition_cost'=>'10000','salvage_value'=>'1000','useful_life_years'=>'5','acquisition_date'=>'2024-01-01','is_active'=>'1','csrf'=>$csrf]);check($status===302,'Depreciation form saves');
        [$status]=request('equipment-action',['action'=>'value','id'=>$created,'value_type'=>'MARKET','source_type'=>'USER','source_detail'=>'Fixture observation','as_of_date'=>gmdate('Y-m-d'),'amount'=>'8000','csrf'=>$csrf]);check($status===302,'Value source form saves');
        [$status,$body]=request('equipment?id='.$created);check($status===200&&str_contains($body,'Value over time'),'Value chart renders');
        $photoFixture=tempnam(sys_get_temp_dir(),'nonagon-photo-');file_put_contents($photoFixture,base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jRZkAAAAASUVORK5CYII='));
        [$status]=request('equipment-action',['action'=>'photo.upload','id'=>$created,'photos[0]'=>new CURLFile($photoFixture,'image/png','fixture-one.png'),'photos[1]'=>new CURLFile($photoFixture,'image/png','fixture-two.png'),'primary_photo_index'=>'1','csrf'=>$csrf]);check($status===302,'Multiple photo upload saves');
        $uploadedPhotos=rows('SELECT id,is_primary FROM equipment_photos WHERE equipment_id=? ORDER BY created_at,id',[$created]);check(count($uploadedPhotos)===2&&array_sum(array_column($uploadedPhotos,'is_primary'))===1,'Multiple upload keeps exactly one primary photo');
        [$status,$bytes]=request('equipment-photo?equipment_id='.$created);check($status===200&&str_starts_with($bytes,"\x89PNG"),'Scoped primary photo is served');
        [$status]=request('equipment-photo?equipment_id='.$two);check($status===404,'Photo retrieval blocks inaccessible unit');
        [$status]=request('equipment-action',['action'=>'photo.primary','id'=>$created,'photo_id'=>$uploadedPhotos[0]['id'],'csrf'=>$csrf]);check($status===302&&rows('SELECT is_primary FROM equipment_photos WHERE id=?',[$uploadedPhotos[0]['id']])[0]['is_primary'],'Primary photo can switch between uploads');
        [$status]=request('equipment-action',['action'=>'photo.delete','id'=>$created,'photo_id'=>$uploadedPhotos[1]['id'],'csrf'=>$csrf]);check($status===302&&(int)rows('SELECT COUNT(*) n FROM equipment_photos WHERE equipment_id=?',[$created])[0]['n']===1,'Equipment photo can be deleted when another remains');
        [$status]=request('equipment-action',['action'=>'photo.delete','id'=>$created,'photo_id'=>$uploadedPhotos[0]['id'],'csrf'=>$csrf]);check($status===409&&(int)rows('SELECT COUNT(*) n FROM equipment_photos WHERE equipment_id=?',[$created])[0]['n']===1,'Last equipment photo cannot be deleted');
        [$status]=request('equipment-action',['action'=>'assembly.add','id'=>$created,'name'=>'HTTP custom assembly','csrf'=>$csrf]);check($status===302,'Custom assembly form saves');
        $httpAssembly=rows('SELECT id FROM equipment_assemblies WHERE equipment_id=?',[$created])[0]['id'];
        [$status]=request('equipment-action',['action'=>'part.add','id'=>$created,'equipment_assembly_id'=>$httpAssembly,'custom_name'=>'Fixture bracket','quantity'=>'2','csrf'=>$csrf]);check($status===302,'Installed custom part form saves');
        $httpPart=rows('SELECT id FROM equipment_assembly_items WHERE equipment_assembly_id=?',[$httpAssembly])[0]['id'];
        [$status]=request('equipment-action',['action'=>'part.remove','id'=>$created,'item_id'=>$httpPart,'csrf'=>$csrf]);check($status===302,'Installed part removal preserves history');
        [$status]=request('operations-action',['action'=>'equipment.status','id'=>$created,'status'=>'DOWN','csrf'=>$csrf]);check($status===302,'Equipment status action');
        check((int)rows("SELECT COUNT(*) AS n FROM alerts WHERE equipment_id=? AND kind='BREAKDOWN'",[$created])[0]['n']===1,'Down state generates breakdown alert');
        [$status]=request('operations-action',['action'=>'equipment.operator','id'=>$created,'operator_id'=>$operator['id'],'csrf'=>$csrf]);check($status===302,'Operator assignment action');
        foreach(['equipment?view=list&sort=status','equipment?view=list&sort=activity&direction=desc','equipment?q=Test','equipment?critical=1','equipment?unit='.$a['other'],'equipment?category=unknown','equipment?cursor=bad'] as $route){[$status,$body]=request($route);check($status===200&&!str_contains($body,'Fatal error'),'Register filter route '.$route);}
        [$status,$body]=request('equipment?q=NO-MATCH-'.uuid());check($status===200&&str_contains($body,'No equipment found'),'Register empty search');
        [$status,$body]=request('equipment?view=list');check(str_contains($body,'register-table'),'List mode rendered');
        [$status,$detailBody]=request('equipment?id='.$one);
        check($status===200&&str_contains($detailBody,'asset-overview-columns'),'Equipment detail overview layout');
        check(str_contains($detailBody,'id="asset-main-photo"')&&str_contains($detailBody,'class="asset-tabs"'),'Detail gallery and section navigation');
        check(str_contains($detailBody,'class="photo-add-card photo-upload-card"')&&str_contains($detailBody,'name="photos[]"')&&str_contains($detailBody,'multiple'),'Detail uses the multiple-photo add card');
        check(str_contains($detailBody,'id="schedule-maintenance"')&&str_contains($detailBody,'id="asset-edit"'),'Detail toolbar targets existing forms');

        [$status,$body]=request('equipment');check(str_contains($body,'register-grid'),'Grid is default');
        [$status,$body]=request('equipment?create=1');check($status===200&&str_contains($body,'data-registration-photos')&&str_contains($body,'name="photos[]"')&&str_contains($body,'multiple'),'Registration uses the same multiple-photo card interaction');
        $paginationIds=[];
        for($i=0;$i<14;$i++){$paginationId=fixtureEquipment($a,$a['unit']);$paginationIds[]=$paginationId;$pdo->prepare('UPDATE equipment SET name=? WHERE id=?')->execute(['Register pagination fixture',$paginationId]);}
        [$status,$body]=request('equipment?q=Register+pagination+fixture');
        check($status===200&&substr_count($body,'class="equipment-card"')===12,'Grid page is bounded to twelve');
        preg_match('/href="([^"]*cursor=[^"]+)"[^>]*>Next<\/a>/', $body,$next);
        check(isset($next[1]),'Grid emits cursor link');
        [$status,$nextBody]=request(html_entity_decode($next[1]));
        check($status===200&&substr_count($nextBody,'class="equipment-card"')===2,'Cursor retrieves remaining two records');
        preg_match_all('/class="card-target" href="equipment\?id=([^"&]+)/',$body,$firstIds);
        preg_match_all('/class="card-target" href="equipment\?id=([^"&]+)/',$nextBody,$secondIds);
        check(!array_intersect($firstIds[1],$secondIds[1]),'Cursor does not repeat records with identical names');
        foreach($paginationIds as $paginationId)$pdo->prepare('DELETE FROM equipment WHERE id=?')->execute([$paginationId]);
        $archiveId=fixtureEquipment($a,$a['unit']);
        [$status]=request('equipment-action',['action'=>'archive','id'=>$archiveId,'confirmation'=>$archiveId]);check($status===403,'Archive requires CSRF');
        [$status]=request('equipment-action',['action'=>'archive','id'=>$two,'confirmation'=>$two,'csrf'=>$csrf]);check($status===404,'Archive blocks cross-unit access');
        [$status]=request('equipment-action',['action'=>'archive','id'=>$archiveId,'confirmation'=>'wrong','csrf'=>$csrf]);check($status===422,'Archive requires exact AID confirmation');
        [$status]=request('equipment-action',['action'=>'archive','id'=>$archiveId,'confirmation'=>$archiveId,'csrf'=>$csrf]);check($status===302,'Archive action succeeds');
        [$status]=request('equipment?id='.$archiveId);check($status===404,'Archived equipment detail is not active');
        [$status]=request('equipment-photo?equipment_id='.$archiveId);check($status===404,'Archived equipment photos are not accessible');
        [$status,$body]=request('equipment?q='.$archiveId);check($status===200&&str_contains($body,'No equipment found'),'Archived equipment is absent from register');



        [$status]=request('operations-action',['action'=>'maintenance.create','id'=>$created,'title'=>'HTTP fixture work','due_at'=>'2026-01-01T10:00','csrf'=>$csrf]);check($status===302,'Maintenance creation action');
        $createdWork=rows("SELECT id FROM maintenance_records WHERE equipment_id=?",[$created])[0]['id'];
        [$status]=request('operations-action',['action'=>'maintenance.status','id'=>$createdWork,'status'=>'IN_PROGRESS','csrf'=>$csrf]);check($status===302,'Work order start action');
        [$status]=request('operations-action',['action'=>'maintenance.status','id'=>$createdWork,'status'=>'COMPLETED','csrf'=>$csrf]);check($status===302,'Work order completion action');
        [$status]=request('operations-action',['action'=>'alert.create','id'=>$created,'title'=>'HTTP fixture risk','kind'=>'COMPLIANCE','severity'=>'MEDIUM','csrf'=>$csrf]);check($status===302,'Compliance alert action');
        [$status]=request('operations-action',['action'=>'equipment.create','name'=>'Forbidden fixture','asset_code'=>uuid(),'unit_id'=>$a['other'],'csrf'=>$csrf]);check($status===403,'Cross-unit equipment creation blocked');
        $pdo->prepare('UPDATE users SET is_active=0 WHERE id=?')->execute([$supervisor['id']]);
        [$status]=request('dashboard-data');check($status===401,'Disabled account loses API access');
        $pdo->prepare('UPDATE users SET is_active=1 WHERE id=?')->execute([$supervisor['id']]);
        [$status,$body]=request('operations-action',['action'=>'acknowledge','id'=>$alert,'csrf'=>'bad']);check($status===403,'CSRF rejected; received '.$status);
        [$status]=request('operations-action');check($status===405,'GET mutation rejected');
        $revocationBody='';$loggedOut=false;$revocationStream=curl_init('http://localhost/nonagon/dashboard-data?stream=1');
        curl_setopt_array($revocationStream,[CURLOPT_COOKIEFILE=>$cookie,CURLOPT_TIMEOUT=>8,CURLOPT_WRITEFUNCTION=>function($handle,$chunk)use(&$revocationBody,&$loggedOut,$csrf){
            $revocationBody.=$chunk;
            if(!$loggedOut&&str_contains($revocationBody,'event: delta')){$loggedOut=true;request('logout',['csrf'=>$csrf]);}
            return str_contains($revocationBody,'event: revoked')?0:strlen($chunk);
        }]);
        curl_exec($revocationStream);curl_close($revocationStream);
        check(str_contains($revocationBody,'event: revoked'),'Logout revokes an existing SSE stream');
        echo 'HTTP dashboard load: '.round($elapsed*1000,1)." ms\n";
    }
    echo "PASS: {$checks} dashboard checks.\n";
}catch(Throwable $error){fwrite(STDERR,'FAIL: '.$error->getMessage()."\n");$failed=true;}
finally {
    if($pdo->inTransaction())$pdo->rollBack();
    foreach($owners as $ownerId) {
        $pdo->prepare('DELETE a FROM alerts a JOIN equipment e ON e.id=a.equipment_id WHERE e.owner_id=?')->execute([$ownerId]);
        $pdo->prepare('DELETE m FROM maintenance_records m JOIN equipment e ON e.id=m.equipment_id WHERE e.owner_id=?')->execute([$ownerId]);
        $pdo->prepare('DELETE FROM equipment WHERE owner_id=?')->execute([$ownerId]);
        $pdo->prepare('DELETE FROM audit_logs WHERE owner_id=?')->execute([$ownerId]);
        $pdo->prepare('DELETE FROM dashboard_events WHERE owner_id=?')->execute([$ownerId]);
        $pdo->prepare('DELETE us FROM user_scopes us JOIN users u ON u.id=us.user_id WHERE u.owner_id=?')->execute([$ownerId]);
        $pdo->prepare('DELETE FROM equipment_statuses WHERE owner_id=?')->execute([$ownerId]);
        $pdo->prepare('DELETE FROM owners WHERE id=?')->execute([$ownerId]);
    }
    foreach($keys as $key){$pdo->prepare('DELETE FROM dashboard_cache WHERE scope_key=?')->execute([$key]);$pdo->prepare('DELETE FROM dashboard_history WHERE scope_key=?')->execute([$key]);}
    if($cookie&&is_file($cookie))unlink($cookie);
    if($photoFixture&&is_file($photoFixture))unlink($photoFixture);
    echo "Test fixtures removed.\n";
}
exit(!empty($failed)?1:0);
