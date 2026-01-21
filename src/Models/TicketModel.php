<?php
namespace App\Models;

use App\Core\BaseModel;
use App\Core\Database;
use App\Core\QueryBuilder;
use App\Domain\Enums\TicketStatus;
use \PDO;

class TicketModel extends BaseModel {
    protected $table = 'tickets';
    protected $fillable = ['user_id', 'subject', 'priority', 'status'];
    protected $timestamps = true;

    public function __construct() {
        parent::__construct();
    }

    // --- Funciones para el Usuario ---
    public function crearTicket($userId, $subject, $priority, $firstMessage) {
        try {
            $this->db->beginTransaction();
            $ticketId = $this->create([
                'user_id' => $userId,
                'subject' => $subject,
                'priority' => $priority
            ]);
            
            $this->agregarRespuesta($ticketId, $userId, $firstMessage);

            $this->db->commit();
            return $ticketId;
        } catch (\Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return false;
        }
    }

    public function obtenerTicketsPorUsuario($userId) {
        return $this->query()
            ->where('user_id', $userId)
            ->orderBy('updated_at', 'DESC')
            ->get();
    }

    public function obtenerTicketPorId($userId, $ticketId) {
        return $this->query()
            ->where('id', $ticketId)
            ->where('user_id', $userId)
            ->first();
    }

    public function obtenerRespuestas($ticketId) {
        return QueryBuilder::table('ticket_replies')
            ->select(['ticket_replies.*'])
            ->selectRaw('usuarios.email')
            ->join('usuarios', 'ticket_replies.user_id', '=', 'usuarios.id')
            ->where('ticket_replies.ticket_id', $ticketId)
            ->orderBy('ticket_replies.created_at', 'ASC')
            ->get();
    }

    public function agregarRespuesta($ticketId, $userId, $message) {
        return QueryBuilder::table('ticket_replies')->insert([
            'ticket_id' => $ticketId,
            'user_id' => $userId,
            'message' => $message
        ]);
    }
    
    public function cambiarEstado($ticketId, $status) {
        return $this->query()->where('id', $ticketId)->update(['status' => $status]) > 0;
    }

    // --- Funciones para el Administrador ---
    public function obtenerTodosLosTickets() {
        return $this->query()
            ->select(['tickets.*'])
            ->selectRaw('usuarios.email')
            ->join('usuarios', 'tickets.user_id', '=', 'usuarios.id')
            ->orderBy('tickets.updated_at', 'DESC')
            ->get();
    }

    public function obtenerTicketPorIdAdmin($ticketId) {
        return $this->query()->where('id', $ticketId)->first();
    }
}