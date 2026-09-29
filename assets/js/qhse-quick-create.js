(()=>{
  const csrf=document.querySelector('.certificate-form input[name="csrf"]')?.value||'';
  document.querySelectorAll('[data-open-quick]').forEach(button=>button.addEventListener('click',()=>document.querySelector(`[data-quick-modal="${button.dataset.openQuick}"]`)?.showModal()));
  document.querySelectorAll('[data-quick-submit]').forEach(button=>button.addEventListener('click',async()=>{
    const dialog=button.closest('dialog'),form=dialog.querySelector('form'),status=dialog.querySelector('.quick-status'),data=new FormData(form);
    data.set('csrf',csrf);data.set('kind',button.dataset.quickSubmit);if(!form.reportValidity())return;
    button.disabled=true;status.className='quick-status';status.textContent='Creating…';
    try{
      const response=await fetch('qhse-quick-create',{method:'POST',body:data,headers:{Accept:'application/json'}}),result=await response.json();
      if(!response.ok||!result.ok)throw new Error(result.message||'The record could not be created.');
      const kind=button.dataset.quickSubmit;
      if(kind==='equipment')document.querySelectorAll('[data-equipment-select]').forEach((select,index)=>{const option=new Option(`${result.label}${result.meta?' · '+result.meta:''}`,result.id,false,index===0);option.dataset.label=result.label;select.add(option);if(index===0)select.value=result.id});
      else document.querySelectorAll(`[data-quick-list="${kind}"]`).forEach((select,index)=>{const option=new Option(result.label,result.id,true,index===0);if(result.type)option.dataset.type=result.type;select.add(option);if(index===0)select.value=result.id});
      document.dispatchEvent(new CustomEvent('qhse:quick-created',{detail:{kind,result}}));status.className='quick-status success';status.textContent=result.message;
      setTimeout(()=>{dialog.close();form.reset();status.textContent='';status.className='quick-status'},1100);
    }catch(error){status.className='quick-status error';status.textContent=error.message}finally{button.disabled=false}
  }));
})();
