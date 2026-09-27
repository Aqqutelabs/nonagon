<?php
$publicActive='marketplace';
$publicMainClass='market-main';
$publicStyles=['assets/css/marketplace.css','assets/css/marketplace-landing.css','assets/css/marketplace-negotiation.css','assets/css/request-supply.css','assets/css/marketplace-demand.css','assets/css/request-card-listing.css'];
require __DIR__.'/public-header.php';
?>
<?php foreach(['success','error'] as $kind):if(function_exists('flash')&&$message=flash($kind)):?><div class="market-notice <?= $kind ?>" role="<?= $kind==='error'?'alert':'status' ?>"><?= e($message) ?></div><?php endif;endforeach;?>
