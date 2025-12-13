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
            
            <div class="overflow-x-auto flex-1">
                <table class="w-full text-sm text-left">
                    <thead>
                        <tr class="text-xs text-slate-500 dark:text-slate-400 uppercase border-b border-slate-100 dark:border-slate-600">
                            <th class="px-4 py-3 font-semibold">Empresa</th>
                            <th class="px-4 py-3 font-semibold">Contacto</th>
                            <th class="px-4 py-3 font-semibold">Info Contacto</th>
                            <th class="px-4 py-3 font-semibold text-center w-24">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-600">
                        <?php if (empty($proveedores)): ?>
                            <tr>
                                <td colspan="4" class="px-4 py-12 text-center">
                                    <div class="flex flex-col items-center">
                                        <?= Icons::get('suppliers', 'w-12 h-12 text-slate-200 dark:text-slate-600 mb-2') ?>
                                        <p class="text-slate-400">No hay proveedores registrados</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($proveedores as $p): ?>
                            <tr class="group hover:bg-slate-50 dark:hover:bg-slate-600/30 transition-colors">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold text-xs">
                                            <?= strtoupper(substr($p['nombre'], 0, 2)) ?>
                                        </div>
                                        <span class="font-medium text-slate-800 dark:text-white"><?= htmlspecialchars($p['nombre']) ?></span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">
                                    <?= htmlspecialchars($p['contacto']) ?>
                                </td>
                                <td class="px-4 py-3">
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
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button onclick='editarProveedor(<?= json_encode($p) ?>)' class="p-1.5 text-amber-500 hover:bg-amber-50 dark:hover:bg-amber-900/30 rounded-lg transition-colors" title="Editar">
                                            <?= Icons::get('edit', 'w-4 h-4') ?>
                                        </button>
                                        <form action="index.php?controlador=proveedor&accion=eliminar" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar este proveedor?');" class="inline">
                                            <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                            <button type="submit" class="p-1.5 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition-colors" title="Eliminar">
                                                <?= Icons::get('trash', 'w-4 h-4') ?>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Paginación y Selector -->
            <?php if (isset($paginacion) && ($paginacion['total'] > 1 || !empty($proveedores))): ?>
            <div class="flex flex-col sm:flex-row items-center justify-between p-4 border-t border-slate-100 dark:border-slate-600 gap-4">
                
                <!-- Selector Límite -->
                <div class="flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400">
                    <span>Mostrar</span>
                    <select onchange="window.location.href='<?= $buildUrl(1) ?>&limit=' + this.value"
                            class="bg-white dark:bg-slate-600 border border-slate-200 dark:border-slate-500 rounded-lg py-1 px-2 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                        <?php 
                        $opciones = [5, 10, 25, 50, 100];
                        $actual = $paginacion['limit'] ?? 10;
                        foreach ($opciones as $op): 
                        ?>
                            <option value="<?= $op ?>" <?= $actual == $op ? 'selected' : '' ?>><?= $op ?></option>
                        <?php endforeach; ?>
                    </select>
                    <span>por pág</span>
                </div>

                <!-- Paginación Numerada -->
                <?php if ($paginacion['total'] > 1): ?>
                <div class="flex items-center gap-1">
                    <?php 
                    $paginaActual = $paginacion['current'];
                    $totalPaginas = $paginacion['total'];
                    $rango = 2; 
                    $inicio = max(1, $paginaActual - $rango);
                    $fin = min($totalPaginas, $paginaActual + $rango);
                    ?>

                    <!-- Prev -->
                    <a href="<?= $buildUrl(max(1, $paginaActual - 1)) ?>" 
                       class="p-2 rounded-lg border border-slate-200 dark:border-slate-500 hover:bg-slate-50 dark:hover:bg-slate-600 transition-colors <?= $paginaActual <= 1 ? 'pointer-events-none opacity-50' : '' ?>">
                        <?= Icons::get('chevron-left', 'w-4 h-4 text-slate-600 dark:text-slate-300') ?>
                    </a>

                    <!-- Primero -->
                    <?php if ($inicio > 1): ?>
                        <a href="<?= $buildUrl(1) ?>" class="px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-500 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-600 text-sm font-medium">1</a>
                        <?php if ($inicio > 2): ?><span class="px-2 text-slate-400">...</span><?php endif; ?>
                    <?php endif; ?>

                    <!-- Loop -->
                    <?php for ($i = $inicio; $i <= $fin; $i++): ?>
                        <a href="<?= $buildUrl($i) ?>" 
                           class="px-3 py-1.5 rounded-lg text-sm font-medium transition-all
                                  <?= $i == $paginaActual 
                                      ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/30' 
                                      : 'bg-white dark:bg-slate-600 border border-slate-200 dark:border-slate-500 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-600' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>

                    <!-- Último -->
                    <?php if ($fin < $totalPaginas): ?>
                        <?php if ($fin < $totalPaginas - 1): ?><span class="px-2 text-slate-400">...</span><?php endif; ?>
                        <a href="<?= $buildUrl($totalPaginas) ?>" class="px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-500 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-600 text-sm font-medium"><?= $totalPaginas ?></a>
                    <?php endif; ?>

                    <!-- Next -->
                    <a href="<?= $buildUrl(min($totalPaginas, $paginaActual + 1)) ?>" 
                       class="p-2 rounded-lg border border-slate-200 dark:border-slate-500 hover:bg-slate-50 dark:hover:bg-slate-600 transition-colors <?= $paginaActual >= $totalPaginas ? 'pointer-events-none opacity-50' : '' ?>">
                        <?= Icons::get('chevron-right', 'w-4 h-4 text-slate-600 dark:text-slate-300') ?>
                    </a>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Editar (Solo estructura básica, lógica JS manejará la apertura) -->
