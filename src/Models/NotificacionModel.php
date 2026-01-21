<?php
namespace App\Models;

use App\Core\BaseModel;
use App\Core\Database;
use \PDO;

/**
 * Modelo de Notificaciones - Gestiona las notificaciones del sistema
 */
class NotificacionModel extends BaseModel {
    protected $table = 'notificaciones';
    protected $fillable = [
        'user_id', 'tipo', 'titulo', 'mensaje', 
        'leida', 'prioridad', 'link', 'icono'
    ];
    protected $timestamps = true;

    public function __construct() {
        parent::__construct();
    }

    /**
     * Crear una nueva notificación
     */
    public function crear($userId, $tipo, $titulo, $mensaje, $prioridad = 'media', $link = null, $icono = 'fa-bell') {
        return $this->create([
            'user_id' => $userId,
            'tipo' => $tipo,
            'titulo' => $titulo,
            'mensaje' => $mensaje,
            'prioridad' => $prioridad,
            'link' => $link,
            'icono' => $icono
        ]);
    }

    /**
     * Obtener notificaciones no leídas de un usuario
     */
    public function obtenerNoLeidas($userId, $limite = 10) {
        return $this->query()
            ->where('user_id', $userId)
            ->where('leida', false)
            ->orderBy('created_at', 'DESC')
            ->limit($limite)
            ->get();
    }

    /**
     * Contar notificaciones no leídas
     */
    public function contarNoLeidas($userId) {
        return $this->query()
            ->where('user_id', $userId)
            ->where('leida', false)
            ->count();
    }

    /**
     * Obtener todas las notificaciones (paginadas)
     */
    public function obtenerTodas($userId, $limite = 50, $offset = 0) {
        return $this->query()
            ->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->limit($limite)
            ->offset($offset)
            ->get();
    }

    /**
     * Marcar notificación como leída
     */
    public function marcarLeida($userId, $id) {
        return $this->query()
            ->where('id', $id)
            ->where('user_id', $userId)
            ->update(['leida' => true]) > 0;
    }

    /**
     * Marcar todas como leídas
     */
    public function marcarTodasLeidas($userId) {
        return $this->query()
            ->where('user_id', $userId)
            ->where('leida', false)
            ->update(['leida' => true]) > 0;
    }

    /**
     * Eliminar notificaciones antiguas (más de 30 días)
     */
    public function limpiarAntiguas($userId) {
        $threshold = date('Y-m-d H:i:s', strtotime('-30 days'));
        return $this->query()
            ->where('user_id', $userId)
            ->where('created_at', '<', $threshold)
            ->delete() > 0;
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
