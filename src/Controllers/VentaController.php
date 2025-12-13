<?php
namespace App\Controllers;

use App\Core\Database;
use App\Core\Session;
use App\Models\Producto;
use App\Models\Movimiento;
use App\Models\VentaModel;
use App\Models\Cliente;

class VentaController {

    private $productoModel;
    private $movimientoModel;
    private $ventaModel;
    private $clienteModel;
    private $db;

    public function __construct() {
        if (!isset($_SESSION['user_plan']) || $_SESSION['user_plan'] === 'free') {
            Session::flash('error', 'El Punto de Venta (POS) es una función Premium.');
            redirect('index.php?controlador=dashboard');
        }
        $this->productoModel = new Producto();
        $this->movimientoModel = new Movimiento();
        $this->ventaModel = new VentaModel();
        $this->clienteModel = new Cliente();
        $this->db = Database::conectar();
    }

    public function index() {
        $this->render('ventas/pos');
    }

    // Búsqueda normal (autocompletado)
    public function buscarProductos() {
        header('Content-Type: application/json');
        $userId = $_SESSION['user_id'];
        $termino = $_GET['term'] ?? '';
        $productos = $this->productoModel->buscarParaPOS($userId, $termino);
        echo json_encode($productos);
        exit;
    }

    // --- ¡NUEVA FUNCIÓN PARA EL ESCÁNER! ---
    // --- ¡NUEVA FUNCIÓN PARA EL ESCÁNER! ---
    public function checkout() {
        // Asegurar que no hay salida previa
        if (ob_get_length()) ob_clean();
        
        header('Content-Type: application/json');
        
        try {
            $userId = $_SESSION['user_id'];
            
            // Leer JSON (POST)
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
    
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON input');
            }
    
            $carrito = $data['carrito'] ?? [];
            $tasa = (float)($data['tasa'] ?? 0);
            
            // CORRECCIÓN: Asegurar que cliente_id sea null si está vacío o es 0
            $clienteId = !empty($data['cliente_id']) ? (int)$data['cliente_id'] : null;
            if ($clienteId === 0) $clienteId = null;
    
            $estadoPago = $data['estado_pago'] ?? 'Pagada';
            $metodoPago = $data['metodo_pago'] ?? 'Efectivo';
            $notas = $data['notas'] ?? null;
    
            // Si la tasa es inválida, usar 1 por defecto
            if ($tasa <= 0) $tasa = 1.0;
    
            if (empty($carrito)) {
                echo json_encode(['success' => false, 'message' => 'El carrito está vacío.']);
                exit;
            }
            
            // Validar venta a crédito
            if ($estadoPago === 'Pendiente') {
                if (!$clienteId) {
                    echo json_encode(['success' => false, 'message' => 'Debe seleccionar un cliente para vender a crédito.']);
                    exit;
                }
                
                // Verificar límite de crédito
                $totalUSD = 0;
                foreach ($carrito as $item) {
                    $totalUSD += $item['precioVentaUSD'] * $item['cantidad'];
                }
                
                if (!$this->clienteModel->puedeComprarCredito($clienteId, $totalUSD)) {
                    $deudaActual = $this->clienteModel->obtenerDeuda($clienteId);
                    $cliente = $this->clienteModel->obtenerPorId($userId, $clienteId);
                    echo json_encode([
                        'success' => false, 
                        'message' => 'El cliente ha superado su límite de crédito. Límite: $' . number_format($cliente['limite_credito'], 2) . ', Deuda actual: $' . number_format($deudaActual, 2)
                    ]);
                    exit;
                }
            }
    
            $this->db->beginTransaction();
    
            $totalUSD = 0;
            $totalUSD = 0;
            foreach ($carrito as $item) {
                // Frontend envía 'precio'
                $precio = isset($item['precio']) ? (float)$item['precio'] : 0;
                $totalUSD += $precio * $item['cantidad'];
            }
            $totalVES = $totalUSD * $tasa;
    
            $ventaId = $this->ventaModel->crearVenta($userId, $totalUSD, $tasa, $totalVES, $clienteId, $estadoPago, $metodoPago, $notas);
    
            foreach ($carrito as $item) {
                $precio = isset($item['precio']) ? (float)$item['precio'] : 0;
                $this->ventaModel->crearVentaItem($ventaId, $item['id'], $item['nombre'], $item['cantidad'], $precio);
                $this->productoModel->actualizarStock($userId, $item['id'], $item['cantidad'], 'Salida');
                $this->movimientoModel->crear($userId, $item['id'], $item['nombre'], 'Salida', 'Venta (POS)', $item['cantidad'], 'Venta ID #' . $ventaId, null);
            }
    
            $this->db->commit();
            echo json_encode(['success' => true, 'ventaId' => $ventaId, 'message' => 'Venta registrada']);
            exit;
    
        } catch (\Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("CHECKOUT ERROR: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
            exit;
        }
    }
    
