<?php
namespace App\Models;

use App\Core\BaseModel;
use App\Core\Database;
use App\Core\QueryBuilder;
use \PDO;

class Producto extends BaseModel {
    protected $table = 'productos';
    protected $fillable = [
        'user_id', 'codigo', 'nombre', 'categoria', 'stock', 
        'precioCompraUSD', 'precioVentaUSD', 'gananciaUnitariaUSD', 
        'proveedor_id', 'codigo_barras', 'tiene_iva', 'iva_porcentaje',
        'created_at', 'updated_at'
    ];
    protected $timestamps = true;

    public function __construct() {
        parent::__construct();
    }

    public function validate($data) {
        $errors = [];
        if (empty($data['nombre'])) {
            $errors[] = 'El nombre es obligatorio.';
        }
        if (isset($data['stock']) && (int)$data['stock'] < 0) {
            $errors[] = 'El stock no puede ser negativo.';
        }
        if (isset($data['precio_base']) && (float)$data['precio_base'] < 0) {
            $errors[] = 'El precio base no puede ser negativo.';
        }
        return $errors;
    }

    public function obtenerTodos($userId, $busqueda = '', $limit = 10, $offset = 0) {
        $busqueda = trim($busqueda);
        $query = $this->query()
            ->from($this->table . ' p')
            ->where('p.user_id', $userId)
            ->select(['p.*', 'prov.nombre as nombre_proveedor'])
            ->selectRaw('CASE WHEN p.precioCompraUSD > 0 THEN ROUND(((p.precioVentaUSD - p.precioCompraUSD) / p.precioCompraUSD) * 100, 0) ELSE 0 END as margen_ganancia')
            ->selectRaw('CASE WHEN p.tiene_iva = 1 AND p.iva_porcentaje > 0 THEN ROUND(p.precioCompraUSD / (1 + (p.iva_porcentaje / 100)), 2) ELSE p.precioCompraUSD END as precio_base')
            ->leftJoin('proveedores prov', 'p.proveedor_id', '=', 'prov.id');

        if (!empty($busqueda)) {
            $dbDriver = Database::conectar()->getAttribute(\PDO::ATTR_DRIVER_NAME);
            
            // Si es MySQL y la búsqueda tiene más de 3 caracteres, usamos FULLTEXT
            if ($dbDriver === 'mysql' && strlen($busqueda) >= 3) {
                $query->whereRaw('MATCH(p.nombre, p.categoria) AGAINST(? IN BOOLEAN MODE)', [$busqueda . '*']);
            } else {
                $busquedaParam = '%' . $busqueda . '%';
                $query->whereRaw('(p.nombre LIKE ? OR p.codigo_barras LIKE ? OR p.id LIKE ?)', [$busquedaParam, $busquedaParam, $busquedaParam]);
            }
        }

        return $query->orderBy('p.nombre', 'ASC')
            ->limit($limit)
            ->offset($offset)
            ->get();
    }

    public function contarTodos($userId, $busqueda = '') {
        $busqueda = trim($busqueda);
        $query = $this->scopeUser($userId);
        
        if (!empty($busqueda)) {
            $dbDriver = Database::conectar()->getAttribute(\PDO::ATTR_DRIVER_NAME);
            if ($dbDriver === 'mysql' && strlen($busqueda) >= 3) {
                $query->whereRaw('MATCH(nombre, categoria) AGAINST(? IN BOOLEAN MODE)', [$busqueda . '*']);
            } else {
                $busquedaParam = '%' . $busqueda . '%';
                $query->whereRaw('(nombre LIKE ? OR codigo_barras LIKE ? OR id LIKE ?)', [$busquedaParam, $busquedaParam, $busquedaParam]);
            }
        }

        return $query->count();
    }
    
    public function obtenerPorId($userId, $id) {
        return QueryBuilder::table('productos p')
            ->select(['p.*'])
            ->selectRaw('CASE WHEN p.precioCompraUSD > 0 THEN ROUND(((p.precioVentaUSD - p.precioCompraUSD) / p.precioCompraUSD) * 100, 0) ELSE 0 END as margen_ganancia')
            ->selectRaw('CASE WHEN p.tiene_iva = 1 AND p.iva_porcentaje > 0 THEN ROUND(p.precioCompraUSD / (1 + (p.iva_porcentaje / 100)), 2) ELSE p.precioCompraUSD END as precio_base')
            ->where('p.id', $id)
            ->where('p.user_id', $userId)
            ->first();
    }

