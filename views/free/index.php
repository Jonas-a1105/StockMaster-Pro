<?php
use App\Helpers\Icons;
?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="max-w-3xl mx-auto">
        <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
            <!-- Header Banner -->
            <div class="bg-gradient-to-r from-slate-800 to-slate-900 px-8 py-12 text-center relative overflow-hidden">
                <div class="absolute inset-0 opacity-10 pattern-grid-lg"></div>
                <div class="relative z-10 flex flex-col items-center">
                    <div class="w-20 h-20 bg-white/10 backdrop-blur-sm rounded-2xl flex items-center justify-center mb-6 shadow-inner ring-1 ring-white/20">
                        <?= Icons::get('lock', 'w-10 h-10 text-slate-300') ?>
                    </div>
                    
                    <h1 class="text-3xl sm:text-4xl font-bold text-white mb-2">
                        ¡Bienvenido, <?= htmlspecialchars($email ?? 'Usuario') ?>!
                    </h1>
                    <p class="text-slate-300 text-lg">
                        Tu cuenta actual es <span class="text-emerald-400 font-semibold">Gratuita</span>
                    </p>
                </div>
            </div>

            <!-- Content -->
            <div class="p-8 sm:p-12 text-center">
                <div class="mb-8">
                    <div class="inline-flex items-center gap-2 px-4 py-2 bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-400 rounded-full text-sm font-medium border border-amber-200 dark:border-amber-900/50 mb-6">
                        <?= Icons::get('alert-circle', 'w-4 h-4') ?>
                        <span>Acceso Limitado</span>
                    </div>

                    <h2 class="text-2xl font-bold text-slate-800 dark:text-white mb-4">
                        Desbloquea todo el potencial
                    </h2>
                    <p class="text-slate-600 dark:text-slate-400 max-w-xl mx-auto leading-relaxed">
                        Tu período de prueba ha finalizado o aún no ha sido activado. 
                        Para acceder al <strong class="text-slate-800 dark:text-slate-200">Inventario profesional, POS, Ventas y Reportes avanzados</strong>, necesitas activar un plan Premium.
                    </p>
                </div>

                <!-- Features Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 max-w-2xl mx-auto mb-10 text-left">
                    <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-700/50 border border-slate-100 dark:border-slate-700 transition-all duration-300 hover:border-blue-500 hover:shadow-lg hover:shadow-blue-500/10 group">
                        <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center mb-3 group-hover:scale-110 transition-transform duration-300">
                            <?= Icons::get('inventory', 'w-5 h-5 text-blue-600 dark:text-blue-400') ?>
                        </div>
                        <h3 class="font-semibold text-slate-800 dark:text-white mb-1">Inventario Ilimitado</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Gestiona miles de productos y variantes.</p>
                    </div>
                    
                    <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-700/50 border border-slate-100 dark:border-slate-700 transition-all duration-300 hover:border-emerald-500 hover:shadow-lg hover:shadow-emerald-500/10 group">
                        <div class="w-10 h-10 bg-emerald-100 dark:bg-emerald-900/30 rounded-xl flex items-center justify-center mb-3 group-hover:scale-110 transition-transform duration-300">
                            <?= Icons::get('chart', 'w-5 h-5 text-emerald-600 dark:text-emerald-400') ?>
                        </div>
                        <h3 class="font-semibold text-slate-800 dark:text-white mb-1">Reportes Avanzados</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Analíticas de ventas y ganancias reales.</p>
                    </div>
                    
                    <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-700/50 border border-slate-100 dark:border-slate-700 transition-all duration-300 hover:border-purple-500 hover:shadow-lg hover:shadow-purple-500/10 group">
                        <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900/30 rounded-xl flex items-center justify-center mb-3 group-hover:scale-110 transition-transform duration-300">
                            <?= Icons::get('shopping-cart', 'w-5 h-5 text-purple-600 dark:text-purple-400') ?>
                        </div>
                        <h3 class="font-semibold text-slate-800 dark:text-white mb-1">Punto de Venta</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Vende rápido y factura al instante.</p>
                    </div>
                </div>
                
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a href="index.php?controlador=premium&accion=index" 
                       class="w-full sm:w-auto inline-flex items-center justify-center gap-3 px-8 py-4 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl font-bold shadow-lg shadow-emerald-500/30 hover:shadow-emerald-500/40 transform hover:-translate-y-0.5 transition-all text-lg">
                        <?= Icons::get('rocket', 'w-6 h-6') ?>
                        <span>Activar Premium Ahora</span>
                    </a>
                </div>
                
                <p class="mt-6 text-sm text-slate-400">
                    ¿Tienes dudas? <a href="#" class="text-emerald-500 hover:text-emerald-600 font-medium">Contáctanos</a>
                </p>
            </div>
        </div>
    </div>
</div>