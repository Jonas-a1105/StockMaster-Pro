/**
 * =========================================================================
 * EXCHANGE-RATE.JS - Tasa de Cambio
 * =========================================================================
 * Lógica para obtener y aplicar tasas de cambio USD/VES
 */

// Variable global para la tasa
let tasaCambioBS = 0;

/**
 * Obtiene tasa con reintentos
 */
async function getTasaWithRetry(url, retries = 3, delay = 1000) {
    for (let i = 0; i < retries; i++) {
        try {
            const response = await fetch(url);
            if (!response.ok) throw new Error('HTTP error');
            const data = await response.json();
            if (data && data.length > 0 && data[0] && typeof data[0].promedio !== 'undefined') {
                return data[0].promedio;
            }
            throw new Error('API inválida');
        } catch (error) {
            if (i >= retries - 1) throw error;
            await new Promise(resolve => setTimeout(resolve, delay));
        }
    }
    return null;
}

/**
 * Obtiene tasa de fallback
 */
async function getTasaFallback() {
    try {
        const res = await fetch('https://pydolarve.org/api/v1/dollar?page=alcambio');
        const data = await res.json();
        if (data?.monitors?.usd?.price) return parseFloat(data.monitors.usd.price);
        throw new Error('Fallback inválido');
    } catch (e) {
        return null;
    }
}

/**
 * Inicializa la tasa de cambio
 */
async function inicializarTasa() {
    const apiSource = document.getElementById('api-source');
    const tasaUsd = document.getElementById('tasa-usd');
    const apiStatus = document.getElementById('api-status');
    const inputTasaManual = document.getElementById('tasa-manual-input');

    // 1. Verificar si hay tasa manual guardada
    // 1. Verificar si hay tasa manual guardada
    const tasaGuardada = localStorage.getItem('manualRate');

    // Elementos Navbar
    const navInput = document.getElementById('nav-tasa-input');

    if (tasaGuardada) {
        const tasa = parseFloat(tasaGuardada);
        tasaCambioBS = tasa;

        if (apiSource) apiSource.textContent = 'Manual (Guardada)';
        if (tasaUsd) tasaUsd.textContent = `${tasa.toFixed(2)} VES`;
        if (apiStatus) apiStatus.textContent = 'Aplicada.';
        if (inputTasaManual) inputTasaManual.value = tasa;
        if (navInput) navInput.value = tasa; // Init Navbar Input

        ExchangeRate.aplicar(tasa);
        return;
    }

    // 2. Llamar a la API
    if (apiSource) {
        apiSource.textContent = 'DolarApi.com';
        if (apiStatus) apiStatus.textContent = 'Conectando...';
    }

    try {
        const tasa = await getTasaWithRetry('https://dolarapi.com/v1/dolares/paralelo');
        if (tasa) {
            tasaCambioBS = tasa;
            if (tasaUsd) tasaUsd.textContent = `${tasa.toFixed(2)} VES`;
            if (navInput) navInput.value = tasa; // Init Navbar Input
            if (apiStatus) apiStatus.textContent = 'Conectado.';
            ExchangeRate.aplicar(tasa);
        }
    } catch (error) {
        // Fallback
        const tasaFallback = await getTasaFallback();
        if (tasaFallback) {
            tasaCambioBS = tasaFallback;
            if (apiSource) apiSource.textContent = 'PyDolarVE (Fallback)';
            if (tasaUsd) tasaUsd.textContent = `${tasaFallback.toFixed(2)} VES`;
            if (navInput) navInput.value = tasaFallback; // Init Navbar Input
            if (apiStatus) apiStatus.textContent = 'Conectado.';
            ExchangeRate.aplicar(tasaFallback);
        } else {
            if (apiSource) apiSource.textContent = 'Sin Conexión';
            if (apiStatus) apiStatus.textContent = 'Error.';
        }
    }
}

/**
 * Aplica la tasa a los elementos del DOM
 */
function aplicarTasa(tasa) {
    tasaCambioBS = tasa;
    actualizarPreciosVES(tasa);

    // Notificar al sistema
    window.dispatchEvent(new CustomEvent('tasa-cambio-actualizada', { detail: { tasa } }));

    // Actualizar POS si existe
    if (typeof window.actualizarTotalesPOS === 'function') {
        window.actualizarTotalesPOS();
    }
}

