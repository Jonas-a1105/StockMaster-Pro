<?php
namespace App\Models;

use App\Core\BaseModel;
use App\Core\Database;
use App\Core\QueryBuilder;
use \PDO;

class Cliente extends BaseModel {
    protected $table = 'clientes';
    protected $fillable = [
        'user_id', 'nombre', 'tipo_documento', 'numero_documento', 
        'telefono', 'email', 'direccion', 'tipo_cliente', 
        'limite_credito', 'activo', 'created_at', 'updated_at'
    ];
    protected $timestamps = true;

    public function __construct() {
        parent::__construct();
    }

    /**
     * Crear un nuevo cliente
     */
    public function crear($data) {
        return $this->create($data);
    }

    /**
     * Obtener todos los clientes de un usuario con filtros
     */
    public function obtenerTodos($userId, $busqueda = '', $soloActivos = true) {
        return $this->buscarStandard($userId, ['nombre', 'numero_documento', 'email'], $busqueda, $soloActivos)
            ->orderBy('nombre', 'ASC')
            ->get();
    }

    /**
     * Obtener un cliente por ID
     */
    public function obtenerPorId($userId, $id) {
        return $this->query()
            ->where('id', $id)
            ->where('user_id', $userId)
            ->first();
    }

    /**
     * Actualizar datos de un cliente
     */
    public function actualizar($id, $data) {
        return parent::update($id, $data) > 0;
    }

    /**
     * Desactivar un cliente (soft delete)
     */
    public function desactivar($userId, $id) {
        return $this->query()
            ->where('id', $id)
            ->where('user_id', $userId)
            ->update(['activo' => 0]) > 0;
    }

    /**
     * Reactivar un cliente
     */
    public function reactivar($userId, $id) {
        return $this->query()
            ->where('id', $id)
            ->where('user_id', $userId)
            ->update(['activo' => 1]) > 0;
    }

    /**
     * Buscar clientes para el POS (autocompletar)
     */
    public function buscarParaPOS($userId, $termino) {
        return $this->query()
            ->select(['id', 'nombre', 'numero_documento', 'tipo_documento', 'limite_credito'])
            ->where('user_id', $userId)
            ->whereRaw("(nombre LIKE ? OR numero_documento LIKE ?)", ['%' . $termino . '%', '%' . $termino . '%'])
            ->where('activo', 1)
            ->limit(10)
            ->get();
    }

    /**
     * Obtener la deuda total de un cliente (ventas pendientes)
     */
    public function obtenerDeuda($clienteId) {
        $result = QueryBuilder::table('ventas')
            ->selectRaw("COALESCE(SUM(total_usd), 0) as deuda_total")
            ->where('cliente_id', $clienteId)
            ->where('estado_pago', 'Pendiente')
            ->first();
        return (float)$result['deuda_total'];
    }

    /**
     * Obtener historial de compras de un cliente
     */
    public function obtenerHistorialCompras($clienteId, $limite = 20) {
        // 1. Obtener las ventas principales
        $ventas = QueryBuilder::table('ventas')
            ->where('cliente_id', $clienteId)
            ->orderBy('created_at', 'DESC')
            ->limit($limite)
            ->get();

        if (empty($ventas)) {
            return [];
        }

        // 2. Extraer IDs
        $ventaIds = array_column($ventas, 'id');

        // 3. Obtener los items de esas ventas
        $todosItems = QueryBuilder::table('venta_items')
            ->whereIn('venta_id', $ventaIds)
            ->get();

        // 4. Agrupar items por venta en memoria (PHP)
        $itemsPorVenta = [];
        foreach ($todosItems as $item) {
            $itemsPorVenta[$item['venta_id']][] = $item['cantidad'] . 'x ' . ($item['nombre_producto'] ?? 'Producto');
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
        return QueryBuilder::table('ventas')
            ->selectRaw("COUNT(*) as total_compras")
            ->selectRaw("COALESCE(SUM(total_usd), 0) as total_gastado")
            ->selectRaw("COALESCE(SUM(CASE WHEN estado_pago = 'Pendiente' THEN total_usd ELSE 0 END), 0) as deuda_actual")
            ->selectRaw("MAX(created_at) as ultima_compra")
            ->where('cliente_id', $clienteId)
            ->first();
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
