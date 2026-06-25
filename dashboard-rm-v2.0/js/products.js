import { fmt$, fmtPct, fmtNum, extractApiData, showLoader, hideLoader, PALETTE, CHART_COLORS } from './app.js';

let topChart           = null;
let bottomChart        = null;
let movChart           = null;
let receiveChart       = null;
let catalogTable       = null;
let receiveProductCode = '';

function today()   { return moment().format('YYYY-MM-DD'); }
function m1From()  { return moment().startOf('month').format('YYYY-MM-DD'); }

export async function loadProductsSection(isNew) {
  if (isNew) {
    bindTabEvents();
    bindSearchEvents();
    bindMovementEvents();
    bindCatalogEvents();
    bindModalEvents();
    bindReceiveEvents();
  }
  await loadPerformanceTab();
}

// ── Tab activation ────────────────────────────────────────────────────────────
function bindTabEvents() {
  document.querySelector('[href="#prod-tab-catalog"]')?.addEventListener('shown.bs.tab', loadCatalogTab);
  document.querySelector('[href="#prod-tab-performance"]')?.addEventListener('shown.bs.tab', loadPerformanceTab);
}

// ── Performance tab ────────────────────────────────────────────────────────────
async function loadPerformanceTab() {
  showLoader();
  try {
    const from = m1From(), to = today();
    const [topData, botData] = await Promise.all([
      fetchData('TopSellProducts',  { DateFrom: from, DateTo: to }),
      fetchData('LowSellProducts',  { DateFrom: from, DateTo: to }),
    ]);
    renderTopChart(extractApiData(topData));
    renderBottomChart(extractApiData(botData));
  } catch (err) {
    console.error('[products.js] performance tab:', err);
  } finally {
    hideLoader();
  }
}

function renderTopChart(rows) {
  const canvas = el('prod-top-chart');
  if (!canvas) return;
  if (topChart) { topChart.destroy(); topChart = null; }

  const top10  = rows.slice(0, 10);
  const labels = top10.map(r => truncate(r.ProductName, 20));
  const sales  = top10.map(r => parseFloat(r.TotalSales) || 0);

  topChart = new Chart(canvas, {
    type: 'bar',
    data: {
      labels,
      datasets: [{ label: 'Ventas ($)', data: sales, backgroundColor: alpha(PALETTE.success,.8), borderRadius: 4 }],
    },
    options: baseOpts({
      indexAxis: 'y',
      scales: {
        x: { ticks: { callback: v => '$'+numeral(v).format('0,0') } },
        y: { grid: { display: false }, ticks: { font: { size: 10 } } },
      },
    }),
  });
}

function renderBottomChart(rows) {
  const canvas = el('prod-bottom-chart');
  if (!canvas) return;
  if (bottomChart) { bottomChart.destroy(); bottomChart = null; }

  const bot    = rows.slice(0, 10);
  const labels = bot.map(r => truncate(r.ProductName, 20));
  const sales  = bot.map(r => parseFloat(r.TotalSales) || 0);

  bottomChart = new Chart(canvas, {
    type: 'bar',
    data: {
      labels,
      datasets: [{ label: 'Ventas ($)', data: sales, backgroundColor: alpha(PALETTE.danger,.7), borderRadius: 4 }],
    },
    options: baseOpts({
      indexAxis: 'y',
      scales: {
        x: { ticks: { callback: v => '$'+numeral(v).format('0,0') } },
        y: { grid: { display: false }, ticks: { font: { size: 10 } } },
      },
    }),
  });
}

// ── Product movement ───────────────────────────────────────────────────────────
function bindMovementEvents() {
  el('prod-movement-btn')?.addEventListener('click', loadMovementChart);
  el('prod-movement-code')?.addEventListener('keypress', e => { if (e.key === 'Enter') loadMovementChart(); });
}

