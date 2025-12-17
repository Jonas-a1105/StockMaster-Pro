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
    const modal = document.getElementById('modal-editar-proveedor');
    const backdrop = document.getElementById('modal-backdrop');
    const panel = document.getElementById('modal-panel');

    if (!modal || !backdrop || !panel) return;

    if (show) {
        modal.classList.remove('hidden');
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

            await fetch('index.php?controlador=proveedor&accion=editar', {
                method: 'POST',
                body: formData
            });

            window.location.reload();

        } catch (error) {
            console.error(error);
            if (typeof showToast === 'function') {
                showToast('Error al guardar', 'error');
            } else {
                alert('Error al guardar');
            }
            btn.disabled = false;
            btn.textContent = originalText;
        }
    });

    console.log('[Proveedores] Módulo inicializado ✓');
}

// =========================================================================
// EXPORTAR AL SCOPE GLOBAL
// =========================================================================
window.toggleModal = toggleModal;
window.editarProveedor = editarProveedor;
window.confirmarEliminar = confirmarEliminar;
window.cerrarModalGlobal = cerrarModalGlobal;

// Inicializar
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initProveedores);
} else {
    initProveedores();
}
