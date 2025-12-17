<?php
namespace App\Models;

use App\Core\Database;
use \PDO;

class Cliente {
    private $db;

    public function __construct() {
        $this->db = Database::conectar();
    }

    /**
     * Crear un nuevo cliente
     */
    public function crear($userId, $nombre, $tipoDocumento, $numeroDocumento, $telefono, $email, $direccion, $tipoCliente, $limiteCredito) {
        $query = "INSERT INTO clientes (user_id, nombre, tipo_documento, numero_documento, telefono, email, direccion, tipo_cliente, limite_credito) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($query);
        $exito = $stmt->execute([
            $userId, 
            $nombre, 
            $tipoDocumento, 
            $numeroDocumento, 
            $telefono, 
            $email, 
            $direccion, 
            $tipoCliente, 
            $limiteCredito
        ]);
        
        if ($exito) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    /**
     * Obtener todos los clientes de un usuario con filtros
     */
    public function obtenerTodos($userId, $busqueda = '', $soloActivos = true) {
        $query = "SELECT * FROM clientes WHERE user_id = ?";
        $params = [$userId];

        if (!empty($busqueda)) {
            $query .= " AND (nombre LIKE ? OR numero_documento LIKE ? OR email LIKE ?)";
            $params[] = '%' . $busqueda . '%';
            $params[] = '%' . $busqueda . '%';
            $params[] = '%' . $busqueda . '%';
        }

        if ($soloActivos) {
            $query .= " AND activo = 1";
        }

        $query .= " ORDER BY nombre ASC";

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Obtener un cliente por ID
     */
    public function obtenerPorId($userId, $id) {
        $query = "SELECT * FROM clientes WHERE id = ? AND user_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id, $userId]);
        return $stmt->fetch();
    }

    /**
     * Actualizar datos de un cliente
     */
    public function actualizar($userId, $id, $nombre, $tipoDocumento, $numeroDocumento, $telefono, $email, $direccion, $tipoCliente, $limiteCredito) {
        $query = "UPDATE clientes SET 
                  nombre = ?, 
                  tipo_documento = ?, 
                  numero_documento = ?, 
                  telefono = ?, 
                  email = ?, 
                  direccion = ?, 
                  tipo_cliente = ?, 
                  limite_credito = ? 
                  WHERE id = ? AND user_id = ?";
        
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            $nombre, 
            $tipoDocumento, 
            $numeroDocumento, 
            $telefono, 
            $email, 
            $direccion, 
            $tipoCliente, 
            $limiteCredito, 
            $id, 
            $userId
        ]);
    }

    /**
     * Desactivar un cliente (soft delete)
     */
    public function desactivar($userId, $id) {
        $query = "UPDATE clientes SET activo = 0 WHERE id = ? AND user_id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$id, $userId]);
    }

    /**
     * Reactivar un cliente
     */
    public function reactivar($userId, $id) {
        $query = "UPDATE clientes SET activo = 1 WHERE id = ? AND user_id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$id, $userId]);
    }

    /**
     * Buscar clientes para el POS (autocompletar)
     */
    public function buscarParaPOS($userId, $termino) {
        $query = "SELECT id, nombre, numero_documento, tipo_documento, limite_credito 
                  FROM clientes 
                  WHERE user_id = ? 
                  AND (nombre LIKE ? OR numero_documento LIKE ?) 
                  AND activo = 1 
                  LIMIT 10";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$userId, '%' . $termino . '%', '%' . $termino . '%']);
        return $stmt->fetchAll();
    }

    /**
     * Obtener la deuda total de un cliente (ventas pendientes)
     */
    public function obtenerDeuda($clienteId) {
        $query = "SELECT COALESCE(SUM(total_usd), 0) as deuda_total 
                  FROM ventas 
                  WHERE cliente_id = ? AND estado_pago = 'Pendiente'";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$clienteId]);
        $resultado = $stmt->fetch();
        return (float)$resultado['deuda_total'];
    }

    /**
     * Obtener historial de compras de un cliente
     */
    public function obtenerHistorialCompras($clienteId, $limite = 20) {
        // 1. Obtener las ventas principales
        $query = "SELECT v.* FROM ventas v
                  WHERE v.cliente_id = ?
                  ORDER BY v.created_at DESC
                  LIMIT ?";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(1, $clienteId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limite, PDO::PARAM_INT);
        $stmt->execute();
        $ventas = $stmt->fetchAll();

        if (empty($ventas)) {
            return [];
        }

        // 2. Obtener los IDs de las ventas encontradas
        $ventaIds = array_column($ventas, 'id');
        
        // 3. Obtener los items de esas ventas (Soporte Multi-DB: Evitamos GROUP_CONCAT específico)
        // Usamos placeholders dinámicos (?, ?, ?)
        $placeholders = implode(',', array_fill(0, count($ventaIds), '?'));
        
        $queryItems = "SELECT venta_id, cantidad, nombre_producto 
                       FROM venta_items 
                       WHERE venta_id IN ($placeholders)";
        
        $stmtItems = $this->db->prepare($queryItems);
        $stmtItems->execute($ventaIds);
        $todosItems = $stmtItems->fetchAll();

        // 4. Agrupar items por venta en memoria (PHP)
        $itemsPorVenta = [];
        foreach ($todosItems as $item) {
            $itemsPorVenta[$item['venta_id']][] = $item['cantidad'] . 'x ' . $item['nombre_producto'];
        }

        // 5. Asignar la cadena formateada a cada venta
        foreach ($ventas as &$venta) {
            $vid = $venta['id'];
            if (isset($itemsPorVenta[$vid])) {
                $venta['productos'] = implode(', ', $itemsPorVenta[$vid]);
            } else {
                $venta['productos'] = '';
            }
        }
        unset($venta); // Romper referencia

        return $ventas;
    }

    /**
     * Obtener estadísticas de un cliente
     */
    public function obtenerEstadisticas($clienteId) {
        $query = "SELECT 
                  COUNT(*) as total_compras,
                  COALESCE(SUM(total_usd), 0) as total_gastado,
                  COALESCE(SUM(CASE WHEN estado_pago = 'Pendiente' THEN total_usd ELSE 0 END), 0) as deuda_actual,
                  MAX(created_at) as ultima_compra
                  FROM ventas
                  WHERE cliente_id = ?";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$clienteId]);
        return $stmt->fetch();
    }

    /**
     * Verificar si el cliente puede comprar a crédito
     */
    public function puedeComprarCredito($clienteId, $montoNuevo) {
        $cliente = $this->obtenerPorId($_SESSION['user_id'], $clienteId);
        if (!$cliente) return false;

        $deudaActual = $this->obtenerDeuda($clienteId);
        $limiteCredito = (float)$cliente['limite_credito'];

        return ($deudaActual + $montoNuevo) <= $limiteCredito;
    }
}