async function loadMovementChart() {
  const query  = (el('prod-movement-code')?.value || '').trim();
  const msgEl  = el('prod-movement-msg');
  const wrap   = el('prod-movement-wrap');
  const detail = el('mv-detail-wrap');

  if (!query) {
    if (msgEl) msgEl.textContent = 'Ingresa un código, barcode o nombre.';
    return;
  }

  if (msgEl) msgEl.textContent = 'Buscando...';
  if (detail) detail.classList.add('d-none');
  showLoader();

  try {
    // 1. Resolve query to an ItemCode — probes use raw fetch (no SweetAlert on failure)
    let code = null;
    let name = '';

    if (/^\d{8,}$/.test(query)) {
      try {
        const r  = await fetch(`api_proxy.php?endpoint=InfoBarCode&barcode=${encodeURIComponent(query)}`);
        if (r.ok) {
          const bc = await r.json();
          if (bc && bc !== 'NoMatch' && bc.ProductCode) code = bc.ProductCode;
        }
      } catch {}
    }

    if (!code) {
      try {
        const r  = await fetch(`api_proxy.php?endpoint=GetProduct&ItemCode=${encodeURIComponent(query)}`);
        if (r.ok) {
          const pd   = await r.json();
          const prow = extractApiData(pd);
          if (prow.length && prow[0].ProductCode) { code = prow[0].ProductCode; name = prow[0].ProductName || ''; }
        }
      } catch {}
    }

    if (!code) {
      try {
        const r    = await fetch('api_proxy.php?endpoint=GetAllProducts');
        if (r.ok) {
          const allData = await r.json();
          const allRows = extractApiData(allData);
          const q       = query.toLowerCase();
          const match   = allRows.find(p => (p.ProductName || '').toLowerCase().includes(q));
          if (match) { code = match.ProductCode; name = match.ProductName || ''; }
        }
      } catch {}
    }

    if (!code) {
      if (msgEl) msgEl.textContent = 'Producto no encontrado.';
      if (wrap)  wrap.classList.add('d-none');
      return;
    }

    // 2. Fetch movement + product details in parallel
    const [movData, prodData] = await Promise.all([
      fetchData('ProdMovementChart', { ItemCode: code }),
      fetchData('GetProduct',        { ItemCode: code }),
    ]);

    const movRows  = Array.isArray(movData) ? movData : extractApiData(movData);
    const prodRows = extractApiData(prodData);
    const prod     = prodRows[0] || {};
    if (!name) name = prod.ProductName || code;
    const stock = parseFloat(prod.CurrentStock || prod.OnHand || 0);

    if (!movRows.length) {
      if (msgEl) msgEl.textContent = 'No hay datos de movimiento para este producto.';
      if (wrap)  wrap.classList.add('d-none');
      return;
    }

    // 3. Render movement chart (existing Ventas/Unidades combo)
    if (msgEl) msgEl.textContent = '';
    if (wrap)  wrap.classList.remove('d-none');
    renderMovChart(movRows);

    // 4. Render rich detail panel with mv- prefix
    setText('mv-product-name', name);
    renderMonthButtons(movRows, 'mv-');
    renderAnnualSummary(movRows, stock, 'mv-');
    if (detail) detail.classList.remove('d-none');

  } catch (err) {
    if (msgEl) msgEl.textContent = 'Error cargando datos.';
    console.error('[products.js] movement:', err);
  } finally {
    hideLoader();
  }
}

function renderMovChart(rows) {
  const canvas = el('prod-movement-chart');
  if (!canvas) return;
  if (movChart) { movChart.destroy(); movChart = null; }

  const labels = rows.map(r => r.MonthYear || r.Month || r.Period || '');
  const sales  = rows.map(r => parseFloat(r.TotalSales || r.Sales) || 0);
  const units  = rows.map(r => parseFloat(r.TotalQuantitySold || r.Quantity || r.Units) || 0);

  movChart = new Chart(canvas, {
    type: 'bar',
    data: {
      labels,
      datasets: [
        { label: 'Ventas ($)', data: sales, backgroundColor: alpha(PALETTE.primary,.8), borderRadius: 3, yAxisID: 'y' },
        { label: 'Unidades',   data: units, type: 'line', borderColor: PALETTE.warning, backgroundColor: 'transparent', pointRadius: 4, yAxisID: 'y1' },
      ],
    },
    options: baseOpts({
      scales: {
        x:  { grid: { display: false } },
        y:  { ticks: { callback: v => '$'+numeral(v).format('0,0') }, position: 'left' },
        y1: { ticks: {}, position: 'right', grid: { drawOnChartArea: false } },
      },
    }),
  });
}

// ── Catalog tab ────────────────────────────────────────────────────────────────
async function loadCatalogTab() {
  showLoader();
  try {
    await Promise.all([
      loadDeptOptions('prod-filter-dept', 'pm-dept'),
      loadCatOptions('prod-filter-cat',   'pm-cat'),
    ]);
    await loadCatalogTable();
  } catch (err) {
    console.error('[products.js] catalog tab:', err);
  } finally {
    hideLoader();
  }
}

