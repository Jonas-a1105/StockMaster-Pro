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
<div class="flex flex-col lg:flex-row gap-4 mb-6">
    <!-- Búsqueda -->
    <div class="flex-1 relative">
        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
            <?= Icons::get('search', 'w-5 h-5 text-slate-400') ?>
        </div>
        <input type="text" 
               id="busqueda-input" 
               value="<?= htmlspecialchars($terminoBusqueda) ?>"
               placeholder="Buscar por nombre, código o categoría..."
               class="w-full pl-12 pr-4 py-3 bg-slate-100 dark:bg-slate-700 border-0 rounded-xl text-slate-800 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 transition-all">
    </div>
    
    <!-- Acciones -->
    <div class="flex gap-2">
        <!-- Selector Modo Vista -->
        <select id="currency-display-mode" 
                class="px-3 py-2.5 bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 rounded-xl font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500/30 text-xs sm:text-sm">
            <option value="mixed">Mixto ($ / Bs)</option>
            <option value="usd">Solo Dólares ($)</option>
            <option value="ves">Solo Bolívares (Bs)</option>
        </select>

        <button onclick="exportarInventario('csv')" 
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 rounded-xl font-medium hover:bg-slate-50 dark:hover:bg-slate-600 transition-colors">
            <?= Icons::get('download', 'w-4 h-4') ?>
            <span class="hidden sm:inline">Exportar</span>
        </button>
        <button onclick="document.getElementById('input-importar').click()" 
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 rounded-xl font-medium hover:bg-slate-50 dark:hover:bg-slate-600 transition-colors">
            <?= Icons::get('upload', 'w-4 h-4') ?>
            <span class="hidden sm:inline">Importar</span>
        </button>
        <input type="file" id="input-importar" accept=".csv" class="hidden" onchange="importarInventario(this)">
    </div>
</div>

<!-- Tabla -->
<div class="overflow-x-auto rounded-2xl border border-slate-200 dark:border-slate-700">
    <table class="w-full text-sm" id="tabla-inventario">
        <thead class="bg-slate-50 dark:bg-slate-700/50">
            <tr>
                <th class="px-4 py-3 text-left font-semibold text-slate-600 dark:text-slate-300 w-[280px]">Producto</th>
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
                    
                    <!-- Producto -->
                    <td class="px-4 py-3">
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
                            <span class="price-sec block text-xs text-slate-400">Bs. --</span>
                        </div>
                    </td>
                    
                    <!-- Precio Venta -->
                    <td class="px-4 py-3 text-right">
                        <div class="flex flex-col items-end currency-wrapper" data-usd="<?= $p['precioVentaUSD'] ?>">
                            <span class="price-main font-mono font-semibold text-slate-800 dark:text-white">$<?= number_format($p['precioVentaUSD'], 2) ?></span>
                            <span class="price-sec block text-xs text-emerald-600 dark:text-emerald-400">Bs. --</span>
                        </div>
                    </td>
                    
                    <!-- Ganancia -->
                    <td class="px-4 py-3 text-right">
                        <span class="font-mono text-emerald-600 dark:text-emerald-400">+$<?= number_format($p['gananciaUnitariaUSD'], 2) ?></span>
                        <span class="block text-xs text-slate-400"><?= $p['margen_ganancia'] ?? 30 ?>% margen</span>
                    </td>
                    
                    <!-- Valor Stock -->
                    <td class="px-4 py-3 text-right hidden xl:table-cell">
                        <div class="flex flex-col items-end currency-wrapper" data-usd="<?= $valorStock ?>">
                            <span class="price-main font-mono font-semibold text-slate-800 dark:text-white">$<?= number_format($valorStock, 2) ?></span>
                            <span class="price-sec block text-xs text-slate-400">Bs. --</span>
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
        <select onchange="window.location.href='index.php?controlador=producto&accion=index&limite=' + this.value + '&busqueda=<?= urlencode($terminoBusqueda) ?>'"
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
    <div class="fixed inset-0 bg-slate-200/50 dark:bg-slate-900/50 backdrop-blur-sm transition-opacity" onclick="closeModal('modal-agregar-producto')"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4 overflow-y-auto">
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-lg my-8 relative fade-in">
            <!-- Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 dark:border-slate-700">
                <h3 class="text-lg font-semibold text-slate-800 dark:text-white">Nuevo Producto</h3>
                <button onclick="closeModal('modal-agregar-producto')" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors">
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
                        <select name="categoria" required 
                                class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                            <option value="">Seleccionar...</option>
                            <?php foreach ($categorias as $cat): ?>
                                <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
                            <?php endforeach; ?>
                            <option value="Otros">Otros</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Proveedor</label>
                        <div class="relative group" id="combobox-proveedor-add">
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
                                       class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30 cursor-pointer caret-emerald-500"
                                       onfocus="this.removeAttribute('readonly');" 
                                       onblur="setTimeout(() => this.setAttribute('readonly', true), 200);">
                                
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
                            <ul id="proveedor-list-add" 
                                class="absolute left-0 right-0 top-full mt-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-xl shadow-xl max-h-60 overflow-y-auto hidden z-50 divide-y divide-slate-100 dark:divide-slate-700">
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
                        <div class="relative group" id="combobox-proveedor-edit">
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
                                       class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30 cursor-pointer caret-emerald-500"
                                       onfocus="this.removeAttribute('readonly');" 
                                       onblur="setTimeout(() => this.setAttribute('readonly', true), 200);">
                                
                                <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-slate-400">
                                    <svg class="w-4 h-4 transition-transform duration-200" id="proveedor-chevron-edit" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                                
                                <button type="button" id="btn-limpiar-prov-edit" class="absolute inset-y-0 right-8 flex items-center px-2 text-slate-400 hover:text-red-500 hidden cursor-pointer z-10">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>

                            <!-- Dropdown List -->
                            <ul id="proveedor-list-edit" 
                                class="absolute left-0 right-0 top-full mt-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-xl shadow-xl max-h-60 overflow-y-auto hidden z-50 divide-y divide-slate-100 dark:divide-slate-700">
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

