<?php
/**
 * CREAR TICKET - Vista Enterprise
 * views/tickets/crear.php
 */
use App\Helpers\Icons;
?>

<!-- Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-800 dark:text-white flex items-center gap-3">
            <?= Icons::get('plus-circle', 'w-7 h-7 text-emerald-500') ?>
            Crear Nuevo Ticket
        </h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
            Describe tu problema o solicitud para que podamos ayudarte
        </p>
    </div>
    
    <a href="index.php?controlador=ticket&accion=index" class="inline-flex items-center gap-2 px-4 py-2 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition-colors font-medium">
        <?= Icons::get('arrow-left', 'w-4 h-4') ?>
        Volver a la lista
    </a>
</div>

<div class="max-w-3xl mx-auto">
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm">
        
        <form action="index.php?controlador=ticket&accion=guardar" method="POST" class="space-y-6">
            
            <!-- Asunto -->
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Asunto</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <?= Icons::get('tag', 'w-5 h-5 text-slate-400') ?>
                    </div>
                    <input type="text" name="subject" required placeholder="Resumen breve del problema"
                           class="w-full pl-11 pr-4 py-3 bg-slate-50 dark:bg-slate-700/50 border-0 rounded-xl text-slate-800 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 transition-all">
                </div>
            </div>

            <!-- Prioridad -->
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Prioridad</label>
                <div class="grid grid-cols-3 gap-4">
                    <label class="cursor-pointer">
                        <input type="radio" name="priority" value="Baja" class="peer sr-only" checked>
                        <div class="flex flex-col items-center justify-center p-3 rounded-xl border-2 border-slate-100 dark:border-slate-600 peer-checked:border-emerald-500 peer-checked:bg-emerald-50 dark:peer-checked:bg-emerald-900/20 transition-all text-center">
                            <span class="text-sm font-medium text-slate-500 dark:text-slate-400 peer-checked:text-emerald-600">Baja</span>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="priority" value="Media" class="peer sr-only">
                        <div class="flex flex-col items-center justify-center p-3 rounded-xl border-2 border-slate-100 dark:border-slate-600 peer-checked:border-amber-500 peer-checked:bg-amber-50 dark:peer-checked:bg-amber-900/20 transition-all text-center">
                            <span class="text-sm font-medium text-slate-500 dark:text-slate-400 peer-checked:text-amber-600">Media</span>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="priority" value="Alta" class="peer sr-only">
                        <div class="flex flex-col items-center justify-center p-3 rounded-xl border-2 border-slate-100 dark:border-slate-600 peer-checked:border-red-500 peer-checked:bg-red-50 dark:peer-checked:bg-red-900/20 transition-all text-center">
                            <span class="text-sm font-medium text-slate-500 dark:text-slate-400 peer-checked:text-red-600">Alta</span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Descripción -->
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Descripción Detallada</label>
                <div class="relative">
                     <div class="absolute top-3 left-4 flex items-center pointer-events-none">
                        <?= Icons::get('align-left', 'w-5 h-5 text-slate-400') ?>
                    </div>
                    <textarea name="description" required rows="6" placeholder="Explica el problema con el mayor detalle posible..."
                              class="w-full pl-11 pr-4 py-3 bg-slate-50 dark:bg-slate-700/50 border-0 rounded-xl text-slate-800 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 transition-all resize-none"></textarea>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 dark:border-slate-700 flex justify-end">
                <button type="submit" class="inline-flex items-center gap-2 px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold shadow-lg shadow-emerald-500/30 transition-all">
                    <?= Icons::get('send', 'w-5 h-5') ?>
                    Enviar Ticket
                </button>
            </div>

        </form>
    </div>
</div>