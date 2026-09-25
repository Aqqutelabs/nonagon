(() => {
  const form = document.querySelector('#listing-form');
  if (!form) return;

  const editData = JSON.parse(document.querySelector('#marketplace-edit-data')?.textContent || '{}');
  const field = name => form.elements.namedItem(name);
  const setEnabled = (container, enabled) => {
    container.hidden = !enabled;
    container.querySelectorAll('input,select,textarea,button').forEach(control => { control.disabled = !enabled; });
  };

  const assetMode = () => field('asset_mode')?.value || 'EXISTING';
  function syncAssetMode() {
    form.querySelectorAll('[data-asset-mode]').forEach(container => setEnabled(container, container.dataset.assetMode === assetMode()));
  }
  function syncTerms() {
    const purpose = field('purpose')?.value;
    form.querySelectorAll('[data-terms]').forEach(container => setEnabled(container, purpose === 'LEASE_OR_SALE' || container.dataset.terms === purpose));
    syncConditional();
  }
  function syncConditional() {
    form.querySelectorAll('[data-show-when]').forEach(container => {
      const [name, expected] = container.dataset.showWhen.split(':');
      const control = field(name);
      setEnabled(container, Boolean(control) && String(control.value) === expected && !container.closest('[data-terms][hidden]'));
    });
  }
  function currencySymbol(code) {
    return ({NGN:'₦',USD:'$',EUR:'€',GBP:'£',GHS:'GH₵',ZAR:'R',CAD:'C$',AUD:'A$',AED:'AED'})[String(code || '').toUpperCase()] || String(code || 'Currency').toUpperCase();
  }
  function syncRateLabels() {
    const currency = currencySymbol(field('lease_currency')?.value);
    const unit = field('duration_unit')?.selectedOptions[0]?.textContent || 'Unit';
    form.querySelector('[data-rate-label]')?.replaceChildren(document.createTextNode(`Rate (${currency} / ${unit})`));
    form.querySelector('[data-operator-rate-label]')?.replaceChildren(document.createTextNode(`Operator rate (${currency} / ${unit})`));
  }

  const directoryControls = {};
  function buildDirectory(select) {
    const kind = select.dataset.marketDirectory;
    const create = field(kind === 'oem' ? 'new_oem' : 'new_model');
    const label = select.closest('label');
    const input = document.createElement('input');
    const list = document.createElement('div');
    const status = document.createElement('small');
    input.type = 'search';
    input.autocomplete = 'off';
    input.maxLength = 255;
    input.placeholder = kind === 'oem' ? 'Search or create manufacturer' : 'Search or create OEM model';
    input.setAttribute('role','combobox');
    input.setAttribute('aria-autocomplete','list');
    input.setAttribute('aria-expanded','false');
    list.className = 'catalog-options market-directory-options';
    list.setAttribute('role','listbox');
    list.hidden = true;
    status.className = 'catalog-selection';
    select.hidden = true;
    label.append(input, list, status);

    const selectedText = () => create.value || (select.value ? select.selectedOptions[0]?.textContent.trim() : '');
    const available = () => [...select.options].filter(option => {
      if (!option.value) return false;
      if (kind !== 'model') return true;
      return Boolean(field('oem_id')?.value) && option.dataset.parent === field('oem_id').value;
    });
    function close() { list.hidden = true; input.setAttribute('aria-expanded','false'); }
    function choose(option, newValue = '') {
      select.value = option?.value || '';
      create.value = newValue;
      input.value = newValue || option?.textContent.trim() || '';
      status.textContent = newValue ? 'New suggestion — pending marketplace verification' : '';
      close();
      if (kind === 'oem') {
        const model = directoryControls.model;
        if (model) { field('oem_model_id').value=''; field('new_model').value=''; model.refresh(); }
      }
    }
    function render() {
      if (input.disabled) return;
      const query = input.value.trim().toLocaleLowerCase();
      const matches = available().filter(option => option.textContent.toLocaleLowerCase().includes(query));
      list.replaceChildren();
      for (const option of matches.slice(0,20)) {
        const item = document.createElement('button');
        item.type='button'; item.setAttribute('role','option'); item.textContent=option.textContent;
        item.addEventListener('mousedown', event => event.preventDefault());
        item.addEventListener('click', () => choose(option));
        list.append(item);
      }
      if (query && !available().some(option => option.textContent.trim().toLocaleLowerCase() === query)) {
        const item=document.createElement('button');
        item.type='button'; item.className='catalog-create'; item.textContent=`+ Create “${input.value.trim()}”`;
        item.addEventListener('mousedown', event => event.preventDefault());
        item.addEventListener('click', () => choose(null,input.value.trim()));
        list.append(item);
      }
      list.hidden = !list.children.length;
      input.setAttribute('aria-expanded',String(!list.hidden));
    }
    function refresh() {
      const disabled = kind === 'model' && !field('oem_id')?.value && !field('new_oem')?.value;
      input.disabled = disabled;
      input.value = disabled ? '' : selectedText();
      input.placeholder = disabled ? 'Choose an OEM first' : (kind === 'oem' ? 'Search or create manufacturer' : 'Search or create OEM model');
      status.textContent = create.value ? 'New suggestion — pending marketplace verification' : '';
      close();
    }
    input.addEventListener('focus',render);
    input.addEventListener('input',() => { select.value=''; create.value=''; render(); if(kind==='oem')directoryControls.model?.refresh(); });
    input.addEventListener('keydown',event => {
      if(event.key==='Escape') { input.value=selectedText(); close(); }
      if(event.key==='Enter' && !list.hidden) { event.preventDefault(); list.querySelector('button')?.click(); }
    });
    input.addEventListener('blur',() => {
      const typed=input.value.trim();
      const exact=available().find(option=>option.textContent.trim().toLocaleLowerCase()===typed.toLocaleLowerCase());
      if(typed && !select.value && !create.value) choose(exact,exact?'':typed);
      else { input.value=selectedText(); close(); }
    });
    directoryControls[kind]={refresh,input};
    refresh();
  }
  form.querySelectorAll('[data-market-directory]').forEach(buildDirectory);

  const compliance = document.createElement('fieldset');
  compliance.innerHTML='<legend>Compliance details</legend><div class="form-grid"><label>Certification type<input name="certification_type" maxlength="150"></label><label>Certification valid until<input type="date" name="certification_valid_until"></label><label>Last inspected on<input type="date" name="last_inspected_on"></label></div>';
  form.querySelector('[data-terms="LEASE"]')?.before(compliance);
  for (const [name,value] of Object.entries(editData.compliance || {})) if(field(name)) field(name).value=value || '';

  const locationInput = document.querySelector('#market-location-search');
  if (locationInput) {
    const results=document.querySelector('#market-location-results');
    const actions=form.querySelector('.location-rule-actions');
    const chips=form.querySelector('.location-chips');
    const empty=form.querySelector('[data-location-empty]');
    const store=field('location_rules');
    let selected=null;
    let rules=[];
    try { rules=JSON.parse(store.value || '[]').map(rule=>({id:rule.id || rule.location_id,name:rule.name,type:rule.type,country:rule.country,rule:rule.rule})); } catch {}
    const save=()=>{store.value=JSON.stringify(rules.map(({id,rule})=>({id,rule})));};
    function paint() {
      chips.replaceChildren();
      empty.hidden=rules.length>0;
      for(const rule of rules) {
        const chip=document.createElement('span');
        chip.className=`location-chip ${rule.rule==='ALLOW'?'allow':'restrict'}`;
        chip.textContent=`${rule.rule==='ALLOW'?'+':'−'} ${rule.name}`;
        const remove=document.createElement('button');remove.type='button';remove.setAttribute('aria-label',`Remove ${rule.name}`);remove.textContent='×';
        remove.addEventListener('click',()=>{rules=rules.filter(item=>item.id!==rule.id);save();paint();});
        chip.append(remove);chips.append(chip);
      }
      save();
    }
    function search() {
      const query=locationInput.value.trim().toLocaleLowerCase();
      selected=null;actions.hidden=true;results.replaceChildren();
      if(query.length<2){results.hidden=true;return;}
      const matches=(editData.locations||[]).filter(location=>`${location.name} ${location.country||''} ${location.country_code||''}`.toLocaleLowerCase().includes(query)).slice(0,20);
      for(const location of matches){
        const button=document.createElement('button');button.type='button';button.setAttribute('role','option');
        button.textContent=`${location.name} · ${location.type.toLowerCase()}${location.country && location.country!==location.name?' · '+location.country:''}`;
        button.addEventListener('click',()=>{selected=location;locationInput.value=location.name;results.hidden=true;actions.hidden=false;});
        results.append(button);
      }
      results.hidden=!matches.length;
    }
    locationInput.addEventListener('input',search);
    locationInput.addEventListener('focus',search);
    form.querySelectorAll('[data-location-rule]').forEach(button=>button.addEventListener('click',()=>{
      if(!selected)return;
      const existing=rules.find(rule=>rule.id===selected.id);
      if(existing){existing.rule=button.dataset.locationRule;}
      else rules.push({...selected,rule:button.dataset.locationRule});
      selected=null;locationInput.value='';actions.hidden=true;paint();
    }));
    paint();
  }

  const listingId=field('id')?.value;
  if(listingId){
    const availability=document.createElement('section');
    availability.className='manage-table';
    availability.innerHTML='<h2>Availability calendar</h2><p>Date ranges marked unavailable override the general available-from date.</p><div class="availability-list"></div><div class="form-grid"><label>Start<input type="date" name="period_start"></label><label>End<input type="date" name="period_end"></label><label>Availability<select name="period_availability"><option value="UNAVAILABLE">Unavailable / blocked</option><option value="AVAILABLE">Available</option></select></label><label>Note<input name="period_note" maxlength="255"></label><div class="full"><button class="market-button" type="button" data-period-add>Add date range</button></div></div>';
    form.after(availability);
    const post=(values)=>{const action=document.createElement('form');action.method='post';action.action='marketplace-action';for(const [name,value] of Object.entries(values)){const input=document.createElement('input');input.type='hidden';input.name=name;input.value=value;action.append(input);}document.body.append(action);action.submit();};
    for(const period of editData.periods||[]){
      const chip=document.createElement('span');chip.className='availability-chip';chip.textContent=`${period.start_date} – ${period.end_date} · ${period.availability}${period.note?' · '+period.note:''}`;
      const remove=document.createElement('button');remove.type='button';remove.textContent='×';remove.setAttribute('aria-label','Remove availability period');
      remove.addEventListener('click',()=>post({csrf:field('csrf').value,action:'availability.delete',id:listingId,period_id:period.id}));
      chip.append(remove);availability.querySelector('.availability-list').append(chip);
    }
    availability.querySelector('[data-period-add]').addEventListener('click',()=>post({csrf:field('csrf').value,action:'availability.add',id:listingId,start_date:availability.querySelector('[name="period_start"]').value,end_date:availability.querySelector('[name="period_end"]').value,availability:availability.querySelector('[name="period_availability"]').value,note:availability.querySelector('[name="period_note"]').value}));
  }

  form.addEventListener('change',event=>{
    if(event.target.name==='asset_mode')syncAssetMode();
    if(event.target.name==='purpose')syncTerms();
    if(['operator_included','consumables_included','mobilization_option','demobilization_option','maintenance_option','insurance_option','payment_terms'].includes(event.target.name))syncConditional();
    if(['duration_unit','lease_currency'].includes(event.target.name))syncRateLabels();
  });
  form.addEventListener('input',event=>{if(event.target.name==='lease_currency')syncRateLabels();});
  syncAssetMode();syncTerms();syncConditional();syncRateLabels();
})();
