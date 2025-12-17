<?php
/**
 * LISTA DE TICKETS - Vista Enterprise
 * views/tickets/index.php
 */
use App\Helpers\Icons;

$tickets = $tickets ?? [];
?>

<!-- Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-800 dark:text-white flex items-center gap-3">
            <?= Icons::get('life-ring', 'w-7 h-7 text-indigo-500') ?>
            Mis Tickets de Soporte
        </h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
            Gestiona tus solicitudes de ayuda y seguimiento
        </p>
    </div>
    
    <a href="index.php?controlador=ticket&accion=crear" class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold shadow-lg shadow-emerald-500/30 transition-all">
        <?= Icons::get('plus-circle', 'w-5 h-5') ?>
        Crear Nuevo Ticket
    </a>
</div>

<!-- Tabla Tickets -->
<div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="bg-slate-50 dark:bg-slate-700/50 text-slate-500 dark:text-slate-400 font-medium border-b border-slate-200 dark:border-slate-700">
                <tr>
                    <th class="px-6 py-4 uppercase text-xs">ID</th>
                    <th class="px-6 py-4 uppercase text-xs">Asunto</th>
                    <th class="px-6 py-4 uppercase text-xs text-center">Estado</th>
                    <th class="px-6 py-4 uppercase text-xs text-center">Prioridad</th>
                    <th class="px-6 py-4 uppercase text-xs text-right">Última Act.</th>
                    <th class="px-6 py-4 uppercase text-xs text-center">Acción</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                <?php if (empty($tickets)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                            <?= Icons::get('search', 'w-12 h-12 mx-auto mb-3 text-slate-200 dark:text-slate-600') ?>
                            No has creado ningún ticket.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($tickets as $ticket): 
                        $statusColors = [
                            'Abierto' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                            'En Progreso' => 'bg-blue-100 text-blue-700 border-blue-200',
                            'Cerrado' => 'bg-slate-100 text-slate-600 border-slate-200',
                            'Pendiente' => 'bg-amber-100 text-amber-700 border-amber-200'
                        ];
                        $statusClass = $statusColors[$ticket['status']] ?? 'bg-slate-100 text-slate-600 border-slate-200';
                        
                        $priorityColors = [
                            'Alta' => 'text-red-600 bg-red-50',
                            'Media' => 'text-amber-600 bg-amber-50',
                            'Baja' => 'text-emerald-600 bg-emerald-50'
                        ];
                        $priorityClass = $priorityColors[$ticket['priority']] ?? 'text-slate-600';
                    ?>
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="px-6 py-4 font-mono text-slate-500">
                                #<?= str_pad($ticket['id'], 5, '0', STR_PAD_LEFT) ?>
                            </td>
                            <td class="px-6 py-4 font-medium text-slate-800 dark:text-white">
                                <?= htmlspecialchars($ticket['subject']) ?>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border <?= $statusClass ?>">
                                    <?= $ticket['status'] ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $priorityClass ?>">
                                    <?= $ticket['priority'] ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right text-slate-500 text-xs">
                                <?= (new DateTime($ticket['updated_at']))->format('d/m/Y h:i A') ?>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <a href="index.php?controlador=ticket&accion=ver&id=<?= $ticket['id'] ?>" 
                                   class="inline-flex items-center justify-center p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors"
                                   title="Ver Detalles">
                                    <?= Icons::get('eye', 'w-4 h-4') ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>