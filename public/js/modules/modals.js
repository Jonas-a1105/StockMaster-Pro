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

        modal.classList.remove('hidden');
        // Asegurar que el display sea flex para centrado (Tailwind 'hidden' usa display:none)
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

/**
 * Cierra un modal por ID
 * @param {string} modalId - ID del modal
 */
function cerrarModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
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

    // Cerrar al hacer click fuera del contenido
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.style.display = 'none';
            }
        });
    });

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
