<?php
use App\Helpers\Icons;
?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="max-w-6xl mx-auto">
        <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
            <!-- Header Banner -->
            <div class="bg-gradient-to-r from-slate-800 to-slate-900 px-8 py-12 text-center relative overflow-hidden">
                <div class="absolute inset-0 opacity-10 pattern-grid-lg"></div>
                <div class="relative z-10 flex flex-col items-center">
                    <div class="w-20 h-20 rounded-2xl flex items-center justify-center mb-6 shadow-lg shadow-amber-500/30 ring-4 ring-white/10 transform rotate-3"
                         style="background: linear-gradient(135deg, #fbbf24 0%, #f97316 100%);">
                        <?= Icons::get('crown', 'w-10 h-10 text-white') ?>
                    </div>
                    
                    <h1 class="text-3xl sm:text-4xl font-bold text-white mb-2">
                        ¡Bienvenido, <?= htmlspecialchars($_SESSION['user_name'] ?? $email ?? 'Usuario') ?>!
                    </h1>
                    <p class="text-lg font-medium" style="color: #fde68a;">
                        ¡Activa tu Plan Premium Ahora!
                    </p>
                </div>
            </div>

            <!-- Content -->
            <div class="p-8 sm:p-12 text-center">
                <div class="mb-8">
                    <div class="inline-flex items-center gap-2 px-4 py-2 bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400 rounded-full text-sm font-medium border border-red-200 dark:border-red-900/50 mb-6">
                        <?= Icons::get('lock', 'w-4 h-4') ?>
                        <span>Activación Necesaria</span>
                    </div>

                    <h2 class="text-2xl font-bold text-slate-800 dark:text-white mb-4">
                        Desbloquea todo el potencial
                    </h2>
                    <p class="text-slate-600 dark:text-slate-400 max-w-xl mx-auto leading-relaxed">
                        Tu período de prueba ha finalizado o aún no ha sido activado. 
                        Para acceder al <strong class="text-slate-800 dark:text-slate-200">Inventario profesional, POS, Ventas y Reportes avanzados</strong>, necesitas activar un plan Premium.
                    </p>
                </div>

                <!-- Features Grid Premium Refactor -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-10 text-left">
                    <!-- Dashboard -->
                    <div class="bg-slate-50 dark:bg-slate-700/50 p-4 rounded-xl border border-slate-100 dark:border-slate-700 hover:shadow-lg hover:-translate-y-1 transition-all duration-300 group">
                        <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-lg flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                            <?= Icons::get('chart', 'w-5 h-5') ?>
                        </div>
                        <h3 class="font-bold text-slate-800 dark:text-white text-sm mb-1">Dashboard Financiero</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Ganancias y costos en tiempo real.</p>
                    </div>

                    <!-- Inventario -->
                    <div class="bg-slate-50 dark:bg-slate-700/50 p-4 rounded-xl border border-slate-100 dark:border-slate-700 hover:shadow-lg hover:-translate-y-1 transition-all duration-300 group">
                        <div class="w-10 h-10 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 rounded-lg flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                            <?= Icons::get('inventory', 'w-5 h-5') ?>
                        </div>
                        <h3 class="font-bold text-slate-800 dark:text-white text-sm mb-1">Gestión Total</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Control de stock, precios y márgenes.</p>
                    </div>

                    <!-- POS -->
                    <div class="bg-slate-50 dark:bg-slate-700/50 p-4 rounded-xl border border-slate-100 dark:border-slate-700 hover:shadow-lg hover:-translate-y-1 transition-all duration-300 group">
                        <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 rounded-lg flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                            <?= Icons::get('shopping-cart', 'w-5 h-5') ?>
                        </div>
                        <h3 class="font-bold text-slate-800 dark:text-white text-sm mb-1">Punto de Venta</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Ventas rápidas, tickets PDF y stock auto.</p>
                    </div>

                    <!-- Reportes -->
                    <div class="bg-slate-50 dark:bg-slate-700/50 p-4 rounded-xl border border-slate-100 dark:border-slate-700 hover:shadow-lg hover:-translate-y-1 transition-all duration-300 group">
                        <div class="w-10 h-10 bg-pink-100 dark:bg-pink-900/30 text-pink-600 dark:text-pink-400 rounded-lg flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                            <?= Icons::get('document', 'w-5 h-5') ?>
                        </div>
                        <h3 class="font-bold text-slate-800 dark:text-white text-sm mb-1">Reportes Avanzados</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Exporta data a Excel/PDF.</p>
                    </div>
                    
                    <!-- Alertas -->
                    <div class="bg-slate-50 dark:bg-slate-700/50 p-4 rounded-xl border border-slate-100 dark:border-slate-700 hover:shadow-lg hover:-translate-y-1 transition-all duration-300 group">
                        <div class="w-10 h-10 bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 rounded-lg flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                            <?= Icons::get('bell', 'w-5 h-5') ?>
                        </div>
                        <h3 class="font-bold text-slate-800 dark:text-white text-sm mb-1">Alertas Inteligentes</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Notificaciones de stock bajo.</p>
                    </div>

                    <!-- Equipos -->
                    <div class="bg-slate-50 dark:bg-slate-700/50 p-4 rounded-xl border border-slate-100 dark:border-slate-700 hover:shadow-lg hover:-translate-y-1 transition-all duration-300 group">
                        <div class="w-10 h-10 bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 rounded-lg flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                            <?= Icons::get('users', 'w-5 h-5') ?>
                        </div>
                        <h3 class="font-bold text-slate-800 dark:text-white text-sm mb-1">Gestión de Equipos</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Cuentas y roles para empleados.</p>
                    </div>

                    <!-- Marca Blanca -->
                    <div class="bg-slate-50 dark:bg-slate-700/50 p-4 rounded-xl border border-slate-100 dark:border-slate-700 hover:shadow-lg hover:-translate-y-1 transition-all duration-300 group">
                        <div class="w-10 h-10 bg-teal-100 dark:bg-teal-900/30 text-teal-600 dark:text-teal-400 rounded-lg flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                            <?= Icons::get('settings', 'w-5 h-5') ?>
                        </div>
                        <h3 class="font-bold text-slate-800 dark:text-white text-sm mb-1">Marca Blanca</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Logotipos personalizados.</p>
                    </div>

                    <!-- Soporte -->
                    <div class="bg-slate-50 dark:bg-slate-700/50 p-4 rounded-xl border border-slate-100 dark:border-slate-700 hover:shadow-lg hover:-translate-y-1 transition-all duration-300 group">
                        <div class="w-10 h-10 bg-cyan-100 dark:bg-cyan-900/30 text-cyan-600 dark:text-cyan-400 rounded-lg flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                            <?= Icons::get('chat', 'w-5 h-5') ?>
                        </div>
                        <h3 class="font-bold text-slate-800 dark:text-white text-sm mb-1">Soporte Prioritario</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Canal técnico especializado.</p>
                    </div>
                </div>
                
                <div class="max-w-md mx-auto bg-slate-50 dark:bg-slate-700/30 rounded-2xl p-6 border border-slate-100 dark:border-slate-700/50">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-white mb-4 flex items-center justify-center gap-2">
                        <?= Icons::get('key', 'w-5 h-5 text-emerald-500') ?>
                        Ingresar Licencia
                    </h3>

                    <!-- Flash Messages Local -->
                    <?php if ($msg = \App\Core\Session::getFlash('error')): ?>
                        <div class="bg-red-50 text-red-600 p-3 rounded-lg text-sm font-medium mb-4 flex items-center gap-2 border border-red-100">
                            <?= Icons::get('error', 'w-4 h-4') ?>
                            <?= htmlspecialchars($msg) ?>
                        </div>
                    <?php endif; ?>

                    <form id="form-license-activation" action="index.php?controlador=license&accion=activar" method="POST" class="space-y-4">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <?= Icons::get('lock', 'w-4 h-4 text-slate-400') ?>
                            </div>
                            <input type="text" name="license_key" required placeholder="Pegar clave de licencia aquí..."
                                   class="w-full pl-12 pr-4 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-xl text-slate-800 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 font-mono text-sm shadow-sm transition-all">
                        </div>

                        <button id="btn-license-activation" type="submit" class="w-full bg-emerald-500 hover:bg-emerald-600 text-white font-bold py-2.5 px-4 rounded-xl shadow-lg shadow-emerald-500/20 transition-all transform hover:scale-[1.02] flex items-center justify-center gap-2">
                            <span id="btn-text">Validar y Activar</span>
                            <span id="btn-icon"><?= Icons::get('chevron-right', 'w-4 h-4') ?></span>
                            <!-- Spinner (Hidden by default) -->
                            <svg id="btn-spinner" class="hidden animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </button>
                    </form>

                    <script>
                        document.getElementById('form-license-activation').addEventListener('submit', function(e) {
                            e.preventDefault();
                            const btn = document.getElementById('btn-license-activation');
                            const text = document.getElementById('btn-text');
                            const icon = document.getElementById('btn-icon');
                            const spinner = document.getElementById('btn-spinner');

                            // Loading State
                            btn.disabled = true;
                            btn.classList.add('opacity-75', 'cursor-not-allowed');
                            text.innerText = 'Validando...';
                            icon.classList.add('hidden');
                            spinner.classList.remove('hidden');

                            // Delay 2 seconds
                            setTimeout(() => {
                                this.submit();
                            }, 2000);
                        });
                    </script>
                    
                    <div class="mt-4 text-center">
                        <p class="text-xs text-slate-400">
                            ¿No tienes una clave? 
                            <button onclick="openModal('modal-contacto-premium')" class="text-emerald-500 hover:text-emerald-600 font-medium hover:underline">Adquirir Licencia</button>
                            <span class="mx-1 text-slate-300">|</span>
                            <button onclick="openModal('modal-precios')" class="text-blue-500 hover:text-blue-600 font-medium hover:underline">Ver Precios</button>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Precios -->
