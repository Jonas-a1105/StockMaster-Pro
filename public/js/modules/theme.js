/**
 * =========================================================================
 * THEME.JS - Toggle de Tema Oscuro/Claro
 * =========================================================================
 */

/**
 * Inicializa el sistema de temas
 */
function inicializarTema() {
    const html = document.documentElement;
    const themeToggle = document.getElementById('btn-theme-toggle');
    const themeIcon = document.getElementById('theme-icon');

    // Cargar tema guardado o usar preferencia del sistema
    const savedTheme = localStorage.getItem('theme');
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    const currentTheme = savedTheme || (prefersDark ? 'dark' : 'light');

    // Aplicar tema inicial
    html.setAttribute('data-theme', currentTheme);
    actualizarIcono(currentTheme);

    // Toggle de tema
    if (themeToggle) {
        themeToggle.addEventListener('click', () => {
            const isDark = html.getAttribute('data-theme') === 'dark';
            const newTheme = isDark ? 'light' : 'dark';

            html.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            actualizarIcono(newTheme);

            // Feedback visual
            if (typeof mostrarNotificacion === 'function') {
                mostrarNotificacion(`Tema ${newTheme === 'dark' ? 'oscuro' : 'claro'} activado`, 'success');
            }
        });
    }

    function actualizarIcono(theme) {
        if (themeIcon) {
            themeIcon.className = theme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
        }
    }
}

/**
 * Obtiene el tema actual
 * @returns {string} 'dark' o 'light'
 */
function getTemaActual() {
    return document.documentElement.getAttribute('data-theme') || 'light';
}

/**
 * Establece el tema
 * @param {string} theme - 'dark' o 'light'
 */
function setTema(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem('theme', theme);
}

// Exportar al scope global
window.Theme = {
    init: inicializarTema,
    get: getTemaActual,
    set: setTema
};
