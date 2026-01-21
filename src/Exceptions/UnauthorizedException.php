<?php
namespace App\Exceptions;

class UnauthorizedException extends AppException {
    protected $httpCode = 401;
}
