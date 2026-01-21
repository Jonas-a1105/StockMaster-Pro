<?php
namespace App\Core;

use \PDO;

class QueryBuilder {
    protected $db;
    protected $table;
    protected $select = ['*'];
    protected $selectParams = [];
    protected $where = [];
    protected $params = [];
    protected $orderBy = [];
    protected $limit;
    protected $offset;
    protected $joins = [];
    protected $joinParams = [];
    protected $groupBy = [];
    protected $groupByParams = [];

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public static function table($tableName) {
        $instance = new self(Database::conectar());
        $instance->table = $tableName;
        return $instance;
    }

    public function from($tableName) {
        $this->table = $tableName;
        return $this;
    }

    public function select($columns = ['*']) {
        $this->select = is_array($columns) ? $columns : func_get_args();
        $this->selectParams = [];
        return $this;
    }

    public function selectRaw($sql, $params = []) {
        $this->select[] = $sql;
        foreach ($params as $param) {
            $this->selectParams[] = $param;
        }
        // Remove '*' if it was there as default
        if (($key = array_search('*', $this->select)) !== false) {
            unset($this->select[$key]);
            $this->select = array_values($this->select);
        }
        return $this;
    }

    public function join($table, $first, $operator, $second, $type = 'INNER') {
        $this->joins[] = " $type JOIN $table ON $first $operator $second";
        return $this;
    }

    public function leftJoin($table, $first, $operator, $second) {
        return $this->join($table, $first, $operator, $second, 'LEFT');
    }

    public function leftJoinRaw($sql, $params = []) {
        $this->joins[] = " LEFT JOIN $sql";
        foreach ($params as $param) {
            $this->joinParams[] = $param;
        }
        return $this;
    }

    public function where($column, $operator, $value = null) {
        if (is_null($value)) {
            $value = $operator;
            $operator = '=';
        }
        $this->where[] = "$column $operator ?";
        $this->params[] = $value;
        return $this;
    }

    public function whereRaw($sql, $params = []) {
        $this->where[] = "($sql)";
        foreach ($params as $param) {
            $this->params[] = $param;
        }
        return $this;
    }

    public function whereIn($column, array $values) {
        if (empty($values)) {
            // Force a false condition if it's an empty array to match expected SQL behavior
            $this->where[] = "1 = 0";
            return $this;
        }
        $placeholders = implode(', ', array_fill(0, count($values), '?'));
        $this->where[] = "$column IN ($placeholders)";
        $this->params = array_merge($this->params, array_values($values));
        return $this;
    }

    public function whereNotIn($column, array $values) {
        if (empty($values)) {
            return $this;
        }
        $placeholders = implode(', ', array_fill(0, count($values), '?'));
        $this->where[] = "$column NOT IN ($placeholders)";
        $this->params = array_merge($this->params, array_values($values));
        return $this;
    }

    public function orderBy($column, $direction = 'ASC') {
        $this->orderBy[] = "$column $direction";
        return $this;
    }

    public function limit($limit) {
        $this->limit = (int)$limit;
        return $this;
    }

    public function offset($offset) {
        $this->offset = (int)$offset;
        return $this;
    }

    public function groupBy($columns) {
        $cols = is_array($columns) ? $columns : func_get_args();
        foreach ($cols as $col) {
            $this->groupBy[] = $col;
        }
        return $this;
    }

    public function groupByRaw($sql, $params = []) {
        $this->groupBy[] = $sql;
        foreach ($params as $param) {
            $this->groupByParams[] = $param;
        }
        return $this;
    }

    public function get() {
        $sql = $this->buildSelect();
        $stmt = $this->db->prepare($sql);
        $allParams = array_merge($this->selectParams, $this->joinParams, $this->params, $this->groupByParams);
        $stmt->execute($allParams);
        return $stmt->fetchAll();
    }

    public function first() {
        $this->limit(1);
        $results = $this->get();
        return !empty($results) ? $results[0] : null;
    }

    public function count() {
        $result = $this->selectRaw("COUNT(*) as aggregate")->first();
        return (int)($result['aggregate'] ?? 0);
    }

    protected function buildSelect() {
        $sql = "SELECT " . implode(', ', $this->select) . " FROM " . $this->table;

        if (!empty($this->joins)) {
            $sql .= implode(' ', $this->joins);
        }

        if (!empty($this->where)) {
            $sql .= " WHERE " . implode(' AND ', $this->where);
        }

        if (!empty($this->groupBy)) {
            $sql .= " GROUP BY " . implode(', ', $this->groupBy);
        }

        if (!empty($this->orderBy)) {
            $sql .= " ORDER BY " . implode(', ', $this->orderBy);
        }

        if (isset($this->limit)) {
            $sql .= " LIMIT " . $this->limit;
            if (isset($this->offset)) {
                $sql .= " OFFSET " . $this->offset;
            }
        }

        return $sql;
    }

    // Basic support for Insert, Update, Delete could be added here too
    public function insert(array $data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO {$this->table} ($columns) VALUES ($placeholders)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_values($data));
        return $this->db->lastInsertId();
    }

    public function update(array $data) {
        $set = implode(', ', array_map(function($col) { return "$col = ?"; }, array_keys($data)));
        $sql = "UPDATE {$this->table} SET $set";
        
        $params = array_values($data);

        if (!empty($this->where)) {
            $sql .= " WHERE " . implode(' AND ', $this->where);
            $params = array_merge($params, $this->params);
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    public function delete() {
        $sql = "DELETE FROM {$this->table}";
        if (!empty($this->where)) {
            $sql .= " WHERE " . implode(' AND ', $this->where);
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($this->params);
        return $stmt->rowCount();
    }

    public function increment($column, $amount = 1) {
        return $this->updateRaw("$column = $column + ?", [$amount]);
    }

    public function decrement($column, $amount = 1) {
        return $this->updateRaw("$column = GREATEST(0, $column - ?)", [$amount]);
    }

    public function insertOrUpdate(array $data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $updates = implode(', ', array_map(function($col) { return "$col = VALUES($col)"; }, array_keys($data)));
        
        $sql = "INSERT INTO {$this->table} ($columns) VALUES ($placeholders) ON DUPLICATE KEY UPDATE $updates";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(array_values($data));
    }

    public function updateRaw($sql, $params = []) {
        $query = "UPDATE {$this->table} SET $sql";
        $finalParams = array_values($params);

        if (!empty($this->where)) {
            $query .= " WHERE " . implode(' AND ', $this->where);
            $finalParams = array_merge($finalParams, $this->params);
        }

        $stmt = $this->db->prepare($query);
        return $stmt->execute($finalParams);
    }
}
