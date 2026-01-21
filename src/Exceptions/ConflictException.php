<?php
namespace App\Exceptions;

class ConflictException extends AppException {
    protected $httpCode = 409;
}
