</main>
<?php $publicFooterLogo=$publicFooterLogo??'assets/images/logo-dark.svg';$publicLogoAlt=$publicLogoAlt??'Nonagon';$publicFooterClass=$publicFooterClass??'';?>
<footer class="public-footer footer wrap<?= $publicFooterClass!==''?' '.htmlspecialchars($publicFooterClass,ENT_QUOTES,'UTF-8'):'' ?>"><a href="./"><img class="logo" src="<?= htmlspecialchars($publicFooterLogo,ENT_QUOTES,'UTF-8') ?>" alt="<?= htmlspecialchars($publicLogoAlt,ENT_QUOTES,'UTF-8') ?>" width="132" height="35"></a><p>Equipment. People. Operations.</p><small>© <?= date('Y') ?> Nonagon.</small></footer>
<?php foreach(($publicScripts??[]) as $script):?><script src="<?= htmlspecialchars($script,ENT_QUOTES,'UTF-8') ?>" defer></script><?php endforeach;?>
<script>(()=>{const toggle=document.querySelector('.menu-toggle'),nav=document.querySelector('#navigation');if(!toggle||!nav)return;const close=()=>{nav.classList.remove('open');toggle.setAttribute('aria-expanded','false')};toggle.addEventListener('click',()=>{const open=nav.classList.toggle('open');toggle.setAttribute('aria-expanded',String(open))});nav.addEventListener('click',event=>{if(event.target.closest('a'))close()});document.addEventListener('keydown',event=>{if(event.key==='Escape'){close();toggle.focus()}})})();</script>
</body>
</html>
