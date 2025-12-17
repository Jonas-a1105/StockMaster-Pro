<?php
namespace App\Controllers;

use App\Models\Producto;
use App\Models\Movimiento;
use App\Models\VentaModel;
use App\Core\Session;

class DashboardController {

    public function __construct() {
        Session::init();
        if (($_SESSION['user_plan'] ?? 'free') === 'free') {
            redirect('index.php?controlador=free');
        }
    }

    public function index() {
        $userId = $_SESSION['user_id'];
        $productoModel = new Producto();
        $movimientoModel = new Movimiento();
        $ventaModel = new VentaModel();
        
        $umbral = $_SESSION['stock_umbral'] ?? 10;
        
        // KPIs
        $kpis = $productoModel->obtenerKPIsDashboard($userId, $umbral); 
        $gananciaPotencial = $kpis['valorTotalVentaUSD'] - $kpis['valorTotalCostoUSD'];
        
        // Últimos movimientos
        $ultimosMovimientos = $movimientoModel->obtenerTodos($userId, 5);
        
        // Top 5 productos más vendidos (para el dashboard)
        $topProductos = $ventaModel->obtenerProductosMasVendidos($userId, null, null, 5);
        
        // Estadísticas de ventas del mes actual
        $inicioMes = date('Y-m-01');
        $estadisticasVentas = $ventaModel->obtenerEstadisticasVentas($userId, $inicioMes);

        $this->render('dashboard/index', [
            'valorTotalVentaUSD' => $kpis['valorTotalVentaUSD'],
            'valorTotalCostoUSD' => $kpis['valorTotalCostoUSD'],
            'gananciaPotencialUSD' => $gananciaPotencial,
            'stockBajo' => $kpis['stockBajo'],
            'ultimosMovimientos' => $ultimosMovimientos,
            'topProductos' => $topProductos,
            'estadisticasVentas' => $estadisticasVentas
        ]);
    }

    public function apiDatosGraficos() {
        if (($_SESSION['user_plan'] ?? 'free') === 'free') { exit; }
        
        $userId = $_SESSION['user_id'];
        $productoModel = new Producto();
        $datos = $productoModel->obtenerDatosParaGraficos($userId);

        $labels = []; $costos = []; $ganancias = []; $stocks = [];
        foreach ($datos as $item) {
            $labels[] = $item['categoria'];
            $costos[] = (float)$item['totalCosto'];
            $ganancias[] = (float)$item['totalGanancia'];
            $stocks[] = (int)$item['totalStock'];
        }
        $respuesta = [
            'labels' => $labels,
            'datasets' => [
                'valor' => ['costos' => $costos, 'ganancias' => $ganancias],
                'stock' => ['stocks' => $stocks]
            ]
        ];
        header('Content-Type: application/json');
        echo json_encode($respuesta);
        exit;
    }
    
    /**
     * API: Obtener ventas por período para gráfico de líneas
     */
    public function apiVentasPeriodo() {
        header('Content-Type: application/json');
        if (($_SESSION['user_plan'] ?? 'free') === 'free') { 
            echo json_encode(['error' => 'Premium required']);
            exit; 
        }
        
        $userId = $_SESSION['user_id'];
        $dias = (int)($_GET['dias'] ?? 7);
        
        $ventaModel = new VentaModel();
        $ventas = $ventaModel->obtenerVentasPorDia($userId, $dias);
        
        // Rellenar días sin ventas con 0
        $resultado = [];
        $fechaActual = new \DateTime();
        $fechaInicio = (clone $fechaActual)->modify("-{$dias} days");
        
        // Crear mapa de ventas por fecha
        $ventasPorFecha = [];
        foreach ($ventas as $v) {
            $ventasPorFecha[$v['fecha']] = $v;
        }
        
        // Generar todos los días
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
        
        echo json_encode($resultado);
        exit;
    }
    
    /**
     * API: Obtener top productos más vendidos
     */
    public function apiTopProductos() {
        header('Content-Type: application/json');
        if (($_SESSION['user_plan'] ?? 'free') === 'free') { 
            echo json_encode(['error' => 'Premium required']);
            exit; 
        }
        
        $userId = $_SESSION['user_id'];
        $limite = (int)($_GET['limit'] ?? 10);
        
        $ventaModel = new VentaModel();
        $productos = $ventaModel->obtenerProductosMasVendidos($userId, null, null, $limite);
        
        echo json_encode($productos);
        exit;
    }

    /**
     * API: Obtener datos para el footer (Tasa y Valor Inventario)
     */
    public function apiFooterStats() {
        header('Content-Type: application/json');
        
        // Permitir incluso en free (aunque idealmente es premium, mostramos datos básicos o 0)
        // si se quiere restringir: if (($_SESSION['user_plan'] ?? 'free') === 'free') ...
        
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) { echo json_encode(['error' => 'No session']); exit; }

        $productoModel = new Producto();
        $kpis = $productoModel->obtenerKPIsDashboard($userId, 0); // Umbral 0 pq no nos importa stock bajo aquí
        
        echo json_encode([
            'valor_inventario_usd' => $kpis['valorTotalCostoUSD'] ?? 0, // Valor al costo
            'valor_venta_usd' => $kpis['valorTotalVentaUSD'] ?? 0,
            'tasa_registrada' => $_SESSION['tasa_bcv'] ?? 0
        ]);
        exit;
    }

    private function render($vista, $data = []) {
        extract($data);
        $vistaContenido = __DIR__ . '/../../views/' . $vista . '.php';
        require __DIR__ . '/../../views/layouts/main.php';
    }
}