<?php
namespace App\Core;

use App\Core\Database;
use App\Core\QueryBuilder;
use PDO;

abstract class BaseModel {
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    protected $fillable = []; // Campos permitidos para asignación masiva

    public function __construct() {
        $this->db = Database::conectar();
    }

    /**
     * Retorna una instancia de QueryBuilder vinculada a este modelo.
     */
    public function query() {
        return QueryBuilder::table($this->table);
    }

    /**
     * Busca un registro por su clave primaria
     */
    public function find($id) {
        return $this->query()->where($this->primaryKey, $id)->first();
    }

    /**
     * Obtiene todos los registros (opcionalmente filtrados por usuario)
     */
    public function all(int $userId = null, string $orderBy = null) {
        $query = $this->query();
        if ($userId !== null) {
            $query->where('user_id', $userId);
        }
        if ($orderBy) {
            $parts = explode(' ', $orderBy);
            $query->orderBy($parts[0], $parts[1] ?? 'ASC');
        }
        return $query->get();
    }

    /**
     * Crea un nuevo registro filtrando por fillable
     */
    public function create(array $data) {
        if (!empty($this->fillable)) {
            $data = array_intersect_key($data, array_flip($this->fillable));
        }
        
        // Auto-timestamps
        if (property_exists($this, 'timestamps') && $this->timestamps === true) {
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['updated_at'] = date('Y-m-d H:i:s');
        }

        return $this->query()->insert($data);
    }

    /**
     * Actualiza un registro por ID filtrando por fillable
     */
    public function update($id, array $data) {
        if (!empty($this->fillable)) {
            $data = array_intersect_key($data, array_flip($this->fillable));
        }

        if (property_exists($this, 'timestamps') && $this->timestamps === true) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }

        return $this->query()->where($this->primaryKey, $id)->update($data);
    }

    /**
     * Elimina un registro por ID
     */
    public function delete($id) {
        return $this->query()->where($this->primaryKey, $id)->delete();
    }

    /**
     * Aplica un filtro de usuario a una consulta.
     */
    public function scopeUser($userId) {
        return $this->query()->where('user_id', $userId);
    }

    /**
     * Realiza una búsqueda estándar en múltiples campos tipo LIKE.
     * @param int $userId Id del usuario.
     * @param array $campos Lista de campos en los que buscar.
     * @param string $termino Término de búsqueda.
     * @param bool $soloActivos Filtrar solo por activos si la columna existe.
     * @return QueryBuilder
     */
    public function buscarStandard(int $userId, array $campos, string $termino, bool $soloActivos = true) {
        $query = $this->scopeUser($userId);
        
        if (!empty($termino)) {
            $wildcard = "%$termino%";
            $placeholders = array_fill(0, count($campos), "$campos[0] LIKE ?"); // Fallback simple
            
            // Construir manualmente el string LIKE con OR para mayor flexibilidad
            $sqlParts = [];
            $params = [];
            foreach ($campos as $campo) {
                $sqlParts[] = "$campo LIKE ?";
                $params[] = $wildcard;
            }
            $query->whereRaw("(" . implode(" OR ", $sqlParts) . ")", $params);
        }

        if ($soloActivos && property_exists($this, 'table') && $this->table === 'clientes') {
             // Este es un chequeo específico, idealmente sería más genérico pero sirve por ahora
             $query->where('activo', 1);
        }

        return $query;
    }

    /**
     * Cuenta total de registros
     */
    public function count(int $userId = null) {
        $query = $this->query();
        if ($userId !== null) {
            $query->where('user_id', $userId);
        }
        return $query->count();
    }
}
