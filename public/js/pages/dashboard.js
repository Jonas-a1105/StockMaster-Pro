/**
 * =========================================================================
 * DASHBOARD.JS - Módulo del Dashboard
 * =========================================================================
 */

console.log('[Dashboard] Módulo cargando...');

// Variable global para el gráfico
window.chartVentasPeriodo = window.chartVentasPeriodo || null;

// =========================================================================
// INICIALIZACIÓN
// =========================================================================
function initDashboard() {
    console.log('[Dashboard] Inicializando...');

    // Verificar si estamos en el dashboard
    if (!document.getElementById('chartVentasPeriodo')) {
        console.log('[Dashboard] No es página dashboard, saltando');
        return;
    }

    // Cargar gráficos usando el helper global si existe
    if (typeof actualizarCharts === 'function') {
        actualizarCharts();
    }

    // Cargar gráfico específico de ventas por periodo
    cargarGraficoVentas(7);

    // Configurar listener para cambio de periodo
    const selector = document.getElementById('periodo-ventas');
    if (selector) {
        selector.addEventListener('change', (e) => {
            cargarGraficoVentas(parseInt(e.target.value));
        });
    }

    // Escuchar cambios en tasa de cambio (Reactividad)
    if (window.Store) {
        Store.subscribe('tasa', (nuevaTasa) => {
            actualizarKpisDashboard(nuevaTasa);
        });

        // Carga inicial
        if (Store.tasa > 0) {
            actualizarKpisDashboard(Store.tasa);
        }
    }

    console.log('[Dashboard] Módulo inicializado ✓');
}

function actualizarKpisDashboard(tasa) {
    const kpis = [
        { id: 'kpi-valor-usd', label: 'Valor Inventario' },
        { id: 'kpi-costo-usd', label: 'Costo Total' },
        { id: 'kpi-ganancia-usd', label: 'Ganancia Potencial' }
    ];

    kpis.forEach(item => {
        const el = document.getElementById(item.id);
        if (el && el.dataset.rawValue) {
            const valUsd = parseFloat(el.dataset.rawValue);
            const valVes = valUsd * tasa;

            // Verificar si ya existe el elemento de VES, si no crearlo
            let vesEl = el.parentElement.querySelector('.kpi-ves-value');
            if (!vesEl) {
                vesEl = document.createElement('p');
                vesEl.className = 'text-xs text-slate-500 dark:text-slate-400 font-mono mt-1 kpi-ves-value';
                el.parentElement.appendChild(vesEl);
            }
            vesEl.textContent = `Bs. ${valVes.toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
        }
    });
}

// =========================================================================
// GRÁFICO DE VENTAS POR PERÍODO
// =========================================================================
async function cargarGraficoVentas(dias) {
    const canvas = document.getElementById('chartVentasPeriodo');
    if (!canvas) return;

    // Asegurar que Chart.js esté disponible
    if (typeof Chart === 'undefined') {
        if (typeof ensureChartJS === 'function') {
            const loaded = await ensureChartJS();
            if (!loaded) {
                console.warn('No se pudo cargar Chart.js');
                return;
            }
        } else {
            console.warn('Chart.js no está cargado.');
            return;
        }
    }

    try {
        const data = await Endpoints.ventasPeriodo(dias);

        if (!Array.isArray(data)) {
            console.error('Error: Datos recibidos no son un array', data);
            return;
        }

        const labels = data.map(d => d.label);
        const valores = data.map(d => d.total_usd || 0);
        const numVentas = data.map(d => d.num_ventas || 0);

        if (window.chartVentasPeriodo && typeof window.chartVentasPeriodo.destroy === 'function') {
            window.chartVentasPeriodo.destroy();
        }

        const isDark = document.documentElement.classList.contains('dark');
        const gridColor = isDark ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.05)';
        const textColor = isDark ? '#94a3b8' : '#64748b';

        window.chartVentasPeriodo = new Chart(canvas.getContext('2d'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Ventas ($)',
                    data: valores,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: '#10b981',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: isDark ? '#1e293b' : '#fff',
                        titleColor: isDark ? '#fff' : '#1e293b',
                        bodyColor: isDark ? '#94a3b8' : '#64748b',
                        borderColor: isDark ? '#334155' : '#e2e8f0',
                        borderWidth: 1,
                        padding: 12,
                        cornerRadius: 8,
                        callbacks: {
                            label: (ctx) => `$${ctx.raw.toFixed(2)}`,
                            afterLabel: (ctx) => `${numVentas[ctx.dataIndex]} ventas`
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: gridColor },
                        ticks: {
                            color: textColor,
                            callback: (value) => '$' + value
                        }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { color: textColor }
                    }
                }
            }
        });
    } catch (error) {
        console.error('Error cargando gráfico ventas:', error);
    }
}

// =========================================================================
// EXPORTAR AL SCOPE GLOBAL
// =========================================================================
window.cargarGraficoVentas = cargarGraficoVentas;

// Inicializar
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initDashboard);
} else {
    initDashboard();
}
