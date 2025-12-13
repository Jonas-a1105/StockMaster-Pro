<?php
/**
 * REPORTES - Vista Enterprise
 * views/reportes/index.php
 */
use App\Helpers\Icons;

$productos = $productos ?? [];
$filtros = $filtros ?? [];
$tipoReporte = $filtros['reporte-tipo'] ?? 'valor-inventario';
?>

<!-- Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-800 dark:text-white flex items-center gap-3">
            <?= Icons::get('reports', 'w-7 h-7 text-indigo-500') ?>
            Generador de Reportes
        </h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
            Analiza el rendimiento y estado de tu inventario
        </p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
    
    <!-- Filtros (Sidebar) -->
    <div class="lg:col-span-1">
        <div class="bg-white dark:bg-slate-700/50 rounded-2xl border border-slate-200 dark:border-slate-600 p-5 sticky top-24">
            <h3 class="font-semibold text-slate-800 dark:text-white flex items-center gap-2 mb-4 pb-3 border-b border-slate-100 dark:border-slate-600">
                <?= Icons::get('filter', 'w-5 h-5 text-slate-400') ?>
                Configuración
            </h3>
            
            <form id="form-reporte" action="index.php?controlador=reporte&accion=index" method="POST" class="space-y-4">
                
                <!-- Tipo Reporte -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Tipo de Reporte</label>
                    <select id="reporte-tipo" name="reporte-tipo" onchange="toggleReportFilters()" 
                            class="w-full px-4 py-2.5 bg-slate-100 dark:bg-slate-600 border-0 rounded-xl text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                        <option value="valor-inventario" <?= $tipoReporte == 'valor-inventario' ? 'selected' : '' ?>>
                            Valor de Inventario
                        </option>
                        <option value="movimientos-producto" <?= $tipoReporte == 'movimientos-producto' ? 'selected' : '' ?>>
                            Historial por Producto
                        </option>
                        <option value="movimientos-general" <?= $tipoReporte == 'movimientos-general' ? 'selected' : '' ?>>
                            Movimientos Generales
                        </option>
                    </select>
                </div>
                
                <!-- Producto (Condicional) -->
                <div id="reporte-producto-group" class="<?= $tipoReporte == 'movimientos-producto' ? '' : 'hidden' ?>">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Producto</label>
                    <select id="reporte-producto" name="reporte-producto" 
                            class="w-full px-4 py-2.5 bg-slate-100 dark:bg-slate-600 border-0 rounded-xl text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                        <option value="0">Seleccionar...</option>
                        <?php foreach ($productos as $producto): ?>
                            <option value="<?= $producto['id'] ?>" <?= ($filtros['reporte-producto'] ?? 0) == $producto['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($producto['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Fechas (Condicional para movimientos) -->
                <div id="reporte-fechas-group" class="<?= strpos($tipoReporte, 'movimientos') !== false ? '' : 'hidden' ?> space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Desde</label>
                        <input type="date" name="reporte-fecha-inicio" value="<?= htmlspecialchars($filtros['reporte-fecha-inicio'] ?? '') ?>"
                               class="w-full px-4 py-2.5 bg-slate-100 dark:bg-slate-600 border-0 rounded-xl text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Hasta</label>
                        <input type="date" name="reporte-fecha-fin" value="<?= htmlspecialchars($filtros['reporte-fecha-fin'] ?? '') ?>"
                               class="w-full px-4 py-2.5 bg-slate-100 dark:bg-slate-600 border-0 rounded-xl text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                    </div>
                </div>
                
                <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold shadow-lg shadow-indigo-500/30 transition-all mt-6">
                    <?= Icons::get('check', 'w-5 h-5') ?>
                    Generar Reporte
                </button>
            </form>
        </div>
    </div>
    
    <!-- Resultados (Main) -->
    <div class="lg:col-span-3">
        <?php if (isset($reporte) && $reporte !== null): ?>
            <div class="bg-white dark:bg-slate-700/50 rounded-2xl border border-slate-200 dark:border-slate-600 p-5 h-full flex flex-col animate-fade-in-up">
                <div class="flex flex-wrap items-center justify-between gap-4 mb-5 pb-4 border-b border-slate-100 dark:border-slate-600">
                    <h3 class="font-semibold text-lg text-slate-800 dark:text-white flex items-center gap-2">
                        <?= Icons::get('clipboard', 'w-5 h-5 text-indigo-500') ?>
                        <?= htmlspecialchars($reporte['titulo']) ?>
                    </h3>
                    
                    <div class="flex gap-2">
                        <button onclick="exportarReporte('csv')" class="inline-flex items-center gap-2 px-3 py-2 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 rounded-lg text-sm font-medium hover:bg-emerald-100 dark:hover:bg-emerald-900/50 transition-colors">
                            <?= Icons::get('download', 'w-4 h-4') ?>
                            CSV
                        </button>
                        <button onclick="exportarReporte('pdf')" class="inline-flex items-center gap-2 px-3 py-2 bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-400 rounded-lg text-sm font-medium hover:bg-red-100 dark:hover:bg-red-900/50 transition-colors">
                            <?= Icons::get('printer', 'w-4 h-4') ?>
                            PDF
                        </button>
                    </div>
                </div>
                
                <div class="overflow-x-auto rounded-xl border border-slate-100 dark:border-slate-600">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-slate-50 dark:bg-slate-600/50">
                            <tr>
                                <?php foreach ($reporte['columnas'] as $columna): ?>
                                    <th class="px-4 py-3 font-semibold text-slate-500 dark:text-slate-400 uppercase text-xs"><?= htmlspecialchars($columna) ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-600">
                            <?php if (empty($reporte['resultados'])): ?>
                                <tr>
                                    <td colspan="<?= count($reporte['columnas']) ?>" class="px-4 py-12 text-center text-slate-500 dark:text-slate-400">
                                        No se encontraron resultados para los filtros seleccionados.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($reporte['resultados'] as $fila): ?>
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-600/30 transition-colors">
                                        <?php foreach ($fila as $celda): ?>
                                            <td class="px-4 py-3 text-slate-700 dark:text-slate-300 whitespace-nowrap">
                                                <?= htmlspecialchars($celda) ?>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <script>
                window.reporteActual = {
                    titulo: <?= json_encode($reporte['titulo']) ?>,
                    columnas: <?= json_encode($reporte['columnas']) ?>,
                    datos: <?= json_encode($reporte['resultados']) ?>
                };
            </script>
        <?php else: ?>
            <div class="bg-white dark:bg-slate-700/50 rounded-2xl border border-slate-200 dark:border-slate-600 p-12 text-center h-full flex flex-col justify-center items-center">
                <div class="w-20 h-20 bg-slate-100 dark:bg-slate-600 rounded-full flex items-center justify-center mb-4">
                    <?= Icons::get('reports', 'w-10 h-10 text-slate-300 dark:text-slate-500') ?>
                </div>
                <h3 class="text-xl font-bold text-slate-800 dark:text-white mb-2">Generar Nuevo Reporte</h3>
                <p class="text-slate-500 dark:text-slate-400 max-w-md mx-auto">
                    Selecciona el tipo de reporte y los filtros necesarios en el panel de configuración para visualizar los datos.
                </p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function toggleReportFilters() {
    const tipo = document.getElementById('reporte-tipo').value;
    const groupProd = document.getElementById('reporte-producto-group');
    const groupFechas = document.getElementById('reporte-fechas-group');
    
    if (tipo === 'movimientos-producto') {
        groupProd.classList.remove('hidden');
        groupFechas.classList.remove('hidden');
    } else if (tipo === 'movimientos-general') {
        groupProd.classList.add('hidden');
        groupFechas.classList.remove('hidden');
    } else {
        groupProd.classList.add('hidden');
        groupFechas.classList.add('hidden');
    }
}
</script>