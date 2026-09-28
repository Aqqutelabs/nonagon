<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
require dirname(__DIR__).'/app/commercial.php';
$pdo=db();$owner=uuid();$userId=uuid();$checks=0;
function cc(bool $ok,string $message):void{global $checks;if(!$ok)throw new RuntimeException($message);$checks++;}
try{
    $pdo->prepare('INSERT INTO owners(id,name,email,phone) VALUES(?,?,?,?)')->execute([$owner,'Commercial Test Ltd','commercial-test-'.substr($owner,0,8).'@example.invalid','000']);
    $pdo->prepare("INSERT INTO users(id,owner_id,full_name,email,phone,password_hash,is_email_verified,role) VALUES(?,?,?,?,?,?,1,'OWNER_ADMIN')")->execute([$userId,$owner,'Commercial Tester','commercial-user-'.substr($owner,0,8).'@example.invalid','000',password_hash('Testing123',PASSWORD_DEFAULT)]);
    $user=rows('SELECT u.*,o.name company_name FROM users u JOIN owners o ON o.id=u.owner_id WHERE u.id=?',[$userId])[0];
    commercial_profile_save($user,['legal_name'=>'Commercial Test Ltd','default_currency'=>'NGN','default_vat'=>'7.5','quote_prefix'=>'QT','invoice_prefix'=>'INV']);
    $brand=commercial_brand_save($user,['brand_name'=>'Commercial Test Brand','company_name'=>'Commercial Test Ltd','creation_mode'=>'ELEMENTS','addresses'=>['1 Test Road','Operations Yard'],'phones'=>['08000000000'],'emails'=>['billing@example.invalid'],'website'=>'https://example.invalid','is_default'=>'1'],[]);
    $customer=commercial_customer_save($user,['name'=>'Industrial Buyer','currency'=>'NGN','tin'=>'TIN-100']);
    $input=['customer_id'=>$customer,'currency'=>'NGN','issue_date'=>gmdate('Y-m-d'),'valid_until'=>gmdate('Y-m-d',time()+864000),'customer_po'=>'PO-100','item_description'=>['Crane rental'],'item_quantity'=>['1'],'item_unit'=>['Day'],'item_duration'=>['10'],'item_rate'=>['350000'],'item_discount'=>['0'],'item_tax'=>['7.5'],'adjustment_total'=>'0'];
    $quote=commercial_document_save($user,$input,'QUOTATION');commercial_document_brand_set($user,$quote,$brand);$q=commercial_document($user,$quote);
    cc((float)$q['grand_total']===3762500.0,'Duration-aware quotation calculation failed.');
    cc(str_starts_with($q['document_number'],'QT-'),'Quotation numbering failed.');
    commercial_issue($user,$quote);$issued=commercial_document($user,$quote);
    cc($issued['issued_snapshot']!==null&&$issued['status']==='ISSUED','Issue snapshot failed.');
    cc((json_decode($issued['issued_snapshot'],true)['brand']['company_name']??'')==='Commercial Test Ltd','Issued brand snapshot failed.');
    $invoice=commercial_convert_quote($user,$quote);$inv=commercial_document($user,$invoice);
    cc($inv['converted_from_id']===$quote&&count($inv['items'])===1,'Quote conversion failed.');
    cc((int)rows('SELECT COUNT(*) n FROM commercial_document_events WHERE document_id=?',[$quote])[0]['n']>=3,'Document history missing.');
    cc(strlen(commercial_pdf(commercial_snapshot($user,$invoice)))>500,'PDF generation failed.');
    echo "PASS: {$checks} Commercial Sprint 1 checks.\n";
}catch(Throwable $e){fwrite(STDERR,'FAIL: '.$e->getMessage()."\n");$failed=true;}
finally{
    $pdo->prepare('DELETE FROM audit_logs WHERE owner_id=?')->execute([$owner]);
    $pdo->prepare('DELETE FROM commercial_documents WHERE organization_id=? AND converted_from_id IS NOT NULL')->execute([$owner]);
    $pdo->prepare('DELETE FROM commercial_documents WHERE organization_id=?')->execute([$owner]);
    $pdo->prepare('DELETE FROM commercial_brands WHERE organization_id=?')->execute([$owner]);
    $pdo->prepare('DELETE FROM commercial_customers WHERE organization_id=?')->execute([$owner]);
    $pdo->prepare('DELETE FROM commercial_bank_accounts WHERE organization_id=?')->execute([$owner]);
    $pdo->prepare('DELETE FROM commercial_profiles WHERE organization_id=?')->execute([$owner]);
    $pdo->prepare('DELETE FROM users WHERE owner_id=?')->execute([$owner]);
    $pdo->prepare('DELETE FROM owners WHERE id=?')->execute([$owner]);
}
exit(!empty($failed)?1:0);
