/**
 * =========================================================================
 * APP.JS - Inicializador Principal
 * =========================================================================
 * Sistema de Gestión de Inventario & POS (SaaS Pro)
 * 
 * Este archivo importa e inicializa todos los módulos del sistema.
 * Los módulos individuales están en:
 * - /js/core/     -> Utilidades base (utils, notifications, api)
 * - /js/modules/  -> Módulos funcionales (exchange-rate, modals, theme, etc)
 * - /js/pages/    -> Lógica específica por página
 */

console.log('APP.JS - Sistema modular cargado');

// Configurar jsPDF si está disponible
if (window.jspdf) {
    window.jsPDF = window.jspdf.jsPDF;
}

/**
 * =========================================================================
 * INICIALIZACIÓN PRINCIPAL
 * =========================================================================
 */
document.addEventListener('DOMContentLoaded', () => {
    console.log('DOMContentLoaded - Inicializando sistema...');

    // === 0. INICIALIZAR TURBO NAV ===
    initTurboNav();

    // === 1. INICIALIZAR MÓDULOS CORE ===

    // Notificaciones
    if (window.Notifications) {
        Notifications.initFlash();
        Notifications.initCenter();
    }

    // Modales
    if (window.Modals) {
        Modals.init();
    }

    // Tema oscuro/claro
    if (window.Theme) {
        Theme.init();
    }

    // === 2. INICIALIZAR TASA DE CAMBIO ===
    if (window.ExchangeRate) {
        ExchangeRate.init();
        ExchangeRate.configurarManual();
    } else if (typeof inicializarTasa === 'function') {
        inicializarTasa();
    }

    // === 3. INICIALIZAR GRÁFICOS (si estamos en dashboard) ===
    if (document.getElementById('chartValorCategoria')) {
        if (window.Charts) {
            Charts.update();
        } else if (typeof actualizarCharts === 'function') {
            actualizarCharts();
        }
    }

    // === 4. INICIALIZAR REPORTES (si estamos en reportes) ===
    if (document.getElementById('reporte-tipo')) {
        if (window.Reports) {
            Reports.configurarSelector();
        }
    }

    // === 5. CONFIGURAR ALERTAS DE STOCK ===
    configurarAlertasStock();

    // === 6. INICIALIZAR LÓGICA DE PÁGINA ESPECÍFICA ===
    inicializarPaginaActual();

    console.log('Sistema inicializado correctamente');
});

/**
 * =========================================================================
 * CONFIGURACIÓN DE ALERTAS DE STOCK
 * =========================================================================
 */
function configurarAlertasStock() {
    const inputUmbral = document.getElementById('stock-umbral-input');
    if (!inputUmbral) return;

    let umbralStockBajo = localStorage.getItem('stockUmbral') || 10;
    inputUmbral.value = umbralStockBajo;

    inputUmbral.addEventListener('change', () => {
        const val = parseInt(inputUmbral.value);
        if (val > 0) {
            localStorage.setItem('stockUmbral', val);
            umbralStockBajo = val;
            fetch(`index.php?controlador=perfil&accion=actualizarUmbral&umbral=${val}`);
            mostrarNotificacion('Umbral actualizado.', 'success');
            verificarAlertasStock();
        }
    });

    // Verificar alertas iniciales y periódicamente
    verificarAlertasStock();
    setInterval(verificarAlertasStock, 600000); // Cada 10 minutos
}

/**
 * Verifica alertas de stock bajo
 */
async function verificarAlertasStock() {
    const umbral = localStorage.getItem('stockUmbral') || 10;
    try {
        const response = await fetch(`index.php?controlador=producto&accion=apiObtenerAlertas&umbral=${umbral}`);
        if (!response.ok) return;
        const data = await response.json();

        if (data.agotado?.length > 0) {
            mostrarNotificacion(`AGOTADO: ${data.agotado[0].nombre}`, 'error');
        } else if (data.bajo?.length > 0) {
            mostrarNotificacion(`Stock bajo: ${data.bajo[0].nombre}`, 'warning');
        }
    } catch (e) {
        console.error('Error verificando alertas:', e);
    }
}

