import { fmt$, fmtPct, fmtNum, extractApiData, showLoader, hideLoader, PALETTE, CHART_COLORS } from './app.js';

let deptChart     = null;
let riskGauge     = null;
let lowStockTable = null;
let slowTable     = null;

function today12m() { return moment().subtract(11,'months').startOf('month').format('YYYY-MM-DD'); }
function today()    { return moment().format('YYYY-MM-DD'); }

export async function loadInventorySection(isNew) {
  if (isNew) {
    document.getElementById('inv-refresh-btn')?.addEventListener('click', () => loadInventorySection(false));
  }
  showLoader();
  try {
    const [invVal, lowLevel, lowSell] = await Promise.all([
      fetchData('InventoryValue',  {}),
      fetchData('LowLevelItems',   { Active: 1 }),
      fetchData('LowSellProducts', { DateFrom: today12m(), DateTo: today() }),
    ]);

    const invRows  = extractApiData(invVal);
    const lowRows  = extractApiData(lowLevel);
    const slowRows = extractApiData(lowSell);

    renderKPIs(invRows, lowRows);
    renderDeptChart(invRows);
    renderRiskGauge(invRows, lowRows);
    renderLowStockTable(lowRows);
    renderSlowTable(slowRows);
  } catch (err) {
    console.error('[inventory.js]', err);
  } finally {
    hideLoader();
  }
}

// Returns true for rows that are summary/total rows (not real departments)
function isTotalRow(r) {
  const d = (r.Department || '').toUpperCase();
  return d === 'TOTAL' || d === 'GRAND TOTAL' || d.includes('GRAN TOTAL');
}
function invCount(r)  { return parseInt(r.ProductCount || r.ItemCount, 10) || 0; }
function invRetail(r) { return parseFloat(r.TotalInventoryValue || r.TotalRetail) || 0; }

// ── KPIs ─────────────────────────────────────────────────────────────────────
function renderKPIs(invRows, lowRows) {
  const deptRows = invRows.filter(r => !isTotalRow(r));

  const totalRetail = deptRows.reduce((s,r) => s + invRetail(r), 0);
  const totalSkus   = deptRows.reduce((s,r) => s + invCount(r),  0);
  // InventoryValue API returns LowStockItems per dept; TotalCost is not available
  const totalAtRisk = deptRows.reduce((s,r) => s + (parseInt(r.LowStockItems) || 0), 0);
  const riskPct     = totalSkus > 0 ? (totalAtRisk / totalSkus) * 100 : 0;

  setText('inv-kpi-skus',    fmtNum(totalSkus));
  setText('inv-kpi-retail',  fmt$(totalRetail));
  setText('inv-kpi-cost',    fmtNum(totalAtRisk));
  setText('inv-kpi-margin',  fmtPct(riskPct));
}

// ── Dept horizontal bar ────────────────────────────────────────────────────────
function renderDeptChart(rows) {
  const canvas = el('inv-dept-chart');
  if (!canvas) return;
  if (deptChart) { deptChart.destroy(); deptChart = null; }

  const depts = rows.filter(r => !isTotalRow(r))
                    .sort((a,b) => invRetail(b) - invRetail(a));

  deptChart = new Chart(canvas, {
    type: 'bar',
    data: {
      labels: depts.map(r => r.Department),
      datasets: [
        { label: 'Valor Inventario', data: depts.map(r => invRetail(r)), backgroundColor: alpha(PALETTE.primary,.8) },
      ],
    },
    options: baseOpts({
      indexAxis: 'y',
      scales: {
        x: { ticks: { callback: v => '$'+numeral(v).format('0,0') } },
        y: { grid: { display: false } },
      },
    }),
  });
}

// ── Risk half-doughnut gauge ──────────────────────────────────────────────────
function renderRiskGauge(invRows, lowRows) {
  const canvas = el('inv-risk-gauge');
  if (!canvas) return;
  if (riskGauge) { riskGauge.destroy(); riskGauge = null; }

  const deptRows  = invRows.filter(r => !isTotalRow(r));
  const totalSkus = deptRows.reduce((s,r) => s + invCount(r), 0);

  const critical = lowRows.filter(r => (parseFloat(r.CurrentStock)||0) === 0).length;
  const warning  = lowRows.filter(r => (parseFloat(r.CurrentStock)||0)  > 0).length;
  const atRisk   = critical + warning;
  const pct      = totalSkus > 0 ? Math.min((atRisk / totalSkus) * 100, 100) : 0;

  setText('inv-risk-pct',      fmtPct(pct));
  setText('inv-critical-count', `${critical} críticos`);
  setText('inv-warning-count',  `${warning} advertencia`);

  const color = pct >= 20 ? PALETTE.danger : pct >= 10 ? PALETTE.warning : PALETTE.success;

  riskGauge = new Chart(canvas, {
    type: 'doughnut',
    data: {
      datasets: [{
        data: [pct, 100 - pct, 100],
        backgroundColor: [color, '#e2e8f0', 'transparent'],
        borderWidth: 0,
        borderColor: 'transparent',
      }],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      circumference: 180,
      rotation: -90,
      cutout: '75%',
      plugins: { legend: { display: false }, tooltip: { enabled: false } },
    },
  });
}

