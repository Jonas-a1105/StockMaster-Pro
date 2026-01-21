<?php
namespace App\Controllers;

use App\Core\BaseController;
use App\Core\Session;
use App\Models\Producto;
use App\Models\VentaModel;
use App\Models\Cliente;
use App\Models\ApiKeyModel;
use App\Exceptions\UnauthorizedException;
use App\Exceptions\NotFoundException;
use App\Exceptions\AppException;

/**
 * API REST Controller
 * Soporta autenticación por sesión o API Key
 */
class ApiController extends BaseController {
    
    public function __construct() {
        parent::__construct();
        
        // La API soporta autenticación alternativa por API Key
        if ($this->userId <= 0) {
            $apiKey = $this->request->query('api_key') ?? $this->request->header('Authorization');
            if ($apiKey) {
                if (strpos($apiKey, 'Bearer ') === 0) {
                    $apiKey = substr($apiKey, 7);
                }
                if (!$this->validarApiKey($apiKey)) {
                    throw new UnauthorizedException('No autorizado para acceder a la API.');
                }
            } else {
                throw new UnauthorizedException('No autorizado para acceder a la API.');
            }
        }
    }
    
    private function validarApiKey($key) {
        $apiKeyModel = new ApiKeyModel();
        $userId = $apiKeyModel->validateKey($key);
        if ($userId) {
            $this->userId = $userId;
            return true;
        }
        return false;
    }
    
    // ==================== ENDPOINTS ====================
    
    public function productos() {
        $this->setCorsHeaders();
        
        if ($this->request->method() === 'GET') {
            $productoModel = new Producto();
            $buscar = $this->request->query('buscar', '');
            $limit = min($this->request->query('limit', 50, 'int'), 100);
            $offset = $this->request->query('offset', 0, 'int');
            
            $productos = $productoModel->obtenerTodos($this->userId, $buscar, $limit, $offset);
            $total = $productoModel->contarTodos($this->userId, $buscar);
            
            return $this->response->json([
                'success' => true,
                'data' => $productos,
                'meta' => [
                    'total' => $total,
                    'limit' => $limit,
                    'offset' => $offset,
                    'has_more' => ($offset + $limit) < $total
                ]
            ]);
        }
        
        throw new AppException('Método no permitido', 405);
    }
    
    public function producto() {
        $this->setCorsHeaders();
        $id = $this->request->query('id', 0, 'int');
        
        if ($id <= 0) {
            throw new AppException('ID de producto inválido', 400);
        }
        
        $productoModel = new Producto();
        $producto = $productoModel->obtenerPorId($this->userId, $id);
        
        if (!$producto) {
            throw new NotFoundException('Producto no encontrado');
        }
        
        return $this->response->json(['success' => true, 'data' => $producto]);
    }
    
    public function ventas() {
        $this->setCorsHeaders();
        
        $ventaModel = new VentaModel();
        $filtros = [
            'fecha_inicio' => $this->request->query('fecha_inicio'),
            'fecha_fin' => $this->request->query('fecha_fin'),
            'estado' => $this->request->query('estado'),
            'limit' => min($this->request->query('limit', 50, 'int'), 100)
        ];
        
        $ventas = $ventaModel->obtenerVentas($this->userId, $filtros);
        
        return $this->response->json([
            'success' => true,
            'data' => $ventas,
            'meta' => [
                'count' => count($ventas),
                'filtros' => array_filter($filtros)
            ]
        ]);
    }
    
    public function estadisticas() {
        $this->setCorsHeaders();
        
        $productoModel = new Producto();
        $ventaModel = new VentaModel();
        
        $umbral = $this->request->query('umbral_stock', 10, 'int');
        $kpis = $productoModel->obtenerKPIsDashboard($this->userId, $umbral);
        
        $inicioMes = date('Y-m-01');
        $estadisticasVentas = $ventaModel->obtenerEstadisticasVentas($this->userId, $inicioMes);
        
        return $this->response->json([
            'success' => true,
            'data' => [
                'inventario' => [
                    'valor_total_venta' => $kpis['valorTotalVentaUSD'],
                    'valor_total_costo' => $kpis['valorTotalCostoUSD'],
                    'ganancia_potencial' => $kpis['valorTotalVentaUSD'] - $kpis['valorTotalCostoUSD'],
                    'productos_stock_bajo' => $kpis['stockBajo']
                ],
                'ventas_mes' => $estadisticasVentas
            ]
        ]);
    }
    
    public function clientes() {
        $this->setCorsHeaders();
        $clienteModel = new Cliente();
        $clientes = $clienteModel->obtenerTodos($this->userId, '', true);
        return $this->response->json(['success' => true, 'data' => $clientes]);
    }
    
    public function docs() {
        return $this->response->view('api/docs', [], false);
    }
    
    private function setCorsHeaders() {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        
        if ($this->request->method() === 'OPTIONS') {
            exit;
        }
    }
}
