<?php use App\Helpers\Icons; ?>
<!-- Modal Cliente Shared Component -->
<!-- Modal Cliente Shared Component -->
<!-- Modal Cliente -->
<?php
echo View::component('modal', [
    'id' => 'modal-cliente',
    'title' => '<span id="modal-cliente-titulo">Nuevo Cliente</span>',
    'size' => '2xl',
    'content' => View::raw('
        <form id="form-cliente" class="space-y-5">
            <input type="hidden" id="cliente_id" name="id">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <!-- Nombre -->
                <div class="col-span-1 md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Nombre Completo / Razón Social *</label>
                    <input type="text" id="cliente_nombre" name="nombre" required placeholder="Ej: Juan Pérez"
                           class="w-full px-4 py-2.5 bg-slate-100 dark:bg-slate-700 border-0 rounded-xl text-slate-800 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-emerald-500/30">
                </div>

                <!-- Tipo Cliente -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Tipo de Cliente</label>
                    <select id="cliente_tipo_cliente" name="tipo_cliente" data-setup-simple-select class="w-full px-4 py-2.5 bg-slate-100 dark:bg-slate-700 border-0 rounded-xl text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500/30">
                        <option value="Natural">Persona Natural</option>
                        <option value="Juridico">Empresa/Jurídico</option>
                    </select>
                </div>

                <!-- Tipo Documento -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Tipo Documento</label>
                    <div class="flex gap-2">
                        <select id="cliente_tipo_documento" name="tipo_documento" data-setup-simple-select class="w-1/3 px-2 py-2.5 bg-slate-100 dark:bg-slate-700 border-0 rounded-xl text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500/30">
                            <option value="CI">CI</option>
                            <option value="RIF">RIF</option>
                            <option value="Pasaporte">Pasaporte</option>
                        </select>
                        <input type="text" id="cliente_numero_documento" name="numero_documento" placeholder="12345678"
                               class="w-2/3 px-4 py-2.5 bg-slate-100 dark:bg-slate-700 border-0 rounded-xl text-slate-800 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-emerald-500/30">
                    </div>
                </div>

                <!-- Teléfono -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Teléfono</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            ' . Icons::get('phone', 'w-4 h-4 text-slate-400') . '
                        </div>
                        <input type="text" id="cliente_telefono" name="telefono" placeholder="0424-0000000"
                               class="w-full pl-10 pr-4 py-2.5 bg-slate-100 dark:bg-slate-700 border-0 rounded-xl text-slate-800 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-emerald-500/30">
                    </div>
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Email</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            ' . Icons::get('mail', 'w-4 h-4 text-slate-400') . '
                        </div>
                        <input type="email" id="cliente_email" name="email" placeholder="cliente@email.com"
                               class="w-full pl-10 pr-4 py-2.5 bg-slate-100 dark:bg-slate-700 border-0 rounded-xl text-slate-800 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-emerald-500/30">
                    </div>
                </div>

                <!-- Dirección -->
                <div class="col-span-1 md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Dirección</label>
                    <textarea id="cliente_direccion" name="direccion" rows="2" placeholder="Dirección completa..."
                               class="w-full px-4 py-2.5 bg-slate-100 dark:bg-slate-700 border-0 rounded-xl text-slate-800 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-emerald-500/30 resize-none"></textarea>
                </div>

                <!-- Límite Crédito -->
                <div class="col-span-1 md:col-span-2 bg-indigo-50 dark:bg-indigo-900/20 p-4 rounded-xl border border-indigo-100 dark:border-indigo-800">
                     <label class="block text-sm font-semibold text-indigo-700 dark:text-indigo-400 mb-1.5 flex items-center gap-2">
                        ' . Icons::get('dollar', 'w-4 h-4') . ' Límite de Crédito (USD)
                     </label>
                     <input type="number" id="cliente_limite_credito" name="limite_credito" step="0.01" min="0" value="0.00"
                            class="w-full px-4 py-2.5 bg-white dark:bg-slate-600 border border-indigo-200 dark:border-indigo-700 rounded-xl text-slate-800 dark:text-white font-bold text-lg focus:ring-2 focus:ring-emerald-500/30">
                     <p class="text-xs text-indigo-500/80 dark:text-indigo-400/80 mt-1">Monto máximo permitido para ventas a crédito.</p>
                </div>
            </div>
        </form>
    '),
    'footer' => View::raw('
        <div class="flex justify-end gap-3 w-full sm:w-auto">
            ' . View::component('button', [
                'label' => 'Cancelar',
                'variant' => 'outline',
                'attributes' => 'type="button" onclick="cerrarModalCliente()"',
                'class' => 'flex-1 sm:flex-none'
            ]) . '
            ' . View::component('button', [
                'label' => 'Guardar Cliente',
                'variant' => 'primary',
                'attributes' => 'form="form-cliente" type="submit"',
                'class' => 'flex-1 sm:flex-none'
            ]) . '
        </div>
    ')
]);
?>

<!-- Módulo de Clientes (cargado desde archivo externo) -->
<script src="<?= BASE_URL ?>js/pages/clientes.js?v=<?= time() ?>"></script>
