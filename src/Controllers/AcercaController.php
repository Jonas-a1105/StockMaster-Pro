<?php
namespace App\Controllers;

use App\Core\Session;

class AcercaController {
    
    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?controlador=login&accion=index');
            exit;
        }
    }

    public function index() {
        $this->render('acerca/index');
    }

    private function render($vista, $data = []) {
        extract($data);
        $vistaContenido = __DIR__ . '/../../views/' . $vista . '.php';
        require __DIR__ . '/../../views/layouts/main.php';
    }
}
