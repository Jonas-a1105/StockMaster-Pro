<?php
namespace App\Controllers;

use App\Core\Session;

class FreeController {

    /**
     * Muestra la página de bienvenida del plan gratuito
     */
    public function index() {
        $this->render('free/index', [
            'email' => $_SESSION['user_email'] ?? 'usuario'
        ]);
    }

    private function render($vista, $data = []) {
        extract($data);
        $vistaContenido = __DIR__ . '/../../views/' . $vista . '.php';
        require __DIR__ . '/../../views/layouts/main.php';
    }
}