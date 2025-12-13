<?php
namespace App\Models;

use App\Core\Database;
use \PDO;

class VentaModel {
    
    private $db;

    public function __construct() {
        $this->db = Database::conectar();
    }

    public function crearVenta($userId, $totalUSD, $tasa, $totalVES, $clienteId = null, $estadoPago = 'Pagada', $metodoPago = 'Efectivo', $notas = null) {
        $query = "INSERT INTO ventas (user_id, cliente_id, total_usd, tasa_ves, total_ves, estado_pago, metodo_pago, notas) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$userId, $clienteId, $totalUSD, $tasa, $totalVES, $estadoPago, $metodoPago, $notas]);
        return $this->db->lastInsertId();
    }

    public function crearVentaItem($ventaId, $productoId, $nombre, $cantidad, $precioUSD) {
        $query = "INSERT INTO venta_items (venta_id, producto_id, nombre_producto, cantidad, precio_unitario_usd) 
                  VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$ventaId, $productoId, $nombre, $cantidad, $precioUSD]);
    }
    
    public function obtenerVentaPorId($userId, $ventaId) {
        $query = "SELECT * FROM ventas WHERE id = ? AND user_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$ventaId, $userId]);
        return $stmt->fetch();
    }
    
    public function obtenerItemsPorVentaId($ventaId) {
        $query = "SELECT * FROM venta_items WHERE venta_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$ventaId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Obtener ventas con filtros para reportes e historial
     */
    public function obtenerVentas($userId, $filtros = []) {
        $query = "SELECT v.*, c.nombre as cliente_nombre, c.numero_documento 
                  FROM ventas v 
                  LEFT JOIN clientes c ON v.cliente_id = c.id 
                  WHERE v.user_id = ?";
        
        $params = [$userId];
        
        if (!empty($filtros['fecha_inicio'])) {
            $query .= " AND v.created_at >= ?";
            $params[] = $filtros['fecha_inicio'];
        }
        
        if (!empty($filtros['fecha_fin'])) {
            $query .= " AND v.created_at <= ?";
            $params[] = $filtros['fecha_fin'] . ' 23:59:59';
        }
        
        if (!empty($filtros['cliente_id'])) {
            $query .= " AND v.cliente_id = ?";
            $params[] = $filtros['cliente_id'];
        }
        
        if (!empty($filtros['estado_pago'])) {
            $query .= " AND v.estado_pago = ?";
            $params[] = $filtros['estado_pago'];
        }
        
        if (!empty($filtros['metodo_pago'])) {
            $query .= " AND v.metodo_pago = ?";
            $params[] = $filtros['metodo_pago'];
        }
        
        $query .= " ORDER BY v.created_at DESC";
        
        if (!empty($filtros['limit'])) {
            $query .= " LIMIT " . (int)$filtros['limit'];
            if (!empty($filtros['offset'])) {
                $query .= " OFFSET " . (int)$filtros['offset'];
            }
        }
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Contar ventas para paginación
     */
    public function contarVentas($userId, $filtros = []) {
        $query = "SELECT COUNT(*) FROM ventas v WHERE v.user_id = ?";
        $params = [$userId];
        
        if (!empty($filtros['fecha_inicio'])) {
            $query .= " AND v.created_at >= ?";
            $params[] = $filtros['fecha_inicio'];
        }
        if (!empty($filtros['fecha_fin'])) {
            $query .= " AND v.created_at <= ?";
            $params[] = $filtros['fecha_fin'] . ' 23:59:59';
        }
        if (!empty($filtros['cliente_id'])) {
            $query .= " AND v.cliente_id = ?";
            $params[] = $filtros['cliente_id'];
        }
        if (!empty($filtros['estado_pago'])) {
            $query .= " AND v.estado_pago = ?";
            $params[] = $filtros['estado_pago'];
        }
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }
    
    /**
     * Obtener ventas de un cliente específico
     */
    public function obtenerVentasPorCliente($clienteId, $limite = 20) {
        $query = "SELECT * FROM ventas WHERE cliente_id = ? ORDER BY created_at DESC LIMIT ?";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(1, $clienteId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Marcar una venta pendiente como pagada
     */
    public function marcarPagada($userId, $ventaId) {
        // DEBUG: Log para diagnosticar
        error_log("marcarPagada() - userId: $userId, ventaId: $ventaId");
        
        // Primero verificar que la venta existe y su estado actual
        $checkQuery = "SELECT id, user_id, estado_pago FROM ventas WHERE id = ?";
        $checkStmt = $this->db->prepare($checkQuery);
        $checkStmt->execute([$ventaId]);
        $venta = $checkStmt->fetch();
        
        if ($venta) {
            error_log("marcarPagada() - Venta encontrada: user_id={$venta['user_id']}, estado={$venta['estado_pago']}");
        } else {
            error_log("marcarPagada() - Venta NO encontrada con id=$ventaId");
            return false;
        }
        
        // UPDATE sin verificar user_id (la venta ya fue validada por acceso)
        $query = "UPDATE ventas SET estado_pago = 'Pagada' WHERE id = ? AND estado_pago = 'Pendiente'";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$ventaId]);
        $affected = $stmt->rowCount();
        
        error_log("marcarPagada() - Filas afectadas: $affected");
        
        return $affected > 0;
    }
    
    /**
     * Obtener deuda total de un cliente
     */
    public function obtenerDeudaPorCliente($clienteId) {
        $query = "SELECT COALESCE(SUM(total_usd), 0) as deuda_total 
                  FROM ventas 
                  WHERE cliente_id = ? AND estado_pago = 'Pendiente'";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$clienteId]);
        $resultado = $stmt->fetch();
        return (float)$resultado['deuda_total'];
    }
    
    /**
     * Obtener estadísticas de ventas para reportes
     */
    public function obtenerEstadisticasVentas($userId, $fechaInicio = null, $fechaFin = null) {
        $query = "SELECT 
                  COUNT(*) as total_ventas,
                  COALESCE(SUM(total_usd), 0) as total_vendido_usd,
                  COALESCE(SUM(total_ves), 0) as total_vendido_ves,
                  COALESCE(AVG(total_usd), 0) as promedio_venta_usd,
                  COALESCE(SUM(CASE WHEN estado_pago = 'Pagada' THEN total_usd ELSE 0 END), 0) as total_cobrado,
                  COALESCE(SUM(CASE WHEN estado_pago = 'Pendiente' THEN total_usd ELSE 0 END), 0) as total_por_cobrar
                  FROM ventas 
                  WHERE user_id = ?";
        
        $params = [$userId];
        
        if ($fechaInicio) {
            $query .= " AND created_at >= ?";
            $params[] = $fechaInicio;
        }
        
        if ($fechaFin) {
            $query .= " AND created_at <= ?";
            $params[] = $fechaFin . ' 23:59:59';
        }
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetch();
    }
    
    /**
     * Obtener productos más vendidos
     */
    public function obtenerProductosMasVendidos($userId, $fechaInicio = null, $fechaFin = null, $limite = 10) {
        $query = "SELECT 
                  vi.nombre_producto,
                  SUM(vi.cantidad) as cantidad_vendida,
                  COALESCE(SUM(vi.cantidad * vi.precio_unitario_usd), 0) as total_vendido_usd,
                  COUNT(DISTINCT vi.venta_id) as num_ventas
                  FROM venta_items vi
                  INNER JOIN ventas v ON vi.venta_id = v.id
                  WHERE v.user_id = ?";
        
        $params = [$userId];
        
        if ($fechaInicio) {
            $query .= " AND v.created_at >= ?";
            $params[] = $fechaInicio;
        }
        
        if ($fechaFin) {
            $query .= " AND v.created_at <= ?";
            $params[] = $fechaFin . ' 23:59:59';
        }
        
        $query .= " GROUP BY vi.nombre_producto 
                    ORDER BY cantidad_vendida DESC 
                    LIMIT ?";
        
        $stmt = $this->db->prepare($query);
        foreach ($params as $i => $param) {
            $stmt->bindValue($i + 1, $param, is_int($param) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->bindValue(count($params) + 1, $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Obtener reporte de ganancias por producto vendido
     */
    public function obtenerReporteGanancias($userId, $fechaInicio = null, $fechaFin = null) {
        $query = "SELECT 
                  vi.nombre_producto,
                  vi.producto_id,
                  SUM(vi.cantidad) as cantidad_vendida,
                  COALESCE(p.precioCompraUSD, 0) as costo_unitario,
                  AVG(vi.precio_unitario_usd) as precio_venta_promedio,
                  COALESCE(SUM(vi.cantidad * p.precioCompraUSD), 0) as costo_total,
                  COALESCE(SUM(vi.cantidad * vi.precio_unitario_usd), 0) as venta_total,
                  COALESCE(SUM(vi.cantidad * vi.precio_unitario_usd) - SUM(vi.cantidad * p.precioCompraUSD), 0) as ganancia_total
                  FROM venta_items vi
                  INNER JOIN ventas v ON vi.venta_id = v.id
                  LEFT JOIN productos p ON vi.producto_id = p.id
                  WHERE v.user_id = ?";
        
        $params = [$userId];
        
        if ($fechaInicio) {
            $query .= " AND v.created_at >= ?";
            $params[] = $fechaInicio;
        }
        
        if ($fechaFin) {
            $query .= " AND v.created_at <= ?";
            $params[] = $fechaFin . ' 23:59:59';
        }
        
        $query .= " GROUP BY vi.nombre_producto, vi.producto_id, p.precioCompraUSD
                    ORDER BY ganancia_total DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Obtener todas las cuentas por cobrar (ventas pendientes)
     */
    public function obtenerCuentasPorCobrar($userId, $clienteId = null) {
        $query = "SELECT v.*, 
                  c.nombre as cliente_nombre,
                  c.numero_documento,
                  c.telefono as cliente_telefono,
                  c.telefono as cliente_telefono
                  FROM ventas v
                  INNER JOIN clientes c ON v.cliente_id = c.id
                  WHERE v.user_id = ? AND v.estado_pago = 'Pendiente'";
        
        $params = [$userId];
        
        if ($clienteId) {
            $query .= " AND v.cliente_id = ?";
            $params[] = $clienteId;
        }
        
        $query .= " ORDER BY v.created_at ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $ventas = $stmt->fetchAll();
        
        // Calcular días transcurridos en PHP (MySQL DATEDIFF no existe en SQLite)
        foreach ($ventas as &$v) {
            $fechaVenta = new \DateTime($v['created_at']);
            $hoy = new \DateTime();
            $diff = $hoy->diff($fechaVenta);
            $v['dias_transcurridos'] = $diff->days;
        }
        
        return $ventas;
    }
    
    /**
     * Obtener ventas agrupadas por día para gráficos
     */
    public function obtenerVentasPorDia($userId, $dias = 7) {
        $fechaInicio = date('Y-m-d H:i:s', strtotime("-$dias days"));
        
        $query = "SELECT 
                  DATE(created_at) as fecha,
                  COUNT(*) as num_ventas,
                  COALESCE(SUM(total_usd), 0) as total_usd,
                  COALESCE(SUM(total_ves), 0) as total_ves
                  FROM ventas 
                  WHERE user_id = ? AND created_at >= ?
                  GROUP BY DATE(created_at)
                  ORDER BY fecha ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$userId, $fechaInicio]);
        return $stmt->fetchAll();
    }
    
    public function debugEstadoVenta($id) {
        $stmt = $this->db->prepare("SELECT id, estado_pago, user_id, cliente_id FROM ventas WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);
        echo "<pre style='text-align:left; color:black; background:white; padding:10px;'>DATA BD: ";
        print_r($data);
        echo "</pre>";
    }
}