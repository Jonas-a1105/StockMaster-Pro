/**
 * =========================================================================
 * PROVEEDORES.JS - Módulo de Gestión de Proveedores
 * =========================================================================
 */

console.log('[Proveedores] Módulo cargando...');

// =========================================================================
// MODAL DE EDICIÓN
// =========================================================================
function toggleModal(show) {
    if (show) {
        if (typeof openModal === 'function') {
            openModal('modal-editar-proveedor');
        }
    } else {
        if (typeof closeModal === 'function') {
            closeModal('modal-editar-proveedor');
        }
    }
}

function editarProveedor(p) {
    document.getElementById('editar-prov-id').value = p.id;
    document.getElementById('editar-prov-nombre').value = p.nombre;
    document.getElementById('editar-prov-contacto').value = p.contacto || '';
    document.getElementById('editar-prov-telefono').value = p.telefono || '';
    document.getElementById('editar-prov-email').value = p.email || '';

    toggleModal(true);
}

function confirmarEliminar(id) {
    const modal = document.getElementById('modal-confirmar-eliminar');
    const btnConfirmar = document.getElementById('btn-confirmar-eliminar');

    if (btnConfirmar) {
        btnConfirmar.onclick = () => {
            document.getElementById('form-eliminar-' + id).submit();
        };
    }

    if (typeof openModal === 'function') {
        openModal('modal-confirmar-eliminar');
    } else if (modal) {
        modal.classList.remove('hidden');
    }
}

function cerrarModalGlobal(id) {
    const modal = document.getElementById(id);
    if (modal) modal.classList.add('hidden');
}

// =========================================================================
// INICIALIZACIÓN
// =========================================================================
function initProveedores() {
    console.log('[Proveedores] Inicializando...');

    if (!document.getElementById('modal-editar-proveedor')) {
        console.log('[Proveedores] No es página de proveedores, saltando');
        return;
    }

    // Cerrar modal con botón cancelar
    document.getElementById('cancelar-modal-proveedor')?.addEventListener('click', () => toggleModal(false));

    // Submit del form de edición
    document.getElementById('form-editar-proveedor')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = e.target.querySelector('button[type="submit"]');
        const originalText = btn.textContent;

        try {
            btn.disabled = true;
            btn.textContent = 'Guardando...';

            const formData = new FormData(e.target);

            await Endpoints.actualizarProveedor(formData);

            if (typeof showToast === 'function') showToast('Proveedor actualizado', 'success');
            setTimeout(() => window.location.reload(), 800);

        } catch (error) {
            console.error(error);
            if (typeof showToast === 'function') {
                showToast('Error al guardar', 'error');
            } else {
                alert('Error al guardar');
            }
        } finally {
            // Re-habilitar solo si no hubo éxito y recarga pendiente (aunque reload detendrá el script)
            // Se deja por seguridad si la recarga falla o es lenta
            if (btn) {
                btn.disabled = false;
                btn.textContent = originalText;
            }
        }
    });

    console.log('[Proveedores] Módulo inicializado ✓');
}

// Exportar al scope global
window.Proveedores = {
    init: initProveedores,
    toggleModal: toggleModal,
    editar: editarProveedor,
    confirmarEliminar: confirmarEliminar
};
window.inicializarProveedores = initProveedores;
window.editarProveedor = editarProveedor;
window.confirmarEliminar = confirmarEliminar;
window.cerrarModalGlobal = cerrarModalGlobal;
window.toggleModal = toggleModal;
