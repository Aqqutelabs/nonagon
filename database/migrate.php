<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require dirname(__DIR__) . '/app/bootstrap.php';

try {
    $pdo = db();
    $pdo->exec('CREATE TABLE IF NOT EXISTS schema_migrations (version VARCHAR(100) PRIMARY KEY, applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB');
    foreach (glob(__DIR__ . '/migrations/*.sql') as $file) {
        $version = basename($file);
        $check = $pdo->prepare('SELECT 1 FROM schema_migrations WHERE version=?');
        $check->execute([$version]);
        if ($check->fetchColumn()) continue;
        foreach (explode(';', file_get_contents($file)) as $sql) {
            if (trim($sql) !== '') $pdo->exec($sql);
        }
        $pdo->prepare('INSERT INTO schema_migrations(version) VALUES(?)')->execute([$version]);
        echo "Applied {$version}\n";
    }
    require_once dirname(__DIR__).'/app/equipment-catalog.php';
    equipment_seed_catalog();
    foreach(rows('SELECT id FROM owners') as $owner)equipment_seed_statuses($owner['id']);
    // One-statement triggers keep events atomic with writes, including imports.
    foreach (['equipment','maintenance_records','alerts'] as $table) {
        foreach (['INSERT','UPDATE','DELETE'] as $operation) {
            $trigger = 'dashboard_' . $table . '_' . strtolower($operation);
            $exists = $pdo->prepare('SELECT 1 FROM information_schema.TRIGGERS WHERE TRIGGER_SCHEMA=DATABASE() AND TRIGGER_NAME=?');
            $exists->execute([$trigger]);
            if ($exists->fetchColumn()) continue;
            $row = $operation === 'DELETE' ? 'OLD' : 'NEW';
            $select = $table === 'equipment'
                ? "SELECT {$row}.owner_id,{$row}.unit_id,'equipment',{$row}.id"
                : "SELECT e.owner_id,e.unit_id,'{$table}',{$row}.id FROM equipment e WHERE e.id={$row}.equipment_id";
            $pdo->exec("CREATE TRIGGER {$trigger} AFTER {$operation} ON {$table} FOR EACH ROW INSERT INTO dashboard_events(owner_id,unit_id,entity_type,entity_id) {$select}");
        }
    }
    foreach (['INSERT','UPDATE'] as $verb) {
        $trigger='equipment_history_'.strtolower($verb);
        $check=$pdo->prepare('SELECT 1 FROM information_schema.TRIGGERS WHERE TRIGGER_SCHEMA=DATABASE() AND TRIGGER_NAME=?');
        $check->execute([$trigger]);
        if($check->fetchColumn())continue;
        $assignment=$verb==='INSERT' ? 'TRUE' : 'NOT (OLD.operator_id <=> NEW.operator_id)';
        $status=$verb==='INSERT' ? 'TRUE' : 'NOT (OLD.status <=> NEW.status)';
        $pdo->exec("CREATE TRIGGER {$trigger} AFTER {$verb} ON equipment FOR EACH ROW BEGIN
          IF {$assignment} THEN
            UPDATE equipment_operator_assignments SET unassigned_at=UTC_TIMESTAMP() WHERE equipment_id=NEW.id AND unassigned_at IS NULL;
            IF NEW.operator_id IS NOT NULL THEN INSERT INTO equipment_operator_assignments(id,equipment_id,operator_user_id) VALUES(UUID(),NEW.id,NEW.operator_id); END IF;
          END IF;
          IF {$status} THEN INSERT INTO equipment_status_history(equipment_id,status) VALUES(NEW.id,NEW.status); END IF;
        END");
    }
    echo "Dashboard migrations and event triggers ready.\n";
} catch (Throwable $error) {
    fwrite(STDERR, 'Migration failed: ' . $error->getMessage() . "\n");
    exit(1);
}
