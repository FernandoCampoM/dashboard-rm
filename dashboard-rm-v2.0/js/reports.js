import { fmt$, fmtPct, fmtNum, extractApiData, showLoader, hideLoader, PALETTE } from './app.js';

let rvChart    = null;
let rpayDonut  = null;
let rpayBar    = null;
let rtendChart = null;
let rdeptChart = null;
let rcatChart  = null;
let rhrsChart  = null;
let rvTable    = null;
let rprodTable = null;
let rpayTable  = null;
let rtendTable = null;
let rdeptTable = null;
let rcatTable  = null;
let rhrsTable  = null;
let rivuTable  = null;
let ryoyChart  = null;
let ryoyTable  = null;
let activeReport = 'ventas';

function todayStr()    { return moment().format('YYYY-MM-DD'); }
function monthStart()  { return moment().startOf('month').format('YYYY-MM-DD'); }

export async function loadReportsSection(isNew) {
  if (isNew) {
    initDefaultDates();
    bindTabEvents();
    bindApplyEvents();
  }
  await loadActiveReport();
}

function initDefaultDates() {
  const from = monthStart();
  const to   = todayStr();
  setVal('rv-from',    from); setVal('rv-to',    to);
  setVal('rprod-from', from); setVal('rprod-to', to);
  setVal('rpay-from',  from); setVal('rpay-to',  to);
  setVal('rdept-from', from); setVal('rdept-to', to);
  setVal('rcat-from',  from); setVal('rcat-to',  to);
  setVal('rhrs-from',  from); setVal('rhrs-to',  to);
  setVal('rivu-from',  from); setVal('rivu-to',  to);
}

function bindTabEvents() {
  document.querySelectorAll('#rpt-tabs-nav .nav-link').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('#rpt-tabs-nav .nav-link').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      activeReport = btn.dataset.rpt;
      document.querySelectorAll('.rpt-panel').forEach(p => p.classList.add('d-none'));
      document.getElementById(`rpt-${activeReport}`)?.classList.remove('d-none');
      loadActiveReport();
    });
  });
}

function bindApplyEvents() {
  el('rv-apply')?.addEventListener('click',    loadVentas);
  el('rv-refresh')?.addEventListener('click',  loadVentas);
  el('rprod-apply')?.addEventListener('click', loadProductos);
  el('rpay-apply')?.addEventListener('click',  loadMetodos);
  el('rdept-apply')?.addEventListener('click', loadDepartamento);
  el('rcat-apply')?.addEventListener('click',  loadCategoria);
  el('rhrs-apply')?.addEventListener('click',  loadHoras);
  el('rivu-apply')?.addEventListener('click',  loadIVU);
  el('ryoy-apply')?.addEventListener('click',  loadYoY);
}

async function loadActiveReport() {
  switch (activeReport) {
    case 'ventas':       await loadVentas();       break;
    case 'productos':    await loadProductos();    break;
    case 'metodos':      await loadMetodos();      break;
    case 'tendencia':    await loadTendencia();    break;
    case 'departamento': await loadDepartamento(); break;
    case 'categoria':    await loadCategoria();    break;
    case 'horas':        await loadHoras();        break;
    case 'ivu':          await loadIVU();          break;
    case 'yoy':          await loadYoY();          break;
  }
}

// ── Reporte de Ventas ─────────────────────────────────────────────────────────
async function loadVentas() {
  const from = el('rv-from')?.value || monthStart();
  const to   = el('rv-to')?.value   || todayStr();
  showLoader();
  try {
    const [totRaw, methodRaw] = await Promise.all([
      fetchData('SalesTotals',   { DateFrom: from, DateTo: to }),
      fetchData('SalesByMethod', { DateFrom: from, DateTo: to }),
    ]);
    const rows = extractApiData(methodRaw);
    const tot  = extractApiData(totRaw)[0] || {};
    renderVentasKPIs(tot);
    renderVentasChart(rows);
    renderVentasTable(rows);
  } catch (err) { console.error('[reports] ventas:', err); }
  finally { hideLoader(); }
}

function renderVentasKPIs(t) {
  const sales   = parseFloat(t.TotalSales)  || 0;
  const cost    = parseFloat(t.TotalCost)   || 0;
  const profit  = sales - cost;
  const margin  = sales > 0 ? (profit / sales) * 100 : 0;
  const ticket  = parseFloat(t.AverageTicketAmount) || 0;
  const txCount = parseInt(t.TransactionCount, 10)  || 0;
  setText('rv-kpi-sales',   fmt$(sales));
  setText('rv-kpi-profit',  fmt$(profit));
  setText('rv-kpi-margin',  fmtPct(margin));
  setText('rv-kpi-txcount', fmtNum(txCount));
  setText('rv-kpi-ticket',  fmt$(ticket));
}

function renderVentasChart(rows) {
  const canvas = el('rv-chart');
  if (!canvas) return;
  if (rvChart) { rvChart.destroy(); rvChart = null; }
  const labels = rows.map(r => moment(r.SaleDate).format('MM/DD'));
  const sales  = rows.map(r => parseFloat(r.TotalSales)  || 0);
  const profit = rows.map(r => parseFloat(r.TotalProfit) || 0);
  rvChart = new Chart(canvas, {
    type: 'bar',
    data: {
      labels,
      datasets: [
        { label: 'Ventas',   data: sales,  backgroundColor: alpha(PALETTE.primary, .7), order: 2 },
        { label: 'Ganancia', data: profit, type: 'line', borderColor: PALETTE.success,
          backgroundColor: 'transparent', tension: .4, pointRadius: 2, order: 1 },
      ],
    },
    options: baseOpts({
      scales: {
        x: { grid: { display: false } },
        y: { ticks: { callback: v => '$' + numeral(v).format('0,0') } },
      },
    }),
  });
}

