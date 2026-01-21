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

// NOTE: escapeHTML is provided by utils.js - no need for duplicate definition

// =========================================================================
// INICIALIZACIÓN POR PÁGINA
// =========================================================================
function inicializarPaginaActual() {
    console.log('[App] Inicializando lógica de página específica...');

    // POS (Punto de Venta)
    if (document.getElementById('pos-container') || document.getElementById('pos-buscador')) {
        if (typeof window.inicializarPOS === 'function') {
            window.inicializarPOS();
        }
    }

    // Compras (Crear)
    if (document.getElementById('compra-buscador') || document.getElementById('cuerpo-compra')) {
        if (typeof window.inicializarCompras === 'function') {
            window.inicializarCompras();
        }
    }

    // Proveedores
    if (document.getElementById('tabla-proveedores') || document.getElementById('form-editar-proveedor')) {
        if (typeof window.inicializarProveedores === 'function') {
            window.inicializarProveedores();
        }
    }

    // Productos (Inventario)
    if (document.getElementById('tabla-inventario') || document.getElementById('busqueda-input')) {
        if (typeof window.inicializarProductos === 'function') {
            window.inicializarProductos();
        }
    }

    // Clientes
    if (document.getElementById('tabla-clientes')) {
        if (typeof window.inicializarClientes === 'function') {
            window.inicializarClientes();
        }
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

        // Re-aplicar tasa y re-init página al navegar con Turbo
        window.addEventListener('app:page-loaded', () => {
            console.log('[TurboNav] app:page-loaded recibido');

            if (ExchangeRate.tasa > 0) ExchangeRate.reapply();

            // Re-cargar stats del footer
            if (typeof initFooterStats === 'function') {
                initFooterStats();
            }

            // RE-INICIALIZAR LÓGICA DE PÁGINA ESPECÍFICA
            inicializarPaginaActual();
        });

        // Suscribirse a cambios de tasa para actualizar UI global
        Store.subscribe('tasa', (nuevaTasa) => {
            if (typeof window.actualizarPreciosVES === 'function') {
                window.actualizarPreciosVES(nuevaTasa);
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
