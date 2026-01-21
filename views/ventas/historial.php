<?php
/**
 * HISTORIAL DE VENTAS - Vista Enterprise
 * views/ventas/historial.php
 */
use App\Helpers\Icons;

// Calculate stats
$total_ventas = count($ventas);
$total_usd = 0;
$total_ves = 0;
$total_pendientes = 0;

foreach ($ventas as $v) {
    $total_usd += $v['total_usd'];
    $total_ves += $v['total_ves'];
    if (($v['estado_pago'] ?? 'Pagada') === 'Pendiente') {
        $total_pendientes++;
    }
}

// Pagination Logic URL builder
$buildUrl = function($page) use ($filtros) {
    $url = "index.php?controlador=venta&accion=historial&pagina={$page}";
    if (!empty($filtros['fecha_inicio'])) $url .= "&fecha_inicio=" . urlencode($filtros['fecha_inicio']);
    if (!empty($filtros['fecha_fin'])) $url .= "&fecha_fin=" . urlencode($filtros['fecha_fin']);
    if (!empty($filtros['cliente_id'])) $url .= "&cliente_id=" . $filtros['cliente_id'];
    if (!empty($filtros['estado_pago'])) $url .= "&estado_pago=" . urlencode($filtros['estado_pago']);
    return $url;
};
?>

<!-- Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-800 dark:text-white flex items-center gap-3">
            <?= Icons::get('history', 'w-7 h-7 text-indigo-500') ?>
            Historial de Ventas
        </h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
            Registro completo de transacciones y estados de pago
        </p>
    </div>
    
    <a href="index.php?controlador=venta&accion=index" class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold shadow-lg shadow-indigo-500/30 transition-all">
        <?= Icons::get('cart-plus', 'w-5 h-5') ?>
        Nueva Venta
    </a>
</div>

<!-- KPI Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Card 1: Total Ventas -->
    <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm flex items-start justify-between group hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
        <div>
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Ventas Registradas</p>
            <h3 class="text-2xl font-bold text-slate-800 dark:text-white mt-1"><?= $total_ventas ?></h3>
        </div>
        <div class="p-3 bg-indigo-50 dark:bg-indigo-900/30 rounded-xl text-indigo-600 dark:text-indigo-400 transform transition-transform duration-300 group-hover:scale-110 group-hover:rotate-3">
            <?= Icons::get('files', 'w-6 h-6') ?>
        </div>
    </div>

    <!-- Card 2: Total USD -->
    <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm flex items-start justify-between group hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
        <div>
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Total Ingresos</p>
            <h3 class="text-2xl font-bold text-slate-800 dark:text-white mt-1">$<?= number_format($total_usd, 2) ?></h3>
        </div>
        <div class="p-3 bg-emerald-50 dark:bg-emerald-900/30 rounded-xl text-emerald-600 dark:text-emerald-400 transform transition-transform duration-300 group-hover:scale-110 group-hover:rotate-3">
            <?= Icons::get('dollar', 'w-6 h-6') ?>
        </div>
    </div>

    <!-- Card 3: Total VES -->
    <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm flex items-start justify-between group hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
        <div>
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Total Moneda Local</p>
            <h3 class="text-2xl font-bold text-slate-800 dark:text-white mt-1">Bs <?= number_format($total_ves, 2) ?></h3>
        </div>
        <div class="p-3 bg-blue-50 dark:bg-blue-900/30 rounded-xl text-blue-600 dark:text-blue-400 transform transition-transform duration-300 group-hover:scale-110 group-hover:rotate-3">
            <?= Icons::get('currency', 'w-6 h-6') ?>
        </div>
    </div>

    <!-- Card 4: Pendientes (Condicional) -->
    <?php if ($total_pendientes > 0): ?>
    <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-red-100 dark:border-red-900/50 shadow-sm flex items-start justify-between relative overflow-hidden group hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
        <div class="relative z-10">
            <p class="text-sm font-medium text-red-500 dark:text-red-400">Pendientes por Cobrar</p>
            <h3 class="text-2xl font-bold text-red-600 dark:text-red-400 mt-1"><?= $total_pendientes ?></h3>
        </div>
        <div class="p-3 bg-red-50 dark:bg-red-900/30 rounded-xl text-red-600 dark:text-red-400 relative z-10 transform transition-transform duration-300 group-hover:scale-110 group-hover:rotate-3">
            <?= Icons::get('clock', 'w-6 h-6') ?>
        </div>
    </div>
    <?php else: ?>
    <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm flex items-start justify-between group hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
        <div>
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Cuentas por Cobrar</p>
            <div class="flex items-center gap-2 mt-1">
                <h3 class="text-2xl font-bold text-slate-800 dark:text-white">0</h3>
                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400">
                    Al día
                </span>
            </div>
        </div>
        <div class="p-3 bg-slate-50 dark:bg-slate-700 rounded-xl text-slate-400 dark:text-slate-300 transform transition-transform duration-300 group-hover:scale-110 group-hover:rotate-3">
            <?= Icons::get('check', 'w-6 h-6') ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Filtros -->