    public function obtenerPorCodigo($userId, $codigo) {
        return QueryBuilder::table('productos')
            ->where('codigo_barras', $codigo)
            ->where('user_id', $userId)
            ->first();
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
    public function crear($data) {
        return $this->create($data);
    }
    
    /**
     * Actualiza un producto completo
     */
    public function actualizarCompleto($id, $data) {
        return parent::update($id, $data) > 0;
    }
    
    /**
     * Elimina un producto
     */
    public function eliminar($userId, $id) {
        try {
            return QueryBuilder::table('productos')
                ->where('id', $id)
                ->where('user_id', $userId)
                ->delete() > 0;
        } catch (\PDOException $e) {
            error_log("Error deleting product: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Actualiza solo el stock de un producto
     */
    public function actualizarStock($userId, $id, $cantidad, $tipo = 'Entrada') {
        try {
            $cantidad = abs($cantidad);
            $operador = ($tipo === 'Entrada' || $tipo === 'add') ? '+' : '-';
            
            return QueryBuilder::table('productos')
                ->where('id', $id)
                ->where('user_id', $userId)
                ->updateRaw("stock = GREATEST(0, stock $operador ?)", [$cantidad]);
        } catch (\PDOException $e) {
            error_log("Error updating stock: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerKPIsDashboard($userId, $umbralStockBajo = 10) {
        $result = QueryBuilder::table('productos')
            ->selectRaw('SUM(stock * precioVentaUSD) as valorTotalVentaUSD')
            ->selectRaw('SUM(stock * precioCompraUSD) as valorTotalCostoUSD')
            ->selectRaw('SUM(CASE WHEN stock > 0 AND stock <= ? THEN 1 ELSE 0 END) as stockBajo', [$umbralStockBajo])
            ->where('user_id', $userId)
            ->first();

        return [
            'valorTotalVentaUSD' => (float)($result['valorTotalVentaUSD'] ?? 0),
            'valorTotalCostoUSD' => (float)($result['valorTotalCostoUSD'] ?? 0),
            'stockBajo' => (int)($result['stockBajo'] ?? 0)
        ];
    }

    public function obtenerDatosParaGraficos($userId) {
        return QueryBuilder::table('productos')
            ->select(['categoria'])
            ->selectRaw('SUM(stock) as totalStock')
            ->selectRaw('SUM(stock * precioCompraUSD) as totalCosto')
            ->selectRaw('SUM(stock * gananciaUnitariaUSD) as totalGanancia')
            ->where('user_id', $userId)
            ->groupBy('categoria')
            ->orderBy('categoria', 'ASC')
            ->get();
    }
    
    public function obtenerAlertasStock($userId, $umbral) {
        $bajo = QueryBuilder::table('productos')
            ->select(['nombre', 'stock'])
            ->where('user_id', $userId)
            ->where('stock', '<=', $umbral)
            ->where('stock', '>', 0)
            ->get();
            
        $agotado = QueryBuilder::table('productos')
            ->select(['nombre', 'stock'])
            ->where('user_id', $userId)
            ->where('stock', '=', 0)
            ->get();
            
        return ['bajo' => $bajo, 'agotado' => $agotado];
    }

    public function buscarParaCompra($userId, $termino) {
        $busquedaParam = '%' . $termino . '%';
        return QueryBuilder::table('productos')
            ->select(['id', 'nombre', 'precioCompraUSD', 'stock', 'codigo_barras'])
            ->where('user_id', $userId)
            ->whereRaw('(nombre LIKE ? OR codigo_barras = ? OR id = ?)', [$busquedaParam, $termino, $termino])
            ->limit(10)
            ->get();
    }

    public function buscarParaPOS($userId, $termino) {
        $termino = trim($termino);
        $query = QueryBuilder::table('productos')
            ->select(['id', 'nombre', 'precioVentaUSD', 'stock', 'codigo_barras'])
            ->where('user_id', $userId)
            ->where('stock', '>', 0);

        if (!empty($termino)) {
            $dbDriver = Database::conectar()->getAttribute(\PDO::ATTR_DRIVER_NAME);
            if ($dbDriver === 'mysql' && strlen($termino) >= 3) {
                $query->whereRaw('MATCH(nombre, categoria) AGAINST(? IN BOOLEAN MODE)', [$termino . '*']);
            } else {
                $busquedaParam = '%' . $termino . '%';
                $query->whereRaw('(nombre LIKE ? OR codigo_barras = ? OR id = ?)', [$busquedaParam, $termino, $termino]);
            }
        }

        return $query->limit(10)->get();
    }
}