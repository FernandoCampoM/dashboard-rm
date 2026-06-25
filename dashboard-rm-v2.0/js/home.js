import { fmt$, fmtPct, fmtNum, renderDelta, extractApiData, showLoader, hideLoader, PALETTE, CHART_COLORS } from './app.js';

// Chart instances (module-level so we can destroy/recreate)
let trendChart   = null;
let paymentChart = null;
let hourlyChart  = null;

function todayStr()    { return moment().format('YYYY-MM-DD'); }
function lastWeekStr() { return moment().subtract(7, 'days').format('YYYY-MM-DD'); }
function monthsAgoStr(n) { return moment().subtract(n, 'months').startOf('month').format('YYYY-MM-DD'); }

export async function loadHomeSection(isNew) {
  if (isNew) {
    document.getElementById('home-refresh-btn')
      ?.addEventListener('click', doHomeRefresh);
  }
  showLoader();
  try {
    const cached = await fetchCachedSection('home');
    if (cached) {
      renderFromCache(cached);
      if (cached.needs_refresh) {
        // Background update; next Actualize or load will find fresh cache
        fetch('cache/refresh_cache.php?section=home').catch(() => {});
      }
      return;
    }
    // Cache miss → live fetch, then seed cache in background
    await loadHomeLive();
    fetch('cache/refresh_cache.php?section=home').catch(() => {});
  } catch (err) {
    console.error('[home.js]', err);
  } finally {
    hideLoader();
  }
}

// Force-refresh cache then re-render (Actualize button handler)
async function doHomeRefresh() {
  const btn = document.getElementById('home-refresh-btn');
  if (btn) btn.disabled = true;
  showLoader();
  try {
    await refreshCacheSection('home');
    const cached = await fetchCachedSection('home');
    if (cached) { renderFromCache(cached); return; }
    await loadHomeLive();
  } finally {
    if (btn) btn.disabled = false;
    hideLoader();
  }
}

// Render all home widgets from a cache object
function renderFromCache(cached) {
  const d = cached.data;
  renderKPIs(d.SalesTotals_today, d.SalesTotals_lw);
  renderTrendChart(extractApiData(d.SaleTrendByMonth));
  renderPaymentDonut(extractApiData(d.SalesByMethod_today));
  renderTop5(extractApiData(d.TopSellProducts_today));
  renderLowStock(extractApiData(d.LowLevelItems));
  renderHourlyChart(extractApiData(d.SalesByHour_today));
  const ts = cached.generated_at ? cached.generated_at.slice(11, 16) : '';
  setText('home-cache-ts', ts ? `Datos de: ${ts}` : '');
}

// Live fetch path (cache miss or cache unavailable)
async function loadHomeLive() {
  const td  = todayStr();
  const lw  = lastWeekStr();
  const m12 = monthsAgoStr(11);

  const [totToday, totLastWk, trend, methods, top5, lowStock, hourly] = await Promise.all([
    fetchData('SalesTotals',     { DateFrom: td,  DateTo: td }),
    fetchData('SalesTotals',     { DateFrom: lw,  DateTo: lw }),
    fetchData('SaleTrendByMonth',{ DateFrom: m12, DateTo: td }),
    fetchData('SalesByMethod',   { DateFrom: td,  DateTo: td }),
    fetchData('TopSellProducts', { DateFrom: td,  DateTo: td }),
    fetchData('LowLevelItems',   { Active: 1 }),
    fetchData('SalesByHour',     { DateFrom: td,  DateTo: td }),
  ]);

  renderKPIs(totToday, totLastWk);
  renderTrendChart(extractApiData(trend));
  renderPaymentDonut(extractApiData(methods));
  renderTop5(extractApiData(top5));
  renderLowStock(extractApiData(lowStock));
  renderHourlyChart(extractApiData(hourly));
  setText('home-cache-ts', 'En vivo');
}

