<?php


// 1. Habilitar la visualización de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2. Cargar el Autoloader de Composer
require_once __DIR__ . '/../vendor/autoload.php';

// 3. Definir namespaces
use App\Models\UsuarioModel;
use App\Core\Session;

// 4. Función de ayuda 'redirect()'
// Detectar BASE_URL dinámicamente
$scriptDir = dirname($_SERVER['SCRIPT_NAME']);
$baseUrl = rtrim($scriptDir, '/\\') . '/';
define('BASE_URL', $baseUrl);

// Detectar Versión de la App (desde package.json)
// Detectar Versión de la App
// 1. Intentar desde Variable de Entorno (Inyectada por Electron)
$appVersion = getenv('APP_VERSION_ELECTRON');

// 2. Si no existe (Entorno Web o Dev sin Electron), buscar en package.json
if (!$appVersion) {
    $appVersion = '1.0.8'; // Valor por defecto
    $posiblesRutas = [
        __DIR__ . '/../package.json',           // Desarrollo / Estándar
        __DIR__ . '/../../package.json',        // Alternativa profundidad
        dirname(__DIR__) . '/package.json'      // Otra forma de llegar al root
    ];

    foreach ($posiblesRutas as $ruta) {
        if (file_exists($ruta)) {
            $packageContent = file_get_contents($ruta);
            $packageData = json_decode($packageContent, true);
            if ($packageData && isset($packageData['version'])) {
                $appVersion = $packageData['version'];
                break;
            }
        }
    }
}
define('APP_VERSION', $appVersion);

function redirect($ruta) {
    // Asegurar que la sesión se guarde antes de redirigir
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_write_close();
    }
    
    header('Location: ' . BASE_URL . $ruta);
    exit;
}

// 5. Iniciar Sesión y Chequear Expiración
Session::init(); 

// 6. Chequeo de Expiración de Trial
if (isset($_SESSION['user_id'])) {
    $planEnSesion = $_SESSION['user_plan'] ?? 'free'; 
    $fechaExpiracion = $_SESSION['trial_ends_at'] ?? null;
    
    if ($planEnSesion === 'premium' && $fechaExpiracion !== null && strtotime($fechaExpiracion) < time()) {
        $usuarioModel = new UsuarioModel();
        $usuarioModel->actualizarPlan($_SESSION['user_id'], 'free');
        $_SESSION['user_plan'] = 'free'; 
        $_SESSION['trial_ends_at'] = null;
    }
}

// 7. La Muralla de Seguridad
$controladorNombre = $_GET['controlador'] ?? null;
$accion = $_GET['accion'] ?? 'index';

// Chequeo de Admin
if ($controladorNombre === 'admin' && ($_SESSION['user_rol'] ?? 'usuario') !== 'admin') {
    redirect('index.php?controlador=dashboard');
}

// Chequeo de Páginas Públicas
$paginasPublicas = [
    'login' => ['index', 'verificar', 'logout', 'bienvenida', 'verificarDesbloqueo'],
    'registro' => ['index', 'guardar'],
    'password' => ['request', 'send', 'reset', 'update'],
    'webhook' => ['recibir']
];



// --- AUTO-LOGIN CHECK (Remember Me) ---
if (!isset($_SESSION['user_id'])) {
    if (isset($_COOKIE['remember_token'])) {
        $token = $_COOKIE['remember_token'];
        $usuarioModel = new UsuarioModel();
        $usuario = $usuarioModel->findByRememberToken($token);
        
        if ($usuario) {
            // ... (Session set logic moved inside) ...
            $_SESSION['user_id'] = $usuario['id'];
            $_SESSION['real_user_id'] = $usuario['id'];
            
            // ... (Rest of login logic) ...
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
            $_SESSION['user_rol'] = $usuario['rol'];
            $_SESSION['tasa_bcv'] = !empty($usuario['tasa_dolar']) && $usuario['tasa_dolar'] > 0 ? (float)$usuario['tasa_dolar'] : 0;
            
             // Refresh Token
            $newToken = bin2hex(random_bytes(32));
            $usuarioModel->setRememberToken($usuario['id'], $newToken);
            setcookie('remember_token', $newToken, time() + (30 * 24 * 60 * 60), '/', '', false, true);
        }
    }
}

