<?php
use App\Core\Session;
use App\Helpers\Icons;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión | StockMaster Pro</title>
    <link rel="shortcut icon" href="<?= BASE_URL ?>img/StockMasterPro.ico" type="image/x-icon">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/main.css?v=<?= time() ?>">
    <link href="<?= BASE_URL ?>css/animations.css" rel="stylesheet">
    <style>
        .glass-effect {
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }
    </style>
</head>
<body class="min-h-screen bg-[#eef2f6] flex flex-col items-center justify-center p-4">

    <!-- Flash Messages (Hidden Data) -->
    <?php if ($msg = Session::getFlash('success')): ?>
        <div id="flash-data" data-type="success" data-message="<?= htmlspecialchars($msg) ?>"></div>
    <?php elseif ($msg = Session::getFlash('error')): ?>
        <div id="flash-data" data-type="error" data-message="<?= htmlspecialchars($msg) ?>"></div>
    <?php endif; ?>

    <!-- Loading Screen (Hidden by default, toggled via JS) -->
    <div id="loading-screen" class="fixed inset-0 z-50 bg-[#eef2f6] flex flex-col items-center justify-center p-4 hidden">
        <div class="bg-white rounded-[32px] p-12 shadow-2xl shadow-slate-200/50 border border-slate-200 flex flex-col items-center text-center max-w-sm w-full animate-in fade-in zoom-in-95 duration-500">
            <!-- Logo Animado -->
            <div class="relative mb-8 group">
                <div class="absolute inset-0 bg-emerald-100 rounded-full animate-ping opacity-20 duration-1000"></div>
                <div class="relative w-28 h-28 bg-white rounded-full border-4 border-slate-50 shadow-lg flex items-center justify-center overflow-hidden animate-bounce">
                    <div class="w-full h-full bg-gradient-to-tr from-emerald-50 to-emerald-100 flex items-center justify-center text-emerald-600">
                    <svg class="w-full h-full p-4" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <linearGradient id="grad_stock_load" x1="0" y1="64" x2="0" y2="0" gradientUnits="userSpaceOnUse">
                                <stop stop-color="#D1FAE5"/>
                                <stop offset="1" stop-color="#34D399"/>
                            </linearGradient>
                            <linearGradient id="grad_growth_load" x1="10" y1="50" x2="54" y2="10" gradientUnits="userSpaceOnUse">
                                <stop stop-color="#10B981"/>
                                <stop offset="1" stop-color="#047857"/>
                            </linearGradient>
                            <filter id="shadow_load" x="-20%" y="-20%" width="140%" height="140%">
                                <feDropShadow dx="0" dy="3" stdDeviation="2" flood-color="#064E3B" flood-opacity="0.2"/>
                            </filter>
                        </defs>
                        <rect x="12" y="38" width="10" height="7" rx="1" fill="url(#grad_stock_load)" opacity="0.5"/>
                        <rect x="12" y="46" width="10" height="7" rx="1" fill="url(#grad_stock_load)" opacity="0.6"/>
                        <rect x="26" y="30" width="10" height="7" rx="1" fill="url(#grad_stock_load)" opacity="0.7"/>
                        <rect x="26" y="38" width="10" height="7" rx="1" fill="url(#grad_stock_load)" opacity="0.8"/>
                        <rect x="26" y="46" width="10" height="7" rx="1" fill="url(#grad_stock_load)" opacity="0.9"/>
                        <rect x="40" y="22" width="10" height="7" rx="1" fill="url(#grad_stock_load)" />
                        <rect x="40" y="30" width="10" height="7" rx="1" fill="url(#grad_stock_load)" />
                        <rect x="40" y="38" width="10" height="7" rx="1" fill="url(#grad_stock_load)" />
                        <rect x="40" y="46" width="10" height="7" rx="1" fill="url(#grad_stock_load)" />
                        <circle cx="10" cy="50" r="5" fill="#10B981" stroke="white" stroke-width="1.5" filter="url(#shadow_load)" />
                        <path d="M10 47V53M7 50H13" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
                        <path d="M10 50 C 18 50, 24 35, 34 38 C 42 40, 46 25, 54 12" stroke="url(#grad_growth_load)" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" fill="none" filter="url(#shadow_load)"/>
                        <path d="M54 12 C 54 12, 44 14, 42 22 C 42 22, 58 24, 60 14 C 60 14, 58 8, 54 12 Z" fill="#10B981" stroke="white" stroke-width="1" filter="url(#shadow_load)"/>
                    </svg>
                    </div>
                </div>
                <div class="absolute bottom-1 right-2 w-7 h-7 bg-emerald-500 border-4 border-white rounded-full shadow-sm"></div>
            </div>

            <h2 class="text-2xl font-bold text-slate-800 mb-2">¡Hola de nuevo!</h2>
            <p class="text-sm text-slate-500 font-medium mb-10 px-4 leading-relaxed">
                Estamos preparando tu entorno de trabajo...
            </p>

            <div class="flex flex-col items-center gap-3">
                <div class="relative">
                    <div class="w-10 h-10 border-4 border-slate-100 border-t-emerald-500 rounded-full animate-spin"></div>
                    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-2 h-2 bg-emerald-500 rounded-full opacity-50"></div>
                </div>
                <p class="text-[10px] text-slate-400 font-bold tracking-widest uppercase animate-pulse">Cargando módulos</p>
            </div>
        </div>
    </div>

    <!-- Login Card -->
    <div class="w-full max-w-[400px] bg-white rounded-[24px] shadow-xl shadow-slate-200/60 border border-slate-200 overflow-hidden animate-in zoom-in-95 duration-300 relative z-10">
        
        <!-- Header -->
        <div class="p-8 pb-6 flex flex-col items-center">
            <div class="relative w-16 h-16 bg-emerald-50 border border-emerald-200 rounded-2xl p-1 shadow-lg shadow-emerald-100 flex items-center justify-center mb-4 overflow-hidden">
                <svg class="w-full h-full" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <linearGradient id="grad_stock_log" x1="0" y1="64" x2="0" y2="0" gradientUnits="userSpaceOnUse">
                            <stop stop-color="#D1FAE5"/>
                            <stop offset="1" stop-color="#34D399"/>
                        </linearGradient>
                        <linearGradient id="grad_growth_log" x1="10" y1="50" x2="54" y2="10" gradientUnits="userSpaceOnUse">
                            <stop stop-color="#10B981"/>
                            <stop offset="1" stop-color="#047857"/>
                        </linearGradient>
                        <filter id="shadow_log" x="-20%" y="-20%" width="140%" height="140%">
                            <feDropShadow dx="0" dy="3" stdDeviation="2" flood-color="#064E3B" flood-opacity="0.2"/>
                        </filter>
                    </defs>
                    <rect x="12" y="38" width="10" height="7" rx="1" fill="url(#grad_stock_log)" opacity="0.5"/>
                    <rect x="12" y="46" width="10" height="7" rx="1" fill="url(#grad_stock_log)" opacity="0.6"/>
                    <rect x="26" y="30" width="10" height="7" rx="1" fill="url(#grad_stock_log)" opacity="0.7"/>
                    <rect x="26" y="38" width="10" height="7" rx="1" fill="url(#grad_stock_log)" opacity="0.8"/>
                    <rect x="26" y="46" width="10" height="7" rx="1" fill="url(#grad_stock_log)" opacity="0.9"/>
                    <rect x="40" y="22" width="10" height="7" rx="1" fill="url(#grad_stock_log)" />
                    <rect x="40" y="30" width="10" height="7" rx="1" fill="url(#grad_stock_log)" />
                    <rect x="40" y="38" width="10" height="7" rx="1" fill="url(#grad_stock_log)" />
                    <rect x="40" y="46" width="10" height="7" rx="1" fill="url(#grad_stock_log)" />
                    <circle cx="10" cy="50" r="5" fill="#10B981" stroke="white" stroke-width="1.5" filter="url(#shadow_log)" />
                    <path d="M10 47V53M7 50H13" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
                    <path d="M10 50 C 18 50, 24 35, 34 38 C 42 40, 46 25, 54 12" stroke="url(#grad_growth_log)" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" fill="none" filter="url(#shadow_log)"/>
                    <path d="M54 12 C 54 12, 44 14, 42 22 C 42 22, 58 24, 60 14 C 60 14, 58 8, 54 12 Z" fill="#10B981" stroke="white" stroke-width="1" filter="url(#shadow_log)"/>
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-slate-800">StockMaster Pro</h2>
            <p class="text-sm text-slate-400 mt-1">Gestión Empresarial Inteligente</p>
        </div>

        <!-- Form -->
        <div class="p-8 pt-0">
            <form action="index.php?controlador=login&accion=verificar" method="POST" id="login-form" class="space-y-5">
                
                <div class="space-y-1.5">
                    <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wide ml-1">Usuario</label>
                    <div class="relative group">
                        <input type="text" name="username" required
                            value="<?= htmlspecialchars($_COOKIE['last_username'] ?? '') ?>"
                            class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 outline-none transition-all text-sm font-medium text-slate-700 placeholder:text-slate-400 group-hover:border-slate-300"
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
                            class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 outline-none transition-all text-sm font-medium text-slate-700 placeholder:text-slate-400 group-hover:border-slate-300"
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
        
        <!-- Footer -->
        <div class="p-5 bg-slate-50 border-t border-slate-100 text-center">
            <p class="text-xs text-slate-500">¿No tienes cuenta? <a href="index.php?controlador=registro&accion=index" class="font-bold text-emerald-600 hover:text-emerald-700">Regístrate aquí</a></p>
        </div>
    </div>

    <p class="text-[10px] text-slate-400 mt-8 font-medium fixed bottom-4">© 2025 StockMaster Solutions <span class="opacity-50 mx-1">|</span> v<?= APP_VERSION ?></p>

    <!-- Global JS (for notifications) -->
    <script src="<?= BASE_URL ?>js/core/utils.js?v=<?= time() ?>"></script>
    <script src="<?= BASE_URL ?>js/core/notifications.js?v=<?= time() ?>"></script>
    <script src="<?= BASE_URL ?>js/app.js?v=<?= time() ?>"></script>
    
    <!-- Módulo de Autenticación (cargado desde archivo externo) -->
    <script src="<?= BASE_URL ?>js/pages/auth.js?v=<?= time() ?>"></script>
    <script src="<?= BASE_URL ?>js/electron-bridge.js?v=<?= time() ?>"></script>

        
    <!-- Modal Recuperación -->
    <div id="modal-recovery" class="fixed inset-0 z-[100] hidden">
        <!-- Backdrop (System Standard) -->
        <div class="absolute inset-0 bg-slate-200/50 dark:bg-slate-900/50 backdrop-blur-sm transition-opacity" onclick="closeRecoveryModal()"></div>
        
        <!-- Wrapper for Flex Centering -->
        <div class="fixed inset-0 flex items-center justify-center p-4">
            <!-- Content -->
            <div class="w-full max-w-sm bg-white rounded-2xl shadow-2xl border border-slate-200 p-6 text-center relative overflow-hidden animate-in zoom-in-95 duration-300">
                <!-- Header -->
                <div class="w-12 h-12 bg-indigo-50 rounded-full flex items-center justify-center mx-auto mb-3 text-indigo-600">
                    <?= Icons::get('lock', 'w-6 h-6') ?>
                </div>
                
                <h3 class="text-lg font-bold text-slate-800 mb-1">Desbloqueo por Soporte</h3>
                <p class="text-xs text-slate-500 mb-4 px-2">
                    Proporciona el siguiente código al administrador para recibir tu clave de autorización.
                </p>
                
                <!-- Challenge Code Display -->
                <div class="bg-slate-900 rounded-xl p-3 mb-4 shadow-inner">
                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mb-1">TU CÓDIGO DE SOLICITUD</p>
                    <div class="flex items-center justify-between px-2">
                        <span id="challenge-code" class="text-2xl font-mono font-bold text-white tracking-wider mx-auto">CARGANDO...</span>
                    </div>
                </div>

                <!-- Form -->
                <form onsubmit="realizarDesbloqueo(event)" class="space-y-3">
                    <div class="text-left">
                        <label class="block text-xs font-bold text-slate-600 mb-1 ml-1">Usuario a Recuperar</label>
                        <input type="text" id="unlock-username" required
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none transition-all placeholder-slate-400"
                               placeholder="Ej. admin">
                    </div>

                    <div class="text-left">
                        <label class="block text-xs font-bold text-slate-600 mb-1 ml-1">Código de Autorización</label>
                        <input type="text" id="unlock-response" required
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm font-mono uppercase tracking-widest focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none transition-all placeholder-slate-400"
                               placeholder="XXXXXX">
                    </div>

                    <div id="unlock-msg" class="hidden text-xs font-medium text-center p-2 rounded-lg"></div>

                    <div class="grid grid-cols-2 gap-3 pt-2">
                        <button type="button" onclick="closeRecoveryModal()" class="px-4 py-2 bg-white border border-slate-200 text-slate-600 rounded-xl font-medium hover:bg-slate-50 text-sm transition-colors">
                            Cancelar
                        </button>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold text-sm shadow-md shadow-indigo-500/20 transition-colors">
                            Desbloquear
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Script Modal -->
    <script>
        // Generador de Challenge Aleatorio (4Chars-4Chars)
        function generateChallenge() {
            const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // No I, O, 0, 1 for clarity
            let result = '';
            for (let i = 0; i < 8; i++) {
                if(i===4) result += '-';
                result += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            return result;
        }

        function openRecoveryModal(e) {
            e.preventDefault();
            const challenge = generateChallenge();
            document.getElementById('challenge-code').textContent = challenge;
            // Pre-fill username from main form if exists
            const mainUser = document.querySelector('input[name="username"]')?.value;
            if(mainUser) document.getElementById('unlock-username').value = mainUser;
            
            document.getElementById('modal-recovery').classList.remove('hidden');
            document.getElementById('unlock-response').value = '';
            document.getElementById('unlock-msg').classList.add('hidden');
        }

        function closeRecoveryModal() {
            document.getElementById('modal-recovery').classList.add('hidden');
        }

        async function realizarDesbloqueo(e) {
            e.preventDefault();
            
            const btn = e.target.querySelector('button[type="submit"]');
            const originalText = btn.innerText;
            btn.innerText = 'Verificando...';
            btn.disabled = true;

            const challenge = document.getElementById('challenge-code').textContent;
            const responseCode = document.getElementById('unlock-response').value.trim().toUpperCase();
            const username = document.getElementById('unlock-username').value.trim();

            try {
                const formData = new FormData();
                formData.append('challenge', challenge);
                formData.append('response', responseCode);
                formData.append('username', username);

                const res = await fetch('index.php?controlador=login&accion=verificarDesbloqueo', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await res.json();
                
                const msgBox = document.getElementById('unlock-msg');
                msgBox.classList.remove('hidden', 'bg-red-50', 'text-red-600', 'bg-emerald-50', 'text-emerald-600');
                
                if (data.success) {
                    msgBox.classList.add('bg-emerald-50', 'text-emerald-600');
                    msgBox.textContent = data.message;
                    // Success!
                    setTimeout(() => {
                        if (window.Notifications) {
                            window.Notifications.show(data.message, 'success');
                        } else {
                            alert(data.message);
                        }
                        closeRecoveryModal();
                    }, 1500);
                } else {
                    msgBox.classList.add('bg-red-50', 'text-red-600');
                    msgBox.textContent = data.message;
                }

            } catch (err) {
                console.error(err);
                alert('Error de conexión');
            } finally {
                btn.innerText = originalText;
                btn.disabled = false;
            }
        }
    </script>
</body>
</html>