function renderVentasTable(rows) {
  if (rvTable) { rvTable.destroy(); rvTable = null; }
  const tbody = document.querySelector('#rv-table tbody');
  if (!tbody) return;
  let totSales = 0, totProfit = 0, totTx = 0;
  tbody.innerHTML = rows.map(r => {
    const sales  = parseFloat(r.TotalSales)  || 0;
    const profit = parseFloat(r.TotalProfit) || 0;
    const tx     = parseInt(r.TransactionCount, 10) || 0;
    const margin = sales > 0 ? (profit / sales) * 100 : 0;
    const ticket = parseFloat(r.AverageTicketAmount) || 0;
    totSales += sales; totProfit += profit; totTx += tx;
    const col = margin >= 25 ? 'success' : margin >= 10 ? 'warning' : 'danger';
    return `<tr>
      <td>${moment(r.SaleDate).format('MM/DD/YYYY')}</td>
      <td class="text-end">${fmt$(sales)}</td>
      <td class="text-end">${fmt$(profit)}</td>
      <td class="text-end"><span class="badge bg-${col}-subtle text-${col}-emphasis">${fmtPct(margin)}</span></td>
      <td class="text-end">${fmtNum(tx)}</td>
      <td class="text-end">${fmt$(ticket)}</td>
    </tr>`;
  }).join('');
  const totMargin = totSales > 0 ? (totProfit / totSales) * 100 : 0;
  setText('rv-foot-sales',  fmt$(totSales));
  setText('rv-foot-profit', fmt$(totProfit));
  setText('rv-foot-margin', fmtPct(totMargin));
  setText('rv-foot-tx',     fmtNum(totTx));
  rvTable = new DataTable('#rv-table', {
    pageLength: 31, order: [[0, 'desc']],
    dom: 'Bfrtip', buttons: ['excel', 'print', 'colvis'],
  });
}

// ── Ventas por Productos ──────────────────────────────────────────────────────
async function loadProductos() {
  const from = el('rprod-from')?.value || monthStart();
  const to   = el('rprod-to')?.value   || todayStr();
  showLoader();
  try {
    const raw  = await fetchData('TopSellProducts', { DateFrom: from, DateTo: to });
    renderProductosTable(extractApiData(raw));
  } catch (err) { console.error('[reports] productos:', err); }
  finally { hideLoader(); }
}

function renderProductosTable(rows) {
  if (rprodTable) { rprodTable.destroy(); rprodTable = null; }
  const tbody = document.querySelector('#rprod-table tbody');
  if (!tbody) return;
  tbody.innerHTML = rows.map(r => {
    const margin = parseFloat(r.ProfitMarginPercentage) || 0;
    const col    = margin >= 30 ? 'success' : margin >= 15 ? 'warning' : 'danger';
    return `<tr>
      <td>${escHtml(r.ProductCode)}</td>
      <td>${escHtml(r.ProductName)}</td>
      <td>${escHtml(r.Department)}</td>
      <td>${escHtml(r.Category)}</td>
      <td class="text-end">${fmtNum(r.TotalQuantitySold)}</td>
      <td class="text-end">${fmt$(r.TotalSales)}</td>
      <td class="text-end">${fmt$(r.TotalProfit)}</td>
      <td class="text-end"><span class="badge bg-${col}-subtle text-${col}-emphasis">${fmtPct(margin)}</span></td>
    </tr>`;
  }).join('');
  rprodTable = new DataTable('#rprod-table', {
    pageLength: 25, order: [[5, 'desc']],
    dom: 'Bfrtip', buttons: ['excel', 'print', 'colvis'],
    responsive: true,
  });
}

// ── Reporte de Métodos de Pago ────────────────────────────────────────────────
async function loadMetodos() {
  const from = el('rpay-from')?.value || monthStart();
  const to   = el('rpay-to')?.value   || todayStr();
  showLoader();
  try {
    const raw  = await fetchData('SalesByMethod', { DateFrom: from, DateTo: to });
    const rows = extractApiData(raw);
    renderMetodosKPIs(rows);
    renderMetodosDonut(rows);
    renderMetodosBar(rows);
    renderMetodosTable(rows);
  } catch (err) { console.error('[reports] metodos:', err); }
  finally { hideLoader(); }
}

function sumField(rows, key) {
  return rows.reduce((s, r) => s + (parseFloat(r[key]) || 0), 0);
}

function renderMetodosKPIs(rows) {
  const cash   = sumField(rows, 'CashPayments');
  const credit = sumField(rows, 'CreditCardPayments');
  const debit  = sumField(rows, 'DebitCardPayments');
  const ath    = sumField(rows, 'AthMovilPayments');
  const check  = sumField(rows, 'CheckPayments');
  setText('rpay-kpi-cash',   fmt$(cash));
  setText('rpay-kpi-credit', fmt$(credit));
  setText('rpay-kpi-debit',  fmt$(debit));
  setText('rpay-kpi-ath',    fmt$(ath));
  setText('rpay-kpi-check',  fmt$(check));
  setText('rpay-kpi-total',  fmt$(cash + credit + debit + ath + check));
}

