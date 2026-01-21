<?php
namespace App\Helpers;

use App\Core\Session;

class Security {
    /**
     * Genera un token CSRF si no existe
     */
    public static function csrfToken() {
        return Session::csrfToken();
    }

    /**
     * Valida el token CSRF recibido en la petición
     */
    public static function validateCsrf() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_SERVER['HTTP_X_XSRF_TOKEN'] ?? null;
            
            // Si la petición es JSON, buscar en el body
            if (!$token) {
                $input = json_decode(file_get_contents('php://input'), true);
                if (is_array($input)) {
                    $token = $input['csrf_token'] ?? null;
                }
            }

            if (!$token || !Session::verifyCsrf($token)) {
                if ((isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') || 
                    (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false)) {
                    http_response_code(403);
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Error de seguridad: Token CSRF no válido o expirado. Por favor, recargue la página.']);
                } else {
                    Session::flash('error', 'Su sesión ha expirado o el token de seguridad es inválido. Por favor, intente de nuevo.');
                    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'index.php'));
                }
                exit;
            }
        }
    }

    /**
     * Genera un campo oculto con el token CSRF para formularios
     */
    public static function csrfField() {
        $token = self::csrfToken();
        return '<input type="hidden" name="csrf_token" value="' . $token . '">';
    }
}