// ── Low stock DataTable ────────────────────────────────────────────────────────
function renderLowStockTable(rows) {
  if (lowStockTable) { lowStockTable.destroy(); lowStockTable = null; }

  const tbody = document.querySelector('#inv-lowstock-table tbody');
  if (!tbody) return;

  const count = el('inv-lowstock-count');
  if (count) count.textContent = rows.length;

  tbody.innerHTML = rows.map(r => {
    const stock  = parseFloat(r.CurrentStock)  || 0;
    const minLvl = parseFloat(r.MinimumLevel)  || 0;
    const maxLvl = parseFloat(r.MaximumLevel)  || 0;
    const reorder = Math.max(maxLvl - stock, 0);
    const rowCls = stock === 0 ? 'rm-row-critical' : 'rm-row-warning';
    return `<tr class="${rowCls}">
      <td>${escHtml(r.ProductCode)}</td>
      <td>${escHtml(r.ProductName)}</td>
      <td>${escHtml(r.Department)}</td>
      <td>${escHtml(r.Category)}</td>
      <td class="text-end fw-bold ${stock===0?'text-danger':''}">${fmtNum(stock)}</td>
      <td class="text-end">${fmtNum(minLvl)}</td>
      <td class="text-end">${fmtNum(maxLvl)}</td>
      <td class="text-end text-primary fw-semibold">${fmtNum(reorder)}</td>
      <td>${escHtml(r.PrimarySupplier)}</td>
    </tr>`;
  }).join('');

  lowStockTable = new DataTable('#inv-lowstock-table', {
    pageLength: 15,
    order: [[4, 'asc']],
    dom: 'Bfrtip',
    buttons: ['excel', 'print'],
  });
}

// ── Slow movers DataTable ─────────────────────────────────────────────────────
function renderSlowTable(rows) {
  if (slowTable) { slowTable.destroy(); slowTable = null; }

  const tbody = document.querySelector('#inv-slow-table tbody');
  if (!tbody) return;

  tbody.innerHTML = rows.map(r => {
    const margin = parseFloat(r.ProfitMarginPercentage) || 0;
    const col    = margin >= 20 ? 'success' : margin >= 10 ? 'warning' : 'danger';
    return `<tr>
      <td>${escHtml(r.ProductCode)}</td>
      <td>${escHtml(r.ProductName)}</td>
      <td>${escHtml(r.Department)}</td>
      <td>${escHtml(r.Category)}</td>
      <td class="text-end">${fmtNum(r.TotalQuantitySold)}</td>
      <td class="text-end">${fmt$(r.TotalSales)}</td>
      <td class="text-end"><span class="badge bg-${col}-subtle text-${col}-emphasis">${fmtPct(margin)}</span></td>
      <td class="text-end">${fmtNum(r.CurrentStock)}</td>
    </tr>`;
  }).join('');

  slowTable = new DataTable('#inv-slow-table', {
    pageLength: 10,
    order: [[4, 'asc']],
    dom: 'Bfrtip',
    buttons: ['excel', 'print'],
  });
}

// ── Helpers ───────────────────────────────────────────────────────────────────
function el(id)        { return document.getElementById(id); }
function setText(id,v) { const e = el(id); if (e) e.textContent = v; }
function escHtml(s)    { const d = document.createElement('div'); d.textContent = s ?? ''; return d.innerHTML; }
function alpha(hex,a)  {
  const r=parseInt(hex.slice(1,3),16),g=parseInt(hex.slice(3,5),16),b=parseInt(hex.slice(5,7),16);
  return `rgba(${r},${g},${b},${a})`;
}
function baseOpts(extra={}) {
  return { responsive:true, maintainAspectRatio:false, plugins:{ legend:{ labels:{ boxWidth:12, font:{ size:11 } } } }, ...extra };
}
