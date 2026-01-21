<?php
namespace App\Controllers;

use App\Core\BaseController;
use App\Models\Proveedor;
use App\Services\ProveedorService;
use App\Core\Session;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Exceptions\AppException;

use App\Domain\Enums\Capability;

class ProveedorController extends BaseController {
    private $proveedorService;
    private $proveedorModel;

    public function __construct() {
        parent::__construct();
        $this->requireCapability(Capability::ADVANCED_INVENTORY, 'Acceso denegado. Esta es una función Premium.');

        $this->proveedorModel = new Proveedor();
        $this->proveedorService = new ProveedorService();
    }

    public function index() {
        $busqueda = trim($this->request->query('busqueda', ''));
        
        // Usar helper de paginación del BaseController
        $total = $this->proveedorModel->contarTodos($this->userId, $busqueda);
        $pagData = $this->getPaginationData($total, 10, 'prov_per_page');
        
        $proveedores = $this->proveedorModel->obtenerTodos(
            $this->userId, 
            $pagData['limit'], 
            $pagData['offset'], 
            $busqueda
        );

        return $this->response->view('proveedores/index', [
            'proveedores' => $proveedores,
            'paginacion' => [
                'current' => $pagData['page'],
                'total' => $pagData['totalPages'],
                'limit' => $pagData['limit'],
                'total_registros' => $total,
                'busqueda' => $busqueda
            ]
        ]);
    }

    public function crear() {
        if (!$this->request->isPost()) {
             throw new AppException('Solicitud inválida.', 405);
        }

        $rules = [
            'prov-nombre' => 'required|min:3',
            'prov-email'  => 'email'
        ];

        if (!$this->request->validate($rules)) {
            Session::flash('error', $this->request->firstError());
            return $this->response->redirect('index.php?controlador=proveedor&accion=index');
        }

        $this->proveedorService->createProveedor($this->userId, [
            'nombre'   => $this->request->input('prov-nombre'),
            'contacto' => $this->request->input('prov-contacto'),
            'telefono' => $this->request->input('prov-telefono'),
            'email'    => $this->request->input('prov-email')
        ]);
        
        Session::flash('success', 'Proveedor agregado correctamente.');
        return $this->response->redirect('index.php?controlador=proveedor&accion=index');
    }

    public function apiObtener() {
        $id = $this->request->query('id', 0, 'int');
        
        if ($id === 0) {
            throw new AppException('ID de proveedor no válido', 400);
        }

        $proveedor = $this->proveedorModel->obtenerPorId($this->userId, $id);
        if ($proveedor) {
            return $this->response->json($proveedor);
        }
        
        throw new NotFoundException('Proveedor no encontrado');
    }

    public function actualizar() {
        if (!$this->request->isPost()) {
             throw new AppException('Solicitud inválida.', 405);
        }

        $rules = [
            'editar-prov-id'     => 'required|numeric',
            'editar-prov-nombre' => 'required|min:3',
            'editar-prov-email'  => 'email'
        ];

        if (!$this->request->validate($rules)) {
            throw new ValidationException(['form' => $this->request->firstError()], 'Error al actualizar proveedor.');
        }

        $data = [
            'id'       => $this->request->input('editar-prov-id'),
            'nombre'   => $this->request->input('editar-prov-nombre'),
            'contacto' => $this->request->input('editar-prov-contacto'),
            'telefono' => $this->request->input('editar-prov-telefono'),
            'email'    => $this->request->input('editar-prov-email')
        ];
        
        if ($this->proveedorService->updateProveedor($this->userId, $data)) {
            return $this->response->json(['success' => true, 'proveedor' => $data]);
        }
        
        throw new AppException('Error al actualizar el proveedor.', 500);
    }

    public function eliminar() {
        if (!$this->request->isPost()) {
            return $this->response->redirect('index.php?controlador=proveedor&accion=index');
        }

        $id = $this->request->input('id', 0, 'int');
        if ($id > 0) {
            $this->proveedorService->deleteProveedor($this->userId, $id);
            Session::flash('success', 'Proveedor eliminado.');
        }
        return $this->response->redirect('index.php?controlador=proveedor&accion=index');
    }
}
