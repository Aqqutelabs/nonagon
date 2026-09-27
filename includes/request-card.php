<?php $requestUrl='marketplace-request?id='.rawurlencode((string)$request['id']);$deadlineIso=gmdate('Y-m-d\TH:i:s\Z',strtotime((string)$request['response_deadline'].' UTC'));?>
<article class="market-card request-card">
  <a class="market-card-image" href="<?= e($requestUrl) ?>">
    <img src="<?= e($request['request_image']??'assets/images/equipment-placeholder.svg') ?>" alt="<?= e($request['equipment_type']) ?> required" loading="lazy">
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
    <a class="market-card-action" href="<?= e($requestUrl) ?>">Respond to Request <span>→</span></a>
  </div>
</article>
