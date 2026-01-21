<?php
namespace App\Controllers;
 
use App\Core\BaseController;
use App\Core\Session;
use App\Services\AuthService;
use App\Domain\Enums\UserPlan;
 
class LoginController extends BaseController {
    private $authService;
 
    public function __construct() {
        parent::__construct();
        $this->authService = new AuthService();
    }

    public function index() {
        return $this->response->view('login/index', [], 'auth');
    }

    /**
     * Procesa las credenciales de inicio de sesión
     */
    public function verificar() {
        if (!$this->request->isPost()) {
            return $this->response->redirect('index.php?controlador=login');
        }

        $username = $this->request->input('username', '');
        $password = $this->request->input('password', '');

        $usuario = $this->authService->authenticate($username, $password);

        if ($usuario) {
            $this->authService->handleRememberMe($usuario['id'], $this->request->has('remember'));
            Session::flash('bienvenida', '¡Bienvenido al sistema, ' . htmlspecialchars($usuario['username']) . '!');
            return $this->response->redirect('index.php?controlador=login&accion=bienvenida');
        } else {
            Session::flash('error', 'Usuario o contraseña incorrectos.');
            return $this->response->redirect('index.php?controlador=login');
        }
    }
    
    /**
     * Pantalla intermedia de bienvenida
     */
    public function bienvenida() {
        $mensaje = Session::getFlash('bienvenida');
        if (!$mensaje) {
            return $this->response->redirect('index.php?controlador=dashboard');
        }

        $plan = Session::get('user_plan', \App\Domain\Enums\UserPlan::FREE->value);
        $redirectUrl = ($plan === \App\Domain\Enums\UserPlan::FREE->value) 
            ? 'index.php?controlador=free&accion=index' 
            : 'index.php?controlador=dashboard';
            
        return $this->response->view('login/bienvenida', [
            'redirectUrl' => $redirectUrl,
            'mensaje' => $mensaje
        ], 'auth');
    }
    
    /**
     * Desbloqueo de emergencia (Challenge/Response)
     */
    public function verificarDesbloqueo() {
        if (!$this->request->isPost()) {
            return $this->response->json(['success' => false, 'message' => 'Método no permitido'], 405);
        }

        $result = $this->authService->verifyUnlock(
            $this->request->input('challenge', ''),
            $this->request->input('response', ''),
            $this->request->input('username', '')
        );
        return $this->response->json($result);
    }

    /**
     * Cierra la sesión
     */
    public function logout() {
        if (!$this->request->isPost()) {
            Session::flash('error', 'Acción no permitida (Debe ser POST).');
            return $this->response->redirect('index.php?controlador=dashboard');
        }

        $realUserId = Session::get('real_user_id');
        $this->authService->logout($realUserId);
        
        Session::flash('success', 'Has cerrado sesión correctamente.');
        return $this->response->redirect('index.php?controlador=login');
    }
}
