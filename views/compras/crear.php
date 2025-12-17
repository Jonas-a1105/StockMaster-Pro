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
                    <!-- Wrapper con Z-Index alto para que el dropdown flote sobre todo -->
                    <div class="relative z-50" id="combobox-proveedor-compra">
                        <!-- Hidden Real Input -->
                        <input type="hidden" name="proveedor_id" id="compra-proveedor" value="">
                        
                        <!-- Visual Input -->
                        <div class="relative">
                            <input type="text" 
                                   id="proveedor-input-visual"
                                   class="w-full pl-4 pr-10 py-2.5 bg-slate-100 dark:bg-slate-600 border-0 rounded-xl text-slate-800 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/30 cursor-pointer"
                                   placeholder="Seleccionar proveedor..."
                                   autocomplete="off">
                                   
                            <!-- Icons -->
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <svg class="w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                            
                            <!-- Clear Button -->
                            <button type="button" id="btn-limpiar-prov-compra" class="absolute inset-y-0 right-8 flex items-center pr-1 text-slate-400 hover:text-red-500 hidden z-10">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <!-- Dropdown List (Z-Index 50) -->
                        <ul id="proveedor-list-compra" style="z-index: 9999;"
                            class="absolute w-full mt-1 bg-white dark:bg-slate-700 rounded-xl shadow-xl border border-slate-200 dark:border-slate-600 max-h-60 overflow-y-auto hidden">
                            <!-- JS populated -->
                        </ul>
                    </div>
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
                    <select id="compra-condicion" data-setup-simple-select class="w-full px-4 py-2.5 bg-slate-100 dark:bg-slate-600 border-0 rounded-xl text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/30">
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
                <div id="compra-resultados" style="z-index: 9999;" class="absolute left-0 right-0 top-full mt-2 bg-white dark:bg-slate-700 rounded-xl shadow-xl border border-slate-200 dark:border-slate-600 max-h-60 overflow-y-auto hidden"></div>
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

</div>

<!-- Data Injection safe escape (OUTSIDE JS) -->
<script id="proveedores-data" type="application/json">
    <?= json_encode($proveedores, JSON_HEX_TAG | JSON_HEX_AMP) ?>
</script>

<!-- Módulo de Crear Compras (cargado desde archivo externo) -->
<script src="<?= BASE_URL ?>js/pages/compras-crear.js?v=<?= time() ?>"></script>
