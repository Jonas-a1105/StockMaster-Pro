<?php
namespace App\Controllers;
 
use App\Core\BaseController;
use App\Core\Session;
use App\Models\UsuarioModel;
use App\Models\TicketModel;
use App\Domain\Enums\UserRole;
use App\Domain\Enums\UserPlan;
use App\Exceptions\ForbiddenException;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Exceptions\AppException;
 
class AdminController extends BaseController {
    private $usuarioModel;
    private $ticketModel;
 
    public function __construct() {
        parent::__construct();
 
        if ($this->userRole !== UserRole::ADMIN->value) {
            throw new ForbiddenException('No tienes permisos de administrador.');
        }
        
        $this->usuarioModel = new UsuarioModel();
        $this->ticketModel = new TicketModel();
    }

    public function index() {
        $usuarios = $this->usuarioModel->obtenerTodos();
        return $this->response->view('admin/index', ['usuarios' => $usuarios]);
    }

    public function cambiarPlan() {
        if (!$this->request->isPost()) {
            throw new AppException('Solicitud inválida.', 405);
        }

        $usuarioId = $this->request->input('usuario_id', 0, 'int');
        $accion = $this->request->input('accion');
        
        if ($accion === UserPlan::FREE->value) {
            $this->usuarioModel->actualizarPlan($usuarioId, UserPlan::FREE->value, null);
            Session::flash('success', 'Usuario bajado a plan Gratis.');
        } elseif ($accion === UserPlan::PREMIUM->value) {
            $dias = $this->request->input('duracion', 'permanent');
            $fechaVencimiento = null;

            if ($dias !== 'permanent') {
                $diasInt = (int)$dias;
                $fechaVencimiento = date('Y-m-d H:i:s', strtotime("+$diasInt days"));
                $mensaje = "Plan Premium activado por $diasInt días.";
            } else {
                $mensaje = "Plan Premium activado permanentemente.";
            }

            $this->usuarioModel->actualizarPlan($usuarioId, UserPlan::PREMIUM->value, $fechaVencimiento);
            Session::flash('success', $mensaje);
        }
        
        return $this->response->redirect('index.php?controlador=admin&accion=index');
    }

    public function tickets() {
        $tickets = $this->ticketModel->obtenerTodosLosTickets();
        return $this->response->view('admin/tickets', ['tickets' => $tickets]);
    }

    public function verTicket() {
        $ticketId = $this->request->query('id', 0, 'int');
        $ticket = $this->ticketModel->obtenerTicketPorIdAdmin($ticketId);
        
        if (!$ticket) {
            throw new NotFoundException('Ticket no encontrado.');
        }
        
        $respuestas = $this->ticketModel->obtenerRespuestas($ticketId);
        return $this->response->view('admin/verTicket', ['ticket' => $ticket, 'respuestas' => $respuestas]);
    }

    public function responderTicket() {
        if (!$this->request->isPost()) {
            throw new AppException('Solicitud inválida.', 405);
        }

        $adminUserId = Session::get('user_id');
        $ticketId = $this->request->input('ticket_id', 0, 'int');
        $message = $this->request->input('message');
        $newStatus = $this->request->input('status', 'En Progreso');

        if (empty($message)) {
            throw new ValidationException(['message' => 'La respuesta no puede estar vacía.'], 'Error al responder ticket.');
        }

        $this->ticketModel->agregarRespuesta($ticketId, $adminUserId, $message);
        $this->ticketModel->cambiarEstado($ticketId, $newStatus);

        Session::flash('success', 'Respuesta enviada.');
        return $this->response->redirect('index.php?controlador=admin&accion=verTicket&id=' . $ticketId);
    }
}
