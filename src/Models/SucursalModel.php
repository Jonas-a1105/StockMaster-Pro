<?php
namespace App\Models;

use App\Core\BaseModel;
use App\Core\Database;
use App\Core\QueryBuilder;

/**
 * Modelo de Sucursales / Almacenes
 */
class SucursalModel extends BaseModel {
    protected $table = 'sucursales';
    
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Obtener todas las sucursales de un usuario
     */
    public function obtenerTodas($userId, $soloActivas = true) {
        $query = $this->query()->where('user_id', $userId);
        if ($soloActivas) {
            $query->where('activa', 1);
        }
        return $query->orderBy('es_principal', 'DESC')->orderBy('nombre', 'ASC')->get();
    }
    
    /**
     * Obtener sucursal por ID
     */
    public function obtenerPorId($userId, $id) {
        return $this->query()->where('id', $id)->where('user_id', $userId)->first();
    }
    
    /**
     * Obtener sucursal principal
     */
    public function obtenerPrincipal($userId) {
        return $this->query()->where('user_id', $userId)->where('es_principal', 1)->first();
    }
    
    /**
     * Crear nueva sucursal
     */
    public function crear($userId, $datos) {
        if (!empty($datos['es_principal'])) {
            $this->query()->where('user_id', $userId)->update(['es_principal' => 0]);
        }
        
        return $this->create([
            'user_id' => $userId,
            'nombre' => $datos['nombre'],
            'codigo' => $datos['codigo'] ?? null,
            'direccion' => $datos['direccion'] ?? null,
            'telefono' => $datos['telefono'] ?? null,
            'email' => $datos['email'] ?? null,
            'es_principal' => $datos['es_principal'] ?? 0
        ]);
    }
    
    /**
     * Actualizar sucursal
     */
    public function actualizar($userId, $id, $datos) {
        if (!empty($datos['es_principal'])) {
            $this->query()->where('user_id', $userId)->update(['es_principal' => 0]);
        }
        
        return $this->query()->where('id', $id)->where('user_id', $userId)->update([
            'nombre' => $datos['nombre'],
            'codigo' => $datos['codigo'] ?? null,
            'direccion' => $datos['direccion'] ?? null,
            'telefono' => $datos['telefono'] ?? null,
            'email' => $datos['email'] ?? null,
            'es_principal' => $datos['es_principal'] ?? 0,
            'activa' => $datos['activa'] ?? 1
        ]) > 0;
    }
    
    /**
     * Obtener stock de una sucursal
     */
    public function obtenerStock($sucursalId) {
        return QueryBuilder::table('stock_sucursales')
            ->select(['stock_sucursales.*'])
            ->selectRaw('productos.nombre, productos.categoria, productos.precioVentaUSD, productos.codigo_barras')
            ->join('productos', 'stock_sucursales.producto_id', '=', 'productos.id')
            ->where('stock_sucursales.sucursal_id', $sucursalId)
            ->orderBy('productos.nombre')
            ->get();
    }
    
    /**
     * Actualizar stock de producto en sucursal
     */
    public function actualizarStock($sucursalId, $productoId, $cantidad, $tipo = 'set') {
        $query = QueryBuilder::table('stock_sucursales')
            ->where('sucursal_id', $sucursalId)
            ->where('producto_id', $productoId);

        if ($query->count() > 0) {
            if ($tipo === 'set') {
                return $query->update(['stock' => $cantidad]);
            } elseif ($tipo === 'add') {
                return $query->increment('stock', $cantidad);
            } else { // subtract
                return $query->decrement('stock', $cantidad);
            }
        } else {
            return QueryBuilder::table('stock_sucursales')->insert([
                'sucursal_id' => $sucursalId,
                'producto_id' => $productoId,
                'stock' => $cantidad
            ]);
        }
    }
    
    /**
     * Crear transferencia entre sucursales
     */
    public function crearTransferencia($userId, $datos) {
        return QueryBuilder::table('transferencias')->insert([
            'user_id' => $userId,
            'sucursal_origen_id' => $datos['origen_id'],
            'sucursal_destino_id' => $datos['destino_id'],
            'producto_id' => $datos['producto_id'],
            'cantidad' => $datos['cantidad'],
            'nota' => $datos['nota'] ?? null
        ]);
    }
    
    /**
     * Completar transferencia
     */
    public function completarTransferencia($transferId) {
        // Obtener datos de la transferencia usando QueryBuilder
        $transfer = QueryBuilder::table('transferencias')
            ->where('id', $transferId)
            ->where('estado', 'Pendiente')
            ->first();
        
        if (!$transfer) return false;

        
        $this->db->beginTransaction();
        try {
            // Restar stock de origen
            $this->actualizarStock($transfer['sucursal_origen_id'], $transfer['producto_id'], $transfer['cantidad'], 'subtract');
            
            // Sumar stock en destino
            $this->actualizarStock($transfer['sucursal_destino_id'], $transfer['producto_id'], $transfer['cantidad'], 'add');
            
            // Marcar como completada
            QueryBuilder::table('transferencias')
                ->where('id', $transferId)
                ->update(['estado' => 'Completada', 'completed_at' => date('Y-m-d H:i:s')]);
            
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
    
    /**
     * Obtener transferencias
     */
    public function obtenerTransferencias($userId, $estado = null, $limit = 50) {
        $query = QueryBuilder::table('transferencias')
            ->select(['transferencias.*'])
            ->selectRaw('so.nombre as origen_nombre')
            ->selectRaw('sd.nombre as destino_nombre')
            ->selectRaw('p.nombre as producto_nombre')
            ->join('sucursales as so', 'transferencias.sucursal_origen_id', '=', 'so.id')
            ->join('sucursales as sd', 'transferencias.sucursal_destino_id', '=', 'sd.id')
            ->join('productos as p', 'transferencias.producto_id', '=', 'p.id')
            ->where('transferencias.user_id', $userId);
        
        if ($estado) {
            $query->where('transferencias.estado', $estado);
        }
        
        return $query->orderBy('transferencias.created_at', 'DESC')->limit($limit)->get();
    }
}
