import { fmt$, fmtPct, fmtNum, extractApiData, showLoader, hideLoader, PALETTE, CHART_COLORS } from './app.js';

let topChart           = null;
let bottomChart        = null;
let movChart           = null;
let modalMovementChart = null;
let catalogTable       = null;
let receiveProductCode = '';
let receiveActionMode  = 'receive';
let receiveUnitId      = '1';
let movementProduct    = null;
let movementSearchTimer = null;
let movementSelectedProduct = null;
let productSearchTimer = null;
let productSearchSelectedProduct = null;

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
  el('prod-movement-code')?.addEventListener('input', handleMovementSearchInput);
  el('prod-movement-code')?.addEventListener('focus', handleMovementSearchInput);
  el('prod-movement-code')?.addEventListener('keypress', e => { if (e.key === 'Enter') loadMovementChart(); });
  document.addEventListener('click', e => {
    if (!e.target.closest('#prod-movement-code') && !e.target.closest('#prod-movement-suggestions')) {
      hideMovementSuggestions();
    }
  });
  el('mv-receive-btn')?.addEventListener('click', () => {
    if (!movementProduct?.code) return;
    const suggested = parseInt(el('mv-suggested-order-quantity')?.textContent || '1', 10) || 1;
    openReceiveModal(movementProduct.code, movementProduct.name, movementProduct.barcode, suggested);
  });
}

function handleMovementSearchInput() {
  const query = (el('prod-movement-code')?.value || '').trim();
  movementSelectedProduct = null;
  clearTimeout(movementSearchTimer);

  if (query.length < 2) {
    hideMovementSuggestions();
    return;
  }

  movementSearchTimer = setTimeout(() => searchMovementProducts(query), 250);
}

async function searchMovementProducts(query) {
  try {
    const data = await fetchData('ProdDescSearch', { Description: query });
    const rows = extractApiData(data).map(normalizeSearchProduct).filter(p => p.code);
    renderMovementSuggestions(rows.slice(0, 12));
  } catch (err) {
    console.error('[products.js] ProdDescSearch:', err);
    hideMovementSuggestions();
  }
}

function renderMovementSuggestions(rows) {
  const box = el('prod-movement-suggestions');
  if (!box) return;

  if (!rows.length) {
    box.innerHTML = '<div class="list-group-item text-muted small">No se encontraron productos</div>';
    box.classList.remove('d-none');
    return;
  }

  box.innerHTML = rows.map((p, index) => `
    <button type="button" class="list-group-item list-group-item-action text-start" data-index="${index}">
      <div class="fw-semibold">${escHtml(p.name || p.code)}</div>
      <div class="small text-muted">Código: ${escHtml(p.code)}${p.barcode ? ` · Barcode: ${escHtml(p.barcode)}` : ''}</div>
    </button>
  `).join('');

  box.querySelectorAll('[data-index]').forEach(button => {
    const product = rows[Number(button.dataset.index)];
    button.addEventListener('click', () => selectMovementProduct(product));
  });

  box.classList.remove('d-none');
}

function selectMovementProduct(product) {
  movementSelectedProduct = product;
  const input = el('prod-movement-code');
  if (input) input.value = product.name ? `${product.name} (${product.code})` : product.code;
  hideMovementSuggestions();
}

function hideMovementSuggestions() {
  el('prod-movement-suggestions')?.classList.add('d-none');
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
    const resolved = movementSelectedProduct || await resolveMovementProduct(query);
    const code     = resolved?.code || null;

    if (!code) {
      if (msgEl) msgEl.textContent = 'Producto no encontrado.';
      if (wrap)  wrap.classList.add('d-none');
      return;
    }

    const movData = await fetchData('ProdMovementChart', { ItemCode: code });
    const movRows  = Array.isArray(movData) ? movData : extractApiData(movData);
    const name     = resolved.name || code;
    const barcode  = resolved.barcode || '';

    if (!movRows.length) {
      if (msgEl) msgEl.textContent = 'No hay datos de movimiento para este producto.';
      if (wrap)  wrap.classList.add('d-none');
      return;
    }

    if (msgEl) msgEl.textContent = '';
    if (wrap)  wrap.classList.remove('d-none');
    renderMovChart(movRows);

    setText('mv-product-name', name);
    renderMonthButtons(movRows, 'mv-');
    renderModalAnnualSummary(movRows, 'mv-');
    movementProduct = { code, name, barcode };
    if (detail) detail.classList.remove('d-none');

  } catch (err) {
    if (msgEl) msgEl.textContent = 'Error cargando datos.';
    console.error('[products.js] movement:', err);
  } finally {
    hideLoader();
  }
}

