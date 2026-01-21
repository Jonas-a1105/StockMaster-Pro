<?php
use App\Helpers\Icons;

$titulo = 'Iniciar Sesión | StockMaster Pro';
?>
<!-- Login Card -->
<div class="w-full max-w-[400px] bg-white rounded-[24px] shadow-xl shadow-slate-200/60 border border-slate-200 overflow-hidden animate-in zoom-in-95 duration-300 relative z-10">
    
    <!-- Header -->
    <div class="p-8 pb-6 flex flex-col items-center">
        <div class="relative w-16 h-16 bg-emerald-50 border border-emerald-200 rounded-2xl p-1 shadow-lg shadow-emerald-100 flex items-center justify-center mb-4 overflow-hidden">
            <?= Icons::get('logo', 'w-full h-full') ?>
        </div>
        <h2 class="text-2xl font-bold text-slate-800">StockMaster Pro</h2>
        <p class="text-sm text-slate-400 mt-1">Gestión Empresarial Inteligente</p>
    </div>

    <!-- Form -->
    <div class="p-8 pt-0">
        <form action="index.php?controlador=login&accion=verificar" method="POST" id="login-form" class="space-y-5">
            <?= \App\Helpers\Security::csrfField() ?>
            
            <div class="space-y-1.5">
                <label for="username" class="text-[11px] font-bold text-slate-500 uppercase tracking-wide ml-1">Usuario</label>
                <div class="relative group">
                    <input type="text" name="username" id="username" required autofocus
                        aria-label="Nombre de usuario"
                        value="<?= htmlspecialchars($_COOKIE['last_username'] ?? '') ?>"
                        class="input-with-icon-left w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 outline-none transition-all text-sm font-medium text-slate-700 placeholder:text-slate-400 group-hover:border-slate-300"
                        placeholder="usuario123">
                    <div class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-emerald-500 transition-colors">
                        <?= Icons::get('user', 'w-[18px] h-[18px]') ?>
                    </div>
                </div>
            </div>

            <div class="space-y-1.5">
                <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wide ml-1">Contraseña</label>
                <div class="relative group">
                    <input type="password" name="password" required
                        class="input-with-icon-left w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 outline-none transition-all text-sm font-medium text-slate-700 placeholder:text-slate-400 group-hover:border-slate-300"
                        placeholder="••••••••">
                     <div class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-emerald-500 transition-colors">
                        <?= Icons::get('lock', 'w-[18px] h-[18px]') ?>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between mt-1 px-1">
                <label class="flex items-center gap-2 cursor-pointer group select-none">
                    <input type="checkbox" name="remember" class="w-4 h-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 border bg-slate-50">
                    <span class="text-xs font-medium text-slate-500 group-hover:text-slate-700">Recordarme</span>
                </label>
                <a href="#" onclick="openRecoveryModal(event)" class="text-xs font-bold text-emerald-600 hover:text-emerald-700 hover:underline">Recuperar clave</a>
            </div>

            <button type="submit" id="btn-submit"
                class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3.5 rounded-xl shadow-lg shadow-emerald-200 border border-emerald-500 transition-all active:scale-[0.98] flex items-center justify-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed mt-6">
                <span id="btn-text">Entrar al Sistema</span>
                <span id="btn-icon"><?= Icons::get('arrow-right', 'w-[18px] h-[18px]') ?></span>
                <span id="btn-loader" class="hidden animate-spin"><?= Icons::get('loader', 'w-5 h-5') ?></span>
            </button>
        </form>
    </div>
    
    <!-- Footer Link -->
    <div class="p-5 bg-slate-50 border-t border-slate-100 text-center">
        <p class="text-xs text-slate-500">¿No tienes cuenta? <a href="index.php?controlador=registro&accion=index" class="font-bold text-emerald-600 hover:text-emerald-700">Regístrate aquí</a></p>
    </div>
</div>

<!-- Modal Recuperación -->
<div id="modal-recovery" class="fixed inset-0 hidden z-[100]">
    <div class="absolute inset-0 bg-slate-200/50 dark:bg-slate-900/50 backdrop-blur-sm transition-opacity" onclick="closeRecoveryModal()"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="w-full max-w-sm bg-white rounded-2xl shadow-2xl border border-slate-200 p-6 text-center relative overflow-hidden animate-in zoom-in-95 duration-300">
            <div class="w-12 h-12 bg-indigo-50 rounded-full flex items-center justify-center mx-auto mb-3 text-indigo-600">
                <?= Icons::get('lock', 'w-6 h-6') ?>
            </div>
            
            <h3 class="text-lg font-bold text-slate-800 mb-1">Desbloqueo por Soporte</h3>
            <p class="text-xs text-slate-500 mb-4 px-2">Proporciona el siguiente código al administrador para recibir tu clave de autorización.</p>
            
            <div class="bg-slate-900 rounded-xl p-3 mb-4 shadow-inner">
                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mb-1">TU CÓDIGO DE SOLICITUD</p>
                <div class="flex items-center justify-between px-2">
                    <span id="challenge-code" class="text-2xl font-mono font-bold text-white tracking-wider mx-auto">CARGANDO...</span>
                </div>
            </div>

            <form onsubmit="realizarDesbloqueo(event)" class="space-y-3">
                <div class="text-left">
                    <label class="block text-xs font-bold text-slate-600 mb-1 ml-1">Usuario a Recuperar</label>
                    <input type="text" id="unlock-username" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none transition-all placeholder-slate-400" placeholder="Ej. admin">
                </div>
                <div class="text-left">
                    <label class="block text-xs font-bold text-slate-600 mb-1 ml-1">Código de Autorización</label>
                    <input type="text" id="unlock-response" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm font-mono uppercase tracking-widest focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none transition-all placeholder-slate-400" placeholder="XXXXXX">
                </div>
                <div id="unlock-msg" class="hidden text-xs font-medium text-center p-2 rounded-lg"></div>
                <div class="grid grid-cols-2 gap-3 pt-2">
                    <button type="button" onclick="closeRecoveryModal()" class="px-4 py-2 bg-white border border-slate-200 text-slate-600 rounded-xl font-medium hover:bg-slate-50 text-sm transition-colors">Cancelar</button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold text-sm shadow-md shadow-indigo-500/20 transition-colors">Desbloquear</button>
                </div>
            </form>
        </div>
    </div>
</div>