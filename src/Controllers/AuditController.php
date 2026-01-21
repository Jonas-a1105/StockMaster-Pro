namespace App\Controllers;

use App\Core\BaseController;
use App\Models\AuditModel;

use App\Domain\Enums\Capability;

class AuditController extends BaseController {
    private $auditModel;

    public function __construct() {
        parent::__construct();
        $this->requireCapability(Capability::VIEW_REPORTS);
        $this->auditModel = new AuditModel();
    }

    public function index() {
        // Obtenemos filtros de la request
        $filtros = [
            'entity_type' => $this->request->query('entity_type'),
            'action' => $this->request->query('action'),
            'fecha_inicio' => $this->request->query('fecha_inicio'),
            'fecha_fin' => $this->request->query('fecha_fin'),
            'busqueda' => $this->request->query('busqueda')
        ];

        // Usar helper de paginación del BaseController
        // Nota: AuditModel->obtenerHistorial necesita ser adaptado para paginación estándar si se desea, 
        // pero por ahora mantendremos el límite fijo o lo pasaremos desde pagData.
        $logs = $this->auditModel->obtenerHistorial($this->userId, array_merge($filtros, ['limit' => 50]));

        return $this->response->view('audit/index', [
            'logs' => $logs,
            'filtros' => $filtros
        ]);
    }
}
