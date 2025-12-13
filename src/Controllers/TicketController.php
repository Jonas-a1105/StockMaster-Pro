<?php
namespace App\Controllers;

use App\Core\Session;
use App\Models\TicketModel;
use App\Models\TicketsModel;

class TicketController {

    private $ticketModel;

    public function __construct() {
        // --- ¡PROTECCIÓN PREMIUM! ---
        if ($_SESSION['user_plan'] === 'free') {
            Session::flash('error', 'El soporte por tickets es una función Premium.');
            redirect('index.php?controlador=dashboard');
        }
        $this->ticketModel = new TicketModel();
    }

    /**
     * Muestra la lista de "Mis Tickets"
     */
    public function index() {
        $userId = $_SESSION['user_id'];
        $tickets = $this->ticketModel->obtenerTicketsPorUsuario($userId);
        
        $this->render('tickets/index', [
            'tickets' => $tickets
        ]);
    }

    /**
     * Muestra el formulario para crear un nuevo ticket
     */
    public function crear() {
        $this->render('tickets/crear');
    }

    /**
     * Guarda el nuevo ticket y la primera respuesta
     */
    public function guardar() {
        $userId = $_SESSION['user_id'];
        $subject = $_POST['subject'] ?? '';
        $priority = $_POST['priority'] ?? 'Media';
        $message = $_POST['message'] ?? '';

        if (empty($subject) || empty($message)) {
            Session::flash('error', 'Asunto y Mensaje son obligatorios.');
            redirect('index.php?controlador=ticket&accion=crear');
        }

        $ticketId = $this->ticketModel->crearTicket($userId, $subject, $priority, $message);

        if ($ticketId) {
            Session::flash('success', 'Ticket creado exitosamente.');
            redirect('index.php?controlador=ticket&accion=ver&id=' . $ticketId);
        } else {
            Session::flash('error', 'No se pudo crear el ticket.');
            redirect('index.php?controlador=ticket&accion=crear');
        }
    }

    /**
     * Muestra un ticket específico y sus respuestas (la vista de chat)
     */
    public function ver() {
        $userId = $_SESSION['user_id'];
        $ticketId = (int)$_GET['id'];

        // Verificar que el ticket pertenece al usuario
        $ticket = $this->ticketModel->obtenerTicketPorId($userId, $ticketId);
        if (!$ticket) {
            Session::flash('error', 'No tienes permiso para ver este ticket.');
            redirect('index.php?controlador=ticket');
        }
        
        $respuestas = $this->ticketModel->obtenerRespuestas($ticketId);

        $this->render('tickets/ver', [
            'ticket' => $ticket,
            'respuestas' => $respuestas
        ]);
    }

    /**
     * Guarda una nueva respuesta del usuario
     */
    public function responder() {
        $userId = $_SESSION['user_id'];
        $ticketId = (int)$_POST['ticket_id'];
        $message = $_POST['message'] ?? '';

        // Verificar que el ticket pertenece al usuario
        $ticket = $this->ticketModel->obtenerTicketPorId($userId, $ticketId);
        if (!$ticket || empty($message)) {
            Session::flash('error', 'No se pudo enviar la respuesta.');
            redirect('index.php?controlador=ticket');
        }
        
        // Añadir la respuesta
        $this->ticketModel->agregarRespuesta($ticketId, $userId, $message);
        
        // Marcar como "En Progreso" si el admin lo había cerrado
        $this->ticketModel->cambiarEstado($ticketId, 'En Progreso');

        Session::flash('success', 'Respuesta enviada.');
        redirect('index.php?controlador=ticket&accion=ver&id=' . $ticketId);
    }
    
    /**
     * Cierra un ticket (acción del usuario)A
     */
    public function cerrar() {
        $userId = $_SESSION['user_id'];
        $ticketId = (int)$_GET['id'];
        
        $ticket = $this->ticketModel->obtenerTicketPorId($userId, $ticketId);
        if ($ticket) {
            $this->ticketModel->cambiarEstado($ticketId, 'Cerrado');
            Session::flash('success', 'Ticket cerrado.');
        }
        redirect('index.php?controlador=ticket&accion=ver&id=' . $ticketId);
    }


    private function render($vista, $data = []) {
        extract($data);
        $vistaContenido = __DIR__ . '/../../views/' . $vista . '.php';
        require __DIR__ . '/../../views/layouts/main.php';
    }
}