if (!isset($_SESSION['user_id'])) {
    // Si el usuario NO está logueado
    if ($controladorNombre === null || (!array_key_exists($controladorNombre, $paginasPublicas) || !in_array($accion, $paginasPublicas[$controladorNombre]))) {
        redirect('index.php?controlador=login&accion=index');
    }
} else {
    // Si el usuario SÍ está logueado
    
    // 7.5 Verificación de Licencia (Sistema Enterprise)
    // Bloquear acceso a módulos Premium si la licencia no es válida.
    // WHITELIST: Controladores permitidos para todos (incluso sin licencia/free)
    $whitelist = ['license', 'free', 'ayuda', 'perfil', 'acerca'];
    
    if (!in_array($controladorNombre, $whitelist) && $accion !== 'logout') {
        if (!\App\Helpers\LicenseHelper::validarEstado()) {
            // Si la licencia falló o expiró, hacemos downgrade inmediato
            if (isset($_SESSION['user_plan']) && $_SESSION['user_plan'] === 'premium') {
                $_SESSION['user_plan'] = 'free';
                
                // Actualizar DB para persistencia
                if (isset($_SESSION['user_id'])) {
                    $uModel = new \App\Models\UsuarioModel();
                    $uModel->actualizarPlan($_SESSION['user_id'], 'free');
                }
                Session::flash('error', 'Tu licencia ha expirado. Por favor, renuevala.');
            }

            // Redirigir a la vista de bloqueo
            redirect('index.php?controlador=free');
        }
    }
    
    if ($controladorNombre === null) {
        // Si es free, su página por defecto es 'free', si es premium es 'dashboard'
        $controladorNombre = ($_SESSION['user_plan'] === 'free') ? 'free' : 'dashboard';
    }
    
    // --- ¡LISTA ACTUALIZADA AQUÍ! ---
    // Agregamos 'equipo' y 'config' a la lista de controladores Premium
    $controladoresPremium = [
        'dashboard', 
        'producto', 
        'movimiento', 
        'proveedor', 
        'reporte', 
        'venta', 
        'ticket', 
        'equipo', 
        'config',
        'compra'
    ];
    
    if (in_array($controladorNombre, $controladoresPremium) && ($_SESSION['user_plan'] ?? 'free') === 'free') {
        Session::flash('error', 'Esta es una función Premium. ¡Actualiza tu plan!');
        redirect('index.php?controlador=premium'); 
    }
}

// 8. El Router
$claseControlador = 'App\\Controllers\\' . ucfirst($controladorNombre) . 'Controller';

if (class_exists($claseControlador)) {
    $controlador = new $claseControlador();
    if (method_exists($controlador, $accion)) {
        $controlador->$accion();
    } else {
        http_response_code(404);
        // Verificar si se espera JSON (fetch API suele enviar Content-Type: application/json)
        if (
            (isset($_SERVER['HTTP_CONTENT_TYPE']) && strpos($_SERVER['HTTP_CONTENT_TYPE'], 'json') !== false) ||
            (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'json') !== false)
        ) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => "Error 404: Acción '$accion' no encontrada en '$claseControlador'"]);
        } else {
            echo "Error 404: Acción '$accion' no encontrada en '$claseControlador'.";
        }
    }
} else {
    http_response_code(404);
    if (
        (isset($_SERVER['HTTP_CONTENT_TYPE']) && strpos($_SERVER['HTTP_CONTENT_TYPE'], 'json') !== false) ||
        (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'json') !== false)
    ) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => "Error 404: Controlador '$claseControlador' no encontrado."]);
    } else {
        echo "Error 404: Controlador '$claseControlador' no encontrado.";
    }
}