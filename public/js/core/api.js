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
    /**
     * Procesa la respuesta de forma estandarizada
     */
    async _handleResponse(response) {
        if (!response.ok) {
            const errorMsg = `Error del Servidor (HTTP ${response.status})`;
            if (typeof showToast === 'function') showToast(errorMsg, 'error');
            throw new Error(errorMsg);
        }

        const text = await response.text();
        try {
            const result = JSON.parse(text);

            // Si el backend envía error=true o success=false con mensaje
            if (result && result.success === false && result.message) {
                if (typeof showToast === 'function') showToast(result.message, 'error');
            }

            if (result && result.success && result.hasOwnProperty('data')) {
                return result.data;
            }
            return result;
        } catch (e) {
            console.error('Respuesta no v\u00e1lida:', text);
            const msg = 'El servidor devolvi\u00f3 una respuesta inv\u00e1lida';
            if (typeof showToast === 'function') showToast(msg, 'error');
            throw new Error(msg);
        }
    },

    async get(url, params = {}) {
        const queryString = new URLSearchParams(params).toString();
        const fullUrl = queryString ? `${url}&${queryString}` : url;

        try {
            const response = await fetch(fullUrl);
            return await this._handleResponse(response);
        } catch (error) {
            console.error('API GET Error:', error);
            throw error;
        }
    },

    async post(url, data = {}) {
        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.csrfToken || ''
                },
                body: JSON.stringify({
                    ...(data || {}),
                    csrf_token: window.csrfToken || ''
                })
            });

            return await this._handleResponse(response);
        } catch (error) {
            console.error('API POST Error:', error);
            throw error;
        }
    },

    async postForm(url, formData) {
        const data = formData instanceof FormData ? formData : new FormData(formData);

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': window.csrfToken || ''
                },
                body: data
            });

            return await this._handleResponse(response);
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

    eliminarMasivo: (ids) =>
        API.post('index.php?controlador=producto&accion=eliminarMasivo', { ids }),

    // Proveedores
    obtenerProveedor: (id) =>
        API.get(`index.php?controlador=proveedor&accion=apiObtener&id=${id}`),

    actualizarProveedor: (formData) =>
        API.postForm('index.php?controlador=proveedor&accion=actualizar', formData),

    // Clientes
    buscarClientes: (term) =>
        API.get(`index.php?controlador=cliente&accion=buscarParaPOS&term=${encodeURIComponent(term)}`),

    desactivarCliente: (id) =>
        API.post('index.php?controlador=cliente&accion=desactivar', { id }),

    reactivarCliente: (id) =>
        API.post('index.php?controlador=cliente&accion=activar', { id }),

    // Dashboard
    datosGraficos: () =>
        API.get('index.php?controlador=dashboard&accion=apiDatosGraficos'),

    ventasPeriodo: (dias) =>
        API.get(`index.php?controlador=dashboard&accion=apiVentasPeriodo&dias=${dias}`),

    footerStats: () =>
        API.get('index.php?controlador=dashboard&accion=apiFooterStats'),

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
    obtenerTasa: () =>
        API.get('index.php?controlador=config&accion=obtenerTasa'),

    guardarTasa: (tasa) =>
        API.post('index.php?controlador=config&accion=guardarTasa', { tasa }),

    actualizarUmbralStock: (umbral) =>
        API.get(`index.php?controlador=perfil&accion=actualizarUmbral&umbral=${umbral}`),

    // Auth
    verificarDesbloqueo: (data) =>
        API.post('index.php?controlador=login&accion=verificarDesbloqueo', data),

    // Notificaciones
    marcarTodasLeidas: () =>
        API.get('index.php?controlador=notificacion&accion=marcarTodasLeidas')
};

// Exportar al scope global
window.API = API;
window.Endpoints = Endpoints;
