<?php
namespace App\Exceptions;

class ValidationException extends AppException {
    protected $httpCode = 422;
    protected array $errors = [];

    public function __construct(array $errors, $message = "Error de validación") {
        parent::__construct($message);
        $this->errors = $errors;
    }

    public function getErrors(): array {
        return $this->errors;
    }
}
