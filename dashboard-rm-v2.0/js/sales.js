import { fmt$, fmtPct, fmtNum, extractApiData, showLoader, hideLoader, PALETTE, CHART_COLORS } from './app.js';

let trendChart    = null;
let deptChart     = null;
let categoryChart = null;
let paymentChart  = null;
let topTable      = null;
let currentPeriod = 'today';

function fmt(v)  { return moment(v).isValid() ? moment(v).format('YYYY-MM-DD') : v; }
function today() { return moment().format('YYYY-MM-DD'); }

function periodRange(period) {
  const m = moment();
  switch (period) {
    case 'today':  return { from: today(), to: today() };
    case 'week':   return { from: fmt(m.clone().startOf('isoWeek')), to: today() };
    case 'month':  return { from: fmt(m.clone().startOf('month')),   to: today() };
    case 'custom': {
      const from = document.getElementById('sales-date-from')?.value;
      const to   = document.getElementById('sales-date-to')?.value;
      return { from: from || today(), to: to || today() };
    }
    default: return { from: today(), to: today() };
  }
}

export async function loadSalesSection(isNew) {
  if (isNew) bindEvents();
  await refreshSales();
}

function bindEvents() {
  document.querySelectorAll('.rm-period-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('.rm-period-btn').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      currentPeriod = btn.dataset.period;
      const customRange = document.getElementById('sales-custom-range');
      if (customRange) customRange.classList.toggle('d-none', currentPeriod !== 'custom');
      if (currentPeriod !== 'custom') refreshSales();
    });
  });

  document.getElementById('sales-apply-btn')?.addEventListener('click', () => refreshSales());
  document.getElementById('sales-refresh-btn')?.addEventListener('click', async () => {
    if (currentPeriod === 'today') {
      showLoader();
      await refreshCacheSection('sales_today').catch(() => {});
      hideLoader();
    }
    refreshSales();
  });
}

async function refreshSales() {
  showLoader();
  try {
    // For "Hoy" preset, try cache first for instant render
    if (currentPeriod === 'today') {
      const cached = await fetchCachedSection('sales_today');
      if (cached) {
        const d = cached.data;
        renderKPIs(d.SalesTotals);
        renderTrendChart(extractApiData(d.SaleTrendByMonth));
        renderDeptChart(extractApiData(d.SalesByDepartment));
        renderCategoryChart(extractApiData(d.SalesByCategory));
        renderPaymentChart(extractApiData(d.SalesByMethod));
        renderTopTable(extractApiData(d.TopSellProducts));
        const ts = cached.generated_at ? cached.generated_at.slice(11, 16) : '';
        setText('sales-cache-ts', ts ? `Datos de: ${ts}` : '');
        if (cached.needs_refresh) {
          fetch('cache/refresh_cache.php?section=sales_today').catch(() => {});
        }
        return;
      }
    }

    // Live fetch for all other periods (or cache miss on today)
    const { from, to } = periodRange(currentPeriod);
    const m12from = moment().subtract(11, 'months').startOf('month').format('YYYY-MM-DD');

    const [totals, trend, dept, cat, methods, topProds] = await Promise.all([
      fetchData('SalesTotals',      { DateFrom: from, DateTo: to }),
      fetchData('SaleTrendByMonth', { DateFrom: m12from, DateTo: today() }),
      fetchData('SalesByDepartment',{ DateFrom: from, DateTo: to }),
      fetchData('SalesByCategory',  { DateFrom: from, DateTo: to }),
      fetchData('SalesByMethod',    { DateFrom: from, DateTo: to }),
      fetchData('TopSellProducts',  { DateFrom: from, DateTo: to }),
    ]);

    renderKPIs(totals);
    renderTrendChart(extractApiData(trend));
    renderDeptChart(extractApiData(dept));
    renderCategoryChart(extractApiData(cat));
    renderPaymentChart(extractApiData(methods));
    renderTopTable(extractApiData(topProds));
    if (currentPeriod === 'today') {
      setText('sales-cache-ts', 'En vivo');
      fetch('cache/refresh_cache.php?section=sales_today').catch(() => {});
    } else {
      setText('sales-cache-ts', '');
    }
  } catch (err) {
    console.error('[sales.js]', err);
  } finally {
    hideLoader();
  }
}

// ── KPIs ─────────────────────────────────────────────────────────────────────
function renderKPIs(rawData) {
  // SalesTotals may come wrapped in RESULT format — extract and take first row
  const rows = extractApiData(rawData);
  const t    = rows[0] || rawData || {};

  const sales   = parseFloat(t.TotalSales)  || 0;
  const cost    = parseFloat(t.TotalCost)   || 0;
  const profit  = sales - cost;
  const margin  = sales > 0 ? (profit / sales) * 100 : 0;
  const ticket  = parseFloat(t.AverageTicketAmount) || 0;
  const disc    = parseFloat(t.TotalDiscounts)      || 0;
  const txCount = parseInt(t.TransactionCount, 10)  || 0;

  setText('s-kpi-sales',     fmt$(sales));
  setText('s-kpi-profit',    fmt$(profit));
  setText('s-kpi-margin',    fmtPct(margin));
  setText('s-kpi-ticket',    fmt$(ticket));
  setText('s-kpi-discounts', fmt$(disc));
  setText('s-kpi-txcount',   fmtNum(txCount));
}

