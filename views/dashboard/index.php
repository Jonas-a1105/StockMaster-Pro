<?php
/**
 * DASHBOARD - Vista Enterprise
 * views/dashboard/index.php
 */
use App\Helpers\Icons;

// Variables del controlador
$valorTotalVentaUSD = $valorTotalVentaUSD ?? 0;
$valorTotalCostoUSD = $valorTotalCostoUSD ?? 0;
$gananciaPotencialUSD = $gananciaPotencialUSD ?? 0;
$stockBajo = $stockBajo ?? 0;
$estadisticasVentas = $estadisticasVentas ?? null;
$topProductos = $topProductos ?? [];
$ultimosMovimientos = $ultimosMovimientos ?? [];
?>

<!-- Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-800 dark:text-white flex items-center gap-2">
            <?= Icons::get('dashboard', 'w-8 h-8 text-emerald-500') ?>
            Dashboard
        </h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
            Bienvenido, hoy es <?= date('d/m/Y') ?>
        </p>
    </div>
    
    <div class="flex items-center gap-3">
        <a href="index.php?controlador=reporte&accion=index" 
           class="inline-flex items-center gap-2 px-4 py-2.5 bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 rounded-xl font-medium hover:bg-slate-50 dark:hover:bg-slate-600 transition-colors">
            <?= Icons::get('reports', 'w-4 h-4') ?>
            <span>Reportes</span>
        </a>
    </div>
</div>

<!-- KPIs Principales -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <!-- Valor Inventario -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200 dark:border-slate-700 shadow-sm group hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
        <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 bg-emerald-100 dark:bg-emerald-900/30 rounded-xl flex items-center justify-center transform transition-transform duration-300 group-hover:scale-110 group-hover:rotate-3">
                <?= Icons::get('dollar', 'w-5 h-5 text-emerald-600 dark:text-emerald-400') ?>
            </div>
            <?= Icons::get('trending-up', 'w-5 h-5 text-emerald-500') ?>
        </div>
        <p class="text-slate-500 dark:text-slate-400 text-sm font-medium">Valor Inventario</p>
        <p class="text-2xl font-bold mt-1 text-slate-800 dark:text-white" id="kpi-valor-usd" data-raw-value="<?= $valorTotalVentaUSD ?>">$<?= number_format($valorTotalVentaUSD, 2) ?></p>
    </div>
    
    <!-- Costo Inventario -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200 dark:border-slate-700 shadow-sm group hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
        <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 bg-red-100 dark:bg-red-900/30 rounded-xl flex items-center justify-center transform transition-transform duration-300 group-hover:scale-110 group-hover:rotate-3">
                <?= Icons::get('trending-down', 'w-5 h-5 text-red-600 dark:text-red-400') ?>
            </div>
            <?= Icons::get('chart-bar', 'w-5 h-5 text-red-500') ?>
        </div>
        <p class="text-slate-500 dark:text-slate-400 text-sm font-medium">Costo Total</p>
        <p class="text-2xl font-bold mt-1 text-slate-800 dark:text-white" id="kpi-costo-usd" data-raw-value="<?= $valorTotalCostoUSD ?>">$<?= number_format($valorTotalCostoUSD, 2) ?></p>
    </div>
    
    <!-- Ganancia Potencial -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200 dark:border-slate-700 shadow-sm group hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
        <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center transform transition-transform duration-300 group-hover:scale-110 group-hover:rotate-3">
                <?= Icons::get('trending-up', 'w-5 h-5 text-blue-600 dark:text-blue-400') ?>
            </div>
            <?= Icons::get('chart-bar', 'w-5 h-5 text-blue-500') ?>
        </div>
        <p class="text-slate-500 dark:text-slate-400 text-sm font-medium">Ganancia Potencial</p>
        <p class="text-2xl font-bold mt-1 text-slate-800 dark:text-white" id="kpi-ganancia-usd" data-raw-value="<?= $gananciaPotencialUSD ?>">$<?= number_format($gananciaPotencialUSD, 2) ?></p>
    </div>
    
    <!-- Alertas Stock -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200 dark:border-slate-700 shadow-sm group hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
        <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 <?= $stockBajo > 0 ? 'bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400' : 'bg-slate-100 dark:bg-slate-700 text-slate-500' ?> rounded-xl flex items-center justify-center transform transition-transform duration-300 group-hover:scale-110 group-hover:rotate-3">
                <?= Icons::get('warning', 'w-5 h-5') ?>
            </div>
            <?php if ($stockBajo > 0): ?>
                <?= Icons::get('error', 'w-5 h-5 text-amber-500') ?>
            <?php else: ?>
                <?= Icons::get('check-circle', 'w-5 h-5 text-emerald-500') ?>
            <?php endif; ?>
        </div>
        <p class="text-slate-500 dark:text-slate-400 text-sm font-medium">Alertas Stock</p>
        <p class="text-2xl font-bold mt-1 text-slate-800 dark:text-white" id="kpi-stock-bajo"><?= $stockBajo ?></p>
    </div>
