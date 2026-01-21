<?php
namespace App\Services;

use App\Models\TicketModel;
use App\Models\AuditModel;
use App\Domain\Enums\TicketStatus;

class TicketService {
    private $ticketModel;
    private $auditModel;

    public function __construct() {
        $this->ticketModel = new TicketModel();
        $this->auditModel = new AuditModel();
    }

    public function createTicket($userId, $data) {
        $subject = trim($data['subject']);
        $priority = $data['priority'] ?? 'Media';
        $message = trim($data['message']);

        if (empty($subject) || empty($message)) {
            throw new \Exception('Asunto y Mensaje son obligatorios.');
        }

        $ticketId = $this->ticketModel->crearTicket($userId, $subject, $priority, $message);

        if ($ticketId) {
            $this->auditModel->registrar($userId, 'crear', 'ticket', $ticketId, $subject, null, [
                'subject' => $subject, 'priority' => $priority
            ]);
            return $ticketId;
        }

        return false;
    }

    public function replyToTicket($userId, $ticketId, $message) {
        if (empty($message)) {
            throw new \Exception('El mensaje no puede estar vacío.');
        }

        $ticket = $this->ticketModel->obtenerTicketPorId($userId, $ticketId);
        if (!$ticket) return false;

        $exito = $this->ticketModel->agregarRespuesta($ticketId, $userId, $message);
        if ($exito) {
            $this->ticketModel->cambiarEstado($ticketId, TicketStatus::RESPONDIDO->value);
            return true;
        }

        return false;
    }

    public function closeTicket($userId, $ticketId) {
        $ticket = $this->ticketModel->obtenerTicketPorId($userId, $ticketId);
        if ($ticket) {
            return $this->ticketModel->cambiarEstado($ticketId, TicketStatus::CERRADO->value);
        }
        return false;
    }
}
