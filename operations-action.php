<?php
declare(strict_types=1);
require __DIR__ . '/app/marketplace.php';
require_once __DIR__ . '/app/xinng.php';
header('Cache-Control: no-store, private');
$json = str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');
if ($json) header('Content-Type: application/json; charset=utf-8');
$destination = 'dashboard';
try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Allow: POST'); throw new DomainException('Use POST for operational actions.',405); }
    $user = current_user();
    if (!$user) throw new DomainException('Sign in to continue.',401);
    if (!isset($_POST['csrf']) || !is_string($_POST['csrf']) || !hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'])) throw new DomainException('Your session expired. Refresh the page.',403);
    if (!operation_can_manage($user)) throw new DomainException('Your account has read-only operational access.',403);
    $action = (string)($_POST['action'] ?? '');
    if (($action==='equipment.status'&&!equipment_can_action($user,'change_status'))||($action==='equipment.operator'&&!equipment_can_action($user,'operators'))) throw new DomainException('This equipment feature is disabled.',403);
    $id = (string)($_POST['id'] ?? '');
    if ($action==='equipment.qr.create') {
        $destination='equipment?id='.rawurlencode($id);
        $link=xinng_equipment_link($user,$id,($_POST['confirm_destination_change']??'')==='1');
        if($json){$equipment=operation_equipment($user,$id);echo json_encode(['ok'=>true,'short_link'=>['id'=>$link['id'],'full_short_url'=>$link['url']],'qr_data_uri'=>xinng_qr_data_uri_from_image_url($link['qr_image_url'])],JSON_THROW_ON_ERROR);exit;}
        flash('success','Equipment QR link is ready.');
        redirect($destination);
    }
    if (in_array($action,['acknowledge','assign','escalate'],true)) {
        $destination = 'alert?id=' . rawurlencode($id);
        operation_alert_action($user,$id,$action,$_POST);
    } else {
        $pdo = db();
        $pdo->beginTransaction();
        if ($action === 'equipment.import') {
            $destination = 'equipment?create=1';
            $unit = equipment_unit($user,equipment_text($_POST,'unit_id',36));
            $records = equipment_upload_rows($_FILES['equipment_file'] ?? []);
            $metadata = equipment_inline_catalog($user,$_POST);
            $insert = $pdo->prepare('INSERT INTO equipment(id,owner_id,unit_id,asset_code,sequence_no,serial_no,name,owner_name,condition_remarks) VALUES(?,?,?,?,?,?,?,?,?)');
            foreach ($records as $record) {
                $id = uuid();
                $insert->execute([$id,$user['owner_id'],$unit,$record['asset_code'],$record['sequence_no'] ?: null,$record['serial_no'] ?: null,$record['name'],$record['owner_name'] ?: null,$record['condition_remarks'] ?: null]);
                equipment_apply_metadata($user,$id,$metadata);
                operation_audit($user,$action,'equipment',$id,null,operation_equipment($user,$id));
            }
            $destination = 'equipment';
            flash('success', count($records) . ' equipment record(s) imported and recorded in the audit log.');
        } elseif ($action === 'equipment.create') {
            $destination = 'equipment?create=1';
            if(($_POST['preview_confirmed']??'0')!=='1')throw new DomainException('Review the equipment preview before registering it.',422);
            $name = equipment_text($_POST,'name',255,true);
            $code = equipment_text($_POST,'asset_code',100,true);
            $unit = equipment_unit($user,equipment_text($_POST,'unit_id',36));
            if ($name === '' || strlen($name)>255 || $code === '' || strlen($code)>100) throw new DomainException('Enter an equipment name (255 characters max) and asset ID (100 characters max).',422);
            if (!array_filter(operation_locations($user),fn($l)=>$l['unit_id']===$unit)) throw new DomainException('Choose an accessible unit.',403);
            $id = uuid();
            $pdo->prepare('INSERT INTO equipment(id,owner_id,unit_id,asset_code,name) VALUES(?,?,?,?,?)')->execute([$id,$user['owner_id'],$unit,$code,$name]);
            equipment_apply_metadata($user,$id,$_POST);
            if(isset($_FILES['photos'])){$selected=array_filter((array)($_FILES['photos']['name']??[]));if($selected)equipment_upload_photos($user,$id,$_FILES['photos'],$_POST);}
            elseif(($_FILES['photo']['error']??UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_NO_FILE)equipment_upload_photo($user,$id,$_FILES['photo'],$_POST);
            operation_audit($user,$action,'equipment',$id,null,operation_equipment($user,$id));
            $destination = 'equipment?id=' . $id;
        } elseif ($action === 'equipment.status') {
            $before = operation_equipment($user,$id);
            $status = (string)($_POST['status'] ?? '');
            if (!in_array($status,['OPERATIONAL','MAINTENANCE','DOWN'],true)) throw new DomainException('Choose a valid equipment status.',422);
            equipment_seed_statuses($user['owner_id']);
            $pdo->prepare('UPDATE equipment SET status=?,status_id=(SELECT id FROM equipment_statuses WHERE owner_id=? AND operational_state=? AND is_default=1 LIMIT 1),last_activity_at=UTC_TIMESTAMP() WHERE id=?')->execute([$status,$user['owner_id'],$status,$id]);
            marketplace_apply_operational_status($user,$id);
            operation_audit($user,$action,'equipment',$id,$before,operation_equipment($user,$id));
            if ($status==='DOWN' && $before['status']!=='DOWN') {
                $alertId=uuid();
                $pdo->prepare("INSERT INTO alerts(id,equipment_id,kind,severity,title) VALUES(?,?,'BREAKDOWN','CRITICAL',?)")->execute([$alertId,$id,'Equipment down: '.substr($before['name'],0,220)]);
                operation_audit($user,'alert.create','alert',$alertId,null,operation_alert($user,$alertId));
            }
            if ($status!=='DOWN') $pdo->prepare("UPDATE alerts SET status='RESOLVED' WHERE equipment_id=? AND kind='BREAKDOWN' AND status<>'RESOLVED'")->execute([$id]);
            $destination = 'equipment?id=' . rawurlencode($id);
        } elseif ($action === 'equipment.operator') {
            $before=operation_equipment($user,$id);
            $operator=(string)($_POST['operator_id']??'');
            if($operator!=='') {
                [$sql,$params]=operation_operator_query($user,['site'=>$before['site_id'],'unit'=>$before['unit_id']]);
                if(!rows("SELECT id FROM ({$sql}) eligible WHERE id=?",[...$params,$operator])) throw new DomainException('Choose an operator assigned to this unit.',422);
            }
            $pdo->prepare('UPDATE equipment SET operator_id=?,last_activity_at=UTC_TIMESTAMP() WHERE id=?')->execute([$operator?:null,$id]);
            operation_audit($user,$action,'equipment',$id,$before,operation_equipment($user,$id));
            $destination='equipment?id='.rawurlencode($id);
        } elseif ($action === 'maintenance.create') {
            operation_equipment($user,$id);
            $title=trim((string)($_POST['title']??''));
            $due=DateTimeImmutable::createFromFormat('!Y-m-d\TH:i',(string)($_POST['due_at']??''),new DateTimeZone('UTC'));
            if($title==='' || strlen($title)>255 || !$due || $due->format('Y-m-d\TH:i')!==($_POST['due_at']??'')) throw new DomainException('Enter a work order title and valid due date in UTC.',422);
            $recordId=uuid();
            $pdo->prepare('INSERT INTO maintenance_records(id,equipment_id,title,due_at) VALUES(?,?,?,?)')->execute([$recordId,$id,$title,$due->format('Y-m-d H:i:s')]);
            operation_audit($user,$action,'maintenance',$recordId,null,['equipment_id'=>$id,'title'=>$title,'due_at'=>$due->format('Y-m-d H:i:s')]);
            $destination='maintenance?id='.$recordId;
        } elseif ($action === 'maintenance.status') {
            [$scope,$params]=operation_scope($user);
            $before=rows("SELECT m.* FROM maintenance_records m JOIN equipment_status_view e ON e.id=m.equipment_id WHERE {$scope} AND m.id=? FOR UPDATE",[...$params,$id])[0]??null;
            if(!$before)throw new DomainException('Work order not found in your scope.',404);
            $status=(string)($_POST['status']??'');
            $transitions=['SCHEDULED'=>['IN_PROGRESS','CANCELLED'],'IN_PROGRESS'=>['COMPLETED','CANCELLED'],'COMPLETED'=>[],'CANCELLED'=>[]];
            if(!in_array($status,$transitions[$before['status']],true))throw new DomainException('That work order transition is not available.',409);
            $pdo->prepare("UPDATE maintenance_records SET status=?,completed_at=IF(?='COMPLETED',UTC_TIMESTAMP(),NULL) WHERE id=?")->execute([$status,$status,$id]);
            operation_audit($user,$action,'maintenance',$id,$before,['status'=>$status]);
            operation_sync_risks($user);
            $destination='maintenance?id='.rawurlencode($id);
        } elseif ($action === 'alert.create') {
            operation_equipment($user,$id);
            $kind=(string)($_POST['kind']??'');$severity=(string)($_POST['severity']??'');$title=trim((string)($_POST['title']??''));
            if(!in_array($kind,['SAFETY','COMPLIANCE'],true)||!in_array($severity,['CRITICAL','HIGH','MEDIUM'],true)||$title===''||strlen($title)>255)throw new DomainException('Enter a valid risk type, severity, and title.',422);
            $alertId=uuid();
            $pdo->prepare('INSERT INTO alerts(id,equipment_id,kind,severity,title) VALUES(?,?,?,?,?)')->execute([$alertId,$id,$kind,$severity,$title]);
            operation_audit($user,$action,'alert',$alertId,null,operation_alert($user,$alertId));
            $destination='alert?id='.$alertId;
        } else throw new DomainException('Unknown operation.',422);
        $pdo->commit();
    }
    if ($json) { echo json_encode(['ok'=>true]); exit; }
    flash('success','Action saved and recorded in the audit log.');
    redirect($destination);
} catch (Throwable $error) {
    if(isset($pdo)&&$pdo->inTransaction())$pdo->rollBack();
    $code=$error instanceof DomainException?$error->getCode():500;
    $message=$error instanceof DomainException?$error->getMessage():'The action could not be saved. Check the details and retry.';
    if($error instanceof PDOException && ($error->errorInfo[1]??null)===1062){$code=409;$message='That asset ID already exists in your organization.';}
    $reference=null;
    if(!$error instanceof DomainException&&$code>=500){
        $reference=bin2hex(random_bytes(6));
        error_log(sprintf('Operational action [%s] %s failed: %s in %s:%d',$reference,$action??'unknown',$error->getMessage(),$error->getFile(),$error->getLine()));
    }
    if(!$json&&($action??'')==='equipment.qr.create'&&$code===409){flash('error',$message);redirect(($destination??'equipment').'&confirm_qr=1');}
    http_response_code($code);
    if($json){echo json_encode(['error'=>$message,'reference'=>$reference]);exit;}
    if(!isset($user)||!$user){echo e($message);exit;}
    if(($action??'')==='equipment.create'){flash('error',$message.' No equipment was registered. Your device draft is still available.');redirect($destination);}
    $pageTitle='Action not saved';require __DIR__.'/includes/operations-header.php';
    echo '<div class="notice error" role="alert">'.e($message).'</div><a href="'.e($destination).'">Return to operations</a>';
    require __DIR__.'/includes/operations-footer.php';
}