function renderMetodosDonut(rows) {
  const canvas = el('rpay-donut');
  if (!canvas) return;
  if (rpayDonut) { rpayDonut.destroy(); rpayDonut = null; }
  rpayDonut = new Chart(canvas, {
    type: 'doughnut',
    data: {
      labels: ['Efectivo','Crédito','Débito','ATH Móvil','Cheque'],
      datasets: [{
        data: [
          sumField(rows,'CashPayments'), sumField(rows,'CreditCardPayments'),
          sumField(rows,'DebitCardPayments'), sumField(rows,'AthMovilPayments'),
          sumField(rows,'CheckPayments'),
        ],
        backgroundColor: [PALETTE.success, PALETTE.primary, PALETTE.info, PALETTE.warning, PALETTE.danger],
        borderWidth: 2,
      }],
    },
    options: { responsive: true, maintainAspectRatio: false,
      plugins: { legend: { position: 'bottom', labels: { boxWidth: 12 } } } },
  });
}

function renderMetodosBar(rows) {
  const canvas = el('rpay-bar');
  if (!canvas) return;
  if (rpayBar) { rpayBar.destroy(); rpayBar = null; }
  const labels = rows.map(r => moment(r.SaleDate).format('MM/DD'));
  const make = (key, label, color) => ({
    label, stack: 'pay', backgroundColor: color,
    data: rows.map(r => parseFloat(r[key]) || 0),
  });
  rpayBar = new Chart(canvas, {
    type: 'bar',
    data: {
      labels,
      datasets: [
        make('CashPayments',       'Efectivo',  PALETTE.success),
        make('CreditCardPayments', 'Crédito',   PALETTE.primary),
        make('DebitCardPayments',  'Débito',    PALETTE.info),
        make('AthMovilPayments',   'ATH Móvil', PALETTE.warning),
        make('CheckPayments',      'Cheque',    PALETTE.danger),
      ],
    },
    options: baseOpts({
      scales: {
        x: { stacked: true, grid: { display: false } },
        y: { stacked: true, ticks: { callback: v => '$' + numeral(v).format('0,0') } },
      },
    }),
  });
}

function renderMetodosTable(rows) {
  if (rpayTable) { rpayTable.destroy(); rpayTable = null; }
  const tbody = document.querySelector('#rpay-table tbody');
  if (!tbody) return;
  let tCash=0, tCredit=0, tDebit=0, tAth=0, tCheck=0, tTotal=0;
  tbody.innerHTML = rows.map(r => {
    const cash   = parseFloat(r.CashPayments)       || 0;
    const credit = parseFloat(r.CreditCardPayments) || 0;
    const debit  = parseFloat(r.DebitCardPayments)  || 0;
    const ath    = parseFloat(r.AthMovilPayments)   || 0;
    const check  = parseFloat(r.CheckPayments)      || 0;
    const total  = cash + credit + debit + ath + check;
    tCash+=cash; tCredit+=credit; tDebit+=debit; tAth+=ath; tCheck+=check; tTotal+=total;
    return `<tr>
      <td>${moment(r.SaleDate).format('MM/DD/YYYY')}</td>
      <td class="text-end">${fmt$(cash)}</td>
      <td class="text-end">${fmt$(credit)}</td>
      <td class="text-end">${fmt$(debit)}</td>
      <td class="text-end">${fmt$(ath)}</td>
      <td class="text-end">${fmt$(check)}</td>
      <td class="text-end fw-semibold">${fmt$(total)}</td>
    </tr>`;
  }).join('');
  setText('rpay-foot-cash',   fmt$(tCash));
  setText('rpay-foot-credit', fmt$(tCredit));
  setText('rpay-foot-debit',  fmt$(tDebit));
  setText('rpay-foot-ath',    fmt$(tAth));
  setText('rpay-foot-check',  fmt$(tCheck));
  setText('rpay-foot-total',  fmt$(tTotal));
  rpayTable = new DataTable('#rpay-table', {
    pageLength: 31, order: [[0, 'desc']],
    dom: 'Bfrtip', buttons: ['excel', 'print', 'colvis'],
  });
}

// ── Tendencia Mensual ─────────────────────────────────────────────────────────
async function loadTendencia() {
  const from = moment().subtract(12, 'months').startOf('month').format('YYYY-MM-DD');
  const to   = todayStr();
  showLoader();
  try {
    const raw  = await fetchData('SaleTrendByMonth', { DateFrom: from, DateTo: to });
    const rows = extractApiData(raw);
    renderTendenciaKPIs(rows);
    renderTendenciaChart(rows);
    renderTendenciaTable(rows);
  } catch (err) { console.error('[reports] tendencia:', err); }
  finally { hideLoader(); }
}

function renderTendenciaKPIs(rows) {
  const totalSales  = rows.reduce((s, r) => s + (parseFloat(r.TotalSales)  || 0), 0);
  const totalProfit = rows.reduce((s, r) => s + (parseFloat(r.TotalProfit) || 0), 0);
  const margins     = rows.map(r => parseFloat(r.GrossMarginPercentage) || 0).filter(m => m !== 0);
  const avgMargin   = margins.length ? margins.reduce((s, m) => s + m, 0) / margins.length : 0;

  let bestRow = rows[0], worstRow = rows[0];
  rows.forEach(r => {
    if ((parseFloat(r.TotalSales) || 0) > (parseFloat(bestRow.TotalSales) || 0)) bestRow = r;
    if ((parseFloat(r.GrossMarginPercentage) || 0) < (parseFloat(worstRow.GrossMarginPercentage) || 999)) worstRow = r;
  });

  setText('rtend-kpi-sales',  fmt$(totalSales));
  setText('rtend-kpi-profit', fmt$(totalProfit));
  setText('rtend-kpi-margin', fmtPct(avgMargin));
  setText('rtend-kpi-best',   bestRow ? (bestRow.MonthYear || '—') : '—');
  setText('rtend-kpi-worst',  worstRow ? `${worstRow.MonthYear || '—'} (${fmtPct(parseFloat(worstRow.GrossMarginPercentage)||0)})` : '—');
}

