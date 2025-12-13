<?php
/**
 * DETALLE DE CLIENTE - Vista Enterprise
 * views/clientes/ver.php
 */
use App\Helpers\Icons;

// Calculate additional stats if not present
$deuda = (float)($stats['deuda_actual'] ?? 0);
$total_compras = $stats['total_compras'] ?? 0;
?>

<!-- Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-800 dark:text-white flex items-center gap-3">
            <?= Icons::get('user', 'w-7 h-7 text-indigo-500') ?>
            <?= htmlspecialchars($cliente['nombre']) ?>
        </h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 flex items-center gap-2">
            <span class="<?= $cliente['tipo_cliente'] === 'Juridico' ? 'text-blue-600 dark:text-blue-400' : 'text-emerald-600 dark:text-emerald-400' ?> font-medium">
                <?= $cliente['tipo_cliente'] === 'Juridico' ? 'Cliente Jurídico' : 'Persona Natural' ?>
            </span>
            <span>•</span>
            <span>Registrado el <?= (new DateTime($cliente['created_at']))->format('d/m/Y') ?></span>
        </p>
    </div>
    
    <a href="index.php?controlador=cliente&accion=index" class="inline-flex items-center gap-2 px-4 py-2 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition-colors font-medium">
        <?= Icons::get('arrow-left', 'w-4 h-4') ?>
        Volver al Directorio
    </a>
</div>

<!-- Stats Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Total Compras -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-sm border border-slate-200 dark:border-slate-700 flex items-center justify-between animate-in fade-in-up duration-500 hover:shadow-md hover:-translate-y-1 transition-all cursor-default">
        <div>
            <p class="text-slate-500 dark:text-slate-400 text-sm font-medium mb-1">Total Compras</p>
            <h3 class="text-2xl font-bold text-slate-800 dark:text-white"><?= $total_compras ?></h3>
        </div>
        <div class="p-3 bg-indigo-50 dark:bg-indigo-900/30 rounded-xl text-indigo-600 dark:text-indigo-400">
             <?= Icons::get('cart', 'w-8 h-8') ?>
        </div>
    </div>

    <!-- Total Gastado -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-sm border border-slate-200 dark:border-slate-700 flex items-center justify-between animate-in fade-in-up duration-500 hover:shadow-md hover:-translate-y-1 transition-all cursor-default" style="animation-delay: 100ms;">
        <div>
            <p class="text-slate-500 dark:text-slate-400 text-sm font-medium mb-1">Total Gastado</p>
            <h3 class="text-2xl font-bold text-slate-800 dark:text-white">$<?= number_format((float)($stats['total_gastado'] ?? 0), 2) ?></h3>
        </div>
        <div class="p-3 bg-fuchsia-50 dark:bg-fuchsia-900/30 rounded-xl text-fuchsia-600 dark:text-fuchsia-400">
             <?= Icons::get('dollar', 'w-8 h-8') ?>
        </div>
    </div>

    <!-- Deuda Actual -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-sm border border-slate-200 dark:border-slate-700 flex items-center justify-between animate-in fade-in-up duration-500 hover:shadow-md hover:-translate-y-1 transition-all cursor-default" style="animation-delay: 200ms;">
        <div>
            <p class="text-slate-500 dark:text-slate-400 text-sm font-medium mb-1">Deuda Actual</p>
            <h3 class="text-2xl font-bold text-slate-800 dark:text-white">$<?= number_format($deuda, 2) ?></h3>
        </div>
        <?php 
            // Determine styles based on debt
            $bgIcon = $deuda > 0 ? 'bg-amber-50 dark:bg-amber-900/30' : 'bg-emerald-50 dark:bg-emerald-900/30';
            $textIcon = $deuda > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400';
            $iconName = $deuda > 0 ? 'alert-triangle' : 'check-circle';
        ?>
        <div class="p-3 <?= $bgIcon ?> rounded-xl <?= $textIcon ?>">
             <?= Icons::get($iconName, 'w-8 h-8') ?>
        </div>
    </div>

    <!-- Ultima Actividad -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-sm border border-slate-200 dark:border-slate-700 flex items-center justify-between animate-in fade-in-up duration-500 hover:shadow-md hover:-translate-y-1 transition-all cursor-default" style="animation-delay: 300ms;">
        <div>
            <p class="text-slate-500 dark:text-slate-400 text-sm font-medium mb-1">Última Compra</p>
            <h3 class="text-2xl font-bold text-slate-800 dark:text-white">
                <?= !empty($stats['ultima_compra']) ? (new DateTime($stats['ultima_compra']))->format('d/m/Y') : 'Nunca' ?>
            </h3>
        </div>
        <div class="p-3 bg-blue-50 dark:bg-blue-900/30 rounded-xl text-blue-600 dark:text-blue-400">
             <?= Icons::get('calendar', 'w-8 h-8') ?>
        </div>
    </div>
