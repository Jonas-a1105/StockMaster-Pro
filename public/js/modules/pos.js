/**
 * =========================================================================
 * POS.JS - Módulo de Punto de Venta
 * =========================================================================
 */

// Carrito global
window.carritoPOS = window.carritoPOS || [];

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
    const existente = window.carritoPOS.find(i => i.id === producto.id);
    if (existente) {
        existente.cantidad++;
    } else {
        window.carritoPOS.push({
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

    window.carritoPOS.forEach((item, index) => {
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

function actualizarCantidadPOS(index, cantidad) {
    cantidad = parseInt(cantidad);
    if (cantidad > 0) {
        window.carritoPOS[index].cantidad = cantidad;
    }
    renderizarCarritoPOS();
}

function eliminarDelCarritoPOS(index) {
    window.carritoPOS.splice(index, 1);
    renderizarCarritoPOS();
}

function limpiarCarritoPOS() {
    window.carritoPOS.length = 0;
    renderizarCarritoPOS();
}

function actualizarTotalesPOS() {
    const total = window.carritoPOS.reduce((sum, item) => sum + (item.precio * item.cantidad), 0);
    const tasa = window.ExchangeRate ? ExchangeRate.tasa : (window.tasaCambioBS || 0);

    const totalUSD = document.getElementById('total-usd');
    const totalVES = document.getElementById('total-ves');

    if (totalUSD) totalUSD.textContent = `$${total.toFixed(2)}`;
    if (totalVES) totalVES.textContent = `Bs. ${(total * tasa).toFixed(2)}`;
}

async function procesarVenta() {
    if (window.carritoPOS.length === 0) {
        mostrarNotificacion('El carrito está vacío', 'error');
        return;
    }

    try {
        const res = await fetch('index.php?controlador=venta&accion=checkout', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ items: window.carritoPOS })
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

// Exponer globalmente
window.POS = {
    init: inicializarPOS,
    agregar: agregarAlCarritoPOS,
    renderizar: renderizarCarritoPOS,
    limpiar: limpiarCarritoPOS,
    procesar: procesarVenta
};

window.inicializarPOS = inicializarPOS;
window.agregarAlCarritoPOS = agregarAlCarritoPOS;
window.renderizarCarritoPOS = renderizarCarritoPOS;
window.actualizarCantidadPOS = actualizarCantidadPOS;
window.eliminarDelCarritoPOS = eliminarDelCarritoPOS;
window.limpiarCarritoPOS = limpiarCarritoPOS;
window.actualizarTotalesPOS = actualizarTotalesPOS;
window.procesarVenta = procesarVenta;