/**
 * =========================================================================
 * INICIALIZACIÓN POR PÁGINA
 * =========================================================================
 */
function inicializarPaginaActual() {
    // Detectar página actual basándose en elementos del DOM

    // POS (Punto de Venta)
    if (document.getElementById('pos-buscador')) {
        // inicializarPOS(); // Deshabilitado porque pos.php maneja su propia lógica
    }

    // Inventario de Productos (Ahora manejado localmente en index.php)
    // if (document.getElementById('tabla-inventario') && document.getElementById('busqueda-input')) {
    //     inicializarInventario();
    // }

    // Compras
    if (document.getElementById('compra-buscador')) {
        inicializarCompras();
    }

    // Proveedores
    if (document.getElementById('tabla-proveedores')) {
        inicializarProveedores();
    }

    // Productos (modales de agregar/editar)
    if (document.getElementById('modal-agregar-producto')) {
        inicializarModalesProducto();
    }
}

/**
 * =========================================================================
 * MÓDULO: INVENTARIO (LEGACY - REEMPLAZADO POR LÓGICA EN VISTA)
 * =========================================================================
 */
// function inicializarInventario() { ... }
// function renderizarTablaInventario(tbody, productos) { ... }

/**
 * =========================================================================
 * MÓDULO: POS (Punto de Venta)
 * =========================================================================
 */
window.carritoPOS = [];

function inicializarPOS() {
    const buscador = document.getElementById('pos-buscador');
    const resultados = document.getElementById('pos-resultados-busqueda');
    let timer;

    if (!buscador || !resultados) return;

    // Mover dropdown al body para escapar del stacking context
    document.body.appendChild(resultados);

    function actualizarPosicion() {
        const rect = buscador.getBoundingClientRect();
        resultados.style.position = 'fixed';
        resultados.style.top = rect.bottom + 'px';
        resultados.style.left = rect.left + 'px';
        resultados.style.width = rect.width + 'px';
        resultados.style.zIndex = '99999';
    }

    window.addEventListener('scroll', actualizarPosicion);
    window.addEventListener('resize', actualizarPosicion);
    actualizarPosicion();

    // Auto-foco
    buscador.focus();

    // Búsqueda
    buscador.addEventListener('keyup', (e) => {
        clearTimeout(timer);
        const term = e.target.value;

        if (term.length < 2) {
            resultados.innerHTML = '';
            return;
        }

        timer = setTimeout(async () => {
            try {
                const res = await fetch(`index.php?controlador=venta&accion=buscarProductos&term=${encodeURIComponent(term)}`);
                const prods = await res.json();

                resultados.innerHTML = '';
                prods.forEach(p => {
                    const item = document.createElement('div');
                    item.className = 'pos-item-resultado';
                    item.innerHTML = `
                        <strong>${escapeHTML(p.nombre)}</strong>
                        <span>$${parseFloat(p.precioVentaUSD).toFixed(2)}</span>
                    `;
                    item.onclick = () => {
                        agregarAlCarritoPOS(p);
                        buscador.value = '';
                        resultados.innerHTML = '';
                        buscador.focus();
                    };
                    resultados.appendChild(item);
                });
            } catch (e) {
                console.error('Error búsqueda POS:', e);
            }
        }, 300);
    });

    // Botones de acciones
    document.getElementById('btn-limpiar-carrito')?.addEventListener('click', limpiarCarritoPOS);
    document.getElementById('btn-cobrar')?.addEventListener('click', procesarVenta);
}

function agregarAlCarritoPOS(producto) {
    const existente = carritoPOS.find(i => i.id === producto.id);
    if (existente) {
        existente.cantidad++;
    } else {
        carritoPOS.push({
            id: producto.id,
            nombre: producto.nombre,
            precio: parseFloat(producto.precioVentaUSD),
            cantidad: 1
        });
    }
    renderizarCarritoPOS();
}

