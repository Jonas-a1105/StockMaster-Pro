<?php
namespace App\Models;

use App\Core\BaseModel;
use App\Core\Database;
use \PDO;

class Proveedor extends BaseModel {
    protected $table = 'proveedores';
    protected $fillable = ['user_id', 'nombre', 'contacto', 'telefono', 'email', 'created_at', 'updated_at'];
    protected $timestamps = true;

    public function __construct() {
        parent::__construct();
    }

    public function obtenerTodos($userId, $limit = null, $offset = 0, $busqueda = '') {
        $query = $this->buscarStandard($userId, ['nombre', 'contacto', 'email'], $busqueda, false)
            ->orderBy('nombre', 'ASC');

        if ($limit !== null) {
            $query->limit($limit)->offset($offset);
        }

        return $query->get();
    }

    public function contarTodos($userId, $busqueda = '') {
        return $this->buscarStandard($userId, ['nombre', 'contacto', 'email'], $busqueda, false)->count();
    }

    public function obtenerPorId($userId, $id) {
        return $this->query()->where('id', $id)->where('user_id', $userId)->first();
    }

    /**
     * Busca un proveedor por nombre (case-insensitive)
     * @param int $userId
     * @param string $nombre
     * @return array|false Datos del proveedor o false si no existe
     */
    public function obtenerPorNombre($userId, $nombre) {
        return $this->query()
            ->where('user_id', $userId)
            ->whereRaw("LOWER(nombre) = LOWER(?)", [$nombre])
            ->first();
    }

    /**
     * Crea un nuevo proveedor y devuelve su ID
     * @param int $userId
     * @param string $nombre
     * @param string|null $contacto
     * @param string|null $telefono
     * @param string|null $email
     * @return int|false ID del proveedor creado o false si falla
     */
    public function crear($data) {
        if (!is_array($data)) {
            // Fallback for old positional calls if any
            $args = func_get_args();
            $data = [
                'user_id' => $args[0],
                'nombre' => $args[1] ?? '',
                'contacto' => $args[2] ?? null,
                'telefono' => $args[3] ?? null,
                'email' => $args[4] ?? null,
            ];
        }
        return $this->create($data);
    }

    /**
     * Busca un proveedor por nombre, si existe lo devuelve, si no lo crea
     * @param int $userId
     * @param string $nombre
     * @param string|null $contacto
     * @param string|null $telefono
     * @param string|null $email
     * @return int|false ID del proveedor (existente o nuevo) o false si falla
     */
    public function obtenerOCrear($userId, $nombre, $contacto = null, $telefono = null, $email = null) {
        // Normalizar valores vacíos a NULL
        $contacto = trim($contacto ?? '') ?: null;
        $telefono = trim($telefono ?? '') ?: null;
        $email = trim($email ?? '') ?: null;
        
        // Buscar proveedor existente por nombre
        $proveedorExistente = $this->obtenerPorNombre($userId, $nombre);
        
        if ($proveedorExistente) {
            // Ya existe, devolver su ID
            return $proveedorExistente['id'];
        }
        
        // No existe, crear nuevo y devolver su ID
        return $this->crear([
            'user_id' => $userId,
            'nombre' => $nombre,
            'contacto' => $contacto,
            'telefono' => $telefono,
            'email' => $email
        ]);
    }

    public function actualizar($id, $data) {
        return parent::update($id, $data) > 0;
    }

    public function eliminar($userId, $id) {
        return $this->query()
            ->where('id', $id)
            ->where('user_id', $userId)
            ->delete() > 0;
    }
}