function renderTendenciaChart(rows) {
  const canvas = el('rtend-chart');
  if (!canvas) return;
  if (rtendChart) { rtendChart.destroy(); rtendChart = null; }
  const labels  = rows.map(r => r.MonthYear || `${r.Year}-${String(r.Month).padStart(2,'0')}`);
  const sales   = rows.map(r => parseFloat(r.TotalSales) || 0);
  const margins = rows.map(r => parseFloat(r.GrossMarginPercentage) || 0);
  rtendChart = new Chart(canvas, {
    data: {
      labels,
      datasets: [
        { type: 'bar',  label: 'Ventas',  data: sales,   backgroundColor: alpha(PALETTE.primary, .7), order: 2, yAxisID: 'y' },
        { type: 'line', label: 'Margen%', data: margins, borderColor: PALETTE.warning,
          backgroundColor: 'transparent', tension: .4, pointRadius: 3, order: 1, yAxisID: 'y2' },
      ],
    },
    options: baseOpts({
      scales: {
        x:  { grid: { display: false } },
        y:  { ticks: { callback: v => '$' + numeral(v).format('0,0') } },
        y2: { position: 'right', grid: { drawOnChartArea: false },
               ticks: { callback: v => v.toFixed(1) + '%' } },
      },
    }),
  });
}

function renderTendenciaTable(rows) {
  if (rtendTable) { rtendTable.destroy(); rtendTable = null; }
  const tbody = document.querySelector('#rtend-table tbody');
  if (!tbody) return;
  let tInv=0, tSales=0, tSub=0, tDisc=0, tProfit=0;
  tbody.innerHTML = rows.map(r => {
    const sales   = parseFloat(r.TotalSales)             || 0;
    const sub     = parseFloat(r.Subtotal)               || 0;
    const disc    = parseFloat(r.TotalDiscounts)         || 0;
    const profit  = parseFloat(r.TotalProfit)            || 0;
    const margin  = parseFloat(r.GrossMarginPercentage)  || 0;
    const inv     = parseInt(r.InvoiceCount, 10)         || 0;
    const ticket  = parseFloat(r.AverageTicketAmount)    || 0;
    tInv+=inv; tSales+=sales; tSub+=sub; tDisc+=disc; tProfit+=profit;
    const col = margin >= 30 ? 'success' : margin >= 20 ? 'warning' : 'danger';
    return `<tr>
      <td>${escHtml(r.MonthYear || `${r.Year}-${String(r.Month).padStart(2,'0')}`)}</td>
      <td class="text-end">${fmtNum(inv)}</td>
      <td class="text-end">${fmt$(sales)}</td>
      <td class="text-end">${fmt$(sub)}</td>
      <td class="text-end">${fmt$(disc)}</td>
      <td class="text-end">${fmt$(profit)}</td>
      <td class="text-end"><span class="badge bg-${col}-subtle text-${col}-emphasis">${fmtPct(margin)}</span></td>
      <td class="text-end">${fmt$(ticket)}</td>
    </tr>`;
  }).join('');
  const totMargin = tSales > 0 ? (tProfit / tSales) * 100 : 0;
  setText('rtend-foot-invoices', fmtNum(tInv));
  setText('rtend-foot-sales',    fmt$(tSales));
  setText('rtend-foot-subtotal', fmt$(tSub));
  setText('rtend-foot-discount', fmt$(tDisc));
  setText('rtend-foot-profit',   fmt$(tProfit));
  setText('rtend-foot-margin',   fmtPct(totMargin));
  rtendTable = new DataTable('#rtend-table', {
    pageLength: 13, order: [[0, 'desc']],
    dom: 'Bfrtip', buttons: ['excel', 'print', 'colvis'],
  });
}

// ── Por Departamento ──────────────────────────────────────────────────────────
async function loadDepartamento() {
  const from = el('rdept-from')?.value || monthStart();
  const to   = el('rdept-to')?.value   || todayStr();
  showLoader();
  try {
    const raw  = await fetchData('SalesByDepartment', { DateFrom: from, DateTo: to });
    const rows = extractApiData(raw);
    renderDeptKPIs(rows);
    renderDeptChart(rows);
    renderDeptTable(rows);
  } catch (err) { console.error('[reports] departamento:', err); }
  finally { hideLoader(); }
}

function renderDeptKPIs(rows) {
  const totalSales  = rows.reduce((s, r) => s + (parseFloat(r.TotalSales)  || 0), 0);
  const totalProfit = rows.reduce((s, r) => s + (parseFloat(r.TotalProfit) || 0), 0);
  const margins     = rows.map(r => parseFloat(r.ProfitMarginPercentage) || 0).filter(m => m !== 0);
  const avgMargin   = margins.length ? margins.reduce((s, m) => s + m, 0) / margins.length : 0;
  setText('rdept-kpi-depts',  fmtNum(rows.length));
  setText('rdept-kpi-sales',  fmt$(totalSales));
  setText('rdept-kpi-profit', fmt$(totalProfit));
  setText('rdept-kpi-margin', fmtPct(avgMargin));
}

