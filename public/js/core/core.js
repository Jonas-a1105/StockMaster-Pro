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
// TOAST NOTIFICATIONS (Delegated to notifications.js)
// =========================================================================
// Las notificaciones ahora se gestionan en public/js/core/notifications.js
// para evitar duplicidad de código y centralizar los estilos premium.

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
        const data = await Endpoints.footerStats();

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
