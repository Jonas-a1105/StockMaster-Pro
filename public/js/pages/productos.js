/**
 * =========================================================================
 * PRODUCTOS.JS - Módulo de Gestión de Productos/Inventario
 * =========================================================================
 * Este módulo maneja toda la lógica del inventario de productos:
 * - Búsqueda con debounce
 * - Renderizado dinámico de tabla
 * - Edición y creación de productos
 * - Exportación CSV/PDF
 * - Modo selección múltiple
 * - Eliminación masiva
 * - Conversión de monedas (USD/Bs)
 * 
 * Dependencias globales (deben estar definidas antes de cargar):
 * - window.tasaCambio
 * - window.currencyMode
 * - window.listaProveedores
 * - window.listaCategorias
 * - window.svgIcons
 */

console.log('[Productos] Módulo cargando...');

// =========================================================================
// VARIABLES DE ESTADO
// =========================================================================
var selectionMode = false;
var selectionTimeout = null;
var searchTimer = null;

// =========================================================================
// TOGGLE TOOLS (Herramientas de exportación/importación)
// =========================================================================
function toggleTools(checkbox) {
    const container = document.getElementById('tools-container');
    if (!container) return;

    if (checkbox.checked) {
        container.classList.remove('hidden');
    } else {
        container.classList.add('hidden');
    }
}

// =========================================================================
// BÚSQUEDA CON DEBOUNCE
// =========================================================================
function initBusqueda() {
    const searchInput = document.getElementById('busqueda-input');
    if (!searchInput) return;

    searchInput.addEventListener('input', (e) => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => {
            buscarProductos(e.target.value);
        }, 300);
    });
}

async function buscarProductos(term) {
    try {
        const productos = await Endpoints.buscarProductos(term);
        renderizarTabla(productos);
    } catch (e) {
        console.error('Error búsqueda:', e);
    }
}

