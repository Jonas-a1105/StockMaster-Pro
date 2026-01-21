<?php
namespace App\Controllers;
 
use App\Core\BaseController;
use App\Core\Database;
use App\Core\Session;
use App\Models\UsuarioModel;
use App\Domain\Enums\UserPlan;
 
class EquipoController extends BaseController {
    private $usuarioModel;
 
    public function __construct() {
        parent::__construct();
 
        if ($this->userPlan === UserPlan::FREE->value) {
            return $this->response->redirect('index.php?controlador=premium');
        }
        if (Session::get('es_empleado')) {
            Session::flash('error', 'Solo el dueño de la cuenta puede gestionar el equipo.');
            return $this->response->redirect('index.php?controlador=dashboard');
        }
 
        $this->usuarioModel = new UsuarioModel();
    }

    public function index() {
        $ownerId = Session::get('user_id');
        $empleados = $this->usuarioModel->obtenerEquipo($ownerId);
        return $this->response->view('equipo/index', ['empleados' => $empleados]);
    }

    public function crear() {
        if (!$this->request->isPost()) {
             return $this->response->redirect('index.php?controlador=equipo');
        }

        $email = $this->request->input('email');
        $password = $this->request->input('password');
        $ownerId = Session::get('user_id');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 4) {
            Session::flash('error', 'Datos inválidos.');
            return $this->response->redirect('index.php?controlador=equipo');
        }

        try {
            $this->usuarioModel->create([
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'plan' => 'free',
                'owner_id' => $ownerId,
                'es_empleado' => 1
            ]);
            Session::flash('success', 'Miembro del equipo agregado.');
        } catch (\Exception $e) {
            Session::flash('error', 'El email ya está registrado.');
        }
        
        return $this->response->redirect('index.php?controlador=equipo');
    }

    public function eliminar() {
        if (!$this->request->isPost()) {
             return $this->response->redirect('index.php?controlador=equipo');
        }

        $id = $this->request->input('id', 0, 'int');
        $ownerId = Session::get('user_id');

        if ($this->usuarioModel->eliminarMiembro($id, $ownerId)) {
            Session::flash('success', 'Miembro eliminado.');
        } else {
            Session::flash('error', 'No se pudo eliminar el miembro.');
        }
        
        return $this->response->redirect('index.php?controlador=equipo');
    }
}
