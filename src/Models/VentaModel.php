<?php
namespace App\Models;

use App\Core\BaseModel;
use App\Core\Database;
use App\Core\QueryBuilder;
use App\Domain\Enums\PaymentStatus;
use \PDO;

class VentaModel extends BaseModel {
    protected $table = 'ventas';
    protected $fillable = [
        'user_id', 'cliente_id', 'total_usd', 'tasa_ves', 'total_ves', 
        'estado_pago', 'metodo_pago', 'notas', 'created_at', 'updated_at'
    ];
    protected $timestamps = true;

    public function __construct() {
        parent::__construct();
    }

    public function validate($data) {
        $errors = [];
        if (empty($data['carrito'])) {
            $errors[] = 'El carrito no puede estar vacío.';
        }
        return $errors;
    }

    public function crearVenta($userId, $totalUSD, $tasa, $totalVES, $clienteId = null, $estadoPago = null, $metodoPago = 'Efectivo', $notas = null) {
        $estadoPago = $estadoPago ?? PaymentStatus::PAGADA->value;
        return QueryBuilder::table('ventas')->insert([
            'user_id' => $userId,
            'cliente_id' => $clienteId,
            'total_usd' => $totalUSD,
            'tasa_ves' => $tasa,
            'total_ves' => $totalVES,
            'estado_pago' => $estadoPago,
            'metodo_pago' => $metodoPago,
            'notas' => $notas
        ]);
    }

    public function crearVentaItem($ventaId, $productoId, $nombre, $cantidad, $precioUSD, $costoUSD = 0) {
        return QueryBuilder::table('venta_items')->insert([
            'venta_id' => $ventaId,
            'producto_id' => $productoId,
            'nombre_producto' => $nombre,
            'cantidad' => $cantidad,
            'precio_unitario_usd' => $precioUSD,
            'costo_unitario_usd' => $costoUSD
        ]);
    }
    
    public function obtenerVentaPorId($userId, $ventaId) {
        return QueryBuilder::table('ventas')
            ->where('id', $ventaId)
            ->where('user_id', $userId)
            ->first();
    }
    
    public function obtenerItemsPorVentaId($ventaId) {
        return QueryBuilder::table('venta_items')
            ->where('venta_id', $ventaId)
            ->get();
    }
    
    /**
     * Obtener ventas con filtros para reportes e historial
     */
    public function obtenerVentas($userId, $filtros = []) {
        $query = $this->query()
            ->from($this->table . ' v')
            ->where('v.user_id', $userId)
            ->select(['v.*', 'c.nombre as cliente_nombre', 'c.numero_documento'])
            ->leftJoin('clientes c', 'v.cliente_id', '=', 'c.id');
        
        if (!empty($filtros['fecha_inicio'])) {
            $query->where('v.created_at', '>=', $filtros['fecha_inicio']);
        }
        if (!empty($filtros['fecha_fin'])) {
            $query->where('v.created_at', '<=', $filtros['fecha_fin'] . ' 23:59:59');
        }
        if (!empty($filtros['cliente_id'])) {
            $query->where('v.cliente_id', $filtros['cliente_id']);
        }
        if (!empty($filtros['estado_pago'])) {
            $query->where('v.estado_pago', $filtros['estado_pago']);
        }
        if (!empty($filtros['metodo_pago'])) {
            $query->where('v.metodo_pago', $filtros['metodo_pago']);
        }
        
        $query->orderBy('v.created_at', 'DESC');
        
        if (!empty($filtros['limit'])) {
            $query->limit($filtros['limit']);
            if (!empty($filtros['offset'])) {
                $query->offset($filtros['offset']);
            }
        }
        
        return $query->get();
    }
    
    /**
     * Contar ventas para paginación
     */
    public function contarVentas($userId, $filtros = []) {
        $query = $this->query()
            ->from($this->table . ' v')
            ->where('v.user_id', $userId);
        
        if (!empty($filtros['fecha_inicio'])) {
            $query->where('v.created_at', '>=', $filtros['fecha_inicio']);
        }
        if (!empty($filtros['fecha_fin'])) {
            $query->where('v.created_at', '<=', $filtros['fecha_fin'] . ' 23:59:59');
        }
        if (!empty($filtros['cliente_id'])) {
            $query->where('v.cliente_id', $filtros['cliente_id']);
        }
        if (!empty($filtros['estado_pago'])) {
            $query->where('v.estado_pago', $filtros['estado_pago']);
        }
        
        return $query->count();
    }
    
    /**
     * Obtener ventas de un cliente específico
     */
    public function obtenerVentasPorCliente($clienteId, $limite = 20) {
        return QueryBuilder::table('ventas')
            ->where('cliente_id', $clienteId)
            ->orderBy('created_at', 'DESC')
            ->limit($limite)
            ->get();
    }
    
    /**
     * Marcar una venta pendiente como pagada
     */
    public function marcarPagada($userId, $ventaId) {
        return QueryBuilder::table('ventas')
            ->where('id', $ventaId)
            ->where('estado_pago', PaymentStatus::PENDIENTE->value)
            ->update(['estado_pago' => PaymentStatus::PAGADA->value]) > 0;
    }
    
    /**
     * Obtener deuda total de un cliente
     */
    public function obtenerDeudaPorCliente($clienteId) {
        $result = QueryBuilder::table('ventas')
            ->selectRaw("COALESCE(SUM(total_usd), 0) as deuda_total")
            ->where('cliente_id', $clienteId)
            ->where('estado_pago', PaymentStatus::PENDIENTE->value)
            ->first();
        return (float)$result['deuda_total'];
    }
    
