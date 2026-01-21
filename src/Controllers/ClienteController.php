<?php
namespace App\Controllers;

use App\Core\BaseController;
use App\Core\Session;
use App\Models\Cliente;
use App\Services\ClienteService;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Exceptions\AppException;

use App\Domain\Enums\Capability;

class ClienteController extends BaseController {
    private $clienteModel;
    private $clienteService;

    public function __construct() {
        parent::__construct();
        $this->requireCapability(Capability::ADVANCED_INVENTORY, 'El Módulo de Clientes es Premium.');
        
        $this->clienteModel = new Cliente();
        $this->clienteService = new ClienteService();
    }

    public function index() {
        $busqueda = $this->request->query('buscar', '');
        
        // Paginación si fuera necesaria (aunque el original no la tenía, la dejamos preparada)
        $clientes = $this->clienteModel->obtenerTodos($this->userId, $busqueda, true);
        
        foreach ($clientes as &$cliente) {
            $cliente['deuda'] = $this->clienteModel->obtenerDeuda($cliente['id']);
        }
        
        return $this->response->view('clientes/index', [
            'clientes' => $clientes, 
            'busqueda' => $busqueda
        ]);
    }

    public function ver() {
        $clienteId = $this->request->query('id', 0, 'int');
        $cliente = $this->clienteModel->obtenerPorId($this->userId, $clienteId);
        
        if (!$cliente) {
            throw new NotFoundException('Cliente no encontrado.');
        }
        
        $stats = $this->clienteModel->obtenerEstadisticas($clienteId);
        $historial = $this->clienteModel->obtenerHistorialCompras($clienteId, 20);
        
        return $this->response->view('clientes/ver', [
            'cliente' => $cliente,
            'stats' => $stats,
            'historial' => $historial
        ]);
    }

    public function guardar() {
        if (!$this->request->isPost()) {
             throw new AppException('Solicitud inválida.', 405);
        }

        $rules = [
            'nombre' => 'required|min:3',
            'email'  => 'email',
            'numero_documento' => 'required'
        ];

        if (!$this->request->validate($rules)) {
            throw new ValidationException(['form' => $this->request->firstError()], 'Error al crear cliente.');
        }

        $clienteId = $this->clienteService->createCliente($this->userId, $this->request->all());
        return $this->response->json([
            'success' => true, 
            'message' => 'Cliente creado exitosamente.', 
            'clienteId' => $clienteId
        ]);
    }

    public function actualizar() {
        if (!$this->request->isPost()) {
             throw new AppException('Solicitud inválida.', 405);
        }

        $rules = [
            'id'     => 'required|numeric',
            'nombre' => 'required|min:3',
            'email'  => 'email'
        ];

        if (!$this->request->validate($rules)) {
            throw new ValidationException(['form' => $this->request->firstError()], 'Error al actualizar cliente.');
        }

        if ($this->clienteService->updateCliente($this->userId, $this->request->all())) {
            return $this->response->json(['success' => true, 'message' => 'Cliente actualizado exitosamente.']);
        }
        
        throw new AppException('Error al actualizar el cliente.', 500);
    }

    public function desactivar() {
        if (!$this->request->isPost()) {
             throw new AppException('Solicitud inválida.', 405);
        }

        $id = $this->request->input('id', 0, 'int');
        if ($this->clienteService->deactivateCliente($this->userId, $id)) {
            return $this->response->json(['success' => true, 'message' => 'Cliente desactivado.']);
        }
        throw new AppException('Error al desactivar el cliente.', 500);
    }

    public function pagarVenta() {
        $ventaId = $this->request->input('venta_id', 0, 'int');
        $clienteId = $this->request->input('cliente_id', 0, 'int');
        
        if ($ventaId <= 0) {
            Session::flash('error', 'Venta no válida.');
            return $this->response->redirect('index.php?controlador=cliente&accion=index');
        }
        
        $ventaService = new \App\Services\VentaService();
        if ($ventaService->marcarComoPagada($this->userId, $ventaId)) {
            Session::flash('success', 'Venta #' . $ventaId . ' marcada como pagada.');
        } else {
            Session::flash('error', 'No se pudo marcar como pagada.');
        }
        
        if ($clienteId > 0) {
            return $this->response->redirect('index.php?controlador=cliente&accion=ver&id=' . $clienteId);
        }
        return $this->response->redirect('index.php?controlador=cliente&accion=index');
    }

    public function buscarParaPOS() {
        $termino = $this->request->query('term', '');
        $clientes = $this->clienteModel->buscarParaPOS($this->userId, $termino);
        
        $resultado = [];
        foreach ($clientes as $c) {
            $resultado[] = [
                'id' => $c['id'],
                'label' => $c['nombre'] . ($c['numero_documento'] ? ' (' . $c['numero_documento'] . ')' : ''),
                'value' => $c['nombre'],
                'nombre' => $c['nombre'],
                'documento' => $c['numero_documento'],
                'tipo_documento' => $c['tipo_documento'],
                'limite_credito' => $c['limite_credito']
            ];
        }
        return $this->response->json($resultado);
    }
}
