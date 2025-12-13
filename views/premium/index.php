<?php
use App\Helpers\Icons;
?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <!-- Header -->
    <div class="text-center max-w-3xl mx-auto mb-16">
        <div class="inline-flex items-center justify-center p-3 bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 rounded-2xl mb-6 ring-8 ring-amber-50 dark:ring-amber-900/10">
            <?= Icons::get('crown', 'w-10 h-10') ?>
        </div>
        <h1 class="text-4xl sm:text-5xl font-bold text-slate-900 dark:text-white mb-6">
            Activa tu Plan Premium
        </h1>
        <p class="text-lg text-slate-600 dark:text-slate-400 leading-relaxed">
            Lleva tu negocio al siguiente nivel desbloqueando todas las herramientas profesionales de administración, ventas y reportes.
        </p>
    </div>

    <!-- Features Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-16">
        <!-- Dashboard -->
        <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm transition-all group hover:shadow-md hover:border-blue-500 hover:shadow-blue-500/10">
            <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                <?= Icons::get('chart', 'w-6 h-6') ?>
            </div>
            <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">Dashboard Financiero</h3>
            <p class="text-sm text-slate-500 dark:text-slate-400">Visualiza ganancias, costos de operación y valor total de inventario en tiempo real.</p>
        </div>

        <!-- Inventario -->
        <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm transition-all group hover:shadow-md hover:border-emerald-500 hover:shadow-emerald-500/10">
            <div class="w-12 h-12 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                <?= Icons::get('inventory', 'w-6 h-6') ?>
            </div>
            <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">Gestión Total</h3>
            <p class="text-sm text-slate-500 dark:text-slate-400">Control absoluto de stock, precios múltiples, códigos de barra y cálculo de márgenes.</p>
        </div>

        <!-- POS -->
        <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm transition-all group hover:shadow-md hover:border-purple-500 hover:shadow-purple-500/10">
            <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                <?= Icons::get('shopping-cart', 'w-6 h-6') ?>
            </div>
            <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">Punto de Venta</h3>
            <p class="text-sm text-slate-500 dark:text-slate-400">Vende rápido, genera tickets PDF al instante y descuenta stock automáticamente.</p>
        </div>

        <!-- Reportes -->
        <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm transition-all group hover:shadow-md hover:border-pink-500 hover:shadow-pink-500/10">
            <div class="w-12 h-12 bg-pink-100 dark:bg-pink-900/30 text-pink-600 dark:text-pink-400 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                <?= Icons::get('document', 'w-6 h-6') ?>
            </div>
            <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">Reportes Avanzados</h3>
            <p class="text-sm text-slate-500 dark:text-slate-400">Exporta toda tu data a Excel/PDF para auditorías y toma de decisiones.</p>
        </div>

        <!-- Alertas -->
        <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm transition-all group hover:shadow-md hover:border-amber-500 hover:shadow-amber-500/10">
            <div class="w-12 h-12 bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                <?= Icons::get('bell', 'w-6 h-6') ?>
            </div>
            <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">Alertas Inteligentes</h3>
            <p class="text-sm text-slate-500 dark:text-slate-400">Recibe notificaciones automáticas cuando el stock esté bajo o agotado.</p>
        </div>

        <!-- Equipos -->
        <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm transition-all group hover:shadow-md hover:border-indigo-500 hover:shadow-indigo-500/10">
            <div class="w-12 h-12 bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                <?= Icons::get('users', 'w-6 h-6') ?>
            </div>
            <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">Gestión de Equipos</h3>
            <p class="text-sm text-slate-500 dark:text-slate-400">Crea cuentas para empleados, asigna roles y controla permisos de acceso.</p>
        </div>

        <!-- Personalización -->
        <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm transition-all group hover:shadow-md hover:border-teal-500 hover:shadow-teal-500/10">
            <div class="w-12 h-12 bg-teal-100 dark:bg-teal-900/30 text-teal-600 dark:text-teal-400 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                <?= Icons::get('settings', 'w-6 h-6') ?>
            </div>
            <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">Marca Blanca</h3>
            <p class="text-sm text-slate-500 dark:text-slate-400">Personaliza recibos y reportes con tu logotipo e información fiscal.</p>
        </div>

        <!-- Soporte -->
        <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm transition-all group hover:shadow-md hover:border-cyan-500 hover:shadow-cyan-500/10">
            <div class="w-12 h-12 bg-cyan-100 dark:bg-cyan-900/30 text-cyan-600 dark:text-cyan-400 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                <?= Icons::get('chat', 'w-6 h-6') ?>
            </div>
            <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">Soporte Prioritario</h3>
            <p class="text-sm text-slate-500 dark:text-slate-400">Acceso directo a canal de soporte técnico especializado.</p>
        </div>
    </div>

    <!-- CTA Section -->
    <div class="relative rounded-3xl overflow-hidden bg-slate-900 px-6 py-16 text-center">
        <!-- Background Pattern -->
        <div class="absolute inset-0 opacity-20 bg-[radial-gradient(#ffffff33_1px,transparent_1px)] [background-size:16px_16px]"></div>
        
        <div class="relative z-10 max-w-2xl mx-auto">
            <h2 class="text-3xl font-bold text-white mb-4">¿Listo para escalar tu negocio?</h2>
            <p class="text-slate-300 mb-8 text-lg">
                Activa tu cuenta hoy mismo. Contacta con nuestro equipo de ventas para procesar tu pago y activación inmediata.
            </p>
            
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <button onclick="openModal('modal-contacto-premium')" 
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-3 px-8 py-4 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl font-bold shadow-lg shadow-emerald-500/30 hover:shadow-emerald-500/40 transform hover:-translate-y-0.5 transition-all text-lg">
                    <?= Icons::get('phone', 'w-6 h-6') ?>
                    <span>Contactar Ventas</span>
                </button>
            </div>
            
            <div class="mt-8 pt-8 border-t border-slate-800 flex flex-wrap justify-center gap-6 text-sm text-slate-400">
                <span class="flex items-center gap-2">
                    <div class="w-2 h-2 bg-emerald-500 rounded-full"></div> Pago Móvil
                </span>
                <span class="flex items-center gap-2">
                    <div class="w-2 h-2 bg-blue-500 rounded-full"></div> Transferencias
                </span>
                <span class="flex items-center gap-2">
                    <div class="w-2 h-2 bg-yellow-500 rounded-full"></div> Binance / USDT
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Modal Contacto -->
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

                <a href="https://t.me/SaaSProVentas" target="_blank" 
                   class="flex items-center justify-center gap-3 w-full px-6 py-4 bg-[#0088cc] hover:bg-[#007ebd] text-white rounded-xl font-bold shadow-lg shadow-blue-500/30 transition-all transform hover:-translate-y-0.5">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
                    <span>Contactar por Telegram</span>
                </a>

                <a href="https://wa.me/1234567890" target="_blank" 
                   class="flex items-center justify-center gap-3 w-full px-6 py-4 bg-[#25D366] hover:bg-[#20bd5a] text-white rounded-xl font-bold shadow-lg shadow-green-500/30 transition-all transform hover:-translate-y-0.5">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg>
                    <span>Contactar por WhatsApp</span>
                </a>
            </div>
            
            <div class="px-6 pb-6 bg-slate-50 dark:bg-slate-700/30 rounded-b-2xl pt-4 border-t border-slate-100 dark:border-slate-700">
                 <p class="text-xs text-center text-slate-400">
                    Al confirmar tu pago, tu cuenta se activará en menos de 15 minutos.
                 </p>
            </div>
        </div>
    </div>
</div>