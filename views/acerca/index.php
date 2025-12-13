<div class="max-w-3xl mx-auto">
    <!-- Hero Card -->
    <div class="bg-white rounded-[32px] p-12 shadow-xl shadow-slate-200/60 border border-slate-200 text-center relative overflow-hidden mb-8">
        <!-- Background Decor -->
        <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-emerald-400 via-teal-500 to-emerald-600"></div>
        <div class="absolute -top-20 -left-20 w-64 h-64 bg-emerald-50 rounded-full blur-3xl opacity-50"></div>
        <div class="absolute -bottom-20 -right-20 w-64 h-64 bg-teal-50 rounded-full blur-3xl opacity-50"></div>

        <!-- Logo -->
        <div class="relative w-24 h-24 mx-auto mb-6 bg-white rounded-2xl border border-emerald-100 shadow-lg flex items-center justify-center p-2">
             <?= \App\Helpers\Icons::get('inventory', 'w-12 h-12 text-emerald-600') ?>
        </div>

        <h1 class="text-4xl font-bold text-slate-800 mb-2">StockMaster <span class="text-emerald-500">Pro</span></h1>
        <p class="text-lg text-slate-500 font-medium mb-8">Gestión Empresarial Inteligente</p>

        <div class="inline-flex items-center gap-3 px-4 py-2 bg-slate-50 border border-slate-200 rounded-full mb-8">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            <span class="text-sm font-semibold text-slate-600">Versión 1.0.0 (Enterprise)</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-2xl mx-auto border-t border-slate-100 pt-8">
            <div class="text-center">
                <p class="text-sm text-slate-400 uppercase tracking-widest font-bold mb-1">Desarrollado por</p>
                <p class="text-base font-bold text-slate-700">Jonas Mendoza</p>
            </div>
            <div class="text-center">
                <p class="text-sm text-slate-400 uppercase tracking-widest font-bold mb-1">Tecnología</p>
                <p class="text-base font-bold text-slate-700">PHP 8 + Electron</p>
            </div>
            <div class="text-center">
                <p class="text-sm text-slate-400 uppercase tracking-widest font-bold mb-1">Licencia</p>
                <p class="text-base font-bold text-slate-700">Propietaria</p>
            </div>
        </div>
    </div>

    <!-- Tech Stack / Info -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600">
                    <?= \App\Helpers\Icons::get('code', 'w-5 h-5') ?>
                </div>
                <div>
                    <h3 class="font-bold text-slate-800">Arquitectura</h3>
                    <p class="text-xs text-slate-500">MVC + Singleton Pattern</p>
                </div>
            </div>
            <p class="text-sm text-slate-600 leading-relaxed">
                Este sistema ha sido construido siguiendo los estándares más altos de desarrollo, utilizando una arquitectura modular que garantiza escalabilidad, seguridad y alto rendimiento.
            </p>
        </div>

        <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm">
             <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-purple-50 flex items-center justify-center text-purple-600">
                    <?= \App\Helpers\Icons::get('shield', 'w-5 h-5') ?>
                </div>
                <div>
                    <h3 class="font-bold text-slate-800">Seguridad</h3>
                    <p class="text-xs text-slate-500">Cifrado de Grado Militar</p>
                </div>
            </div>
            <p class="text-sm text-slate-600 leading-relaxed">
                Sus datos están protegidos localmente mediante SQLite cifrado y el sistema opera en un entorno aislado (Sandbox) para prevenir fugas de información.
            </p>
        </div>
    </div>
    
    <div class="mt-8 text-center">
        <p class="text-xs text-slate-400">© 2025 StockMaster Solutions. Todos los derechos reservados.</p>
    </div>
</div>
