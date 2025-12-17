/**
 * =========================================================================
 * CLIENTES.JS - Módulo de Gestión de Clientes
 * =========================================================================
 */

console.log('[Clientes] Módulo cargando...');

// =========================================================================
// MODAL - ABRIR
// =========================================================================
function abrirModalCliente(clienteData = null) {
    const modal = document.getElementById('modal-cliente');
    const backdrop = document.getElementById('modal-backdrop-cliente');
    const panel = document.getElementById('modal-panel-cliente');
    const form = document.getElementById('form-cliente');

    if (!modal || !form) return;

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
        backdrop?.classList.remove('opacity-0');
        panel?.classList.remove('opacity-0', 'translate-y-4', 'sm:translate-y-0', 'sm:scale-95');
    }, 10);
}

// =========================================================================
// MODAL - CERRAR
// =========================================================================
function cerrarModalCliente() {
    const modal = document.getElementById('modal-cliente');
    const backdrop = document.getElementById('modal-backdrop-cliente');
    const panel = document.getElementById('modal-panel-cliente');

    if (!modal) return;

    backdrop?.classList.add('opacity-0');
    panel?.classList.add('opacity-0', 'translate-y-4', 'sm:translate-y-0', 'sm:scale-95');

    setTimeout(() => {
        modal.classList.add('hidden');
    }, 300);
}

// =========================================================================
// GUARDAR CLIENTE
// =========================================================================
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
    btnSubmit.innerHTML = `
        <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        Guardando...
    `;
    btnSubmit.classList.add('flex', 'items-center', 'justify-center');

    fetch('index.php?controlador=cliente&accion=' + accion, {
        method: 'POST',
        body: formData
    })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                if (typeof showToast === 'function') showToast(data.message, 'success');

                // Check context (POS or Index)
                if (document.getElementById('pos-buscador')) {
                    // POS Context
                    cerrarModalCliente();
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
                    setTimeout(() => window.location.reload(), 1000);
                }
            } else {
                if (typeof showToast === 'function') showToast('Error: ' + data.message, 'error');
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = originalContent;
                btnSubmit.classList.remove('flex', 'items-center', 'justify-center');
            }
        })
        .catch(err => {
            console.error(err);
            if (typeof showToast === 'function') showToast('Error de conexión', 'error');
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = originalContent;
            btnSubmit.classList.remove('flex', 'items-center', 'justify-center');
        });
}

// Bridge para editar desde index
function editarCliente(cliente) {
    if (typeof abrirModalCliente === 'function') {
        abrirModalCliente(cliente);
    }
}

// =========================================================================
// EXPORTAR AL SCOPE GLOBAL
// =========================================================================
window.abrirModalCliente = abrirModalCliente;
window.cerrarModalCliente = cerrarModalCliente;
window.guardarCliente = guardarCliente;
window.editarCliente = editarCliente;
