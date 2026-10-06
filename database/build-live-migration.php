<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$migrationDirectory = __DIR__ . '/migrations';
$outputFile = __DIR__ . '/LIVE_DEPLOY_026_TO_045_2026-10-04.sql';
$files = array_values(array_filter(
    glob($migrationDirectory . '/*.sql') ?: [],
    static function (string $file): bool {
        $number = (int) substr(basename($file), 0, 3);
        return $number >= 26 && $number <= 45;
    }
));
sort($files, SORT_NATURAL);

if (count($files) !== 20) {
    fwrite(STDERR, "Expected migrations 026 through 045; found " . count($files) . ".\n");
    exit(1);
}

$referenceTargets = [];
foreach ($files as $file) {
    $migrationSql = (string) file_get_contents($file);
    preg_match_all('/REFERENCES\s+`?([a-z0-9_]+)`?\s*\(\s*`?([a-z0-9_]+)`?\s*\)/i', $migrationSql, $matches, PREG_SET_ORDER);
    foreach ($matches as $match) $referenceTargets[strtolower($match[1]).'.'.strtolower($match[2])] = [strtolower($match[1]),strtolower($match[2])];
}
ksort($referenceTargets, SORT_NATURAL);

$parts = [
    '-- NONAGON CONSOLIDATED LIVE DEPLOYMENT',
    '-- Baseline required: schema_migrations contains 001_dashboard.sql through 025_commercial_branding.sql.',
    '-- Target: the currently selected Nonagon database. This file never creates, drops, or selects a database.',
    '-- Generated: 2026-10-04',
    '',
    'SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;',
    'CREATE TABLE IF NOT EXISTS schema_migrations (version VARCHAR(100) PRIMARY KEY, applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB;',
    '',
    '-- Compatibility repair for older/imported databases whose canonical ID columns lost their indexes.',
    '-- A unique-index failure here means the affected table contains duplicate IDs and must be repaired before deployment.',
    'DELIMITER $$',
    'DROP PROCEDURE IF EXISTS nonagon_repair_reference_indexes$$',
    'CREATE PROCEDURE nonagon_repair_reference_indexes()',
    'BEGIN',
];

foreach ($referenceTargets as [$table,$column]) {
    $index = substr('deploy_' . $table . '_' . $column . '_uq', 0, 64);
    $parts[] = " IF EXISTS (SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='{$table}')";
    $parts[] = " AND NOT EXISTS (SELECT 1 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='{$table}' AND COLUMN_NAME='{$column}' AND SEQ_IN_INDEX=1) THEN";
    $parts[] = "  ALTER TABLE `{$table}` ADD UNIQUE KEY `{$index}` (`{$column}`);";
    $parts[] = ' END IF;';
}

$parts = array_merge($parts, [
    'END$$',
    'CALL nonagon_repair_reference_indexes()$$',
    'DROP PROCEDURE nonagon_repair_reference_indexes$$',
    'DELIMITER ;',
    '',
]);

foreach ($files as $file) {
    $version = basename($file);
    $sql = trim((string) file_get_contents($file));
    $sql = preg_replace('/^\s*SET\s+NAMES\s+[^;]+;\s*/i', '', $sql) ?? $sql;
    $procedure = 'nonagon_apply_' . substr($version, 0, 3);
    $parts[] = '-- -----------------------------------------------------------------------------';
    $parts[] = '-- ' . $version;
    $parts[] = '-- -----------------------------------------------------------------------------';
    $parts[] = 'DELIMITER $$';
    $parts[] = 'DROP PROCEDURE IF EXISTS ' . $procedure . '$$';
    $parts[] = 'CREATE PROCEDURE ' . $procedure . '()';
    $parts[] = 'BEGIN';
    $parts[] = " IF NOT EXISTS (SELECT 1 FROM schema_migrations WHERE version='" . str_replace("'", "''", $version) . "') THEN";
    foreach (explode(';', $sql) as $statement) {
        $statement = trim($statement);
        if ($statement !== '') $parts[] = '  ' . $statement . ';';
    }
    $parts[] = "  INSERT INTO schema_migrations(version) VALUES ('" . str_replace("'", "''", $version) . "');";
    $parts[] = ' END IF;';
    $parts[] = 'END$$';
    $parts[] = 'CALL ' . $procedure . '()$$';
    $parts[] = 'DROP PROCEDURE ' . $procedure . '$$';
    $parts[] = 'DELIMITER ;';
    $parts[] = '';
}

$parts[] = '-- Deployment verification';
$parts[] = "SELECT version, applied_at FROM schema_migrations WHERE CAST(LEFT(version, 3) AS UNSIGNED) BETWEEN 26 AND 45 ORDER BY version;";
$parts[] = '';

file_put_contents($outputFile, implode(PHP_EOL, $parts));
echo basename($outputFile) . ' created from ' . count($files) . " migrations.\n";
