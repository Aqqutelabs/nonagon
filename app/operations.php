<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';

function rows(string $sql, array $params = []): array
{
    $statement = db()->prepare($sql);
    $statement->execute($params);
    return $statement->fetchAll();
}

function operation_locations(array $user): array
{
    $sql = 'SELECT s.id AS site_id,s.name AS site_name,u.id AS unit_id,u.name AS unit_name FROM spaces sp JOIN sites s ON s.space_id=sp.id JOIN plants p ON p.site_id=s.id JOIN units u ON u.plant_id=p.id WHERE sp.owner_id=?';
    $params = [$user['owner_id']];
    if ($user['role'] !== 'OWNER_ADMIN') {
        $sql .= ' AND EXISTS(SELECT 1 FROM user_scopes us WHERE us.user_id=? AND us.site_id=s.id AND us.unit_id=u.id)';
        $params[] = $user['id'];
    }
    return rows($sql . ' ORDER BY s.name,u.name,u.id', $params);
}

function operation_filters(array $user, array $input): array
{
    $site = is_string($input['site'] ?? '') ? ($input['site'] ?? '') : '';
    $unit = is_string($input['unit'] ?? '') ? ($input['unit'] ?? '') : '';
    $locations = operation_locations($user);
    if (($site !== '' || $unit !== '') && !array_filter($locations, fn($l) => (!$site || $l['site_id'] === $site) && (!$unit || $l['unit_id'] === $unit))) {
        throw new DomainException('This location is outside your access scope.', 403);
    }
    return ['site' => $site, 'unit' => $unit];
}

/** Every operational read starts with this owner + site/unit predicate. */
function operation_scope(array $user, string $alias = 'e', array $filters = [], ?string $equipmentColumn = 'id'): array
{
    $sql = "{$alias}.owner_id=?";
    $params = [$user['owner_id']];
    if ($user['role'] !== 'OWNER_ADMIN') {
        $sql .= " AND EXISTS(SELECT 1 FROM user_scopes access_scope WHERE access_scope.user_id=? AND access_scope.site_id={$alias}.site_id AND access_scope.unit_id={$alias}.unit_id)";
        $params[] = $user['id'];
    }
    if ($user['role'] === 'OPERATOR' && $equipmentColumn !== null) {
        $sql .= " AND EXISTS(SELECT 1 FROM equipment_operator_assignments assignment WHERE assignment.equipment_id={$alias}.{$equipmentColumn} AND assignment.operator_user_id=? AND assignment.unassigned_at IS NULL)";
        $params[] = $user['id'];
    }
    foreach (['site','unit'] as $field) {
        if (!empty($filters[$field])) { $sql .= " AND {$alias}.{$field}_id=?"; $params[] = $filters[$field]; }
    }
    return [$sql, $params];
}

function operation_can_manage(array $user): bool
{
    return (bool)$user['is_active'] && email_access_allowed($user) && in_array($user['role'], ['OWNER_ADMIN','ADMIN','OPS_MANAGER','SUPERVISOR'], true);
}

function equipment_can_action(array $user,string $feature): bool
{
    return operation_can_manage($user) && (bool)config('features.'.$feature,false);
}

function operation_equipment(array $user, string $id): array
{
    [$scope,$params] = operation_scope($user);
    $record = rows("SELECT e.* FROM equipment_status_view e WHERE {$scope} AND e.id=?", [...$params,$id])[0] ?? null;
    if (!$record) throw new DomainException('Equipment not found in your access scope.', 404);
    return $record;
}

