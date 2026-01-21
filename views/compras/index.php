<?php
/**
 * COMPRAS - Index Enterprise
 * views/compras/index.php
 */
use App\Helpers\Icons;

$compras = $compras ?? [];
$filtro = $filtro ?? '';

// Pagination Helper
$buildUrl = function($page) use ($filtro) {
    $url = "index.php?controlador=compra&accion=index&pagina={$page}";
    if (!empty($filtro)) $url .= "&estado=" . urlencode($filtro);
    return $url;
};
?>

<!-- Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-800 dark:text-white flex items-center gap-3">
            <?= Icons::get('purchases', 'w-7 h-7 text-indigo-500') ?>
            Gestión de Compras
        </h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
            Control de cuentas por pagar y reposición de inventario
        </p>
    </div>
    
    <a href="index.php?controlador=compra&accion=crear" class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold shadow-lg shadow-emerald-500/30 transition-all">
        <?= Icons::get('plus-circle', 'w-5 h-5') ?>
        Registrar Compra
    </a>
</div>

<!-- Filtros Estado -->
<div class="flex flex-wrap gap-2 mb-6">
    <a href="index.php?controlador=compra&accion=index" 
       class="px-4 py-2 rounded-lg text-sm font-medium transition-all <?= $filtro === '' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/20' : 'bg-white dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-600' ?>">
       Todas
    </a>
    <a href="index.php?controlador=compra&accion=index&estado=Pendiente" 
       class="px-4 py-2 rounded-lg text-sm font-medium transition-all <?= $filtro === 'Pendiente' ? 'bg-amber-500 text-white shadow-md shadow-amber-500/20' : 'bg-white dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-600' ?>">
       Por Pagar
    </a>
    <a href="index.php?controlador=compra&accion=index&estado=Pagada" 
       class="px-4 py-2 rounded-lg text-sm font-medium transition-all <?= $filtro === 'Pagada' ? 'bg-emerald-500 text-white shadow-md shadow-emerald-500/20' : 'bg-white dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-600' ?>">
       Pagadas
    </a>
</div>

<!-- Tabla Compras -->
<?php
// Preparar contenido de las filas
ob_start();
$compraModelView = new \App\Models\CompraModel();
foreach ($compras as $c): 
    $classVence = '';
    $vence = new DateTime($c['fecha_vencimiento']);
    $hoy = new DateTime();
    if ($c['estado'] === 'Pendiente' && $vence < $hoy) $classVence = 'text-red-500 font-bold';
    
    // Items Logic
    $items = $compraModelView->obtenerItems($c['id']);
    $itemsStr = implode(', ', array_map(fn($i) => $i['nombre_producto'] . ' (x' . $i['cantidad'] . ')', array_slice($items, 0, 3)));
    if(count($items) > 3) $itemsStr .= '...';
?>
<tr class="hover:bg-slate-50 dark:hover:bg-slate-600/30 transition-colors">
    <td class="px-6 py-4 font-mono text-slate-600 dark:text-slate-300">
        <?= htmlspecialchars($c['nro_factura']) ?>
    </td>
    <td class="px-6 py-4 font-medium text-slate-800 dark:text-white">
        <?= htmlspecialchars($c['proveedor_nombre']) ?>
    </td>
    <td class="px-6 py-4 text-xs text-slate-500 dark:text-slate-400 max-w-xs truncate">
        <?php if(empty($items)): ?>
            <span class="text-slate-300 dark:text-slate-500 italic">Sin detalle</span>
        <?php else: ?>
            <?= htmlspecialchars($itemsStr) ?>
        <?php endif; ?>
    </td>
    <td class="px-6 py-4 text-xs">
        <div class="text-slate-600 dark:text-slate-300">Emit: <?= date('d/m/Y', strtotime($c['fecha_emision'])) ?></div>
        <div class="<?= $classVence ?: 'text-slate-400' ?>">Venc: <?= $vence->format('d/m/Y') ?></div>
    </td>
    <td class="px-6 py-4 text-right font-bold text-slate-700 dark:text-slate-200">
        $<?= number_format($c['total_usd'] ?? 0, 2) ?>
    </td>
    <td class="px-6 py-4 text-center">
        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium <?= $c['estado']==='Pagada' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' ?>">
            <?= $c['estado'] ?>
        </span>
    </td>
    <td class="px-6 py-4 text-center">
        <?php if ($c['estado'] === 'Pendiente'): ?>
            <button onclick="confirmarPagoCompra(<?= $c['id'] ?>, '<?= $c['nro_factura'] ?>')" 
                    class="p-2 text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/30 rounded-lg transition-colors" title="Registrar Pago">
                <?= Icons::get('check-circle', 'w-5 h-5') ?>
            </button>
        <?php else: ?>
            <span class="text-emerald-500" title="Pagada">
                <?= Icons::get('check', 'w-5 h-5') ?>
            </span>
        <?php endif; ?>
    </td>
</tr>
<?php endforeach;
$tableContent = ob_get_clean();

echo View::component('table', [
    'headers' => [
        ['label' => 'Factura', 'align' => 'left'],
        ['label' => 'Proveedor', 'align' => 'left'],
        ['label' => 'Productos', 'align' => 'left'],
        ['label' => 'Emisión / Venc.', 'align' => 'left'],
        ['label' => 'Total', 'align' => 'right'],
        ['label' => 'Estado', 'align' => 'center'],
        ['label' => 'Acción', 'align' => 'center']
    ],
    'content' => $tableContent,
    'empty' => empty($compras),
    'emptyMsg' => 'No hay compras registradas',
    'emptyIcon' => 'search'
]);
?>

