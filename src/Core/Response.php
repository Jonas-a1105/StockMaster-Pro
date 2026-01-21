<?php
namespace App\Core;

use App\Core\View;
use Throwable;

class Response {
    /**
     * Envía las cabeceras de seguridad estándar
     */
    private function sendSecurityHeaders(): void {
        if (!headers_sent()) {
            header('X-Content-Type-Options: nosniff');
            header('X-Frame-Options: DENY');
            header('X-XSS-Protection: 1; mode=block');
        }
    }

    /**
     * Envía una respuesta JSON estandarizada
     */
    public function json($data, int $code = 200): void {
        header('Content-Type: application/json; charset=utf-8');
        $this->sendSecurityHeaders();
        http_response_code($code);
        
        $response = is_array($data) && isset($data['success']) ? $data : [
            'success' => $code >= 200 && $code < 300,
            'data' => $data
        ];

        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
    
    // ... rest of methods updated to call sendSecurityHeaders ...
    
    public function view(string $path, array $data = [], ?string $layout = null): void {
        $this->sendSecurityHeaders();
        try {
            echo View::render($path, $data, $layout);
            exit;
        } catch (Throwable $e) {
            $this->error($e->getMessage(), 404);
        }
    }

    /**
     * Redirección simple
     */
    public function redirect(string $url): void {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
        header('Location: ' . BASE_URL . $url);
        exit;
    }
}
