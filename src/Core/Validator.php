<?php
namespace App\Core;

class Validator {
    private $errors = [];
    private $db = null;

    public function __construct($db = null) {
        $this->db = $db;
    }

    /**
     * Valida un conjunto de datos contra un conjunto de reglas.
     * Ejemplo de $rules: ['email' => 'required|email', 'password' => 'required|min:6']
     */
    public function validate(array $data, array $rules): bool {
        $this->errors = [];

        foreach ($rules as $field => $fieldRules) {
            $value = $data[$field] ?? null;
            $rulesList = explode('|', $fieldRules);

            foreach ($rulesList as $rule) {
                $this->applyRule($field, $value, $rule, $data);
            }
        }

        return empty($this->errors);
    }

    /**
     * Aplica una regla individual
     */
    private function applyRule(string $field, $value, string $rule, array $allData): void {
        $params = [];
        if (strpos($rule, ':') !== false) {
            list($ruleName, $paramStr) = explode(':', $rule);
            $params = explode(',', $paramStr);
            $rule = $ruleName;
        }

        switch ($rule) {
            case 'required':
                if ($value === null || (is_string($value) && trim($value) === '') || (is_array($value) && empty($value))) {
                    $this->addError($field, "El campo $field es obligatorio.");
                }
                break;

            case 'email':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->addError($field, "El formato de correo es inválido.");
                }
                break;

            case 'numeric':
                if (!empty($value) && !is_numeric($value)) {
                    $this->addError($field, "El campo $field debe ser un número.");
                }
                break;

            case 'min':
                $min = (int)$params[0];
                if (!empty($value)) {
                    if (is_numeric($value) && $value < $min) {
                        $this->addError($field, "El valor debe ser mínimo $min.");
                    } elseif (is_string($value) && strlen($value) < $min) {
                        $this->addError($field, "La longitud mínima es de $min caracteres.");
                    }
                }
                break;

            case 'max':
                $max = (int)$params[0];
                if (!empty($value)) {
                    if (is_numeric($value) && $value > $max) {
                        $this->addError($field, "El valor debe ser máximo $max.");
                    } elseif (is_string($value) && strlen($value) > $max) {
                        $this->addError($field, "La longitud máxima es de $max caracteres.");
                    }
                }
                break;

            case 'unique':
                if ($this->db && !empty($value)) {
                    $table = $params[0];
                    $column = $params[1];
                    $exceptId = $params[2] ?? null;
                    
                    $sql = "SELECT COUNT(*) FROM $table WHERE $column = ?";
                    $args = [$value];
                    
                    if ($exceptId) {
                        $sql .= " AND id != ?";
                        $args[] = $exceptId;
                    }
                    
                    $stmt = $this->db->prepare($sql);
                    $stmt->execute($args);
                    if ($stmt->fetchColumn() > 0) {
                        $this->addError($field, "Este valor ya está en uso.");
                    }
                }
                break;
        }
    }

    private function addError(string $field, string $message): void {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = $message;
        }
    }

    public function getErrors(): array {
        return $this->errors;
    }

    public function firstError(): ?string {
        if (empty($this->errors)) return null;
        return reset($this->errors);
    }
}
