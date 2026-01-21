<?php
namespace App\Controllers;
 
use App\Core\BaseController;
use App\Services\ReporteService;
use App\Core\Session;
use App\Domain\Enums\UserPlan;
 
class ReporteController extends BaseController {
    private $reporteService;
 
    public function __construct() {
        parent::__construct();
 
        if ($this->userPlan === UserPlan::FREE->value) {
            Session::flash('error', 'Los reportes son una función Premium.');
            return $this->response->redirect('index.php?controlador=dashboard');
        }
        $this->reporteService = new ReporteService();
    }

    public function index() {
        $userId = Session::get('user_id');
        $filterData = $this->reporteService->getFilterData($userId);

        $datosReporte = [
            'productos' => $filterData['productos'],
            'clientes' => $filterData['clientes'],
            'filtros' => $this->request->all(),
            'reporte' => null
        ];

        if ($this->request->isPost()) {
            $tipo = $this->request->input('reporte-tipo', 'valor-inventario');
            $datosReporte['reporte'] = $this->reporteService->generateReport($userId, $tipo, $this->request->all());
        }

        return $this->response->view('reportes/index', $datosReporte);
    }
}