<?php
declare(strict_types=1);
require __DIR__.'/app/investment.php';
$user=require_verified();if($_SERVER['REQUEST_METHOD']!=='POST')redirect('invest-opportunities');verify_csrf();
$action=investment_text($_POST,'action',40);$id=investment_text($_POST,'id',36);
try {
    if($action==='promoter'){
        if(!investment_is_platform($user))throw new DomainException('Platform administrator access required.',403);
        $organizationId=investment_text($_POST,'organization_id',36);$type=investment_text($_POST,'promoter_type',30);$status=investment_text($_POST,'promoter_status',20);$stage=(int)($_POST['approved_stage']??1);
        if(!rows('SELECT 1 FROM owners WHERE id=?',[$organizationId]))throw new DomainException('Organization not found.',404);
        if(!in_array($type,['OFISSA','INTERNAL','VERIFIED_COMPANY','OPEN_SUBMITTER'],true)||!in_array($status,['PENDING','VERIFIED','SUSPENDED','REJECTED'],true)||$stage<1||$stage>3)throw new DomainException('Choose a valid promoter type, status and rollout stage.',422);
        db()->prepare('INSERT INTO investment_promoters(id,organization_id,promoter_type,status,approved_stage,approved_by,approved_at) VALUES(?,?,?,?,?,?,IF(?=\'VERIFIED\',UTC_TIMESTAMP(),NULL)) ON DUPLICATE KEY UPDATE promoter_type=VALUES(promoter_type),status=VALUES(status),approved_stage=VALUES(approved_stage),approved_by=VALUES(approved_by),approved_at=IF(VALUES(status)=\'VERIFIED\',UTC_TIMESTAMP(),approved_at)')->execute([uuid(),$organizationId,$type,$status,$stage,$user['id'],$status]);
        flash('success','Opportunity Promoter access updated.');redirect('invest-opportunities');
    }
    if($action==='save'){$id=investment_save($user,$_POST);flash('success','Opportunity draft saved with a new version.');redirect('invest-opportunity-edit?id='.rawurlencode($id));}
    if($action==='transition'){investment_transition($user,$id,investment_text($_POST,'to_status',30));flash('success','Opportunity status updated.');redirect('invest-opportunity?id='.rawurlencode($id));}
    $op=investment_opportunity($user,$id);
    if(in_array($action,['interest_test','evidence','document'],true)&&!operation_can_manage($user))throw new DomainException('Opportunity manager access required.',403);
    if($action==='interest_test'){
        if(!in_array($op['status'],['SUBMITTED','DUE_DILIGENCE','REVIEW','APPROVED','CONDITIONAL'],true))throw new DomainException('Submit the proposal before interest testing.',409);
        db()->prepare("UPDATE investment_opportunities SET publication_mode='INTEREST_TESTING' WHERE id=?")->execute([$id]);investment_event($user,$id,'INTEREST_TESTING_ENABLED',null,null);flash('success','Interest testing is live. It remains clearly labelled as not approved for investment.');
    } elseif($action==='evidence'){
        $type=investment_text($_POST,'evidence_type',40);$strength=investment_text($_POST,'evidence_strength',30);
        $types=['VIEW','LIKE','SAVE','FOLLOW','SHARE','INVESTOR_INTEREST','INDICATIVE_INVESTMENT','CUSTOMER_INTEREST','ENQUIRY','VERIFIED_REQUEST','LOI','TENDER','CONTRACT','COMPARABLE_LEASE','UTILIZATION','SUPPLY_SHORTAGE','OTHER'];
        if(!in_array($type,$types,true)||!in_array($strength,['DISCOVERY_SIGNAL','SOFT_INTEREST','VERIFIED_COMMERCIAL'],true))throw new DomainException('Choose a valid evidence classification.',422);
        if(in_array($type,['VIEW','LIKE','SAVE','FOLLOW','SHARE'],true)&&$strength!=='DISCOVERY_SIGNAL')throw new DomainException('Views, likes, saves, follows and shares are discovery signals only.',422);
        $title=investment_text($_POST,'evidence_title',255);if($title==='')throw new DomainException('Add an evidence title.',422);
        db()->prepare('INSERT INTO investment_demand_evidence(id,opportunity_id,evidence_type,evidence_strength,title,counterparty,amount,currency,evidence_date,verified_at,verified_by,source_url,notes,created_by) VALUES(?,?,?,?,?,?,?,?,?,IF(?=\'VERIFIED_COMMERCIAL\',UTC_TIMESTAMP(),NULL),IF(?=\'VERIFIED_COMMERCIAL\',?,NULL),?,?,?)')->execute([uuid(),$id,$type,$strength,$title,investment_text($_POST,'counterparty',255),investment_number($_POST,'amount')?:null,strtoupper(investment_text($_POST,'evidence_currency',3))?:null,investment_text($_POST,'evidence_date',10)?:null,$strength,$strength,$user['id'],investment_text($_POST,'source_url',500),investment_text($_POST,'evidence_notes'),$user['id']]);investment_event($user,$id,'DEMAND_EVIDENCE_ADDED',null,null,['type'=>$type,'strength'=>$strength]);flash('success','Demand evidence added with its strength classification.');
    } elseif($action==='review'){
        if(!investment_can_review($user))throw new DomainException('Administrator review access required.',403);
        $area=investment_text($_POST,'review_area',40);$status=investment_text($_POST,'review_status',20);if(!in_array($area,INVESTMENT_REVIEW_AREAS,true)||!in_array($status,['IN_REVIEW','APPROVED','CONDITIONAL','REJECTED'],true))throw new DomainException('Invalid review decision.',422);
        if($user['id']===$op['created_by']&&in_array($status,['APPROVED','REJECTED'],true))throw new DomainException('The proposal creator cannot provide the final approval or rejection for a review area.',409);
        db()->prepare('INSERT INTO investment_due_diligence_reviews(id,opportunity_id,review_area,status,reviewer_id,findings,conditions,blockers,reviewed_at) VALUES(?,?,?,?,?,?,?,?,IF(? IN (\'APPROVED\',\'CONDITIONAL\',\'REJECTED\'),UTC_TIMESTAMP(),NULL)) ON DUPLICATE KEY UPDATE status=VALUES(status),reviewer_id=VALUES(reviewer_id),findings=VALUES(findings),conditions=VALUES(conditions),blockers=VALUES(blockers),reviewed_at=VALUES(reviewed_at)')->execute([uuid(),$id,$area,$status,$user['id'],investment_text($_POST,'findings'),investment_text($_POST,'conditions'),investment_text($_POST,'blockers'),$status]);investment_event($user,$id,'DUE_DILIGENCE_REVIEWED',null,null,['area'=>$area,'decision'=>$status]);flash('success','Due-diligence review recorded.');
    } elseif($action==='document'){
        $file=$_FILES['document']??[];if(($file['error']??UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_OK)throw new DomainException('Choose a document to upload.',422);if(($file['size']??0)>10*1024*1024)throw new DomainException('Documents must be 10 MB or smaller.',422);
        $mime=(new finfo(FILEINFO_MIME_TYPE))->file((string)$file['tmp_name']);$allowed=['application/pdf','image/jpeg','image/png','application/vnd.openxmlformats-officedocument.wordprocessingml.document','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];if(!in_array($mime,$allowed,true))throw new DomainException('Upload a PDF, DOCX, XLSX, JPEG or PNG file.',422);
        $type=investment_text($_POST,'document_type',40);$types=['TECHNICAL','QUOTATION','WARRANTY','DEMAND','LOI','TENDER','CONTRACT','FINANCIAL_MODEL','LEGAL','INSURANCE','INVESTMENT_MEMORANDUM','OTHER'];if(!in_array($type,$types,true))$type='OTHER';
        db()->prepare('INSERT INTO investment_opportunity_documents(id,opportunity_id,document_type,title,file_name,mime_type,file_size,file_data,visibility,uploaded_by) VALUES(?,?,?,?,?,?,?,?,?,?)')->execute([uuid(),$id,$type,investment_text($_POST,'document_title',255)?:basename((string)$file['name']),substr(basename((string)$file['name']),0,255),$mime,(int)$file['size'],file_get_contents((string)$file['tmp_name']),($_POST['visibility']??'')==='INVESTOR'?'INVESTOR':'PRIVATE_REVIEW',$user['id']]);investment_event($user,$id,'DOCUMENT_UPLOADED',null,null,['type'=>$type]);flash('success','Due-diligence document uploaded.');
    } else throw new DomainException('Unknown investment action.',422);
    redirect('invest-opportunity?id='.rawurlencode($id));
} catch(DomainException $e){flash('error',$e->getMessage());redirect($id?'invest-opportunity?id='.rawurlencode($id):'invest-opportunities');}
catch(Throwable $e){error_log('Investment action: '.$e->getMessage());flash('error','The investment action could not be completed.');redirect($id?'invest-opportunity?id='.rawurlencode($id):'invest-opportunities');}
