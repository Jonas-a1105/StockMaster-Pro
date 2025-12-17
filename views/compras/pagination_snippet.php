
<!-- Paginación y Selector -->
<div class="flex flex-col sm:flex-row items-center justify-between gap-4 mt-6 px-2">
    
    <!-- Selector de Límite -->
    <div class="flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400">
        <span>Mostrar</span>
        <select id="limit-selector"
                data-setup-simple-select
                onchange="window.location.href='index.php?controlador=compra&accion=index&limite=' + this.value + '&estado=<?= urlencode($filtro) ?>'"
                class="bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-700 dark:text-slate-300 py-1 px-2 focus:outline-none focus:ring-2 focus:ring-blue-500/20">
            <?php 
            $opciones = $opcionesLimite ?? [3, 5, 7, 10, 25, 50, 100];
            $actual = $porPagina ?? 10;
            foreach ($opciones as $op): 
            ?>
                <option value="<?= $op ?>" <?= $actual == $op ? 'selected' : '' ?>><?= $op ?></option>
            <?php endforeach; ?>
        </select>
        <span>por página</span>
    </div>

    <!-- Controles de Paginación -->
    <?php if ($totalPaginas > 1): ?>
    <div class="flex items-center gap-1">
        <?php 
        $rango = 2; // Número de páginas a mostrar alrededor de la actual
        $inicio = max(1, $paginaActual - $rango);
        $fin = min($totalPaginas, $paginaActual + $rango);
        
        $prevUrl = "index.php?controlador=compra&accion=index&pagina=" . ($paginaActual - 1) . "&limite=$actual&estado=" . urlencode($filtro);
        $nextUrl = "index.php?controlador=compra&accion=index&pagina=" . ($paginaActual + 1) . "&limite=$actual&estado=" . urlencode($filtro);
        ?>

        <!-- Anterior -->
        <a href="<?= $prevUrl ?>" 
           class="p-2 rounded-lg border border-slate-200 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors <?= $paginaActual <= 1 ? 'pointer-events-none opacity-50' : '' ?>">
            <?= Icons::get('chevron-left', 'w-4 h-4 text-slate-600 dark:text-slate-300') ?>
        </a>

        <!-- Primera página si estamos lejos -->
        <?php if ($inicio > 1): ?>
            <a href="index.php?controlador=compra&accion=index&pagina=1&limite=<?= $actual ?>&estado=<?= urlencode($filtro) ?>" 
               class="px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 text-sm font-medium transition-colors">
                1
            </a>
            <?php if ($inicio > 2): ?>
                <span class="px-2 text-slate-400">...</span>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Páginas numeradas -->
        <?php for ($i = $inicio; $i <= $fin; $i++): ?>
            <a href="index.php?controlador=compra&accion=index&pagina=<?= $i ?>&limite=<?= $actual ?>&estado=<?= urlencode($filtro) ?>" 
               class="px-3 py-1.5 rounded-lg text-sm font-medium transition-all
                      <?= $i == $paginaActual 
                          ? 'bg-blue-500 text-white shadow-lg shadow-blue-500/30' 
                          : 'bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-600' ?>">
                <?= $i ?>
            </a>
        <?php endfor; ?>

        <!-- Última página si estamos lejos -->
        <?php if ($fin < $totalPaginas): ?>
            <?php if ($fin < $totalPaginas - 1): ?>
                <span class="px-2 text-slate-400">...</span>
            <?php endif; ?>
            <a href="index.php?controlador=compra&accion=index&pagina=<?= $totalPaginas ?>&limite=<?= $actual ?>&estado=<?= urlencode($filtro) ?>" 
               class="px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 text-sm font-medium transition-colors">
                <?= $totalPaginas ?>
            </a>
        <?php endif; ?>

        <!-- Siguiente -->
        <a href="<?= $nextUrl ?>" 
           class="p-2 rounded-lg border border-slate-200 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors <?= $paginaActual >= $totalPaginas ? 'pointer-events-none opacity-50' : '' ?>">
            <?= Icons::get('chevron-right', 'w-4 h-4 text-slate-600 dark:text-slate-300') ?>
        </a>
    </div>
    <?php endif; ?>
</div>