    /**
     * Obtener estadísticas de ventas para reportes
     */
    public function obtenerEstadisticasVentas($userId, $fechaInicio = null, $fechaFin = null) {
        $query = QueryBuilder::table('ventas')
            ->selectRaw("COUNT(*) as total_ventas")
            ->selectRaw("COALESCE(SUM(total_usd), 0) as total_vendido_usd")
            ->selectRaw("COALESCE(SUM(total_ves), 0) as total_vendido_ves")
            ->selectRaw("COALESCE(AVG(total_usd), 0) as promedio_venta_usd")
            ->selectRaw("COALESCE(SUM(CASE WHEN estado_pago = '" . PaymentStatus::PAGADA->value . "' THEN total_usd ELSE 0 END), 0) as total_cobrado")
            ->selectRaw("COALESCE(SUM(CASE WHEN estado_pago = '" . PaymentStatus::PENDIENTE->value . "' THEN total_usd ELSE 0 END), 0) as total_por_cobrar")
            ->where('user_id', $userId);
        
        if ($fechaInicio) {
            $query->where('created_at', '>=', $fechaInicio);
        }
        if ($fechaFin) {
            $query->where('created_at', '<=', $fechaFin . ' 23:59:59');
        }
        
        return $query->first();
    }
    
    /**
     * Obtener productos más vendidos
     */
    public function obtenerProductosMasVendidos($userId, $fechaInicio = null, $fechaFin = null, $limite = 10) {
        $query = QueryBuilder::table('venta_items vi')
            ->select(['vi.nombre_producto'])
            ->selectRaw('SUM(vi.cantidad) as cantidad_vendida')
            ->selectRaw('COALESCE(SUM(vi.cantidad * vi.precio_unitario_usd), 0) as total_vendido_usd')
            ->selectRaw('COUNT(DISTINCT vi.venta_id) as num_ventas')
            ->join('ventas v', 'vi.venta_id', '=', 'v.id')
            ->where('v.user_id', $userId);
        
        if ($fechaInicio) {
            $query->where('v.created_at', '>=', $fechaInicio);
        }
        if ($fechaFin) {
            $query->where('v.created_at', '<=', $fechaFin . ' 23:59:59');
        }
        
        return $query->groupBy('vi.nombre_producto')
            ->orderBy('cantidad_vendida', 'DESC')
            ->limit($limite)
            ->get();
    }
    
    /**
     * Obtener reporte de ganancias por producto vendido
     */
    public function obtenerReporteGanancias($userId, $fechaInicio = null, $fechaFin = null) {
        $query = QueryBuilder::table('venta_items vi')
            ->select(['vi.nombre_producto', 'vi.producto_id'])
            ->selectRaw('SUM(vi.cantidad) as cantidad_vendida')
            ->selectRaw('AVG(vi.costo_unitario_usd) as costo_unitario')
            ->selectRaw('AVG(vi.precio_unitario_usd) as precio_venta_promedio')
            ->selectRaw('COALESCE(SUM(vi.cantidad * vi.costo_unitario_usd), 0) as costo_total')
            ->selectRaw('COALESCE(SUM(vi.cantidad * vi.precio_unitario_usd), 0) as venta_total')
            ->selectRaw('COALESCE(SUM(vi.cantidad * vi.precio_unitario_usd) - SUM(vi.cantidad * vi.costo_unitario_usd), 0) as ganancia_total')
            ->join('ventas v', 'vi.venta_id', '=', 'v.id')
            ->leftJoin('productos p', 'vi.producto_id', '=', 'p.id')
            ->where('v.user_id', $userId);
        
        if ($fechaInicio) {
            $query->where('v.created_at', '>=', $fechaInicio);
        }
        if ($fechaFin) {
            $query->where('v.created_at', '<=', $fechaFin . ' 23:59:59');
        }
        
        return $query->groupBy(['vi.nombre_producto', 'vi.producto_id'])
            ->orderBy('ganancia_total', 'DESC')
            ->get();
    }
    
    /**
     * Obtener todas las cuentas por cobrar (ventas pendientes)
     */
    public function obtenerCuentasPorCobrar($userId, $clienteId = null) {
        $query = QueryBuilder::table('ventas v')
            ->select(['v.*', 'c.nombre as cliente_nombre', 'c.numero_documento', 'c.telefono as cliente_telefono'])
            ->join('clientes c', 'v.cliente_id', '=', 'c.id')
            ->where('v.user_id', $userId)
            ->where('v.estado_pago', PaymentStatus::PENDIENTE->value);
        
        if ($clienteId) {
            $query->where('v.cliente_id', $clienteId);
        }
        
        $ventas = $query->orderBy('v.created_at', 'ASC')->get();
        
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
        
        return QueryBuilder::table('ventas')
            ->selectRaw('DATE(created_at) as fecha')
            ->selectRaw('COUNT(*) as num_ventas')
            ->selectRaw('COALESCE(SUM(total_usd), 0) as total_usd')
            ->selectRaw('COALESCE(SUM(total_ves), 0) as total_ves')
            ->where('user_id', $userId)
            ->where('created_at', '>=', $fechaInicio)
            ->groupByRaw('DATE(created_at)')
            ->orderBy('fecha', 'ASC')
            ->get();
    }
    
    /**
     * Obtener estado de una venta por ID (para debugging interno)
     */
    public function obtenerEstadoVenta($id) {
        return $this->query()
            ->select(['id', 'estado_pago', 'user_id', 'cliente_id'])
            ->where('id', $id)
            ->first();
    }
}
