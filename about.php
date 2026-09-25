<?php
declare(strict_types=1);
$features = [
    ['01', 'Know every asset.', 'Keep equipment details, status and location together in one organized register.'],
    ['02', 'Stay ahead of maintenance.', 'Bring schedules, work orders and equipment history into your daily workflow.'],
    ['03', 'Keep your team connected.', 'Organize access around your bases, sites, plants and units.'],
];
$pageTitle='About Nonagon';$publicActive='about';$publicStyles=['assets/css/home.css','assets/css/hierarchy.css'];require __DIR__.'/includes/public-header.php';
?>
    <section class="hero wrap">
        <div class="hero-copy">
            <p class="eyebrow"><span class="dot"></span> EQUIPMENT MANAGEMENT, CONNECTED</p>
            <h1>Your operations.<br>One clear <span>picture.</span></h1>
            <p class="intro">Bring your equipment, maintenance and people together. Less searching for answers. More moving forward.</p>
            <div class="hero-actions"><a class="button" href="register">Create your account <span aria-hidden="true">↗</span></a><a class="text-link" href="#solutions">See how it works <span aria-hidden="true">↓</span></a></div>
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
    <section class="workflow section" id="solutions"><div class="wrap workflow-inner"><div><p class="eyebrow">SOLUTIONS FOR OPERATIONAL TEAMS</p><h2>From the big picture<br>to the smallest detail.</h2><p>Mirror your real-world organization across industries. Find equipment and its parts where they belong, and keep each team focused on its area of responsibility.</p></div><ol class="location-tree"><li><span>01</span> Organization</li><li><span>02</span> Base</li><li><span>03</span> Site</li><li><span>04</span> Plant</li><li><span>05</span> Unit <small>Your equipment, right where it belongs.</small></li><li class="parts-level"><div class="level-label"><span>06</span> Assembly</div><div class="parts-grid" aria-label="Parts within this unit"><div>Part 01</div><div>Part 02</div><div>Part 03</div></div></li></ol></div></section>
    <section class="access wrap section" id="tools"><p class="eyebrow">AT YOUR DESK. OUT IN THE FIELD.</p><h2>Tools for every<br>operational context.</h2><p>Use Nonagon in the browser today, with installable and dedicated experiences planned as the platform grows.</p><div class="platform-options"><div><strong>Browser</strong><span>Manage equipment and marketplace listings</span></div><div><strong>Android</strong><span>Installable web app planned</span></div><div><strong>Desktop</strong><span>Dedicated app planned</span></div></div><a class="text-link" href="#main">Back to the overview <span aria-hidden="true">↑</span></a></section>
<?php require __DIR__.'/includes/public-footer.php';?>
