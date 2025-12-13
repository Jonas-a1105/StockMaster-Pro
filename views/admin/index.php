<?php
/**
 * ADMINISTRACIÓN USUARIOS - Vista Enterprise
 * views/admin/index.php
 */
use App\Helpers\Icons;

$usuarios = $usuarios ?? [];
?>

<!-- Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-800 dark:text-white flex items-center gap-3">
            <?= Icons::get('team', 'w-7 h-7 text-indigo-500') ?>
            Administración de Usuarios
        </h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
            Gestión de usuarios y planes de suscripción
        </p>
    </div>
    
    <div class="flex items-center gap-3">
        <a href="index.php?controlador=admin&accion=tickets" class="inline-flex items-center gap-2 px-4 py-2.5 bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 rounded-xl font-medium hover:bg-slate-50 dark:hover:bg-slate-600 transition-colors">
            <?= Icons::get('ticket', 'w-4 h-4') ?>
            <span>Tickets de Soporte</span>
        </a>
    </div>
</div>

<!-- Tabla de Usuarios -->
<div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="bg-slate-50 dark:bg-slate-700/50 text-slate-500 dark:text-slate-400 font-medium border-b border-slate-200 dark:border-slate-700">
                <tr>
                    <th class="px-6 py-4">Usuario / Email</th>
                    <th class="px-6 py-4 text-center">Plan Actual</th>
                    <th class="px-6 py-4">Estado / Vencimiento</th>
                    <th class="px-6 py-4 text-center">Gestionar Plan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                <?php if (empty($usuarios)): ?>
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center text-slate-400">
                            <?= Icons::get('user', 'w-12 h-12 mx-auto mb-3 text-slate-200 dark:text-slate-600') ?>
                            No hay usuarios registrados en el sistema.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($usuarios as $usuario): ?>
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-slate-500 font-bold border border-slate-200 dark:border-slate-600">
                                        <?= strtoupper(substr($usuario['email'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <p class="font-medium text-slate-800 dark:text-white">
                                            <?= htmlspecialchars($usuario['email']) ?>
                                        </p>
                                        <p class="text-xs text-slate-500">
                                            ID: <?= $usuario['id'] ?> • Reg: <?= (new DateTime($usuario['fecha_registro']))->format('d/M Y') ?>
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <?php if ($usuario['plan'] === 'premium'): ?>
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 border border-amber-200 dark:border-amber-800">
                                        <?= Icons::get('crown', 'w-3 h-3') ?> Premium
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-400 border border-slate-200 dark:border-slate-600">
                                        Free
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <?php 
                                    if ($usuario['plan'] === 'free') {
                                        echo '<span class="text-slate-400 text-xs">—</span>';
                                    } elseif ($usuario['trial_ends_at'] === null) {
                                        echo '<span class="text-emerald-600 font-medium flex items-center gap-1.5 text-xs">' . Icons::get('check-circle', 'w-3.5 h-3.5') . ' Permanente</span>';
                                    } else {
                                        $fecha = new DateTime($usuario['trial_ends_at']);
                                        $hoy = new DateTime();
                                        $diff = $hoy->diff($fecha);
                                        // Si ya pasó
                                        if ($fecha < $hoy) {
                                            echo '<div class="flex items-center gap-1.5 text-red-600 text-xs font-medium">' . Icons::get('error', 'w-3.5 h-3.5') . ' Vencido</div>';
                                            echo '<div class="text-xs text-slate-400 mt-0.5">Venció el ' . $fecha->format('d/m/Y') . '</div>';
                                        } else {
                                            $color = $diff->days < 5 ? 'text-amber-600' : 'text-slate-600 dark:text-slate-300';
                                            echo '<div class="flex flex-col">';
                                            echo '<span class="' . $color . ' font-medium text-sm">' . $diff->days . ' días restantes</span>';
                                            echo '<span class="text-xs text-slate-400">Vence: ' . $fecha->format('d/m/Y') . '</span>';
                                            echo '</div>';
                                        }
                                    }
                                ?>
                            </td>
                            <td class="px-6 py-4">
                                <form action="index.php?controlador=admin&accion=cambiarPlan" method="POST" class="flex items-center justify-center gap-2">
                                    <input type="hidden" name="usuario_id" value="<?= $usuario['id'] ?>">
                                    
                                    <?php if ($usuario['plan'] === 'free'): ?>
                                        <input type="hidden" name="accion" value="premium">
                                        <div class="relative">
                                            <select name="duracion" class="appearance-none pl-3 pr-8 py-1.5 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg text-xs font-medium focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                                                <option value="15">15 Días</option>
                                                <option value="30" selected>1 Mes</option>
                                                <option value="90">3 Meses</option>
                                                <option value="365">1 Año</option>
                                                <option value="permanent">Permanente</option>
                                            </select>
                                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-500">
                                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                            </div>
                                        </div>
                                        <button type="submit" class="p-1.5 bg-emerald-100 text-emerald-700 rounded-lg hover:bg-emerald-200 transition-colors" title="Activar Premium">
                                            <?= Icons::get('check', 'w-4 h-4') ?>
                                        </button>
                                    <?php else: ?>
                                        <input type="hidden" name="accion" value="free">
                                        <button type="submit" class="flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors text-xs font-medium" onclick="return confirm('¿Estás seguro de quitar el plan Premium?');">
                                            <?= Icons::get('arrow-down', 'w-3.5 h-3.5') ?>
                                            Degradan a Free
                                        </button>
                                    <?php endif; ?>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>