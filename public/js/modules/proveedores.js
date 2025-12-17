/**
 * =========================================================================
 * PROVEEDORES.JS - Módulo de Gestión de Proveedores
 * =========================================================================
 */

function inicializarProveedores() {
    const tablaProveedores = document.getElementById('tabla-proveedores');
    const modalEditar = document.getElementById('modal-editar-proveedor');
    const formEditar = document.getElementById('form-editar-proveedor');
    const btnCerrar = document.getElementById('cerrar-modal-proveedor');
    const btnCancel = document.getElementById('cancelar-modal-proveedor');

    if (!tablaProveedores) return;

    // Click en editar
    tablaProveedores.addEventListener('click', async (e) => {
        const btn = e.target.closest('.btn-editar-proveedor');
        if (!btn) return;

        e.preventDefault();
        const id = btn.dataset.id;

        try {
            const res = await fetch(`index.php?controlador=proveedor&accion=apiObtener&id=${id}`);
            const d = await res.json();
            if (d.error) throw new Error(d.error);

            formEditar.querySelector('#editar-prov-id').value = d.id;
            formEditar.querySelector('#editar-prov-nombre').value = d.nombre;
            formEditar.querySelector('#editar-prov-contacto').value = d.contacto;
            formEditar.querySelector('#editar-prov-telefono').value = d.telefono;
            formEditar.querySelector('#editar-prov-email').value = d.email;

            if (modalEditar) modalEditar.style.display = 'block';
        } catch (e) {
            mostrarNotificacion(e.message, 'error');
        }
    });

    // Cerrar modal
    if (btnCerrar) btnCerrar.onclick = () => { if (modalEditar) modalEditar.style.display = 'none'; };
    if (btnCancel) btnCancel.onclick = () => { if (modalEditar) modalEditar.style.display = 'none'; };

    // Submit formulario
    if (formEditar) {
        formEditar.addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = formEditar.querySelector('button[type="submit"]');
            const originalText = btn.innerHTML;

            try {
                btn.disabled = true;
                btn.innerHTML = `
                    <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Guardando...
                `;
                btn.classList.add('flex', 'items-center', 'justify-center');

                const res = await fetch('index.php?controlador=proveedor&accion=actualizar', {
                    method: 'POST',
                    body: new FormData(formEditar)
                });
                const d = await res.json();
                if (d.success) {
                    mostrarNotificacion('¡Proveedor actualizado correctamente!', 'success');
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    throw new Error(d.message);
                }
            } catch (err) {
                mostrarNotificacion(err.message, 'error');
                btn.disabled = false;
                btn.innerHTML = originalText;
                btn.classList.remove('flex', 'items-center', 'justify-center');
            }
        });
    }
}

// Exponer globalmente
window.Proveedores = {
    init: inicializarProveedores
};
window.inicializarProveedores = inicializarProveedores;
