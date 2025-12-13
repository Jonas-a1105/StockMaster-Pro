<?php
/**
 * PERFIL DE USUARIO - Vista Enterprise
 * views/perfil/index.php
 */
use App\Helpers\Icons;

$current_email = $current_email ?? '';
$current_username = $current_username ?? '';
?>

<!-- Header -->
<div class="mb-8">
    <h1 class="text-2xl font-bold text-slate-800 dark:text-white flex items-center gap-3">
        <?= Icons::get('user-cog', 'w-8 h-8 text-indigo-500') ?>
        Configuración de Perfil
    </h1>
    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
        Gestiona tus credenciales y preferencias de acceso
    </p>
</div>

<!-- Main Content Card -->
<div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-8 shadow-sm">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
        
        <!-- Información Personal -->
        <div class="relative">
            <h3 class="text-lg font-bold text-slate-800 dark:text-white flex items-center gap-2 mb-8">
                <?= Icons::get('user', 'w-5 h-5 text-indigo-500') ?>
                Información Personal
            </h3>
            
            <form action="index.php?controlador=perfil&accion=actualizarInformacion" method="POST" class="space-y-6">
                <!-- Usuario -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Nombre de Usuario</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <?= Icons::get('user', 'w-5 h-5 text-slate-400') ?>
                        </div>
                        <input type="text" name="username" value="<?= htmlspecialchars($current_username) ?>" required placeholder="jdoe" style="padding-left: 3rem;"
                               class="w-full pr-4 py-3 bg-slate-50 dark:bg-slate-700/50 border-0 rounded-xl text-slate-800 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 transition-all">
                    </div>
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Correo Electrónico</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <?= Icons::get('mail', 'w-5 h-5 text-slate-400') ?>
                        </div>
                        <input type="email" name="email" value="<?= htmlspecialchars($current_email) ?>" required placeholder="correo@ejemplo.com" style="padding-left: 3rem;"
                               class="w-full pr-4 py-3 bg-slate-50 dark:bg-slate-700/50 border-0 rounded-xl text-slate-800 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 transition-all">
                    </div>
                </div>
                
                <div class="pt-4 border-t border-slate-100 dark:border-slate-700">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Contraseña Actual (Confirmación)</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <?= Icons::get('lock', 'w-5 h-5 text-slate-400') ?>
                        </div>
                        <input type="password" name="password" required placeholder="••••••••" style="padding-left: 3rem;"
                               class="w-full pr-4 py-3 bg-slate-50 dark:bg-slate-700/50 border-0 rounded-xl text-slate-800 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 transition-all">
                    </div>
                    <p class="text-xs text-slate-400 mt-2">Ingresa tu contraseña actual para guardar los cambios.</p>
                </div>
                
                <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/40 transition-all mt-2">
                    <?= Icons::get('save', 'w-5 h-5') ?>
                    Guardar Información
                </button>
            </form>

            <!-- Vertical Divider for Desktop -->
            <div class="hidden lg:block absolute right-[-1.5rem] top-0 bottom-0 w-px bg-slate-200 dark:bg-slate-700"></div>
        </div>

        <!-- Seguridad -->
        <div>
            <h3 class="text-lg font-bold text-slate-800 dark:text-white flex items-center gap-2 mb-8">
                <?= Icons::get('lock', 'w-5 h-5 text-emerald-500') ?>
                Seguridad
            </h3>
            
            <form action="index.php?controlador=perfil&accion=actualizarPassword" method="POST" class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Contraseña Actual</label>
                     <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <?= Icons::get('lock', 'w-5 h-5 text-slate-400') ?>
                        </div>
                        <input type="password" name="current_password" required placeholder="••••••••" style="padding-left: 3rem;"
                               class="w-full pr-4 py-3 bg-slate-50 dark:bg-slate-700/50 border-0 rounded-xl text-slate-800 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 transition-all">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Nueva Contraseña</label>
                     <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <?= Icons::get('help-circle', 'w-5 h-5 text-slate-400') ?>
                        </div>
                        <input type="password" name="new_password" required placeholder="••••••••" style="padding-left: 3rem;"
                               class="w-full pr-4 py-3 bg-slate-50 dark:bg-slate-700/50 border-0 rounded-xl text-slate-800 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 transition-all">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Confirmar Nueva Contraseña</label>
                     <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <?= Icons::get('check-circle', 'w-5 h-5 text-slate-400') ?>
                        </div>
                        <input type="password" name="confirm_password" required placeholder="••••••••" style="padding-left: 3rem;"
                               class="w-full pr-4 py-3 bg-slate-50 dark:bg-slate-700/50 border-0 rounded-xl text-slate-800 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 transition-all">
                    </div>
                </div>
                
                <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold shadow-lg shadow-emerald-500/30 hover:shadow-emerald-500/40 transition-all mt-2">
                    <?= Icons::get('save', 'w-5 h-5') ?>
                    Actualizar Contraseña
                </button>
            </form>
        </div>
    </div>
</div>