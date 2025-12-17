<?php 
use App\Helpers\Icons; 
use App\Core\Session;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Cuenta | StockMaster Pro</title>
    <link rel="stylesheet" href="css/main.css?v=<?= time() ?>">
    <link href="css/animations.css" rel="stylesheet">
</head>
<body class="min-h-screen bg-[#eef2f6] flex flex-col items-center justify-center p-4">

    <!-- Flash Messages (Hidden Data) -->
    <?php if ($msg = Session::getFlash('success')): ?>
        <div id="flash-data" data-type="success" data-message="<?= htmlspecialchars($msg) ?>"></div>
    <?php elseif ($msg = Session::getFlash('error')): ?>
        <div id="flash-data" data-type="error" data-message="<?= htmlspecialchars($msg) ?>"></div>
    <?php endif; ?>

    <!-- Loading Screen (Hidden) -->
    <div id="loading-screen" class="fixed inset-0 z-50 bg-[#eef2f6] flex flex-col items-center justify-center p-4 hidden">
        <div class="bg-white rounded-[32px] p-12 shadow-2xl shadow-slate-200/50 border border-slate-200 flex flex-col items-center text-center max-w-sm w-full animate-in fade-in zoom-in-95 duration-500">
             <div class="relative mb-8 group">
                <div class="absolute inset-0 bg-emerald-100 rounded-full animate-ping opacity-20 duration-1000"></div>
                <div class="relative w-28 h-28 bg-white rounded-full border-4 border-slate-50 shadow-lg flex items-center justify-center overflow-hidden animate-bounce">
                    <div class="w-full h-full bg-gradient-to-tr from-emerald-50 to-emerald-100 flex items-center justify-center text-emerald-600">
                        <svg class="w-full h-full p-4" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <defs>
                                <linearGradient id="grad_stock_load_r" x1="0" y1="64" x2="0" y2="0" gradientUnits="userSpaceOnUse">
                                    <stop stop-color="#D1FAE5"/>
                                    <stop offset="1" stop-color="#34D399"/>
                                </linearGradient>
                                <linearGradient id="grad_growth_load_r" x1="10" y1="50" x2="54" y2="10" gradientUnits="userSpaceOnUse">
                                    <stop stop-color="#10B981"/>
                                    <stop offset="1" stop-color="#047857"/>
                                </linearGradient>
                                <filter id="shadow_load_r" x="-20%" y="-20%" width="140%" height="140%">
                                    <feDropShadow dx="0" dy="3" stdDeviation="2" flood-color="#064E3B" flood-opacity="0.2"/>
                                </filter>
                            </defs>
                            <rect x="12" y="38" width="10" height="7" rx="1" fill="url(#grad_stock_load_r)" opacity="0.5"/>
                            <rect x="12" y="46" width="10" height="7" rx="1" fill="url(#grad_stock_load_r)" opacity="0.6"/>
                            <rect x="26" y="30" width="10" height="7" rx="1" fill="url(#grad_stock_load_r)" opacity="0.7"/>
                            <rect x="26" y="38" width="10" height="7" rx="1" fill="url(#grad_stock_load_r)" opacity="0.8"/>
                            <rect x="26" y="46" width="10" height="7" rx="1" fill="url(#grad_stock_load_r)" opacity="0.9"/>
                            <rect x="40" y="22" width="10" height="7" rx="1" fill="url(#grad_stock_load_r)" />
                            <rect x="40" y="30" width="10" height="7" rx="1" fill="url(#grad_stock_load_r)" />
                            <rect x="40" y="38" width="10" height="7" rx="1" fill="url(#grad_stock_load_r)" />
                            <rect x="40" y="46" width="10" height="7" rx="1" fill="url(#grad_stock_load_r)" />
                            <circle cx="10" cy="50" r="5" fill="#10B981" stroke="white" stroke-width="1.5" filter="url(#shadow_load_r)" />
                            <path d="M10 47V53M7 50H13" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
                            <path d="M10 50 C 18 50, 24 35, 34 38 C 42 40, 46 25, 54 12" stroke="url(#grad_growth_load_r)" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" fill="none" filter="url(#shadow_load_r)"/>
                            <path d="M54 12 C 54 12, 44 14, 42 22 C 42 22, 58 24, 60 14 C 60 14, 58 8, 54 12 Z" fill="#10B981" stroke="white" stroke-width="1" filter="url(#shadow_load_r)"/>
                        </svg>
                    </div>
                </div>
            </div>
            <h2 class="text-2xl font-bold text-slate-800 mb-2">Creando Cuenta...</h2>
            <div class="flex flex-col items-center gap-3 mt-8">
                 <div class="relative">
                    <div class="w-10 h-10 border-4 border-slate-100 border-t-emerald-500 rounded-full animate-spin"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Register Card -->
    <div class="w-full max-w-[400px] bg-white rounded-[24px] shadow-xl shadow-slate-200/60 border border-slate-200 overflow-hidden animate-in zoom-in-95 duration-300 relative z-10">
        
        <!-- Header -->
        <div class="p-8 pb-6 flex flex-col items-center">
            <div class="relative w-16 h-16 bg-emerald-50 border border-emerald-200 rounded-2xl p-1 shadow-lg shadow-emerald-100 flex items-center justify-center mb-4 overflow-hidden">
                <svg class="w-full h-full" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <linearGradient id="grad_stock_reg" x1="0" y1="64" x2="0" y2="0" gradientUnits="userSpaceOnUse">
                            <stop stop-color="#D1FAE5"/>
                            <stop offset="1" stop-color="#34D399"/>
                        </linearGradient>
                        <linearGradient id="grad_growth_reg" x1="10" y1="50" x2="54" y2="10" gradientUnits="userSpaceOnUse">
                            <stop stop-color="#10B981"/>
                            <stop offset="1" stop-color="#047857"/>
                        </linearGradient>
                        <filter id="shadow_reg" x="-20%" y="-20%" width="140%" height="140%">
                            <feDropShadow dx="0" dy="3" stdDeviation="2" flood-color="#064E3B" flood-opacity="0.2"/>
                        </filter>
                    </defs>
                    <rect x="12" y="38" width="10" height="7" rx="1" fill="url(#grad_stock_reg)" opacity="0.5"/>
                    <rect x="12" y="46" width="10" height="7" rx="1" fill="url(#grad_stock_reg)" opacity="0.6"/>
                    <rect x="26" y="30" width="10" height="7" rx="1" fill="url(#grad_stock_reg)" opacity="0.7"/>
                    <rect x="26" y="38" width="10" height="7" rx="1" fill="url(#grad_stock_reg)" opacity="0.8"/>
                    <rect x="26" y="46" width="10" height="7" rx="1" fill="url(#grad_stock_reg)" opacity="0.9"/>
                    <rect x="40" y="22" width="10" height="7" rx="1" fill="url(#grad_stock_reg)" />
                    <rect x="40" y="30" width="10" height="7" rx="1" fill="url(#grad_stock_reg)" />
                    <rect x="40" y="38" width="10" height="7" rx="1" fill="url(#grad_stock_reg)" />
                    <rect x="40" y="46" width="10" height="7" rx="1" fill="url(#grad_stock_reg)" />
                    <circle cx="10" cy="50" r="5" fill="#10B981" stroke="white" stroke-width="1.5" filter="url(#shadow_reg)" />
                    <path d="M10 47V53M7 50H13" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
                    <path d="M10 50 C 18 50, 24 35, 34 38 C 42 40, 46 25, 54 12" stroke="url(#grad_growth_reg)" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" fill="none" filter="url(#shadow_reg)"/>
                    <path d="M54 12 C 54 12, 44 14, 42 22 C 42 22, 58 24, 60 14 C 60 14, 58 8, 54 12 Z" fill="#10B981" stroke="white" stroke-width="1" filter="url(#shadow_reg)"/>
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-slate-800">Crear Cuenta</h2>
            <p class="text-sm text-slate-400 mt-1">Únete a Nexus Enterprise</p>
        </div>

        <!-- Form -->
        <div class="p-8 pt-0">
            <form action="index.php?controlador=registro&accion=guardar" method="POST" id="register-form" class="space-y-4">
                
                <div class="space-y-1">
                    <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wide ml-1">Usuario</label>
                    <div class="relative group">
                        <input type="text" name="username" required
                            class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 outline-none transition-all text-sm font-medium text-slate-700 group-hover:border-slate-300"
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
                            class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 outline-none transition-all text-sm font-medium text-slate-700 group-hover:border-slate-300"
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
                            class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 outline-none transition-all text-sm font-medium text-slate-700 group-hover:border-slate-300"
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
                            class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 outline-none transition-all text-sm font-medium text-slate-700 group-hover:border-slate-300"
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
        
        <!-- Footer -->
        <div class="p-5 bg-slate-50 border-t border-slate-100 text-center">
            <p class="text-xs text-slate-500">¿Ya tienes cuenta? <a href="index.php?controlador=login&accion=index" class="font-bold text-emerald-600 hover:text-emerald-700">Inicia sesión</a></p>
        </div>
    </div>

    <script src="<?= BASE_URL ?>js/core/utils.js?v=<?= time() ?>"></script>
    <script src="<?= BASE_URL ?>js/core/notifications.js?v=<?= time() ?>"></script>
    <script src="<?= BASE_URL ?>js/app.js?v=<?= time() ?>"></script>

    <!-- Módulo de Autenticación (cargado desde archivo externo) -->
    <script src="<?= BASE_URL ?>js/pages/auth.js?v=<?= time() ?>"></script>
</body>
</html>