function renderizarCarritoPOS() {
    const tbody = document.getElementById('carrito-body');
    if (!tbody) return;

    tbody.innerHTML = '';

    carritoPOS.forEach((item, index) => {
        const subtotal = item.precio * item.cantidad;
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${escapeHTML(item.nombre)}</td>
            <td class="text-right">$${item.precio.toFixed(2)}</td>
            <td class="text-center">
                <input type="number" class="pos-input-cantidad" value="${item.cantidad}" min="1" 
                       onchange="actualizarCantidadPOS(${index}, this.value)">
            </td>
            <td class="text-right">$${subtotal.toFixed(2)}</td>
            <td class="text-center">
                <button class="btn btn-danger btn-sm" onclick="eliminarDelCarritoPOS(${index})">
                    <i class="fas fa-times"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });

    actualizarTotalesPOS();
}

window.actualizarCantidadPOS = function (index, cantidad) {
    cantidad = parseInt(cantidad);
    if (cantidad > 0) {
        carritoPOS[index].cantidad = cantidad;
    }
    renderizarCarritoPOS();
};

window.eliminarDelCarritoPOS = function (index) {
    carritoPOS.splice(index, 1);
    renderizarCarritoPOS();
};

function limpiarCarritoPOS() {
    carritoPOS.length = 0;
    renderizarCarritoPOS();
}

window.actualizarTotalesPOS = function () {
    const total = carritoPOS.reduce((sum, item) => sum + (item.precio * item.cantidad), 0);
    const tasa = window.ExchangeRate ? ExchangeRate.tasa : (window.tasaCambioBS || 0);

    const totalUSD = document.getElementById('total-usd');
    const totalVES = document.getElementById('total-ves');

    if (totalUSD) totalUSD.textContent = `$${total.toFixed(2)}`;
    if (totalVES) totalVES.textContent = `Bs. ${(total * tasa).toFixed(2)}`;
};

async function procesarVenta() {
    if (carritoPOS.length === 0) {
        mostrarNotificacion('El carrito está vacío', 'error');
        return;
    }

    try {
        const res = await fetch('index.php?controlador=venta&accion=checkout', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ items: carritoPOS })
        });
        const data = await res.json();

        if (data.success) {
            mostrarNotificacion('Venta registrada correctamente', 'success');
            limpiarCarritoPOS();
        } else {
            throw new Error(data.message || 'Error al registrar venta');
        }
    } catch (e) {
        mostrarNotificacion(e.message, 'error');
    }
}

/**
 * =========================================================================
 * MÓDULO: COMPRAS
 * =========================================================================
 */
window.carritoCompra = [];

function inicializarCompras() {
    const buscador = document.getElementById('compra-buscador');
    const resultados = document.getElementById('compra-resultados');
    const selectCondicion = document.getElementById('compra-condicion');
    const divVencimiento = document.getElementById('div-vencimiento');
    let timer;

    if (buscador && resultados) {
        buscador.addEventListener('keyup', (e) => {
            clearTimeout(timer);
            const term = e.target.value;
            if (term.length < 2) {
                resultados.innerHTML = '';
                return;
            }

            timer = setTimeout(async () => {
                const res = await fetch(`index.php?controlador=compra&accion=buscarProductos&term=${encodeURIComponent(term)}`);
                const prods = await res.json();

                resultados.innerHTML = '';
                prods.forEach(p => {
                    const item = document.createElement('div');
                    item.className = 'pos-item-resultado';
                    item.innerHTML = `<strong>${escapeHTML(p.nombre)}</strong> - Costo: $${parseFloat(p.precioCompraUSD).toFixed(2)}`;
                    item.onclick = () => {
                        p.costo = parseFloat(p.precioCompraUSD);
                        agregarACompra(p);
                        buscador.value = '';
                        resultados.innerHTML = '';
                    };
                    resultados.appendChild(item);
                });
            }, 300);
        });
    }

    // Mostrar/ocultar fecha de vencimiento según condición
    if (selectCondicion && divVencimiento) {
        selectCondicion.addEventListener('change', () => {
            divVencimiento.style.display = selectCondicion.value === 'Credito' ? 'block' : 'none';
        });
    }

    // Configurar botón de guardar
    document.getElementById('btn-guardar-compra')?.addEventListener('click', guardarCompra);
}

