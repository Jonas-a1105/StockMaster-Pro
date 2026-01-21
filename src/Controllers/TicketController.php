<?php
namespace App\Controllers;
 
use App\Core\BaseController;
use App\Models\TicketModel;
use App\Services\TicketService;
use App\Domain\Enums\UserPlan;
use App\Core\Session;
use App\Exceptions\ForbiddenException;
use App\Exceptions\NotFoundException;
use App\Exceptions\AppException;
 
class TicketController extends BaseController {
    private $ticketModel;
    private $ticketService;
 
    public function __construct() {
        parent::__construct();
 
        // Premium Check
        if ($this->userPlan === UserPlan::FREE->value) {
            if ($this->request->isAjax()) {
                throw new ForbiddenException('El soporte por tickets es una función Premium.');
            }
            Session::flash('error', 'El soporte por tickets es una función Premium.');
            return $this->response->redirect('index.php?controlador=dashboard');
        }
        $this->ticketModel = new TicketModel();
        $this->ticketService = new TicketService();
    }

    public function index() {
        $userId = Session::get('user_id');
        $tickets = $this->ticketModel->obtenerTicketsPorUsuario($userId);
        return $this->response->view('tickets/index', ['tickets' => $tickets]);
    }

    public function crear() {
        return $this->response->view('tickets/crear');
    }

    public function guardar() {
        if (!$this->request->isPost()) {
            throw new AppException('Solicitud inválida.', 405);
        }

        $userId = Session::get('user_id');
        $ticketId = $this->ticketService->createTicket($userId, $this->request->all());
        
        if ($ticketId) {
            Session::flash('success', 'Ticket creado exitosamente.');
            return $this->response->redirect('index.php?controlador=ticket&accion=ver&id=' . $ticketId);
        }
        
        throw new AppException('Error al crear el ticket.', 500);
    }

    public function ver() {
        $userId = Session::get('user_id');
        $ticketId = $this->request->query('id', 0, 'int');

        $ticket = $this->ticketModel->obtenerTicketPorId($userId, $ticketId);
        if (!$ticket) {
            throw new ForbiddenException('No tienes permiso para ver este ticket o no existe.');
        }
        
        $respuestas = $this->ticketModel->obtenerRespuestas($ticketId);

        return $this->response->view('tickets/ver', [
            'ticket' => $ticket,
            'respuestas' => $respuestas
        ]);
    }

    public function responder() {
        if (!$this->request->isPost()) {
             throw new AppException('Solicitud inválida.', 405);
        }

        $userId = Session::get('user_id');
        $ticketId = $this->request->input('ticket_id', 0, 'int');
        $message = $this->request->input('message', '');

        if ($this->ticketService->replyToTicket($userId, $ticketId, $message)) {
            Session::flash('success', 'Respuesta enviada.');
        } else {
            throw new AppException('Error al enviar la respuesta.');
        }
        
        return $this->response->redirect('index.php?controlador=ticket&accion=ver&id=' . $ticketId);
    }
    
    public function cerrar() {
        $userId = Session::get('user_id');
        $id = $this->request->query('id', 0, 'int');
        if ($this->ticketService->closeTicket($userId, $id)) {
            Session::flash('success', 'Ticket cerrado.');
        }
        return $this->response->redirect('index.php?controlador=ticket&accion=ver&id=' . $id);
    }
}
