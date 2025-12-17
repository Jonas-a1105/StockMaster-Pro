/**
 * =========================================================================
 * POS.JS - Módulo de Punto de Venta (Completo)
 * =========================================================================
 * Lógica completa del POS incluyendo:
 * - Carrito de productos
 * - Búsqueda de productos
 * - Búsqueda y selección de clientes
 * - Procesamiento de ventas
 * - Atajos de teclado
 * 
 * Dependencias globales (deben estar definidas antes de cargar):
 * - window.tasaCambioBS
 */

console.log('[POS] Módulo cargando...');

// =========================================================================
// CARRITO GLOBAL
// =========================================================================
window.carritoPOS = window.carritoPOS || [];

// =========================================================================
// VARIABLES DE ESTADO
// =========================================================================
var searchTimer = null;
var searchClientTimer = null;
var currentSearchResults = [];
var clientResults = [];

// =========================================================================
// FUNCIONES DE CARRITO
// =========================================================================
function limpiarCarrito() {
    if (!window.carritoPOS || window.carritoPOS.length === 0) {
        if (typeof showToast === 'function') showToast('El carrito ya está vacío', 'info');
        return;
    }
    window.carritoPOS = [];
    renderizarCarrito();
    if (typeof showToast === 'function') showToast('Carrito limpiado', 'success');
}

function renderizarCarrito() {
    const tbody = document.getElementById('carrito-body');
    const tabla = document.getElementById('tabla-carrito');
    const vacio = document.getElementById('carrito-vacio');

    if (!tbody) return;

    const isEmpty = !window.carritoPOS || window.carritoPOS.length === 0;

    if (vacio) vacio.classList.toggle('hidden', !isEmpty);
    if (tabla) tabla.classList.toggle('hidden', isEmpty);

    if (isEmpty) {
        actualizarTotalesPOS();
        return;
    }

    tbody.innerHTML = window.carritoPOS.map((item, index) => {
        const subtotal = item.precio * item.cantidad;
        return `
            <tr class="group">
                <td class="py-3">
                    <p class="font-medium text-slate-800 dark:text-white">${escapeHTML(item.nombre)}</p>
                </td>
                <td class="py-3 text-center">
                    <input type="number" 
                           class="w-16 px-2 py-1.5 bg-slate-100 dark:bg-slate-600 border-0 rounded-lg text-center font-semibold text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30 qty-input"
                           value="${item.cantidad}" 
                           min="1"
                           onchange="actualizarCantidad(${index}, this.value)">
                </td>
                <td class="py-3 text-right font-mono text-slate-600 dark:text-slate-300">$${item.precio.toFixed(2)}</td>
                <td class="py-3 text-right font-mono font-semibold text-emerald-600 dark:text-emerald-400">$${subtotal.toFixed(2)}</td>
                <td class="py-3 text-center">
                    <button onclick="eliminarItem(${index})" class="p-1.5 text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </td>
            </tr>
        `;
    }).join('');

    actualizarTotalesPOS();
}

function actualizarCantidad(index, value) {
    const cantidad = parseInt(value);
    if (cantidad > 0) {
        window.carritoPOS[index].cantidad = cantidad;
        renderizarCarrito();
    }
}

function eliminarItem(index) {
    window.carritoPOS.splice(index, 1);
    renderizarCarrito();
}

function actualizarTotalesPOS() {
    const totalUSD = document.getElementById('total-usd');
    const totalVES = document.getElementById('total-ves');

    if (!totalUSD || !totalVES) return;

    const total = window.carritoPOS.reduce((sum, item) => sum + (item.precio * item.cantidad), 0);
    const tasa = parseFloat(document.getElementById('tasa-manual')?.value) || window.tasaCambioBS || 0;

    totalUSD.textContent = `$${total.toFixed(2)}`;
    totalVES.textContent = `Bs. ${(total * tasa).toFixed(2)}`;
}

function agregarAlCarrito(producto) {
    const existente = window.carritoPOS.find(i => i.id === producto.id);
    if (existente) {
        existente.cantidad++;
    } else {
        window.carritoPOS.push({
            id: producto.id,
            nombre: producto.nombre,
            precio: parseFloat(producto.precioVentaUSD) || 0,
            cantidad: 1
        });
    }
    renderizarCarrito();

    const resultados = document.getElementById('pos-resultados-busqueda');
    const buscador = document.getElementById('pos-buscador');
    if (resultados) resultados.classList.add('hidden');
    if (buscador) {
        buscador.value = '';
        buscador.focus();
    }
    if (typeof showToast === 'function') showToast(`${producto.nombre} agregado`, 'success');
}

