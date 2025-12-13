/**
 * =========================================================================
 * NOTIFICATIONS.JS - Sistema de Notificaciones (Modern Toasts)
 * =========================================================================
 */

const Icons = {
    success: '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>',
    error: '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>',
    warning: '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>',
    x: '<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>'
};

/**
 * Muestra una notificación Toast Flotante
 * @param {string} message - Mensaje a mostrar
 * @param {string} type - 'success' | 'error' | 'warning'
 * @param {string} title - Título (opcional)
 */
function showToast(message, type = 'success', title = null) {
    // 1. Eliminar toasts anteriores si se desea evitar acaparamientos, o dejar apilar.
    // Para simplificar y seguir el diseño 'fixed top-4', removemos el anterior si existe en la misma posición.
    const existingToast = document.getElementById('toast-notification');
    if (existingToast) existingToast.remove();

    // 2. Configuración por tipo
    const config = {
        success: { bg: 'bg-emerald-100', text: 'text-emerald-600', border: 'border-emerald-100', shadow: 'shadow-emerald-500/10' },
        error: { bg: 'bg-red-100', text: 'text-red-600', border: 'border-red-100', shadow: 'shadow-red-500/10' },
        warning: { bg: 'bg-amber-100', text: 'text-amber-600', border: 'border-amber-100', shadow: 'shadow-amber-500/10' }
    };
    const style = config[type] || config.success;
    const defaultTitle = type === 'error' ? 'Error' : (type === 'warning' ? 'Advertencia' : 'Éxito');
    const finalTitle = title || defaultTitle;

    // 3. Crear Estructura HTML
    const toast = document.createElement('div');
    toast.id = 'toast-notification';
    toast.className = `fixed top-4 left-1/2 -translate-x-1/2 z-[100] animate-in fade-in slide-in-from-top-4 duration-500 toast-center`;

    toast.innerHTML = `
        <div class="bg-white border ${style.border} shadow-lg ${style.shadow} rounded-xl px-4 py-3 flex items-center gap-3 min-w-[320px] max-w-[90vw]">
            <!-- Icono -->
            <div class="${style.bg} p-1.5 rounded-full ${style.text} flex-shrink-0">
                ${Icons[type] || Icons.success}
            </div>
            
            <!-- Texto -->
            <div class="flex-1 min-w-0">
                <p class="text-sm font-bold text-slate-800">${finalTitle}</p>
                <p class="text-xs text-slate-500 font-medium break-words">${message}</p>
            </div>
            
            <!-- Botón Cerrar -->
            <button class="text-slate-400 hover:text-slate-600 ml-2 transition-colors p-1 hover:bg-slate-100 rounded-lg" onclick="this.closest('#toast-notification').remove()">
                ${Icons.x}
            </button>
        </div>
    `;

    document.body.appendChild(toast);

    // 4. Auto-eliminar
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
    // Buscar inputs hidden o elementos data con mensajes flash
    const flashData = document.getElementById('flash-data');
    if (flashData) {
        const msg = flashData.dataset.message;
        const type = flashData.dataset.type;
        if (msg) showToast(msg, type);
    }
}

// Exportar
window.Notifications = {
    show: showToast,
    flash: showToast, // Alias
    initFlash: initFlashNotifications,
    initCenter: () => { } // Placeholder si se requiere compatibilidad
};

// Compatibilidad
window.mostrarNotificacion = showToast;

/**
 * Crea una notificación flash
 * @param {string} mensaje - Mensaje a mostrar
 * @param {string} tipo - Tipo: 'success', 'error', 'warning'
 */
function crearFlashNotification(mensaje, tipo = 'success') {
    const container = document.getElementById('flash-container');
    if (!container) return;

    const icons = {
        success: 'fa-check-circle',
        error: 'fa-times-circle',
        warning: 'fa-exclamation-triangle'
    };

    const flash = document.createElement('div');
    flash.className = `flash-notification flash-${tipo}`;
    flash.innerHTML = `
        <i class="fas ${icons[tipo] || icons.success}"></i>
        <span>${mensaje}</span>
    `;

    flash.onclick = () => {
        flash.classList.add('fade-out');
        setTimeout(() => flash.remove(), 400);
    };

    container.appendChild(flash);

    // Auto-remover después de 5 segundos
    setTimeout(() => {
        flash.classList.add('fade-out');
        setTimeout(() => flash.remove(), 400);
    }, 5000);
}

/**
 * Inicializa las notificaciones flash existentes
 */
function inicializarFlashNotifications() {
    const notifications = document.querySelectorAll('.flash-notification');
    notifications.forEach(notification => {
        setTimeout(() => {
            notification.classList.add('fade-out');
            setTimeout(() => notification.remove(), 400);
        }, 5000);
    });
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
                document.querySelector('.notif-body').innerHTML = `
                    <div class="notif-empty">
                        <i class="fas fa-check-circle" style="font-size: 2em; color: var(--success-color);"></i>
                        <p>No hay notificaciones nuevas</p>
                    </div>
                `;
            } catch (e) {
                console.error(e);
            }
        });
    }
}

// Exportar al scope global
window.Notifications = {
    show: mostrarNotificacion,
    flash: crearFlashNotification,
    initFlash: inicializarFlashNotifications,
    initCenter: inicializarCentroNotificaciones
};

// Compatibilidad con código existente
window.mostrarNotificacion = mostrarNotificacion;
