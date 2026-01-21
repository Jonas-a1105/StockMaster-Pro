<?php
namespace App\Controllers;

use App\Core\BaseController;
use App\Core\Session;
use App\Services\PasswordService;

class PasswordController extends BaseController {
    private $passwordService;

    public function __construct() {
        parent::__construct();
        $this->passwordService = new PasswordService();
    }

    public function request() {
        return $this->response->view('password/request');
    }

    public function send() {
        if (!$this->request->isPost()) {
            return $this->response->redirect('index.php?controlador=login');
        }
        
        $email = $this->request->input('email', '');
        $this->passwordService->initiateReset($email);
        
        Session::flash('success', 'Si existe una cuenta con ese email, se ha enviado un enlace de recuperación.');
        return $this->response->redirect('index.php?controlador=login');
    }

    public function reset() {
        $token = $this->request->query('token', '');
        if (!$this->passwordService->validateToken($token)) {
            Session::flash('error', 'El enlace de recuperación es inválido o ha expirado.');
            return $this->response->redirect('index.php?controlador=login');
        }
        return $this->response->view('password/reset', ['token' => $token]);
    }

    public function update() {
        if (!$this->request->isPost()) {
            return $this->response->redirect('index.php?controlador=login');
        }

        $token = $this->request->input('token', '');
        $password = $this->request->input('password', '');
        $password_confirm = $this->request->input('password_confirm', '');

        if (empty($password) || $password !== $password_confirm) {
            Session::flash('error', 'Las contraseñas no coinciden o están vacías.');
            return $this->response->redirect('index.php?controlador=password&accion=reset&token=' . $token);
        }

        if ($this->passwordService->completeReset($token, $password)) {
            Session::flash('success', '¡Contraseña actualizada! Ya puedes iniciar sesión.');
            return $this->response->redirect('index.php?controlador=login');
        } else {
            Session::flash('error', 'Error al actualizar contraseña o token expirado.');
            return $this->response->redirect('index.php?controlador=login');
        }
    }
}