</div>

<!-- Tabs & Content -->
<div class="bg-white dark:bg-slate-700/50 rounded-2xl border border-slate-200 dark:border-slate-600 overflow-hidden shadow-sm">
    <!-- Tabs Header -->
    <div class="flex border-b border-slate-200 dark:border-slate-600">
        <button onclick="showTab('detalles')" class="tab-btn active px-6 py-4 text-sm font-semibold text-indigo-600 border-b-2 border-indigo-600 bg-indigo-50/50 dark:bg-transparent dark:text-indigo-400 dark:border-indigo-400 transition-colors focus:outline-none" data-tab="detalles">
            Información General
        </button>
        <button onclick="showTab('historial')" class="tab-btn px-6 py-4 text-sm font-medium text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 border-b-2 border-transparent transition-colors focus:outline-none" data-tab="historial">
            Historial de Compras
        </button>
    </div>

    <!-- Tab: Detalles -->
    <div id="detalles" class="tab-content p-6 sm:p-8">
        <h3 class="text-lg font-bold text-slate-800 dark:text-white mb-6 flex items-center gap-2">
            <?= Icons::get('document', 'w-5 h-5 text-slate-400') ?>
            Datos de Contacto
        </h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-8">
            <div class="space-y-6">
                <div>
                    <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Documento Identidad</span>
                    <span class="text-slate-800 dark:text-white font-medium text-lg flex items-center gap-2">
                        <?= Icons::get('card', 'w-4 h-4 text-slate-400') ?>
                        <?= $cliente['tipo_documento'] ?>-<?= htmlspecialchars($cliente['numero_documento'] ?? 'N/A') ?>
                    </span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Correo Electrónico</span>
                    <span class="text-slate-800 dark:text-white font-medium flex items-center gap-2">
                        <?= Icons::get('mail', 'w-4 h-4 text-slate-400') ?>
                        <?= htmlspecialchars($cliente['email'] ?? 'No especificado') ?>
                    </span>
                </div>
            </div>
            
            <div class="space-y-6">
                <div>
                    <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Teléfono Móvil</span>
                    <span class="text-slate-800 dark:text-white font-medium flex items-center gap-2">
                        <?= Icons::get('phone', 'w-4 h-4 text-slate-400') ?>
                        <?= htmlspecialchars($cliente['telefono'] ?? 'No especificado') ?>
                    </span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Dirección Fiscal</span>
                    <span class="text-slate-800 dark:text-white font-medium leading-relaxed flex items-start gap-2">
                        <?= Icons::get('map-pin', 'w-4 h-4 text-slate-400 mt-1') ?>
                        <?= htmlspecialchars($cliente['direccion'] ?? 'No especificada') ?>
                    </span>
                </div>
            </div>
            
            <div class="col-span-1 md:col-span-2 pt-6 border-t border-slate-100 dark:border-slate-600">
                <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Estado Crediticio</span>
                <div class="flex items-center gap-4">
                    <div class="flex-1 bg-slate-100 dark:bg-slate-700 h-2 rounded-full overflow-hidden">
                        <?php 
                            $limite = (float)$cliente['limite_credito'];
                            $percent = $limite > 0 ? min(100, ($deuda / $limite) * 100) : 0;
                            $color = $percent > 80 ? 'bg-red-500' : ($percent > 50 ? 'bg-amber-500' : 'bg-emerald-500');
                        ?>
                        <div class="h-full <?= $color ?>" style="width: <?= $percent ?>%"></div>
                    </div>
                    <span class="text-sm font-bold text-slate-700 dark:text-slate-300 whitespace-nowrap">
                        $<?= number_format($deuda, 2) ?> / $<?= number_format($limite, 2) ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab: Historial -->
    <div id="historial" class="tab-content hidden p-0">
        <?php if (empty($historial)): ?>
            <div class="p-12 text-center">
                <?= Icons::get('search', 'w-16 h-16 mx-auto text-slate-200 dark:text-slate-600 mb-4') ?>
                <p class="text-slate-500 dark:text-slate-400 text-lg font-medium">Este cliente no ha realizado compras</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-slate-50 dark:bg-slate-600/50 border-b border-slate-100 dark:border-slate-600">
                        <tr>
                            <th class="px-6 py-4 font-semibold text-slate-500 dark:text-slate-400 uppercase text-xs">ID</th>
                            <th class="px-6 py-4 font-semibold text-slate-500 dark:text-slate-400 uppercase text-xs">Fecha</th>
                            <th class="px-6 py-4 font-semibold text-slate-500 dark:text-slate-400 uppercase text-xs">Resumen Productos</th>
                            <th class="px-6 py-4 font-semibold text-slate-500 dark:text-slate-400 uppercase text-xs text-right">Total USD</th>
                            <th class="px-6 py-4 font-semibold text-slate-500 dark:text-slate-400 uppercase text-xs text-right">Total Bs</th>
                            <th class="px-6 py-4 font-semibold text-slate-500 dark:text-slate-400 uppercase text-xs text-center">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-600">
                        <?php foreach ($historial as $h): 
                            $isPaid = ($h['estado_pago'] ?? 'Pagada') === 'Pagada';
                        ?>
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-600/30 transition-colors">
                            <td class="px-6 py-4 font-mono text-slate-500 dark:text-slate-400">#<?= str_pad($h['id'], 5, '0', STR_PAD_LEFT) ?></td>
                            <td class="px-6 py-4 text-slate-600 dark:text-slate-300"><?= (new DateTime($h['created_at']))->format('d/m/Y') ?></td>
                            <td class="px-6 py-4 text-slate-500 dark:text-slate-400 max-w-xs truncate">
                                <?= htmlspecialchars($h['productos'] ?? 'N/A') ?>
                            </td>
                            <td class="px-6 py-4 text-right font-bold text-slate-700 dark:text-slate-200">$<?= number_format($h['total_usd'], 2) ?></td>
                            <td class="px-6 py-4 text-right font-medium text-emerald-600 dark:text-emerald-400">
                                Bs <span class="precio-bs" data-usd="<?= $h['total_usd'] ?>">...</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <?php if (!$isPaid): ?>
                                    <a href="index.php?controlador=cliente&accion=pagarVenta&venta_id=<?= $h['id'] ?>&cliente_id=<?= $cliente['id'] ?>&t=<?= time() ?>" 
                                       onclick="return confirm('¿Confirmar pago de esta venta?')"
                                       class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 rounded-full text-xs font-medium hover:bg-amber-100 dark:hover:bg-amber-900/50 transition-colors">
                                        <?= Icons::get('clock', 'w-3 h-3') ?> Pendiente
                                    </a>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 rounded-full text-xs font-medium">
                                        <?= Icons::get('check', 'w-3 h-3') ?> Pagada
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Tab Logic
function showTab(tabId) {
    // Hide all
    document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
    document.querySelectorAll('.tab-btn').forEach(el => {
        el.classList.remove('text-indigo-600', 'border-indigo-600', 'active', 'bg-indigo-50/50', 'dark:text-indigo-400', 'dark:border-indigo-400');
        el.classList.add('text-slate-500', 'border-transparent');
    });
    
    // Show Target
    document.getElementById(tabId).classList.remove('hidden');
    
    // Activate Button
    const btn = document.querySelector(`button[data-tab="${tabId}"]`);
    if(btn) {
        btn.classList.remove('text-slate-500', 'border-transparent');
        btn.classList.add('text-indigo-600', 'border-indigo-600', 'active', 'bg-indigo-50/50', 'dark:text-indigo-400', 'dark:border-indigo-400');
    }

    if(tabId === 'historial') {
        cargarYCalcularBs();
    }
}

// Exchange Rate Logic
async function cargarYCalcularBs() {
    try {
        let tasa = window.tasaCambioBS || 0;
        if (tasa <= 0) {
            const response = await fetch('index.php?controlador=config&accion=obtenerTasa');
            const data = await response.json();
            tasa = data.tasa || 0;
        }
        
        if (tasa > 0) {
            document.querySelectorAll('.precio-bs').forEach(span => {
                const usd = parseFloat(span.dataset.usd) || 0;
                span.textContent = (usd * tasa).toFixed(2);
            });
        }
    } catch (e) {
        console.error('Error calculando BCV', e);
    }
}
</script>
