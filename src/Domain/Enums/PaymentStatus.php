<?php
namespace App\Domain\Enums;

enum PaymentStatus: string {
    case PAGADA = 'Pagada';
    case PENDIENTE = 'Pendiente';
    case ANULADA = 'Anulada';
}
