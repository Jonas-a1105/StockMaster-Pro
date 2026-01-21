<?php
namespace App\Controllers;
 
use App\Core\BaseController;
use App\Services\DashboardService;
use App\Models\VentaModel;
use App\Core\Session;
use App\Domain\Enums\UserPlan;
 
class DashboardController extends BaseController {
    private $dashboardService;
 
    public function __construct() {
        parent::__construct();
 
        // Premium Check using Session wrapper and Enums
        if ($this->userPlan === UserPlan::FREE->value) {
            return $this->response->redirect('index.php?controlador=free');
        }
        $this->dashboardService = new DashboardService();
    }

    public function index() {
        $userId = Session::get('user_id');
        $umbral = Session::get('stock_umbral', 10);
        $data = $this->dashboardService->getDashboardData($userId, $umbral);
        return $this->response->view('dashboard/index', $data);
    }

    /**
     * API: Datos para el gráfico de categorías
     */
    public function apiDatosGraficos() {
        $userId = Session::get('user_id');
        $respuesta = $this->dashboardService->getChartData($userId);
        return $this->response->json($respuesta);
    }
    
    /**
     * API: Ventas por periodo para gráfico de líneas
     */
    public function apiVentasPeriodo() {
        $userId = Session::get('user_id');
        $dias = $this->request->query('dias', 7, 'int');
        $resultado = $this->dashboardService->getVentasPeriodo($userId, $dias);
        return $this->response->json($resultado);
    }
    
    /**
     * API: Top productos más vendidos
     */
    public function apiTopProductos() {
        $userId = Session::get('user_id');
        $limite = $this->request->query('limit', 10, 'int');
        $ventaModel = new \App\Models\VentaModel();
        $productos = $ventaModel->obtenerProductosMasVendidos($userId, null, null, $limite);
        return $this->response->json($productos);
    }

    /**
     * API: Estadísticas para el pie de página
     */
    public function apiFooterStats() {
        $userId = Session::get('user_id');
        if (!$userId) { 
            return $this->response->json(['error' => 'No session'], 401); 
        }
        $stats = $this->dashboardService->getFooterStats($userId);
        return $this->response->json($stats);
    }
}
