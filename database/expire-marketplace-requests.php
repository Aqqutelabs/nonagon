<?php
declare(strict_types=1);if(PHP_SAPI!=='cli'){http_response_code(404);exit;}require dirname(__DIR__).'/app/request-supply.php';$before=(int)(rows("SELECT COUNT(*) n FROM marketplace_requests WHERE status IN ('OPEN','RESPONSES_RECEIVED') AND response_deadline<=UTC_TIMESTAMP()")[0]['n']??0);request_expire();echo "Expired {$before} marketplace request".($before===1?'':'s').".\n";
