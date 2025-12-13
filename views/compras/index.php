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
<div class="bg-white dark:bg-slate-700/50 rounded-2xl border border-slate-200 dark:border-slate-600 overflow-hidden shadow-sm">
    <?php if (empty($compras)): ?>
        <div class="p-12 text-center">
            <?= Icons::get('search', 'w-16 h-16 mx-auto text-slate-200 dark:text-slate-600 mb-4') ?>
            <p class="text-slate-500 dark:text-slate-400 text-lg font-medium">No hay compras registradas</p>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-slate-50 dark:bg-slate-600/50 border-b border-slate-100 dark:border-slate-600">
                    <tr>
                        <th class="px-6 py-4 font-semibold text-slate-500 dark:text-slate-400 uppercase text-xs">Factura</th>
                        <th class="px-6 py-4 font-semibold text-slate-500 dark:text-slate-400 uppercase text-xs">Proveedor</th>
                        <th class="px-6 py-4 font-semibold text-slate-500 dark:text-slate-400 uppercase text-xs">Productos</th>
                        <th class="px-6 py-4 font-semibold text-slate-500 dark:text-slate-400 uppercase text-xs">Emisión / Venc.</th>
                        <th class="px-6 py-4 font-semibold text-slate-500 dark:text-slate-400 uppercase text-xs text-right">Total</th>
                        <th class="px-6 py-4 font-semibold text-slate-500 dark:text-slate-400 uppercase text-xs text-center">Estado</th>
                        <th class="px-6 py-4 font-semibold text-slate-500 dark:text-slate-400 uppercase text-xs text-center">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-600">
                    <?php foreach ($compras as $c): 
                        $classVence = '';
                        $vence = new DateTime($c['fecha_vencimiento']);
                        $hoy = new DateTime();
                        if ($c['estado'] === 'Pendiente' && $vence < $hoy) $classVence = 'text-red-500 font-bold';
                        
                        // Items Logic
                        $compraModel = new \App\Models\CompraModel();
                        $items = $compraModel->obtenerItems($c['id']);
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
                            <?= htmlspecialchars($itemsStr) ?>
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
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        <?php if (isset($totalPaginas) && $totalPaginas > 1): ?>
        <div class="flex items-center justify-between p-4 border-t border-slate-100 dark:border-slate-600">
            <p class="text-sm text-slate-500 dark:text-slate-400">
                Página <span class="font-medium"><?= $paginaActual ?></span> de <span class="font-medium"><?= $totalPaginas ?></span>
            </p>
            <div class="flex gap-2">
                <?php if ($paginaActual > 1): ?>
                    <a href="<?= $buildUrl($paginaActual - 1) ?>" class="px-4 py-2 text-sm bg-white dark:bg-slate-600 border border-slate-200 dark:border-slate-500 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-500 text-slate-600 dark:text-slate-200 transition-colors">
                        Anterior
                    </a>
                <?php endif; ?>
                <?php if ($paginaActual < $totalPaginas): ?>
                    <a href="<?= $buildUrl($paginaActual + 1) ?>" class="px-4 py-2 text-sm bg-white dark:bg-slate-600 border border-slate-200 dark:border-slate-500 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-500 text-slate-600 dark:text-slate-200 transition-colors">
                        Siguiente
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- Modal Confirmacion Pago -->
<div id="modal-pago-compra" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity opacity-0" id="backdrop-pago"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-800 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-md opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" id="panel-pago">
                <div class="bg-white dark:bg-slate-800 px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-900/30 sm:mx-0 sm:h-10 sm:w-10">
                            <?= Icons::get('dollar', 'w-6 h-6 text-emerald-600 dark:text-emerald-400') ?>
                        </div>
                        <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                            <h3 class="text-base font-semibold leading-6 text-slate-900 dark:text-white" id="modal-title">Confirmar Pago</h3>
                            <div class="mt-2">
                                <p class="text-sm text-slate-500 dark:text-slate-300">
                                    ¿Deseas marcar la factura <span id="pago-factura-ref" class="font-bold"></span> como pagada?
                                    <br>Esto registrará la salida de dinero y actualizará el estado financiero.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-slate-700/50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                    <form id="form-pagar-compra" action="index.php?controlador=compra&accion=marcarPagada" method="POST">
                        <input type="hidden" name="id" id="pago-compra-id">
                        <button type="submit" class="inline-flex w-full justify-center rounded-xl bg-emerald-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500 sm:ml-3 sm:w-auto transition-colors">
                            Sí, Pagar Ahora
                        </button>
                    </form>
                    <button type="button" onclick="cerrarModalPago()" class="mt-3 inline-flex w-full justify-center rounded-xl bg-white dark:bg-slate-600 px-3 py-2 text-sm font-semibold text-slate-900 dark:text-white shadow-sm ring-1 ring-inset ring-slate-300 dark:ring-slate-500 hover:bg-slate-50 dark:hover:bg-slate-500 sm:mt-0 sm:w-auto transition-colors">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function confirmarPagoCompra(id, factura) {
    const modal = document.getElementById('modal-pago-compra');
    const backdrop = document.getElementById('backdrop-pago');
    const panel = document.getElementById('panel-pago');
    
    document.getElementById('pago-compra-id').value = id;
    document.getElementById('pago-factura-ref').textContent = factura;
    
    modal.classList.remove('hidden');
    setTimeout(() => {
        backdrop.classList.remove('opacity-0');
        panel.classList.remove('opacity-0', 'translate-y-4', 'sm:translate-y-0', 'sm:scale-95');
    }, 10);
}

function cerrarModalPago() {
    const modal = document.getElementById('modal-pago-compra');
    const backdrop = document.getElementById('backdrop-pago');
    const panel = document.getElementById('panel-pago');
    
    backdrop.classList.add('opacity-0');
    panel.classList.add('opacity-0', 'translate-y-4', 'sm:translate-y-0', 'sm:scale-95');
    setTimeout(() => {
        modal.classList.add('hidden');
    }, 300);
}
</script>