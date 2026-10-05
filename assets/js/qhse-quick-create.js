(()=>{
  document.querySelectorAll('.quick-create-modal .eyebrow').forEach(label=>label.remove());
  document.querySelector('[data-quick-modal="template"]')?.remove();
  const brandDialog=document.querySelector('[data-quick-modal="brand"]');
  if(brandDialog){brandDialog.querySelector('.modal-fields').innerHTML='<label>Brand name<input name="brand_name" required></label><label>Company name<input name="company_name" required></label><fieldset><legend>Creation method</legend><label><input type="radio" name="creation_mode" value="ELEMENTS" checked> Individual elements</label><label><input type="radio" name="creation_mode" value="ARTWORK"> Header & footer artwork</label></fieldset><label>Logo<input type="file" name="logo" accept="image/jpeg,image/png"></label><label>Header artwork<input type="file" name="header_artwork" accept="image/jpeg,image/png"></label><label>Footer artwork<input type="file" name="footer_artwork" accept="image/jpeg,image/png"></label><label>Address<input name="addresses[]" required></label><label>Phone number<input name="phones[]" required></label><label>Email address<input type="email" name="emails[]" required></label><label>Website<input type="url" name="website" required></label>';const applyMode=()=>{const selected=brandDialog.querySelector('[name="creation_mode"]:checked')?.value||'ELEMENTS',toggle=(names,visible)=>names.forEach(name=>{const input=brandDialog.querySelector(`[name="${name}"]`),label=input?.closest('label');if(label){label.hidden=!visible;input.disabled=!visible}});toggle(['logo','addresses[]','phones[]','emails[]','website'],selected==='ELEMENTS');toggle(['header_artwork','footer_artwork'],selected==='ARTWORK')};brandDialog.addEventListener('change',event=>{if(event.target.matches('[name="creation_mode"]'))applyMode()});applyMode();}
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
