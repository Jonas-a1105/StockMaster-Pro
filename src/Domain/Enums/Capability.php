<?php
namespace App\Domain\Enums;

enum Capability: string {
    case ACCESS_POS = 'pos.access';
    case VIEW_REPORTS = 'reports.view';
    case MANAGE_USERS = 'users.manage';
    case MANAGE_CONFIG = 'config.manage';
    case ADVANCED_INVENTORY = 'inventory.advanced';
    case MANAGE_TICKETS = 'tickets.manage';
    case EXPORT_DATA = 'data.export';
}
