<?php


// 1. Habilitar la visualización de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2. Cargar el Autoloader de Composer
require_once __DIR__ . '/../vendor/autoload.php';

// 3. Iniciar Sesión (¡IMPORTANTE: Debe ir antes de cualquier salida!)
App\Core\Session::init();

// 3.1 Aliases globales para las Vistas
class_alias(\App\Core\View::class, 'View');
class_alias(\App\Helpers\Icons::class, 'Icons');

// 2.1 Cargar variables de entorno (.env)
try {
    if (file_exists(__DIR__ . '/../.env')) {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
        $dotenv->load();
    }
} catch (\Exception $e) {
    // Si no hay .env, continuamos con los valores por defecto
}

// 4. Definir namespaces
use App\Models\UsuarioModel;
use App\Core\Session;
use App\Core\Request;
use App\Core\Response;
use App\Core\ExceptionHandler;
use App\Core\Migration\MigrationManager;

// --- REGISTRO DE MANEJADOR DE EXCEPCIONES ---
$exceptionHandler = new ExceptionHandler();
$exceptionHandler->register();

// 4. Definir BASE_URL dinámicamente
$scriptDir = dirname($_SERVER['SCRIPT_NAME']);
$baseUrl = rtrim($scriptDir, '/\\') . '/';
define('BASE_URL', $baseUrl);

// Detectar Versión de la App (desde package.json o env)
$appVersion = getenv('APP_VERSION_ELECTRON');
if (!$appVersion) {
    $appVersion = '1.0.8'; // Valor por defecto
    $posiblesRutas = [
        __DIR__ . '/../package.json',
        dirname(__DIR__) . '/package.json'
    ];
    foreach ($posiblesRutas as $ruta) {
        if (file_exists($ruta)) {
            $packageData = json_decode(file_get_contents($ruta), true);
            if ($packageData && isset($packageData['version'])) {
                $appVersion = $packageData['version'];
                break;
            }
        }
    }
}
define('APP_VERSION', $appVersion);

function redirect($ruta) {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_write_close();
    }
    header('Location: ' . BASE_URL . $ruta);
    exit;
}

// --- EJECUTAR MIGRACIONES ---
try {
    $migrationManager = new MigrationManager();
    $migrationManager->run();
} catch (\Exception $e) {
    throw $e;
}

// 6. Ejecutar Stack de Middlewares (La Muralla de Seguridad Profesional)
$handler = new \App\Core\Middleware\MiddlewareHandler();
$handler->add(new \App\Core\Middleware\CsrfMiddleware())
        ->add(new \App\Core\Middleware\AuthMiddleware())
        ->add(new \App\Core\Middleware\AdminMiddleware())
        ->add(new \App\Core\Middleware\LicenseMiddleware())
        ->add(new \App\Core\Middleware\PlanMiddleware());

// Si algún middleware falla (redirige o detiene), el handler retorna false
if (!$handler->run()) {
    exit;
}

// 7. Preparar ruteo 
$request = new \App\Core\Request();
$controladorNombre = $request->query('controlador', 'dashboard');
$accion = $request->query('accion', 'index');

// 8. El Router
$claseControlador = 'App\\Controllers\\' . ucfirst($controladorNombre) . 'Controller';

// DEBUG
// var_dump($claseControlador);

if (class_exists($claseControlador)) {
    $controlador = new $claseControlador();
    if (method_exists($controlador, $accion)) {
        // Ejecutamos la acción pasando el objeto Request
        $controlador->$accion($request);
    } else {
        http_response_code(404);
        // Verificar si se espera JSON
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
        echo "Error 404: Controlador '$claseControlador' no encontrado. (File: " . (__DIR__ . '/../src/Controllers/' . ucfirst($controladorNombre) . 'Controller.php') . " exists: " . (file_exists(__DIR__ . '/../src/Controllers/' . ucfirst($controladorNombre) . 'Controller.php') ? 'SI' : 'NO') . ")";
    }
}