<!-- Paginación y Selector -->
<div class="flex flex-col sm:flex-row items-center justify-between gap-4 mt-6 px-2">
    
    <!-- Selector de Límite -->
    <div class="flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400">
        <span>Mostrar</span>
        <select id="limit-selector"
                data-setup-simple-select
                onchange="window.location.href='index.php?controlador=compra&accion=index&limite=' + this.value + '&estado=<?= urlencode($filtro) ?>'"
                class="bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-700 dark:text-slate-300 py-1 px-2 focus:outline-none focus:ring-2 focus:ring-blue-500/20">
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
        
        $prevUrl = "index.php?controlador=compra&accion=index&pagina=" . ($paginaActual - 1) . "&limite=$actual&estado=" . urlencode($filtro);
        $nextUrl = "index.php?controlador=compra&accion=index&pagina=" . ($paginaActual + 1) . "&limite=$actual&estado=" . urlencode($filtro);
        ?>

        <!-- Anterior -->
        <a href="<?= $prevUrl ?>" 
           class="p-2 rounded-lg border border-slate-200 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors <?= $paginaActual <= 1 ? 'pointer-events-none opacity-50' : '' ?>">
            <?= Icons::get('chevron-left', 'w-4 h-4 text-slate-600 dark:text-slate-300') ?>
        </a>

        <!-- Primera página si estamos lejos -->
        <?php if ($inicio > 1): ?>
            <a href="index.php?controlador=compra&accion=index&pagina=1&limite=<?= $actual ?>&estado=<?= urlencode($filtro) ?>" 
               class="px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 text-sm font-medium transition-colors">
                1
            </a>
            <?php if ($inicio > 2): ?>
                <span class="px-2 text-slate-400">...</span>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Páginas numeradas -->
        <?php for ($i = $inicio; $i <= $fin; $i++): ?>
            <a href="index.php?controlador=compra&accion=index&pagina=<?= $i ?>&limite=<?= $actual ?>&estado=<?= urlencode($filtro) ?>" 
               class="px-3 py-1.5 rounded-lg text-sm font-medium transition-all
                      <?= $i == $paginaActual 
                          ? 'bg-blue-500 text-white shadow-lg shadow-blue-500/30' 
                          : 'bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-600' ?>">
                <?= $i ?>
            </a>
        <?php endfor; ?>

        <!-- Última página si estamos lejos -->
        <?php if ($fin < $totalPaginas): ?>
            <?php if ($fin < $totalPaginas - 1): ?>
                <span class="px-2 text-slate-400">...</span>
            <?php endif; ?>
            <a href="index.php?controlador=compra&accion=index&pagina=<?= $totalPaginas ?>&limite=<?= $actual ?>&estado=<?= urlencode($filtro) ?>" 
               class="px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 text-sm font-medium transition-colors">
                <?= $totalPaginas ?>
            </a>
        <?php endif; ?>

        <!-- Siguiente -->
        <a href="<?= $nextUrl ?>" 
           class="p-2 rounded-lg border border-slate-200 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors <?= $paginaActual >= $totalPaginas ? 'pointer-events-none opacity-50' : '' ?>">
            <?= Icons::get('chevron-left', 'w-4 h-4 text-slate-600 dark:text-slate-300') ?>
        </a>
    </div>
    <?php endif; ?>
</div>

<!-- Modal Confirmacion Pago -->
<?php
echo View::component('modal', [
    'id' => 'modal-pago-compra',
    'title' => 'Confirmar Pago',
    'size' => 'md',
    'content' => '
        <div class="flex flex-col items-center sm:flex-row sm:items-start gap-4">
            <div class="flex-shrink-0 flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-900/30">
                ' . Icons::get('dollar', 'w-6 h-6 text-emerald-600 dark:text-emerald-400') . '
            </div>
            <div>
                <p class="text-sm text-slate-500 dark:text-slate-300">
                    ¿Deseas marcar la factura <span id="pago-factura-ref" class="font-bold text-slate-700 dark:text-white"></span> como pagada?
                    <br><br>
                    Esto registrará la salida de dinero y actualizará el estado financiero del sistema.
                </p>
                <form id="form-pagar-compra" action="index.php?controlador=compra&accion=marcarPagada" method="POST" class="hidden">
                    <?= \App\Helpers\Security::csrfField() ?>
                    <input type="hidden" name="id" id="pago-compra-id">
                </form>
            </div>
        </div>
    ',
    'footer' => '
        <div class="flex flex-col sm:flex-row-reverse gap-3 w-full">
            ' . View::component('button', [
                'label' => 'Sí, Pagar Ahora',
                'variant' => 'primary',
                'attributes' => 'form="form-pagar-compra" type="submit"',
                'class' => 'w-full sm:w-auto bg-emerald-600 hover:bg-emerald-500'
            ]) . '
            ' . View::component('button', [
                'label' => 'Cancelar',
                'variant' => 'outline',
                'attributes' => 'type="button" onclick="cerrarModalPago()"',
                'class' => 'w-full sm:w-auto'
            ]) . '
        </div>
    '
]);
?>

<!-- Módulo de Lista de Compras (cargado desde archivo externo) -->
<script src="<?= BASE_URL ?>js/pages/compras-index.js?v=<?= time() ?>"></script>