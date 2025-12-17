/**
 * =========================================================================
 * COMPRAS-INDEX.JS - Módulo de Lista de Compras
 * =========================================================================
 */

console.log('[ComprasIndex] Módulo cargando...');

// =========================================================================
// MODAL DE CONFIRMACIÓN DE PAGO
// =========================================================================
function confirmarPagoCompra(id, factura) {
    const modal = document.getElementById('modal-pago-compra');
    const backdrop = document.getElementById('backdrop-pago');
    const panel = document.getElementById('panel-pago');

    if (!modal) return;

    document.getElementById('pago-compra-id').value = id;
    document.getElementById('pago-factura-ref').textContent = factura;

    modal.classList.remove('hidden');
    setTimeout(() => {
        backdrop?.classList.remove('opacity-0');
        panel?.classList.remove('opacity-0', 'translate-y-4', 'sm:translate-y-0', 'sm:scale-95');
    }, 10);
}

function cerrarModalPago() {
    const modal = document.getElementById('modal-pago-compra');
    const backdrop = document.getElementById('backdrop-pago');
    const panel = document.getElementById('panel-pago');

    if (!modal) return;

    backdrop?.classList.add('opacity-0');
    panel?.classList.add('opacity-0', 'translate-y-4', 'sm:translate-y-0', 'sm:scale-95');
    setTimeout(() => {
        modal.classList.add('hidden');
    }, 300);
}

// =========================================================================
// EXPORTAR AL SCOPE GLOBAL
// =========================================================================
window.confirmarPagoCompra = confirmarPagoCompra;
window.cerrarModalPago = cerrarModalPago;