</div>

<!-- KPIs Ventas del Mes -->
<?php if ($estadisticasVentas): ?>
<div class="mb-6">
    <h2 class="text-sm font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-4">
        Ventas de <?= date('F Y') ?>
    </h2>
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-700/50 rounded-2xl p-5 border border-slate-200 dark:border-slate-600 group hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center transform transition-transform duration-300 group-hover:scale-110 group-hover:rotate-3">
                    <?= Icons::get('sales', 'w-5 h-5 text-blue-600 dark:text-blue-400') ?>
                </div>
                <span class="text-sm text-slate-500 dark:text-slate-400">Nº Ventas</span>
            </div>
            <p class="text-2xl font-bold text-slate-800 dark:text-white"><?= $estadisticasVentas['total_ventas'] ?? 0 ?></p>
        </div>
        
        <div class="bg-white dark:bg-slate-700/50 rounded-2xl p-5 border border-slate-200 dark:border-slate-600 group hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 bg-emerald-100 dark:bg-emerald-900/30 rounded-xl flex items-center justify-center transform transition-transform duration-300 group-hover:scale-110 group-hover:rotate-3">
                    <?= Icons::get('dollar', 'w-5 h-5 text-emerald-600 dark:text-emerald-400') ?>
                </div>
                <span class="text-sm text-slate-500 dark:text-slate-400">Total Vendido</span>
            </div>
            <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">$<?= number_format($estadisticasVentas['total_vendido_usd'] ?? 0, 2) ?></p>
        </div>
        
        <div class="bg-white dark:bg-slate-700/50 rounded-2xl p-5 border border-slate-200 dark:border-slate-600 group hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 bg-cyan-100 dark:bg-cyan-900/30 rounded-xl flex items-center justify-center transform transition-transform duration-300 group-hover:scale-110 group-hover:rotate-3">
                    <?= Icons::get('check-circle', 'w-5 h-5 text-cyan-600 dark:text-cyan-400') ?>
                </div>
                <span class="text-sm text-slate-500 dark:text-slate-400">Cobrado</span>
            </div>
            <p class="text-2xl font-bold text-slate-800 dark:text-white">$<?= number_format($estadisticasVentas['total_cobrado'] ?? 0, 2) ?></p>
        </div>
        
        <div class="bg-white dark:bg-slate-700/50 rounded-2xl p-5 border border-slate-200 dark:border-slate-600 group hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 bg-amber-100 dark:bg-amber-900/30 rounded-xl flex items-center justify-center transform transition-transform duration-300 group-hover:scale-110 group-hover:rotate-3">
                    <?= Icons::get('history', 'w-5 h-5 text-amber-600 dark:text-amber-400') ?>
                </div>
                <span class="text-sm text-slate-500 dark:text-slate-400">Por Cobrar</span>
            </div>
            <p class="text-2xl font-bold text-amber-600 dark:text-amber-400">$<?= number_format($estadisticasVentas['total_por_cobrar'] ?? 0, 2) ?></p>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Grid Principal: Gráficos + Sidebar -->