function renderDeptChart(rows) {
  const canvas = el('rdept-chart');
  if (!canvas) return;
  if (rdeptChart) { rdeptChart.destroy(); rdeptChart = null; }
  const top15 = [...rows].sort((a, b) => (parseFloat(b.TotalSales)||0) - (parseFloat(a.TotalSales)||0)).slice(0, 15).reverse();
  rdeptChart = new Chart(canvas, {
    type: 'bar',
    data: {
      labels: top15.map(r => escHtml(r.Department)),
      datasets: [
        { label: 'Ventas',   data: top15.map(r => parseFloat(r.TotalSales)  || 0), backgroundColor: alpha(PALETTE.primary, .8) },
        { label: 'Ganancia', data: top15.map(r => parseFloat(r.TotalProfit) || 0), backgroundColor: alpha(PALETTE.success, .8) },
      ],
    },
    options: baseOpts({
      indexAxis: 'y',
      scales: {
        x: { ticks: { callback: v => '$' + numeral(v).format('0,0') } },
        y: { grid: { display: false } },
      },
    }),
  });
}

function renderDeptTable(rows) {
  if (rdeptTable) { rdeptTable.destroy(); rdeptTable = null; }
  const tbody = document.querySelector('#rdept-table tbody');
  if (!tbody) return;
  let tInv=0, tQty=0, tSales=0, tProfit=0;
  tbody.innerHTML = rows.map(r => {
    const sales  = parseFloat(r.TotalSales)            || 0;
    const profit = parseFloat(r.TotalProfit)           || 0;
    const margin = parseFloat(r.ProfitMarginPercentage)|| 0;
    const inv    = parseInt(r.InvoiceCount, 10)        || 0;
    const qty    = parseFloat(r.QuantitySold)          || 0;
    const avg    = parseFloat(r.AveragePrice)          || 0;
    tInv+=inv; tQty+=qty; tSales+=sales; tProfit+=profit;
    const col = margin >= 30 ? 'success' : margin >= 15 ? 'warning' : 'danger';
    return `<tr>
      <td>${escHtml(r.Department)}</td>
      <td class="text-end">${fmtNum(inv)}</td>
      <td class="text-end">${fmtNum(qty)}</td>
      <td class="text-end">${fmt$(sales)}</td>
      <td class="text-end">${fmt$(profit)}</td>
      <td class="text-end">${fmt$(avg)}</td>
      <td class="text-end"><span class="badge bg-${col}-subtle text-${col}-emphasis">${fmtPct(margin)}</span></td>
    </tr>`;
  }).join('');
  const totMargin = tSales > 0 ? (tProfit / tSales) * 100 : 0;
  setText('rdept-foot-invoices', fmtNum(tInv));
  setText('rdept-foot-qty',      fmtNum(tQty));
  setText('rdept-foot-sales',    fmt$(tSales));
  setText('rdept-foot-profit',   fmt$(tProfit));
  setText('rdept-foot-margin',   fmtPct(totMargin));
  rdeptTable = new DataTable('#rdept-table', {
    pageLength: 25, order: [[3, 'desc']],
    dom: 'Bfrtip', buttons: ['excel', 'print', 'colvis'],
  });
}

// ── Por Categoría ─────────────────────────────────────────────────────────────
async function loadCategoria() {
  const from = el('rcat-from')?.value || monthStart();
  const to   = el('rcat-to')?.value   || todayStr();
  showLoader();
  try {
    const raw  = await fetchData('SalesByCategory', { DateFrom: from, DateTo: to });
    const rows = extractApiData(raw);
    renderCatKPIs(rows);
    renderCatChart(rows);
    renderCatTable(rows);
  } catch (err) { console.error('[reports] categoria:', err); }
  finally { hideLoader(); }
}

function renderCatKPIs(rows) {
  const totalSales  = rows.reduce((s, r) => s + (parseFloat(r.TotalSales)  || 0), 0);
  const totalProfit = rows.reduce((s, r) => s + (parseFloat(r.TotalProfit) || 0), 0);
  const margins     = rows.map(r => parseFloat(r.ProfitMarginPercentage) || 0).filter(m => m !== 0);
  const avgMargin   = margins.length ? margins.reduce((s, m) => s + m, 0) / margins.length : 0;
  setText('rcat-kpi-cats',   fmtNum(rows.length));
  setText('rcat-kpi-sales',  fmt$(totalSales));
  setText('rcat-kpi-profit', fmt$(totalProfit));
  setText('rcat-kpi-margin', fmtPct(avgMargin));
}

function renderCatChart(rows) {
  const canvas = el('rcat-chart');
  if (!canvas) return;
  if (rcatChart) { rcatChart.destroy(); rcatChart = null; }
  const top15 = [...rows].sort((a, b) => (parseFloat(b.TotalSales)||0) - (parseFloat(a.TotalSales)||0)).slice(0, 15).reverse();
  rcatChart = new Chart(canvas, {
    type: 'bar',
    data: {
      labels: top15.map(r => escHtml(r.CategoryName)),
      datasets: [
        { label: 'Ventas',   data: top15.map(r => parseFloat(r.TotalSales)  || 0), backgroundColor: alpha(PALETTE.primary, .8) },
        { label: 'Ganancia', data: top15.map(r => parseFloat(r.TotalProfit) || 0), backgroundColor: alpha(PALETTE.success, .8) },
      ],
    },
    options: baseOpts({
      indexAxis: 'y',
      scales: {
        x: { ticks: { callback: v => '$' + numeral(v).format('0,0') } },
        y: { grid: { display: false } },
      },
    }),
  });
}

