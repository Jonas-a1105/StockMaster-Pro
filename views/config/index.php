<?php
/**
 * CONFIGURACIÓN - Vista Enterprise
 * views/config/index.php
 */
use App\Helpers\Icons;

$config = $config ?? [];
?>

<!-- Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-800 dark:text-white flex items-center gap-3">
            <?= Icons::get('settings', 'w-7 h-7 text-slate-500') ?>
            Configuración del Negocio
        </h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
            Personaliza la información que aparecerá en tus documentos
        </p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    
    <!-- Formulario Principal -->
    <div class="lg:col-span-2">
        <?php ob_start(); ?>
            <form action="index.php?controlador=config&accion=guardar" method="POST" enctype="multipart/form-data" class="space-y-6">
                <?= \App\Helpers\Security::csrfField() ?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Nombre -->
                    <div class="col-span-1 md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Nombre Legal / Fantasía</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <?= Icons::get('briefcase', 'w-4 h-4 text-slate-400') ?>
                            </div>
                            <input type="text" name="nombre" value="<?= htmlspecialchars($config['empresa_nombre'] ?? '') ?>" placeholder="Mi Empresa C.A."
                                   class="w-full pl-10 pr-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-xl text-slate-800 dark:text-white focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all">
                        </div>
                    </div>
                    
                    <!-- Teléfono -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Teléfono</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <?= Icons::get('phone', 'w-4 h-4 text-slate-400') ?>
                            </div>
                            <input type="text" name="telefono" value="<?= htmlspecialchars($config['empresa_telefono'] ?? '') ?>" placeholder="+58 412..."
                                   class="w-full pl-10 pr-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-xl text-slate-800 dark:text-white focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all">
                        </div>
                    </div>
                    
                    <!-- Dirección -->
                    <div class="col-span-1 md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Dirección Fiscal</label>
                        <div class="relative">
                             <div class="absolute top-3 left-3 flex items-center pointer-events-none">
                                <?= Icons::get('map-pin', 'w-4 h-4 text-slate-400') ?>
                            </div>
                            <textarea name="direccion" rows="2" placeholder="Av. Principal, Edificio..."
                                      class="w-full pl-10 pr-4 py-2.5 bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-xl text-slate-800 dark:text-white focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all resize-none"><?= htmlspecialchars($config['empresa_direccion'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Logo Section -->
                <div class="pt-6 border-t border-slate-100 dark:border-slate-700">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-3">Logo de la Empresa</label>
                    <div class="flex items-center gap-6">
                        <div class="w-20 h-20 rounded-2xl border-2 border-dashed border-slate-200 dark:border-slate-600 flex items-center justify-center bg-slate-50 dark:bg-slate-800/50 overflow-hidden">
                            <?php if (!empty($config['logo'])): ?>
                                <img src="public/img/<?= htmlspecialchars($config['logo']) ?>" alt="Logo" class="w-full h-full object-cover">
                            <?php else: ?>
                                <?= Icons::get('image', 'w-8 h-8 text-slate-300 dark:text-slate-600') ?>
                            <?php endif; ?>
                        </div>
                        <div class="flex-1">
                            <input type="file" id="logo-upload" name="logo" accept="image/*" class="hidden" onchange="previewLogo(this)">
                            <label for="logo-upload" class="cursor-pointer inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl text-slate-700 dark:text-slate-200 text-sm font-medium hover:bg-slate-50 dark:hover:bg-slate-600 transition-colors">
                                <?= Icons::get('upload', 'w-4 h-4') ?> Seleccionar Archivo
                            </label>
                            <p id="file-name" class="mt-2 text-xs text-slate-500 dark:text-slate-400">JPG o PNG (Máx 2MB)</p>
                        </div>
                    </div>
                </div>

                <!-- Submit -->
                <div class="pt-6">
                     <button type="submit" class="flex items-center gap-2 px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold shadow-lg shadow-indigo-500/20 transition-all active:scale-[0.98]">
                        <?= Icons::get('save', 'w-5 h-5') ?> Guardar Configuración
                    </button>
                </div>
            </form>
        <?php $formContent = ob_get_clean(); ?>
        
        <?= App\Core\View::component('card', [
            'title' => 'Datos de la Empresa',
            'icon' => 'inventory',
            'content' => App\Core\View::raw($formContent)
        ]) ?>
    </div>
    
    <!-- Currency & Info -->
    <div class="lg:col-span-1 space-y-6">
        <?php ob_start(); ?>
            <div class="space-y-4">
                <div class="flex items-center justify-between p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-100 dark:border-slate-700">
                    <span class="text-sm text-slate-500 dark:text-slate-400 font-medium">Estado API</span>
                    <span id="api-status" class="text-sm font-bold text-emerald-600 dark:text-emerald-400">Verificando...</span>
                </div>
                
                <div class="flex items-center justify-between p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-100 dark:border-slate-700">
                    <span class="text-sm text-slate-500 dark:text-slate-400 font-medium">Fuente</span>
                    <span id="api-source" class="text-sm font-bold text-slate-700 dark:text-slate-200">Auto</span>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Tasa Manual (Bs/USD)</label>
                    <div class="flex gap-2">
                        <input type="number" id="tasa-manual-input" step="0.01" placeholder="0.00"
                               class="flex-1 px-4 py-2 bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-xl text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30 transition-all font-mono">
                        <button id="btn-aplicar-tasa" 
                                class="px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl font-bold transition-all shadow-lg shadow-emerald-500/20 active:scale-95">
                            Aplicar
                        </button>
                    </div>
                </div>
                
                <button onclick="localStorage.removeItem('manualRate'); location.reload();" 
                        class="w-full py-2.5 text-xs font-semibold uppercase tracking-wider text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/10 rounded-xl transition-all border border-transparent hover:border-emerald-100 dark:hover:border-emerald-900/30">
                    Restablecer a Automático (API)
                </button>
            </div>
        <?php $currencyContent = ob_get_clean(); ?>

        <?= App\Core\View::component('card', [
            'title' => 'Moneda & Tasa',
            'icon' => 'dollar',
            'content' => App\Core\View::raw($currencyContent)
        ]) ?>

        <!-- Info Card -->
        <div class="bg-indigo-50 dark:bg-indigo-900/10 rounded-2xl p-6 border border-indigo-100/50 dark:border-indigo-800/30">
            <h4 class="font-bold text-indigo-900 dark:text-indigo-300 mb-3 flex items-center gap-2">
                <?= Icons::get('info', 'w-5 h-5') ?> Información
            </h4>
            <p class="text-sm text-indigo-800/70 dark:text-indigo-200/50 mb-4 leading-relaxed">
                La información configurada aquí se reflejará dinámicamente en:
            </p>
            <ul class="space-y-3">
                <?php foreach (['Encabezados de Facturas', 'Reportes PDF', 'Tickets de Venta'] as $item): ?>
                <li class="flex items-center gap-3 text-sm font-medium text-indigo-700 dark:text-indigo-300">
                    <span class="flex-shrink-0 w-5 h-5 bg-indigo-100 dark:bg-indigo-900/30 rounded-full flex items-center justify-center">
                        <?= Icons::get('check', 'w-3 h-3 text-indigo-600') ?>
                    </span>
                    <?= $item ?>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>

<!-- Módulo de Configuración (cargado desde archivo externo) -->
<script src="<?= BASE_URL ?>js/pages/config.js?v=<?= time() ?>"></script>