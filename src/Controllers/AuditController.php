<?php
namespace App\Controllers;

use App\Core\Database;
use App\Core\Session;

class AuditController {

    public function __construct() {
        if ($_SESSION['user_plan'] === 'free') {
            redirect('index.php?controlador=premium');
        }
    }

    public function index() {
        $db = Database::conectar();
        $userId = $_SESSION['user_id'];
        
        // Obtener logs del usuario
        $query = "SELECT * FROM audit_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT 50";
        $stmt = $db->prepare($query);
        $stmt->execute([$userId]);
        $logs = $stmt->fetchAll();

        $this->render('audit/index', ['logs' => $logs]);
    }

    private function render($vista, $data = []) {
        extract($data);
        $vistaContenido = __DIR__ . '/../../views/' . $vista . '.php';
        require __DIR__ . '/../../views/layouts/main.php';
    }
}
