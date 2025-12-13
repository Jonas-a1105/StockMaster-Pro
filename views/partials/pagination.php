<?php
/**
 * PAGINATION - Componente de paginación reutilizable
 * 
 * Variables esperadas:
 * - $paginaActual: Página actual
 * - $totalPaginas: Total de páginas
 * - $baseUrl: URL base para los enlaces (ej: "index.php?controlador=producto&accion=index")
 * - $parametrosExtra: Array de parámetros adicionales (opcional)
 */

if (!isset($totalPaginas) || $totalPaginas <= 1) return;

$parametrosExtra = $parametrosExtra ?? [];
$queryExtra = '';
foreach ($parametrosExtra as $key => $value) {
    $queryExtra .= '&' . urlencode($key) . '=' . urlencode($value);
}
?>

<nav class="pagination-container" style="display: flex; justify-content: center; gap: 5px; margin-top: 20px;">
    <?php if ($paginaActual > 1): ?>
        <a href="<?= $baseUrl ?>&pagina=<?= $paginaActual - 1 ?><?= $queryExtra ?>" class="btn btn-secondary btn-sm">
            <i class="fas fa-chevron-left"></i> Anterior
        </a>
    <?php endif; ?>
    
    <span style="padding: 5px 15px; background: var(--glass-bg); border-radius: var(--border-radius); border: 1px solid var(--border-color);">
        Página <?= $paginaActual ?> de <?= $totalPaginas ?>
    </span>
    
    <?php if ($paginaActual < $totalPaginas): ?>
        <a href="<?= $baseUrl ?>&pagina=<?= $paginaActual + 1 ?><?= $queryExtra ?>" class="btn btn-secondary btn-sm">
            Siguiente <i class="fas fa-chevron-right"></i>
        </a>
    <?php endif; ?>
</nav>
