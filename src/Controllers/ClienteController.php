<?php
namespace App\Controllers;

use App\Core\Session;
use App\Models\Cliente;

class ClienteController {

    private $clienteModel;

    public function __construct() {
        if (!isset($_SESSION['user_plan']) || $_SESSION['user_plan'] === 'free') {
            Session::flash('error', 'El Módulo de Clientes es Premium.');
            redirect('index.php?controlador=dashboard');
        }
        $this->clienteModel = new Cliente();
    }

    /**
     * Mostrar listado de clientes
     */
    public function index() {
        $userId = $_SESSION['user_id'];
        $busqueda = $_GET['buscar'] ?? '';
        $clientes = $this->clienteModel->obtenerTodos($userId, $busqueda, true);
        
        // Calcular deuda de cada cliente
        foreach ($clientes as &$cliente) {
            $cliente['deuda'] = $this->clienteModel->obtenerDeuda($cliente['id']);
        }
        
        $this->render('clientes/index', ['clientes' => $clientes, 'busqueda' => $busqueda]);
    }

    /**
     * Ver detalle de un cliente
     */
    public function ver() {
        $userId = $_SESSION['user_id'];
        $clienteId = (int)($_GET['id'] ?? 0);
        
        $cliente = $this->clienteModel->obtenerPorId($userId, $clienteId);
        
        if (!$cliente) {
            Session::flash('error', 'Cliente no encontrado.');
            redirect('index.php?controlador=cliente&accion=index');
        }
        
        $stats = $this->clienteModel->obtenerEstadisticas($clienteId);
        $historial = $this->clienteModel->obtenerHistorialCompras($clienteId, 20);
        
        $this->render('clientes/ver', [
            'cliente' => $cliente,
            'stats' => $stats,
            'historial' => $historial
        ]);
    }

    /**
     * Guardar nuevo cliente (AJAX)
     */
    public function guardar() {
        header('Content-Type: application/json');
        $userId = $_SESSION['user_id'];
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
            exit;
        }

        $nombre = trim($_POST['nombre']);
        $tipoDocumento = $_POST['tipo_documento'];
        $numeroDocumento = trim($_POST['numero_documento'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $direccion = trim($_POST['direccion'] ?? '');
        $tipoCliente = $_POST['tipo_cliente'];
        $limiteCredito = (float)($_POST['limite_credito'] ?? 0);

        if (empty($nombre)) {
            echo json_encode(['success' => false, 'message' => 'El nombre es obligatorio.']);
            exit;
        }

        $clienteId = $this->clienteModel->crear(
            $userId, 
            $nombre, 
            $tipoDocumento, 
            $numeroDocumento, 
            $telefono, 
            $email, 
            $direccion, 
            $tipoCliente, 
            $limiteCredito
        );

        if ($clienteId) {
            echo json_encode(['success' => true, 'message' => 'Cliente creado exitosamente.', 'clienteId' => $clienteId]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al crear el cliente.']);
        }
        exit;
    }

    /**
     * Actualizar cliente existente (AJAX)
     */
    public function actualizar() {
        header('Content-Type: application/json');
        $userId = $_SESSION['user_id'];
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
            exit;
        }

        $id = (int)$_POST['id'];
        $nombre = trim($_POST['nombre']);
        $tipoDocumento = $_POST['tipo_documento'];
        $numeroDocumento = trim($_POST['numero_documento'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $direccion = trim($_POST['direccion'] ?? '');
        $tipoCliente = $_POST['tipo_cliente'];
        $limiteCredito = (float)($_POST['limite_credito'] ?? 0);

        if (empty($nombre)) {
            echo json_encode(['success' => false, 'message' => 'El nombre es obligatorio.']);
            exit;
        }

        $exito = $this->clienteModel->actualizar(
            $userId, 
            $id, 
            $nombre, 
            $tipoDocumento, 
            $numeroDocumento, 
            $telefono, 
            $email, 
            $direccion, 
            $tipoCliente, 
            $limiteCredito
        );

        if ($exito) {
            echo json_encode(['success' => true, 'message' => 'Cliente actualizado exitosamente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar el cliente.']);
        }
        exit;
    }

    /**
     * Desactivar un cliente (AJAX)
     */
    public function desactivar() {
        header('Content-Type: application/json');
        $userId = $_SESSION['user_id'];
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
            exit;
        }

        $id = (int)$_POST['id'];
        $exito = $this->clienteModel->desactivar($userId, $id);

        if ($exito) {
            echo json_encode(['success' => true, 'message' => 'Cliente desactivado.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al desactivar el cliente.']);
        }
        exit;
    }

    /**
     * Pagar una venta pendiente de un cliente (acepta GET o POST)
     */
    public function pagarVenta() {
        $userId = $_SESSION['user_id'];
        
        // Aceptar tanto GET como POST
        $ventaId = (int)($_GET['venta_id'] ?? $_POST['venta_id'] ?? 0);
        $clienteId = (int)($_GET['cliente_id'] ?? $_POST['cliente_id'] ?? 0);
        
        if ($ventaId <= 0) {
            Session::flash('error', 'Venta no válida.');
            redirect('index.php?controlador=cliente&accion=index');
        }
        
        // Usar VentaModel para marcar como pagada
        $ventaModel = new \App\Models\VentaModel();
        $exito = $ventaModel->marcarPagada($userId, $ventaId);
        
        if ($exito) {
            Session::flash('success', 'Venta #' . $ventaId . ' marcada como pagada.');
        } else {
            Session::flash('error', 'Error al marcar la venta como pagada.');
        }
        
        // Redirigir de vuelta al cliente
        if ($clienteId > 0) {
            redirect('index.php?controlador=cliente&accion=ver&id=' . $clienteId);
        } else {
            redirect('index.php?controlador=cliente&accion=index');
        }
    }

    /**
     * Buscar clientes para POS (AJAX - autocomplete)
     */
    public function buscarParaPOS() {
        header('Content-Type: application/json');
        $userId = $_SESSION['user_id'];
        $termino = $_GET['term'] ?? '';
        
        $clientes = $this->clienteModel->buscarParaPOS($userId, $termino);
        
        // Formatear para autocompletar
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
        
        echo json_encode($resultado);
        exit;
    }

    private function render($vista, $data = []) {
        extract($data);
        $vistaContenido = __DIR__ . '/../../views/' . $vista . '.php';
        require __DIR__ . '/../../views/layouts/main.php';
    }
}
