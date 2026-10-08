<?php
if(!function_exists('current_user'))require_once __DIR__.'/../app/bootstrap.php';
header('Cache-Control: no-store, private');
$pageTitle=$pageTitle??'Nonagon';
$publicActive=$publicActive??'';
$publicStyles=$publicStyles??[];
$publicMainClass=$publicMainClass??'';
$publicBodyClass=$publicBodyClass??'';
$publicHeaderLogo=$publicHeaderLogo??'assets/images/logo-dark.svg';
$publicLogoAlt=$publicLogoAlt??'Nonagon';
$publicUser=current_user();
$titleSafe=htmlspecialchars((string)$pageTitle,ENT_QUOTES,'UTF-8');
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="theme-color" content="#0a6fb8">
<title><?= $titleSafe ?> — Nonagon</title>
<link rel="icon" href="assets/images/favicon.ico" type="image/x-icon">
<link rel="stylesheet" href="assets/css/public-shell.css">
<?php foreach($publicStyles as $style):?><link rel="stylesheet" href="<?= htmlspecialchars($style,ENT_QUOTES,'UTF-8') ?>"><?php endforeach;?>
</head>
<body<?= $publicBodyClass!==''?' class="'.htmlspecialchars($publicBodyClass,ENT_QUOTES,'UTF-8').'"':'' ?>>
<a class="skip-link" href="#main">Skip to content</a>
<header class="public-header header wrap">
  <a href="./" aria-label="<?= htmlspecialchars($publicLogoAlt,ENT_QUOTES,'UTF-8') ?> home"><img class="logo" src="<?= htmlspecialchars($publicHeaderLogo,ENT_QUOTES,'UTF-8') ?>" alt="<?= htmlspecialchars($publicLogoAlt,ENT_QUOTES,'UTF-8') ?>" width="151" height="40"></a>
  <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="navigation">Menu <span aria-hidden="true">+</span></button>
  <nav id="navigation" aria-label="Main navigation">
    <a href="about" <?= $publicActive==='about'?'aria-current="page"':'' ?>>About</a>
    <a href="about#platform">Product</a>
    <a href="about#solutions">Solutions</a>
    <details class="public-nav-dropdown" <?= $publicActive==='tools'?'open':'' ?>><summary <?= $publicActive==='tools'?'aria-current="page"':'' ?>>Tools <span aria-hidden="true">⌄</span></summary><div class="public-nav-menu"><a href="quote-invoice-generator?type=quotation">Quotation Generator</a><a href="quote-invoice-generator?type=invoice">Invoice Generator</a></div></details>
    <?php if($publicUser):?><a class="public-profile" href="profile" aria-label="Open profile for <?= htmlspecialchars((string)$publicUser['full_name'],ENT_QUOTES,'UTF-8') ?>"><span aria-hidden="true"><?= htmlspecialchars(strtoupper(substr((string)$publicUser['full_name'],0,1)),ENT_QUOTES,'UTF-8') ?></span></a><?php else:?><a class="nav-cta" href="login">Login / Sign up <span aria-hidden="true">↗</span></a><?php endif;?>
  </nav>
</header>
<main id="main"<?= $publicMainClass!==''?' class="'.htmlspecialchars($publicMainClass,ENT_QUOTES,'UTF-8').'"':'' ?>>
