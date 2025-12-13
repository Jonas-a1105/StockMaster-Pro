<?php
namespace App\Controllers;

use App\Core\Database;
use App\Core\Session;

class ConfigController {

    public function __construct() {
        if ($_SESSION['user_plan'] === 'free') {
            redirect('index.php?controlador=premium');
        }
    }

    public function index() {
        $db = Database::conectar();
        $stmt = $db->prepare("SELECT empresa_nombre, empresa_direccion, empresa_telefono, empresa_logo FROM usuarios WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $config = $stmt->fetch();

        $this->render('config/index', ['config' => $config]);
    }

    public function guardarTasa() {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);
        $tasa = (float)($data['tasa'] ?? 0);
        
        if ($tasa > 0) {
            // Guardar en sesión
            $_SESSION['tasa_bcv'] = $tasa;
            
            // Guardar en Base de Datos
            try {
                $db = Database::conectar();
                $stmt = $db->prepare("UPDATE usuarios SET tasa_dolar = ? WHERE id = ?");
                $stmt->execute([$tasa, $_SESSION['user_id']]);
                
                echo json_encode(['success' => true, 'message' => 'Tasa actualizada y guardada']);
            } catch (\Exception $e) {
                // Si falla la BD, al menos queda en sesión
                echo json_encode(['success' => true, 'message' => 'Tasa actualizada (solo sesión)']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Tasa inválida']);
        }
        exit;
    }
    
    public function obtenerTasa() {
        header('Content-Type: application/json');
        
        // Si no está en sesión, intentar buscar en BD
        if (empty($_SESSION['tasa_bcv']) || $_SESSION['tasa_bcv'] <= 0) {
            try {
                $db = Database::conectar();
                $stmt = $db->prepare("SELECT tasa_dolar FROM usuarios WHERE id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $user = $stmt->fetch();
                
                if ($user && $user['tasa_dolar'] > 0) {
                    $_SESSION['tasa_bcv'] = (float)$user['tasa_dolar'];
                }
            } catch (\Exception $e) {
                // Silencioso
            }
        }
        
        $tasa = (float)($_SESSION['tasa_bcv'] ?? 0);
        echo json_encode(['success' => true, 'tasa' => $tasa]);
        exit;
    }

    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre = $_POST['nombre'];
            $direccion = $_POST['direccion'];
            $telefono = $_POST['telefono'];
            $userId = $_SESSION['user_id'];

            $db = Database::conectar();
            
            // 1. Actualizar textos
            $sql = "UPDATE usuarios SET empresa_nombre = ?, empresa_direccion = ?, empresa_telefono = ? WHERE id = ?";
            $params = [$nombre, $direccion, $telefono, $userId];
            $stmt = $db->prepare($sql);
            $stmt->execute($params);

            // 2. Procesar Logo (Si se subió uno)
            if (isset($_FILES['logo']) && $_FILES['logo']['error'] === 0) {
                $permitidos = ['image/jpeg', 'image/png', 'image/jpg'];
                if (in_array($_FILES['logo']['type'], $permitidos)) {
                    
                    $ext = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
                    $nombreArchivo = 'logo_' . $userId . '.' . $ext;
                    $rutaDestino = __DIR__ . '/../../public/uploads/' . $nombreArchivo;
                    
                    // Crear carpeta si no existe
                    if (!file_exists(__DIR__ . '/../../public/uploads/')) {
                        mkdir(__DIR__ . '/../../public/uploads/', 0777, true);
                    }

                    if (move_uploaded_file($_FILES['logo']['tmp_name'], $rutaDestino)) {
                        // Guardar ruta en BD
                        $stmtLogo = $db->prepare("UPDATE usuarios SET empresa_logo = ? WHERE id = ?");
                        $stmtLogo->execute([$nombreArchivo, $userId]);
                    }
                }
            }

            Session::flash('success', 'Configuración guardada.');
        }
        redirect('index.php?controlador=config');
    }

    private function render($vista, $data = []) {
        extract($data);
        $vistaContenido = __DIR__ . '/../../views/' . $vista . '.php';
        require __DIR__ . '/../../views/layouts/main.php';
    }
}