/**
 * =========================================================================
 * CORE.JS - Módulo Core de UI (Tema, Dropdowns, Toast, Modals)
 * =========================================================================
 * Este módulo reemplaza el script inline en layouts/main.php
 */

console.log('[Core] Módulo cargando...');

// =========================================================================
// DROPDOWNS
// =========================================================================
function setupDropdown(btnId, dropdownId, containerId) {
    const btn = document.getElementById(btnId);
    const dropdown = document.getElementById(dropdownId);
    const container = document.getElementById(containerId);

    if (!btn || !dropdown) return;

    btn.addEventListener('click', (e) => {
        e.stopPropagation();
        dropdown.classList.toggle('hidden');
    });

    document.addEventListener('click', (e) => {
        if (container && !container.contains(e.target)) {
            dropdown.classList.add('hidden');
        }
    });
}

// =========================================================================
// TOAST NOTIFICATIONS
// =========================================================================
function showToast(message, type = 'success', duration = 4000) {
    const container = document.getElementById('toast-container');
    if (!container) {
        console.warn('[Core] Toast container not found');
        return;
    }

    const colors = {
        success: 'bg-emerald-500',
        error: 'bg-red-500',
        warning: 'bg-amber-500',
        info: 'bg-blue-500'
    };

    const toast = document.createElement('div');
    toast.className = `${colors[type] || colors.info} text-white px-4 py-3 rounded-xl shadow-lg flex items-center gap-3 slide-up`;
    toast.innerHTML = `
        <span class="flex-1 text-sm font-medium">${message}</span>
        <button onclick="this.parentElement.remove()" class="text-white/80 hover:text-white">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    `;

    container.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100%)';
        setTimeout(() => toast.remove(), 300);
    }, duration);
}

// Alias para compatibilidad
function mostrarNotificacion(msg, type) {
    showToast(msg, type === 'success' ? 'success' : (type === 'error' ? 'error' : 'warning'));
}

// =========================================================================
// TEMA (Dark/Light Mode)
// =========================================================================
function toggleTheme() {
    const html = document.documentElement;
    const isDark = html.classList.contains('dark');

    if (isDark) {
        html.classList.remove('dark');
        localStorage.setItem('theme', 'light');
    } else {
        html.classList.add('dark');
        localStorage.setItem('theme', 'dark');
    }

    updateThemeIcons();
}

function updateThemeIcons() {
    const isDark = document.documentElement.classList.contains('dark');
    document.getElementById('theme-icon-light')?.classList.toggle('hidden', isDark);
    document.getElementById('theme-icon-dark')?.classList.toggle('hidden', !isDark);
}

function initTheme() {
    const saved = localStorage.getItem('theme');
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

    if (saved === 'dark' || (!saved && prefersDark)) {
        document.documentElement.classList.add('dark');
    }

    updateThemeIcons();
}

// =========================================================================
// MENÚ MÓVIL
// =========================================================================
function toggleMobileMenu() {
    const menu = document.getElementById('mobile-menu');
    menu?.classList.toggle('hidden');
}

// =========================================================================
// INICIALIZACIÓN CORE
// =========================================================================
function initCore() {
    console.log('[Core] Inicializando...');

    // Tema
    initTheme();
    document.getElementById('btn-theme-toggle')?.addEventListener('click', toggleTheme);

    // Dropdowns
    setupDropdown('btn-notificaciones', 'notif-dropdown', 'notif-container');
    setupDropdown('btn-user-menu', 'user-dropdown', 'user-menu-container');

    // Menú móvil
    document.getElementById('btn-mobile-menu')?.addEventListener('click', toggleMobileMenu);

    // Logout
    document.getElementById('btn-logout-trigger')?.addEventListener('click', () => {
        if (typeof openModal === 'function') {
            openModal('modal-logout');
        }
    });

    // ESC para cerrar modales
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            document.querySelectorAll('[id^="modal-"]').forEach(m => {
                if (!m.classList.contains('hidden') && typeof closeModal === 'function') {
                    closeModal(m.id);
                }
            });
        }
    });

    // Stats Footer
    initFooterStats();

    console.log('[Core] Módulo inicializado ✓');
}

/**
 * Carga estadísticas para el footer (Valor Inventario)
 */
async function initFooterStats() {
    const footerInv = document.getElementById('footer-inventario');
    if (!footerInv) return;

    try {
        const res = await fetch('index.php?controlador=dashboard&accion=apiFooterStats');
        const data = await res.json();

        if (data.valor_inventario_usd !== undefined) {
            const valor = parseFloat(data.valor_inventario_usd);
            footerInv.textContent = `$${valor.toLocaleString('en-US', { minimumFractionDigits: 2 })}`;
        }

        // Si la tasa no se ha cargado por exchange-rate.js aún
        const footerTasa = document.getElementById('footer-tasa');
        if (footerTasa && footerTasa.textContent.includes('--') && data.tasa_registrada) {
            footerTasa.textContent = `Bs. ${parseFloat(data.tasa_registrada).toFixed(2)}`;
        }

    } catch (e) {
        console.warn('[Core] Error cargando footer stats:', e);
    }
}

// =========================================================================
// EXPORTAR AL SCOPE GLOBAL
// =========================================================================
window.setupDropdown = setupDropdown;
window.showToast = showToast;
window.mostrarNotificacion = mostrarNotificacion;
window.toggleTheme = toggleTheme;
window.toggleMobileMenu = toggleMobileMenu;

// Inicializar cuando el DOM esté listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initCore);
} else {
    initCore();
}
