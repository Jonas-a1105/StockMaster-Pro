<?php
namespace App\Core\Middleware;

class AdminMiddleware implements MiddlewareInterface {
    public function handle(): bool {
        $controlador = $_GET['controlador'] ?? null;
        
        if ($controlador === 'admin' && ($_SESSION['user_rol'] ?? 'usuario') !== 'admin') {
            redirect('index.php?controlador=dashboard');
            return false;
        }
        
        return true;
    }
}
