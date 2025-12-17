/**
 * =========================================================================
 * APP.JS - Inicializador Principal
 * =========================================================================
 * Sistema de Gestión de Inventario & POS (SaaS Pro)
 * 
 * Este archivo SOLO contiene:
 * - Variables globales
 * - Configuración inicial
 * - Inicialización de módulos
 * 
 * Los módulos están en:
 * - /js/core/     -> Utilidades base (utils, notifications, api)
 * - /js/modules/  -> Módulos funcionales (turbo-nav, pos, compras, etc)
 */

console.log('APP.JS v2.0 - Sistema modular cargado');

// =========================================================================
// CONFIGURACIÓN GLOBAL
// =========================================================================

// Configurar jsPDF si está disponible
if (window.jspdf) {
    window.jsPDF = window.jspdf.jsPDF;
}

// Variables globales de carrito (usadas por módulos)
window.carritoPOS = window.carritoPOS || [];
window.carritoCompra = window.carritoCompra || [];

// =========================================================================
// HELPER GLOBAL: escapeHTML
// =========================================================================
window.escapeHTML = function (str) {
    if (str === null || str === undefined) return '';
    return String(str).replace(/[&<>"']/g, function (m) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m];
    });
};

// =========================================================================
// INICIALIZACIÓN POR PÁGINA
// =========================================================================
function inicializarPaginaActual() {
    // POS (Punto de Venta)
    if (document.getElementById('pos-buscador') && typeof window.inicializarPOS === 'function') {
        window.inicializarPOS();
    }

    // Compras
    if (document.getElementById('compra-buscador') && typeof window.inicializarCompras === 'function') {
        window.inicializarCompras();
    }

    // Proveedores
    if (document.getElementById('tabla-proveedores') && typeof window.inicializarProveedores === 'function') {
        window.inicializarProveedores();
    }

    // Productos (modales de agregar/editar)
    if (document.getElementById('modal-agregar-producto') && typeof window.inicializarModalesProducto === 'function') {
        window.inicializarModalesProducto();
    }
}

// Exponer globalmente
window.inicializarPaginaActual = inicializarPaginaActual;

// =========================================================================
// INICIALIZACIÓN PRINCIPAL
// =========================================================================
document.addEventListener('DOMContentLoaded', () => {
    console.log('DOMContentLoaded - Inicializando sistema...');

    // === 0. INICIALIZAR TURBO NAV ===
    if (typeof window.initTurboNav === 'function') {
        window.initTurboNav();
    }

    // === 1. INICIALIZAR MÓDULOS CORE ===
    if (window.Notifications) {
        Notifications.initFlash();
        Notifications.initCenter();
    }

    if (window.Modals) {
        Modals.init();
    }

    if (window.Theme) {
        Theme.init();
    }

    // === 2. INICIALIZAR TASA DE CAMBIO ===
    if (window.ExchangeRate) {
        ExchangeRate.init();
        ExchangeRate.configurarManual();

        // Re-aplicar tasa al navegar con Turbo
        window.addEventListener('app:page-loaded', () => {
            if (ExchangeRate.tasa > 0) ExchangeRate.reapply();

            // Re-cargar stats del footer
            if (typeof initFooterStats === 'function') {
                initFooterStats();
            }
        });
    }

    // === 3. INICIALIZAR GRÁFICOS (si estamos en dashboard) ===
    if (document.getElementById('chartValorCategoria') && window.Charts) {
        Charts.update();
    }

    // === 4. INICIALIZAR REPORTES (si estamos en reportes) ===
    if (document.getElementById('reporte-tipo') && window.Reports) {
        Reports.configurarSelector();
    }

    // === 5. CONFIGURAR ALERTAS DE STOCK ===
    if (typeof window.configurarAlertasStock === 'function') {
        window.configurarAlertasStock();
    }

    // === 6. INICIALIZAR LÓGICA DE PÁGINA ESPECÍFICA ===
    inicializarPaginaActual();

    console.log('Sistema inicializado correctamente ✓');
});