// =========================================================================
// BÚSQUEDA DE PRODUCTOS
// =========================================================================
function initBusquedaProductos() {
    const buscador = document.getElementById('pos-buscador');
    const resultados = document.getElementById('pos-resultados-busqueda');

    if (!buscador || !resultados) return;

    buscador.addEventListener('input', (e) => {
        clearTimeout(searchTimer);
        const term = e.target.value;

        if (term.length < 2) {
            resultados.classList.add('hidden');
            return;
        }

        searchTimer = setTimeout(async () => {
            try {
                const res = await fetch(`index.php?controlador=venta&accion=buscarProductos&term=${encodeURIComponent(term)}`);
                const productos = await res.json();
                currentSearchResults = productos;

                if (productos.length === 0) {
                    resultados.innerHTML = '<div class="p-4 text-center text-slate-400">No se encontraron productos</div>';
                } else {
                    resultados.innerHTML = productos.map((p, index) => `
                        <div class="flex items-center gap-3 px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-600 cursor-pointer transition-colors product-result-item" onclick="seleccionarProducto(${index})">
                            <div class="w-10 h-10 bg-slate-100 dark:bg-slate-600 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-slate-800 dark:text-white truncate">${escapeHTML(p.nombre)}</p>
                                <p class="text-xs text-slate-400">Stock: ${p.stock}</p>
                            </div>
                            <span class="text-emerald-600 dark:text-emerald-400 font-semibold">$${parseFloat(p.precioVentaUSD).toFixed(2)}</span>
                        </div>
                    `).join('');
                }

                resultados.classList.remove('hidden');
            } catch (e) {
                console.error('[POS] Error búsqueda:', e);
            }
        }, 300);
    });
}

function seleccionarProducto(index) {
    const producto = currentSearchResults[index];
    if (producto) {
        agregarAlCarrito(producto);
    }
}

// =========================================================================
// BÚSQUEDA DE CLIENTES
// =========================================================================
function initBusquedaClientes() {
    const clienteBuscador = document.getElementById('cliente-buscador');
    const clienteResultados = document.getElementById('cliente-resultados');

    if (!clienteBuscador || !clienteResultados) return;

    clienteBuscador.addEventListener('input', (e) => {
        clearTimeout(searchClientTimer);
        const term = e.target.value;

        if (term.length < 2) {
            clienteResultados.classList.add('hidden');
            return;
        }

        searchClientTimer = setTimeout(async () => {
            try {
                const res = await fetch(`index.php?controlador=cliente&accion=buscarParaPOS&term=${encodeURIComponent(term)}`);
                const clientes = await res.json();
                clientResults = clientes;

                if (clientes.length === 0) {
                    clienteResultados.innerHTML = '<div class="p-4 text-center text-slate-400">No se encontraron clientes</div>';
                } else {
                    clienteResultados.innerHTML = clientes.map((c, index) => `
                        <div class="flex items-center gap-3 px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-600 cursor-pointer transition-colors" onclick="seleccionarCliente(${index})">
                            <div class="w-10 h-10 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-slate-800 dark:text-white truncate">${escapeHTML(c.nombre)}</p>
                                <p class="text-xs text-slate-400">${c.documento || 'Sin documento'}</p>
                            </div>
                        </div>
                    `).join('');
                }

                clienteResultados.classList.remove('hidden');
            } catch (e) {
                console.error('[POS] Error búsqueda cliente:', e);
            }
        }, 300);
    });
}

function seleccionarCliente(index) {
    const cliente = clientResults[index];
    if (!cliente) return;

    document.getElementById('cliente-nombre').textContent = cliente.nombre;
    document.getElementById('cliente-documento').textContent = cliente.documento || 'Sin documento';

    const creditoBadget = document.getElementById('cliente-credito');
    if (creditoBadget) {
        const credito = parseFloat(cliente.limite_credito || 0);
        creditoBadget.textContent = `Crédito: $${credito.toFixed(2)}`;
        creditoBadget.className = credito > 0
            ? 'inline-block mt-2 px-2 py-1 bg-emerald-500 text-white text-xs font-bold rounded-lg'
            : 'inline-block mt-2 px-2 py-1 bg-slate-400 text-white text-xs font-bold rounded-lg';
    }

    document.getElementById('cliente-id').value = cliente.id;
    document.getElementById('cliente-seleccionado').classList.remove('hidden');

    const clienteBuscador = document.getElementById('cliente-buscador');
    const clienteResultados = document.getElementById('cliente-resultados');
    if (clienteBuscador) {
        clienteBuscador.value = '';
        clienteBuscador.parentElement.classList.add('hidden');
    }
    if (clienteResultados) clienteResultados.classList.add('hidden');
}

function quitarCliente() {
    document.getElementById('cliente-seleccionado').classList.add('hidden');
    document.getElementById('cliente-id').value = '';

    const clienteBuscador = document.getElementById('cliente-buscador');
    if (clienteBuscador) {
        clienteBuscador.value = '';
        clienteBuscador.parentElement.classList.remove('hidden');
        clienteBuscador.focus();
    }
}

