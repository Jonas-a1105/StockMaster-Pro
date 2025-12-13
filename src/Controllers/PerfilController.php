<?php
namespace App\Controllers;

use App\Core\Session;
use App\Models\UsuarioModel;

class PerfilController {

    private $usuarioModel;

    public function __construct() {
        // Todos los métodos aquí requieren estar logueado
        // (La muralla en index.php ya nos protege)
        $this->usuarioModel = new UsuarioModel();
    }

    /**
     * Muestra la página principal de "Mi Perfil"
     */
    /**
     * Muestra la página principal de "Mi Perfil"
     */
    public function index() {
        $this->render('perfil/index', [
            'current_email' => $_SESSION['user_email'],
            'current_username' => $_SESSION['user_name'] ?? ''
        ]);
    }

    /**
     * Procesa la actualización de información personal (Usuario y Email)
     */
    public function actualizarInformacion() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php?controlador=perfil');
        }

        $userId = $_SESSION['user_id'];
        $newUsername = trim($_POST['username'] ?? '');
        $newEmail = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        // 1. Validaciones básicas
        if (strlen($newUsername) < 3) {
            Session::flash('error', 'El nombre de usuario debe tener al menos 3 caracteres.');
            redirect('index.php?controlador=perfil');
        }
        if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
            Session::flash('error', 'El formato del email no es válido.');
            redirect('index.php?controlador=perfil');
        }

        // 2. Verificar password actual (Seguridad)
        $currentHash = $this->usuarioModel->getPasswordById($userId);
        if (!password_verify($password, $currentHash)) {
            Session::flash('error', 'La contraseña actual es incorrecta. No se guardaron los cambios.');
            redirect('index.php?controlador=perfil');
        }

        // 3. Intentar actualizar Usuario
        $userUpdated = false;
        $emailUpdated = false;

        // Solo actualizamos si cambiaron
        if ($newUsername !== $_SESSION['user_name']) {
             $exitoUser = $this->usuarioModel->updateUsername($userId, $newUsername);
             if ($exitoUser) {
                 $_SESSION['user_name'] = $newUsername;
                 $userUpdated = true;
             } else {
                 Session::flash('error', 'El nombre de usuario ya está ocupado.');
                 redirect('index.php?controlador=perfil');
             }
        }

        // 4. Intentar actualizar Email
        if ($newEmail !== $_SESSION['user_email']) {
            $exitoEmail = $this->usuarioModel->updateEmail($userId, $newEmail);
            if ($exitoEmail) {
                $_SESSION['user_email'] = $newEmail;
                $emailUpdated = true;
            } else {
                Session::flash('error', 'El email ya está en uso por otra cuenta.');
                if ($userUpdated) Session::flash('warning', 'Solo se actualizó el usuario, el email falló.');
                redirect('index.php?controlador=perfil');
            }
        }

        Session::flash('success', 'Información actualizada correctamente.');
        redirect('index.php?controlador=perfil');
    }

    /**
     * Procesa el formulario de cambio de contraseña
     */
    public function actualizarPassword() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php?controlador=perfil');
        }
        
        $userId = $_SESSION['user_id'];
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // 1. Validar que las nuevas contraseñas coincidan
        if (empty($newPassword) || $newPassword !== $confirmPassword) {
            Session::flash('error', 'Las nuevas contraseñas no coinciden o están vacías.');
            redirect('index.php?controlador=perfil');
        }

        // 2. Verificar la contraseña actual
        $currentHash = $this->usuarioModel->getPasswordById($userId);
        if (!password_verify($currentPassword, $currentHash)) {
            Session::flash('error', 'La contraseña actual es incorrecta.');
            redirect('index.php?controlador=perfil');
        }

        // 3. Hashear y guardar la nueva contraseña
        $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $this->usuarioModel->updatePasswordById($userId, $newPasswordHash);

        Session::flash('success', 'Contraseña actualizada correctamente.');
        redirect('index.php?controlador=perfil');
    }

    /**
     * Función helper para renderizar (usa el layout principal)
     */
    private function render($vista, $data = []) {
        extract($data);
        $vistaContenido = __DIR__ . '/../../views/' . $vista . '.php';
        require __DIR__ . '/../../views/layouts/main.php';
    }
}