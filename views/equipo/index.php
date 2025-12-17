<?php
/**
 * GESTIÓN DE EQUIPO - Vista Enterprise
 * views/equipo/index.php
 */
use App\Helpers\Icons;

$empleados = $empleados ?? [];
?>

<!-- Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-800 dark:text-white flex items-center gap-3">
            <?= Icons::get('users', 'w-7 h-7 text-indigo-500') ?>
            Gestión de Equipo
        </h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
            Agrega empleados para que puedan acceder a tu inventario y POS
        </p>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">

    <!-- Card: Agregar Miembro (1/3) -->
    <div class="md:col-span-1">
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm sticky top-6">
            <h3 class="text-lg font-bold text-slate-800 dark:text-white flex items-center gap-2 mb-6">
                <?= Icons::get('user-plus', 'w-5 h-5 text-emerald-500') ?>
                Agregar Miembro
            </h3>
            
            <form action="index.php?controlador=equipo&accion=crear" method="POST" class="space-y-4">
                
                <!-- Email -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Correo Electrónico</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <?= Icons::get('mail', 'w-5 h-5 text-slate-400') ?>
                        </div>
                        <input type="email" name="email" required placeholder="empleado@empresa.com"
                               class="w-full pl-10 pr-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 border-0 rounded-xl text-slate-800 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 transition-all">
                    </div>
                </div>

                <!-- Password -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Contraseña Temporal</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <?= Icons::get('lock', 'w-5 h-5 text-slate-400') ?>
                        </div>
                        <input type="password" name="password" required placeholder="••••••••"
                               class="w-full pl-10 pr-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 border-0 rounded-xl text-slate-800 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 transition-all">
                    </div>
                </div>
                
                <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold shadow-lg shadow-emerald-500/30 hover:shadow-emerald-500/40 transition-all mt-4">
                    <?= Icons::get('plus', 'w-5 h-5') ?>
                    Crear Cuenta
                </button>
            </form>
        </div>
    </div>

    <!-- Lista Miembros (2/3) -->
    <div class="md:col-span-2">
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-100 dark:border-slate-700">
                <h3 class="font-bold text-slate-800 dark:text-white">Miembros Actuales del Equipo</h3>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-slate-50 dark:bg-slate-700/50 text-slate-500 dark:text-slate-400 font-medium">
                        <tr>
                            <th class="px-6 py-4 uppercase text-xs">Email / Usuario</th>
                            <th class="px-6 py-4 uppercase text-xs">Fecha Registro</th>
                            <th class="px-6 py-4 uppercase text-xs text-center">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        <?php if (empty($empleados)): ?>
                            <tr>
                                <td colspan="3" class="px-6 py-12 text-center text-slate-400">
                                    <?= Icons::get('users', 'w-12 h-12 mx-auto mb-3 text-slate-200 dark:text-slate-600') ?>
                                    No hay miembros en el equipo.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($empleados as $emp): ?>
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                                            <?= Icons::get('user', 'w-4 h-4') ?>
                                        </div>
                                        <span class="font-medium text-slate-800 dark:text-white">
                                            <?= htmlspecialchars($emp['email']) ?>
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-slate-500 dark:text-slate-400">
                                    <?= (new DateTime($emp['fecha_registro']))->format('d/m/Y') ?>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <form action="index.php?controlador=equipo&accion=eliminar" method="POST" class="inline" onsubmit="return confirm('¿Estás seguro de eliminar el acceso a este miembro? Esta acción no se puede deshacer.');">
                                        <input type="hidden" name="id" value="<?= $emp['id'] ?>">
                                        <button type="submit" class="p-2 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors" title="Eliminar Acceso">
                                            <?= Icons::get('trash', 'w-4 h-4') ?>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>