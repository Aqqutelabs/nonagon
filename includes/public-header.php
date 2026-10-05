<?php
if(!function_exists('current_user'))require_once __DIR__.'/../app/bootstrap.php';
header('Cache-Control: no-store, private');
$pageTitle=$pageTitle??'Nonagon';
$publicActive=$publicActive??'';
$publicStyles=$publicStyles??[];
$publicMainClass=$publicMainClass??'';
$publicBodyClass=$publicBodyClass??'';
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
  <a href="./" aria-label="Nonagon home"><img class="logo" src="assets/images/logo-dark.svg" alt="Nonagon" width="151" height="40"></a>
  <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="navigation">Menu <span aria-hidden="true">+</span></button>
  <nav id="navigation" aria-label="Main navigation">
    <a href="about" <?= $publicActive==='about'?'aria-current="page"':'' ?>>About</a>
    <a href="about#platform">Product</a>
    <a href="about#solutions">Solutions</a>
    <a href="invest" <?= $publicActive==='investment'?'aria-current="page"':'' ?>>Invest</a>
    <details class="public-nav-dropdown" <?= $publicActive==='tools'?'open':'' ?>><summary <?= $publicActive==='tools'?'aria-current="page"':'' ?>>Tools <span aria-hidden="true">⌄</span></summary><div class="public-nav-menu"><a href="quote-invoice-generator?type=quotation">Quotation Generator</a><a href="quote-invoice-generator?type=invoice">Invoice Generator</a></div></details>
    <?php if($publicUser):?><details class="public-account-menu"><summary class="public-profile" aria-label="Open account menu for <?= htmlspecialchars((string)$publicUser['full_name'],ENT_QUOTES,'UTF-8') ?>"><span aria-hidden="true"><?= htmlspecialchars(strtoupper(substr((string)$publicUser['full_name'],0,1)),ENT_QUOTES,'UTF-8') ?></span></summary><div class="public-account-dropdown"><div><strong><?= htmlspecialchars((string)$publicUser['full_name'],ENT_QUOTES,'UTF-8') ?></strong><small><?= htmlspecialchars((string)$publicUser['email'],ENT_QUOTES,'UTF-8') ?></small></div><a href="profile"><span>Account</span><small>Manage your account</small></a><a href="dashboard"><span>Dashboard</span><small>Open operations overview</small></a></div></details><?php else:?><a class="nav-cta" href="login">Login / Sign up <span aria-hidden="true">↗</span></a><?php endif;?>
  </nav>
</header>
<main id="main"<?= $publicMainClass!==''?' class="'.htmlspecialchars($publicMainClass,ENT_QUOTES,'UTF-8').'"':'' ?>>
