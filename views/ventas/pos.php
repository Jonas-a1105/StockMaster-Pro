<?php
/**
 * POS - Punto de Venta Enterprise
 * views/ventas/pos.php
 */
use App\Helpers\Icons;

$tasaBCV = $_SESSION['tasa_bcv'] ?? 0;
?>

<!-- Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-800 dark:text-white flex items-center gap-3">
            <?= Icons::get('pos', 'w-7 h-7 text-emerald-500') ?>
            Punto de Venta
        </h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
            Procesa ventas de forma rápida y eficiente
        </p>
    </div>
    
    <div class="flex items-center gap-3">
        <button onclick="toggleShortcutsHelp()" class="inline-flex items-center gap-2 px-3 py-2 text-slate-500 hover:text-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors text-sm">
            <?= Icons::get('info', 'w-4 h-4') ?>
            <span>F1 Ayuda</span>
        </button>
        <a href="index.php?controlador=venta&accion=historial" class="inline-flex items-center gap-2 px-4 py-2.5 bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 rounded-xl font-medium hover:bg-slate-50 dark:hover:bg-slate-600 transition-colors">
            <?= Icons::get('history', 'w-4 h-4') ?>
            <span>Historial</span>
        </a>
    </div>
</div>

<!-- Layout Principal -->
<div class="grid grid-cols-1 xl:grid-cols-5 gap-6">
    
    <!-- Columna Izquierda: Búsqueda y Carrito (3/5) -->
    <div class="xl:col-span-3 space-y-6">
        
        <!-- Búsqueda de Productos -->
        <div class="bg-white dark:bg-slate-700/50 rounded-2xl border border-slate-200 dark:border-slate-600 p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-slate-800 dark:text-white flex items-center gap-2">
                    <?= Icons::get('search', 'w-5 h-5 text-blue-500') ?>
                    Buscar Producto
                </h3>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-lg text-xs font-medium">
                    <?= Icons::get('barcode', 'w-3.5 h-3.5') ?>
                    Escanear código
                </span>
            </div>
            
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <?= Icons::get('barcode', 'w-5 h-5 text-slate-400') ?>
                </div>
                <input type="text" 
                       id="pos-buscador" 
                       placeholder="Buscar producto o escanear código de barras..."
                       autocomplete="off"
                       class="w-full pl-12 pr-4 py-3.5 bg-slate-100 dark:bg-slate-600 border-0 rounded-xl text-slate-800 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 text-lg">
                <div id="pos-resultados-busqueda" class="absolute left-0 right-0 top-full mt-2 bg-white dark:bg-slate-700 rounded-xl shadow-xl border border-slate-200 dark:border-slate-600 max-h-80 overflow-y-auto z-50 hidden"></div>
            </div>
        </div>
        
        <!-- Carrito -->
        <div class="bg-white dark:bg-slate-700/50 rounded-2xl border border-slate-200 dark:border-slate-600 p-5 min-h-[400px]">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100 dark:border-slate-600">
                <h3 class="font-semibold text-slate-800 dark:text-white flex items-center gap-2">
                    <?= Icons::get('sales', 'w-5 h-5 text-emerald-500') ?>
                    Ticket de Venta
                </h3>
                <button onclick="limpiarCarrito()" class="inline-flex items-center gap-2 px-3 py-1.5 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition-colors text-sm font-medium">
                    <?= Icons::get('trash', 'w-4 h-4') ?>
                    Limpiar
                </button>
            </div>
            
            <!-- Estado Vacío -->
            <div id="carrito-vacio" class="py-16 text-center">
                <?= Icons::get('sales', 'w-16 h-16 mx-auto text-slate-200 dark:text-slate-600 mb-4') ?>
                <p class="text-slate-500 dark:text-slate-400 font-medium">El carrito está vacío</p>
                <p class="text-sm text-slate-400 dark:text-slate-500 mt-1">Busca productos para comenzar</p>
            </div>
            
            <!-- Tabla Carrito -->
            <table id="tabla-carrito" class="w-full hidden">
                <thead>
                    <tr class="border-b border-slate-100 dark:border-slate-600">
                        <th class="pb-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Producto</th>
                        <th class="pb-3 text-center text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase w-20">Cant.</th>
                        <th class="pb-3 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase w-24">Precio</th>
                        <th class="pb-3 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase w-28">Total</th>
                        <th class="pb-3 w-12"></th>
                    </tr>
                </thead>
                <tbody id="carrito-body" class="divide-y divide-slate-50 dark:divide-slate-600">
                    <!-- Items dinámicos -->
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Columna Derecha: Cliente y Pago (2/5) -->
    <div class="xl:col-span-2 space-y-6">
        
        <!-- Selección de Cliente -->
        <div class="bg-white dark:bg-slate-700/50 rounded-2xl border border-slate-200 dark:border-slate-600 p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-slate-800 dark:text-white flex items-center gap-2">
                    <?= Icons::get('user', 'w-5 h-5 text-slate-400') ?>
                    Cliente
                </h3>
            </div>
            
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <?= Icons::get('search', 'w-4 h-4 text-slate-400') ?>
                </div>
                <input type="text" 
                       id="cliente-buscador" 
                       placeholder="Buscar cliente..."
                       autocomplete="off"
                       class="w-full pl-10 pr-4 py-2.5 bg-slate-100 dark:bg-slate-600 border-0 rounded-xl text-slate-800 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                <div id="cliente-resultados" class="absolute left-0 right-0 top-full mt-2 bg-white dark:bg-slate-700 rounded-xl shadow-xl border border-slate-200 dark:border-slate-600 max-h-60 overflow-y-auto z-50 hidden"></div>
            </div>
            
            <button onclick="abrirModalCliente()" class="mt-3 w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 rounded-xl font-medium hover:bg-emerald-100 dark:hover:bg-emerald-900/50 transition-colors">
                <?= Icons::get('plus-circle', 'w-4 h-4') ?>
                Crear Nuevo Cliente
            </button>
            
            <!-- Cliente Seleccionado -->
            <div id="cliente-seleccionado" class="hidden mt-4 p-4 bg-blue-50 dark:bg-blue-900/30 rounded-xl border border-blue-200 dark:border-blue-800">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <?= Icons::get('check-circle', 'w-4 h-4 text-blue-600 dark:text-blue-400') ?>
                            <span id="cliente-nombre" class="font-semibold text-blue-800 dark:text-blue-200">Cliente</span>
                        </div>
                        <p id="cliente-documento" class="text-sm text-blue-600 dark:text-blue-400 mt-1"></p>
                        <span id="cliente-credito" class="inline-block mt-2 px-2 py-1 bg-emerald-500 text-white text-xs font-bold rounded-lg">
                            Crédito: $0.00
                        </span>
                    </div>
                    <button onclick="quitarCliente()" class="p-1 text-blue-400 hover:text-red-500 transition-colors">
                        <?= Icons::get('x', 'w-5 h-5') ?>
                    </button>
                </div>
                <input type="hidden" id="cliente-id">
            </div>
        </div>
        
        <!-- Detalles de Pago -->
        <div class="bg-white dark:bg-slate-700/50 rounded-2xl border border-slate-200 dark:border-slate-600 p-5">
            <h3 class="font-semibold text-slate-800 dark:text-white flex items-center gap-2 mb-4">
                <?= Icons::get('dollar', 'w-5 h-5 text-emerald-500') ?>
                Detalle de Pago
            </h3>
            
            <div class="space-y-4">
                <!-- Método de Pago -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Método de Pago</label>
                    <select id="metodo-pago" class="w-full px-4 py-2.5 bg-slate-100 dark:bg-slate-600 border-0 rounded-xl text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                        <option value="Efectivo">💵 Efectivo</option>
                        <option value="Pago Movil">📱 Pago Móvil</option>
                        <option value="Transferencia">🏦 Transferencia</option>
                        <option value="Punto de Venta">💳 Punto de Venta</option>
                        <option value="Zelle">💰 Zelle</option>
                        <option value="Binance">₿ Binance</option>
                    </select>
                </div>
                
                <!-- Estado de Pago -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Estado</label>
                    <select id="estado-pago" class="w-full px-4 py-2.5 bg-slate-100 dark:bg-slate-600 border-0 rounded-xl text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30" onchange="verificarCredito()">
                        <option value="Pagada">✅ Pagada</option>
                        <option value="Pendiente">⏳ Pendiente (Crédito)</option>
                    </select>
                </div>
                
                <!-- Tasa de Cambio -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5 flex items-center gap-2">
                        <?= Icons::get('refresh', 'w-4 h-4') ?>
                        Tasa de Cambio (VES)
                    </label>
                    <input type="number" 
                           id="tasa-manual" 
                           value="<?= $tasaBCV ?>" 
                           step="0.01" 
                           placeholder="0.00"
                           onchange="window.actualizarTotalesPOS ? window.actualizarTotalesPOS() : null"
                           class="w-full px-4 py-2.5 bg-slate-100 dark:bg-slate-600 border-0 rounded-xl text-slate-800 dark:text-white text-right font-mono focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                </div>
            </div>
            
            <!-- Resumen de Total -->
            <div class="mt-6 p-5 bg-gradient-to-br from-slate-800 to-slate-900 rounded-2xl text-white">
                <p class="text-xs uppercase tracking-wide text-slate-400 font-semibold mb-1">Total a Pagar</p>
                <p class="text-3xl font-bold" id="total-usd">$0.00</p>
                <p class="text-lg text-emerald-400 font-semibold mt-1" id="total-ves">Bs. 0.00</p>
            </div>
            
            <!-- Botones de Acción -->
            <div class="grid grid-cols-2 gap-3 mt-5">
                <button onclick="limpiarCarrito()" class="flex items-center justify-center gap-2 px-4 py-3 bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-400 border border-red-200 dark:border-red-800 rounded-xl font-medium hover:bg-red-100 dark:hover:bg-red-900/50 transition-colors">
                    <?= Icons::get('x', 'w-4 h-4') ?>
                    Cancelar
                </button>
                <button id="btn-cobrar" class="flex items-center justify-center gap-2 px-4 py-3 bg-gradient-to-r from-emerald-500 to-emerald-600 text-white rounded-xl font-bold shadow-lg shadow-emerald-500/30 hover:shadow-emerald-500/50 transition-all">
                    <?= Icons::get('check', 'w-5 h-5') ?>
                    Cobrar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Cliente -->
