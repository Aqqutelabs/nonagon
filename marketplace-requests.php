<?php
declare(strict_types=1);
$query=$_GET;
$query['view']='requests';
header('Location: marketplace?'.http_build_query($query),true,302);
exit;