<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
    <!-- Columna Gráficos (2/3) -->
    <div class="xl:col-span-2 space-y-6">
        <!-- Gráfico Ventas por Día -->
        <div class="bg-white dark:bg-slate-700/50 rounded-2xl border border-slate-200 dark:border-slate-600 p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-slate-800 dark:text-white flex items-center gap-2">
                    <?= Icons::get('chart-bar', 'w-5 h-5 text-blue-500') ?>
                    Ventas por Día
                </h3>
                <select id="periodo-ventas" data-setup-simple-select class="px-3 py-1.5 bg-slate-100 dark:bg-slate-600 border-0 rounded-lg text-sm text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                    <option value="7">Últimos 7 días</option>
                    <option value="15">Últimos 15 días</option>
                    <option value="30">Últimos 30 días</option>
                </select>
            </div>
            <div class="h-64">
                <canvas id="chartVentasPeriodo"></canvas>
            </div>
        </div>
        
        <!-- Gráfico Finanzas por Categoría -->
        <div class="bg-white dark:bg-slate-700/50 rounded-2xl border border-slate-200 dark:border-slate-600 p-5">
            <h3 class="font-semibold text-slate-800 dark:text-white flex items-center gap-2 mb-4">
                <?= Icons::get('chart-bar', 'w-5 h-5 text-emerald-500') ?>
                Finanzas por Categoría
            </h3>
            <div class="h-56">
                <canvas id="chartValorCategoria"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Columna Sidebar (1/3) -->
    <div class="space-y-6">
        <!-- Top 5 Productos -->
        <div class="bg-white dark:bg-slate-700/50 rounded-2xl border border-slate-200 dark:border-slate-600 p-5">
            <h3 class="font-semibold text-slate-800 dark:text-white flex items-center gap-2 mb-4 pb-3 border-b border-slate-100 dark:border-slate-600">
                <?= Icons::get('crown', 'w-5 h-5 text-amber-500') ?>
                Top 5 Más Vendidos
            </h3>
            
            <?php if (empty($topProductos)): ?>
                <div class="py-8 text-center">
                    <?= Icons::get('sales', 'w-12 h-12 mx-auto text-slate-200 dark:text-slate-600 mb-3') ?>
                    <p class="text-sm text-slate-400">Sin ventas registradas</p>
                </div>
            <?php else: ?>
                <ul class="space-y-3">
                    <?php foreach ($topProductos as $idx => $prod): 
                        $bgColors = ['bg-amber-400', 'bg-slate-300', 'bg-amber-600', 'bg-slate-400', 'bg-slate-400'];
                        $textColors = ['text-amber-900', 'text-slate-700', 'text-white', 'text-white', 'text-white'];
                    ?>
                        <li class="flex items-center gap-3">
                            <span class="w-7 h-7 <?= $bgColors[$idx] ?? 'bg-slate-400' ?> <?= $textColors[$idx] ?? 'text-white' ?> rounded-full flex items-center justify-center text-sm font-bold flex-shrink-0">
                                <?= $idx + 1 ?>
                            </span>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-slate-800 dark:text-white truncate"><?= htmlspecialchars($prod['nombre_producto']) ?></p>
                                <p class="text-xs text-slate-400"><?= $prod['cantidad_vendida'] ?> uds</p>
                            </div>
                            <span class="text-sm font-semibold text-emerald-600 dark:text-emerald-400">$<?= number_format($prod['total_vendido_usd'], 0) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
        
        <!-- Actividad Reciente -->
        <div class="bg-white dark:bg-slate-700/50 rounded-2xl border border-slate-200 dark:border-slate-600 p-5">
            <h3 class="font-semibold text-slate-800 dark:text-white flex items-center gap-2 mb-4 pb-3 border-b border-slate-100 dark:border-slate-600">
                <?= Icons::get('history', 'w-5 h-5 text-slate-400') ?>
                Actividad Reciente
            </h3>
            
            <?php if (empty($ultimosMovimientos)): ?>
                <div class="py-8 text-center">
                    <?= Icons::get('movements', 'w-12 h-12 mx-auto text-slate-200 dark:text-slate-600 mb-3') ?>
                    <p class="text-sm text-slate-400">Sin movimientos recientes</p>
                </div>
            <?php else: ?>
                <ul class="space-y-3">
                    <?php foreach ($ultimosMovimientos as $mov): ?>
                        <li class="flex items-center gap-3">
                            <div class="w-8 h-8 <?= $mov['tipo'] === 'Entrada' ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400' : 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400' ?> rounded-full flex items-center justify-center flex-shrink-0">
                                <?php if ($mov['tipo'] === 'Entrada'): ?>
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                                <?php else: ?>
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                                <?php endif; ?>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-slate-800 dark:text-white truncate"><?= htmlspecialchars($mov['productoNombre']) ?></p>
                                <p class="text-xs text-slate-400"><?= (new DateTime($mov['fecha']))->format('d/m H:i') ?></p>
                            </div>
                            <span class="text-sm font-semibold <?= $mov['tipo'] === 'Entrada' ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' ?>">
                                <?= $mov['tipo'] === 'Entrada' ? '+' : '-' ?><?= $mov['cantidad'] ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
                
                <a href="index.php?controlador=movimiento&accion=index" 
                   class="mt-4 w-full inline-flex items-center justify-center gap-2 px-4 py-2 bg-slate-100 dark:bg-slate-600 text-slate-700 dark:text-slate-200 rounded-xl text-sm font-medium hover:bg-slate-200 dark:hover:bg-slate-500 transition-colors">
                    Ver todos los movimientos
                    <?= Icons::get('chevron-right', 'w-4 h-4') ?>
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Módulo del Dashboard (cargado desde archivo externo) -->
<script src="<?= BASE_URL ?>js/pages/dashboard.js?v=<?= time() ?>"></script>