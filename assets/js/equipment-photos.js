(() => {
 const registration=document.querySelector('[data-registration-photos]');
 if(registration){
  const input=registration.querySelector('input[type=file]'),grid=registration.querySelector('[data-photo-preview]'),add=grid.querySelector('.photo-add-card'),count=registration.querySelector('[data-photo-count]'),primary=registration.querySelector('[data-primary-photo-index]');let urls=[];
  const clear=()=>{urls.forEach(URL.revokeObjectURL);urls=[];grid.querySelectorAll('.photo-manager-card').forEach(card=>card.remove());};
  input.addEventListener('change',()=>{clear();const files=[...input.files];count.textContent=`${files.length} photo${files.length===1?'':'s'}`;primary.value='0';files.forEach((file,index)=>{const url=URL.createObjectURL(file);urls.push(url);const card=document.createElement('figure');card.className='photo-manager-card'+(index===0?' is-primary':'');card.innerHTML='<img alt=""><figcaption><span></span><strong>Primary</strong></figcaption><button type="button" class="photo-preview-primary">Set primary</button>';card.querySelector('img').src=url;card.querySelector('span').textContent=file.name;card.querySelector('button').addEventListener('click',()=>{primary.value=String(index);grid.querySelectorAll('.photo-manager-card').forEach(item=>item.classList.toggle('is-primary',item===card));});grid.insertBefore(card,add);});});
  window.addEventListener('pagehide',clear,{once:true});
 }
 document.querySelectorAll('[data-photo-auto-submit]').forEach(input=>input.addEventListener('change',()=>{if(!input.files.length)return;const card=input.closest('[data-photo-upload-form]');card.classList.add('is-uploading');card.querySelector('strong').textContent=`Uploading ${input.files.length} photo${input.files.length===1?'':'s'}…`;card.submit();}));
 document.querySelectorAll('[data-photo-delete-form]').forEach(form=>form.addEventListener('submit',event=>{if(!confirm('Delete this equipment photo?'))event.preventDefault();}));
})();
