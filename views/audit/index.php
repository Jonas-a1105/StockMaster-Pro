<?php
/**
 * HISTORIAL DE ACTIVIDAD - Vista Enterprise
 * views/audit/index.php
 */
use App\Helpers\Icons;
?>

<!-- Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-800 dark:text-white flex items-center gap-3">
            <?= Icons::get('history', 'w-7 h-7 text-indigo-500') ?>
            Historial de Actividad
        </h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
            Registro cronológico de eventos y acciones del sistema
        </p>
    </div>
</div>

<!-- Table -->
<div class="bg-white dark:bg-slate-700/50 rounded-2xl border border-slate-200 dark:border-slate-600 overflow-hidden shadow-sm">
    <?php if (empty($logs)): ?>
        <div class="p-12 text-center">
            <?= Icons::get('search', 'w-16 h-16 mx-auto text-slate-200 dark:text-slate-600 mb-4') ?>
            <p class="text-slate-500 dark:text-slate-400 text-lg font-medium">No hay actividad registrada</p>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-slate-50 dark:bg-slate-600/50 border-b border-slate-100 dark:border-slate-600">
                    <tr>
                        <th class="px-6 py-4 font-semibold text-slate-500 dark:text-slate-400 uppercase text-xs w-48">Fecha</th>
                        <th class="px-6 py-4 font-semibold text-slate-500 dark:text-slate-400 uppercase text-xs w-48">Acción</th>
                        <th class="px-6 py-4 font-semibold text-slate-500 dark:text-slate-400 uppercase text-xs">Detalles</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-600">
                    <?php foreach($logs as $log): ?>
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-600/30 transition-colors">
                        <td class="px-6 py-4 text-slate-500 dark:text-slate-400 whitespace-nowrap">
                            <?= $log['created_at'] ?>
                        </td>
                        <td class="px-6 py-4 font-medium text-indigo-600 dark:text-indigo-400">
                            <?= htmlspecialchars($log['action']) ?>
                        </td>
                        <td class="px-6 py-4 text-slate-600 dark:text-slate-300">
                            <?= htmlspecialchars($log['details']) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
