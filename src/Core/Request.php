<?php
namespace App\Core;

use App\Core\Validator;

class Request {
    private array $get;
    private array $post;
    private array $json;
    private array $all;
    private $validator;
    private array $errors = [];

    public function __construct() {
        $this->get = $this->sanitize($_GET);
        $this->post = $this->sanitize($_POST);
        $this->json = $this->parseJson();
        $this->all = array_merge($this->get, $this->post, $this->json);
    }

    /**
     * Sanitiza recursivamente un array de inputs
     */
    private function sanitize(array $data): array {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->sanitize($value);
            } else {
                // Trim y escape básico para prevenir XSS
                $data[$key] = htmlspecialchars(trim((string)$value), ENT_QUOTES, 'UTF-8');
            }
        }
        return $data;
    }

    /**
     * Parsea el body JSON si existe
     */
    private function parseJson(): array {
        $input = file_get_contents('php://input');
        if (empty($input)) return [];
        
        $data = json_decode($input, true);
        return is_array($data) ? $this->sanitize($data) : [];
    }

    /**
     * Obtiene un valor de cualquier fuente (GET, POST, JSON)
     */
    public function input(string $key, $default = null) {
        return $this->all[$key] ?? $default;
    }

    /**
     * Obtiene un valor de $_GET
     */
    public function query(string $key, $default = null) {
        return $this->get[$key] ?? $default;
    }

    /**
     * Obtiene un valor de $_POST
     */
    public function post(string $key, $default = null) {
        return $this->post[$key] ?? $default;
    }

    /**
     * Obtiene todos los datos combinados
     */
    public function all(): array {
        return $this->all;
    }

    /**
     * Obtiene solo los campos especificados
     */
    public function only(array $keys): array {
        return array_intersect_key($this->all, array_flip($keys));
    }

    /**
     * Verifica si existe una clave
     */
    public function has(string $key): bool {
        return isset($this->all[$key]);
    }

    /**
     * Valida los datos del request.
     * @param array $rules Reglas de validación.
     * @param mixed $db Objeto de base de datos para reglas 'unique'.
     * @return bool True si es válido, False de lo contrario.
     */
    public function validate(array $rules, $db = null): bool {
        $this->validator = new Validator($db);
        $isValid = $this->validator->validate($this->all(), $rules);
        
        if (!$isValid) {
            $this->errors = $this->validator->getErrors();
        }
        
        return $isValid;
    }

    /**
     * Retorna los errores de la última validación.
     */
    public function getErrors(): array {
        return $this->errors;
    }

    /**
     * Retorna el primer error de la última validación.
     */
    public function firstError(): ?string {
        return !empty($this->errors) ? reset($this->errors) : null;
    }

    /**
     * Obtiene el método de la petición
     */
    public function method(): string {
        return $_SERVER['REQUEST_METHOD'] ?? 'GET';
    }

    /**
     * Obtiene información de un archivo subido
     */
    public function file(string $key): ?array {
        return $_FILES[$key] ?? null;
    }

    /**
     * Verifica si se ha subido un archivo
     */
    public function hasFile(string $key): bool {
        return isset($_FILES[$key]) && $_FILES[$key]['error'] !== UPLOAD_ERR_NO_FILE;
    }

    /**
     * Verifica si es una petición POST
     */
    public function isPost(): bool {
        return $this->method() === 'POST';
    }

    /**
     * Verifica si es una petición AJAX
     */
    public function isAjax(): bool {
        return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
    }

    /**
     * Verifica si se espera una respuesta JSON
     */
    public function isJson(): bool {
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        return (strpos($accept, 'application/json') !== false || strpos($contentType, 'application/json') !== false);
    }
}
