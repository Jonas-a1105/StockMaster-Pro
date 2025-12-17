<?php
/**
 * MOVIMIENTOS - Vista Enterprise
 * views/movimientos/index.php
 */
use App\Helpers\Icons;

$productos = $productos ?? [];
$proveedores = $proveedores ?? [];
$movimientos = $movimientos ?? [];
$paginaActual = $paginaActual ?? 1;
$totalPaginas = $totalPaginas ?? 1;
$totalRegistros = $totalRegistros ?? 0;
?>

<!-- Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-800 dark:text-white flex items-center gap-3">
            <?= Icons::get('movements', 'w-7 h-7 text-indigo-500') ?>
            Movimientos de Stock
        </h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
            Registra y audita todos los cambios en tu inventario
        </p>
    </div>
    
    <div class="flex items-center gap-3">
        <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 rounded-lg text-sm font-medium">
            <?= Icons::get('info', 'w-4 h-4') ?>
            <span>Total: <?= $totalRegistros ?></span>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
    
    <!-- Columna Izquierda: Formulario (1/3) -->
    <div class="xl:col-span-1 space-y-6">
        <div class="bg-white dark:bg-slate-700/50 rounded-2xl border border-slate-200 dark:border-slate-600 p-5 sticky top-24">
            <h3 class="font-semibold text-slate-800 dark:text-white flex items-center gap-2 mb-4 pb-3 border-b border-slate-100 dark:border-slate-600">
                <?= Icons::get('plus-circle', 'w-5 h-5 text-emerald-500') ?>
                Registrar Movimiento
            </h3>
            
            <form id="form-movimiento" action="index.php?controlador=movimiento&accion=crear" method="POST" class="space-y-4">
                
                <!-- Producto (Combobox) -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Producto</label>
                    <div class="relative z-50" id="combobox-producto-mov">
                        <input type="hidden" name="mov-producto" id="mov-producto-hidden" required>
                        
                        <div class="relative">
                            <input type="text" id="producto-input-visual" 
                                   class="w-full px-4 py-2.5 bg-slate-100 dark:bg-slate-600 border-0 rounded-xl text-slate-800 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 font-medium"
                                   placeholder="Selecciona un producto..." autocomplete="off">
                                   
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>

                            <button type="button" id="btn-limpiar-prod-mov" class="absolute inset-y-0 right-8 flex items-center pr-1 text-slate-400 hover:text-red-500 hidden z-10 cursor-pointer">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <ul id="producto-list-mov" class="absolute z-50 w-full mt-1 bg-white dark:bg-slate-700 rounded-xl shadow-xl border border-slate-200 dark:border-slate-600 max-h-60 overflow-y-auto hidden">
                            <!-- JS Injected -->
                        </ul>
                    </div>
                </div>
                
                <!-- Tipo -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Tipo de Movimiento</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="cursor-pointer">
                            <input type="radio" name="mov-tipo" value="Entrada" class="peer sr-only" required onchange="actualizarMotivos()">
                            <div class="flex flex-col items-center justify-center p-3 rounded-xl border-2 border-slate-100 dark:border-slate-600 peer-checked:border-emerald-500 peer-checked:bg-emerald-50 dark:peer-checked:bg-emerald-900/20 transition-all text-center">
                                <?= Icons::get('trending-up', 'w-6 h-6 text-slate-400 peer-checked:text-emerald-500 mb-1') ?>
                                <span class="text-sm font-medium text-slate-500 dark:text-slate-400 peer-checked:text-emerald-600 dark:peer-checked:text-emerald-400">Entrada</span>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="mov-tipo" value="Salida" class="peer sr-only" required onchange="actualizarMotivos()">
                            <div class="flex flex-col items-center justify-center p-3 rounded-xl border-2 border-slate-100 dark:border-slate-600 peer-checked:border-red-500 peer-checked:bg-red-50 dark:peer-checked:bg-red-900/20 transition-all text-center">
                                <?= Icons::get('trending-down', 'w-6 h-6 text-slate-400 peer-checked:text-red-500 mb-1') ?>
                                <span class="text-sm font-medium text-slate-500 dark:text-slate-400 peer-checked:text-red-600 dark:peer-checked:text-red-400">Salida</span>
                            </div>
                        </label>
                    </div>
                </div>
                
                <!-- Motivo -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Motivo</label>
                    <select id="mov-motivo" name="mov-motivo" required data-setup-simple-select class="w-full px-4 py-2.5 bg-slate-100 dark:bg-slate-600 border-0 rounded-xl text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                        <option value="">Selecciona tipo primero...</option>
                    </select>
                </div>
                
                <!-- Proveedor (Condicional) -->
                <div id="mov-proveedor-group" class="hidden">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Proveedor</label>
                    <!-- Combobox Proveedor -->
                    <div class="relative z-40" id="combobox-proveedor-mov">
                        <input type="hidden" name="mov-proveedor" id="mov-proveedor-hidden" value="0">
                        
                        <div class="relative">
                            <input type="text" id="proveedor-input-visual" 
                                   class="w-full px-4 py-2.5 bg-slate-100 dark:bg-slate-600 border-0 rounded-xl text-slate-800 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 font-medium"
                                   placeholder="Buscar proveedor..." autocomplete="off">
                                   
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>

                            <button type="button" id="btn-limpiar-prov-mov" class="absolute inset-y-0 right-8 flex items-center pr-1 text-slate-400 hover:text-red-500 hidden z-10 cursor-pointer">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <ul id="proveedor-list-mov" class="absolute z-50 w-full mt-1 bg-white dark:bg-slate-700 rounded-xl shadow-xl border border-slate-200 dark:border-slate-600 max-h-60 overflow-y-auto hidden">
                            <!-- JS Injected -->
                        </ul>
                    </div>
                </div>
                
                <!-- Cantidad -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Cantidad</label>
                    <input type="number" name="mov-cantidad" placeholder="0" min="1" required 
                           class="w-full px-4 py-2.5 bg-slate-100 dark:bg-slate-600 border-0 rounded-xl text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30 font-semibold">
                </div>
                
                <!-- Nota -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Nota (Opcional)</label>
                    <textarea name="mov-nota" placeholder="Ej: Factura #001, Cliente Juan..." rows="2"
                              class="w-full px-4 py-2.5 bg-slate-100 dark:bg-slate-600 border-0 rounded-xl text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30 resize-none"></textarea>
                </div>
                
                <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold shadow-lg shadow-indigo-500/30 transition-all">
                    <?= Icons::get('save', 'w-5 h-5') ?>
                    Registrar Movimiento
                </button>
            </form>
        </div>
    </div>
    
    <!-- Columna Derecha: Historial (2/3) -->
    <div class="xl:col-span-2">
        <div class="bg-white dark:bg-slate-700/50 rounded-2xl border border-slate-200 dark:border-slate-600 p-5 h-full flex flex-col">
            <h3 class="font-semibold text-slate-800 dark:text-white flex items-center gap-2 mb-4 pb-3 border-b border-slate-100 dark:border-slate-600">
                <?= Icons::get('history', 'w-5 h-5 text-slate-400') ?>
                Historial Reciente
            </h3>
            
            <div class="overflow-x-auto flex-1">
                <table class="w-full text-sm text-left">
                    <thead>
                        <tr class="text-xs text-slate-500 dark:text-slate-400 uppercase border-b border-slate-100 dark:border-slate-600">
                            <th class="px-4 py-3 font-semibold">Fecha</th>
                            <th class="px-4 py-3 font-semibold">Producto</th>
                            <th class="px-4 py-3 font-semibold text-center">Tipo</th>
                            <th class="px-4 py-3 font-semibold">Motivo</th>
                            <th class="px-4 py-3 font-semibold text-right">Cant.</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-600">
                        <?php if (empty($movimientos)): ?>
                            <tr>
                                <td colspan="5" class="px-4 py-12 text-center">
                                    <div class="flex flex-col items-center">
                                        <?= Icons::get('search', 'w-12 h-12 text-slate-200 dark:text-slate-600 mb-2') ?>
                                        <p class="text-slate-400">No hay movimientos registrados</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($movimientos as $m): 
                                $isEntrada = $m['tipo'] === 'Entrada';
                                $fecha = new DateTime($m['fecha']);
                            ?>
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-600/30 transition-colors">
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="block font-medium text-slate-700 dark:text-slate-200"><?= $fecha->format('d/m/Y') ?></span>
                                    <span class="text-xs text-slate-400"><?= $fecha->format('h:i A') ?></span>
                                </td>
                                <td class="px-4 py-3">
                                    <p class="font-medium text-slate-800 dark:text-white truncate max-w-[150px]"><?= htmlspecialchars($m['productoNombre']) ?></p>
                                    <?php if (!empty($m['proveedor'])): ?>
                                        <p class="text-xs text-slate-400 truncate max-w-[150px]"><?= htmlspecialchars($m['proveedor']) ?></p>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-xs font-medium <?= $isEntrada ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' ?>">
                                        <?= $isEntrada ? Icons::get('arrow-down', 'w-3 h-3') : Icons::get('arrow-up', 'w-3 h-3') ?>
                                        <?= $m['tipo'] ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">
                                    <?= htmlspecialchars($m['motivo']) ?>
                                    <?php if (!empty($m['nota'])): ?>
                                        <span class="block text-xs text-slate-400 italic truncate max-w-[150px]"><?= htmlspecialchars($m['nota']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-right font-bold font-mono <?= $isEntrada ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' ?>">
                                    <?= $isEntrada ? '+' : '-' ?><?= $m['cantidad'] ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Paginación -->
            <!-- Paginación y Selector -->
            <?php if ($totalPaginas > 1 || !empty($movimientos)): ?>
            <div class="flex flex-col sm:flex-row items-center justify-between mt-4 pt-4 border-t border-slate-100 dark:border-slate-600 gap-4">
                
                <!-- Selector Límite -->
                <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                    <span>Ver</span>
                    <select id="limit-selector-mov" data-setup-simple-select
                            onchange="window.location.href='index.php?controlador=movimiento&accion=index&limite=' + this.value + '&pagina=1'"
                            class="bg-white dark:bg-slate-600 border border-slate-200 dark:border-slate-500 rounded-lg py-1 px-2 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                        <?php 
                        $opciones = $opcionesLimite ?? [3, 5, 7, 10, 25, 50, 100];
                        $actual = $porPagina ?? 20;
                        foreach ($opciones as $op): 
                        ?>
                            <option value="<?= $op ?>" <?= $actual == $op ? 'selected' : '' ?>><?= $op ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Paginación Numerada -->
                <?php if ($totalPaginas > 1): ?>
                <div class="flex items-center gap-1">
                    <?php 
                    $rango = 1; 
                    $inicio = max(1, $paginaActual - $rango);
                    $fin = min($totalPaginas, $paginaActual + $rango);
                    ?>

                    <!-- Prev -->
                    <a href="index.php?controlador=movimiento&accion=index&pagina=<?= max(1, $paginaActual - 1) ?>&limite=<?= $actual ?>" 
                       class="p-1.5 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-600 rounded-lg <?= $paginaActual <= 1 ? 'pointer-events-none opacity-50' : '' ?>">
                        <?= Icons::get('chevron-left', 'w-4 h-4') ?>
                    </a>

                    <!-- Primero -->
                    <?php if ($inicio > 1): ?>
                        <a href="index.php?controlador=movimiento&accion=index&pagina=1&limite=<?= $actual ?>" class="px-2.5 py-1 text-xs rounded-lg hover:bg-slate-100 dark:hover:bg-slate-600 text-slate-600 dark:text-slate-300">1</a>
                        <?php if ($inicio > 2): ?><span class="text-slate-400 text-xs">...</span><?php endif; ?>
                    <?php endif; ?>

                    <!-- Loop -->
                    <?php for ($i = $inicio; $i <= $fin; $i++): ?>
                        <a href="index.php?controlador=movimiento&accion=index&pagina=<?= $i ?>&limite=<?= $actual ?>" 
                           <?php if ($i == $paginaActual): ?>
                               style="background-color: #6366f1 !important; color: white !important;"
                               class="px-2.5 py-1 text-xs rounded-lg font-medium transition-colors shadow-md shadow-indigo-500/20"
                           <?php else: ?>
                               class="px-2.5 py-1 text-xs rounded-lg font-medium transition-colors hover:bg-slate-100 dark:hover:bg-slate-600 text-slate-600 dark:text-slate-300"
                           <?php endif; ?>>
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>

                    <!-- Último -->
                    <?php if ($fin < $totalPaginas): ?>
                        <?php if ($fin < $totalPaginas - 1): ?><span class="text-slate-400 text-xs">...</span><?php endif; ?>
                        <a href="index.php?controlador=movimiento&accion=index&pagina=<?= $totalPaginas ?>&limite=<?= $actual ?>" class="px-2.5 py-1 text-xs rounded-lg hover:bg-slate-100 dark:hover:bg-slate-600 text-slate-600 dark:text-slate-300"><?= $totalPaginas ?></a>
                    <?php endif; ?>

                    <!-- Next -->
                    <a href="index.php?controlador=movimiento&accion=index&pagina=<?= min($totalPaginas, $paginaActual + 1) ?>&limite=<?= $actual ?>" 
                       class="p-1.5 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-600 rounded-lg <?= $paginaActual >= $totalPaginas ? 'pointer-events-none opacity-50' : '' ?>">
                        <?= Icons::get('chevron-right', 'w-4 h-4') ?>
                    </a>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>


<!-- Safe Data Injection -->
<script type="application/json" id="productos-data">
<?= json_encode(array_map(function($p) {
    return [
        'id' => $p['id'],
        'nombre' => $p['nombre'] . ' (Stock: ' . $p['stock'] . ')' 
    ];
}, $productos), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>
</script>

<script type="application/json" id="proveedores-data">
<?= json_encode(array_map(function($p) {
    return [
        'id' => $p['id'],
        'nombre' => $p['nombre']
    ];
}, $proveedores), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>
</script>

<!-- Módulo de Movimientos (cargado desde archivo externo) -->
<script src="<?= BASE_URL ?>js/pages/movimientos.js?v=<?= time() ?>"></script>