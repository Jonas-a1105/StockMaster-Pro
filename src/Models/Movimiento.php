<?php
namespace App\Models;

use App\Core\Database;
use \PDO;

class Movimiento {
    private $db;

    public function __construct() {
        $this->db = Database::conectar();
    }

    /**
     * Crea un nuevo registro de movimiento
     */
    public function crear($userId, $productoId, $productoNombre, $tipo, $motivo, $cantidad, $nota, $proveedor) {
        $query = "INSERT INTO movimientos (user_id, producto_id, productoNombre, tipo, motivo, cantidad, nota, proveedor) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            $userId, 
            $productoId, 
            $productoNombre, 
            $tipo, 
            $motivo, 
            $cantidad, 
            $nota, 
            $proveedor
        ]);
    }

    /**
     * Obtiene todos los movimientos de un usuario, con filtros
     */
    public function obtenerTodos($userId, $filtros = []) {
        // Compatibilidad hacia atrás: si filtros es un número, es el límite
        if (is_numeric($filtros)) {
            $filtros = ['limit' => $filtros];
        }

        $sql = "SELECT m.*, p.nombre as productoNombreActual, 
                       COALESCE(pr.nombre, m.proveedor) as proveedor
                FROM movimientos m
                LEFT JOIN productos p ON m.producto_id = p.id AND m.user_id = p.user_id
                LEFT JOIN proveedores pr ON p.proveedor_id = pr.id AND p.user_id = pr.user_id
                WHERE m.user_id = ?";
        
        $params = [$userId];

        if (!empty($filtros['producto'])) {
            $sql .= " AND (m.productoNombre LIKE ? OR p.nombre LIKE ?)";
            $params[] = '%' . $filtros['producto'] . '%';
            $params[] = '%' . $filtros['producto'] . '%';
        }

        if (!empty($filtros['producto_id'])) {
            $sql .= " AND m.producto_id = ?";
            $params[] = $filtros['producto_id'];
        }

        if (!empty($filtros['tipo'])) {
            $sql .= " AND m.tipo = ?";
            $params[] = $filtros['tipo'];
        }
        
        if (!empty($filtros['fecha_inicio'])) {
            $sql .= " AND m.fecha >= ?";
            $params[] = $filtros['fecha_inicio'];
        }
        if (!empty($filtros['fecha_fin'])) {
            $sql .= " AND m.fecha <= ?";
            $params[] = $filtros['fecha_fin'] . ' 23:59:59';
        }

        $sql .= " ORDER BY m.fecha DESC";

        if (!empty($filtros['limit'])) {
            $sql .= " LIMIT " . (int)$filtros['limit'];
            if (!empty($filtros['offset'])) {
                $sql .= " OFFSET " . (int)$filtros['offset'];
            }
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Cuenta el total de movimientos para paginación
     */
    public function contarTodos($userId, $filtros = []) {
        $sql = "SELECT COUNT(*) FROM movimientos m WHERE m.user_id = ?";
        $params = [$userId];

        if (!empty($filtros['producto'])) {
            $sql .= " AND m.productoNombre LIKE ?";
            $params[] = '%' . $filtros['producto'] . '%';
        }
        if (!empty($filtros['producto_id'])) {
            $sql .= " AND m.producto_id = ?";
            $params[] = $filtros['producto_id'];
        }
        if (!empty($filtros['tipo'])) {
            $sql .= " AND m.tipo = ?";
            $params[] = $filtros['tipo'];
        }
        if (!empty($filtros['fecha_inicio'])) {
            $sql .= " AND m.fecha >= ?";
            $params[] = $filtros['fecha_inicio'];
        }
        if (!empty($filtros['fecha_fin'])) {
            $sql .= " AND m.fecha <= ?";
            $params[] = $filtros['fecha_fin'] . ' 23:59:59';
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }
}