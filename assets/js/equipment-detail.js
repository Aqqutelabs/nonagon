(() => {
 const profile=document.querySelector('#equipment-profile');if(!profile)return;
 document.body.classList.add('asset-detail-page');
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
 const panes=[...content.children].filter(node=>node.matches('section'));
 function activate(){const id=location.hash.slice(1)||'equipment-profile';const target=document.getElementById(id);const pane=panes.find(node=>node===target||node.contains(target))||profile;panes.forEach(node=>node.hidden=node!==pane);tabs.querySelectorAll('a').forEach(link=>{if(link.hash==='#'+pane.id)link.setAttribute('aria-current','page');else link.removeAttribute('aria-current');});if(target?.matches('details'))target.open=true;if(target&&target!==pane)requestAnimationFrame(()=>target.scrollIntoView({block:'start'}));}
 window.addEventListener('hashchange',activate);activate();
 document.querySelectorAll('[data-photo]').forEach(button=>button.addEventListener('click',()=>{const image=document.querySelector('#asset-main-photo');image.src=button.dataset.photo;image.alt=button.dataset.caption;document.querySelectorAll('[data-photo]').forEach(other=>other.setAttribute('aria-pressed',String(other===button)));}));
 document.querySelector('#asset-share').addEventListener('click',async()=>{const message=document.querySelector('#asset-share-status');try{const url=new URL(location.href);url.hash='';await navigator.clipboard.writeText(url.href);message.textContent='Equipment link copied. Recipients need access to this equipment.';}catch{message.textContent='Copy this page address to share it. Recipients need equipment access.';}});
})();
