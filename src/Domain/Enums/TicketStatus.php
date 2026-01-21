<?php
namespace App\Domain\Enums;

enum TicketStatus: string {
    case ABIERTO = 'Abierto';
    case RESPONDIDO = 'Respondido';
    case CERRADO = 'Cerrado';
}
