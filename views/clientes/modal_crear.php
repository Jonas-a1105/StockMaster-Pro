<?php use App\Helpers\Icons; ?>
<!-- Modal Cliente Shared Component -->
<!-- Modal Cliente Shared Component -->
<div id="modal-cliente" class="fixed inset-0 z-[100] hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <!-- Backdrop, usando onclick para cerrar también -->
    <!-- Backdrop, usando onclick para cerrar también -->
    <div class="fixed inset-0 bg-slate-200/50 dark:bg-slate-900/50 backdrop-blur-sm transition-opacity opacity-0" id="modal-backdrop-cliente"></div>

    <!-- Panel Wrapper: Centrado y scroll -->
    <div class="fixed inset-0 flex items-center justify-center p-4 overflow-y-auto" onclick="if(event.target === this) cerrarModalCliente()">
        <!-- Panel Content -->
        <div class="relative w-full max-w-2xl bg-white dark:bg-slate-800 rounded-2xl shadow-xl transform transition-all opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" id="modal-panel-cliente">
                
                <!-- Header -->
                <div class="bg-white dark:bg-slate-800 rounded-t-2xl px-4 pb-4 pt-5 sm:p-6 sm:pb-4 border-b border-slate-100 dark:border-slate-700">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-900/30">
                                <?= Icons::get('user-plus', 'w-6 h-6 text-emerald-600 dark:text-emerald-400') ?>
                            </div>
                            <h3 class="text-xl font-bold leading-6 text-slate-900 dark:text-white" id="modal-cliente-titulo">Nuevo Cliente</h3>
                        </div>
                        <button onclick="cerrarModalCliente()" class="text-slate-400 hover:text-slate-500 transition-colors">
                            <?= Icons::get('x', 'w-6 h-6') ?>
                        </button>
                    </div>
                </div>

                <!-- Form -->
                <form id="form-cliente" onsubmit="guardarCliente(event)" class="px-6 py-6 space-y-5">
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
                                    <?= Icons::get('phone', 'w-4 h-4 text-slate-400') ?>
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
                                    <?= Icons::get('mail', 'w-4 h-4 text-slate-400') ?>
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
                                <?= Icons::get('dollar', 'w-4 h-4') ?> Límite de Crédito (USD)
                             </label>
                             <input type="number" id="cliente_limite_credito" name="limite_credito" step="0.01" min="0" value="0.00"
                                    class="w-full px-4 py-2.5 bg-white dark:bg-slate-600 border border-indigo-200 dark:border-indigo-700 rounded-xl text-slate-800 dark:text-white font-bold text-lg focus:ring-2 focus:ring-emerald-500/30">
                             <p class="text-xs text-indigo-500/80 dark:text-indigo-400/80 mt-1">Monto máximo permitido para ventas a crédito.</p>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="flex justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-700">
                        <button type="button" onclick="cerrarModalCliente()" class="px-4 py-2.5 text-slate-600 dark:text-slate-300 font-medium hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition-colors">
                            Cancelar
                        </button>
                        <button type="submit" class="flex items-center gap-2 px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold shadow-lg shadow-emerald-500/30 transition-all">
                            <?= Icons::get('save', 'w-5 h-5') ?>
                            Guardar Cliente
                        </button>
                    </div>
                </form>
            </div>
        </div>
</div>

<!-- Módulo de Clientes (cargado desde archivo externo) -->
<script src="<?= BASE_URL ?>js/pages/clientes.js?v=<?= time() ?>"></script>
