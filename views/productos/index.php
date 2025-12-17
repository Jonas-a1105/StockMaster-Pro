<?php
/**
 * INVENTARIO - Vista Enterprise
 * views/productos/index.php
 */
use App\Helpers\Icons;

$productos = $productos ?? [];
$proveedores = $proveedores ?? [];
$categorias = $categorias ?? [];
$paginaActual = $paginaActual ?? 1;
$totalPaginas = $totalPaginas ?? 1;
$terminoBusqueda = $terminoBusqueda ?? '';
?>

<!-- Header -->

<!-- Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-800 dark:text-white flex items-center gap-2">
            <?= Icons::get('inventory', 'w-8 h-8 text-emerald-500') ?>
            Inventario
        </h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
            Gestiona tu catálogo de productos
        </p>
    </div>
    
    <button onclick="openModal('modal-agregar-producto')" 
            class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl font-medium shadow-lg shadow-emerald-500/30 hover:shadow-emerald-500/40 transition-all">
        <?= Icons::get('plus', 'w-5 h-5') ?>
        <span>Nuevo Producto</span>
    </button>
</div>

<!-- Toolbar -->
<div class="flex flex-col lg:flex-row gap-4 mb-6 justify-between">
    
    <!-- Unified Left Group (Switch + Actions + Search) -->
    <div class="flex-1 flex items-center gap-3">
        <!-- Modo Selección Switch -->
        <button id="toggle-selection-mode" onclick="toggleSelectionMode()" 
                class="flex-shrink-0 flex items-center justify-center p-2 rounded-full text-slate-600 dark:text-slate-300 transition-colors hover:bg-slate-100 dark:hover:bg-slate-700"
                title="Activar Modo Selección">
            <div class="w-9 h-5 rounded-full bg-slate-300 dark:bg-slate-500 transition-colors flex items-center px-[2px]" id="selection-switch-bg">
                <div class="w-4 h-4 rounded-full bg-white shadow-sm transition-transform duration-300" id="selection-switch-knob"></div>
            </div>
        </button>

        <!-- Wrapper Animado para Acciones Masivas -->
        <div id="bulk-actions-wrapper" class="flex-none hidden">
            <div class="flex items-center gap-2 min-w-max">
                <button onclick="toggleSelectAll()" id="btn-select-all"
                        class="whitespace-nowrap px-3 py-2.5 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-xl font-medium hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors text-sm">
                    Todo
                </button>
                <button onclick="confirmarEliminacionMasiva()" id="btn-delete-bulk" disabled
                        class="whitespace-nowrap flex items-center gap-2 px-3 py-2.5 bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 rounded-xl font-medium hover:bg-red-200 dark:hover:bg-red-900/50 transition-colors disabled:opacity-50 disabled:cursor-not-allowed text-sm">
                    <?= Icons::get('trash', 'w-4 h-4') ?>
                    <span>Eliminar (<span id="count-selected">0</span>)</span>
                </button>
            </div>
        </div>

        <!-- Búsqueda (Flexible) -->
        <div class="flex-1 relative transition-all duration-300">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <?= Icons::get('search', 'w-5 h-5 text-slate-400') ?>
            </div>
            <input type="text" 
                   id="busqueda-input" 
                   value="<?= htmlspecialchars($terminoBusqueda) ?>"
                   placeholder="Buscar..."
                   class="w-full pl-12 pr-4 py-2.5 bg-slate-100 dark:bg-slate-700 border-0 rounded-xl text-slate-800 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 transition-all">
        </div>
    </div>
    
    <!-- Acciones Derecha -->
    <div class="flex items-center gap-3">
        <!-- Selector Modo Vista -->
        <select id="currency-display-mode" 
                data-setup-simple-select
                class="px-3 py-2.5 bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 rounded-xl font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500/30 text-xs sm:text-sm">
            <option value="mixed">Mixto ($ / Bs)</option>
            <option value="usd">Solo Dólares ($)</option>
            <option value="ves">Solo Bolívares (Bs)</option>
        </select>

        <!-- Divisor Vertical -->
        <div class="h-8 w-px bg-slate-200 dark:bg-slate-700 mx-1"></div>

        <!-- Toggle Herramientas -->
        <label class="flex items-center gap-2 cursor-pointer bg-white dark:bg-slate-700 px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-600 transition-colors" title="Mostrar herramientas avanzadas">
            <input type="checkbox" id="toggle-tools" class="w-4 h-4 text-emerald-500 rounded border-slate-300 focus:ring-emerald-500/30" onchange="toggleTools(this)">
            <span class="text-xs font-medium text-slate-600 dark:text-slate-300 select-none">Herramientas</span>
        </label>

        <!-- Contenedor de Herramientas (Oculto por defecto) -->
        <div id="tools-container" class="hidden flex gap-2 animate-fade-in-right">
            <button onclick="exportarInventario('csv')" 
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-700 text-emerald-700 dark:text-emerald-400 rounded-xl font-medium hover:bg-emerald-100 dark:hover:bg-emerald-900/50 transition-colors shadow-sm">
                <?= Icons::get('download', 'w-4 h-4') ?>
                <span class="hidden sm:inline">CSV</span>
            </button>

            <button onclick="document.getElementById('input-importar').click()" 
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 rounded-xl font-medium hover:bg-slate-50 dark:hover:bg-slate-600 transition-colors shadow-sm">
                <?= Icons::get('upload', 'w-4 h-4') ?>
                <span class="hidden sm:inline">Importar</span>
            </button>
            <input type="file" id="input-importar" accept=".csv" class="hidden" onchange="importarInventario(this)">
        </div>
    </div>

    <script>
        function toggleTools(checkbox) {
            const container = document.getElementById('tools-container');
            if (checkbox.checked) {
                container.classList.remove('hidden');
            } else {
                container.classList.add('hidden');
            }
        }
    </script>
