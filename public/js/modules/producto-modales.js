/**
 * =========================================================================
 * PRODUCTO-MODALES.JS - Modales de Producto (Agregar/Editar)
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
            const btn = formAgregar.querySelector('button[type="submit"]');
            const originalText = btn.innerHTML;

            try {
                btn.disabled = true;
                btn.innerHTML = `
                    <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Guardando...
                `;
                btn.classList.add('flex', 'items-center', 'justify-center');

                const res = await fetch('index.php?controlador=producto&accion=crear', {
                    method: 'POST',
                    body: new FormData(formAgregar)
                });
                const d = await res.json();
                if (d.success) {
                    mostrarNotificacion('¡Producto creado exitosamente!', 'success');
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    throw new Error(d.message || 'Error al crear producto');
                }
            } catch (e) {
                mostrarNotificacion(e.message, 'error');
                btn.disabled = false;
                btn.innerHTML = originalText;
                btn.classList.remove('flex', 'items-center', 'justify-center');
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
            const btn = formEditar.querySelector('button[type="submit"]');
            const originalText = btn.innerHTML;

            try {
                btn.disabled = true;
                btn.innerHTML = `
                    <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Actualizando...
                `;
                btn.classList.add('flex', 'items-center', 'justify-center');

                const res = await fetch('index.php?controlador=producto&accion=actualizar', {
                    method: 'POST',
                    body: new FormData(formEditar)
                });
                const d = await res.json();
                if (d.success) {
                    mostrarNotificacion('¡Producto actualizado correctamente!', 'success');
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    throw new Error(d.message || 'Error al actualizar');
                }
            } catch (e) {
                mostrarNotificacion(e.message, 'error');
                btn.disabled = false;
                btn.innerHTML = originalText;
                btn.classList.remove('flex', 'items-center', 'justify-center');
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

// Exponer globalmente
window.ProductoModales = {
    init: inicializarModalesProducto,
    actualizarPreview: actualizarPreviewEditar
};
window.inicializarModalesProducto = inicializarModalesProducto;
window.actualizarPreviewEditar = actualizarPreviewEditar;
