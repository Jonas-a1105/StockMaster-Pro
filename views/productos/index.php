<?php
/**
 * INVENTARIO - Vista Enterprise
 * views/productos/index.php
 */
use App\Helpers\Icons;
use App\Core\View;

$productos = $productos ?? [];
$proveedores = $proveedores ?? [];
$categorias = $categorias ?? [];
$paginaActual = $paginaActual ?? 1;
$totalPaginas = $totalPaginas ?? 1;
$terminoBusqueda = $terminoBusqueda ?? '';
?>

<!-- Estilos específicos de la página -->
<link rel="stylesheet" href="<?= BASE_URL ?>css/pages/productos.css?v=<?= time() ?>">


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
    
    <?= View::component('button', [
        'label' => 'Nuevo Producto',
        'icon' => 'plus',
        'attributes' => View::raw('onclick="openModal(\'modal-agregar-producto\')"'),
        'class' => 'shadow-lg shadow-emerald-500/30 hover:shadow-emerald-500/40'
    ]) ?>
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


</div>

<!-- Tabla -->
<?php
// Preparar contenido de las filas
ob_start();
    if (empty($productos)): ?>
        <!-- Handled by component empty prop -->
    <?php else: 
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
            
            <td class="selection-col w-0 opacity-0 overflow-hidden px-0 py-3 transition-all duration-300 ease-out border-b border-slate-100 dark:border-slate-700 group-hover:bg-indigo-50/30">
                <div class="w-10 flex justify-center">
                    <label class="relative flex items-center justify-center cursor-pointer p-2 rounded-full hover:bg-slate-100 dark:hover:bg-slate-600">
                        <input type="checkbox" name="selected_products[]" value="<?= $p['id'] ?>" 
                               onchange="updateBulkDeleteState()"
                               class="peer sr-only product-checkbox">
                        <div class="visual-checkbox w-5 h-5 flex items-center justify-center rounded-full border-2 border-slate-300 dark:border-slate-500 bg-white dark:bg-transparent transition-all duration-200">
                            <?= Icons::get('check', 'checkmark-icon w-3.5 h-3.5 text-white opacity-0 transition-all duration-200 transform scale-90') ?>
                        </div>
                    </label>
                </div>
            </td>

            <td class="px-6 py-4">
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
            
            <td class="px-6 py-4 text-center">
                <div class="flex flex-col items-center">
                    <span class="font-semibold <?= $stockClass ?>"><?= $p['stock'] ?></span>
                    <?= $stockBadge ?>
                </div>
            </td>
            
            <td class="px-6 py-4 text-center">
                <?php if ($p['tiene_iva'] ?? false): ?>
                    <span class="px-2 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 text-xs font-medium rounded-lg">
                        <?= intval($p['iva_porcentaje'] ?? 16) ?>%
                    </span>
                <?php else: ?>
                    <span class="text-slate-400 text-xs">—</span>
                <?php endif; ?>
            </td>
            
            <td class="px-6 py-4 text-right">
                <div class="flex flex-col items-end currency-wrapper" data-usd="<?= $p['precioCompraUSD'] ?>">
                    <span class="price-main font-mono text-slate-600 dark:text-slate-300">$<?= number_format($p['precioCompraUSD'], 2) ?></span>
                    <span class="price-sec block text-xs text-slate-400 precio-compra-ves">Bs. --</span>
                </div>
            </td>
            
            <td class="px-6 py-4 text-right">
                <div class="flex flex-col items-end currency-wrapper" data-usd="<?= $p['precioVentaUSD'] ?>">
                    <span class="price-main font-mono font-semibold text-slate-800 dark:text-white">$<?= number_format($p['precioVentaUSD'], 2) ?></span>
                    <span class="price-sec block text-xs text-emerald-600 dark:text-emerald-400 precio-venta-ves">Bs. --</span>
                </div>
            </td>
            
            <td class="px-6 py-4 text-right">
                <div class="flex flex-col items-end currency-wrapper" data-usd="<?= $p['gananciaUnitariaUSD'] ?>" data-text-class="text-emerald-600 dark:text-emerald-400">
                     <span class="price-main font-mono text-emerald-600 dark:text-emerald-400">+$<?= number_format($p['gananciaUnitariaUSD'], 2) ?></span>
                     <span class="price-sec block text-xs text-slate-400 ganancia-ves">Bs. --</span>
                </div>
                <span class="block text-xs text-slate-400 mt-0.5"><?= $p['margen_ganancia'] ?? 0 ?>% margen</span>
            </td>
            
            <td class="px-6 py-4 text-right hidden xl:table-cell">
                <div class="flex flex-col items-end currency-wrapper" data-usd="<?= $valorStock ?>">
                    <span class="price-main font-mono font-semibold text-slate-800 dark:text-white">$<?= number_format($valorStock, 2) ?></span>
                    <span class="price-sec block text-xs text-slate-400 valor-venta-total-ves">Bs. --</span>
                </div>
            </td>
            
            <td class="px-6 py-4 text-center">
                <div class="flex items-center justify-center gap-1">
                    <button onclick="editarProducto(<?= $p['id'] ?>)" 
                            class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg transition-colors"
                            title="Editar">
                        <?= Icons::get('edit', 'w-4 h-4') ?>
                    </button>
                    <button onclick="eliminarProducto(<?= $p['id'] ?>)" 
                            class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition-colors"
                            title="Eliminar">
                        <?= Icons::get('trash', 'w-4 h-4') ?>
                    </button>
                </div>
            </td>
        </tr>
        <?php endforeach; 
    endif;
    $tableContent = ob_get_clean();

    $buildUrl = function($page) use ($terminoBusqueda) {
        return "index.php?controlador=producto&accion=index&pagina=$page&busqueda=" . urlencode($terminoBusqueda);
    };

    echo \App\Core\View::component('table', [
        'tableId' => 'tabla-inventario',
        'tbodyId' => 'tabla-body',
        'headers' => [
            ['label' => '', 'align' => 'left', 'class' => 'selection-col w-0 opacity-0 overflow-hidden px-0 py-3 transition-all duration-300 ease-out'],
            ['label' => 'Producto', 'align' => 'left', 'class' => 'w-[280px]'],
            ['label' => 'Stock', 'align' => 'center'],
            ['label' => 'IVA', 'align' => 'center'],
            ['label' => 'P. Compra', 'align' => 'right'],
            ['label' => 'P. Venta', 'align' => 'right'],
            ['label' => 'Ganancia', 'align' => 'right'],
            ['label' => 'Valor Stock', 'align' => 'right', 'class' => 'hidden xl:table-cell'],
            ['label' => 'Acciones', 'align' => 'center']
        ],
        'content' => View::raw($tableContent),
        'empty' => empty($productos),
        'emptyMsg' => 'No hay productos que coincidan',
        'pagination' => [
            'current' => $paginaActual ?? 1,
            'total' => $totalPaginas ?? 1,
            'limit' => $porPagina ?? 10,
            'url_builder' => $buildUrl,
            'limit_name' => 'limite'
        ]
    ]);
    ?>

