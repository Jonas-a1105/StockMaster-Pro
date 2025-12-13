<?php use App\Helpers\Icons; ?>
<!-- Modal Cliente Shared Component -->
<div id="modal-cliente" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity opacity-0" id="modal-backdrop-cliente"></div>

    <!-- Panel -->
    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-800 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" id="modal-panel-cliente">
                
                <!-- Header -->
                <div class="bg-white dark:bg-slate-800 px-4 pb-4 pt-5 sm:p-6 sm:pb-4 border-b border-slate-100 dark:border-slate-700">
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
                            <select id="cliente_tipo_cliente" name="tipo_cliente" class="w-full px-4 py-2.5 bg-slate-100 dark:bg-slate-700 border-0 rounded-xl text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500/30">
                                <option value="Natural">Persona Natural</option>
                                <option value="Juridico">Empresa/Jurídico</option>
                            </select>
                        </div>

                        <!-- Tipo Documento -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Tipo Documento</label>
                            <div class="flex gap-2">
                                <select id="cliente_tipo_documento" name="tipo_documento" class="w-1/3 px-2 py-2.5 bg-slate-100 dark:bg-slate-700 border-0 rounded-xl text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500/30">
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
</div>

<script>
// Modal Logic
function abrirModalCliente(clienteData = null) {
    const modal = document.getElementById('modal-cliente');
    const backdrop = document.getElementById('modal-backdrop-cliente');
    const panel = document.getElementById('modal-panel-cliente');
    const form = document.getElementById('form-cliente');
    
    // Reset Form
    form.reset();
    document.getElementById('cliente_id').value = '';
    
    // Set Data if editing
    if (clienteData && clienteData.id) {
        document.getElementById('modal-cliente-titulo').textContent = 'Editar Cliente';
        document.getElementById('cliente_id').value = clienteData.id;
        document.getElementById('cliente_nombre').value = clienteData.nombre || '';
        document.getElementById('cliente_tipo_cliente').value = clienteData.tipo_cliente || 'Natural';
        document.getElementById('cliente_tipo_documento').value = clienteData.tipo_documento || 'CI';
        document.getElementById('cliente_numero_documento').value = clienteData.numero_documento || '';
        document.getElementById('cliente_telefono').value = clienteData.telefono || '';
        document.getElementById('cliente_email').value = clienteData.email || '';
        document.getElementById('cliente_direccion').value = clienteData.direccion || '';
        document.getElementById('cliente_limite_credito').value = clienteData.limite_credito || '0.00';
    } else {
        document.getElementById('modal-cliente-titulo').textContent = 'Nuevo Cliente';
        document.getElementById('cliente_limite_credito').value = '0.00';
    }
    
    // Show Modal
    modal.classList.remove('hidden');
    setTimeout(() => {
        backdrop.classList.remove('opacity-0');
        panel.classList.remove('opacity-0', 'translate-y-4', 'sm:translate-y-0', 'sm:scale-95');
    }, 10);
}

function cerrarModalCliente() {
    const modal = document.getElementById('modal-cliente');
    const backdrop = document.getElementById('modal-backdrop-cliente');
    const panel = document.getElementById('modal-panel-cliente');
    
    backdrop.classList.add('opacity-0');
    panel.classList.add('opacity-0', 'translate-y-4', 'sm:translate-y-0', 'sm:scale-95');
    
    setTimeout(() => {
        modal.classList.add('hidden');
    }, 300);
}

function guardarCliente(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    const clienteId = document.getElementById('cliente_id').value;
    const accion = clienteId ? 'actualizar' : 'guardar';
    
    // UI Loading
    const btnSubmit = form.querySelector('button[type="submit"]');
    const originalContent = btnSubmit.innerHTML;
    btnSubmit.disabled = true;
    btnSubmit.innerHTML = '<svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Guardando...';

    fetch('index.php?controlador=cliente&accion=' + accion, {
        method: 'POST', 
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if(data.success) {
            showToast(data.message, 'success');
            cerrarModalCliente();
            
            // Check context (POS or Index)
            if (document.getElementById('pos-buscador')) {
                // POS Context
                if (!clienteId && data.clienteId && window.seleccionarCliente) {
                    window.seleccionarCliente({
                        id: data.clienteId,
                        nombre: formData.get('nombre'),
                        documento: formData.get('numero_documento'),
                        credito: formData.get('limite_credito')
                    });
                }
            } else {
                // Index Context - Reload to show changes
                setTimeout(() => window.location.reload(), 500);
            }
        } else {
            showToast('Error: ' + data.message, 'error');
        }
    })
    .catch(err => {
        console.error(err);
        showToast('Error de conexión', 'error');
    })
    .finally(() => {
        btnSubmit.disabled = false;
        btnSubmit.innerHTML = originalContent;
    });
}
</script>
