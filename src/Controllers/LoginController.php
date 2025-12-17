<?php
namespace App\Controllers;

use App\Core\Database;
use App\Core\Session;

class LoginController {

    /**
     * Muestra el formulario de login
     */
    public function index() {
        $this->render('login/index');
    }

    /**
     * Procesa el formulario de login
     */
    public function verificar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php?controlador=login');
        }

        $username = $_POST['username'] ?? ''; // Ahora es username
        $password = $_POST['password'] ?? '';

        // Buscar al usuario por username
        $db = Database::conectar();
        $stmt = $db->prepare("SELECT * FROM usuarios WHERE username = ?");
        $stmt->execute([$username]);
        $usuario = $stmt->fetch();

            // Verificar contraseña
        if ($usuario && password_verify($password, $usuario['password'])) {
            
            Session::init(); // Iniciar sesión (si no estaba iniciada)
            session_regenerate_id(true); // <--- PARCHE DE SEGURIDAD: Previene fijación de sesión
            
            
            // --- LÓGICA DE EQUIPOS (EMPLEADOS VS DUEÑOS) ---
            if (!empty($usuario['owner_id'])) {
                // CASO 1: ES UN EMPLEADO
                // Usamos el ID del jefe para que vea los productos del jefe
                $_SESSION['user_id'] = $usuario['owner_id']; 
                $_SESSION['real_user_id'] = $usuario['id']; // Guardamos su ID real por si acaso
                $_SESSION['es_empleado'] = true;
                
                // El empleado hereda el plan y el trial del jefe
                $stmtJefe = $db->prepare("SELECT plan, trial_ends_at FROM usuarios WHERE id = ?");
                $stmtJefe->execute([$usuario['owner_id']]);
                $jefe = $stmtJefe->fetch();
                
                $_SESSION['user_plan'] = $jefe['plan'];
                $_SESSION['trial_ends_at'] = $jefe['trial_ends_at'];
                
            } else {
                // CASO 2: ES EL DUEÑO (O USUARIO INDEPENDIENTE)
                $_SESSION['user_id'] = $usuario['id'];
                $_SESSION['real_user_id'] = $usuario['id'];
                $_SESSION['es_empleado'] = false;
                
                $_SESSION['user_plan'] = $usuario['plan'];
                $_SESSION['trial_ends_at'] = $usuario['trial_ends_at'];
            }
            // -----------------------------------------------

            // Datos comunes
            $_SESSION['user_email'] = $usuario['email'];
            $_SESSION['user_name'] = $usuario['username']; // Guardamos username en sesión
            $_SESSION['user_rol'] = $usuario['rol'];
            
            // Cargar tasa de cambio (Si existe y es mayor a 0)
            if (!empty($usuario['tasa_dolar']) && $usuario['tasa_dolar'] > 0) {
                $_SESSION['tasa_bcv'] = (float)$usuario['tasa_dolar'];
            } else {
                $_SESSION['tasa_bcv'] = 0; // O un valor por defecto
            }
            
            // Mensaje de éxito para la pantalla de bienvenida
            Session::flash('bienvenida', '¡Bienvenido al sistema, ' . htmlspecialchars($usuario['username']) . '!');
            
            // --- REMEMBER ME LOGIC ---
            
            // 1. Remember Username (Last User) - Persistent even after logout
            setcookie('last_username', $usuario['username'], time() + (90 * 24 * 60 * 60), '/', '', false, true);

            // 2. Remember Session (Auto Login)
            if (isset($_POST['remember'])) {
                $token = bin2hex(random_bytes(32)); // Generate secure token
                $usuarioModel = new \App\Models\UsuarioModel();
                $usuarioModel->setRememberToken($_SESSION['real_user_id'], $token); // Store in DB
                
                // Set Secure Cookie (30 days)
                $expiry = time() + (30 * 24 * 60 * 60);
                setcookie('remember_token', $token, $expiry, '/', '', false, true);
            }
            
            // Redirigir a la animación de bienvenida
            redirect('index.php?controlador=login&accion=bienvenida');

        } else {
            // Error de credenciales
            Session::flash('error', 'Usuario o contraseña incorrectos.');
            redirect('index.php?controlador=login');
        }
    }
    
    /**
     * Muestra la pantalla de animación de bienvenida
     */
    public function bienvenida() {
        // Solo mostramos esta pantalla si hay un mensaje de bienvenida
        $mensaje = Session::getFlash('bienvenida');
        
        if (!$mensaje) {
            // Si recargan la página, ir directo al dashboard
            redirect('index.php?controlador=dashboard');
        }
        
        // Renderizar la vista de bienvenida (sin layout)
        $redirectUrl = ($_SESSION['user_plan'] ?? 'free') === 'free' 
            ? 'index.php?controlador=free&accion=index' 
            : 'index.php?controlador=dashboard';
            
        require __DIR__ . '/../../views/login/bienvenida.php';
    }
    
    /**
     * RESETEO TÉCNICO (Soporte Challenge-Response)
     */
    public function verificarDesbloqueo() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método inválido']);
            exit;
        }

        $challenge = $_POST['challenge'] ?? '';
        $response  = $_POST['response'] ?? '';
        $username  = $_POST['username'] ?? '';
        // Secreto Maestro (Debe coincidir con la herramienta del soporte)
        $masterSecret = "StockMaster_Secure_2025_Key"; 

        if (empty($challenge) || empty($response) || empty($username)) {
            echo json_encode(['success' => false, 'message' => 'Faltan datos']);
            exit;
        }

        // 1. Verificar Firma Digital (SHA-256)
        // La lógica debe ser EXACTAMENTE igual a la herramienta JS del soporte
        // Algoritmo: Upper(Substr(SHA256(Challenge + Secret), 0, 6))
        
        $hash = hash('sha256', $challenge . $masterSecret);
        $expectedResponse = strtoupper(substr($hash, 0, 6));

        if ($response !== $expectedResponse) {
            echo json_encode(['success' => false, 'message' => 'Código de autorización inválido']);
            exit;
        }

        // 2. Si es válido, reseteamos al usuario
        $db = Database::conectar();
        
        // Verificar si usuario existe
        $stmt = $db->prepare("SELECT id FROM usuarios WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
            exit;
        }

        // 3. Resetear contraseña a 'admin123'
        $newPass = password_hash('admin123', PASSWORD_DEFAULT);
        $update = $db->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
        
        if ($update->execute([$newPass, $user['id']])) {
            echo json_encode(['success' => true, 'message' => 'Contraseña restablecida a: admin123']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error de base de datos']);
        }
        exit;
    }

    /**
     * Cierra la sesión del usuario
     */
    public function logout() {
        Session::init();
        
        // Remove DB Token
        if (isset($_SESSION['real_user_id'])) {
           $usuarioModel = new \App\Models\UsuarioModel();
           $usuarioModel->removeRememberToken($_SESSION['real_user_id']);
        }
        
        session_unset();
        session_destroy();
        
        // Remove Cookie
        if (isset($_COOKIE['remember_token'])) {
            unset($_COOKIE['remember_token']); 
            setcookie('remember_token', '', time() - 3600, '/'); // expire it
        }
        
        // Iniciar nueva sesión solo para el mensaje de despedida
        Session::init();
        Session::flash('success', 'Has cerrado sesión correctamente.');
        
        redirect('index.php?controlador=login');
    }

    /**
     * Función helper para renderizar vistas
     */
    private function render($vista, $data = []) {
        extract($data);
        require __DIR__ . '/../../views/' . $vista . '.php';
    }
}