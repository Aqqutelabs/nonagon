<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
require dirname(__DIR__).'/app/xinng.php';
$checks=0;
function xinng_qr_check(bool $condition,string $message):void{global $checks;if(!$condition)throw new RuntimeException($message);$checks++;}

$id='12345678-1234-4234-8234-123456789abc';
$destination='https://nonagon.example/equipment-public?token='.str_repeat('a',64);
$equipmentSlug=xinng_back_half('equipment',$id);
$xinng_qr_check_pattern='/^[a-z0-9]{4}$/';
xinng_qr_check((bool)preg_match($xinng_qr_check_pattern,$equipmentSlug),'Equipment back-half must be exactly four lowercase alphanumeric characters.');
$nextEquipmentSlug=xinng_back_half('equipment',$id);
xinng_qr_check((bool)preg_match($xinng_qr_check_pattern,$nextEquipmentSlug),'Every generated back-half must be exactly four lowercase letters.');
$qrPayload=xinng_qr_create_payload('equipment',$id,'Test equipment',$destination);
xinng_qr_check(($qrPayload['title']??null)==='Test equipment'&&($qrPayload['type']??null)==='website'&&($qrPayload['destination_url']??null)===$destination,'QR creation must include the title, website type and complete destination URL.');
xinng_qr_check(isset($qrPayload['back_half'])&&preg_match($xinng_qr_check_pattern,$qrPayload['back_half'])===1,'QR creation must use a four-letter back-half.');
xinng_qr_check((bool)preg_match($xinng_qr_check_pattern,xinng_back_half('certificate',$id)),'Certificate codes must be four lowercase alphanumeric characters.');
xinng_qr_check((bool)preg_match($xinng_qr_check_pattern,xinng_back_half('request',$id)),'Request codes must be four lowercase alphanumeric characters.');
xinng_qr_check((bool)preg_match($xinng_qr_check_pattern,xinng_back_half('opportunity',$id)),'Opportunity codes must be four lowercase alphanumeric characters.');
xinng_qr_check(xinng_resource_settings('certificate')[0]==='qhse_certificates','Certificates must persist xin.ng links on their own records.');
xinng_qr_check(xinng_resource_settings('opportunity')[0]==='marketplace_listings','Opportunities must persist xin.ng links on marketplace listings.');
$taken=new XinngApiException(409,['error'=>'This back-half is already taken.'],'https://xin.ng/api/short-links.php','POST');
xinng_qr_check(xinng_back_half_taken($taken),'Explicit back-half conflicts must be recognized for retry.');
$requiresConfirmation=new XinngApiException(409,['error'=>'requires_confirmation','requires_confirmation'=>true],'https://xin.ng/api/short-links.php','PATCH');
xinng_qr_check(!xinng_back_half_taken($requiresConfirmation),'Destination-change confirmation conflicts must not be treated as slug collisions.');
$qrResponseBody='<br />'."\n".'<b>Warning</b>:  Undefined array key "type" in <b>/home/example/public_html/api/qr-codes.php</b> on line <b>88</b><br />'."\n".'{"ok":true,"qr_code":{"id":0,"type":"website","title":"Test product","destination_url":"https://example.com/product","back_half":"test-product","full_short_url":"http://localhost/xinngqr/test-product","qr_image_url":"https://api.qrserver.com/v1/create-qr-code/?size=260x260&data=https%3A%2F%2Fexample.com%2Fproduct"}}';
$decodedQrResponse=xinng_decode_api_response($qrResponseBody,'POST','https://xin.ng/api/qr-codes.php',200);
xinng_qr_check(($decodedQrResponse['ok']??false)===true&&($decodedQrResponse['qr_code']['type']??null)==='website','A PHP warning before a valid QR JSON response must not prevent decoding.');
$invalidResponseRejected=false;
try{xinng_decode_api_response('<html>not JSON</html>','POST','https://xin.ng/api/qr-codes.php',200);}catch(DomainException){$invalidResponseRejected=true;}
xinng_qr_check($invalidResponseRejected,'Non-JSON API responses must continue to fail explicitly.');

