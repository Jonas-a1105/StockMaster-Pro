<?php
/**
 * CLIENTES - Index Enterprise
 * views/clientes/index.php
 */
use App\Helpers\Icons;

$clientes = $clientes ?? [];
$busqueda = $_GET['buscar'] ?? '';
?>

<!-- Header -->
<div id="tabla-clientes" class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-800 dark:text-white flex items-center gap-3">
            <?= Icons::get('clients', 'w-7 h-7 text-indigo-500') ?>
            Gestión de Clientes
        </h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
            Administra cartera de clientes y límites de crédito
        </p>
    </div>
    
    <button onclick="abrirModalCliente()" class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold shadow-lg shadow-emerald-500/30 transition-all">
        <?= Icons::get('user-plus', 'w-5 h-5') ?>
        Nuevo Cliente
    </button>
</div>

<!-- Search Bar -->
<div class="bg-white dark:bg-slate-700/50 rounded-2xl border border-slate-200 dark:border-slate-600 p-4 mb-6 shadow-sm">
    <form method="GET" action="index.php" class="flex gap-4">
        <input type="hidden" name="controlador" value="cliente">
        <input type="hidden" name="accion" value="index">
        
        <div class="relative flex-1">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <?= Icons::get('search', 'w-5 h-5 text-slate-400') ?>
            </div>
            <input type="text" name="buscar" value="<?= htmlspecialchars($busqueda) ?>" 
                   placeholder="Buscar por nombre, documento, email..."
                   class="w-full pl-10 pr-4 py-2.5 bg-slate-100 dark:bg-slate-600 border-0 rounded-xl text-slate-800 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
        </div>
        
        <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-medium shadow-md shadow-indigo-500/20 transition-all">
            Buscar
        </button>
    </form>
</div>

<!-- Grid de Clientes -->
<?php if (empty($clientes)): ?>
    <div class="bg-white dark:bg-slate-700/50 rounded-2xl border border-slate-200 dark:border-slate-600 p-12 text-center">
        <?= Icons::get('user', 'w-16 h-16 mx-auto text-slate-200 dark:text-slate-600 mb-4') ?>
        <p class="text-slate-500 dark:text-slate-400 text-lg font-medium">No hay clientes registrados</p>
        <?php if (!empty($busqueda)): ?>
            <p class="text-slate-400 dark:text-slate-500 mt-1">Intenta con otros términos de búsqueda</p>
        <?php else: ?>
            <button onclick="abrirModalCliente()" class="mt-4 px-4 py-2 text-indigo-600 dark:text-indigo-400 font-medium hover:underline">
                Registrar primer cliente
            </button>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        <?php foreach($clientes as $c): 
            $esEmpresa = $c['tipo_cliente'] === 'Juridico';
            $deuda = $c['deuda'] ?? 0;
            $limite = $c['limite_credito'] ?? 0;
            $porcentajeDeuda = $limite > 0 ? ($deuda / $limite) * 100 : 0;
        ?>
        <div class="group bg-white dark:bg-slate-700/50 rounded-2xl border border-slate-200 dark:border-slate-600 p-5 hover:border-indigo-300 dark:hover:border-indigo-700 hover:shadow-lg hover:shadow-indigo-500/10 transition-all duration-300 relative overflow-hidden">
            <!-- Header Card -->
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3 class="font-bold text-lg text-slate-800 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                        <?= htmlspecialchars($c['nombre']) ?>
                    </h3>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 mt-1 rounded-full text-xs font-semibold <?= $esEmpresa ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' ?>">
                        <?= $esEmpresa ? Icons::get('briefcase', 'w-3 h-3') : Icons::get('user', 'w-3 h-3') ?>
                        <?= $esEmpresa ? 'Empresa' : 'Persona' ?>
                    </span>
                </div>
                <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-600 flex items-center justify-center text-slate-500 dark:text-slate-400 font-bold text-sm">
                    <?= strtoupper(substr($c['nombre'], 0, 2)) ?>
                </div>
            </div>
            
            <!-- Info -->
            <div class="space-y-2 mb-5">
                <div class="flex items-center gap-3 text-sm text-slate-600 dark:text-slate-300">
                    <?= Icons::get('document', 'w-4 h-4 text-slate-400') ?>
                    <span class="font-medium text-slate-700 dark:text-slate-200"><?= $c['tipo_documento'] ?>-</span>
                    <span><?= htmlspecialchars($c['numero_documento'] ?? 'N/A') ?></span>
                </div>
                <div class="flex items-center gap-3 text-sm text-slate-600 dark:text-slate-300">
                    <?= Icons::get('phone', 'w-4 h-4 text-slate-400') ?>
                    <span><?= htmlspecialchars($c['telefono'] ?? 'Sin teléfono') ?></span>
                </div>
                <div class="flex items-center gap-3 text-sm text-slate-600 dark:text-slate-300">
                    <?= Icons::get('mail', 'w-4 h-4 text-slate-400') ?>
                    <span class="truncate"><?= htmlspecialchars($c['email'] ?? 'Sin email') ?></span>
                </div>
            </div>
            
            <!-- Finanzas -->
            <div class="pt-4 border-t border-slate-100 dark:border-slate-600">
                <div class="flex justify-between items-end mb-2">
                    <span class="text-xs text-slate-500 uppercase font-bold tracking-wider">Crédito</span>
                    <span class="text-xs font-medium text-slate-600 dark:text-slate-400">
                        $<?= number_format($deuda, 2) ?> / $<?= number_format($limite, 2) ?>
                    </span>
                </div>
                
                <!-- Progress Bar -->
                <div class="h-2 w-full bg-slate-100 dark:bg-slate-600 rounded-full overflow-hidden">
                    <div class="h-full rounded-full transition-all duration-500 <?= $porcentajeDeuda > 80 ? 'bg-red-500' : ($porcentajeDeuda > 50 ? 'bg-amber-500' : 'bg-emerald-500') ?>" 
                         style="width: <?= min(100, $porcentajeDeuda) ?>%"></div>
                </div>
            </div>
            
            <!-- Actions Overlay (Visible on Hover) -->
            <div class="absolute inset-x-0 bottom-0 p-4 bg-white/90 dark:bg-slate-800/90 backdrop-blur-sm translate-y-full group-hover:translate-y-0 transition-transform duration-300 border-t border-slate-100 dark:border-slate-600 flex gap-3">
                <button onclick='editarCliente(<?= json_encode($c) ?>)' class="flex-1 flex items-center justify-center gap-2 px-3 py-2 bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 rounded-lg hover:bg-amber-100 dark:hover:bg-amber-900/50 transition-colors text-sm font-medium">
                    <?= Icons::get('edit', 'w-4 h-4') ?> Editar
                </button>
                <a href="index.php?controlador=cliente&accion=ver&id=<?= $c['id'] ?>" class="flex-1 flex items-center justify-center gap-2 px-3 py-2 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 rounded-lg hover:bg-indigo-100 dark:hover:bg-indigo-900/50 transition-colors text-sm font-medium">
                    <?= Icons::get('eye', 'w-4 h-4') ?> Ver Detalle
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Include Shared Modal -->
<?php include 'modal_crear.php'; ?>


