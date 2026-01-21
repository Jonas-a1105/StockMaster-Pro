/**
 * =========================================================================
 * MODALS.JS - Control de Modales
 * =========================================================================
 * Gestión centralizada de modales del sistema.
 */

// Variable para almacenar el formulario pendiente de eliminación
let formParaEliminar = null;

/**
 * Abre un modal por ID
 * @param {string} modalId - ID del modal
 */
function abrirModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        // Portal: Mover al body para evitar conflictos de z-index/overflow
        if (modal.parentNode !== document.body) {
            document.body.appendChild(modal);
        }

        // Agregar atributos ARIA para accesibilidad
        modal.setAttribute('role', 'dialog');
        modal.setAttribute('aria-modal', 'true');

        // Standardized Transitions (Backdrop & Panel)
        const backdrop = document.getElementById(`${modalId}-backdrop`);
        const panel = document.getElementById(`${modalId}-panel`);

        // IMPORTANT: Reset animation state BEFORE showing modal
        if (backdrop) backdrop.classList.add('opacity-0');
        if (panel) panel.classList.add('opacity-0', 'translate-y-4', 'sm:scale-95');

        // Initial Display
        modal.classList.remove('hidden');
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';

        // Trigger animations in next frame (after display is set)
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                if (backdrop) backdrop.classList.remove('opacity-0');
                if (panel) panel.classList.remove('opacity-0', 'translate-y-4', 'sm:scale-95');
            });
        });

        // Autofocus in the first input/textarea del modal
        setTimeout(() => {
            const firstInput = modal.querySelector('input:not([type="hidden"]), textarea, select');
            if (firstInput) {
                firstInput.focus();
            } else {
                // Si no hay inputs, enfocar el botón de cerrar o el modal mismo
                const closeBtn = modal.querySelector('button[aria-label="Close"], .close-btn');
                if (closeBtn) closeBtn.focus();
            }

            modal.dispatchEvent(new CustomEvent('modal:opened', { bubbles: true, detail: { modalId } }));
        }, 300); // Wait for transition
    }
}


/**
 * Cierra un modal por ID
 * @param {string} modalId - ID del modal
 */
function cerrarModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        const backdrop = document.getElementById(`${modalId}-backdrop`);
        const panel = document.getElementById(`${modalId}-panel`);

        if (backdrop) backdrop.classList.add('opacity-0');
        if (panel) panel.classList.add('opacity-0', 'translate-y-4', 'sm:translate-y-0', 'sm:scale-95');

        setTimeout(() => {
            modal.style.display = 'none';
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }, 300); // Match transition duration
    }
}

/**
 * Cierra todos los modales abiertos
 */
function cerrarTodosModales() {
    document.querySelectorAll('.modal').forEach(modal => {
        modal.style.display = 'none';
    });
    document.body.style.overflow = '';
}

/**
 * Configura modal de logout
 */
function configurarModalLogout() {
    const modalLogout = document.getElementById('modal-logout');
    const btnLogout = document.getElementById('btn-logout-confirm');
    const btnCancelLogout = document.getElementById('btn-cancel-logout');

    if (btnLogout) {
        btnLogout.onclick = (e) => {
            e.preventDefault();
            abrirModal('modal-logout');
        };
    }

    if (btnCancelLogout) {
        btnCancelLogout.onclick = () => cerrarModal('modal-logout');
    }
}

/**
 * Configura modal de confirmación de eliminación
 */
function configurarModalEliminar() {
    const modalDel = document.getElementById('modal-confirmar-eliminar');
    const btnDelConfirm = document.getElementById('btn-confirmar-eliminar');
    const btnDelClose = document.getElementById('cerrar-modal-eliminar');
    const btnDelCancel = document.getElementById('cancelar-modal-eliminar');

    const closeDel = () => {
        cerrarModal('modal-confirmar-eliminar');
        formParaEliminar = null;
    };

    if (btnDelClose) btnDelClose.onclick = closeDel;
    if (btnDelCancel) btnDelCancel.onclick = closeDel;

    if (btnDelConfirm) {
        btnDelConfirm.onclick = () => {
            if (formParaEliminar) formParaEliminar.submit();
            closeDel();
        };
    }

    // Interceptar formularios de eliminación
    document.addEventListener('submit', e => {
        if (e.target.tagName === 'FORM' && e.target.action.includes('accion=eliminar')) {
            e.preventDefault();
            formParaEliminar = e.target;
            abrirModal('modal-confirmar-eliminar');
        }
    });
}

/**
 * Configura cierre de modales con botón X y click outside
 */
function configurarCierreModales() {
    // Cerrar con botón X
    document.querySelectorAll('.close-btn').forEach(btn => {
        btn.onclick = () => {
            const modal = btn.closest('.modal');
            if (modal) modal.style.display = 'none';
        };
    });

    // NOTE: Click-outside close is handled by backdrop onclick in modal.php
    // Removed legacy .modal click handler to avoid conflicts with modal-wrapper structure

    // Cerrar con tecla ESC
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            cerrarTodosModales();
        }
    });
}

/**
 * Inicializa todos los modales
 */
function inicializarModales() {
    configurarModalLogout();
    configurarModalEliminar();
    configurarCierreModales();
}

// Exportar al scope global
window.Modals = {
    open: abrirModal,
    close: cerrarModal,
    closeAll: cerrarTodosModales,
    init: inicializarModales
};

// Compatibilidad
window.abrirModal = abrirModal;
window.cerrarModal = cerrarModal;
window.openModal = abrirModal;
window.closeModal = cerrarModal;
