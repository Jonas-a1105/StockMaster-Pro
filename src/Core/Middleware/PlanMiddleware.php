<?php
namespace App\Core\Middleware;

use App\Core\Session;
use App\Models\UsuarioModel;
use App\Domain\Enums\UserPlan;

use App\Domain\Enums\Capability;
use App\Core\Gate;

class PlanMiddleware implements MiddlewareInterface {
    /**
     * Mapeo de Controladores a Capacidades requeridas
     */
    private array $controllerPermissions = [
        'venta' => Capability::ACCESS_POS,
        'reporte' => Capability::VIEW_REPORTS,
        'config' => Capability::MANAGE_CONFIG,
        'equipo' => Capability::MANAGE_USERS,
        'compra' => Capability::ADVANCED_INVENTORY,
        'producto' => Capability::ADVANCED_INVENTORY,
        'movimiento' => Capability::ADVANCED_INVENTORY,
        'proveedor' => Capability::ADVANCED_INVENTORY,
        'ticket' => Capability::MANAGE_TICKETS,
        'audit' => Capability::VIEW_REPORTS,
        // Agrega más mapeos según sea necesario
    ];

    public function handle(): bool {
        if (!isset($_SESSION['user_id'])) return true;

        $this->checkTrialExpiration();

        $controlador = $_GET['controlador'] ?? null;
        
        if ($controlador === null) {
            $_GET['controlador'] = ($_SESSION['user_plan'] === UserPlan::FREE->value) ? 'free' : 'dashboard';
            $controlador = $_GET['controlador'];
        }

        // Si el controlador requiere una capacidad específica
        if (isset($this->controllerPermissions[$controlador])) {
            $capability = $this->controllerPermissions[$controlador];
            $plan = $_SESSION['user_plan'] ?? UserPlan::FREE->value;
            $role = $_SESSION['user_rol'] ?? 'usuario';

            if (!Gate::allows($plan, $role, $capability)) {
                Session::flash('error', 'Esta es una función Premium. ¡Actualiza tu plan!');
                redirect('index.php?controlador=premium'); 
                return false;
            }
        }

        return true;
    }

    private function checkTrialExpiration() {
        $planEnSesion = $_SESSION['user_plan'] ?? UserPlan::FREE->value; 
        $fechaExpiracion = $_SESSION['trial_ends_at'] ?? null;
        
        if ($planEnSesion === UserPlan::PREMIUM->value && $fechaExpiracion !== null && strtotime($fechaExpiracion) < time()) {
            $usuarioModel = new UsuarioModel();
            $usuarioModel->actualizarPlan($_SESSION['user_id'], UserPlan::FREE->value);
            $_SESSION['user_plan'] = UserPlan::FREE->value; 
            $_SESSION['trial_ends_at'] = null;
        }
    }
}
