(() => {
  let sequence = 0;
  document.querySelectorAll('.catalog-fields').forEach(group => {
    const select = name => group.querySelector(`[name="${name}"]`);
    const controls = new Map();
    const parentFields = {subcategory_id: 'category_id', type_id: 'subcategory_id'};
    function update(changed) {
      for (const [childName, parentName] of Object.entries({...parentFields, model_id: 'brand_id'})) {
        const child = select(childName), parent = select(parentName);
        if (!child || !parent) continue;
        if (changed === parentName || (changed === 'category_id' && childName === 'type_id')) {
          child.value = '';
          controls.get(childName)?.clear();
        }
        for (const option of child.options) {
          if (!option.value) continue;
          option.hidden = !parent.value || option.dataset.parent !== parent.value;
          option.disabled = option.hidden;
        }
        if (child.selectedOptions[0]?.disabled) child.value = '';
        controls.get(childName)?.refresh();
      }
    }
    group.querySelectorAll('select[data-creatable]').forEach(native => {
      const kind = native.dataset.catalog;
      const label = native.closest('label');
      const title = label.firstChild.textContent.trim();
      const wrapper = document.createElement('div'); wrapper.className = 'catalog-combobox';
      // Keep the native select as a progressive-enhancement fallback and form value.
      label.after(wrapper); wrapper.append(label); native.hidden = true;
      const input = document.createElement('input'); input.type = 'text'; input.autocomplete = 'off'; input.maxLength = 150;
      input.id = `catalog-input-${++sequence}`; label.htmlFor = input.id; input.setAttribute('role', 'combobox');
      input.setAttribute('aria-autocomplete', 'list'); input.setAttribute('aria-expanded', 'false');
      input.placeholder = 'Search or create...'; label.append(input);
      const pending = document.createElement('input'); pending.type = 'hidden'; pending.name = `new_${kind}`; wrapper.append(pending);
      const list = document.createElement('div'); list.id = `catalog-options-${sequence}`; list.className = 'catalog-options';
      list.setAttribute('role', 'listbox'); list.setAttribute('aria-label', title); list.hidden = true; wrapper.append(list);
      input.setAttribute('aria-controls', list.id);
      const status = document.createElement('small'); status.className = 'catalog-selection'; status.setAttribute('role', 'status'); wrapper.append(status);
      let active = -1, choices = [];
      const selectedText = () => pending.value || (native.value ? native.selectedOptions[0].textContent : '');
      function close() {list.hidden = true; input.setAttribute('aria-expanded', 'false'); input.removeAttribute('aria-activedescendant'); active = -1;}
      function parentReady() {const parent = parentFields[native.name]; return !parent || Boolean(select(parent)?.value || controls.get(parent)?.pending.value);}
      function refresh() {
        input.disabled = !parentReady();
        input.placeholder = input.disabled ? (kind === 'subcategory' ? 'Choose a category first' : 'Choose a subcategory first') : 'Search or create...';
        input.value = selectedText();
        status.textContent = pending.value ? 'New company entry - saved with this form' : '';
        close();
      }
      function choose(choice) {
        native.value = choice.option?.value || '';
        pending.value = choice.create || '';
        refresh(); update(native.name); input.focus(); close();
      }
      function render(showAll = false) {
        if (input.disabled) return;
        const query = showAll ? '' : input.value.trim().toLocaleLowerCase();
        const available = [...native.options].filter(option => option.value && !option.disabled);
        choices = [{text: 'Not specified', option: native.options[0]}, ...available.filter(option => option.textContent.toLocaleLowerCase().includes(query)).map(option => ({text: option.textContent, option}))];
        const text = input.value.trim();
        if (!showAll && text && !available.some(option => (option.dataset.name || option.textContent).toLocaleLowerCase() === text.toLocaleLowerCase() || option.textContent.toLocaleLowerCase() === text.toLocaleLowerCase())) {
          choices.push({text: `+ Create "${text}"`, create: text});
        }
        list.replaceChildren(); active = -1; input.removeAttribute('aria-activedescendant');
        choices.forEach((choice, index) => {
          const node = document.createElement('div'); node.id = `${list.id}-${index}`; node.setAttribute('role', 'option'); node.setAttribute('aria-selected', 'false');
          node.textContent = choice.text; if (choice.create) node.className = 'catalog-create';
          node.addEventListener('mousedown', event => event.preventDefault());
          node.addEventListener('click', () => choose(choice)); list.append(node);
        });
        list.hidden = false; input.setAttribute('aria-expanded', 'true');
      }
      input.addEventListener('focus', () => render(true));
      input.addEventListener('click', () => {if (list.hidden) render(true);});
      input.addEventListener('input', () => render());
      input.addEventListener('keydown', event => {
        if (event.key === 'Escape') {input.value = selectedText(); close(); return;}
        if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
          event.preventDefault(); if (list.hidden) render(true);
          active = (active + (event.key === 'ArrowDown' ? 1 : -1) + choices.length) % choices.length;
          [...list.children].forEach((node, index) => node.setAttribute('aria-selected', String(index === active)));
          const node = list.children[active]; input.setAttribute('aria-activedescendant', node.id); node.scrollIntoView({block: 'nearest'});
        } else if (event.key === 'Enter' && !list.hidden) {
          event.preventDefault(); if (active >= 0) choose(choices[active]);
        }
      });
      input.addEventListener('blur', () => {input.value = selectedText(); close();});
      controls.set(native.name, {pending, refresh, clear() {pending.value = ''; input.value = ''; status.textContent = ''; close();}});
      refresh();
    });
    group.addEventListener('change', event => {if (event.target.matches('select')) update(event.target.name);});
    group.closest('form')?.addEventListener('reset', () => setTimeout(() => {controls.forEach(control => control.clear()); update(); controls.forEach(control => control.refresh());}, 0));
    update();
  });
})();
