</main>
<footer class="public-footer footer wrap"><a href="./"><img class="logo" src="assets/images/logo-dark.svg" alt="Nonagon" width="132" height="35"></a><p>Equipment. People. Operations.</p><small>© <?= date('Y') ?> Nonagon.</small></footer>
<?php foreach(($publicScripts??[]) as $script):?><script src="<?= htmlspecialchars($script,ENT_QUOTES,'UTF-8') ?>" defer></script><?php endforeach;?>
<script src="assets/js/modal-close-confirmation.js?v=<?= filemtime(APP_ROOT.'/assets/js/modal-close-confirmation.js') ?>" defer></script>
<script>(()=>{const toggle=document.querySelector('.menu-toggle'),nav=document.querySelector('#navigation'),account=document.querySelector('.public-account-menu');if(!toggle||!nav)return;const close=()=>{nav.classList.remove('open');toggle.setAttribute('aria-expanded','false')};toggle.addEventListener('click',()=>{const open=nav.classList.toggle('open');toggle.setAttribute('aria-expanded',String(open))});nav.addEventListener('click',event=>{if(event.target.closest('a'))close()});document.addEventListener('click',event=>{if(account?.open&&!account.contains(event.target))account.removeAttribute('open')});document.addEventListener('keydown',event=>{if(event.key==='Escape'){close();account?.removeAttribute('open')}})})();</script>
<?php require __DIR__.'/google-analytics.php'; ?>
</body>
</html>