<?php if (file_exists(__DIR__ . '/../clientes/modal_crear.php')): ?>
    <?php include __DIR__ . '/../clientes/modal_crear.php'; ?>
<?php endif; ?>

<!-- Panel Atajos de Teclado -->
<div id="shortcuts-help-panel" class="fixed bottom-6 right-6 w-72 bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 opacity-0 translate-y-4 pointer-events-none transition-all duration-300 z-50">
    <div class="flex items-center justify-between px-5 py-3 bg-gradient-to-r from-emerald-500 to-emerald-600 text-white rounded-t-2xl">
        <h4 class="font-semibold flex items-center gap-2">
            <?= Icons::get('info', 'w-4 h-4') ?>
            Atajos de Teclado
        </h4>
        <button onclick="toggleShortcutsHelp()" class="text-white/80 hover:text-white">&times;</button>
    </div>
    <div class="p-4 space-y-2">
        <div class="flex items-center gap-3 text-sm text-slate-600 dark:text-slate-300">
            <span class="px-2 py-1 bg-slate-100 dark:bg-slate-700 rounded font-mono text-xs">F1</span>
            <span>Mostrar ayuda</span>
        </div>
        <div class="flex items-center gap-3 text-sm text-slate-600 dark:text-slate-300">
            <span class="px-2 py-1 bg-slate-100 dark:bg-slate-700 rounded font-mono text-xs">F2</span>
            <span>Buscar producto</span>
        </div>
        <div class="flex items-center gap-3 text-sm text-slate-600 dark:text-slate-300">
            <span class="px-2 py-1 bg-slate-100 dark:bg-slate-700 rounded font-mono text-xs">F3</span>
            <span>Buscar cliente</span>
        </div>
        <div class="flex items-center gap-3 text-sm text-slate-600 dark:text-slate-300">
            <span class="px-2 py-1 bg-slate-100 dark:bg-slate-700 rounded font-mono text-xs">F4</span>
            <span>Cobrar venta</span>
        </div>
        <div class="flex items-center gap-3 text-sm text-slate-600 dark:text-slate-300">
            <span class="px-2 py-1 bg-slate-100 dark:bg-slate-700 rounded font-mono text-xs">ESC</span>
            <span>Cerrar modales</span>
        </div>
    </div>
