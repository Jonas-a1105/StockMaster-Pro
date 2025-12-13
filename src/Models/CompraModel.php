<?php
namespace App\Models;

use App\Core\Database;
use \PDO;

class CompraModel {
    private $db;

    public function __construct() {
        $this->db = Database::conectar();
    }

    // Registrar una nueva compra
    public function crearCompra($userId, $proveedorId, $nroFactura, $total, $estado, $fechaEmision, $fechaVencimiento) {
        $query = "INSERT INTO compras (user_id, proveedor_id, nro_factura, total_usd, estado, fecha_emision, fecha_vencimiento) 
                  VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$userId, $proveedorId, $nroFactura, $total, $estado, $fechaEmision, $fechaVencimiento]);
        return $this->db->lastInsertId();
    }

    public function crearCompraItem($compraId, $productoId, $cantidad, $precioUnitario) {
        $query = "INSERT INTO compra_items (compra_id, producto_id, cantidad, precio_unitario) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$compraId, $productoId, $cantidad, $precioUnitario]);
    }

    // Actualizar el precio de costo de este proveedor específico
    public function actualizarPrecioProveedor($productoId, $proveedorId, $precioCosto) {
        // "INSERT ... ON DUPLICATE KEY UPDATE" (Upsert)
        $query = "INSERT INTO producto_proveedores (producto_id, proveedor_id, ultimo_precio_costo) 
                  VALUES (?, ?, ?) 
                  ON DUPLICATE KEY UPDATE ultimo_precio_costo = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$productoId, $proveedorId, $precioCosto, $precioCosto]);
    }

    // Obtener reporte de compras (Filtrable por estado 'Pendiente')
    public function obtenerTodas($userId, $estado = '', $limit = 0, $offset = 0) {
        $sql = "SELECT c.*, p.nombre as proveedor_nombre 
                FROM compras c 
                JOIN proveedores p ON c.proveedor_id = p.id 
                WHERE c.user_id = ?";
        
        $params = [$userId];
        if (!empty($estado)) {
            $sql .= " AND c.estado = ?";
            $params[] = $estado;
        }
        
        $sql .= " ORDER BY c.id DESC";
        
        if ($limit > 0) {
            $sql .= " LIMIT " . (int)$limit;
            if ($offset > 0) {
                $sql .= " OFFSET " . (int)$offset;
            }
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // Contar total de compras para paginación
    public function contarTodas($userId, $estado = '') {
        $sql = "SELECT COUNT(*) FROM compras WHERE user_id = ?";
        $params = [$userId];
        
        if (!empty($estado)) {
            $sql .= " AND estado = ?";
            $params[] = $estado;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    // Obtener una compra específica
    public function obtenerPorId($userId, $compraId) {
        $sql = "SELECT * FROM compras WHERE id = ? AND user_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$compraId, $userId]);
        return $stmt->fetch();
    }

    // Obtener los productos dentro de una compra
    public function obtenerItems($compraId) {
        $sql = "SELECT ci.*, p.nombre as nombre_producto 
                FROM compra_items ci
                LEFT JOIN productos p ON ci.producto_id = p.id
                WHERE ci.compra_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$compraId]);
        return $stmt->fetchAll();
    }

    public function pagarCompra($compraId) {
        $stmt = $this->db->prepare("UPDATE compras SET estado = 'Pagada' WHERE id = ?");
        return $stmt->execute([$compraId]);
    }
}