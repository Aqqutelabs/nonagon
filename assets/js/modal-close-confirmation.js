(()=>{
  'use strict';

  const isCloseButton=button=>{
    if(!(button instanceof HTMLButtonElement)||!button.closest('dialog'))return false;
    const label=(button.getAttribute('aria-label')||'').trim().toLowerCase();
    const text=(button.textContent||'').trim();
    return text==='×'||text==='✕'||label==='close'||label.startsWith('close ');
  };

  const closeWithoutSaving=source=>{
    source.querySelectorAll('form').forEach(form=>form.reset());
    source.close('discard');
    if(source.classList.contains('commercial-create-dialog')||source.classList.contains('creation-review-dialog'))source.remove();
  };

  const ask=source=>{
    const confirmation=document.createElement('dialog');
    confirmation.className='modal-discard-confirmation';
    confirmation.setAttribute('aria-labelledby','modal-discard-title');
    confirmation.innerHTML='<div class="modal-discard-card"><h2 id="modal-discard-title">Close without saving?</h2><p>Any unsaved information in this modal will be discarded.</p><div><button type="button" class="secondary" data-discard-no>No</button><button type="button" class="primary" data-discard-yes>Yes</button></div></div>';
    document.body.append(confirmation);
    const finish=()=>{if(confirmation.open)confirmation.close();confirmation.remove()};
    confirmation.querySelector('[data-discard-no]').addEventListener('click',finish);
    confirmation.querySelector('[data-discard-yes]').addEventListener('click',()=>{finish();closeWithoutSaving(source)});
    confirmation.addEventListener('cancel',event=>{event.preventDefault();finish()});
    confirmation.showModal();
    confirmation.querySelector('[data-discard-no]').focus();
  };

  document.addEventListener('click',event=>{
    const button=event.target.closest('button');
    if(!isCloseButton(button)||button.closest('.modal-discard-confirmation'))return;
    const source=button.closest('dialog');
    if(!source?.open)return;
    event.preventDefault();event.stopImmediatePropagation();ask(source);
  },true);
})();
