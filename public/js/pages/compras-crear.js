/**
 * =========================================================================
 * COMPRAS-CREAR.JS - Módulo para Crear Compras
 * =========================================================================
 * Lógica completa para crear nuevas compras:
 * - Carrito de compras
 * - Búsqueda de productos
 * - Selección de proveedor (combobox)
 * - Guardado de compras
 * 
 * Dependencias globales:
 * - window.listaProveedores (cargado desde JSON en la vista)
 * - window.setupCombobox (de combobox.js)
 */

console.log('[ComprasCrear] Módulo cargando...');

// =========================================================================
// CARRITO GLOBAL
// =========================================================================
window.carritoCompra = window.carritoCompra || [];

// =========================================================================
// VARIABLES DE ESTADO
// =========================================================================
var searchTimer = null;

// =========================================================================
// FUNCIONES DE CARRITO
// =========================================================================
function agregarProductoCompra(producto) {
    const existente = window.carritoCompra.find(i => i.id === producto.id);
    if (existente) {
        existente.cantidad++;
    } else {
        window.carritoCompra.push({
            id: producto.id,
            nombre: producto.nombre,
            costo: parseFloat(producto.precioCompraUSD || 0),
            cantidad: 1
        });
    }
    renderizarTablaCompra();

    const resultados = document.getElementById('compra-resultados');
    const buscador = document.getElementById('compra-buscador');
    if (resultados) resultados.classList.add('hidden');
    if (buscador) buscador.value = '';

    showToast(`${producto.nombre} agregado`, 'success');
}

function renderizarTablaCompra() {
    const tbody = document.getElementById('cuerpo-compra');
    if (!tbody) return;

    const isEmpty = window.carritoCompra.length === 0;

    if (isEmpty) {
        tbody.innerHTML = `
            <tr id="row-empty">
                <td colspan="5" class="px-4 py-12 text-center text-slate-400">
                    Busca productos para agregar
                </td>
            </tr>
        `;
        actualizarResumen(0, 0, 0);
        return;
    }

    let total = 0;
    let totalItems = 0;

    tbody.innerHTML = window.carritoCompra.map((item, index) => {
        const subtotal = item.costo * item.cantidad;
        total += subtotal;
        totalItems += item.cantidad;

        return `
            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-600/30">
                <td class="px-4 py-3 font-medium text-slate-800 dark:text-white">${escapeHTML(item.nombre)}</td>
                <td class="px-4 py-3 text-center">
                    <input type="number" 
                           class="w-16 px-2 py-1.5 bg-slate-100 dark:bg-slate-600 border-0 rounded-lg text-center font-semibold text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/30"
                           value="${item.cantidad}" 
                           min="1"
                           onchange="actualizarCantidadCompra(${index}, this.value)">
                </td>
                <td class="px-4 py-3 text-right">
                    <input type="number" 
                           class="w-20 px-2 py-1.5 bg-slate-100 dark:bg-slate-600 border-0 rounded-lg text-right font-mono text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/30"
                           value="${item.costo.toFixed(2)}" 
                           step="0.01"
                           min="0"
                           onchange="actualizarCostoCompra(${index}, this.value)">
                </td>
                <td class="px-4 py-3 text-right font-mono font-semibold text-emerald-600 dark:text-emerald-400">$${subtotal.toFixed(2)}</td>
                <td class="px-4 py-3 text-center">
                    <button onclick="eliminarItemCompra(${index})" class="p-1.5 text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </td>
            </tr>
        `;
    }).join('');

    const totalDisplay = document.getElementById('compra-total-display');
    if (totalDisplay) totalDisplay.textContent = `$${total.toFixed(2)}`;

    actualizarResumen(window.carritoCompra.length, totalItems, total);
}

