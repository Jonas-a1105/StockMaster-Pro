<?php
namespace App\Models;

use App\Core\Database;
use \PDO;

class Producto {
    private $db;

    public function __construct() {
        $this->db = Database::conectar();
    }

    public function obtenerTodos($userId, $busqueda = '', $limit = 10, $offset = 0) {
        $query = "SELECT p.*, prov.nombre as nombre_proveedor,
                  CASE WHEN p.precioCompraUSD > 0 THEN ROUND(((p.precioVentaUSD - p.precioCompraUSD) / p.precioCompraUSD) * 100, 0) ELSE 0 END as margen_ganancia,
                  CASE WHEN p.tiene_iva = 1 AND p.iva_porcentaje > 0 THEN ROUND(p.precioCompraUSD / (1 + (p.iva_porcentaje / 100)), 2) ELSE p.precioCompraUSD END as precio_base
                  FROM productos p 
                  LEFT JOIN proveedores prov ON p.proveedor_id = prov.id
                  WHERE p.user_id = ? AND (p.nombre LIKE ? OR p.codigo_barras LIKE ? OR p.id LIKE ?) 
                  ORDER BY p.nombre ASC
                  LIMIT ? OFFSET ?";
                  
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(1, $userId, PDO::PARAM_INT);
        $stmt->bindValue(2, '%' . $busqueda . '%', PDO::PARAM_STR);
        $stmt->bindValue(3, '%' . $busqueda . '%', PDO::PARAM_STR);
        $stmt->bindValue(4, '%' . $busqueda . '%', PDO::PARAM_STR);
        $stmt->bindValue(5, (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(6, (int)$offset, PDO::PARAM_INT);
        
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function contarTodos($userId, $busqueda = '') {
        $query = "SELECT COUNT(*) as total 
                  FROM productos 
                  WHERE user_id = ? AND (nombre LIKE ? OR codigo_barras LIKE ? OR id LIKE ?)";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$userId, '%' . $busqueda . '%', '%' . $busqueda . '%', '%' . $busqueda . '%']);
        $resultado = $stmt->fetch();
        return $resultado['total'];
    }
    
    public function obtenerPorId($userId, $id) {
        $stmt = $this->db->prepare("
            SELECT p.*,
            CASE WHEN p.precioCompraUSD > 0 THEN ROUND(((p.precioVentaUSD - p.precioCompraUSD) / p.precioCompraUSD) * 100, 0) ELSE 0 END as margen_ganancia,
            CASE WHEN p.tiene_iva = 1 AND p.iva_porcentaje > 0 THEN ROUND(p.precioCompraUSD / (1 + (p.iva_porcentaje / 100)), 2) ELSE p.precioCompraUSD END as precio_base
            FROM productos p 
            WHERE id = ? AND user_id = ?
        ");
        $stmt->execute([$id, $userId]);
        return $stmt->fetch();
    }

    public function obtenerPorCodigo($userId, $codigo) {
        $stmt = $this->db->prepare("SELECT * FROM productos WHERE codigo_barras = ? AND user_id = ?");
        $stmt->execute([$codigo, $userId]);
        return $stmt->fetch();
    }

    /**
     * Crea un producto
     * @param int $userId
     * @param string $nombre
     * @param string $categoria
     * @param int $stock
     * @param float $precioBase Precio base (costo de compra)
     * @param int $tieneIva 1 si tiene IVA, 0 si no
     * @param float $ivaPorcentaje Porcentaje de IVA
     * @param float $margen Margen de ganancia en porcentaje
     * @param int $proveedorId ID del proveedor (opcional)
     * @param string $codigoBarras Codigo de barras (opcional)
     * @return int|false ID del producto creado o false si falla
     */
    public function crear($userId, $nombre, $categoria, $stock, $precioBase, $tieneIva, $ivaPorcentaje, $margen, $proveedorId = 0, $codigoBarras = null) {
        try {
            // Precio de COMPRA = precio base + IVA (lo que realmente pagaste)
            $precioCompraUSD = (float)$precioBase;
            if ($tieneIva && $ivaPorcentaje > 0) {
                $precioCompraUSD = $precioCompraUSD * (1 + ($ivaPorcentaje / 100));
            }
            
            // Precio de VENTA = precio de compra + margen
            $precioVentaUSD = $precioCompraUSD * (1 + ($margen / 100));
            
            // Ganancia = Venta - Compra (simple)
            $gananciaUnitariaUSD = $precioVentaUSD - $precioCompraUSD;
            
            // Generar código único automático
            $codigo = !empty($codigoBarras) ? $codigoBarras : 'PROD-' . time() . '-' . mt_rand(1000, 9999);
            
            $query = "INSERT INTO productos (user_id, codigo, nombre, categoria, stock, precioCompraUSD, precioVentaUSD, gananciaUnitariaUSD, proveedor_id, codigo_barras, tiene_iva, iva_porcentaje) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                $userId,
                $codigo,
                $nombre,
                $categoria,
                $stock,
                round($precioCompraUSD, 2),
                round($precioVentaUSD, 2),
                round($gananciaUnitariaUSD, 2),
                $proveedorId > 0 ? $proveedorId : null,
                !empty($codigoBarras) ? $codigoBarras : null,
                $tieneIva ? 1 : 0,
                $tieneIva ? round($ivaPorcentaje, 2) : null
            ]);
            
            return $this->db->lastInsertId();
        } catch (\PDOException $e) {
            error_log("Error creating product: " . $e->getMessage());
            throw $e; // Re-lanzar para que el controlador maneje el error
        }
    }
    
    /**
     * Actualiza un producto completo
     */
    public function actualizarCompleto($userId, $id, $nombre, $precioBase, $tieneIva, $ivaPorcentaje, $margen, $proveedorId = 0, $codigoBarras = null, $stock = null) {
        try {
            // Precio de COMPRA = precio base + IVA (lo que realmente pagaste)
            $precioCompraUSD = (float)$precioBase;
            if ($tieneIva && $ivaPorcentaje > 0) {
                $precioCompraUSD = $precioCompraUSD * (1 + ($ivaPorcentaje / 100));
            }
            
            // Precio de VENTA = precio de compra + margen
            $precioVentaUSD = $precioCompraUSD * (1 + ($margen / 100));
            
            // Ganancia = Venta - Compra (simple)
            $gananciaUnitariaUSD = $precioVentaUSD - $precioCompraUSD;
            
            $query = "UPDATE productos SET nombre = ?, precioCompraUSD = ?, precioVentaUSD = ?, gananciaUnitariaUSD = ?, proveedor_id = ?, codigo_barras = ?, tiene_iva = ?, iva_porcentaje = ?, stock = IFNULL(?, stock) WHERE id = ? AND user_id = ?";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                $nombre,
                round($precioCompraUSD, 2),
                round($precioVentaUSD, 2),
                round($gananciaUnitariaUSD, 2),
                $proveedorId > 0 ? $proveedorId : null,
                !empty($codigoBarras) ? $codigoBarras : null,
                $tieneIva ? 1 : 0,
                $tieneIva ? round($ivaPorcentaje, 2) : null,
                $stock,
                $id,
                $userId
            ]);
            
            return $stmt->rowCount() > 0;
        } catch (\PDOException $e) {
            error_log("Error updating product: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Elimina un producto
     */
    public function eliminar($userId, $id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM productos WHERE id = ? AND user_id = ?");
            $stmt->execute([$id, $userId]);
            return $stmt->rowCount() > 0;
        } catch (\PDOException $e) {
            error_log("Error deleting product: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Actualiza solo el stock de un producto
     */
    public function actualizarStock($userId, $id, $nuevoStock) {
        try {
            $stmt = $this->db->prepare("UPDATE productos SET stock = ? WHERE id = ? AND user_id = ?");
            $stmt->execute([$nuevoStock, $id, $userId]);
            return true;
        } catch (\PDOException $e) {
            error_log("Error updating stock: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerKPIsDashboard($userId, $umbralStockBajo = 10) {
        $query = "SELECT SUM(stock * precioVentaUSD) as valorTotalVentaUSD, SUM(stock * precioCompraUSD) as valorTotalCostoUSD, SUM(CASE WHEN stock > 0 AND stock <= ? THEN 1 ELSE 0 END) as stockBajo FROM productos WHERE user_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$umbralStockBajo, $userId]);
        $kpis = $stmt->fetch();
        return ['valorTotalVentaUSD' => (float)($kpis['valorTotalVentaUSD'] ?? 0), 'valorTotalCostoUSD' => (float)($kpis['valorTotalCostoUSD'] ?? 0), 'stockBajo' => (int)($kpis['stockBajo'] ?? 0)];
    }

    public function obtenerDatosParaGraficos($userId) {
        $query = "SELECT categoria, SUM(stock) as totalStock, SUM(stock * precioCompraUSD) as totalCosto, SUM(stock * gananciaUnitariaUSD) as totalGanancia FROM productos WHERE user_id = ? GROUP BY categoria ORDER BY categoria ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
    
    public function obtenerAlertasStock($userId, $umbral) {
        $queryBajo = "SELECT nombre, stock FROM productos WHERE user_id = ? AND stock <= ? AND stock > 0";
        $stmtBajo = $this->db->prepare($queryBajo); 
        $stmtBajo->execute([$userId, $umbral]);
        
        $queryAgotado = "SELECT nombre, stock FROM productos WHERE user_id = ? AND stock = 0";
        $stmtAgotado = $this->db->prepare($queryAgotado); 
        $stmtAgotado->execute([$userId]);
        
        return ['bajo' => $stmtBajo->fetchAll(), 'agotado' => $stmtAgotado->fetchAll()];
    }

    public function buscarParaCompra($userId, $termino) {
        $query = "SELECT id, nombre, precioCompraUSD, stock, codigo_barras 
                  FROM productos 
                  WHERE user_id = ? AND (nombre LIKE ? OR codigo_barras = ? OR id = ?)
                  LIMIT 10";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$userId, '%' . $termino . '%', $termino, $termino]);
        return $stmt->fetchAll();
    }

    public function buscarParaPOS($userId, $termino) {
        $query = "SELECT id, nombre, precioVentaUSD, stock, codigo_barras 
                  FROM productos 
                  WHERE user_id = ? 
                  AND (nombre LIKE ? OR codigo_barras = ? OR id = ?) 
                  AND stock > 0 
                  LIMIT 10";
                  
        $stmt = $this->db->prepare($query);
        $stmt->execute([$userId, '%' . $termino . '%', $termino, $termino]);
        return $stmt->fetchAll();
    }
}