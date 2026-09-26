<?php
declare(strict_types=1);
require __DIR__.'/app/bootstrap.php';

$code=(string)($_GET['code']??'');
if(!preg_match('/^\d{6}$/',$code)){
    http_response_code(404);
    exit('Share link not found.');
}

$company=rows("SELECT id FROM owners WHERE LPAD(MOD(CRC32(id),1000000),6,'0')=? LIMIT 1",[$code])[0]??null;
if(!$company){
    http_response_code(404);
    exit('Share link not found.');
}

redirect('../marketplace-company?id='.rawurlencode($company['id']));
