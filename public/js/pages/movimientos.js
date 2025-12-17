/**
 * =========================================================================
 * MOVIMIENTOS.JS - Módulo de Movimientos de Stock
 * =========================================================================
 */

console.log('[Movimientos] Módulo cargando...');

// =========================================================================
// INICIALIZACIÓN PRINCIPAL
// =========================================================================
function initMovimientos() {
    console.log('[Movimientos] Init started...');

    // Verificar si estamos en la página de movimientos
    if (!document.getElementById('form-movimiento')) {
        console.log('[Movimientos] No es página de movimientos, saltando');
        return;
    }

    // 1. Load Data safely
    try {
        const dataEl = document.getElementById('productos-data');
        if (dataEl && dataEl.textContent) {
            window.listaProductosMov = JSON.parse(dataEl.textContent);
            console.log('[Movimientos] Loaded products:', window.listaProductosMov.length);
        } else {
            console.warn('[Movimientos] No product data found in DOM');
            window.listaProductosMov = [];
        }
    } catch (e) {
        console.error('[Movimientos] JSON Parse Error:', e);
        window.listaProductosMov = [];
    }

    // 2. Setup Producto Combobox
    if (window.setupCombobox && window.listaProductosMov && window.listaProductosMov.length > 0) {
        console.log('[Movimientos] Setting up combobox...');
        window.setupCombobox(
            'combobox-producto-mov',
            'mov-producto-hidden',
            'producto-input-visual',
            'producto-list-mov',
            'btn-limpiar-prod-mov',
            {
                dataSource: window.listaProductosMov,
                defaultLabel: 'Selecciona un producto...'
            }
        );
    } else {
        console.warn('[Movimientos] Setup skipped. setupCombobox:', !!window.setupCombobox, 'Data:', window.listaProductosMov?.length);
    }

    // 3. Setup Event Listeners (Radio Buttons)
    const radios = document.querySelectorAll('input[name="mov-tipo"]');
    radios.forEach(r => r.addEventListener('change', actualizarMotivos));

    // 4. Load & Setup Proveedores
    try {
        const provDataEl = document.getElementById('proveedores-data');
        if (provDataEl && provDataEl.textContent) {
            window.listaProveedoresMov = JSON.parse(provDataEl.textContent);

            if (window.setupCombobox) {
                window.setupCombobox(
                    'combobox-proveedor-mov',
                    'mov-proveedor-hidden',
                    'proveedor-input-visual',
                    'proveedor-list-mov',
                    'btn-limpiar-prov-mov',
                    {
                        dataSource: window.listaProveedoresMov,
                        defaultLabel: 'Buscar proveedor...'
                    }
                );
            }
        }
    } catch (e) { console.error('Error init proveedores:', e); }

    console.log('[Movimientos] Módulo inicializado ✓');
}

// =========================================================================
// ACTUALIZAR MOTIVOS SEGÚN TIPO
// =========================================================================
function actualizarMotivos() {
    const tipo = document.querySelector('input[name="mov-tipo"]:checked')?.value;
    const select = document.getElementById('mov-motivo');
    const groupProv = document.getElementById('mov-proveedor-group');

    if (!select) return;

    select.innerHTML = '<option value="">Selecciona...</option>';

    if (tipo === 'Entrada') {
        const options = [
            'Compra', 'Devolución Proveedor', 'Ajuste Positivo', 'Inventario Inicial'
        ];
        options.forEach(opt => {
            select.add(new Option(opt, opt));
        });
        groupProv?.classList.remove('hidden');
    } else if (tipo === 'Salida') {
        const options = [
            'Venta', 'Pérdida/Daño', 'Devolución Cliente', 'Ajuste Negativo', 'Consumo Interno'
        ];
        options.forEach(opt => {
            select.add(new Option(opt, opt));
        });
        groupProv?.classList.add('hidden');
    }

    // Trigger change to update simple-select
    select.dispatchEvent(new Event('change'));
}

// =========================================================================
// EXPORTAR AL SCOPE GLOBAL
// =========================================================================
window.actualizarMotivos = actualizarMotivos;

// Inicializar
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initMovimientos);
} else {
    initMovimientos();
}