<div id="modal-editar-proveedor" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity opacity-0" id="modal-backdrop"></div>

    <!-- Panel -->
    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-800 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" id="modal-panel">
                
                <div class="bg-white dark:bg-slate-800 px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-900/30 sm:mx-0 sm:h-10 sm:w-10">
                            <?= Icons::get('edit', 'w-6 h-6 text-amber-600 dark:text-amber-400') ?>
                        </div>
                        <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                            <h3 class="text-base font-semibold leading-6 text-slate-900 dark:text-white" id="modal-title">Editar Proveedor</h3>
                            
                            <form id="form-editar-proveedor" class="mt-4 space-y-4">
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
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-slate-700/50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                    <button type="submit" form="form-editar-proveedor" class="inline-flex w-full justify-center rounded-xl bg-amber-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-amber-500 sm:ml-3 sm:w-auto transition-colors">
                        Guardar Cambios
                    </button>
                    <button type="button" id="cancelar-modal-proveedor" class="mt-3 inline-flex w-full justify-center rounded-xl bg-white dark:bg-slate-600 px-3 py-2 text-sm font-semibold text-slate-900 dark:text-white shadow-sm ring-1 ring-inset ring-slate-300 dark:ring-slate-500 hover:bg-slate-50 dark:hover:bg-slate-500 sm:mt-0 sm:w-auto transition-colors">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Lógica básica modal
function toggleModal(show) {
    const modal = document.getElementById('modal-editar-proveedor');
    const backdrop = document.getElementById('modal-backdrop');
    const panel = document.getElementById('modal-panel');
    
    if (show) {
        modal.classList.remove('hidden');
        // Trigger animations
        setTimeout(() => {
            backdrop.classList.remove('opacity-0');
            panel.classList.remove('opacity-0', 'translate-y-4', 'sm:translate-y-0', 'sm:scale-95');
        }, 10);
    } else {
        backdrop.classList.add('opacity-0');
        panel.classList.add('opacity-0', 'translate-y-4', 'sm:translate-y-0', 'sm:scale-95');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }
}

function editarProveedor(p) {
    document.getElementById('editar-prov-id').value = p.id;
    document.getElementById('editar-prov-nombre').value = p.nombre;
    document.getElementById('editar-prov-contacto').value = p.contacto;
    document.getElementById('editar-prov-telefono').value = p.telefono;
    document.getElementById('editar-prov-email').value = p.email;
    
    toggleModal(true);
}

document.getElementById('cancelar-modal-proveedor')?.addEventListener('click', () => toggleModal(false));

// Interceptar submit para AJAX (opcional, pero buena práctica)
document.getElementById('form-editar-proveedor')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = e.target.querySelector('button[type="submit"]');
    const originalText = btn.textContent;
    
    try {
        btn.disabled = true;
        btn.textContent = 'Guardando...';
        
        const formData = new FormData(e.target);
        const data = {};
        formData.forEach((value, key) => data[key] = value);
        
        const response = await fetch('index.php?controlador=proveedor&accion=editar', {
            method: 'POST',
            body: formData
        });
        
        // Asumiendo que el backend redirige o devuelve JSON. 
        // Si devuelve HTML completo, recargamos.
        window.location.reload();
        
    } catch (error) {
        console.error(error);
        alert('Error al guardar');
        btn.disabled = false;
        btn.textContent = originalText;
    }
});
</script>