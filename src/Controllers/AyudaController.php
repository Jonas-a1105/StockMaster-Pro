<?php
namespace App\Controllers;

use App\Core\Session;

class AyudaController {

    public function __construct() {
        // Solo usuarios logueados pueden ver la ayuda
        if (!Session::isLoggedIn()) {
            redirect('index.php?controlador=login');
        }
    }

    public function index() {
        // Título de la página
        $data = [
            'titulo' => 'Centro de Ayuda'
        ];
        
        // Renderizar vista principal
        // El layout main se encarga de headers/footers
        $this->render('ayuda/index', $data);
    }

    private function render($vista, $data = []) {
        // Construir ruta completa de la vista para el layout
        $vistaContenido = __DIR__ . '/../../views/' . $vista . '.php';
        
        extract($data);
        require __DIR__ . '/../../views/layouts/main.php'; 
    }
}
