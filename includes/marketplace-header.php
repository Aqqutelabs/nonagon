<?php
$publicActive='marketplace';
$publicMainClass='market-main';
$marketCardCss='assets/css/request-card-listing.css?v='.filemtime(APP_ROOT.'/assets/css/request-card-listing.css');
$marketResilienceJs='assets/js/marketplace-resilience.js?v='.filemtime(APP_ROOT.'/assets/js/marketplace-resilience.js');
$publicHeaderLogo='assets/images/eq-logo-dark.png';
$publicFooterLogo='assets/images/eq-logo-white.png';
$publicLogoAlt='Equipment Marketplace';
$publicFooterClass='marketplace-brand-footer';
$publicStyles=['assets/css/marketplace.css','assets/css/marketplace-landing.css','assets/css/marketplace-negotiation.css','assets/css/request-supply.css','assets/css/marketplace-demand.css',$marketCardCss,'assets/css/request-distribution.css','assets/css/marketplace-brand.css'];
$publicScripts=[$marketResilienceJs];
require __DIR__.'/public-header.php';
?>
<?php foreach(['success','error'] as $kind):if(function_exists('flash')&&$message=flash($kind)):?><div class="market-notice <?= $kind ?>" role="<?= $kind==='error'?'alert':'status' ?>"><?= e($message) ?></div><?php endif;endforeach;?>
