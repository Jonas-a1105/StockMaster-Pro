<?php
namespace App\Controllers;
 
use App\Core\BaseController;
use App\Core\Session;
 
class PremiumController extends BaseController {
 
    /**
     * Muestra la página de "Volverse Premium" con el modal de contacto
     */
    public function index() {
        return $this->response->view('premium/index', [
            'email' => Session::get('user_email', 'usuario')
        ]);
    }
}