</div>

<!-- Tabla -->
<style>
    /* === ANIMACIÓN MODO SELECCIÓN === */
    
    .animate-fade-in-right {
        animation: fadeInRight 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }
    
    @keyframes fadeInRight {
        from { opacity: 0; transform: translateX(-10px); }
        to { opacity: 1; transform: translateX(0); }
    }
    
    /* Default: Hide columns when not in selection mode */
    #tabla-inventario:not(.selection-visible) .selection-col {
        display: none !important;
    }
    
    /* Base state: columns visible but collapsed */
    #tabla-inventario.selection-visible .selection-col {
        width: 0;
        min-width: 0;
        max-width: 0;
        padding-left: 0;
        padding-right: 0;
        opacity: 0;
        overflow: hidden;
        transition: width 0.4s cubic-bezier(0.4, 0, 0.2, 1),
                    max-width 0.4s cubic-bezier(0.4, 0, 0.2, 1),
                    opacity 0.3s ease-out,
                    padding 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    /* Expanded state: columns fully visible */
    #tabla-inventario.selection-expanded .selection-col {
        width: 3.5rem !important;
        min-width: 3.5rem;
        max-width: 3.5rem;
        padding-left: 0.5rem;
        padding-right: 0.5rem;
        opacity: 1 !important;
    }
    
    /* Bulk Actions Wrapper - Base collapsed state */
    #bulk-actions-wrapper {
        max-width: 0;
        opacity: 0;
        overflow: hidden;
        padding-right: 0;
        margin-right: 0;
        pointer-events: none;
        transition: max-width 0.4s cubic-bezier(0.4, 0, 0.2, 1),
                    opacity 0.35s ease-out,
                    padding 0.4s cubic-bezier(0.4, 0, 0.2, 1),
                    margin 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    /* Bulk Actions Wrapper - Expanded state */
    #bulk-actions-wrapper.expanded {
        max-width: 280px;
        opacity: 1;
        padding-right: 12px;
        margin-right: 8px;
        pointer-events: auto;
    }
    
    /* Force Checkbox Visibility (Bypassing Tailwind JIT) */
    .product-checkbox:checked + .visual-checkbox {
        background-color: #ef4444 !important; /* red-500 */
        border-color: #ef4444 !important;
    }
    .product-checkbox:checked + .visual-checkbox .checkmark-icon {
        opacity: 1 !important;
        transform: scale(1) !important;
    }
