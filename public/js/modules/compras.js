/**
 * =========================================================================
 * COMPRAS.JS - Módulo de Gestión de Compras
 * =========================================================================
 */

// Carrito global de compras
window.carritoCompra = window.carritoCompra || [];

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
                try {
                    const res = await fetch(`index.php?controlador=compra&accion=buscarProductos&term=${encodeURIComponent(term)}`);
                    const prods = await res.json();

                    resultados.innerHTML = '';
                    if (prods.length === 0) {
                        resultados.innerHTML = '<div class="p-4 text-center text-slate-400 text-sm">No se encontraron productos</div>';
                    } else {
                        prods.forEach(p => {
                            const item = document.createElement('div');
                            item.className = 'flex items-center gap-3 px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-600 cursor-pointer transition-colors border-b last:border-0 border-slate-100 dark:border-slate-600';
                            item.innerHTML = `
                                <div class="flex-1">
                                    <p class="font-medium text-slate-800 dark:text-white text-sm">${escapeHTML(p.nombre)}</p>
                                    <p class="text-xs text-slate-400">Costo Base: $${parseFloat(p.precioCompraUSD).toFixed(2)}</p>
                                </div>
                                <svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                            `;
                            item.onclick = () => {
                                p.costo = parseFloat(p.precioCompraUSD);
                                agregarACompra(p);
                                buscador.value = '';
                                resultados.innerHTML = '';
                            };
                            resultados.appendChild(item);
                        });
                    }
                    resultados.classList.remove('hidden');
                } catch (e) {
                    console.error('Error búsqueda compras:', e);
                }
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
    const btnGuardar = document.getElementById('btn-guardar-compra');
    if (btnGuardar) {
        const newBtn = btnGuardar.cloneNode(true);
        btnGuardar.parentNode.replaceChild(newBtn, btnGuardar);
        newBtn.addEventListener('click', guardarCompra);
    }
}

function agregarACompra(producto) {
    const existente = window.carritoCompra.find(i => i.id === producto.id);
    if (existente) {
        existente.cantidad++;
    } else {
        window.carritoCompra.push({
            id: producto.id,
            nombre: producto.nombre,
            costo: producto.costo,
            cantidad: 1
        });
    }
    renderizarTablaCompra();
}

function actualizarCantidadCompra(index, cantidad) {
    cantidad = parseInt(cantidad);
    if (cantidad > 0) {
        window.carritoCompra[index].cantidad = cantidad;
    }
    renderizarTablaCompra();
}

function eliminarDeCompra(index) {
    window.carritoCompra.splice(index, 1);
    renderizarTablaCompra();
}

function renderizarTablaCompra() {
    const tbody = document.getElementById('cuerpo-compra');
    const totalDisplay = document.getElementById('compra-total-display');
    if (!tbody) return;

    tbody.innerHTML = '';
    let total = 0;

    window.carritoCompra.forEach((item, index) => {
        const subtotal = item.costo * item.cantidad;
        total += subtotal;

        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-300">${escapeHTML(item.nombre)}</td>
            <td class="px-4 py-3 text-center">
                <input type="number" class="w-20 px-2 py-1 text-sm text-center border border-slate-300 dark:border-slate-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-slate-700 text-slate-800 dark:text-white" value="${item.cantidad}" min="1"
                       onchange="actualizarCantidadCompra(${index}, this.value)">
            </td>
            <td class="px-4 py-3 text-right text-sm text-slate-700 dark:text-slate-300">$${item.costo.toFixed(2)}</td>
            <td class="px-4 py-3 text-right text-sm font-bold text-slate-800 dark:text-white">$${subtotal.toFixed(2)}</td>
            <td class="px-4 py-3 text-center">
                <button class="p-1.5 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition-colors" onclick="eliminarDeCompra(${index})">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });

    if (totalDisplay) totalDisplay.textContent = `$${total.toFixed(2)}`;
}

async function guardarCompra() {
    const btn = document.getElementById('btn-guardar-compra');
    const originalText = btn ? btn.innerHTML : '';

    if (btn) {
        btn.disabled = true;
        btn.innerHTML = `
            <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Guardando...
        `;
    }

    try {
        const provId = document.getElementById('compra-proveedor')?.value || 0;

        if (!provId || provId == 0) {
            mostrarNotificacion('Selecciona un proveedor.', 'error');
            if (btn) { btn.disabled = false; btn.innerHTML = originalText; }
            return;
        }

        if (window.carritoCompra.length === 0) {
            mostrarNotificacion('La lista de compra está vacía.', 'error');
            if (btn) { btn.disabled = false; btn.innerHTML = originalText; }
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
            carrito: window.carritoCompra
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
        if (btn) { btn.disabled = false; btn.innerHTML = originalText; }
    }
}

// Exponer globalmente
window.Compras = {
    init: inicializarCompras,
    agregar: agregarACompra,
    actualizar: actualizarCantidadCompra,
    eliminar: eliminarDeCompra,
    renderizar: renderizarTablaCompra,
    guardar: guardarCompra
};

window.inicializarCompras = inicializarCompras;
window.agregarACompra = agregarACompra;
window.actualizarCantidadCompra = actualizarCantidadCompra;
window.eliminarDeCompra = eliminarDeCompra;
window.renderizarTablaCompra = renderizarTablaCompra;
window.guardarCompra = guardarCompra;
