<?php
namespace App\Models;

use App\Core\BaseModel;
use App\Core\Database;
use \PDO;

/**
 * Modelo de Auditoría - Registra todas las acciones del sistema
 */
class AuditModel extends BaseModel {
    protected $table = 'audit_logs';
    protected $fillable = [
        'user_id', 'action', 'entity_type', 'entity_id', 
        'entity_name', 'old_values', 'new_values', 
        'ip_address', 'user_agent', 'created_at'
    ];
    protected $timestamps = true;

    public function __construct() {
        parent::__construct();
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
        return $this->create([
            'user_id' => $userId,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'entity_name' => $entityName,
            'old_values' => $oldValues ? json_encode($oldValues, JSON_UNESCAPED_UNICODE) : null,
            'new_values' => $newValues ? json_encode($newValues, JSON_UNESCAPED_UNICODE) : null,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255)
        ]);
    }

    /**
     * Obtener historial de auditoría con filtros
     */
    public function obtenerHistorial($userId = null, $filtros = []) {
        $query = $this->scopeUser($userId)
            ->table($this->table)
            ->select(['audit_logs.*'])
            ->selectRaw('usuarios.email as user_email')
            ->leftJoin('usuarios', 'audit_logs.user_id', '=', 'usuarios.id');
        
        if (!empty($filtros['entity_type'])) {
            $query->where('audit_logs.entity_type', $filtros['entity_type']);
        }
        
        if (!empty($filtros['action'])) {
            $query->where('audit_logs.action', $filtros['action']);
        }
        
        if (!empty($filtros['fecha_inicio'])) {
            $query->where('audit_logs.created_at', '>=', $filtros['fecha_inicio']);
        }
        
        if (!empty($filtros['fecha_fin'])) {
            $query->where('audit_logs.created_at', '<=', $filtros['fecha_fin'] . ' 23:59:59');
        }
        
        if (!empty($filtros['busqueda'])) {
            $query->where('audit_logs.entity_name', 'LIKE', '%' . $filtros['busqueda'] . '%');
        }
        
        return $query->orderBy('audit_logs.created_at', 'DESC')
            ->limit((int)($filtros['limit'] ?? 100))
            ->get();
    }

    /**
     * Obtener historial de una entidad específica
     */
    public function obtenerHistorialEntidad($entityType, $entityId) {
        return $this->query()
            ->select(['audit_logs.*'])
            ->selectRaw('usuarios.email as user_email')
            ->leftJoin('usuarios', 'audit_logs.user_id', '=', 'usuarios.id')
            ->where('audit_logs.entity_type', $entityType)
            ->where('audit_logs.entity_id', $entityId)
            ->orderBy('audit_logs.created_at', 'DESC')
            ->limit(50)
            ->get();
    }

    /**
     * Obtener resumen de actividad por usuario
     */
    public function obtenerResumenActividad($userId, $dias = 7) {
        $startDate = date('Y-m-d H:i:s', strtotime("-$dias days"));
        return $this->query()
            ->select(['action', 'entity_type'])
            ->selectRaw('COUNT(*) as cantidad')
            ->where('user_id', $userId)
            ->where('created_at', '>=', $startDate)
            ->groupBy(['action', 'entity_type'])
            ->orderBy('cantidad', 'DESC')
            ->get();
    }
}