<script>
// === CONFIGURACIÓN GLOBAL ===
window.tasaCambio = <?= $tasaCambio ?>;
window.currencyMode = localStorage.getItem('currencyMode') || 'mixed';
window.listaProveedores = <?= json_encode(array_map(function($p){ return ['id'=>$p['id'], 'nombre'=>$p['nombre']]; }, $proveedores)) ?>;

// Restaurar modo guardado
document.getElementById('currency-display-mode').value = window.currencyMode;

// === BÚSQUEDA CON DEBOUNCE ===
window.searchTimer = window.searchTimer || null;
window.searchInput = document.getElementById('busqueda-input');
// Use var or global assignment via destructuring if needed, but since it's const, 
// we must change it to var or window.prop if the script re-runs in the same scope. 
// However, 'replaceChild' creates a new script context in some environments but not all. 
// Safest is window prop.
// Inject icons from PHP for JS usage
window.svgIcons = <?= json_encode(\App\Helpers\Icons::getAll()) ?>;

window.searchInput = document.getElementById('busqueda-input');

window.searchInput?.addEventListener('input', (e) => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        const term = e.target.value;
        buscarProductos(term);
    }, 300);
});

async function buscarProductos(term) {
    try {
        const res = await fetch(`index.php?controlador=producto&accion=apiBuscar&term=${encodeURIComponent(term)}`);
        const productos = await res.json();
        renderizarTabla(productos);
    } catch (e) {
        console.error('Error búsqueda:', e);
    }
}