</div>

<script>
window.tasaCambioBS = <?= json_encode((float)$tasaBCV) ?>;
console.log('[POS] Inicializando... Tasa:', window.tasaCambioBS);

// === CARRITO ===
if (!window.carritoPOS) window.carritoPOS = [];

window.limpiarCarrito = function() {
    if (!window.carritoPOS || window.carritoPOS.length === 0) {
        showToast('El carrito ya está vacío', 'info');
        return;
    }
    window.carritoPOS = [];
    renderizarCarrito();
    showToast('Carrito limpiado', 'success');
};

window.renderizarCarrito = function() {
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
};

window.actualizarCantidad = function(index, value) {
    const cantidad = parseInt(value);
    if (cantidad > 0) {
        window.carritoPOS[index].cantidad = cantidad;
        renderizarCarrito();
    }
};

window.eliminarItem = function(index) {
    window.carritoPOS.splice(index, 1);
    renderizarCarrito();
};

window.actualizarTotalesPOS = function() {
    const totalUSD = document.getElementById('total-usd');
    const totalVES = document.getElementById('total-ves');
    
    // Si no existen los elementos (ej: otra vista), no hacer nada
    if (!totalUSD || !totalVES) return;

    const total = window.carritoPOS.reduce((sum, item) => sum + (item.precio * item.cantidad), 0);
    const tasa = parseFloat(document.getElementById('tasa-manual')?.value) || window.tasaCambioBS || 0;
    
    totalUSD.textContent = `$${total.toFixed(2)}`;
    totalVES.textContent = `Bs. ${(total * tasa).toFixed(2)}`;
};

