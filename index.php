<?php
declare(strict_types=1);
$features = [
    ['01', 'Know every asset.', 'Keep equipment details, status and location together in one organized register.'],
    ['02', 'Stay ahead of maintenance.', 'Bring schedules, work orders and equipment history into your daily workflow.'],
    ['03', 'Keep your team connected.', 'Organize access around your bases, sites, plants and units.'],
];
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Nonagon brings equipment, maintenance and teams together. A clearer view of your operations, from the office to the field.">
    <meta name="theme-color" content="#0a6fb8">
    <title>Nonagon — Equipment. People. Operations.</title>
    <link rel="icon" href="assets/images/favicon.png">
    <link rel="stylesheet" href="assets/css/home.css">
    <link rel="stylesheet" href="assets/css/hierarchy.css">
    <script src="assets/js/home.js" defer></script>
</head>
<body>
<a class="skip-link" href="#main">Skip to content</a>
<header class="header wrap">
    <a href="./" aria-label="Nonagon home"><img class="logo" src="assets/images/logo-dark.svg" alt="Nonagon" width="151" height="40"></a>
    <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="navigation">Menu <span aria-hidden="true">+</span></button>
    <nav id="navigation" aria-label="Main navigation">
        <a href="#platform">The platform</a><a href="#workflow">How it works</a><a href="#access">Built for your team</a>
        <a href="login">Sign in</a><a class="nav-cta" href="register">Create account <span aria-hidden="true">↗</span></a>
    </nav>
</header>
<main id="main">
    <section class="hero wrap">
        <div class="hero-copy">
            <p class="eyebrow"><span class="dot"></span> EQUIPMENT MANAGEMENT, CONNECTED</p>
            <h1>Your operations.<br>One clear <span>picture.</span></h1>
            <p class="intro">Bring your equipment, maintenance and people together. Less searching for answers. More moving forward.</p>
            <div class="hero-actions"><a class="button" href="register">Create your account <span aria-hidden="true">↗</span></a><a class="text-link" href="#workflow">See how it works <span aria-hidden="true">↓</span></a></div>
            <p class="hero-note">From a single site to your entire organization.</p>
        </div>
        <div class="hero-visual" id="preview">
            <div class="preview-top"><span><span class="dot"></span> YOUR OPERATIONS AT A GLANCE</span><span>Platform preview</span></div>
            <div class="equipment-image"><img src="assets/images/truck.png" alt="Heavy equipment for industrial operations" width="600" height="460"><span class="image-label">EVERY ASSET. ACCOUNTED FOR.</span></div>
            <div class="asset-card"><div><span class="mini-label">ASSET REGISTER</span><h2>A place for every<br>piece of equipment.</h2></div><span class="round-arrow" aria-hidden="true">↗</span></div>
            <div class="preview-bottom"><span>Equipment</span><span>Maintenance</span><span>People</span></div>
        </div>
    </section>
    <div class="principles wrap"><p>Built around the way<br><strong>your operations work.</strong></p><span>Centralized records</span><span>Location-based access</span><span>Connected workflows</span></div>
    <section class="platform wrap section" id="platform">
        <div class="section-heading"><div><p class="eyebrow">LESS FRICTION. MORE CLARITY.</p><h2>Everything connected.<br>Nothing lost in the shuffle.</h2></div><p>Give your equipment a complete story, and your team a shared place to find it.</p></div>
        <div class="feature-grid"><?php foreach ($features as [$number, $title, $description]): ?>
            <article class="feature"><span class="feature-number"><?= $number ?></span><h3><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h3><p><?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8') ?></p></article>
        <?php endforeach; ?></div>
    </section>
    <section class="workflow section" id="workflow"><div class="wrap workflow-inner"><div><p class="eyebrow">A STRUCTURE THAT MAKES SENSE</p><h2>From the big picture<br>to the smallest detail.</h2><p>Mirror your real-world organization. Find equipment and its parts where they belong, and keep each team focused on its area of responsibility.</p></div><ol class="location-tree"><li><span>01</span> Organization</li><li><span>02</span> Base</li><li><span>03</span> Site</li><li><span>04</span> Plant</li><li><span>05</span> Unit <small>Your equipment, right where it belongs.</small></li><li class="parts-level"><div class="level-label"><span>06</span> Assembly</div><div class="parts-grid" aria-label="Parts within this unit"><div>Part 01</div><div>Part 02</div><div>Part 03</div></div></li></ol></div></section>
    <section class="access wrap section" id="access"><p class="eyebrow">AT YOUR DESK. OUT IN THE FIELD.</p><h2>One platform.<br>Room to grow.</h2><p>We’re building Nonagon for the way your team works, starting with the web.</p><div class="platform-options"><div><strong>Browser</strong><span>Web experience in development</span></div><div><strong>Android</strong><span>Installable web app planned</span></div><div><strong>Desktop</strong><span>Dedicated app planned</span></div></div><a class="text-link" href="#main">Back to the overview <span aria-hidden="true">↑</span></a></section>
</main>
<footer class="footer wrap"><a href="./"><img class="logo" src="assets/images/logo-dark.svg" alt="Nonagon" width="132" height="35"></a><p>Equipment. People. Operations.</p><small>© <?= date('Y') ?> Nonagon.</small></footer>
</body>
</html>
