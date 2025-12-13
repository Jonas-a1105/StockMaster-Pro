<?php
namespace App\Models;

use App\Core\Database;

/**
 * Modelo de Sucursales / Almacenes
 */
class SucursalModel {
    
    private $db;
    
    public function __construct() {
        $this->db = Database::conectar();
    }
    
    /**
     * Obtener todas las sucursales de un usuario
     */
    public function obtenerTodas($userId, $soloActivas = true) {
        $sql = "SELECT * FROM sucursales WHERE user_id = ?";
        if ($soloActivas) {
            $sql .= " AND activa = 1";
        }
        $sql .= " ORDER BY es_principal DESC, nombre ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Obtener sucursal por ID
     */
    public function obtenerPorId($userId, $id) {
        $stmt = $this->db->prepare("SELECT * FROM sucursales WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $userId]);
        return $stmt->fetch();
    }
    
    /**
     * Obtener sucursal principal
     */
    public function obtenerPrincipal($userId) {
        $stmt = $this->db->prepare("SELECT * FROM sucursales WHERE user_id = ? AND es_principal = 1 LIMIT 1");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }
    
    /**
     * Crear nueva sucursal
     */
    public function crear($userId, $datos) {
        // Si es la primera o se marca como principal, desmarcar las demás
        if (!empty($datos['es_principal'])) {
            $this->db->prepare("UPDATE sucursales SET es_principal = 0 WHERE user_id = ?")->execute([$userId]);
        }
        
        $stmt = $this->db->prepare("
            INSERT INTO sucursales (user_id, nombre, codigo, direccion, telefono, email, es_principal)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $userId,
            $datos['nombre'],
            $datos['codigo'] ?? null,
            $datos['direccion'] ?? null,
            $datos['telefono'] ?? null,
            $datos['email'] ?? null,
            $datos['es_principal'] ?? 0
        ]);
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Actualizar sucursal
     */
    public function actualizar($userId, $id, $datos) {
        if (!empty($datos['es_principal'])) {
            $this->db->prepare("UPDATE sucursales SET es_principal = 0 WHERE user_id = ?")->execute([$userId]);
        }
        
        $stmt = $this->db->prepare("
            UPDATE sucursales SET 
                nombre = ?, codigo = ?, direccion = ?, telefono = ?, email = ?, es_principal = ?, activa = ?
            WHERE id = ? AND user_id = ?
        ");
        
        return $stmt->execute([
            $datos['nombre'],
            $datos['codigo'] ?? null,
            $datos['direccion'] ?? null,
            $datos['telefono'] ?? null,
            $datos['email'] ?? null,
            $datos['es_principal'] ?? 0,
            $datos['activa'] ?? 1,
            $id,
            $userId
        ]);
    }
    
    /**
     * Obtener stock de una sucursal
     */
    public function obtenerStock($sucursalId) {
        $stmt = $this->db->prepare("
            SELECT ss.*, p.nombre, p.categoria, p.precioVentaUSD, p.codigo_barras
            FROM stock_sucursales ss
            INNER JOIN productos p ON ss.producto_id = p.id
            WHERE ss.sucursal_id = ?
            ORDER BY p.nombre
        ");
        $stmt->execute([$sucursalId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Actualizar stock de producto en sucursal
     */
    public function actualizarStock($sucursalId, $productoId, $cantidad, $tipo = 'set') {
        // Verificar si existe el registro
        $stmt = $this->db->prepare("SELECT * FROM stock_sucursales WHERE sucursal_id = ? AND producto_id = ?");
        $stmt->execute([$sucursalId, $productoId]);
        $existe = $stmt->fetch();
        
        if ($existe) {
            if ($tipo === 'set') {
                $sql = "UPDATE stock_sucursales SET stock = ? WHERE sucursal_id = ? AND producto_id = ?";
                $params = [$cantidad, $sucursalId, $productoId];
            } elseif ($tipo === 'add') {
                $sql = "UPDATE stock_sucursales SET stock = stock + ? WHERE sucursal_id = ? AND producto_id = ?";
                $params = [$cantidad, $sucursalId, $productoId];
            } else { // subtract
                $sql = "UPDATE stock_sucursales SET stock = GREATEST(0, stock - ?) WHERE sucursal_id = ? AND producto_id = ?";
                $params = [$cantidad, $sucursalId, $productoId];
            }
        } else {
            $sql = "INSERT INTO stock_sucursales (sucursal_id, producto_id, stock) VALUES (?, ?, ?)";
            $params = [$sucursalId, $productoId, $cantidad];
        }
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
    
    /**
     * Crear transferencia entre sucursales
     */
    public function crearTransferencia($userId, $datos) {
        $stmt = $this->db->prepare("
            INSERT INTO transferencias (user_id, sucursal_origen_id, sucursal_destino_id, producto_id, cantidad, nota)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $userId,
            $datos['origen_id'],
            $datos['destino_id'],
            $datos['producto_id'],
            $datos['cantidad'],
            $datos['nota'] ?? null
        ]);
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Completar transferencia
     */
    public function completarTransferencia($transferId) {
        // Obtener datos de la transferencia
        $stmt = $this->db->prepare("SELECT * FROM transferencias WHERE id = ? AND estado = 'Pendiente'");
        $stmt->execute([$transferId]);
        $transfer = $stmt->fetch();
        
        if (!$transfer) return false;
        
        $this->db->beginTransaction();
        try {
            // Restar stock de origen
            $this->actualizarStock($transfer['sucursal_origen_id'], $transfer['producto_id'], $transfer['cantidad'], 'subtract');
            
            // Sumar stock en destino
            $this->actualizarStock($transfer['sucursal_destino_id'], $transfer['producto_id'], $transfer['cantidad'], 'add');
            
            // Marcar como completada
            $now = date('Y-m-d H:i:s');
            $this->db->prepare("UPDATE transferencias SET estado = 'Completada', completed_at = ? WHERE id = ?")
                ->execute([$now, $transferId]);
            
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
    
    /**
     * Obtener transferencias
     */
    public function obtenerTransferencias($userId, $estado = null, $limit = 50) {
        $sql = "SELECT t.*, 
                       so.nombre as origen_nombre, 
                       sd.nombre as destino_nombre,
                       p.nombre as producto_nombre
                FROM transferencias t
                INNER JOIN sucursales so ON t.sucursal_origen_id = so.id
                INNER JOIN sucursales sd ON t.sucursal_destino_id = sd.id
                INNER JOIN productos p ON t.producto_id = p.id
                WHERE t.user_id = ?";
        
        $params = [$userId];
        
        if ($estado) {
            $sql .= " AND t.estado = ?";
            $params[] = $estado;
        }
        
        $sql .= " ORDER BY t.created_at DESC LIMIT ?";
        $params[] = $limit;
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
