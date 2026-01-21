/**
 * =========================================================================
 * STOCK-ALERTS.JS - Alertas de Stock Bajo
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
            Endpoints.actualizarUmbralStock(val);
            mostrarNotificacion('Umbral actualizado.', 'success');
            verificarAlertasStock();
        }
    });

    // Verificar alertas iniciales y periódicamente
    verificarAlertasStock();
    setInterval(verificarAlertasStock, 600000); // Cada 10 minutos
}

async function verificarAlertasStock() {
    const umbral = localStorage.getItem('stockUmbral') || 10;
    try {
        const data = await Endpoints.obtenerAlertas(umbral);

        if (data.agotado?.length > 0) {
            mostrarNotificacion(`AGOTADO: ${data.agotado[0].nombre}`, 'error');
        } else if (data.bajo?.length > 0) {
            mostrarNotificacion(`Stock bajo: ${data.bajo[0].nombre}`, 'warning');
        }
    } catch (e) {
        console.error('Error verificando alertas:', e);
    }
}

// Exponer globalmente
window.StockAlerts = {
    configurar: configurarAlertasStock,
    verificar: verificarAlertasStock
};
window.configurarAlertasStock = configurarAlertasStock;
window.verificarAlertasStock = verificarAlertasStock;
