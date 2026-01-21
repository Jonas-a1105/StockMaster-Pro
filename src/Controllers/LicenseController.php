<?php
namespace App\Controllers;
 
use App\Core\BaseController;
use App\Helpers\LicenseHelper;
use App\Core\Session;
use App\Models\UsuarioModel;
use App\Domain\Enums\UserPlan;
 
class LicenseController extends BaseController {
    private $usuarioModel;
 
    public function __construct() {
        parent::__construct();
        $this->usuarioModel = new UsuarioModel();
    }
    
    /**
     * Renderiza la vista de activación (Bloqueo)
     */
    public function index() {
        // Si ya está activa, redirigir al dashboard
        if (LicenseHelper::validarEstado()) {
            return $this->response->redirect('index.php?controlador=dashboard');
        }
        
        return $this->response->view('license/activate');
    }

    /**
     * Procesa la activación de licencia
     */
    public function activar() {
        if (!$this->request->isPost()) {
            return $this->response->redirect('index.php?controlador=dashboard');
        }

        $key = $this->request->input('license_key');
        $resultado = LicenseHelper::activarLicencia($key);

        if ($resultado['success']) {
            // 1. Actualizar Sesión usando Enum
            Session::set('user_plan', UserPlan::PREMIUM->value);
            
            // 2. Actualizar Usuario en BD
            $userId = Session::get('user_id');
            if ($userId) {
                $this->usuarioModel->update($userId, ['plan' => UserPlan::PREMIUM->value]);
            }

            Session::flash('success', $resultado['message']);
            return $this->response->redirect('index.php?controlador=dashboard');
        } else {
            Session::flash('error', $resultado['message']);
            // Redirigir a free porque ahí está el formulario de upgrade
            return $this->response->redirect('index.php?controlador=free'); 
        }
    }

    /**
     * Endpoint AJAX para verificar estado (Heartbeat)
     */
    public function checkStatus() {
        $isActive = LicenseHelper::validarEstado();
        return $this->response->json(['active' => $isActive]);
    }
}
