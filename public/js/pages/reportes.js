/**
 * =========================================================================
 * REPORTES.JS - Módulo de Reportes
 * =========================================================================
 */

console.log('[Reportes] Módulo cargando...');

// =========================================================================
// TOGGLE DE FILTROS SEGÚN TIPO DE REPORTE
// =========================================================================
function toggleReportFilters() {
    const tipo = document.getElementById('reporte-tipo')?.value;
    const groupProd = document.getElementById('reporte-producto-group');
    const groupFechas = document.getElementById('reporte-fechas-group');

    if (!groupProd || !groupFechas) return;

    if (tipo === 'movimientos-producto') {
        groupProd.classList.remove('hidden');
        groupFechas.classList.remove('hidden');
    } else if (tipo === 'movimientos-general') {
        groupProd.classList.add('hidden');
        groupFechas.classList.remove('hidden');
    } else {
        groupProd.classList.add('hidden');
        groupFechas.classList.add('hidden');
    }
}

// =========================================================================
// EXPORTAR AL SCOPE GLOBAL
// =========================================================================
window.toggleReportFilters = toggleReportFilters;
