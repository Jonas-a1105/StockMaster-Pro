<?php
/**
 * VER TICKET - Vista Enterprise
 * views/tickets/ver.php
 */
use App\Helpers\Icons;

$ticket = $ticket ?? [];
$mensajes = $mensajes ?? [];
?>

<!-- Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div class="flex items-start gap-4">
        <a href="index.php?controlador=ticket&accion=index" class="mt-1 p-2 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors">
            <?= Icons::get('arrow-left', 'w-5 h-5') ?>
        </a>
        <div>
            <div class="flex items-center gap-3 flex-wrap">
                <span class="text-sm font-mono text-slate-400">#<?= str_pad($ticket['id'], 5, '0', STR_PAD_LEFT) ?></span>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600 border border-slate-200">
                    <?= $ticket['status'] ?>
                </span>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-white border border-slate-200 text-slate-500">
                    <?= $ticket['priority'] ?>
                </span>
            </div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white mt-1">
                <?= htmlspecialchars($ticket['subject']) ?>
            </h1>
            <p class="text-sm text-slate-500 mt-1">
                Creado el <?= (new DateTime($ticket['created_at']))->format('d/m/Y h:i A') ?>
            </p>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- Columna Chat (2/3) -->
    <div class="lg:col-span-2 space-y-6">
        
        <!-- Descripción Original -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm">
            <h3 class="text-sm font-bold text-slate-500 uppercase tracking-wide mb-4">Descripción del Problema</h3>
            <div class="prose prose-slate dark:prose-invert max-w-none text-slate-700 dark:text-slate-300">
                <?= nl2br(htmlspecialchars($ticket['description'])) ?>
            </div>
        </div>

        <!-- Historial Conversación -->
        <div class="space-y-4">
            <h3 class="text-sm font-bold text-slate-500 uppercase tracking-wide ml-1">Conversación</h3>
            
            <?php if (empty($mensajes)): ?>
                <div class="text-center py-8 bg-slate-50 dark:bg-slate-800/50 rounded-2xl border border-dashed border-slate-300 dark:border-slate-600">
                    <p class="text-slate-400 text-sm">No hay respuestas aún.</p>
                </div>
            <?php else: ?>
                <?php foreach ($mensajes as $msg): 
                    $isStaff = ($msg['user_id'] != $_SESSION['user_id']); // Asumiendo logica simple
                    $bgClass = $isStaff ? 'bg-indigo-50 dark:bg-indigo-900/20 ml-auto' : 'bg-white dark:bg-slate-800';
                    $borderClass = $isStaff ? 'border-indigo-100 dark:border-indigo-800' : 'border-slate-200 dark:border-slate-700';
                ?>
                <div class="flex gap-4 <?= $isStaff ? 'flex-row-reverse' : '' ?>">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center <?= $isStaff ? 'bg-indigo-100 text-indigo-600' : 'bg-slate-100 text-slate-600' ?>">
                            <?= Icons::get('user', 'w-5 h-5') ?>
                        </div>
                    </div>
                    <div class="flex-1 max-w-2xl">
                        <div class="rounded-2xl p-5 border shadow-sm <?= $bgClass ?> <?= $borderClass ?>">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-bold <?= $isStaff ? 'text-indigo-600' : 'text-slate-700' ?>">
                                    <?= $isStaff ? 'Soporte' : 'Tú' ?>
                                </span>
                                <span class="text-xs text-slate-400">
                                    <?= (new DateTime($msg['created_at']))->format('d/m/Y h:i A') ?>
                                </span>
                            </div>
                            <div class="text-sm text-slate-700 dark:text-slate-300 whitespace-pre-wrap">
                                <?= htmlspecialchars($msg['message']) ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Formulario Respuesta -->
        <?php if ($ticket['status'] !== 'Cerrado'): ?>
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm sticky bottom-4">
            <form action="index.php?controlador=ticket&accion=responder" method="POST">
                <input type="hidden" name="ticket_id" value="<?= $ticket['id'] ?>">
                <div class="relative">
                    <textarea name="message" required rows="3" placeholder="Escribe tu respuesta..."
                              class="w-full p-4 bg-slate-50 dark:bg-slate-700/50 border-0 rounded-xl text-slate-800 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 resize-none mb-3"></textarea>
                    
                    <div class="flex justify-end">
                        <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-bold shadow-lg shadow-indigo-500/30 transition-all">
                            <?= Icons::get('send', 'w-4 h-4') ?>
                            Enviar Respuesta
                        </button>
                    </div>
                </div>
            </form>
        </div>
        <?php else: ?>
        <div class="bg-slate-100 dark:bg-slate-800 p-4 rounded-xl text-center border border-slate-200 dark:border-slate-700">
            <p class="text-slate-500 font-medium">Este ticket ha sido cerrado y no admite más respuestas.</p>
        </div>
        <?php endif; ?>

    </div>

    <!-- Sidebar (1/3) -->
    <div class="lg:col-span-1 space-y-6">
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm">
            <h3 class="text-sm font-bold text-slate-500 uppercase tracking-wide mb-4">Detalles</h3>
            
            <dl class="space-y-4">
                <div>
                    <dt class="text-xs text-slate-400 uppercase">Estado</dt>
                    <dd class="mt-1 font-medium text-slate-800 dark:text-white"><?= $ticket['status'] ?></dd>
                </div>
                <div>
                    <dt class="text-xs text-slate-400 uppercase">Prioridad</dt>
                    <dd class="mt-1 font-medium text-slate-800 dark:text-white"><?= $ticket['priority'] ?></dd>
                </div>
                <div>
                    <dt class="text-xs text-slate-400 uppercase">Departamento</dt>
                    <dd class="mt-1 font-medium text-slate-800 dark:text-white">Soporte General</dd>
                </div>
            </dl>

            <?php if ($ticket['status'] !== 'Cerrado'): ?>
            <div class="mt-8 pt-6 border-t border-slate-100 dark:border-slate-700">
                <form action="index.php?controlador=ticket&accion=cambiarEstado" method="POST">
                    <input type="hidden" name="ticket_id" value="<?= $ticket['id'] ?>">
                    <input type="hidden" name="status" value="Cerrado">
                    <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-2 border-2 border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 font-semibold rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors" onclick="return confirm('¿Estás seguro de cerrar este ticket?');">
                        <?= Icons::get('check-circle', 'w-4 h-4') ?>
                        Marcar como Resuelto
                    </button>
                </form>
            </div>
            <?php endif; ?>
        </div>
    </div>

</div>