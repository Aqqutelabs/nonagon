<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require dirname(__DIR__) . '/app/operations.php';
$original = $appConfig;
$checks = 0;
try {
    foreach (['local','production','staging'] as $environment) {
        foreach ([false,true] as $enabled) {
            $appConfig['app']['environment'] = $environment;
            $appConfig['app']['bypass_email_verification'] = $enabled;
            $expected = $environment === 'local' && $enabled;
            if (email_access_allowed(['is_email_verified'=>0]) !== $expected) throw new RuntimeException('Verification bypass environment guard failed.');
            if (!email_access_allowed(['is_email_verified'=>1])) throw new RuntimeException('Verified account denied.');
            if (operation_can_manage(['is_active'=>0,'is_email_verified'=>0,'role'=>'OWNER_ADMIN'])) throw new RuntimeException('Inactive account allowed.');
            if (operation_can_manage(['is_active'=>1,'is_email_verified'=>0,'role'=>'OPERATOR'])) throw new RuntimeException('Read-only role elevated.');
            $checks += 4;
        }
    }
    echo "PASS: {$checks} verification bypass checks.\n";
} finally { $appConfig = $original; }
