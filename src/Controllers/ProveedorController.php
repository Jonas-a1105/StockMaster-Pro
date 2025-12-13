<?php
namespace App\Controllers;

use App\Models\Proveedor;
use App\Core\Session; // <-- ¡AÑADIR ESTE!

class ProveedorController {

    public function index() {
        // Chequeo de plan
        if (($_SESSION['user_plan'] ?? 'free') === 'free') {
            Session::flash('error', 'Acceso denegado. Esta es una función Premium.');
            redirect('index.php?controlador=dashboard');
        }
        
        $userId = $_SESSION['user_id'];
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';
        
        // Dynamic Limit
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $allowedLimits = [5, 10, 25, 50, 100];
        if (!in_array($limit, $allowedLimits)) {
            $limit = 10;
        }

        $offset = ($page - 1) * $limit;

        $proveedorModel = new Proveedor();
        
        $proveedores = $proveedorModel->obtenerTodos($userId, $limit, $offset, $busqueda);
        $total = $proveedorModel->contarTodos($userId, $busqueda);
        $totalPaginas = ceil($total / $limit);

        $this->render('proveedores/index', [
            'proveedores' => $proveedores,
            'paginacion' => [
                'current' => $page,
                'total' => $totalPaginas,
                'limit' => $limit,
                'total_registros' => $total,
                'busqueda' => $busqueda
            ]
        ]);
    }

    // --- ¡REVERTIDO A PÁGINA COMPLETA! ---
    // 'crear' vuelve a ser una acción de página completa
    public function crear() {
        $userId = $_SESSION['user_id'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre = trim($_POST['prov-nombre'] ?? '');
            $contacto = trim($_POST['prov-contacto'] ?? '');
            $telefono = trim($_POST['prov-telefono'] ?? '');
            $email = trim($_POST['prov-email'] ?? '');

            if (!empty($nombre)) {
                $proveedorModel = new Proveedor();
                $proveedorModel->crear($userId, $nombre, $contacto, $telefono, $email);
                Session::flash('success', 'Proveedor agregado correctamente.');
            } else {
                Session::flash('error', 'El nombre del proveedor no puede estar vacío.');
            }
        }
        redirect('index.php?controlador=proveedor&accion=index');
    }

    // --- ¡NUEVA ACCIÓN! ---
    // API para el modal de "Editar"
    public function apiObtener() {
        header('Content-Type: application/json');
        $userId = $_SESSION['user_id'];
        $id = (int)($_GET['id'] ?? 0);
        
        if ($id === 0) {
            echo json_encode(['error' => 'ID no válido']);
            exit;
        }

        $proveedorModel = new Proveedor();
        $proveedor = $proveedorModel->obtenerPorId($userId, $id);

        if ($proveedor) {
            echo json_encode($proveedor);
        } else {
            echo json_encode(['error' => 'Proveedor no encontrado']);
        }
        exit;
    }

    // --- ¡ACCIÓN MODIFICADA! ---
    // Responde al modal de "Editar"
    public function actualizar() {
        $userId = $_SESSION['user_id'];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)($_POST['editar-prov-id'] ?? 0);
            $nombre = trim($_POST['editar-prov-nombre'] ?? '');
            $contacto = trim($_POST['editar-prov-contacto'] ?? '');
            $telefono = trim($_POST['editar-prov-telefono'] ?? '');
            $email = trim($_POST['editar-prov-email'] ?? '');

            if ($id > 0 && !empty($nombre)) {
                $proveedorModel = new Proveedor();
                $proveedorModel->actualizar($userId, $id, $nombre, $contacto, $telefono, $email);

                $respuesta = [
                    'success' => true,
                    'proveedor' => ['id' => $id, 'nombre' => $nombre, 'contacto' => $contacto, 'telefono' => $telefono, 'email' => $email]
                ];
                header('Content-Type: application/json');
                echo json_encode($respuesta);
                exit;
            }
        }
        
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Error en los datos']);
        exit;
    }

    public function eliminar() {
        $userId = $_SESSION['user_id'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id > 0) {
                $proveedorModel = new Proveedor();
                $proveedorModel->eliminar($userId, $id);
                Session::flash('success', 'Proveedor eliminado.');
            }
        }
        redirect('index.php?controlador=proveedor&accion=index');
    }

    private function render($vista, $data = []) {
        extract($data);
        $vistaContenido = __DIR__ . '/../../views/' . $vista . '.php';
        require __DIR__ . '/../../views/layouts/main.php';
    }
}