// === BÚSQUEDA PRODUCTOS ===
window.searchTimer = window.searchTimer || null;
window.buscador = document.getElementById('pos-buscador');
window.resultados = document.getElementById('pos-resultados-busqueda');
window.currentSearchResults = [];

window.seleccionarProducto = function(index) {
    const producto = window.currentSearchResults[index];
    if (producto) {
        agregarAlCarrito(producto);
    }
};

buscador?.addEventListener('input', (e) => {
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
            window.currentSearchResults = productos;
            
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

window.agregarAlCarrito = function(producto) {
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
    window.resultados.classList.add('hidden');
    buscador.value = '';
    buscador.focus();
    showToast(`${producto.nombre} agregado`, 'success');
};

// === PROCESAR VENTA ===
window.checkoutPOS = async function() {
    const btnCobrar = document.getElementById('btn-cobrar');
    
    try {
        if (!window.carritoPOS || window.carritoPOS.length === 0) {
            showToast('El carrito está vacío', 'error');
            return;
        }
        
        const clienteId = document.getElementById('cliente-id')?.value || null;
        const metodoPago = document.getElementById('metodo-pago')?.value || 'Efectivo';
        const estadoPago = document.getElementById('estado-pago')?.value || 'Pagada';
        let tasa = parseFloat(document.getElementById('tasa-manual')?.value || 0);
        if (tasa <= 0) tasa = 1.00;
        
        if (estadoPago === 'Pendiente' && !clienteId) {
            showToast('Para crédito debes seleccionar un cliente', 'warning');
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
            showToast('¡Venta registrada!', 'success');
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
        showToast(error.message, 'error');
    } finally {
        if (btnCobrar) {
            btnCobrar.disabled = false;
            btnCobrar.innerHTML = `<?= Icons::get("check", "w-5 h-5") ?> Cobrar`;
        }
    }
};

// === CLIENTE ===
window.searchClientTimer = null;
window.clienteBuscador = document.getElementById('cliente-buscador');
window.clienteResultados = document.getElementById('cliente-resultados');
window.clientResults = [];

window.seleccionarCliente = function(index) {
    const cliente = window.clientResults[index];
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
    
    // Ocultar buscador y limpiar
    clienteBuscador.value = '';
    clienteResultados.classList.add('hidden');
    clienteBuscador.parentElement.classList.add('hidden');
};

window.quitarCliente = function() {
    document.getElementById('cliente-seleccionado').classList.add('hidden');
    document.getElementById('cliente-id').value = '';
    
    // Mostrar buscador
    if (clienteBuscador) {
        clienteBuscador.value = '';
        clienteBuscador.parentElement.classList.remove('hidden');
        clienteBuscador.focus();
    }
};

clienteBuscador?.addEventListener('input', (e) => {
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
            window.clientResults = clientes;
            
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

// === ATAJOS ===
function toggleShortcutsHelp() {
    const panel = document.getElementById('shortcuts-help-panel');
    panel.classList.toggle('opacity-0');
    panel.classList.toggle('translate-y-4');
    panel.classList.toggle('pointer-events-none');
}

document.addEventListener('keydown', (e) => {
    const shortcuts = {
        'F1': () => { e.preventDefault(); toggleShortcutsHelp(); },
        'F2': () => { e.preventDefault(); document.getElementById('pos-buscador')?.focus(); },
        'F3': () => { e.preventDefault(); document.getElementById('cliente-buscador')?.focus(); },
        'F4': () => { e.preventDefault(); checkoutPOS(); },
        'Escape': () => { resultados?.classList.add('hidden'); }
    };
    if (shortcuts[e.key]) shortcuts[e.key]();
});

// === INIT ===
// === INIT ===
function initPOS() {
    console.log('[POS] Inicializando scripts...');
    
    // Configurar botón cobrar
    const btnCobrar = document.getElementById('btn-cobrar');
    if (btnCobrar) {
        // Remover listeners anteriores para evitar duplicados en recargas turbo
        const newBtn = btnCobrar.cloneNode(true);
        btnCobrar.parentNode.replaceChild(newBtn, btnCobrar);
        
        console.log('[POS] Configurando botón Cobrar');
        newBtn.addEventListener('click', (e) => {
            console.log('[POS] Click en Cobrar');
            checkoutPOS();
        });
    } else {
        console.warn('[POS] Botón Cobrar no encontrado');
    }
    
    renderizarCarrito();
    
    // Re-configurar focos
    if (window.buscador) window.buscador = document.getElementById('pos-buscador');
    if (window.clienteBuscador) window.clienteBuscador = document.getElementById('cliente-buscador');
    if (window.buscador) window.buscador.focus();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPOS);
} else {
    initPOS();
}

function escapeHTML(str) {
    if (!str) return '';
    return str.replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));
}
</script>
