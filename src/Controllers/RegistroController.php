<?php
namespace App\Controllers;

use App\Core\Database;
use App\Core\Session;

class RegistroController {

    public function index() {
        $this->render('registro/index');
    }

    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php?controlador=registro');
        }

        $email = $_POST['email'] ?? '';
        $username = $_POST['username'] ?? ''; // Nuevo campo
        $password = $_POST['password'] ?? '';
        $password_confirm = $_POST['password_confirm'] ?? '';

        // Validar username
        if (empty($username) || strlen($username) < 3) {
             Session::flash('error', 'El nombre de usuario debe tener al menos 3 caracteres.');
             redirect('index.php?controlador=registro');
        }

        if (empty($email) || empty($password) || $password !== $password_confirm || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::flash('error', 'Datos de registro inválidos.');
            redirect('index.php?controlador=registro');
        }
        
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $plan = 'premium'; 
        
        $db = Database::conectar();
        
        // Verificar si el usuario ya existe
        $stmtCheck = $db->prepare("SELECT id FROM usuarios WHERE username = ? OR email = ?");
        $stmtCheck->execute([$username, $email]);
        if ($stmtCheck->fetch()) {
             Session::flash('error', 'El usuario o correo ya están registrados.');
             redirect('index.php?controlador=registro');
        }

        // Calcular fecha de expiración (30 días de prueba)
        $trial_ends_at = date('Y-m-d H:i:s', strtotime('+30 days'));

        try {
            // Explicitly set rol to 'usuario'
            $stmt = $db->prepare("INSERT INTO usuarios (username, email, password, plan, trial_ends_at, rol) VALUES (?, ?, ?, ?, ?, 'usuario')");
            $stmt->execute([$username, $email, $passwordHash, $plan, $trial_ends_at]);
            
            Session::flash('success', '¡Te has registrado con éxito! Por favor, inicia sesión.');
            redirect('index.php?controlador=login');

        } catch (\PDOException $e) {
            if ($e->getCode() == 23000) {
                Session::flash('error', 'El email ya está registrado.');
            } else {
                Session::flash('error', 'Ocurrió un error en el registro.');
            }
            redirect('index.php?controlador=registro');
        }
    }

    private function render($vista, $data = []) {
        extract($data);
        require __DIR__ . '/../../views/' . $vista . '.php';
    }
}