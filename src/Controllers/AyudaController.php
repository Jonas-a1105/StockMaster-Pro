<?php
namespace App\Controllers;
 
use App\Core\BaseController;
 
class AyudaController extends BaseController {
 
    public function __construct() {
        parent::__construct();
        $this->checkAuth();
    }
 
    public function index() {
        return $this->response->view('ayuda/index', [
            'titulo' => 'Centro de Ayuda'
        ]);
    }
}