</style>
<div class="overflow-x-auto rounded-2xl border border-slate-200 dark:border-slate-700">
    <table class="w-full text-sm" id="tabla-inventario">
        <thead class="bg-slate-50 dark:bg-slate-700/50">
            <tr>
                <!-- Checkbox Header (Hidden by default) -->
                <th class="selection-col w-0 opacity-0 overflow-hidden px-0 py-3 transition-all duration-300 ease-out border-b border-slate-200 dark:border-slate-700">
                    <div class="w-10 flex justify-center">
                        <!-- Header Checkbox (Optional, or just spacer) -->
                    </div>
                </th>
                <th class="pl-4 pr-4 py-3 text-left font-semibold text-slate-600 dark:text-slate-300 w-[280px]">Producto</th>
                <th class="px-4 py-3 text-center font-semibold text-slate-600 dark:text-slate-300">Stock</th>
                <th class="px-4 py-3 text-center font-semibold text-slate-600 dark:text-slate-300">IVA</th>
                <th class="px-4 py-3 text-right font-semibold text-slate-600 dark:text-slate-300">P. Compra</th>
                <th class="px-4 py-3 text-right font-semibold text-slate-600 dark:text-slate-300">P. Venta</th>
                <th class="px-4 py-3 text-right font-semibold text-slate-600 dark:text-slate-300">Ganancia</th>
                <th class="px-4 py-3 text-right font-semibold text-slate-600 dark:text-slate-300 hidden xl:table-cell">Valor Stock</th>
                <th class="px-4 py-3 text-center font-semibold text-slate-600 dark:text-slate-300">Acciones</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-700" id="tabla-body">
            <?php if (empty($productos)): ?>
                <tr>
                    <td colspan="8" class="px-4 py-12 text-center">
                        <div class="flex flex-col items-center">
                            <?= Icons::get('inventory', 'w-12 h-12 text-slate-300 dark:text-slate-600 mb-3') ?>
                            <p class="text-slate-500 dark:text-slate-400 font-medium">No hay productos</p>
                            <p class="text-sm text-slate-400 dark:text-slate-500 mt-1">Agrega tu primer producto para comenzar</p>
                        </div>
                    </td>
                </tr>
            <?php else: ?>
                <?php 
                $umbral = $_SESSION['stock_umbral'] ?? 10;
                foreach ($productos as $p): 
                    $stockClass = '';
                    $stockBadge = '';
                    if ($p['stock'] == 0) {
                        $stockClass = 'text-red-600 dark:text-red-400';
                        $stockBadge = '<span class="ml-1 px-1.5 py-0.5 bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 text-xs font-medium rounded">Agotado</span>';
                    } elseif ($p['stock'] <= $umbral) {
                        $stockClass = 'text-amber-600 dark:text-amber-400';
                        $stockBadge = '<span class="ml-1 px-1.5 py-0.5 bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 text-xs font-medium rounded">Bajo</span>';
                    }
                    
                    $gastoTotal = $p['precioCompraUSD'] * $p['stock'];
                    $gananciaTotal = $p['gananciaUnitariaUSD'] * $p['stock'];
                    $valorStock = $p['precioVentaUSD'] * $p['stock'];

                    // Dynamic Icon Logic
                    $iconName = \App\Helpers\ProductIcons::get($p['nombre'], $p['categoria'] ?? '');
                    $iconStyle = \App\Helpers\ProductIcons::getBgColor($iconName);
                ?>
                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/30 transition-colors"
                    id="producto-fila-<?= $p['id'] ?>"
                    data-precio-compra-usd="<?= $p['precioCompraUSD'] ?>"
                    data-precio-venta-usd="<?= $p['precioVentaUSD'] ?>"
                    data-ganancia-usd="<?= $p['gananciaUnitariaUSD'] ?>"
                    data-gasto-total-usd="<?= $gastoTotal ?>"
                    data-ganancia-total-usd="<?= $gananciaTotal ?>"
                    data-valor-venta-total-usd="<?= $valorStock ?>">
                    
                    <!-- Checkbox Cell (Hidden by default) -->
                    <th class="selection-col w-0 opacity-0 overflow-hidden px-0 py-3 transition-all duration-300 ease-out border-b border-slate-100 dark:border-slate-700 group-hover:bg-indigo-50/30">
                        <div class="w-10 flex justify-center">
                            <label class="relative flex items-center justify-center cursor-pointer p-2 rounded-full hover:bg-slate-100 dark:hover:bg-slate-600">
                                <!-- Hidden Input -->
                                <input type="checkbox" name="selected_products[]" value="<?= $p['id'] ?>" 
                                       onchange="updateBulkDeleteState()"
                                       class="peer sr-only product-checkbox">
                                
                                <!-- Visual Checkbox Ring -->
                                <div class="visual-checkbox w-5 h-5 flex items-center justify-center rounded-full border-2 border-slate-300 dark:border-slate-500 bg-white dark:bg-transparent transition-all duration-200">
                                    <!-- Checkmark Icon -->
                                    <?= Icons::get('check', 'checkmark-icon w-3.5 h-3.5 text-white opacity-0 transition-all duration-200 transform scale-90') ?>
                                </div>
                            </label>
                        </div>
                    </th>

                    <!-- Producto -->
                    <td class="pl-0 pr-4 py-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-gradient-to-br <?= $iconStyle ?> rounded-lg flex items-center justify-center flex-shrink-0 shadow-sm">
                                <?= Icons::get($iconName, 'w-5 h-5') ?>
                            </div>
                            <div class="min-w-0 flex-1 max-w-[25ch]">
                                <p class="font-medium text-slate-800 dark:text-white truncate" title="<?= htmlspecialchars($p['nombre']) ?>">
                                    <?= htmlspecialchars($p['nombre']) ?>
                                </p>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <?php if (!empty($p['codigo_barras'])): ?>
                                        <span class="text-xs text-slate-400 font-mono"><?= htmlspecialchars($p['codigo_barras']) ?></span>
                                    <?php endif; ?>
                                    <span class="text-xs text-slate-400 truncate"><?= htmlspecialchars($p['categoria'] ?? 'Sin categoría') ?></span>
                                </div>
                            </div>
                        </div>
                    </td>
                    
                    <!-- Stock -->
                    <td class="px-4 py-3 text-center">
                        <span class="font-semibold <?= $stockClass ?>"><?= $p['stock'] ?></span>
                        <?= $stockBadge ?>
                    </td>
                    
                    <!-- IVA -->
                    <td class="px-4 py-3 text-center">
                        <?php if ($p['tiene_iva'] ?? false): ?>
                            <span class="px-2 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 text-xs font-medium rounded-lg">
                                <?= intval($p['iva_porcentaje'] ?? 16) ?>%
                            </span>
                        <?php else: ?>
                            <span class="text-slate-400 text-xs">—</span>
                        <?php endif; ?>
                    </td>
                    
                    <!-- Precio Compra -->
                    <td class="px-4 py-3 text-right">
                        <div class="flex flex-col items-end currency-wrapper" data-usd="<?= $p['precioCompraUSD'] ?>">
                            <span class="price-main font-mono text-slate-600 dark:text-slate-300">$<?= number_format($p['precioCompraUSD'], 2) ?></span>
                            <span class="price-sec block text-xs text-slate-400 precio-compra-ves">Bs. --</span>
                        </div>
                    </td>
                    
                    <!-- Precio Venta -->
                    <td class="px-4 py-3 text-right">
                        <div class="flex flex-col items-end currency-wrapper" data-usd="<?= $p['precioVentaUSD'] ?>">
                            <span class="price-main font-mono font-semibold text-slate-800 dark:text-white">$<?= number_format($p['precioVentaUSD'], 2) ?></span>
                            <span class="price-sec block text-xs text-emerald-600 dark:text-emerald-400 precio-venta-ves">Bs. --</span>
                        </div>
                    </td>
                    
                    <!-- Ganancia -->
                    <td class="px-4 py-3 text-right">
                        <div class="flex flex-col items-end currency-wrapper" data-usd="<?= $p['gananciaUnitariaUSD'] ?>" data-text-class="text-emerald-600 dark:text-emerald-400">
                             <span class="price-main font-mono text-emerald-600 dark:text-emerald-400">+$<?= number_format($p['gananciaUnitariaUSD'], 2) ?></span>
                             <span class="price-sec block text-xs text-slate-400 ganancia-ves">Bs. --</span>
                        </div>
                        <span class="block text-xs text-slate-400 mt-0.5"><?= $p['margen_ganancia'] ?? 30 ?>% margen</span>
                    </td>
                    
                    <!-- Valor Stock -->
                    <td class="px-4 py-3 text-right hidden xl:table-cell">
                        <div class="flex flex-col items-end currency-wrapper" data-usd="<?= $valorStock ?>">
                            <span class="price-main font-mono font-semibold text-slate-800 dark:text-white">$<?= number_format($valorStock, 2) ?></span>
                            <span class="price-sec block text-xs text-slate-400 valor-venta-total-ves">Bs. --</span>
                        </div>
                    </td>
                    
                    <!-- Acciones -->
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-center gap-1">
                            <button onclick="editarProducto(<?= $p['id'] ?>)" 
                                    class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg transition-colors"
                                    title="Editar">
                                <?= Icons::get('edit', 'w-4 h-4') ?>
                            </button>
                            <form action="index.php?controlador=producto&accion=eliminar" method="POST" class="inline form-eliminar">
                                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                <button type="submit" 
                                        class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition-colors"
                                        title="Eliminar">
                                    <?= Icons::get('trash', 'w-4 h-4') ?>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Paginación -->
