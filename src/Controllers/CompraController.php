<?php
namespace App\Controllers;

use App\Core\BaseController;
use App\Services\CompraService;
use App\Models\CompraModel;
use App\Models\Proveedor;
use App\Core\Session;
use App\Exceptions\AppException;

use App\Domain\Enums\Capability;

class CompraController extends BaseController {
    private $compraService;
    private $compraModel;

    public function __construct() {
        parent::__construct();
        $this->requireCapability(Capability::ADVANCED_INVENTORY, 'El Módulo de Compras es Premium.');

        $this->compraService = new CompraService();
        $this->compraModel = new CompraModel();
    }

    public function index() {
        $filtro = $this->request->query('estado', '');
        
        $totalRegistros = $this->compraModel->contarTodas($this->userId, $filtro);
        $pagData = $this->getPaginationData($totalRegistros, 10, 'compras_per_page');
        
        $compras = $this->compraModel->obtenerTodas($this->userId, $filtro, $pagData['limit'], $pagData['offset']);

        return $this->response->view('compras/index', [
            'compras' => $compras, 
            'filtro' => $filtro,
            'paginaActual' => $pagData['page'],
            'totalPaginas' => $pagData['totalPages'],
            'totalRegistros' => $totalRegistros,
            'porPagina' => $pagData['limit'],
            'opcionesLimite' => [5, 10, 25, 50, 100]
        ]);
    }

    public function crear() {
        $proveedorModel = new Proveedor();
        $proveadores = $proveedorModel->obtenerTodos($this->userId);
        return $this->response->view('compras/crear', ['proveedores' => $proveadores]);
    }

    public function guardar() {
        if (!$this->request->isPost()) {
            throw new AppException('Método no permitido', 405);
        }

        $compraId = $this->compraService->processPurchase($this->userId, $this->request->all());
        return $this->response->json(['success' => true, 'compraId' => $compraId, 'message' => 'Compra registrada']);
    }
    
    public function marcarPagada() {
        if (!$this->request->isPost()) {
            throw new AppException('Solicitud inválida.', 405);
        }

        $compraId = $this->request->input('id', 0, 'int');
        
        if ($this->compraService->markAsPaid($this->userId, $compraId)) {
            Session::flash('success', 'Factura pagada. El stock ha sido sumado al inventario.');
        } else {
            throw new AppException('No se pudo marcar la compra como pagada.');
        }

        return $this->response->redirect('index.php?controlador=compra&accion=index');
    }
}
