<?php
namespace App\Core\Middleware;

use App\Helpers\Security;

class CsrfMiddleware implements MiddlewareInterface {
    private $excludedRoutes = ['webhook'];

    public function handle(): bool {
        $controlador = $_GET['controlador'] ?? '';
        
        if (!in_array($controlador, $this->excludedRoutes)) {
            Security::validateCsrf();
        }
        
        return true;
    }
}
