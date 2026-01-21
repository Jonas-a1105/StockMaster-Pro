<?php
namespace App\Core\Middleware;

use App\Models\UsuarioModel;
use App\Core\Session;
use App\Domain\Enums\UserPlan;
use App\Domain\Enums\UserRole;

class AuthMiddleware implements MiddlewareInterface {
    private $publicRoutes = [
        'login' => ['index', 'verificar', 'logout', 'bienvenida', 'verificarDesbloqueo'],
        'registro' => ['index', 'guardar'],
        'password' => ['request', 'send', 'reset', 'update'],
        'webhook' => ['recibir']
    ];

    public function handle(): bool {
        $controlador = $_GET['controlador'] ?? null;
        $accion = $_GET['accion'] ?? 'index';

        // 1. Process "Remember Me" if no session exists
        if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_token'])) {
            $this->processRememberMe($_COOKIE['remember_token']);
        }

        // 2. Access control
        if (!isset($_SESSION['user_id'])) {
            if ($controlador === null || !$this->isPublic($controlador, $accion)) {
                redirect('index.php?controlador=login&accion=index');
                return false;
            }
        }

        return true;
    }

    private function isPublic($controlador, $accion): bool {
        return isset($this->publicRoutes[$controlador]) && in_array($accion, $this->publicRoutes[$controlador]);
    }

    private function processRememberMe($token) {
        $usuarioModel = new UsuarioModel();
        $usuario = $usuarioModel->findByRememberToken($token);
        
        if ($usuario) {
            $_SESSION['user_id'] = $usuario['id'];
            $_SESSION['real_user_id'] = $usuario['id'];
            
            if (!empty($usuario['owner_id'])) {
                 $db = \App\Core\Database::conectar();
                 $stmtJefe = $db->prepare("SELECT plan, trial_ends_at FROM usuarios WHERE id = ?");
                 $stmtJefe->execute([$usuario['owner_id']]);
                 $jefe = $stmtJefe->fetch();
                 $_SESSION['es_empleado'] = true;
                 $_SESSION['user_plan'] = $jefe['plan'];
                 $_SESSION['trial_ends_at'] = $jefe['trial_ends_at'];
            } else {
                $_SESSION['es_empleado'] = false;
                $_SESSION['user_plan'] = $usuario['plan'];
                $_SESSION['trial_ends_at'] = $usuario['trial_ends_at'];
            }
            
            $_SESSION['user_email'] = $usuario['email'];
            $_SESSION['user_name'] = $usuario['username'];
            $_SESSION['user_rol'] = $usuario['rol'] ?? UserRole::USUARIO->value;
            $_SESSION['tasa_bcv'] = !empty($usuario['tasa_dolar']) && $usuario['tasa_dolar'] > 0 ? (float)$usuario['tasa_dolar'] : 0;
            
            // Refresh Token
            $newToken = bin2hex(random_bytes(32));
            $usuarioModel->setRememberToken($usuario['id'], $newToken);
            setcookie('remember_token', $newToken, time() + (30 * 24 * 60 * 60), '/', '', false, true);
        }
    }
}