$appConfig['xinng']['public_base_url']='https://xin.ng';
$localLink=xinng_link_from_response(['short_link'=>['id'=>'42','full_short_url'=>'http://localhost/xinngqr/abcd','back_half'=>'abcd']]);
xinng_qr_check($localLink['url']==='https://xin.ng/abcd','Local Xinng short URLs must use the configured HTTPS public base without the xinngqr path.');
$hostedLink=xinng_link_from_response(['short_link'=>['id'=>'44','full_short_url'=>'https://xin.ng/xinngqr/abcd','back_half'=>'abcd']]);
xinng_qr_check($hostedLink['url']==='https://xin.ng/abcd','Hosted Xinng short URLs must not retain the xinngqr path.');
xinng_qr_check(xinng_strip_xinngqr_path('https://xin.ng/xinngqr/abcd')==='https://xin.ng/abcd','Previously saved Xinng URLs must be normalized for display and reuse.');
xinng_qr_check(xinng_strip_xinngqr_path('https://xin.ng/abcd')==='https://xin.ng/abcd','Root-level Xinng URLs must remain unchanged.');
xinng_qr_check(xinng_short_url_has_back_half('https://xin.ng/abcd','abcd'),'A valid saved URL must use its four-letter back-half.');
xinng_qr_check(xinng_short_url_has_back_half('https://xin.ng/a7k2','a7k2'),'A valid saved URL may use a four-character alphanumeric back-half.');
xinng_qr_check(!xinng_short_url_has_back_half('https://xin.ng/xinngqr/ng-eq-3b2d0d50055640b892b7-209df949','ng-eq-3b2d0d50055640b892b7-209df949'),'Saved URLs with long back-halves must be rejected.');
xinng_qr_check(!xinng_short_url_has_back_half('https://xin.ng/xinngqr/abcd','wxyz'),'A saved back-half must match the final URL path segment.');
$imageUrl=xinng_public_qr_image_url('https://api.qrserver.com/v1/create-qr-code/?size=260x260&data='.rawurlencode('http://localhost/xinngqr/abcd'),'https://xin.ng/abcd');
parse_str((string)parse_url($imageUrl,PHP_URL_QUERY),$imageParameters);
xinng_qr_check(($imageParameters['data']??null)==='https://xin.ng/abcd','QR images must encode the same public short URL shown to users.');
$qrList=xinng_qr_link_for_destination(['qr_codes'=>[['id'=>42,'destination_url'=>$destination,'back_half'=>'abcd','full_short_url'=>'http://localhost/xinngqr/abcd','qr_image_url'=>'https://api.qrserver.com/v1/create-qr-code/?data='.rawurlencode('http://localhost/xinngqr/abcd'),'status'=>'active']]],$destination);
xinng_qr_check(($qrList['url']??null)==='https://xin.ng/abcd'&&($qrList['qr_image_url']??'')!=='','Equipment QR lookup must return the public URL and its matching QR image.');
$invalidShortLinkRejected=false;
try{xinng_link_from_response(['short_link'=>['id'=>'43','full_short_url'=>'https://xin.ng/xinngqr/ng-eq-long','back_half'=>'ng-eq-long']]);}catch(DomainException){$invalidShortLinkRejected=true;}
xinng_qr_check($invalidShortLinkRejected,'Xinng responses with long back-halves must not be accepted.');

$confirmation=new XinngApiException(409,['error'=>'requires_confirmation','requires_confirmation'=>true],'https://xin.ng/api/short-links.php','PATCH');
xinng_qr_check(str_contains($confirmation->getMessage(),'Confirm creation'),'Destination changes must require confirmation.');
$credits=new XinngApiException(402,['error'=>'insufficient_credits'],'https://xin.ng/api/short-links.php','POST');
xinng_qr_check(str_contains($credits->getMessage(),'HTTP 402'),'Xinng API policy failures must be actionable.');
$unauthorized=new XinngApiException(401,['error'=>'user_id_required','message'=>'Send your customer UUID.'],'http://localhost/xinngqr/api/short-links.php','POST');
xinng_qr_check(str_contains($unauthorized->getMessage(),'user_id_required')&&str_contains($unauthorized->getMessage(),'Send your customer UUID.')&&str_contains($unauthorized->getMessage(),'http://localhost/xinngqr/api/short-links.php'),'401 errors must include the API endpoint and the returned explanation.');
$transportError=new XinngTransportException('POST','http://localhost/xinngqr/api/short-links.php','Could not resolve host: localhost');
xinng_qr_check(str_contains($transportError->getMessage(),'Could not resolve host: localhost')&&str_contains($transportError->getMessage(),'http://localhost/xinngqr/api/short-links.php'),'Transport errors must expose the actual cURL failure and configured endpoint.');
try{xinng_api_call('GET','87654321-4321-4321-8321-cba987654321');throw new RuntimeException('GET requests to the POST-only short-link API must be rejected.');}catch(InvalidArgumentException $error){xinng_qr_check(str_contains($error->getMessage(),'does not support GET'),'GET requests must fail clearly instead of reaching the API.');}

$appConfig['xinng']['api_base_url']='https://xin.ng';
$customerId='87654321-4321-4321-8321-cba987654321';
$endpoint=xinng_api_endpoint('short-links.php');
xinng_qr_check($endpoint==='https://xin.ng/api/short-links.php','Xinng API base URLs must resolve to the short-link endpoint.');
$appConfig['xinng']['api_base_url']='http://localhost/xinngqr/api/short-links.php';
$endpoint=xinng_api_endpoint('short-links.php');
xinng_qr_check($endpoint==='http://localhost/xinngqr/api/short-links.php','A configured short-link endpoint must not be appended to twice.');
$qrEndpoint=xinng_api_endpoint('qr-codes.php');
xinng_qr_check($qrEndpoint==='http://localhost/xinngqr/api/qr-codes.php','The QR endpoint must be derived as a sibling of the configured short-link endpoint.');
$appConfig['xinng']['api_base_url']='http://localhost/xinngqr';
$endpoint=xinng_api_endpoint('short-links.php');
xinng_qr_check($endpoint==='http://localhost/xinngqr/api/short-links.php','A local Xinng base URL must resolve to the short-link endpoint.');
$appConfig['xinng']['api_base_url']='https://xin.ng';
$imageUrl='https://api.qrserver.com/v1/create-qr-code/?size=260x260&data='.rawurlencode('https://nonagon.example/verify?token='.str_repeat('b',32));
xinng_qr_check(xinng_validate_qr_image_url($imageUrl)===$imageUrl,'QR image URLs returned by Xinng must be accepted.');
try{xinng_validate_qr_image_url('https://attacker.example/image.png');throw new RuntimeException('Unexpected QR image hosts must be rejected.');}catch(DomainException){$checks++;}
xinng_qr_check(xinng_user_id($customerId)===$customerId,'Valid customer UUIDs must be accepted.');
try{xinng_user_id('not-a-uuid');throw new RuntimeException('Invalid customer IDs must be rejected.');}catch(InvalidArgumentException){$checks++;}

echo "PASS: {$checks} Xinng QR checks.\n";
