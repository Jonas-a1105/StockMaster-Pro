<?php
namespace App\Exceptions;

class NotFoundException extends AppException {
    protected $httpCode = 404;
}
