<?php
/**
 * PROVEEDORES - Gestión Enterprise
 * views/proveedores/index.php
 */
use App\Helpers\Icons;

$proveedores = $proveedores ?? [];

// Helper para construir URLs de paginación manteniendo parámetros
$buildUrl = function($page) use ($paginacion) {
    $url = "index.php?controlador=proveedor&accion=index&page={$page}";
    if (!empty($paginacion['busqueda'])) {
        $url .= "&busqueda=" . urlencode($paginacion['busqueda']);
    }
    // Limit se maneja en el selector, pero si ya está en URL, se mantiene
    if (isset($_GET['limit'])) {
        $url .= "&limit=" . (int)$_GET['limit'];
    }
    return $url;
};
?>

<!-- Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-800 dark:text-white flex items-center gap-3">
            <?= Icons::get('suppliers', 'w-7 h-7 text-indigo-500') ?>
            Gestión de Proveedores
        </h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
            Administra tus relaciones comerciales y contactos
        </p>
    </div>
    
    <div class="flex items-center gap-3">
        <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 rounded-lg text-sm font-medium">
            <?= Icons::get('info', 'w-4 h-4') ?>
            <span>Total: <?= $paginacion['total_registros'] ?? count($proveedores) ?></span>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
    
    <!-- Columna Izquierda: Formulario (1/3) -->
    <div class="xl:col-span-1 space-y-6">
        <div class="bg-white dark:bg-slate-700/50 rounded-2xl border border-slate-200 dark:border-slate-600 p-5 sticky top-24">
            <h3 class="font-semibold text-slate-800 dark:text-white flex items-center gap-2 mb-4 pb-3 border-b border-slate-100 dark:border-slate-600">
                <?= Icons::get('plus-circle', 'w-5 h-5 text-emerald-500') ?>
                Nuevo Proveedor
            </h3>
            
            <form id="form-proveedor" action="index.php?controlador=proveedor&accion=crear" method="POST" class="space-y-4">
                
                <!-- Nombre -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Nombre Empresa</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <?= Icons::get('suppliers', 'w-4 h-4 text-slate-400') ?>
                        </div>
                        <input type="text" name="prov-nombre" placeholder="Ej: Distribuidora Central" required 
                               class="w-full pl-10 pr-4 py-2.5 bg-slate-100 dark:bg-slate-600 border-0 rounded-xl text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                    </div>
                </div>
                
                <!-- Contacto -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Persona de Contacto</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <?= Icons::get('user', 'w-4 h-4 text-slate-400') ?>
                        </div>
                        <input type="text" name="prov-contacto" placeholder="Ej: Juan Pérez" 
                               class="w-full pl-10 pr-4 py-2.5 bg-slate-100 dark:bg-slate-600 border-0 rounded-xl text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                    </div>
                </div>
                
                <!-- Teléfono -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Teléfono</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <?= Icons::get('phone', 'w-4 h-4 text-slate-400') ?>
                        </div>
                        <input type="text" name="prov-telefono" placeholder="Ej: +58 412-1234567" 
                               class="w-full pl-10 pr-4 py-2.5 bg-slate-100 dark:bg-slate-600 border-0 rounded-xl text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                    </div>
                </div>
                
                <!-- Email -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Correo Electrónico</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <?= Icons::get('mail', 'w-4 h-4 text-slate-400') ?>
                        </div>
                        <input type="email" name="prov-email" placeholder="contacto@empresa.com" 
                               class="w-full pl-10 pr-4 py-2.5 bg-slate-100 dark:bg-slate-600 border-0 rounded-xl text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                    </div>
                </div>
                
                <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold shadow-lg shadow-indigo-500/30 transition-all">
                    <?= Icons::get('plus', 'w-5 h-5') ?>
                    Agregar Proveedor
                </button>
            </form>
        </div>
    </div>
    
    <!-- Columna Derecha: Lista (2/3) -->
    <div class="xl:col-span-2">
        <div class="bg-white dark:bg-slate-700/50 rounded-2xl border border-slate-200 dark:border-slate-600 p-5 h-full flex flex-col">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4 pb-3 border-b border-slate-100 dark:border-slate-600">
                <h3 class="font-semibold text-slate-800 dark:text-white flex items-center gap-2">
                    <?= Icons::get('list', 'w-5 h-5 text-slate-400') ?>
                    Directorio
                </h3>
                
                <!-- Buscador -->
                <form action="index.php" method="GET" class="relative w-full sm:w-64">
                    <input type="hidden" name="controlador" value="proveedor">
                    <input type="hidden" name="accion" value="index">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <?= Icons::get('search', 'w-4 h-4 text-slate-400') ?>
                    </div>
                    <input type="text" name="busqueda" value="<?= htmlspecialchars($paginacion['busqueda'] ?? '') ?>" 
                           placeholder="Buscar proveedor..." 
                           class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-600 border border-slate-200 dark:border-slate-500 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:text-white">
                </form>
            </div>
            
            <?php
            // Preparar contenido de las filas
            ob_start();
            foreach ($proveedores as $p): ?>
                <tr class="group hover:bg-slate-50 dark:hover:bg-slate-600/30 transition-colors">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold text-xs">
                                <?= strtoupper(substr($p['nombre'], 0, 2)) ?>
                            </div>
                            <span class="font-medium text-slate-800 dark:text-white"><?= htmlspecialchars($p['nombre']) ?></span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-slate-600 dark:text-slate-300">
                        <?= htmlspecialchars($p['contacto']) ?>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex flex-col gap-1">
                            <?php if (!empty($p['telefono'])): ?>
                                <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                                    <?= Icons::get('phone', 'w-3 h-3') ?>
                                    <?= htmlspecialchars($p['telefono']) ?>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($p['email'])): ?>
                                <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                                    <?= Icons::get('mail', 'w-3 h-3') ?>
                                    <?= htmlspecialchars($p['email']) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button onclick='editarProveedor(<?= json_encode($p) ?>)' class="p-1.5 text-amber-500 hover:bg-amber-50 dark:hover:bg-amber-900/30 rounded-lg transition-colors" title="Editar">
                                <?= Icons::get('edit', 'w-4 h-4') ?>
                            </button>
                            <form id="form-eliminar-<?= $p['id'] ?>" action="index.php?controlador=proveedor&accion=eliminar" method="POST" class="inline">
                                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                <button type="button" onclick="confirmarEliminar(<?= $p['id'] ?>)" class="p-1.5 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition-colors" title="Eliminar">
                                    <?= Icons::get('trash', 'w-4 h-4') ?>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; 
            $tableContent = ob_get_clean();

            echo View::component('table', [
                'headers' => [
                    ['label' => 'Empresa', 'align' => 'left'],
                    ['label' => 'Contacto', 'align' => 'left'],
                    ['label' => 'Info Contacto', 'align' => 'left'],
                    ['label' => 'Acciones', 'align' => 'center', 'class' => 'w-24']
                ],
                'content' => View::raw($tableContent),
                'empty' => empty($proveedores),
                'emptyMsg' => 'No hay proveedores registrados',
                'emptyIcon' => 'suppliers',
                'pagination' => [
                    'current' => $paginacion['current'] ?? 1,
                    'total' => $paginacion['total'] ?? 1,
                    'limit' => $paginacion['limit'] ?? 10,
                    'url_builder' => $buildUrl,
                    'limit_name' => 'limit'
                ]
            ]);
            ?>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/modal_crear.php'; ?>

