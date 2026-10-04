document.addEventListener('DOMContentLoaded',()=>{
 const form=document.querySelector('[data-investment-form]');if(!form)return;
 const panels=[...form.querySelectorAll('[data-step]')],targets=[...form.querySelectorAll('[data-step-target]')];
 const previous=form.querySelector('[data-step-previous]'),next=form.querySelector('[data-step-next]');let current=1;
 const show=step=>{current=Math.max(1,Math.min(panels.length,step));panels.forEach(p=>p.hidden=Number(p.dataset.step)!==current);targets.forEach(t=>t.toggleAttribute('aria-current',Number(t.dataset.stepTarget)===current));previous.hidden=current===1;next.hidden=current===panels.length;panels[current-1].scrollIntoView({behavior:'smooth',block:'start'});};
 targets.forEach(t=>t.addEventListener('click',()=>show(Number(t.dataset.stepTarget))));previous.addEventListener('click',()=>show(current-1));next.addEventListener('click',()=>show(current+1));
});
