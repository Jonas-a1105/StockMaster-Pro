/**
 * =========================================================================
 * REPORTS.JS - Lógica de Reportes
 * =========================================================================
 * Exportación de reportes a PDF y CSV
 */

// Variable global para el reporte actual (solo inicializar si no existe)
if (!window.reporteActual || !window.reporteActual.datos || window.reporteActual.datos.length === 0) {
    window.reporteActual = {
        datos: [],
        columnas: [],
        titulo: ''
    };
}

/**
 * Exporta el reporte actual al formato especificado
 * @param {string} formato - 'pdf' o 'csv'
 */
function exportarReporte(formato) {
    let reporte = window.reporteActual;

    // Si no hay datos en la variable global, intentar leer de la tabla HTML
    if (!reporte || !reporte.datos || reporte.datos.length === 0) {
        reporte = leerReporteDeTabla();
    }

    if (!reporte || !reporte.datos || reporte.datos.length === 0) {
        mostrarNotificacion('No hay datos en pantalla para exportar. Genera un reporte primero.', 'error');
        return;
    }

    if (formato === 'pdf') {
        exportarPDF(reporte);
    } else if (formato === 'csv') {
        exportarCSV(reporte);
    }
}

/**
 * Lee los datos del reporte directamente de la tabla HTML (fallback)
 */
function leerReporteDeTabla() {
    const tabla = document.querySelector('.lg\\:col-span-3 table');
    if (!tabla) return null;

    const titulo = document.querySelector('.lg\\:col-span-3 h3')?.textContent?.trim() || 'Reporte';
    const columnas = [];
    const datos = [];

    // Leer columnas del thead
    tabla.querySelectorAll('thead th').forEach(th => {
        columnas.push(th.textContent.trim());
    });

    // Leer filas del tbody
    tabla.querySelectorAll('tbody tr').forEach(tr => {
        const fila = [];
        tr.querySelectorAll('td').forEach(td => {
            fila.push(td.textContent.trim());
        });
        if (fila.length > 0 && fila.some(c => c !== '')) {
            datos.push(fila);
        }
    });

    if (datos.length === 0) return null;

    // Actualizar la variable global
    window.reporteActual = { titulo, columnas, datos };
    return window.reporteActual;
}

/**
 * Exporta a PDF
 * @param {Object} reporte - Datos del reporte
 */
function exportarPDF(reporte) {
    // Auto-configurar jsPDF si viene del UMD bundle
    if (!window.jsPDF && window.jspdf) {
        window.jsPDF = window.jspdf.jsPDF;
    }

    if (!window.jsPDF) {
        mostrarNotificacion('Error: Librería PDF no disponible. Recarga la página.', 'error');
        return;
    }

    const doc = new window.jsPDF();
    const fecha = new Date().toLocaleString();

    // Título
    doc.setFontSize(18);
    doc.setTextColor(40);
    doc.text(reporte.titulo, 14, 22);

    // Fecha de generación
    doc.setFontSize(11);
    doc.setTextColor(100);
    doc.text(`Generado: ${fecha}`, 14, 30);

    // Tabla
    doc.autoTable({
        head: [reporte.columnas],
        body: reporte.datos,
        startY: 40,
        theme: 'grid',
        headStyles: { fillColor: [0, 123, 255] },
        styles: { fontSize: 8 }
    });

    doc.save(`${reporte.titulo}.pdf`);
}

/**
 * Exporta a CSV
 * @param {Object} reporte - Datos del reporte
 */
function exportarCSV(reporte) {
    let csv = reporte.columnas.join(',') + '\n';

    reporte.datos.forEach(row => {
        csv += row.map(c => `"${String(c).replace(/"/g, '""')}"`).join(',') + '\n';
    });

    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = `${reporte.titulo}.csv`;
    link.click();
}

/**
 * Configura el selector de tipo de reporte
 */
function configurarSelectorReporte() {
    const reporteTipoSelect = document.getElementById('reporte-tipo');
    const reporteProductoGroup = document.getElementById('reporte-producto-group');

    if (!reporteTipoSelect || !reporteProductoGroup) return;

    function toggleReporteProducto() {
        if (reporteTipoSelect.value === 'movimientos-producto') {
            reporteProductoGroup.style.display = 'block';
            const selectProd = reporteProductoGroup.querySelector('select');
            if (selectProd) selectProd.focus();
        } else {
            reporteProductoGroup.style.display = 'none';
        }
    }

    reporteTipoSelect.addEventListener('change', toggleReporteProducto);
    toggleReporteProducto();
}

// Exportar al scope global
window.Reports = {
    exportar: exportarReporte,
    exportarPDF,
    exportarCSV,
    configurarSelector: configurarSelectorReporte
};

// Compatibilidad
window.exportarReporte = exportarReporte;
