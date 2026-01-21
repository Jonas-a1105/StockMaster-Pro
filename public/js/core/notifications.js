/**
 * =========================================================================
 * NOTIFICATIONS.JS - Sistema de Notificaciones (Modern Toasts)
 * =========================================================================
 * Notificaciones tipo toast con diseño moderno basado en el componente Alert
 */

// Iconos SVG basados en el Componente Alert / Heroicons
const NotificationIcons = {
    success: '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
    error: '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
    warning: '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>',
    info: '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
    close: '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>'
};

// Configuración de estilos por tipo (Clases Tailwind)
const NotificationStyles = {
    success: 'bg-emerald-50 border-emerald-200 text-emerald-800 dark:bg-emerald-900/20 dark:border-emerald-800 dark:text-emerald-400',
    error: 'bg-rose-50 border-rose-200 text-rose-800 dark:bg-rose-900/20 dark:border-rose-800 dark:text-rose-400',
    warning: 'bg-amber-50 border-amber-200 text-amber-800 dark:bg-amber-900/20 dark:border-amber-800 dark:text-amber-400',
    info: 'bg-blue-50 border-blue-200 text-blue-800 dark:bg-blue-900/20 dark:border-blue-800 dark:text-blue-400'
};

/**
 * Muestra una notificación Toast Flotante
 * @param {string} message - Mensaje a mostrar
 * @param {string} type - 'success' | 'error' | 'warning' | 'info'
 * @param {string} title - Título (opcional)
 */
function mostrarNotificacion(message, type = 'success', title = null) {
    // Buscar o crear contenedor (top-right debajo del navbar)
    let container = document.getElementById('toast-container-main');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container-main';
        container.className = 'fixed top-20 right-4 z-[200] space-y-3 w-full max-w-sm pointer-events-none';
        document.body.appendChild(container);
    }


    const typeStyle = NotificationStyles[type] || NotificationStyles.info;
    const icon = NotificationIcons[type] || NotificationIcons.info;

    // Crear elemento toast
    const toast = document.createElement('div');
    toast.className = `pointer-events-auto flex items-center p-4 border rounded-2xl shadow-xl glass transition-all duration-500 scale-95 opacity-0 translate-y-[-10px] ${typeStyle}`;

    toast.innerHTML = `
        <div class="flex-shrink-0">
            ${icon}
        </div>
        <div class="ml-3 mr-8 flex-1">
            ${title ? `<p class="text-sm font-bold leading-tight mb-0.5">${title}</p>` : ''}
            <p class="text-xs font-medium">${message}</p>
        </div>
        <button type="button" class="ml-auto -mx-1.5 -my-1.5 p-1.5 inline-flex h-8 w-8 rounded-lg opacity-50 hover:opacity-100 transition-opacity" 
                onclick="this.parentElement.remove()" aria-label="Close">
            ${NotificationIcons.close}
        </button>
    `;

    container.appendChild(toast);

    // Animación de entrada
    requestAnimationFrame(() => {
        toast.classList.remove('scale-95', 'opacity-0', 'translate-y-[-10px]');
    });

    // Auto-eliminar después de 4.5 segundos
    const timeout = setTimeout(() => {
        removeToast(toast);
    }, 4500);

    // Permitir clic para cerrar
    toast.addEventListener('click', () => {
        clearTimeout(timeout);
        removeToast(toast);
    });
}

function removeToast(toast) {
    toast.classList.add('scale-95', 'opacity-0', 'translate-y-[-10px]');
    setTimeout(() => toast.remove(), 500);
}

/**
 * Busca mensajes flash en el DOM (inyectados por PHP) y los muestra
 */
function initFlashNotifications() {
    const flashData = document.getElementById('flash-data');
    if (flashData) {
        const msg = flashData.dataset.message;
        const type = flashData.dataset.type || 'success';
        if (msg) {
            // Un pequeño retardo para que la carga de la página se asiente
            setTimeout(() => mostrarNotificacion(msg, type), 300);
            flashData.remove();
        }
    }
}

/**
 * Inicializa el dropdown del centro de notificaciones (en Navbar)
 */
function inicializarCentroNotificaciones() {
    const btnNotif = document.getElementById('btn-notificaciones');
    const dropdownNotif = document.getElementById('notif-dropdown');

    if (!btnNotif || !dropdownNotif) return;

    btnNotif.addEventListener('click', (e) => {
        e.stopPropagation();
        dropdownNotif.classList.toggle('hidden');
        if (!dropdownNotif.classList.contains('hidden')) {
            dropdownNotif.classList.add('animate-in', 'fade-in', 'zoom-in-95');
        }
    });

    document.addEventListener('click', (e) => {
        if (!dropdownNotif.contains(e.target) && !btnNotif.contains(e.target)) {
            dropdownNotif.classList.add('hidden');
        }
    });

    // Marcar todas como leídas
    const btnMarcarTodas = document.getElementById('marcar-todas-leidas');
    if (btnMarcarTodas) {
        btnMarcarTodas.addEventListener('click', async (e) => {
            e.preventDefault();
            try {
                await Endpoints.marcarTodasLeidas();
                document.querySelectorAll('.notif-badge').forEach(b => b.remove());
                const body = document.querySelector('.notif-body');
                if (body) {
                    body.innerHTML = `
                        <div class="notif-empty text-center py-10">
                            <div class="w-12 h-12 bg-emerald-100 dark:bg-emerald-900/30 rounded-full flex items-center justify-center mx-auto mb-3">
                                ${NotificationIcons.success}
                            </div>
                            <p class="text-sm font-medium text-slate-500 underline-offset-4">Todo leído. ¡Buen trabajo!</p>
                        </div>
                    `;
                }
            } catch (e) {
                console.error('Error marcando notificaciones:', e);
            }
        });
    }
}

// =========================================================================
// EXPORTACIÓN ÚNICA AL SCOPE GLOBAL
// =========================================================================
window.Notifications = {
    show: mostrarNotificacion,
    flash: mostrarNotificacion,
    initFlash: initFlashNotifications,
    initCenter: inicializarCentroNotificaciones
};

// Compatibilidad con código existente
window.mostrarNotificacion = mostrarNotificacion;
window.showToast = mostrarNotificacion;

/**
 * Inicialización al cargar el documento
 */
document.addEventListener('DOMContentLoaded', () => {
    Notifications.initFlash();
    Notifications.initCenter();
});