<!-- Paginación y Selector -->
<div class="flex flex-col sm:flex-row items-center justify-between gap-4 mt-6 px-2">
    
    <!-- Selector de Límite -->
    <div class="flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400">
        <span>Mostrar</span>
        <select id="limit-selector"
                data-setup-simple-select
                onchange="window.location.href='index.php?controlador=producto&accion=index&limite=' + this.value + '&busqueda=<?= urlencode($terminoBusqueda) ?>'"
                class="bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-700 dark:text-slate-300 py-1 px-2 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
            <?php 
            $opciones = $opcionesLimite ?? [3, 5, 7, 10, 25, 50, 100];
            $actual = $porPagina ?? 10;
            foreach ($opciones as $op): 
            ?>
                <option value="<?= $op ?>" <?= $actual == $op ? 'selected' : '' ?>><?= $op ?></option>
            <?php endforeach; ?>
        </select>
        <span>por página</span>
    </div>

    <!-- Controles de Paginación -->
    <?php if ($totalPaginas > 1): ?>
    <div class="flex items-center gap-1">
        <?php 
        $rango = 2; // Número de páginas a mostrar alrededor de la actual
        $inicio = max(1, $paginaActual - $rango);
        $fin = min($totalPaginas, $paginaActual + $rango);
        
        $prevUrl = "index.php?controlador=producto&accion=index&pagina=" . ($paginaActual - 1) . "&limite=$actual&busqueda=" . urlencode($terminoBusqueda);
        $nextUrl = "index.php?controlador=producto&accion=index&pagina=" . ($paginaActual + 1) . "&limite=$actual&busqueda=" . urlencode($terminoBusqueda);
        ?>

        <!-- Anterior -->
        <a href="<?= $prevUrl ?>" 
           class="p-2 rounded-lg border border-slate-200 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors <?= $paginaActual <= 1 ? 'pointer-events-none opacity-50' : '' ?>">
            <?= Icons::get('chevron-left', 'w-4 h-4 text-slate-600 dark:text-slate-300') ?>
        </a>

        <!-- Primera página si estamos lejos -->
        <?php if ($inicio > 1): ?>
            <a href="index.php?controlador=producto&accion=index&pagina=1&limite=<?= $actual ?>&busqueda=<?= urlencode($terminoBusqueda) ?>" 
               class="px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 text-sm font-medium transition-colors">
                1
            </a>
            <?php if ($inicio > 2): ?>
                <span class="px-2 text-slate-400">...</span>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Páginas numeradas -->
        <?php for ($i = $inicio; $i <= $fin; $i++): ?>
            <a href="index.php?controlador=producto&accion=index&pagina=<?= $i ?>&limite=<?= $actual ?>&busqueda=<?= urlencode($terminoBusqueda) ?>" 
               class="px-3 py-1.5 rounded-lg text-sm font-medium transition-all
                      <?= $i == $paginaActual 
                          ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-500/30' 
                          : 'bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-600' ?>">
                <?= $i ?>
            </a>
        <?php endfor; ?>

        <!-- Última página si estamos lejos -->
        <?php if ($fin < $totalPaginas): ?>
            <?php if ($fin < $totalPaginas - 1): ?>
                <span class="px-2 text-slate-400">...</span>
            <?php endif; ?>
            <a href="index.php?controlador=producto&accion=index&pagina=<?= $totalPaginas ?>&limite=<?= $actual ?>&busqueda=<?= urlencode($terminoBusqueda) ?>" 
               class="px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 text-sm font-medium transition-colors">
                <?= $totalPaginas ?>
            </a>
        <?php endif; ?>

        <!-- Siguiente -->
        <a href="<?= $nextUrl ?>" 
           class="p-2 rounded-lg border border-slate-200 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors <?= $paginaActual >= $totalPaginas ? 'pointer-events-none opacity-50' : '' ?>">
            <?= Icons::get('chevron-right', 'w-4 h-4 text-slate-600 dark:text-slate-300') ?>
        </a>
    </div>
    <?php endif; ?>
