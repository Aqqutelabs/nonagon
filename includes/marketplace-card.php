<?php $specs=json_decode($listing['public_specifications']??'{}',true)?:[];$available=$listing['marketplace_status']==='AVAILABLE';?>
<?php $cardViewer=current_user();$cardSaved=$cardViewer?(bool)rows('SELECT 1 FROM marketplace_saved_listings WHERE user_id=? AND listing_id=?',[$cardViewer['id'],$listing['id']]):false;?>
<article class="market-card">
  <a class="market-card-image" href="marketplace-listing?id=<?= e($listing['id']) ?>">
    <img src="marketplace-media?listing_id=<?= e($listing['id']) ?>" alt="<?= e($listing['title']) ?>" loading="lazy">
    <?php if(!$available):?><span class="market-ribbon"><?= e(ucfirst(strtolower(str_replace('_',' ',$listing['marketplace_status'])))) ?></span><?php endif;?>
    <span class="market-purpose"><?= e(marketplace_purpose_label($listing['purpose'])) ?></span>
  </a>
  <div class="market-card-body">
    <p class="market-card-type"><?= e($listing['type_name']?:$listing['category_name']?:'Equipment') ?></p>
    <h2><a href="marketplace-listing?id=<?= e($listing['id']) ?>"><?= e($listing['title']) ?></a></h2>
    <p class="market-make"><?= e(implode(' · ',array_filter([$listing['oem_name'],$listing['model_name']]))) ?></p>
    <p class="market-location"><span aria-hidden="true">⌖</span> <?= e(implode(', ',array_filter([$listing['city'],$listing['state_region'],$listing['country']]))) ?></p>
    <?php if($specs):?><p class="market-spec-line"><span aria-hidden="true">⚙</span> <?= e(implode(' · ',array_slice(array_map(fn($k,$v)=>$k.': '.$v,array_keys($specs),array_values($specs)),0,3))) ?></p><?php endif;?>
    <p class="market-owner">Listed by <a href="marketplace-company?id=<?= e($listing['organization_id']) ?>"><?= e($listing['owner_name']?:'Marketplace owner') ?></a><?= $listing['marketplace_verified']?' ✓':'' ?></p>
    <div class="market-card-price"><span><?= $listing['purpose']==='SALE'?'Sale price':'Rate' ?></span><strong><?= e(marketplace_price($listing)) ?></strong></div>
    <div class="market-card-action-row">
      <a class="market-card-action" href="marketplace-listing?id=<?= e($listing['id']) ?>">View equipment <span>→</span></a>
      <div class="market-card-tools"><button type="button" data-market-share="marketplace-listing?id=<?= e($listing['id']) ?>" aria-label="Share <?= e($listing['title']) ?>"><span aria-hidden="true">↗</span></button><?php if($cardViewer&&$cardViewer['owner_id']!==$listing['organization_id']):?><form action="marketplace-save-action" method="post"><?= csrf_field() ?><input type="hidden" name="entity" value="listing"><input type="hidden" name="id" value="<?= e($listing['id']) ?>"><input type="hidden" name="return_to" value="marketplace"><button aria-label="<?= $cardSaved?'Remove from saved':'Save' ?> <?= e($listing['title']) ?>"><span aria-hidden="true"><?= $cardSaved?'♥':'♡' ?></span></button></form><?php elseif(!$cardViewer):?><a href="login" aria-label="Sign in to save <?= e($listing['title']) ?>"><span aria-hidden="true">♡</span></a><?php endif;?></div>
    </div>
  </div>
</article>
