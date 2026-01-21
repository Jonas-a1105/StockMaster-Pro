<?php
/**
 * Componente Loader / Pantalla de Carga
 * Reutilizable entre login, registro y procesos largos
 * 
 * @param string $titulo - Título principal
 * @param string $subtitulo - Texto descriptivo
 */
$titulo = $titulo ?? 'StockMaster Pro';
$subtitulo = $subtitulo ?? 'Preparando el entorno...';
?>
<div id="loading-screen" class="fixed inset-0 z-50 bg-[#eef2f6] flex flex-col items-center justify-center p-4 hidden">
    <div class="bg-white rounded-[32px] p-12 shadow-2xl shadow-slate-200/50 border border-slate-200 flex flex-col items-center text-center max-w-sm w-full animate-in fade-in zoom-in-95 duration-500">
        <!-- Logo Animado -->
        <div class="relative mb-8 group">
            <div class="absolute inset-0 bg-emerald-100 rounded-full animate-ping opacity-20 duration-1000"></div>
            <div class="relative w-28 h-28 bg-white rounded-full border-4 border-slate-50 shadow-lg flex items-center justify-center overflow-hidden animate-bounce">
                <div class="w-full h-full bg-gradient-to-tr from-emerald-50 to-emerald-100 flex items-center justify-center text-emerald-600">
                    <?= \App\Helpers\Icons::get('logo', 'w-full h-full p-4') ?>
                </div>
            </div>
            <div class="absolute bottom-1 right-2 w-7 h-7 bg-emerald-500 border-4 border-white rounded-full shadow-sm"></div>
        </div>

        <h2 class="text-2xl font-bold text-slate-800 mb-2"><?= htmlspecialchars($titulo) ?></h2>
        <p class="text-sm text-slate-500 font-medium mb-10 px-4 leading-relaxed">
            <?= htmlspecialchars($subtitulo) ?>
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
