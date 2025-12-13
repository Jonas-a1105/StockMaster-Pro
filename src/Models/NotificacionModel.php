<?php
namespace App\Models;

use App\Core\Database;
use \PDO;

/**
 * Modelo de Notificaciones - Gestiona las notificaciones del sistema
 */
class NotificacionModel {
    
    private $db;

    public function __construct() {
        $this->db = Database::conectar();
    }

    /**
     * Crear una nueva notificación
     */
    public function crear($userId, $tipo, $titulo, $mensaje, $prioridad = 'media', $link = null, $icono = 'fa-bell') {
        $query = "INSERT INTO notificaciones (user_id, tipo, titulo, mensaje, prioridad, link, icono) 
                  VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$userId, $tipo, $titulo, $mensaje, $prioridad, $link, $icono]);
        return $this->db->lastInsertId();
    }

    /**
     * Obtener notificaciones no leídas de un usuario
     */
    public function obtenerNoLeidas($userId, $limite = 10) {
        $query = "SELECT * FROM notificaciones 
                  WHERE user_id = ? AND leida = FALSE 
                  ORDER BY created_at DESC 
                  LIMIT ?";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(1, $userId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Contar notificaciones no leídas
     */
    public function contarNoLeidas($userId) {
        $query = "SELECT COUNT(*) as total FROM notificaciones 
                  WHERE user_id = ? AND leida = FALSE";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        return (int)$result['total'];
    }

    /**
     * Obtener todas las notificaciones (paginadas)
     */
    public function obtenerTodas($userId, $limite = 50, $offset = 0) {
        $query = "SELECT * FROM notificaciones 
                  WHERE user_id = ? 
                  ORDER BY created_at DESC 
                  LIMIT ? OFFSET ?";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(1, $userId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limite, PDO::PARAM_INT);
        $stmt->bindValue(3, $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Marcar notificación como leída
     */
    public function marcarLeida($userId, $id) {
        $query = "UPDATE notificaciones SET leida = TRUE 
                  WHERE id = ? AND user_id = ?";
        
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$id, $userId]);
    }

    /**
     * Marcar todas como leídas
     */
    public function marcarTodasLeidas($userId) {
        $query = "UPDATE notificaciones SET leida = TRUE 
                  WHERE user_id = ? AND leida = FALSE";
        
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$userId]);
    }

    /**
     * Eliminar notificaciones antiguas (más de 30 días)
     */
    public function limpiarAntiguas($userId) {
        $threshold = date('Y-m-d H:i:s', strtotime('-30 days'));
        $query = "DELETE FROM notificaciones 
                  WHERE user_id = ? AND created_at < ?";
        
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$userId, $threshold]);
    }

    /**
     * Crear notificación de stock bajo
     */
    public function crearAlertaStock($userId, $producto, $stockActual, $umbral) {
        $titulo = 'Stock Bajo';
        $mensaje = "El producto '{$producto}' tiene solo {$stockActual} unidades (umbral: {$umbral})";
        $icono = 'fa-exclamation-triangle';
        $prioridad = $stockActual == 0 ? 'critica' : 'alta';
        
        if ($stockActual == 0) {
            $titulo = '¡Stock Agotado!';
            $mensaje = "El producto '{$producto}' está AGOTADO";
        }
        
        return $this->crear($userId, 'stock', $titulo, $mensaje, $prioridad, 
            'index.php?controlador=producto&accion=index', $icono);
    }

    /**
     * Crear notificación de factura por vencer
     */
    public function crearAlertaFactura($userId, $proveedor, $monto, $diasRestantes) {
        $titulo = 'Factura por Vencer';
        $prioridad = 'media';
        $icono = 'fa-file-invoice-dollar';
        
        if ($diasRestantes <= 1) {
            $titulo = '¡Factura Vence Hoy!';
            $prioridad = 'critica';
        } elseif ($diasRestantes <= 3) {
            $titulo = 'Factura Próxima a Vencer';
            $prioridad = 'alta';
        }
        
        $mensaje = "Factura de {$proveedor} por \${$monto} vence en {$diasRestantes} día(s)";
        
        return $this->crear($userId, 'factura', $titulo, $mensaje, $prioridad,
            'index.php?controlador=compra&accion=index', $icono);
    }
}