function renderCatTable(rows) {
  if (rcatTable) { rcatTable.destroy(); rcatTable = null; }
  const tbody = document.querySelector('#rcat-table tbody');
  if (!tbody) return;
  let tInv=0, tQty=0, tSales=0, tProfit=0;
  tbody.innerHTML = rows.map(r => {
    const sales  = parseFloat(r.TotalSales)            || 0;
    const profit = parseFloat(r.TotalProfit)           || 0;
    const margin = parseFloat(r.ProfitMarginPercentage)|| 0;
    const inv    = parseInt(r.InvoiceCount, 10)        || 0;
    const qty    = parseFloat(r.QuantitySold)          || 0;
    const avg    = parseFloat(r.AveragePrice)          || 0;
    tInv+=inv; tQty+=qty; tSales+=sales; tProfit+=profit;
    const col = margin >= 30 ? 'success' : margin >= 15 ? 'warning' : 'danger';
    return `<tr>
      <td>${escHtml(r.CategoryName)}</td>
      <td class="text-end">${fmtNum(inv)}</td>
      <td class="text-end">${fmtNum(qty)}</td>
      <td class="text-end">${fmt$(sales)}</td>
      <td class="text-end">${fmt$(profit)}</td>
      <td class="text-end">${fmt$(avg)}</td>
      <td class="text-end"><span class="badge bg-${col}-subtle text-${col}-emphasis">${fmtPct(margin)}</span></td>
    </tr>`;
  }).join('');
  const totMargin = tSales > 0 ? (tProfit / tSales) * 100 : 0;
  setText('rcat-foot-invoices', fmtNum(tInv));
  setText('rcat-foot-qty',      fmtNum(tQty));
  setText('rcat-foot-sales',    fmt$(tSales));
  setText('rcat-foot-profit',   fmt$(tProfit));
  setText('rcat-foot-margin',   fmtPct(totMargin));
  rcatTable = new DataTable('#rcat-table', {
    pageLength: 25, order: [[3, 'desc']],
    dom: 'Bfrtip', buttons: ['excel', 'print', 'colvis'],
  });
}

// ── Análisis de Horas ─────────────────────────────────────────────────────────
async function loadHoras() {
  const from = el('rhrs-from')?.value || monthStart();
  const to   = el('rhrs-to')?.value   || todayStr();
  showLoader();
  try {
    const raw  = await fetchData('SalesByHour', { DateFrom: from, DateTo: to });
    const rows = extractApiData(raw);
    renderHorasKPIs(rows);
    renderHorasChart(rows);
    renderHorasTable(rows);
  } catch (err) { console.error('[reports] horas:', err); }
  finally { hideLoader(); }
}

function renderHorasKPIs(rows) {
  if (!rows.length) return;
  const peakRow   = rows.reduce((best, r) => (parseFloat(r.TotalSales)||0) > (parseFloat(best.TotalSales)||0) ? r : best, rows[0]);
  const profitRow = rows.reduce((best, r) => (parseFloat(r.TotalProfit)||0) > (parseFloat(best.TotalProfit)||0) ? r : best, rows[0]);
  const totalTx   = rows.reduce((s, r) => s + (parseInt(r.TransactionCount, 10) || 0), 0);
  const totalSales = rows.reduce((s, r) => s + (parseFloat(r.TotalSales) || 0), 0);
  const avgTicket  = totalTx > 0 ? totalSales / totalTx : 0;
  const fmtHour = h => {
    const hr = parseInt(h, 10);
    const ampm = hr < 12 ? 'AM' : 'PM';
    const display = hr === 0 ? 12 : hr > 12 ? hr - 12 : hr;
    return `${display}:00 ${ampm}`;
  };
  setText('rhrs-kpi-peak',      fmtHour(peakRow.HourOfDay));
  setText('rhrs-kpi-bestprofit', fmtHour(profitRow.HourOfDay));
  setText('rhrs-kpi-txcount',   fmtNum(totalTx));
  setText('rhrs-kpi-ticket',    fmt$(avgTicket));
}

function renderHorasChart(rows) {
  const canvas = el('rhrs-chart');
  if (!canvas) return;
  if (rhrsChart) { rhrsChart.destroy(); rhrsChart = null; }

  const byHour = Array.from({ length: 24 }, (_, h) => rows.find(r => parseInt(r.HourOfDay, 10) === h) || null);
  const salesData = byHour.map(r => r ? (parseFloat(r.TotalSales) || 0) : 0);
  const txData    = byHour.map(r => r ? (parseInt(r.TransactionCount, 10) || 0) : 0);
  const labels    = Array.from({ length: 24 }, (_, h) => `${String(h).padStart(2,'0')}:00`);

  const sorted = [...salesData].sort((a, b) => b - a);
  const top3   = new Set(sorted.slice(0, 3).filter(v => v > 0));
  const barColors = salesData.map(v => top3.has(v) && v > 0 ? PALETTE.warning : alpha(PALETTE.primary, .7));

  rhrsChart = new Chart(canvas, {
    data: {
      labels,
      datasets: [
        { type: 'bar',  label: 'Ventas ($)',    data: salesData, backgroundColor: barColors, order: 2, yAxisID: 'y' },
        { type: 'line', label: 'Transacciones', data: txData, borderColor: PALETTE.info,
          backgroundColor: 'transparent', tension: .4, pointRadius: 2, order: 1, yAxisID: 'y2' },
      ],
    },
    options: baseOpts({
      scales: {
        x:  { grid: { display: false } },
        y:  { ticks: { callback: v => '$' + numeral(v).format('0,0') } },
        y2: { position: 'right', grid: { drawOnChartArea: false },
               ticks: { callback: v => fmtNum(v) } },
      },
    }),
  });
}

