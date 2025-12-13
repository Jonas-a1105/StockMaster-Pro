<?php
namespace App\Controllers;

use App\Core\Database;
use App\Core\Session;

class EquipoController {

    public function __construct() {
        // Solo Premium y Solo Dueños (no empleados) pueden gestionar equipos
        if ($_SESSION['user_plan'] === 'free') {
            redirect('index.php?controlador=premium');
        }
        if (!empty($_SESSION['es_empleado'])) {
            Session::flash('error', 'Solo el dueño de la cuenta puede gestionar el equipo.');
            redirect('index.php?controlador=dashboard');
        }
    }

    public function index() {
        $db = Database::conectar();
        // Buscar usuarios cuyo "jefe" sea el usuario actual
        $stmt = $db->prepare("SELECT id, email, created_at as fecha_registro FROM usuarios WHERE owner_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $empleados = $stmt->fetchAll();

        $this->render('equipo/index', ['empleados' => $empleados]);
    }

    public function crear() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'];
            $password = $_POST['password'];
            $ownerId = $_SESSION['user_id'];

            // Validar
            if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 4) {
                Session::flash('error', 'Datos inválidos.');
                redirect('index.php?controlador=equipo');
            }

            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            try {
                $db = Database::conectar();
                // Insertar usuario vinculado al owner_id
                $stmt = $db->prepare("INSERT INTO usuarios (email, password, plan, owner_id) VALUES (?, ?, 'free', ?)");
                $stmt->execute([$email, $passwordHash, $ownerId]);
                
                Session::flash('success', 'Miembro del equipo agregado.');
            } catch (\Exception $e) {
                Session::flash('error', 'El email ya está registrado.');
            }
        }
        redirect('index.php?controlador=equipo');
    }

    public function eliminar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)$_POST['id'];
            $ownerId = $_SESSION['user_id'];

            $db = Database::conectar();
            // Solo eliminar si el usuario me pertenece (owner_id = mi id)
            $stmt = $db->prepare("DELETE FROM usuarios WHERE id = ? AND owner_id = ?");
            $stmt->execute([$id, $ownerId]);
            
            Session::flash('success', 'Miembro eliminado.');
        }
        redirect('index.php?controlador=equipo');
    }

    private function render($vista, $data = []) {
        extract($data);
        $vistaContenido = __DIR__ . '/../../views/' . $vista . '.php';
        require __DIR__ . '/../../views/layouts/main.php';
    }
}