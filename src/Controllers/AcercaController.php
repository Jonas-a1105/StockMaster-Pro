<?php
namespace App\Controllers;
 
use App\Core\BaseController;
 
class AcercaController extends BaseController {
 
    public function __construct() {
        parent::__construct();
        $this->checkAuth();
    }
 
    public function index() {
        return $this->response->view('acerca/index');
    }
}
