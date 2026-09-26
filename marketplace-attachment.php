<?php
declare(strict_types=1);
require __DIR__.'/app/marketplace-negotiation.php';
$user=require_verified();$id=marketplace_text($_GET,'id',36,true);
$file=rows('SELECT a.*,q.buyer_organization_id,q.owner_organization_id FROM marketplace_message_attachments a JOIN marketplace_messages m ON m.id=a.message_id JOIN marketplace_conversations c ON c.id=m.conversation_id JOIN marketplace_enquiries q ON q.id=c.enquiry_id WHERE a.id=?',[$id])[0]??null;
if(!$file||!in_array($user['owner_id'],[$file['buyer_organization_id'],$file['owner_organization_id']],true)){http_response_code(404);exit('Attachment not found.');}
header('Cache-Control: private, no-store');header('X-Content-Type-Options: nosniff');header('Content-Type: '.$file['mime_type']);header('Content-Length: '.$file['file_size']);header("Content-Disposition: attachment; filename*=UTF-8''".rawurlencode($file['file_name']));echo $file['file_data'];