async function loadDeptOptions(...selectIds) {
  const data = await fetchData('InventoryDepartments', {});
  const rows = extractApiData(data);
  selectIds.forEach(id => {
    const sel = el(id);
    if (!sel) return;
    const current = sel.value;
    const opts = rows.map(r => `<option value="${escAttr(r.DepartmentName||r.Name||r.ID)}">${escHtml(r.DepartmentName||r.Name||r.ID)}</option>`).join('');
    sel.innerHTML = `<option value="">Todos</option>${opts}`;
    if (current) sel.value = current;
  });
}

async function loadCatOptions(...selectIds) {
  const data = await fetchData('InventoryCategories', {});
  const rows = extractApiData(data);
  selectIds.forEach(id => {
    const sel = el(id);
    if (!sel) return;
    const opts = rows.map(r => `<option value="${escAttr(r.CategoryName||r.Name||r.ID)}">${escHtml(r.CategoryName||r.Name||r.ID)}</option>`).join('');
    sel.innerHTML = `<option value="">Todas</option>${opts}`;
  });
}

async function loadCatalogTable() {
  const dept = el('prod-filter-dept')?.value || '';
  const cat  = el('prod-filter-cat')?.value  || '';
  const code = (el('prod-filter-code')?.value || '').trim();
  const name = (el('prod-filter-name')?.value || '').trim();

  const params = {};
  if (dept) params.Department = dept;
  if (cat)  params.Category   = cat;
  if (code) params.ItemCode   = code;

  showLoader();
  try {
    const data = await fetchData('GetAllProducts', params);
    let rows = extractApiData(data);
    if (name) {
      const q = name.toLowerCase();
      rows = rows.filter(r => (r.ProductName||'').toLowerCase().includes(q));
    }
    buildCatalogTable(rows);
  } finally {
    hideLoader();
  }
}

function bindCatalogEvents() {
  el('prod-filter-apply')?.addEventListener('click', loadCatalogTable);
  el('prod-filter-reset')?.addEventListener('click', () => {
    ['prod-filter-dept','prod-filter-cat','prod-filter-code','prod-filter-name'].forEach(id => {
      const e = el(id);
      if (e) e.value = '';
    });
    loadCatalogTable();
  });
  el('prod-add-btn')?.addEventListener('click', () => openProductModal(null));

  // Delegated handler — on a stable ancestor so it survives DataTable rebuilds
  el('prod-tab-catalog')?.addEventListener('click', e => {
    const editBtn    = e.target.closest('.prod-edit-btn');
    const recvBtn    = e.target.closest('.prod-recv-btn');
    const delBtn     = e.target.closest('.prod-del-btn');
    const inlineCell = e.target.closest('.prod-inline-cell');
    if (editBtn)    loadProductForEdit(editBtn.dataset.code);
    if (recvBtn)    openReceiveModal(recvBtn.dataset.code, recvBtn.dataset.name, recvBtn.dataset.barcode);
    if (delBtn)     confirmDeleteProduct(delBtn.dataset.code, delBtn.dataset.name);
    if (inlineCell && !inlineCell.querySelector('input')) activateInlineEdit(inlineCell);
  });
}

