<?php
namespace App\Controllers;
 
use App\Core\BaseController;
use App\Core\Database;
use App\Core\Session;
use App\Domain\Enums\UserPlan;
use App\Domain\Enums\UserRole;
use App\Models\UsuarioModel;
 
class RegistroController extends BaseController {
    private $usuarioModel;
 
    public function __construct() {
        parent::__construct();
        $this->usuarioModel = new UsuarioModel();
    }

    public function index() {
        return $this->response->view('registro/index', [], 'auth');
    }

    /**
     * Procesa el registro de un nuevo usuario
     */
    public function guardar() {
        if (!$this->request->isPost()) {
            return $this->response->redirect('index.php?controlador=registro');
        }

        $email = $this->request->input('email', '');
        $username = $this->request->input('username', '');
        $password = $this->request->input('password', '');
        $password_confirm = $this->request->input('password_confirm', '');

        // Validación
        $rules = [
            'username' => 'required|min:3|unique:usuarios,username',
            'email'    => 'required|email|unique:usuarios,email',
            'password' => 'required|min:6'
        ];

        $db = \App\Core\Database::conectar();
        if (!$this->request->validate($rules, $db)) {
            Session::flash('error', $this->request->firstError());
            return $this->response->redirect('index.php?controlador=registro');
        }

        if ($password !== $password_confirm) {
            Session::flash('error', 'Las contraseñas no coinciden.');
            return $this->response->redirect('index.php?controlador=registro');
        }
        
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        
        try {
            $userData = [
                'username' => $username,
                'email' => $email,
                'password' => $passwordHash,
                'plan' => \App\Domain\Enums\UserPlan::FREE->value,
                'role' => \App\Domain\Enums\UserRole::USUARIO->value,
                'trial_ends_at' => null
            ];

            $this->usuarioModel->create($userData);
            
            Session::flash('success', '¡Te has registrado con éxito! Por favor, inicia sesión.');
            return $this->response->redirect('index.php?controlador=login');

        } catch (\Exception $e) {
            error_log("REGISTRO ERROR: " . $e->getMessage());
            Session::flash('error', 'Ocurrió un error en el registro. Es posible que el correo ya esté en uso.');
            return $this->response->redirect('index.php?controlador=registro');
        }
    }
}