<!-- Modal Editar -->
<?php
echo View::component('modal', [
    'id' => 'modal-editar-proveedor',
    'title' => 'Editar Proveedor',
    'size' => 'lg',
    'content' => '
        <form id="form-editar-proveedor" class="space-y-4">
            <input type="hidden" id="editar-prov-id" name="editar-prov-id">
            
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Nombre</label>
                <input type="text" id="editar-prov-nombre" name="editar-prov-nombre" required 
                       class="w-full px-3 py-2 bg-slate-100 dark:bg-slate-600 border-0 rounded-lg text-slate-800 dark:text-white focus:ring-2 focus:ring-amber-500/30">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Contacto</label>
                <input type="text" id="editar-prov-contacto" name="editar-prov-contacto" 
                       class="w-full px-3 py-2 bg-slate-100 dark:bg-slate-600 border-0 rounded-lg text-slate-800 dark:text-white focus:ring-2 focus:ring-amber-500/30">
            </div>
            
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Teléfono</label>
                    <input type="text" id="editar-prov-telefono" name="editar-prov-telefono" 
                           class="w-full px-3 py-2 bg-slate-100 dark:bg-slate-600 border-0 rounded-lg text-slate-800 dark:text-white focus:ring-2 focus:ring-amber-500/30">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Email</label>
                    <input type="email" id="editar-prov-email" name="editar-prov-email" 
                           class="w-full px-3 py-2 bg-slate-100 dark:bg-slate-600 border-0 rounded-lg text-slate-800 dark:text-white focus:ring-2 focus:ring-amber-500/30">
                </div>
            </div>
        </form>
    ',
    'footer' => '
        <div class="flex gap-3 w-full sm:justify-end">
            ' . View::component('button', [
                'label' => 'Cancelar',
                'variant' => 'outline',
                'attributes' => 'type="button" onclick="closeModal(\'modal-editar-proveedor\')"',
                'class' => 'flex-1 sm:flex-none'
            ]) . '
            ' . View::component('button', [
                'label' => 'Guardar Cambios',
                'variant' => 'primary',
                'attributes' => 'form="form-editar-proveedor" type="submit"',
                'class' => 'flex-1 sm:flex-none bg-amber-600 hover:bg-amber-700 shadow-amber-500/30'
            ]) . '
        </div>
    '
]);
?>

<!-- Módulo de Proveedores (cargado desde archivo externo) -->
<script src="<?= BASE_URL ?>js/pages/proveedores.js?v=<?= time() ?>"></script>