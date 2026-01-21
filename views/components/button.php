<?php
/**
 * Componente Button
 * @var string $type button|submit
 * @var string $variant primary|secondary|danger|outline|ghost
 * @var string $size sm|md|lg
 * @var string $icon
 * @var string $label
 * @var string $class
 * @var string $id
 * @var string $attributes
 */
$type = $type ?? 'button';
$variant = $variant ?? 'primary';
$size = $size ?? 'md';

$variantClasses = [
    'primary'   => 'bg-emerald-500 text-white hover:bg-emerald-600 shadow-emerald-200 dark:shadow-none',
    'secondary' => 'bg-slate-800 text-white hover:bg-slate-900',
    'danger'    => 'bg-red-500 text-white hover:bg-red-600',
    'outline'   => 'bg-transparent border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700',
    'ghost'     => 'bg-transparent text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700',
][$variant];

$sizeClasses = [
    'sm' => 'px-3 py-1.5 text-xs',
    'md' => 'px-4 py-2.5 text-sm',
    'lg' => 'px-6 py-3 text-base',
][$size];
?>

<button type="<?= $type ?>" 
        id="<?= $id ?? '' ?>"
        class="inline-flex items-center justify-center font-semibold rounded-xl transition-all active:scale-95 disabled:opacity-50 disabled:pointer-events-none <?= $variantClasses ?> <?= $sizeClasses ?> <?= $class ?? '' ?>"
        <?= $attributes ?? '' ?>>
    <?php if (isset($icon)): ?>
        <span class="<?= isset($label) ? 'mr-2' : '' ?>">
            <?= \App\Helpers\Icons::get($icon, ($size === 'sm' ? 'w-4 h-4' : 'w-5 h-5')) ?>
        </span>
    <?php endif; ?>
    <?= $label ?? '' ?>
</button>