function agregarACompra(producto) {
    const existente = carritoCompra.find(i => i.id === producto.id);
    if (existente) {
        existente.cantidad++;
    } else {
        carritoCompra.push({
            id: producto.id,
            nombre: producto.nombre,
            costo: producto.costo,
            cantidad: 1
        });
    }
    renderizarTablaCompra();
}

function renderizarTablaCompra() {
    const tbody = document.getElementById('cuerpo-compra');
    const totalDisplay = document.getElementById('compra-total-display');
    if (!tbody) return;

    tbody.innerHTML = '';
    let total = 0;

    carritoCompra.forEach((item, index) => {
        const subtotal = item.costo * item.cantidad;
        total += subtotal;

        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${escapeHTML(item.nombre)}</td>
            <td class="text-right">$${item.costo.toFixed(2)}</td>
            <td class="text-center">
                <input type="number" class="pos-input-cantidad" value="${item.cantidad}" min="1"
                       onchange="actualizarCantidadCompra(${index}, this.value)">
            </td>
            <td class="text-right">$${subtotal.toFixed(2)}</td>
            <td class="text-center">
                <button class="btn btn-danger btn-sm" onclick="eliminarDeCompra(${index})">
                    <i class="fas fa-times"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });

    if (totalDisplay) totalDisplay.textContent = `$${total.toFixed(2)}`;
}

window.actualizarCantidadCompra = function (index, cantidad) {
    cantidad = parseInt(cantidad);
    if (cantidad > 0) {
        carritoCompra[index].cantidad = cantidad;
    }
    renderizarTablaCompra();
};

window.eliminarDeCompra = function (index) {
    carritoCompra.splice(index, 1);
    renderizarTablaCompra();
};

window.guardarCompra = async function () {
    const btn = document.getElementById('btn-guardar-compra');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
    }

    try {
        const provId = document.getElementById('compra-proveedor')?.value || 0;

        if (!provId || provId == 0) {
            mostrarNotificacion('Selecciona un proveedor.', 'error');
            if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-save"></i> Guardar Compra'; }
            return;
        }

        if (carritoCompra.length === 0) {
            mostrarNotificacion('La lista de compra está vacía.', 'error');
            if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-save"></i> Guardar Compra'; }
            return;
        }

        const factura = document.getElementById('compra-factura')?.value || '';
        const fecha = document.getElementById('compra-fecha')?.value || '';
        const condicion = document.getElementById('compra-condicion')?.value || 'Contado';
        const vencimiento = condicion === 'Credito'
            ? (document.getElementById('compra-vencimiento')?.value || fecha)
            : fecha;
        const estado = condicion === 'Credito' ? 'Pendiente' : 'Pagada';

        const payload = {
            proveedor_id: provId,
            nro_factura: factura,
            fecha_emision: fecha,
            fecha_vencimiento: vencimiento,
            estado: estado,
            carrito: carritoCompra
        };

        const res = await fetch('index.php?controlador=compra&accion=guardar', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        const text = await res.text();
        let d;
        try {
            d = JSON.parse(text);
        } catch (e) {
            throw new Error('El servidor devolvió un error.');
        }

        if (d.success) {
            mostrarNotificacion('Compra registrada con éxito.', 'success');
            setTimeout(() => window.location.href = 'index.php?controlador=compra&accion=index', 1000);
        } else {
            throw new Error(d.message || 'Error desconocido');
        }
    } catch (e) {
        console.error('Error en guardarCompra:', e);
        mostrarNotificacion(e.message, 'error');
        if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-save"></i> Guardar Compra'; }
    }
};

/**
 * =========================================================================
 * MÓDULO: PROVEEDORES
 * =========================================================================
 */
