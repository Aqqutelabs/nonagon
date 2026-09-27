<?php $requestUrl='marketplace-request?id='.rawurlencode((string)$request['id']);$deadlineIso=gmdate('Y-m-d\TH:i:s\Z',strtotime((string)$request['response_deadline'].' UTC'));$requestPhoto=rows('SELECT id FROM request_photos WHERE request_id=? ORDER BY is_primary DESC,sequence,created_at LIMIT 1',[$request['id']])[0]['id']??null;$requestImage=$requestPhoto?'request-photo?id='.rawurlencode($requestPhoto):($request['request_image']??'assets/images/equipment-placeholder.svg');$cardViewer=current_user();$requestSaved=$cardViewer?(bool)rows('SELECT 1 FROM marketplace_saved_requests WHERE user_id=? AND request_id=?',[$cardViewer['id'],$request['id']]):false;?>
<article class="market-card request-card">
  <a class="market-card-image" href="<?= e($requestUrl) ?>">
    <img src="<?= e($requestImage) ?>" alt="<?= e($request['equipment_type']) ?> required" loading="lazy">
    <span class="request-ribbon">Equipment Required</span>
    <span class="market-purpose"><?= e(ucfirst(strtolower($request['intent']))) ?></span>
  </a>
  <div class="market-card-body">
    <p class="market-card-type"><?= e($request['equipment_type']) ?></p>
    <h2><a href="<?= e($requestUrl) ?>"><?= e($request['title']) ?></a></h2>
    <p class="request-meta"><strong><?= (int)$request['quantity_required'] ?></strong> required</p>
    <p class="market-location"><span aria-hidden="true">⌖</span> <?= e(implode(', ',array_filter([$request['location_name'],$request['country_name']]))) ?></p>
    <p class="market-owner">Requested by <?= e($request['requester_name']) ?><?= !empty($request['marketplace_verified'])?' ✓':'' ?></p>
    <div class="request-countdown" data-request-countdown data-deadline="<?= e($deadlineIso) ?>"><span>Time left to respond</span><strong data-countdown-value>Calculating…</strong></div>
    <div class="market-card-action-row">
      <a class="market-card-action" href="<?= e($requestUrl) ?>">Respond to Request <span>→</span></a>
      <div class="market-card-tools"><button type="button" data-market-share="<?= e($requestUrl) ?>" aria-label="Share <?= e($request['title']) ?>"><span aria-hidden="true">↗</span></button><?php if($cardViewer&&$cardViewer['owner_id']!==$request['organization_id']):?><form action="marketplace-save-action" method="post"><?= csrf_field() ?><input type="hidden" name="entity" value="request"><input type="hidden" name="id" value="<?= e($request['id']) ?>"><input type="hidden" name="return_to" value="marketplace"><button aria-label="<?= $requestSaved?'Remove from saved':'Save' ?> <?= e($request['title']) ?>"><span aria-hidden="true"><?= $requestSaved?'♥':'♡' ?></span></button></form><?php elseif(!$cardViewer):?><a href="login" aria-label="Sign in to save <?= e($request['title']) ?>"><span aria-hidden="true">♡</span></a><?php endif;?></div>
    </div>
  </div>
</article>
