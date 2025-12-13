<?php
namespace App\Models;

use App\Core\Database;
use \PDO;

/**
 * Modelo de Auditoría - Registra todas las acciones del sistema
 */
class AuditModel {
    
    private $db;

    public function __construct() {
        $this->db = Database::conectar();
    }

    /**
     * Registrar una acción en el log de auditoría
     * 
     * @param int $userId ID del usuario que realiza la acción
     * @param string $action Tipo de acción: 'crear', 'actualizar', 'eliminar'
     * @param string $entityType Entidad afectada: 'producto', 'cliente', 'venta', etc.
     * @param int|null $entityId ID de la entidad afectada
     * @param string|null $entityName Nombre de la entidad (para referencia rápida)
     * @param array|null $oldValues Valores anteriores (para updates)
     * @param array|null $newValues Valores nuevos
     */
    public function registrar($userId, $action, $entityType, $entityId = null, $entityName = null, $oldValues = null, $newValues = null) {
        $query = "INSERT INTO audit_logs (user_id, action, entity_type, entity_id, entity_name, old_values, new_values, ip_address, user_agent) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            $userId,
            $action,
            $entityType,
            $entityId,
            $entityName,
            $oldValues ? json_encode($oldValues, JSON_UNESCAPED_UNICODE) : null,
            $newValues ? json_encode($newValues, JSON_UNESCAPED_UNICODE) : null,
            $_SERVER['REMOTE_ADDR'] ?? null,
            substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255)
        ]);
    }

    /**
     * Obtener historial de auditoría con filtros
     */
    public function obtenerHistorial($userId = null, $filtros = []) {
        $query = "SELECT a.*, u.email as user_email 
                  FROM audit_logs a
                  LEFT JOIN usuarios u ON a.user_id = u.id
                  WHERE 1=1";
        
        $params = [];
        
        // Solo para un usuario específico (no admin ve solo lo suyo)
        if ($userId) {
            $query .= " AND a.user_id = ?";
            $params[] = $userId;
        }
        
        // Filtro por entidad
        if (!empty($filtros['entity_type'])) {
            $query .= " AND a.entity_type = ?";
            $params[] = $filtros['entity_type'];
        }
        
        // Filtro por acción
        if (!empty($filtros['action'])) {
            $query .= " AND a.action = ?";
            $params[] = $filtros['action'];
        }
        
        // Filtro por fecha
        if (!empty($filtros['fecha_inicio'])) {
            $query .= " AND a.created_at >= ?";
            $params[] = $filtros['fecha_inicio'];
        }
        
        if (!empty($filtros['fecha_fin'])) {
            $query .= " AND a.created_at <= ?";
            $params[] = $filtros['fecha_fin'] . ' 23:59:59';
        }
        
        // Búsqueda por nombre de entidad
        if (!empty($filtros['busqueda'])) {
            $query .= " AND a.entity_name LIKE ?";
            $params[] = '%' . $filtros['busqueda'] . '%';
        }
        
        $query .= " ORDER BY a.created_at DESC";
        
        // Limitar resultados
        $limit = (int)($filtros['limit'] ?? 100);
        $query .= " LIMIT $limit";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Obtener historial de una entidad específica
     */
    public function obtenerHistorialEntidad($entityType, $entityId) {
        $query = "SELECT a.*, u.email as user_email 
                  FROM audit_logs a
                  LEFT JOIN usuarios u ON a.user_id = u.id
                  WHERE a.entity_type = ? AND a.entity_id = ?
                  ORDER BY a.created_at DESC
                  LIMIT 50";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$entityType, $entityId]);
        return $stmt->fetchAll();
    }

    /**
     * Obtener resumen de actividad por usuario
     */
    public function obtenerResumenActividad($userId, $dias = 7) {
        $startDate = date('Y-m-d H:i:s', strtotime("-$dias days"));
        $query = "SELECT 
                    action,
                    entity_type,
                    COUNT(*) as cantidad
                  FROM audit_logs
                  WHERE user_id = ? AND created_at >= ?
                  GROUP BY action, entity_type
                  ORDER BY cantidad DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$userId, $startDate]);
        return $stmt->fetchAll();
    }
}
