<?php
/**
 * FOOTER ENTERPRISE - Footer sticky con totales
 */
use App\Helpers\Icons;
?>

<footer class="mt-auto border-t border-slate-200 bg-slate-50/50">
    <div class="px-8 py-4">
        <div class="flex flex-col md:flex-row items-center justify-between gap-4">
            <!-- Info Financiera -->
            <div class="flex items-center gap-6">
                <div class="flex items-center gap-2">
                    <?= Icons::get('dollar', 'w-4 h-4 text-emerald-500') ?>
                    <span class="text-xs text-slate-500">Tasa:</span>
                    <span id="footer-tasa" class="text-sm font-semibold text-emerald-600">Bs. --</span>
                </div>
                <div class="hidden md:flex items-center gap-2">
                    <?= Icons::get('inventory', 'w-4 h-4 text-slate-400') ?>
                    <span class="text-xs text-slate-500">Inventario valorizado:</span>
                    <span id="footer-inventario" class="text-sm font-semibold text-slate-700">$--</span>
                </div>
            </div>
            
            <!-- Copyright -->
            <div class="flex items-center gap-4 text-xs text-slate-400">
                <span>© <?= date('Y') ?> StockMaster Pro</span>
                <span class="hidden sm:inline">•</span>
                <a href="#" class="hidden sm:inline hover:text-slate-600 transition-colors">Ayuda</a>
                <span class="hidden sm:inline">•</span>
                <a href="#" class="hidden sm:inline hover:text-slate-600 transition-colors">Términos</a>
            </div>
        </div>
    </div>
</footer>
