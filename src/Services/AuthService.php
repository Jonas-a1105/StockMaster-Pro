<?php
namespace App\Services;

use App\Models\UsuarioModel;
use App\Core\Database;
use App\Core\Session;
use App\Domain\ValueObjects\Email;

class AuthService {
    private $usuarioModel;
    private $db;

    public function __construct() {
        $this->usuarioModel = new UsuarioModel();
        $this->db = Database::conectar();
    }

    public function authenticate($username, $password) {
        $usuario = $this->usuarioModel->findByUsername($username);

        if ($usuario && password_verify($password, $usuario['password'])) {
            $this->setupSession($usuario);
            return $usuario;
        }
        return false;
    }

    private function setupSession($usuario) {
        Session::init();
        Session::regenerate();

        $userId = $usuario['id'];
        $realUserId = $usuario['id'];
        $esEmpleado = false;
        $plan = $usuario['plan'];
        $trialEndsAt = $usuario['trial_ends_at'];

        if (!empty($usuario['owner_id'])) {
            // Empleado
            $userId = $usuario['owner_id'];
            $esEmpleado = true;

            $jefe = $this->usuarioModel->getPlanAndTrialById($usuario['owner_id']);
            $plan = $jefe['plan'];
            $trialEndsAt = $jefe['trial_ends_at'];
        }

        Session::set('user_id', $userId);
        Session::set('real_user_id', $realUserId);
        Session::set('es_empleado', $esEmpleado);
        Session::set('user_plan', $plan);
        Session::set('trial_ends_at', $trialEndsAt);

        // Validar email con VO antes de guardar en sesión
        $email = new Email($usuario['email']);
        Session::set('user_email', $email->getAddress());
        Session::set('user_name', $usuario['username']);
        Session::set('user_rol', $usuario['rol'] ?? $usuario['role']);
        
        $tasa = (!empty($usuario['tasa_dolar']) && $usuario['tasa_dolar'] > 0) ? (float)$usuario['tasa_dolar'] : 0;
        Session::set('tasa_bcv', $tasa);
    }

    public function handleRememberMe($userId, $remember = false) {
        // Set last_username cookie anyway
        $username = Session::get('user_name');
        setcookie('last_username', $username, time() + (90 * 24 * 60 * 60), '/', '', false, true);

        if ($remember) {
            $token = bin2hex(random_bytes(32));
            $this->usuarioModel->setRememberToken($userId, $token);
            setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/', '', false, true);
        }
    }

    public function logout($realUserId) {
        Session::init();
        if ($realUserId) {
            $this->usuarioModel->removeRememberToken($realUserId);
        }
        
        // Destruir sesión completamente
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
        
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/');
        }
    }

    public function verifyUnlock($challenge, $response, $username) {
        $masterSecret = "StockMaster_Secure_2025_Key"; 
        if (empty($challenge) || empty($response) || empty($username)) {
            return ['success' => false, 'message' => 'Faltan datos'];
        }

        $hash = hash('sha256', $challenge . $masterSecret);
        $expectedResponse = strtoupper(substr($hash, 0, 6));

        if ($response !== $expectedResponse) {
            return ['success' => false, 'message' => 'Código de autorización inválido'];
        }

        $user = $this->usuarioModel->findByUsername($username);

        if (!$user) {
            return ['success' => false, 'message' => 'Usuario no encontrado'];
        }

        $newPass = password_hash('admin123', PASSWORD_DEFAULT);
        
        if ($this->usuarioModel->updatePasswordById($user['id'], $newPass)) {
            return ['success' => true, 'message' => 'Contraseña restablecida a: admin123'];
        }
        return ['success' => false, 'message' => 'Error de base de datos'];
    }
}