/**
 * Actualiza precios en VES en la tabla de inventario
 */
function actualizarPreciosVES(tasa) {
    if (tasa <= 0) return;

    const tablaInventario = document.getElementById('tabla-inventario');
    if (!tablaInventario) return;

    const filas = tablaInventario.querySelectorAll('tbody tr[data-precio-venta-usd]');

    filas.forEach(fila => {
        const setCell = (selector, valorUSD) => {
            const celda = fila.querySelector(selector);
            if (celda && !isNaN(valorUSD) && valorUSD !== null) {
                const valorBs = valorUSD * tasa;
                celda.textContent = `Bs. ${valorBs.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                })}`;
            }
        };

        const pCompra = parseFloat(fila.dataset.precioCompraUsd || 0);
        const pVenta = parseFloat(fila.dataset.precioVentaUsd || 0);
        const gUnit = parseFloat(fila.dataset.gananciaUsd || 0);
        const gTotal = parseFloat(fila.dataset.gastoTotalUsd || 0);
        const ganTotal = parseFloat(fila.dataset.gananciaTotalUsd || 0);
        const vTotal = parseFloat(fila.dataset.valorVentaTotalUsd || 0);

        setCell('.precio-compra-ves', pCompra);
        setCell('.precio-venta-ves', pVenta);
        setCell('.ganancia-ves', gUnit);
        setCell('.gasto-total-ves', gTotal);
        setCell('.ganancia-total-ves', ganTotal);
        setCell('.valor-venta-total-ves', vTotal);
    });
}

/**
 * Lógica compartida para guardar tasa
 */
async function guardarTasaManual(tasa) {
    if (isNaN(tasa) || tasa <= 0) {
        mostrarNotificacion('Ingresa una tasa válida', 'error');
        return;
    }

    try {
        const res = await fetch('index.php?controlador=config&accion=guardarTasa', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ tasa: tasa })
        });
        const data = await res.json();

        if (data.success) {
            localStorage.setItem('manualRate', tasa);
            tasaCambioBS = tasa;

            // Actualizar inputs visuales
            const navInput = document.getElementById('nav-tasa-input');
            const configInput = document.getElementById('tasa-manual-input');
            const tasaUsd = document.getElementById('tasa-usd');

            if (navInput) navInput.value = tasa;
            if (configInput) configInput.value = tasa;
            if (tasaUsd) tasaUsd.textContent = `Bs. ${tasa}`; // Falback text element if exists

            ExchangeRate.aplicar(tasa);
            mostrarNotificacion('Tasa actualizada correctamente', 'success');
        } else {
            mostrarNotificacion('Error al guardar tasa', 'error');
        }
    } catch (e) {
        console.error(e);
        mostrarNotificacion('Error de conexión', 'error');
    }
}

/**
 * Configura el botón de tasa manual
 */
function configurarTasaManual() {
    // Config Page
    const btnConfig = document.getElementById('btn-aplicar-tasa');
    const inputConfig = document.getElementById('tasa-manual-input');

    if (btnConfig && inputConfig) {
        btnConfig.addEventListener('click', () => {
            guardarTasaManual(parseFloat(inputConfig.value));
        });
    }

    // Navbar (Elementos persistentes, verificar para no duplicar listeners)
    const btnNav = document.getElementById('nav-btn-update');
    const inputNav = document.getElementById('nav-tasa-input');

    if (btnNav && inputNav) {
        if (!btnNav.dataset.hasListener) {
            btnNav.addEventListener('click', () => {
                guardarTasaManual(parseFloat(inputNav.value));
            });
            btnNav.dataset.hasListener = 'true';
        }

        if (!inputNav.dataset.hasListener) {
            inputNav.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    guardarTasaManual(parseFloat(inputNav.value));
                }
            });
            inputNav.dataset.hasListener = 'true';
        }
    }
}

// Exportar al scope global
window.ExchangeRate = {
    init: inicializarTasa,
    aplicar: aplicarTasa,
    actualizar: actualizarPreciosVES,
    configurarManual: configurarTasaManual,
    get tasa() { return tasaCambioBS; },
    set tasa(value) { tasaCambioBS = value; }
};

// Compatibilidad
window.tasaCambioBS = tasaCambioBS;
window.inicializarTasa = inicializarTasa;
window.actualizarPreciosVES = actualizarPreciosVES;
