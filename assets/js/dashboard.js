(() => {
  'use strict';
  const config = JSON.parse(document.getElementById('dashboard-bootstrap').textContent);
  let state = config.state, hashes = config.hashes, source, timer, stopped = false, busy = false;
  let lastContact = Date.now();
  let equipmentPage = 1, equipmentRequest = 0, equipmentController, searchTimer, lastEquipmentCheck = 0;
  const cacheKey = `nonagon-dashboard:${config.session}:${state.meta.scope}`;
  const escape = value => String(value ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
  const badges = {OPERATIONAL:'✓ Operational',MAINTENANCE:'◷ Maintenance',DOWN:'! Critical / Down',CRITICAL:'! Critical',HIGH:'▲ High',MEDIUM:'● Medium'};
  const badge = value => `<span class="badge ${escape(value.toLowerCase())}">${escape(badges[value] || value)}</span>`;
  const toUTC = value => value.replace(' ', 'T') + 'Z';
  const equipmentIcon = document.querySelector('.utilization-panel .widget-icon').innerHTML;
  const alertIcon = document.querySelector('.alerts-panel .widget-icon').innerHTML;
  function equipmentRows(items) {
    document.getElementById('equipment-body').innerHTML = items.map(item => `<tr><td><a class="record-link" href="equipment?id=${encodeURIComponent(item.id)}">${escape(item.name)}</a><small>${escape(item.asset_code)}</small></td><td>${badge(item.status)}</td><td>${item.operator_name ? `<a href="operators?id=${encodeURIComponent(item.operator_id)}">${escape(item.operator_name)}</a>` : 'Unassigned'}</td><td><time datetime="${escape(toUTC(item.last_activity_at))}">${escape(item.last_activity_at)}</time></td></tr>`).join('');
    document.getElementById('equipment-empty').hidden = items.length > 0;
  }
  async function loadEquipment() {
    if (stopped || !navigator.onLine) return;
    const request = ++equipmentRequest;
    equipmentController?.abort();
    equipmentController = new AbortController();
    const timeout = setTimeout(() => equipmentController?.abort(), 10000);
    const params = new URLSearchParams({...config.filters,q:document.getElementById('equipment-search').value,status:document.getElementById('equipment-status').value,page:String(equipmentPage)});
    document.getElementById('equipment-body').setAttribute('aria-busy','true');
    try {
      const response = await fetch(`dashboard-equipment?${params}`, {cache:'no-store',signal:equipmentController.signal});
      if ([401,403].includes(response.status)) { revoke(); return; }
      if (!response.ok) throw new Error('Equipment unavailable');
      const result = await response.json();
      if (request !== equipmentRequest || stopped) return;
      equipmentPage = result.page;
      equipmentRows(result.items);
      document.getElementById('equipment-page-summary').textContent = result.total ? `Showing ${(result.page-1)*5+1}–${(result.page-1)*5+result.items.length} of ${result.total} assets` : 'No matching equipment';
      document.getElementById('equipment-previous').disabled = result.page <= 1;
      document.getElementById('equipment-next').disabled = result.page >= result.pages;
      lastEquipmentCheck = Date.now();
    } catch (error) {
      if (request === equipmentRequest && error.name !== 'AbortError') document.getElementById('equipment-page-summary').textContent = 'Could not refresh equipment. Showing the last loaded page.';
    } finally {
      clearTimeout(timeout);
      if (request === equipmentRequest) document.getElementById('equipment-body')?.removeAttribute('aria-busy');
    }
  }
  function utilizationCards() {
    document.getElementById('utilization-list').innerHTML = state.equipment.slice(0,2).map(item => {
      const url = `equipment?id=${encodeURIComponent(item.id)}`;
      return `<article class="utilization-card"><div class="widget-card-heading">${equipmentIcon}<a href="${url}">${escape(item.name)}</a><a class="record-menu" href="${url}" aria-label="View equipment details">⋮</a></div><p>Next maintenance: ${escape(item.next_maintenance_at || 'Not scheduled')}</p><p>Utilization and usage: <strong>${item.utilization_percent == null ? 'Not recorded' : escape(item.utilization_percent)+'%'}</strong></p>${badge(item.status)}</article><div class="widget-card-actions"><a href="${url}">View activity logs</a><a href="${url}#maintenance-records">View maintenance</a></div>`;
    }).join('');
    document.getElementById('utilization-empty').hidden = state.equipment.length > 0;
  }
  function remember() {
    try {
      for (const key of Object.keys(sessionStorage)) if (key.startsWith('nonagon-dashboard:') && key !== cacheKey) sessionStorage.removeItem(key);
      sessionStorage.setItem(cacheKey, JSON.stringify(state));
    } catch { /* Last-known state remains in memory if storage is unavailable. */ }
  }
  function restore() {
    try {
      const cached = JSON.parse(sessionStorage.getItem(cacheKey));
      if (cached?.meta?.scope === state.meta.scope && Date.parse(cached.meta.captured_at) >= Date.parse(state.meta.captured_at)) {
        state = cached;
        render(state);
      }
    } catch { /* Keep the currently displayed snapshot. */ }
  }
  function connection(live, label) {
    document.getElementById('connection').textContent = `${live ? '●' : '○'} ${label}`;
    document.getElementById('offline-banner').hidden = live;
    document.querySelectorAll('.alert-quick button').forEach(button => { button.disabled = !live; });
  }
  function times() {
    const age = Math.max(0, Math.floor((Date.now() - Date.parse(state.meta.captured_at)) / 1000));
    document.getElementById('freshness').textContent = `Last checked ${age}s ago · ${new Date(state.meta.captured_at).toLocaleTimeString('en-GB',{timeZone:'UTC'})} UTC`;
    document.querySelectorAll('[data-triggered]').forEach(node => {
      const minutes = Math.max(0, Math.floor((Date.now() - Date.parse(node.dataset.triggered)) / 60000));
      node.textContent = minutes < 60 ? `${minutes}m ago` : minutes < 1440 ? `${Math.floor(minutes / 60)}h ago` : `${Math.floor(minutes / 1440)}d ago`;
      node.title = node.dataset.triggered;
    });
    if (Date.now() - lastContact > 20000) connection(false, 'Data may be stale');
  }
  function render(changes) {
    if (changes.summary) {
      for (const [attr, values] of [['metric',state.summary.metrics],['risk',state.summary.risks],['maintenance',state.summary.maintenance]]) {
        for (const [key, value] of Object.entries(values)) document.querySelectorAll(`[data-${attr}="${key}"]`).forEach(node => { node.textContent = Number(value).toLocaleString(); });
      }
      for (const [key,value] of Object.entries(state.summary.trends)) document.querySelectorAll(`[data-trend="${key}"]`).forEach(node => {
        node.textContent = value === null ? 'No history yet' : `${value > 0 ? '↗ +' : value < 0 ? '↘ ' : '→ '}${value}`;
      });
    }
    if (changes.equipment) {
      utilizationCards();
    }
    if (changes.alerts) {
      document.getElementById('alert-list').innerHTML = state.alerts.map(alert => {
        const url = `alert?id=${encodeURIComponent(alert.id)}`;
        const actions = config.canManage ? `<a href="${url}#assign">Assign</a>${!alert.acknowledged_at ? `<form action="operations-action" method="post" class="alert-quick"><input type="hidden" name="csrf" value="${escape(config.csrf)}"><input type="hidden" name="id" value="${escape(alert.id)}"><input type="hidden" name="action" value="acknowledge"><button class="text-button">Acknowledge</button></form>` : ''}<a href="${url}#escalate">Escalate</a>` : '';
        return `<article class="alert-card severity-${escape(alert.severity.toLowerCase())}"><div class="widget-card-heading">${alertIcon}<a href="${url}">${escape(alert.equipment_name)}</a><a class="record-menu" href="${url}" aria-label="View alert details">⋮</a></div><h3>${escape(alert.title)}</h3><p>${escape(alert.asset_code)} · <time data-triggered="${escape(toUTC(alert.triggered_at))}"></time></p>${badge(alert.severity)}<div class="alert-actions"><a href="${url}">View details</a>${actions}</div></article>`;
      }).join('');
      document.getElementById('alerts-empty').hidden = state.alerts.length > 0;
    }
    times();
  }
  function accept(packet, label) {
    if (stopped) return;
    if (packet.meta.scope !== state.meta.scope) { revoke(); return; }
    Object.assign(state, packet.changes);
    state.meta = packet.meta;
    hashes = packet.hashes;
    lastContact = Date.now();
    render(packet.changes);
    if (packet.changes.equipment || packet.changes.summary || Date.now()-lastEquipmentCheck > 10000) loadEquipment();
    document.getElementById('load-time').textContent = `Query ${state.meta.load_ms} ms${state.meta.cache_hit ? ' · cached' : ''}`;
    const lag = state.meta.event_lag_ms;
    document.getElementById('event-lag').textContent = lag == null ? 'No pending event delay' : `Event delivery ${Math.round(lag)} ms`;
    remember();
    connection(true, label);
  }
  function url(stream = false) {
    const params = new URLSearchParams({...config.filters,hashes:JSON.stringify(hashes)});
    if (stream) params.set('stream','1');
    return `dashboard-data?${params}`;
  }
  function revoke() {
    stopped = true;
    source?.close();
    clearTimeout(timer);
    try { sessionStorage.removeItem(cacheKey); } catch {}
    // Remove sensitive state immediately; a reload obtains the new permissions.
    document.getElementById('main').replaceChildren();
    location.replace('dashboard');
  }
  async function poll() {
    if (stopped || busy || document.hidden) return;
    busy = true;
    try {
      const response = await fetch(url(), {cache:'no-store',signal:AbortSignal.timeout(10000)});
      if ([401,403].includes(response.status)) { revoke(); return; }
      if (!response.ok) throw new Error('Data unavailable');
      accept(await response.json(), 'Connected · polling');
    } catch { restore(); connection(false, navigator.onLine ? 'Reconnecting' : 'Offline'); }
    finally { busy = false; }
  }
  function fallback() {
    source?.close();
    clearTimeout(timer);
    if (stopped) return;
    poll().finally(() => { if (!stopped) timer = setTimeout(fallback, 10000); });
  }
  function connect() {
    source?.close();
    clearTimeout(timer);
    if (stopped || document.hidden) return;
    if (!navigator.onLine) { connection(false,'Offline'); return; }
    if (!window.EventSource) { fallback(); return; }
    source = new EventSource(url(true));
    for (const type of ['delta','freshness']) source.addEventListener(type, event => {
      try { accept(JSON.parse(event.data), 'Live updates'); } catch { fallback(); }
    });
    source.addEventListener('revoked', revoke);
    source.addEventListener('unavailable', fallback);
    source.onerror = fallback;
  }
  document.addEventListener('submit', async event => {
    if (!event.target.matches('.alert-quick')) return;
    event.preventDefault();
    const form = event.target, button = form.querySelector('button');
    button.disabled = true;
    try {
      const response = await fetch('operations-action', {method:'POST',body:new FormData(form),headers:{Accept:'application/json'},signal:AbortSignal.timeout(10000)});
      const result = await response.json();
      if ([401,403,419].includes(response.status)) { revoke(); return; }
      if (!response.ok) throw new Error(result.error || 'Action failed');
      await poll();
    } catch (error) {
      connection(false,'Action not confirmed');
      const banner = document.getElementById('offline-banner');
      banner.textContent = `${error.message}. Check the alert details before retrying.`;
    } finally { if (button.isConnected) button.disabled = !navigator.onLine; }
  });
  document.getElementById('site-filter').addEventListener('change', event => {
    const unit = document.getElementById('unit-filter');
    unit.value = '';
    for (const option of unit.options) option.hidden = !!event.target.value && !!option.dataset.site && option.dataset.site !== event.target.value;
  });
  document.getElementById('retry-live').addEventListener('click', connect);
  document.getElementById('equipment-search-form').addEventListener('submit', event => { event.preventDefault(); clearTimeout(searchTimer); equipmentPage=1; loadEquipment(); });
  document.getElementById('equipment-search').addEventListener('input', () => { clearTimeout(searchTimer); equipmentPage=1; searchTimer=setTimeout(loadEquipment,250); });
  document.getElementById('equipment-status').addEventListener('change', () => { equipmentPage=1; loadEquipment(); });
  document.getElementById('equipment-previous').addEventListener('click', () => { equipmentPage=Math.max(1,equipmentPage-1); loadEquipment(); });
  document.getElementById('equipment-next').addEventListener('click', () => { equipmentPage++; loadEquipment(); });
  window.addEventListener('offline', () => { source?.close(); clearTimeout(timer); connection(false,'Offline'); });
  window.addEventListener('online', connect);
  window.addEventListener('pagehide', () => { source?.close(); clearTimeout(timer); });
  window.addEventListener('pageshow', event => { if(event.persisted) location.reload(); });
  document.addEventListener('visibilitychange', () => { if(document.hidden){source?.close();clearTimeout(timer);}else{poll().then(connect);} });
  setInterval(times, 5000);
  remember();
  times();
  loadEquipment();
  connect();
})();
