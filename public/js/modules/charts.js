/**
 * =========================================================================
 * CHARTS.JS - Gráficos del Dashboard
 * =========================================================================
 * Lógica para renderizar gráficos con Chart.js
 */

// Referencias a instancias de gráficos
let chartValorCategoria = null;
let chartStockCategoria = null;

/**
 * Actualiza los gráficos del dashboard
 */
async function actualizarCharts() {
    const canvasValor = document.getElementById('chartValorCategoria');
    if (!canvasValor) return;

    try {
        const response = await fetch('index.php?controlador=dashboard&accion=apiDatosGraficos');
        if (!response.ok) throw new Error('Error al cargar gráficos');

        const data = await response.json();
        const labels = data.labels;

        // Gráfico de Valor por Categoría
        const ctxValor = canvasValor.getContext('2d');
        if (ctxValor) {
            if (chartValorCategoria) chartValorCategoria.destroy();

            chartValorCategoria = new Chart(ctxValor, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Costo Total',
                            data: data.datasets.valor.costos,
                            backgroundColor: 'rgba(220, 53, 69, 0.6)'
                        },
                        {
                            label: 'Ganancia Total',
                            data: data.datasets.valor.ganancias,
                            backgroundColor: 'rgba(40, 167, 69, 0.6)'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: { stacked: true },
                        y: { stacked: true }
                    },
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }

        // Gráfico de Stock por Categoría
        const canvasStock = document.getElementById('chartStockCategoria');
        if (canvasStock) {
            const ctxStock = canvasStock.getContext('2d');
            if (chartStockCategoria) chartStockCategoria.destroy();

            chartStockCategoria = new Chart(ctxStock, {
                type: 'polarArea',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Stock',
                        data: data.datasets.stock.stocks,
                        backgroundColor: [
                            'rgba(54, 162, 235, 0.7)',
                            'rgba(255, 206, 86, 0.7)',
                            'rgba(75, 192, 192, 0.7)',
                            'rgba(153, 102, 255, 0.7)',
                            'rgba(255, 159, 64, 0.7)'
                        ],
                        borderColor: '#fff',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }

    } catch (error) {
        console.error('Error al cargar gráficos:', error);
    }
}

/**
 * Destruye todos los gráficos
 */
function destruirCharts() {
    if (chartValorCategoria) {
        chartValorCategoria.destroy();
        chartValorCategoria = null;
    }
    if (chartStockCategoria) {
        chartStockCategoria.destroy();
        chartStockCategoria = null;
    }
}

// Exportar al scope global
window.Charts = {
    update: actualizarCharts,
    destroy: destruirCharts
};

// Compatibilidad
window.actualizarCharts = actualizarCharts;
