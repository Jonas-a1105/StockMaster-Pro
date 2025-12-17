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
    const tasaGuardada = localStorage.getItem('manualRate');

    // Elementos Navbar
    const navInput = document.getElementById('nav-tasa-input');
    const posInput = document.getElementById('tasa-manual'); // POS page

    if (tasaGuardada) {
        const tasa = parseFloat(tasaGuardada);
        tasaCambioBS = tasa;

        if (apiSource) apiSource.textContent = 'Manual (Guardada)';
        if (tasaUsd) tasaUsd.textContent = `${tasa.toFixed(2)} VES`;
        if (apiStatus) apiStatus.textContent = 'Aplicada.';
        if (inputTasaManual) inputTasaManual.value = tasa;
        if (posInput) posInput.value = tasa;
        if (navInput) navInput.value = tasa;

        // Footer update
        const footerTasa = document.getElementById('footer-tasa');
        if (footerTasa) footerTasa.textContent = `Bs. ${tasa.toFixed(2)}`;

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
            if (navInput) navInput.value = tasa;
            if (apiStatus) apiStatus.textContent = 'Conectado.';

            const footerTasa = document.getElementById('footer-tasa');
            if (footerTasa) footerTasa.textContent = `Bs. ${tasa.toFixed(2)}`;

            ExchangeRate.aplicar(tasa);
        }
    } catch (error) {
        // Fallback
        const tasaFallback = await getTasaFallback();
        if (tasaFallback) {
            tasaCambioBS = tasaFallback;
            if (apiSource) apiSource.textContent = 'PyDolarVE (Fallback)';
            if (tasaUsd) tasaUsd.textContent = `${tasaFallback.toFixed(2)} VES`;
            if (navInput) navInput.value = tasaFallback;
            if (apiStatus) apiStatus.textContent = 'Conectado.';

            const footerTasa = document.getElementById('footer-tasa');
            if (footerTasa) footerTasa.textContent = `Bs. ${tasaFallback.toFixed(2)}`;

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
    console.log('[ExchangeRate] actualizarPreciosVES ejecutado con tasa:', tasa);
    if (!tasa || isNaN(tasa) || tasa <= 0) {
        console.warn('[ExchangeRate] Tasa inválida para actualizar precios');
        return;
    }

    // Usar requestAnimationFrame para asegurar que el DOM esté listo
    requestAnimationFrame(() => {
        // SI existe el módulo de Productos con su lógica de modos (USD/VES), delegar en él
        if (window.Productos && typeof window.Productos.actualizarPrecios === 'function') {
            console.log('[ExchangeRate] Delegando a Productos.actualizarPrecios()');
            window.Productos.actualizarPrecios();
            return;
        }

        // Si no (p.ej. estamos en otra vista), usar lógica genérica de fallback

        // 1. Actualizar elementos .currency-wrapper (Fallback genérico)
        // Esta clase está en los TDs de la tabla: Precio Compra, Venta, Ganancia
        const wrappers = document.querySelectorAll('.currency-wrapper');

        wrappers.forEach(wrapper => {
            const valorUSD = parseFloat(wrapper.dataset.usd);
            const precioSec = wrapper.querySelector('.price-sec');

            if (precioSec && !isNaN(valorUSD)) {
                const valorBs = valorUSD * tasa;

                // Forzar repintado si es necesario ocultando y mostrando (hack extremo si nada funciona)
                // precioSec.style.display = 'none';
                // precioSec.offsetHeight; // trigger reflow
                // precioSec.style.display = 'block';

                precioSec.textContent = `Bs. ${valorBs.toLocaleString('es-VE', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                })}`;
            }
        });

        // 2. Actualizar elementos genéricos con data-currency-usd
        const generics = document.querySelectorAll('[data-currency-usd]');

        generics.forEach(el => {
            const valorUSD = parseFloat(el.dataset.currencyUsd);

            // Evitamos actualizar si el elemento está DENTRO de un wrapper ya actualizado
            if (el.closest('.currency-wrapper')) return;

            if (!isNaN(valorUSD)) {
                const valorBs = valorUSD * tasa;
                el.textContent = `Bs. ${valorBs.toLocaleString('es-VE', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                })}`;
            }
        });
    });
}

/**
 * Lógica compartida para guardar tasa
 */
/**
 * Lógica compartida para guardar tasa
 */
async function guardarTasaManual(tasa) {
    if (!tasa || isNaN(tasa) || tasa <= 0) {
        mostrarNotificacion('Ingresa una tasa válida', 'error');
        return;
    }

    // 1. OPTIMISTIC UPDATE: Aplicar cambios visuales de inmediato
    console.log('[ExchangeRate] Aplicando actualización optimista...');
    tasaCambioBS = tasa;

    // Forzar actualización inmediata de elementos UI globales
    const tasaUsd = document.getElementById('tasa-usd');
    const footerTasa = document.getElementById('footer-tasa');
    const navInput = document.getElementById('nav-tasa-input');
    const configInput = document.getElementById('tasa-manual-input');

    if (tasaUsd) tasaUsd.textContent = `${tasa.toFixed(2)} VES`;
    if (footerTasa) footerTasa.textContent = `Bs. ${Number(tasa).toFixed(2)}`;

    // Solo actualizar input del navbar si no tiene foco (para no molestar al usuario mientras escribe)
    if (navInput && document.activeElement !== navInput) {
        navInput.value = tasa;
    }

    if (configInput && document.activeElement !== configInput) {
        configInput.value = tasa;
    }

    // Aplicar a la vista actual (tablas, etc)
    if (window.ExchangeRate && window.ExchangeRate.aplicar) {
        window.ExchangeRate.aplicar(tasa);
    } else {
        // Fallback si ExchangeRate no está totalmente expuesto aún
        if (typeof actualizarPreciosVES === 'function') actualizarPreciosVES(tasa);
    }

    try {
        // 2. Guardar en Backend
        const res = await fetch('index.php?controlador=config&accion=guardarTasa', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ tasa: tasa })
        });
        const data = await res.json();

        if (data.success) {
            localStorage.setItem('manualRate', tasa);
            mostrarNotificacion('Tasa actualizada correctamente', 'success');
        } else {
            console.error('[ExchangeRate] Error en servidor:', data.error);
            mostrarNotificacion('Error al guardar tasa en servidor', 'error');
        }
    } catch (e) {
        console.error(e);
        // Aún así mantenemos el cambio visual porque es probable que sea error de red temporal
        mostrarNotificacion('Tasa aplicada localmente (Error de conexión)', 'warning');
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
        // Remover listener anterior si existe (cloning)
        const newBtnConfig = btnConfig.cloneNode(true);
        btnConfig.parentNode.replaceChild(newBtnConfig, btnConfig);

        newBtnConfig.addEventListener('click', (e) => {
            e.preventDefault();
            let valor = inputConfig.value;
            valor = valor.replace(',', '.'); // Soporte coma
            guardarTasaManual(parseFloat(valor));
        });
    }

    // Navbar (Elementos persistentes)
    const btnNav = document.getElementById('nav-btn-update');
    const inputNav = document.getElementById('nav-tasa-input');

    if (btnNav && inputNav) {
        console.log('[ExchangeRate] Botón navbar encontrado, configurando listener...');

        // Clonar para asegurar limpieza de event listeners previos
        const newBtn = btnNav.cloneNode(true);
        btnNav.parentNode.replaceChild(newBtn, btnNav);

        newBtn.addEventListener('click', (e) => {
            e.preventDefault();
            console.log('[ExchangeRate] Click en actualizar tasa navbar');

            let valor = inputNav.value;
            valor = valor.replace(',', '.'); // Soporte coma
            const tasa = parseFloat(valor);

            console.log(`[ExchangeRate] Intentando guardar tasa: ${valor} -> ${tasa}`);
            guardarTasaManual(tasa);
        });

        if (!inputNav.dataset.hasListener) {
            inputNav.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    let valor = inputNav.value.replace(',', '.');
                    guardarTasaManual(parseFloat(valor));
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
    reapply: () => aplicarTasa(tasaCambioBS),
    get tasa() { return tasaCambioBS; },
    set tasa(value) { tasaCambioBS = value; }
};

// Compatibilidad
window.tasaCambioBS = tasaCambioBS;
window.inicializarTasa = inicializarTasa;
window.actualizarPreciosVES = actualizarPreciosVES;