// ── KPIs ─────────────────────────────────────────────────────────────────────
function renderKPIs(rawT, rawLw) {
  const todayRows = extractApiData(rawT);
  const lwRows    = extractApiData(rawLw);
  const today    = todayRows[0] || rawT    || {};
  const lastWeek = lwRows[0]   || rawLw   || {};

  const sales   = parseFloat(today.TotalSales)  || 0;
  const cost    = parseFloat(today.TotalCost)   || 0;
  const profit  = sales - cost;
  const margin  = sales > 0 ? (profit / sales) * 100 : 0;
  const ticket  = parseFloat(today.AverageTicketAmount) || 0;
  const txCount = parseInt(today.TransactionCount, 10)  || 0;

  const lwSales   = parseFloat(lastWeek.TotalSales)  || 0;
  const lwCost    = parseFloat(lastWeek.TotalCost)   || 0;
  const lwProfit  = lwSales - lwCost;
  const lwMargin  = lwSales > 0 ? (lwProfit / lwSales) * 100 : 0;
  const lwTicket  = parseFloat(lastWeek.AverageTicketAmount) || 0;
  const lwTxCount = parseInt(lastWeek.TransactionCount, 10)  || 0;

  setText('kpi-sales-value',   fmt$(sales));
  setText('kpi-profit-value',  fmt$(profit));
  setText('kpi-margin-value',  fmtPct(margin));
  setText('kpi-ticket-value',  fmt$(ticket));
  setText('kpi-txcount-value', fmtNum(txCount));

  renderDelta(el('kpi-sales-delta'),   sales,   lwSales);
  renderDelta(el('kpi-profit-delta'),  profit,  lwProfit);
  renderDelta(el('kpi-margin-delta'),  margin,  lwMargin, ' vs. sem. pasada');
  renderDelta(el('kpi-ticket-delta'),  ticket,  lwTicket);
  renderDelta(el('kpi-txcount-delta'), txCount, lwTxCount);
}

// ── Trend area chart (12 months) ─────────────────────────────────────────────
function renderTrendChart(rows) {
  const canvas = el('home-trend-chart');
  if (!canvas) return;
  if (trendChart) { trendChart.destroy(); trendChart = null; }

  const labels  = rows.map(r => r.MonthYear || `${r.Year}-${String(r.Month).padStart(2,'0')}`);
  const sales   = rows.map(r => parseFloat(r.TotalSales)  || 0);
  const profits = rows.map(r => parseFloat(r.TotalProfit) || 0);

  trendChart = new Chart(canvas, {
    type: 'line',
    data: {
      labels,
      datasets: [
        {
          label: 'Ventas',
          data: sales,
          borderColor: PALETTE.primary,
          backgroundColor: hexAlpha(PALETTE.primary, 0.12),
          fill: true, tension: 0.4, pointRadius: 3,
        },
        {
          label: 'Ganancia',
          data: profits,
          borderColor: PALETTE.success,
          backgroundColor: hexAlpha(PALETTE.success, 0.12),
          fill: true, tension: 0.4, pointRadius: 3,
        },
      ],
    },
    options: chartDefaults({
      scales: {
        x: { grid: { display: false } },
        y: { ticks: { callback: v => '$' + numeral(v).format('0,0') } },
      },
    }),
  });
}

// ── Payment donut ─────────────────────────────────────────────────────────────
function renderPaymentDonut(rows) {
  const canvas = el('home-payment-donut');
  if (!canvas) return;
  if (paymentChart) { paymentChart.destroy(); paymentChart = null; }

  // Aggregate across all rows (today should be 1 row, but guard anyway)
  const agg = { Efectivo: 0, Crédito: 0, Débito: 0, Cheque: 0, 'ATH Móvil': 0 };
  rows.forEach(r => {
    agg['Efectivo']  += parseFloat(r.CashPayments)       || 0;
    agg['Crédito']   += parseFloat(r.CreditCardPayments) || 0;
    agg['Débito']    += parseFloat(r.DebitCardPayments)  || 0;
    agg['Cheque']    += parseFloat(r.CheckPayments)      || 0;
    agg['ATH Móvil'] += parseFloat(r.AthMovilPayments)   || 0;
  });

  const labels = Object.keys(agg).filter(k => agg[k] > 0);
  const values = labels.map(k => agg[k]);
  const colors = [PALETTE.primary, PALETTE.info, PALETTE.success, PALETTE.warning, PALETTE.danger];

  paymentChart = new Chart(canvas, {
    type: 'doughnut',
    data: {
      labels,
      datasets: [{ data: values, backgroundColor: colors, borderWidth: 2, borderColor: '#fff' }],
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      cutout: '68%',
      plugins: {
        legend: { display: false },
        tooltip: { callbacks: { label: ctx => ` ${ctx.label}: ${fmt$(ctx.raw)}` } },
      },
    },
  });

  // Inline legend
  const legend = el('home-payment-legend');
  if (legend) {
    legend.innerHTML = labels.map((l, i) =>
      `<span style="display:inline-flex;align-items:center;gap:.3rem;margin:.15rem .4rem">` +
      `<span style="width:10px;height:10px;border-radius:50%;background:${colors[i]};display:inline-block"></span>${l}</span>`
    ).join('');
  }
}

