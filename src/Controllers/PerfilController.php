<?php
namespace App\Controllers;

use App\Core\BaseController;
use App\Core\Session;
use App\Models\UsuarioModel;
use App\Core\Database;
use App\Exceptions\ValidationException;
use App\Exceptions\AppException;

class PerfilController extends BaseController {
    private $usuarioModel;

    public function __construct() {
        parent::__construct();
        $this->usuarioModel = new UsuarioModel();
    }

    public function index() {
        return $this->response->view('perfil/index', [
            'current_email' => Session::get('user_email'),
            'current_username' => Session::get('user_name')
        ]);
    }

    public function actualizarInformacion() {
        if (!$this->request->isPost()) {
            throw new AppException('Solicitud inválida.', 405);
        }

        $newUsername = trim($this->request->input('username', ''));
        $newEmail = trim($this->request->input('email', ''));
        $password = $this->request->input('password', '');

        $rules = [
            'username' => 'required|min:3',
            'email' => 'required|email',
            'password' => 'required'
        ];

        if (!$this->request->validate($rules, Database::conectar())) {
            throw new ValidationException(['form' => $this->request->firstError()], 'Error al actualizar información.');
        }

        $currentHash = $this->usuarioModel->getPasswordById($this->userId);
        if (!password_verify($password, $currentHash)) {
            throw new AppException('La contraseña actual es incorrecta.', 401);
        }

        if ($newUsername !== Session::get('user_name')) {
             if ($this->usuarioModel->updateUsername($this->userId, $newUsername)) {
                 Session::set('user_name', $newUsername);
             } else {
                 throw new ValidationException(['username' => 'El nombre de usuario ya está ocupado.'], 'Error de validación.');
             }
        }

        if ($newEmail !== Session::get('user_email')) {
            if ($this->usuarioModel->updateEmail($this->userId, $newEmail)) {
                Session::set('user_email', $newEmail);
            } else {
                throw new ValidationException(['email' => 'El email ya está en uso.'], 'Error de validación.');
            }
        }

        Session::flash('success', 'Información actualizada correctamente.');
        return $this->response->redirect('index.php?controlador=perfil');
    }

    public function actualizarPassword() {
        if (!$this->request->isPost()) {
            throw new AppException('Solicitud inválida.', 405);
        }
        
        $currentPassword = $this->request->input('current_password', '');
        $newPassword = $this->request->input('new_password', '');
        $confirmPassword = $this->request->input('confirm_password', '');

        if (empty($newPassword) || $newPassword !== $confirmPassword) {
            throw new ValidationException(['new_password' => 'Las nuevas contraseñas no coinciden.'], 'Error al cambiar contraseña.');
        }

        if (strlen($newPassword) < 8) {
            throw new ValidationException(['new_password' => 'La nueva contraseña debe tener al menos 8 caracteres.'], 'Seguridad insuficiente.');
        }

        $currentHash = $this->usuarioModel->getPasswordById($this->userId);
        if (!password_verify($currentPassword, $currentHash)) {
            throw new AppException('La contraseña actual es incorrecta.', 401);
        }

        $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $this->usuarioModel->updatePasswordById($this->userId, $newPasswordHash);

        Session::flash('success', 'Contraseña actualizada correctamente.');
        return $this->response->redirect('index.php?controlador=perfil');
    }
}
