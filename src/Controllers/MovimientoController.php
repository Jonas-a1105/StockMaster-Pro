<?php
namespace App\Controllers;
 
use App\Core\BaseController;
use App\Services\MovimientoService;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Movimiento;
use App\Core\Session;
use App\Domain\Enums\UserPlan;
 
class MovimientoController extends BaseController {
    private $movimientoService;
    private $movimientoModel;
 
    public function __construct() {
        parent::__construct();
 
        if ($this->userPlan === UserPlan::FREE->value) {
            Session::flash('error', 'Acceso denegado. Esta es una función Premium.');
            return $this->response->redirect('index.php?controlador=dashboard');
        }
        $this->movimientoService = new MovimientoService();
        $this->movimientoModel = new Movimiento();
    }

    public function index() {
        $productoModel = new \App\Models\Producto();
        $proveedorModel = new \App\Models\Proveedor();

        $totalRegistros = $this->movimientoModel->contarTodos($this->userId, []);
        $pagData = $this->getPaginationData($totalRegistros, 10, 'mov_per_page');

        $filtros = [
            'limit' => $pagData['limit'], 
            'offset' => $pagData['offset'],
            'producto' => $this->request->query('producto'),
            'tipo' => $this->request->query('tipo'),
            'fecha_inicio' => $this->request->query('fecha_inicio'),
            'fecha_fin' => $this->request->query('fecha_fin')
        ];

        // Recalcular total con filtros
        $totalRegistros = $this->movimientoModel->contarTodos($this->userId, $filtros);
        $movimientosRecientes = $this->movimientoModel->obtenerTodos($this->userId, $filtros);

        return $this->response->view('movimientos/index', [
            'productos' => $productoModel->obtenerTodos($this->userId),
            'proveedores' => $proveedorModel->obtenerTodos($this->userId),
            'movimientos' => $movimientosRecientes,
            'paginaActual' => $pagData['page'],
            'totalPaginas' => $pagData['totalPages'],
            'totalRegistros' => $totalRegistros,
            'porPagina' => $pagData['limit'],
            'opcionesLimite' => [5, 10, 25, 50, 100],
            'filtros' => $filtros
        ]);
    }

    public function crear() {
        if (!$this->request->isPost()) {
            return $this->response->redirect('index.php?controlador=movimiento&accion=index');
        }

        try {
            $userId = Session::get('user_id');
            $this->movimientoService->registerMovement($userId, $this->request->all());
            Session::flash('success', 'Movimiento registrado correctamente.');
        } catch (\Exception $e) {
            Session::flash('error', $e->getMessage());
        }

        return $this->response->redirect('index.php?controlador=movimiento&accion=index');
    }
}
