<?php
use App\Helpers\Icons;

$headers = ['ID', 'Asunto', 'Usuario (Email)', 'Estado', 'Prioridad / Actualizado', 'Acción'];
$rows = [];

foreach ($tickets as $ticket) {
    // Estado con color
    $statusColor = 'text-slate-500';
    if ($ticket['status'] === 'Abierto') $statusColor = 'text-emerald-500 font-bold';
    if ($ticket['status'] === 'En Progreso') $statusColor = 'text-blue-500 font-bold';
    $status = '<span class="' . $statusColor . '">' . $ticket['status'] . '</span>';

    // Prioridad y Fecha
    $priority = '<div class="flex flex-col">
                    <span class="font-medium text-slate-700 dark:text-slate-200">' . $ticket['priority'] . '</span>
                    <span class="text-xs text-slate-400">' . (new DateTime($ticket['updated_at']))->format('d/m/Y H:i') . '</span>
                </div>';

    // Acción
    $accion = '<a href="index.php?controlador=admin&accion=verTicket&id=' . $ticket['id'] . '" class="inline-flex items-center gap-2 px-3 py-1.5 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 rounded-lg hover:bg-indigo-100 dark:hover:bg-indigo-900/50 transition-colors text-sm font-medium">
                ' . Icons::get('edit', 'w-4 h-4') . ' Responder
               </a>';

    $rows[] = [
        'content' => [
            '#' . $ticket['id'],
            htmlspecialchars($ticket['subject']),
            htmlspecialchars($ticket['email']),
            \App\Core\View::raw($status),
            \App\Core\View::raw($priority),
            ['content' => \App\Core\View::raw($accion), 'class' => 'text-center']
        ]
    ];
}
?>

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-800 dark:text-white flex items-center gap-3">
            <?= Icons::get('support', 'w-7 h-7 text-indigo-500') ?>
            Administrar Tickets de Soporte
        </h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
            Visualiza y responde las solicitudes de los usuarios
        </p>
    </div>
</div>

<?= App\Core\View::component('table', [
    'headers' => $headers,
    'rows' => $rows,
    'emptyMessage' => 'No hay tickets activos.',
    'emptyIcon' => 'support'
]) ?>