function renderizarTabla(productos) {
    const tbody = document.getElementById('tabla-body');
    if (!tbody) return;
    
    if (productos.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="px-4 py-12 text-center">
                    <p class="text-slate-500">No se encontraron productos</p>
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = productos.map(p => {
        const valorStock = p.precioVentaUSD * p.stock;
        
        // Helper Functions for Dynamic Icons (Mirrors PHP ProductIcons)
        const getIconData = (name, category) => {
            const mappings = {
                coffee: ['cafe', 'café', 'coffee', 'espresso', 'late', 'capuchino'],
                cookie: ['galleta', 'cookie', 'dulce', 'caramelo', 'chocolate', 'snack', 'confite'],
                bread: ['pan', 'harina', 'sandwich', 'torta', 'pastel', 'trigo', 'masa'],
                drink: ['refresco', 'jugo', 'bebida', 'agua', 'gaseosa', 'coca', 'pepsi', 'liquido'],
                droplet: ['aceite', 'salsa', 'vinagre', 'lubricante'],
                meat: ['carne', 'pollo', 'res', 'cerdo', 'embutido', 'jamon'],
                fish: ['pescado', 'atun', 'sardina', 'marisco'],
                carrot: ['fruta', 'verdura', 'vegetal', 'zanahoria', 'tomate', 'cebolla', 'papa'],
                tag: ['ropa', 'camisa', 'pantalon', 'zapato', 'vestido'],
                device: ['telefono', 'celular', 'laptop', 'computadora', 'mouse', 'teclado', 'cable', 'cargador'],
                tool: ['herramienta', 'martillo', 'clavo', 'tornillo', 'taladro'],
                medicine: ['medicina', 'pastilla', 'jarabe', 'farmacia', 'salud'],
                box: ['caja', 'paquete', 'bulto']
            };

            const colors = {
                coffee: 'from-amber-100 to-amber-200 text-amber-600',
                cookie: 'from-orange-100 to-orange-200 text-orange-600',
                bread: 'from-yellow-100 to-yellow-200 text-yellow-600',
                drink: 'from-blue-100 to-blue-200 text-blue-600',
                droplet: 'from-cyan-100 to-cyan-200 text-cyan-600',
                meat: 'from-red-100 to-red-200 text-red-600',
                fish: 'from-sky-100 to-sky-200 text-sky-600',
                carrot: 'from-green-100 to-green-200 text-green-600',
                tag: 'from-purple-100 to-purple-200 text-purple-600',
                device: 'from-indigo-100 to-indigo-200 text-indigo-600',
                medicine: 'from-rose-100 to-rose-200 text-rose-600',
                box: 'from-slate-100 to-slate-200 text-slate-500'
            };

            const search = (name + ' ' + (category || '')).toLowerCase();
            let iconKey = 'box';

            for (const [key, keywords] of Object.entries(mappings)) {
                if (keywords.some(k => search.includes(k))) {
                    iconKey = key;
                    break;
                }
            }
            // Fallback by category
            if (iconKey === 'box') {
                if ((category||'').toLowerCase().includes('alimento')) iconKey = 'food';
                if ((category||'').toLowerCase().includes('bebida')) iconKey = 'drink';
            }

            return {
                icon: iconKey,
                style: colors[iconKey] || colors['box']
            };
        };

        const iconData = getIconData(p.nombre, p.categoria);
        // Get SVG string and inject classes
        let svgHtml = window.svgIcons[iconData.icon] || window.svgIcons['box'];
        // Replace current class (assumed empty or not having specific size) with needed size
        // The PHP version uses str_replace('<svg ', '<svg class="..." '). 
        // We can do proper replacement or just wrap it. 
        // The Icons.php returns clean SVG usually within <svg ...>.
        // Let's attempt simple string injection or just use innerHTML injection if SVG string is clean.
        // We need to add 'w-5 h-5' class.
        svgHtml = svgHtml.replace('<svg ', '<svg class="w-5 h-5" ');

        const wrapPrice = (val, mainClass, secClass = 'text-slate-400') => `
            <div class="flex flex-col items-end currency-wrapper" data-usd="${val}">
                <span class="price-main font-mono ${mainClass}">$${parseFloat(val).toFixed(2)}</span>
                <span class="price-sec block text-xs ${secClass}">Bs. --</span>
            </div>
        `;

        return `
            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/30 transition-colors">
                <td class="px-4 py-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-gradient-to-br ${iconData.style} rounded-lg flex items-center justify-center flex-shrink-0 shadow-sm">
                            ${svgHtml}
                        </div>
                        <div class="min-w-0 flex-1 max-w-[25ch]">
                            <p class="font-medium text-slate-800 dark:text-white truncate" title="${escapeHTML(p.nombre)}">${escapeHTML(p.nombre)}</p>
                            <div class="flex items-center gap-2 mt-0.5">
                                ${p.codigo_barras ? `<span class="text-xs text-slate-400 font-mono">${escapeHTML(p.codigo_barras)}</span>` : ''}
                                <span class="text-xs text-slate-400 truncate">${escapeHTML(p.categoria || 'Sin categoría')}</span>
                            </div>
                        </div>
                    </div>
                </td>
                <td class="px-4 py-3 text-center">
                     <span class="font-semibold ${p.stock == 0 ? 'text-red-600 dark:text-red-400' : (p.stock <= 10 ? 'text-amber-600 dark:text-amber-400' : '')}">${p.stock}</span>
                     ${p.stock == 0 ? '<span class="ml-1 px-1.5 py-0.5 bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 text-xs font-medium rounded">Agotado</span>' : (p.stock <= 10 ? '<span class="ml-1 px-1.5 py-0.5 bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 text-xs font-medium rounded">Bajo</span>' : '')}
                </td>
                
                <td class="px-4 py-3 text-center">
                    ${p.tiene_iva ? `<span class="px-2 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 text-xs font-medium rounded-lg">${parseInt(p.iva_porcentaje)}%</span>` : '<span class="text-slate-400 text-xs">—</span>'}
                </td>
                
                <td class="px-4 py-3 text-right">
                    ${wrapPrice(p.precioCompraUSD, 'text-slate-600 dark:text-slate-300')}
                </td>
                
                <td class="px-4 py-3 text-right">
                    ${wrapPrice(p.precioVentaUSD, 'font-semibold text-slate-800 dark:text-white', 'text-emerald-600 dark:text-emerald-400')}
                </td>
                
                <td class="px-4 py-3 text-right">
                    <span class="font-mono text-emerald-600 dark:text-emerald-400">+$${parseFloat(p.gananciaUnitariaUSD).toFixed(2)}</span>
                    <span class="block text-xs text-slate-400">${p.margen_ganancia || 30}% margen</span>
                </td>
                
                <td class="px-4 py-3 text-right hidden xl:table-cell">
                    ${wrapPrice(valorStock, 'font-semibold text-slate-800 dark:text-white')}
                </td>
                
                <td class="px-4 py-3 text-center">
                    <div class="flex items-center justify-center gap-1">
                        <button onclick="editarProducto(${p.id})" class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg transition-colors" title="Editar">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </button>
                        <form action="index.php?controlador=producto&accion=eliminar" method="POST" class="inline form-eliminar" onsubmit="confirmarEliminar(event, this)">
                            <input type="hidden" name="id" value="${p.id}">
                            <button type="submit" class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition-colors" title="Eliminar">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
        `;
    }).join('');

    // Actualizar conversiones
    if (typeof actualizarPreciosDOM === 'function') {
        actualizarPreciosDOM();
    }
}

// === EDITAR PRODUCTO ===
async function editarProducto(id) {
    try {
        const res = await fetch(`index.php?controlador=producto&accion=apiObtener&id=${id}`);
        const p = await res.json();
        
        if (p.error) throw new Error(p.error);
        
        document.getElementById('editar-id').value = p.id;
        document.getElementById('editar-nombre').value = p.nombre;
        document.getElementById('editar-codigo-barras').value = p.codigo_barras || '';
        document.getElementById('editar-codigo-barras').value = p.codigo_barras || '';
        
        // Custom Combobox Setter
        const comboEdit = document.getElementById('combobox-proveedor-edit');
        if (comboEdit && typeof comboEdit.setComboboxValue === 'function') {
            comboEdit.setComboboxValue(p.proveedor_id || 0);
        } else {
             // Fallback for native select if JS failed
            document.getElementById('editar-proveedor-hidden').value = p.proveedor_id || 0;
        }

        document.getElementById('editar-stock').value = p.stock || 0;
        document.getElementById('editar-stock').value = p.stock || 0;
        document.getElementById('editar-precio-base').value = p.precio_base || p.precioCompraUSD;
        document.getElementById('editar-margen').value = p.margen_ganancia || 30;
        
        const tieneIva = document.getElementById('editar-tiene-iva');
        const ivaGrupo = document.getElementById('editar-iva-grupo');
        tieneIva.checked = p.tiene_iva == 1;
        ivaGrupo.classList.toggle('hidden', !tieneIva.checked);
        document.getElementById('editar-iva-porcentaje').value = p.iva_porcentaje || 16;
        
        // Preview
        document.getElementById('preview-precio-compra').textContent = `$${parseFloat(p.precioCompraUSD).toFixed(2)}`;
        document.getElementById('preview-precio-venta').textContent = `$${parseFloat(p.precioVentaUSD).toFixed(2)}`;
        document.getElementById('preview-ganancia').textContent = `$${parseFloat(p.gananciaUnitariaUSD).toFixed(2)}`;
        
        calcularPreviewEditar(); // Recalcular con datos frescos
        
        openModal('modal-editar-producto');
    } catch (e) {
        showToast(e.message, 'error');
    }
}

// === IVA TOGGLE ===
document.getElementById('add-tiene-iva')?.addEventListener('change', (e) => {
    document.getElementById('add-iva-grupo').classList.toggle('hidden', !e.target.checked);
    calcularPreviewAgregar();
});

document.getElementById('editar-tiene-iva')?.addEventListener('change', (e) => {
    document.getElementById('editar-iva-grupo').classList.toggle('hidden', !e.target.checked);
});

// === CALCULAR PREVIEW ===
function calcularPreviewAgregar() {
    const base = parseFloat(document.getElementById('add-precio-base')?.value) || 0;
    const margen = parseFloat(document.getElementById('add-margen')?.value) || 0;
    const tieneIva = document.getElementById('add-tiene-iva')?.checked;
    const ivaPct = parseFloat(document.getElementById('add-iva-porcentaje')?.value) || 0;
    
    const precioCompra = tieneIva ? base * (1 + ivaPct/100) : base;
    const precioVenta = precioCompra * (1 + margen/100);
    const ganancia = precioVenta - precioCompra;
    
    document.getElementById('add-preview-compra').textContent = `$${precioCompra.toFixed(2)}`;
    document.getElementById('add-preview-venta').textContent = `$${precioVenta.toFixed(2)}`;
    document.getElementById('add-preview-ganancia').textContent = `$${ganancia.toFixed(2)}`;
}

document.getElementById('add-precio-base')?.addEventListener('input', calcularPreviewAgregar);
document.getElementById('add-margen')?.addEventListener('input', calcularPreviewAgregar);
document.getElementById('add-iva-porcentaje')?.addEventListener('input', calcularPreviewAgregar);

// === CALCULAR PREVIEW EDITAR ===
function calcularPreviewEditar() {
    const base = parseFloat(document.getElementById('editar-precio-base')?.value) || 0;
    const margen = parseFloat(document.getElementById('editar-margen')?.value) || 0;
    const tieneIva = document.getElementById('editar-tiene-iva')?.checked;
    const ivaPct = parseFloat(document.getElementById('editar-iva-porcentaje')?.value) || 0;
    
    const precioCompra = tieneIva ? base * (1 + ivaPct/100) : base;
    const precioVenta = precioCompra * (1 + margen/100);
    const ganancia = precioVenta - precioCompra;
    
    document.getElementById('preview-precio-compra').textContent = `$${precioCompra.toFixed(2)}`;
    document.getElementById('preview-precio-venta').textContent = `$${precioVenta.toFixed(2)}`;
    document.getElementById('preview-ganancia').textContent = `$${ganancia.toFixed(2)}`;
}

document.getElementById('editar-precio-base')?.addEventListener('input', calcularPreviewEditar);
document.getElementById('editar-margen')?.addEventListener('input', calcularPreviewEditar);
document.getElementById('editar-iva-porcentaje')?.addEventListener('input', calcularPreviewEditar);
document.getElementById('editar-tiene-iva')?.addEventListener('change', calcularPreviewEditar);

// === EXPORTAR/IMPORTAR ===
function exportarInventario(formato) {
    window.location.href = `index.php?controlador=producto&accion=exportar&formato=${formato}`;
}

function importarInventario(input) {
    if (!input.files[0]) return;
    
    const formData = new FormData();
    formData.append('archivo', input.files[0]);
    
    fetch('index.php?controlador=producto&accion=importar', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast('Productos importados correctamente', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.message || 'Error al importar', 'error');
        }
    })
    .catch(e => showToast('Error al importar', 'error'));
    
    input.value = '';
}