// ── 12-month trend ────────────────────────────────────────────────────────────
function renderTrendChart(rows) {
  const canvas = el('sales-trend-chart');
  if (!canvas) return;
  if (trendChart) { trendChart.destroy(); trendChart = null; }

  const labels = rows.map(r => r.MonthYear || `${r.Year}-${String(r.Month).padStart(2,'0')}`);
  const sales  = rows.map(r => parseFloat(r.TotalSales)  || 0);
  const profit = rows.map(r => parseFloat(r.TotalProfit) || 0);

  trendChart = new Chart(canvas, {
    type: 'line',
    data: {
      labels,
      datasets: [
        { label: 'Ventas',   data: sales,  borderColor: PALETTE.primary, backgroundColor: alpha(PALETTE.primary,.1), fill: true, tension: .4, pointRadius: 3 },
        { label: 'Ganancia', data: profit, borderColor: PALETTE.success, backgroundColor: alpha(PALETTE.success,.1), fill: true, tension: .4, pointRadius: 3 },
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

// ── Department horizontal bar ─────────────────────────────────────────────────
function renderDeptChart(rows) {
  const canvas = el('sales-dept-chart');
  if (!canvas) return;
  if (deptChart) { deptChart.destroy(); deptChart = null; }

  const sorted = [...rows].sort((a,b) => (parseFloat(b.TotalSales)||0) - (parseFloat(a.TotalSales)||0));
  const labels = sorted.map(r => r.Department || r.DepartmentName || r.DeptName || '');
  const sales  = sorted.map(r => parseFloat(r.TotalSales  || r.Sales)  || 0);
  const profit = sorted.map(r => parseFloat(r.TotalProfit || r.Profit) || 0);

  deptChart = new Chart(canvas, {
    type: 'bar',
    data: {
      labels,
      datasets: [
        { label: 'Ventas',   data: sales,  backgroundColor: alpha(PALETTE.primary,.8) },
        { label: 'Ganancia', data: profit, backgroundColor: alpha(PALETTE.success,.8) },
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

// ── Category grouped bar ──────────────────────────────────────────────────────
function renderCategoryChart(rows) {
  const canvas = el('sales-category-chart');
  if (!canvas) return;
  if (categoryChart) { categoryChart.destroy(); categoryChart = null; }

  const top10  = [...rows].sort((a,b) => (parseFloat(b.TotalSales)||0) - (parseFloat(a.TotalSales)||0)).slice(0,10);
  const labels = top10.map(r => r.CategoryName || r.Category || r.CatName || '');
  const sales  = top10.map(r => parseFloat(r.TotalSales  || r.Sales)  || 0);
  const profit = top10.map(r => parseFloat(r.TotalProfit || r.Profit) || 0);

  categoryChart = new Chart(canvas, {
    type: 'bar',
    data: {
      labels,
      datasets: [
        { label: 'Ventas',   data: sales,  backgroundColor: alpha(PALETTE.primary,.8) },
        { label: 'Ganancia', data: profit, backgroundColor: alpha(PALETTE.success,.8) },
      ],
    },
    options: baseOpts({
      scales: {
        x: { grid: { display: false }, ticks: { maxRotation: 30 } },
        y: { ticks: { callback: v => '$' + numeral(v).format('0,0') } },
      },
    }),
  });
}

// ── Payment stacked bar ───────────────────────────────────────────────────────
function renderPaymentChart(rows) {
  const canvas = el('sales-payment-chart');
  if (!canvas) return;
  if (paymentChart) { paymentChart.destroy(); paymentChart = null; }

  const labels = rows.map(r => moment(r.SaleDate).format('MM/DD'));
  const make   = (key, label, color) => ({
    label,
    data: rows.map(r => parseFloat(r[key]) || 0),
    backgroundColor: color,
    stack: 'pay',
  });

  paymentChart = new Chart(canvas, {
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

// ── Top 20 DataTable ──────────────────────────────────────────────────────────
function renderTopTable(rows) {
  if (topTable) {
    topTable.destroy();
    topTable = null;
  }
  const tbody = document.querySelector('#sales-top-products-table tbody');
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

  topTable = new DataTable('#sales-top-products-table', {
    pageLength: 10,
    order: [[5, 'desc']],
    dom: 'Bfrtip',
    buttons: ['excel', 'print', 'colvis'],
    responsive: true,
  });
}

// ── Helpers ───────────────────────────────────────────────────────────────────
function el(id)        { return document.getElementById(id); }
function setText(id, v){ const e = el(id); if (e) e.textContent = v; }
function escHtml(s)   { const d = document.createElement('div'); d.textContent = s ?? ''; return d.innerHTML; }
function alpha(hex,a) {
  const r=parseInt(hex.slice(1,3),16),g=parseInt(hex.slice(3,5),16),b=parseInt(hex.slice(5,7),16);
  return `rgba(${r},${g},${b},${a})`;
}
function baseOpts(extra={}) {
  return { responsive:true, maintainAspectRatio:false, plugins:{ legend:{ labels:{ boxWidth:12, font:{ size:11 } } } }, ...extra };
}