<div id="modal-precios" class="hidden fixed inset-0 z-[100]">
    <div class="fixed inset-0 bg-slate-200/50 dark:bg-slate-900/50 backdrop-blur-sm transition-opacity" onclick="closeModal('modal-precios')"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-2xl w-full max-w-4xl relative fade-in transform scale-100 transition-all max-h-[90vh] overflow-y-auto">
            <div class="absolute top-4 right-4 z-10">
                <button onclick="closeModal('modal-precios')" class="p-2 bg-white/50 dark:bg-slate-700/50 hover:bg-white dark:hover:bg-slate-700 rounded-full transition-colors text-slate-500 hover:text-red-500">
                    <?= Icons::get('x', 'w-6 h-6') ?>
                </button>
            </div>
            
            <div class="p-8 sm:p-12">
                <div class="text-center mb-10">
                    <h2 class="text-3xl font-bold text-slate-800 dark:text-white mb-4">Planes Flexibles para tu Negocio</h2>
                    <p class="text-slate-600 dark:text-slate-400">Elige el plan que mejor se adapte a tus necesidades. Sin contratos forzosos.</p>
                </div>

                <div class="grid md:grid-cols-2 gap-8 max-w-4xl mx-auto">
                    <!-- Plan Mensual -->
                    <div class="bg-white dark:bg-slate-700 rounded-2xl p-8 border border-slate-200 dark:border-slate-600 shadow-sm hover:shadow-xl transition-all relative">
                        <h3 class="text-xl font-bold text-slate-800 dark:text-white mb-2">Mensual</h3>
                        <div class="flex items-baseline gap-1 mb-6">
                            <span class="text-4xl font-bold text-slate-900 dark:text-white">$5</span>
                            <span class="text-slate-500">/mes</span>
                        </div>
                        <ul class="space-y-4 mb-8">
                            <li class="flex items-center gap-3 text-sm text-slate-600 dark:text-slate-300">
                                <?= Icons::get('check', 'w-5 h-5 text-emerald-500') ?> Acceso a módulos Premium
                            </li>
                            <li class="flex items-center gap-3 text-sm text-slate-600 dark:text-slate-300">
                                <?= Icons::get('check', 'w-5 h-5 text-emerald-500') ?> Facturación ilimitada
                            </li>
                            <li class="flex items-center gap-3 text-sm text-slate-600 dark:text-slate-300">
                                <?= Icons::get('check', 'w-5 h-5 text-emerald-500') ?> Soporte estándar
                            </li>
                        </ul>
                        <button onclick="closeModal('modal-precios'); openModal('modal-contacto-premium')" class="w-full py-3 px-4 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold rounded-xl transition-colors">
                            Elegir Mensual
                        </button>
                    </div>

                    <!-- Plan Anual (Destacado) -->
                    <div class="bg-slate-900 dark:bg-slate-800 rounded-2xl p-8 border-2 border-emerald-500 shadow-2xl relative transform md:-translate-y-4">
                        <div class="absolute top-0 right-0 bg-emerald-500 text-white text-xs font-bold px-3 py-1 rounded-bl-xl rounded-tr-xl">
                            MÁS POPULAR
                        </div>
                        <h3 class="text-xl font-bold text-white mb-2">Anual</h3>
                        <div class="flex items-baseline gap-1 mb-6">
                            <span class="text-4xl font-bold text-white">$50</span>
                            <span class="text-emerald-400">/año</span>
                        </div>
                        <p class="text-emerald-400 text-sm mb-6 font-medium">Ahorras $10 (2 meses gratis)</p>
                        <ul class="space-y-4 mb-8">
                            <li class="flex items-center gap-3 text-sm text-slate-300">
                                <?= Icons::get('check', 'w-5 h-5 text-emerald-400') ?> Todo lo del plan Mensual
                            </li>
                            <li class="flex items-center gap-3 text-sm text-slate-300">
                                <?= Icons::get('check', 'w-5 h-5 text-emerald-400') ?> Soporte Prioritario
                            </li>
                            <li class="flex items-center gap-3 text-sm text-slate-300">
                                <?= Icons::get('check', 'w-5 h-5 text-emerald-400') ?> Actualizaciones anticipadas
                            </li>
                        </ul>
                        <button onclick="closeModal('modal-precios'); openModal('modal-contacto-premium')" class="w-full py-3 px-4 bg-emerald-500 hover:bg-emerald-600 text-white font-bold rounded-xl shadow-lg shadow-emerald-500/30 transition-colors">
                            Elegir Anual
                        </button>
                    </div>
                </div>
                
                <div class="mt-12 text-center">
                     <p class="text-slate-500 text-sm">¿Dudas? Contáctanos directamente por WhatsApp o Telegram.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Contacto (Traído de Premium) -->
