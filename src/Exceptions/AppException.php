<?php
namespace App\Exceptions;

use Exception;

class AppException extends Exception {
    protected $httpCode = 500;

    public function getHttpCode() {
        return ($this->getCode() >= 100 && $this->getCode() < 600) ? $this->getCode() : 500;
    }
}