<!-- MODALES -->

<?php
echo View::component('modal', [
    'id' => 'modal-agregar-producto',
    'title' => 'Nuevo Producto',
    'size' => 'lg',
    'content' => View::partial('productos/form_crear', ['categorias' => $categorias, 'proveedores' => $proveedores]),
    'footer' => View::raw('
        <div class="flex gap-3 w-full">
            ' . View::component('button', [
                'label' => 'Cancelar',
                'variant' => 'outline',
                'class' => 'flex-1',
                'attributes' => View::raw('onclick="closeModal(\'modal-agregar-producto\')"')
            ]) . '
            ' . View::component('button', [
                'label' => 'Guardar Producto',
                'variant' => 'primary',
                'class' => 'flex-1',
                'attributes' => View::raw('form="form-agregar-producto" type="submit"')
            ]) . '
        </div>
    ')
]);
?>

<?php
echo View::component('modal', [
    'id' => 'modal-editar-producto',
    'title' => 'Editar Producto',
    'size' => 'lg',
    'content' => View::partial('productos/form_editar', [
        'categorias' => $categorias, 
        'proveedores' => $proveedores
    ]),
    'footer' => View::raw('
        <div class="flex gap-3 w-full">
            ' . View::component('button', [
                'label' => 'Cancelar',
                'variant' => 'outline',
                'class' => 'flex-1',
                'attributes' => View::raw('onclick="closeModal(\'modal-editar-producto\')"')
            ]) . '
            ' . View::component('button', [
                'label' => 'Guardar Cambios',
                'variant' => 'primary',
                'class' => 'flex-1',
                'attributes' => View::raw('form="form-editar-producto" type="submit"')
            ]) . '
        </div>
    ')
]);
?>

