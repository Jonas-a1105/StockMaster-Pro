/**
 * =========================================================================
 * UTILS.JS - Utilidades Core
 * =========================================================================
 * Funciones utilitarias globales para todo el sistema.
 */

/**
 * Escapa caracteres HTML para prevenir XSS
 * @param {string} str - Cadena a escapar
 * @returns {string} Cadena escapada
 */
function escapeHTML(str) {
    if (str === null || str === undefined) return '';
    return String(str).replace(/[&<>"']/g, function (m) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m];
    });
}

/**
 * Formatea un número como moneda
 * @param {number} value - Valor a formatear
 * @param {string} currency - Símbolo de moneda (default: $)
 * @param {number} decimals - Número de decimales (default: 2)
 * @returns {string} Valor formateado
 */
function formatCurrency(value, currency = '$', decimals = 2) {
    const num = parseFloat(value) || 0;
    return `${currency}${num.toLocaleString('en-US', {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals
    })}`;
}

/**
 * Formatea un número como bolívares
 * @param {number} value - Valor a formatear
 * @returns {string} Valor formateado
 */
function formatBs(value) {
    return formatCurrency(value, 'Bs. ');
}

/**
 * Debounce - Limita la frecuencia de ejecución de una función
 * @param {Function} func - Función a ejecutar
 * @param {number} wait - Tiempo de espera en ms
 * @returns {Function} Función con debounce
 */
function debounce(func, wait = 300) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Obtiene parámetros de la URL
 * @param {string} name - Nombre del parámetro
 * @returns {string|null} Valor del parámetro
 */
function getUrlParam(name) {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get(name);
}

/**
 * Genera un ID único
 * @returns {string} ID único
 */
function generateId() {
    return Date.now().toString(36) + Math.random().toString(36).substr(2);
}

/**
 * Almacenamiento local seguro
 */
const Storage = {
    get(key, defaultValue = null) {
        try {
            const item = localStorage.getItem(key);
            return item ? JSON.parse(item) : defaultValue;
        } catch (e) {
            return defaultValue;
        }
    },
    
    set(key, value) {
        try {
            localStorage.setItem(key, JSON.stringify(value));
            return true;
        } catch (e) {
            return false;
        }
    },
    
    remove(key) {
        try {
            localStorage.removeItem(key);
            return true;
        } catch (e) {
            return false;
        }
    }
};

// Exportar al scope global
window.Utils = {
    escapeHTML,
    formatCurrency,
    formatBs,
    debounce,
    getUrlParam,
    generateId,
    Storage
};

// También exportar funciones individuales para compatibilidad
window.escapeHTML = escapeHTML;
window.formatCurrency = formatCurrency;
window.formatBs = formatBs;
window.debounce = debounce;
