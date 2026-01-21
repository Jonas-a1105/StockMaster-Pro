<?php
namespace App\Core;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Domain\Enums\Capability;
use App\Domain\Enums\UserPlan;
use App\Core\Gate;

/**
 * BaseController centraliza la lógica común de todos los controladores.
 * Proporciona acceso a Request, Response y utilidades de sesión/permisos.
 */
abstract class BaseController {
    protected Request $request;
    protected Response $response;
    protected int $userId;
    protected ?string $userPlan;
    protected ?string $userRole;

    public function __construct() {
        $this->request = new Request();
        $this->response = new Response();
        $this->userId = (int)Session::get('user_id', 0);
        $this->userPlan = Session::get('user_plan', UserPlan::FREE->value);
        $this->userRole = Session::get('user_rol', 'usuario');
    }

    /**
     * Verifica si el usuario tiene un Id válido
     */
    protected function checkAuth(): void {
        if ($this->userId <= 0) {
            $this->response->redirect('index.php?controlador=login');
        }
    }

    /**
     * Verifica si el usuario actual tiene una capacidad específica
     */
    protected function can(Capability $capability): bool {
        return Gate::allows($this->userPlan, $this->userRole, $capability);
    }

    /**
     * Asegura que el usuario tenga una capacidad, de lo contrario lanza excepción o redirige
     */
    protected function requireCapability(Capability $capability, string $message = 'No tienes permiso para realizar esta acción.'): void {
        if (!$this->can($capability)) {
            if ($this->request->isAjax()) {
                throw new ForbiddenException($message);
            }
            Session::flash('error', $message);
            $this->response->redirect('index.php?controlador=dashboard');
        }
    }

    /**
     * @deprecated Usar requireCapability(Capability::ACCESS_POS) o similar
     */
    protected function requirePremium(string $message = 'Esta función es exclusiva del Plan Premium.'): void {
        $this->requireCapability(Capability::ACCESS_POS, $message);
    }

    /**
     * Helper para obtener datos de paginación estandarizados
     * @param int $total Total de registros
     * @param int $limitDefault Límite por defecto
     * @param string $sessionKey Clave para guardar el límite en sesión
     * @return array [limit, offset, page, totalPages]
     */
    protected function getPaginationData(int $total, int $limitDefault = 10, string $sessionKey = 'items_per_page'): array {
        $allowedLimits = [5, 10, 25, 50, 100];
        $limit = (int)$this->request->query('limit', Session::get($sessionKey, $limitDefault));
        
        if (!in_array($limit, $allowedLimits)) {
            $limit = $limitDefault;
        }
        
        Session::set($sessionKey, $limit);
        
        $page = $this->request->query('page', 1, 'int');
        if ($page < 1) $page = 1;
        
        $totalPages = ceil($total / $limit);
        if ($page > $totalPages && $totalPages > 0) $page = $totalPages;
        
        $offset = ($page - 1) * $limit;
        
        return [
            'limit' => $limit,
            'offset' => $offset,
            'page' => $page,
            'totalPages' => $totalPages
        ];
    }
}
