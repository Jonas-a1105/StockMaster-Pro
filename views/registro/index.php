<?php 
use App\Helpers\Icons; 

$titulo = 'Crear Cuenta | StockMaster Pro';
$loader_titulo = 'Creando Cuenta...';
$loader_subtitulo = 'Estamos registrando tu acceso empresarial.';
?>
<!-- Register Card -->
<div class="w-full max-w-[400px] bg-white rounded-[24px] shadow-xl shadow-slate-200/60 border border-slate-200 overflow-hidden animate-in zoom-in-95 duration-300 relative z-10">
    
    <!-- Header -->
    <div class="p-8 pb-6 flex flex-col items-center">
        <div class="relative w-16 h-16 bg-emerald-50 border border-emerald-200 rounded-2xl p-1 shadow-lg shadow-emerald-100 flex items-center justify-center mb-4 overflow-hidden">
            <?= Icons::get('logo', 'w-full h-full') ?>
        </div>
        <h2 class="text-2xl font-bold text-slate-800">Crear Cuenta</h2>
        <p class="text-sm text-slate-400 mt-1">Únete a Nexus Enterprise</p>
    </div>

    <!-- Form -->
    <div class="p-8 pt-0">
        <form action="index.php?controlador=registro&accion=guardar" method="POST" id="register-form" class="space-y-4">
            <?= \App\Helpers\Security::csrfField() ?>
            
            <div class="space-y-1">
                <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wide ml-1">Usuario</label>
                <div class="relative group">
                    <input type="text" name="username" required
                        class="input-with-icon-left w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 outline-none transition-all text-sm font-medium text-slate-700 group-hover:border-slate-300"
                        placeholder="usuario123">
                    <div class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-emerald-500 transition-colors">
                        <?= Icons::get('user', 'w-[18px] h-[18px]') ?>
                    </div>
                </div>
            </div>

            <div class="space-y-1">
                <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wide ml-1">Email</label>
                <div class="relative group">
                    <input type="email" name="email" required
                        class="input-with-icon-left w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 outline-none transition-all text-sm font-medium text-slate-700 group-hover:border-slate-300"
                        placeholder="nombre@empresa.com">
                    <div class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-emerald-500 transition-colors">
                        <?= Icons::get('mail', 'w-[18px] h-[18px]') ?>
                    </div>
                </div>
            </div>

            <div class="space-y-1">
                <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wide ml-1">Contraseña</label>
                <div class="relative group">
                    <input type="password" name="password" id="password" required
                        class="input-with-icon-left w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 outline-none transition-all text-sm font-medium text-slate-700 group-hover:border-slate-300"
                        placeholder="••••••••">
                    <div class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-emerald-500 transition-colors">
                        <?= Icons::get('lock', 'w-[18px] h-[18px]') ?>
                    </div>
                </div>
            </div>

            <div class="space-y-1">
                <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wide ml-1">Confirmar</label>
                <div class="relative group">
                    <input type="password" name="password_confirm" id="password_confirm" required
                        class="input-with-icon-left w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 outline-none transition-all text-sm font-medium text-slate-700 group-hover:border-slate-300"
                        placeholder="••••••••">
                    <div class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-emerald-500 transition-colors">
                        <?= Icons::get('check', 'w-[18px] h-[18px]') ?>
                    </div>
                </div>
            </div>

            <button type="submit" id="btn-submit"
                class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3.5 rounded-xl shadow-lg shadow-emerald-200 border border-emerald-500 transition-all active:scale-[0.98] flex items-center justify-center gap-2 mt-6">
                <span id="btn-text">Registrarse</span>
                <span id="btn-icon"><?= Icons::get('arrow-right', 'w-[18px] h-[18px]') ?></span>
                <span id="btn-loader" class="hidden animate-spin"><?= Icons::get('loader', 'w-5 h-5') ?></span>
            </button>
        </form>
    </div>
    
    <!-- Footer Link -->
    <div class="p-5 bg-slate-50 border-t border-slate-100 text-center">
        <p class="text-xs text-slate-500">¿Ya tienes cuenta? <a href="index.php?controlador=login&accion=index" class="font-bold text-emerald-600 hover:text-emerald-700">Inicia sesión</a></p>
    </div>
</div>