function actualizarResumen(items, cantidad, total) {
    const elItems = document.getElementById('resumen-items');
    const elCantidad = document.getElementById('resumen-cantidad');
    const elTotal = document.getElementById('resumen-total');

    if (elItems) elItems.textContent = items;
    if (elCantidad) elCantidad.textContent = cantidad;
    if (elTotal) elTotal.textContent = `$${total.toFixed(2)}`;
}

function actualizarCantidadCompra(index, value) {
    const cantidad = parseInt(value);
    if (cantidad > 0) {
        window.carritoCompra[index].cantidad = cantidad;
        renderizarTablaCompra();
    }
}

function actualizarCostoCompra(index, value) {
    const costo = parseFloat(value);
    if (costo >= 0) {
        window.carritoCompra[index].costo = costo;
        renderizarTablaCompra();
    }
}

function eliminarItemCompra(index) {
    window.carritoCompra.splice(index, 1);
    renderizarTablaCompra();
}

// =========================================================================
// BÚSQUEDA DE PRODUCTOS
// =========================================================================
function initBusquedaProductos() {
    const buscador = document.getElementById('compra-buscador');
    const resultados = document.getElementById('compra-resultados');

    if (!buscador || !resultados) return;

    buscador.addEventListener('input', (e) => {
        clearTimeout(searchTimer);
        const term = e.target.value.trim();

        if (term.length < 2) {
            resultados.innerHTML = '';
            resultados.classList.add('hidden');
            return;
        }

        searchTimer = setTimeout(async () => {
            try {
                const res = await fetch(`index.php?controlador=compra&accion=buscarProductos&term=${encodeURIComponent(term)}`);
                const productos = await res.json();

                resultados.innerHTML = '';

                if (productos.length === 0) {
                    resultados.innerHTML = '<div class="px-4 py-3 text-center text-sm text-slate-400">No se encontraron productos</div>';
                } else {
                    productos.forEach(p => {
                        const item = document.createElement('div');
                        item.className = 'flex items-center gap-3 px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-600 cursor-pointer transition-colors border-b last:border-0 border-slate-100 dark:border-slate-600';
                        item.innerHTML = `
                            <div class="flex-1">
                                <p class="font-medium text-slate-800 dark:text-white text-sm">${escapeHTML(p.nombre)}</p>
                                <p class="text-xs text-slate-400">Costo: $${parseFloat(p.precioCompraUSD || 0).toFixed(2)}</p>
                            </div>
                            <svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                        `;
                        item.onclick = () => agregarProductoCompra(p);
                        resultados.appendChild(item);
                    });
                }
                resultados.classList.remove('hidden');
            } catch (e) {
                console.error('Error buscando productos:', e);
            }
        }, 300);
    });

    // Cerrar resultados al hacer clic fuera
    document.addEventListener('click', (e) => {
        if (!buscador.contains(e.target) && !resultados.contains(e.target)) {
            resultados.classList.add('hidden');
        }
    });
}

// =========================================================================
// CONDICIÓN DE PAGO
// =========================================================================
function initCondicionListener() {
    const condicion = document.getElementById('compra-condicion');
    const divVencimiento = document.getElementById('div-vencimiento');

    if (condicion && divVencimiento) {
        condicion.addEventListener('change', (e) => {
            divVencimiento.classList.toggle('hidden', e.target.value !== 'Credito');
        });
    }
}

// =========================================================================
// GUARDAR COMPRA
// =========================================================================
async function guardarCompra() {
    const btn = document.getElementById('btn-guardar-compra');

    try {
        const provId = document.getElementById('compra-proveedor')?.value;
        if (!provId) {
            showToast('Selecciona un proveedor', 'error');
            return;
        }

        if (window.carritoCompra.length === 0) {
            showToast('Agrega productos a la compra', 'error');
            return;
        }

        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Guardando...';
        }

        const condicion = document.getElementById('compra-condicion')?.value || 'Contado';
        const fecha = document.getElementById('compra-fecha')?.value || '';

        const payload = {
            proveedor_id: provId,
            nro_factura: document.getElementById('compra-factura')?.value || '',
            fecha_emision: fecha,
            fecha_vencimiento: condicion === 'Credito'
                ? (document.getElementById('compra-vencimiento')?.value || fecha)
                : fecha,
            estado: condicion === 'Credito' ? 'Pendiente' : 'Pagada',
            carrito: window.carritoCompra
        };

        const res = await fetch('index.php?controlador=compra&accion=guardar', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        const data = await res.json();

        if (data.success) {
            showToast('Compra registrada con éxito', 'success');
            setTimeout(() => window.location.href = 'index.php?controlador=compra&accion=index', 1000);
        } else {
            throw new Error(data.message || 'Error al guardar');
        }
    } catch (e) {
        showToast(e.message, 'error');
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Guardar Compra';
        }
    }
}