function buildCatalogTable(rows) {
  if (catalogTable) { catalogTable.destroy(); catalogTable = null; }

  const tbody = document.querySelector('#prod-catalog-table tbody');
  if (!tbody) return;

  tbody.innerHTML = rows.map(r => {
    const code    = escAttr(r.ProductCode);
    const barcode = r.BarCode || r.Barcode || '';
    const price   = parseFloat(r.Price) || 0;
    const cost    = parseFloat(r.Cost)  || 0;
    return `<tr>
    <td>${escHtml(r.ProductCode)}</td>
    <td class="prod-inline-cell" data-code="${code}" data-field="name"    data-raw="${escAttr(r.ProductName)}" title="Clic para editar" style="cursor:pointer">${escHtml(r.ProductName)}</td>
    <td>${escHtml(r.Department)}</td>
    <td>${escHtml(r.Category)}</td>
    <td class="prod-inline-cell text-end" data-code="${code}" data-field="price"   data-raw="${price}" title="Clic para editar" style="cursor:pointer">${fmt$(price)}</td>
    <td class="prod-inline-cell text-end" data-code="${code}" data-field="cost"    data-raw="${cost}"  title="Clic para editar" style="cursor:pointer">${fmt$(cost)}</td>
    <td class="text-end">${fmtNum(r.CurrentStock)}</td>
    <td class="prod-inline-cell" data-code="${code}" data-field="barcode" data-raw="${escAttr(barcode)}" title="Clic para editar" style="cursor:pointer"><code>${escHtml(barcode)}</code></td>
    <td class="text-center">
      <button class="btn btn-sm btn-outline-success prod-recv-btn" data-code="${code}" data-name="${escAttr(r.ProductName)}" data-barcode="${escAttr(barcode)}" title="Pre-Orden / Recibir"><i class="fas fa-clipboard-list"></i></button>
    </td>
  </tr>`;
  }).join('');

  try {
    catalogTable = new DataTable('#prod-catalog-table', {
      pageLength: 15,
      order: [[1, 'asc']],
      dom: 'Bfrtip',
      buttons: ['excel', 'print', 'colvis'],
    });
  } catch (e) {
    console.warn('[products.js] DataTable buttons init failed, retrying without buttons:', e);
    try {
      catalogTable = new DataTable('#prod-catalog-table', { pageLength: 15, order: [[1, 'asc']] });
    } catch (e2) { console.error('[products.js] DataTable init failed:', e2); }
  }
}

// ── Search tab ─────────────────────────────────────────────────────────────────
function bindSearchEvents() {
  el('prod-search-btn')?.addEventListener('click', doProductSearch);
  el('prod-search-input')?.addEventListener('keypress', e => { if (e.key === 'Enter') doProductSearch(); });
}

async function doProductSearch() {
  const query  = (el('prod-search-input')?.value || '').trim();
  const result = el('prod-search-result');
  if (!query || !result) return;

  result.classList.add('d-none');
  result.innerHTML = '';
  showLoader();
  try {
    // Try barcode first
    let prod = null;
    if (/^\d{8,}$/.test(query)) {
      const bcData = await fetchData('InfoBarCode', { barcode: query });
      if (bcData && bcData !== 'NoMatch' && bcData.ProductCode) {
        const detail = await fetchData('ProductInfo', { Referencia: bcData.ProductCode });
        if (detail) prod = detail;
      }
    }
    if (!prod) {
      const detail = await fetchData('ProductInfo', { Referencia: query });
      if (detail && detail.Description) prod = detail;
    }

    if (!prod) {
      result.innerHTML = `<div class="alert alert-warning"><i class="fas fa-search me-2"></i>No se encontró producto con ese código o barcode.</div>`;
    } else {
      const special = await fetchData('Especial', { Referencia: query });
      result.innerHTML = buildProductCard(prod, special);
    }
    result.classList.remove('d-none');
  } catch (err) {
    result.innerHTML = `<div class="alert alert-danger">Error buscando producto.</div>`;
    result.classList.remove('d-none');
    console.error('[products.js] search:', err);
  } finally {
    hideLoader();
  }
}

function buildProductCard(p, special) {
  const sp = Array.isArray(special) ? special[0] : (special && special.SpecialPrice ? special : null);
  const margin = p.Price > 0 && p.Cost > 0 ? ((p.Price - p.Cost) / p.Price * 100).toFixed(1) : '—';
  return `<div class="card rm-card">
    <div class="card-body">
      <h5 class="fw-bold mb-1">${escHtml(p.Description)}</h5>
      <div class="text-muted small mb-3">${escHtml(p.Department)} / ${escHtml(p.Category)}</div>
      <div class="row g-3">
        <div class="col-6 col-md-3"><div class="rm-kpi-card"><div class="rm-kpi-label">Precio</div><div class="rm-kpi-value">${fmt$(p.Price)}</div></div></div>
        <div class="col-6 col-md-3"><div class="rm-kpi-card"><div class="rm-kpi-label">Costo</div><div class="rm-kpi-value">${fmt$(p.Cost)}</div></div></div>
        <div class="col-6 col-md-3"><div class="rm-kpi-card"><div class="rm-kpi-label">En Existencia</div><div class="rm-kpi-value">${fmtNum(p.OnHand)}</div></div></div>
        <div class="col-6 col-md-3"><div class="rm-kpi-card"><div class="rm-kpi-label">Margen</div><div class="rm-kpi-value">${margin}%</div></div></div>
      </div>
      ${sp ? `<div class="alert alert-info mt-3 mb-0"><i class="fas fa-tag me-2"></i><strong>Precio Especial: ${fmt$(sp.SpecialPrice)}</strong> — vigente ${sp.DateFrom} al ${sp.DateUntil}</div>` : ''}
      <div class="mt-3 text-muted small">
        Barcode: <code>${escHtml(p.Barcode||'—')}</code> &nbsp;|&nbsp;
        Proveedor: ${escHtml(p.Suplier||'—')} &nbsp;|&nbsp;
        Ubicación: ${escHtml(p.Location||'—')}
      </div>
    </div>
  </div>`;
}

