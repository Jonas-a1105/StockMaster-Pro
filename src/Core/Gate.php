<?php
namespace App\Core;

use App\Domain\Enums\Capability;
use App\Domain\Enums\UserPlan;
use App\Domain\Enums\UserRole;

class Gate {
    /**
     * Mapeo de Capacidades por Plan
     */
    private static array $planCapabilities = [
        UserPlan::FREE->value => [
            // El plan Free solo tiene funciones básicas
        ],
        UserPlan::PREMIUM->value => [
            Capability::ACCESS_POS->value,
            Capability::VIEW_REPORTS->value,
            Capability::ADVANCED_INVENTORY->value,
            Capability::MANAGE_TICKETS->value,
            Capability::EXPORT_DATA->value
        ]
    ];

    /**
     * Mapeo de Capacidades por Rol
     */
    private static array $roleCapabilities = [
        UserRole::USUARIO->value => [],
        UserRole::ADMIN->value => [
            Capability::MANAGE_USERS->value,
            Capability::MANAGE_CONFIG->value
        ]
    ];

    /**
     * Verifica si el usuario actual tiene el permiso necesario
     */
    public static function allows(string $plan, string $role, Capability $capability): bool {
        $capValue = $capability->value;

        // Si el plan tiene la capacidad
        if (in_array($capValue, self::$planCapabilities[$plan] ?? [])) {
            return true;
        }

        // Si el rol tiene la capacidad
        if (in_array($capValue, self::$roleCapabilities[$role] ?? [])) {
            return true;
        }

        return false;
    }
}
