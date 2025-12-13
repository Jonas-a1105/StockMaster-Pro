<?php
/**
 * COMPRAS - Crear Nueva Compra Enterprise
 * views/compras/crear.php
 */
use App\Helpers\Icons;

$proveedores = $proveedores ?? [];
?>

<!-- Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-800 dark:text-white flex items-center gap-3">
            <?= Icons::get('purchases', 'w-7 h-7 text-blue-500') ?>
            Registrar Compra
        </h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
            Ingresa los datos de la factura de compra
        </p>
    </div>
    
    <a href="index.php?controlador=compra&accion=index" class="inline-flex items-center gap-2 px-4 py-2.5 bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 rounded-xl font-medium hover:bg-slate-50 dark:hover:bg-slate-600 transition-colors">
        <?= Icons::get('chevron-left', 'w-4 h-4') ?>
        <span>Volver</span>
    </a>
</div>

<!-- Layout Grid -->
<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
    
    <!-- Columna Izquierda: Datos de Compra (2/3) -->
    <div class="xl:col-span-2 space-y-6">
        
        <!-- Datos del Documento -->
        <div class="bg-white dark:bg-slate-700/50 rounded-2xl border border-slate-200 dark:border-slate-600 p-5">
            <h3 class="font-semibold text-slate-800 dark:text-white flex items-center gap-2 mb-4 pb-3 border-b border-slate-100 dark:border-slate-600">
                <?= Icons::get('document', 'w-5 h-5 text-blue-500') ?>
                Datos del Documento
            </h3>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Proveedor</label>
                    <select id="compra-proveedor" class="w-full px-4 py-2.5 bg-slate-100 dark:bg-slate-600 border-0 rounded-xl text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                        <option value="">Seleccionar...</option>
                        <?php foreach($proveedores as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Nro. Factura</label>
                    <input type="text" id="compra-factura" placeholder="Ej: A-123" 
                           class="w-full px-4 py-2.5 bg-slate-100 dark:bg-slate-600 border-0 rounded-xl text-slate-800 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Fecha Emisión</label>
                    <input type="date" id="compra-fecha" value="<?= date('Y-m-d') ?>" 
                           class="w-full px-4 py-2.5 bg-slate-100 dark:bg-slate-600 border-0 rounded-xl text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Condición</label>
                    <select id="compra-condicion" class="w-full px-4 py-2.5 bg-slate-100 dark:bg-slate-600 border-0 rounded-xl text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                        <option value="Contado">💵 Contado (Pagada)</option>
                        <option value="Credito">📅 Crédito (Pendiente)</option>
                    </select>
                </div>
                
                <div id="div-vencimiento" class="hidden">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Fecha Vencimiento</label>
                    <input type="date" id="compra-vencimiento" value="<?= date('Y-m-d', strtotime('+30 days')) ?>" 
                           class="w-full px-4 py-2.5 bg-slate-100 dark:bg-slate-600 border-0 rounded-xl text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                </div>
            </div>
        </div>
        
        <!-- Productos -->
        <div class="bg-white dark:bg-slate-700/50 rounded-2xl border border-slate-200 dark:border-slate-600 p-5">
            <h3 class="font-semibold text-slate-800 dark:text-white flex items-center gap-2 mb-4">
                <?= Icons::get('inventory', 'w-5 h-5 text-emerald-500') ?>
                Productos
            </h3>
            
            <!-- Buscador -->
            <div class="relative mb-4">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <?= Icons::get('search', 'w-5 h-5 text-slate-400') ?>
                </div>
                <input type="text" 
                       id="compra-buscador" 
                       placeholder="Buscar producto por nombre o código..."
                       autocomplete="off"
                       class="w-full pl-12 pr-4 py-3 bg-slate-100 dark:bg-slate-600 border-0 rounded-xl text-slate-800 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                <div id="compra-resultados" class="absolute left-0 right-0 top-full mt-2 bg-white dark:bg-slate-700 rounded-xl shadow-xl border border-slate-200 dark:border-slate-600 max-h-60 overflow-y-auto z-50 hidden"></div>
            </div>
            
            <!-- Tabla Items -->
            <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-600">
                <table class="w-full" id="tabla-compra-items">
                    <thead class="bg-slate-50 dark:bg-slate-600/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Producto</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase w-24">Cant.</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase w-28">Costo ($)</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase w-28">Subtotal</th>
                            <th class="px-4 py-3 w-12"></th>
                        </tr>
                    </thead>
                    <tbody id="cuerpo-compra" class="divide-y divide-slate-100 dark:divide-slate-600">
                        <tr id="row-empty">
                            <td colspan="5" class="px-4 py-12 text-center">
                                <?= Icons::get('purchases', 'w-12 h-12 mx-auto text-slate-200 dark:text-slate-600 mb-3') ?>
                                <p class="text-slate-400">Busca productos para agregar</p>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot class="bg-slate-800 text-white">
                        <tr>
                            <td colspan="3" class="px-4 py-4 text-right font-semibold">TOTAL:</td>
                            <td class="px-4 py-4 text-right font-bold text-lg" id="compra-total-display">$0.00</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Columna Derecha: Resumen (1/3) -->
    <div class="space-y-6">
        
        <!-- Resumen -->
        <div class="bg-white dark:bg-slate-700/50 rounded-2xl border border-slate-200 dark:border-slate-600 p-5 sticky top-24">
            <h3 class="font-semibold text-slate-800 dark:text-white flex items-center gap-2 mb-4 pb-3 border-b border-slate-100 dark:border-slate-600">
                <?= Icons::get('clipboard', 'w-5 h-5 text-slate-400') ?>
                Resumen
            </h3>
            
            <div class="space-y-3">
                <div class="flex justify-between text-sm">
                    <span class="text-slate-500 dark:text-slate-400">Productos:</span>
                    <span id="resumen-items" class="font-medium text-slate-800 dark:text-white">0</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-slate-500 dark:text-slate-400">Total Items:</span>
                    <span id="resumen-cantidad" class="font-medium text-slate-800 dark:text-white">0</span>
                </div>
            </div>
            
            <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-600">
                <div class="flex justify-between">
                    <span class="text-slate-600 dark:text-slate-300 font-medium">Total:</span>
                    <span id="resumen-total" class="text-2xl font-bold text-blue-600 dark:text-blue-400">$0.00</span>
                </div>
            </div>
            
            <button id="btn-guardar-compra" class="mt-6 w-full flex items-center justify-center gap-2 px-4 py-3 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-xl font-bold shadow-lg shadow-blue-500/30 hover:shadow-blue-500/50 transition-all">
                <?= Icons::get('check', 'w-5 h-5') ?>
                Guardar Compra
            </button>
            
            <a href="index.php?controlador=compra&accion=index" class="mt-3 w-full flex items-center justify-center gap-2 px-4 py-2.5 border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 rounded-xl font-medium hover:bg-slate-50 dark:hover:bg-slate-600 transition-colors">
                Cancelar
            </a>
        </div>
    </div>
</div>

<script>
window.carritoCompra = [];

// === BÚSQUEDA PRODUCTOS ===
let searchTimer;
const buscador = document.getElementById('compra-buscador');
const resultados = document.getElementById('compra-resultados');

buscador?.addEventListener('input', (e) => {
    clearTimeout(searchTimer);
    const term = e.target.value;
    
    if (term.length < 2) {
        resultados.classList.add('hidden');
        return;
    }
    
    searchTimer = setTimeout(async () => {
        try {
            const res = await fetch(`index.php?controlador=compra&accion=buscarProductos&term=${encodeURIComponent(term)}`);
            const productos = await res.json();
            
            if (productos.length === 0) {
                resultados.innerHTML = '<div class="p-4 text-center text-slate-400">No se encontraron productos</div>';
            } else {
                resultados.innerHTML = productos.map(p => `
                    <div class="flex items-center gap-3 px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-600 cursor-pointer transition-colors" onclick='agregarProductoCompra(${JSON.stringify(p).replace(/'/g, "\\'")})'>
                        <div class="flex-1">
                            <p class="font-medium text-slate-800 dark:text-white">${escapeHTML(p.nombre)}</p>
                            <p class="text-xs text-slate-400">Costo: $${parseFloat(p.precioCompraUSD || 0).toFixed(2)}</p>
                        </div>
                    </div>
                `).join('');
            }
            
            resultados.classList.remove('hidden');
        } catch (e) {
            console.error('Error búsqueda:', e);
        }
    }, 300);
});

// Cerrar resultados al hacer clic fuera
document.addEventListener('click', (e) => {
    if (!buscador?.contains(e.target) && !resultados?.contains(e.target)) {
        resultados?.classList.add('hidden');
    }
});

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
    resultados.classList.add('hidden');
    buscador.value = '';
    showToast(`${producto.nombre} agregado`, 'success');
}

