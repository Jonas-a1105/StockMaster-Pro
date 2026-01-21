<?php
namespace App\Services;

use App\Models\Producto;
use App\Models\Movimiento;
use App\Models\VentaModel;

class DashboardService {
    private $productoModel;
    private $movimientoModel;
    private $ventaModel;

    public function __construct() {
        $this->productoModel = new Producto();
        $this->movimientoModel = new Movimiento();
        $this->ventaModel = new VentaModel();
    }

    public function getDashboardData($userId, $umbral = 10) {
        $kpis = $this->productoModel->obtenerKPIsDashboard($userId, $umbral);
        $gananciaPotencial = $kpis['valorTotalVentaUSD'] - $kpis['valorTotalCostoUSD'];
        
        $inicioMes = date('Y-m-01');
        
        return [
            'valorTotalVentaUSD' => $kpis['valorTotalVentaUSD'],
            'valorTotalCostoUSD' => $kpis['valorTotalCostoUSD'],
            'gananciaPotencialUSD' => $gananciaPotencial,
            'stockBajo' => $kpis['stockBajo'],
            'ultimosMovimientos' => $this->movimientoModel->obtenerTodos($userId, 5),
            'topProductos' => $this->ventaModel->obtenerProductosMasVendidos($userId, null, null, 5),
            'estadisticasVentas' => $this->ventaModel->obtenerEstadisticasVentas($userId, $inicioMes)
        ];
    }

    public function getChartData($userId) {
        $datos = $this->productoModel->obtenerDatosParaGraficos($userId);
        $labels = []; $costos = []; $ganancias = []; $stocks = [];
        foreach ($datos as $item) {
            $labels[] = $item['categoria'];
            $costos[] = (float)$item['totalCosto'];
            $ganancias[] = (float)$item['totalGanancia'];
            $stocks[] = (int)$item['totalStock'];
        }
        return [
            'labels' => $labels,
            'datasets' => [
                'valor' => ['costos' => $costos, 'ganancias' => $ganancias],
                'stock' => ['stocks' => $stocks]
            ]
        ];
    }

    public function getVentasPeriodo($userId, $dias = 7) {
        $ventas = $this->ventaModel->obtenerVentasPorDia($userId, $dias);
        $resultado = [];
        $fechaActual = new \DateTime();
        $fechaInicio = (clone $fechaActual)->modify("-{$dias} days");
        
        $ventasPorFecha = [];
        foreach ($ventas as $v) {
            $ventasPorFecha[$v['fecha']] = $v;
        }
        
        while ($fechaInicio <= $fechaActual) {
            $fechaStr = $fechaInicio->format('Y-m-d');
            $resultado[] = [
                'fecha' => $fechaStr,
                'label' => $fechaInicio->format('d/m'),
                'num_ventas' => $ventasPorFecha[$fechaStr]['num_ventas'] ?? 0,
                'total_usd' => (float)($ventasPorFecha[$fechaStr]['total_usd'] ?? 0)
            ];
            $fechaInicio->modify('+1 day');
        }
        return $resultado;
    }

    public function getFooterStats($userId) {
        $kpis = $this->productoModel->obtenerKPIsDashboard($userId, 0);
        return [
            'valor_inventario_usd' => $kpis['valorTotalCostoUSD'] ?? 0,
            'valor_venta_usd' => $kpis['valorTotalVentaUSD'] ?? 0,
            'tasa_registrada' => $_SESSION['tasa_bcv'] ?? 0
        ];
    }
}