// === ELIMINAR CON CONFIRMACIÓN ===
document.querySelectorAll('.form-eliminar').forEach(form => {
    form.addEventListener('submit', (e) => {
        e.preventDefault();
        window.formParaEliminar = form;
        openModal('modal-confirmar-eliminar');
    });
});

document.getElementById('btn-confirmar-eliminar')?.addEventListener('click', () => {
    if (window.formParaEliminar) {
        window.formParaEliminar.submit();
    }
});

// === COMBOBOX PROVEEDORES (Add & Edit) ===
function setupCombobox(wrapperId, hiddenSelectId, inputVisualId, listId, btnLimpiarId) {
    const wrapper = document.getElementById(wrapperId);
    if (!wrapper) return;

    const hiddenSelect = document.getElementById(hiddenSelectId);
    const inputVisual = document.getElementById(inputVisualId);
    const list = document.getElementById(listId);
    const btnLimpiar = document.getElementById(btnLimpiarId);
    
    // 1. Initial Render
    renderList('');
    
    // 2. Event Listeners
    inputVisual.addEventListener('click', () => {
        list.classList.toggle('hidden');
        if (!list.classList.contains('hidden')) {
            renderList('');
            inputVisual.focus();
        }
    });

    inputVisual.addEventListener('input', (e) => {
        const term = e.target.value;
        list.classList.remove('hidden');
        renderList(term);
        
        // Show clear button if text exists
        btnLimpiar.classList.toggle('hidden', term === '');
    });

    // Close on click outside
    document.addEventListener('click', (e) => {
        if (!wrapper.contains(e.target)) {
            list.classList.add('hidden');
        }
    });

    // Clear Button
    btnLimpiar.addEventListener('click', (e) => {
        e.stopPropagation();
        inputVisual.value = '';
        hiddenSelect.value = '0';
        btnLimpiar.classList.add('hidden');
        renderList('');
        list.classList.remove('hidden');
        inputVisual.focus();
    });

    function renderList(term) {
        list.innerHTML = '';
        const lowerTerm = term.toLowerCase();
        
        // Always include 'Sin proveedor' if matching or term empty
        if ('sin proveedor'.includes(lowerTerm)) {
             addItem({id: '0', nombre: 'Sin proveedor'});
        }

        const matches = window.listaProveedores.filter(p => p.nombre.toLowerCase().includes(lowerTerm));
        
        if (matches.length === 0 && lowerTerm !== '' && !'sin proveedor'.includes(lowerTerm)) {
            list.innerHTML += `<li class="px-4 py-3 text-sm text-slate-500 text-center">No encontrado</li>`;
        } else {
            matches.forEach(p => addItem(p));
        }
    }

    function addItem(p) {
        const li = document.createElement('li');
        li.className = 'px-4 py-2.5 text-sm text-slate-700 dark:text-slate-200 hover:bg-emerald-50 dark:hover:bg-slate-700 cursor-pointer transition-colors flex items-center justify-between group';
        if (hiddenSelect.value == p.id) {
            li.classList.add('bg-emerald-50', 'dark:bg-emerald-900/20', 'text-emerald-700', 'dark:text-emerald-400');
        }
        
        li.innerHTML = `<span>${p.nombre}</span>`;
        if (hiddenSelect.value == p.id) {
             li.innerHTML += `<svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>`;
        }

        li.onclick = () => {
            selectItem(p);
        };
        list.appendChild(li);
    }

    function selectItem(p) {
        hiddenSelect.value = p.id;
        inputVisual.value = p.nombre;
        list.classList.add('hidden');
        if(p.id !== '0') btnLimpiar.classList.remove('hidden');
    }
    
    // API Publica para setear valor externamente (ej: al editar)
    wrapper.setComboboxValue = function(id) {
        hiddenSelect.value = id;
        const p = window.listaProveedores.find(x => x.id == id);
        if (p) {
            inputVisual.value = p.nombre;
            btnLimpiar.classList.remove('hidden');
        } else {
            inputVisual.value = 'Sin proveedor';
            btnLimpiar.classList.add('hidden');
            if (id == 0) hiddenSelect.value = 0;
        }
    };
}