// ── Product CRUD Modal ─────────────────────────────────────────────────────────
function bindModalEvents() {
  el('pm-save-btn')?.addEventListener('click', saveProduct);
}

function openProductModal(code) {
  el('pm-mode').value = code ? 'edit' : 'new';
  el('pm-code').value = code || '';
  el('pm-code').readOnly = !!code;
  el('pm-name').value = '';
  el('pm-price').value = '';
  el('pm-cost').value = '';
  el('pm-stock').value = '0';
  el('pm-barcode').value = '';
  el('pm-active').value = '1';
  el('pm-error')?.classList.add('d-none');
  setText('prod-modal-title', code ? 'Editar Producto' : 'Nuevo Producto');

  loadDeptOptions('pm-dept');
  loadCatOptions('pm-cat');

  new bootstrap.Modal(el('prod-modal')).show();
}

async function loadProductForEdit(code) {
  showLoader();
  try {
    const data = await fetchData('GetProduct', { ItemCode: code });
    const rows = extractApiData(data);
    const p    = rows[0] || data;
    if (!p) return;

    openProductModal(code);
    setTimeout(() => {
      el('pm-name').value    = p.ProductName || '';
      el('pm-price').value   = p.Price || '';
      el('pm-cost').value    = p.Cost  || '';
      el('pm-stock').value   = p.CurrentStock || '0';
      el('pm-barcode').value = p.BarCode || p.Barcode || '';
      el('pm-dept').value    = p.Department || '';
      el('pm-cat').value     = p.Category   || '';
    }, 200);
  } finally {
    hideLoader();
  }
}

async function saveProduct() {
  const mode    = el('pm-mode').value;
  const errorEl = el('pm-error');
  errorEl?.classList.add('d-none');

  const payload = {
    ItemCode:   (el('pm-code')?.value    || '').trim(),
    Name:        el('pm-name')?.value    || '',
    Price:       el('pm-price')?.value   || '',
    Cost:        el('pm-cost')?.value    || '',
    Stock:       el('pm-stock')?.value   || '0',
    Barcode:     el('pm-barcode')?.value || '',
    Department:  el('pm-dept')?.value    || '',
    Category:    el('pm-cat')?.value     || '',
    Active:      el('pm-active')?.value  || '1',
  };

  if (!payload.Name) {
    showError(errorEl, 'El nombre del producto es requerido.');
    return;
  }

  showLoader();
  try {
    const endpoint = mode === 'edit' ? 'UpdateProduct' : 'CreateProduct';
    const res = await postData(endpoint, payload);
    if (res && res.success) {
      bootstrap.Modal.getInstance(el('prod-modal'))?.hide();
      await loadCatalogTable();
    } else {
      showError(errorEl, res?.message || 'Error guardando el producto.');
    }
  } catch (err) {
    showError(errorEl, 'Error de comunicación con el servidor.');
    console.error('[products.js] saveProduct:', err);
  } finally {
    hideLoader();
  }
}

async function confirmDeleteProduct(code, name) {
  const result = await Swal.fire({
    title: '¿Eliminar producto?',
    text: `"${name}" será eliminado permanentemente.`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#ef4444',
    cancelButtonText: 'Cancelar',
    confirmButtonText: 'Eliminar',
  });
  if (!result.isConfirmed) return;

  showLoader();
  try {
    const res = await deleteRecord('DeleteProduct', `ItemCode=${encodeURIComponent(code)}`);
    if (res?.success) {
      await loadCatalogTable();
    } else {
      await Swal.fire('Error', res?.message || 'No se pudo eliminar el producto.', 'error');
    }
  } finally {
    hideLoader();
  }
}

