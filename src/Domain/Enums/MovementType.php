<?php
namespace App\Domain\Enums;

enum MovementType: string {
    case ENTRADA = 'Entrada';
    case SALIDA = 'Salida';
    case AJUSTE = 'Ajuste';
}