function equipment_upload_rows(array $file): array
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) throw new DomainException('Choose a CSV or Excel file to upload.', 422);
    if (($file['size'] ?? 0) > 5 * 1024 * 1024) throw new DomainException('The upload must be 5 MB or smaller.', 422);
    $extension = strtolower(pathinfo((string)($file['name'] ?? ''), PATHINFO_EXTENSION));
    if (!in_array($extension, ['csv', 'xlsx'], true)) throw new DomainException('Upload a .csv or .xlsx file.', 422);

    $rows = $extension === 'csv' ? equipment_csv_rows((string)$file['tmp_name']) : equipment_xlsx_rows((string)$file['tmp_name']);
    if (count($rows) < 2) throw new DomainException('The file must contain a header row and at least one equipment row.', 422);
    $header = array_map('equipment_upload_key', array_shift($rows));
    $required = ['equipment','serialnumber','equipmentid','owner','conditionremarks'];
    $missing = array_values(array_diff(array_unique($required), $header));
    if ($missing) throw new DomainException('Missing required columns: ' . implode(', ', $missing) . '.', 422);
    $columns = array_flip($header);$companySerialColumn=$columns['companyserialnumber']??$columns['sn']??null;
    if($companySerialColumn===null)throw new DomainException('Missing required column: Company Serial Number.',422);
    $result = [];
    foreach ($rows as $number => $row) {
        $row = array_pad($row, count($header), '');
        $record = [
            'sequence_no' => trim((string)$row[$companySerialColumn]),
            'serial_no' => trim((string)$row[$columns['serialnumber']]),
            'name' => trim((string)$row[$columns['equipment']]),
            'asset_code' => trim((string)$row[$columns['equipmentid']]),
            'owner_name' => trim((string)$row[$columns['owner']]),
            'condition_remarks' => trim((string)$row[$columns['conditionremarks']]),
        ];
        if (implode('', $record) === '') continue;
        if ($record['name'] === '' || $record['asset_code'] === '') throw new DomainException('Row ' . ($number + 2) . ' must include Equipment and Equipment ID.', 422);
        if (strlen($record['sequence_no']) > 50 || strlen($record['serial_no']) > 100 || strlen($record['asset_code']) > 100 || strlen($record['name']) > 255 || strlen($record['owner_name']) > 255 || strlen($record['condition_remarks']) > 65535) {
            throw new DomainException('Row ' . ($number + 2) . ' contains a value that is too long.', 422);
        }
        $result[] = $record;
        if(count($result)>500)throw new DomainException('Import up to 500 equipment rows per file.',422);
    }
    if (!$result) throw new DomainException('No equipment rows were found in the file.', 422);
    return $result;
}

function equipment_upload_key(string $value): string
{
    return strtolower((string)preg_replace('/[^a-z0-9]+/i', '', trim($value)));
}

function equipment_csv_rows(string $path): array
{
    $handle = fopen($path, 'rb');
    if (!$handle) throw new DomainException('The CSV file could not be read.', 422);
    $rows = [];
    while (($row = fgetcsv($handle)) !== false) $rows[] = $row;
    fclose($handle);
    if (isset($rows[0][0])) $rows[0][0] = preg_replace('/^\xEF\xBB\xBF/', '', (string)$rows[0][0]);
    return $rows;
}

function equipment_xlsx_rows(string $path): array
{
    if (!class_exists('ZipArchive')) throw new DomainException('Excel uploads are unavailable on this server. Use CSV or enable PHP ZipArchive.', 422);
    $zip = new ZipArchive();
    if ($zip->open($path) !== true) throw new DomainException('The Excel file could not be opened.', 422);
    $shared = [];
    if (($xml = $zip->getFromName('xl/sharedStrings.xml')) !== false) {
        $document = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NONET);
        foreach ($document->si as $item) $shared[] = (string)$item->t;
    }
    $sheet = $zip->getFromName('xl/worksheets/sheet1.xml');
    $zip->close();
    if ($sheet === false) throw new DomainException('The Excel file has no readable first worksheet.', 422);
    $document = simplexml_load_string($sheet, 'SimpleXMLElement', LIBXML_NONET);
    $document->registerXPathNamespace('x', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
    $rows = [];
    foreach ($document->xpath('//x:sheetData/x:row') as $xmlRow) {
        $values = [];
        foreach ($xmlRow->c as $cell) {
            $column = preg_replace('/\d+/', '', (string)$cell['r']); $index = 0;
            foreach (str_split($column) as $letter) $index = $index * 26 + ord($letter) - 64;
            $index--;
            $value = (string)$cell->v;
            if ((string)$cell['t'] === 's') $value = $shared[(int)$value] ?? '';
            $values[$index] = $value;
        }
        if ($values) {
            $normalized = array_fill(0, max(array_keys($values)) + 1, '');
            foreach ($values as $index => $value) $normalized[$index] = $value;
            $rows[] = $normalized;
        }
    }
    return $rows;
}

function operation_alert(array $user, string $id, bool $lock = false): array
{
    [$scope,$params] = operation_scope($user);
    $record = rows("SELECT a.*,e.owner_id,e.site_id,e.unit_id,e.name AS equipment_name,e.asset_code FROM alerts a JOIN equipment_status_view e ON e.id=a.equipment_id WHERE {$scope} AND a.id=?" . ($lock ? ' FOR UPDATE' : ''), [...$params,$id])[0] ?? null;
    if (!$record) throw new DomainException('Alert not found in your access scope.', 404);
    return $record;
}

