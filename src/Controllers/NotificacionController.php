<?php
namespace App\Controllers;

use App\Core\BaseController;
use App\Core\Session;
use App\Models\NotificacionModel;

class NotificacionController extends BaseController {
    private $notificacionModel;

    public function __construct() {
        parent::__construct();
        $this->notificacionModel = new NotificacionModel();
    }

    /**
     * Marcar todas las notificaciones como leídas
     */
    public function marcarTodasLeidas() {
        $result = $this->notificacionModel->marcarTodasLeidas($this->userId);
        return $this->response->json(['success' => $result]);
    }

    /**
     * Marcar una notificación específica como leída
     */
    public function marcarLeida() {
        $id = $this->request->input('id', 0, 'int');
        
        if ($id > 0) {
            $result = $this->notificacionModel->marcarLeida($this->userId, $id);
            return $this->response->json(['success' => $result]);
        }
        return $this->response->json(['success' => false, 'message' => 'ID inválido'], 400);
    }

    /**
     * API: Obtener notificaciones no leídas (para polling/actualización)
     */
    public function apiObtenerNoLeidas() {
        $notificaciones = $this->notificacionModel->obtenerNoLeidas($this->userId, 10);
        $count = $this->notificacionModel->contarNoLeidas($this->userId);
        
        return $this->response->json([
            'count' => $count,
            'notificaciones' => $notificaciones
        ]);
    }
}
