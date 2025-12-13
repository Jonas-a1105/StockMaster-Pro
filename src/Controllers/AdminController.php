<?php
namespace App\Controllers;

use App\Core\Session;
use App\Models\UsuarioModel;
use App\Models\TicketModel;


class AdminController {

    private $usuarioModel;
    private $ticketModel;

    public function __construct() {
        // Verificar que sea Admin
        if (!isset($_SESSION['user_rol']) || $_SESSION['user_rol'] !== 'admin') {
            redirect('index.php?controlador=dashboard');
        }
        
        // Instanciar modelos
        $this->usuarioModel = new UsuarioModel();
        
        // IMPORTANTE: Si no has creado el TicketModel, esto dará error.
        // Asegúrate de tener src/Models/TicketModel.php
        $this->ticketModel = new TicketModel();
    }

    /**
     * Acción Principal: Muestra la lista de usuarios
     */
    public function index() {
        $usuarios = $this->usuarioModel->obtenerTodos();
        
        $this->render('admin/index', [
            'usuarios' => $usuarios
        ]);
    }

    // --- FUNCIÓN ACTUALIZADA ---
    public function cambiarPlan() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuarioId = (int)$_POST['usuario_id'];
            $accion = $_POST['accion']; // 'premium' o 'free'
            
            if ($accion === 'free') {
                // Bajar a gratis
                $this->usuarioModel->actualizarPlan($usuarioId, 'free', null);
                Session::flash('success', 'Usuario bajado a plan Gratis.');
            } 
            elseif ($accion === 'premium') {
                // Activar Premium
                $dias = $_POST['duracion'] ?? 'permanent';
                $fechaVencimiento = null;

                if ($dias !== 'permanent') {
                    // Calcular la fecha futura (ej: HOY + 15 Días)
                    $diasInt = (int)$dias;
                    $fechaVencimiento = date('Y-m-d H:i:s', strtotime("+$diasInt days"));
                    $mensaje = "Plan Premium activado por $diasInt días.";
                } else {
                    $mensaje = "Plan Premium activado permanentemente.";
                }

                $this->usuarioModel->actualizarPlan($usuarioId, 'premium', $fechaVencimiento);
                Session::flash('success', $mensaje);
            }
        }
        
        redirect('index.php?controlador=admin&accion=index');
    }

    /**
     * Acción: Ver lista de todos los tickets
     */
    public function tickets() {
        $tickets = $this->ticketModel->obtenerTodosLosTickets();
        
        $this->render('admin/tickets', [
            'tickets' => $tickets
        ]);
    }

    /**
     * Acción: Ver detalle de un ticket específico
     */
    public function verTicket() {
        $ticketId = (int)($_GET['id'] ?? 0);
        
        $ticket = $this->ticketModel->obtenerTicketPorIdAdmin($ticketId);
        if (!$ticket) {
            Session::flash('error', 'Ticket no encontrado.');
            redirect('index.php?controlador=admin&accion=tickets');
        }
        
        $respuestas = $this->ticketModel->obtenerRespuestas($ticketId);
        
        $this->render('admin/verTicket', [
            'ticket' => $ticket,
            'respuestas' => $respuestas
        ]);
    }

    /**
     * Acción: Responder a un ticket
     */
    public function responderTicket() {
        $adminUserId = $_SESSION['user_id'];
        $ticketId = (int)$_POST['ticket_id'];
        $message = $_POST['message'] ?? '';
        $newStatus = $_POST['status'] ?? 'En Progreso';

        if (empty($message)) {
            Session::flash('error', 'La respuesta no puede estar vacía.');
            redirect('index.php?controlador=admin&accion=verTicket&id=' . $ticketId);
        }

        // Añadir la respuesta
        $this->ticketModel->agregarRespuesta($ticketId, $adminUserId, $message);
        
        // Actualizar el estado
        $this->ticketModel->cambiarEstado($ticketId, $newStatus);

        Session::flash('success', 'Respuesta enviada.');
        redirect('index.php?controlador=admin&accion=verTicket&id=' . $ticketId);
    }

    private function render($vista, $data = []) {
        extract($data);
        // Verifica que la vista exista antes de cargarla para evitar errores fatales
        $archivoVista = __DIR__ . '/../../views/' . $vista . '.php';
        if (file_exists($archivoVista)) {
            $vistaContenido = $archivoVista;
        } else {
            die("Error: La vista '$vista' no existe en: $archivoVista");
        }
        require __DIR__ . '/../../views/layouts/main.php';
    }
}