<div id="modal-contacto-premium" class="hidden fixed inset-0 z-[100]">
    <div class="fixed inset-0 bg-slate-200/50 dark:bg-slate-900/50 backdrop-blur-sm transition-opacity" onclick="closeModal('modal-contacto-premium')"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-md relative fade-in transform scale-100 transition-all">
            <!-- Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 dark:border-slate-700">
                <h3 class="text-lg font-semibold text-slate-800 dark:text-white flex items-center gap-2">
                    <?= Icons::get('user', 'w-5 h-5 text-emerald-500') ?>
                    Contactar Ventas
                </h3>
                <button onclick="closeModal('modal-contacto-premium')" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors">
                    <?= Icons::get('x', 'w-5 h-5') ?>
                </button>
            </div>
            
            <!-- Body -->
            <div class="p-6 space-y-6">
                <div>
                    <label class="block text-sm font-medium text-slate-600 dark:text-slate-400 mb-2">Tu ID de Cuenta (Email)</label>
                    <div class="flex items-center gap-3 px-4 py-3 bg-slate-50 dark:bg-slate-700/50 rounded-xl border border-slate-200 dark:border-slate-700">
                        <?= Icons::get('mail', 'w-5 h-5 text-slate-400') ?>
                        <span class="font-mono font-medium text-slate-800 dark:text-white truncate">
                            <?= htmlspecialchars($email ?? 'usuario@ejemplo.com') ?>
                        </span>
                    </div>
                    <p class="text-xs text-slate-400 mt-2">Envíanos este email para identificar tu pago rápidamente.</p>
                </div>

                <a href="https://t.me/+584269400924" target="_blank" 
                   class="flex items-center justify-center gap-3 w-full px-6 py-4 bg-[#0088cc] hover:bg-[#007ebd] text-white rounded-xl font-bold shadow-lg shadow-blue-500/30 transition-all transform hover:-translate-y-0.5">
                    <!-- Telegram SVG -->
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
                    <span>Contactar por Telegram</span>
                </a>

                <a href="https://wa.me/584245416646" target="_blank" 
                   class="flex items-center justify-center gap-3 w-full px-6 py-4 bg-[#25D366] hover:bg-[#20bd5a] text-white rounded-xl font-bold shadow-lg shadow-green-500/30 transition-all transform hover:-translate-y-0.5">
                    <!-- WhatsApp SVG -->
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg>
                    <span>Contactar por WhatsApp</span>
                </a>
            </div>
            
            <div class="px-6 pb-6 bg-slate-50 dark:bg-slate-700/30 rounded-b-2xl pt-4 border-t border-slate-100 dark:border-slate-700">
                 <p class="text-xs text-center text-slate-400">
                    Tu cuenta se activará en pocos minutos tras confirmar el pago.
                 </p>
            </div>
        </div>
    </div>
</div>