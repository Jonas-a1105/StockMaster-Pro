<?php
namespace App\Controllers;

// ¡Ya no se usa Stripe!

class PremiumController {

    /**
     * Muestra la página de "Volverse Premium" con el modal de contacto
     */
    public function index() {
        $this->render('premium/index', [
            'email' => $_SESSION['user_email'] ?? 'usuario'
        ]);
    }

    private function render($vista, $data = []) {
        extract($data);
        $vistaContenido = __DIR__ . '/../../views/' . $vista . '.php';
        require __DIR__ . '/../../views/layouts/main.php';
    }
}