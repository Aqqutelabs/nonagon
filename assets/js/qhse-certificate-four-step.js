(()=>{
  const form=document.querySelector('.dynamic-certificate-form');
  if(!form||!form.qhseDynamic)return;

  const originalOne=form.querySelector('[data-certificate-step="1"]');
  const originalTwo=form.querySelector('[data-certificate-step="2"]');
  const oneSections=[...originalOne.querySelectorAll(':scope > section.panel')];
  const twoSections=[...originalTwo.querySelectorAll(':scope > section.panel')];
  if(oneSections.length<3||twoSections.length<2)return;

  form.elements.certificate_description?.closest('label')?.remove();
  const testSelect=form.elements.template_id;
  if(testSelect){
    if(testSelect.options[0])testSelect.options[0].textContent='Which test do you want to create?';
    [...testSelect.options].slice(1).forEach(option=>{option.textContent=option.textContent.replace(/\s*·\s*v\d+\s*$/i,'')});
    const label=testSelect.closest('label');
    if(label)label.childNodes[0].textContent='Test';
    label?.querySelector('[data-open-quick="template"]')?.remove();
  }
  ['reviewer_id','approver_id','reviewer_signature_id','approver_signature_id'].forEach(name=>form.elements[name]?.closest('label')?.remove());
  form.elements.signoff_date?.closest('label')?.remove();
  const usageData=JSON.parse(document.querySelector('#certificate-signature-usage-data')?.textContent||'{}');
  [['technician_signature_id','technician_asset_usage','TECHNICIAN'],['client_signature_id','client_asset_usage','CLIENT_REPRESENTATIVE']].forEach(([assetName,usageName,role])=>{
    const asset=form.elements[assetName];if(!asset)return;
    [...asset.options].forEach(option=>{if(option.value&&option.textContent.includes(' · '))option.textContent=option.textContent.split(' · ').pop()});
    const label=document.createElement('label');label.textContent='Use on certificate';
    const select=document.createElement('select');select.name=usageName;
    select.innerHTML='<option value="SIGNATURE">Signature only</option><option value="STAMP">Stamp only</option><option value="BOTH">Signature and stamp</option>';
    select.value=usageData[role]||'BOTH';label.append(select);asset.closest('label')?.after(label);
  });
  const reviewCopy=form.querySelector('#certificate-review p');
  if(reviewCopy)reviewCopy.textContent='Check the certificate information, test results, and signatures. The saved draft can be published when it is ready.';
  const procedure=form.elements.procedure_reference?.closest('label');
  if(procedure)procedure.childNodes[0].textContent='Procedure number';
  const authorization=form.elements.nuprc_number?.closest('label');
  if(authorization)authorization.childNodes[0].textContent='Authorization number (NUPRC)';
  const manualBlock=form.querySelector('.manual-test-item');
  if(manualBlock){
    manualBlock.innerHTML='<strong>Manual tested items</strong><div data-manual-items></div><button type="button" class="secondary" data-add-manual-item>+ Add manual item</button>';
    const manualHost=manualBlock.querySelector('[data-manual-items]');let manualIndex=0;
    const addManual=()=>{const row=document.createElement('div');row.className='form-grid four manual-tested-item-row';row.innerHTML=`<label>Description<input name="manual_items[${manualIndex}][name]" required></label><label>ID number<input name="manual_items[${manualIndex}][asset_code]"></label><label>Range<input name="manual_items[${manualIndex}][range_text]" placeholder="e.g. 0–15,000 PSI"></label><label>Test pressure<input name="manual_items[${manualIndex}][test_pressure]"></label><button type="button" class="text-button" data-remove-manual>Remove</button>`;row.querySelector('[data-remove-manual]').addEventListener('click',()=>row.remove());manualHost.append(row);manualIndex++};
    manualBlock.querySelector('[data-add-manual-item]').addEventListener('click',addManual);
  }
  const referenceHost=form.querySelector('[data-equipment-selectors="reference"]');
  if(referenceHost){const addReference=document.createElement('button');addReference.type='button';addReference.className='secondary add-equipment-selector';addReference.textContent='+ Add another reference equipment';addReference.addEventListener('click',()=>{const source=referenceHost.querySelector('label');const label=source.cloneNode(true);label.firstChild.textContent=`Reference equipment ${referenceHost.children.length+1}`;const select=label.querySelector('select');select.value='';select.required=false;referenceHost.append(label)});referenceHost.after(addReference)}

  const makeStep=(number,sections)=>{
    const step=document.createElement('div');
    step.dataset.certificateStep=String(number);
    step.hidden=number!==1;
    sections.forEach(section=>step.append(section));
    return step;
  };
  const actions=(step,back,next,final=false)=>{
    const row=document.createElement('div');row.className='step-actions';
    if(back){const button=document.createElement('button');button.type='button';button.className='secondary';button.textContent='Back';button.addEventListener('click',()=>show(back));row.append(button)}
    else{const cancel=document.createElement('a');cancel.className='secondary';cancel.href='qhse-certificates';cancel.textContent='Cancel';row.append(cancel)}
    if(final){const review=document.createElement('button');review.type='button';review.className='secondary';review.textContent='Review';review.addEventListener('click',()=>{form.qhseDynamic.collect();const panel=form.querySelector('#certificate-review');panel.hidden=false;panel.scrollIntoView({behavior:'smooth'})});const save=document.createElement('button');save.className='primary';save.textContent='Save draft';row.append(review,save)}
    else{const button=document.createElement('button');button.type='button';button.className='primary';button.textContent=next===2?'Continue to tested items':next===3?'Continue to test results':'Continue to signing';button.addEventListener('click',()=>advance(step,next));row.append(button)}
    step.append(row);
  };
  const steps=[makeStep(1,[oneSections[0],oneSections[1]]),makeStep(2,[oneSections[2]]),makeStep(3,[twoSections[0]]),makeStep(4,twoSections.slice(1))];
  const stepper=form.querySelector('.certificate-stepper');
  const style=document.createElement('style');style.textContent='.certificate-stepper{grid-template-columns:auto 1fr auto 1fr auto 1fr auto}@media(max-width:650px){.certificate-stepper{grid-template-columns:repeat(7,auto);overflow-x:auto}}';document.head.append(style);
  stepper.innerHTML='<span class="active" data-step-indicator="1">1 <b>Certificate &amp; Customer</b></span><i></i><span data-step-indicator="2">2 <b>Tested Items</b></span><i></i><span data-step-indicator="3">3 <b>Test Results</b></span><i></i><span data-step-indicator="4">4 <b>Signing</b></span>';
  oneSections[0].querySelector('.step-number').textContent='01';oneSections[1].querySelector('.step-number').textContent='01';oneSections[2].querySelector('.step-number').textContent='02';twoSections[0].querySelector('.step-number').textContent='03';twoSections[1].querySelector('.step-number').textContent='04';
  originalOne.remove();originalTwo.remove();steps.forEach(step=>form.append(step));
  actions(steps[0],0,2);actions(steps[1],1,3);actions(steps[2],2,4);actions(steps[3],3,0,true);

  function show(number){steps.forEach((step,index)=>step.hidden=index+1!==number);stepper.querySelectorAll('[data-step-indicator]').forEach(item=>item.classList.toggle('active',Number(item.dataset.stepIndicator)===number));scrollTo({top:0,behavior:'smooth'})}
  function valid(container){for(const field of container.querySelectorAll('[required]'))if(!field.reportValidity())return false;return true}
  function advance(step,next){
    if(!valid(step))return;
    if(next===3){
      form.querySelectorAll('select[data-equipment-select]').forEach(select=>select.dispatchEvent(new Event('change',{bubbles:true})));
      const selected=[...form.querySelectorAll('select[data-equipment-select="primary"]')].some(select=>select.value);
      const manual=[...form.querySelectorAll('[name^="manual_items"][name$="[name]"]')].some(input=>input.value.trim());
      const reference=[...form.querySelectorAll('select[data-equipment-select="reference"]')].some(select=>select.value);
      if(!selected&&!manual){alert('Select registered equipment or enter a manual tested item.');return}
      if(!reference){alert('Select the reference instrument.');return}
      form.qhseDynamic.renderTraceability();form.qhseDynamic.render();
    }
    if(next===4){form.qhseDynamic.collect();if(!valid(step))return}
    show(next);
  }
})();
