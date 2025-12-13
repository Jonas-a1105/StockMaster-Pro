<?php
namespace App\Models;

use App\Core\Database;
use \PDO;

class Proveedor {
    
    private $db;

    public function __construct() {
        $this->db = Database::conectar();
    }

    public function obtenerTodos($userId, $limit = null, $offset = 0, $busqueda = '') {
        $sql = "SELECT * FROM proveedores WHERE user_id = ?";
        $params = [$userId];

        if (!empty($busqueda)) {
            $sql .= " AND (nombre LIKE ? OR contacto LIKE ? OR email LIKE ?)";
            $term = "%{$busqueda}%";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $sql .= " ORDER BY nombre ASC";

        if ($limit !== null) {
            // Cast to int for safety and embed directly to avoid PDO string binding issues with LIMIT
            $sql .= " LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function contarTodos($userId, $busqueda = '') {
        $sql = "SELECT COUNT(*) as total FROM proveedores WHERE user_id = ?";
        $params = [$userId];

        if (!empty($busqueda)) {
            $sql .= " AND (nombre LIKE ? OR contacto LIKE ? OR email LIKE ?)";
            $term = "%{$busqueda}%";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch()['total'];
    }

    public function obtenerPorId($userId, $id) {
        $query = "SELECT * FROM proveedores WHERE id = ? AND user_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id, $userId]);
        return $stmt->fetch();
    }

    /**
     * Busca un proveedor por nombre (case-insensitive)
     * @param int $userId
     * @param string $nombre
     * @return array|false Datos del proveedor o false si no existe
     */
    public function obtenerPorNombre($userId, $nombre) {
        $query = "SELECT * FROM proveedores WHERE user_id = ? AND LOWER(nombre) = LOWER(?)";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$userId, $nombre]);
        return $stmt->fetch();
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
    public function crear($userId, $nombre, $contacto, $telefono, $email) {
        $query = "INSERT INTO proveedores (user_id, nombre, contacto, telefono, email) 
                  VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($query);
        
        if ($stmt->execute([$userId, $nombre, $contacto, $telefono, $email])) {
            return $this->db->lastInsertId(); // Devuelve el ID del proveedor recién creado
        }
        return false;
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
        return $this->crear($userId, $nombre, $contacto, $telefono, $email);
    }

    public function actualizar($userId, $id, $nombre, $contacto, $telefono, $email) {
        $query = "UPDATE proveedores 
                  SET nombre = ?, contacto = ?, telefono = ?, email = ?
                  WHERE id = ? AND user_id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$nombre, $contacto, $telefono, $email, $id, $userId]);
    }

    public function eliminar($userId, $id) {
        $query = "DELETE FROM proveedores WHERE id = ? AND user_id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$id, $userId]);
    }
}