/**
 * =========================================================================
 * API.JS - Cliente API Centralizado
 * =========================================================================
 * Wrapper para llamadas fetch con manejo de errores consistente.
 */

/**
 * Cliente HTTP base
 */
const API = {
    /**
     * Realiza una petición GET
     * @param {string} url - URL de la petición
     * @param {Object} params - Parámetros query
     * @returns {Promise} Respuesta JSON
     */
    async get(url, params = {}) {
        const queryString = new URLSearchParams(params).toString();
        const fullUrl = queryString ? `${url}&${queryString}` : url;

        try {
            const response = await fetch(fullUrl);
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            return await response.json();
        } catch (error) {
            console.error('API GET Error:', error);
            throw error;
        }
    },

    /**
     * Realiza una petición POST con JSON
     * @param {string} url - URL de la petición
     * @param {Object} data - Datos a enviar
     * @returns {Promise} Respuesta JSON
     */
    async post(url, data = {}) {
        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });

            const text = await response.text();

            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('Respuesta no es JSON:', text);
                throw new Error('El servidor devolvió una respuesta inválida');
            }
        } catch (error) {
            console.error('API POST Error:', error);
            throw error;
        }
    },

    /**
     * Realiza una petición POST con FormData
     * @param {string} url - URL de la petición
     * @param {FormData|HTMLFormElement} formData - Datos del formulario
     * @returns {Promise} Respuesta JSON
     */
    async postForm(url, formData) {
        const data = formData instanceof FormData ? formData : new FormData(formData);

        try {
            const response = await fetch(url, {
                method: 'POST',
                body: data
            });

            const text = await response.text();

            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('Respuesta no es JSON:', text);
                throw new Error('El servidor devolvió una respuesta inválida');
            }
        } catch (error) {
            console.error('API POST Form Error:', error);
            throw error;
        }
    }
};

/**
 * Endpoints específicos del sistema
 */
const Endpoints = {
    // Productos
    buscarProductos: (term) =>
        API.get(`index.php?controlador=producto&accion=apiBuscar&term=${encodeURIComponent(term)}`),

    obtenerProducto: (id) =>
        API.get(`index.php?controlador=producto&accion=apiObtener&id=${id}`),

    crearProducto: (formData) =>
        API.postForm('index.php?controlador=producto&accion=crear', formData),

    actualizarProducto: (formData) =>
        API.postForm('index.php?controlador=producto&accion=actualizar', formData),

    obtenerAlertas: (umbral) =>
        API.get(`index.php?controlador=producto&accion=apiObtenerAlertas&umbral=${umbral}`),

    // Proveedores
    obtenerProveedor: (id) =>
        API.get(`index.php?controlador=proveedor&accion=apiObtener&id=${id}`),

    actualizarProveedor: (formData) =>
        API.postForm('index.php?controlador=proveedor&accion=actualizar', formData),

    // Dashboard
    datosGraficos: () =>
        API.get('index.php?controlador=dashboard&accion=apiDatosGraficos'),

    // Ventas
    buscarProductosPOS: (term) =>
        API.get(`index.php?controlador=venta&accion=buscarProductos&term=${encodeURIComponent(term)}`),

    registrarVenta: (data) =>
        API.post('index.php?controlador=venta&accion=checkout', data),

    // Compras
    buscarProductosCompra: (term) =>
        API.get(`index.php?controlador=compra&accion=buscarProductos&term=${encodeURIComponent(term)}`),

    registrarCompra: (data) =>
        API.post('index.php?controlador=compra&accion=guardar', data),

    // Configuración
    guardarTasa: (tasa) =>
        API.post('index.php?controlador=config&accion=guardarTasa', { tasa }),

    // Notificaciones
    marcarTodasLeidas: () =>
        API.get('index.php?controlador=notificacion&accion=marcarTodasLeidas')
};

// Exportar al scope global
window.API = API;
window.Endpoints = Endpoints;