</div>

<!-- MODALES -->

<!-- Modal Agregar Producto -->
<div id="modal-agregar-producto" class="hidden fixed inset-0 z-[100]">
    <!-- Backdrop (Visual only) -->
    <div class="fixed inset-0 bg-slate-200/50 dark:bg-slate-900/50 backdrop-blur-sm transition-opacity"></div>
    
    <!-- Wrapper (Clickable area) -->
    <div class="fixed inset-0 flex items-center justify-center p-4 overflow-y-auto" onclick="if(event.target === this) closeModal('modal-agregar-producto')">
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-lg my-8 relative fade-in">
            <!-- Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 dark:border-slate-700">
                <h3 class="text-lg font-semibold text-slate-800 dark:text-white">Nuevo Producto</h3>
                <button id="cerrar-modal-agregar-prod" onclick="closeModal('modal-agregar-producto')" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors">
                    <?= Icons::get('x', 'w-5 h-5') ?>
                </button>
            </div>
            
            <!-- Body -->
            <form id="form-agregar-producto" action="index.php?controlador=producto&accion=crear" method="POST" class="p-6 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Nombre del Producto</label>
                        <input type="text" name="nombre" required 
                               class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl text-slate-800 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Código de Barras</label>
                        <input type="text" name="codigo_barras" 
                               class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl text-slate-800 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Categoría</label>
                        <div class="relative z-50 group" id="combobox-categoria-add">
                            <!-- Hidden Native Select (for Form Submit) -->
                            <select name="categoria" id="categoria-select-hidden" class="sr-only" required>
                                <option value="">Seleccionar...</option>
                                <?php foreach ($categorias as $cat): ?>
                                    <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
                                <?php endforeach; ?>
                                <option value="Otros">Otros</option>
                            </select>

                            <!-- Custom Display (Solo lectura, sin búsqueda) -->
                            <div class="relative">
                                <input type="text" id="categoria-input-visual" 
                                       placeholder="Seleccionar categoría..." 
                                       readonly
                                       class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30 cursor-pointer">
                                
                                <!-- Icono Chevron -->
                                <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-slate-400">
                                    <svg class="w-4 h-4 transition-transform duration-200" id="categoria-chevron" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                            </div>

                            <!-- Dropdown List -->
                            <ul id="categoria-list-add" style="z-index: 9999;"
                                class="absolute left-0 right-0 top-full mt-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-xl shadow-xl max-h-60 overflow-y-auto hidden divide-y divide-slate-100 dark:divide-slate-700">
                                <!-- Options injected by JS -->
                            </ul>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Proveedor</label>
                        <div class="relative z-50 group" id="combobox-proveedor-add">
                            <!-- Hidden Native Select (for Form Submit) -->
                            <select name="proveedor_id" id="proveedor-select-hidden" class="sr-only">
                                <option value="0">Sin proveedor</option>
                                <?php foreach ($proveedores as $prov): ?>
                                    <option value="<?= $prov['id'] ?>"><?= htmlspecialchars($prov['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>

                            <!-- Custom Display Input -->
                            <div class="relative">
                                <input type="text" id="proveedor-input-visual" 
                                       placeholder="Seleccionar proveedor..." 
                                       readonly
                                       class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30 cursor-pointer caret-emerald-500">
                                
                                <!-- Icono Chevron -->
                                <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-slate-400">
                                    <svg class="w-4 h-4 transition-transform duration-200" id="proveedor-chevron" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                                
                                <!-- Botón Limpiar -->
                                <button type="button" id="btn-limpiar-prov-add" class="absolute inset-y-0 right-8 flex items-center px-2 text-slate-400 hover:text-red-500 hidden cursor-pointer z-10">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>

                            <!-- Dropdown List -->
                            <ul id="proveedor-list-add" style="z-index: 9999;"
                                class="absolute left-0 right-0 top-full mt-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-xl shadow-xl max-h-60 overflow-y-auto hidden divide-y divide-slate-100 dark:divide-slate-700">
                                <!-- Options injected by JS -->
                            </ul>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Stock Inicial</label>
                        <input type="number" name="stock" placeholder="0" min="0" required 
                               class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Precio Base (USD)</label>
                        <input type="number" name="precio_base" step="0.01" min="0" required 
                               id="add-precio-base"
                               class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Margen (%)</label>
                        <input type="number" name="margen_ganancia" placeholder="30" min="0" max="100" required 
                               id="add-margen"
                               class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                    </div>
                    
                    <!-- IVA -->
                    <div class="col-span-2">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="tiene_iva" id="add-tiene-iva" value="1"
                                   class="w-5 h-5 rounded border-slate-300 text-emerald-500 focus:ring-emerald-500/30">
                            <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Aplicar IVA</span>
                        </label>
                    </div>
                    
                    <div id="add-iva-grupo" class="col-span-2 hidden">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Porcentaje IVA</label>
                        <input type="number" name="iva_porcentaje" value="16" min="0" max="100" 
                               id="add-iva-porcentaje"
                               class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                    </div>
                </div>
                
                <!-- Preview -->
                <div class="p-4 bg-slate-50 dark:bg-slate-700/50 rounded-xl">
                    <p class="text-sm text-slate-500 dark:text-slate-400 mb-2">Vista previa de precios</p>
                    <div class="grid grid-cols-3 gap-4 text-center">
                        <div>
                            <p class="text-xs text-slate-400">Compra</p>
                            <p class="text-lg font-semibold text-slate-800 dark:text-white" id="add-preview-compra">$0.00</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400">Venta</p>
                            <p class="text-lg font-semibold text-emerald-600" id="add-preview-venta">$0.00</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400">Ganancia</p>
                            <p class="text-lg font-semibold text-blue-600" id="add-preview-ganancia">$0.00</p>
                        </div>
                    </div>
                </div>
                
                <!-- Footer -->
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="closeModal('modal-agregar-producto')" 
                            class="flex-1 px-4 py-2.5 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 rounded-xl font-medium hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                        Cancelar
                    </button>
                    <button type="submit" 
                            class="flex-1 px-4 py-2.5 bg-emerald-500 text-white rounded-xl font-medium hover:bg-emerald-600 transition-colors">
                        Guardar Producto
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Editar Producto -->
<div id="modal-editar-producto" class="hidden fixed inset-0 z-[100]">
    <div class="fixed inset-0 bg-slate-200/50 dark:bg-slate-900/50 backdrop-blur-sm transition-opacity" onclick="closeModal('modal-editar-producto')"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4 overflow-y-auto">
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-lg my-8 relative fade-in">
            <!-- Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 dark:border-slate-700">
                <h3 class="text-lg font-semibold text-slate-800 dark:text-white">Editar Producto</h3>
                <button onclick="closeModal('modal-editar-producto')" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors">
                    <?= Icons::get('x', 'w-5 h-5') ?>
                </button>
            </div>
            
            <!-- Body -->
            <form id="form-editar-producto" action="index.php?controlador=producto&accion=actualizar" method="POST" class="p-6 space-y-4">
                <input type="hidden" name="id" id="editar-id">
                
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Nombre</label>
                        <input type="text" name="nombre" id="editar-nombre" required 
                               class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Código</label>
                        <input type="text" name="codigo_barras" id="editar-codigo-barras"
                               class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Proveedor</label>
                        <div class="relative z-50 group" id="combobox-proveedor-edit">
                            <!-- Hidden Native Select -->
                            <select name="proveedor_id" id="editar-proveedor-hidden" class="sr-only">
                                <option value="0">Sin proveedor</option>
                                <?php foreach ($proveedores as $prov): ?>
                                    <option value="<?= $prov['id'] ?>"><?= htmlspecialchars($prov['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>

                            <!-- Custom Display Input -->
                            <div class="relative">
                                <input type="text" id="editar-proveedor-visual" 
                                       placeholder="Seleccionar proveedor..." 
                                       readonly
                                       class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30 cursor-pointer caret-emerald-500">
                                
                                <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-slate-400">
                                    <svg class="w-4 h-4 transition-transform duration-200" id="proveedor-chevron-edit" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                                
                                <button type="button" id="btn-limpiar-prov-edit" class="absolute inset-y-0 right-8 flex items-center px-2 text-slate-400 hover:text-red-500 hidden cursor-pointer z-10">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>

                            <!-- Dropdown List -->
                            <ul id="proveedor-list-edit" style="z-index: 9999;"
                                class="absolute left-0 right-0 top-full mt-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-xl shadow-xl max-h-60 overflow-y-auto hidden divide-y divide-slate-100 dark:divide-slate-700">
                            </ul>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Stock Actual</label>
                        <input type="number" name="stock" id="editar-stock" min="0" required
                               class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Precio Base (USD)</label>
                        <input type="number" name="precio_base" id="editar-precio-base" step="0.01" min="0" required 
                               class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Margen (%)</label>
                        <input type="number" name="margen_ganancia" id="editar-margen" min="0" max="100" required 
                               class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                    </div>
                    
                    <!-- IVA -->
                    <div class="col-span-2">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="tiene_iva" id="editar-tiene-iva" value="1"
                                   class="w-5 h-5 rounded border-slate-300 text-emerald-500 focus:ring-emerald-500/30">
                            <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Aplicar IVA</span>
                        </label>
                    </div>
                    
                    <div id="editar-iva-grupo" class="col-span-2 hidden">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Porcentaje IVA</label>
                        <input type="number" name="iva_porcentaje" id="editar-iva-porcentaje" value="16" min="0" max="100"
                               class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                    </div>
                </div>
                
                <!-- Preview -->
                <div class="p-4 bg-slate-50 dark:bg-slate-700/50 rounded-xl">
                    <div class="grid grid-cols-3 gap-4 text-center">
                        <div>
                            <p class="text-xs text-slate-400">Compra</p>
                            <p class="text-lg font-semibold text-slate-800 dark:text-white" id="preview-precio-compra">$0.00</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400">Venta</p>
                            <p class="text-lg font-semibold text-emerald-600" id="preview-precio-venta">$0.00</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400">Ganancia</p>
                            <p class="text-lg font-semibold text-blue-600" id="preview-ganancia">$0.00</p>
                        </div>
                    </div>
                </div>
                
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="closeModal('modal-editar-producto')" 
                            class="flex-1 px-4 py-2.5 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 rounded-xl font-medium hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                        Cancelar
                    </button>
                    <button type="submit" 
                            class="flex-1 px-4 py-2.5 bg-emerald-500 text-white rounded-xl font-medium hover:bg-emerald-600 transition-colors">
                        Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Confirmar Eliminar (Individual) -->
<div id="modal-eliminar-producto" class="hidden fixed inset-0 z-[110]">
    <div class="fixed inset-0 bg-slate-200/50 dark:bg-slate-900/50 backdrop-blur-sm transition-opacity" onclick="closeModal('modal-eliminar-producto')"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl w-full max-w-sm relative fade-in p-6 text-center">
            <div class="w-16 h-16 bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 rounded-full flex items-center justify-center mx-auto mb-4">
                <?= Icons::get('trash', 'w-8 h-8') ?>
            </div>
            
            <h3 class="text-lg font-bold text-slate-800 dark:text-white mb-2">¿Eliminar producto?</h3>
            <p class="text-slate-500 dark:text-slate-400 text-sm mb-6">Esta acción no se puede deshacer. El producto será eliminado del inventario permanentemente.</p>
            
            <div class="flex gap-3">
                <button onclick="closeModal('modal-eliminar-producto')" 
                        class="flex-1 px-4 py-2.5 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 rounded-xl font-medium hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                    Cancelar
                </button>
                <button id="btn-confirmar-borrar-producto"
                        class="flex-1 px-4 py-2.5 bg-red-500 text-white rounded-xl font-medium hover:bg-red-600 transition-colors shadow-lg shadow-red-500/30">
                    Eliminar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Eliminar Masivo -->
<div id="modal-eliminar-masivo" class="hidden fixed inset-0 z-[110]">
    <div class="fixed inset-0 bg-slate-200/50 dark:bg-slate-900/50 backdrop-blur-sm transition-opacity" onclick="closeModal('modal-eliminar-masivo')"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl w-full max-w-md relative fade-in p-6">
            <div class="text-center mb-6">
                <div class="w-16 h-16 bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 rounded-full flex items-center justify-center mx-auto mb-4">
                    <?= Icons::get('trash', 'w-8 h-8') ?>
                </div>
                <h3 class="text-lg font-bold text-slate-800 dark:text-white mb-2">Eliminar <span id="count-eliminar-masivo">0</span> productos</h3>
                <p class="text-slate-500 dark:text-slate-400 text-sm">
                    Para confirmar, escribe <span class="font-bold text-slate-700 dark:text-slate-200">ELIMINAR</span> en el campo de abajo.
                </p>
            </div>
            
            <input type="text" id="input-verificacion-eliminar" placeholder="Escribe ELIMINAR"
                   class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-xl text-center font-bold text-slate-800 dark:text-white mb-6 focus:outline-none focus:ring-2 focus:ring-red-500/30 uppercase transition-all">
            
            <div class="flex gap-3">
                <button onclick="closeModal('modal-eliminar-masivo')" 
                        class="flex-1 px-4 py-2.5 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 rounded-xl font-medium hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                    Cancelar
                </button>
                <button id="btn-confirmar-eliminar-masivo" disabled onclick="procesarEliminacionMasiva()"
                        class="flex-1 px-4 py-2.5 bg-red-500 text-white rounded-xl font-medium hover:bg-red-600 transition-colors shadow-lg shadow-red-500/30 opacity-50 cursor-not-allowed">
                    Confirmar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// =========================================================================
// DATOS DESDE PHP (requeridos por el módulo productos.js)
// =========================================================================
window.tasaCambio = <?= $tasaCambio ?>;
window.currencyMode = localStorage.getItem('currencyMode') || 'mixed';
window.listaProveedores = <?= json_encode(array_map(function($p){ return ['id'=>$p['id'], 'nombre'=>$p['nombre']]; }, $proveedores)) ?>;
window.svgIcons = <?= json_encode(\App\Helpers\Icons::getAll()) ?>;
window.listaCategorias = [
    <?php foreach ($categorias as $cat): ?>
    { id: '<?= htmlspecialchars($cat, ENT_QUOTES) ?>', nombre: '<?= htmlspecialchars($cat, ENT_QUOTES) ?>' },
    <?php endforeach; ?>
    { id: 'Otros', nombre: 'Otros' }
];
</script>

<!-- Módulo de Productos (cargado desde archivo externo) -->
<script src="<?= BASE_URL ?>js/pages/productos.js?v=<?= time() ?>"></script>