// ── Top 5 table ───────────────────────────────────────────────────────────────
function renderTop5(rows) {
  const tbody = el('home-top5-body');
  if (!tbody) return;

  const top = rows.slice(0, 5);
  if (!top.length) {
    tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-3">Sin datos para hoy</td></tr>';
    return;
  }

  tbody.innerHTML = top.map((r, i) => {
    const margin = parseFloat(r.ProfitMarginPercentage) || 0;
    const badgeColor = margin >= 30 ? 'success' : margin >= 15 ? 'warning' : 'danger';
    return `<tr>
      <td><span class="badge bg-light text-dark me-2">${i + 1}</span>${escHtml(r.ProductName)}</td>
      <td class="text-end">${fmtNum(r.TotalQuantitySold)}</td>
      <td class="text-end">${fmt$(r.TotalSales)}</td>
      <td class="text-end"><span class="badge bg-${badgeColor}-subtle text-${badgeColor}-emphasis">${fmtPct(margin)}</span></td>
    </tr>`;
  }).join('');
}

// ── Low stock alert list ───────────────────────────────────────────────────────
function renderLowStock(rows) {
  const badge = el('home-lowstock-badge');
  const list  = el('home-lowstock-list');
  if (!list) return;

  if (badge) badge.textContent = rows.length;

  if (!rows.length) {
    list.innerHTML = '<li class="text-center text-muted py-3"><i class="fas fa-check-circle text-success me-1"></i>Sin artículos bajo mínimo</li>';
    return;
  }

  // Sort: stock=0 first (critical), then by CurrentStock asc
  const sorted = [...rows].sort((a, b) => {
    const aStock = parseFloat(a.CurrentStock) || 0;
    const bStock = parseFloat(b.CurrentStock) || 0;
    if (aStock === 0 && bStock !== 0) return -1;
    if (bStock === 0 && aStock !== 0) return 1;
    return aStock - bStock;
  });

  list.innerHTML = sorted.slice(0, 10).map(r => {
    const stock    = parseFloat(r.CurrentStock)  || 0;
    const minLevel = parseFloat(r.MinimumLevel)  || 0;
    const isCrit   = stock === 0;
    return `<li class="${isCrit ? 'rm-alert-critical' : 'rm-alert-warning'}">
      <strong>${escHtml(r.ProductName)}</strong>
      <span class="float-end badge ${isCrit ? 'bg-danger' : 'bg-warning text-dark'}">${stock} / ${minLevel} mín</span>
      <div class="text-muted" style="font-size:.72rem">${escHtml(r.Department)}</div>
    </li>`;
  }).join('');
}

// ── Hourly bar chart ──────────────────────────────────────────────────────────
function renderHourlyChart(rows) {
  const canvas = el('home-hourly-chart');
  if (!canvas) return;
  if (hourlyChart) { hourlyChart.destroy(); hourlyChart = null; }

  // Build 24-slot array (fill missing hours with 0)
  const byHour = Array.from({ length: 24 }, (_, h) => {
    const found = rows.find(r => parseInt(r.HourOfDay, 10) === h);
    return found ? (parseFloat(found.TotalSales) || 0) : 0;
  });
  const labels = Array.from({ length: 24 }, (_, h) => `${String(h).padStart(2,'0')}:00`);

  hourlyChart = new Chart(canvas, {
    type: 'bar',
    data: {
      labels,
      datasets: [{
        label: 'Ventas ($)',
        data: byHour,
        backgroundColor: hexAlpha(PALETTE.primary, 0.75),
        borderColor: PALETTE.primary,
        borderWidth: 1,
        borderRadius: 4,
      }],
    },
    options: chartDefaults({
      scales: {
        x: { grid: { display: false } },
        y: { ticks: { callback: v => '$' + numeral(v).format('0,0') } },
      },
    }),
  });
}

// ── Helpers ───────────────────────────────────────────────────────────────────
function el(id)      { return document.getElementById(id); }
function setText(id, v) { const e = el(id); if (e) e.textContent = v; }
function escHtml(s)  { const d = document.createElement('div'); d.textContent = s ?? ''; return d.innerHTML; }
function hexAlpha(hex, a) {
  const r = parseInt(hex.slice(1,3),16), g = parseInt(hex.slice(3,5),16), b = parseInt(hex.slice(5,7),16);
  return `rgba(${r},${g},${b},${a})`;
}

function chartDefaults(extra = {}) {
  return {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { labels: { boxWidth: 12, font: { size: 11 } } }, tooltip: {} },
    ...extra,
  };
}