function operation_audit(array $user, string $action, string $type, string $id, ?array $before, array $after): void
{
    db()->prepare('INSERT INTO audit_logs(owner_id,actor_id,action,entity_type,entity_id,before_json,after_json) VALUES(?,?,?,?,?,?,?)')
        ->execute([$user['owner_id'],$user['id'],$action,$type,$id,$before === null ? null : json_encode($before, JSON_THROW_ON_ERROR),json_encode($after, JSON_THROW_ON_ERROR)]);
}

function operation_alert_action(array $user, string $id, string $action, array $input): void
{
    if (!operation_can_manage($user)) throw new DomainException('Your role has read-only operational access.', 403);
    if (!in_array($action, ['acknowledge','assign','escalate'], true)) throw new DomainException('Unknown alert action.', 422);
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $before = operation_alert($user, $id, true);
        if ($before['status'] === 'RESOLVED') throw new DomainException('This alert is already resolved.', 409);
        if ($action === 'acknowledge') {
            if ($before['acknowledged_at'] !== null) throw new DomainException('This alert has already been acknowledged.', 409);
            $pdo->prepare("UPDATE alerts SET acknowledged_by=?,acknowledged_at=UTC_TIMESTAMP(),status=IF(status='ESCALATED','ESCALATED','ACKNOWLEDGED') WHERE id=?")->execute([$user['id'],$id]);
        } elseif ($action === 'assign') {
            $assignee = (string)($input['assigned_to'] ?? '');
            $eligible = rows("SELECT id FROM users u WHERE u.id=? AND u.owner_id=? AND u.is_active=1 AND (u.is_email_verified=1 OR ?=1) AND (u.role='OWNER_ADMIN' OR EXISTS(SELECT 1 FROM user_scopes us WHERE us.user_id=u.id AND us.site_id=? AND us.unit_id=?))", [$assignee,$user['owner_id'],(int)development_verification_bypass(),$before['site_id'],$before['unit_id']]);
            if (!$eligible) throw new DomainException('Choose an active, verified person with access to this unit.', 422);
            if ($before['assigned_to'] === $assignee) throw new DomainException('This person is already assigned.', 409);
            $pdo->prepare('UPDATE alerts SET assigned_to=? WHERE id=?')->execute([$assignee,$id]);
        } else {
            $reason = trim((string)($input['reason'] ?? ''));
            if ($reason === '' || strlen($reason) > 500) throw new DomainException('Enter an escalation reason of 1–500 characters.', 422);
            if ($before['status'] === 'ESCALATED') throw new DomainException('This alert has already been escalated.', 409);
            $pdo->prepare("UPDATE alerts SET status='ESCALATED',severity='CRITICAL',escalation_reason=? WHERE id=?")->execute([$reason,$id]);
        }
        operation_audit($user, 'alert.' . $action, 'alert', $id, $before, operation_alert($user,$id));
        $pdo->commit();
    } catch (Throwable $error) { if ($pdo->inTransaction()) $pdo->rollBack(); throw $error; }
}

function operation_operator_query(array $user, array $filters = []): array
{
    [$scope,$params] = operation_scope($user, 'loc', $filters, null);
    if($user['role']==='OPERATOR') { $scope.=' AND op.id=?'; $params[]=$user['id']; }
    return ["SELECT DISTINCT op.id,op.full_name,op.position,op.is_active FROM users op JOIN user_scopes os ON os.user_id=op.id JOIN (SELECT sp.owner_id,s.id AS site_id,u.id AS unit_id FROM spaces sp JOIN sites s ON s.space_id=sp.id JOIN plants p ON p.site_id=s.id JOIN units u ON u.plant_id=p.id) loc ON loc.site_id=os.site_id AND loc.unit_id=os.unit_id AND loc.owner_id=op.owner_id WHERE {$scope} AND op.role='OPERATOR' AND op.is_active=1", $params];
}

/** Clock-driven risks: idempotent insert; closed work resolves generated alerts. */
function operation_sync_risks(array $user, array $filters = []): void
{
    [$scope,$params] = operation_scope($user, 'e', $filters);
    db()->prepare("INSERT INTO alerts(id,equipment_id,maintenance_id,kind,severity,title,triggered_at) SELECT UUID(),e.id,m.id,'OVERDUE','HIGH',CONCAT('Overdue: ',LEFT(m.title,240)),m.due_at FROM maintenance_records m JOIN equipment_status_view e ON e.id=m.equipment_id WHERE {$scope} AND m.status IN ('SCHEDULED','IN_PROGRESS') AND m.due_at<UTC_TIMESTAMP() AND NOT EXISTS(SELECT 1 FROM alerts existing WHERE existing.maintenance_id=m.id) ON DUPLICATE KEY UPDATE id=alerts.id")->execute($params);
    db()->prepare("UPDATE alerts a JOIN maintenance_records m ON m.id=a.maintenance_id JOIN equipment_status_view e ON e.id=m.equipment_id SET a.status='RESOLVED' WHERE {$scope} AND a.kind='OVERDUE' AND a.status<>'RESOLVED' AND (m.status IN ('COMPLETED','CANCELLED') OR m.due_at>=UTC_TIMESTAMP())")->execute($params);
    db()->prepare("UPDATE alerts a JOIN maintenance_records m ON m.id=a.maintenance_id JOIN equipment_status_view e ON e.id=m.equipment_id SET a.status='OPEN',a.acknowledged_by=NULL,a.acknowledged_at=NULL,a.triggered_at=m.due_at WHERE {$scope} AND a.kind='OVERDUE' AND a.status='RESOLVED' AND m.status IN ('SCHEDULED','IN_PROGRESS') AND m.due_at<UTC_TIMESTAMP()")->execute($params);
}

