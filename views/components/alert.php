<?php
/**
 * Componente Alert
 * @var string $type success|error|warning|info
 * @var string $message
 * @var bool $dismissible
 */
$type = $type ?? 'info';
$bgClass = [
    'success' => 'bg-emerald-50 border-emerald-200 text-emerald-800 dark:bg-emerald-900/20 dark:border-emerald-800 dark:text-emerald-400',
    'error'   => 'bg-red-50 border-red-200 text-red-800 dark:bg-red-900/20 dark:border-red-800 dark:text-red-400',
    'warning' => 'bg-amber-50 border-amber-200 text-amber-800 dark:bg-amber-900/20 dark:border-amber-800 dark:text-amber-400',
    'info'    => 'bg-blue-50 border-blue-200 text-blue-800 dark:bg-blue-900/20 dark:border-blue-800 dark:text-blue-400',
][$type];

$icon = [
    'success' => 'check-circle',
    'error'   => 'x-circle',
    'warning' => 'exclamation-triangle',
    'info'    => 'information-circle',
][$type];
?>

<div class="flex items-center p-4 mb-4 border rounded-2xl <?= $bgClass ?>" role="alert">
    <div class="flex-shrink-0">
        <?= \App\Helpers\Icons::get($icon, 'w-5 h-5') ?>
    </div>
    <div class="ml-3 text-sm font-medium">
        <?= $message ?>
    </div>
    <?php if ($dismissible ?? false): ?>
    <button type="button" class="ml-auto -mx-1.5 -my-1.5 p-1.5 inline-flex h-8 w-8 rounded-lg focus:ring-2 focus:ring-offset-1 opacity-50 hover:opacity-100 transition-opacity" 
            onclick="this.parentElement.remove()" aria-label="Close">
        <?= \App\Helpers\Icons::get('x', 'w-4 h-4') ?>
    </button>
    <?php endif; ?>
</div>
