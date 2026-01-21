<?php
/**
 * Componente Card
 * @var string $title
 * @var string $subtitle
 * @var string $content
 * @var string $footer
 * @var string $class
 */
?>
<div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden <?= $class ?? '' ?>">
    <?php if (isset($title) || isset($subtitle)): ?>
    <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700">
        <?php if (isset($title)): ?>
        <h3 class="text-lg font-bold text-slate-800 dark:text-white"><?= $title ?></h3>
        <?php endif; ?>
        <?php if (isset($subtitle)): ?>
        <p class="text-sm text-slate-500 dark:text-slate-400"><?= $subtitle ?></p>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="p-6">
        <?= $content ?? $slot ?? '' ?>
    </div>

    <?php if (isset($footer)): ?>
    <div class="px-6 py-4 bg-slate-50/50 dark:bg-slate-700/30 border-t border-slate-100 dark:border-slate-700">
        <?= $footer ?>
    </div>
    <?php endif; ?>
</div>