// ── Receive Inventory Modal ────────────────────────────────────────────────────
function bindReceiveEvents() {
  el('btnRecibirInventario')?.addEventListener('click', doReceiveInventory);
  el('recibirProductoModal')?.addEventListener('hidden.bs.modal', () => {
    if (receiveChart) { receiveChart.destroy(); receiveChart = null; }
  });
}

async function openReceiveModal(code, name, barcode) {
  receiveProductCode = code;
  el('nombreProducto').textContent  = name    || '';
  el('codigoProducto').textContent  = code    || '';
  el('barcodeProducto').textContent = barcode || '';
  el('cantidadProducto').value      = '1';

  const movCont = el('movementContainer');
  const movMsg  = el('ProdMovementChartMessage');
  if (movCont) movCont.classList.add('d-none');
  if (movMsg)  movMsg.textContent = 'Cargando movimiento...';

  bootstrap.Modal.getOrCreateInstance(el('recibirProductoModal')).show();

  showLoader();
  try {
    const [movData, unitsData, prodData] = await Promise.all([
      fetchData('ProdMovementChart', { ItemCode: code }),
      fetchData('ProdUnits',         { ItemCode: code }),
      fetchData('GetProduct',        { ItemCode: code }),
    ]);

    const movRows  = Array.isArray(movData)   ? movData   : extractApiData(movData);
    const unitRows = Array.isArray(unitsData) ? unitsData : extractApiData(unitsData);
    const prodRows = extractApiData(prodData);
    const prod     = prodRows[0] || {};
    const stock    = parseFloat(prod.CurrentStock || prod.OnHand || 0);

    renderReceiveChart(movRows);
    renderMonthButtons(movRows);
    renderAnnualSummary(movRows, stock);

    const unitSel = el('unidadesProducto');
    if (unitSel) {
      unitSel.innerHTML = '';
      if (!unitRows.length) {
        unitSel.innerHTML = '<option value="1">Unidad - Each</option>';
      } else {
        unitRows.forEach(u => {
          const opt = document.createElement('option');
          opt.value       = u.UnitID;
          opt.textContent = u.UnitDescription || u.UnitName || u.UnitID;
          unitSel.appendChild(opt);
        });
      }
    }
  } catch (err) {
    if (movMsg) movMsg.textContent = 'Error cargando datos de movimiento.';
    console.error('[products.js] openReceiveModal:', err);
  } finally {
    hideLoader();
  }
}

function renderMonthButtons(rows, prefix = '') {
  const container = el(prefix + 'monthButtonsContainer');
  if (!container) return;
  container.innerHTML = '';
  rows.forEach((r, i) => {
    const raw   = r.MonthName || r.MonthYear || String(r.Month || i + 1);
    const label = raw.length > 5 ? raw.slice(0, 3) : raw;
    const btn   = document.createElement('button');
    btn.type      = 'button';
    btn.textContent = label;
    btn.className = 'btn btn-sm rounded-circle ' + (i === 0 ? 'btn-primary' : 'btn-secondary');
    btn.style.cssText = 'width:46px;height:46px;font-size:.72rem;padding:0;flex-shrink:0';
    btn.addEventListener('click', () => {
      container.querySelectorAll('button').forEach(b => {
        b.classList.replace('btn-primary', 'btn-secondary');
      });
      btn.classList.replace('btn-secondary', 'btn-primary');
      populateMonthlySummary(r, prefix);
    });
    container.appendChild(btn);
  });
  if (rows.length) populateMonthlySummary(rows[0], prefix);
}

function populateMonthlySummary(r, prefix = '') {
  const sales   = parseFloat(r.GrossSalesValue  || r.TotalSales         || 0);
  const cost    = parseFloat(r.TotalCost        || r.NetCostValue        || 0);
  const profit  = parseFloat(r.TotalProfit      || 0) || (sales - cost);
  const recps   = parseFloat(r.ReceiptsQuantity || 0);
  const sold    = parseFloat(r.NetSalesQuantity || r.TotalQuantitySold   || 0);
  setText(prefix + 'summary-ventas',   fmt$(sales));
  setText(prefix + 'summary-costo',    fmt$(cost));
  setText(prefix + 'summary-profit',   fmt$(profit));
  setText(prefix + 'summary-recibos',  String(Math.round(recps)));
  setText(prefix + 'summary-vendidos', String(Math.round(sold)));
}