function inicializarProveedores() {
    const tablaProveedores = document.getElementById('tabla-proveedores');
    const modalEditar = document.getElementById('modal-editar-proveedor');
    const formEditar = document.getElementById('form-editar-proveedor');
    const btnCerrar = document.getElementById('cerrar-modal-proveedor');
    const btnCancel = document.getElementById('cancelar-modal-proveedor');

    if (!tablaProveedores) return;

    // Click en editar
    tablaProveedores.addEventListener('click', async (e) => {
        const btn = e.target.closest('.btn-editar-proveedor');
        if (!btn) return;

        e.preventDefault();
        const id = btn.dataset.id;

        try {
            const res = await fetch(`index.php?controlador=proveedor&accion=apiObtener&id=${id}`);
            const d = await res.json();
            if (d.error) throw new Error(d.error);

            formEditar.querySelector('#editar-prov-id').value = d.id;
            formEditar.querySelector('#editar-prov-nombre').value = d.nombre;
            formEditar.querySelector('#editar-prov-contacto').value = d.contacto;
            formEditar.querySelector('#editar-prov-telefono').value = d.telefono;
            formEditar.querySelector('#editar-prov-email').value = d.email;

            if (modalEditar) modalEditar.style.display = 'block';
        } catch (e) {
            mostrarNotificacion(e.message, 'error');
        }
    });

    // Cerrar modal
    if (btnCerrar) btnCerrar.onclick = () => { if (modalEditar) modalEditar.style.display = 'none'; };
    if (btnCancel) btnCancel.onclick = () => { if (modalEditar) modalEditar.style.display = 'none'; };

    // Submit formulario
    if (formEditar) {
        formEditar.addEventListener('submit', async (e) => {
            e.preventDefault();
            try {
                const res = await fetch('index.php?controlador=proveedor&accion=actualizar', {
                    method: 'POST',
                    body: new FormData(formEditar)
                });
                const d = await res.json();
                if (d.success) {
                    location.reload();
                } else {
                    throw new Error(d.message);
                }
            } catch (err) {
                mostrarNotificacion(err.message, 'error');
            }
        });
    }
}

/**
 * =========================================================================
 * MÓDULO: MODALES DE PRODUCTO
 * =========================================================================
 */
function inicializarModalesProducto() {
    // Modal Agregar
    const modalAgregar = document.getElementById('modal-agregar-producto');
    const formAgregar = document.getElementById('form-agregar-producto');
    const btnAbrir = document.getElementById('btn-abrir-modal-agregar-prod');
    const btnCerrar = document.getElementById('cerrar-modal-agregar-prod');

    if (btnAbrir && modalAgregar && formAgregar) {
        btnAbrir.onclick = () => {
            formAgregar.reset();
            modalAgregar.style.display = 'block';
        };
    }

    if (btnCerrar && modalAgregar) {
        btnCerrar.onclick = () => modalAgregar.style.display = 'none';
    }

    if (formAgregar) {
        formAgregar.addEventListener('submit', async (e) => {
            e.preventDefault();
            try {
                const res = await fetch('index.php?controlador=producto&accion=crear', {
                    method: 'POST',
                    body: new FormData(formAgregar)
                });
                const d = await res.json();
                if (d.success) {
                    location.reload();
                } else {
                    throw new Error(d.message || 'Error al crear producto');
                }
            } catch (e) {
                mostrarNotificacion(e.message, 'error');
            }
        });
    }

    // Modal Editar
    const modalEditar = document.getElementById('modal-editar-producto');
    const formEditar = document.getElementById('form-editar-producto');
    const btnCerrarEditar = document.getElementById('cerrar-modal-producto');
    const btnCancelar = document.getElementById('cancelar-modal-producto');
    const tablaProductos = document.getElementById('tabla-inventario');

    if (btnCerrarEditar && modalEditar) {
        btnCerrarEditar.onclick = () => modalEditar.style.display = 'none';
    }
    if (btnCancelar && modalEditar) {
        btnCancelar.onclick = () => modalEditar.style.display = 'none';
    }

    // Click en editar producto
    if (tablaProductos) {
        tablaProductos.addEventListener('click', async (e) => {
            const btn = e.target.closest('.btn-editar-producto');
            if (!btn) return;

            e.preventDefault();
            const id = btn.dataset.id;

            try {
                const res = await fetch(`index.php?controlador=producto&accion=apiObtener&id=${id}`);
                const p = await res.json();
                if (p.error) throw new Error(p.error);

                // Llenar formulario
                document.getElementById('editar-id').value = p.id;
                document.getElementById('editar-nombre').value = p.nombre;
                document.getElementById('editar-codigo-barras').value = p.codigo_barras || '';
                document.getElementById('editar-proveedor').value = p.proveedor_id || 0;
                document.getElementById('editar-precio-base').value = p.precio_base || p.precioCompraUSD;
                document.getElementById('editar-margen').value = p.margen_ganancia || 30;

                // IVA
                const tieneIva = document.getElementById('editar-tiene-iva');
                const ivaGrupo = document.getElementById('iva-porcentaje-grupo-editar');
                const ivaPorcentaje = document.getElementById('editar-iva-porcentaje');

                if (tieneIva) {
                    tieneIva.checked = p.tiene_iva == 1;
                    if (ivaGrupo) ivaGrupo.style.display = tieneIva.checked ? 'block' : 'none';
                }
                if (ivaPorcentaje) ivaPorcentaje.value = p.iva_porcentaje || 16;

                // Preview
                actualizarPreviewEditar(p);

                if (modalEditar) modalEditar.style.display = 'block';
            } catch (e) {
                mostrarNotificacion(e.message, 'error');
            }
        });
    }

    // Submit editar
    if (formEditar) {
        formEditar.addEventListener('submit', async (e) => {
            e.preventDefault();
            try {
                const res = await fetch('index.php?controlador=producto&accion=actualizar', {
                    method: 'POST',
                    body: new FormData(formEditar)
                });
                const d = await res.json();
                if (d.success) {
                    location.reload();
                } else {
                    throw new Error(d.message || 'Error al actualizar');
                }
            } catch (e) {
                mostrarNotificacion(e.message, 'error');
            }
        });
    }
}