// =========================================================================
// COMBOBOX DE PROVEEDORES
// =========================================================================
function initComboboxProveedor() {
    // Cargar datos desde el elemento JSON
    try {
        const dataElement = document.getElementById('proveedores-data');
        if (dataElement) {
            window.listaProveedores = JSON.parse(dataElement.textContent);
            console.log('[ComprasCrear] Proveedores cargados:', window.listaProveedores.length);
        } else {
            console.error('[ComprasCrear] Elemento proveedores-data no encontrado');
            window.listaProveedores = [];
        }
    } catch (e) {
        console.error('[ComprasCrear] Error cargando proveedores:', e);
        window.listaProveedores = [];
    }

    // Inicializar combobox si está disponible
    if (typeof window.setupCombobox === 'function') {
        window.setupCombobox(
            'combobox-proveedor-compra',
            'compra-proveedor',
            'proveedor-input-visual',
            'proveedor-list-compra',
            'btn-limpiar-prov-compra',
            {
                dataSource: window.listaProveedores,
                defaultLabel: 'Seleccionar proveedor...'
            }
        );
    } else {
        console.warn('[ComprasCrear] setupCombobox no disponible, reintentando...');
        setTimeout(initComboboxProveedor, 100);
    }
}

// =========================================================================
// INICIALIZACIÓN PRINCIPAL
// =========================================================================
function initCompra() {
    console.log('[ComprasCrear] Inicializando módulo...');

    // Verificar si estamos en la página de crear compra
    if (!document.getElementById('compra-buscador')) {
        console.log('[ComprasCrear] No es página de crear compra, saltando');
        return;
    }

    initComboboxProveedor();
    initBusquedaProductos();
    initCondicionListener();

    // Configurar botón guardar
    const btnGuardar = document.getElementById('btn-guardar-compra');
    if (btnGuardar) {
        const newBtn = btnGuardar.cloneNode(true);
        btnGuardar.parentNode.replaceChild(newBtn, btnGuardar);
        document.getElementById('btn-guardar-compra').addEventListener('click', (e) => {
            e.preventDefault();
            guardarCompra();
        });
    }

    renderizarTablaCompra();

    console.log('[ComprasCrear] Módulo inicializado ✓');
}

// =========================================================================
// HELPER FUNCTIONS
// =========================================================================
function escapeHTML(str) {
    if (!str) return '';
    return String(str).replace(/[&<>"']/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m]));
}

// showToast se usa directamente desde window.showToast (core.js)
// NO definir localmente para evitar recursión infinita

// =========================================================================
// EXPORTAR AL SCOPE GLOBAL
// =========================================================================
window.ComprasCrear = {
    init: initCompra,
    agregar: agregarProductoCompra,
    renderizar: renderizarTablaCompra,
    guardar: guardarCompra
};

// Funciones globales para onclick en HTML
window.agregarProductoCompra = agregarProductoCompra;
window.actualizarCantidadCompra = actualizarCantidadCompra;
window.actualizarCostoCompra = actualizarCostoCompra;
window.eliminarItemCompra = eliminarItemCompra;
window.guardarCompra = guardarCompra;

// Inicializar cuando el DOM esté listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initCompra);
} else {
    initCompra();
}
