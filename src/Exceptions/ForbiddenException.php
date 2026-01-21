<?php
namespace App\Exceptions;

class ForbiddenException extends AppException {
    protected $httpCode = 403;
}
