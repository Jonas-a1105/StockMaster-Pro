<?php
namespace App\Core\Middleware;

use App\Helpers\LicenseHelper;
use App\Models\UsuarioModel;
use App\Core\Session;
use App\Domain\Enums\UserPlan;

class LicenseMiddleware implements MiddlewareInterface {
    private $whitelist = ['license', 'free', 'ayuda', 'perfil', 'acerca'];

    public function handle(): bool {
        if (!isset($_SESSION['user_id'])) return true;

        $controlador = $_GET['controlador'] ?? null;
        $accion = $_GET['accion'] ?? 'index';

        if (!in_array($controlador, $this->whitelist) && $accion !== 'logout') {
            if (!LicenseHelper::validarEstado()) {
                $this->handleExpiredLicense();
                redirect('index.php?controlador=free');
                return false;
            }
        }
        
        return true;
    }

    private function handleExpiredLicense() {
        if (isset($_SESSION['user_plan']) && $_SESSION['user_plan'] === UserPlan::PREMIUM->value) {
            $_SESSION['user_plan'] = UserPlan::FREE->value;
            $uModel = new UsuarioModel();
            $uModel->actualizarPlan($_SESSION['user_id'], UserPlan::FREE->value);
            Session::flash('error', 'Tu licencia ha expirado. Por favor, renuevala.');
        }
    }
}