// =========================================================================
// CHECKOUT / PROCESAR VENTA
// =========================================================================
async function checkoutPOS() {
    const btnCobrar = document.getElementById('btn-cobrar');

    try {
        if (!window.carritoPOS || window.carritoPOS.length === 0) {
            if (typeof showToast === 'function') showToast('El carrito está vacío', 'error');
            return;
        }

        const clienteId = document.getElementById('cliente-id')?.value || null;
        const metodoPago = document.getElementById('metodo-pago')?.value || 'Efectivo';
        const estadoPago = document.getElementById('estado-pago')?.value || 'Pagada';
        let tasa = parseFloat(document.getElementById('tasa-manual')?.value || 0);
        if (tasa <= 0) tasa = 1.00;

        if (estadoPago === 'Pendiente' && !clienteId) {
            if (typeof showToast === 'function') showToast('Para crédito debes seleccionar un cliente', 'warning');
            return;
        }

        if (btnCobrar) {
            btnCobrar.disabled = true;
            btnCobrar.innerHTML = `<svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Procesando...`;
        }

        const response = await fetch('index.php?controlador=venta&accion=checkout', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                carrito: window.carritoPOS,
                tasa: tasa,
                cliente_id: clienteId,
                metodo_pago: metodoPago,
                estado_pago: estadoPago,
                notas: ''
            })
        });

        const data = await response.json();

        if (data.success) {
            if (typeof showToast === 'function') showToast('¡Venta registrada!', 'success');
            window.carritoPOS = [];
            renderizarCarrito();
            quitarCliente();

            if (data.ventaId) {
                window.open(`index.php?controlador=venta&accion=recibo&id=${data.ventaId}`, '_blank');
            }
        } else {
            throw new Error(data.message || 'Error al procesar');
        }
    } catch (error) {
        if (typeof showToast === 'function') showToast(error.message, 'error');
    } finally {
        if (btnCobrar) {
            btnCobrar.disabled = false;
            btnCobrar.innerHTML = `<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Cobrar`;
        }
    }
}

// =========================================================================
// ATAJOS DE TECLADO
// =========================================================================
function toggleShortcutsHelp() {
    const panel = document.getElementById('shortcuts-help-panel');
    if (!panel) return;
    panel.classList.toggle('opacity-0');
    panel.classList.toggle('translate-y-4');
    panel.classList.toggle('pointer-events-none');
}

function initAtajos() {
    document.addEventListener('keydown', (e) => {
        // Solo en página POS
        if (!document.getElementById('pos-buscador')) return;

        const shortcuts = {
            'F1': () => { e.preventDefault(); toggleShortcutsHelp(); },
            'F2': () => { e.preventDefault(); document.getElementById('pos-buscador')?.focus(); },
            'F3': () => { e.preventDefault(); document.getElementById('cliente-buscador')?.focus(); },
            'F4': () => { e.preventDefault(); checkoutPOS(); },
            'Escape': () => {
                document.getElementById('pos-resultados-busqueda')?.classList.add('hidden');
                document.getElementById('cliente-resultados')?.classList.add('hidden');
            }
        };
        if (shortcuts[e.key]) shortcuts[e.key]();
    });
}

// =========================================================================
// INICIALIZACIÓN PRINCIPAL
// =========================================================================
function initPOS() {
    console.log('[POS] Inicializando módulo...');

    // Verificar si estamos en la página POS
    if (!document.getElementById('pos-buscador')) {
        console.log('[POS] No es página POS, saltando inicialización');
        return;
    }

    initBusquedaProductos();
    initBusquedaClientes();
    initAtajos();

    // Configurar botón cobrar
    const btnCobrar = document.getElementById('btn-cobrar');
    if (btnCobrar) {
        // Clonar para remover listeners anteriores
        const newBtn = btnCobrar.cloneNode(true);
        btnCobrar.parentNode.replaceChild(newBtn, btnCobrar);
        newBtn.addEventListener('click', () => checkoutPOS());
    }

    renderizarCarrito();

    // Auto-focus en buscador
    document.getElementById('pos-buscador')?.focus();

    console.log('[POS] Módulo inicializado ✓');
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
window.POS = {
    init: initPOS,
    agregar: agregarAlCarrito,
    renderizar: renderizarCarrito,
    limpiar: limpiarCarrito,
    checkout: checkoutPOS
};

// Funciones globales para onclick en HTML
window.limpiarCarrito = limpiarCarrito;
window.renderizarCarrito = renderizarCarrito;
window.actualizarCantidad = actualizarCantidad;
window.eliminarItem = eliminarItem;
window.actualizarTotalesPOS = actualizarTotalesPOS;
window.agregarAlCarrito = agregarAlCarrito;
window.seleccionarProducto = seleccionarProducto;
window.seleccionarCliente = seleccionarCliente;
window.quitarCliente = quitarCliente;
window.checkoutPOS = checkoutPOS;
window.toggleShortcutsHelp = toggleShortcutsHelp;

// Inicializar cuando el DOM esté listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPOS);
} else {
    initPOS();
}