function actualizarPreviewEditar(producto) {
    const prevCompra = document.getElementById('preview-precio-compra');
    const prevVenta = document.getElementById('preview-precio-venta');
    const prevGanancia = document.getElementById('preview-ganancia');

    if (prevCompra) prevCompra.textContent = `$${parseFloat(producto.precioCompraUSD).toFixed(2)}`;
    if (prevVenta) prevVenta.textContent = `$${parseFloat(producto.precioVentaUSD).toFixed(2)}`;
    if (prevGanancia) prevGanancia.textContent = `$${parseFloat(producto.gananciaUnitariaUSD).toFixed(2)}`;
}

// Exponer funciones necesarias globalmente
window.escapeHTML = escapeHTML || function (str) {
    if (str === null || str === undefined) return '';
    return String(str).replace(/[&<>"']/g, function (m) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m];
    });
};

/**
 * =========================================================================
 * MÓDULO: TURBO NAVIGATION (Navegación Instantánea)
 * =========================================================================
 * Evita la recarga completa de la página en enlaces internos.
 */
// Cache simple en memoria para prefetching
const turboCache = new Map();

function initTurboNav() {
    console.log('TurboNav V2 initialized 🚀');

    // Create Top Progress Bar
    let progressBar = document.getElementById('turbo-progress');
    if (!progressBar) {
        progressBar = document.createElement('div');
        progressBar.id = 'turbo-progress';
        progressBar.style.cssText = `
            position: fixed; top: 0; left: 0; width: 0%; height: 3px;
            background: #3b82f6; z-index: 9999;
            transition: width 0.2s ease, opacity 0.3s;
            box-shadow: 0 0 10px #3b82f6;
        `;
        document.body.appendChild(progressBar);
    }

    // Main content container
    const mainContainer = document.querySelector('main');
    const turboCache = new Map();
    const PREFETCH_DELAY = 50;

    // Handle internal links
    document.addEventListener('click', async (e) => {
        const link = e.target.closest('a');
        if (!link) return;

        // Ignore external, hash, or special links
        const href = link.getAttribute('href');
        if (!href || href.startsWith('#') || href.startsWith('javascript:') || link.target === '_blank' || link.hasAttribute('download')) return;

        // Check if internal (same origin)
        const url = new URL(link.href, window.location.origin);
        if (url.origin !== window.location.origin) return;

        // Prevent default navigation
        e.preventDefault();

        // == ANIMATION OUT ==
        if (mainContainer) {
            mainContainer.style.transition = 'opacity 0.15s ease, transform 0.15s ease';
            mainContainer.style.opacity = '0.5';
            mainContainer.style.transform = 'scale(0.99)';
        }

        // Start loading UI
        progressBar.style.width = '30%';
        progressBar.style.opacity = '1';

        try {
            // Check cache or fetch
            let html;
            if (turboCache.has(url.href)) {
                html = turboCache.get(url.href);
                progressBar.style.width = '70%'; // Cache hit visual
            } else {
                const res = await fetch(url.href);
                if (!res.ok) throw new Error('Network error');
                html = await res.text();
            }

            // Parse content
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newMain = doc.querySelector('main');
            const newTitle = doc.querySelector('title');

            if (!newMain) {
                // Fallback if structure is different
                window.location.href = url.href;
                return;
            }

            // Update History
            window.history.pushState({}, '', url.href);
            if (newTitle) document.title = newTitle.textContent;

            // == SWAP CONTENT ==
            if (mainContainer) {
                mainContainer.innerHTML = newMain.innerHTML;

                // Clear exit styles
                mainContainer.style.opacity = '';
                mainContainer.style.transform = '';
                mainContainer.style.transition = '';

                // == ANIMATION IN ==
                mainContainer.classList.remove('animate-fade-in-up');
                void mainContainer.offsetWidth; // Trigger reflow
                mainContainer.classList.add('animate-fade-in-up');
            }

            // Finish loading UI
            progressBar.style.width = '100%';
            setTimeout(() => {
                progressBar.style.opacity = '0';
                setTimeout(() => { progressBar.style.width = '0%'; }, 200);
            }, 300);

            // Re-init scripts
            executeScripts(mainContainer);

            // === RE-INIT MODULES ===
            if (window.ExchangeRate) {
                ExchangeRate.init();
                ExchangeRate.configurarManual();
            }
            if (window.Modals) Modals.init();
            if (window.Notifications) Notifications.initFlash();

            // Re-init app components
            if (typeof inicializarPaginaActual === 'function') inicializarPaginaActual();

            // Scroll to top
            window.scrollTo({ top: 0, behavior: 'smooth' });

            // Notify app navigation finished
            window.dispatchEvent(new CustomEvent('app:page-loaded', {
                detail: { url: url.href }
            }));

        } catch (err) {
            console.error('TurboNav failed:', err);
            window.location.href = url.href; // Fallback
        }
    });

    // Handle Back/Forward buttons
    window.addEventListener('popstate', () => {
        window.location.reload();
    });

    // == PREFETCHING ==
    document.addEventListener('mouseover', (e) => {
        const link = e.target.closest('a');
        if (!link) return;
        const href = link.href;

        // Simple internal check
        if (!href.includes(window.location.origin) || href.includes('#')) return;

        if (!turboCache.has(href)) {
            // Debounce prefetch
            setTimeout(() => {
                if (link.matches(':hover')) {
                    fetch(href)
                        .then(r => r.text())
                        .then(h => turboCache.set(href, h))
                        .catch(() => { });
                }
            }, PREFETCH_DELAY);
        }
    });
}

/**
 * Execute inner scripts of new content
 */
/**
 * Execute inner scripts of new content
 */
function executeScripts(container) {
    const scripts = container.querySelectorAll('script');
    scripts.forEach(oldScript => {
        try {
            const newScript = document.createElement('script');
            // Copiar atributos
            Array.from(oldScript.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));

            // Copiar contenido (usar textContent para evitar problemas con entidades HTML)
            newScript.textContent = oldScript.textContent;

            // Reemplazar solo si el padre existe
            if (oldScript.parentNode) {
                oldScript.parentNode.replaceChild(newScript, oldScript);
            }
        } catch (e) {
            console.error('Error re-executing script:', e);
        }
    });
}