// Init
setupCombobox('combobox-proveedor-add', 'proveedor-select-hidden', 'proveedor-input-visual', 'proveedor-list-add', 'btn-limpiar-prov-add');
setupCombobox('combobox-proveedor-edit', 'editar-proveedor-hidden', 'editar-proveedor-visual', 'proveedor-list-edit', 'btn-limpiar-prov-edit');


// === LÓGICA DE MONEDA ===
// === LÓGICA DE MONEDA ===
// Eliminado const conflicto


// === LOGICA DE PRECIOS Y SELECTOR ===
function actualizarPreciosDOM() {
    const tasa = window.tasaCambio || 30;
    const mode = window.currencyMode || 'mixed';
    
    document.querySelectorAll('.currency-wrapper').forEach(el => {
        const usdValue = parseFloat(el.dataset.usd || 0);
        const vesValue = usdValue * tasa;
        
        const mainSpan = el.querySelector('.price-main');
        const secSpan = el.querySelector('.price-sec');
        
        if (!mainSpan || !secSpan) return;

        // Reset: Asegurar que ambos son visibles y tienen clases base correctas antes de switch
        mainSpan.classList.remove('hidden', 'text-sm');
        secSpan.classList.remove('hidden', 'text-lg', 'font-semibold');
        
        // Formatters
        const fmtUSD = (val) => `$${val.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
        const fmtVES = (val) => `Bs. ${val.toLocaleString('es-VE', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
        
        if (mode === 'mixed') {
            // Main: USD, Sec: VES
            mainSpan.textContent = fmtUSD(usdValue);
            secSpan.textContent = fmtVES(vesValue);
            secSpan.classList.add('text-xs');
        } else if (mode === 'usd') {
            // Main: USD
            mainSpan.textContent = fmtUSD(usdValue);
            // Sec: Hidden COMPLETAMENTE
            secSpan.textContent = ''; 
            secSpan.classList.add('hidden');
        } else if (mode === 'ves') {
            // Main: VES (Swap)
            mainSpan.textContent = fmtVES(vesValue);
            // Sec: Hidden
            secSpan.textContent = '';
            secSpan.classList.add('hidden');
        }
    });
}

// Event Listener para el selector
// Event Listener para el selector
if (document.getElementById('currency-display-mode')) {
    document.getElementById('currency-display-mode').addEventListener('change', (e) => {
        window.currencyMode = e.target.value;
        localStorage.setItem('currencyMode', window.currencyMode);
        actualizarPreciosDOM();
    });
}

// Escuchar cambios de tasa desde el Navbar (exchange-rate.js)
window.addEventListener('tasa-cambio-actualizada', (e) => {
    if (e.detail && e.detail.tasa) {
        window.tasaCambio = parseFloat(e.detail.tasa);
        actualizarPreciosDOM();
    }
});

// Inicializar precios al cargar
document.addEventListener('DOMContentLoaded', () => {
    actualizarPreciosDOM();
});

// Redefinición de renderizarTabla eliminada ya que se integró arriba


function escapeHTML(str) {
    if (!str) return '';
    return str.replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));
}
</script>
