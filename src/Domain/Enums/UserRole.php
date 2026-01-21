<?php
namespace App\Domain\Enums;

enum UserRole: string {
    case ADMIN = 'admin';
    case USUARIO = 'usuario';
}
