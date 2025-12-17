<?php
namespace App\Controllers;

use App\Core\Controller; // Assuming Base Controller exists or similar
use App\Helpers\LicenseHelper;
use App\Core\Session;

class LicenseController {
    
    // Renderiza la vista de activación (Bloqueo)
    // Renderiza la vista de activación (Bloqueo)
    public function index() {
        // DEBUG TEMPORAL ELIMINADO
        
        // Si ya está activa, redirigir al dashboard
        if (LicenseHelper::validarEstado()) {
            header("Location: index.php?controlador=dashboard");
            exit;
        }
        
        require __DIR__ . '/../../views/license/activate.php';
    }

    // Procesa la activación
    public function activar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $key = $_POST['license_key'] ?? '';
            
            $resultado = LicenseHelper::activarLicencia($key);

            if ($resultado['success']) {
                // 1. Actualizar Sesión
                $_SESSION['user_plan'] = 'premium';
                
                // 2. Actualizar Usuario en BD (Para que persista al reloguear)
                if (isset($_SESSION['user_id'])) {
                    $userId = $_SESSION['user_id'];
                    $db = \App\Core\Database::conectar();
                    $stmt = $db->prepare("UPDATE usuarios SET plan = 'premium' WHERE id = ?");
                    $stmt->execute([$userId]);
                }

                Session::flash('success', $resultado['message']);
                header("Location: index.php?controlador=dashboard");
            } else {
                Session::flash('error', $resultado['message']);
                // IMPORTANTE: Redirigir a FREE porque ahí está ahora el formulario
                header("Location: index.php?controlador=free"); 
            }
            exit;
        }
    }
    // Endpoint AJAX para verificar estado (Heartbeat)
    public function checkStatus() {
        header('Content-Type: application/json');
        
        $isActive = LicenseHelper::validarEstado();
        
        echo json_encode(['active' => $isActive]);
        exit;
    }
}