function renderAnnualSummary(rows, currentStock, prefix = '') {
  const MONTHS_COVER = 1.35;

  const totalUnits  = rows.reduce((s, r) => s + parseFloat(r.NetSalesQuantity  || r.TotalQuantitySold || 0), 0);
  const totalSales  = rows.reduce((s, r) => s + parseFloat(r.GrossSalesValue   || r.TotalSales        || 0), 0);
  const totalCost   = rows.reduce((s, r) => s + parseFloat(r.TotalCost         || r.NetCostValue      || 0), 0);
  const totalProfit = rows.reduce((s, r) => s + parseFloat(r.TotalProfit       || 0), 0) || (totalSales - totalCost);

  setText(prefix + 'annual-total-sales-units', String(Math.round(totalUnits)));
  setText(prefix + 'annual-gross-sales-value', fmt$(totalSales));
  setText(prefix + 'annual-total-costs',       fmt$(totalCost));
  setText(prefix + 'annual-total-profit',      fmt$(Math.abs(totalProfit)));

  const last3    = rows.slice(-3);
  const avgMonth = last3.length
    ? last3.reduce((s, r) => s + parseFloat(r.NetSalesQuantity || r.TotalQuantitySold || 0), 0) / last3.length
    : 0;
  const avgWeek  = avgMonth / 4.33;

  setText(prefix + 'demand-monthly',    avgMonth.toFixed(2));
  setText(prefix + 'demand-weekly',     avgWeek.toFixed(2));
  setText(prefix + 'current-inventory', String(Math.round(currentStock)));

  const suggested = Math.max(0, Math.ceil(avgMonth * MONTHS_COVER - currentStock));
  setText(prefix + 'suggested-order-quantity', String(suggested));

  const excessEl = el(prefix + 'suggested-order-excess-message');
  if (excessEl) {
    if (suggested === 0 && avgMonth > 0 && currentStock > 0) {
      const months = (currentStock / avgMonth).toFixed(0);
      excessEl.textContent = `*producto en exceso para cubrir ${months} meses`;
      excessEl.style.display = '';
    } else {
      excessEl.style.display = 'none';
    }
  }
}

function renderReceiveChart(rows) {
  const canvas = el('ProdMovementChart');
  const movCont = el('movementContainer');
  const movMsg  = el('ProdMovementChartMessage');
  if (!canvas) return;
  if (receiveChart) { receiveChart.destroy(); receiveChart = null; }

  if (!rows.length) {
    if (movMsg)  movMsg.textContent = 'No hay datos de movimiento para este producto.';
    if (movCont) movCont.classList.add('d-none');
    return;
  }
  if (movMsg)  movMsg.textContent = '';
  if (movCont) movCont.classList.remove('d-none');

  const labels = rows.map(r => r.MonthName || r.MonthYear || r.Month || '');
  const sales  = rows.map(r => parseFloat(r.NetSalesQuantity  || r.TotalQuantitySold || 0));
  const recps  = rows.map(r => parseFloat(r.ReceiptsQuantity  || 0));

  receiveChart = new Chart(canvas, {
    type: 'bar',
    data: {
      labels,
      datasets: [
        { label: 'Vendidos',  data: sales, backgroundColor: alpha(PALETTE.primary, .8), borderRadius: 3 },
        { label: 'Recibidos', data: recps, backgroundColor: alpha(PALETTE.success, .7), borderRadius: 3 },
      ],
    },
    options: baseOpts({ scales: { x: { grid: { display: false } } } }),
  });
}

async function doReceiveInventory() {
  const qty    = parseInt(el('cantidadProducto')?.value || '1', 10);
  const userId = el('rm-user-id')?.textContent?.trim() || '';
  if (!receiveProductCode || qty < 1) return;

  showLoader();
  try {
    const res = await fetchData('RecReceiveInventory', {
      ItemCode:         receiveProductCode,
      QuantityReceived: qty,
      UserID:           userId,
    });
    if (res?.success) {
      bootstrap.Modal.getInstance(el('recibirProductoModal'))?.hide();
      await Swal.fire({ title: 'Exito', text: 'Producto recibido correctamente', icon: 'success',
        timer: 2500, timerProgressBar: true, showConfirmButton: false });
      await loadCatalogTable();
    } else {
      await Swal.fire('Error', res?.message || 'No se pudo recibir el inventario.', 'error');
    }
  } catch (err) {
    console.error('[products.js] doReceiveInventory:', err);
  } finally {
    hideLoader();
  }
}

