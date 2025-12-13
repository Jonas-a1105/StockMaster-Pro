<?php
namespace App\Controllers;

use App\Models\Producto;
use App\Models\VentaModel;
use App\Models\Cliente;
use App\Core\Database;

/**
 * API REST Controller
 * 
 * Endpoints disponibles:
 * - GET  /api/productos      - Listar productos
 * - GET  /api/productos/{id} - Obtener producto
 * - GET  /api/ventas         - Listar ventas
 * - GET  /api/estadisticas   - Obtener KPIs
 * - POST /api/productos      - Crear producto
 * 
 * Autenticación: Token Bearer en header Authorization
 */
class ApiController {
    
    private $userId;
    
    public function __construct() {
        // Verificar autenticación API
        if (!$this->autenticar()) {
            $this->responderError('No autorizado. Token inválido o expirado.', 401);
        }
    }
    
    /**
     * Autenticar request API
     * Acepta: Bearer token en header o api_key en query string
     */
    private function autenticar() {
        // Opción 1: Sesión activa (para uso desde frontend)
        if (isset($_SESSION['user_id'])) {
            $this->userId = $_SESSION['user_id'];
            return true;
        }
        
        // Opción 2: API Key en query string
        $apiKey = $_GET['api_key'] ?? null;
        if ($apiKey) {
            return $this->validarApiKey($apiKey);
        }
        
        // Opción 3: Bearer token en header
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return $this->validarApiKey($matches[1]);
        }
        
        return false;
    }
    
    /**
     * Validar API Key contra la base de datos
     */
    private function validarApiKey($key) {
        try {
            $db = Database::conectar();
            $now = date('Y-m-d H:i:s');
            $stmt = $db->prepare("SELECT user_id FROM api_keys WHERE api_key = ? AND activo = 1 AND (expira IS NULL OR expira > ?)");
            $stmt->execute([$key, $now]);
            $result = $stmt->fetch();
            
            if ($result) {
                $this->userId = $result['user_id'];
                return true;
            }
        } catch (\Exception $e) {
            error_log("Error validando API key: " . $e->getMessage());
        }
        return false;
    }
    
    // ==================== ENDPOINTS ====================
    
    /**
     * GET /api/productos
     * Listar productos con filtros opcionales
     * 
     * Query params:
     * - buscar: string (búsqueda por nombre)
     * - categoria: string (filtrar por categoría)
     * - limit: int (máximo 100, default 50)
     * - offset: int (para paginación)
     */
    public function productos() {
        $this->setCorsHeaders();
        
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $productoModel = new Producto();
            
            $buscar = $_GET['buscar'] ?? '';
            $limit = min((int)($_GET['limit'] ?? 50), 100);
            $offset = (int)($_GET['offset'] ?? 0);
            
            $productos = $productoModel->obtenerTodos($this->userId, $buscar, $limit, $offset);
            $total = $productoModel->contarTodos($this->userId, $buscar);
            
            $this->responder([
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
        
        $this->responderError('Método no permitido', 405);
    }
    
    /**
     * GET /api/producto/{id}
     * Obtener un producto específico
     */
    public function producto() {
        $this->setCorsHeaders();
        $id = (int)($_GET['id'] ?? 0);
        
        if ($id <= 0) {
            $this->responderError('ID de producto inválido', 400);
        }
        
        $productoModel = new Producto();
        $producto = $productoModel->obtenerPorId($this->userId, $id);
        
        if (!$producto) {
            $this->responderError('Producto no encontrado', 404);
        }
        
        $this->responder([
            'success' => true,
            'data' => $producto
        ]);
    }
    
    /**
     * GET /api/ventas
     * Listar ventas con filtros
     * 
     * Query params:
     * - fecha_inicio: date (YYYY-MM-DD)
     * - fecha_fin: date (YYYY-MM-DD)
     * - estado: string (Pagada|Pendiente)
     * - limit: int
     */
    public function ventas() {
        $this->setCorsHeaders();
        
        $ventaModel = new VentaModel();
        
        $filtros = [
            'fecha_inicio' => $_GET['fecha_inicio'] ?? null,
            'fecha_fin' => $_GET['fecha_fin'] ?? null,
            'estado' => $_GET['estado'] ?? null,
            'limit' => min((int)($_GET['limit'] ?? 50), 100)
        ];
        
        $ventas = $ventaModel->obtenerVentas($this->userId, $filtros);
        
        $this->responder([
            'success' => true,
            'data' => $ventas,
            'meta' => [
                'count' => count($ventas),
                'filtros' => array_filter($filtros)
            ]
        ]);
    }
    
    /**
     * GET /api/estadisticas
     * Obtener KPIs del dashboard
     */
    public function estadisticas() {
        $this->setCorsHeaders();
        
        $productoModel = new Producto();
        $ventaModel = new VentaModel();
        
        $umbral = (int)($_GET['umbral_stock'] ?? 10);
        $kpis = $productoModel->obtenerKPIsDashboard($this->userId, $umbral);
        
        $inicioMes = date('Y-m-01');
        $estadisticasVentas = $ventaModel->obtenerEstadisticasVentas($this->userId, $inicioMes);
        
        $this->responder([
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
    
    /**
     * GET /api/clientes
     * Listar clientes
     */
    public function clientes() {
        $this->setCorsHeaders();
        
        $clienteModel = new Cliente();
        $clientes = $clienteModel->obtenerTodos($this->userId, '', true);
        
        $this->responder([
            'success' => true,
            'data' => $clientes
        ]);
    }
    
    /**
     * GET /api/docs
     * Documentación de la API
     */
    public function docs() {
        header('Content-Type: text/html; charset=utf-8');
        include __DIR__ . '/../../views/api/docs.php';
        exit;
    }
    
    // ==================== HELPERS ====================
    
    private function setCorsHeaders() {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
    }
    
    private function responder($data, $code = 200) {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
    
    private function responderError($mensaje, $code = 400) {
        $this->responder([
            'success' => false,
            'error' => [
                'code' => $code,
                'message' => $mensaje
            ]
        ], $code);
    }
}
