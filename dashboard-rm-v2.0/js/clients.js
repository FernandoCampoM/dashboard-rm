import { fmt$, fmtNum, extractApiData, showLoader, hideLoader } from './app.js';

let clientsTable = null;
let allClients   = [];

export async function loadClientsSection(isNew) {
  if (isNew) {
    loadCategoryOptions();
    bindEvents();
  }
  await refreshClients();
}

async function refreshClients() {
  showLoader();
  try {
    const data = await fetchData('Clients', {});
    allClients = extractApiData(data);
    renderKPIs(allClients);
    buildTable(allClients);
  } catch (err) {
    console.error('[clients.js]', err);
  } finally {
    hideLoader();
  }
}

// ── KPIs ─────────────────────────────────────────────────────────────────────
function renderKPIs(clients) {
  const now    = moment();
  const active = clients.filter(c => (c.Active || c.Status || 'S').toUpperCase() === 'S').length;
  const recent = clients.filter(c => c.LastPurchaseDate && moment(c.LastPurchaseDate).isValid()
    && moment(c.LastPurchaseDate).isAfter(now.clone().subtract(30,'days'))).length;
  const newThisMonth = clients.filter(c => c.CreateDate && moment(c.CreateDate).isValid()
    && moment(c.CreateDate).isSame(now, 'month')).length;

  setText('cl-kpi-active', fmtNum(active));
  setText('cl-kpi-recent', fmtNum(recent));
  setText('cl-kpi-new',    fmtNum(newThisMonth));
}

// ── DataTable ─────────────────────────────────────────────────────────────────
function buildTable(clients) {
  if (clientsTable) { clientsTable.destroy(); clientsTable = null; }

  const tbody = document.querySelector('#clients-table tbody');
  if (!tbody) return;

  tbody.innerHTML = clients.map(c => {
    const active = (c.Active || c.Status || 'S').toUpperCase() === 'S';
    const badge  = active
      ? '<span class="badge bg-success-subtle text-success-emphasis">Activo</span>'
      : '<span class="badge bg-secondary-subtle text-secondary-emphasis">Inactivo</span>';

    return `<tr>
      <td>${escHtml(c.ClientID || c.ID || '')}</td>
      <td>${escHtml(c.FirstName || c.Name || '')}</td>
      <td>${escHtml(c.LastName  || '')}</td>
      <td>${escHtml(c.City      || '')}</td>
      <td>${escHtml(c.Phone     || '')}</td>
      <td>${escHtml(c.Email     || '')}</td>
      <td>${escHtml(c.Category  || '')}</td>
      <td class="text-end">${fmt$(c.Balance || 0)}</td>
      <td>${c.LastPurchaseDate ? moment(c.LastPurchaseDate).format('MM/DD/YYYY') : '—'}</td>
      <td class="text-center">${badge}</td>
      <td class="text-center">
        <button class="btn btn-sm btn-outline-primary me-1 cl-edit-btn" data-id="${escAttr(c.ClientID||c.ID||'')}" title="Editar"><i class="fas fa-edit"></i></button>
        <button class="btn btn-sm btn-outline-danger cl-del-btn" data-id="${escAttr(c.ClientID||c.ID||'')}" data-name="${escAttr((c.FirstName||c.Name||'')+' '+(c.LastName||''))}" title="Eliminar"><i class="fas fa-trash"></i></button>
      </td>
    </tr>`;
  }).join('');

  clientsTable = new DataTable('#clients-table', {
    pageLength: 15,
    order: [[1, 'asc']],
    dom: 'Bfrtip',
    buttons: ['excel', 'print', 'colvis'],
  });
}

// ── Events ────────────────────────────────────────────────────────────────────
function bindEvents() {
  el('cl-add-btn')?.addEventListener('click', () => openClientModal(null, 'new'));
  el('cm-save-btn')?.addEventListener('click', saveClient);

  // Delegated listener at document level — survives DataTable DOM rebuilds
  document.addEventListener('click', e => {
    const editBtn = e.target.closest('.cl-edit-btn');
    const delBtn  = e.target.closest('.cl-del-btn');
    if (editBtn) openClientModal(editBtn.dataset.id, 'edit').catch(err => console.error('[clients] openClientModal:', err));
    if (delBtn)  confirmDeleteClient(delBtn.dataset.id, delBtn.dataset.name);
  });
}

// ── Category dropdown ─────────────────────────────────────────────────────────
async function loadCategoryOptions() {
  const sel = el('cm-category');
  if (!sel) return;
  try {
    const data = await fetchData('ClientCategories', {});
    const rows = extractApiData(data);
    const opts = rows.map(r => `<option value="${escAttr(r.CategoryName||r.Name||r.ID)}">${escHtml(r.CategoryName||r.Name||r.ID)}</option>`).join('');
    sel.innerHTML = `<option value="">Sin categoría</option>${opts}`;
  } catch { /* leave default */ }
}

