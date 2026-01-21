<?php
namespace App\Models;

use App\Core\BaseModel;
use App\Core\Database;
use App\Domain\Enums\MovementType;
use \PDO;

class Movimiento extends BaseModel {
    protected $table = 'movimientos';
    protected $fillable = [
        'user_id', 'producto_id', 'productoNombre', 'tipo', 
        'motivo', 'cantidad', 'nota', 'proveedor', 'fecha'
    ];
    protected $timestamps = false; // La tabla usa 'fecha' en lugar de created_at/updated_at por ahora

    public function __construct() {
        parent::__construct();
    }

    /**
     * Crea un nuevo registro de movimiento
     */
    public function crear($userId, $productoId, $productoNombre, $tipo, $motivo, $cantidad, $nota, $proveedor) {
        return $this->create([
            'user_id' => $userId,
            'producto_id' => $productoId,
            'productoNombre' => $productoNombre,
            'tipo' => $tipo,
            'motivo' => $motivo,
            'cantidad' => $cantidad,
            'nota' => $nota,
            'proveedor' => $proveedor
        ]);
    }

    /**
     * Obtiene todos los movimientos de un usuario, con filtros
     */
    public function obtenerTodos($userId, $filtros = []) {
        if (is_numeric($filtros)) {
            $filtros = ['limit' => $filtros];
        }

        $query = $this->query()
            ->select(['movimientos.*'])
            ->selectRaw('productos.nombre as productoNombreActual')
            ->selectRaw('COALESCE(proveedores.nombre, movimientos.proveedor) as proveedor')
            ->leftJoinRaw('productos ON movimientos.producto_id = productos.id AND movimientos.user_id = productos.user_id')
            ->leftJoinRaw('proveedores ON productos.proveedor_id = proveedores.id AND productos.user_id = proveedores.user_id')
            ->where('movimientos.user_id', $userId);

        if (!empty($filtros['producto'])) {
            $query->whereRaw("(movimientos.productoNombre LIKE ? OR productos.nombre LIKE ?)", [
                '%' . $filtros['producto'] . '%', '%' . $filtros['producto'] . '%'
            ]);
        }

        if (!empty($filtros['producto_id'])) {
            $query->where('movimientos.producto_id', $filtros['producto_id']);
        }

        if (!empty($filtros['tipo'])) {
            $query->where('movimientos.tipo', $filtros['tipo']);
        }
        
        if (!empty($filtros['fecha_inicio'])) {
            $query->where('movimientos.fecha', '>=', $filtros['fecha_inicio']);
        }
        if (!empty($filtros['fecha_fin'])) {
            $query->where('movimientos.fecha', '<=', $filtros['fecha_fin'] . ' 23:59:59');
        }

        $query->orderBy('movimientos.fecha', 'DESC');

        if (!empty($filtros['limit'])) {
            $query->limit($filtros['limit'])->offset($filtros['offset'] ?? 0);
        }

        return $query->get();
    }

    /**
     * Cuenta el total de movimientos para paginación
     */
    public function contarTodos($userId, $filtros = []) {
        $query = $this->query()->where('user_id', $userId);

        if (!empty($filtros['producto'])) {
            $query->where('productoNombre', 'LIKE', '%' . $filtros['producto'] . '%');
        }
        if (!empty($filtros['producto_id'])) {
            $query->where('producto_id', $filtros['producto_id']);
        }
        if (!empty($filtros['tipo'])) {
            $query->where('tipo', $filtros['tipo']);
        }
        if (!empty($filtros['fecha_inicio'])) {
            $query->where('fecha', '>=', $filtros['fecha_inicio']);
        }
        if (!empty($filtros['fecha_fin'])) {
            $query->where('fecha', '<=', $filtros['fecha_fin'] . ' 23:59:59');
        }

        return $query->count();
    }
}