<?php
namespace App\Controllers;

use App\Models\NotificacionModel;

class NotificacionController {
    
    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'No autorizado']);
                exit;
            }
            redirect('index.php?controlador=login');
        }
    }

    /**
     * Marcar todas las notificaciones como leídas
     */
    public function marcarTodasLeidas() {
        header('Content-Type: application/json');
        $userId = $_SESSION['user_id'];
        
        $model = new NotificacionModel();
        $result = $model->marcarTodasLeidas($userId);
        
        echo json_encode(['success' => $result]);
        exit;
    }

    /**
     * Marcar una notificación específica como leída
     */
    public function marcarLeida() {
        header('Content-Type: application/json');
        $userId = $_SESSION['user_id'];
        $id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
        
        if ($id > 0) {
            $model = new NotificacionModel();
            $result = $model->marcarLeida($userId, $id);
            echo json_encode(['success' => $result]);
        } else {
            echo json_encode(['success' => false, 'message' => 'ID inválido']);
        }
        exit;
    }

    /**
     * API: Obtener notificaciones no leídas (para polling/actualización)
     */
    public function apiObtenerNoLeidas() {
        header('Content-Type: application/json');
        $userId = $_SESSION['user_id'];
        
        $model = new NotificacionModel();
        $notificaciones = $model->obtenerNoLeidas($userId, 10);
        $count = $model->contarNoLeidas($userId);
        
        echo json_encode([
            'count' => $count,
            'notificaciones' => $notificaciones
        ]);
        exit;
    }
}
