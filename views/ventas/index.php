<?php
/**
 * VENTAS - Landing Page (Menu)
 * views/ventas/index.php
 */
use App\Helpers\Icons;
?>

<!-- Header -->
<div class="mb-8">
    <h1 class="text-2xl font-bold text-slate-800 dark:text-white flex items-center gap-3">
        <?= Icons::get('cart', 'w-8 h-8 text-indigo-500') ?>
        Módulo de Ventas
    </h1>
    <p class="text-sm text-slate-500 dark:text-slate-400 mt-2">
        Selecciona una operación para continuar
    </p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-4xl mx-auto mt-12">
    
    <!-- Opción 1: Punto de Venta (POS) -->
    <a href="index.php?controlador=venta&accion=pos" class="group relative overflow-hidden bg-white dark:bg-slate-700/50 rounded-2xl p-8 border border-slate-200 dark:border-slate-600 shadow-xl shadow-indigo-500/10 hover:shadow-2xl hover:shadow-indigo-500/20 hover:-translate-y-1 transition-all duration-300">
        <div class="absolute top-0 right-0 -mt-4 -mr-4 w-32 h-32 bg-indigo-500/10 rounded-full group-hover:scale-150 transition-transform duration-500 ease-out"></div>
        
        <div class="relative z-10 flex flex-col items-center text-center">
            <div class="w-20 h-20 rounded-2xl bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center mb-6 group-hover:bg-indigo-100 dark:group-hover:bg-indigo-800/50 transition-colors">
                <?= Icons::get('cart-plus', 'w-10 h-10 text-indigo-600 dark:text-indigo-400') ?>
            </div>
            
            <h3 class="text-xl font-bold text-slate-800 dark:text-white mb-2">Nueva Venta (POS)</h3>
            <p class="text-slate-500 dark:text-slate-400 text-sm mb-6">
                Interfaz optimizada para facturación rápida, control de caja y emisión de tickets.
            </p>
            
            <span class="inline-flex items-center gap-2 text-indigo-600 dark:text-indigo-400 font-bold text-sm bg-indigo-50 dark:bg-indigo-900/30 px-4 py-2 rounded-lg group-hover:bg-indigo-600 group-hover:text-white transition-all">
                Ir al POS <?= Icons::get('arrow-right', 'w-4 h-4') ?>
            </span>
        </div>
    </a>

    <!-- Opción 2: Historial de Ventas -->
    <a href="index.php?controlador=venta&accion=historial" class="group relative overflow-hidden bg-white dark:bg-slate-700/50 rounded-2xl p-8 border border-slate-200 dark:border-slate-600 shadow-xl shadow-emerald-500/10 hover:shadow-2xl hover:shadow-emerald-500/20 hover:-translate-y-1 transition-all duration-300">
        <div class="absolute top-0 right-0 -mt-4 -mr-4 w-32 h-32 bg-emerald-500/10 rounded-full group-hover:scale-150 transition-transform duration-500 ease-out"></div>
        
        <div class="relative z-10 flex flex-col items-center text-center">
            <div class="w-20 h-20 rounded-2xl bg-emerald-50 dark:bg-emerald-900/30 flex items-center justify-center mb-6 group-hover:bg-emerald-100 dark:group-hover:bg-emerald-800/50 transition-colors">
                <?= Icons::get('history', 'w-10 h-10 text-emerald-600 dark:text-emerald-400') ?>
            </div>
            
            <h3 class="text-xl font-bold text-slate-800 dark:text-white mb-2">Historial de Ventas</h3>
            <p class="text-slate-500 dark:text-slate-400 text-sm mb-6">
                Consulta transacciones pasadas, reimprime recibos y gestiona cuentas por cobrar.
            </p>
            
            <span class="inline-flex items-center gap-2 text-emerald-600 dark:text-emerald-400 font-bold text-sm bg-emerald-50 dark:bg-emerald-900/30 px-4 py-2 rounded-lg group-hover:bg-emerald-600 group-hover:text-white transition-all">
                Ver Historial <?= Icons::get('arrow-right', 'w-4 h-4') ?>
            </span>
        </div>
    </a>
</div>