<?php
echo View::component('modal', [
    'id' => 'modal-eliminar-producto',
    'title' => '¿Eliminar producto?',
    'size' => 'sm',
    'content' => View::raw('
        <div class="text-center">
            <div class="w-16 h-16 bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 rounded-full flex items-center justify-center mx-auto mb-4">
                ' . Icons::get('trash', 'w-8 h-8') . '
            </div>
            <p class="text-slate-500 dark:text-slate-400 text-sm mb-2">Esta acción no se puede deshacer. El producto será eliminado del inventario permanentemente.</p>
        </div>
    '),
    'footer' => View::raw('
        <div class="flex gap-3 w-full">
            ' . View::component('button', [
                'label' => 'Cancelar',
                'variant' => 'outline',
                'class' => 'flex-1',
                'attributes' => View::raw('onclick="closeModal(\'modal-eliminar-producto\')"')
            ]) . '
            ' . View::component('button', [
                'id' => 'btn-confirmar-borrar-producto',
                'label' => 'Eliminar',
                'variant' => 'danger',
                'class' => 'flex-1 shadow-lg shadow-red-500/30'
            ]) . '
        </div>
    ')
]);
?>

<?php
echo View::component('modal', [
    'id' => 'modal-eliminar-masivo',
    'title' => View::raw('Eliminar <span id="count-eliminar-masivo">0</span> productos'),
    'size' => 'md',
    'content' => View::raw('
        <div class="text-center mb-6">
            <div class="w-16 h-16 bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 rounded-full flex items-center justify-center mx-auto mb-4">
                ' . Icons::get('trash', 'w-8 h-8') . '
            </div>
            <p class="text-slate-500 dark:text-slate-400 text-sm">
                Para confirmar, escribe <span class="font-bold text-slate-700 dark:text-slate-200">ELIMINAR</span> en el campo de abajo.
            </p>
        </div>
        <input type="text" id="input-verificacion-eliminar" placeholder="Escribe ELIMINAR"
               class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-xl text-center font-bold text-slate-800 dark:text-white mb-2 focus:outline-none focus:ring-2 focus:ring-red-500/30 uppercase transition-all">
    '),
    'footer' => View::raw('
        <div class="flex gap-3 w-full">
            ' . View::component('button', [
                'label' => 'Cancelar',
                'variant' => 'outline',
                'class' => 'flex-1',
                'attributes' => View::raw('onclick="closeModal(\'modal-eliminar-masivo\')"')
            ]) . '
            ' . View::component('button', [
                'id' => 'btn-confirmar-eliminar-masivo',
                'label' => 'Confirmar',
                'variant' => 'danger',
                'class' => 'flex-1 shadow-lg shadow-red-500/30 opacity-50 cursor-not-allowed',
                'attributes' => View::raw('disabled onclick="procesarEliminacionMasiva()"')
            ]) . '
        </div>
    ')
]);
?>

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
