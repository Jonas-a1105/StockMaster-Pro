<?php
/**
 * Componente Modal
 * @var string $id
 * @var string $title
 * @var string $content
 * @var string $footer
 * @var string $size sm|md|lg|xl|2xl
 * @var bool $closeOnClickBackdrop
 */
$sizeClass = [
    'sm' => 'max-w-sm',
    'md' => 'max-w-md',
    'lg' => 'max-w-lg',
    'xl' => 'max-w-xl',
    '2xl' => 'max-w-2xl',
][$size ?? 'md'];
?>
<div id="<?= $id ?>" class="hidden fixed inset-0 z-[100] modal-wrapper">
    <!-- Backdrop (visual only, no click handler) -->
    <div id="<?= $id ?>-backdrop" class="fixed inset-0 bg-slate-200/50 dark:bg-slate-900/50 backdrop-blur-sm transition-opacity opacity-0"></div>
    
    <!-- Content Wrapper (handles backdrop clicks) -->
    <div class="fixed inset-0 flex items-center justify-center p-4 overflow-y-auto"
         <?= ($closeOnClickBackdrop ?? true) ? "onclick=\"closeModal('$id')\"" : "" ?>>
        <!-- Panel (stops propagation to prevent close when clicking inside) -->
        <div id="<?= $id ?>-panel" onclick="event.stopPropagation()" class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full <?= $sizeClass ?> my-8 relative fade-in opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95 transition-all duration-300">
            <!-- Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 dark:border-slate-700">
                <h3 class="text-lg font-semibold text-slate-800 dark:text-white"><?= $title ?? '' ?></h3>
                <button onclick="closeModal('<?= $id ?>')" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors">
                    <?= \App\Helpers\Icons::get('x', 'w-5 h-5') ?>
                </button>
            </div>
            
            <!-- Body -->
            <div class="p-6">
                <?= $content ?? '' ?>
            </div>
            
            <?php if (isset($footer)): ?>
            <!-- Footer -->
            <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-700/30">
                <?= $footer ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