async function resolveMovementProduct(query) {
  const cleanQuery = String(query || '').trim();
  const data = await fetchData('ProdDescSearch', { Description: cleanQuery });
  const rows = extractApiData(data).map(normalizeSearchProduct).filter(p => p.code);
  if (!rows.length) return null;

  const exact = rows.find(p =>
    p.code.toLowerCase() === cleanQuery.toLowerCase() ||
    p.barcode.toLowerCase() === cleanQuery.toLowerCase()
  );

  return exact || rows[0];
}

function normalizeSearchProduct(row) {
  if (!row || typeof row !== 'object') return {};
  const code = row.ProductCode || row.ItemCode || row.Code || row.ItemID || row.ID || '';
  const barcode = row.BarCode || row.Barcode || row.BCde13 || row.BarCode1 || '';
  const name = row.ProductName || row.Description || row.Name || row.LongDesc || '';
  const stock = row.CurrentStock || row.OnHand || row.Stock || 0;
  return {
    code: String(code || '').trim(),
    barcode: String(barcode || '').trim(),
    name: String(name || '').trim(),
    stock,
  };
}

function renderMovChart(rows) {
  const canvas = el('prod-movement-chart');
  if (!canvas) return;
  if (movChart) { movChart.destroy(); movChart = null; }

  const labels = rows.map(r => r.MonthName || r.MonthYear || r.Month || '');
  const sales = rows.map(r => parseFloat(r.NetSalesQuantity || r.TotalQuantitySold || 0));
  const receipts = rows.map(r => parseFloat(r.ReceiptsQuantity || 0));

  movChart = new Chart(canvas, {
    type: 'bar',
    data: {
      labels,
      datasets: [
        { label: 'Ventas', data: sales, backgroundColor: alpha(PALETTE.primary, .8) },
        { label: 'Recibos', data: receipts, backgroundColor: alpha(PALETTE.danger, .8) },
      ],
    },
    options: baseOpts({
      scales: { x: { grid: { display: false } } },
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
    renderCatalogEmptyState('Usa los campos de busqueda y presiona Buscar para cargar productos.');
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

async function loadCatalogTable(options = {}) {
  const showBusy = options?.silent !== true;
  const dept = el('prod-filter-dept')?.value || '';
  const cat  = el('prod-filter-cat')?.value  || '';
  const code = (el('prod-filter-code')?.value || '').trim();
  const name = (el('prod-filter-name')?.value || '').trim();

  const params = {};
  if (dept) params.Department = dept;
  if (cat)  params.Category   = cat;
  if (code) params.ItemCode   = code;

  if (showBusy) showLoader();
  try {
    const data = await fetchData('GetAllProducts', params);
    let rows = extractApiData(data);
    if (name) {
      const q = name.toLowerCase();
      rows = rows.filter(r => (r.ProductName||'').toLowerCase().includes(q));
    }
    buildCatalogTable(rows);
  } finally {
    if (showBusy) hideLoader();
  }
}

function bindCatalogEvents() {
  el('prod-filter-apply')?.addEventListener('click', loadCatalogTable);
  ['prod-filter-code','prod-filter-name'].forEach(id => {
    el(id)?.addEventListener('keypress', e => { if (e.key === 'Enter') loadCatalogTable(); });
  });
  el('prod-filter-reset')?.addEventListener('click', () => {
    ['prod-filter-dept','prod-filter-cat','prod-filter-code','prod-filter-name'].forEach(id => {
      const e = el(id);
      if (e) e.value = '';
    });
    renderCatalogEmptyState('Usa los campos de busqueda y presiona Buscar para cargar productos.');
  });
  el('prod-add-btn')?.addEventListener('click', () => openProductModal(null));

  // Delegated handler — on a stable ancestor so it survives DataTable rebuilds
  el('prod-tab-catalog')?.addEventListener('click', e => {
    const recvBtn    = e.target.closest('.prod-recv-btn');
    const tagBtn     = e.target.closest('.prod-tag-btn');
    const preBtn     = e.target.closest('.prod-preorder-btn');
    const inlineCell = e.target.closest('.prod-inline-cell');
    if (recvBtn)    openReceiveModal(recvBtn.dataset.code, recvBtn.dataset.name, recvBtn.dataset.barcode);
    if (tagBtn)     printProductLabel(tagBtn.dataset.code);
    if (preBtn)     openPreOrderModal(preBtn.dataset.code, preBtn.dataset.name, preBtn.dataset.barcode);
    if (inlineCell && !inlineCell.querySelector('input')) activateInlineEdit(inlineCell);
  });
}

function renderCatalogEmptyState(message) {
  if (catalogTable) { catalogTable.destroy(); catalogTable = null; }
  const tbody = document.querySelector('#prod-catalog-table tbody');
  if (!tbody) return;
  tbody.innerHTML = `<tr><td colspan="9" class="text-center text-muted py-4">${escHtml(message)}</td></tr>`;
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
    const name    = escAttr(r.ProductName);
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
      <div class="btn-group btn-group-sm" role="group" aria-label="Acciones del producto">
        <button type="button" class="btn btn-outline-warning prod-preorder-btn" data-code="${code}" data-name="${name}" data-barcode="${escAttr(barcode)}" title="Pre-Orden">
          Pre-Orden
        </button>
        <button type="button" class="btn btn-outline-success prod-recv-btn" data-code="${code}" data-name="${name}" data-barcode="${escAttr(barcode)}" title="Recibir Inventario">
          <i class="fas fa-square-plus"></i>
        </button>
        <button type="button" class="btn btn-outline-secondary prod-tag-btn" data-code="${code}" title="Imprimir etiqueta">
          <i class="fas fa-tag"></i>
        </button>
      </div>
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

function openMovementFromCatalog(code) {
  const performanceTab = document.querySelector('[href="#prod-tab-performance"]');
  if (performanceTab && window.bootstrap?.Tab) {
    bootstrap.Tab.getOrCreateInstance(performanceTab).show();
  }

  const input = el('prod-movement-code');
  if (input) input.value = code || '';
  loadMovementChart({ showPreOrder: false });
}

async function printProductLabel(code) {
  if (!code) return;
  showLoader();
  try {
    const res = await fetchData('ProdLabelPrint', { ItemCode: code });
    if (res?.success) {
      await Swal.fire({
        title: 'Exito',
        text: 'Etiquetas enviadas a imprimir correctamente',
        icon: 'success',
        timer: 3000,
        timerProgressBar: true,
      });
    } else {
      await Swal.fire('Error', res?.message || 'No se pudo imprimir la etiqueta.', 'error');
    }
  } catch (err) {
    console.error('[products.js] printProductLabel:', err);
    await Swal.fire('Error', 'No se pudo imprimir la etiqueta.', 'error');
  } finally {
    hideLoader();
  }
}

// ── Search tab ─────────────────────────────────────────────────────────────────
function bindSearchEvents() {
  el('prod-search-btn')?.addEventListener('click', () => doProductSearch());
  el('prod-search-input')?.addEventListener('input', handleProductSearchInput);
  el('prod-search-input')?.addEventListener('focus', handleProductSearchInput);
  el('prod-search-input')?.addEventListener('keypress', e => { if (e.key === 'Enter') doProductSearch(); });
  document.addEventListener('click', e => {
    if (!e.target.closest('#prod-search-input') && !e.target.closest('#prod-search-suggestions')) {
      hideProductSearchSuggestions();
    }
  });
}

function handleProductSearchInput() {
  const query = (el('prod-search-input')?.value || '').trim();
  productSearchSelectedProduct = null;
  clearTimeout(productSearchTimer);

  if (query.length < 2) {
    hideProductSearchSuggestions();
    return;
  }

  productSearchTimer = setTimeout(() => searchProductsForDetail(query), 250);
}

async function searchProductsForDetail(query) {
  try {
    const data = await fetchData('ProdDescSearch', { Description: query });
    const rows = extractApiData(data).map(normalizeSearchProduct).filter(p => p.code);
    renderProductSearchSuggestions(rows.slice(0, 12));
  } catch (err) {
    console.error('[products.js] search suggestions:', err);
    hideProductSearchSuggestions();
  }
}

function renderProductSearchSuggestions(rows) {
  const box = el('prod-search-suggestions');
  if (!box) return;

  if (!rows.length) {
    box.innerHTML = '<div class="list-group-item text-muted small">No se encontraron productos</div>';
    box.classList.remove('d-none');
    return;
  }

  box.innerHTML = rows.map((p, index) => `
    <button type="button" class="list-group-item list-group-item-action text-start" data-index="${index}">
      <div class="fw-semibold">${escHtml(p.name || p.code)}</div>
      <div class="small text-muted">Código: ${escHtml(p.code)}${p.barcode ? ` · Barcode: ${escHtml(p.barcode)}` : ''}</div>
    </button>
  `).join('');

  box.querySelectorAll('[data-index]').forEach(button => {
    const product = rows[Number(button.dataset.index)];
    button.addEventListener('click', () => selectProductSearchResult(product));
  });

  box.classList.remove('d-none');
}

function selectProductSearchResult(product) {
  productSearchSelectedProduct = product;
  const input = el('prod-search-input');
  if (input) input.value = product.name ? `${product.name} (${product.code})` : product.code;
  hideProductSearchSuggestions();
  doProductSearch(product);
}

function hideProductSearchSuggestions() {
  el('prod-search-suggestions')?.classList.add('d-none');
}

async function doProductSearch(selectedProduct = null) {
  const query  = (el('prod-search-input')?.value || '').trim();
  const result = el('prod-search-result');
  if (!query || !result) return;

  result.classList.add('d-none');
  result.innerHTML = '';
  hideProductSearchSuggestions();
  showLoader();
  try {
    const resolved = selectedProduct || productSearchSelectedProduct || await resolveMovementProduct(query);
    const detail = await fetchSearchProductDetail(resolved, query);
    const prod = getFirstApiRow(detail);

    if (!prod) {
      result.innerHTML = `<div class="alert alert-warning"><i class="fas fa-search me-2"></i>No se encontró producto con ese código, barcode o nombre.</div>`;
    } else {
      const reference = resolved?.barcode || resolved?.code || query;
      const special = await fetchData('Especial', { Referencia: reference });
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

async function fetchSearchProductDetail(product, query) {
  const attempts = [];

  if (product?.barcode) attempts.push({ barcode: product.barcode });
  if (product?.code) attempts.push({ itemCode: product.code });
  if (/^\d{8,}$/.test(query)) attempts.push({ barcode: query });
  attempts.push({ itemCode: query });

  for (const params of attempts) {
    const detail = await fetchProductInfo(params);
    const row = getFirstApiRow(detail);
    if (row && (row.Description || row.ProductName || row.Name || row.Price !== undefined || row.Cost !== undefined)) {
      return detail;
    }
  }

  return null;
}

function getFirstApiRow(data) {
  const rows = extractApiData(data);
  return rows.length && rows[0] && typeof rows[0] === 'object' ? rows[0] : null;
}

function buildProductCard(p, special) {
  const sp = Array.isArray(special) ? special[0] : (special && special.SpecialPrice ? special : null);
  const name = p.Description || p.ProductName || p.Name || '';
  const dept = p.Department || p.DepartmentName || '—';
  const cat = p.Category || p.CategoryName || '—';
  const price = parseFloat(p.Price || p.SalesPrice || 0);
  const cost = parseFloat(p.Cost || p.CurrentCost || p.LastCost || 0);
  const stock = p.OnHand ?? p.CurrentStock ?? p.Stock ?? 0;
  const code = p.ItemCode || p.ProductCode || p.Code || '';
  const barcode = p.Barcode || p.BarCode || p.BCde13 || '';
  const supplier = p.Suplier || p.Supplier || p.Provider || '—';
  const location = p.Location || p.Loc || '—';
  const margin = price > 0 && cost > 0 ? ((price - cost) / price * 100).toFixed(1) : '—';
  return `<div class="card rm-card">
    <div class="card-body">
      <h5 class="fw-bold mb-1">${escHtml(name)}</h5>
      <div class="text-muted small mb-3">${escHtml(dept)} / ${escHtml(cat)}</div>
      <div class="row g-3">
        <div class="col-6 col-md-3"><div class="rm-kpi-card"><div class="rm-kpi-label">Precio</div><div class="rm-kpi-value">${fmt$(price)}</div></div></div>
        <div class="col-6 col-md-3"><div class="rm-kpi-card"><div class="rm-kpi-label">Costo</div><div class="rm-kpi-value">${fmt$(cost)}</div></div></div>
        <div class="col-6 col-md-3"><div class="rm-kpi-card"><div class="rm-kpi-label">En Existencia</div><div class="rm-kpi-value">${fmtNum(stock)}</div></div></div>
        <div class="col-6 col-md-3"><div class="rm-kpi-card"><div class="rm-kpi-label">Margen</div><div class="rm-kpi-value">${margin}%</div></div></div>
      </div>
      ${sp ? `<div class="alert alert-info mt-3 mb-0"><i class="fas fa-tag me-2"></i><strong>Precio Especial: ${fmt$(sp.SpecialPrice)}</strong> — vigente ${sp.DateFrom} al ${sp.DateUntil}</div>` : ''}
      <div class="mt-3 text-muted small">
        Código: <code>${escHtml(code || '—')}</code> &nbsp;|&nbsp;
        Barcode: <code>${escHtml(barcode || '—')}</code> &nbsp;|&nbsp;
        Proveedor: ${escHtml(supplier)} &nbsp;|&nbsp;
        Ubicación: ${escHtml(location)}
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
    const data = await fetchProductInfo({ itemCode: code });
    const p    = normalizeProductInfo(data);
    if (!p) return;

    openProductModal(code);
    setTimeout(() => {
      el('pm-name').value    = p.name || '';
      el('pm-price').value   = p.price || '';
      el('pm-cost').value    = p.cost  || '';
      el('pm-stock').value   = p.onHand || '0';
      el('pm-barcode').value = p.barcode || '';
      el('pm-dept').value    = p.department || '';
      el('pm-cat').value     = p.category   || '';
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

// ── Receive Inventory Modal ────────────────────────────────────────────────────
function bindReceiveEvents() {
  el('btnRecibirInventario')?.addEventListener('click', doInventoryModalAction);
}

async function openReceiveModal(code, name, barcode, defaultQty = 1) {
  receiveActionMode = 'receive';
  receiveProductCode = code;
  receiveUnitId = '1';
  configureInventoryModalShell({
    title: 'Recibir Producto',
    productCode: code,
    productName: name,
    barcode,
    quantity: defaultQty,
    showMovement: false,
    showUnits: false,
    buttonText: 'RECIBIR',
  });

  bootstrap.Modal.getOrCreateInstance(el('recibirProductoModal')).show();
}

async function openPreOrderModal(code, name, barcode) {
  receiveActionMode = 'preorder';
  receiveProductCode = code;
  receiveUnitId = '1';

  configureInventoryModalShell({
    title: 'Pre-Orden Change',
    productCode: code,
    productName: name,
    barcode,
    quantity: 1,
    showMovement: true,
    showUnits: true,
    buttonText: 'GUARDAR PRE-ORDEN',
  });

  bootstrap.Modal.getOrCreateInstance(el('recibirProductoModal')).show();

  showLoader();
  try {
    await Promise.all([
      loadModalUnits(code),
      loadModalMovementChart(code),
    ]);
  } finally {
    hideLoader();
  }
}

function configureInventoryModalShell(options) {
  el('nombreProducto').textContent  = options.productName || '';
  el('codigoProducto').textContent  = options.productCode || '';
  el('barcodeProducto').textContent = options.barcode || '';
  el('cantidadProducto').value      = String(Math.max(options.quantity || 1, 1));
  setText('recibirProductoLabel', options.title);

  const movementContainer = el('movementContainer');
  const movementCanvas = el('ProdMovementChart');
  const movementMessage = el('ProdMovementChartMessage');
  const unitsGroup = el('unidadesProductoGroup');
  const receiveButton = el('btnRecibirInventario');

  movementContainer?.classList.toggle('d-none', !options.showMovement);
  movementCanvas?.classList.toggle('d-none', !options.showMovement);
  movementMessage?.classList.add('d-none');
  unitsGroup?.classList.toggle('d-none', !options.showUnits);

  if (receiveButton) receiveButton.textContent = options.buttonText;
  if (modalMovementChart && !options.showMovement) {
    modalMovementChart.destroy();
    modalMovementChart = null;
  }
}

async function loadModalUnits(code) {
  const unitsData = await fetchData('ProdUnits', { ItemCode: code });
  const unitRows = Array.isArray(unitsData) ? unitsData : extractApiData(unitsData);

  const unitSel = el('unidadesProducto');
  if (!unitSel) return;
  unitSel.innerHTML = '';

  if (!unitRows.length) {
    unitSel.innerHTML = '<option value="1">Unidad - Each</option>';
    receiveUnitId = '1';
    return;
  }

  unitRows.forEach(u => {
    const opt = document.createElement('option');
    opt.value = u.UnitID || '1';
    opt.textContent = u.UnitDescription || u.UnitName || u.UnitID || 'Unidad - Each';
    unitSel.appendChild(opt);
  });
  receiveUnitId = unitSel.value || '1';
}

async function loadModalMovementChart(code) {
  const rows = extractApiData(await fetchData('ProdMovementChart', { ItemCode: code }));
  const movementContainer = el('movementContainer');
  const movementCanvas = el('ProdMovementChart');
  const movementMessage = el('ProdMovementChartMessage');

  if (!rows.length) {
    movementContainer?.classList.add('d-none');
    movementCanvas?.classList.add('d-none');
    if (movementMessage) {
      movementMessage.classList.remove('d-none');
      movementMessage.textContent = 'No hay datos de movimiento para este producto.';
    }
    renderModalAnnualSummary([]);
    return;
  }

  movementContainer?.classList.remove('d-none');
  movementCanvas?.classList.remove('d-none');
  movementMessage?.classList.add('d-none');

  const labels = rows.map(r => r.MonthName || r.MonthYear || r.Month || '');
  const sales = rows.map(r => parseFloat(r.NetSalesQuantity || r.TotalQuantitySold || 0));
  const receipts = rows.map(r => parseFloat(r.ReceiptsQuantity || 0));
  const canvas = el('ProdMovementChart');
  if (!canvas) return;

  const chartWrap = el('ProdMovementChartWrap');
  if (chartWrap) {
    chartWrap.style.height = '260px';
    chartWrap.style.maxHeight = '260px';
    chartWrap.style.position = 'relative';
    chartWrap.style.overflow = 'hidden';
  }
  canvas.style.height = '260px';
  canvas.style.maxHeight = '260px';

  if (modalMovementChart) modalMovementChart.destroy();
  modalMovementChart = new Chart(canvas, {
    type: 'bar',
    data: {
      labels,
      datasets: [
        { label: 'Ventas', data: sales, backgroundColor: alpha(PALETTE.primary, .8) },
        { label: 'Recibos', data: receipts, backgroundColor: alpha(PALETTE.danger, .8) },
      ],
    },
    options: baseOpts({
      resizeDelay: 150,
      scales: { x: { grid: { display: false } } },
    }),
  });

  renderMonthButtons(rows);
  if (rows.length) populateMonthlySummary(rows[0]);
  renderModalAnnualSummary(rows);
}

function renderModalAnnualSummary(rows, prefix = '') {
  if (!rows.length) {
    setText(prefix + 'annual-total-sales-units', '0');
    setText(prefix + 'annual-gross-sales-value', '$0.00');
    setText(prefix + 'annual-total-costs', '$0.00');
    setText(prefix + 'annual-total-profit', '$0.00');
    setText(prefix + 'demand-weekly', '0.00');
    setText(prefix + 'demand-monthly', '0.00');
    setText(prefix + 'suggested-order-quantity', '0000');
    setText(prefix + 'suggested-order-excess-message', '');
    setText(prefix + 'current-inventory', '0');
    return;
  }

  const totalUnits = rows.reduce((sum, row) => sum + parseFloat(row.NetSalesQuantity || row.TotalQuantitySold || 0), 0);
  const totalSales = rows.reduce((sum, row) => sum + parseFloat(row.GrossSalesValue || row.TotalSales || 0), 0);
  const totalCost = rows.reduce((sum, row) => sum + parseFloat(row.CurrentCost || row.TotalCost || row.NetCostValue || 0), 0);
  const totalProfit = rows.reduce((sum, row) => sum + parseFloat(row.ProfitValue || row.TotalProfit || 0), 0) || (totalSales - totalCost);

  setText(prefix + 'annual-total-sales-units', String(Math.round(totalUnits)));
  setText(prefix + 'annual-gross-sales-value', fmt$(totalSales));
  setText(prefix + 'annual-total-costs', fmt$(totalCost));
  setText(prefix + 'annual-total-profit', fmt$(totalProfit));

  const lastThreeMonthsSales = rows.slice(-3).reduce((sum, row) => sum + parseFloat(row.NetSalesQuantity || row.TotalQuantitySold || 0), 0);
  const avgMonthly = lastThreeMonthsSales / 3;
  const avgWeekly = avgMonthly / 4;
  const currentStock = parseFloat(rows[0]?.CurrentStock || 0);

  setText(prefix + 'demand-weekly', avgWeekly.toFixed(2));
  setText(prefix + 'demand-monthly', avgMonthly.toFixed(2));
  setText(prefix + 'current-inventory', String(Math.round(currentStock)));

  fetchData('inventoryMonthsOfCover', {}).then(config => {
    const months = parseFloat(config?.inventoryMonthsOfCover || 1.35);
    const suggested = Math.max(0, avgMonthly * months);
    setText(prefix + 'suggested-order-quantity', suggested > 0 ? suggested.toFixed(0) : '0000');
  });

  const excess = avgMonthly > 0 && currentStock > avgMonthly
    ? `*producto en exceso para cubrir ${(currentStock / avgMonthly).toFixed(0)} meses `
    : '';
  setText(prefix + 'suggested-order-excess-message', excess);
}

async function doInventoryModalAction() {
  const qty = parseInt(el('cantidadProducto')?.value || '1', 10);
  const userId = el('rm-user-id')?.textContent?.trim() || '';
  if (!receiveProductCode || qty < 1) return;

  if (receiveActionMode === 'preorder') {
    receiveUnitId = el('unidadesProducto')?.value || receiveUnitId || '1';
    await doPreOrderChange(qty, userId, receiveUnitId);
    return;
  }

  await doReceiveInventory(qty, userId);
}

async function doReceiveInventory(qty, userId) {
  try {
    const res = await fetchData('RecReceiveInventory', {
      ItemCode: receiveProductCode,
      QuantityReceived: qty,
      UserID: userId,
    });
    if (res?.success) {
      bootstrap.Modal.getInstance(el('recibirProductoModal'))?.hide();
      await Swal.fire({ title: 'Exito', text: 'Producto recibido correctamente', icon: 'success', timer: 3000, timerProgressBar: true });
      loadCatalogTable({ silent: true });
    } else {
      await Swal.fire('Error', res?.message || 'No se pudo recibir el inventario.', 'error');
    }
  } catch (err) {
    console.error('[products.js] doReceiveInventory:', err);
  }
}

async function doPreOrderChange(qty, userId, unitId) {
  showLoader();
  try {
    const res = await fetchData('ProdPreOrdChange', {
      ItemCode: receiveProductCode,
      PreOrdeQty: qty,
      UserID: userId,
      UnitID: unitId,
    });
    if (res?.success) {
      bootstrap.Modal.getInstance(el('recibirProductoModal'))?.hide();
      await Swal.fire({ title: 'Exito', text: 'Pre - Orden cambiado correctamente', icon: 'success', timer: 3000, timerProgressBar: true });
      loadCatalogTable({ silent: true });
    } else {
      await Swal.fire('Error', res?.message || 'No se pudo cambiar la Pre - Orden.', 'error');
    }
  } catch (err) {
    console.error('[products.js] doPreOrderChange:', err);
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
  const cost    = parseFloat(r.CurrentCost      || r.TotalCost          || r.NetCostValue || 0);
  const profit  = parseFloat(r.ProfitValue      || r.TotalProfit        || 0) || (sales - cost);
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
function fetchProductInfo({ itemCode = '', barcode = '' } = {}) {
  const cleanBarcode = String(barcode || '').trim();
  const cleanItemCode = String(itemCode || '').trim();

  if (cleanBarcode && /^\d{8,}$/.test(cleanBarcode)) {
    return fetchData('ProductInfo', { ItemCode: 0, Barcode: cleanBarcode });
  }

  return fetchData('ProductInfo', { ItemCode: cleanItemCode || 0, Barcode: cleanBarcode });
}
async function resolveProductReference(query) {
  if (/^\d{8,}$/.test(query)) {
    try {
      const r = await fetch(`api_proxy.php?endpoint=InfoBarCode&barcode=${encodeURIComponent(query)}`);
      if (r.ok) {
        const bc = await r.json();
        if (bc && bc !== 'NoMatch' && bc.ProductCode) {
          return { code: bc.ProductCode, barcode: bc.Barcode || query };
        }
      }
    } catch {}
  }

  const detail = await fetchProductInfo({ itemCode: query });
  const product = normalizeProductInfo(detail);
  if (product?.name) {
    return { code: query, name: product.name, barcode: product.barcode };
  }

  try {
    const r = await fetch('api_proxy.php?endpoint=GetAllProducts');
    if (r.ok) {
      const allRows = extractApiData(await r.json());
      const q = query.toLowerCase();
      const match = allRows.find(p => (p.ProductName || '').toLowerCase().includes(q));
      if (match) {
        return {
          code: match.ProductCode,
          name: match.ProductName || '',
          barcode: match.BarCode || match.Barcode || '',
        };
      }
    }
  } catch {}

  return null;
}
function normalizeProductInfo(data) {
  const p = Array.isArray(data) ? data[0] : data;
  if (!p || typeof p !== 'object') return null;
  return {
    name: p.Description || p.ProductName || p.Name || '',
    price: p.Price ?? '',
    cost: p.Cost ?? '',
    onHand: p.OnHand ?? p.CurrentStock ?? '',
    department: p.Department || '',
    category: p.Category || '',
    barcode: p.Barcode || p.BarCode || p.BCde13 || '',
  };
}
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
