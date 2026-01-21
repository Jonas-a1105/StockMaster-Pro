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
                <div id="pos-resultados-busqueda" class="dropdown-list-floating hidden"></div>
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
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Producto</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase w-20">Cant.</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase w-24">Precio</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase w-28">Total</th>
                        <th class="px-4 py-3 w-12"></th>
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
                <div id="cliente-resultados" class="dropdown-list-floating hidden"></div>
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
                    <select id="metodo-pago" data-setup-simple-select class="w-full px-4 py-2.5 bg-slate-100 dark:bg-slate-600 border-0 rounded-xl text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
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
                    <select id="estado-pago" data-setup-simple-select class="w-full px-4 py-2.5 bg-slate-100 dark:bg-slate-600 border-0 rounded-xl text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30" onchange="verificarCredito()">
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
// =========================================================================
// DATOS DESDE PHP (requeridos por el módulo ventas-pos.js)
// =========================================================================
window.tasaCambioBS = <?= json_encode((float)$tasaBCV) ?>;
console.log('[POS] Tasa de cambio:', window.tasaCambioBS);
</script>

<!-- Módulo de Ventas POS (cargado desde archivo externo) -->
<script src="<?= BASE_URL ?>js/pages/ventas-pos.js?v=<?= time() ?>"></script>