// ── Inline cell editing ────────────────────────────────────────────────────────
function activateInlineEdit(td) {
  const code     = td.dataset.code;
  const field    = td.dataset.field;
  const rawValue = td.dataset.raw;
  const origHTML = td.innerHTML;

  const isNumeric = field === 'price' || field === 'cost';

  const input = document.createElement('input');
  input.type      = isNumeric ? 'number' : 'text';
  if (isNumeric) input.step = '0.01';
  input.value     = rawValue;
  input.className = 'form-control form-control-sm';
  input.style.cssText = 'display:inline-block;width:calc(100% - 64px);min-width:70px';

  const cancelBtn = document.createElement('button');
  cancelBtn.type      = 'button';
  cancelBtn.innerHTML = '<i class="fas fa-times"></i>';
  cancelBtn.className = 'btn btn-sm btn-outline-danger ms-1';
  cancelBtn.style.cssText = 'padding:2px 6px';

  const saveBtn = document.createElement('button');
  saveBtn.type      = 'button';
  saveBtn.innerHTML = '<i class="fas fa-check"></i>';
  saveBtn.className = 'btn btn-sm btn-outline-success ms-1';
  saveBtn.style.cssText = 'padding:2px 6px';

  td.innerHTML = '';
  td.appendChild(input);
  td.appendChild(cancelBtn);
  td.appendChild(saveBtn);
  input.focus();
  input.select();

  const restore = () => { td.innerHTML = origHTML; };

  cancelBtn.addEventListener('click', e => { e.stopPropagation(); restore(); });

  const doSave = () => {
    const newVal = input.value.trim();
    if (newVal === String(rawValue)) { restore(); return; }
    saveInlineField(code, field, newVal, td, origHTML);
  };
  saveBtn.addEventListener('click', e => { e.stopPropagation(); doSave(); });
  input.addEventListener('keydown', e => {
    if (e.key === 'Enter') { e.stopPropagation(); doSave(); }
    if (e.key === 'Escape') { e.stopPropagation(); restore(); }
  });
}

async function saveInlineField(code, field, newVal, td, origHTML) {
  const MAP = {
    name:    { endpoint: 'ProdNameChange',    param: 'NewName'    },
    barcode: { endpoint: 'ProdBarcodeChange', param: 'NewBarcode' },
    price:   { endpoint: 'ProdPriceChange',   param: 'NewPrice'   },
    cost:    { endpoint: 'ProdCostChange',     param: 'NewCost'    },
  };
  const cfg = MAP[field];
  if (!cfg) { td.innerHTML = origHTML; return; }

  const userId = el('rm-user-id')?.textContent?.trim() || '';
  td.innerHTML = '<span class="text-muted" style="font-size:.8rem">Guardando…</span>';

  try {
    const res = await fetchData(cfg.endpoint, { ItemCode: code, [cfg.param]: newVal, UserID: userId });
    if (res?.success) {
      const isNumeric = field === 'price' || field === 'cost';
      let display;
      if (isNumeric)       display = fmt$(parseFloat(newVal));
      else if (field === 'barcode') display = `<code>${escHtml(newVal)}</code>`;
      else                 display = escHtml(newVal);
      td.innerHTML   = display;
      td.dataset.raw = newVal;
    } else {
      td.innerHTML = origHTML;
      await Swal.fire('Error', res?.message || 'No se pudo guardar el cambio.', 'error');
    }
  } catch (err) {
    td.innerHTML = origHTML;
    console.error('[products.js] saveInlineField:', err);
    await Swal.fire('Error', 'Error de comunicación con el servidor.', 'error');
  }
}

// ── POST helpers ──────────────────────────────────────────────────────────────
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
function truncate(s,n) { return s && s.length > n ? s.slice(0,n)+'…' : (s||''); }
function showError(el, msg) { if (el) { el.textContent = msg; el.classList.remove('d-none'); } }
function alpha(hex,a)  {
  const r=parseInt(hex.slice(1,3),16),g=parseInt(hex.slice(3,5),16),b=parseInt(hex.slice(5,7),16);
  return `rgba(${r},${g},${b},${a})`;
}
function baseOpts(extra={}) {
  return { responsive:true, maintainAspectRatio:false, plugins:{ legend:{ labels:{ boxWidth:12, font:{ size:11 } } } }, ...extra };
}