function renderizarTablaCompra() {
    const tbody = document.getElementById('cuerpo-compra');
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
    
    document.getElementById('compra-total-display').textContent = `$${total.toFixed(2)}`;
    actualizarResumen(window.carritoCompra.length, totalItems, total);
}

function actualizarResumen(items, cantidad, total) {
    document.getElementById('resumen-items').textContent = items;
    document.getElementById('resumen-cantidad').textContent = cantidad;
    document.getElementById('resumen-total').textContent = `$${total.toFixed(2)}`;
}

window.actualizarCantidadCompra = function(index, value) {
    const cantidad = parseInt(value);
    if (cantidad > 0) {
        window.carritoCompra[index].cantidad = cantidad;
        renderizarTablaCompra();
    }
};

window.actualizarCostoCompra = function(index, value) {
    const costo = parseFloat(value);
    if (costo >= 0) {
        window.carritoCompra[index].costo = costo;
        renderizarTablaCompra();
    }
};

window.eliminarItemCompra = function(index) {
    window.carritoCompra.splice(index, 1);
    renderizarTablaCompra();
};

// === CONDICIÓN ===
document.getElementById('compra-condicion')?.addEventListener('change', (e) => {
    document.getElementById('div-vencimiento').classList.toggle('hidden', e.target.value !== 'Credito');
});

// === GUARDAR ===
window.guardarCompra = async function() {
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
            btn.innerHTML = '<?= Icons::get("check", "w-5 h-5") ?> Guardar Compra';
        }
    }
};

// === INIT ===
document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('btn-guardar-compra')?.addEventListener('click', (e) => {
        e.preventDefault();
        window.guardarCompra();
    });
    renderizarTablaCompra();
});

function escapeHTML(str) {
    if (!str) return '';
    return str.replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));
}
</script>