function renderHorasTable(rows) {
  if (rhrsTable) { rhrsTable.destroy(); rhrsTable = null; }
  const tbody = document.querySelector('#rhrs-table tbody');
  if (!tbody) return;
  const fmtHour = h => {
    const hr = parseInt(h, 10);
    const ampm = hr < 12 ? 'AM' : 'PM';
    const display = hr === 0 ? 12 : hr > 12 ? hr - 12 : hr;
    return `${display}:00 ${ampm}`;
  };
  tbody.innerHTML = rows.map(r => {
    const tx     = parseInt(r.TransactionCount, 10) || 0;
    const sales  = parseFloat(r.TotalSales)         || 0;
    const profit = parseFloat(r.TotalProfit)        || 0;
    const ticket = parseFloat(r.AverageTicketAmount)|| 0;
    const items  = parseFloat(r.TotalItemsSold)     || 0;
    return `<tr>
      <td>${fmtHour(r.HourOfDay)}</td>
      <td class="text-end">${fmtNum(tx)}</td>
      <td class="text-end">${fmt$(sales)}</td>
      <td class="text-end">${fmt$(profit)}</td>
      <td class="text-end">${fmt$(ticket)}</td>
      <td class="text-end">${fmtNum(items)}</td>
    </tr>`;
  }).join('');
  rhrsTable = new DataTable('#rhrs-table', {
    pageLength: 24, order: [[2, 'desc']],
    dom: 'Bfrtip', buttons: ['excel', 'print'],
  });
}

// ── Reporte IVU ───────────────────────────────────────────────────────────────
async function loadIVU() {
  const from = el('rivu-from')?.value || monthStart();
  const to   = el('rivu-to')?.value   || todayStr();
  showLoader();
  try {
    const raw  = await fetchData('SalesByMethod', { DateFrom: from, DateTo: to });
    const rows = extractApiData(raw);
    renderIVUKPIs(rows);
    renderIVUTable(rows);
  } catch (err) { console.error('[reports] ivu:', err); }
  finally { hideLoader(); }
}

function renderIVUKPIs(rows) {
  const subtotal = sumField(rows, 'Subtotal');
  const city     = sumField(rows, 'TotalCityTax');
  const state    = sumField(rows, 'TotalStateTax');
  const total    = sumField(rows, 'TotalSales');
  setText('rivu-kpi-subtotal', fmt$(subtotal));
  setText('rivu-kpi-city',     fmt$(city));
  setText('rivu-kpi-state',    fmt$(state));
  setText('rivu-kpi-total',    fmt$(city + state));
  setText('rivu-kpi-sales',    fmt$(total));
}

function renderIVUTable(rows) {
  if (rivuTable) { rivuTable.destroy(); rivuTable = null; }
  const tbody = document.querySelector('#rivu-table tbody');
  if (!tbody) return;
  let tTx=0, tSub=0, tCity=0, tState=0, tSales=0;
  tbody.innerHTML = rows.map(r => {
    const tx    = parseInt(r.TransactionCount, 10) || 0;
    const sub   = parseFloat(r.Subtotal)           || 0;
    const city  = parseFloat(r.TotalCityTax)       || 0;
    const state = parseFloat(r.TotalStateTax)      || 0;
    const sales = parseFloat(r.TotalSales)         || 0;
    tTx+=tx; tSub+=sub; tCity+=city; tState+=state; tSales+=sales;
    return `<tr>
      <td>${moment(r.SaleDate).format('MM/DD/YYYY')}</td>
      <td class="text-end">${fmtNum(tx)}</td>
      <td class="text-end">${fmt$(sub)}</td>
      <td class="text-end">${fmt$(city)}</td>
      <td class="text-end">${fmt$(state)}</td>
      <td class="text-end">${fmt$(city + state)}</td>
      <td class="text-end fw-semibold">${fmt$(sales)}</td>
    </tr>`;
  }).join('');
  setText('rivu-foot-tx',       fmtNum(tTx));
  setText('rivu-foot-subtotal', fmt$(tSub));
  setText('rivu-foot-city',     fmt$(tCity));
  setText('rivu-foot-state',    fmt$(tState));
  setText('rivu-foot-ivu',      fmt$(tCity + tState));
  setText('rivu-foot-sales',    fmt$(tSales));
  rivuTable = new DataTable('#rivu-table', {
    pageLength: 31, order: [[0, 'desc']],
    dom: 'Bfrtip', buttons: ['excel', 'print', 'colvis'],
  });
}

// ── Año vs Año ────────────────────────────────────────────────────────────────
const YOY_MONTHS = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];

async function loadYoY() {
  const cy     = moment().year();
  const py     = cy - 1;
  const cyFrom = `${cy}-01-01`;
  const cyTo   = moment().format('YYYY-MM-DD');
  const pyFrom = `${py}-01-01`;
  const pyTo   = `${py}-12-31`;

  setText('ryoy-range-label', `Comparando ${py} vs ${cy}`);
  setText('ryoy-label-cy', `${cy}`);
  setText('ryoy-label-py', `${py}`);
  showLoader();
  try {
    const [cyRaw, pyRaw] = await Promise.all([
      fetchData('SaleTrendByMonth', { DateFrom: cyFrom, DateTo: cyTo }),
      fetchData('SaleTrendByMonth', { DateFrom: pyFrom, DateTo: pyTo }),
    ]);
    const cyRows = extractApiData(cyRaw);
    const pyRows = extractApiData(pyRaw);
    renderYoYKPIs(cyRows, pyRows);
    renderYoYChart(cyRows, pyRows, cy, py);
    renderYoYTable(cyRows, pyRows);
  } catch (err) { console.error('[reports] yoy:', err); }
  finally { hideLoader(); }
}

function yoyMonthMap(rows) {
  const map = {};
  rows.forEach(r => {
    const m = parseInt(r.Month, 10);
    if (m >= 1 && m <= 12) map[m] = r;
  });
  return map;
}

