<?php
namespace App\Models;

use App\Core\BaseModel;
use App\Core\Database;
use App\Core\QueryBuilder;
use App\Domain\Enums\PaymentStatus;
use \PDO;

class CompraModel extends BaseModel {
    protected $table = 'compras';
    protected $fillable = [
        'user_id', 'proveedor_id', 'nro_factura', 'total_usd', 
        'estado', 'fecha_emision', 'fecha_vencimiento', 
        'created_at', 'updated_at'
    ];
    protected $timestamps = true;

    public function __construct() {
        parent::__construct();
    }

    // Registrar una nueva compra
    public function crearCompra($userId, $proveedorId, $nroFactura, $total, $estado, $fechaEmision, $fechaVencimiento) {
        return $this->create([
            'user_id' => $userId,
            'proveedor_id' => $proveedorId,
            'nro_factura' => $nroFactura,
            'total_usd' => $total,
            'estado' => $estado,
            'fecha_emision' => $fechaEmision,
            'fecha_vencimiento' => $fechaVencimiento
        ]);
    }

    public function crearCompraItem($compraId, $productoId, $cantidad, $precioUnitario) {
        return QueryBuilder::table('compra_items')->insert([
            'compra_id' => $compraId,
            'producto_id' => $productoId,
            'cantidad' => $cantidad,
            'precio_unitario' => $precioUnitario
        ]);
    }

    // Actualizar el precio de costo de este proveedor específico
    public function actualizarPrecioProveedor($productoId, $proveedorId, $precioCosto) {
        return QueryBuilder::table('producto_proveedores')->updateRaw(
            "ultimo_precio_costo = ?",
            [$precioCosto]
        )->where('producto_id', $productoId)
          ->where('proveedor_id', $proveedorId)
          ->insertOrUpdate([
              'producto_id' => $productoId,
              'proveedor_id' => $proveedorId,
              'ultimo_precio_costo' => $precioCosto
          ]);
    }

    // Obtener reporte de compras (Filtrable por estado 'Pendiente')
    public function obtenerTodas($userId, $estado = '', $limit = 0, $offset = 0) {
        $query = $this->query()
            ->from('compras c')
            ->where('c.user_id', $userId)
            ->select(['c.*'])
            ->selectRaw('p.nombre as proveedor_nombre')
            ->join('proveedores p', 'c.proveedor_id', '=', 'p.id');
        
        if (!empty($estado)) {
            $query->where('c.estado', $estado);
        }
        
        $query->orderBy('c.id', 'DESC');
        
        if ($limit > 0) {
            $query->limit($limit)->offset($offset);
        }
        
        return $query->get();
    }

    // Contar total de compras para paginación
    public function contarTodas($userId, $estado = '') {
        $query = $this->scopeUser($userId);
        
        if (!empty($estado)) {
            $query->where('estado', $estado);
        }
        
        return $query->count();
    }

    // Obtener una compra específica
    public function obtenerPorId($userId, $compraId) {
        return $this->query()
            ->where('id', $compraId)
            ->where('user_id', $userId)
            ->first();
    }

    // Obtener los productos dentro de una compra
    public function obtenerItems($compraId) {
        return QueryBuilder::table('compra_items')
            ->select(['compra_items.*'])
            ->selectRaw('productos.nombre as nombre_producto')
            ->leftJoin('productos', 'compra_items.producto_id', '=', 'productos.id')
            ->where('compra_items.compra_id', $compraId)
            ->get();
    }

    public function pagarCompra($compraId) {
        return $this->query()
            ->where('id', $compraId)
            ->update(['estado' => PaymentStatus::PAGADA->value]) > 0;
    }
}