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
<?php
ob_start();
foreach($logs as $log): ?>
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
<?php endforeach;
$tableContent = ob_get_clean();

echo View::component('table', [
    'headers' => [
        ['label' => 'Fecha', 'align' => 'left', 'class' => 'w-48'],
        ['label' => 'Acción', 'align' => 'left', 'class' => 'w-48'],
        ['label' => 'Detalles', 'align' => 'left']
    ],
    'content' => $tableContent,
    'empty' => empty($logs),
    'emptyMsg' => 'No hay actividad registrada',
    'emptyIcon' => 'history'
]);
?>