function renderYoYKPIs(cyRows, pyRows) {
  const cySales = sumField(cyRows, 'TotalSales');
  const pySales = sumField(pyRows, 'TotalSales');
  const delta   = cySales - pySales;
  const pct     = pySales > 0 ? (delta / pySales) * 100 : 0;
  setText('ryoy-kpi-cy', fmt$(cySales));
  setText('ryoy-kpi-py', fmt$(pySales));
  const deltaEl = el('ryoy-kpi-delta');
  if (deltaEl) {
    deltaEl.textContent = (delta >= 0 ? '+' : '') + fmt$(delta);
    deltaEl.style.color = delta >= 0 ? 'var(--rm-success)' : 'var(--rm-danger)';
  }
  const pctEl = el('ryoy-kpi-pct');
  if (pctEl) {
    pctEl.textContent = (pct >= 0 ? '+' : '') + fmtPct(pct);
    pctEl.style.color = pct >= 0 ? 'var(--rm-success)' : 'var(--rm-danger)';
  }
}

function renderYoYChart(cyRows, pyRows, cy, py) {
  const canvas = el('ryoy-chart');
  if (!canvas) return;
  if (ryoyChart) { ryoyChart.destroy(); ryoyChart = null; }

  const cyMap  = yoyMonthMap(cyRows);
  const pyMap  = yoyMonthMap(pyRows);
  const cyData = YOY_MONTHS.map((_, i) => parseFloat(cyMap[i+1]?.TotalSales || 0));
  const pyData = YOY_MONTHS.map((_, i) => parseFloat(pyMap[i+1]?.TotalSales || 0));

  ryoyChart = new Chart(canvas, {
    type: 'bar',
    data: {
      labels: YOY_MONTHS,
      datasets: [
        { label: String(cy), data: cyData, backgroundColor: alpha(PALETTE.primary, .8), borderRadius: 4 },
        { label: String(py), data: pyData, backgroundColor: alpha(PALETTE.info,    .35), borderRadius: 4, borderColor: alpha(PALETTE.info, .7), borderWidth: 1 },
      ],
    },
    options: baseOpts({
      scales: {
        x: { grid: { display: false } },
        y: { ticks: { callback: v => '$' + numeral(v).format('0,0') } },
      },
    }),
  });
}

function renderYoYTable(cyRows, pyRows) {
  if (ryoyTable) { ryoyTable.destroy(); ryoyTable = null; }
  const tbody = document.querySelector('#ryoy-table tbody');
  if (!tbody) return;

  const cyMap = yoyMonthMap(cyRows);
  const pyMap = yoyMonthMap(pyRows);
  let tCy=0, tPy=0, tProfitCy=0, tProfitPy=0;

  tbody.innerHTML = YOY_MONTHS.map((name, i) => {
    const m      = i + 1;
    const cy     = parseFloat(cyMap[m]?.TotalSales  || 0);
    const py     = parseFloat(pyMap[m]?.TotalSales  || 0);
    const profCy = parseFloat(cyMap[m]?.TotalProfit || 0);
    const profPy = parseFloat(pyMap[m]?.TotalProfit || 0);
    const delta  = cy - py;
    const pct    = py > 0 ? (delta / py * 100) : (cy > 0 ? 100 : 0);
    tCy += cy; tPy += py; tProfitCy += profCy; tProfitPy += profPy;
    const rowCls   = cy > py ? 'table-success' : (cy < py && cy > 0 ? 'table-danger' : '');
    const pctCls   = delta >= 0 ? 'text-success' : 'text-danger';
    const arrow    = delta > 0 ? '▲' : (delta < 0 ? '▼' : '—');
    return `<tr class="${rowCls}">
      <td class="fw-semibold">${name}</td>
      <td class="text-end fw-semibold">${fmt$(cy)}</td>
      <td class="text-end">${fmt$(py)}</td>
      <td class="text-end">${(delta >= 0 ? '+' : '') + fmt$(delta)}</td>
      <td class="text-end ${pctCls}">${arrow} ${Math.abs(pct).toFixed(1)}%</td>
      <td class="text-end">${fmt$(profCy)}</td>
      <td class="text-end">${fmt$(profPy)}</td>
    </tr>`;
  }).join('');

  const totalDelta = tCy - tPy;
  const totalPct   = tPy > 0 ? (totalDelta / tPy * 100) : 0;
  setText('ryoy-foot-cy',        fmt$(tCy));
  setText('ryoy-foot-py',        fmt$(tPy));
  setText('ryoy-foot-delta',     (totalDelta >= 0 ? '+' : '') + fmt$(totalDelta));
  setText('ryoy-foot-pct',       (totalPct >= 0 ? '+' : '') + totalPct.toFixed(1) + '%');
  setText('ryoy-foot-profit-cy', fmt$(tProfitCy));
  setText('ryoy-foot-profit-py', fmt$(tProfitPy));

  ryoyTable = new DataTable('#ryoy-table', {
    pageLength: 12, ordering: false,
    dom: 'Bfrtip', buttons: ['excel', 'print'],
  });
}

// ── Helpers ───────────────────────────────────────────────────────────────────
function el(id)        { return document.getElementById(id); }
function setText(id,v) { const e = el(id); if (e) e.textContent = v; }
function setVal(id,v)  { const e = el(id); if (e) e.value = v; }
function escHtml(s)    { const d = document.createElement('div'); d.textContent = s ?? ''; return d.innerHTML; }
function alpha(hex, a) {
  const r=parseInt(hex.slice(1,3),16), g=parseInt(hex.slice(3,5),16), b=parseInt(hex.slice(5,7),16);
  return `rgba(${r},${g},${b},${a})`;
}
function baseOpts(extra = {}) {
  return { responsive: true, maintainAspectRatio: false,
    plugins: { legend: { labels: { boxWidth: 12, font: { size: 11 } } } }, ...extra };
}
