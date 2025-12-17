/**
 * =========================================================================
 * NOTIFICATIONS.JS - Sistema de Notificaciones (Modern Toasts)
 * =========================================================================
 * Notificaciones tipo toast con diseño moderno y soporte para dark mode
 */

// Iconos SVG para los diferentes tipos de notificación
const NotificationIcons = {
    success: '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>',
    error: '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>',
    warning: '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>',
    close: '<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>'
};

// Configuración de estilos por tipo
const NotificationStyles = {
    success: { bg: 'bg-emerald-100', text: 'text-emerald-600', border: 'border-emerald-100', shadow: 'shadow-emerald-500/10' },
    error: { bg: 'bg-red-100', text: 'text-red-600', border: 'border-red-100', shadow: 'shadow-red-500/10' },
    warning: { bg: 'bg-amber-100', text: 'text-amber-600', border: 'border-amber-100', shadow: 'shadow-amber-500/10' }
};

/**
 * Muestra una notificación Toast Flotante
 * @param {string} message - Mensaje a mostrar
 * @param {string} type - 'success' | 'error' | 'warning'
 * @param {string} title - Título (opcional)
 */
function mostrarNotificacion(message, type = 'success', title = null) {
    // Eliminar toast anterior si existe
    const existingToast = document.getElementById('toast-notification');
    if (existingToast) existingToast.remove();

    // Obtener estilos y título
    const style = NotificationStyles[type] || NotificationStyles.success;
    const defaultTitle = type === 'error' ? 'Error' : (type === 'warning' ? 'Advertencia' : 'Éxito');
    const finalTitle = title || defaultTitle;

    // Crear elemento toast
    const toast = document.createElement('div');
    toast.id = 'toast-notification';
    toast.className = 'fixed top-4 left-1/2 -translate-x-1/2 z-[100] animate-in fade-in slide-in-from-top-4 duration-500';

    toast.innerHTML = `
        <div class="bg-white dark:bg-slate-800 border ${style.border} dark:border-slate-700 shadow-lg ${style.shadow} rounded-xl px-4 py-3 flex items-center gap-3 min-w-[320px] max-w-[90vw]">
            <div class="${style.bg} dark:bg-opacity-20 p-1.5 rounded-full ${style.text} flex-shrink-0">
                ${NotificationIcons[type] || NotificationIcons.success}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-bold text-slate-800 dark:text-white">${finalTitle}</p>
                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium break-words">${message}</p>
            </div>
            <button class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 ml-2 transition-colors p-1 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg" onclick="this.closest('#toast-notification').remove()">
                ${NotificationIcons.close}
            </button>
        </div>
    `;

    document.body.appendChild(toast);

    // Auto-eliminar después de 4 segundos
    setTimeout(() => {
        if (toast && document.body.contains(toast)) {
            toast.style.opacity = '0';
            toast.style.transform = 'translate(-50%, -1rem)';
            toast.style.transition = 'all 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }
    }, 4000);
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
            mostrarNotificacion(msg, type);
            // CRITICAL: Remove the element to prevent Turbo Cache from saving it
            // and showing the notification again on page restore/navigation.
            flashData.remove();
        }
    }
}

/**
 * Inicializa el dropdown del centro de notificaciones
 */
function inicializarCentroNotificaciones() {
    const btnNotif = document.getElementById('btn-notificaciones');
    const dropdownNotif = document.getElementById('notif-dropdown');

    if (!btnNotif || !dropdownNotif) return;

    btnNotif.addEventListener('click', (e) => {
        e.stopPropagation();
        dropdownNotif.classList.toggle('show');
    });

    document.addEventListener('click', (e) => {
        if (!dropdownNotif.contains(e.target) && e.target !== btnNotif) {
            dropdownNotif.classList.remove('show');
        }
    });

    // Marcar todas como leídas
    const btnMarcarTodas = document.getElementById('marcar-todas-leidas');
    if (btnMarcarTodas) {
        btnMarcarTodas.addEventListener('click', async (e) => {
            e.preventDefault();
            try {
                await fetch('index.php?controlador=notificacion&accion=marcarTodasLeidas');
                document.querySelectorAll('.notif-badge').forEach(b => b.remove());
                const body = document.querySelector('.notif-body');
                if (body) {
                    body.innerHTML = `
                        <div class="notif-empty text-center py-8">
                            <svg class="w-12 h-12 mx-auto text-emerald-500 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <p class="text-slate-500">No hay notificaciones nuevas</p>
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