function dashboard_scope_key(array $user, array $filters): string
{
    return hash('sha256', json_encode(['equipment-archive-v3',$user['id'],$user['owner_id'],$user['role'],operation_locations($user),$filters], JSON_THROW_ON_ERROR));
}

function dashboard_state(array $user, array $filters = [], bool $force = false): array
{
    if (!$user['is_active'] || !email_access_allowed($user)) throw new DomainException('Verified account required.', 403);
    $start = microtime(true);
    $key = dashboard_scope_key($user,$filters);
    $revision = (int)(rows('SELECT COALESCE(MAX(id),0) AS revision FROM dashboard_events WHERE owner_id=?', [$user['owner_id']])[0]['revision']);
    $cached = rows('SELECT payload,revision,TIMESTAMPDIFF(SECOND,captured_at,UTC_TIMESTAMP()) AS age FROM dashboard_cache WHERE scope_key=?',[$key])[0] ?? null;
    if (!$force && $cached && (int)$cached['revision'] === $revision && (int)$cached['age'] < 15) {
        $state = json_decode($cached['payload'],true,512,JSON_THROW_ON_ERROR);
        $state['meta']['cache_hit'] = true;
        return $state;
    }
    operation_sync_risks($user,$filters);
    [$scope,$params] = operation_scope($user,'e',$filters);
    $metrics = rows("SELECT COUNT(*) AS total,COALESCE(SUM(e.status='OPERATIONAL'),0) AS active,COALESCE(SUM(e.status='MAINTENANCE'),0) AS maintenance,COALESCE(SUM(e.status='DOWN'),0) AS down FROM equipment_status_view e WHERE {$scope}",$params)[0];
    [$operatorSql,$operatorParams] = operation_operator_query($user,$filters);
    $metrics['operators'] = rows("SELECT COUNT(*) AS n FROM ({$operatorSql}) operators",$operatorParams)[0]['n'];
    $metrics = array_map('intval',$metrics);
    $equipment = rows("SELECT e.id,e.asset_code,e.name,e.status,e.operator_id,e.operator_name,e.site_name,e.unit_name,e.last_activity_at,e.utilization_percent,(SELECT MIN(m.due_at) FROM maintenance_records m WHERE m.equipment_id=e.id AND m.status IN ('SCHEDULED','IN_PROGRESS')) AS next_maintenance_at FROM equipment_status_view e WHERE {$scope} ORDER BY FIELD(e.status,'DOWN','MAINTENANCE','OPERATIONAL'),e.last_activity_at DESC,e.id LIMIT 8",$params);
    [$scope,$params] = operation_scope($user,'e',$filters,'equipment_id');
    $alerts = rows("SELECT e.id,e.equipment_id,e.equipment_name,e.asset_code,e.kind,e.severity,e.title,e.status,e.triggered_at,e.acknowledged_at FROM alert_priority_view e WHERE {$scope} AND e.status<>'RESOLVED' ORDER BY e.priority,e.triggered_at,e.id LIMIT 5",$params);
    $risks = rows("SELECT COUNT(*) AS total,COALESCE(SUM(e.severity='CRITICAL'),0) AS critical,COALESCE(SUM(e.kind='OVERDUE'),0) AS overdue FROM alert_priority_view e WHERE {$scope} AND e.status<>'RESOLVED'",$params)[0];
    [$scope,$params] = operation_scope($user,'e',$filters);
    $maintenance = rows("SELECT COALESCE(SUM(m.status='SCHEDULED'),0) scheduled,COALESCE(SUM(m.status='IN_PROGRESS'),0) active,COALESCE(SUM(m.status IN ('SCHEDULED','IN_PROGRESS') AND m.due_at<UTC_TIMESTAMP()),0) overdue FROM maintenance_records m JOIN equipment_status_view e ON e.id=m.equipment_id WHERE {$scope}",$params)[0];
    // First observed value per day is retained; no invented historical trends.
    db()->prepare('INSERT IGNORE INTO dashboard_history(scope_key,snapshot_date,metrics) VALUES(?,UTC_DATE(),?)')->execute([$key,json_encode($metrics,JSON_THROW_ON_ERROR)]);
    $baseline = rows('SELECT metrics FROM dashboard_history WHERE scope_key=? AND snapshot_date=DATE_SUB(UTC_DATE(),INTERVAL 7 DAY)',[$key])[0] ?? null;
    $prior = $baseline ? json_decode($baseline['metrics'],true,512,JSON_THROW_ON_ERROR) : null;
    $trends = [];
    foreach ($metrics as $metric => $value) $trends[$metric] = $prior === null ? null : $value - (int)$prior[$metric];
    [$scope,$params] = operation_scope($user,'e',$filters,null);
    $event = rows("SELECT MAX(ev.occurred_at) AS latest FROM dashboard_events ev JOIN (SELECT sp.owner_id,s.id AS site_id,u.id AS unit_id FROM spaces sp JOIN sites s ON s.space_id=sp.id JOIN plants p ON p.site_id=s.id JOIN units u ON u.plant_id=p.id) e ON e.owner_id=ev.owner_id AND e.unit_id=ev.unit_id WHERE {$scope}",$params)[0]['latest'];
    $lag = $cached ? rows("SELECT MAX(TIMESTAMPDIFF(MICROSECOND,ev.occurred_at,UTC_TIMESTAMP(6)))/1000 AS lag_ms FROM dashboard_events ev JOIN (SELECT sp.owner_id,s.id AS site_id,u.id AS unit_id FROM spaces sp JOIN sites s ON s.space_id=sp.id JOIN plants p ON p.site_id=s.id JOIN units u ON u.plant_id=p.id) e ON e.owner_id=ev.owner_id AND e.unit_id=ev.unit_id WHERE {$scope} AND ev.id>?",[...$params,(int)$cached['revision']])[0]['lag_ms'] : null;
    $state = [
        'summary' => ['metrics'=>$metrics,'trends'=>$trends,'risks'=>array_map('intval',$risks),'maintenance'=>array_map('intval',$maintenance)],
        'equipment'=>$equipment,'alerts'=>$alerts,
        'meta'=>['scope'=>$key,'captured_at'=>gmdate('c'),'latest_event_at'=>$event ? str_replace(' ','T',$event).'Z' : null,'event_lag_ms'=>$lag===null?null:round((float)$lag,1),'load_ms'=>round((microtime(true)-$start)*1000,1),'cache_hit'=>false],
    ];
    $payload = json_encode($state,JSON_THROW_ON_ERROR);
    if (strlen($payload)>65536) throw new RuntimeException('Dashboard payload exceeds 64 KB.');
    db()->prepare('INSERT INTO dashboard_cache(scope_key,revision,payload,captured_at) VALUES(?,?,?,UTC_TIMESTAMP()) ON DUPLICATE KEY UPDATE revision=VALUES(revision),payload=VALUES(payload),captured_at=VALUES(captured_at)')->execute([$key,$revision,$payload]);
    return $state;
}

function dashboard_hashes(array $state): array
{
    $hashes = [];
    foreach (['summary','equipment','alerts'] as $section) $hashes[$section] = hash('sha256',json_encode($state[$section],JSON_THROW_ON_ERROR));
    return $hashes;
}

function dashboard_delta(array $state, array $hashes): array
{
    $current = dashboard_hashes($state);
    $changed = [];
    foreach ($current as $section => $hash) if (($hashes[$section] ?? '') !== $hash) $changed[$section] = $state[$section];
    return ['changes'=>$changed,'hashes'=>$current,'meta'=>$state['meta']];
}

function operation_badge(string $status): string
{
    $labels = ['OPERATIONAL'=>'✓ Operational','MAINTENANCE'=>'◷ Maintenance','DOWN'=>'! Critical / Down','CRITICAL'=>'! Critical','HIGH'=>'▲ High','MEDIUM'=>'● Medium','OPEN'=>'Open','ACKNOWLEDGED'=>'✓ Acknowledged','ESCALATED'=>'↑ Escalated','RESOLVED'=>'✓ Resolved'];
    return '<span class="badge ' . strtolower(e($status)) . '">' . e($labels[$status] ?? str_replace('_',' ',ucfirst(strtolower($status)))) . '</span>';
}
