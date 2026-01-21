<?php
/**
 * Componente Table Pro
 * @var array $headers  [['label' => '...', 'align' => 'left|center|right', 'class' => '...'], ...]
 * @var string $content Filas de la tabla (HTML tr/td)
 * @var array $pagination ['current' => 1, 'total' => 5, 'limit' => 10, 'url_builder' => fn, 'limit_name' => 'limit']
 * @var bool $empty     Si la tabla está vacía
 * @var string $emptyMsg Mensaje opcional
 * @var string $emptyIcon Nombre del icono opcional
 * @var string $class   Clases extra para el contenedor
 */

use App\Helpers\Icons;

$headers = $headers ?? [];
$content = $content ?? '';
$pagination = $pagination ?? null;
$empty = $empty ?? false;
$emptyMsg = $emptyMsg ?? 'No se encontraron registros';
$emptyIcon = $emptyIcon ?? 'search';
$class = $class ?? '';
$id = $id ?? '';
$tableId = $tableId ?? '';
$tbodyId = $tbodyId ?? '';

// Estilos de alineación
$alignStyles = [
    'left'   => 'text-left',
    'center' => 'text-center',
    'right'  => 'text-right'
];
?>

<div id="<?= $id ?>" class="bg-white dark:bg-slate-700/50 rounded-2xl border border-slate-200 dark:border-slate-600 overflow-hidden shadow-sm <?= $class ?>">
    <div class="overflow-x-auto">
        <table id="<?= $tableId ?>" class="w-full text-sm text-left border-collapse">
            <thead class="bg-slate-50 dark:bg-slate-600/50 border-b border-slate-100 dark:border-slate-600">
                <tr>
                    <?php foreach ($headers as $h): 
                        $hLabel = is_array($h) ? ($h['label'] ?? '') : $h;
                        $hAlign = is_array($h) ? ($h['align'] ?? 'left') : 'left';
                        $hClass = is_array($h) ? ($h['class'] ?? '') : '';
                    ?>
                        <th class="px-6 py-4 font-semibold text-slate-500 dark:text-slate-400 uppercase text-xs <?= $alignStyles[$hAlign] ?? 'text-left' ?> <?= $hClass ?>">
                            <?= $hLabel ?>
                        </th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody id="<?= $tbodyId ?>" class="divide-y divide-slate-100 dark:divide-slate-600">
                <?php if ($empty): ?>
                    <tr>
                        <td colspan="<?= count($headers) ?>" class="px-6 py-16 text-center">
                            <div class="flex flex-col items-center">
                                <?= Icons::get($emptyIcon, 'w-16 h-16 mx-auto text-slate-200 dark:text-slate-600 mb-4') ?>
                                <p class="text-slate-500 dark:text-slate-400 text-lg font-medium"><?= $emptyMsg ?></p>
                                <p class="text-slate-400 dark:text-slate-500 text-sm mt-1">Refina tus filtros o intenta una búsqueda diferente</p>
                            </div>
                        </td>
                    </tr>
                <?php elseif (!empty($content)): ?>
                    <?= $content ?>
                <?php elseif (!empty($rows)): ?>
                    <?php foreach ($rows as $row): 
                        $rowItems = isset($row['content']) && is_array($row['content']) ? $row['content'] : $row;
                        $rowClass = is_array($row) ? ($row['class'] ?? '') : '';
                    ?>
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/30 transition-colors <?= $rowClass ?>">
                            <?php foreach ($rowItems as $index => $cell): 
                                $cContent = is_array($cell) ? ($cell['content'] ?? '') : $cell;
                                $cClass = is_array($cell) ? ($cell['class'] ?? '') : '';
                                
                                // Intentar obtener alineación desde el header correspondiente
                                $hAlign = isset($headers[$index]['align']) ? ($alignStyles[$headers[$index]['align']] ?? 'text-left') : 'text-left';
                            ?>
                                <td class="px-6 py-4 <?= $hAlign ?> <?= $cClass ?>">
                                    <?= $cContent ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Paginación Footer -->
    <?php if ($pagination && (!$empty || ($pagination['total'] ?? 0) > 1)): ?>
        <?php 
            $buildUrl = $pagination['url_builder'];
            $limitName = $pagination['limit_name'] ?? 'limit';
            $limit = $pagination['limit'] ?? 10;
            $current = $pagination['current'] ?? 1;
            $total = $pagination['total'] ?? 1;
        ?>
        <div class="flex flex-col sm:flex-row items-center justify-between p-4 border-t border-slate-100 dark:border-slate-600 gap-4 bg-slate-50/30 dark:bg-transparent">
            
            <!-- Selector Límite -->
            <div class="flex items-center gap-2 text-xs font-medium text-slate-500 dark:text-slate-400">
                <span>Mostrar</span>
                <select onchange="window.location.href='<?= $buildUrl(1) ?>&<?= $limitName ?>=' + this.value"
                        class="bg-white dark:bg-slate-600 border border-slate-200 dark:border-slate-500 rounded-lg py-1 px-2 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 cursor-pointer">
                    <?php foreach ([5, 10, 25, 50, 100] as $op): ?>
                        <option value="<?= $op ?>" <?= $limit == $op ? 'selected' : '' ?>><?= $op ?></option>
                    <?php endforeach; ?>
                </select>
                <span>por pág</span>
            </div>

            <!-- Paginador Numerado -->
            <?php if ($total > 1): ?>
                <div class="flex items-center gap-1">
                    <?php 
                        $rango = 2; 
                        $inicio = max(1, $current - $rango);
                        $fin = min($total, $current + $rango);
                    ?>

                    <!-- Prev -->
                    <a href="<?= $buildUrl(max(1, $current - 1)) ?>&<?= $limitName ?>=<?= $limit ?>" 
                       class="p-2 rounded-lg border border-slate-200 dark:border-slate-500 hover:bg-white dark:hover:bg-slate-600 transition-colors <?= $current <= 1 ? 'pointer-events-none opacity-40' : '' ?>">
                        <?= Icons::get('chevron-left', 'w-4 h-4 text-slate-600 dark:text-slate-300') ?>
                    </a>

                    <?php if ($inicio > 1): ?>
                        <a href="<?= $buildUrl(1) ?>&<?= $limitName ?>=<?= $limit ?>" class="w-9 h-9 flex items-center justify-center rounded-lg border border-slate-200 dark:border-slate-500 text-slate-600 dark:text-slate-300 hover:bg-white dark:hover:bg-slate-600 text-sm font-medium">1</a>
                        <?php if ($inicio > 2): ?><span class="px-1 text-slate-400 text-xs">...</span><?php endif; ?>
                    <?php endif; ?>

                    <?php for ($i = $inicio; $i <= $fin; $i++): ?>
                        <a href="<?= $buildUrl($i) ?>&<?= $limitName ?>=<?= $limit ?>" 
                           class="w-9 h-9 flex items-center justify-center rounded-lg text-sm font-bold transition-all
                                  <?= $i == $current 
                                      ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/30' 
                                      : 'bg-white dark:bg-slate-600 border border-slate-200 dark:border-slate-500 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-500' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($fin < $total): ?>
                        <?php if ($fin < $total - 1): ?><span class="px-1 text-slate-400 text-xs">...</span><?php endif; ?>
                        <a href="<?= $buildUrl($total) ?>&<?= $limitName ?>=<?= $limit ?>" class="w-9 h-9 flex items-center justify-center rounded-lg border border-slate-200 dark:border-slate-500 text-slate-600 dark:text-slate-300 hover:bg-white dark:hover:bg-slate-600 text-sm font-medium"><?= $total ?></a>
                    <?php endif; ?>

                    <!-- Next -->
                    <a href="<?= $buildUrl(min($total, $current + 1)) ?>&<?= $limitName ?>=<?= $limit ?>" 
                       class="p-2 rounded-lg border border-slate-200 dark:border-slate-500 hover:bg-white dark:hover:bg-slate-600 transition-colors <?= $current >= $total ? 'pointer-events-none opacity-40' : '' ?>">
                        <?= Icons::get('chevron-right', 'w-4 h-4 text-slate-600 dark:text-slate-300') ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
