(() => {
 const profile=document.querySelector('#equipment-profile');if(!profile)return;
 document.body.classList.add('asset-detail-page');
 const qrForm=document.querySelector('[data-equipment-qr-form]');
 if(qrForm){
  const submitButton=qrForm.querySelector('[data-equipment-qr-submit]');
  const message=qrForm.querySelector('[data-equipment-qr-message]');
  const confirmInput=qrForm.querySelector('[data-equipment-qr-confirm]');
  const ready=document.querySelector('[data-equipment-qr-ready]');
  const shortUrl=document.querySelector('[data-equipment-short-url]');
  const openLink=document.querySelector('[data-equipment-qr-open]');
  const qrImage=document.querySelector('[data-equipment-qr-image]');
  let creating=false;
  qrForm.addEventListener('submit',async event=>{
   event.preventDefault();if(creating)return;creating=true;
   submitButton.disabled=true;submitButton.textContent=confirmInput.value==='1'?'Creating replacement...':'Creating QR link...';
   message.setAttribute('role','status');message.textContent='Contacting Nonagon to create the Xinng short link...';
   try{
   const endpoint=new URL(qrForm.getAttribute('action')||location.href,location.href);
   const response=await fetch(endpoint,{method:'POST',body:new FormData(qrForm),headers:{Accept:'application/json'},credentials:'same-origin'});
   const responseText=await response.text();
   let result;
   try{result=JSON.parse(responseText);}
   catch{
    const finalUrl=new URL(response.url);
    const redirectedToLogin=response.redirected&&/login|signin/i.test(finalUrl.pathname);
    const reason=redirectedToLogin?'Your sign-in session may have expired. Sign in to Nonagon and retry.':`Check that ${endpoint.origin}${endpoint.pathname} is the deployed Nonagon action route.`;
    throw new Error(`QR request returned HTML instead of JSON (HTTP ${response.status}). ${reason}`);
   }
	if(!response.ok||result.ok!==true){
	 if(response.status===409){confirmInput.value='1';submitButton.textContent='Confirm new QR link';}
    const reference=result.reference?` Reference: ${result.reference}.`:'';
    throw new Error((result.error||'The equipment QR link could not be created.')+reference);
	}
	const url=result.short_link?.full_short_url;
	let shortPath='';
	try{shortPath=new URL(url).pathname.split('/').filter(Boolean).pop()||'';}catch{}
	if(typeof url!=='string'||!url.startsWith('https://'))throw new Error('A valid public equipment link was not returned.');
	if(!result.fallback&&(!/^[a-z0-9]{4}$/.test(shortPath)||typeof result.qr_data_uri!=='string'))throw new Error('The QR service did not return a valid four-character short link.');
	shortUrl.value=url;openLink.href=url;if(result.fallback){qrImage.removeAttribute('src');qrImage.hidden=true;}else{qrImage.src=result.qr_data_uri;qrImage.hidden=false;}
	ready.hidden=false;qrForm.hidden=true;
	ready.querySelector('[data-equipment-qr-success]').textContent=result.fallback?'xin.ng is unavailable. Use this direct equipment link; no QR code or short code was created.':'Short link and QR code created and saved. This link will be reused.';
   }catch(error){
	message.setAttribute('role','alert');message.textContent=error.message||'The equipment QR link could not be created. Please try again.';
	if(confirmInput.value!=='1')submitButton.textContent='Try again';
   }finally{creating=false;if(!qrForm.hidden)submitButton.disabled=false;}
  });
 }
 document.querySelectorAll('[data-copy-equipment-qr]').forEach(button=>button.addEventListener('click',async()=>{
  const input=document.querySelector('[data-equipment-short-url]');
  const status=document.querySelector('[data-equipment-qr-success]');
  try{await navigator.clipboard.writeText(input.value);status.textContent='Short link copied.';}
  catch{input.focus();input.select();status.textContent='Select and copy the short link.';}
 }));
 const detail=profile.closest('.detail-grid'), content=profile.parentElement, actions=detail.querySelector('.action-forms');
 const heading=document.querySelector('.asset-heading'), tabs=document.querySelector('.asset-tabs');
 detail.before(heading,tabs);
 const generic=content.querySelector('.detail-content');if(generic?.parentElement===content)generic.hidden=true;
 const status=document.createElement('section');status.id='asset-status-pane';status.className='asset-pane';content.append(status);
 const maintenance=document.createElement('section');maintenance.id='asset-maintenance-pane';maintenance.className='asset-pane';content.append(maintenance);
 if(generic){generic.hidden=false;status.append(generic);}
 for(const panel of [...actions.children]){(panel.id==='schedule-maintenance'?maintenance:status).append(panel);}
 actions.remove();
 const work=document.querySelector('#maintenance-records');if(work){const risks=work.nextElementSibling;maintenance.prepend(work);if(risks)status.append(risks);}
 const audit=[...content.children].find(node=>node.querySelector('h2')?.textContent==='Recent audit activity');if(audit)audit.id='equipment-activity';
 const panes=[...content.children].filter(node=>node.matches('section.asset-pane'));
 function activate(){const id=location.hash.slice(1)||'equipment-profile';const target=document.getElementById(id);const pane=panes.find(node=>node===target||node.contains(target))||profile;panes.forEach(node=>node.hidden=node!==pane);tabs.querySelectorAll('a').forEach(link=>{if(link.hash==='#'+pane.id)link.setAttribute('aria-current','page');else link.removeAttribute('aria-current');});if(target?.matches('details'))target.open=true;if(target&&target!==pane)requestAnimationFrame(()=>target.scrollIntoView({block:'start'}));}
 window.addEventListener('hashchange',activate);activate();
 document.querySelectorAll('[data-photo]').forEach(button=>button.addEventListener('click',()=>{const image=document.querySelector('#asset-main-photo');image.src=button.dataset.photo;image.alt=button.dataset.caption;document.querySelectorAll('[data-photo]').forEach(other=>other.setAttribute('aria-pressed',String(other===button)));}));
 document.querySelector('#asset-share').addEventListener('click',async()=>{const message=document.querySelector('#asset-share-status');try{const url=new URL(location.href);url.hash='';await navigator.clipboard.writeText(url.href);message.textContent='Equipment link copied. Recipients need access to this equipment.';}catch{message.textContent='Copy this page address to share it. Recipients need equipment access.';}});
})();