<div class="bg-white dark:bg-slate-700/50 rounded-2xl border border-slate-200 dark:border-slate-600 p-5 mb-6 shadow-sm">
    <form method="GET" action="index.php" class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-5 gap-4 items-end">
        <input type="hidden" name="controlador" value="venta">
        <input type="hidden" name="accion" value="historial">
        
        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Desde</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <?= Icons::get('calendar', 'w-4 h-4 text-slate-400') ?>
                </div>
                <input type="date" name="fecha_inicio" value="<?= $filtros['fecha_inicio'] ?? '' ?>"
                       class="w-full pl-10 pr-4 py-2.5 bg-slate-100 dark:bg-slate-600 border-0 rounded-xl text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
            </div>
        </div>
        
        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Hasta</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <?= Icons::get('calendar', 'w-4 h-4 text-slate-400') ?>
                </div>
                <input type="date" name="fecha_fin" value="<?= $filtros['fecha_fin'] ?? '' ?>"
                       class="w-full pl-10 pr-4 py-2.5 bg-slate-100 dark:bg-slate-600 border-0 rounded-xl text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
            </div>
        </div>
        
        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Cliente</label>
            <select name="cliente_id" class="w-full px-4 py-2.5 bg-slate-100 dark:bg-slate-600 border-0 rounded-xl text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                <option value="">Todos los clientes</option>
                <?php foreach ($clientes as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= isset($filtros['cliente_id']) && $filtros['cliente_id'] == $c['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Estado</label>
            <select name="estado_pago" class="w-full px-4 py-2.5 bg-slate-100 dark:bg-slate-600 border-0 rounded-xl text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                <option value="">Todos</option>
                <option value="Pagada" <?= isset($filtros['estado_pago']) && $filtros['estado_pago'] === 'Pagada' ? 'selected' : '' ?>>Pagada</option>
                <option value="Pendiente" <?= isset($filtros['estado_pago']) && $filtros['estado_pago'] === 'Pendiente' ? 'selected' : '' ?>>Pendiente</option>
            </select>
        </div>
        
        <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-slate-800 hover:bg-slate-900 dark:bg-slate-600 dark:hover:bg-slate-500 text-white rounded-xl font-medium transition-all">
            <?= Icons::get('filter', 'w-4 h-4') ?>
            Filtrar
        </button>
    </form>
</div>

    <?php
    // Preparar contenido de las filas
    ob_start();
    foreach ($ventas as $v): 
        $status = $v['estado_pago'] ?? 'Pagada';
        $isPaid = $status === 'Pagada';
    ?>
    <tr class="hover:bg-slate-50 dark:hover:bg-slate-600/30 transition-colors">
        <td class="px-6 py-4 font-mono text-slate-500 dark:text-slate-400">#<?= str_pad($v['id'], 5, '0', STR_PAD_LEFT) ?></td>
        <td class="px-6 py-4 text-slate-600 dark:text-slate-300">
            <?= (new DateTime($v['created_at']))->format('d/m/Y h:i A') ?>
        </td>
        <td class="px-6 py-4">
            <?php if (!empty($v['cliente_nombre'])): ?>
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400 text-xs font-bold">
                        <?= strtoupper(substr($v['cliente_nombre'], 0, 1)) ?>
                    </div>
                    <div>
                        <p class="font-medium text-slate-800 dark:text-white"><?= htmlspecialchars($v['cliente_nombre']) ?></p>
                        <?php if (!empty($v['numero_documento'])): ?>
                            <p class="text-xs text-slate-400"><?= htmlspecialchars($v['numero_documento']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <span class="italic text-slate-400">Cliente General</span>
            <?php endif; ?>
        </td>
        <td class="px-6 py-4 text-right font-bold text-slate-700 dark:text-slate-200">
            $<?= number_format($v['total_usd'], 2) ?>
        </td>
        <td class="px-6 py-4 text-right font-medium text-slate-500 dark:text-slate-400">
            Bs <?= number_format($v['total_ves'], 2) ?>
        </td>
        <td class="px-6 py-4 text-center">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium <?= $isPaid ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' ?>">
                <span class="w-1.5 h-1.5 rounded-full <?= $isPaid ? 'bg-emerald-500' : 'bg-red-500' ?>"></span>
                <?= $status ?>
            </span>
        </td>
        <td class="px-6 py-4 text-center">
            <div class="flex justify-center gap-2">
                <a href="index.php?controlador=venta&accion=recibo&id=<?= $v['id'] ?>" target="_blank" class="p-2 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-600 rounded-lg transition-colors" title="Ver Recibo">
                    <?= Icons::get('printer', 'w-4 h-4') ?>
                </a>
                <?php if (!$isPaid): ?>
                    <form action="index.php?controlador=venta&accion=pagarVenta" method="POST" class="inline" onsubmit="return confirm('¿Confirmar pago de esta venta?')">
                        <?= \App\Helpers\Security::csrfField() ?>
                        <input type="hidden" name="id" value="<?= $v['id'] ?>">
                        <button type="submit" class="p-2 text-emerald-500 hover:bg-emerald-50 dark:hover:bg-emerald-900/30 rounded-lg transition-colors" title="Marcar como Pagada">
                            <?= Icons::get('check', 'w-4 h-4') ?>
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </td>
    </tr>
    <?php endforeach; 
    $tableContent = ob_get_clean();

    echo \App\Core\View::component('table', [
        'headers' => [
            ['label' => 'ID', 'align' => 'left'],
            ['label' => 'Fecha', 'align' => 'left'],
            ['label' => 'Cliente', 'align' => 'left'],
            ['label' => 'Total USD', 'align' => 'right'],
            ['label' => 'Total VES', 'align' => 'right'],
            ['label' => 'Estado', 'align' => 'center'],
            ['label' => 'Acciones', 'align' => 'center']
        ],
        'content' => \App\Core\View::raw($tableContent),
        'empty' => empty($ventas),
        'emptyMsg' => 'No se encontraron ventas',
        'pagination' => [
            'current' => $paginaActual ?? 1,
            'total' => $totalPaginas ?? 1,
            'limit' => $porPagina ?? 20,
            'url_builder' => $buildUrl,
            'limit_name' => 'limite'
        ]
    ]);
    ?>
</div>
