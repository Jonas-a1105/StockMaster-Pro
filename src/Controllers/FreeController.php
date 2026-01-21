<?php
namespace App\Controllers;

use App\Core\BaseController;
use App\Core\Session;

class FreeController extends BaseController {

    /**
     * Muestra la página de bienvenida del plan gratuito
     */
    public function index() {
        return $this->response->view('free/index', [
            'email' => Session::get('user_email', 'usuario')
        ]);
    }
}