// ── Modal ─────────────────────────────────────────────────────────────────────
async function openClientModal(id, mode) {
  el('cm-mode').value = mode;
  el('cm-error')?.classList.add('d-none');
  setText('cl-modal-title', mode === 'edit' ? 'Editar Cliente' : 'Nuevo Cliente');

  // Clear all fields
  ['cm-id','cm-firstname','cm-lastname','cm-addr1','cm-addr2','cm-city','cm-zip','cm-country','cm-phone','cm-email'].forEach(fid => {
    const e = el(fid);
    if (e) e.value = fid === 'cm-country' ? 'PR' : '';
  });
  el('cm-active').value   = 'S';
  el('cm-category').value = '';

  if (mode === 'new') {
    showLoader();
    try {
      const res = await fetchData('GetNewClientID', {});
      const newId = res?.userId || res?.clientId || res?.ClientID || '';
      el('cm-id').value = newId;
    } catch { /* leave blank */ } finally {
      hideLoader();
    }
  } else if (id) {
    const c = allClients.find(x => String(x.ClientID||x.ID||'') === String(id));
    if (c) {
      el('cm-id').value        = c.ClientID || c.ID || '';
      el('cm-firstname').value = c.FirstName || c.Name || '';
      el('cm-lastname').value  = c.LastName  || '';
      el('cm-addr1').value     = c.Address1  || '';
      el('cm-addr2').value     = c.Address2  || '';
      el('cm-city').value      = c.City      || '';
      el('cm-zip').value       = c.ZipCode   || '';
      el('cm-country').value   = c.Country   || 'PR';
      el('cm-phone').value     = c.Phone     || '';
      el('cm-email').value     = c.Email     || '';
      el('cm-active').value    = (c.Active || c.Status || 'S').toUpperCase();
      el('cm-category').value  = c.Category  || '';
    }
  }

  bootstrap.Modal.getOrCreateInstance(el('client-modal')).show();
}

async function saveClient() {
  const mode    = el('cm-mode').value;
  const errorEl = el('cm-error');
  errorEl?.classList.add('d-none');

  const payload = {
    ClientID:   el('cm-id')?.value        || '',
    FirstName:  el('cm-firstname')?.value || '',
    LastName:   el('cm-lastname')?.value  || '',
    Address1:   el('cm-addr1')?.value     || '',
    Address2:   el('cm-addr2')?.value     || '',
    City:       el('cm-city')?.value      || '',
    ZipCode:    el('cm-zip')?.value       || '',
    Country:    el('cm-country')?.value   || 'PR',
    Phone:      el('cm-phone')?.value     || '',
    Email:      el('cm-email')?.value     || '',
    Active:     el('cm-active')?.value    || 'S',
    Category:   el('cm-category')?.value  || '',
  };

  if (!payload.FirstName.trim()) {
    if (errorEl) { errorEl.textContent = 'El nombre es requerido.'; errorEl.classList.remove('d-none'); }
    return;
  }

  showLoader();
  try {
    const endpoint = mode === 'edit' ? 'UpdateClient' : 'CreateClient';
    const res = await postData(endpoint, payload);
    if (res?.success) {
      bootstrap.Modal.getInstance(el('client-modal'))?.hide();
      await refreshClients();
    } else {
      if (errorEl) { errorEl.textContent = res?.message || 'Error guardando el cliente.'; errorEl.classList.remove('d-none'); }
    }
  } catch (err) {
    if (errorEl) { errorEl.textContent = 'Error de comunicación.'; errorEl.classList.remove('d-none'); }
    console.error('[clients.js] saveClient:', err);
  } finally {
    hideLoader();
  }
}

async function confirmDeleteClient(id, name) {
  const result = await Swal.fire({
    title: '¿Eliminar cliente?',
    text: `"${name.trim()}" será eliminado.`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#ef4444',
    cancelButtonText: 'Cancelar',
    confirmButtonText: 'Eliminar',
  });
  if (!result.isConfirmed) return;

  showLoader();
  try {
    const res = await deleteRecord('DeleteClient', `ClientID=${encodeURIComponent(id)}`);
    if (res?.success) {
      await refreshClients();
    } else {
      await Swal.fire('Error', res?.message || 'No se pudo eliminar el cliente.', 'error');
    }
  } finally {
    hideLoader();
  }
}

// ── POST helpers ─────────────────────────────────────────────────────────────
async function postData(endpoint, body) {
  try {
    const res = await fetch(`api_proxy.php?endpoint=${encodeURIComponent(endpoint)}`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(body),
    });
    if (res.status === 403) { window.location.href = 'authentication/logout.php'; return null; }
    return res.json();
  } catch { return null; }
}

async function deleteRecord(endpoint, urlParams) {
  try {
    const res = await fetch(`api_proxy.php?endpoint=${encodeURIComponent(endpoint)}&${urlParams}`, { method: 'POST' });
    if (res.status === 403) { window.location.href = 'authentication/logout.php'; return null; }
    return res.json();
  } catch { return null; }
}

// ── Helpers ───────────────────────────────────────────────────────────────────
function el(id)        { return document.getElementById(id); }
function setText(id,v) { const e = el(id); if (e) e.textContent = v; }
function escHtml(s)    { const d = document.createElement('div'); d.textContent = s ?? ''; return d.innerHTML; }
function escAttr(s)    { return String(s ?? '').replace(/"/g, '&quot;').replace(/'/g, '&#39;'); }
