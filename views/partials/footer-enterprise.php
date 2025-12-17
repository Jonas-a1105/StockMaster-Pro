<?php
/**
 * FOOTER ENTERPRISE - Footer sticky con totales
 */
use App\Helpers\Icons;


// Usar la constante global APP_VERSION definida en index.php
$version = defined('APP_VERSION') ? APP_VERSION : '1.0.0';
$isPremium = isset($_SESSION['user_plan']) && $_SESSION['user_plan'] === 'premium';
?>

<footer class="mt-auto border-t border-slate-200 bg-slate-50/50">
    <div class="px-8 py-4">
        <div class="flex flex-col md:flex-row items-center <?= $isPremium ? 'justify-between' : 'justify-center' ?> gap-4">
            
            <!-- Info Financiera (Solo Premium) -->
            <?php if ($isPremium): ?>
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
            <?php endif; ?>
            
            <!-- Copyright -->
            <div class="flex items-center gap-4 text-xs text-slate-400">
                <span>© <?= date('Y') ?> StockMaster Pro <span class="text-slate-300 mx-1">|</span> v<?= $version ?></span>
                <span class="hidden sm:inline">•</span>
                <button onclick="openModal('modal-ayuda')" class="hidden sm:inline hover:text-slate-600 transition-colors">Ayuda</button>
                <span class="hidden sm:inline">•</span>
                <button onclick="openModal('modal-terminos')" class="hidden sm:inline hover:text-slate-600 transition-colors">Términos</button>
            </div>
        </div>
    </div>
</footer>

<!-- Modal Ayuda -->
<div id="modal-ayuda" class="hidden fixed inset-0 z-[100]">
    <div class="fixed inset-0 bg-slate-200/50 dark:bg-slate-900/50 backdrop-blur-sm transition-opacity" onclick="closeModal('modal-ayuda')"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-md relative fade-in transform scale-100 transition-all">
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 dark:border-slate-700">
                <h3 class="text-lg font-semibold text-slate-800 dark:text-white flex items-center gap-2">
                    <?= Icons::get('chat', 'w-5 h-5 text-emerald-500') ?>
                    Centro de Ayuda
                </h3>
                <button onclick="closeModal('modal-ayuda')" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors">
                    <?= Icons::get('x', 'w-5 h-5') ?>
                </button>
            </div>
            <div class="p-6 space-y-4">
                <p class="text-sm text-slate-600 dark:text-slate-400">
                    Estamos aquí para ayudarte. Si tienes problemas con tu licencia o el sistema, contáctanos por nuestros canales oficiales.
                </p>
                
                <div class="space-y-3">
                    <a href="https://t.me/+584269400924" target="_blank" 
                       class="flex items-center gap-3 px-4 py-3 bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400 rounded-xl hover:bg-blue-100 dark:hover:bg-blue-900/30 transition-colors">
                        <!-- Telegram Icon -->
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
                        <span class="font-medium">Soporte via Telegram</span>
                    </a>
                    
                    <a href="https://wa.me/584245416646" target="_blank" 
                       class="flex items-center gap-3 px-4 py-3 bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400 rounded-xl hover:bg-green-100 dark:hover:bg-green-900/30 transition-colors">
                        <!-- WhatsApp Icon -->
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg>
                        <span class="font-medium">Soporte via WhatsApp</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Términos -->
<div id="modal-terminos" class="hidden fixed inset-0 z-[100]">
    <div class="fixed inset-0 bg-slate-200/50 dark:bg-slate-900/50 backdrop-blur-sm transition-opacity" onclick="closeModal('modal-terminos')"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-lg relative fade-in transform scale-100 transition-all max-h-[80vh] flex flex-col">
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 dark:border-slate-700 shrink-0">
                <h3 class="text-lg font-semibold text-slate-800 dark:text-white flex items-center gap-2">
                    <?= Icons::get('document', 'w-5 h-5 text-emerald-500') ?>
                    Términos y Condiciones
                </h3>
                <button onclick="closeModal('modal-terminos')" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors">
                    <?= Icons::get('x', 'w-5 h-5') ?>
                </button>
            </div>
            <div class="p-6 space-y-4 overflow-y-auto">
                <div class="prose prose-sm dark:prose-invert">
                    <h4>1. Aceptación</h4>
                    <p>Al usar este software, aceptas estos términos.</p>
                    
                    <h4>2. Licencia</h4>
                    <p>La licencia es personal e intransferible. El uso indebido resultará en la suspensión.</p>
                    
                    <h4>3. Soporte</h4>
                    <p>El soporte se proporciona según el plan contratado. Los usuarios gratuitos tienen soporte limitado.</p>
                    
                    <h4>4. Garantía</h4>
                    <p>El software se proporciona "tal cual" sin garantías expresas o implícitas.</p>
                </div>
            </div>
            <div class="p-6 border-t border-slate-200 dark:border-slate-700 shrink-0 flex justify-end">
                <button onclick="closeModal('modal-terminos')" class="px-4 py-2 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl hover:bg-slate-200 dark:hover:bg-slate-600 font-medium transition-colors">
                    Entendido
                </button>
            </div>
        </div>
    </div>
</div>