// =========================================================================
// RENDERIZADO DE TABLA
// =========================================================================
function renderizarTabla(productos) {
    const tbody = document.getElementById('tabla-body');
    if (!tbody) return;

    if (productos.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="10" class="px-4 py-12 text-center">
                    <p class="text-slate-500 dark:text-slate-400">No se encontraron productos</p>
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = productos.map(p => {
        const valorStock = p.precioVentaUSD * p.stock;
        const iconData = getIconData(p.nombre, p.categoria);
        const stockClass = getStockClass(p.stock);

        return `
            <tr class="group hover:bg-slate-50 dark:hover:bg-slate-700/50 border-b border-slate-100 dark:border-slate-700"
                data-id="${p.id}"
                data-precio-compra-usd="${p.precioCompraUSD}"
                data-precio-venta-usd="${p.precioVentaUSD}"
                data-ganancia-usd="${p.gananciaUnitariaUSD}">
                
                <td class="selection-col px-2 py-4 text-center">
                    <input type="checkbox" name="selected_products[]" value="${p.id}" 
                           onchange="updateBulkDeleteState()"
                           class="w-4 h-4 rounded border-slate-300 text-emerald-500 focus:ring-emerald-500/30">
                </td>
                
                <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br ${iconData.color} flex items-center justify-center flex-shrink-0">
                            ${iconData.svg}
                        </div>
                        <div class="min-w-0">
                            <p class="font-medium text-slate-800 dark:text-white truncate">${escapeHTML(p.nombre)}</p>
                            <p class="text-xs text-slate-400 truncate">${escapeHTML(p.categoria || 'Sin categoría')}</p>
                        </div>
                    </div>
                </td>
                
                <td class="px-6 py-4 text-center">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-sm font-semibold ${stockClass}">
                        ${p.stock}
                    </span>
                </td>
                
                <td class="px-6 py-4 text-center">
                    ${p.tiene_iva ? `<span class="px-2 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 text-xs font-medium rounded-lg">${parseInt(p.iva_porcentaje)}%</span>` : '<span class="text-slate-400 text-xs">—</span>'}
                </td>
                
                <td class="px-6 py-4 text-right">
                    ${wrapPrice(p.precioCompraUSD, 'text-slate-600 dark:text-slate-300')}
                </td>
                
                <td class="px-6 py-4 text-right">
                    ${wrapPrice(p.precioVentaUSD, 'font-semibold text-slate-800 dark:text-white', 'text-emerald-600 dark:text-emerald-400')}
                </td>
                
                <td class="px-6 py-4 text-right">
                    <div class="flex flex-col items-end currency-wrapper" data-usd="${p.gananciaUnitariaUSD}" data-text-class="text-emerald-600 dark:text-emerald-400">
                        <span class="price-main font-mono text-emerald-600 dark:text-emerald-400">+$${parseFloat(p.gananciaUnitariaUSD).toFixed(2)}</span>
                        <span class="price-sec block text-xs text-slate-400">Bs. --</span>
                    </div>
                    <span class="block text-xs text-slate-400 mt-0.5">${p.margen_ganancia || 30}% margen</span>
                </td>
                
                <td class="px-6 py-4 text-right hidden xl:table-cell">
                    ${wrapPrice(valorStock, 'font-semibold text-slate-800 dark:text-white')}
                </td>
                
                <td class="px-6 py-4 text-center">
                    <div class="flex items-center justify-center gap-1">
                        <button onclick="editarProducto(${p.id})" class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg transition-colors" title="Editar">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </button>
                        <form action="index.php?controlador=producto&accion=eliminar" method="POST" class="inline form-eliminar" onsubmit="confirmarEliminar(event, this)">
                            <input type="hidden" name="id" value="${p.id}">
                            <button type="submit" class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition-colors" title="Eliminar">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
        `;
    }).join('');

    // Actualizar conversiones
    actualizarPreciosDOM();
}

// =========================================================================
// HELPERS DE RENDERIZADO
// =========================================================================
function getIconData(nombre, categoria) {
    const mappings = {
        coffee: ['cafe', 'café', 'coffee', 'espresso', 'late', 'capuchino'],
        cookie: ['galleta', 'cookie', 'dulce', 'caramelo', 'chocolate', 'snack', 'confite'],
        bread: ['pan', 'harina', 'sandwich', 'torta', 'pastel', 'trigo', 'masa'],
        drink: ['refresco', 'jugo', 'bebida', 'agua', 'gaseosa', 'coca', 'pepsi', 'liquido'],
        droplet: ['aceite', 'salsa', 'vinagre', 'lubricante'],
        meat: ['carne', 'pollo', 'res', 'cerdo', 'embutido', 'jamon'],
        fish: ['pescado', 'atun', 'sardina', 'marisco'],
        carrot: ['fruta', 'verdura', 'vegetal', 'zanahoria', 'tomate', 'cebolla', 'papa'],
        tag: ['ropa', 'camisa', 'pantalon', 'zapato', 'vestido'],
        device: ['telefono', 'celular', 'laptop', 'computadora', 'mouse', 'teclado', 'cable', 'cargador'],
        tool: ['herramienta', 'martillo', 'clavo', 'tornillo', 'taladro'],
        medicine: ['medicina', 'pastilla', 'jarabe', 'farmacia', 'salud'],
        box: ['caja', 'paquete', 'bulto']
    };

    const colors = {
        coffee: 'from-amber-100 to-amber-200 text-amber-600',
        cookie: 'from-orange-100 to-orange-200 text-orange-600',
        bread: 'from-yellow-100 to-yellow-200 text-yellow-600',
        drink: 'from-blue-100 to-blue-200 text-blue-600',
        droplet: 'from-emerald-100 to-emerald-200 text-emerald-600',
        meat: 'from-red-100 to-red-200 text-red-600',
        fish: 'from-cyan-100 to-cyan-200 text-cyan-600',
        carrot: 'from-green-100 to-green-200 text-green-600',
        tag: 'from-purple-100 to-purple-200 text-purple-600',
        device: 'from-indigo-100 to-indigo-200 text-indigo-600',
        tool: 'from-slate-100 to-slate-200 text-slate-600',
        medicine: 'from-pink-100 to-pink-200 text-pink-600',
        box: 'from-slate-100 to-slate-200 text-slate-500'
    };

    const defaultSvg = '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>';

    const searchText = ((nombre || '') + ' ' + (categoria || '')).toLowerCase();

    for (const [icon, keywords] of Object.entries(mappings)) {
        if (keywords.some(kw => searchText.includes(kw))) {
            return {
                svg: window.svgIcons?.[icon] || defaultSvg,
                color: colors[icon] || colors.box
            };
        }
    }

    return { svg: defaultSvg, color: colors.box };
}

function getStockClass(stock) {
    if (stock <= 0) return 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400';
    if (stock <= 5) return 'bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400';
    return 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400';
}

function wrapPrice(usd, mainClass = '', secClass = 'text-slate-400') {
    const mode = window.currencyMode || 'mixed';
    const tasa = window.tasaCambio || 1;
    const usdVal = parseFloat(usd) || 0;
    const bsVal = usdVal * tasa;

    if (mode === 'usd') {
        return `<span class="font-mono ${mainClass}">$${usdVal.toFixed(2)}</span>`;
    } else if (mode === 'ves') {
        return `<span class="font-mono ${secClass}">Bs. ${bsVal.toFixed(2)}</span>`;
    } else {
        return `
            <div class="flex flex-col items-end currency-wrapper" data-usd="${usdVal}">
                <span class="price-main font-mono ${mainClass}">$${usdVal.toFixed(2)}</span>
                <span class="price-sec text-xs ${secClass}">Bs. ${bsVal.toFixed(2)}</span>
            </div>
        `;
    }
}

// =========================================================================
// CONVERSIÓN DE MONEDAS
// =========================================================================
function actualizarPreciosDOM() {
    const mode = window.currencyMode || 'mixed';
    // Prioritize ExchangeRate module tasa, fallback to window.tasaCambio or 1
    const tasa = (window.ExchangeRate && window.ExchangeRate.tasa) ? window.ExchangeRate.tasa : (window.tasaCambio || 1);

    document.querySelectorAll('.currency-wrapper').forEach(wrapper => {
        const usd = parseFloat(wrapper.dataset.usd) || 0;
        const bs = usd * tasa;
        const mainEl = wrapper.querySelector('.price-main');
        const secEl = wrapper.querySelector('.price-sec');

        if (!mainEl || !secEl) return;

        // Formatter helper
        const formatBS = (val) => `Bs. ${val.toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
        const formatUSD = (val) => `$${val.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

        if (mode === 'usd') {
            mainEl.textContent = formatUSD(usd);
            secEl.style.display = 'none';
        } else if (mode === 'ves') {
            // In VES mode, main element shows BS
            mainEl.textContent = formatBS(bs);
            secEl.style.display = 'none';
        } else {
            // Mixed mode
            mainEl.textContent = formatUSD(usd);
            secEl.textContent = formatBS(bs);
            secEl.style.display = 'block';
        }
    });

    // Also update any generic data-currency-usd elements if they are not inside wrappers
    document.querySelectorAll('[data-currency-usd]').forEach(el => {
        if (el.closest('.currency-wrapper')) return;
        const usd = parseFloat(el.dataset.currencyUsd) || 0;
        const bs = usd * tasa;
        el.textContent = `Bs. ${bs.toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
    });
}

function initCurrencySelector() {
    const selector = document.getElementById('currency-display-mode');
    if (!selector) return;

    // Restaurar modo guardado
    selector.value = window.currencyMode || 'mixed';

    selector.addEventListener('change', (e) => {
        window.currencyMode = e.target.value;
        localStorage.setItem('currencyMode', e.target.value);
        actualizarPreciosDOM();
    });
}

// =========================================================================
// EDITAR PRODUCTO
// =========================================================================
async function editarProducto(id) {
    try {
        const p = await Endpoints.obtenerProducto(id);

        if (!p) throw new Error('Producto no encontrado');

        document.getElementById('editar-id').value = p.id;
        document.getElementById('editar-nombre').value = p.nombre;
        document.getElementById('editar-codigo-barras').value = p.codigo_barras || '';
        document.getElementById('editar-stock').value = p.stock || 0;
        document.getElementById('editar-precio-base').value = p.precio_base || 0;
        document.getElementById('editar-margen').value = p.margen_ganancia || 30;

        // IVA
        const tieneIva = document.getElementById('editar-tiene-iva');
        const ivaGrupo = document.getElementById('editar-iva-grupo');
        const ivaPorcentaje = document.getElementById('editar-iva-porcentaje');

        if (tieneIva) {
            tieneIva.checked = p.tiene_iva == 1;
            if (ivaGrupo) {
                if (tieneIva.checked) {
                    ivaGrupo.classList.remove('hidden');
                } else {
                    ivaGrupo.classList.add('hidden');
                }
            }
        }
        if (ivaPorcentaje) ivaPorcentaje.value = p.iva_porcentaje || 16;

        // Proveedor combobox
        if (typeof window.setComboboxValue === 'function' && document.getElementById('combobox-proveedor-edit')) {
            window.setComboboxValue('combobox-proveedor-edit', p.proveedor_id, window.listaProveedores);
        }

        // Calcular preview
        calcularPreviewEditar();

        openModal('modal-editar-producto');
    } catch (e) {
        mostrarNotificacion(e.message, 'error');
    }
}

// =========================================================================
// TOGGLE IVA
// =========================================================================
function initIvaToggles() {
    // Modal Agregar
    const addIvaToggle = document.getElementById('add-tiene-iva');
    const addIvaGroup = document.getElementById('add-iva-grupo');
    if (addIvaToggle && addIvaGroup) {
        addIvaToggle.addEventListener('change', () => {
            if (addIvaToggle.checked) {
                addIvaGroup.classList.remove('hidden');
            } else {
                addIvaGroup.classList.add('hidden');
            }
            calcularPreviewAgregar();
        });
    }

    // Modal Editar
    const editIvaToggle = document.getElementById('editar-tiene-iva');
    const editIvaGroup = document.getElementById('editar-iva-grupo');
    if (editIvaToggle && editIvaGroup) {
        editIvaToggle.addEventListener('change', () => {
            if (editIvaToggle.checked) {
                editIvaGroup.classList.remove('hidden');
            } else {
                editIvaGroup.classList.add('hidden');
            }
            calcularPreviewEditar();
        });
    }
}

// =========================================================================
// CÁLCULO DE PREVIEWS
// =========================================================================
function calcularPreviewAgregar() {
    const base = parseFloat(document.getElementById('add-precio-base')?.value) || 0;
    const margen = parseFloat(document.getElementById('add-margen')?.value) || 0;
    const tieneIva = document.getElementById('add-tiene-iva')?.checked;
    const ivaPct = parseFloat(document.getElementById('add-iva-porcentaje')?.value) || 0;

    const precioCompra = tieneIva ? base * (1 + ivaPct / 100) : base;
    const precioVenta = precioCompra * (1 + margen / 100);
    const ganancia = precioVenta - precioCompra;

    const elCompra = document.getElementById('add-preview-compra');
    const elVenta = document.getElementById('add-preview-venta');
    const elGanancia = document.getElementById('add-preview-ganancia');

    if (elCompra) elCompra.textContent = `$${precioCompra.toFixed(2)}`;
    if (elVenta) elVenta.textContent = `$${precioVenta.toFixed(2)}`;
    if (elGanancia) elGanancia.textContent = `$${ganancia.toFixed(2)}`;
}

function calcularPreviewEditar() {
    const base = parseFloat(document.getElementById('editar-precio-base')?.value) || 0;
    const margen = parseFloat(document.getElementById('editar-margen')?.value) || 0;
    const tieneIva = document.getElementById('editar-tiene-iva')?.checked;
    const ivaPct = parseFloat(document.getElementById('editar-iva-porcentaje')?.value) || 0;

    const precioCompra = tieneIva ? base * (1 + ivaPct / 100) : base;
    const precioVenta = precioCompra * (1 + margen / 100);
    const ganancia = precioVenta - precioCompra;

    const elCompra = document.getElementById('preview-precio-compra');
    const elVenta = document.getElementById('preview-precio-venta');
    const elGanancia = document.getElementById('preview-ganancia');

    if (elCompra) elCompra.textContent = `$${precioCompra.toFixed(2)}`;
    if (elVenta) elVenta.textContent = `$${precioVenta.toFixed(2)}`;
    if (elGanancia) elGanancia.textContent = `$${ganancia.toFixed(2)}`;
}

function initPreviewListeners() {
    // Agregar
    document.getElementById('add-precio-base')?.addEventListener('input', calcularPreviewAgregar);
    document.getElementById('add-margen')?.addEventListener('input', calcularPreviewAgregar);
    document.getElementById('add-iva-porcentaje')?.addEventListener('input', calcularPreviewAgregar);

    // Editar
    document.getElementById('editar-precio-base')?.addEventListener('input', calcularPreviewEditar);
    document.getElementById('editar-margen')?.addEventListener('input', calcularPreviewEditar);
    document.getElementById('editar-iva-porcentaje')?.addEventListener('input', calcularPreviewEditar);
    document.getElementById('editar-tiene-iva')?.addEventListener('change', calcularPreviewEditar);
}

// =========================================================================
// EXPORTAR/IMPORTAR
// =========================================================================
function exportarInventario(formato) {
    window.location.href = `index.php?controlador=producto&accion=exportar&formato=${formato}`;
}

function exportarInventarioPDF() {
    // Auto-configurar jsPDF si viene del UMD bundle
    if (!window.jsPDF && window.jspdf) {
        window.jsPDF = window.jspdf.jsPDF;
    }

    if (!window.jsPDF) {
        mostrarNotificacion('Error: Librería PDF no disponible. Recarga la página.', 'error');
        return;
    }

    const filas = document.querySelectorAll('#tabla-body tr:not([id="row-empty"])');
    if (filas.length === 0) {
        mostrarNotificacion('No hay productos para exportar', 'error');
        return;
    }

    const doc = new window.jsPDF();
    const fecha = new Date().toLocaleString();

    doc.setFontSize(18);
    doc.setTextColor(40);
    doc.text('Inventario de Productos', 14, 22);

    doc.setFontSize(11);
    doc.setTextColor(100);
    doc.text(`Generado: ${fecha}`, 14, 30);

    const datos = [];
    filas.forEach(fila => {
        const producto = fila.querySelector('td p.font-medium')?.textContent?.trim() || '';
        const stockCell = fila.querySelectorAll('td')[2];
        const stock = stockCell?.querySelector('span')?.textContent?.trim() || '0';
        const precioCompra = fila.dataset.precioCompraUsd || '0';
        const precioVenta = fila.dataset.precioVentaUsd || '0';
        const ganancia = fila.dataset.gananciaUsd || '0';

        if (producto) {
            datos.push([
                producto,
                stock,
                `$${parseFloat(precioCompra).toFixed(2)}`,
                `$${parseFloat(precioVenta).toFixed(2)}`,
                `$${parseFloat(ganancia).toFixed(2)}`
            ]);
        }
    });

    doc.autoTable({
        head: [['Producto', 'Stock', 'P. Compra', 'P. Venta', 'Ganancia']],
        body: datos,
        startY: 40,
        theme: 'grid',
        headStyles: { fillColor: [16, 185, 129] },
        styles: { fontSize: 9 }
    });

    doc.save(`inventario_${new Date().toISOString().split('T')[0]}.pdf`);
    mostrarNotificacion('PDF generado correctamente', 'success');
}

function importarInventario(input) {
    if (!input.files[0]) return;

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'index.php?controlador=producto&accion=importar';
    form.enctype = 'multipart/form-data';

    const fileInput = input.cloneNode(true);
    fileInput.name = 'archivo_csv';
    form.appendChild(fileInput);

    document.body.appendChild(form);
    form.submit();
}

// =========================================================================
// MODO SELECCIÓN MÚLTIPLE
// =========================================================================
function toggleSelectionMode() {
    selectionMode = !selectionMode;
    const knob = document.getElementById('selection-switch-knob');
    const bg = document.getElementById('selection-switch-bg');
    const bulkWrapper = document.getElementById('bulk-actions-wrapper');
    const table = document.getElementById('tabla-inventario');
    const btn = document.getElementById('toggle-selection-mode');

    clearTimeout(selectionTimeout);

    if (selectionMode) {
        knob.style.transform = 'translateX(16px)';
        bg.classList.remove('bg-slate-300', 'dark:bg-slate-500');
        bg.classList.add('bg-emerald-500');
        btn.classList.add('bg-slate-200', 'dark:bg-slate-600');

        table.classList.add('selection-visible');

        // Remove hidden class first to allow display
        bulkWrapper.classList.remove('hidden');

        // Trigger reflow/animation
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                bulkWrapper.classList.add('expanded');
                table.classList.add('selection-expanded');
            });
        });
    } else {
        knob.style.transform = 'translateX(0px)';
        bg.classList.add('bg-slate-300', 'dark:bg-slate-500');
        bg.classList.remove('bg-emerald-500');
        btn.classList.remove('bg-slate-200', 'dark:bg-slate-600');

        bulkWrapper.classList.remove('expanded');
        table.classList.remove('selection-expanded');

        selectionTimeout = setTimeout(() => {
            if (!selectionMode) {
                // Add hidden class after animation
                bulkWrapper.classList.add('hidden');
                table.classList.remove('selection-visible');
                document.querySelectorAll('input[name="selected_products[]"]').forEach(cb => cb.checked = false);
                updateBulkDeleteState();
            }
        }, 450); // Matches transition duration
    }
}

function toggleSelectAll() {
    const checkboxes = document.querySelectorAll('input[name="selected_products[]"]');
    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
    checkboxes.forEach(cb => cb.checked = !allChecked);
    updateBulkDeleteState();
}

function updateBulkDeleteState() {
    const checkboxes = document.querySelectorAll('input[name="selected_products[]"]:checked');
    const count = checkboxes.length;
    const btn = document.getElementById('btn-delete-bulk');
    const counter = document.getElementById('count-selected');

    if (counter) counter.textContent = count;

    if (btn) {
        if (count > 0) {
            btn.disabled = false;
            btn.classList.remove('opacity-50', 'cursor-not-allowed');
        } else {
            btn.disabled = true;
            btn.classList.add('opacity-50', 'cursor-not-allowed');
        }
    }
}

// =========================================================================
// ELIMINACIÓN
// =========================================================================
function confirmarEliminar(event, form) {
    event.preventDefault();
    window.formParaEliminar = form;
    openModal('modal-eliminar-producto');
}

/**
 * Eliminar un producto individual por ID (usado en botones onclick)
 */
function eliminarProducto(id) {
    window.productoIdParaEliminar = id;
    openModal('modal-eliminar-producto');
}

function initDeleteConfirmation() {
    document.getElementById('btn-confirmar-borrar-producto')?.addEventListener('click', async () => {
        // Si hay un formulario pendiente (tabla dinámica), usarlo
        if (window.formParaEliminar) {
            window.formParaEliminar.submit();
            closeModal('modal-eliminar-producto');
            return;
        }

        // Si hay un ID pendiente (botón directo), hacer fetch
        if (window.productoIdParaEliminar) {
            try {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'index.php?controlador=producto&accion=eliminar';

                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'id';
                input.value = window.productoIdParaEliminar;
                form.appendChild(input);

                document.body.appendChild(form);
                form.submit();
            } catch (e) {
                mostrarNotificacion('Error al eliminar: ' + e.message, 'error');
            }
            closeModal('modal-eliminar-producto');
        }
    });
}

function confirmarEliminacionMasiva() {
    const checkboxes = document.querySelectorAll('input[name="selected_products[]"]:checked');
    if (checkboxes.length === 0) {
        mostrarNotificacion('Selecciona al menos un producto', 'warning');
        return;
    }

    window.idsParaEliminarMasivo = Array.from(checkboxes).map(cb => cb.value);

    const countEl = document.getElementById('count-eliminar-masivo');
    if (countEl) countEl.textContent = window.idsParaEliminarMasivo.length;

    if (window.resetModalEliminacionMasiva) window.resetModalEliminacionMasiva();

    openModal('modal-eliminar-masivo');
}

async function ejecutarEliminacionMasiva(ids) {
    try {
        const data = await Endpoints.eliminarMasivo(ids);

        if (data.success) {
            mostrarNotificacion(`${data.eliminados} productos eliminados`, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            throw new Error(data.message || 'Error al eliminar');
        }
    } catch (e) {
        mostrarNotificacion(e.message, 'error');
    }

    closeModal('modal-eliminar-masivo');
}

function procesarEliminacionMasiva() {
    const inputVerif = document.getElementById('input-verificacion-eliminar');
    if (!inputVerif || inputVerif.value.trim().toUpperCase() !== 'ELIMINAR') return;

    if (window.idsParaEliminarMasivo && window.idsParaEliminarMasivo.length > 0) {
        ejecutarEliminacionMasiva(window.idsParaEliminarMasivo);
    }
}

function initMassDeleteModal() {
    const inputVerif = document.getElementById('input-verificacion-eliminar');
    const btnMasivo = document.getElementById('btn-confirmar-eliminar-masivo');

    if (!inputVerif || !btnMasivo) return;

    function validarInput() {
        const isValid = inputVerif.value.trim().toUpperCase() === 'ELIMINAR';
        btnMasivo.disabled = !isValid;

        if (isValid) {
            inputVerif.classList.remove('border-slate-300', 'dark:border-slate-600');
            inputVerif.classList.add('border-green-500', 'ring-2', 'ring-green-500/30');
            btnMasivo.classList.remove('opacity-50', 'cursor-not-allowed');
        } else {
            inputVerif.classList.add('border-slate-300', 'dark:border-slate-600');
            inputVerif.classList.remove('border-green-500', 'ring-2', 'ring-green-500/30');
            btnMasivo.classList.add('opacity-50', 'cursor-not-allowed');
        }
    }

    inputVerif.addEventListener('input', validarInput);
    inputVerif.addEventListener('keyup', validarInput);
    inputVerif.addEventListener('change', validarInput);

    inputVerif.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && inputVerif.value.trim().toUpperCase() === 'ELIMINAR') {
            e.preventDefault();
            procesarEliminacionMasiva();
        }
    });

    window.resetModalEliminacionMasiva = function () {
        inputVerif.value = '';
        btnMasivo.disabled = true;
        inputVerif.classList.remove('border-green-500', 'ring-2', 'ring-green-500/30');
        inputVerif.classList.add('border-slate-300', 'dark:border-slate-600');
        btnMasivo.classList.add('opacity-50', 'cursor-not-allowed');
    };
}

// =========================================================================
// COMBOBOXES
// =========================================================================
function initComboboxes() {
    if (typeof window.setupCombobox !== 'function') {
        console.warn('[Productos] setupCombobox no disponible, reintentando...');
        setTimeout(initComboboxes, 100);
        return;
    }

    // Proveedores - Agregar
    if (window.listaProveedores && document.getElementById('combobox-proveedor-add')) {
        window.setupCombobox(
            'combobox-proveedor-add',
            'proveedor-select-hidden',
            'proveedor-input-visual',
            'proveedor-list-add',
            'btn-limpiar-prov-add',
            { dataSource: window.listaProveedores, defaultLabel: 'Sin proveedor' }
        );
    }

    // Proveedores - Editar
    if (window.listaProveedores && document.getElementById('combobox-proveedor-edit')) {
        window.setupCombobox(
            'combobox-proveedor-edit',
            'editar-proveedor-hidden',
            'editar-proveedor-visual',
            'proveedor-list-edit',
            'btn-limpiar-prov-edit',
            { dataSource: window.listaProveedores, defaultLabel: 'Sin proveedor' }
        );
    }

    // Categorías - Agregar
    if (window.listaCategorias && document.getElementById('combobox-categoria-add')) {
        window.setupCombobox(
            'combobox-categoria-add',
            'categoria-select-hidden',
            'categoria-input-visual',
            'categoria-list-add',
            'btn-limpiar-cat-add',
            { dataSource: window.listaCategorias, defaultLabel: 'Seleccionar categoría', allowCustom: true }
        );
    }

    // Categorías - Editar
    if (window.listaCategorias && document.getElementById('combobox-categoria-edit')) {
        window.setupCombobox(
            'combobox-categoria-edit',
            'editar-categoria-hidden',
            'editar-categoria-visual',
            'categoria-list-edit',
            'btn-limpiar-cat-edit',
            { dataSource: window.listaCategorias, defaultLabel: 'Seleccionar categoría', allowCustom: true }
        );
    }
}

// =========================================================================
// INICIALIZACIÓN PRINCIPAL
// =========================================================================
function initProductos() {
    console.log('[Productos] Inicializando módulo...');

    initBusqueda();
    initCurrencySelector();
    initIvaToggles();
    initPreviewListeners();
    initDeleteConfirmation();
    initMassDeleteModal();
    initComboboxes();
    actualizarPreciosDOM();

    console.log('[Productos] Módulo inicializado ✓');
}

// =========================================================================
// EXPORTAR AL SCOPE GLOBAL
// =========================================================================
window.Productos = {
    init: initProductos,
    buscar: buscarProductos,
    renderizar: renderizarTabla,
    actualizarPrecios: actualizarPreciosDOM
};

// Funciones que deben estar globales para onclick en HTML
window.inicializarProductos = initProductos;
window.editarProducto = editarProducto;
// ... rest of globals ...
window.toggleTools = toggleTools;