    public function recibo() {
        $userId = $_SESSION['user_id'];
        $ventaId = (int)($_GET['id'] ?? 0);
        $venta = $this->ventaModel->obtenerVentaPorId($userId, $ventaId);
        if (!$venta) { Session::flash('error', 'Venta no encontrada.'); redirect('index.php?controlador=venta'); }
        $items = $this->ventaModel->obtenerItemsPorVentaId($ventaId);
        
        // Obtener datos del cliente si existe
        $cliente = null;
        if ($venta['cliente_id']) {
            $cliente = $this->clienteModel->obtenerPorId($userId, $venta['cliente_id']);
        }
        
        require __DIR__ . '/../../views/ventas/recibo.php';
    }
    
    public function historial() {
        $userId = $_SESSION['user_id'];
        
        // === Configuración de Paginación ===
        $opcionesLimite = [3, 5, 7, 10, 25, 50, 100];
        $limiteDefault = 10;

        $porPagina = (int)($_GET['limite'] ?? $_SESSION['venta_per_page'] ?? $limiteDefault);
        
        if (!in_array($porPagina, $opcionesLimite)) {
            if ($porPagina <= 0) $porPagina = $limiteDefault;
        }

        $_SESSION['venta_per_page'] = $porPagina;

        $paginaActual = (int)($_GET['pagina'] ?? 1);
        if ($paginaActual < 1) $paginaActual = 1;
        $offset = ($paginaActual - 1) * $porPagina;
        
        $filtros = [];
        if (!empty($_GET['fecha_inicio'])) $filtros['fecha_inicio'] = $_GET['fecha_inicio'];
        if (!empty($_GET['fecha_fin'])) $filtros['fecha_fin'] = $_GET['fecha_fin'];
        if (!empty($_GET['cliente_id'])) $filtros['cliente_id'] = (int)$_GET['cliente_id'];
        if (!empty($_GET['estado_pago'])) $filtros['estado_pago'] = $_GET['estado_pago'];
        
        $totalRegistros = $this->ventaModel->contarVentas($userId, $filtros);
        $totalPaginas = ceil($totalRegistros / $porPagina);
        
        $filtros['limit'] = $porPagina;
        $filtros['offset'] = $offset;
        
        $ventas = $this->ventaModel->obtenerVentas($userId, $filtros);
        $clientes = $this->clienteModel->obtenerTodos($userId, '', true);
        
        $this->render('ventas/historial', [
            'ventas' => $ventas,
            'clientes' => $clientes,
            'filtros' => $filtros,
            'paginaActual' => $paginaActual,
            'totalPaginas' => $totalPaginas,
            'totalPaginas' => $totalPaginas,
            'totalRegistros' => $totalRegistros,
            'porPagina' => $porPagina,
            'opcionesLimite' => $opcionesLimite
        ]);
    }
    
    public function marcarPagada() {
        $userId = $_SESSION['user_id'];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ventaId = (int)$_POST['venta_id'];
            $exito = $this->ventaModel->marcarPagada($userId, $ventaId);
            
            if ($exito) {
                Session::flash('success', 'Venta marcada como pagada.');
            } else {
                Session::flash('error', 'Error al actualizar la venta.');
            }
        }
        
        redirect('index.php?controlador=venta&accion=historial');
    }
    
    /**
     * Pagar una venta pendiente (vía GET desde el historial)
     */
    public function pagarVenta() {
        $userId = $_SESSION['user_id'];
        $ventaId = (int)($_GET['id'] ?? 0);
        
        if ($ventaId <= 0) {
            Session::flash('error', 'Venta no válida.');
            redirect('index.php?controlador=venta&accion=historial');
        }
        
        $exito = $this->ventaModel->marcarPagada($userId, $ventaId);
        
        if ($exito) {
            Session::flash('success', 'Venta #' . $ventaId . ' marcada como pagada.');
        } else {
            Session::flash('error', 'Error al marcar la venta como pagada. Es posible que ya esté pagada.');
        }
        
        redirect('index.php?controlador=venta&accion=historial');
    }

    private function render($vista, $data = []) {
        extract($data);
        $vistaContenido = __DIR__ . '/../../views/' . $vista . '.php';
        require __DIR__ . '/../../views/layouts/main.php';
    }
}
