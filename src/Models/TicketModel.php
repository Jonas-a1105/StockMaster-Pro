<?php
namespace App\Models;

use App\Core\Database;
use \PDO;

class TicketModel {
    
    private $db;

    public function __construct() {
        $this->db = Database::conectar();
    }

    // --- Funciones para el Usuario ---
    public function crearTicket($userId, $subject, $priority, $firstMessage) {
        try {
            $this->db->beginTransaction();
            $queryTicket = "INSERT INTO tickets (user_id, subject, priority) VALUES (?, ?, ?)";
            $stmtTicket = $this->db->prepare($queryTicket);
            $stmtTicket->execute([$userId, $subject, $priority]);
            
            $ticketId = $this->db->lastInsertId();
            $this->agregarRespuesta($ticketId, $userId, $firstMessage);

            $this->db->commit();
            return $ticketId;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function obtenerTicketsPorUsuario($userId) {
        $query = "SELECT * FROM tickets WHERE user_id = ? ORDER BY updated_at DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function obtenerTicketPorId($userId, $ticketId) {
        $query = "SELECT * FROM tickets WHERE id = ? AND user_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$ticketId, $userId]);
        return $stmt->fetch();
    }

    public function obtenerRespuestas($ticketId) {
        $query = "SELECT r.*, u.email 
                  FROM ticket_replies r
                  JOIN usuarios u ON r.user_id = u.id
                  WHERE r.ticket_id = ? ORDER BY r.created_at ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$ticketId]);
        return $stmt->fetchAll();
    }

    public function agregarRespuesta($ticketId, $userId, $message) {
        $query = "INSERT INTO ticket_replies (ticket_id, user_id, message) VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$ticketId, $userId, $message]);
    }
    
    public function cambiarEstado($ticketId, $status) {
         $query = "UPDATE tickets SET status = ? WHERE id = ?";
         $stmt = $this->db->prepare($query);
         return $stmt->execute([$status, $ticketId]);
    }

    // --- Funciones para el Administrador ---
    public function obtenerTodosLosTickets() {
        $query = "SELECT t.*, u.email 
                  FROM tickets t
                  JOIN usuarios u ON t.user_id = u.id
                  ORDER BY t.updated_at DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function obtenerTicketPorIdAdmin($ticketId) {
        $query = "SELECT * FROM tickets WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$ticketId]);
        return $stmt->fetch();
    }
}