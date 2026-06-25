/**
 * app.js — SPA Orchestrator
 * Exports shared utilities and manages navigation between sections.
 */

// ── Color palettes ──────────────────────────────────────────────────────────
export const PALETTE = {
  primary: '#6366f1',
  success: '#10b981',
  warning: '#f59e0b',
  danger:  '#ef4444',
  info:    '#3b82f6',
};

export const CHART_COLORS = [
  '#6366f1','#10b981','#f59e0b','#ef4444','#3b82f6',
  '#8b5cf6','#06b6d4','#84cc16','#f43f5e','#fb923c',
  '#a78bfa','#34d399','#fcd34d','#f87171','#60a5fa',
  '#c084fc','#22d3ee','#a3e635','#fb7185','#fdba74',
];

// ── Formatters ───────────────────────────────────────────────────────────────
export function fmt$(n) {
  if (n == null || n === '' || isNaN(Number(n))) return '—';
  return new Intl.NumberFormat('en-US', {
    style: 'currency', currency: 'USD', maximumFractionDigits: 2
  }).format(Number(n));
}

export function fmtPct(n) {
  if (n == null || n === '' || isNaN(Number(n))) return '—';
  return Number(n).toFixed(1) + '%';
}

export function fmtNum(n) {
  if (n == null || n === '' || isNaN(Number(n))) return '—';
  return new Intl.NumberFormat('en-US').format(Math.round(Number(n)));
}

// ── Delta renderer ────────────────────────────────────────────────────────────
export function renderDelta(deltaEl, current, previous, suffix = ' vs. sem. pasada') {
  if (!deltaEl) return;
  const curr = parseFloat(current) || 0;
  const prev = parseFloat(previous) || 0;
  if (prev === 0) {
    deltaEl.innerHTML = `<span class="delta-neutral">—</span>`;
    return;
  }
  const pct = ((curr - prev) / Math.abs(prev)) * 100;
  const up  = pct >= 0;
  deltaEl.innerHTML =
    `<span class="${up ? 'delta-up' : 'delta-down'}">` +
    `<i class="fas fa-arrow-${up ? 'up' : 'down'}"></i> ${Math.abs(pct).toFixed(1)}%</span>` +
    `<span style="color:var(--rm-muted);font-size:.73rem">${suffix}</span>`;
}

// ── API data normaliser ──────────────────────────────────────────────────────
export function extractApiData(data) {
  if (!data) return [];
  if (Array.isArray(data)) return data;
  if (data.RESULT !== undefined) {
    const r = data.RESULT;
    if (Array.isArray(r)) {
      // Format: RESULT = [[row1vals, row2vals], [colNames]]
      if (r.length >= 2 && Array.isArray(r[0]) && Array.isArray(r[1])) {
        const rows = r[0], cols = r[1];
        return rows.map(row => {
          const obj = {};
          cols.forEach((col, i) => { obj[col] = row[i]; });
          return obj;
        });
      }
      return r;
    }
    return [r];
  }
  if (typeof data === 'object') return [data];
  return [];
}

// ── Loader ───────────────────────────────────────────────────────────────────
export function showLoader() {
  const el = document.getElementById('rm-loader');
  if (el) el.classList.add('active');
}
export function hideLoader() {
  const el = document.getElementById('rm-loader');
  if (el) el.classList.remove('active');
}

// ── Section registry ─────────────────────────────────────────────────────────
const SECTION_TITLES = {
  'home-section':      'Pulso del Negocio',
  'sales-section':     'Análisis de Ventas',
  'inventory-section': 'Inventario',
  'products-section':  'Productos',
  'clients-section':   'Clientes',
  'schedule-section':  'Horario',
  'reports-section':   'Reportes',
};

const sectionInitialized = new Set();

// ── initApp ───────────────────────────────────────────────────────────────────
export function initApp() {
  // Live date in topbar
  const dateEl = document.getElementById('topbar-date');
  function updateDate() {
    if (dateEl) {
      dateEl.textContent = new Date().toLocaleDateString('es-PR', {
        weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
      });
    }
  }
  updateDate();
  setInterval(updateDate, 60000);

  // Sidebar collapse / mobile overlay
  const toggleBtn = document.getElementById('rm-sidebar-toggle');
  const overlay   = document.getElementById('rm-sidebar-overlay');
  const body      = document.body;

  if (localStorage.getItem('rm-sidebar-collapsed') === '1') {
    body.classList.add('sidebar-collapsed');
  }

  if (toggleBtn) {
    toggleBtn.addEventListener('click', () => {
      if (window.innerWidth < 768) {
        body.classList.toggle('sidebar-mobile-open');
      } else {
        const collapsed = body.classList.toggle('sidebar-collapsed');
        localStorage.setItem('rm-sidebar-collapsed', collapsed ? '1' : '0');
      }
    });
  }
  if (overlay) {
    overlay.addEventListener('click', () => body.classList.remove('sidebar-mobile-open'));
  }

  // Section navigation
  document.querySelectorAll('[data-section]').forEach(el => {
    el.addEventListener('click', e => {
      e.preventDefault();
      const sec = el.dataset.section;
      if (sec) navigateTo(sec);
      if (window.innerWidth < 768) body.classList.remove('sidebar-mobile-open');
    });
  });

  // Initial section
  navigateTo('home-section');
}

// ── navigateTo ────────────────────────────────────────────────────────────────
export async function navigateTo(sectionId) {
  // Active nav item
  document.querySelectorAll('.rm-nav-item').forEach(el => {
    el.classList.toggle('rm-nav-item--active', el.dataset.section === sectionId);
  });

  // Topbar title
  const titleEl = document.getElementById('topbar-section-title');
  if (titleEl) titleEl.textContent = SECTION_TITLES[sectionId] || '';

  // Section swap
  document.querySelectorAll('.rm-section').forEach(s => s.classList.remove('rm-section--active'));
  const sec = document.getElementById(sectionId);
  if (sec) sec.classList.add('rm-section--active');

  await loadSectionData(sectionId);
}

// ── loadSectionData ───────────────────────────────────────────────────────────
async function loadSectionData(sectionId) {
  const isNew = !sectionInitialized.has(sectionId);
  try {
    switch (sectionId) {
      case 'home-section': {
        const { loadHomeSection } = await import('./home.js');
        await loadHomeSection(isNew);
        break;
      }
      case 'sales-section': {
        const { loadSalesSection } = await import('./sales.js');
        await loadSalesSection(isNew);
        break;
      }
      case 'inventory-section': {
        const { loadInventorySection } = await import('./inventory.js');
        await loadInventorySection(isNew);
        break;
      }
      case 'products-section': {
        const { loadProductsSection } = await import('./products.js');
        await loadProductsSection(isNew);
        break;
      }
      case 'clients-section': {
        const { loadClientsSection } = await import('./clients.js');
        await loadClientsSection(isNew);
        break;
      }
      case 'schedule-section': {
        if (isNew) {
          const { loadCalendarSection } = await import('./scheduleCalendar.js');
          await loadCalendarSection();
        }
        break;
      }
      case 'reports-section': {
        const { loadReportsSection } = await import('./reports.js');
        await loadReportsSection(isNew);
        break;
      }
    }
    sectionInitialized.add(sectionId);
  } catch (err) {
    console.error(`Error loading section "${sectionId}":`, err);
    hideLoader();
  }
}
