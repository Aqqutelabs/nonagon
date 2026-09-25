<?php
require_once __DIR__.'/equipment-catalog.php';
$user=require_verified();$active='equipment';$pageTitle='Equipment';
function register_value(string $key): string {return is_string($_GET[$key]??null)?trim($_GET[$key]):'';}
function register_link(array $changes): string {return 'equipment?'.http_build_query(array_merge($_GET,['cursor'=>'','page'=>1],$changes));}
function register_time(?string $value): string {if(!$value)return 'No activity yet';$seconds=max(0,time()-strtotime($value.' UTC'));return $seconds<60?'Just now':($seconds<3600?floor($seconds/60).'m ago':($seconds<86400?floor($seconds/3600).'h ago':floor($seconds/86400).'d ago'));}
$mode=register_value('view')==='list'?'list':'grid';
$sort=register_value('sort');$sorts=['name'=>'e.name','status'=>'e.status','activity'=>"COALESCE(e.last_activity_at,'1970-01-01 00:00:00')"];$order=$sorts[$sort]??$sorts['name'];
$direction=register_value('direction')==='desc'?'DESC':'ASC';
try {
[$scope,$params]=operation_scope($user);$baseScope=$scope;$baseParams=$params;
$tree=rows("SELECT DISTINCT e.block_id,e.block_name,e.site_id,e.site_name,e.plant_id,e.plant_name,e.unit_id,e.unit_name FROM equipment_list_view e WHERE {$scope} ORDER BY e.block_name,e.site_name,e.plant_name,e.unit_name",$params);
if($user['role']!=='OPERATOR'){
 [$locationScope,$locationParams]=operation_scope($user,'loc',[],null);
 $tree=rows("SELECT loc.* FROM (SELECT sp.owner_id,sp.id block_id,sp.name block_name,s.id site_id,s.name site_name,p.id plant_id,p.name plant_name,u.id unit_id,u.name unit_name FROM spaces sp JOIN sites s ON s.space_id=sp.id JOIN plants p ON p.site_id=s.id JOIN units u ON u.plant_id=p.id) loc WHERE {$locationScope} ORDER BY block_name,site_name,plant_name,unit_name",$locationParams);
}
foreach(['block','site','plant','unit','category','subcategory','type','brand','model'] as $field){$value=register_value($field);if($value!==''){$scope.=" AND e.{$field}_id=?";$params[]=$value;}}
$status=register_value('critical')==='1'?'DOWN':register_value('status');
if($status!==''){$scope.=' AND e.status=?';$params[]=$status;}
if(register_value('assigned')==='1')$scope.=' AND e.operator_id IS NOT NULL';
$q=mb_substr(register_value('q'),0,200);
if($q!==''){$scope.=" AND (CONCAT_WS(' ',e.name,e.asset_code,e.brand_name,e.model_name,e.category_name,e.subcategory_name,e.type_name,e.operator_name) LIKE ? ESCAPE '!')";$params[]='%'.str_replace(['!','%','_'],['!!','!%','!_'],$q).'%';}
$total=(int)rows("SELECT COUNT(*) n FROM equipment_list_view e WHERE {$scope}",$params)[0]['n'];
$page=max(1,min(100000,(int)register_value('page')));$limit=12;$offset=($page-1)*$limit;
if($mode==='grid' && register_value('cursor')!==''){
 $cursor=json_decode(base64_decode(register_value('cursor'),true)?:'',true);
 if(is_array($cursor)&&is_string($cursor['value']??null)&&is_string($cursor['id']??null)){$comparison=$direction==='ASC'?'>':'<';$scope.=" AND ({$order} {$comparison} ? OR ({$order}=? AND e.id {$comparison} ?))";array_push($params,$cursor['value'],$cursor['value'],$cursor['id']);}
}
$items=rows("SELECT e.*,{$order} AS cursor_value FROM equipment_list_view e WHERE {$scope} ORDER BY {$order} {$direction},e.id {$direction} LIMIT 13".($mode==='list'?" OFFSET {$offset}":''),$params);
$hasNext=count($items)>12;$items=array_slice($items,0,12);
$state=dashboard_state($user);$metrics=$state['summary']['metrics'];
$key=$state['meta']['scope'];$baseline=rows('SELECT metrics FROM dashboard_history WHERE scope_key=? AND snapshot_date=DATE_SUB(UTC_DATE(),INTERVAL 30 DAY)',[$key])[0]??null;$prior=$baseline?json_decode($baseline['metrics'],true):null;

}catch(Throwable $error){
    http_response_code(503);
    error_log('Equipment register: '.$error->getMessage());
    require __DIR__.'/../includes/operations-header.php';
    echo '<section class="panel detail-content" role="alert"><h1>Equipment is temporarily unavailable</h1><p>Your equipment could not be loaded. Please try again.</p><a class="primary" href="equipment">Retry</a></section>';
    require __DIR__.'/../includes/operations-footer.php';
    return;
}
require __DIR__.'/../includes/operations-header.php';
?>
<link rel="stylesheet" href="assets/css/equipment-register.css">
<div id="equipment-register">
<section class="kpi-grid register-kpis" aria-label="Equipment summary">
<?php foreach(['total'=>['Total Assets','equipment',['status'=>'','critical'=>'','assigned'=>'']],'active'=>['Active Assets','equipment',['status'=>'OPERATIONAL','critical'=>'']],'maintenance'=>['Under Maintenance','maintenance',['status'=>'MAINTENANCE','critical'=>'']],'operators'=>['Total Operators','users',['assigned'=>'1']]] as $key=>$card):$delta=$prior&&($prior[$key]??0)>0?round(($metrics[$key]-$prior[$key])/$prior[$key]*100,1):null;?>
<a class="kpi" href="<?= e(register_link($card[2])) ?>"><span class="kpi-icon"><?= operation_icon($card[1]) ?></span><span class="kpi-copy"><span><?= e($card[0]) ?></span><strong><?= $metrics[$key] ?></strong></span><small title="Compared with 30 days ago"><?= $delta===null?'30d: &mdash;':($delta>=0?'&#8593; +':'&#8595; ').$delta.'%' ?></small></a>
<?php endforeach;?></section>
<div class="register-layout"><section class="register-main" aria-label="Equipment register">
<form method="get" action="equipment" id="register-filters">
<input type="hidden" name="view" value="<?= $mode ?>">
<?php foreach(['block','site','plant','unit','assigned'] as $field):if(register_value($field)!==''):?><input type="hidden" name="<?= $field ?>" value="<?= e(register_value($field)) ?>"><?php endif;endforeach;?>
<label class="register-search"><span class="sr-only">Search equipment</span><input type="search" name="q" value="<?= e($q) ?>" placeholder="Search equipment name, asset ID, brand, model, category or operator..."><?= operation_icon('search') ?></label>
<div class="register-controls"><label><span class="sr-only">Status</span><select name="status"><option value="">All statuses</option><?php foreach(['OPERATIONAL'=>'Operational','MAINTENANCE'=>'Maintenance','DOWN'=>'Critical'] as $value=>$label):?><option value="<?= $value ?>" <?= register_value('status')===$value?'selected':'' ?>><?= $label ?></option><?php endforeach;?></select></label>
<?php $categories=rows("SELECT DISTINCT e.category_id id,e.category_name name FROM equipment_list_view e WHERE {$baseScope} AND e.category_id IS NOT NULL ORDER BY name",$baseParams);?><label><span class="sr-only">Category</span><select name="category"><option value="">All categories</option><?php foreach($categories as $entry):?><option value="<?= e($entry['id']) ?>" <?= register_value('category')===$entry['id']?'selected':'' ?>><?= e($entry['name']) ?></option><?php endforeach;?></select></label>
<label class="critical-toggle"><input type="checkbox" name="critical" value="1" <?= register_value('critical')==='1'?'checked':'' ?>> Critical only</label>
<div class="view-switch" aria-label="Display mode"><a aria-label="Card view" aria-current="<?= $mode==='grid'?'true':'false' ?>" href="<?= e(register_link(['view'=>'grid'])) ?>">&#9638;</a><a aria-label="List view" aria-current="<?= $mode==='list'?'true':'false' ?>" href="<?= e(register_link(['view'=>'list'])) ?>">&#9776;</a></div></div>
<details class="register-more" <?= array_filter(array_map('register_value',['subcategory','type','brand','model']))?'open':'' ?>><summary>More filters & sorting</summary><div class="register-controls">
<?php foreach(['subcategory','type','brand','model'] as $field):$options=rows("SELECT DISTINCT e.{$field}_id id,e.{$field}_name name FROM equipment_list_view e WHERE {$baseScope} AND e.{$field}_id IS NOT NULL ORDER BY name",$baseParams);?><label><?= ucfirst($field) ?><select name="<?= $field ?>"><option value="">All</option><?php foreach($options as $entry):?><option value="<?= e($entry['id']) ?>" <?= register_value($field)===$entry['id']?'selected':'' ?>><?= e($entry['name']) ?></option><?php endforeach;?></select></label><?php endforeach;?>
<label>Sort<select name="sort"><?php foreach(['name'=>'Name','status'=>'Status','activity'=>'Last activity'] as $value=>$label):?><option value="<?= $value ?>" <?= $sort===$value?'selected':'' ?>><?= $label ?></option><?php endforeach;?></select></label><label>Order<select name="direction"><option value="asc">Ascending</option><option value="desc" <?= $direction==='DESC'?'selected':'' ?>>Descending</option></select></label></div></details>
<div class="register-filter-footer"><button type="submit">Apply filters</button><a href="equipment?view=<?= $mode ?>">Clear all</a><span role="status"><?= $total ?> equipment found</span></div>
</form>
<?php if(!$items):?><div class="register-empty"><h2><?= $user['role']==='OPERATOR' && !$tree?'No equipment assigned to you yet.':'No equipment found' ?></h2><p>Try clearing your filters or selecting another location.</p></div><?php elseif($mode==='grid'):?><div class="register-grid">
<?php foreach($items as $item):$detail='equipment?id='.urlencode($item['id']);?><article class="equipment-card"><img src="equipment-photo?equipment_id=<?= e($item['id']) ?>" alt="" loading="lazy"><div class="equipment-card-title"><div><a class="card-target" href="<?= e($detail) ?>"><strong><?= e($item['name']) ?></strong></a><small><?= e($item['asset_code']) ?></small></div><?= operation_badge($item['status']) ?></div><div class="equipment-card-meta"><p><b>Category:</b> <?= e($item['category_name']??'Uncategorized') ?></p><?php if($item['type_name']):?><p><b>Type:</b> <?= e($item['type_name']) ?></p><?php endif;?><p><b>Location:</b> <?= e($item['plant_name'].' / '.$item['unit_name']) ?></p></div><footer><strong><?= e($item['operator_name']??'Unassigned') ?></strong><time title="<?= e($item['last_activity_at']??'') ?>"><?= e(register_time($item['last_activity_at'])) ?></time></footer><div class="card-actions"><a href="<?= e($detail) ?>">View</a><?php if(equipment_can_action($user,'change_status')):?><a href="<?= e($detail) ?>#change-status">Change status</a><?php endif;?><?php if(equipment_can_action($user,'operators')):?><a href="<?= e($detail) ?>#assign-operator">Assign operator</a><?php endif;?></div></article><?php endforeach;?></div>
<?php else:?><div class="table-wrap register-table"><table><thead><tr><?php foreach(['name'=>'Name / Asset ID','category'=>'Category','location'=>'Location','status'=>'Status','operator'=>'Operator','activity'=>'Last activity','actions'=>'Actions'] as $key=>$label):?><th><?php if(isset($sorts[$key])):?><a href="<?= e(register_link(['sort'=>$key,'direction'=>$sort===$key&&$direction==='ASC'?'desc':'asc'])) ?>"><?= $label ?> &#8597;</a><?php else:echo $label;endif;?></th><?php endforeach;?></tr></thead><tbody><?php foreach($items as $item):?><tr><td><a href="equipment?id=<?= e($item['id']) ?>"><?= e($item['name']) ?></a><small><?= e($item['asset_code']) ?></small></td><td><?= e($item['category_name']??'Uncategorized') ?></td><td><?= e($item['plant_name'].' / '.$item['unit_name']) ?></td><td><?= operation_badge($item['status']) ?></td><td><?= e($item['operator_name']??'Unassigned') ?></td><td><?= e(register_time($item['last_activity_at'])) ?></td><td><a href="equipment?id=<?= e($item['id']) ?>">View</a><?php if(equipment_can_action($user,'change_status')):?><a href="equipment?id=<?= e($item['id']) ?>#change-status">Status</a><?php endif;?><?php if(equipment_can_action($user,'operators')):?><a href="equipment?id=<?= e($item['id']) ?>#assign-operator">Assign</a><?php endif;?></td></tr><?php endforeach;?></tbody></table></div><?php endif;?>
<nav class="register-pagination" aria-label="Equipment pages"><span>Showing <?= count($items) ?> of <?= $total ?></span><?php if($mode==='list'&&$page>1):?><a href="<?= e(register_link(['page'=>$page-1])) ?>">Previous</a><?php elseif(register_value('cursor')!==''):?><a href="<?= e(register_link([])) ?>">First page</a><?php endif;?><?php if($hasNext):$last=end($items);?><a href="<?= e(register_link($mode==='grid'?['cursor'=>base64_encode(json_encode(['value'=>(string)$last['cursor_value'],'id'=>$last['id']]))]:['page'=>$page+1])) ?>">Next</a><?php endif;?></nav>
</section><aside class="register-locations"><?php if(operation_can_manage($user)):?><a class="primary register-add" href="equipment?create=1">+ Add New Equipment</a><?php endif;?><h2>Location</h2><small>Filter by location</small><a class="location-node <?= !array_filter(array_map('register_value',['block','site','plant','unit']))?'selected':'' ?>" href="<?= e(register_link(['block'=>'','site'=>'','plant'=>'','unit'=>''])) ?>">All Locations</a>
<?php
$nodes=[];foreach($tree as $loc){$cursor=&$nodes;foreach(['block','site','plant','unit'] as $level){$id=$loc[$level.'_id'];if(!isset($cursor[$id]))$cursor[$id]=['level'=>$level,'name'=>$loc[$level.'_name'],'children'=>[]];$cursor=&$cursor[$id]['children'];}unset($cursor);}
$render=function(array $nodes)use(&$render){foreach($nodes as $id=>$node){$changes=['block'=>'','site'=>'','plant'=>'','unit'=>''];$changes[$node['level']]=$id;$link='<a class="location-node '.(register_value($node['level'])===$id?'selected':'').'" href="'.e(register_link($changes)).'">'.e($node['name']).'</a>';if($node['children']){echo '<details open><summary>'.$link.'</summary><div>';$render($node['children']);echo '</div></details>';}else echo $link;}};$render($nodes);
?></aside></div></div><script src="assets/js/equipment-register.js" defer></script>
<?php require __DIR__.'/../includes/operations-footer.php';?>
