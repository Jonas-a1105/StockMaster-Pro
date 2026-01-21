<?php
namespace App\Controllers;

use App\Core\BaseController;
use App\Services\VentaService;
use App\Domain\DTOs\VentaCheckoutDTO;

use App\Domain\Enums\Capability;
use App\Models\Producto;
use App\Models\VentaModel;
use App\Models\Cliente;
use App\Exceptions\NotFoundException;
use App\Exceptions\AppException;
use App\Core\Session;

class VentaController extends BaseController {
    private $productoModel;
    private $ventaModel;
    private $clienteModel;
    private $ventaService;

    public function __construct() {
        parent::__construct();
        $this->requireCapability(Capability::ACCESS_POS, 'El Punto de Venta (POS) es una función Premium.');

        $this->productoModel = new Producto();
        $this->ventaModel = new VentaModel();
        $this->clienteModel = new Cliente();
        $this->ventaService = new VentaService();
    }

    public function index() {
        return $this->response->view('ventas/pos');
    }

    /**
     * Búsqueda para autocompletado en POS
     */
    public function buscarProductos() {
        $termino = $this->request->query('term', '');
        $productos = $this->productoModel->buscarParaPOS($this->userId, $termino);
        return $this->response->json($productos);
    }

    /**
     * Procesa la venta (AJAX)
     */
    public function checkout() {
        $dto = VentaCheckoutDTO::fromRequest($this->request->all());
        $ventaId = $this->ventaService->processCheckout($this->userId, $dto);
        return $this->response->json([
            'success' => true, 
            'ventaId' => $ventaId, 
            'message' => 'Venta registrada con éxito'
        ]);
    }
    
    /**
     * Muestra el recibo de venta
     */
    public function recibo() {
        $ventaId = $this->request->query('id', 0, 'int');
        
        $venta = $this->ventaModel->obtenerVentaPorId($this->userId, $ventaId);
        if (!$venta) { 
            throw new NotFoundException('Venta no encontrada.'); 
        }
        
        $items = $this->ventaModel->obtenerItemsPorVentaId($ventaId);
        $cliente = $venta['cliente_id'] ? $this->clienteModel->obtenerPorId($this->userId, $venta['cliente_id']) : null;
        
        return $this->response->view('ventas/recibo', [
            'venta' => $venta,
            'items' => $items,
            'cliente' => $cliente
        ]);
    }
    
    /**
     * Historial de ventas con paginación y filtros
     */
    public function historial() {
        // Filtros
        $filtros = [];
        if ($this->request->query('fecha_inicio')) $filtros['fecha_inicio'] = $this->request->query('fecha_inicio');
        if ($this->request->query('fecha_fin')) $filtros['fecha_fin'] = $this->request->query('fecha_fin');
        if ($this->request->query('cliente_id')) $filtros['cliente_id'] = $this->request->query('cliente_id', 0, 'int');
        if ($this->request->query('estado_pago')) $filtros['estado_pago'] = $this->request->query('estado_pago');
        
        $totalRegistros = $this->ventaModel->contarVentas($this->userId, $filtros);
        $pagData = $this->getPaginationData($totalRegistros, 10, 'venta_per_page');
        
        $filtros['limit'] = $pagData['limit'];
        $filtros['offset'] = $pagData['offset'];
        
        $ventas = $this->ventaModel->obtenerVentas($this->userId, $filtros);
        $clientes = $this->clienteModel->obtenerTodos($this->userId, '', true);
        
        return $this->response->view('ventas/historial', [
            'ventas' => $ventas,
            'clientes' => $clientes,
            'filtros' => $filtros,
            'paginaActual' => $pagData['page'],
            'totalPaginas' => $pagData['totalPages'],
            'totalRegistros' => $totalRegistros,
            'porPagina' => $pagData['limit'],
            'opcionesLimite' => [5, 10, 25, 50, 100]
        ]);
    }
    
    /**
     * Pagar una venta pendiente
     */
    public function pagarVenta() {
        if (!$this->request->isPost()) {
            Session::flash('error', 'Acción no permitida (Debe ser POST).');
            return $this->response->redirect('index.php?controlador=venta&accion=historial');
        }

        $ventaId = $this->request->input('id', 0, 'int');
        
        if ($ventaId <= 0) {
            Session::flash('error', 'Venta no válida.');
            return $this->response->redirect('index.php?controlador=venta&accion=historial');
        }
        
        if ($this->ventaService->marcarComoPagada($this->userId, $ventaId)) {
            Session::flash('success', "Venta #$ventaId marcada como pagada.");
        } else {
            Session::flash('error', 'Error al marcar la venta como pagada. Es posible que ya esté pagada o no exista.');
        }
        
        return $this->response->redirect('index.php?controlador=venta&accion=historial');
    }
}
